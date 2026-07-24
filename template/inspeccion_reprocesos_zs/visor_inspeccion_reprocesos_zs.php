<?php
require '../sesion.php';

if (!isset($_SESSION['nombre']) || empty($_SESSION['sede'])) {
    header('Location: ../../index.php');
    exit;
}

$sede        = $_SESSION['sede'];
$sede_dir    = preg_replace('/[^A-Za-z0-9_-]/', '', $sede);
$target_file = $_GET['file'] ?? '';

if (empty($target_file)) {
    die("Archivo no especificado. Vuelva a la Galería.");
}

$ruta_json = "../../archivos/generados/inspeccion_reprocesos_zs/" . $sede_dir . "/" . basename($target_file);

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

$periodo = str_replace(['INGREPROC_', '.json'], '', basename($target_file));

$items_labels = [
    'eval_directriz'    => 'Directríz de Reprocesos',
    'eval_productos'    => 'Productos susceptibles al Reproceso',
    'eval_operaciones'  => 'Operaciones de Reproceso',
    'eval_rutas'        => 'Rutas de circulación de Productos para Reproceso',
    'eval_riesgos'      => 'Evaluación de Riesgos',
    'eval_prevencion'   => 'Prevención de la contaminación',
    'eval_uso_correcto' => 'Uso Correcto',
    'eval_pnc'          => 'Control de PNC',
    'eval_trazabilidad' => 'Trazabilidad',
];

function fmtEval($val) {
    if ($val === '1' || $val === 1)   return '<span class="v-cumple">1</span>';
    if ($val === '0' || $val === 0)   return '<span class="v-nocumple">0</span>';
    if ($val === 'NA')                return '<span class="v-na">NA</span>';
    return '<span class="v-empty">—</span>';
}

function pctClass($pct) {
    if ($pct === null) return '';
    if ($pct >= 80)   return 'pct-good';
    if ($pct >= 60)   return 'pct-warn';
    return 'pct-danger';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visor — Inspección Gestión de Reprocesos | <?= htmlspecialchars($periodo) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
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
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 6px;
            font-family: 'Roboto', sans-serif;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .btn-back   { background: #fff; color: #333; border: 1px solid #ccc; }
        .btn-back:hover { background: #f0f0f0; }

        .btn-pdf    { background: #003366; color: #fff; }
        .btn-pdf:hover { background: #004080; box-shadow: 0 4px 12px rgba(0,51,102,0.3); }

        .btn-print  { background: #166534; color: #fff; }
        .btn-print:hover { background: #15803d; }

        /* ── DOCUMENTO ── */
        #documento {
            max-width: 99%;
            margin: 0 auto;
            background: #fff;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }

        /* Header institucional */
        .doc-header {
            display: grid;
            grid-template-columns: 120px 1fr 160px;
            border: 2px solid #000;
            border-bottom: none;
        }

        .doc-header-logo {
            border-right: 1px solid #000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 8px;
            background: #fff;
        }

        .doc-header-logo img {
            max-width: 100px;
            max-height: 50px;
            object-fit: contain;
        }

        .doc-header-center {
            text-align: center;
            padding: 8px 12px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 4px;
            border-right: 1px solid #000;
        }

        .doc-header-center .doc-supertitle {
            font-size: 11px;
            color: #555;
        }

        .doc-header-center .doc-title {
            font-size: 14px;
            font-weight: 700;
            color: #003366;
        }

        .doc-header-center .doc-subtitle {
            font-size: 13px;
            font-weight: 600;
        }

        .doc-header-meta {
            font-size: 11px;
            display: flex;
            flex-direction: column;
        }

        .doc-header-meta .meta-row {
            display: grid;
            grid-template-columns: 70px 1fr;
            border-bottom: 1px solid #000;
            flex: 1;
        }

        .doc-header-meta .meta-row:last-child { border-bottom: none; }

        .meta-key {
            background: #f0f4f8;
            font-weight: 700;
            padding: 3px 6px;
            border-right: 1px solid #000;
            display: flex;
            align-items: center;
        }

        .meta-val-cell {
            padding: 3px 6px;
            display: flex;
            align-items: center;
        }

        /* Sede row */
        .sede-row {
            display: grid;
            grid-template-columns: 140px 1fr;
            border: 2px solid #000;
            border-bottom: none;
        }

        .sede-key {
            background: #e8f0fe;
            font-weight: 700;
            font-size: 12px;
            padding: 6px 10px;
            border-right: 1px solid #000;
            display: flex;
            align-items: center;
        }

        .sede-val {
            font-size: 12px;
            padding: 6px 10px;
            font-weight: 600;
            display: flex;
            align-items: center;
        }

        /* Instrucciones */
        .instrucciones-row {
            border: 2px solid #000;
            border-bottom: none;
            padding: 6px 10px;
            font-size: 11px;
            line-height: 1.5;
            background: #fffde7;
        }

        /* Tabla principal */
        .tabla-principal {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #000;
            font-size: 11px;
        }

        .tabla-principal th {
            background: #003366;
            color: #fff;
            text-align: center;
            padding: 6px 4px;
            border: 1px solid #000;
            font-weight: 600;
            vertical-align: middle;
            line-height: 1.3;
        }

        .tabla-principal td {
            border: 1px solid #000;
            padding: 5px 6px;
            text-align: center;
            vertical-align: middle;
        }

        .tabla-principal tbody tr:nth-child(even) { background: #f8fafc; }
        .tabla-principal tbody tr:hover { background: #e8f4fd; }

        .td-fecha {
            font-weight: 600;
            white-space: nowrap;
            text-align: left;
            padding-left: 8px;
        }

        .td-responsable {
            text-align: left;
            font-size: 10px;
            padding: 5px 6px;
        }

        .v-cumple   { color: #166534; font-weight: 700; }
        .v-nocumple { color: #991b1b; font-weight: 700; }
        .v-na       { color: #92400e; font-weight: 700; }
        .v-empty    { color: #9ca3af; }

        .pct-good   { color: #166534; font-weight: 700; }
        .pct-warn   { color: #92400e; font-weight: 700; }
        .pct-danger { color: #991b1b; font-weight: 700; }

        /* Pie de página */
        .doc-footer {
            border: 2px solid #000;
            border-top: none;
            padding: 8px 12px;
            font-size: 10px;
            color: #555;
            display: flex;
            justify-content: space-between;
            background: #f8fafc;
        }

        /* ── MEDIA PRINT ── */
        @media print {
            body { background: #fff; padding: 0; }
            .action-bar { display: none; }
            #documento { box-shadow: none; max-width: 100%; }
        }
    </style>
</head>
<body>

<!-- BARRA DE ACCIONES -->
<div class="action-bar">
    <a href="rev_inspeccion_reprocesos_zs.php" class="btn btn-back">
        ← Volver a Galería
    </a>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
        <button class="btn btn-print" onclick="window.print()">
            🖨 Imprimir
        </button>
        <button class="btn btn-pdf" onclick="exportarPDF()">
            📄 Exportar PDF
        </button>
    </div>
</div>

<!-- DOCUMENTO FORMAL -->
<div id="documento">

    <!-- ENCABEZADO -->
    <div class="doc-header">
        <div class="doc-header-logo">
            <img src="../../archivos/img/logo_mas.png" alt="MAS" onerror="this.style.display='none'">
        </div>
        <div class="doc-header-center">
            <div class="doc-supertitle">Manual de Gestión de la Producción</div>
            <div class="doc-title">PPR Gestión de Reprocesos</div>
            <div class="doc-subtitle">"Inspección Gestión de Reprocesos"</div>
        </div>
        <div class="doc-header-meta">
            <div class="meta-row">
                <div class="meta-key">Código:</div>
                <div class="meta-val-cell">OPE-GR-FO-002</div>
            </div>
            <div class="meta-row">
                <div class="meta-key">Versión:</div>
                <div class="meta-val-cell">2</div>
            </div>
            <div class="meta-row">
                <div class="meta-key">Fecha:</div>
                <div class="meta-val-cell">25/08/2025</div>
            </div>
            <div class="meta-row">
                <div class="meta-key">Período:</div>
                <div class="meta-val-cell"><?= htmlspecialchars($periodo) ?></div>
            </div>
        </div>
    </div>

    <!-- SEDE / INSTALACIÓN -->
    <div class="sede-row">
        <div class="sede-key">INSTALACIÓN</div>
        <div class="sede-val"><?= htmlspecialchars($sede) ?></div>
    </div>

    <!-- INSTRUCCIONES -->
    <div class="instrucciones-row">
        <strong>INSTRUCCIONES PARA EL REGISTRO:</strong> Por cada ítem a evaluar, escribir <strong>1</strong> si cumple ó <strong>0</strong> si no cumple.
        Escribir <strong>NA</strong> sobre las casillas de los ítems que no aplican para la evaluación, y no incluirlos en el total de ítems evaluados.
    </div>

    <!-- TABLA PRINCIPAL -->
    <table class="tabla-principal">
        <thead>
            <tr>
                <th rowspan="2" style="min-width:70px;">Fecha</th>
                <th>Directríz de Reprocesos<br><small>(debidamente firmada por la Alta Dirección)</small></th>
                <th>Productos susceptibles al Reproceso<br><small>(debidamente identificados y documentados)</small></th>
                <th>Operaciones de Reproceso<br><small>(debidamente identificadas y documentadas)</small></th>
                <th>Rutas de circulación de Productos para Reproceso<br><small>(debidamente documentadas y actualizadas)</small></th>
                <th>Evaluación de Riesgos<br><small>(Planes de Calidad e Inocuidad contemplan los riesgos)</small></th>
                <th>Prevención de la contaminación<br><small>(los productos se identifican, segregan y almacenan)</small></th>
                <th>Uso Correcto<br><small>(se usan e incorporan al proceso según el programa)</small></th>
                <th>Control de PNC<br><small>(se aplican medidas para productos NO CONFORMES)</small></th>
                <th>Trazabilidad<br><small>(permite rastrear origen y destino de lotes)</small></th>
                <th>TOTAL =<br>Suma</th>
                <th>% de Cumplimiento<br><small>(Total / # ítems evaluados) × 100</small></th>
                <th>Responsable Inspección</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($registros as $reg):
                $d   = $reg['datos'];
                $pct = isset($d['porcentaje_cumplimiento']) ? floatval($d['porcentaje_cumplimiento']) : null;
            ?>
            <tr>
                <td class="td-fecha"><?= htmlspecialchars($d['fecha'] ?? '—') ?></td>
                <td><?= fmtEval($d['eval_directriz']    ?? '—') ?></td>
                <td><?= fmtEval($d['eval_productos']    ?? '—') ?></td>
                <td><?= fmtEval($d['eval_operaciones']  ?? '—') ?></td>
                <td><?= fmtEval($d['eval_rutas']        ?? '—') ?></td>
                <td><?= fmtEval($d['eval_riesgos']      ?? '—') ?></td>
                <td><?= fmtEval($d['eval_prevencion']   ?? '—') ?></td>
                <td><?= fmtEval($d['eval_uso_correcto'] ?? '—') ?></td>
                <td><?= fmtEval($d['eval_pnc']          ?? '—') ?></td>
                <td><?= fmtEval($d['eval_trazabilidad'] ?? '—') ?></td>
                <td><strong><?= htmlspecialchars($d['total_suma'] ?? '—') ?></strong></td>
                <td class="<?= pctClass($pct) ?>">
                    <?= $pct !== null ? number_format($pct, 1) . '%' : '—' ?>
                </td>
                <td class="td-responsable"><?= htmlspecialchars($d['responsable_nombre'] ?? '—') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- PIE DE DOCUMENTO -->
    <div class="doc-footer">
        <span>Sede: <strong><?= htmlspecialchars($sede) ?></strong> &nbsp;|&nbsp; Período: <strong><?= htmlspecialchars($periodo) ?></strong> &nbsp;|&nbsp; Registros: <strong><?= count($registros) ?></strong></span>
        <span>Generado: <?= date('d/m/Y H:i') ?> &nbsp;|&nbsp; OPE-GR-FO-002 v2</span>
    </div>

</div>

<script>
    async function exportarPDF() {
        const { jsPDF } = window.jspdf;
        const doc_el = document.getElementById('documento');

        Swal.fire({
            title: 'Generando PDF...',
            text: 'Por favor espere.',
            allowOutsideClick: false,
            background: '#151A22', color: '#fff',
            didOpen: () => Swal.showLoading()
        });

        try {
            const canvas = await html2canvas(doc_el, { scale: 2, useCORS: true });
            const imgData = canvas.toDataURL('image/png');
            const pdf = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a3' });
            const pdfW = pdf.internal.pageSize.getWidth();
            const pdfH = (canvas.height * pdfW) / canvas.width;

            pdf.addImage(imgData, 'PNG', 0, 0, pdfW, pdfH);
            pdf.save('Inspeccion_Reprocesos_<?= preg_replace('/[^A-Za-z0-9_-]/', '', $periodo) ?>_<?= htmlspecialchars($sede) ?>.pdf');

            Swal.fire({
                title: '¡PDF Generado!',
                icon: 'success',
                background: '#151A22', color: '#fff',
                confirmButtonColor: '#00F0FF',
                timer: 2000, showConfirmButton: false
            });
        } catch (err) {
            Swal.fire({
                title: 'Error al generar PDF',
                text: err.message,
                icon: 'error',
                background: '#151A22', color: '#fff',
                confirmButtonColor: '#FF3366'
            });
        }
    }
</script>

</body>
</html>
