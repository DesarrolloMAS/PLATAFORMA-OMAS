<?php
require '../sesion.php';

if (!isset($_SESSION['nombre']) || empty($_SESSION['sede'])) {
    header('Location: ../../index.php');
    exit;
}

$sede        = $_SESSION['sede'];
$target_file = $_GET['file'] ?? '';

if (empty($target_file)) {
    die("Archivo no especificado. Vuelva a la Galería.");
}

$ruta_json = "../../archivos/generados/preparacion_mejorante/"
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
    $fA = $a['datos']['fecha'] ?? '';
    $fB = $b['datos']['fecha'] ?? '';
    if ($fA !== $fB) return strtotime($fA) <=> strtotime($fB);
    return strtotime($a['timestamp'] ?? '') <=> strtotime($b['timestamp'] ?? '');
});

$periodo = str_replace(['PMEJ_', '.json'], '', basename($target_file));

$MEJORANTES_FIJOS = [
    'Grindamyl A1000',
    'Powerbake 7200',
    'ADA 50%',
    'Ácido Ascórbico',
    'Surebake 900',
    'Powerbake 4200',
    'Granozyme OXD',
];
$FILAS_EXTRA = 3;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visor - Preparación de Mejorante | <?= htmlspecialchars($periodo) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --navy:   #0F172A;
            --blue:   #003366;
            --white:  #FFFFFF;
            --border: #000000;
            --accent: #00F0FF;
            --gray-header: #D1D5DB;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Roboto', sans-serif;
            background: #E2E8F0;
            padding: 20px;
            color: #000;
        }

        /* ── Barra de acción ── */
        .action-bar {
            max-width: 860px;
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

        .action-bar .info { color: #94A3B8; font-size: 13px; }
        .action-bar .info strong { color: var(--accent); }

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
            letter-spacing: 0.5px;
            transition: all 0.2s;
        }
        .btn-back:hover { background: rgba(0,240,255,0.1); }

        .btn-print {
            background: #10B981;
            color: #fff;
            border: none;
            padding: 9px 18px;
            border-radius: 4px;
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-print:hover { background: #059669; }

        /* ── Separador de registros ── */
        .registro-sep {
            max-width: 860px;
            margin: 0 auto 10px auto;
            display: flex;
            align-items: center;
            gap: 12px;
            color: #475569;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .registro-sep::before, .registro-sep::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #CBD5E1;
        }

        /* ── Documento (hoja blanca) ── */
        .page-wrap {
            max-width: 860px;
            margin: 0 auto 30px auto;
            background: var(--white);
            padding: 20px 24px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }

        /* ── Tablas generales ── */
        table { width: 100%; border-collapse: collapse; }

        td, th {
            border: 1px solid var(--border);
            padding: 4px 6px;
            vertical-align: middle;
        }

        /* ── Encabezado institucional ── */
        .header-table td {
            padding: 6px 10px;
            vertical-align: middle;
        }

        .header-center-top {
            font-size: 8pt;
            font-weight: 600;
            color: #374151;
            margin-bottom: 2px;
        }
        .header-center-main {
            font-size: 11pt;
            font-weight: 700;
            text-align: center;
        }

        .iso-meta { border-collapse: collapse; height: 100%; }
        .iso-meta td {
            border: 0;
            border-bottom: 1px solid #000;
            padding: 3px 6px;
            font-size: 7pt;
            text-align: left;
        }
        .iso-meta tr:last-child td { border-bottom: 0; }
        .iso-meta td:first-child { font-weight: 700; border-right: 1px solid #000; width: 50%; }

        /* ── Bloque de campos del encabezado del formulario ── */
        .form-fields {
            margin-top: 0;
            font-size: 8.5pt;
        }
        .form-fields td {
            padding: 5px 8px;
        }
        .field-label {
            font-weight: 700;
            background: #F1F5F9;
            width: 38%;
            white-space: nowrap;
        }
        .field-value {
            width: 62%;
        }

        /* ── Tabla de mejorantes ── */
        .tabla-mejorantes { margin-top: 0; font-size: 8.5pt; }

        .tabla-mejorantes thead th {
            background: #D1D5DB;
            font-weight: 700;
            font-size: 8pt;
            text-align: center;
            padding: 6px 4px;
        }
        .tabla-mejorantes thead th.col-nombre { text-align: left; padding-left: 8px; width: 40%; }
        .tabla-mejorantes thead th.col-lote    { width: 18%; }
        .tabla-mejorantes thead th.col-venc    { width: 24%; }
        .tabla-mejorantes thead th.col-cant    { width: 18%; }

        .tabla-mejorantes tbody td {
            padding: 5px 6px;
            font-size: 8.5pt;
            vertical-align: middle;
        }
        .tabla-mejorantes tbody td.td-nombre { font-weight: 600; }
        .tabla-mejorantes tbody td.td-center { text-align: center; }
        .tabla-mejorantes tbody tr.fila-extra td { height: 20px; }
        .tabla-mejorantes tbody tr:nth-child(even) { background: #F9FAFB; }

        /* ── Bloque de totales / resumen ── */
        .tabla-resumen { margin-top: 0; font-size: 8.5pt; }
        .tabla-resumen td { padding: 5px 8px; }
        .tabla-resumen .res-label {
            font-weight: 700;
            background: #D1D5DB;
            width: 38%;
            white-space: nowrap;
        }
        .tabla-resumen .res-value { width: 62%; }

        /* ── Observaciones ── */
        .obs-block {
            border: 1px solid #000;
            border-top: 0;
            padding: 8px 10px;
            font-size: 8.5pt;
            min-height: 45px;
        }
        .obs-label { font-weight: 700; margin-bottom: 4px; }

        /* ── Meta del registro ── */
        .registro-meta {
            margin-top: 8px;
            font-size: 7pt;
            color: #64748B;
            text-align: right;
            font-style: italic;
        }

        /* ── Separador vacío entre docs ── */
        .page-break-hint { page-break-after: always; }

        @media print {
            body        { background: #fff; padding: 0; }
            .action-bar  { display: none !important; }
            .registro-sep { display: none !important; }
            .page-wrap   { box-shadow: none; padding: 10px; max-width: 100%; margin-bottom: 0; }
            .page-break-hint { page-break-after: always; }
            @page { size: portrait; margin: 8mm; }
        }
    </style>
</head>
<body>

<!-- BARRA DE ACCIONES -->
<div class="action-bar">
    <a href="rev_preparacion_mejorante.php" class="btn-back">← Volver al Listado</a>
    <div class="info">
        Período: <strong><?= htmlspecialchars($periodo) ?></strong>
        &nbsp;|&nbsp; Sede: <strong><?= htmlspecialchars($sede) ?></strong>
        &nbsp;|&nbsp; <?= count($registros) ?> preparación<?= count($registros) !== 1 ? 'es' : '' ?>
    </div>
    <button class="btn-print" onclick="window.print()">🖨 IMPRIMIR / GUARDAR PDF</button>
</div>

<?php foreach ($registros as $idx => $reg):
    $d = $reg['datos'] ?? [];

    $fecha           = $d['fecha']            ?? '';
    $referencia      = $d['referencia']        ?? '';
    $lote            = $d['lote']              ?? '';
    $vence           = $d['vence']             ?? '';
    $tiempo_mezcla   = $d['tiempo_mezcla_min'] ?? '';
    $total           = $d['total']             ?? '';
    $devolucion      = $d['devolucion']        ?? '';
    $realiza         = $d['realiza']           ?? '';
    $verifica        = $d['verifica']          ?? '';
    $observaciones   = $d['observaciones']     ?? '';
    $mejorantes_data = $d['mejorantes']        ?? [];

    // Indexar mejorantes guardados por nombre para búsqueda rápida
    $mej_index = [];
    foreach ($mejorantes_data as $m) {
        $mej_index[$m['nombre']] = $m;
    }

    // Fecha formateada
    $fecha_fmt = $fecha ? date('d/m/Y', strtotime($fecha)) : '—';
    $vence_fmt = $vence ? date('d/m/Y', strtotime($vence)) : '—';
?>

<?php if ($idx > 0): ?>
<div class="registro-sep">Preparación <?= $idx + 1 ?> de <?= count($registros) ?></div>
<?php endif; ?>

<div class="page-wrap <?= ($idx < count($registros) - 1) ? 'page-break-hint' : '' ?>">

    <!-- ENCABEZADO INSTITUCIONAL -->
    <table class="header-table" style="margin-bottom:0;">
        <tr>
            <td style="width:16%; text-align:center; padding:8px;">
                <img src="/img/logo_empresa.jpeg" alt="Logo MAS"
                     style="max-height:62px; max-width:100%; object-fit:contain;">
            </td>
            <td style="width:62%; text-align:center; padding:8px;">
                <div class="header-center-top">PPR Gestión de la Producción</div>
                <div class="header-center-top">Procedimiento de Preparación de Insumos</div>
                <div class="header-center-main">Preparación de Mejorante</div>
            </td>
            <td style="width:22%; padding:0; vertical-align:top;">
                <table class="iso-meta">
                    <tr><td>Código:</td>    <td>GP-PD-PR-GP-FO-XXX</td></tr>
                    <tr><td>Versión:</td>   <td>1</td></tr>
                    <tr><td>Fecha:</td>     <td>01/01/2024</td></tr>
                    <tr><td>Página:</td>    <td>1 de 1</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- CAMPOS DE ENCABEZADO DEL FORMULARIO -->
    <table class="form-fields">
        <tr>
            <td class="field-label">Fecha</td>
            <td class="field-value"><?= htmlspecialchars($fecha_fmt) ?></td>
        </tr>
        <tr>
            <td class="field-label">Referencia</td>
            <td class="field-value"><?= htmlspecialchars($referencia) ?></td>
        </tr>
        <tr>
            <td class="field-label">Lote</td>
            <td class="field-value"><?= htmlspecialchars($lote) ?></td>
        </tr>
        <tr>
            <td class="field-label">Vence</td>
            <td class="field-value"><?= htmlspecialchars($vence_fmt) ?></td>
        </tr>
        <tr>
            <td class="field-label">Tiempo Mezcla (Min)</td>
            <td class="field-value"><?= htmlspecialchars($tiempo_mezcla) ?></td>
        </tr>
    </table>

    <!-- TABLA DE MEJORANTES -->
    <table class="tabla-mejorantes">
        <thead>
            <tr>
                <th class="col-nombre">MEJORANTE</th>
                <th class="col-lote">Lote</th>
                <th class="col-venc">Fecha Vencimiento</th>
                <th class="col-cant">Cantidad</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($MEJORANTES_FIJOS as $nombre_fijo):
                $entry      = $mej_index[$nombre_fijo] ?? null;
                $m_lote     = $entry ? ($entry['lote']      ?? '') : '';
                $m_venc_raw = $entry ? ($entry['fecha_venc'] ?? '') : '';
                $m_cantidad = $entry ? ($entry['cantidad']   ?? '') : '';
                $m_venc_fmt = $m_venc_raw ? date('d/m/Y', strtotime($m_venc_raw)) : '';
            ?>
            <tr>
                <td class="td-nombre"><?= htmlspecialchars($nombre_fijo) ?></td>
                <td class="td-center"><?= htmlspecialchars($m_lote) ?></td>
                <td class="td-center"><?= htmlspecialchars($m_venc_fmt) ?></td>
                <td class="td-center"><?= htmlspecialchars($m_cantidad) ?></td>
            </tr>
            <?php endforeach; ?>

            <?php
            // Mejorantes adicionales (no fijos) guardados en el registro
            $nombres_fijos_set = array_flip($MEJORANTES_FIJOS);
            $extras = array_filter($mejorantes_data, fn($m) => !isset($nombres_fijos_set[$m['nombre'] ?? '']));
            foreach ($extras as $ex):
                $ex_venc = isset($ex['fecha_venc']) && $ex['fecha_venc'] ? date('d/m/Y', strtotime($ex['fecha_venc'])) : '';
            ?>
            <tr>
                <td class="td-nombre"><?= htmlspecialchars($ex['nombre'] ?? '') ?></td>
                <td class="td-center"><?= htmlspecialchars($ex['lote'] ?? '') ?></td>
                <td class="td-center"><?= htmlspecialchars($ex_venc) ?></td>
                <td class="td-center"><?= htmlspecialchars($ex['cantidad'] ?? '') ?></td>
            </tr>
            <?php endforeach; ?>

            <?php
            // Filas en blanco adicionales
            $usados = count($MEJORANTES_FIJOS) + count($extras);
            $total_filas_minimas = count($MEJORANTES_FIJOS) + $FILAS_EXTRA;
            $faltan = max(0, $total_filas_minimas - $usados);
            for ($i = 0; $i < $faltan; $i++):
            ?>
            <tr class="fila-extra">
                <td>&nbsp;</td><td></td><td></td><td></td>
            </tr>
            <?php endfor; ?>
        </tbody>
    </table>

    <!-- FILA EN BLANCO SEPARADORA -->
    <table style="margin-top:0;">
        <tr><td style="height:10px; border-left:1px solid #000; border-right:1px solid #000; border-bottom:1px solid #000; border-top:0;">&nbsp;</td></tr>
    </table>

    <!-- RESUMEN: TOTAL / DEVOLUCIÓN / REALIZÁ / VERIFICA -->
    <table class="tabla-resumen">
        <tr>
            <td class="res-label">TOTAL</td>
            <td class="res-value"><?= htmlspecialchars($total) ?></td>
        </tr>
        <tr>
            <td class="res-label">DEVOLUCIÓN</td>
            <td class="res-value"><?= htmlspecialchars($devolucion) ?></td>
        </tr>
        <tr>
            <td class="res-label">REALIZÁ</td>
            <td class="res-value"><?= htmlspecialchars($realiza) ?></td>
        </tr>
        <tr>
            <td class="res-label">VERIFICA</td>
            <td class="res-value"><?= htmlspecialchars($verifica) ?></td>
        </tr>
    </table>

    <!-- OBSERVACIONES -->
    <div class="obs-block">
        <div class="obs-label">OBSERVACIONES:</div>
        <?= nl2br(htmlspecialchars($observaciones)) ?>
    </div>

    <!-- META DEL SISTEMA -->
    <div class="registro-meta">
        Registrado por: <?= htmlspecialchars($reg['usuario_sys'] ?? '—') ?>
        &nbsp;|&nbsp; Sistema: <?= htmlspecialchars($reg['timestamp'] ?? '—') ?>
        &nbsp;|&nbsp; ID: <?= htmlspecialchars($reg['id_registro'] ?? '—') ?>
    </div>

</div><!-- /.page-wrap -->

<?php endforeach; ?>

</body>
</html>
