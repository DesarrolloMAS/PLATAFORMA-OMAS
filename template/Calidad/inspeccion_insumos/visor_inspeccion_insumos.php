<?php
include '../../sesion.php';

$id   = trim($_GET['id']   ?? '');
$file = trim($_GET['file'] ?? '');

$base_dir  = realpath(__DIR__ . '/../../../archivos/generados/inspeccion_insumos');
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
    if ($val === '1' || $val === 1) return '<span class="v-cumple">1</span>';
    if ($val === '0' || $val === 0) return '<span class="v-nocumple">0</span>';
    return '<span class="v-empty">—</span>';
}
function pctClass($pct) {
    if ($pct === null || $pct === '') return '';
    $pct = floatval($pct);
    if ($pct >= 80) return 'pct-good';
    if ($pct >= 60) return 'pct-warn';
    return 'pct-danger';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visor — Inspección de Insumos | <?= htmlspecialchars($d['fecha_inspeccion'] ?? '') ?></title>
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

        .btn-back  { background: #fff; color: #333; border: 1px solid #ccc; }
        .btn-back:hover { background: #f0f0f0; }
        .btn-pdf   { background: #003366; color: #fff; }
        .btn-pdf:hover { background: #004080; box-shadow: 0 4px 12px rgba(0,51,102,0.3); }
        .btn-print { background: #166534; color: #fff; }
        .btn-print:hover { background: #15803d; }
        .btn-editar { background: #92400e; color: #fff; }
        .btn-editar:hover { background: #b45309; box-shadow: 0 4px 12px rgba(146,64,14,0.3); }

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
        .doc-header-logo img { max-width: 100px; max-height: 50px; object-fit: contain; }

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

        .doc-header-meta { font-size: 11px; display: flex; flex-direction: column; }
        .doc-header-meta .meta-row { display: grid; grid-template-columns: 70px 1fr; border-bottom: 1px solid #000; flex: 1; }
        .doc-header-meta .meta-row:last-child { border-bottom: none; }
        .meta-key { background: #f0f4f8; font-weight: 700; padding: 3px 6px; border-right: 1px solid #000; display: flex; align-items: center; }
        .meta-val-cell { padding: 3px 6px; display: flex; align-items: center; }

        .info-row {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            border: 2px solid #000;
            border-bottom: none;
        }
        .info-cell { border-right: 1px solid #000; padding: 6px 10px; }
        .info-cell:last-child { border-right: none; }
        .info-label { font-size: 10px; color: #555; font-weight: 700; text-transform: uppercase; }
        .info-val { font-size: 12px; font-weight: 600; }

        .instrucciones-row {
            border: 2px solid #000;
            border-bottom: none;
            padding: 6px 10px;
            font-size: 11px;
            line-height: 1.5;
            background: #fffde7;
        }

        .table-scroll { overflow-x: auto; }

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
        .td-nombre { text-align: left; font-weight: 600; padding-left: 8px; white-space: nowrap; }
        .td-obs { text-align: left; font-size: 9.5px; }

        .v-cumple   { color: #166534; font-weight: 700; }
        .v-nocumple { color: #991b1b; font-weight: 700; }
        .v-empty    { color: #9ca3af; }

        .pct-good   { color: #166534; font-weight: 700; }
        .pct-warn   { color: #92400e; font-weight: 700; }
        .pct-danger { color: #991b1b; font-weight: 700; }

        .firmas-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            border: 2px solid #000;
            border-top: none;
        }
        .firma-cell { padding: 14px 12px 6px; border-right: 1px solid #000; text-align: center; }
        .firma-cell:last-child { border-right: none; }
        .firma-linea { border-top: 1px solid #000; margin-top: 26px; padding-top: 4px; font-size: 11px; font-weight: 600; }

        .doc-footer {
            border: 2px solid #000;
            border-top: none;
            padding: 8px 12px;
            font-size: 10px;
            color: #555;
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 6px;
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

<div class="action-bar">
    <a href="rev_inspeccion_insumos.php" class="btn btn-back">← Volver a Galería</a>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
        <a href="editar_inspeccion_insumos.php?id=<?= urlencode($id) ?>&file=<?= urlencode($file) ?>" class="btn btn-editar">✎ Corregir</a>
        <button class="btn btn-print" onclick="window.print()">🖨 Imprimir</button>
        <button class="btn btn-pdf" onclick="exportarPDF()">📄 Exportar PDF</button>
    </div>
</div>

<div id="documento">

    <div class="doc-header">
        <div class="doc-header-logo">
            <img src="../../../archivos/formularios/logomas.png" alt="MAS" onerror="this.style.display='none'">
        </div>
        <div class="doc-header-center">
            <div class="doc-supertitle">PPR Almacenamiento</div>
            <div class="doc-title">Recibo y Almacenamiento de Materiales</div>
            <div class="doc-subtitle">"Inspección de Insumos — Microingredientes"</div>
        </div>
        <div class="doc-header-meta">
            <div class="meta-row"><div class="meta-key">Código:</div><div class="meta-val-cell">PD-RA-AM-FO-004</div></div>
            <div class="meta-row"><div class="meta-key">Versión:</div><div class="meta-val-cell">1</div></div>
            <div class="meta-row"><div class="meta-key">Fecha:</div><div class="meta-val-cell">4/10/2023</div></div>
        </div>
    </div>

    <div class="info-row">
        <div class="info-cell">
            <div class="info-label">Fecha Inspección</div>
            <div class="info-val"><?= fmtFecha($d['fecha_inspeccion'] ?? '') ?></div>
        </div>
        <div class="info-cell">
            <div class="info-label">Hora Inspección</div>
            <div class="info-val"><?= v($d, 'hora_inspeccion') ?></div>
        </div>
        <div class="info-cell">
            <div class="info-label">Planta</div>
            <div class="info-val"><?= v($d, 'planta') ?></div>
        </div>
        <div class="info-cell">
            <div class="info-label">Inspeccionado Por</div>
            <div class="info-val"><?= v($d, 'inspeccionado_por') ?></div>
        </div>
        <div class="info-cell">
            <div class="info-label">Verificado Por</div>
            <div class="info-val"><?= v($d, 'verificado_por') ?></div>
        </div>
    </div>

    <div class="instrucciones-row">
        <strong>INSTRUCCIONES:</strong> Diligencie las casillas con la información correspondiente de acuerdo al encabezado.
        Tenga en cuenta para la sección de preguntas <strong>CUMPLE = 1</strong>; <strong>NO CUMPLE = 0</strong>.
    </div>

    <div class="table-scroll">
        <table class="tabla-principal">
            <thead>
                <tr>
                    <th style="min-width:110px;">Materia Prima</th>
                    <th>Lote Interno</th>
                    <th>Lote Proveedor</th>
                    <th>Fecha<br>Vencimiento</th>
                    <th>Proveedor</th>
                    <th>¿Producto<br>vigente?</th>
                    <th>¿Producto<br>etiquetado?</th>
                    <th>¿Libre de<br>plagas?</th>
                    <th>¿Envase/embalaje<br>en buen estado?</th>
                    <th>¿Lote corresponde<br>al SAP?</th>
                    <th>% Cumpl.</th>
                    <th style="min-width:120px;">Observaciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($insumos)): ?>
                <tr><td colspan="12" style="padding:14px;color:#888;">Sin materias primas registradas.</td></tr>
                <?php else: foreach ($insumos as $it): ?>
                <tr>
                    <td class="td-nombre"><?= v($it, 'materia_prima') ?></td>
                    <td><?= v($it, 'lote_interno') ?></td>
                    <td><?= v($it, 'lote_proveedor') ?></td>
                    <td><?= fmtFecha($it['fecha_vencimiento'] ?? '') ?></td>
                    <td><?= v($it, 'proveedor') ?></td>
                    <td><?= fmtEval($it['eval_vigente']     ?? '') ?></td>
                    <td><?= fmtEval($it['eval_etiquetado']  ?? '') ?></td>
                    <td><?= fmtEval($it['eval_plagas']      ?? '') ?></td>
                    <td><?= fmtEval($it['eval_envase']      ?? '') ?></td>
                    <td><?= fmtEval($it['eval_sap']         ?? '') ?></td>
                    <td class="<?= pctClass($it['porcentaje_cumplimiento'] ?? null) ?>">
                        <?= isset($it['porcentaje_cumplimiento']) && $it['porcentaje_cumplimiento'] !== '' ? number_format(floatval($it['porcentaje_cumplimiento']), 0) . '%' : '—' ?>
                    </td>
                    <td class="td-obs"><?= v($it, 'observaciones') ?></td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <div class="firmas-row">
        <div class="firma-cell">
            <div class="firma-linea">Inspeccionado por: <?= v($d, 'inspeccionado_por') ?></div>
        </div>
        <div class="firma-cell">
            <div class="firma-linea">Verificado por: <?= v($d, 'verificado_por') ?></div>
        </div>
    </div>

    <div class="doc-footer">
        <span>Sede: <strong><?= htmlspecialchars($registro['sede_sys'] ?? '—') ?></strong> &nbsp;|&nbsp; Ítems: <strong><?= count($insumos) ?></strong> &nbsp;|&nbsp; Registrado por: <strong><?= htmlspecialchars($registro['usuario_sys'] ?? '—') ?></strong></span>
        <span>Generado: <?= date('d/m/Y H:i') ?> &nbsp;|&nbsp; PD-RA-AM-FO-004 v1</span>
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
            pdf.save('Inspeccion_Insumos_<?= htmlspecialchars(preg_replace('/[^A-Za-z0-9_-]/', '', $d['fecha_inspeccion'] ?? 'registro')) ?>.pdf');

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
