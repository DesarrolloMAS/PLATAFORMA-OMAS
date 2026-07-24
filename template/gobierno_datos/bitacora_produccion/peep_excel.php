<?php
require __DIR__ . '/../../../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

$file = 'Producción Harinas 2026 (1).xlsx';
$reader = IOFactory::createReaderForFile($file);
$reader->setReadDataOnly(true);
$spreadsheet = $reader->load($file);
$sheet = $spreadsheet->getActiveSheet();

echo "Headers (Rows 1-5):\n";
for ($i = 1; $i <= 5; $i++) {
    $row = [];
    $cellIterator = $sheet->getRowIterator($i, $i)->current()->getCellIterator();
    $cellIterator->setIterateOnlyExistingCells(false); 
    foreach ($cellIterator as $cell) {
        $row[] = $cell->getValue();
    }
    echo "Row $i: " . implode(" | ", $row) . "\n";
}
