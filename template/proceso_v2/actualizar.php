<?php
require '../sesion.php';
verificarAutenticacion();
header('Content-Type: application/json; charset=utf-8');

$sede = $_SESSION['sede'];

$input_json = file_get_contents("php://input");
$input_array = json_decode($input_json, true);

if (!$input_array || empty($input_array['file']) || empty($input_array['id_registro'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Faltan parámetros de identificación (file / id_registro).']);
    exit;
}

if (empty($input_array['fecha']) || empty($input_array['referencia_producto'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Faltan datos requeridos (fecha / referencia del producto).']);
    exit;
}

$file = $input_array['file'];
$id_registro = $input_array['id_registro'];

$file_path = "../../archivos/generados/proceso_v2/" . preg_replace('/[^A-Za-z0-9_-]/', '', $sede) . "/" . basename($file);
if (!file_exists($file_path)) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'El archivo de registros no existe.']);
    exit;
}

$records = json_decode(file_get_contents($file_path), true) ?: [];

$indice = null;
foreach ($records as $i => $r) {
    if (($r['id_registro'] ?? null) === $id_registro) {
        $indice = $i;
        break;
    }
}

if ($indice === null) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Registro no encontrado.']);
    exit;
}

$datos_nuevos = $input_array;
unset($datos_nuevos['file'], $datos_nuevos['id_registro']);

$records[$indice]['datos'] = $datos_nuevos;
$records[$indice]['ultima_edicion'] = [
    'usuario'   => $_SESSION['nombre'],
    'timestamp' => date('Y-m-d H:i:s')
];

if (@file_put_contents($file_path, json_encode($records, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
    echo json_encode(['status' => 'success', 'message' => 'Registro corregido correctamente.']);
} else {
    $err = error_get_last();
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error I/O: ' . ($err['message'] ?? 'No se pudo escribir el archivo JSON.')]);
}
