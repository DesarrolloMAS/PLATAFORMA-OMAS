<?php
require '../sesion.php';

$id   = trim($_GET['id']   ?? '');
$file = trim($_GET['file'] ?? '');

$base_dir  = realpath(__DIR__ . '/../../archivos/generados/recepcion_insumos');
$real_file = realpath($file);

if (!$id || !$file || !$real_file || !$base_dir || strpos($real_file, $base_dir) !== 0) {
    die('<p style="color:red;font-family:monospace;padding:40px;">Registro no válido o no encontrado.</p>');
}

$contenido = json_decode(file_get_contents($real_file), true) ?: [];
$registro  = null;
foreach ($contenido as $r) {
    if (($r['id_registro'] ?? '') === $id) { $registro = $r; break; }
}
if (!$registro) {
    die('<p style="color:red;font-family:monospace;padding:40px;">Registro con ID "' . htmlspecialchars($id) . '" no encontrado.</p>');
}

$d       = $registro['datos'] ?? [];
$insumos = $d['insumos'] ?? [];

function v($arr, $key, $default = '—') {
    $val = $arr[$key] ?? '';
    return ($val !== '' && $val !== null) ? htmlspecialchars($val) : $default;
}
function fmtFecha($iso) {
    if (!$iso) return '—';
    $parts = explode('-', $iso);
    return count($parts) === 3 ? $parts[2] . '/' . $parts[1] . '/' . $parts[0] : htmlspecialchars($iso);
}
function fmtEval($val) {
    if ($val === 'C')  return '<span class="v-cumple">C</span>';
    if ($val === 'NC') return '<span class="v-nocumple">NC</span>';
    if ($val === 'NA') return '<span class="v-na">NA</span>';
    return '<span class="v-empty">—</span>';
}

$condiciones = [
    ['no' => 1, 'requisito' => 'Remisión/Certificados/Otros', 'eval' => $d['eval_remision'] ?? '', 'obs' => $d['obs_remision'] ?? ''],
    ['no' => 2, 'requisito' => 'La cantidad recibida corresponde a la solicitada (Dejar observación si se realiza entrega parcial)', 'eval' => $d['eval_cantidad'] ?? '', 'obs' => $d['obs_cantidad'] ?? ''],
    ['no' => 3, 'requisito' => 'Tiempo de entrega pactado', 'eval' => $d['eval_tiempo_entrega'] ?? '', 'obs' => $d['obs_tiempo_entrega'] ?? ''],
    ['no' => 4, 'requisito' => 'Chequeo medio de transporte', 'eval' => $d['eval_medio_transporte'] ?? '', 'obs' => $d['obs_medio_transporte'] ?? ''],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visor - Recepción de Insumos | <?= htmlspecialchars($d['fecha'] ?? '') ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
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

        .action-bar .right { display: flex; gap: 10px; flex-wrap: wrap; }

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

        .btn-print, .btn-pdf {
            border: none;
            padding: 9px 18px;
            border-radius: 4px;
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            cursor: pointer;
            transition: background 0.2s;
            color: #fff;
        }
        .btn-print { background: #10B981; }
        .btn-print:hover { background: #059669; }
        .btn-pdf { background: #003366; }
        .btn-pdf:hover { background: #004080; }

        .page-wrap {
            max-width: 900px;
            margin: 0 auto;
            background: var(--white);
            padding: 24px 28px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        table { width: 100%; border-collapse: collapse; }
        td, th { border: 1px solid var(--border); padding: 6px 8px; vertical-align: middle; }

        .header-table td { border: 1px solid #000; vertical-align: middle; padding: 8px 10px; }
        .header-title-main { font-size: 10pt; font-weight: 600; margin-bottom: 3px; text-align: center; }
        .header-title-doc { font-size: 11.5pt; font-weight: 700; text-align: center; }

        .iso-meta { width: 100%; border-collapse: collapse; height: 100%; }
        .iso-meta td { border: 0; border-bottom: 1px solid #000; padding: 3px 8px; font-size: 8pt; text-align: left; }
        .iso-meta tr:last-child td { border-bottom: 0; }
        .iso-meta td:first-child { font-weight: 700; border-right: 1px solid #000; width: 45%; }

        .info-row { width: 100%; border-collapse: collapse; margin-top: 0; }
        .info-row td { border: 1px solid #000; padding: 8px 10px; font-size: 9.5pt; }
        .info-label { font-size: 8pt; color: #444; font-weight: 700; text-transform: uppercase; display: block; margin-bottom: 2px; }
        .info-val { font-size: 10.5pt; font-weight: 600; }

        .section-header {
            background: var(--blue);
            color: #fff;
            font-size: 9.5pt;
            font-weight: 700;
            text-transform: uppercase;
            padding: 6px 10px;
            letter-spacing: 0.3px;
        }

        .tabla-insumos { margin-top: 0; font-size: 9pt; }
        .tabla-insumos thead th {
            background: #F1F5F9;
            font-size: 8.5pt;
            font-weight: 700;
            text-align: center;
            padding: 6px 4px;
        }
        .tabla-insumos tbody td { text-align: center; padding: 6px 6px; }
        .tabla-insumos tbody td.td-nombre { text-align: left; padding-left: 10px; }

        .tabla-condiciones { margin-top: 0; font-size: 8.5pt; }
        .tabla-condiciones thead th {
            background: #F1F5F9;
            font-size: 8.5pt;
            font-weight: 700;
            text-align: center;
            padding: 6px 4px;
        }
        .tabla-condiciones td.td-requisito { text-align: left; padding: 6px 10px; }
        .tabla-condiciones td.td-eval { text-align: center; font-weight: 700; width: 70px; }
        .tabla-condiciones td.td-obs { text-align: left; font-size: 8.5pt; }

        .v-cumple   { color: #166534; font-weight: 700; }
        .v-nocumple { color: #991B1B; font-weight: 700; }
        .v-na       { color: #92400E; font-weight: 700; }
        .v-empty    { color: #9CA3AF; }

        .obs-generales-box { padding: 10px; font-size: 9.5pt; min-height: 40px; white-space: pre-wrap; }

        .firmas-row { width: 100%; border-collapse: collapse; border-top: none; }
        .firmas-row td { border: 1px solid #000; padding: 10px 12px; font-size: 9pt; width: 50%; }
        .firma-titulo { font-weight: 700; text-transform: uppercase; font-size: 8pt; color: #444; margin-bottom: 6px; }
        .firma-linea { margin-bottom: 4px; }

        .doc-footer {
            border: 1px solid #000;
            border-top: none;
            padding: 6px 10px;
            font-size: 8.5pt;
            color: #555;
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 6px;
            background: #f8fafc;
        }

        @media print {
            body        { background: #fff; padding: 0; }
            .action-bar { display: none !important; }
            .page-wrap  { box-shadow: none; padding: 10px; max-width: 100%; }
            @page       { size: portrait; margin: 10mm; }
        }
    </style>
</head>
<body>

<!-- ACTION BAR -->
<div class="action-bar">
    <a href="rev_recepcion_insumos.php" class="btn-back">← Volver al Listado</a>
    <div class="right">
        <button class="btn-print" onclick="window.print()">🖨️ Imprimir</button>
        <button class="btn-pdf" onclick="exportarPDF()">📄 Exportar PDF</button>
    </div>
</div>

<!-- DOCUMENT -->
<div class="page-wrap" id="document_content">

    <!-- ══ ENCABEZADO INSTITUCIONAL ══ -->
    <table class="header-table" style="margin-bottom:0;">
        <tr>
            <td style="width:18%; text-align:center; padding:10px;">
                <img src="/img/logo_empresa.jpeg" alt="Logo MAS" style="max-height:60px; max-width:100%; object-fit:contain;">
            </td>
            <td style="width:60%; text-align:center; padding:10px;">
                <div class="header-title-main">PPR Gestión de Proveedores</div>
                <div class="header-title-main">Procedimiento Recepción de Insumos</div>
                <div class="header-title-doc">"Control Recepción de Insumos"</div>
            </td>
            <td style="width:22%; padding:0; vertical-align:top;">
                <table class="iso-meta">
                    <tr><td>Código:</td><td>GC-CM-PE-PV-FO-019</td></tr>
                    <tr><td>Versión:</td><td>2</td></tr>
                    <tr><td>Fecha:</td><td>20/10/2021</td></tr>
                    <tr><td>Páginas:</td><td>1 de 1</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- ══ INFORMACIÓN GENERAL ══ -->
    <table class="info-row" style="margin-top:0;">
        <tr>
            <td style="width:25%;"><span class="info-label">Fecha</span><span class="info-val"><?= fmtFecha($d['fecha'] ?? '') ?></span></td>
            <td style="width:25%;"><span class="info-label">Orden de Compra No.</span><span class="info-val"><?= v($d, 'orden_compra') ?></span></td>
            <td style="width:25%;"><span class="info-label">Proveedor</span><span class="info-val"><?= v($d, 'proveedor') ?></span></td>
            <td style="width:25%;"><span class="info-label">Entrada No.</span><span class="info-val"><?= v($d, 'entrada_no') ?></span></td>
        </tr>
    </table>

    <!-- ══ INSUMOS RECIBIDOS ══ -->
    <table class="tabla-insumos" style="margin-top:0;">
        <tr><td colspan="3" class="section-header">Insumos Recibidos</td></tr>
        <thead>
            <tr>
                <th style="width:50%;">Nombre</th>
                <th style="width:25%;">Cantidad</th>
                <th style="width:25%;">Lote Interno</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($insumos)): ?>
                <tr><td colspan="3" style="padding:14px; color:#888;">Sin insumos registrados.</td></tr>
            <?php else: foreach ($insumos as $it): ?>
                <tr>
                    <td class="td-nombre"><?= v($it, 'nombre') ?></td>
                    <td><?= v($it, 'cantidad') ?></td>
                    <td><?= v($it, 'lote_interno') ?></td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>

    <!-- ══ CUMPLIMIENTO CONDICIONES COMERCIALES ══ -->
    <table class="tabla-condiciones" style="margin-top:0;">
        <tr><td colspan="4" class="section-header">Cumplimiento Condiciones Comerciales</td></tr>
        <thead>
            <tr>
                <th style="width:8%;">No.</th>
                <th style="width:42%;">Requisito</th>
                <th style="width:12%;">C / NC / NA</th>
                <th style="width:38%;">Observaciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($condiciones as $c): ?>
                <tr>
                    <td style="text-align:center;"><?= $c['no'] ?></td>
                    <td class="td-requisito"><?= htmlspecialchars($c['requisito']) ?></td>
                    <td class="td-eval"><?= fmtEval($c['eval']) ?></td>
                    <td class="td-obs"><?= $c['obs'] !== '' ? htmlspecialchars($c['obs']) : '—' ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- ══ OBSERVACIONES GENERALES ══ -->
    <table style="margin-top:0;">
        <tr><td class="section-header">Observaciones Generales</td></tr>
        <tr><td class="obs-generales-box"><?= $d['observaciones_generales'] ?? '' ? nl2br(htmlspecialchars($d['observaciones_generales'])) : '—' ?></td></tr>
    </table>

    <!-- ══ FIRMAS ══ -->
    <table class="firmas-row" style="margin-top:0;">
        <tr>
            <td>
                <div class="firma-titulo">Proveedor - Conductor</div>
                <div class="firma-linea">Nombre: <?= v($d, 'proveedor_conductor_nombre') ?></div>
                <div class="firma-linea">Cédula: <?= v($d, 'proveedor_conductor_cedula') ?></div>
            </td>
            <td>
                <div class="firma-titulo">Recibido</div>
                <div class="firma-linea">Nombre: <?= v($d, 'recibido_nombre') ?></div>
                <div class="firma-linea">Cédula: <?= v($d, 'recibido_cedula') ?></div>
            </td>
        </tr>
    </table>

    <div class="doc-footer">
        <span>Sede: <strong><?= htmlspecialchars($registro['sede_sys'] ?? '—') ?></strong> &nbsp;|&nbsp; Registrado por: <strong><?= htmlspecialchars($registro['usuario_sys'] ?? '—') ?></strong></span>
        <span>Generado: <?= date('d/m/Y H:i') ?> &nbsp;|&nbsp; GC-CM-PE-PV-FO-019 v2</span>
    </div>

</div><!-- /page-wrap -->

<script>
    async function exportarPDF() {
        const { jsPDF } = window.jspdf;
        const doc_el = document.getElementById('document_content');

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
            const pdf = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });
            const pdfW = pdf.internal.pageSize.getWidth();
            const pdfH = (canvas.height * pdfW) / canvas.width;

            pdf.addImage(imgData, 'PNG', 0, 0, pdfW, pdfH);
            pdf.save('Recepcion_Insumos_<?= htmlspecialchars(preg_replace('/[^A-Za-z0-9_-]/', '', $d['fecha'] ?? 'registro')) ?>.pdf');

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
