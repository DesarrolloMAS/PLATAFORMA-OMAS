<?php
require __DIR__ . '/../../../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

$file = 'Producción Harinas 2026 (1).xlsx';
$spreadsheet = IOFactory::load($file);
$sheet = $spreadsheet->getActiveSheet();

echo "Columna A (Filas 1-30):\n";
for ($i = 1; $i <= 30; $i++) {
    echo "Fila $i: [" . $sheet->getCell("A$i")->getValue() . "]\n";
}
