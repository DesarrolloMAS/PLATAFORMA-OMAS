<?php
// filepath: c:\xampp\htdocs\fmt\template\purga\purga_save.php
session_start();
require __DIR__ . '/../../vendor/autoload.php';
require '../sesion.php'; // Incluye el archivo de autenticación
require '../conection.php'; // Conexión a la base de datos
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

// Verificar autenticación
verificarAutenticacion();

if (!isset($_SESSION['id_usuario']) || empty($_SESSION['id_usuario'])) {
    die("Error: El usuario no está definido en la sesión.");
}

// Configuración de la ubicación para guardar el archivo Excel
$ubicacion = __DIR__ . '/../../archivos/generados/Purga De proceso';
$nombreArchivo = "Purga_Proceso.xlsx";
$rutaArchivo = $ubicacion . "/" . $nombreArchivo;

// Crear la carpeta si no existe
if (!is_dir($ubicacion)) {
    mkdir($ubicacion, 0777, true);
}

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capturar los datos del formulario
    $fecha_produccion = $_POST['fecha_produccion'];
    $referencia_producto = $_POST['referencia_producto'];
    $hora_inicial = $_POST['hora_inicial'];
    $cantidad = $_POST['cantidad'];
    $responsable_cambio = $_POST['responsable_cambio'];
    $fecha_ingreso = $_POST['fecha_ingreso'];
    $referencia_producto_linea = $_POST['referencia_producto_linea'];
    $hora_ingreso = $_POST['hora_ingreso'];
    $hora_final = $_POST['hora_final'];
    $cantidad_line = $_POST['cantidad_line'];
    $responsable_linea = $_POST['responsable_linea'];

    // Verificar si el archivo ya existe
    if (file_exists($rutaArchivo)) {
        // Cargar el archivo existente
        $spreadsheet = IOFactory::load($rutaArchivo);
    } else {
        // Usar la plantilla para crear un nuevo archivo
        $plantilla = __DIR__ . '/../../archivos/formularios/formulario6.xlsx';
        if (!file_exists($plantilla)) {
            die("Error: La plantilla de Excel no existe.");
        }
        $spreadsheet = IOFactory::load($plantilla);
    }

    $sheet = $spreadsheet->getActiveSheet();

    // Buscar la primera fila vacía
    $fila = 8; // Asumiendo que la fila 1 tiene encabezados
    while ($sheet->getCell("A" . $fila)->getValue() !== null) {
        $fila++;
    }

    // Escribir los datos en la primera fila vacía
    $sheet->setCellValue('A' . $fila, $fecha_produccion);
    $sheet->setCellValue('B' . $fila, $referencia_producto);
    $sheet->setCellValue('D' . $fila, $hora_inicial);
    $sheet->setCellValue('E' . $fila, $cantidad);
    $sheet->setCellValue('F' . $fila, $responsable_cambio);
    $sheet->setCellValue('G' . $fila, $fecha_ingreso);
    $sheet->setCellValue('H' . $fila, $referencia_producto_linea);
    $sheet->setCellValue('I' . $fila, $hora_ingreso);
    $sheet->setCellValue('J' . $fila, $hora_final);
    $sheet->setCellValue('K' . $fila, $cantidad_line);
    $sheet->setCellValue('L' . $fila, $responsable_linea);

    // Guardar el archivo Excel
    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save($rutaArchivo);

    // Redirigir al usuario según su rol
    if ($_SESSION['rol'] === 'adm') {
        header("Location: ../../template/menu_adm.html");
    } elseif ($_SESSION['rol'] === '3') {
        header("Location: ../../template/menu.html");
    } elseif ($_SESSION['rol'] === '1') {
        header("Location: ../../template/menu_adm.html");
    } else {
        header("Location: ../../template/menu.html");
    }
    exit();
}
?>