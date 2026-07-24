<?php
include '../../sesion.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['sede'])) {
    echo json_encode(['status' => 'error', 'archivos' => []]);
    exit;
}

$sede = preg_replace('/[^A-Za-z0-9_-]/', '', $_SESSION['sede']);
$dir  = "../../../archivos/generados/traza_molienda/{$sede}/";

if (!is_dir($dir)) {
    echo json_encode(['status' => 'ok', 'archivos' => []]);
    exit;
}

$archivos = glob($dir . "TRZMOL_*.json");
$resultado = [];
foreach ($archivos as $ruta) {
    $contenido = json_decode(file_get_contents($ruta), true) ?: [];
    foreach ($contenido as $reg) {
        $resultado[] = [
            'id'        => $reg['id_registro'] ?? '',
            'timestamp' => $reg['timestamp'] ?? '',
            'usuario'   => $reg['usuario_sys'] ?? '',
            'fecha'     => $reg['datos']['fecha'] ?? '',
            'dia'       => $reg['datos']['dia'] ?? '',
            'archivo'   => $ruta
        ];
    }
}

usort($resultado, fn($a, $b) => strcmp($b['timestamp'], $a['timestamp']));
echo json_encode(['status' => 'ok', 'archivos' => $resultado]);
?>
