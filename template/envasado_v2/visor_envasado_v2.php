<?php
require '../sesion.php';

if (!isset($_SESSION['nombre']) || empty($_SESSION['sede'])) {
    header('Location: ../../index.php');
    exit;
}

$sede        = $_SESSION['sede'];
$target_file = $_GET['file'] ?? '';

if (empty($target_file)) {
    die("Archivo no especificado.");
}

$ruta_json = "../../archivos/generados/envasado_v2/"
           . preg_replace('/[^A-Za-z0-9_-]/', '', $sede) . "/"
           . basename($target_file);

if (!file_exists($ruta_json)) {
    die("El archivo no existe o fue eliminado.");
}

$registros = json_decode(file_get_contents($ruta_json), true) ?: [];

if (empty($registros)) {
    die("El archivo está vacío.");
}

usort($registros, function($a, $b) {
    $fA = ($a['datos']['fecha'] ?? '') . ' ' . ($a['datos']['hora'] ?? '');
    $fB = ($b['datos']['fecha'] ?? '') . ' ' . ($b['datos']['hora'] ?? '');
    return strcmp($fA, $fB);
});

$periodo = str_replace(['ENV_', '.json'], '', basename($target_file));

function badge($val) {
    if ($val === 'SI')  return '<span style="color:#10B981;font-weight:700;">SI</span>';
    if ($val === 'NO')  return '<span style="color:#FF3366;font-weight:700;">NO</span>';
    if ($val === 'N/A') return '<span style="color:#94A3B8;">N/A</span>';
    return '<span style="color:#64748B;">—</span>';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Envasado V2 — <?= htmlspecialchars($periodo) ?></title>
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
            margin: 0 auto 20px;
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

        .action-bar .left { display: flex; gap: 10px; align-items: center; }

        .btn-back {
            background: transparent;
            border: 1px solid var(--accent);
            color: var(--accent);
            text-decoration: none;
            padding: 9px 18px;
            border-radius: 4px;
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
            transition: all 0.2s;
        }
        .btn-back:hover { background: rgba(0,240,255,0.1); }

        .periodo-label {
            color: #E2E8F0;
            font-size: 13px;
            font-weight: 600;
            padding: 0 8px;
            border-left: 2px solid var(--accent);
        }

        .btn-print {
            background: #10B981;
            color: #fff;
            border: none;
            padding: 9px 18px;
            border-radius: 4px;
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
            cursor: pointer;
            transition: background 0.2s;
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
        .iso-meta td {
            border: 0; border-bottom: 1px solid #000;
            padding: 3px 8px; font-size: 7.5pt; text-align: left;
        }
        .iso-meta tr:last-child td { border-bottom: 0; }
        .iso-meta td:first-child { font-weight: 700; border-right: 1px solid #000; width: 45%; }

        .tabla-datos { margin-top: 0; font-size: 7.5pt; }

        .tabla-datos thead th {
            background: var(--blue);
            color: #fff;
            font-size: 7pt;
            font-weight: 600;
            text-transform: uppercase;
            padding: 6px 4px;
        }

        .tabla-datos tbody td { font-size: 8pt; padding: 5px 4px; }
        .tabla-datos tbody tr:nth-child(even) { background: #F8FAFC; }
        .tabla-datos tbody tr:hover           { background: #EFF6FF; }

        .td-obs { text-align: left; max-width: 200px; font-size: 7.5pt; }

        tfoot td {
            background: #F0F9FF;
            font-weight: 700;
            font-size: 7.5pt;
            border-top: 2px solid #003366;
            padding: 6px 8px;
        }

        .empty-row td { padding: 30px; color: #64748B; font-style: italic; }

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
        <a href="rev_envasado_v2.php" class="btn-back">← Volver al Listado</a>
        <span class="periodo-label">Período: <?= htmlspecialchars($periodo) ?> | <?= count($registros) ?> registro<?= count($registros) !== 1 ? 's' : '' ?></span>
    </div>
    <button class="btn-print" onclick="window.print()">🖨️ IMPRIMIR / PDF</button>
</div>

<div class="page-wrap">

    <table class="header-table" style="margin-bottom:0;">
        <tr>
            <td style="width:18%; text-align:center; padding:10px;">
                <img src="/img/logo_empresa.jpeg" alt="Logo MAS"
                     style="max-height:70px; max-width:100%; object-fit:contain;">
            </td>
            <td style="width:60%; text-align:center; padding:10px;">
                <div class="header-title-main">PPR Gestión de la Producción</div>
                <div class="header-title-main" style="margin-bottom:4px;">Comprobaciones en Línea de Envasado</div>
                <div class="header-title-doc">Período: <?= htmlspecialchars($periodo) ?> &nbsp;|&nbsp; Sede: <?= htmlspecialchars($sede) ?></div>
            </td>
            <td style="width:22%; padding:0; vertical-align:top;">
                <table class="iso-meta" style="height:100%;">
                    <tr><td>Código:</td><td>GP-PD-PP-ENV-FO-001</td></tr>
                    <tr><td>Versión:</td><td>2</td></tr>
                    <tr><td>Registros:</td><td><?= count($registros) ?></td></tr>
                    <tr><td>Impreso:</td><td><?= date('d/m/Y') ?></td></tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="tabla-datos">
        <thead>
            <tr>
                <th style="width:7%;">Fecha</th>
                <th style="width:5%;">Hora</th>
                <th style="width:10%;">Harina</th>
                <th style="width:9%;">Empaque</th>
                <th style="width:6%;">Lote</th>
                <th style="width:6%;">F. Venc.</th>
                <th style="width:9%;">Responsable</th>
                <th style="width:5%;">Purgada</th>
                <th style="width:5%;">Ref. Empaque</th>
                <th style="width:5%;">Timbrado</th>
                <th style="width:5%;">Etiqueta</th>
                <th style="width:5%;">Aprobación</th>
                <th>Observaciones</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($registros)): ?>
            <tr class="empty-row"><td colspan="13">No hay registros.</td></tr>
        <?php else: ?>
            <?php foreach ($registros as $reg):
                $d = $reg['datos'];
                $fecha_fmt = !empty($d['fecha']) ? date('d/m/Y', strtotime($d['fecha'])) : '—';
            ?>
            <tr>
                <td><?= htmlspecialchars($fecha_fmt) ?></td>
                <td><?= htmlspecialchars($d['hora']             ?? '—') ?></td>
                <td><?= htmlspecialchars($d['harina']           ?? '—') ?></td>
                <td><?= htmlspecialchars($d['empaque']          ?? '—') ?></td>
                <td><?= htmlspecialchars($d['loteP']            ?? '—') ?></td>
                <td><?= htmlspecialchars($d['fechaVencimiento'] ?? '—') ?></td>
                <td><?= htmlspecialchars($d['responsable']      ?? ($reg['usuario_sys'] ?? '—')) ?></td>
                <td><?= badge($d['purgada']   ?? '') ?></td>
                <td><?= badge($d['Penvasado'] ?? '') ?></td>
                <td><?= badge($d['timbrado']  ?? '') ?></td>
                <td><?= badge($d['etiqueta']  ?? '') ?></td>
                <td><?= badge($d['aprobacion'] ?? '') ?></td>
                <td class="td-obs"><?= htmlspecialchars($d['observaciones'] ?? '') ?></td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="13" style="text-align:right; padding-right:12px;">
                    Total: <?= count($registros) ?> registro<?= count($registros) !== 1 ? 's' : '' ?> — Generado: <?= date('d/m/Y H:i') ?>
                </td>
            </tr>
        </tfoot>
    </table>

</div>
</body>
</html>
