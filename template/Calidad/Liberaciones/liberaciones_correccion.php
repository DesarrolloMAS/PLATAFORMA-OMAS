<?php
require_once '/var/www/fmt/vendor/autoload.php';
require_once '../../sesion.php';
require '../../conection.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Mpdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

// Función para sanitizar valores antes de procesarlos
function sanitizeValue($value) {
    if ($value === null || $value === false) {
        return '';
    }
    return $value;
}

$sede = $_POST['sede'] ?? $_SESSION['sede'];
if($sede === 'ZS'){
    $carpeta = '/var/www/fmt/archivos/generados/Calidad/liberaciones_zs';
} else {
    $carpeta = '/var/www/fmt/archivos/generados/Calidad/liberaciones';
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['archivo']) && isset($_POST['data'])) {
    $archivo = $_POST['archivo'];
    $rutaArchivo = $carpeta . DIRECTORY_SEPARATOR . $archivo;

    if (!file_exists($rutaArchivo)) {
        die("Error: El archivo no existe en la ruta: " . $rutaArchivo);
    }

    try {
        // 🔧 CARGAR EL ARCHIVO EXCEL (esto faltaba)
        $spreadsheet = IOFactory::load($rutaArchivo);
        $hoja = $spreadsheet->getActiveSheet();

        // 🔧 OBTENER CELDAS CON FÓRMULAS PARA CONSERVARLAS
        $celdasConFormula = [];
        foreach ($hoja->getRowIterator() as $fila) {
            foreach ($fila->getCellIterator() as $celda) {
                if ($celda->getDataType() === \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_FORMULA) {
                    $celdasConFormula[] = $celda->getCoordinate();
                }
            }
        }

        // 🔧 PROCESAR LOS DATOS DEL FORMULARIO (esto también faltaba)
        foreach ($_POST['data'] as $filaIndex => $fila) {
            foreach ($fila as $colIndex => $valorCelda) {
                // Sanitizar el valor
                $valorCelda = sanitizeValue($valorCelda);
                
                // Convertir índices numéricos a referencia de celda (A1, B2, etc.)
                $letraCol = Coordinate::stringFromColumnIndex($colIndex + 1);
                $celdaRef = $letraCol . ($filaIndex + 1);

                // Solo modificar si la celda NO tiene fórmula
                if (!in_array($celdaRef, $celdasConFormula)) {
                    $hoja->setCellValue($celdaRef, $valorCelda);
                }
            }
        }

        // 🔧 AHORA SÍ GUARDAR EL ARCHIVO
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($rutaArchivo);

        // 🔧 REDIRIGIR CON LA SEDE CORRECTA
        header("Location: liberaciones_visualizacion.php?archivo=" . urlencode($archivo) . "&sede=" . urlencode($sede));
        exit();

    } catch (Exception $e) {
        die("Error al guardar los cambios: " . $e->getMessage());
    }
} else {
    // Si no hay datos POST, mostrar error
    die("Error: No se recibieron datos para procesar.");
}
?>