<?php
require '../sesion.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['nombre']) || empty($_SESSION['sede']) || empty($_SESSION['area'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Sin sesión activa.']);
    exit;
}

$sede_saneada = preg_replace('/[^A-Za-z0-9_-]/', '', $_SESSION['sede']);
$area_saneada = preg_replace('/[^A-Za-z0-9_-]/', '', $_SESSION['area']);

$mes = $_GET['mes'] ?? date('Y-m');
if (!preg_match('/^\d{4}-\d{2}$/', $mes)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Formato de mes inválido.']);
    exit;
}

$archivo_json = "../../archivos/generados/cronograma_mantenimiento/" . $area_saneada . "/" . $sede_saneada . "/" . $mes . ".json";

$tareas = file_exists($archivo_json)
    ? (json_decode(file_get_contents($archivo_json), true) ?: [])
    : [];

echo json_encode(['status' => 'success', 'mes' => $mes, 'tareas' => $tareas]);
