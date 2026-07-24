<?php
include '../sesion.php';
header('Content-Type: application/json; charset=utf-8');

$sede = $_SESSION['sede'];

$input_json  = file_get_contents("php://input");
$input_array = json_decode($input_json, true);

$nuevo_registro = [
    'id_registro' => uniqid('PMEJ_'),
    'timestamp'   => date('Y-m-d H:i:s'),
    'usuario_sys' => $_SESSION['nombre'],
    'sede_sys'    => $sede,
    'datos'       => $input_array
];

$base_dir  = "../../archivos/generados/preparacion_mejorante/";
$sede_dir  = $base_dir . preg_replace('/[^A-Za-z0-9_-]/', '', $sede) . "/";
$archivo   = $sede_dir . "PMEJ_" . date('Y-m') . ".json";

if (!file_exists($base_dir)) { mkdir($base_dir, 0777, true); }
if (!file_exists($sede_dir)) { mkdir($sede_dir, 0777, true); }

$existentes = file_exists($archivo)
    ? (json_decode(file_get_contents($archivo), true) ?: [])
    : [];

$existentes[] = $nuevo_registro;
file_put_contents($archivo, json_encode($existentes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo json_encode(['status' => 'success', 'id' => $nuevo_registro['id_registro']]);
?>
