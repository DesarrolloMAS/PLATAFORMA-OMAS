<?php
include '../../sesion.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['nombre']) || empty($_SESSION['sede'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Sin sesión activa.']);
    exit;
}

$sede     = $_SESSION['sede'];
$sede_san = preg_replace('/[^A-Za-z0-9_-]/', '', $sede);
$input    = json_decode(file_get_contents("php://input"), true);

if (!$input || empty($input['empresa']) || empty($input['responsable']) || empty($input['tipo_trabajo']) || empty($input['area'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Campos obligatorios incompletos.']);
    exit;
}

$base_dir     = "../../../archivos/generados/flujo_permisos/";
$sede_dir     = $base_dir . $sede_san . "/";
$mes_actual   = date('Y-m');
$archivo_json = $sede_dir . "FLUJO_" . $mes_actual . ".json";

if (!file_exists($base_dir)) @mkdir($base_dir, 0777, true);
if (!file_exists($sede_dir)) @mkdir($sede_dir, 0777, true);

$datos = file_exists($archivo_json)
    ? (json_decode(file_get_contents($archivo_json), true) ?: [])
    : [];

// Folio secuencial dentro del mes/sede
$consecutivo = count($datos) + 1;
$folio = "PT-" . date('Y') . "-" . date('m') . "-" . str_pad($consecutivo, 3, '0', STR_PAD_LEFT) . "-" . strtoupper($sede_san);

$nuevo = [
    'id_flujo'     => uniqid('FLUJO_'),
    'folio'        => $folio,
    'timestamp'    => date('Y-m-d H:i:s'),
    'usuario_sys'  => $_SESSION['nombre'],
    'sede_sys'     => $sede,
    'empresa'      => trim($input['empresa']),
    'responsable'  => trim($input['responsable']),
    'tipo_trabajo' => trim($input['tipo_trabajo']),
    'area'         => trim($input['area']),
    'fecha_inicio' => $input['fecha_inicio'] ?? date('Y-m-d'),
    'pasos' => [
        'permiso'    => ['completado' => false, 'timestamp' => null],
        'analisis'   => ['completado' => false, 'timestamp' => null],
        'inspeccion' => ['completado' => false, 'timestamp' => null],
    ],
    'estado' => 'en_progreso',
];

$datos[] = $nuevo;

if (@file_put_contents($archivo_json, json_encode($datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX)) {
    echo json_encode(['status' => 'success', 'id_flujo' => $nuevo['id_flujo'], 'folio' => $folio]);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error de escritura en disco.']);
}
?>
