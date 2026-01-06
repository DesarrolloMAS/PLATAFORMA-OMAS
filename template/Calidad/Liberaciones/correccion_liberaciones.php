<?php
require_once '/var/www/fmt/vendor/autoload.php';
require_once '../../sesion.php';
require '../../conection.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

// Función helper para sanitizar valores y evitar errores con null
function sanitizeValue($value) {
    if ($value === null || $value === false) {
        return '';
    }
    return (string)$value;
}

$sede = $_GET['sede'] ?? $_SESSION['sede'];
if ($sede === 'ZS') {
    $carpeta = '/var/www/fmt/archivos/generados/Calidad/liberaciones_zs';
} else {
    $carpeta = '/var/www/fmt/archivos/generados/Calidad/liberaciones';
}

if (isset($_GET['archivo'])) {
    $archivo = $_GET['archivo'];
    $rutaArchivo = $carpeta . DIRECTORY_SEPARATOR . $archivo;
    if (!file_exists($rutaArchivo)) {
        die("<div style='color:red;'>Error: El archivo no existe o la ruta es incorrecta: $rutaArchivo</div>");
    }

    try {
        $spreadsheet = IOFactory::load($rutaArchivo);
        $hoja = $spreadsheet->getActiveSheet();
        $filas = $hoja->toArray();

        // Obtener estilos de celdas (ancho de columnas y alto de filas)
        $colWidths = [];
        foreach ($hoja->getColumnDimensions() as $columna => $dimension) {
            $colWidths[$columna] = $dimension->getWidth();
        }

        $filasHeight = [];
        foreach ($hoja->getRowDimensions() as $fila => $dimension) {
            $filasHeight[$fila] = $dimension->getRowHeight();
        }

        // Carpeta para imágenes temporales
        $rutaImagenes = __DIR__ . "/imagenes_temporales/";
        if (!file_exists($rutaImagenes)) {
            mkdir($rutaImagenes, 0777, true);
        }

        // Obtener imágenes del archivo Excel
        $imagenes = [];
        foreach ($hoja->getDrawingCollection() as $drawing) {
            $imagenNombre = $drawing->getName() . ".png";
            $imagenRuta = $rutaImagenes . $imagenNombre;

            if ($drawing instanceof \PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing) {
                switch ($drawing->getMimeType()) {
                    case \PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing::MIMETYPE_PNG:
                        imagepng($drawing->getImageResource(), $imagenRuta);
                        break;
                    case \PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing::MIMETYPE_JPEG:
                        imagejpeg($drawing->getImageResource(), $imagenRuta);
                        break;
                    case \PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing::MIMETYPE_GIF:
                        imagegif($drawing->getImageResource(), $imagenRuta);
                        break;
                }
            } else {
                copy($drawing->getPath(), $imagenRuta);
            }

            $imagenes[$drawing->getCoordinates()] = "imagenes_temporales/" . $imagenNombre;
        }

        // Obtener celdas combinadas
        $mergedCells = [];
        foreach ($hoja->getMergeCells() as $mergedRange) {
            $mergedCells[$mergedRange] = true;
        }

        // Función para saber si una celda está dentro de un rango combinado
        function isCellInMergedRange($cell, $mergedCells) {
            foreach ($mergedCells as $range => $v) {
                [$start, $end] = explode(':', $range);
                $startCol = preg_replace('/[0-9]/', '', $start);
                $startRow = preg_replace('/[A-Z]/', '', $start);
                $endCol = preg_replace('/[0-9]/', '', $end);
                $endRow = preg_replace('/[A-Z]/', '', $end);

                $col = preg_replace('/[0-9]/', '', $cell);
                $row = preg_replace('/[A-Z]/', '', $cell);

                if (
                    $col >= $startCol && $col <= $endCol &&
                    $row >= $startRow && $row <= $endRow
                ) {
                    return $range;
                }
            }
            return false;
        }

    } catch (Exception $e) {
        die("Error al procesar el archivo: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Excel</title>
    <style>
    .table-container {
        max-height: 900px;
        overflow-y: auto;
        border: 1px solid #ccc;
        margin: 20px 0;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
    }
    table.excel-table {
        width: 100%;
        border-collapse: collapse;
        font-family: 'Segoe UI', Arial, sans-serif;
        font-size: 13px;
        background: #fff;
    }
    .excel-table td, .excel-table th {
        border: 1px solid #bdbdbd;
        padding: 4px 6px;
        min-width: 60px;
        height: 24px;
        text-align: center;
        vertical-align: middle;
    }
    .excel-table input[type="text"] {
        width: 100%;
        border: none;
        background: transparent;
        font-family: inherit;
        font-size: inherit;
        padding: 2px 4px;
        text-align: center;
    }
    .excel-table tr:nth-child(even) {
        background: #f6f8fa;
    }
    .excel-table tr:nth-child(odd) {
        background: #ffffff;
    }
    tr:hover {
        background-color: #f1f1f1;
    }
    </style>
</head>
<body>
<?php
if (!isset($filas)) {
    echo "<div style='color:red;'>No se cargaron datos. ¿Archivo vacío o error de lectura?</div>";
}
?>
<h2>📄 Editar Datos del Excel</h2>

<form action="liberaciones_correccion.php" method="post">
    <input type="hidden" name="archivo" value="<?php echo htmlspecialchars($archivo ?? '', ENT_QUOTES, 'UTF-8'); ?>">
    <input type="hidden" name="carpeta" value="<?php echo htmlspecialchars($carpeta ?? '', ENT_QUOTES, 'UTF-8'); ?>">
    <div class="table-container">
    <table class="excel-table">
        <?php
        // Buscar la fila de encabezados (donde está "Producto")
        $startRow = 0;
        foreach ($filas as $i => $fila) {
            if (isset($fila[0]) && trim($fila[0]) === 'Producto') {
                $startRow = $i;
                break;
            }
        }
        $headers = isset($filas[$startRow]) ? $filas[$startRow] : [];
// Buscar el máximo de columnas con datos desde la fila de encabezados hasta el final
$colCount = count($headers);
for ($r = $startRow + 1; $r < count($filas); $r++) {
    $colCount = max($colCount, count($filas[$r]));
}

        if ($colCount === 0) {
            echo "<tr><td colspan='10' style='color:red;font-weight:bold;'>No se encontraron encabezados de tabla.</td></tr>";
        } else {
            // Imprimir encabezados
            echo "<tr>";
            for ($c = 0; $c < $colCount; $c++) {
                echo "<th>" . htmlspecialchars(sanitizeValue($headers[$c]), ENT_QUOTES, 'UTF-8') . "</th>";
            }
            echo "</tr>";

            // Imprimir filas de datos
            for ($r = $startRow + 1; $r < count($filas); $r++) {
                $fila = $filas[$r];
                // Saltar filas vacías
                if (count(array_filter($fila, fn($v) => $v !== null && $v !== '')) === 0) continue;
                echo "<tr>";
                for ($c = 0; $c < $colCount; $c++) {
                    $valor = isset($fila[$c]) ? $fila[$c] : '';
                    echo "<td><input type='text' name='data[$r][$c]' value='" . htmlspecialchars(sanitizeValue($valor), ENT_QUOTES, 'UTF-8') . "'></td>";
                }
                echo "</tr>";
            }
        }
        ?>
    </table>
     </div>
    <br>
    <button type="submit">✅ Guardar Cambios</button>
</form>

</body>
</html>