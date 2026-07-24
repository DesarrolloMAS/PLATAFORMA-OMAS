<?php
require '../conection.php';
require '../sesion.php';
verificarAutenticacion();

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$sede = $data['sede'] ?? '';
$fecha = $data['fecha'] ?? '';
$sapData = $data['sap'] ?? [];

if (!$sede || !$fecha) {
    echo json_encode(['success' => false, 'error' => 'Faltan parámetros']);
    exit;
}

$mesFile = substr($fecha, 0, 7) . '.json';
$file_path = "../../archivos/generados/molienda/" . $sede . "/" . $mesFile;

if (!file_exists($file_path)) {
    echo json_encode(['success' => false, 'error' => 'No existen registros para este mes']);
    exit;
}

$records = json_decode(file_get_contents($file_path), true) ?: [];
$found = false;

// Debe apuntar al MISMO registro que plantilla_diaria.php resuelve como $turno1:
// si hay duplicados de turno para la fecha, esa plantilla se queda con el ÚLTIMO
// registro con turno == 1 (ver plantilla_diaria.php líneas 73-79). Si guardamos en
// otro registro (p.ej. el primero por fecha), el número queda guardado pero nunca
// se ve reflejado en pantalla.
$targetIdx = null;
$fallbackIdx = null;
foreach ($records as $idx => $r) {
    if ($r['fecha'] === $fecha) {
        if ($fallbackIdx === null) $fallbackIdx = $idx;
        if (($r['turno'] ?? null) == 1) $targetIdx = $idx; // se queda con el último
    }
}
if ($targetIdx === null) $targetIdx = $fallbackIdx;

if ($targetIdx !== null) {
    $records[$targetIdx]['sap_diario'] = $sapData;
    $found = true;
}

if ($found) {
    file_put_contents($file_path, json_encode($records, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'No hay turnos registrados en esta fecha']);
}
