<?php
require '../../sesion.php';
verificarAutenticacion();
header('Content-Type: application/json');

$sede = $_SESSION['sede'];
$base_dir = "../../../archivos/generados/PNC/" . $sede . "/";

$archivos = [];
if (is_dir($base_dir)) {
    $archivos_json = glob($base_dir . "*.json");
    foreach ($archivos_json as $archivo) {
        $nombre = basename($archivo);
        $fecha_mod = date("d/m/Y H:i:s", filemtime($archivo));
        $archivos[] = [
            'nombre' => $nombre, 
            'fecha_mod' => $fecha_mod, 
            'fecha_sort' => filemtime($archivo)
        ];
    }
}

// Ordenar más reciente primero
usort($archivos, function($a, $b) { 
    return $b['fecha_sort'] <=> $a['fecha_sort']; 
});

echo json_encode($archivos);
?>
