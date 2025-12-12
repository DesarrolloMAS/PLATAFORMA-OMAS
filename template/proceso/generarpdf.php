<?php
require __DIR__ . '/../../vendor/autoload.php';
require '../sesion.php';
require '../conection.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
use Mpdf\Mpdf;
    $zona=$_SESSION['sede'];
    $carpeta = rtrim(__DIR__, '/') . '/../../archivos/generados/proceso_molienda/';
    $carpetapdf = rtrim(__DIR__, '/') . '/../../archivos/generados/proces_molienda_pdf/';
    $mes = date('m-Y'); // Obtener el mes y año actual

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['archivo'])) {
    $archivo = trim($_POST['archivo']);
    $rutaArchivo = $carpeta  . $archivo;

    if (!file_exists($rutaArchivo)) {
        die("Error: El archivo no existe o la ruta es incorrecta: $rutaArchivo");
    }

    try {
        // Cargar el archivo Excel
        $spreadsheet = IOFactory::load($rutaArchivo);
        $hoja = $spreadsheet->getActiveSheet();

        // Convertir hoja a HTML
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Html($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $htmlContent = ob_get_clean();

        // Crear el PDF
        $mpdf = new Mpdf(['format' => [431.8, 279.4]]);
        $mpdf->WriteHTML($htmlContent);

        // Guardar el PDF en una ubicación específica
        $pdfCarpeta = $carpetapdf;
        $pdfNombre = 'Proceso_Molienda' . $mes . '.pdf';
        $pdfRuta = $pdfCarpeta . $pdfNombre;

        if (!is_dir($pdfCarpeta)) {
            mkdir($pdfCarpeta, 0777, true);
        }

        $mpdf->Output($pdfRuta, 'F'); // Guardar el PDF en el servidor

        header("Location: galeria_proceso_molienda.php");
        } catch (Exception $e) {
        die("Error al procesar el archivo: " . $e->getMessage());
        }
        } else {
        die("Método no permitido.");
        };
?>
