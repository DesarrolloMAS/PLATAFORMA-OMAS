<?php
require __DIR__ . '/../../vendor/autoload.php';
require '../sesion.php';
require '../conection.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
use Mpdf\Mpdf;
$zona=$_SESSION['sede'];
if ($zona === 'ZC'){
    $carpeta = realpath(__DIR__ . '/../../archivos/generados/excelC_M/');
    $pdfCarpeta = $carpeta . '/../pdfsC_M/';
}
else{
    $carpeta = realpath(__DIR__ . '/../../archivos/generados/excelC_MZS/');
    $pdfCarpeta = $carpeta . '/../pdfsC_MZS/';
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['archivo'])) {
    $archivo = trim($_POST['archivo']);
    $rutaArchivo = $carpeta . DIRECTORY_SEPARATOR . $archivo;

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
        $mpdf = new Mpdf();
        $mpdf->WriteHTML($htmlContent);

        // Guardar el PDF en una ubicación específica
        $pdfNombre = pathinfo($archivo, PATHINFO_FILENAME) . '.pdf';
        $pdfRuta = $pdfCarpeta . $pdfNombre;

        if (!is_dir($pdfCarpeta)) {
            mkdir($pdfCarpeta, 0777, true);
        }

        $mpdf->Output($pdfRuta, 'F'); // Guardar el PDF en el servidor

        header("Location: reformulario002.php");
        } catch (Exception $e) {
        die("Error al procesar el archivo: " . $e->getMessage());
        }
        } else {
        die("Método no permitido.");
        };
?>