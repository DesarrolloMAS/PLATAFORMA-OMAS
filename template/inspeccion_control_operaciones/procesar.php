<?php
include '../sesion.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['nombre']) || empty($_SESSION['sede'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Sin sesión activa o sede asignada. Recarga la página.']);
    exit;
}

$sede = $_SESSION['sede'];

$input_json = file_get_contents("php://input");
$input_array = json_decode($input_json, true);

if (!$input_array || empty($input_array['linea_produccion'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Faltan datos requeridos.']);
    exit;
}

$timestamp_actual = date('Y-m-d H:i:s');
$mes_actual       = date('Y-m');

$nuevo_registro = [
    'id_registro' => uniqid('INSCOP_'),
    'timestamp'   => $timestamp_actual,
    'usuario_sys' => $_SESSION['nombre'],
    'sede_sys'    => $sede,
    'datos'       => $input_array
];

$base_dir    = "../../archivos/generados/inspeccion_control_operaciones/";
$sede_dir    = $base_dir . preg_replace('/[^A-Za-z0-9_-]/', '', $sede) . "/";
$archivo_json = $sede_dir . "INSCOP_" . $mes_actual . ".json";

if (!file_exists($base_dir)) { mkdir($base_dir, 0777, true); }
if (!file_exists($sede_dir)) { mkdir($sede_dir, 0777, true); }

$datos_existentes = [];
if (file_exists($archivo_json)) {
    $contenido_actual = file_get_contents($archivo_json);
    $datos_existentes = json_decode($contenido_actual, true) ?: [];
}

$datos_existentes[] = $nuevo_registro;

if (@file_put_contents($archivo_json, json_encode($datos_existentes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
    echo json_encode([
        'status'  => 'success',
        'message' => 'Inspección Control de Operaciones procesada correctamente.',
        'id'      => $nuevo_registro['id_registro']
    ]);
} else {
    $err    = error_get_last();
    $errMsg = $err ? $err['message'] : 'No se pudo escribir en el archivo JSON (probablemente permisos).';
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error I/O: ' . $errMsg]);
}
?>
