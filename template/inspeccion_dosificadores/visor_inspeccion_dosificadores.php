<?php
require '../sesion.php';

if (!isset($_SESSION['nombre']) || empty($_SESSION['sede'])) {
    header('Location: ../../index.php');
    exit;
}

$sede        = $_SESSION['sede'];
$target_file = $_GET['file'] ?? '';
$id_registro = $_GET['id']   ?? '';

if (empty($target_file)) {
    die("Archivo no especificado. Vuelva a la Galería.");
}

$ruta_json = "../../archivos/generados/inspeccion_dosificadores/"
           . preg_replace('/[^A-Za-z0-9_-]/', '', $sede) . "/"
           . basename($target_file);

if (!file_exists($ruta_json)) {
    die("El archivo no existe o fue eliminado.");
}

$todos = json_decode(file_get_contents($ruta_json), true) ?: [];

if (empty($todos)) {
    die("El archivo está vacío.");
}

if ($id_registro) {
    $registros = array_values(array_filter($todos, fn($r) => ($r['id_registro'] ?? '') === $id_registro));
    if (empty($registros)) $registros = $todos;
} else {
    $registros = $todos;
}

usort($registros, function ($a, $b) {
    $fA = $a['datos']['fecha'] ?? '';
    $fB = $b['datos']['fecha'] ?? '';
    if ($fA !== $fB) return strtotime($fA) <=> strtotime($fB);
    return strtotime($a['timestamp'] ?? '') <=> strtotime($b['timestamp'] ?? '');
});

$periodo = str_replace(['DOSIFICADORES_', '.json'], '', basename($target_file));

function ie($v) { return $v !== null && $v !== '' ? htmlspecialchars($v) : '—'; }

function cumpleBadge($val) {
    if ($val === 'CUMPLE')    return '<span style="color:#166534;font-weight:700;">CUMPLE</span>';
    if ($val === 'NO CUMPLE') return '<span style="color:#991B1B;font-weight:700;">NO CUMPLE</span>';
    if ($val === 'N/A')       return '<span style="color:#92400E;font-weight:700;">N/A</span>';
    return '<span style="color:#9CA3AF;">—</span>';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visor - Inspección de Dosificadores | <?= htmlspecialchars($periodo) ?></title>
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

        .action-bar .left { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }

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

        .doc-label { color: #E2E8F0; font-size: 13px; font-weight: 600; padding: 0 8px; border-left: 2px solid var(--accent); }

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

        .page-wrap { max-width: 99%; margin: 0 auto; }

        .registro-block {
            background: var(--white);
            padding: 20px 24px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin-bottom: 24px;
        }

        table { width: 100%; border-collapse: collapse; }
        td, th { border: 1px solid var(--border); padding: 6px 8px; vertical-align: middle; }

        .header-table td { border: 1px solid #000; vertical-align: middle; padding: 8px 10px; }
        .header-title-main { font-size: 11pt; font-weight: 700; }
        .header-title-doc  { font-size: 10.5pt; font-style: italic; margin-top: 3px; }

        .iso-meta { width: 100%; border-collapse: collapse; height: 100%; }
        .iso-meta td { border: 0; border-bottom: 1px solid #000; padding: 3px 8px; font-size: 8pt; text-align: left; }
        .iso-meta tr:last-child td { border-bottom: 0; }
        .iso-meta td:first-child { font-weight: 700; border-right: 1px solid #000; width: 45%; }

        .sec-title {
            background: var(--blue); color: #fff;
            font-size: 9pt; font-weight: 700; text-transform: uppercase;
            padding: 5px 8px; letter-spacing: 0.5px;
        }

        .spec-label {
            background: #D9E1F2;
            font-size: 8.5pt; font-weight: 700;
            width: 22%;
        }
        .spec-val { font-size: 9pt; width: 11.3%; }

        .pruebas-hdr { background: #1E3A5F; color: #fff; font-size: 8pt; font-weight: 700; text-align: center; }
        .pruebas-num { background: #F1F5F9; font-weight: 700; text-align: center; width: 8%; }
        .pruebas-val { text-align: center; width: 12%; }

        .criterio-label { background: #D9E1F2; font-weight: 700; font-size: 8.5pt; width: 18%; }
        .criterio-desc { font-size: 8pt; text-align: left; padding: 8px 10px; }

        .resumen-label { background: #F1F5F9; font-weight: 700; font-size: 8.5pt; }
        .resumen-val { font-size: 9pt; text-align: center; }

        .plan-row td, .obs-row td { font-size: 8.5pt; text-align: left; padding: 8px 10px; }

        .meta-row { font-size: 8pt; color: #444; padding: 6px 10px; text-align: left; }

        .separator { height: 12px; }

        @media print {
            body        { background: #fff; padding: 0; }
            .action-bar { display: none !important; }
            .registro-block { box-shadow: none; padding: 10px; margin-bottom: 12px; }
            @page { size: A4; margin: 8mm; }
        }
    </style>
</head>
<body>

<div class="action-bar">
    <div class="left">
        <a href="rev_inspeccion_dosificadores.php" class="btn-back">← Volver al Listado</a>
        <span class="doc-label">Período: <?= htmlspecialchars($periodo) ?> | <?= count($registros) ?> registro<?= count($registros) !== 1 ? 's' : '' ?></span>
    </div>
    <button class="btn-print" onclick="window.print()">🖨️ IMPRIMIR / PDF</button>
</div>

<div class="page-wrap">
<?php foreach ($registros as $idx => $reg):
    $d = $reg['datos'];
    $fecha_fmt = !empty($d['fecha']) ? date('d/m/Y', strtotime($d['fecha'])) : '—';
?>

<div class="registro-block">

    <!-- ENCABEZADO INSTITUCIONAL -->
    <table class="header-table" style="margin-bottom:0;">
        <tr>
            <td style="width:18%; text-align:center; padding:10px;">
                <img src="/img/logo_empresa.jpeg" alt="Logo MAS" style="max-height:60px; max-width:100%; object-fit:contain;">
            </td>
            <td style="width:60%; text-align:center; padding:10px;">
                <div class="header-title-main">PPR Metrología</div>
                <div class="header-title-doc">"Inspección del Funcionamiento de los Dosificadores"</div>
            </td>
            <td style="width:22%; padding:0; vertical-align:top;">
                <table class="iso-meta" style="height:100%;">
                    <tr><td>Código:</td><td>GM-MM-ME-MR-FO-009C</td></tr>
                    <tr><td>Versión:</td><td>3</td></tr>
                    <tr><td>Fecha:</td><td>1/07/2022</td></tr>
                    <tr><td>Página:</td><td>1 de 2</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="meta-row" style="border:1px solid #000; border-top:0;">
        Sede: <strong><?= htmlspecialchars($reg['sede_sys'] ?? $sede) ?></strong> &nbsp;|&nbsp;
        Registrado: <?= htmlspecialchars($reg['timestamp'] ?? '—') ?> &nbsp;|&nbsp;
        ID: <?= htmlspecialchars($reg['id_registro'] ?? '—') ?>
    </div>

    <!-- 1. ESPECIFICACIONES -->
    <table style="margin-top:10px;">
        <tr><td class="sec-title" colspan="6">1. Especificaciones</td></tr>
        <tr>
            <td class="spec-label">Fecha:</td><td class="spec-val"><?= ie($fecha_fmt) ?></td>
            <td class="spec-label">Dosificador:</td><td class="spec-val"><?= ie($d['dosificador'] ?? null) ?></td>
            <td class="spec-label">Microingrediente:</td><td class="spec-val"><?= ie($d['microingrediente'] ?? null) ?></td>
        </tr>
        <tr>
            <td class="spec-label">Cantidad Bulto 50kg:</td><td class="spec-val"><?= ie($d['cantidad_bulto_50kg'] ?? null) ?></td>
            <td class="spec-label">Carga Trigo:</td><td class="spec-val"><?= ie($d['carga_trigo'] ?? null) ?></td>
            <td class="spec-label">Extracción (%):</td><td class="spec-val"><?= ie($d['extraccion_pct'] ?? null) ?></td>
        </tr>
        <tr>
            <td class="spec-label">Bultos por hora:</td><td class="spec-val"><?= ie($d['bultos_por_hora'] ?? null) ?></td>
            <td class="spec-label">Microingrediente por minuto:</td><td class="spec-val"><?= ie($d['micro_por_minuto'] ?? null) ?></td>
            <td class="spec-label">Microingrediente por hora:</td><td class="spec-val"><?= ie($d['micro_por_hora'] ?? null) ?></td>
        </tr>
        <tr>
            <td class="spec-label">Micro. por min. límite inferior:</td><td class="spec-val"><?= ie($d['micro_min_limite_inferior'] ?? null) ?></td>
            <td class="spec-label">Micro. por min. límite superior:</td><td class="spec-val"><?= ie($d['micro_min_limite_superior'] ?? null) ?></td>
            <td class="spec-label">Micro. por hora límite inferior:</td><td class="spec-val"><?= ie($d['micro_hora_limite_inferior'] ?? null) ?></td>
        </tr>
        <tr>
            <td class="spec-label">Porcentaje dosificador:</td><td class="spec-val"><?= ie($d['porcentaje_dosificador'] ?? null) ?></td>
            <td class="spec-label">Frecuencia dosificador:</td><td class="spec-val"><?= ie($d['frecuencia_dosificador'] ?? null) ?></td>
            <td class="spec-label">Micro. por hora límite superior:</td><td class="spec-val"><?= ie($d['micro_hora_limite_superior'] ?? null) ?></td>
        </tr>
        <tr>
            <td class="spec-label">Inspeccionado por:</td><td class="spec-val" colspan="3"><?= ie($d['inspeccionado_por'] ?? null) ?></td>
            <td class="spec-label">Verificado por:</td><td class="spec-val"><?= ie($d['verificado_por'] ?? null) ?></td>
        </tr>
    </table>

    <!-- 2. RESULTADOS -->
    <table style="margin-top:0;">
        <tr><td class="sec-title" colspan="4">2. Resultados</td></tr>
        <tr>
            <th class="pruebas-hdr" colspan="4">Gramos por Minuto</th>
        </tr>
        <tr>
            <th class="pruebas-hdr">N° Prueba</th><th class="pruebas-hdr">Gramos</th>
            <th class="pruebas-hdr">N° Prueba</th><th class="pruebas-hdr">Gramos</th>
        </tr>
        <?php for ($i = 1; $i <= 5; $i++): $j = $i + 5; ?>
        <tr>
            <td class="pruebas-num"><?= $i ?></td><td class="pruebas-val"><?= ie($d['gramos_prueba_' . $i] ?? null) ?></td>
            <td class="pruebas-num"><?= $j ?></td><td class="pruebas-val"><?= ie($d['gramos_prueba_' . $j] ?? null) ?></td>
        </tr>
        <?php endfor; ?>
        <tr>
            <td class="resumen-label">Promedio Min:</td><td class="resumen-val"><?= ie($d['promedio_min'] ?? null) ?></td>
            <td class="resumen-label">Gramos hora:</td><td class="resumen-val"><?= ie($d['gramos_hora'] ?? null) ?></td>
        </tr>
        <tr>
            <td class="resumen-label">¿Cumple?</td><td class="resumen-val" colspan="3"><?= cumpleBadge($d['cumple'] ?? null) ?></td>
        </tr>
    </table>

    <!-- CRITERIOS DE ACEPTACIÓN -->
    <table style="margin-top:0;">
        <tr><td class="sec-title" colspan="2">Criterios de Aceptación</td></tr>
        <tr>
            <td class="criterio-label">Fortificación colombiana:</td>
            <td class="criterio-desc">El promedio de gramos por minuto y los gramos hora no deben estar por debajo de su respectiva especificación.</td>
        </tr>
        <tr>
            <td class="criterio-label">Fortificación ecuatoriana:</td>
            <td class="criterio-desc">El promedio de gramos por minuto y los gramos hora pueden variar ± 4% respecto a su especificación.</td>
        </tr>
        <tr>
            <td class="criterio-label">Mejoradores de harina:</td>
            <td class="criterio-desc">El promedio de gramos por minuto y los gramos hora no deben estar por debajo de su especificación y máximo 2% por encima.</td>
        </tr>
    </table>

    <!-- PLAN DE ACCIÓN Y OBSERVACIONES -->
    <table style="margin-top:0;">
        <tr class="plan-row">
            <td style="width:22%; font-weight:700; background:#D9E1F2;">Plan de Acción en caso de NC:</td>
            <td>Ajustar microdosificador y verificar nuevamente el muestreo por minuto.</td>
        </tr>
        <tr class="obs-row">
            <td style="width:22%; font-weight:700; background:#D9E1F2;">Observaciones:</td>
            <td><?= nl2br(ie($d['observaciones'] ?? null)) ?></td>
        </tr>
    </table>

</div>

<?php if ($idx < count($registros) - 1): ?>
    <div class="separator"></div>
<?php endif; ?>

<?php endforeach; ?>
</div>

</body>
</html>
