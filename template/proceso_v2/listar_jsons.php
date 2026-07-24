<?php
require '../sesion.php';
verificarAutenticacion();

header('Content-Type: application/json');

$sede = $_SESSION['sede'];
$base_dir = "../../archivos/generados/proceso_v2/" . preg_replace('/[^A-Za-z0-9_-]/', '', $sede) . "/";

if (!is_dir($base_dir)) {
    echo json_encode([]);
    exit;
}

$files = scandir($base_dir);
$json_files = [];

foreach ($files as $file) {
    if (pathinfo($file, PATHINFO_EXTENSION) === 'json') {
        $json_files[] = [
            "name" => $file,
            "path" => $file,
            "display" => str_replace(['PROCESO_MOLIENDA_', '.json'], '', $file)
        ];
    }
}

// Ordenar por nombre descendente (meses más recientes primero)
rsort($json_files);

echo json_encode($json_files);
?>
