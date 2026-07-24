<?php
ini_set('display_errors', 0);
require_once '../sesion.php';
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
    
    // Limpiar el nombre para usarlo como nombre de carpeta (eliminar caracteres especiales)
    $nombreCarpeta = preg_replace('/[^a-zA-Z0-9_-]/', '_', $nombreUsuario);
    
    // Ruta con carpeta del usuario en la sección de guardados de orden_mantenimiento
    $dirUsuario = __DIR__ . '/guardados/' . $nombreCarpeta;
    
    // Crear directorio del usuario si no existe
    if (!is_dir($dirUsuario)) {
        if (!mkdir($dirUsuario, 0777, true)) {
            echo json_encode(['success' => false, 'message' => 'Error al crear directorio de usuario']);
            exit();
        }
    }
    
    $maquina = isset($datos['objeto_dañado']) && !empty($datos['objeto_dañado']) ? preg_replace('/[^a-zA-Z0-9_-]/', '_', $datos['objeto_dañado']) : 'Sin_Nombre';
    
    // Ruta completa del archivo
    $rutaArchivo = $dirUsuario . '/Borrador_' . $maquina . '_' . date('Ymd_His') . '.json';
    
    // Guardar archivo
    if (file_put_contents($rutaArchivo, json_encode($datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
        echo json_encode([
            'success' => true, 
            'message' => 'Borrador guardado correctamente', 
            'archivo' => basename($rutaArchivo),
            'usuario' => $nombreUsuario
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al guardar archivo en el servidor. Verifique permisos.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos o vacíos']);
}
