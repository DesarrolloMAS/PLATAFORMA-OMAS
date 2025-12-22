<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once '/var/www/fmt/vendor/autoload.php';
require_once '../../sesion.php';
require '../../conection.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

verificarAutenticacion(); // Verifica que el usuario esté autenticado

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ruta de la plantilla base
    $baseExcelPath = "/var/www/fmt/archivos/formularios/calidad/formulario_liberaciones.xlsx";

    if (!file_exists($baseExcelPath)) {
        die("Error: No se encontró la plantilla base.");
    }

    // Cargar la plantilla de Excel
    $spreadsheet = IOFactory::load($baseExcelPath);
    $sheet = $spreadsheet->getActiveSheet();

    // Rellenar datos generales
    $sheet->setCellValue('D5', $_POST['fecha'] ?? ''); // Fecha de producción
    $sheet->setCellValue('I5', $_POST['planta'] ?? ''); // Planta

    // Procesar datos de Harina Extrapan
    $sheet->setCellValue('B8', $_POST['referencia_extrapan'] ?? ''); // Referencia
    $sheet->setCellValue('C8', $_POST['lote_extrapan'] ?? ''); // Lote
    $sheet->setCellValue('D8', $_POST['cantidad_extrapan'] ?? ''); // Cantidad Liberada
    $sheet->setCellValue('E8', $_POST['bitacora1'] ?? ''); // Cumple con especificaciones de FT
    $sheet->setCellValue('F8', $_POST['panificaciona1'] ?? ''); // Cumple análisis de panificación
    $sheet->setCellValue('G8', $_POST['laboratorios_ex1'] ?? ''); // Cumple análisis de laboratorios externos
    $sheet->setCellValue('H8', $_POST['fortificacion1'] ?? ''); // Cumple adición de fortificación
    $sheet->setCellValue('I8', $_POST['mejorantes1'] ?? ''); // Cumple con adición de mejorantes
    $sheet->setCellValue('J8', $_POST['empaque1'] ?? ''); // Cumple condiciones y empaque
    $sheet->setCellValue('K8', $_POST['certificado1'] ?? ''); // Cumple certificado de calidad

    // Procesar datos de Harina Alta Proteína
    $sheet->setCellValue('B12', $_POST['referencia_proteina2'] ?? ''); // Referencia
    $sheet->setCellValue('C12', $_POST['lote2'] ?? ''); // Lote
    $sheet->setCellValue('D12', $_POST['cantidad2'] ?? ''); // Cantidad Liberada
    $sheet->setCellValue('E12', $_POST['bitacora2'] ?? ''); // Cumple con especificaciones de FT
    $sheet->setCellValue('F12', $_POST['panificaciona2'] ?? ''); // Cumple análisis de panificación
    $sheet->setCellValue('G12', $_POST['laboratorios2'] ?? ''); // Cumple análisis de laboratorios externos
    $sheet->setCellValue('H12', $_POST['fortificacion2'] ?? ''); // Cumple adición de fortificación
    $sheet->setCellValue('I12', $_POST['mejorantes2'] ?? ''); // Cumple con adición de mejorantes
    $sheet->setCellValue('J12', $_POST['empaque2'] ?? ''); // Cumple condiciones y empaque
    $sheet->setCellValue('K12', $_POST['certificado2'] ?? ''); // Cumple certificado de calidad

    // Procesar datos de Harina Artesanal
    $sheet->setCellValue('B16', $_POST['referencia_artesanal'] ?? ''); // Referencia
    $sheet->setCellValue('C16', $_POST['lote3'] ?? ''); // Lote
    $sheet->setCellValue('D16', $_POST['cantidad3'] ?? ''); // Cantidad Liberada
    $sheet->setCellValue('E16', $_POST['bitacora3'] ?? ''); // Cumple con especificaciones de FT
    $sheet->setCellValue('F16', $_POST['panificaciona3'] ?? ''); // Cumple análisis de panificación
    $sheet->setCellValue('G16', $_POST['laboratorios3'] ?? ''); // Cumple análisis de laboratorios externos
    $sheet->setCellValue('H16', $_POST['fortificacion3'] ?? ''); // Cumple adición de fortificación
    $sheet->setCellValue('I16', $_POST['mejorantes3'] ?? ''); // Cumple con adición de mejorantes
    $sheet->setCellValue('J16', $_POST['empaque3'] ?? ''); // Cumple condiciones y empaque
    $sheet->setCellValue('K16', $_POST['certificado3'] ?? ''); // Cumple certificado de calidad


// Procesar datos de Harina Artesanal (Harina 3)
$sheet->setCellValue('B16', $_POST['referencia_artesanal'] ?? ''); // Referencia
$sheet->setCellValue('C16', $_POST['lote3'] ?? ''); // Lote
$sheet->setCellValue('D16', $_POST['cantidad3'] ?? ''); // Cantidad Liberada
$sheet->setCellValue('E16', $_POST['bitacora3'] ?? ''); // Cumple con especificaciones de FT
$sheet->setCellValue('F16', $_POST['panificaciona3'] ?? ''); // Cumple análisis de panificación
$sheet->setCellValue('G16', $_POST['laboratorios3'] ?? ''); // Cumple análisis de laboratorios externos
$sheet->setCellValue('H16', $_POST['fortificacion3'] ?? ''); // Cumple adición de fortificación
$sheet->setCellValue('I16', $_POST['mejorantes3'] ?? ''); // Cumple con adición de mejorantes
$sheet->setCellValue('J16', $_POST['empaque3'] ?? ''); // Cumple condiciones y empaque
$sheet->setCellValue('K16', $_POST['certificado3'] ?? ''); // Cumple certificado de calidad

// Procesar datos de Harina Exclusiva (Harina 4)
$sheet->setCellValue('B20', $_POST['referencia4'] ?? ''); // Referencia
$sheet->setCellValue('C20', $_POST['lote4'] ?? ''); // Lote
$sheet->setCellValue('D20', $_POST['cantidad4'] ?? ''); // Cantidad Liberada
$sheet->setCellValue('E20', $_POST['bitacora4'] ?? ''); // Cumple con especificaciones de FT
$sheet->setCellValue('F20', $_POST['panificacion4'] ?? ''); // Cumple análisis de panificación
$sheet->setCellValue('G20', $_POST['laboratorios4'] ?? ''); // Cumple análisis de laboratorios externos
$sheet->setCellValue('H20', $_POST['fortificacion4'] ?? ''); // Cumple adición de fortificación
$sheet->setCellValue('I20', $_POST['mejorantes4'] ?? ''); // Cumple con adición de mejorantes
$sheet->setCellValue('J20', $_POST['empaque4'] ?? ''); // Cumple condiciones y empaque
$sheet->setCellValue('K20', $_POST['certificado4'] ?? ''); // Cumple certificado de calidad

// Procesar datos de Harina Integral (Harina 5)
$sheet->setCellValue('B24', $_POST['referencia5'] ?? ''); // Referencia
$sheet->setCellValue('C24', $_POST['lote5'] ?? ''); // Lote
$sheet->setCellValue('D24', $_POST['cantidad5'] ?? ''); // Cantidad Liberada
$sheet->setCellValue('E24', $_POST['bitacora5'] ?? ''); // Cumple con especificaciones de FT
$sheet->setCellValue('F24', $_POST['panificacion5'] ?? ''); // Cumple análisis de panificación
$sheet->setCellValue('G24', $_POST['laboratorios5'] ?? ''); // Cumple análisis de laboratorios externos
$sheet->setCellValue('H24', $_POST['fortificacion5'] ?? ''); // Cumple adición de fortificación
$sheet->setCellValue('I24', $_POST['mejorantes5'] ?? ''); // Cumple con adición de mejorantes
$sheet->setCellValue('J24', $_POST['empaque5'] ?? ''); // Cumple condiciones y empaque
$sheet->setCellValue('K24', $_POST['certificado5'] ?? ''); // Cumple certificado de calidad

// Procesar datos de Harina Fuerte (Harina 6)
$sheet->setCellValue('B28', $_POST['referencia6'] ?? ''); // Referencia
$sheet->setCellValue('C28', $_POST['lote6'] ?? ''); // Lote
$sheet->setCellValue('D28', $_POST['cantidad6'] ?? ''); // Cantidad Liberada
$sheet->setCellValue('E28', $_POST['bitacora6'] ?? ''); // Cumple con especificaciones de FT
$sheet->setCellValue('F28', $_POST['panificacion6'] ?? ''); // Cumple análisis de panificación
$sheet->setCellValue('G28', $_POST['laboratorios6'] ?? ''); // Cumple análisis de laboratorios externos
$sheet->setCellValue('H28', $_POST['fortificacion6'] ?? ''); // Cumple adición de fortificación
$sheet->setCellValue('I28', $_POST['mejorantes6'] ?? ''); // Cumple con adición de mejorantes
$sheet->setCellValue('J28', $_POST['empaque6'] ?? ''); // Cumple condiciones y empaque
$sheet->setCellValue('K28', $_POST['certificado6'] ?? ''); // Cumple certificado de calidad

// Procesar datos de Harina Especial (Harina 7)
$sheet->setCellValue('B32', $_POST['referencia7'] ?? ''); // Referencia
$sheet->setCellValue('C32', $_POST['lote7'] ?? ''); // Lote
$sheet->setCellValue('D32', $_POST['cantidad7'] ?? ''); // Cantidad Liberada
$sheet->setCellValue('E32', $_POST['bitacora7'] ?? ''); // Cumple con especificaciones de FT
$sheet->setCellValue('F32', $_POST['panificacion7'] ?? ''); // Cumple análisis de panificación
$sheet->setCellValue('G32', $_POST['laboratorios7'] ?? ''); // Cumple análisis de laboratorios externos
$sheet->setCellValue('H32', $_POST['fortificacion7'] ?? ''); // Cumple adición de fortificación
$sheet->setCellValue('I32', $_POST['mejorantes7'] ?? ''); // Cumple con adición de mejorantes
$sheet->setCellValue('J32', $_POST['empaque7'] ?? ''); // Cumple condiciones y empaque
$sheet->setCellValue('K32', $_POST['certificado7'] ?? ''); // Cumple certificado de calidad

// Procesar datos de Mogolla (Harina 8)
$sheet->setCellValue('B36', $_POST['referencia8'] ?? ''); // Referencia
$sheet->setCellValue('C36', $_POST['lote8'] ?? ''); // Lote
$sheet->setCellValue('D36', $_POST['cantidad8'] ?? ''); // Cantidad Liberada
$sheet->setCellValue('E36', $_POST['bitacora8'] ?? ''); // Cumple con especificaciones de FT
$sheet->setCellValue('F36', $_POST['panificacion8'] ?? ''); // Cumple análisis de panificación
$sheet->setCellValue('G36', $_POST['laboratorios8'] ?? ''); // Cumple análisis de laboratorios externos
$sheet->setCellValue('H36', $_POST['fortificacion8'] ?? ''); // Cumple adición de fortificación
$sheet->setCellValue('I36', $_POST['mejorantes8'] ?? ''); // Cumple con adición de mejorantes
$sheet->setCellValue('J36', $_POST['empaque8'] ?? ''); // Cumple condiciones y empaque
$sheet->setCellValue('K36', $_POST['certificado8'] ?? ''); // Cumple certificado de calidad

// Procesar datos de Salvado (Harina 9)
$sheet->setCellValue('B40', $_POST['referencia9'] ?? ''); // Referencia
$sheet->setCellValue('C40', $_POST['lote9'] ?? ''); // Lote
$sheet->setCellValue('D40', $_POST['cantidad9'] ?? ''); // Cantidad Liberada
$sheet->setCellValue('E40', $_POST['bitacora9'] ?? ''); // Cumple con especificaciones de FT
$sheet->setCellValue('F40', $_POST['panificacion9'] ?? ''); // Cumple análisis de panificación
$sheet->setCellValue('G40', $_POST['laboratorios9'] ?? ''); // Cumple análisis de laboratorios externos
$sheet->setCellValue('H40', $_POST['fortificacion9'] ?? ''); // Cumple adición de fortificación
$sheet->setCellValue('I40', $_POST['mejorantes9'] ?? ''); // Cumple con adición de mejorantes
$sheet->setCellValue('J40', $_POST['empaque9'] ?? ''); // Cumple condiciones y empaque
$sheet->setCellValue('K40', $_POST['certificado9'] ?? ''); // Cumple certificado de calidad

// Procesar datos de Segunda (Harina 10)
$sheet->setCellValue('B44', $_POST['referencia10'] ?? ''); // Referencia
$sheet->setCellValue('C44', $_POST['lote10'] ?? ''); // Lote
$sheet->setCellValue('D44', $_POST['cantidad10'] ?? ''); // Cantidad Liberada
$sheet->setCellValue('E44', $_POST['bitacora10'] ?? ''); // Cumple con especificaciones de FT
$sheet->setCellValue('F44', $_POST['panificacion10'] ?? ''); // Cumple análisis de panificación
$sheet->setCellValue('G44', $_POST['laboratorios10'] ?? ''); // Cumple análisis de laboratorios externos
$sheet->setCellValue('H44', $_POST['fortificacion10'] ?? ''); // Cumple adición de fortificación
$sheet->setCellValue('I44', $_POST['mejorantes10'] ?? ''); // Cumple con adición de mejorantes
$sheet->setCellValue('J44', $_POST['empaque10'] ?? ''); // Cumple condiciones y empaque
$sheet->setCellValue('K44', $_POST['certificado10'] ?? ''); // Cumple certificado de calidad

// Procesar datos de Germen (Harina 11)
$sheet->setCellValue('B48', $_POST['referencia11'] ?? ''); // Referencia
$sheet->setCellValue('C48', $_POST['lote11'] ?? ''); // Lote
$sheet->setCellValue('D48', $_POST['cantidad11'] ?? ''); // Cantidad Liberada
$sheet->setCellValue('E48', $_POST['bitacora11'] ?? ''); // Cumple con especificaciones de FT
$sheet->setCellValue('F48', $_POST['panificacion11'] ?? ''); // Cumple análisis de panificación
$sheet->setCellValue('G48', $_POST['laboratorios11'] ?? ''); // Cumple análisis de laboratorios externos
$sheet->setCellValue('H48', $_POST['fortificacion11'] ?? ''); // Cumple adición de fortificación
$sheet->setCellValue('I48', $_POST['mejorantes11'] ?? ''); // Cumple con adición de mejorantes
$sheet->setCellValue('J48', $_POST['empaque11'] ?? ''); // Cumple condiciones y empaque
$sheet->setCellValue('K48', $_POST['certificado11'] ?? ''); // Cumple certificado de calidad

    // Procesar lotes extras de cada harina
    $filasHarinas = [
        'harina1' => 9,  // Harina Extrapan
        'harina2' => 13, // Harina Alta Proteína
        'harina3' => 17, // Harina Artesanal
        'harina4' => 21, // Harina Exclusiva
        'harina5' => 25, // Harina Integral
        'harina6' => 29, // Harina Fuerte
        'harina7' => 33, // Harina Especial
        'harina8' => 37, // Mogolla
        'harina9' => 41, // Salvado
        'harina10' => 45, // Segunda
        'harina11' => 49, // Germen
    ];

    foreach ($filasHarinas as $harinaId => $filaInicial) {
        if (isset($_POST[$harinaId]) && is_array($_POST[$harinaId])) {
            $contadorFila = 0; // Contador para las filas dentro del rango de 3

            foreach ($_POST[$harinaId] as $loteKey => $loteData) {
                // Verificar que no exceda las 3 filas permitidas
                if ($contadorFila >= 3) {
                    break; // Salir si ya se llenaron las 3 filas
                }

                // Calcular la fila actual
                $filaActual = $filaInicial + $contadorFila;

                // Asignar los datos del lote a las celdas correspondientes
                $sheet->setCellValue("B{$filaActual}", $loteData['referencia'] ?? ''); // Referencia
                $sheet->setCellValue("C{$filaActual}", $loteData['lote'] ?? ''); // Lote
                $sheet->setCellValue("D{$filaActual}", $loteData['cantidad'] ?? ''); // Cantidad Liberada
                $sheet->setCellValue("E{$filaActual}", $loteData['bitacora'] ?? ''); // Cumple con especificaciones de FT
                $sheet->setCellValue("F{$filaActual}", $loteData['panificacion'] ?? ''); // Cumple análisis de panificación
                $sheet->setCellValue("G{$filaActual}", $loteData['laboratorios'] ?? ''); // Cumple análisis de laboratorios externos
                $sheet->setCellValue("H{$filaActual}", $loteData['fortificacion'] ?? ''); // Cumple adición de fortificación
                $sheet->setCellValue("I{$filaActual}", $loteData['mejorantes'] ?? ''); // Cumple con adición de mejorantes
                $sheet->setCellValue("J{$filaActual}", $loteData['empaque'] ?? ''); // Cumple condiciones y empaque
                $sheet->setCellValue("K{$filaActual}", $loteData['certificado'] ?? ''); // Cumple certificado de calidad

                $contadorFila++; // Avanzar a la siguiente fila
            }
        }
    }
// Procesar datos de Harinas Extras
if (isset($_POST['harina_extra']) && is_array($_POST['harina_extra'])) {
    $filaExtra = 52; // Fila inicial para las harinas extras (ajústala según tu plantilla)

    $contadorFila = 0; // Contador para limitar a un máximo de 4 filas
    foreach ($_POST['harina_extra'] as $loteKey => $loteData) {
        // Verificar que no exceda las 4 filas permitidas
        if ($contadorFila >= 4) {
            break; // Salir si ya se llenaron las 4 filas
        }

        // Asignar datos del lote extra a las celdas correspondientes
        $sheet->setCellValue("A{$filaExtra}", $loteData['nombre_harina'] ?? ''); // Nombre de la harina
        $sheet->setCellValue("B{$filaExtra}", $loteData['referencia'] ?? ''); // Referencia
        $sheet->setCellValue("C{$filaExtra}", $loteData['lote'] ?? ''); // Lote
        $sheet->setCellValue("D{$filaExtra}", $loteData['cantidad'] ?? ''); // Cantidad Liberada
        $sheet->setCellValue("E{$filaExtra}", $loteData['bitacora'] ?? ''); // Cumple con especificaciones de FT
        $sheet->setCellValue("F{$filaExtra}", $loteData['panificacion'] ?? ''); // Cumple análisis de panificación
        $sheet->setCellValue("G{$filaExtra}", $loteData['laboratorios'] ?? ''); // Cumple análisis de laboratorios externos
        $sheet->setCellValue("H{$filaExtra}", $loteData['fortificacion'] ?? ''); // Cumple adición de fortificación
        $sheet->setCellValue("I{$filaExtra}", $loteData['mejorantes'] ?? ''); // Cumple con adición de mejorantes
        $sheet->setCellValue("J{$filaExtra}", $loteData['empaque'] ?? ''); // Cumple condiciones y empaque
        $sheet->setCellValue("K{$filaExtra}", $loteData['certificado'] ?? ''); // Cumple certificado de calidad

        $filaExtra++; // Avanzar a la siguiente fila
        $contadorFila++; // Incrementar el contador de filas
    }
}
$nombreArchivoBase = 'liberaciones_' . date('Y-m-d');
$nombreArchivo = $nombreArchivoBase . '.xlsx';
$rutaArchivo = "/var/www/html/fmt/archivos/generados/Calidad/liberaciones/$nombreArchivo";

// Verificar si el archivo ya existe y agregar sufijo si es necesario
$contador = 1;
while (file_exists($rutaArchivo)) {
    $nombreArchivo = $nombreArchivoBase . '_' . $contador . '.xlsx';
    $rutaArchivo = "/var/www/html/fmt/archivos/generados/Calidad/liberaciones/$nombreArchivo";
    $contador++;
}

// Guardar el archivo Excel
$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');


try {
    $writer->save($rutaArchivo);

    // Mostrar un mensaje de confirmación con un botón para redirigir
    echo "
    <div style='display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100vh; font-family: Arial, sans-serif;'>
        <h2 style='color: #4CAF50;'>¡Formulario generado exitosamente!</h2>
        <p>El archivo ha sido creado correctamente.</p>
        <button onclick=\"window.location.href='../../redireccion.php'\" style='padding: 10px 20px; background-color: #0056b3; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;'>
            Volver
        </button>
    </div>
    ";
} catch (Exception $e) {
    echo "
    <div style='display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100vh; font-family: Arial, sans-serif;'>
        <h2 style='color: #f44336;'>Error al guardar el archivo Excel</h2>
        <p>" . $e->getMessage() . "</p>
        <button onclick=\"window.location.href='../../redireccion.php'\" style='padding: 10px 20px; background-color: #0056b3; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;'>
            Volver
        </button>
    </div>
    ";
}
}
?>