<?php
require '../../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

$archivo = isset($_GET['archivo']) ? $_GET['archivo'] : (isset($_POST['archivo']) ? $_POST['archivo'] : '');
$ruta_archivo = '../../archivos/generados/empaque/' . basename($archivo);

$mensaje = '';

// Puedes personalizar la columna donde inicia el registro (por ejemplo, columna A)
$columna_inicio = 'A'; // Cambia esto si quieres iniciar en otra columna

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $archivo && file_exists($ruta_archivo)) {
    $spreadsheet = IOFactory::load($ruta_archivo);
    $sheet = $spreadsheet->getActiveSheet();

    // Buscar la primera fila vacía a partir de la fila 2
    $fila = 9;
    while ($sheet->getCell($columna_inicio . $fila)->getValue() !== null && $sheet->getCell($columna_inicio . $fila)->getValue() !== '') {
        $fila++;
    }

    // Guardar los datos del formulario en la fila encontrada
    $sheet->setCellValue('A' . $fila, $_POST['fecha_alistamiento'] ?? '');
    $sheet->setCellValue('B' . $fila, $_POST['responsable_alistamiento'] ?? '');
    $sheet->setCellValue('C' . $fila, $_POST['producto_envasar'] ?? '');
    $sheet->setCellValue('E' . $fila, $_POST['lote_producto'] ?? '');
    $sheet->setCellValue('F' . $fila, $_POST['fecha_vencimiento'] ?? '');
    $sheet->setCellValue('G' . $fila, $_POST['etiquetas_adhesivas'] ?? '');
    $sheet->setCellValue('H' . $fila, $_POST['fecha_entrega_salida'] ?? '');
    $sheet->setCellValue('I' . $fila, $_POST['entregado_a'] ?? '');
    $sheet->setCellValue('J' . $fila, $_POST['cantidad_solicitada'] ?? '');
    $sheet->setCellValue('K' . $fila, $_POST['cantidad_devueltas'] ?? '');
    $sheet->setCellValue('L' . $fila, $_POST['cantidad_total_entregadas'] ?? '');
    $sheet->setCellValue('M' . $fila, $_POST['cantidad_no_conformes_fabrica'] ?? '');
    $sheet->setCellValue('N' . $fila, $_POST['cantidad_no_conformes_planta'] ?? '');

    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save($ruta_archivo);

    // Mensaje de éxito tipo "procesamiento_control.php"
    echo '
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <link rel="stylesheet" href="/css/empaque.css">
        <title>Registro Exitoso</title>
    </head>
    <body>
        <div class="mensaje-exito">
            <h2>¡Datos guardados exitosamente!</h2>
            <p>El registro ha sido añadido correctamente al archivo de empaque.</p>
            <a href="empaque_galerias.php" class="btn-volver">Volver a la galería de empaques</a>
        </div>
    </body>
    </html>
    ';
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/empaque.css">
    <title>CONTROL DE EMPAQUE</title>
</head>
<body>
    <h1 class="titulo-empaque">CONTROL DE ALISTAMIENTO DE MATERIALES DE EMPAQUE EN ALMACEN</h1>
    <form class="form-empaque" action="" method="post">
        <input type="hidden" name="archivo" value="<?php echo htmlspecialchars($archivo); ?>">
        <div>
            <label for="fecha_alistamiento">Fecha de Alistamiento</label>
            <input type="date" name="fecha_alistamiento" id="fecha_alistamiento">
        </div>
        <div>
            <label for="responsable_alistamiento">Responsable del Alistamiento</label>
            <input type="text" name="responsable_alistamiento" id="responsable_alistamiento">
        </div>
        <div>
            <label for="producto_envasar">Producto a Envasar</label>
            <input type="text" name="producto_envasar" id="producto_envasar">
        </div>
        <h2>TIMBRADO</h2>
        <div>
            <label for="lote_producto">Lote del Producto</label>
            <input type="text" name="lote_producto" id="lote_producto">
        </div>
        <div>
            <label for="fecha_vencimiento">Fecha de Vencimiento</label>
            <input type="text" name="fecha_vencimiento" id="fecha_vencimiento">
        </div>
        <div>
            <label for="etiquetas_adhesivas">Etiquetas Adhesivas</label>
            <select name="etiquetas_adhesivas" id="etiquetas_adhesivas">
                <option value="">---</option>
                <option value="SI">SI</option>
                <option value="NO">NO</option>
            </select>
        </div>
        <div>
            <label for="fecha_entrega_salida">Fecha de Entrega/Salida</label>
            <input type="date" name="fecha_entrega_salida" id="fecha_entrega_salida">
        </div>
        <div>
            <label for="entregado_a">Entregado A:</label>
            <input type="text" name="entregado_a" id="entregado_a">
        </div>
        <h2>NUMERO EMPAQUES</h2>
        <div>
            <label for="cantidad_solicitada">Solicitadas</label>
            <input type="text" name="cantidad_solicitada" id="cantidad_solicitada">
        </div>
        <div>
            <label for="cantidad_devueltas">Devueltas</label>
            <input type="number" name="cantidad_devueltas" id="cantidad_devueltas">
        </div>
        <div>
            <label for="cantidad_total_entregadas">Total Entregadas</label>
            <input type="number" name="cantidad_total_entregadas" id="cantidad_total_entregadas">
        </div>
        <div>
            <h2>NO CONFORMES</h2>
            <label for="cantidad_no_conformes_fabrica">De fabrica</label>
            <input type="number" name="cantidad_no_conformes_fabrica" id="cantidad_no_conformes_fabrica">
            <label for="cantidad_no_conformes_planta">En Planta</label>
            <input type="number" name="cantidad_no_conformes_planta" id="cantidad_no_conformes_planta">
        </div>
        <input type="submit" value="Guardar">
    </form>
</body>
</html>