<?php
require '../sesion.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['nombre']) || empty($_SESSION['sede'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Sin sesión activa o sede no asignada.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$sede = $data['sede'] ?? '';
$target_file = $data['target_file'] ?? '';
$updates = $data['updates'] ?? [];

if (!$sede || !$target_file || empty($updates)) {
    echo json_encode(['success' => false, 'error' => 'Faltan parámetros']);
    exit;
}

$ruta_json = "../../archivos/generados/cantidad_bulto/"
           . preg_replace('/[^A-Za-z0-9_-]/', '', $sede) . "/"
           . basename($target_file);

if (!file_exists($ruta_json)) {
    echo json_encode(['success' => false, 'error' => 'El archivo no existe o fue eliminado.']);
    exit;
}

$registros = json_decode(file_get_contents($ruta_json), true) ?: [];

if (empty($registros)) {
    echo json_encode(['success' => false, 'error' => 'El archivo está vacío.']);
    exit;
}

$changed = false;

// Indexar registros por id_registro para búsqueda rápida
$index_map = [];
foreach ($registros as $idx => $reg) {
    if (isset($reg['id_registro'])) {
        $index_map[$reg['id_registro']] = $idx;
    }
}

foreach ($updates as $upd) {
    $id = $upd['id'] ?? '';
    $field = $upd['field'] ?? '';
    $val = $upd['val'] ?? '';

    if (!$id || !$field) continue;

    if (isset($index_map[$id])) {
        $real_index = $index_map[$id];
        
        // Si el campo es un bulto, lo convertimos a float o dejamos nulo si está vacío
        if (strpos($field, 'bulto_') === 0) {
            if ($val === '') {
                $val = null;
            } else {
                $val = str_replace(',', '.', $val);
                if (is_numeric($val)) {
                    $val = floatval($val);
                }
            }
        }

        $registros[$real_index]['datos'][$field] = $val;
        $changed = true;
    }
}

if ($changed) {
    if (@file_put_contents($ruta_json, json_encode($registros, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
        echo json_encode(['success' => true]);
    } else {
        $err = error_get_last();
        echo json_encode(['success' => false, 'error' => 'Error I/O: ' . ($err['message'] ?? 'Permisos insuficientes.')]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'No se encontraron cambios válidos']);
}
?>
