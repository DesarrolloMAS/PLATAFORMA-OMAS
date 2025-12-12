<?php
require '../../vendor/autoload.php';
require '../sesion.php';
require '../conection.php';

$sede = $_SESSION['sede'];
use PhpOffice\PhpSpreadsheet\IOFactory;

// Define la carpeta de destino según la sede
    $carpetaDestino = '/var/www/fmt/archivos/generados/envasado/';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoge los datos del formulario
    $harina = $_POST['harina'] ?? 'FALLIDO'; // Dato enviado desde galeria_productos.php
    $fecha = $_POST['fecha'] ?? '';
    $hora = $_POST['hora'] ?? '';
    $loteP = $_POST['loteP'] ?? '';
    $fechaVencimiento = $_POST['fechaVencimiento'] ?? '';
    $responsable = $_POST['responsable'] ?? '';
    $purgada = $_POST['purgada'] ?? '';
    $Penvasado = $_POST['Penvasado'] ?? '';
    $timbrado = $_POST['timbrado'] ?? '';
    $etiqueta = $_POST['etiqueta'] ?? '';
    $aprobacion = $_POST['aprobacion'] ?? '';
    $observaciones = $_POST['observaciones'] ?? '';

    // Crear la carpeta de destino si no existe
    if (!is_dir($carpetaDestino)) {
        mkdir($carpetaDestino, 0777, true);
    }

    // Nombre del archivo según la harina
    $nombreArchivo = $harina . ".xlsx";
    $rutaArchivo = $carpetaDestino . "/" . $nombreArchivo;

    // Si el archivo existe, cargarlo, si no, usar la plantilla
    if (file_exists($rutaArchivo)) {
        $spreadsheet = IOFactory::load($rutaArchivo);
    } else {
        $plantilla = "../../archivos/formularios/formulario10.xlsx";
        if (!file_exists($plantilla)) {
            die("No se encontró la plantilla base.");
        }
        $spreadsheet = IOFactory::load($plantilla);
    }

    $sheet = $spreadsheet->getActiveSheet();

    // Buscar la primera fila vacía (asumiendo que la fila 1 es encabezado)
    $fila = 9;
    while (
        $sheet->getCell("A" . $fila)->getValue() !== null &&
        $sheet->getCell("A" . $fila)->getValue() !== ''
    ) {
        $fila++;
    }

    // Asigna los valores a las celdas (personaliza aquí según tu plantilla)
    $sheet->setCellValue('F5', $harina);         // Personaliza la celda para harina
    $sheet->setCellValue('A' . $fila, $fecha);          // Fecha
    $sheet->setCellValue('B' . $fila, $hora);           // Hora
    $sheet->setCellValue('C' . $fila, $loteP);          // Lote de producto
    $sheet->setCellValue('D' . $fila, $fechaVencimiento); // Fecha de vencimiento
    $sheet->setCellValue('E' . $fila, $responsable);    // Responsable
    $sheet->setCellValue('F' . $fila, $purgada);        // Línea purgada
    $sheet->setCellValue('G' . $fila, $Penvasado);      // Referencia del empaque
    $sheet->setCellValue('H' . $fila, $timbrado);       // Lote y fecha timbrados
    $sheet->setCellValue('I' . $fila, $etiqueta);       // Etiqueta
    $sheet->setCellValue('J' . $fila, $aprobacion);     // Aprobación
    $sheet->setCellValue('L' . $fila, $observaciones);  // Observaciones

    // Guarda el archivo
    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save($rutaArchivo);

    // Redirige o muestra mensaje
    echo '
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Registro Exitoso</title>
        <style>
            body { font-family: Arial, sans-serif; background: #f4f4f4; }
            .mensaje-exito {
                background: #fff;
                margin: 80px auto;
                padding: 40px 30px;
                border-radius: 10px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                max-width: 400px;
                text-align: center;
            }
            .mensaje-exito h2 { color: #28a745; }
            .mensaje-exito p { margin: 20px 0; }
            .btn-volver {
                display: inline-block;
                padding: 10px 24px;
                background: #007bff;
                color: #fff;
                border: none;
                border-radius: 5px;
                text-decoration: none;
                font-size: 16px;
                margin-top: 10px;
                cursor: pointer;
                transition: background 0.2s;
            }
            .btn-volver:hover { background: #0056b3; }
        </style>
    </head>
    <body>
        <div class="mensaje-exito">
            <h2>¡Datos guardados exitosamente!</h2>
            <p>El registro para <strong>' . htmlspecialchars($harina) . '</strong> ha sido añadido correctamente.</p>
            <a href="geleria_productos.php" class="btn-volver">Volver a la galería de productos</a>
        </div>
    </body>
    </html>
    ';
    exit();
}
?>