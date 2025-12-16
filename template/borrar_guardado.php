<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['nombre'])) {
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit();
}

$jsonData = file_get_contents('php://input');
$datos = json_decode($jsonData, true);

if (isset($datos['archivo'])) {
    $nombreUsuario = $_SESSION['nombre'];
    $nombreCarpeta = preg_replace('/[^a-zA-Z0-9_-]/', '_', $nombreUsuario);
    $rutaArchivo = '/var/www/fmt/data/borradores/' . $nombreCarpeta . '/' . basename($datos['archivo']);
    
    if (file_exists($rutaArchivo) && unlink($rutaArchivo)) {
        echo json_encode(['success' => true, 'message' => 'Borrador eliminado']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se pudo eliminar el archivo']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Archivo no especificado']);
}