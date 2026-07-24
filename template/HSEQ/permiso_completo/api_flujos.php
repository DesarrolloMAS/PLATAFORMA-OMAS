<?php
include '../../sesion.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['nombre']) || empty($_SESSION['sede'])) {
    http_response_code(401);
    echo json_encode([]);
    exit;
}

$sede_san = preg_replace('/[^A-Za-z0-9_-]/', '', $_SESSION['sede']);
$sede_dir = "../../../archivos/generados/flujo_permisos/" . $sede_san . "/";

$flujos = [];
if (file_exists($sede_dir)) {
    foreach (scandir($sede_dir) as $file) {
        if (strtolower(pathinfo($file, PATHINFO_EXTENSION)) !== 'json') continue;
        $contenido = json_decode(file_get_contents($sede_dir . $file), true) ?: [];
        $flujos    = array_merge($flujos, $contenido);
    }
}

usort($flujos, fn($a, $b) => strcmp($b['timestamp'], $a['timestamp']));

echo json_encode(array_slice($flujos, 0, 30));
?>
