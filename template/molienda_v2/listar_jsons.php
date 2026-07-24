<?php
require '../sesion.php';
verificarAutenticacion();

header('Content-Type: application/json');

$sede = $_SESSION['sede'];
$base_dir = "../../archivos/generados/molienda/" . $sede . "/";

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
            "display" => str_replace('.json', '', $file)
        ];
    }
}

// Ordenar por nombre descendente (meses más recientes primero)
rsort($json_files);

echo json_encode($json_files);
?>
