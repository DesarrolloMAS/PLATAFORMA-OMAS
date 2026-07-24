<?php
require_once '../sesion.php';

header('Content-Type: application/json');
verificarAutenticacion();

$sede = $_SESSION['sede'] ?? 'Sin_Sede';
$base_dir = "../../archivos/generados/gestion_subproductos/" . $sede . "/";

$archivos = [];

if (file_exists($base_dir)) {
    $gestor = opendir($base_dir);
    if ($gestor) {
        while (($file = readdir($gestor)) !== false) {
            if ($file != "." && $file != ".." && pathinfo($file, PATHINFO_EXTENSION) == 'json') {
                $filePath = $base_dir . $file;
                $archivos[] = [
                    'nombre' => $file,
                    'tamano' => filesize($filePath),
                    'fecha_mod' => date("Y-m-d H:i:s", filemtime($filePath)),
                    'ruta' => $filePath
                ];
            }
        }
        closedir($gestor);
    }
}

// Ordenar por fecha de modificación descendente
usort($archivos, function($a, $b) {
    return strtotime($b['fecha_mod']) - strtotime($a['fecha_mod']);
});

echo json_encode(['status' => 'success', 'archivos' => $archivos]);
?>
