<?php
/**
 * imprimir_muestra.php
 * ---------------------
 * Visor imprimible (window.print()) que reemplaza la conversión
 * Excel → Dompdf. Mantiene el mismo encabezado institucional
 * (Código CDP-CU-IA-FO-013, Versión 1, Fecha 21/7/2023).
 */
require_once '../../sesion.php';
verificarAutenticacion();

$sede = $_SESSION['sede'];
$sede_san = preg_replace('/[^A-Za-z0-9_-]/', '', $sede);

$periodo = $_GET['periodo'] ?? '';
$fecha   = $_GET['fecha'] ?? '';

if (!preg_match('/^\d{4}-\d{2}$/', $periodo) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
    die('Parámetros inválidos.');
}

$archivo_json = "/var/www/fmt/archivos/generados/Calidad/muestras/" . $sede_san . "/" . $periodo . ".json";

if (!file_exists($archivo_json)) {
    die('No se encontró información para ese período.');
}

$registros = json_decode(file_get_contents($archivo_json), true) ?: [];
$items = [];
foreach ($registros as $r) {
    if (($r['datos']['fecha_registro'] ?? '') === $fecha) {
        $items[] = $r['datos'];
    }
}
usort($items, fn($a, $b) => ($a['item'] ?? 0) <=> ($b['item'] ?? 0));

if (empty($items)) {
    die('No hay ítems registrados para el ' . htmlspecialchars($fecha) . '.');
}

function iv($v) { return ($v !== null && $v !== '') ? htmlspecialchars($v) : '—'; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impresión de Muestras - <?= htmlspecialchars($fecha) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --navy: #0F172A; --blue: #003366; --white: #FFFFFF; --border: #000000; --accent: #00F0FF;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Roboto', sans-serif; background: #E2E8F0; padding: 20px; color: #000; }

        .action-bar {
            max-width: 99%; margin: 0 auto 20px auto;
            display: flex; justify-content: space-between; align-items: center;
            background: var(--navy); padding: 14px 24px; border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3); gap: 10px; flex-wrap: wrap;
        }
        .action-bar .left { display: flex; gap: 10px; align-items: center; }
        .btn-back {
            background: transparent; border: 1px solid var(--accent); color: var(--accent);
            text-decoration: none; padding: 9px 18px; border-radius: 4px;
            font-weight: 700; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; transition: all 0.2s;
        }
        .btn-back:hover { background: rgba(0,240,255,0.1); }
        .doc-label { color: #E2E8F0; font-size: 13px; font-weight: 600; padding: 0 8px; border-left: 2px solid var(--accent); }
        .btn-print {
            background: #10B981; color: #fff; border: none; padding: 9px 18px; border-radius: 4px;
            font-weight: 700; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; cursor: pointer; transition: background 0.2s;
        }
        .btn-print:hover { background: #059669; }

        .page-wrap { max-width: 99%; margin: 0 auto; background: var(--white); padding: 24px 28px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }

        table { width: 100%; border-collapse: collapse; }
        td, th { border: 1px solid var(--border); padding: 5px 6px; vertical-align: middle; text-align: center; }

        .header-table td { border: 1px solid #000; vertical-align: middle; padding: 8px 10px; }
        .header-title-main { font-size: 9.5pt; font-weight: 600; margin-bottom: 3px; }
        .header-title-doc  { font-size: 11pt; font-weight: 700; }
        .header-title-sub  { font-size: 9pt; font-style: italic; margin-top: 3px; }

        .iso-meta { width: 100%; border-collapse: collapse; height: 100%; }
        .iso-meta td { border: 0; border-bottom: 1px solid #000; padding: 3px 8px; font-size: 7.5pt; text-align: left; }
        .iso-meta tr:last-child td { border-bottom: 0; }
        .iso-meta td:first-child { font-weight: 700; border-right: 1px solid #000; width: 45%; }

        .meta-row td { border: 1px solid #000; padding: 6px 10px; font-size: 8pt; text-align: left; }
        .meta-row .accent { font-weight: 700; background: #F1F5F9; width: 15%; }

        .tabla-datos { margin-top: 0; font-size: 7.5pt; }
        .tabla-datos thead th {
            background: var(--blue); color: #fff; font-size: 7pt; font-weight: 600;
            text-transform: uppercase; padding: 6px 4px;
        }
        .tabla-datos tbody td { font-size: 8pt; padding: 5px 4px; }
        .tabla-datos tbody tr:nth-child(even) td { background: #F8FAFC; }
        .tabla-datos tbody tr:hover td { background: #EFF6FF; }

        .disp-pendiente { color: #92400E; font-style: italic; }

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
        <a href="revision_muestras.php" class="btn-back">← Volver al Listado</a>
        <span class="doc-label">Fecha: <?= htmlspecialchars($fecha) ?> | <?= count($items) ?> ítem<?= count($items) !== 1 ? 's' : '' ?></span>
    </div>
    <button class="btn-print" onclick="window.print()">🖨️ IMPRIMIR / PDF</button>
</div>

<div class="page-wrap">

    <!-- ENCABEZADO INSTITUCIONAL -->
    <table class="header-table" style="margin-bottom:0;">
        <tr>
            <td style="width:12%; text-align:center; padding:10px;">
                <img src="/img/logo_empresa.jpeg" alt="Logo MAS" style="max-height:60px; max-width:100%; object-fit:contain;">
            </td>
            <td style="width:72%; text-align:center; padding:10px;">
                <div class="header-title-main">Procedimiento de Inspección y Análisis</div>
                <div class="header-title-doc">Control al Ingreso y Salida de Muestras</div>
                <div class="header-title-sub">Registro y trazabilidad de muestras de laboratorio</div>
            </td>
            <td style="width:16%; padding:0; vertical-align:top;">
                <table class="iso-meta" style="height:100%;">
                    <tr><td>Código:</td><td>CDP-CU-IA-FO-013</td></tr>
                    <tr><td>Versión:</td><td>1</td></tr>
                    <tr><td>Fecha:</td><td>21/7/2023</td></tr>
                    <tr><td>Páginas:</td><td>1 de 1</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="meta-row" style="margin-top:0;">
        <tr>
            <td class="accent">Fecha Ingreso</td>
            <td><?= htmlspecialchars($fecha) ?></td>
            <td class="accent">Planta</td>
            <td><?= htmlspecialchars($sede) ?></td>
        </tr>
    </table>

    <table class="tabla-datos">
        <thead>
            <tr>
                <th>Ítem</th>
                <th>Hora</th>
                <th>Producto</th>
                <th>Lote</th>
                <th>Fecha Muestreo</th>
                <th>Hora Muestreo</th>
                <th>Responsable Toma</th>
                <th>Cantidad (g)</th>
                <th>Fecha Disposición</th>
                <th>Mejorante</th>
                <th>Cantidad Disp.</th>
                <th>Responsable Disp.</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($items as $d): ?>
            <tr>
                <td><?= iv($d['item'] ?? null) ?></td>
                <td><?= iv($d['hora'] ?? null) ?></td>
                <td style="text-align:left; padding-left:6px;"><?= iv($d['producto'] ?? null) ?></td>
                <td><?= iv($d['lote'] ?? null) ?></td>
                <td><?= iv($d['fecha_muestreo'] ?? null) ?></td>
                <td><?= iv($d['hora_muestreo'] ?? null) ?></td>
                <td style="text-align:left; padding-left:6px;"><?= iv($d['responsable_muestra'] ?? null) ?></td>
                <td><?= iv($d['cantidad'] ?? null) ?></td>
                <?php if (!empty($d['disp_fecha'])): ?>
                    <td><?= iv($d['disp_fecha']) ?></td>
                    <td><?= iv($d['disp_mejorante'] ?? null) ?></td>
                    <td><?= iv($d['disp_cantidad'] ?? null) ?></td>
                    <td style="text-align:left; padding-left:6px;"><?= iv($d['disp_responsable'] ?? null) ?></td>
                <?php else: ?>
                    <td colspan="4" class="disp-pendiente">Sin disposición registrada</td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

</div>

</body>
</html>
