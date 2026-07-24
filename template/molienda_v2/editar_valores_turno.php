<?php
require '../conection.php';
require '../sesion.php';
verificarAutenticacion();

header('Content-Type: application/json');

$data   = json_decode(file_get_contents('php://input'), true);
$sede   = $data['sede']    ?? '';
$fecha  = $data['fecha']   ?? '';
$id     = $data['id']      ?? '';
$cat    = $data['cat']     ?? '';
$valores = $data['valores'] ?? []; // {"1":{bultos, lote}, "2":{...}, "3":{...}}

if (!$sede || !$fecha || !$id || !$cat) {
    echo json_encode(['success' => false, 'error' => 'Faltan parámetros (sede/fecha/id/cat)']);
    exit;
}

if (!in_array($cat, ['harinas', 'subproductos', 'materiales'])) {
    echo json_encode(['success' => false, 'error' => 'Categoría inválida']);
    exit;
}

$mesFile   = substr($fecha, 0, 7) . '.json';
$file_path = "../../archivos/generados/molienda/{$sede}/{$mesFile}";

if (!file_exists($file_path)) {
    echo json_encode(['success' => false, 'error' => 'No existen registros para este mes']);
    exit;
}

$records = json_decode(file_get_contents($file_path), true) ?: [];

// Construir mapa: número de turno → índice en el array
$turnosMap = [];
foreach ($records as $index => $r) {
    if (($r['fecha'] ?? '') !== $fecha) continue;
    $numTurno = $r['turno'] ?? null;
    if ($numTurno === null) {
        $pos = count($turnosMap);
        $numTurno = $pos + 1;
    }
    $turnosMap[intval($numTurno)] = $index;
}

if (empty($turnosMap)) {
    echo json_encode(['success' => false, 'error' => 'No se encontraron registros para esta fecha']);
    exit;
}

$changed = false;

foreach ($valores as $tNum => $lotesArr) {
    $tNum = intval($tNum);

    // Esperar un array; si viene como objeto antiguo ignorar
    if (!is_array($lotesArr)) continue;

    // Filtrar filas vacías
    $newLotes = [];
    foreach ($lotesArr as $l) {
        $bultos = floatval($l['bultos'] ?? 0);
        $lote   = trim($l['lote'] ?? '');
        if ($bultos > 0 || $lote !== '') {
            $newLotes[] = ['valor' => $bultos, 'id' => $lote];
        }
    }

    // Si todas las filas estaban vacías → no tocar ese turno
    if (empty($newLotes)) continue;

    $idx = $turnosMap[$tNum] ?? null;
    if ($idx === null) continue;

    if (!isset($records[$idx][$cat][$id])) {
        $records[$idx][$cat][$id] = [
            'active'    => 'on',
            'peso_unit' => '1',
            'lotes'     => $newLotes
        ];
    } else {
        // REEMPLAZAR todos los lotes del turno (nunca acumular)
        $records[$idx][$cat][$id]['active'] = 'on';
        $records[$idx][$cat][$id]['lotes']  = $newLotes;
    }
    $changed = true;
}

if ($changed) {
    file_put_contents($file_path, json_encode($records, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo json_encode(['success' => true, 'message' => 'Valores guardados correctamente']);
} else {
    echo json_encode(['success' => false, 'error' => 'No se detectaron valores para guardar. Asegúrate de ingresar bultos o número de lote.']);
}
