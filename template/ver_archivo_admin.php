<?php
require 'sesion.php';
verificarAutenticacion();

// Configurar zona horaria de Colombia
date_default_timezone_set('America/Bogota');

header('Content-Type: application/json');

// Verificar que el usuario actual sea administrador
$rolActual = $_SESSION['rol'] ?? '';
$rolesAdmin = ['adm', '1'];
$esAdmin = in_array($rolActual, $rolesAdmin);

if (!$esAdmin) {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado. Solo administradores pueden ver archivos.']);
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
        $contenido = file_get_contents($rutaArchivo);
        $datosArchivo = json_decode($contenido, true);
        
        if ($datosArchivo) {
            echo json_encode([
                'success' => true,
                'contenido' => $datosArchivo,
                'archivo' => $nombreArchivo,
                'usuario' => str_replace('_', ' ', $carpetaUsuario)
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Archivo JSON inválido']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Archivo no encontrado']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Datos insuficientes']);
}
?>