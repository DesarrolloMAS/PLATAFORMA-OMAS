<?php
// Ruta donde guardar los archivos
$dir = '/var/www/fmt/archivos/generados/liberaciones_mant/';
if (!is_dir($dir)) mkdir($dir, 0777, true);

// Recibe el JSON
$data = file_get_contents('php://input');
if (!$data) {
    http_response_code(400);
    echo json_encode(['ok'=>false, 'msg'=>'No data received']);
    exit;
}

$json = json_decode($data, true);
if (!$json) {
    http_response_code(400);
    echo json_encode(['ok'=>false, 'msg'=>'Invalid JSON']);
    exit;
}
$zona = isset($json['zona']) ? strtolower(preg_replace('/\s+/', '-', $json['zona'])) : 'sin-zona';

// Nombre de archivo: registro-liberacion-empaque-YYYY-MM-DD-HHMMSS.json
$date = isset($json['fecha']) ? $json['fecha'] : date('Y-m-d');
$time = date('His');
$filename = "registro-liberacion-{$zona}-{$date}-{$time}.json";
file_put_contents($dir . $filename, json_encode($json, JSON_PRETTY_PRINT));

echo json_encode(['ok'=>true, 'msg'=>'Guardado en servidor', 'file'=>$filename]);