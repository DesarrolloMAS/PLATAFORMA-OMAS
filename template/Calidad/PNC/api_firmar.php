<?php
require '../../sesion.php';
verificarAutenticacion();
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$file = $data['file'] ?? '';
$id = $data['id'] ?? '';
$sede = $_SESSION['sede'];
$nombre_usuario = $_SESSION['nombre'];

if (!$file || !$id) {
    echo json_encode(['status' => 'error', 'message' => 'Faltan parámetros.']);
    exit;
}

$file_path = "../../../archivos/generados/PNC/" . $sede . "/" . $file;
if (!file_exists($file_path)) {
    echo json_encode(['status' => 'error', 'message' => 'Archivo no encontrado.']);
    exit;
}

$json_data = json_decode(file_get_contents($file_path), true) ?: [];
$updated = false;

foreach ($json_data as &$r) {
    if ($r['id'] === $id) {
        $r['verifica_correccion'] = $nombre_usuario;
        $updated = true;
        break;
    }
}

if ($updated) {
    if (file_put_contents($file_path, json_encode($json_data, JSON_PRETTY_PRINT)) !== false) {
        echo json_encode(['status' => 'ok']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al escribir el JSON en el servidor.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'El registro (ID='.$id.') no se encontró.']);
}
?>
