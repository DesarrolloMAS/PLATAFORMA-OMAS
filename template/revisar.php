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
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vista Previa - <?php echo htmlspecialchars($archivo); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/revision_1.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <h1 class="header-title">Vista Previa del Archivo Excel</h1>
            <div class="header-actions">
                <a href="procesar.php?archivo=<?php echo urlencode($archivo); ?>" class="correct-button">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                        <path d="M12.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L5.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z"/>
                        <path d="M6.25 8.043l-.896-.897a.5.5 0 1 0-.708.708l.897.896.707-.707zm1 2.414l.896.897a.5.5 0 0 0 .708 0l7-7a.5.5 0 0 0-.708-.708L8.5 10.293l-.543-.543-.707.707z"/>
                    </svg>
                    Corregir
                </a>
                <a href="./reformulario001.php" class="back-button">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M10 12L6 8l4-4"/>
                    </svg>
                    Volver
                </a>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <div class="excel-container">
        <div class="info-badge">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                <path d="M4 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0H4z"/>
                <path d="M9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1z"/>
            </svg>
            <span><?php echo htmlspecialchars($archivo); ?></span>
        </div>

        <div class="excel-viewer">
            <?php echo $htmlContent; ?>
        </div>
    </div>
</body>
</html>