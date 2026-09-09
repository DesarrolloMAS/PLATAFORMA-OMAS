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

$anio = $_GET['anio'] ?? date('Y');
if (!preg_match('/^\d{4}$/', $anio)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Año inválido.']);
    exit;
}

$base_dir = "../../archivos/generados/cronograma_mantenimiento/" . $area_saneada . "/" . $sede_saneada . "/";

$meses = [];
for ($m = 1; $m <= 12; $m++) {
    $mes_key = str_pad($m, 2, '0', STR_PAD_LEFT);
    $archivo = $base_dir . $anio . "-" . $mes_key . ".json";
    $meses[$mes_key] = file_exists($archivo)
        ? (json_decode(file_get_contents($archivo), true) ?: [])
        : [];
}

echo json_encode(['status' => 'success', 'anio' => $anio, 'meses' => $meses]);
