<?php
include '../sesion.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['nombre']) || empty($_SESSION['sede'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Sin sesión activa o sede no asignada.']);
    exit;
}

$sede = $_SESSION['sede'];

$input_json  = file_get_contents("php://input");
$input_array = json_decode($input_json, true);

if (!$input_array || empty($input_array['fecha'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Faltan datos requeridos (fecha).']);
    exit;
}

if (empty($input_array['harinas_especiales']) && empty($input_array['insumos'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Registre al menos una harina especial o un insumo.']);
    exit;
}

// Firma manuscrita (canvas), recibida como imagen PNG codificada en base64 dentro del JSON.
$firma = $input_array['firma'] ?? '';
if (empty($firma) || !preg_match('/^data:image\/png;base64,/', $firma)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'La firma del turno es obligatoria.']);
    exit;
}

$nuevo_registro = [
    'id_registro' => uniqid('PRE_'),
    'timestamp'   => date('Y-m-d H:i:s'),
    'usuario_sys' => $_SESSION['nombre'],
    'sede_sys'    => $sede,
    'datos'       => $input_array
];

// El mes del archivo se calcula a partir de la fecha del registro (no de la
// fecha del servidor), igual que envasado_v2/procesar.php, para que un
// registro tardío de un mes anterior caiga en el archivo de ese mes.
$ts_fecha = strtotime($input_array['fecha']);
$mes = $ts_fecha !== false ? date('Y-m', $ts_fecha) : date('Y-m');

$base_dir     = "../../archivos/generados/premezclas_v2/";
$sede_dir     = $base_dir . preg_replace('/[^A-Za-z0-9_-]/', '', $sede) . "/";
$archivo_json = $sede_dir . "PREMEZCLA_" . $mes . ".json";

if (!file_exists($base_dir)) { mkdir($base_dir, 0777, true); }
if (!file_exists($sede_dir)) { mkdir($sede_dir, 0777, true); }

$datos_existentes = [];
if (file_exists($archivo_json)) {
    $contenido = file_get_contents($archivo_json);
    $datos_existentes = json_decode($contenido, true) ?: [];
}

$datos_existentes[] = $nuevo_registro;

if (@file_put_contents($archivo_json, json_encode($datos_existentes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
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
