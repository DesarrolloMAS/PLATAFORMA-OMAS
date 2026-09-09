<?php
require '../sesion.php';
verificarAutenticacion();

header('Content-Type: application/json');

// Catálogo propio de "Control Familiar" (Zona Sur) — vive aparte del
// config_{sede}.json de molienda_v2 a propósito: son los mismos productos
// que antes estaban hardcodeados en geleria_productos.php, no el catálogo
// real de harinas de Molienda, y no deben mezclarse con ese archivo.
$config_dir  = "../../archivos/generados/cantidad_bulto";
$config_file = "$config_dir/config_familiar.json";

$default_productos = [
    'Harina de Trigo Nariño x10kg',
    'Harina de Trigo Nariño x10kg (5 Und)',
    'Harina de Trigo Nariño 2500g',
    'Harina de Trigo Nariño 1000g',
    'Harina de Trigo Nariño 500g',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (is_array($input)) {
        if (!is_dir($config_dir)) mkdir($config_dir, 0777, true);
        $productos = array_values(array_filter(array_map('trim', $input), fn($p) => $p !== ''));
        file_put_contents($config_file, json_encode($productos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        echo json_encode(['status' => 'ok', 'message' => 'Catálogo guardado correctamente.', 'data' => $productos]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Payload inválido']);
    }
} else {
    if (!file_exists($config_file)) {
        if (!is_dir($config_dir)) mkdir($config_dir, 0777, true);
        file_put_contents($config_file, json_encode($default_productos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        echo json_encode(['status' => 'ok', 'data' => $default_productos]);
    } else {
        $data = json_decode(file_get_contents($config_file), true) ?: [];
        echo json_encode(['status' => 'ok', 'data' => $data]);
    }
}
