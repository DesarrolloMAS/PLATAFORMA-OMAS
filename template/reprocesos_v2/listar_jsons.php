<?php
require '../sesion.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['nombre']) || empty($_SESSION['sede'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Sin sesión activa.']);
    exit;
}

$sede = $_SESSION['sede'];
$sede_saneada = preg_replace('/[^A-Za-z0-9_-]/', '', $sede);
$archivo_json = "../../archivos/generados/reprocesos_v2/REPROCESOS_" . $sede_saneada . ".json";

$registros = [];
if (file_exists($archivo_json)) {
    $registros = json_decode(file_get_contents($archivo_json), true) ?: [];
}

usort($registros, fn($a, $b) => strtotime($b['timestamp'] ?? '') <=> strtotime($a['timestamp'] ?? ''));

echo json_encode(['status' => 'success', 'sede' => $sede, 'archivo' => "REPROCESOS_$sede_saneada.json", 'registros' => $registros]);
?>
