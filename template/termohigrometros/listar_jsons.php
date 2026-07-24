<?php
require_once __DIR__ . '/../sesion.php';
$zona = isset($_SESSION['sede']) ? $_SESSION['sede'] : 'General';
$base_dir = __DIR__ . "/../../archivos/generados/termohigrometros/";
$files = [];

// Escanear carpeta general (legacy)
if (is_dir($base_dir)) {
    foreach (scandir($base_dir) as $f) {
        if (pathinfo($f, PATHINFO_EXTENSION) === 'json') {
            $files[] = $f;
        }
    }
}

// Escanear carpeta de la zona (buscando en subcarpetas de ubicaciones)
$zona_dir = $base_dir . $zona . "/";
if (is_dir($zona_dir)) {
    // Escaner subdirectorios (las ubicaciones)
    foreach (scandir($zona_dir) as $item) {
        if ($item === '.' || $item === '..') continue;
        
        $item_path = $zona_dir . $item;
        
        // Si es un archivo JSON directo (legacy/transicion)
        if (is_file($item_path) && pathinfo($item, PATHINFO_EXTENSION) === 'json') {
            $rel_path = $zona . "/" . $item;
            if (!in_array($item, $files) && !in_array($rel_path, $files)) {
                $files[] = $rel_path;
            }
        } 
        // Si es un directorio (una ubicacion específica nueva)
        elseif (is_dir($item_path)) {
            foreach (scandir($item_path) as $f) {
                if (pathinfo($f, PATHINFO_EXTENSION) === 'json') {
                    $rel_path = $zona . "/" . $item . "/" . $f;
                    if (!in_array($rel_path, $files)) {
                        $files[] = $rel_path;
                    }
                }
            }
        }
    }
}

header('Content-Type: application/json');
echo json_encode($files);
?>
