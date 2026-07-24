<?php
include '../sesion.php'; // Usa el sistema de control de accesos general

header('Content-Type: application/json; charset=utf-8');

// Comprobar si hay sesión y extraer sede
if (!isset($_SESSION['nombre']) || empty($_SESSION['sede'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Sin sesión activa o sede asignada. Recarga la página.']);
    exit;
}

$sede = $_SESSION['sede'];

// Leer cuerpo en JSON (ya que usamos fetch con application/json)
$input_json = file_get_contents("php://input");
$input_array = json_decode($input_json, true);

if (!$input_array || empty($input_array['referencia_empaque'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Faltan datos requeridos.']);
    exit;
}

// Configurar hora de registro y metadatos
$timestamp_actual = date('Y-m-d H:i:s');
$mes_actual = date('Y-m'); // Formato: YYYY-MM para rotación

// Crear array final estructurado
$nuevo_registro = [
    'id_registro'  => uniqid('INSEMP_'),
    'timestamp'    => $timestamp_actual,
    'usuario_sys'  => $_SESSION['nombre'],
    'sede_sys'     => $sede,
    'datos'        => $input_array
];

// Ruta de almacenamiento estructurado por SEDE y MES
$base_dir = "../../archivos/generados/inspeccion_empaque/";
$sede_dir = $base_dir . preg_replace('/[^A-Za-z0-9_-]/', '', $sede) . "/";
$archivo_json = $sede_dir . "INSPECCION_" . $mes_actual . ".json";

// Crear directorios si no existen
if (!file_exists($base_dir)) { mkdir($base_dir, 0777, true); }
if (!file_exists($sede_dir)) { mkdir($sede_dir, 0777, true); }

// Leer archivo actual si existe
$datos_existentes = [];
if (file_exists($archivo_json)) {
    $contenido_actual = file_get_contents($archivo_json);
    $datos_existentes = json_decode($contenido_actual, true) ?: [];
}

// Agregar nuevo registro a la colección
$datos_existentes[] = $nuevo_registro;

// Guardar
if (@file_put_contents($archivo_json, json_encode($datos_existentes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
    echo json_encode(['status' => 'success', 'message' => 'Inspección de empaque procesada en JSON correctamente.', 'id' => $nuevo_registro['id_registro']]);
} else {
    $err = error_get_last();
    $errMsg = $err ? $err['message'] : 'No se pudo escribir en el archivo JSON maestro (probablemente permisos).';
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error I/O: ' . $errMsg]);
}
?>
