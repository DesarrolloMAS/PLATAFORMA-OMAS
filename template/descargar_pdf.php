<?php
ini_set('memory_limit', '512M');
ini_set('max_execution_time', 120); // 120 segundos (ajusta según necesidad)
ini_set('pcre.backtrack_limit', 10000000); // 10 millones
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require '../vendor/autoload.php';
require 'sesion.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
use Mpdf\Mpdf;

if (!isset($_GET['archivo'])) {
    die('Archivo no especificado.');
}

$sede = $_SESSION['sede'];
$archivo = trim($_GET['archivo']);

if ($sede === 'ZC') {
    $carpeta = realpath(__DIR__ . '/../archivos/generados/excelS_M/');
    $pdfCarpeta = realpath(__DIR__ . '/../archivos/generados/excelS_M/') . '/../pdfsS_M/';
} else {
    $carpeta = realpath(__DIR__ . '/../archivos/generados/excelS_MZS/');
    $pdfCarpeta = realpath(__DIR__ . '/../archivos/generados/excelS_MZS/') . '/../pdfsS_MZS/';
}
$rutaArchivo = $carpeta . DIRECTORY_SEPARATOR . $archivo;

if (!file_exists($rutaArchivo)) {
    die('Error: El archivo no existe o la ruta es incorrecta: ' . $rutaArchivo);
}

// Leer el código de orden de trabajo desde la celda J6
try {
    $spreadsheet = IOFactory::load($rutaArchivo);
    $hoja = $spreadsheet->getActiveSheet();
    $codigoorden = $hoja->getCell('J6')->getValue();
    if (!$codigoorden) {
        $codigoorden = pathinfo($archivo, PATHINFO_FILENAME);
    }
    $pdfNombre = 'SolicitudMantenimiento_' . $codigoorden . '.pdf';
    $pdfRuta = $pdfCarpeta . $pdfNombre;
    if (!is_dir($pdfCarpeta)) {
        mkdir($pdfCarpeta, 0777, true);
    }
    // Si el PDF no existe, generarlo
    if (!file_exists($pdfRuta)) {
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Html($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $htmlContent = ob_get_clean();
        $mpdf = new Mpdf();
        // Dividir el HTML en fragmentos de 1 millón de caracteres
        $chunks = str_split($htmlContent, 1000000);
        foreach ($chunks as $chunk) {
            $mpdf->WriteHTML($chunk);
        }
        $mpdf->Output($pdfRuta, 'F');
    }
    // Descargar el PDF
    if (file_exists($pdfRuta)) {
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . basename($pdfRuta) . '"');
        header('Content-Length: ' . filesize($pdfRuta));
        readfile($pdfRuta);
        exit;
    } else {
        die('No se pudo generar el PDF.');
    }
} catch (Exception $e) {
    die('Error al procesar el archivo: ' . $e->getMessage());
}
