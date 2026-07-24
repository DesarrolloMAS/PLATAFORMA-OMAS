<?php
$file = $_GET['file'] ?? '';
if (!$file || !preg_match('/^tara_.*\.json$/', $file)) {
    http_response_code(400);
    echo json_encode(['error' => 'Archivo inválido']);
    exit;
}

$path = __DIR__ . '/../../archivos/generados/Calidad/tara_seca/' . $file;
if (file_exists($path)) {
    header('Content-Type: application/json');
    echo file_get_contents($path);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Archivo no encontrado']);
}
?>
