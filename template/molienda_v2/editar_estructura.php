<?php
require '../conection.php';
require '../sesion.php';
verificarAutenticacion();

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$sede = $data['sede'] ?? '';
$fecha = $data['fecha'] ?? '';
$agregar = $data['agregar'] ?? [];
$eliminar = $data['eliminar'] ?? [];

if (!$sede || !$fecha) {
    echo json_encode(['success' => false, 'error' => 'Faltan parámetros (sede/fecha)']);
    exit;
}

if (empty($agregar) && empty($eliminar)) {
    echo json_encode(['success' => false, 'error' => 'No se recibieron cambios']);
    exit;
}

// Cargar archivo mensual
$mesFile = substr($fecha, 0, 7) . '.json';
$file_path = "../../archivos/generados/molienda/" . $sede . "/" . $mesFile;

if (!file_exists($file_path)) {
    echo json_encode(['success' => false, 'error' => 'No existen registros para este mes']);
    exit;
}

$records = json_decode(file_get_contents($file_path), true) ?: [];

// Cargar config de sede para obtener peso_unit de productos nuevos
$config_file = "../../archivos/generados/molienda/config_{$sede}.json";
$config = [];
if (file_exists($config_file)) {
    $config = json_decode(file_get_contents($config_file), true) ?: [];
}

// Construir diccionario de pesos desde config
$pesoDict = [];
foreach (['harinas', 'subproductos', 'materiales'] as $cat) {
    foreach (($config[$cat] ?? []) as $item) {
        $pesoDict[$item['id']] = $item['weight'] ?? 1;
    }
}

// Encontrar todos los turnos de la fecha dada
$turnosIndices = [];
foreach ($records as $index => $r) {
    if (isset($r['fecha']) && $r['fecha'] === $fecha) {
        $turnosIndices[] = $index;
    }
}

if (empty($turnosIndices)) {
    echo json_encode(['success' => false, 'error' => 'No se encontraron turnos para la fecha ' . $fecha]);
    exit;
}

$changed = false;

// AGREGAR productos: inyectar entrada con active='on' y escribir valores de bultos/lote por turno
foreach ($agregar as $item) {
    $id     = $item['id']     ?? '';
    $cat    = $item['cat']    ?? '';
    $valores = $item['valores'] ?? []; // {1:{bultos,lote}, 2:{...}, 3:{...}}
    if (!$id || !$cat) continue;
    if (!in_array($cat, ['harinas', 'subproductos', 'materiales'])) continue;

    $peso = $pesoDict[$id] ?? 1;

    foreach ($turnosIndices as $idx) {
        // Obtener el número de turno del registro para buscar sus valores
        $numTurno = $records[$idx]['turno'] ?? null;
        // Si no hay campo turno explícito, intentar deducirlo por posición (fallback)
        if ($numTurno === null) {
            $pos = array_search($idx, $turnosIndices);
            $numTurno = $pos !== false ? $pos + 1 : 1;
        }

        // Recuperar bultos y lote declarados para este turno
        $valTurno = $valores[strval($numTurno)] ?? $valores[$numTurno] ?? ['bultos' => 0, 'lote' => ''];
        $bultos   = intval($valTurno['bultos'] ?? 0);
        $lote     = trim($valTurno['lote']    ?? '');

        // Solo agregar si no existe o está desactivado
        if (!isset($records[$idx][$cat][$id]) || $records[$idx][$cat][$id]['active'] !== 'on') {
            $records[$idx][$cat][$id] = [
                'active'    => 'on',
                'peso_unit' => strval($peso),
                'lotes'     => [
                    ['valor' => $bultos, 'id' => $lote]
                ]
            ];
            $changed = true;
        }
    }

    // Activar materiales/insumos asociados con su lote por turno
    $matItems = $item['materiales'] ?? [];
    foreach ($matItems as $mat) {
        $matId    = $mat['id']    ?? '';
        $matLotes = $mat['lotes'] ?? []; // { "1":"lote", "2":"", "3":"" }
        if (!$matId) continue;

        $matPeso = $pesoDict[$matId] ?? 1;

        foreach ($turnosIndices as $idx) {
            $numTurno = $records[$idx]['turno'] ?? null;
            if ($numTurno === null) {
                $pos = array_search($idx, $turnosIndices);
                $numTurno = $pos !== false ? $pos + 1 : 1;
            }

            $matLote = trim($matLotes[strval($numTurno)] ?? $matLotes[$numTurno] ?? '');

            if (!isset($records[$idx]['materiales'][$matId]) || $records[$idx]['materiales'][$matId]['active'] !== 'on') {
                $records[$idx]['materiales'][$matId] = [
                    'active'    => 'on',
                    'peso_unit' => strval($matPeso),
                    'lotes'     => [['valor' => 1, 'id' => $matLote]]
                ];
                $changed = true;
            } elseif (!empty($matLote)) {
                // Si ya existe activo, sólo actualizar el lote
                $records[$idx]['materiales'][$matId]['lotes'] = [['valor' => 1, 'id' => $matLote]];
                $changed = true;
            }
        }
    }
}

// ELIMINAR productos: cambiar active a 'off' (no borrar datos)
foreach ($eliminar as $item) {
    $id = $item['id'] ?? '';
    $cat = $item['cat'] ?? '';
    if (!$id || !$cat) continue;
    if (!in_array($cat, ['harinas', 'subproductos', 'materiales'])) continue;

    foreach ($turnosIndices as $idx) {
        if (isset($records[$idx][$cat][$id]) && $records[$idx][$cat][$id]['active'] === 'on') {
            $records[$idx][$cat][$id]['active'] = 'off';
            $changed = true;
        }
    }
}

if ($changed) {
    file_put_contents($file_path, json_encode($records, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo json_encode(['success' => true, 'message' => 'Estructura actualizada correctamente']);
} else {
    echo json_encode(['success' => false, 'error' => 'No se realizaron cambios (productos ya existían o no se encontraron)']);
}
