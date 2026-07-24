<?php
// Carpeta de almacenamiento para Tara Seca
$carpeta = __DIR__ . '/../../archivos/generados/Calidad/tara_seca/';
if (!is_dir($carpeta)) {
    mkdir($carpeta, 0777, true);
}

// Recibir el JSON
$json = file_get_contents('php://input');
if (!$json) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'No se recibió JSON']);
    exit;
}

// Decodifica para validar
$data = json_decode($json, true);
if ($data === null) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'JSON inválido']);
    exit;
}

// Genera nombre de archivo basado en lote y fecha
$lote = isset($data['lote']) ? preg_replace('/[^A-Za-z0-9_\-]/', '', $data['lote']) : 'sin-lote';
$fecha = date('Ymd_His');
$nombreArchivo = "tara_{$lote}_{$fecha}.json";
$rutaArchivo = $carpeta . $nombreArchivo;

// Guarda el archivo
if (file_put_contents($rutaArchivo, $json)) {
    echo json_encode(['status' => 'ok', 'archivo' => $nombreArchivo]);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error al escribir el archivo']);
}
?>
