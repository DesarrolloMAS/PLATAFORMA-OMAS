<?php
require __DIR__ . '/../../vendor/autoload.php';
require '../sesion.php';
require '../conection.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_POST['bodega']) || empty($_POST['bodega'])) {
    die("Error: No se recibió la bodega seleccionada.");
}

$bodega = $_POST['bodega'];
$diah = ltrim(date('d'), '0'); // Día actual sin ceros a la izquierda
$mesActual = '06'; // Mes actual con ceros a la izquierda

$carpetaDocumentos = realpath(__DIR__ . "/../../archivos/generados/excel_INS/");
$plantillaBase = realpath(__DIR__ . "/../../archivos/formularios/formulario3.xlsx");
$carpetaHistorico = $carpetaDocumentos . '/historico/';

if (!$carpetaDocumentos) {
    die("Error: La carpeta de documentos no existe.");
}
if (!$plantillaBase || !file_exists($plantillaBase)) {
    die("Error: La plantilla base no se encuentra en: $plantillaBase");
}
if (!is_dir($carpetaHistorico)) {
    mkdir($carpetaHistorico, 0777, true);
}

// --- SISTEMA DE CIERRE DE MES ---
// Detectar el mes anterior
$mesAnterior = str_pad(((int)$mesActual - 1) === 0 ? 12 : (int)$mesActual - 1, 2, '0', STR_PAD_LEFT);
$archivoAnterior = "$carpetaDocumentos/{$bodega}_{$mesAnterior}.xlsx";

if (file_exists($archivoAnterior)) {
    $spreadsheetAnterior = IOFactory::load($archivoAnterior);

    // 1. Guardar el Excel como PDF en la carpeta historico
    $pdfPath = $carpetaHistorico . "{$bodega}_{$mesAnterior}.pdf";
    try {
        $writerPdf = IOFactory::createWriter($spreadsheetAnterior, 'Mpdf');
        $writerPdf->save($pdfPath);
    } catch (\Exception $e) {
        // Si no tienes Mpdf, puedes comentar esto y solo guardar el Excel
        // die("Error al exportar a PDF: " . $e->getMessage());
    }

    // 2. Mover el Excel a la carpeta historico
    $excelHistorico = $carpetaHistorico . "{$bodega}_{$mesAnterior}.xlsx";
    if (!rename($archivoAnterior, $excelHistorico)) {
        die("Error: No se pudo mover el archivo Excel a la carpeta historico.");
    }
}

// Generar nombre de archivo: bodega_MM.xlsx para el mes actual
$archivo = "$carpetaDocumentos/{$bodega}_{$mesActual}.xlsx";

// Si el archivo no existe, crearlo desde la plantilla
if (!file_exists($archivo)) {
    if (!copy($plantillaBase, $archivo)) {
        die("Error: No se pudo copiar la plantilla a $archivo");
    }
}

$spreadsheet = IOFactory::load($archivo);
$hojaActiva = $spreadsheet->getActiveSheet();

// Buscar la columna correcta según el día actual en la fila 7
$columna = '';
$valoresFila7 = [];
for ($col = 4; $col <= 40; $col++) {
    $letraColumna = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
    $valorCelda = trim((string) $hojaActiva->getCell($letraColumna . '7')->getFormattedValue());
    $valoresFila7[$letraColumna] = $valorCelda;
    if ($valorCelda == $diah) {
        $columna = $letraColumna;
        break;
    }
}
if ($columna == '') {
    die("Error: No se encontró una columna para el día actual: $diah.");
}

// Insertar respuestas en la columna correcta
for ($i = 1; $i <= 14; $i++) {
    $valor = $_POST["opcion$i"] ?? "N/A";
    $hojaActiva->setCellValue("{$columna}" . (7 + $i), $valor);
}

// Buscar la primera fila vacía en la columna A para hallazgos
$fila = 24;
while ($hojaActiva->getCell("A" . $fila)->getValue() !== null) {
    $fila++;
}

$hojaActiva->setCellValue("A{$fila}", $diah); 
$hojaActiva->setCellValue("C{$fila}", $_POST['hallazgo'] ?? ""); 
$hojaActiva->setCellValue("N{$fila}", $_POST['plan'] ?? ""); 
$hojaActiva->setCellValue("X{$fila}", $_POST['diaacc'] ?? ""); 
$hojaActiva->setCellValue("AC{$fila}", $_POST['result'] ?? "");

$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
$writer->save($archivo);

$archivoVerificacion = "../bodegas/ultima_verificacion_{$bodega}.txt";
file_put_contents($archivoVerificacion, time());

if (isset($_SESSION['rol'])) {
    if ($_SESSION['rol'] === 'adm') {
        header("Location: ../menu_adm.html");
    } elseif ($_SESSION['rol'] === 'usr') {
        header("Location: ../menu.html");
    } else {
        header("Location: ../menu.html");
    }
} else {
    die("Error: No se pudo determinar el rol del usuario.");
}

header("Location: ../bodegas/menu_bodegas.php?verificacion=ok");
exit();
?>