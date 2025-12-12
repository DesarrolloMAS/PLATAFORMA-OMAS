<?php
require '../vendor/autoload.php'; // Asegúrate de tener PhpSpreadsheet instalado
require 'sesion.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
if (isset($_GET['archivo'])) {
    $archivo = $_GET['archivo'];
    $sede = $_SESSION['sede'];
    if ($sede === 'ZC'){
        $carpeta = rtrim(__DIR__, '/') . '/../archivos/generados/excelS_M/';
        $rutaArchivo = $carpeta . $archivo;
    }else{
        $carpetaZS = rtrim(__DIR__, '/') . '/../archivos/generados/excelS_MZS/';
        $rutaArchivo = $carpetaZS . $archivo;
    }

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
    <link rel="stylesheet" href="../css/revision.css"> <!-- Asegúrate de tener estilos -->
    <title>Vista Previa de Excel</title><a href="procesar.php?archivo=<?php echo urlencode($archivo); ?>">Corregir</a>
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
        <br><br><br><br><br>
                <!-- Botón para generar PDF -->
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
    <h1 class="titulo_principal">Vista Previa del Archivo Excel <a class="boton" href="./reformulario001.php">Volver</a></h1>
    <br><br><br><br><br>
    <a class="boton" href="procesar.php?archivo=<?php echo urlencode($archivo); ?>">Corregir</a>
    <?php echo $htmlContent; ?>
</body>
</html>


