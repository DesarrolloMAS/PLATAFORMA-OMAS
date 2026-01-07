<?php
ini_set('memory_limit', '1024M');
ini_set('max_execution_time', 300);
ini_set('pcre.backtrack_limit', 10000000);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require '../vendor/autoload.php';
require 'sesion.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Mpdf\Mpdf;

if (!isset($_GET['archivo'])) {
    die('Archivo no especificado.');
}

$sede = $_GET['sede'] ?? $_SESSION['sede'];
$archivo = trim($_GET['archivo']);
$tipo = $_GET['tipo'] ?? 'mantenimiento'; // 'mantenimiento' o 'liberaciones'

// Determinar carpetas según el tipo
if ($tipo === 'liberaciones') {
    if ($sede === 'ZS') {
        $carpeta = realpath(__DIR__ . '/../archivos/generados/Calidad/liberaciones_zs/');
        $pdfCarpeta = realpath(__DIR__ . '/../archivos/generados/Calidad/') . '/pdfs_liberaciones_zs/';
    } else {
        $carpeta = realpath(__DIR__ . '/../archivos/generados/Calidad/liberaciones/');
        $pdfCarpeta = realpath(__DIR__ . '/../archivos/generados/Calidad/') . '/pdfs_liberaciones/';
    }
    $prefijoPdf = 'Liberacion_';
    $celdaCodigo = 'D5'; // Ajusta según tu formato de liberaciones
} else {
    // Mantenimiento
    if ($sede === 'ZC') {
        $carpeta = realpath(__DIR__ . '/../archivos/generados/excelS_M/');
        $pdfCarpeta = realpath(__DIR__ . '/../archivos/generados/excelS_M/') . '/../pdfsS_M/';
    } else {
        $carpeta = realpath(__DIR__ . '/../archivos/generados/excelS_MZS/');
        $pdfCarpeta = realpath(__DIR__ . '/../archivos/generados/excelS_MZS/') . '/../pdfsS_MZS/';
    }
    $prefijoPdf = 'SolicitudMantenimiento_';
    $celdaCodigo = 'J6';
}

$rutaArchivo = $carpeta . DIRECTORY_SEPARATOR . $archivo;

if (!file_exists($rutaArchivo)) {
    die('Error: El archivo no existe o la ruta es incorrecta: ' . $rutaArchivo);
}

// Leer el código de orden de trabajo
try {
    $spreadsheet = IOFactory::load($rutaArchivo);
    $hoja = $spreadsheet->getActiveSheet();
    
    // Insertar logo en el Excel solo para liberaciones
    if ($tipo === 'liberaciones') {
        $logoPath = realpath(__DIR__ . '/../img/mas-white.png');
        
        if (!file_exists($logoPath)) {
            die('Logo no encontrado. Ruta intentada: ' . $logoPath);
        }
        
        // Crear objeto Drawing para insertar imagen
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Logo de la empresa');
        $drawing->setPath($logoPath);
        $drawing->setCoordinates('A1'); // Celda donde se insertará (ajusta según necesites: B1, C1, etc.)
        $drawing->setHeight(80); // Altura en píxeles (ajusta según necesites)
        $drawing->setWorksheet($hoja);
    }
    
    $codigoorden = $hoja->getCell($celdaCodigo)->getValue();
    if (!$codigoorden) {
        $codigoorden = pathinfo($archivo, PATHINFO_FILENAME);
    }
    $pdfNombre = $prefijoPdf . $codigoorden . '.pdf';
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
        
        if ($tipo === 'liberaciones') {
            $mpdf = new Mpdf([
                'orientation' => 'L', // Horizontal para liberaciones
                'format' => 'A4'
            ]);
        } else {
            $mpdf = new Mpdf(); // Vertical (por defecto) para mantenimiento
        }
        
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