<?php
require __DIR__ . '/../../vendor/autoload.php';
require '../sesion.php';
require '../conection.php';
$sede = $_SESSION['sede'];
use PhpOffice\PhpSpreadsheet\IOFactory;
use Mpdf\Mpdf;


$carpeta = rtrim(__DIR__, '/') . '/../../archivos/generados/control_cantidad/';
$carpetapdf = rtrim(__DIR__, '/') . '/../../archivos/generados/control_cantidad_pdf/';

if (isset($_GET['archivo'])) {
    $archivo = $_GET['archivo'];
    $rutaArchivo = $carpeta . $archivo;
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
        $mpdf = new Mpdf();
$mpdf->WriteHTML($htmlContent);

// Guardar el PDF en una ubicación específica
$pdfCarpeta = $carpetapdf;
$pdfNombre = $archivo . '.pdf'; // Nombre del archivo PDF
$pdfRuta = $pdfCarpeta . $pdfNombre;

if (!is_dir($pdfCarpeta)) {
    mkdir($pdfCarpeta, 0777, true);
}

$mpdf->Output($pdfRuta, 'F'); // Guardar el PDF en el servidor
    } catch (Exception $e) {
        die("Error al procesar el archivo: " . $e->getMessage());
    }
} else {
    die("Archivo no especificado.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/revision.css"> <!-- Asegúrate de tener estilos -->
    <title>Vista Previa de Excel</title>
    <h1 class="titulo_principal">Vista Previa del Archivo Excel</h1>
    <br><br><br><br>
    <!-- Botón para generar PDF -->
    <form action="generarpdf.php" method="POST">
        <input type="hidden" name="archivo" value="<?php echo htmlspecialchars($archivo); ?>">
        <button type="submit" class="boton">Generar PDF</button>
    </form>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
    </style>
</head>
<body>
    <h1 class="titulo_principal">Vista Previa del Archivo Excel <a class="boton" href="../redireccion.php">Volver</a></h1>
    <br><br><br><br><br>
    <a class="boton" href="procesamiento_control_2.php?archivo=<?php echo urlencode($archivo); ?>">Corregir</a>
    <?php echo $htmlContent; ?>
</body>

</html>

