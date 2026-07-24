<?php
$zonas_validas = ['ZC', 'ZS'];
$zona = $_GET['zona'] ?? '';
if (!in_array($zona, $zonas_validas)) {
    http_response_code(400);
    echo json_encode(['error' => 'Zona inválida']);
    exit;
}

$dir = __DIR__ . '/../../../archivos/generados/Calidad/etiquetado/' . $zona . '/';
$files = [];
if (is_dir($dir)) {
    $scanned = scandir($dir, SCANDIR_SORT_DESCENDING);
    foreach ($scanned as $f) {
        if (pathinfo($f, PATHINFO_EXTENSION) === 'json') {
            $files[] = $f;
        }
    }
}
header('Content-Type: application/json');
echo json_encode($files);
?>
