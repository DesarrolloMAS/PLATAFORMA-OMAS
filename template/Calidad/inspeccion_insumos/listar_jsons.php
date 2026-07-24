<?php
include '../../sesion.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['sede'])) {
    echo json_encode(['status' => 'error', 'archivos' => []]);
    exit;
}

$sede = preg_replace('/[^A-Za-z0-9_-]/', '', $_SESSION['sede']);
$dir  = "../../../archivos/generados/inspeccion_insumos/{$sede}/";

if (!is_dir($dir)) {
    echo json_encode(['status' => 'ok', 'archivos' => []]);
    exit;
}

$archivos  = glob($dir . "INSUMOS_*.json");
$resultado = [];

foreach ($archivos as $ruta) {
    $contenido = json_decode(file_get_contents($ruta), true) ?: [];
    foreach ($contenido as $reg) {
        $d     = $reg['datos'] ?? [];
        $items = $d['insumos'] ?? [];

        $resultado[] = [
            'id'                => $reg['id_registro'] ?? '',
            'timestamp'         => $reg['timestamp'] ?? '',
            'usuario'           => $reg['usuario_sys'] ?? '',
            'fecha'             => $d['fecha_inspeccion'] ?? '',
            'planta'            => $d['planta'] ?? '',
            'inspeccionado_por' => $d['inspeccionado_por'] ?? '',
            'verificado_por'    => $d['verificado_por'] ?? '',
            'num_items'         => count($items),
            'promedio'          => isset($d['promedio_cumplimiento']) ? floatval($d['promedio_cumplimiento']) : null,
            'archivo'           => $ruta
        ];
    }
}

usort($resultado, fn($a, $b) => strcmp($b['timestamp'], $a['timestamp']));
echo json_encode(['status' => 'ok', 'archivos' => $resultado]);
?>
