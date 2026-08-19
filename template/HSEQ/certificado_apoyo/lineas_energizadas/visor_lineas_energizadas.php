<?php
include '../../../sesion.php';

if (!isset($_SESSION['nombre']) || empty($_SESSION['sede'])) {
    header('Location: ../../../../index.php');
    exit;
}

$sede        = $_SESSION['sede'];
$target_file = $_GET['file'] ?? '';
$id_registro = $_GET['id']   ?? '';

if (empty($target_file)) die("Archivo no especificado.");

$ruta_json = "../../../../archivos/generados/cert_apoyo_lineas_energ/"
           . preg_replace('/[^A-Za-z0-9_-]/', '', $sede) . "/"
           . basename($target_file);

if (!file_exists($ruta_json)) die("El archivo no existe o fue eliminado.");

$todos = json_decode(file_get_contents($ruta_json), true) ?: [];
if (empty($todos)) die("El archivo está vacío.");

if ($id_registro) {
    $registros = array_values(array_filter($todos, fn($r) => ($r['id_registro'] ?? '') === $id_registro));
    if (empty($registros)) $registros = $todos;
} else {
    $registros = $todos;
}

usort($registros, fn($a, $b) => strcmp($a['datos']['fecha'] ?? '', $b['datos']['fecha'] ?? ''));

$periodo = str_replace(['LENE_', '.json'], '', basename($target_file));

function badge_ctrl($val) {
    if ($val === 'CUMPLE')    return '<span style="color:#10B981;font-weight:700;">CUMPLE</span>';
    if ($val === 'NO CUMPLE') return '<span style="color:#FF3366;font-weight:700;">NO CUMPLE</span>';
    if ($val === 'N/A')       return '<span style="color:#94A3B8;">N/A</span>';
    if ($val === 'SI')        return '<span style="color:#10B981;font-weight:700;">SI</span>';
    if ($val === 'NO')        return '<span style="color:#FF3366;font-weight:700;">NO</span>';
    return '<span style="color:#64748B;">—</span>';
}

$CONTROLES_LABELS = [
    'analisis_riesgo' => 'Análisis de riesgo eléctrico y arco eléctrico',
    'distancias'      => 'Distancias de seguridad',
    'herramientas'    => 'Uso de herramientas aisladas certificadas',
    'proteccion_arco' => 'Equipos de protección contra arco eléctrico',
    'guantes'         => 'Guantes dieléctricos certificados',
    'delimitacion'    => 'Delimitación y control del área',
    'supervision'     => 'Supervisión permanente',
    'personal'        => 'Personal altamente competente y autorizado',
    'aislantes'       => 'Verificación del estado de los elementos aislantes',
    'emergencia'      => 'Plan de emergencia y respuesta',
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Líneas Energizadas — <?= htmlspecialchars($periodo) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Roboto',sans-serif; background:#E2E8F0; padding:20px; color:#000; }
        .action-bar {
            max-width:99%; margin:0 auto 20px; display:flex; justify-content:space-between; align-items:center;
            background:#0F172A; padding:14px 24px; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.3);
            gap:10px; flex-wrap:wrap;
        }
        .action-bar .left { display:flex; gap:10px; align-items:center; }
        .btn-back {
            background:transparent; border:1px solid #EF4444; color:#EF4444;
            text-decoration:none; padding:9px 18px; border-radius:4px;
            font-weight:700; font-size:12px; text-transform:uppercase; transition:all 0.2s;
        }
        .btn-back:hover { background:rgba(239,68,68,0.1); }
        .doc-label { color:#E2E8F0; font-size:13px; font-weight:600; padding:0 8px; border-left:2px solid #EF4444; }
        .btn-print {
            background:#10B981; color:#fff; border:none; padding:9px 18px; border-radius:4px;
            font-weight:700; font-size:12px; text-transform:uppercase; cursor:pointer; transition:background 0.2s;
        }
        .btn-print:hover { background:#059669; }
        .page-wrap { max-width:99%; margin:0 auto; }
        .registro-block { background:#fff; padding:20px 24px; box-shadow:0 4px 20px rgba(0,0,0,0.15); margin-bottom:24px; }
        table { width:100%; border-collapse:collapse; }
        td, th { border:1px solid #000; padding:4px 6px; vertical-align:middle; }
        .header-table td { border:1px solid #000; vertical-align:middle; padding:8px 10px; }
        .header-title-main { font-size:9.5pt; font-weight:600; margin-bottom:3px; }
        .header-title-doc  { font-size:11pt; font-weight:700; }
        .iso-meta { width:100%; border-collapse:collapse; height:100%; }
        .iso-meta td { border:0; border-bottom:1px solid #000; padding:3px 8px; font-size:7.5pt; }
        .iso-meta tr:last-child td { border-bottom:0; }
        .iso-meta td:first-child { font-weight:700; border-right:1px solid #000; width:45%; }
        .sec-title { background:#003366; color:#fff; font-size:8pt; font-weight:700; text-transform:uppercase; text-align:center; letter-spacing:1px; }
        .sec-title td { border:1px solid #000; padding:5px 8px; }
        .lbl { background:#D9E1F2; font-size:7.5pt; font-weight:700; text-align:right; padding:4px 8px; width:18%; }
        .val { font-size:8pt; padding:4px 8px; }
        .val-wide { font-size:8pt; padding:4px 8px; }
        .ctrl-hdr th { background:#003366; color:#fff; font-size:7pt; padding:5px 4px; text-transform:uppercase; }
        .ctrl-row td { font-size:8pt; padding:4px 6px; }
        .ctrl-row:nth-child(even) td { background:#FFF5F5; }
        .separator { height:12px; }
        @media print {
            body { background:#fff; padding:0; }
            .action-bar { display:none !important; }
            .registro-block { box-shadow:none; padding:10px; margin-bottom:12px; }
            @page { size:A4; margin:8mm; }
        }
    </style>
</head>
<body>

<div class="action-bar">
    <div class="left">
        <a href="rev_lineas_energizadas.php" class="btn-back">← Listado</a>
        <span class="doc-label">Período: <?= htmlspecialchars($periodo) ?> | <?= count($registros) ?> registro<?= count($registros) !== 1 ? 's' : '' ?></span>
    </div>
    <button class="btn-print" onclick="window.print()">🖨️ IMPRIMIR / PDF</button>
</div>

<div class="page-wrap">
<?php foreach ($registros as $idx => $reg):
    $d = $reg['datos'];
    $fecha_fmt = !empty($d['fecha']) ? date('d/m/Y', strtotime($d['fecha'])) : '—';
    $controles = $d['controles'] ?? [];
?>
<div class="registro-block">

    <table class="header-table" style="margin-bottom:0;">
        <tr>
            <td style="width:18%;text-align:center;padding:10px;">
                <img src="/img/logo_empresa.jpeg" alt="Logo MAS" style="max-height:70px;max-width:100%;object-fit:contain;">
            </td>
            <td style="width:60%;text-align:center;padding:10px;">
                <div class="header-title-main">Sistema de Gestión HSEQ — Trabajo Seguro</div>
                <div class="header-title-doc">CERTIFICADO DE APOYO — TRABAJO LÍNEAS ENERGIZADAS</div>
                <div style="font-size:9pt;margin-top:4px;">Sede: <?= htmlspecialchars($reg['sede_sys'] ?? $sede) ?> &nbsp;|&nbsp; Registrado: <?= htmlspecialchars($reg['timestamp'] ?? '—') ?></div>
            </td>
            <td style="width:22%;padding:0;vertical-align:top;">
                <table class="iso-meta" style="height:100%;">
                    <tr><td>Código:</td><td>HSEQ-CA-LENE-001</td></tr>
                    <tr><td>Versión:</td><td>1</td></tr>
                    <tr><td>ID:</td><td style="font-size:7pt;"><?= htmlspecialchars($reg['id_registro'] ?? '—') ?></td></tr>
                    <tr><td>Impreso:</td><td><?= date('d/m/Y') ?></td></tr>
                </table>
            </td>
        </tr>
    </table>

    <table style="margin-top:0;">
        <tr class="sec-title"><td colspan="8">GENERALIDADES</td></tr>
        <tr>
            <td class="lbl">Fecha</td><td class="val"><?= htmlspecialchars($fecha_fmt) ?></td>
            <td class="lbl">Hora</td><td class="val"><?= htmlspecialchars($d['hora'] ?? '—') ?></td>
            <td class="lbl">Valoración del Riesgo</td><td class="val"><?= htmlspecialchars($d['valoracion_riesgo'] ?? '—') ?></td>
            <td class="lbl">Frecuencia</td><td class="val"><?= htmlspecialchars($d['frecuencia'] ?? '—') ?></td>
        </tr>
        <tr>
            <td class="lbl">Zona de Trabajo</td><td class="val"><?= htmlspecialchars($d['zona_trabajo'] ?? '—') ?></td>
            <td class="lbl">Dependencia</td><td class="val" colspan="5"><?= htmlspecialchars($d['dependencia'] ?? '—') ?></td>
        </tr>
        <tr>
            <td class="lbl">Equipo / Sistema</td>
            <td class="val-wide" colspan="7"><?= htmlspecialchars($d['equipo_sistema'] ?? '—') ?></td>
        </tr>
    </table>

    <table style="margin-top:0;">
        <tr class="sec-title"><td colspan="2">DESCRIPCIÓN DE LA ACTIVIDAD A REALIZAR</td></tr>
        <tr><td style="font-size:8pt;padding:8px;min-height:50px;" colspan="2"><?= nl2br(htmlspecialchars($d['descripcion_actividad'] ?? '—')) ?></td></tr>
    </table>

    <table style="margin-top:0;">
        <tr class="sec-title"><td colspan="4">¿LAS LABORES INCLUYEN ACTIVIDADES CRÍTICAS ADICIONALES?</td></tr>
        <tr>
            <td class="lbl" style="width:55%;">¿Incluye actividades críticas adicionales?</td>
            <td class="val" style="width:15%;"><?= badge_ctrl($d['tiene_actividades_criticas'] ?? '') ?></td>
            <td class="lbl" style="width:10%;">Actividad(es)</td>
            <td class="val"><?= htmlspecialchars($d['actividades_criticas'] ?? '—') ?></td>
        </tr>
    </table>

    <table style="margin-top:0;">
        <tr class="sec-title"><td colspan="3">PLANEACIÓN Y CONTROLES</td></tr>
        <tr class="ctrl-hdr">
            <th style="width:55%;">Ítem de Control</th>
            <th style="width:15%;">Resultado</th>
            <th>Observación</th>
        </tr>
        <?php foreach ($CONTROLES_LABELS as $key => $label):
            $ctrl = $controles[$key] ?? [];
        ?>
        <tr class="ctrl-row">
            <td><?= htmlspecialchars($label) ?></td>
            <td style="text-align:center;"><?= badge_ctrl($ctrl['resultado'] ?? '') ?></td>
            <td><?= htmlspecialchars($ctrl['observacion'] ?? '') ?></td>
        </tr>
        <?php endforeach; ?>
    </table>


</div>
<?php if ($idx < count($registros) - 1): ?><div class="separator"></div><?php endif; ?>
<?php endforeach; ?>
</div>
</body>
</html>
