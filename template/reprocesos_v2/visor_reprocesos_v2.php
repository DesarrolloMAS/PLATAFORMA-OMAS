<?php
require '../sesion.php';

if (!isset($_SESSION['nombre']) || empty($_SESSION['sede'])) {
    header('Location: ../../index.php');
    exit;
}

$sede = $_SESSION['sede'];
$target_id = $_GET['id'] ?? '';

if (empty($target_id)) {
    die("Registro no especificado. Vuelva a la Galería.");
}

$ruta_json = "../../archivos/generados/reprocesos_v2/REPROCESOS_"
           . preg_replace('/[^A-Za-z0-9_-]/', '', $sede) . ".json";

if (!file_exists($ruta_json)) {
    die("El archivo no existe o fue eliminado.");
}

$registros = json_decode(file_get_contents($ruta_json), true) ?: [];

$registro = null;
foreach ($registros as $reg) {
    if (($reg['id_registro'] ?? '') === $target_id) {
        $registro = $reg;
        break;
    }
}

if (!$registro) {
    die("El registro no existe en este archivo.");
}

$d = $registro['datos'];
$ej = $d['ejecucion'] ?? null;
$fecha_alistamiento_fmt = !empty($d['fecha_alistamiento']) ? date('d/m/Y', strtotime($d['fecha_alistamiento'])) : '—';
$fecha_ejecucion_fmt = ($ej && !empty($ej['fecha'])) ? date('d/m/Y', strtotime($ej['fecha'])) : '—';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visor - Reproceso <?= htmlspecialchars($d['lote'] ?? '') ?></title>
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
            max-width: 900px;
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

        .btn-back {
            background: transparent; border: 1px solid var(--accent); color: var(--accent);
            text-decoration: none; padding: 9px 18px; border-radius: 4px; font-weight: 700;
            font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; transition: all 0.2s;
        }
        .btn-back:hover { background: rgba(0,240,255,0.1); }

        .btn-print {
            background: #10B981; color: #fff; border: none; padding: 9px 18px; border-radius: 4px;
            font-weight: 700; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;
            cursor: pointer; transition: background 0.2s;
        }
        .btn-print:hover { background: #059669; }

        .page-wrap {
            max-width: 900px;
            margin: 0 auto;
            background: var(--white);
            padding: 24px 28px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        table { width: 100%; border-collapse: collapse; }
        td, th { border: 1px solid var(--border); padding: 6px 8px; vertical-align: middle; text-align: left; }

        .header-table td { border: 1px solid #000; vertical-align: middle; padding: 8px 10px; text-align: center; }
        .header-title-main { font-size: 9.5pt; font-weight: 600; margin-bottom: 3px; }
        .header-title-doc { font-size: 11pt; font-weight: 700; }

        .iso-meta { width: 100%; border-collapse: collapse; height: 100%; }
        .iso-meta td { border: 0; border-bottom: 1px solid #000; padding: 3px 8px; font-size: 7.5pt; text-align: left; }
        .iso-meta tr:last-child td { border-bottom: 0; }
        .iso-meta td:first-child { font-weight: 700; border-right: 1px solid #000; width: 45%; }

        .estado-row td {
            padding: 10px; font-size: 10pt; font-weight: 700; text-align: center; text-transform: uppercase;
        }
        .estado-row.pendiente td { background: #FEF3C7; color: #92400E; }
        .estado-row.completado td { background: #D1FAE5; color: #065F46; }

        .section-heading {
            background: var(--blue); color: #fff; font-size: 9pt; font-weight: 700;
            text-transform: uppercase; padding: 6px 10px; margin-top: 16px;
        }

        .datos-table td { font-size: 9pt; }
        .datos-table td:first-child { font-weight: 700; background: #F1F5F9; width: 35%; }

        .firma-box { text-align: center; padding: 10px; }
        .firma-box img { max-height: 90px; max-width: 100%; border-bottom: 1px solid #000; }
        .firma-label { font-size: 8pt; color: #64748B; margin-top: 4px; }

        @media print {
            body        { background: #fff; padding: 0; }
            .action-bar { display: none !important; }
            .page-wrap  { box-shadow: none; padding: 10px; max-width: 100%; }
            @page       { size: portrait; margin: 10mm; }
        }
    </style>
</head>
<body>

<div class="action-bar">
    <a href="rev_reprocesos_v2.php" class="btn-back">← Volver al Listado</a>
    <button class="btn-print" onclick="window.print()">🖨️ IMPRIMIR / GUARDAR PDF</button>
</div>

<div class="page-wrap" id="document_content">

    <table class="header-table" style="margin-bottom:0;">
        <tr>
            <td style="width:18%; text-align:center; padding:10px;">
                <img src="/img/logo_empresa.jpeg" alt="Logo MAS" style="max-height:70px; max-width:100%; object-fit:contain;">
            </td>
            <td style="width:60%; text-align:center; padding:10px;">
                <div class="header-title-main">PPR Gestión de la Producción</div>
                <div class="header-title-main" style="margin-bottom:4px;">Procedimiento Control de Reprocesos</div>
                <div class="header-title-doc">"Registro de Reproceso"</div>
            </td>
            <td style="width:22%; padding:0; vertical-align:top;">
                <table class="iso-meta" style="height:100%;">
                    <tr><td>Sede:</td><td><?= htmlspecialchars($sede) ?></td></tr>
                    <tr><td>Lote:</td><td><?= htmlspecialchars($d['lote'] ?? '—') ?></td></tr>
                    <tr><td>ID Registro:</td><td style="font-size:6.5pt;"><?= htmlspecialchars($registro['id_registro']) ?></td></tr>
                </table>
            </td>
        </tr>
    </table>

    <table>
        <tr class="estado-row <?= htmlspecialchars($d['estado'] ?? 'pendiente') ?>">
            <td>ESTADO: <?= htmlspecialchars(strtoupper($d['estado'] ?? 'pendiente')) ?></td>
        </tr>
    </table>

    <div class="section-heading">1. Alistamiento de Almacén</div>
    <?php if (!empty($d['origen_id_registro'])): ?>
        <table>
            <tr class="estado-row pendiente">
                <td>Este pendiente es el saldo de un reproceso anterior (ID origen: <?= htmlspecialchars($d['origen_id_registro']) ?>).</td>
            </tr>
        </table>
    <?php endif; ?>
    <table class="datos-table">
        <tr><td>Fecha de Alistamiento</td><td><?= htmlspecialchars($fecha_alistamiento_fmt) ?></td></tr>
        <tr><td>Responsable de Alistamiento</td><td><?= htmlspecialchars($d['responsable_alistamiento'] ?? '—') ?></td></tr>
        <tr><td>Producto</td><td><?= htmlspecialchars($d['producto'] ?? '—') ?></td></tr>
        <tr><td>Lote</td><td><?= htmlspecialchars($d['lote'] ?? '—') ?></td></tr>
        <tr><td>Hora</td><td><?= htmlspecialchars($d['hora'] ?? '—') ?></td></tr>
        <tr><td>Cantidad (KG)</td><td><?= htmlspecialchars(number_format(floatval($d['cantidad'] ?? 0), 1)) ?></td></tr>
        <tr><td>Motivo</td><td><?= htmlspecialchars($d['motivo'] ?? '—') ?></td></tr>
        <tr><td>Proceso Sugerido</td><td><?= htmlspecialchars($d['proceso_sugerido'] ?? '—') ?></td></tr>
        <tr><td>Registrado por (sistema)</td><td><?= htmlspecialchars($registro['usuario_sys'] ?? '—') ?></td></tr>
    </table>

    <div class="section-heading">2. Ejecución del Reproceso</div>
    <?php if ($ej): ?>
        <table class="datos-table">
            <tr><td>Fecha de Reproceso</td><td><?= htmlspecialchars($fecha_ejecucion_fmt) ?></td></tr>
            <tr><td>Referencia de Producto</td><td><?= htmlspecialchars($ej['referencia'] ?? '—') ?></td></tr>
            <tr><td>Hora de Inicio</td><td><?= htmlspecialchars($ej['hora_inicio'] ?? '—') ?></td></tr>
            <tr><td>Hora Final</td><td><?= htmlspecialchars($ej['hora_fin'] ?? '—') ?></td></tr>
            <tr><td>Cantidad Solicitada (KG)</td><td><?= htmlspecialchars(number_format(floatval($d['cantidad'] ?? 0), 1)) ?></td></tr>
            <tr><td>Cantidad Procesada (KG)</td><td><?= htmlspecialchars(number_format(floatval($ej['cantidad_procesada'] ?? 0), 1)) ?></td></tr>
            <tr><td>Responsable de Ejecución</td><td><?= htmlspecialchars($ej['responsable_ejecucion'] ?? '—') ?></td></tr>
        </table>
        <?php if (!empty($ej['firma'])): ?>
            <div class="firma-box">
                <img src="<?= htmlspecialchars($ej['firma']) ?>" alt="Firma">
                <div class="firma-label">Firma del responsable de ejecución</div>
            </div>
        <?php endif; ?>
        <?php if (!empty($ej['saldo_pendiente'])): ?>
            <table>
                <tr class="estado-row pendiente">
                    <td>Quedó un saldo de <?= htmlspecialchars(number_format(floatval($ej['saldo_pendiente']), 1)) ?> KG sin procesar — se abrió como pendiente nuevo (ID: <?= htmlspecialchars($ej['id_pendiente_generado'] ?? '—') ?>).</td>
                </tr>
            </table>
        <?php endif; ?>
    <?php else: ?>
        <table><tr><td style="text-align:center; color:#64748B; font-style:italic; padding:20px;">Este reproceso aún no ha sido ejecutado.</td></tr></table>
    <?php endif; ?>

</div>

</body>
</html>
