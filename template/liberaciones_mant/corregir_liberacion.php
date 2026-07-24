<?php
require '../sesion.php';
verificarAutenticacion();

header('Content-Type: application/json; charset=utf-8');

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || empty($input['archivo'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Falta el nombre del archivo.']);
    exit;
}

// Sanitizar nombre de archivo (solo letras, números, guiones y .json)
$nombreArchivo = basename($input['archivo']);
if (!preg_match('/^[\w\-]+\.json$/', $nombreArchivo)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Nombre de archivo inválido.']);
    exit;
}

$rutaBase = '/var/www/fmt/archivos/generados/liberaciones_mant/';
$rutaArchivo = $rutaBase . $nombreArchivo;

if (!file_exists($rutaArchivo)) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Archivo JSON no encontrado: ' . $nombreArchivo]);
    exit;
}

// Leer JSON actual
$datos = json_decode(file_get_contents($rutaArchivo), true);
if (!$datos) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error al leer el archivo JSON.']);
    exit;
}

// Aplicar correcciones (solo sobreescribir si viene con valor no vacío)
if (!empty($input['fecha'])) {
    $datos['fecha'] = $input['fecha'];
}
if (!empty($input['hora_inicio'])) {
    $datos['hora_inicio'] = $input['hora_inicio'];
}
if (!empty($input['hora_final'])) {
    $datos['hora_final'] = $input['hora_final'];
}

// Estado de liberación: puede ser 'si', 'no' o '' (vacío = sin definir)
if (isset($input['aprobada'])) {
    if (!isset($datos['liberacion'])) $datos['liberacion'] = [];
    if ($input['aprobada'] === 'si') {
        $datos['liberacion']['aprobada'] = 'si';
    } elseif ($input['aprobada'] === 'no') {
        $datos['liberacion']['aprobada'] = 'no';
    } else {
        $datos['liberacion']['aprobada'] = null;
    }
}

// Anotar metadato de corrección
$datos['_ultima_correccion'] = [
    'usuario' => $_SESSION['nombre'] ?? 'desconocido',
    'timestamp' => date('Y-m-d H:i:s')
];

// Guardar
if (file_put_contents($rutaArchivo, json_encode($datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
    echo json_encode(['success' => true, 'message' => 'Registro corregido correctamente.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'No se pudo escribir en el archivo JSON.']);
}
?>
