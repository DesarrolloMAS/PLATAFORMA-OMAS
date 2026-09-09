<?php
include '../sesion.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['nombre']) || empty($_SESSION['sede'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Sin sesión activa.']);
    exit;
}

$sede = $_SESSION['sede'];
$sede_saneada = preg_replace('/[^A-Za-z0-9_-]/', '', $sede);
$target_dir = "../../archivos/generados/inspeccion_dosificadores/" . $sede_saneada . "/";

$input = json_decode(file_get_contents('php://input'), true);
$file = basename($input['file'] ?? '');

if ($file === '' || !preg_match('/^INSPECCION_.*\.json$/i', $file)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Archivo inválido.']);
    exit;
}

$ruta = $target_dir . $file;

if (!file_exists($ruta)) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'El registro no existe o ya fue eliminado.']);
    exit;
}

if (unlink($ruta)) {
    echo json_encode(['status' => 'success', 'message' => 'Registro eliminado correctamente.']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'No se pudo eliminar el archivo (permisos de disco).']);
}
?>
