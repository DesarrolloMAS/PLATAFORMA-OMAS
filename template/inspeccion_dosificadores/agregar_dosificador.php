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
$nombre = trim($input['nombre'] ?? '');

if ($nombre === '') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'El nombre del dosificador es obligatorio.']);
    exit;
}

$sede_san = preg_replace('/[^A-Za-z0-9_-]/', '', $sede);
$base_dir = "../../archivos/generados/inspeccion_dosificadores/";
$sede_dir = $base_dir . $sede_san . "/";
$catalogo_file = $sede_dir . "catalogo_dosificadores.json";

if (!file_exists($base_dir)) { mkdir($base_dir, 0777, true); }
if (!file_exists($sede_dir)) { mkdir($sede_dir, 0777, true); }

$dosificadores = file_exists($catalogo_file)
    ? (json_decode(file_get_contents($catalogo_file), true) ?: [])
    : [];

// Evitar duplicados exactos (sin distinguir mayúsculas/minúsculas)
foreach ($dosificadores as $d) {
    if (strcasecmp(trim($d['nombre'] ?? ''), $nombre) === 0) {
        http_response_code(409);
        echo json_encode(['status' => 'error', 'message' => 'Ya existe un dosificador con ese nombre.']);
        exit;
    }
}

$nuevo = [
    'id'             => uniqid('DOS_'),
    'nombre'         => $nombre,
    'fecha_creacion' => date('Y-m-d H:i:s'),
    'usuario_creador' => $_SESSION['nombre'],
];

$dosificadores[] = $nuevo;

if (@file_put_contents($catalogo_file, json_encode($dosificadores, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
    echo json_encode(['status' => 'success', 'dosificador' => $nuevo]);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'No se pudo guardar el catálogo (permisos de disco).']);
}
?>
