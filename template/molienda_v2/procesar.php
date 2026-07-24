<?php
require '../conection.php';
require '../sesion.php';
verificarAutenticacion();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
    exit;
}

$sede = $_SESSION['sede'];
$id_usuario = $_SESSION['id_usuario'];
$fecha_formulario = $_POST['fecha'] ?? date('Y-m-d');

// 1. Verificar firma por cédula
$cedula_firma = trim($_POST['cedula_firma'] ?? '');
$nombre_firma = null;
if (!empty($cedula_firma)) {
    $stmtFirma = $pdoUsuarios->prepare("SELECT nombre_u FROM usuarios WHERE cedula_u = ?");
    $stmtFirma->execute([$cedula_firma]);
    $rowFirma = $stmtFirma->fetch(PDO::FETCH_ASSOC);
    if ($rowFirma) {
        $nombre_firma = $rowFirma['nombre_u'];
    } else {
        echo json_encode(['status' => 'error', 'message' => 'La cédula ingresada no se encuentra registrada o autorizada.']);
        exit;
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Cédula de firma es obligatoria.']);
    exit;
}

// 1. Preparar Carpeta de Destino
$base_dir = "../../archivos/generados/molienda/" . $sede . "/";
if (!is_dir($base_dir)) {
    mkdir($base_dir, 0777, true);
}

// ── LÓGICA DE FECHA LINEAL ────────────────────────────────────────────────
// Antes de usar la fecha del formulario, buscamos si existe algún día con
// turnos incompletos (menos de 3 turnos registrados) en el JSON del mes.
// Si existe, el nuevo turno le pertenece a ese día (sistema lineal).
// Solo si NO hay pendientes se respeta la fecha enviada desde el formulario.
$mes = date('Y-m', strtotime($fecha_formulario));
$json_file_check = $base_dir . $mes . ".json";

$fecha = $fecha_formulario; // valor por defecto
$fecha_pendiente_encontrada = false;

if (file_exists($json_file_check)) {
    $registros_mes = json_decode(file_get_contents($json_file_check), true) ?: [];

    // Agrupar registros por fecha y contar turnos por día
    $turnos_por_dia = [];
    foreach ($registros_mes as $r) {
        $f = $r['fecha'] ?? null;
        if ($f) {
            $turnos_por_dia[$f] = ($turnos_por_dia[$f] ?? 0) + 1;
        }
    }

    // Determinar máximo de turnos permitidos según sede
    $max_turnos = ($sede === 'ZS') ? 2 : 3;

    // Buscar el día más antiguo con turnos incompletos
    ksort($turnos_por_dia); // orden cronológico
    foreach ($turnos_por_dia as $dia => $cantidad) {
        if ($cantidad < $max_turnos) {
            $fecha = $dia;
            $fecha_pendiente_encontrada = true;
            break;
        }
    }
}

// Recalcular mes en base a la fecha definitiva (puede diferir del formulario)
$mes = date('Y-m', strtotime($fecha));
$json_file = $base_dir . $mes . ".json";
// ─────────────────────────────────────────────────────────────────────────

// 2. Estructura de Datos
function filtrarLotes($items) {
    if (!is_array($items)) return [];
    $procesados = [];
    foreach ($items as $id => $data) {
        if (isset($data['active'])) {
            // Filtrar solo los lotes que tengan un valor
            $data['lotes'] = array_filter($data['lotes'] ?? [], function($l) {
                return !empty($l['valor']) && $l['valor'] > 0;
            });
            // Reindexar el array de lotes para que sea secuencial en el JSON
            $data['lotes'] = array_values($data['lotes']);
            $procesados[$id] = $data;
        }
    }
    return $procesados;
}

// Procesar datos de trigo: filtrar filas sin tipo definido
$trigo_raw = $_POST['trigo'] ?? [];
$trigo_procesado = [];
foreach ($trigo_raw as $item) {
    $tipo = trim($item['tipo'] ?? '');
    if ($tipo !== '') {
        $trigo_procesado[] = [
            'tipo'           => $tipo,
            'cantidad'       => floatval($item['cantidad'] ?? 0),
            'lote'           => trim($item['lote'] ?? ''),
            'destino_harina' => trim($item['destino_harina'] ?? '')
        ];
    }
}

$nuevo_registro = [
    "id" => uniqid(),
    "fecha" => $fecha,
    "hora" => $_POST['hora'] ?? date('H:i'),
    "responsable" => $_POST['responsable'],
    "almacenista" => $_POST['almacenista'],
    "responsables_intervencion" => array_values(array_filter($_POST['responsables_intervencion'] ?? [])),
    "sede" => $sede,
    "harinas" => filtrarLotes($_POST['harinas'] ?? []),
    "subproductos" => filtrarLotes($_POST['subproductos'] ?? []),
    "materiales" => filtrarLotes($_POST['materiales'] ?? []),
    "trigo" => $trigo_procesado,
    "firma" => $nombre_firma,
    "created_at" => date('Y-m-d H:i:s')
];

// 2.5 Determinar el número de turno para persistencia
$num_turno = 1;
try {
    $queryT = "SELECT turn1, turn2, turn3 FROM control_molienda WHERE zona = ? ORDER BY id_proceso DESC LIMIT 1";
    $stmtT = $pdoControl->prepare($queryT);
    $stmtT->execute([$sede]);
    $procT = $stmtT->fetch(PDO::FETCH_ASSOC);
    if ($procT) {
        if ($procT['turn1'] == 0) $num_turno = 1;
        else if ($procT['turn2'] == 0) $num_turno = 2;
        else if ($sede !== 'ZS' && $procT['turn3'] == 0) $num_turno = 3;
    }
} catch (Exception $e) {}
$nuevo_registro["turno"] = $num_turno;

// 3. Guardar en JSON
$datos_existentes = [];
if (file_exists($json_file)) {
    $contenido = file_get_contents($json_file);
    $datos_existentes = json_decode($contenido, true) ?: [];
}

$datos_existentes[] = $nuevo_registro;

if (file_put_contents($json_file, json_encode($datos_existentes, JSON_PRETTY_PRINT)) === false) {
    echo json_encode(['status' => 'error', 'message' => 'Error al guardar el archivo JSON']);
    exit;
}

// 4. Actualizar Estado en SQL (Unificado)
try {
    // Buscar el proceso actual (el último abierto para esta zona)
    $query = "SELECT id_proceso, turn1, turn2, turn3 FROM control_molienda 
              WHERE zona = ? ORDER BY id_proceso DESC LIMIT 1";
    $stmt = $pdoControl->prepare($query);
    $stmt->execute([$sede]);
    $proceso = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$proceso) {
        // Crear primer registro si no existe
        $queryInsert = "INSERT INTO control_molienda (fecha, archivogen, zona, turn1, turn2, turn3, creador) 
                        VALUES (?, 0, ?, 0, 0, 0, ?)";
        $stmtInsert = $pdoControl->prepare($queryInsert);
        $stmtInsert->execute([$fecha, $sede, $id_usuario]);
        
        $id_proceso = $pdoControl->lastInsertId();
        $proceso = ['id_proceso' => $id_proceso, 'turn1' => 0, 'turn2' => 0, 'turn3' => 0];
    } else {
        $id_proceso = $proceso['id_proceso'];
    }

    // Determinar qué turno actualizar
    // Usamos el estado actual para saber cuál fue el último guardado
    // El frontend ya valida el orden, aquí aseguramos persistencia
    $turno_a_marcar = null;
    if ($proceso['turn1'] == 0) $turno_a_marcar = 'turn1';
    else if ($proceso['turn2'] == 0) $turno_a_marcar = 'turn2';
    else if ($sede !== 'ZS' && $proceso['turn3'] == 0) $turno_a_marcar = 'turn3';

    if ($turno_a_marcar) {
        $queryUpdate = "UPDATE control_molienda SET $turno_a_marcar = 1, archivo_ruta = ? WHERE id_proceso = ?";
        $stmtUpdate = $pdoControl->prepare($queryUpdate);
        $stmtUpdate->execute([$json_file, $id_proceso]);
    }

    // === SINCRONIZACIÓN AUTOMÁTICA (Postgres y Excel) ===
    try {
        require_once __DIR__ . '/../gobierno_datos/bitacora_produccion/sincronizador.php';
        $sync = new ProduccionSincronizador();
        $sync->sincronizar($fecha, $sede);
    } catch (Exception $e) {
        error_log("Fallo sincronización automatica: " . $e->getMessage());
    }
    // ====================================================

} catch (PDOException $e) {
    error_log("Error SQL en Molienda V2: " . $e->getMessage());
    // No detenemos el proceso si falla el SQL, pero informamos
}

echo json_encode(['status' => 'ok', 'message' => 'Registro guardado correctamente y turno actualizado.']);
?>
