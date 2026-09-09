<?php
require_once '../../../vendor/autoload.php';
include '../../sesion.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

if (empty($_SESSION['sede'])) { http_response_code(401); exit; }

$sede = preg_replace('/[^A-Za-z0-9_-]/', '', $_SESSION['sede']);
$dir  = realpath(__DIR__ . '/../../../archivos/generados/traza_molienda/' . $sede);
if (!$dir || !is_dir($dir)) { http_response_code(404); exit('Sin registros para esta sede.'); }

// ---- CARGA Y ORDEN ----
$registros = [];
foreach (glob($dir . '/TRZMOL_*.json') ?: [] as $f) {
    foreach (json_decode(file_get_contents($f), true) ?: [] as $r) {
        $registros[] = $r;
    }
}
usort($registros, fn($a, $b) => strcmp($a['datos']['fecha'] ?? '', $b['datos']['fecha'] ?? ''));

// ---- HELPERS ----
function fmtF($iso) {
    if (!$iso) return '';
    $p = explode('-', $iso);
    return count($p) === 3 ? "{$p[2]}/{$p[1]}/{$p[0]}" : $iso;
}
function fmtHM($h, $m) {
    $h = (string)($h ?? ''); $m = (string)($m ?? '');
    if ($h === '' && $m === '') return '';
    return str_pad($h, 2, '0', STR_PAD_LEFT) . 'h ' . str_pad($m, 2, '0', STR_PAD_LEFT) . 'm';
}
function sv($arr, $k) {
    return (isset($arr[$k]) && $arr[$k] !== null && $arr[$k] !== '') ? (string)$arr[$k] : '';
}
function splitParada($val) {
    if (!$val) return ['', ''];
    $p = array_map('trim', explode('-', $val, 2));
    return [$p[0], $p[1] ?? ''];
}
function hasData($arr) {
    return is_array($arr) && count(array_filter($arr, fn($v) => $v !== '' && $v !== null)) > 0;
}

// ---- COLUMNAS ----
$cols = [
    [1,  '',                         "Fecha\nProducción",      14],
    [2,  '',                         'Referencia',             16],
    [3,  '',                         "Silo\nAcond.",            8],
    [4,  'Acondicionamiento',        "Fecha\nInicio Moje",     13],
    [5,  'Acondicionamiento',        "Hora\nInicio",            9],
    [6,  'Acondicionamiento',        "Fecha\nFinal Moje",      13],
    [7,  'Acondicionamiento',        "Hora\nFinal",             9],
    [8,  'Acondicionamiento',        "Parada\nHora Inicio",    13],
    [9,  'Acondicionamiento',        "Parada\nHora Final",     13],
    [10, 'Acondicionamiento',        'Motivo',                 22],
    [11, 'Acondicionamiento',        "Tiempo\nMoje (h)",       11],
    [12, '',                         "Total\nTrigo",           10],
    [13, '',                         "Total\nMYFC",            10],
    [14, '',                         "Total\nAgua",            10],
    [15, 'Mezcla Trigo',             "Alta\nProteína",         15],
    [16, 'Mezcla Trigo',             "Media\nProteína",        15],
    [17, 'Mezcla Trigo',             "Baja\nProteína",         15],
    [18, 'Molienda',                 "Fecha\nInicio",          13],
    [19, 'Molienda',                 "Hora\nInicio",            9],
    [20, 'Molienda',                 "Fecha\nFinal",           13],
    [21, 'Molienda',                 "Hora\nFinal",             9],
    [22, 'Molienda',                 "Parada\nHora Inicio",    13],
    [23, 'Molienda',                 "Parada\nHora Final",     13],
    [24, 'Molienda',                 'Motivo',                 22],
    [25, 'Molienda',                 "Tiempo\nMolienda (h)",   14],
    [26, 'Molienda',                 "Tiempo\nReposo (h)",     13],
    [27, 'Rupturas',                 "T1\nDerecha",             9],
    [28, 'Rupturas',                 "T1\nIzquierda",          10],
    [29, 'Rupturas',                 "T2\nDerecha",             9],
    [30, 'Rupturas',                 "T2\nIzquierda",          10],
    [31, 'Rupturas',                 "T3\nDerecha",             9],
    [32, 'Rupturas',                 "T3\nIzquierda",          10],
    [33, 'Rupturas',                 "Granulo-\nmetría",       11],
    [34, 'Control Producto Proceso', 'Hora',                    9],
    [35, 'Control Producto Proceso', "Humedad\nTrigo (%)",     13],
    [36, 'Control Producto Proceso', "Humedad\nHarina (%)",    13],
    [37, 'Control Producto Proceso', "Granulo-\nmetría",       11],
    [38, 'Control Producto Proceso', "Almidón\nDañado",        11],
    [39, 'Control Producto Proceso', 'Cenizas',                10],
];

$nCols = count($cols);
$lastL = Coordinate::stringFromColumnIndex($nCols);

// ---- SPREADSHEET ----
$ss = new Spreadsheet();
$ws = $ss->getActiveSheet();
$ws->setTitle('Trazabilidad Molienda');

// ROW 1 — título
$ws->mergeCells("A1:{$lastL}1");
$ws->setCellValue('A1', 'ORGANIZACIÓN MAS  ·  Manual: Gestión de la Producción  ·  Trazabilidad Molienda Harina de Trigo  ·  Sede: ' . $sede);
$ws->getStyle("A1:{$lastL}1")->applyFromArray([
    'font'      => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FFFFFFFF']],
    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1A3A5C']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
]);
$ws->getRowDimension(1)->setRowHeight(22);

// ROW 2 — grupos (base neutral)
$ws->getStyle("A2:{$lastL}2")->applyFromArray([
    'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE9EFF6']],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFCCCCCC']]],
]);
$ws->getRowDimension(2)->setRowHeight(16);

// Detectar rangos de grupo
$grupos = [];
$curG = null; $gStart = null;
foreach ($cols as [$idx, $grp, , ]) {
    if ($grp === '') {
        if ($curG !== null) { $grupos[] = [$curG, $gStart, $idx - 1]; $curG = null; $gStart = null; }
        continue;
    }
    if ($curG !== $grp) {
        if ($curG !== null) $grupos[] = [$curG, $gStart, $idx - 1];
        $curG = $grp; $gStart = $idx;
    }
}
if ($curG !== null) $grupos[] = [$curG, $gStart, $nCols];

$grpClr = [
    'Acondicionamiento'        => 'FF2E75B6',
    'Mezcla Trigo'             => 'FF548235',
    'Molienda'                 => 'FF7030A0',
    'Rupturas'                 => 'FFB8581A',
    'Control Producto Proceso' => 'FF0070C0',
];
foreach ($grupos as [$gName, $s, $e]) {
    $sL = Coordinate::stringFromColumnIndex($s);
    $eL = Coordinate::stringFromColumnIndex($e);
    if ($s !== $e) $ws->mergeCells("{$sL}2:{$eL}2");
    $ws->setCellValue("{$sL}2", $gName);
    $ws->getStyle("{$sL}2:{$eL}2")->applyFromArray([
        'font'      => ['bold' => true, 'size' => 9, 'color' => ['argb' => 'FFFFFFFF']],
        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $grpClr[$gName] ?? 'FF2E75B6']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFFFFFFF']]],
    ]);
}

// ROW 3 — encabezados columna
foreach ($cols as [$idx, , $hdr, $w]) {
    $l = Coordinate::stringFromColumnIndex($idx);
    $ws->setCellValue("{$l}3", $hdr);
    $ws->getColumnDimension($l)->setWidth($w);
}
$ws->getStyle("A3:{$lastL}3")->applyFromArray([
    'font'      => ['bold' => true, 'size' => 8, 'color' => ['argb' => 'FF1F3864']],
    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFBDD7EE']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF9DC3E6']]],
]);
$ws->getRowDimension(3)->setRowHeight(30);

// ---- FILAS DE DATOS ----
$row = 4;

foreach ($registros as $reg) {
    $datos   = $reg['datos'] ?? [];
    $acond   = $datos['acondicionamiento'] ?? [];
    // 'silos' es el formato vigente; 'lotes' se conserva como fallback de registros antiguos
    $lotes   = $acond['silos'] ?? $acond['lotes'] ?? array_fill(0, 5, []);
    $mezclasLegacy = $acond['mezclas_trigo'] ?? [];
    $turnos  = $datos['molienda']['turnos'] ?? array_fill(0, 5, []);
    $ctrl    = array_values(array_filter($datos['control_producto']  ?? [], 'hasData'));
    $rupt    = array_values(array_filter($datos['control_rupturas']  ?? [], 'hasData'));

    $loteCount = max(1, count(array_filter($lotes, 'hasData')));
    $nRows     = max($loteCount, count($ctrl), count($rupt));

    for ($i = 0; $i < $nRows; $i++) {
        $lote  = $lotes[$i]  ?? [];
        $turno = $turnos[$i] ?? [];
        $cp    = $ctrl[$i]   ?? [];
        $rp    = $rupt[$i]   ?? [];

        // Mezclas propias del silo; si no existen (registro antiguo) se usa la lista global solo en la fila 0
        $mezclasFila = $lote['mezclas_trigo'] ?? ($i === 0 ? $mezclasLegacy : []);
        $mezLabel = function($idx) use ($mezclasFila) {
            if (!isset($mezclasFila[$idx])) return '';
            $t = sv($mezclasFila[$idx], 'trigo');
            $p = sv($mezclasFila[$idx], 'porcentaje');
            return $t . ($p !== '' ? ' (' . $p . '%)' : '');
        };

        [$acParI, $acParF]   = splitParada(sv($lote,  'paradas_hi_hf'));
        [$molParI, $molParF] = splitParada(sv($turno, 'paradas_hi_hf'));

        $rowData = [
            fmtF($datos['fecha'] ?? ''),
            sv($lote, 'referencia_harina'),
            sv($lote, 'silo'),
            fmtF(sv($lote, 'fecha_mojo')),
            fmtHM(sv($lote, 'hi_mojo_hrs'), sv($lote, 'hi_mojo_min')),
            fmtF(sv($lote, 'fecha_mojo')),
            fmtHM(sv($lote, 'hf_mojo_hrs'), sv($lote, 'hf_mojo_min')),
            $acParI,
            $acParF,
            sv($lote, 'paradas_motivo'),
            fmtHM(sv($lote, 'horas_mojo_hrs'), sv($lote, 'horas_mojo_min')),
            sv($lote, 'total_trigo')      !== '' ? sv($lote, 'total_trigo')      : ($i === 0 ? sv($acond, 'total_trigo')      : ''),
            sv($lote, 'total_trigo_myfc') !== '' ? sv($lote, 'total_trigo_myfc') : ($i === 0 ? sv($acond, 'total_trigo_myfc') : ''),
            sv($lote, 'total_agua')       !== '' ? sv($lote, 'total_agua')       : ($i === 0 ? sv($acond, 'total_agua')       : ''),
            $mezLabel(0),
            $mezLabel(1),
            $mezLabel(2),
            fmtF($datos['fecha'] ?? ''),
            fmtHM(sv($turno, 'hi_hrs'), sv($turno, 'hi_min')),
            fmtF($datos['fecha'] ?? ''),
            fmtHM(sv($turno, 'hf_hrs'), sv($turno, 'hf_min')),
            $molParI,
            $molParF,
            sv($turno, 'paradas_motivo'),
            fmtHM(sv($turno, 'horas_molienda_hrs'), sv($turno, 'horas_molienda_min')),
            fmtHM(sv($turno, 'horas_reposo_hrs'),   sv($turno, 'horas_reposo_min')),
            sv($rp, 't1_derecha'),
            sv($rp, 't1_izquierda'),
            sv($rp, 't2_derecha'),
            sv($rp, 't2_izquierda'),
            sv($rp, 't3_derecha'),
            sv($rp, 't3_izquierda'),
            sv($rp, 'granulometria'),
            sv($cp, 'hora'),
            sv($cp, 'humedad_trigo'),
            sv($cp, 'humedad_harina'),
            sv($cp, 'granulometria'),
            sv($cp, 'almidon_danado'),
            sv($cp, 'cenizas'),
        ];

        foreach ($rowData as $ci => $val) {
            $ws->setCellValue(Coordinate::stringFromColumnIndex($ci + 1) . $row, $val);
        }

        $fillARGB = ($row % 2 === 0) ? 'FFEAF4FF' : 'FFFFFFFF';
        $ws->getStyle("A{$row}:{$lastL}{$row}")->applyFromArray([
            'font'      => ['size' => 8],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $fillARGB]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFD0E4F5']]],
        ]);

        if ($i === 0) {
            $ws->getStyle("A{$row}:{$lastL}{$row}")->getBorders()->getTop()
                ->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setARGB('FF2E75B6');
        }

        $ws->getRowDimension($row)->setRowHeight(14);
        $row++;
    }
}

$ws->freezePane('A4');

// ---- DESCARGA ----
$filename = 'Trazabilidad_Molienda_' . $sede . '_' . date('Y-m') . '.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');
header('Expires: 0');
header('Pragma: public');

(new Xlsx($ss))->save('php://output');
exit;
