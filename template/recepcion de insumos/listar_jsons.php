<?php
require '../sesion.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['sede'])) {
    echo json_encode(['status' => 'error', 'archivos' => []]);
    exit;
}

$sede = preg_replace('/[^A-Za-z0-9_-]/', '', $_SESSION['sede']);
$dir  = "../../archivos/generados/recepcion_insumos/{$sede}/";

if (!is_dir($dir)) {
    echo json_encode(['status' => 'ok', 'archivos' => []]);
    exit;
}

$archivos  = glob($dir . "RECEPCION_*.json");
$resultado = [];

foreach ($archivos as $ruta) {
    $contenido = json_decode(file_get_contents($ruta), true) ?: [];
    foreach ($contenido as $reg) {
        $d = $reg['datos'] ?? [];

        $resultado[] = [
            'id'           => $reg['id_registro'] ?? '',
            'timestamp'    => $reg['timestamp'] ?? '',
            'usuario'      => $reg['usuario_sys'] ?? '',
            'fecha'        => $d['fecha'] ?? '',
            'proveedor'    => $d['proveedor'] ?? '',
            'orden_compra' => $d['orden_compra'] ?? '',
            'entrada_no'   => $d['entrada_no'] ?? '',
            'num_insumos'  => count($d['insumos'] ?? []),
            'archivo'      => $ruta
        ];
    }
}

usort($resultado, fn($a, $b) => strcmp($b['timestamp'], $a['timestamp']));
echo json_encode(['status' => 'ok', 'archivos' => $resultado]);
?>
