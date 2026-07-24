<?php
require_once '../sesion.php';
verificarAutenticacion();

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$file        = $data['file']   ?? '';
$id          = $data['id']     ?? '';
$nuevo_numero = trim($data['numero'] ?? '');
$sede        = $_SESSION['sede'] ?? 'NA';

if (!$file || !$id || $nuevo_numero === '') {
    echo json_encode(['success' => false, 'error' => 'Parámetros incompletos']);
    exit;
}

// Validar que no contenga caracteres peligrosos en el nombre del archivo
if (!preg_match('/^\d{4}-\d{2}$/', $file)) {
    echo json_encode(['success' => false, 'error' => 'Nombre de archivo inválido']);
    exit;
}

$path = "../../archivos/generados/orden_mantenimiento/" . $sede . "/" . $file . ".json";

if (!file_exists($path)) {
    echo json_encode(['success' => false, 'error' => 'Archivo no encontrado']);
    exit;
}

$registros = json_decode(file_get_contents($path), true) ?: [];
$updated   = false;

foreach ($registros as &$reg) {
    if ($reg['id'] === $id) {
        $reg['datos']['numero_orden'] = $nuevo_numero;
        $updated = true;
        break;
    }
}
unset($reg);

if ($updated) {
    file_put_contents($path, json_encode($registros, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Registro no encontrado']);
}
