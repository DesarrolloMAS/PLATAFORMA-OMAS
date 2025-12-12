<?php
require '../../vendor/autoload.php'; // Asegúrate de que PhpSpreadsheet esté instalado
require '../sesion.php'; // Incluye el archivo de autenticación
require '../conection.php'; // Conexión a la base de datos
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

$sede = $_SESSION['sede'];
if ($sede == 'ZC'){
    $directorioSalida = __DIR__ . '/../../archivos/generados/reprocesos_zc/';
} else {
    $directorioSalida = __DIR__ . '/../../archivos/generados/reprocesos_zs/';
}
if (!file_exists($directorioSalida) && !mkdir($directorioSalida, 0777, true)) {
    die("Error: No se pudo crear el directorio de salida para la sede: $sede");
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ruta de la plantilla Excel
    $plantillaRuta = __DIR__ . '/../../archivos/formularios/formulario5.xlsx'; // Cambia esta ruta según tu estructura
    $nombreArchivo = 'reproceso_' . date('Ymd_His') . '.xlsx';
    $archivoSalida = $directorioSalida . $nombreArchivo;

    // Verificar si la plantilla existe
    if (!file_exists($plantillaRuta)) {
        die("Error: La plantilla Excel no existe en la ruta especificada: $plantillaRuta");
    }

    try {
        // Cargar la plantilla Excel
        $spreadsheet = IOFactory::load($plantillaRuta);
        $hoja = $spreadsheet->getActiveSheet();

        // Obtener los datos enviados por POST
        $datosJSON = $_POST['json'] ?? []; // Datos originales del JSON
        $datosFormulario = $_POST; // Nuevos datos del formulario

        // Asignar datos del JSON a celdas específicas
        $hoja->setCellValue('B6', $datosJSON['fecha'] ?? ''); // Fecha
        $hoja->setCellValue('B7', $datosJSON['responsable'] ?? ''); // Responsable
        $hoja->setCellValue('B8', $datosJSON['producto'] ?? ''); // Producto
        $hoja->setCellValue('B9', $datosJSON['lote'] ?? ''); // Lote
        $hoja->setCellValue('B10', $datosJSON['hora'] ?? ''); // Hora
        $hoja->setCellValue('B11', $datosJSON['cantidad'] ?? ''); // Cantidad
        $hoja->setCellValue('B12', $datosJSON['motivo'] ?? ''); // Motivo
        $hoja->setCellValue('B13', $datosJSON['proceso'] ?? ''); // Proceso

        // Asignar datos del formulario a celdas específicas
        $hoja->setCellValue('E6', $datosFormulario['Fecha_Reproceso'] ?? ''); // Fecha de reproceso
        $hoja->setCellValue('F6', $datosFormulario['Referencia'] ?? ''); // Referencia
        $hoja->setCellValue('G6', $datosFormulario['hora_inicio'] ?? ''); // Hora de inicio
        $hoja->setCellValue('G10', $datosFormulario['hora_fin'] ?? ''); // Hora de fin
        $hoja->setCellValue('H6', $datosFormulario['cantidad'] ?? ''); // Cantidad
        $hoja->setCellValue('I6', $datosFormulario['responsable'] ?? ''); // Responsable de ejecución

        // Guardar el archivo Excel generado
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($archivoSalida);

        // Eliminar el archivo JSON después de procesar
        if (isset($_POST['json_file'])) {
            $jsonFile = $_POST['json_file'];
            if (file_exists($jsonFile)) {
                unlink($jsonFile);
            }
        }

        // Mostrar mensaje de éxito personalizado
        echo "
        <!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <title>Reproceso generado</title>
            <style>
                body { font-family: Arial, sans-serif; background: #f6f6f6; }
                .container {
                    display: flex; flex-direction: column; align-items: center; justify-content: center;
                    height: 100vh;
                }
                .success {
                    color: #4CAF50; font-size: 2em; margin-bottom: 20px;
                }
                .archivo {
                    font-size: 1.2em; margin-bottom: 30px;
                }
                .btn {
                    padding: 12px 30px; background: #007bff; color: #fff; border: none; border-radius: 5px;
                    font-size: 1em; cursor: pointer; text-decoration: none;
                }
                .btn:hover { background: #0056b3; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='success'>¡Archivo Excel generado correctamente!</div>
                <div class='archivo'>El archivo <strong>$nombreArchivo</strong> ha sido guardado exitosamente.</div>
                <a class='btn' href='/fmt/template/reprocesos/reprocesos_menu.php'>Volver al menú de reprocesos</a>
            </div>
        </body>
        </html>
        ";
    } catch (Exception $e) {
        echo "Error al procesar los datos: " . $e->getMessage();
    }
} else {
    echo "No se han enviado datos.";
}
?>