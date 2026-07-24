<?php
require '../../conection.php';
require '../../sesion.php';
verificarAutenticacion();
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$file = basename($data['file'] ?? '');
$zona = $data['zona'] ?? '';
$nombre_usuario = $_SESSION['nombre'] ?? 'Usuario Desconocido';

$zonas_validas = ['ZC', 'ZS'];
if (!$file || pathinfo($file, PATHINFO_EXTENSION) !== 'json') {
    echo json_encode(['status' => 'error', 'message' => 'Archivo no válido.']);
    exit;
}
if (!in_array($zona, $zonas_validas)) {
    echo json_encode(['status' => 'error', 'message' => 'Zona inválida.']);
    exit;
}

$file_path = "../../../archivos/generados/Calidad/etiquetado/" . $zona . "/" . $file;
if (!file_exists($file_path)) {
    echo json_encode(['status' => 'error', 'message' => 'Archivo no encontrado.']);
    exit;
}

$json_data = json_decode(file_get_contents($file_path), true) ?: [];
$json_data['firma_jefe'] = $nombre_usuario;

if (file_put_contents($file_path, json_encode($json_data, JSON_PRETTY_PRINT)) !== false) {
    echo json_encode(['status' => 'ok', 'firma' => $nombre_usuario]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Error al escribir el JSON en el servidor.']);
}
?>
