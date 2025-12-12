<?php
require '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

// Depuración: log de inicio de script
file_put_contents('debug_guardado.txt', date('Y-m-d H:i:s') . " | Script iniciado\n", FILE_APPEND);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    file_put_contents('debug_guardado.txt', date('Y-m-d H:i:s') . " | POST recibido\n", FILE_APPEND);

    // Recoge los datos del formulario
    $fecha = $_POST['fecha'] ?? '';
    $lider = $_POST['lider'] ?? '';
    $moje = $_POST['moje'] ?? '';
    $producto = $_POST['producto'] ?? '';
    $referencia = $_POST['referencia'] ?? '';
    $hora_inicio = $_POST['hora_inicio'] ?? '';
    $hora_final = $_POST['hora_final'] ?? '';
    $bascula = $_POST['bascula'] ?? '';
    $bascula_harina = $_POST['bascula_harina'] ?? '';
    $bultos_harina = $_POST['bultos_harina'] ?? '';
    $lote_harina = $_POST['lote_harina'] ?? '';
    $cantidad_kg = $_POST['cantidad_kg'] ?? '';
    $silo_granel = $_POST['silo_granel'] ?? '';

    // Subproductos
    $bultos_mogolla = $_POST['bultos_mogolla'] ?? '-';
    $hilo_mogolla = $_POST['hilo_mogolla'] ?? '-';
    $bultos_salvado = $_POST['bultos_salvado'] ?? '-';
    $hilo_salvado = $_POST['hilo_salvado'] ?? '-';
    $bultos_segunda = $_POST['bultos_segunda'] ?? '-';
    $hilo_segunda = $_POST['hilo_segunda'] ?? '-';
    $bultos_germen = $_POST['bultos_germen'] ?? '-';
    $hilo_germen = $_POST['hilo_germen'] ?? '-';
    $cantidad_granza = $_POST['cantidad_granza'] ?? '-';
    $varadas = $_POST['varadas'] ?? '-';

    // Depuración: log de datos recibidos
    // Carpeta y archivo destino
    $carpetaDestino = "../../archivos/generados/proceso_molienda";
    if (!is_dir($carpetaDestino)) {
        if (!mkdir($carpetaDestino, 0777, true)) {
            file_put_contents('debug_guardado.txt', date('Y-m-d H:i:s') . " | ERROR: No se pudo crear la carpeta de destino\n", FILE_APPEND);
            mostrarError("No se pudo crear la carpeta de destino.");
        } else {
            file_put_contents('debug_guardado.txt', date('Y-m-d H:i:s') . " | Carpeta de destino creada\n", FILE_APPEND);
        }
    }

    // --- Manejo de archivo secuencial ---
    // Buscar el archivo más reciente
    $secuencial = 1;
    $nombreArchivoBase = "proceso_molienda";
    $extensionArchivo = ".xlsx";
    $nombreArchivo = $nombreArchivoBase . $extensionArchivo;
    $rutaArchivo = $carpetaDestino . "/" . $nombreArchivo;

    // Buscar el archivo con mayor secuencial existente
    $archivos = glob($carpetaDestino . "/proceso_molienda*.xlsx");
    if ($archivos) {
        $mayorSec = 1;
        foreach ($archivos as $archivo) {
            if (preg_match('/proceso_molienda(?:_(\d+))?\.xlsx$/', basename($archivo), $matches)) {
                $num = isset($matches[1]) && $matches[1] !== '' ? intval($matches[1]) : 1;
                if ($num > $mayorSec) {
                    $mayorSec = $num;
                    $nombreArchivo = basename($archivo);
                    $rutaArchivo = $archivo;
                }
            }
        }
        $secuencial = $mayorSec;
    }

    // Si el archivo existe, cargarlo, si no, usar la plantilla
    if (file_exists($rutaArchivo)) {
        try {
            $spreadsheet = IOFactory::load($rutaArchivo);
            file_put_contents('debug_guardado.txt', date('Y-m-d H:i:s') . " | Archivo existente cargado: $nombreArchivo\n", FILE_APPEND);
        } catch (Exception $e) {
            file_put_contents('debug_guardado.txt', date('Y-m-d H:i:s') . " | ERROR: No se pudo abrir el archivo de destino: " . $e->getMessage() . "\n", FILE_APPEND);
            mostrarError("No se pudo abrir el archivo de destino: " . $e->getMessage());
        }
    } else {
        $plantilla = "../../archivos/formularios/formulario8.xlsx";
        if (!file_exists($plantilla)) {
            file_put_contents('debug_guardado.txt', date('Y-m-d H:i:s') . " | ERROR: No se encontró la plantilla base\n", FILE_APPEND);
            mostrarError("No se encontró la plantilla base.");
        }
        try {
            $spreadsheet = IOFactory::load($plantilla);
            file_put_contents('debug_guardado.txt', date('Y-m-d H:i:s') . " | Plantilla base cargada\n", FILE_APPEND);
        } catch (Exception $e) {
            file_put_contents('debug_guardado.txt', date('Y-m-d H:i:s') . " | ERROR: No se pudo abrir la plantilla base: " . $e->getMessage() . "\n", FILE_APPEND);
            mostrarError("No se pudo abrir la plantilla base: " . $e->getMessage());
        }
    }

    $sheet = $spreadsheet->getActiveSheet();

    // --- Buscar la primera fila vacía para productos de harina normal (tabla 1) ---
    $fila_harina = 9;
    $contador_harina = 0;
    $limite_filas_harina = 36;
    while (
        $sheet->getCell("A" . $fila_harina)->getValue() !== null &&
        $sheet->getCell("A" . $fila_harina)->getValue() !== ''
    ) {
        $fila_harina++;
        $contador_harina++;
        if ($contador_harina > 1000) { // Previene bucle infinito
            file_put_contents('debug_guardado.txt', date('Y-m-d H:i:s') . " | ERROR: Bucle infinito buscando fila_harina\n", FILE_APPEND);
            mostrarError("Error buscando fila vacía para harina.");
        }
    }

    // --- Si la fila de harina supera el límite, crear un nuevo archivo secuencial y reiniciar la fila ---
    if ($fila_harina > $limite_filas_harina) {
        // Buscar el siguiente secuencial disponible
        do {
            $secuencial++;
            $nombreArchivoNuevo = $nombreArchivoBase . '_' . $secuencial . $extensionArchivo;
            $rutaArchivoNuevo = $carpetaDestino . "/" . $nombreArchivoNuevo;
        } while (file_exists($rutaArchivoNuevo));

        // Usar la plantilla para el nuevo archivo
        $plantilla = "../../archivos/formularios/formulario8.xlsx";
        if (!file_exists($plantilla)) {
            file_put_contents('debug_guardado.txt', date('Y-m-d H:i:s') . " | ERROR: No se encontró la plantilla base para nuevo archivo\n", FILE_APPEND);
            mostrarError("No se encontró la plantilla base para nuevo archivo.");
        }
        try {
            $spreadsheet = IOFactory::load($plantilla);
            file_put_contents('debug_guardado.txt', date('Y-m-d H:i:s') . " | Nuevo archivo de molienda creado: $nombreArchivoNuevo\n", FILE_APPEND);
        } catch (Exception $e) {
            file_put_contents('debug_guardado.txt', date('Y-m-d H:i:s') . " | ERROR: No se pudo abrir la plantilla base para nuevo archivo: " . $e->getMessage() . "\n", FILE_APPEND);
            mostrarError("No se pudo abrir la plantilla base para nuevo archivo: " . $e->getMessage());
        }
        $sheet = $spreadsheet->getActiveSheet();
        $rutaArchivo = $rutaArchivoNuevo;
        $nombreArchivo = $nombreArchivoNuevo;
        $fila_harina = 9; // Reinicia la fila para el nuevo archivo
        $fila_subproductos = 54; // También reinicia subproductos para el nuevo archivo
    }

    // --- Buscar la primera fila vacía para subproductos (tabla 2) ---
    $fila_subproductos = 54;
    $contador_sub = 0;
    while (true) {
        $vacia = true;
        foreach (range('A', 'K') as $col) {
            $valor = $sheet->getCell($col . $fila_subproductos)->getValue();
            if ($valor !== null && $valor !== '') {
                $vacia = false;
                break;
            }
        }
        if ($vacia) break;
        $fila_subproductos++;
        $contador_sub++;
        if ($contador_sub > 1000) { // Previene bucle infinito
            file_put_contents('debug_guardado.txt', date('Y-m-d H:i:s') . " | ERROR: Bucle infinito buscando fila_subproductos\n", FILE_APPEND);
            mostrarError("Error buscando fila vacía para subproductos.");
        }
    }
    file_put_contents('debug_guardado.txt', date('Y-m-d H:i:s') . " | Fila subproductos encontrada: $fila_subproductos\n", FILE_APPEND);
    file_put_contents('debug_guardado.txt', date('Y-m-d H:i:s') . " | Fila harina encontrada: $fila_harina\n", FILE_APPEND);
    file_put_contents('debug_guardado.txt', date('Y-m-d H:i:s') . " | Datos recibidos: fecha=$fecha, lider=$lider, moje=$moje, producto=$producto, referencia=$referencia, hora_inicio=$hora_inicio, hora_final=$hora_final, bascula=$bascula, bascula_harina=$bascula_harina, bultos_harina=$bultos_harina, lote_harina=$lote_harina, cantidad_kg=$cantidad_kg, silo_granel=$silo_granel, bultos_mogolla=$bultos_mogolla, hilo_mogolla=$hilo_mogolla, bultos_salvado=$bultos_salvado, hilo_salvado=$hilo_salvado, bultos_segunda=$bultos_segunda, hilo_segunda=$hilo_segunda, bultos_germen=$bultos_germen, hilo_germen=$hilo_germen, cantidad_granza=$cantidad_granza, varadas=$varadas\n", FILE_APPEND);
    file_put_contents('debug_guardado.txt', date('Y-m-d H:i:s') . " | Fila harina: $fila_harina, Fila subproductos: $fila_subproductos\n", FILE_APPEND);

    // Asigna los valores a las celdas de la tabla de harina normal
    $sheet->setCellValue('A' . $fila_harina, $fecha);
    $sheet->setCellValue('B' . $fila_harina, $lider);
    $sheet->setCellValue('C' . $fila_harina, $moje);
    $sheet->setCellValue('D' . $fila_harina, $producto);
    $sheet->setCellValue('E' . $fila_harina, $referencia);
    $sheet->setCellValue('F' . $fila_harina, $hora_inicio);
    $sheet->setCellValue('G' . $fila_harina, $hora_final);
    $sheet->setCellValue('H' . $fila_harina, $bascula);
    $sheet->setCellValue('I' . $fila_harina, $bascula_harina);
    $sheet->setCellValue('J' . $fila_harina, $bultos_harina);
    $sheet->setCellValue('K' . $fila_harina, $lote_harina);
    $sheet->setCellValue('L' . $fila_harina, $cantidad_kg);
    $sheet->setCellValue('M' . $fila_harina, $silo_granel);

    // Asigna los valores a las celdas de la tabla de subproductos
    $sheet->setCellValue('A' . $fila_subproductos, $fecha);
    $sheet->setCellValue('B' . $fila_subproductos, $bultos_mogolla);
    $sheet->setCellValue('C' . $fila_subproductos, $hilo_mogolla);
    $sheet->setCellValue('D' . $fila_subproductos, $bultos_salvado);
    $sheet->setCellValue('E' . $fila_subproductos, $hilo_salvado);
    $sheet->setCellValue('F' . $fila_subproductos, $bultos_segunda);
    $sheet->setCellValue('G' . $fila_subproductos, $hilo_segunda);
    $sheet->setCellValue('H' . $fila_subproductos, $bultos_germen);
    $sheet->setCellValue('I' . $fila_subproductos, $hilo_germen);
    $sheet->setCellValue('J' . $fila_subproductos, $cantidad_granza);
    $sheet->setCellValue('K' . $fila_subproductos, $varadas);

    // Depuración: log antes de guardar
    file_put_contents('debug_guardado.txt', date('Y-m-d H:i:s') . " | Guardando archivo en $rutaArchivo\n", FILE_APPEND);

    // Guarda el archivo
    try {
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($rutaArchivo);
        file_put_contents('debug_guardado.txt', date('Y-m-d H:i:s') . " | Archivo guardado correctamente\n", FILE_APPEND);
    } catch (Exception $e) {
        file_put_contents('debug_guardado.txt', date('Y-m-d H:i:s') . " | ERROR: No se pudo guardar el archivo: " . $e->getMessage() . "\n", FILE_APPEND);
        mostrarError("No se pudo guardar el archivo: " . $e->getMessage());
    }

    // Mensaje final con botón para volver
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
            <p>El registro de control de molienda ha sido añadido correctamente.</p>
            <a href="../redireccion.php" class="btn-volver">Volver al Menu</a>
        </div>
    </body>
    </html>
    ';
    exit();
}

// Función para mostrar errores y terminar la ejecución
function mostrarError($mensaje) {
    echo '
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Error al guardar</title>
        <style>
            body { font-family: Arial, sans-serif; background: #f4f4f4; }
            .mensaje-error {
                background: #fff;
                margin: 80px auto;
                padding: 40px 30px;
                border-radius: 10px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                max-width: 400px;
                text-align: center;
            }
            .mensaje-error h2 { color: #dc3545; }
            .mensaje-error p { margin: 20px 0; }
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
        <div class="mensaje-error">
            <h2>¡Error!</h2>
            <p>' . htmlspecialchars($mensaje) . '</p>
            <a href="../redireccion.php" class="btn-volver">Volver al Menu</a>
        </div>
    </body>
    </html>
    ';
    exit();
}
?>