<?php
include '../../sesion.php';
require_once __DIR__ . '/../flujo_helpers.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['nombre']) || empty($_SESSION['sede'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Sin sesión.']);
    exit;
}

$id_flujo = $_GET['id_flujo'] ?? '';
$paso     = $_GET['paso'] ?? '';
if (empty($id_flujo) || empty($paso)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Faltan parámetros.']);
    exit;
}

$sede_san = preg_replace('/[^A-Za-z0-9_-]/', '', $_SESSION['sede']);
$flujo    = obtenerFlujoPorId($sede_san, $id_flujo);

if ($flujo === null) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Flujo no encontrado.']);
    exit;
}

$registro = obtenerRegistroDelPaso($flujo, $paso, $sede_san);

if ($registro === null) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Ese paso aún no ha sido diligenciado.']);
    exit;
}

echo json_encode(['status' => 'success', 'registro' => $registro]);
