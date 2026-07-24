<?php
require '../sesion.php';
verificarAutenticacion();

$sede        = $_GET['sede'] ?? $_SESSION['sede'];
$target_file = $_GET['file'] ?? '';

if (empty($target_file)) {
    die("Archivo no especificado. Vuelva a la Galería.");
}

$ruta_json = "../../archivos/generados/bodegas_v2/"
           . preg_replace('/[^A-Za-z0-9_-]/', '', $sede) . "/"
           . basename($target_file);

if (!file_exists($ruta_json)) {
    die("El archivo no existe o fue eliminado.");
}

$registros = json_decode(file_get_contents($ruta_json), true) ?: [];

if (empty($registros)) {
    die("El archivo está vacío.");
}

usort($registros, function ($a, $b) {
    $fA = $a['datos']['fecha'] ?? '';
    $fB = $b['datos']['fecha'] ?? '';
    if ($fA !== $fB) return strtotime($fA) <=> strtotime($fB);
    return strtotime($a['timestamp'] ?? '') <=> strtotime($b['timestamp'] ?? '');
});

$ultimo        = end($registros);
$bodega_key    = $ultimo['bodega_key']    ?? basename($target_file);
$bodega_nombre = $ultimo['bodega_nombre'] ?? $bodega_key;
$periodo       = preg_replace('/^' . preg_quote($bodega_key, '/') . '_/', '', str_replace('.json', '', basename($target_file)));

$PREGUNTAS = [
    'Registro de temperatura y humedad del lugar',
    'Pisos y paredes limpios, libres de derrames',
    'Bodega libre de insectos, roedores y palomas',
    'Vías de tránsito en bodega libres de obstáculos',
    'Productos almacenados debidamente ordenados',
    'Estibas y estantes limpios y en buen estado',
    'Materiales almacenados a mínimo 15cm del suelo',
    'Ventilación adecuada',
    'Iluminación adecuada',
    'Elementos almacenados protegidos de la luz solar',
    'Ausencia de productos no conformes',
    'Productos no conformes aislados',
    'Insumos alérgenos aislados',
    'Pisos y superficies libres de residuos',
];

function formatEval($val) {
    if ($val === 'SI')  return '<span style="color:#166534;font-weight:700;">SI</span>';
    if ($val === 'NO')  return '<span style="color:#991B1B;font-weight:700;">NO</span>';
    if ($val === 'N/A') return '<span style="color:#92400E;font-weight:700;">NA</span>';
    return '<span style="color:#9CA3AF;">—</span>';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visor - Inspección de Bodegas | <?= htmlspecialchars($bodega_nombre) ?> · <?= htmlspecialchars($periodo) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --navy:   #0F172A;
            --blue:   #003366;
            --white:  #FFFFFF;
            --border: #000000;
            --accent: #00F0FF;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Roboto', sans-serif;
            background: #E2E8F0;
            padding: 20px;
            color: #000;
        }

        .action-bar {
            max-width: 99%;
            margin: 0 auto 20px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--navy);
            padding: 14px 24px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            gap: 10px;
            flex-wrap: wrap;
        }

        .action-bar .left { display: flex; gap: 10px; flex-wrap: wrap; }

        .btn-back {
            background: transparent; border: 1px solid var(--accent); color: var(--accent);
            text-decoration: none; padding: 9px 18px; border-radius: 4px;
            font-weight: 700; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; transition: all 0.2s;
        }
        .btn-back:hover { background: rgba(0,240,255,0.1); }

        .btn-print {
            background: #10B981; color: #fff; border: none;
            padding: 9px 18px; border-radius: 4px;
            font-weight: 700; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;
            cursor: pointer; transition: background 0.2s;
        }
        .btn-print:hover { background: #059669; }

        .page-wrap {
            max-width: 99%;
            margin: 0 auto;
            background: var(--white);
            padding: 24px 28px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        table { width: 100%; border-collapse: collapse; }
        td, th { border: 1px solid var(--border); padding: 4px 5px; vertical-align: middle; text-align: center; }

        .header-table td { border: 1px solid #000; vertical-align: middle; padding: 8px 10px; }
        .header-title-main { font-size: 9.5pt; font-weight: 600; margin-bottom: 3px; }
        .header-title-doc  { font-size: 11pt; font-weight: 700; }

        .iso-meta { width: 100%; border-collapse: collapse; height: 100%; }
        .iso-meta td { border: 0; border-bottom: 1px solid #000; padding: 3px 8px; font-size: 7.5pt; text-align: left; }
        .iso-meta tr:last-child td { border-bottom: 0; }
        .iso-meta td:first-child { font-weight: 700; border-right: 1px solid #000; width: 45%; }

        .zona-row { width: 100%; border-collapse: collapse; margin-top: 0; }
        .zona-row td { border: 1px solid #000; padding: 5px 10px; font-size: 8pt; }
        .zona-label { font-weight: 700; width: 55px; background: #F1F5F9; }
        .instrucciones-cell { text-align: left; font-size: 7.5pt; line-height: 1.4; }
        .instrucciones-cell strong { color: #000; }

        .tabla-datos { margin-top: 0; font-size: 7pt; }
        .tabla-datos thead th {
            background: var(--blue); color: #fff; font-size: 6.5pt; font-weight: 600;
            text-transform: uppercase; line-height: 1.2; padding: 5px 3px;
        }
        .tabla-datos thead th.eval-header {
            background: #1a4080; font-size: 6pt; font-weight: 500; line-height: 1.25;
            padding: 4px 3px; writing-mode: vertical-rl; text-orientation: mixed;
            transform: rotate(180deg); height: 120px; vertical-align: bottom;
        }
        .tabla-datos tbody td { font-size: 7.5pt; padding: 5px 4px; vertical-align: middle; }
        .tabla-datos tbody tr:nth-child(even) { background: #F8FAFC; }
        .tabla-datos tbody tr:hover { background: #EFF6FF; }

        .col-porcentaje { font-weight: 700; font-size: 8pt; }
        .pct-good   { color: #166534; }
        .pct-warn   { color: #92400E; }
        .pct-danger { color: #991B1B; }

        .empty-row td { padding: 30px; color: #64748B; font-style: italic; font-size: 9pt; }

        @media print {
            body        { background: #fff; padding: 0; }
            .action-bar { display: none !important; }
            .page-wrap  { box-shadow: none; padding: 10px; max-width: 100%; }
            @page       { size: landscape; margin: 8mm; }
        }
    </style>
</head>
<body>

<div class="action-bar">
    <div class="left">
        <a href="rev_bodegas_v2.php" class="btn-back">← Volver al Listado</a>
    </div>
    <button class="btn-print" onclick="window.print()">🖨️ IMPRIMIR / GUARDAR PDF</button>
</div>

<div class="page-wrap" id="document_content">

    <table class="header-table" style="margin-bottom:0;">
        <tr>
            <td style="width:18%; text-align:center; padding:10px;">
                <img src="/img/logo_empresa.jpeg" alt="Logo MAS" style="max-height:70px; max-width:100%; object-fit:contain;">
            </td>
            <td style="width:60%; text-align:center; padding:10px;">
                <div class="header-title-main">PPR Gestión de Almacenamiento</div>
                <div class="header-title-main" style="margin-bottom:4px;">Procedimiento de Inspección de Bodegas</div>
                <div class="header-title-doc">"Inspección de Condiciones de Almacenamiento"</div>
            </td>
            <td style="width:22%; padding:0; vertical-align:top;">
                <table class="iso-meta" style="height:100%;">
                    <tr><td>Código:</td><td>GP-AL-FO-BOD-002</td></tr>
                    <tr><td>Versión:</td><td>2</td></tr>
                    <tr><td>Bodega:</td><td><?= htmlspecialchars($bodega_nombre) ?></td></tr>
                    <tr><td>Página:</td><td>1 de 1</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="zona-row">
        <tr>
            <td class="zona-label">ZONA:</td>
            <td style="width:120px; font-weight:700; font-size:9pt;"><?= htmlspecialchars($sede) ?></td>
            <td class="instrucciones-cell">
                <strong>INSTRUCCIONES PARA EL REGISTRO:</strong>
                Por cada ítem a evaluar, marcar <strong>SI</strong> si cumple, <strong>NO</strong> si no cumple,
                o <strong>NA</strong> si no aplica para esta bodega.
            </td>
        </tr>
    </table>

    <table class="tabla-datos">
        <thead>
            <tr>
                <th rowspan="2" style="width:6%;">Fecha</th>
                <?php foreach ($PREGUNTAS as $p): ?>
                    <th class="eval-header"><?= htmlspecialchars($p) ?></th>
                <?php endforeach; ?>
                <th rowspan="2" style="width:8%;">Hallazgo</th>
                <th rowspan="2" style="width:8%;">Plan de Acción</th>
                <th rowspan="2" style="width:6%;">Fecha Acción</th>
                <th rowspan="2" style="width:8%;">Resultado Esperado</th>
                <th rowspan="2" style="width:6%;">% Cumpl.</th>
                <th rowspan="2" style="width:7%;">Registrado por</th>
            </tr>
            <tr></tr>
        </thead>
        <tbody>
        <?php if (empty($registros)): ?>
            <tr class="empty-row"><td colspan="21">No hay registros de inspección para esta bodega/período.</td></tr>
        <?php else: ?>
            <?php foreach ($registros as $reg):
                $d = $reg['datos'];
                $si = 0; $no = 0;
                $fecha_fmt = !empty($d['fecha']) ? date('d/m/Y', strtotime($d['fecha'])) : '—';
            ?>
            <tr>
                <td><?= htmlspecialchars($fecha_fmt) ?></td>
                <?php for ($i = 1; $i <= 14; $i++):
                    $v = $d["opcion$i"] ?? '';
                    if ($v === 'SI') $si++;
                    if ($v === 'NO') $no++;
                ?>
                    <td><?= formatEval($v) ?></td>
                <?php endfor; ?>
                <td style="text-align:left; padding-left:6px;"><?= htmlspecialchars($d['hallazgo'] ?? '—') ?></td>
                <td style="text-align:left; padding-left:6px;"><?= htmlspecialchars($d['plan_accion'] ?? '—') ?></td>
                <td><?= !empty($d['fecha_accion']) ? htmlspecialchars(date('d/m/Y', strtotime($d['fecha_accion']))) : '—' ?></td>
                <td style="text-align:left; padding-left:6px;"><?= htmlspecialchars($d['resultado_esperado'] ?? '—') ?></td>
                <?php
                    $porcentaje = ($si + $no) > 0 ? round(($si / ($si + $no)) * 100, 1) : null;
                    $pct_class = 'pct-good';
                    if ($porcentaje !== null) {
                        if ($porcentaje < 60)      $pct_class = 'pct-danger';
                        elseif ($porcentaje < 80)  $pct_class = 'pct-warn';
                    }
                ?>
                <td class="col-porcentaje <?= $pct_class ?>"><?= $porcentaje !== null ? $porcentaje . '%' : 'N/A' ?></td>
                <td><?= htmlspecialchars($reg['usuario_sys'] ?? '—') ?></td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>

</div>

</body>
</html>
