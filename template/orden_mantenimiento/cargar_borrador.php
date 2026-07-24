<?php
require_once '../sesion.php';
verificarAutenticacion();
header('Content-Type: application/json');

$jsonData = file_get_contents('php://input');
$datos = json_decode($jsonData, true);

if (isset($datos['archivo'])) {
    $nombreUsuario = $_SESSION['nombre'];
    $nombreCarpeta = preg_replace('/[^a-zA-Z0-9_-]/', '_', $nombreUsuario);
    $rutaArchivo = __DIR__ . '/guardados/' . $nombreCarpeta . '/' . basename($datos['archivo']);
    
    if (file_exists($rutaArchivo)) {
        $contenido = file_get_contents($rutaArchivo);
        echo $contenido;
    } else {
        echo json_encode(['success' => false, 'message' => 'Archivo no encontrado en ' . $rutaArchivo]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Archivo no especificado']);
}
