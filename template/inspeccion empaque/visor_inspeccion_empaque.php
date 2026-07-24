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

$ruta_json = "../../archivos/generados/inspeccion_empaque/"
           . preg_replace('/[^A-Za-z0-9_-]/', '', $sede) . "/"
           . basename($target_file);

if (!file_exists($ruta_json)) {
    die("El archivo no existe o fue eliminado.");
}

$registros = json_decode(file_get_contents($ruta_json), true) ?: [];

if (empty($registros)) {
    die("El archivo está vacío.");
}

// Ordenar cronológicamente
usort($registros, function($a, $b) {
    $fA = $a['datos']['fecha'] ?? '';
    $fB = $b['datos']['fecha'] ?? '';
    if ($fA !== $fB) return strtotime($fA) <=> strtotime($fB);
    return strtotime($a['timestamp'] ?? '') <=> strtotime($b['timestamp'] ?? '');
});

// Extraer el periodo del nombre del archivo para mostrarlo
$periodo = str_replace(['INSPECCION_', '.json'], '', basename($target_file));

// Helper: muestra el valor evaluado de forma legible y con color
function formatEval($val) {
    if ($val === '1' || $val === 1)   return '<span style="color:#166534;font-weight:700;">1</span>';
    if ($val === '0' || $val === 0)   return '<span style="color:#991B1B;font-weight:700;">0</span>';
    if ($val === 'NA')                return '<span style="color:#92400E;font-weight:700;">NA</span>';
    return '<span style="color:#9CA3AF;">—</span>';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visor - Inspección de Empaque | <?= htmlspecialchars($periodo) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

        /* ── ACTION BAR ── */
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

        /* ── DOCUMENT WRAP ── */
        .page-wrap {
            max-width: 99%;
            margin: 0 auto;
            background: var(--white);
            padding: 24px 28px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        /* ── SHARED TABLE RULES ── */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        td, th {
            border: 1px solid var(--border);
            padding: 4px 5px;
            vertical-align: middle;
            text-align: center;
        }

        /* ── INSTITUTIONAL HEADER ── */
        .header-table td {
            border: 1px solid #000;
            vertical-align: middle;
            padding: 8px 10px;
        }

        .header-title-main {
            font-size: 9.5pt;
            font-weight: 600;
            margin-bottom: 3px;
        }
        .header-title-doc {
            font-size: 11pt;
            font-weight: 700;
        }
        .header-title-sub {
            font-size: 9pt;
            font-style: italic;
            margin-top: 3px;
        }

        .iso-meta { width: 100%; border-collapse: collapse; height: 100%; }
        .iso-meta td {
            border: 0;
            border-bottom: 1px solid #000;
            padding: 3px 8px;
            font-size: 7.5pt;
            text-align: left;
        }
        .iso-meta tr:last-child td { border-bottom: 0; }
        .iso-meta td:first-child { font-weight: 700; border-right: 1px solid #000; width: 45%; }

        /* ── ZONA + INSTRUCCIONES ── */
        .zona-row {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0;
        }
        .zona-row td {
            border: 1px solid #000;
            padding: 5px 10px;
            font-size: 8pt;
        }
        .zona-label {
            font-weight: 700;
            width: 55px;
            background: #F1F5F9;
        }
        .instrucciones-cell {
            text-align: left;
            font-size: 7.5pt;
            line-height: 1.4;
        }
        .instrucciones-cell strong { color: #000; }

        /* ── DATA TABLE ── */
        .tabla-datos {
            margin-top: 0;
            font-size: 7pt;
        }

        .tabla-datos thead th {
            background: var(--blue);
            color: #fff;
            font-size: 6.5pt;
            font-weight: 600;
            text-transform: uppercase;
            line-height: 1.2;
            padding: 5px 3px;
        }

        .tabla-datos thead th.group-header {
            background: #1E3A5F;
            font-size: 7pt;
            letter-spacing: 0.3px;
        }

        .tabla-datos thead th.eval-header {
            background: #1a4080;
            font-size: 6pt;
            font-weight: 500;
            line-height: 1.25;
            padding: 4px 3px;
            writing-mode: vertical-rl;
            text-orientation: mixed;
            transform: rotate(180deg);
            height: 100px;
            vertical-align: bottom;
        }

        .tabla-datos tbody td {
            font-size: 7.5pt;
            padding: 5px 4px;
            vertical-align: middle;
        }

        .tabla-datos tbody tr:nth-child(even) { background: #F8FAFC; }
        .tabla-datos tbody tr:hover           { background: #EFF6FF; }

        .col-total {
            background: #EFF6FF !important;
            font-weight: 700;
            font-size: 8pt;
        }
        .col-porcentaje {
            font-weight: 700;
            font-size: 8pt;
        }
        .pct-good   { color: #166534; }
        .pct-warn   { color: #92400E; }
        .pct-danger { color: #991B1B; }

        /* ── EMPTY / INFO ROWS ── */
        .empty-row td {
            padding: 30px;
            color: #64748B;
            font-style: italic;
            font-size: 9pt;
        }

        /* ── PRINT ── */
        @media print {
            body        { background: #fff; padding: 0; }
            .action-bar { display: none !important; }
            .page-wrap  { box-shadow: none; padding: 10px; max-width: 100%; }
            @page       { size: landscape; margin: 8mm; }
        }
    </style>
</head>
<body>

<!-- ACTION BAR -->
<div class="action-bar">
    <div class="left">
        <a href="rev_inspeccion_empaque.php" class="btn-back">← Volver al Listado</a>
    </div>
    <button class="btn-print" onclick="window.print()">
        🖨️ IMPRIMIR / GUARDAR PDF
    </button>
</div>

<!-- DOCUMENT -->
<div class="page-wrap" id="document_content">

    <!-- ══ ENCABEZADO INSTITUCIONAL ══ -->
    <table class="header-table" style="margin-bottom:0;">
        <tr>
            <!-- LOGO -->
            <td style="width:18%; text-align:center; padding:10px;">
                <img src="/img/logo_empresa.jpeg" alt="Logo MAS"
                     style="max-height:70px; max-width:100%; object-fit:contain;">
            </td>
            <!-- TÍTULO -->
            <td style="width:60%; text-align:center; padding:10px;">
                <div class="header-title-main">PPR Gestión de la Producción</div>
                <div class="header-title-main" style="margin-bottom:4px;">Procedimiento Control de Materiales de Empaque</div>
                <div class="header-title-doc">"Inspección Control de Materiales de Empaque"</div>
            </td>
            <!-- ISO META -->
            <td style="width:22%; padding:0; vertical-align:top;">
                <table class="iso-meta" style="height:100%;">
                    <tr><td>Código:</td><td>GP-PD-PP-GP-FO-005</td></tr>
                    <tr><td>Versión:</td><td>2</td></tr>
                    <tr><td>Fecha:</td><td>8/5/2021</td></tr>
                    <tr><td>Página:</td><td>1 de 1</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- ══ ZONA + INSTRUCCIONES ══ -->
    <table class="zona-row">
        <tr>
            <td class="zona-label">ZONA:</td>
            <td style="width:120px; font-weight:700; font-size:9pt;"><?= htmlspecialchars($sede) ?></td>
            <td class="instrucciones-cell">
                <strong>INSTRUCCIONES PARA EL REGISTRO:</strong>
                Por cada ítem a evaluar, escribir <strong>1</strong> si cumple ó <strong>0</strong> si no cumple.
                Escribir <strong>NA</strong> sobre las casillas de los ítems que no aplican para la evaluación,
                y no incluirlos en el total de ítems evaluados.
            </td>
        </tr>
    </table>

    <!-- ══ TABLA MAESTRA DE INSPECCIONES ══ -->
    <table class="tabla-datos">
        <thead>
            <!-- FILA 1: Grupos de columnas -->
            <tr>
                <th rowspan="2" style="width:5%;">Fecha</th>
                <th rowspan="2" style="width:9%;">Referencia de Empaque</th>
                <th rowspan="2" style="width:6%;">Lote Interno</th>
                <th rowspan="2" style="width:8%;">Proveedor</th>
                <th colspan="9" class="group-header">ÍTEMS A EVALUAR</th>
                <th rowspan="2" style="width:4%;" class="group-header">TOTAL =<br>Suma de<br>ítems</th>
                <th rowspan="2" style="width:6%;" class="group-header">% de<br>Cumplimiento<br>= (Total /<br># ítems eval.)<br>× 100</th>
                <th rowspan="2" style="width:8%;">Responsable<br>Inspección</th>
            </tr>
            <!-- FILA 2: Cabeceras de ítems (vertical) -->
            <tr>
                <th class="eval-header" style="width:4%;">
                    Clasificación de empaques<br>(Empaque registrado en la lista de clasificación de empaques actualizada)
                </th>
                <th class="eval-header" style="width:4%;">
                    Especificaciones de compra<br>(disponible para la referencia de empaque)
                </th>
                <th class="eval-header" style="width:4%;">
                    Verificación de requisitos<br>(Según Herramienta de Verificación de Requisitos de Materiales de Empaque)
                </th>
                <th class="eval-header" style="width:4%;">
                    Soportes de Trazabilidad<br>(Reporte de Auditoría IN SITU y ejercicio de trazabilidad documentado de los lotes insumos recibidos)
                </th>
                <th class="eval-header" style="width:4%;">
                    Acta IVC del proveedor<br>(Emitida por el INVIMA)
                </th>
                <th class="eval-header" style="width:4%;">
                    Controles en Recepción<br>(Debidamente diligenciado)
                </th>
                <th class="eval-header" style="width:4%;">
                    Control de Alistamiento<br>(Debidamente diligenciado)
                </th>
                <th class="eval-header" style="width:4%;">
                    Comprobaciones en Línea de Envasado<br>(Debidamente diligenciado)
                </th>
                <th class="eval-header" style="width:4%;">
                    Gestión de Empaques Obsoletos<br>(Debidamente contabilizados y entregados para su disposición)
                </th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($registros)): ?>
            <tr class="empty-row">
                <td colspan="16">No hay registros de inspección para este período.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($registros as $reg):
                $d = $reg['datos'];

                // Leer valores de los 9 ítems
                $items = [
                    'eval_clasificacion'    => $d['eval_clasificacion']    ?? '',
                    'eval_especificaciones' => $d['eval_especificaciones']  ?? '',
                    'eval_verificacion'     => $d['eval_verificacion']      ?? '',
                    'eval_trazabilidad'     => $d['eval_trazabilidad']      ?? '',
                    'eval_acta_ivc'         => $d['eval_acta_ivc']          ?? '',
                    'eval_recepcion'        => $d['eval_recepcion']         ?? '',
                    'eval_alistamiento'     => $d['eval_alistamiento']      ?? '',
                    'eval_envasado'         => $d['eval_envasado']          ?? '',
                    'eval_obsoletos'        => $d['eval_obsoletos']         ?? '',
                ];

                // Recalcular total y porcentaje en el visor (datos de confianza)
                $suma      = 0;
                $evaluados = 0;
                foreach ($items as $v) {
                    if ($v === '1' || $v === 1)  { $suma++; $evaluados++; }
                    if ($v === '0' || $v === 0)  { $evaluados++; }
                }
                $porcentaje = $evaluados > 0 ? round(($suma / $evaluados) * 100, 1) : null;

                // Color del porcentaje
                $pct_class = 'pct-good';
                if ($porcentaje !== null) {
                    if ($porcentaje < 60)      $pct_class = 'pct-danger';
                    elseif ($porcentaje < 80)  $pct_class = 'pct-warn';
                }

                // Fecha formateada
                $fecha_fmt = $d['fecha'] ? date('d/m/Y', strtotime($d['fecha'])) : '—';
            ?>
            <tr>
                <td><?= htmlspecialchars($fecha_fmt) ?></td>
                <td style="text-align:left; padding-left:6px;"><?= htmlspecialchars($d['referencia_empaque'] ?? '—') ?></td>
                <td><?= htmlspecialchars($d['lote_interno'] ?? '—') ?></td>
                <td style="text-align:left; padding-left:6px;"><?= htmlspecialchars($d['proveedor'] ?? '—') ?></td>

                <!-- 9 ÍTEMS -->
                <td><?= formatEval($items['eval_clasificacion']) ?></td>
                <td><?= formatEval($items['eval_especificaciones']) ?></td>
                <td><?= formatEval($items['eval_verificacion']) ?></td>
                <td><?= formatEval($items['eval_trazabilidad']) ?></td>
                <td><?= formatEval($items['eval_acta_ivc']) ?></td>
                <td><?= formatEval($items['eval_recepcion']) ?></td>
                <td><?= formatEval($items['eval_alistamiento']) ?></td>
                <td><?= formatEval($items['eval_envasado']) ?></td>
                <td><?= formatEval($items['eval_obsoletos']) ?></td>

                <!-- TOTAL -->
                <td class="col-total"><?= $suma ?></td>

                <!-- % CUMPLIMIENTO -->
                <td class="col-porcentaje <?= $pct_class ?>">
                    <?= $porcentaje !== null ? $porcentaje . '%' : 'N/A' ?>
                </td>

                <!-- RESPONSABLE -->
                <td><?= htmlspecialchars($d['responsable_inspeccion'] ?? '—') ?></td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>

        <!-- ══ FILA RESUMEN ══ -->
        <?php if (!empty($registros)):
            // Promedios globales del archivo
            $sumas_total  = 0; $pcts = []; $cnt = 0;
            foreach ($registros as $reg) {
                $d2 = $reg['datos'];
                $s = 0; $e = 0;
                foreach (['eval_clasificacion','eval_especificaciones','eval_verificacion',
                          'eval_trazabilidad','eval_acta_ivc','eval_recepcion',
                          'eval_alistamiento','eval_envasado','eval_obsoletos'] as $k) {
                    $v = $d2[$k] ?? '';
                    if ($v === '1' || $v === 1) { $s++; $e++; }
                    if ($v === '0' || $v === 0) { $e++; }
                }
                $sumas_total += $s;
                if ($e > 0) $pcts[] = ($s / $e) * 100;
                $cnt++;
            }
            $prom_pct = count($pcts) > 0 ? round(array_sum($pcts) / count($pcts), 1) : null;
            $prom_cls = 'pct-good';
            if ($prom_pct !== null) {
                if ($prom_pct < 60)     $prom_cls = 'pct-danger';
                elseif ($prom_pct < 80) $prom_cls = 'pct-warn';
            }
        ?>
        <tfoot>
            <tr style="background:#F0F9FF;">
                <td colspan="13" style="text-align:right; font-weight:700; font-size:7.5pt; padding-right:10px; border-top: 2px solid #003366;">
                    PROMEDIO GENERAL DEL PERÍODO (<?= $cnt ?> registro<?= $cnt !== 1 ? 's' : '' ?>):
                </td>
                <td class="col-total" style="border-top:2px solid #003366;"><?= round($sumas_total / max($cnt, 1), 1) ?></td>
                <td class="col-porcentaje <?= $prom_cls ?>" style="border-top:2px solid #003366;">
                    <?= $prom_pct !== null ? $prom_pct . '%' : 'N/A' ?>
                </td>
                <td style="border-top:2px solid #003366;"></td>
            </tr>
        </tfoot>
        <?php endif; ?>
    </table>

</div><!-- /page-wrap -->

</body>
</html>
