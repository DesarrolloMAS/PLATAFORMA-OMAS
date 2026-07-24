<?php
include '../sesion.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['nombre']) || empty($_SESSION['sede'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Sin sesión activa.']);
    exit;
}

$sede = $_SESSION['sede'];
$sede_san = preg_replace('/[^A-Za-z0-9_-]/', '', $sede);
$catalogo_file = "../../archivos/generados/inspeccion_dosificadores/" . $sede_san . "/catalogo_dosificadores.json";

$dosificadores = file_exists($catalogo_file)
    ? (json_decode(file_get_contents($catalogo_file), true) ?: [])
    : [];

usort($dosificadores, fn($a, $b) => strcasecmp($a['nombre'] ?? '', $b['nombre'] ?? ''));

echo json_encode(['status' => 'success', 'sede' => $sede, 'dosificadores' => $dosificadores]);
?>
