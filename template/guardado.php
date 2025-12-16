<?php
// Iniciar sesión para acceder a los datos del usuario
session_start();

// Configurar zona horaria de Colombia
date_default_timezone_set('America/Bogota');

header('Content-Type: application/json');

if (!isset($_SESSION['nombre'])) {
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit();
}

// Recibir datos JSON
$jsonData = file_get_contents('php://input');
$datos = json_decode($jsonData, true);

if ($datos) {
    // Obtener el nombre del usuario desde la sesión
    $nombreUsuario = $_SESSION['nombre'];
    $maquina = isset($datos['objeto_dañado']) ? $datos['objeto_dañado'] : 'desconocida';
    // Limpiar el nombre para usarlo como nombre de carpeta (eliminar caracteres especiales)
    $nombreCarpeta = preg_replace('/[^a-zA-Z0-9_-]/', '_', $nombreUsuario);
    
    // Ruta con carpeta del usuario
    $dirUsuario = '/var/www/fmt/data/borradores/' . $nombreCarpeta;
    
    // Crear directorio del usuario si no existe
    if (!is_dir($dirUsuario)) {
        mkdir($dirUsuario, 0755, true);
    }
    
    // Ruta completa del archivo
    $rutaArchivo = $dirUsuario . '/Orden_de_mantenimiento_' . $maquina . '_' . date('h:i:s A') . '.json';
    
    // Guardar archivo
    if (file_put_contents($rutaArchivo, json_encode($datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
        echo json_encode([
            'success' => true, 
            'message' => 'Guardado correctamente', 
            'archivo' => $rutaArchivo,
            'usuario' => $nombreUsuario
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al guardar archivo']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
}