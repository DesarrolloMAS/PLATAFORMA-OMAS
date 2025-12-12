<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require __DIR__ . '/../../vendor/autoload.php';
require '../conection.php'; // Conexión a la base de datos
require '../sesion.php';
$sede = $_SESSION['sede'];
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
//direccion carpeta destino
$carpetaDestino = '/var/www/fmt/archivos/generados/control_familiar/';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoge los datos del formulario
    $harina = $_POST['harina'] ?? 'SinNombre';
    $fecha = $_POST['fecha'] ?? '';
    $hora = $_POST['hora'] ?? '';
    $lote = $_POST['lote'] ?? '';
    $responsable = $_POST['responsable'] ?? '';
    $pulso = $_POST['pulso'] ?? '';
    $unidades = $_POST['unidades'] ?? '';
    $peso_total = $_POST['peso_total'] ?? '';
    $peso_unitario = $_POST['peso_unitario'] ?? '';
    $peso_presentacion = $_POST['peso_presentacion'] ?? '';
    $cumplimiento = $_POST['cumplimiento'] ?? '';

    // Carpeta de destino
    
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
        $plantilla = "/var/www/fmt/archivos/formularios/formulario7ZS.xlsx";
        if (!file_exists($plantilla)) {
            die("No se encontró la plantilla base.");
        }
        $spreadsheet = IOFactory::load($plantilla);
    }

    $sheet = $spreadsheet->getActiveSheet();

    // Buscar la primera fila vacía (asumiendo que la fila 1 es encabezado)
    $fila = 11;
    while (
        $sheet->getCell("A" . $fila)->getValue() !== null &&
        $sheet->getCell("A" . $fila)->getValue() !== ''
    ) {
        $fila++;
    }

$sheet->setCellValue('C7', $harina);         // Personaliza la celda para harina
$sheet->setCellValue('A' . $fila, $fecha);          // Personaliza la celda para fecha
$sheet->setCellValue('B' . $fila, $hora);           // Personaliza la celda para hora
$sheet->setCellValue('C' . $fila, $lote);           // Personaliza la celda para lote
$sheet->setCellValue('I' . $fila, $responsable);    // Personaliza la celda para responsable

// Nuevas variables agregadas
$sheet->setCellValue('D' . $fila, $pulso);           // Pulso
$sheet->setCellValue('E' . $fila, $unidades);        // Unidades
$sheet->setCellValue('F' . $fila, $peso_total);      // Peso total
$sheet->setCellValue('G' . $fila, $peso_unitario);   // Peso unitario
$sheet->setCellValue('H' . $fila, $peso_presentacion); // Peso presentación
$sheet->setCellValue('J' . $fila, $cumplimiento);    // Cumplimiento


    $imagen_logoPath =  __DIR__ .'/../../archivos/formularios/logomas.png';
    $imagen_logoImg = new Drawing();
    $imagen_logoImg->setPath($imagen_logoPath);
    $imagen_logoImg->setHeight(200); // Ajusta el tamaño según sea necesario
    $imagen_logoImg->setCoordinates('A1');
    $imagen_logoImg->setWorksheet($sheet);

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
            <a href="galeria_productos.php" class="btn-volver">Volver a la galería de productos</a>
        </div>
    </body>
    </html>
    ';
    exit();
}
?>