<?php
include '../sesion.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['nombre']) || empty($_SESSION['sede'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Sin sesión activa o sede asignada. Recarga la página.']);
    exit;
}

$sede = $_SESSION['sede'];

$input_json = file_get_contents("php://input");
$input_array = json_decode($input_json, true);

$id_registro = $input_array['id_registro'] ?? '';
if (empty($id_registro)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Falta id_registro.']);
    exit;
}

$sede_saneada = preg_replace('/[^A-Za-z0-9_-]/', '', $sede);
$archivo_json = "../../archivos/generados/reprocesos_v2/REPROCESOS_" . $sede_saneada . ".json";

if (!file_exists($archivo_json)) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'No hay pendientes registrados para esta sede.']);
    exit;
}

$fp = fopen($archivo_json, 'r+');
if (!$fp) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'No se pudo abrir el archivo JSON maestro.']);
    exit;
}

// Lock exclusivo: quien llegue primero cierra el pendiente; cualquier intento
// simultáneo posterior (mismo id_registro) encuentra estado != 'pendiente' y se rechaza.
flock($fp, LOCK_EX);

$contenido = stream_get_contents($fp);
$registros = $contenido !== '' ? (json_decode($contenido, true) ?: []) : [];

$cantidad_procesada = floatval($input_array['cantidad_procesada'] ?? 0);
$epsilon = 0.01;

$encontrado = false;
$ya_ejecutado = false;
$fuera_de_rango = null;
$nuevo_pendiente = null;

foreach ($registros as &$reg) {
    if (($reg['id_registro'] ?? '') === $id_registro) {
        $encontrado = true;
        if (($reg['datos']['estado'] ?? '') !== 'pendiente') {
            $ya_ejecutado = true;
            break;
        }

        $cantidad_original = floatval($reg['datos']['cantidad'] ?? 0);

        if ($cantidad_procesada <= 0) {
            $fuera_de_rango = 'La cantidad procesada debe ser mayor a 0.';
            break;
        }
        if ($cantidad_procesada > $cantidad_original + $epsilon) {
            $fuera_de_rango = 'No se puede procesar más de lo solicitado (' . $cantidad_original . ' KG).';
            break;
        }

        $reg['datos']['estado'] = 'completado';
        $reg['datos']['ejecucion'] = [
            'fecha'                => $input_array['fecha'] ?? '',
            'referencia'           => $input_array['referencia'] ?? '',
            'hora_inicio'          => $input_array['hora_inicio'] ?? '',
            'hora_fin'             => $input_array['hora_fin'] ?? '',
            'cantidad_procesada'   => $cantidad_procesada,
            'responsable_ejecucion'=> $input_array['responsable_ejecucion'] ?? '',
            'firma'                => $input_array['firma'] ?? '',
            'usuario_sys_ejecucion'=> $_SESSION['nombre'],
            'timestamp_ejecucion'  => date('Y-m-d H:i:s'),
        ];

        // Si quedó saldo, ese saldo se abre como un pendiente nuevo (no se reabre este).
        $restante = $cantidad_original - $cantidad_procesada;
        if ($restante > $epsilon) {
            $nuevo_pendiente = [
                'id_registro'  => uniqid('REPRO_'),
                'timestamp'    => date('Y-m-d H:i:s'),
                'usuario_sys'  => $_SESSION['nombre'],
                'sede_sys'     => $sede,
                'datos'        => [
                    'fecha_alistamiento'       => date('Y-m-d'),
                    'responsable_alistamiento' => $reg['datos']['responsable_alistamiento'] ?? '',
                    'producto'                 => $reg['datos']['producto'] ?? '',
                    'lote'                     => $reg['datos']['lote'] ?? '',
                    'hora'                     => date('H:i'),
                    'cantidad'                 => round($restante, 2),
                    'motivo'                   => $reg['datos']['motivo'] ?? '',
                    'proceso_sugerido'         => $reg['datos']['proceso_sugerido'] ?? '',
                    'estado'                   => 'pendiente',
                    'ejecucion'                => null,
                    'origen_id_registro'       => $id_registro,
                ],
            ];
            $reg['datos']['ejecucion']['saldo_pendiente'] = round($restante, 2);
            $reg['datos']['ejecucion']['id_pendiente_generado'] = $nuevo_pendiente['id_registro'];
        }
        break;
    }
}
unset($reg);

if (!$encontrado) {
    flock($fp, LOCK_UN);
    fclose($fp);
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'El pendiente no existe (id inválido).']);
    exit;
}

if ($ya_ejecutado) {
    flock($fp, LOCK_UN);
    fclose($fp);
    http_response_code(409);
    echo json_encode(['status' => 'error', 'message' => 'Este reproceso ya fue ejecutado por otro usuario.']);
    exit;
}

if ($fuera_de_rango) {
    flock($fp, LOCK_UN);
    fclose($fp);
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $fuera_de_rango]);
    exit;
}

if ($nuevo_pendiente) {
    $registros[] = $nuevo_pendiente;
}

ftruncate($fp, 0);
rewind($fp);
fwrite($fp, json_encode($registros, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
fflush($fp);
flock($fp, LOCK_UN);
fclose($fp);

echo json_encode([
    'status'  => 'success',
    'message' => $nuevo_pendiente
        ? 'Reproceso cerrado. Quedó un nuevo pendiente de ' . $nuevo_pendiente['datos']['cantidad'] . ' KG.'
        : 'Reproceso ejecutado y cerrado.',
    'id'              => $id_registro,
    'saldo_pendiente' => $nuevo_pendiente ? $nuevo_pendiente['datos']['cantidad'] : 0,
]);
?>
