<?php
require_once __DIR__ . '/../sesion.php';
header('Content-Type: application/json');

// Recibir el cuerpo de la solicitud JSON
$json_data = file_get_contents('php://input');
error_log("Datos recibidos en procesar.php: " . $json_data);
$data = json_decode($json_data, true);

if (!$data) {
    error_log("Error: Datos JSON inválidos o vacíos.");
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit;
}

// Obtener la zona y ubicacion
$zona = isset($_SESSION['sede']) ? $_SESSION['sede'] : 'General';
$ubicacion = isset($data['ubicacion_id']) && !empty($data['ubicacion_id']) ? preg_replace('/[^a-zA-Z0-9_]/', '', $data['ubicacion_id']) : 'General';

// Configuración de rutas
$folder_name = 'termohigrometros';
// Definir la ruta de almacenamiento con la zona y la ubicacion
$base_dir = "../../archivos/generados/" . $folder_name . "/" . $zona . "/" . $ubicacion;

// Crear el directorio si no existe
if (!is_dir($base_dir)) {
    mkdir($base_dir, 0777, true);
}

// Generar nombre de archivo basado en el año y mes (Regla de rotación mensual)
$filename = date('Y-m') . '.json';
$filepath = $base_dir . '/' . $filename;

// Agregar marca de tiempo de recepción
$data['timestamp_recepcion'] = date('Y-m-d H:i:s');

// Leer datos existentes o crear un array vacío
$current_data = [];
if (file_exists($filepath)) {
    $current_content = file_get_contents($filepath);
    $current_data = json_decode($current_content, true) ?: [];
}

// Añadir el nuevo registro
$current_data[] = $data;

// Guardar el archivo
if (file_put_contents($filepath, json_encode($current_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
    echo json_encode(['success' => true, 'message' => 'Datos guardados en ' . $filename]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al escribir el archivo']);
}
?>
