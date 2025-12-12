<?php
// Requiere PhpSpreadsheet instalado con Composer
require '/var/www/fmt/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

// Recibe datos del formulario
$empaque = isset($_GET['empaque']) ? trim($_GET['empaque']) : '';
$lote = isset($_GET['lote']) ? trim($_GET['lote']) : '';

if (!$empaque || !$lote) {
    // Si faltan datos, regresa a la galería
    header('Location: empaque_galerias.php');
    exit;
}

// Sanitiza el nombre del archivo
$nombre_archivo = preg_replace('/[^A-Za-z0-9_\-]/', '_', $empaque) . '_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $lote) . '.xlsx';
$ruta_archivo = '../../archivos/generados/empaque/' . $nombre_archivo;

// Si el archivo no existe, crea uno desde la plantilla
if (!file_exists($ruta_archivo)) {
    $ruta_plantilla = "../../archivos/formularios/formulario9.xlsx";
    if (file_exists($ruta_plantilla)) {
        $spreadsheet = IOFactory::load($ruta_plantilla);
    } else {
        // Si no hay plantilla, crea un archivo vacío
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->setCellValue('A1', 'CONTROL DE EMPAQUE');
    }
    // Guarda el archivo nuevo
    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save($ruta_archivo);
}

// Redirige a empaque.php con el archivo creado o existente
header('Location: empaque.php?archivo=' . urlencode($nombre_archivo));
exit;