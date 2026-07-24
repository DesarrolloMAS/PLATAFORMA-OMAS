<?php
$zonas_validas = ['ZC', 'ZS'];
$zona = $_GET['zona'] ?? '';
if (!in_array($zona, $zonas_validas)) {
    http_response_code(400);
    echo json_encode(['error' => 'Zona inválida']);
    exit;
}

$dir = __DIR__ . '/../../../archivos/generados/Calidad/etiquetado/' . $zona . '/';
$file = basename($_GET['file'] ?? '');
$path = $dir . $file;

if ($file && is_file($path) && pathinfo($file, PATHINFO_EXTENSION) === 'json') {
    header('Content-Type: application/json');
    readfile($path);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Archivo no encontrado']);
}
?>
