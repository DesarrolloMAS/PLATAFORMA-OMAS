<?php
require_once '/var/www/html/fmt/vendor/autoload.php';
require_once '../../sesion.php';
require '../../conection.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
use Mpdf\Mpdf;
$sede = $_SESSION['sede'];
if ($sede === 'ZS'){
    $carpeta = '/var/www/html/fmt/archivos/generados/Calidad/liberaciones_zs';
}else
    $carpeta = '/var/www/html/fmt/archivos/generados/Calidad/liberaciones';
if (isset($_GET['archivo'])) {
    $archivo = $_GET['archivo'];
    $rutaArchivo = $carpeta . DIRECTORY_SEPARATOR . $archivo; // Agregar separador entre carpeta y archivo
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
$pdfCarpeta = $carpeta . '/../pdfs_liberaciones/';
$pdfNombre = 'Registro_de_Liberacion' . '.pdf';
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
    <title>Vista Previa de Excel</title><a href="correccion_liberaciones.php?archivo=<?php echo urlencode($archivo); ?>">Corregir</a>
    <h1 class="titulo_principal">Vista Previa del Archivo Excel</h1>

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
    <h1 class="titulo_principal">Vista Previa del Archivo Excel <a class="boton" href="galeria_liberaciones.php">Volver</a></h1>
    <br><br><br><br><br>
    <a class="boton" href="correccion_liberaciones.php?archivo=<?php echo urlencode($archivo); ?>">Corregir</a>
    <?php echo $htmlContent; ?>
</body>

</html>

