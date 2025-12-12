<?php
require '../../vendor/autoload.php';
require '../sesion.php';
require '../conection.php';
$sede = $_SESSION['sede'];
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
if ($sede === 'ZC') {
    $carpetaDestino = '/var/www/fmt/archivos/generados/control_cantidad/';
} else {
    $carpetaDestino = '/var/www/fmt/archivos/generados/control_cantidad_zs/';
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoge los datos del formulario
    $harina = $_POST['harina'] ?? 'SinNombre';
    $fecha = $_POST['fecha'] ?? '';
    $hora = $_POST['hora'] ?? '';
    $lote = $_POST['lote'] ?? '';
    $responsable = $_POST['responsable'] ?? '';

    // Bultos individuales
    $bulto_1 = $_POST['bulto_1'] ?? '';
    $bulto_2 = $_POST['bulto_2'] ?? '';
    $bulto_3 = $_POST['bulto_3'] ?? '';
    $bulto_4 = $_POST['bulto_4'] ?? '';
    $bulto_5 = $_POST['bulto_5'] ?? '';
    $bulto_6 = $_POST['bulto_6'] ?? '';
    $bulto_7 = $_POST['bulto_7'] ?? '';
    $bulto_8 = $_POST['bulto_8'] ?? '';
    $bulto_9 = $_POST['bulto_9'] ?? '';
    $bulto_10 = $_POST['bulto_10'] ?? '';

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
        $plantilla = "../../archivos/formularios/formulario7.xlsx";
        if (!file_exists($plantilla)) {
            die("No se encontró la plantilla base.");
        }
        $spreadsheet = IOFactory::load($plantilla);
    }

    $sheet = $spreadsheet->getActiveSheet();

    // Buscar la primera fila vacía (asumiendo que la fila 1 es encabezado)
    $fila = 8;
    while (
        $sheet->getCell("A" . $fila)->getValue() !== null &&
        $sheet->getCell("A" . $fila)->getValue() !== ''
    ) {
        $fila++;
    }

    // Asigna los valores a las celdas (personaliza aquí)
    $sheet->setCellValue('F5', $harina);         // Personaliza la celda para harina
    $sheet->setCellValue('A' . $fila, $fecha);          // Personaliza la celda para fecha
    $sheet->setCellValue('B' . $fila, $hora);           // Personaliza la celda para hora
    $sheet->setCellValue('C' . $fila, $lote);           // Personaliza la celda para lote
    $sheet->setCellValue('N' . $fila, $responsable);    // Personaliza la celda para responsable

    // Bultos en celdas independientes (personaliza las celdas según tu plantilla)
    $sheet->setCellValue('D' . $fila, $bulto_1);   // Bulto 1
    $sheet->setCellValue('E' . $fila, $bulto_2);   // Bulto 2
    $sheet->setCellValue('F' . $fila, $bulto_3);   // Bulto 3
    $sheet->setCellValue('G' . $fila, $bulto_4);   // Bulto 4
    $sheet->setCellValue('H' . $fila, $bulto_5);   // Bulto 5
    $sheet->setCellValue('I' . $fila, $bulto_6);   // Bulto 6
    $sheet->setCellValue('J' . $fila, $bulto_7);   // Bulto 7
    $sheet->setCellValue('K' . $fila, $bulto_8);   // Bulto 8
    $sheet->setCellValue('L' . $fila, $bulto_9);   // Bulto 9
    $sheet->setCellValue('M' . $fila, $bulto_10);  // Bulto 10}

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
            <a href="geleria_productos.php" class="btn-volver">Volver a la galería de productos</a>
        </div>
    </body>
    </html>
    ';
    exit();
}
?>