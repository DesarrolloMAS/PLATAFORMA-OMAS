<?php
include '../../../sesion.php';
require_once __DIR__ . '/../../flujo_helpers.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['nombre']) || empty($_SESSION['sede'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Sin sesión activa o sede no asignada.']);
    exit;
}

$sede = $_SESSION['sede'];

$input_json  = file_get_contents("php://input");
$input_array = json_decode($input_json, true);

if (!$input_array) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Datos inválidos o faltantes.']);
    exit;
}

$id_flujo = $input_array['id_flujo'] ?? '';
if (empty($id_flujo)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Este formulario debe diligenciarse dentro de un flujo de Permiso de Trabajo.']);
    exit;
}

$sede_san_flujo = preg_replace('/[^A-Za-z0-9_-]/', '', $sede);
$flujo = obtenerFlujoPorId($sede_san_flujo, $id_flujo);

if ($flujo === null) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Flujo no encontrado.']);
    exit;
}

if (!flujoPasosCompletos($flujo)) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Debes completar Permiso, Análisis de Trabajo Seguro e Inspección antes de diligenciar este certificado.']);
    exit;
}

$nuevo_registro = [
    'id_registro' => uniqid('CAL_'),
    'timestamp'   => date('Y-m-d H:i:s'),
    'usuario_sys' => $_SESSION['nombre'],
    'sede_sys'    => $sede,
    'id_flujo'    => $id_flujo,
    'datos'       => $input_array
];

$base_dir     = "../../../../archivos/generados/cert_apoyo_caliente/";
$sede_dir     = $base_dir . preg_replace('/[^A-Za-z0-9_-]/', '', $sede) . "/";
$archivo_json = $sede_dir . "CAL_" . date('Y-m') . ".json";

if (!file_exists($base_dir)) { @mkdir($base_dir, 0777, true); }
if (!file_exists($sede_dir)) { @mkdir($sede_dir, 0777, true); }

$datos_existentes = file_exists($archivo_json)
    ? (json_decode(file_get_contents($archivo_json), true) ?: [])
    : [];

$datos_existentes[] = $nuevo_registro;

if (@file_put_contents($archivo_json, json_encode($datos_existentes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX)) {
    echo json_encode([
        'status'  => 'success',
        'message' => 'Registro guardado correctamente.',
        'id'      => $nuevo_registro['id_registro']
    ]);
} else {
    $err = error_get_last();
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Error I/O: ' . ($err['message'] ?? 'Permisos insuficientes.')
    ]);
}
?>
