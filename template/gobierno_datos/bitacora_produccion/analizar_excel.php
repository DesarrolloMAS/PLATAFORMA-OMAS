<?php
require __DIR__ . '/../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$file = 'Producción Harinas 2026 (1).xlsx';

if (!file_exists($file)) {
    die("Error: El archivo no existe.\n");
}

try {
    echo "Analizando archivo: $file\n";
    $spreadsheet = IOFactory::load($file);
    $sheets = $spreadsheet->getSheetNames();
    echo "Hojas encontradas: " . implode(', ', $sheets) . "\n\n";

    $sheetName = $sheets[0]; // Analizar la primera hoja (Producción)
    $sheet = $spreadsheet->getSheetByName($sheetName);
    $highestRow = 30; // Solo 30 filas
    $highestColumn = $sheet->getHighestColumn();
    
    echo "--- Hoja: $sheetName ---\n";
    echo "Dimensiones Reales: " . $sheet->getHighestColumn() . " " . $sheet->getHighestRow() . "\n";
    
    $data = $sheet->rangeToArray('A1:' . $highestColumn . $highestRow, NULL, TRUE, FALSE);
    foreach ($data as $rowIndex => $row) {
        $cleanRow = array_map(function($v) { return $v === null ? '' : trim($v); }, $row);
        // Solo imprimir si la fila tiene datos
        if (array_filter($cleanRow)) {
            echo "Fila " . ($rowIndex + 1) . ": " . implode(' | ', $cleanRow) . "\n";
        }
    }

} catch (Exception $e) {
    echo "Error al procesar: " . $e->getMessage() . "\n";
}
