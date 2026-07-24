<?php
require '../sesion.php';
verificarAutenticacion();

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$sede = $_SESSION['sede'] ?? '';
$file = $data['file'] ?? '';
$updates = $data['updates'] ?? [];

if (!$sede || !$file) {
    echo json_encode(['success' => false, 'error' => 'Faltan parámetros']);
    exit;
}

$file_path = "../../archivos/generados/empaque_v2/" . preg_replace('/[^A-Za-z0-9_-]/', '', $sede) . "/" . basename($file);

if (!file_exists($file_path)) {
    echo json_encode(['success' => false, 'error' => 'El archivo no existe']);
    exit;
}

$records = json_decode(file_get_contents($file_path), true) ?: [];

if (empty($records)) {
    echo json_encode(['success' => false, 'error' => 'El archivo está vacío']);
    exit;
}

$changed = false;

// Los metadatos los guardamos en el primer registro para consistencia con el visor actual
foreach ($updates as $field => $value) {
    if ($field === 'total_unidades') {
        $records[0]['datos']['cantidad_galeria'] = is_numeric($value) ? (float)$value : $value;
        $changed = true;
    } else if ($field === 'proveedor') {
        $records[0]['datos']['proveedor'] = $value;
        $changed = true;
    } else if ($field === 'lote_proveedor') {
        $records[0]['datos']['lote_proveedor'] = $value;
        $changed = true;
    }
}

if ($changed) {
    if (file_put_contents($file_path, json_encode($records, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Error al guardar el archivo']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'No hay cambios para guardar']);
}
