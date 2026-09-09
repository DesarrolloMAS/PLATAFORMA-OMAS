<?php
include '../../sesion.php';

header('Content-Type: application/json; charset=utf-8');

$sede     = preg_replace('/[^A-Za-z0-9_-]/', '', $_SESSION['sede'] ?? '');
$base_dir = "../../../archivos/generados/analisis_trabajo/{$sede}/";

if (!file_exists($base_dir)) {
    echo json_encode([]);
    exit;
}

$archivos = glob($base_dir . "ATS_*.json");
rsort($archivos);

$lista = [];
foreach ($archivos as $archivo) {
    $lista[] = [
        'ruta'  => $archivo,
        'nombre' => basename($archivo)
    ];
}

echo json_encode($lista);
?>
