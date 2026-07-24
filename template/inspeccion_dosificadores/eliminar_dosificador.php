<?php
include '../sesion.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['nombre']) || empty($_SESSION['sede'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Sin sesión activa.']);
    exit;
}

$sede = $_SESSION['sede'];
$input = json_decode(file_get_contents('php://input'), true);
$id = trim($input['id'] ?? '');

if ($id === '') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Falta el identificador del dosificador.']);
    exit;
}

$sede_san = preg_replace('/[^A-Za-z0-9_-]/', '', $sede);
$catalogo_file = "../../archivos/generados/inspeccion_dosificadores/" . $sede_san . "/catalogo_dosificadores.json";

if (!file_exists($catalogo_file)) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'El catálogo no existe todavía.']);
    exit;
}

$dosificadores = json_decode(file_get_contents($catalogo_file), true) ?: [];
$restantes = array_values(array_filter($dosificadores, fn($d) => ($d['id'] ?? '') !== $id));

if (count($restantes) === count($dosificadores)) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Dosificador no encontrado.']);
    exit;
}

if (@file_put_contents($catalogo_file, json_encode($restantes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
    echo json_encode(['status' => 'success', 'message' => 'Dosificador eliminado del catálogo. El histórico de inspecciones ya guardadas no se ve afectado.']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'No se pudo actualizar el catálogo (permisos de disco).']);
}
?>
