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

$ruta_json = "../../archivos/generados/inspeccion_gestion_produccion_zs/" . $sede_dir . "/" . basename($target_file);

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

$periodo = str_replace(['INGGESTPROD_', '.json'], '', basename($target_file));

$items_labels = [
    'eval_seleccion_diseno'      => 'Las medidas y pasos a seguir para la selección y diseño de productos se encuentran debidamente documentados',
    'eval_mejoras_maquinaria'    => 'Se cuentan con soportes o registros de las mejoras realizadas a la maquinaria y equipos de producción en el mes presente',
    'eval_planificacion_cap'     => 'Se planifica la capacidad de producción a corto y largo plazo, con herramientas y métodos adecuados para realizar esta actividad',
    'eval_planificacion_act'     => 'Se planifican las actividades de producción con herramientas y métodos adecuados para realizar esta actividad',
    'eval_controles_prod'        => 'Existen controles suficientes para garantizar que la producción se hace según los planes, preservando calidad e inocuidad en los productos',
    'eval_reduccion_costos'      => 'Existen medidas para garantizar que la reducción en los costos de producción no afecte la calidad e inocuidad de los productos',
    'eval_control_inventario'    => 'Se hace control del inventario de materias primas y productos con las frecuencias establecidas (diaria y mensualmente)',
    'eval_mantenimiento_equipos' => 'Las medidas y pasos a seguir para el mantenimiento y sustitución de equipos se encuentran debidamente documentados',
    'eval_cobertura_produccion'  => 'Se tienen identificados los elementos que dan cobertura la producción y existe documentación que soporta la gestión de cada elemento',
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
    <title>Visor — Inspección Gestión de la Producción | <?= htmlspecialchars($periodo) ?></title>
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

        #documento {
            max-width: 99%;
            margin: 0 auto;
            background: #fff;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }

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

        .doc-header-center .doc-supertitle { font-size: 11px; color: #555; }
        .doc-header-center .doc-title      { font-size: 14px; font-weight: 700; color: #003366; }
        .doc-header-center .doc-subtitle   { font-size: 13px; font-weight: 600; }

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

        .instrucciones-row {
            border: 2px solid #000;
            border-bottom: none;
            padding: 6px 10px;
            font-size: 11px;
            line-height: 1.5;
            background: #fffde7;
        }

        .tabla-principal {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #000;
            font-size: 10px;
        }

        .tabla-principal th {
            background: #003366;
            color: #fff;
            text-align: center;
            padding: 5px 3px;
            border: 1px solid #000;
            font-weight: 600;
            vertical-align: middle;
            line-height: 1.3;
        }

        .tabla-principal td {
            border: 1px solid #000;
            padding: 4px 5px;
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
            padding: 4px 5px;
        }

        .v-cumple   { color: #166534; font-weight: 700; }
        .v-nocumple { color: #991b1b; font-weight: 700; }
        .v-na       { color: #92400e; font-weight: 700; }
        .v-empty    { color: #9ca3af; }

        .pct-good   { color: #166534; font-weight: 700; }
        .pct-warn   { color: #92400e; font-weight: 700; }
        .pct-danger { color: #991b1b; font-weight: 700; }

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
    <a href="rev_inspeccion_gestion_produccion_zs.php" class="btn btn-back">
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
            <div class="doc-supertitle">PPR Gestión de la Producción</div>
            <div class="doc-title">Inspección Programa Gestión de la Producción</div>
            <div class="doc-subtitle">"Inspección Programa Gestión de la Producción"</div>
        </div>
        <div class="doc-header-meta">
            <div class="meta-row">
                <div class="meta-key">Código:</div>
                <div class="meta-val-cell">GP-PD-FP-GP-FO-016</div>
            </div>
            <div class="meta-row">
                <div class="meta-key">Versión:</div>
                <div class="meta-val-cell">1</div>
            </div>
            <div class="meta-row">
                <div class="meta-key">Fecha:</div>
                <div class="meta-val-cell">27/07/2021</div>
            </div>
            <div class="meta-row">
                <div class="meta-key">Período:</div>
                <div class="meta-val-cell"><?= htmlspecialchars($periodo) ?></div>
            </div>
        </div>
    </div>

    <!-- SEDE -->
    <div class="sede-row">
        <div class="sede-key">ZONA / SEDE</div>
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
                <th rowspan="2" style="min-width:65px;">Fecha</th>
                <?php foreach ($items_labels as $key => $label): ?>
                    <th><?= htmlspecialchars($label) ?></th>
                <?php endforeach; ?>
                <th>TOTAL =<br>Suma de ítems</th>
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
                <td><?= fmtEval($d['eval_seleccion_diseno']      ?? '—') ?></td>
                <td><?= fmtEval($d['eval_mejoras_maquinaria']    ?? '—') ?></td>
                <td><?= fmtEval($d['eval_planificacion_cap']     ?? '—') ?></td>
                <td><?= fmtEval($d['eval_planificacion_act']     ?? '—') ?></td>
                <td><?= fmtEval($d['eval_controles_prod']        ?? '—') ?></td>
                <td><?= fmtEval($d['eval_reduccion_costos']      ?? '—') ?></td>
                <td><?= fmtEval($d['eval_control_inventario']    ?? '—') ?></td>
                <td><?= fmtEval($d['eval_mantenimiento_equipos'] ?? '—') ?></td>
                <td><?= fmtEval($d['eval_cobertura_produccion']  ?? '—') ?></td>
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
        <span>Generado: <?= date('d/m/Y H:i') ?> &nbsp;|&nbsp; GP-PD-FP-GP-FO-016 v1</span>
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
            pdf.save('Inspeccion_GestionProduccion_<?= preg_replace('/[^A-Za-z0-9_-]/', '', $periodo) ?>_<?= htmlspecialchars($sede) ?>.pdf');

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
