<?php
require '../../sesion.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['nombre']) || empty($_SESSION['sede'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Sin sesión.']);
    exit;
}

$sede  = $_SESSION['sede'];
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || empty($input['file']) || empty($input['id_registro']) || !isset($input['datos']) || !is_array($input['datos'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Datos incompletos.']);
    exit;
}

$ruta_json = "../../../archivos/generados/permiso_trabajo/" . preg_replace('/[^A-Za-z0-9_-]/', '', $sede) . "/" . basename($input['file']);

if (!file_exists($ruta_json)) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Archivo no encontrado.']);
    exit;
}

$registros  = json_decode(file_get_contents($ruta_json), true) ?: [];
$actualizado = false;

foreach ($registros as &$reg) {
    if (($reg['id_registro'] ?? '') !== $input['id_registro']) continue;
    $reg['datos']          = array_merge($reg['datos'] ?? [], $input['datos']);
    $reg['modificado_ts']  = date('Y-m-d H:i:s');
    $reg['modificado_por'] = $_SESSION['nombre'];
    $actualizado = true;
    break;
}
unset($reg);

if (!$actualizado) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Registro no encontrado.']);
    exit;
}

if (file_put_contents($ruta_json, json_encode($registros, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX)) {
    echo json_encode(['status' => 'success']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error de escritura en disco.']);
}
