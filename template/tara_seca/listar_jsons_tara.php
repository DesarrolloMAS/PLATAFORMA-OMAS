<?php
$dir = __DIR__ . '/../../archivos/generados/Calidad/tara_seca/';
$files = [];
if (is_dir($dir)) {
    foreach (scandir($dir) as $f) {
        if (pathinfo($f, PATHINFO_EXTENSION) === 'json') {
            $files[] = $f;
        }
    }
    // Ordenar por fecha (descendente si es posible) - basándonos en el nombre que tiene timestamp
    rsort($files);
}
header('Content-Type: application/json');
echo json_encode($files);
?>
