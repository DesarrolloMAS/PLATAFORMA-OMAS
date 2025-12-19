<?php
require 'sesion.php';
verificarAutenticacion();

header('Content-Type: application/json');

// Verificar que el usuario actual sea administrador
$rolActual = $_SESSION['rol'] ?? '';
$rolesAdmin = ['adm', '1'];
$esAdmin = in_array($rolActual, $rolesAdmin);

if (!$esAdmin) {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado. Solo administradores pueden eliminar archivos.']);
    exit();
}

// Recibir datos JSON
$jsonData = file_get_contents('php://input');
$datos = json_decode($jsonData, true);

if ($datos && isset($datos['carpeta_usuario']) && isset($datos['archivo'])) {
    $carpetaUsuario = $datos['carpeta_usuario'];
    $nombreArchivo = $datos['archivo'];
    
    // Construir ruta del archivo
    $rutaArchivo = '/var/www/fmt/data/borradores/' . $carpetaUsuario . '/' . basename($nombreArchivo);
    
    if (file_exists($rutaArchivo)) {
        if (unlink($rutaArchivo)) {
            echo json_encode([
                'success' => true,
                'message' => 'Archivo eliminado correctamente',
                'archivo' => $nombreArchivo,
                'usuario' => str_replace('_', ' ', $carpetaUsuario)
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar el archivo']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Archivo no encontrado']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Datos insuficientes']);
}
?>