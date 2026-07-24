<?php
require_once '../../conection.php';
require_once '../../sesion.php';
verificarAutenticacion();
header('Content-Type: application/json');

$sede = $_SESSION['sede'] ?? 'ZC';

$json = file_get_contents('php://input');
if (!$json) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'No se recibió JSON']);
    exit;
}

$data = json_decode($json, true);
if ($data === null) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'JSON inválido']);
    exit;
}

// Inyectar sede en el registro
$data['sede'] = $sede;

$carpeta = __DIR__ . '/../../../archivos/generados/Calidad/etiquetado/' . $sede . '/';
if (!is_dir($carpeta)) {
    mkdir($carpeta, 0777, true);
}

$fecha = date('Ymd_His');
$archivo = $carpeta . "etiquetado_$fecha.json";

file_put_contents($archivo, json_encode($data, JSON_PRETTY_PRINT));

echo json_encode(['status' => 'ok', 'archivo' => basename($archivo), 'sede' => $sede]);
?>
