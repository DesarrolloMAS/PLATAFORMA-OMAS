<?php
require '../../sesion.php';
// Cargar autoload para usar PhpSpreadsheet (asumiendo que PhpSpreadsheet fue instalado en la raiz ../../vendor)
require '../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Configuración PostgreSQL
$host = '127.0.0.1';
$db = 'bitacora_mantenimiento';
$user = 'bitacora_user';
$pass = 'bitacora2026';

try {
    $dsn = "pgsql:host=$host;port=5432;dbname=$db;";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Error crítico: No se pudo conectar a PostgreSQL para exportar a Excel.");
}

// Extraer registros
$stmt = $pdo->query("SELECT * FROM bitacora ORDER BY id ASC"); // Orden cronológico normal para bases de datos BI
$registros = $stmt->fetchAll();

// Instanciar archivo Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Bitacora_Mantenimiento');

// Encabezados
$encabezados = ['ID (Intervención)', 'Fecha', 'Hora', 'Máquina', 'Código', 'Ubicación', 'Falla / Acción', 'Técnico', 'Tipo Mantenimiento'];
$col = 'A';
foreach ($encabezados as $head) {
    $sheet->setCellValue($col . '1', $head);
    $sheet->getStyle($col . '1')->getFont()->setBold(true);
    $sheet->getStyle($col . '1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFA0A0A0');
    $col++;
}

// Llenar Datos
$row = 2; // Iniciando desde la fila 2
foreach ($registros as $registro) {
    $sheet->setCellValue('A' . $row, $registro['id']);
    $sheet->setCellValue('B' . $row, $registro['fecha']);
    $sheet->setCellValue('C' . $row, $registro['hora']);
    $sheet->setCellValue('D' . $row, $registro['maquina']);
    $sheet->setCellValue('E' . $row, $registro['codigo']);
    $sheet->setCellValue('F' . $row, $registro['ubicacion']);
    $sheet->setCellValue('G' . $row, $registro['descripcion_falla']);
    $sheet->setCellValue('H' . $row, $registro['tecnico']);
    $sheet->setCellValue('I' . $row, $registro['tipo_mantenimiento']);
    $row++;
}

// Congelar primera fila y auto-ajustar ancho de columnas para Power BI
$sheet->freezePane('A2');
foreach (range('A', 'I') as $columnID) {
    $sheet->getColumnDimension($columnID)->setAutoSize(true);
}

// Generar descarga
$filename = 'Data_Bitacora_Mantenimiento_' . date('Ymd_His') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

// Guardar al output (Navegador)
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>
