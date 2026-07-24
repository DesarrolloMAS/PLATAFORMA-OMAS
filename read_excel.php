<?php
require '/var/www/fmt/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

$spreadsheet = IOFactory::load('/var/www/fmt/archivos/formularios/formulario7.xlsx');
$sheet = $spreadsheet->getActiveSheet();

for ($i = 5; $i <= 15; $i++) {
    $row = [];
    foreach (['A', 'B', 'C', 'D', 'E', 'F', 'N'] as $col) {
        $row[] = $sheet->getCell($col . $i)->getValue();
    }
    echo "Fila $i: " . implode(" | ", $row) . "\n";
}
?>
