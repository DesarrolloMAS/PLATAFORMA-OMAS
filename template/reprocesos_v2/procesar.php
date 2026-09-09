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

if (!$input_array || empty($input_array['producto']) || empty($input_array['lote']) || $input_array['cantidad'] === '') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Faltan datos requeridos (producto, lote, cantidad).']);
    exit;
}

$input_array['cantidad'] = floatval($input_array['cantidad']);

if ($input_array['cantidad'] <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'La cantidad a reprocesar debe ser mayor a 0.']);
    exit;
}
$input_array['estado'] = 'pendiente';
$input_array['ejecucion'] = null;

$nuevo_registro = [
    'id_registro'  => uniqid('REPRO_'),
    'timestamp'    => date('Y-m-d H:i:s'),
    'usuario_sys'  => $_SESSION['nombre'],
    'sede_sys'     => $sede,
    'datos'        => $input_array
];

// Ruta: archivos/generados/reprocesos_v2/REPROCESOS_[SEDE].json (un solo archivo corriente por sede,
// sin rotación mensual, porque un pendiente puede quedar abierto de un mes a otro).
$base_dir = "../../archivos/generados/reprocesos_v2/";
$sede_saneada = preg_replace('/[^A-Za-z0-9_-]/', '', $sede);
$archivo_json = $base_dir . "REPROCESOS_" . $sede_saneada . ".json";

if (!file_exists($base_dir)) { mkdir($base_dir, 0777, true); }

$fp = fopen($archivo_json, 'c+');
if (!$fp) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'No se pudo abrir el archivo JSON maestro.']);
    exit;
}

flock($fp, LOCK_EX);

$contenido = stream_get_contents($fp);
$datos_existentes = $contenido !== '' ? (json_decode($contenido, true) ?: []) : [];

$datos_existentes[] = $nuevo_registro;

ftruncate($fp, 0);
rewind($fp);
fwrite($fp, json_encode($datos_existentes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
fflush($fp);
flock($fp, LOCK_UN);
fclose($fp);

echo json_encode(['status' => 'success', 'message' => 'Pendiente de reproceso registrado.', 'id' => $nuevo_registro['id_registro']]);
?>
