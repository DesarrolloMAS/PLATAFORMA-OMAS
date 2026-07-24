<?php
// Configuración para ocultar errores en pantalla

require __DIR__ . '/../../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $consecutivo = $_POST['consecutivo'] ?? date("Ymd");
        $fecha = $_POST['fecha'] ?? date('Y-m-d');
        $harinas = $_POST['harina'] ?? [];
        $lotesHarina = $_POST['harina_lote'] ?? [];
        $cantidadesHarina = $_POST['harina_cantidad'] ?? [];
        $horaInicio = $_POST['harina_inicio'] ?? [];
        $horaFinal = $_POST['harina_final'] ?? [];
        $operarios = $_POST['operarios'] ?? [];
        $insumos = $_POST['data'] ?? [];
        $observaciones = $_POST['observaciones'] ?? '';
        $firma = $_POST['firma_turn1'] ?? '';

        // Cargar plantilla
        $plantillaPath = __DIR__ . '/../../archivos/formularios/formulario4.xlsx';
        if (!file_exists($plantillaPath)) {
            throw new Exception("Error: No se encontró la plantilla base.");
        }

        $spreadsheet = IOFactory::load($plantillaPath);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('I6', $fecha);
        $sheet->setCellValue('D5', $consecutivo);

        // Insertar HARINAS ESPECIALES (desde fila 11 en adelante)
        $filaHarina = 9;
        for ($i = 0; $i < count($harinas); $i++) {
            $sheet->setCellValue("B{$filaHarina}", $harinas[$i] ?? '');
            $sheet->setCellValue("E{$filaHarina}", $lotesHarina[$i] ?? '');
            $sheet->setCellValue("F{$filaHarina}", $cantidadesHarina[$i] ?? '');
            $sheet->setCellValue("G{$filaHarina}", $horaInicio[$i] ?? '');
            $sheet->setCellValue("H{$filaHarina}", $horaFinal[$i] ?? '');
            $sheet->setCellValue("I{$filaHarina}", $operarios[$i] ?? '');
            $filaHarina++;
        }

        // Insertar INSUMOS (desde fila 25 en adelante)
        $filaInsumo = 24;
        foreach ($insumos as $insumo) {
            $sheet->setCellValue("B{$filaInsumo}", $insumo['insumo'] ?? '');
            $sheet->setCellValue("D{$filaInsumo}", $insumo['lote'] ?? '');
            $sheet->setCellValue("E{$filaInsumo}", $insumo['cantidad'] ?? '');
            $filaInsumo++;
        }

        // Observaciones
        $sheet->setCellValue("B42", $observaciones);

        // Firma
        if (!empty($firma)) {
            error_log('Firma recibida: ' . substr($firma, 0, 30));
            $firmaPath = __DIR__ . '/../../archivos/formularios/firma_turno.png';
            $firmaData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $firma));
            if (file_put_contents($firmaPath, $firmaData) === false) {
                throw new Exception("Error al guardar la firma del Turno.");
            }

            $drawing = new Drawing();
            $drawing->setPath($firmaPath);
            $drawing->setHeight(100);
            $drawing->setCoordinates("B54");
            $drawing->setWorksheet($sheet);
        }
        $imagen_logoPath =  __DIR__ .'/../../archivos/formularios/logomas.png';
        $imagen_logoImg = new Drawing();
        $imagen_logoImg->setPath($imagen_logoPath);
        $imagen_logoImg->setHeight(100); // Ajusta el tamaño según sea necesario
        $imagen_logoImg->setCoordinates('A1');
        $imagen_logoImg->setWorksheet($sheet);

        // Guardar archivo
        $directorioSalida = __DIR__ . '/../../archivos/generados/premezclas/';
        if (!file_exists($directorioSalida) && !mkdir($directorioSalida, 0777, true)) {
            throw new Exception("❌ No se pudo crear el directorio de salida.");
        }

        $nombreBase = 'premezcla_' . date('Ymd');
        $contador = 1;

        // Verificar si ya existen archivos con el mismo nombre base y agregar un consecutivo
        do {
            $nombreArchivo = $nombreBase . '_' . $contador . '.xlsx';
            $rutaFinal = $directorioSalida . $nombreArchivo;
            $contador++;
        } while (file_exists($rutaFinal));

        // Guardar el archivo Excel
        $writer = new Xlsx($spreadsheet);
        $writer->save($rutaFinal);

        // Mostrar mensaje de éxito con un botón
        echo "
        <div style='display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100vh; font-family: Arial, sans-serif;'>
            <h2 style='color: #4CAF50;'>¡Formulario generado exitosamente!</h2>
            <p>El archivo <strong>$nombreArchivo</strong> ha sido creado correctamente.</p>
            <button onclick=\"window.location.href='../redireccion.php'\" style='padding: 10px 20px; background-color: #0056b3; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;'>
                Volver
            </button>
        </div>
        ";
    }
} catch (Exception $e) {
    // Manejar errores de forma controlada
    error_log($e->getMessage()); // Registrar el error en el archivo de log
    echo "
    <div style='display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100vh; font-family: Arial, sans-serif;'>
        <h2 style='color: #f44336;'>Error al guardar el archivo Excel</h2>
        <p>" . $e->getMessage() . "</p>
        <button onclick=\"window.location.href='../redireccion.php'\" style='padding: 10px 20px; background-color: #0056b3; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;'>
            Volver
        </button>
    </div>
    ";
}