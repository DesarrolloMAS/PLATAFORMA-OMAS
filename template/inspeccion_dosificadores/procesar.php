<?php
include '../sesion.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['nombre']) || empty($_SESSION['sede'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Sin sesión activa o sede asignada. Recarga la página.']);
    exit;
}

$sede = $_SESSION['sede'];

$input_json  = file_get_contents("php://input");
$input_array = json_decode($input_json, true);

if (!$input_array || empty($input_array['dosificador']) || empty($input_array['microingrediente'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Faltan datos requeridos.']);
    exit;
}

$timestamp_actual = date('Y-m-d H:i:s');
$id_registro = uniqid('DOSIF_');

$nuevo_registro = [
    'id_registro'  => $id_registro,
    'timestamp'    => $timestamp_actual,
    'usuario_sys'  => $_SESSION['nombre'],
    'sede_sys'     => $sede,
    'datos'        => $input_array
];

// Cada inspección se guarda en su propio archivo — nunca se fusiona con otras
// inspecciones, ni por fecha (mes) ni por dosificador.
// Ruta: archivos/generados/inspeccion_dosificadores/[sede]/INSPECCION_[dosificador]_[fecha-hora]_[sufijo].json
$base_dir = "../../archivos/generados/inspeccion_dosificadores/";
$sede_dir = $base_dir . preg_replace('/[^A-Za-z0-9_-]/', '', $sede) . "/";

if (!file_exists($base_dir)) { mkdir($base_dir, 0777, true); }
if (!file_exists($sede_dir)) { mkdir($sede_dir, 0777, true); }

$dosificador_slug = preg_replace('/[^A-Za-z0-9]+/', '_', trim($input_array['dosificador']));
$dosificador_slug = trim($dosificador_slug, '_');
if ($dosificador_slug === '') { $dosificador_slug = 'SIN_NOMBRE'; }

$sufijo = substr($id_registro, -8);
$nombre_archivo = "INSPECCION_{$dosificador_slug}_" . date('Ymd_His') . "_{$sufijo}.json";
$archivo_json = $sede_dir . $nombre_archivo;

if (@file_put_contents($archivo_json, json_encode($nuevo_registro, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
    echo json_encode(['status' => 'success', 'message' => 'Inspección de dosificador procesada en JSON correctamente.', 'id' => $id_registro]);
} else {
    $err = error_get_last();
    $errMsg = $err ? $err['message'] : 'No se pudo escribir en el archivo JSON (probablemente permisos).';
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error I/O: ' . $errMsg]);
}
?>
