<?php
require_once '../../sesion.php';
verificarAutenticacion();

$sede        = $_SESSION['sede'];
$target_file = $_GET['file'] ?? '';

if (empty($target_file)) {
    die("Archivo no especificado. Vuelva a la Galería.");
}

$ruta_json = "../../../archivos/generados/Calidad/liberaciones_v2/"
           . preg_replace('/[^A-Za-z0-9_-]/', '', $sede) . "/"
           . basename($target_file);

if (!file_exists($ruta_json)) {
    die("El archivo no existe o fue eliminado.");
}

$registros = json_decode(file_get_contents($ruta_json), true) ?: [];

// Orden cronológico por fecha de producción
usort($registros, function ($a, $b) {
    $fA = $a['datos']['fecha_produccion'] ?? '';
    $fB = $b['datos']['fecha_produccion'] ?? '';
    if ($fA !== $fB) return strtotime($fA) <=> strtotime($fB);
    return strtotime($a['timestamp'] ?? '') <=> strtotime($b['timestamp'] ?? '');
});

$periodo = str_replace(['LIBERACIONES_', '.json'], '', basename($target_file));

$CAMPOS_CUMPLIMIENTO = [
    'bitacora'              => 'Bitácora / FT',
    'panificacion'          => 'Panificación',
    'laboratorios_externos' => 'Lab. Externos',
    'fortificacion'         => 'Fortificación',
    'mejorantes'            => 'Mejorantes',
    'empaque'               => 'Empaque',
    'certificado'           => 'Certificado',
];

function formatEval($val) {
    if ($val === 'Cumple')    return '<span style="color:#166534;font-weight:700;">Cumple</span>';
    if ($val === 'No Cumple') return '<span style="color:#991B1B;font-weight:700;">No Cumple</span>';
    if ($val === 'N/A')       return '<span style="color:#92400E;font-weight:700;">N/A</span>';
    return '<span style="color:#9CA3AF;">—</span>';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visor - Liberaciones | <?= htmlspecialchars($periodo) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700&family=Space+Mono:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <style>
        :root {
            --bg: #0B0E14;
            --surface: #151A22;
            --border: #1E293B;
            --accent: #00F0FF;
            --accent-glow: rgba(0, 240, 255, 0.4);
            --text: #E2E8F0;
            --text-muted: #94A3B8;
            --danger: #FF3366;
            --r-sm: 4px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Barlow', sans-serif;
            background: var(--bg);
            color: #111;
            padding: 20px;
        }

        .action-bar {
            max-width: 1300px;
            margin: 0 auto 20px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--surface);
            padding: 14px 24px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            gap: 10px;
            flex-wrap: wrap;
        }
        .action-bar .left { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
        .action-bar .sub  { color: var(--text-muted); font-family: 'Space Mono', monospace; font-size: 12px; }

        .btn-back {
            background: transparent; border: 1px solid var(--accent); color: var(--accent);
            text-decoration: none; padding: 9px 18px; border-radius: var(--r-sm);
            font-weight: 700; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;
            transition: all 0.2s; font-family: 'Space Mono', monospace;
        }
        .btn-back:hover { background: rgba(0,240,255,0.1); }

        .btn-print {
            background: #10B981; color: #fff; border: none; padding: 9px 18px; border-radius: var(--r-sm);
            font-weight: 700; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;
            cursor: pointer; transition: background 0.2s; font-family: 'Space Mono', monospace;
        }
        .btn-print:hover { background: #059669; }

        .report-wrapper { max-width: 1300px; margin: auto; }

        .page-container {
            background: #fff;
            padding: 30px;
            box-shadow: 0 0 20px rgba(0,0,0,0.15);
            margin-bottom: 30px;
            border-radius: 4px;
        }

        .doc-header { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .doc-header td { border: 1px solid #000; padding: 10px; text-align: center; vertical-align: middle; }
        .logo-cell { width: 18%; }
        .logo-cell img { max-width: 110px; height: auto; }
        .title-cell { font-weight: bold; font-size: 15px; width: 44%; }
        .meta-cell { font-size: 11px; width: 38%; text-align: left; padding-left: 12px !important; }
        .meta-cell strong { display: inline-block; width: 130px; }

        .data-table { width: 100%; border-collapse: collapse; font-size: 11px; margin-top: 6px; }
        .data-table th, .data-table td { border: 1px solid #000; padding: 6px 5px; text-align: center; vertical-align: middle; }
        .data-table th { background-color: #003366; color: #fff; font-weight: bold; text-transform: uppercase; font-size: 9.5px; }
        .data-table tbody tr:nth-child(even) { background: #F8FAFC; }
        .harina-col { text-align: left !important; font-weight: 600; }
        .extra-badge { display: inline-block; margin-left: 4px; font-size: 8px; color: #92400E; font-weight: 700; }

        .btn-editar {
            background: rgba(0,240,255,0.1); color: #0f172a; border: 1px dashed #003366;
            padding: 3px 7px; font-size: 9px; cursor: pointer; border-radius: 2px;
            font-family: 'Space Mono', monospace; transition: all 0.2s;
        }
        .btn-editar:hover { background: #003366; color: #fff; }

        .empty-row td { padding: 25px; color: #64748B; font-style: italic; font-size: 12px; }

        /* MODAL */
        .modal-overlay {
            display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.85); align-items: center; justify-content: center; backdrop-filter: blur(3px);
        }
        .modal-overlay.open { display: flex; }
        .modal-box {
            background: #141620; border: 1px solid var(--accent); box-shadow: 0 0 30px rgba(0,240,255,0.15);
            width: 92%; max-width: 700px; padding: 25px; border-radius: 4px; max-height: 90vh; overflow-y: auto;
            color: #e0e6ed; font-family: 'Barlow', sans-serif;
        }
        .modal-box h2 {
            font-family: 'Space Mono', monospace; color: var(--accent); border-bottom: 1px solid #2d324a;
            padding-bottom: 10px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;
            font-size: 15px;
        }
        .modal-box h2 span { font-size: 12px; color: #c682ff; cursor: pointer; }
        .m-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px; margin-bottom: 14px; }
        .m-grid label { font-size: 10px; color: #c682ff; font-weight: bold; text-transform: uppercase; }
        .m-grid input, .m-grid select {
            width: 100%; padding: 8px; background: #0a0b10; border: 1px solid #2d324a; border-radius: 2px;
            color: #fff; box-sizing: border-box; margin-top: 4px; font-family: 'Barlow', sans-serif; font-size: 13px;
        }
        .m-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px; }
        .m-btn {
            padding: 10px 16px; font-size: 12px; border: none; border-radius: 2px; cursor: pointer;
            font-family: 'Space Mono', monospace; font-weight: 700;
        }
        .m-btn-cancel { background: #2d324a; color: #fff; }
        .m-btn-save   { background: var(--accent); color: #0a0b10; box-shadow: 0 0 10px rgba(0,240,255,0.2); }

        @media print {
            body { background: #fff; padding: 0; }
            .action-bar, .disable-print { display: none !important; }
            .page-container { box-shadow: none; padding: 10px; }
            @page { size: landscape; margin: 8mm; }
        }
    </style>
</head>
<body>

<div class="action-bar">
    <div class="left">
        <a href="rev_liberaciones_v2.php" class="btn-back">← Volver a Galería</a>
        <span class="sub">PERÍODO: <?= htmlspecialchars($periodo) ?> · SEDE: <?= htmlspecialchars($sede) ?></span>
    </div>
    <button class="btn-print" onclick="exportPDF()">🖨️ Exportar / Imprimir PDF</button>
</div>

<div class="report-wrapper" id="divToPrint">
    <?php if (empty($registros)): ?>
        <div class="page-container">
            <p style="text-align:center; color:#64748B; padding: 30px;">No hay registros de liberaciones para este período.</p>
        </div>
    <?php else: ?>
        <?php foreach ($registros as $reg):
            $d = $reg['datos'] ?? [];
            $harinas       = $d['harinas'] ?? [];
            $harinas_extra = $d['harinas_extra'] ?? [];
            $fecha_fmt     = !empty($d['fecha_produccion']) ? date('d/m/Y', strtotime($d['fecha_produccion'])) : '—';
            $totalLotes    = 0;
            foreach ($harinas as $h) { $totalLotes += count($h['lotes'] ?? []); }
            $totalLotes += count($harinas_extra);
        ?>
        <div class="page-container">

            <table class="doc-header">
                <tr>
                    <td class="logo-cell" rowspan="4">
                        <img src="/archivos/formularios/logomas.png" alt="Logo OMAS" onerror="this.src=''; this.alt='[LOGO OMAS]'">
                    </td>
                    <td class="title-cell" rowspan="4">REGISTRO DE LIBERACIONES DE HARINA</td>
                    <td class="meta-cell"><strong>FECHA PRODUCCIÓN:</strong> <?= htmlspecialchars($fecha_fmt) ?></td>
                </tr>
                <tr><td class="meta-cell"><strong>SEDE:</strong> <?= htmlspecialchars($reg['sede_sys'] ?? $sede) ?></td></tr>
                <tr><td class="meta-cell"><strong>REGISTRADO POR:</strong> <?= htmlspecialchars($reg['usuario_sys'] ?? '—') ?></td></tr>
                <tr><td class="meta-cell"><strong>N° REGISTRO:</strong> <?= htmlspecialchars($reg['id_registro'] ?? '—') ?></td></tr>
            </table>

            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:14%;">Harina</th>
                        <th style="width:8%;">Referencia</th>
                        <th style="width:8%;">Lote</th>
                        <th style="width:8%;">Cantidad</th>
                        <?php foreach ($CAMPOS_CUMPLIMIENTO as $label): ?>
                            <th><?= htmlspecialchars($label) ?></th>
                        <?php endforeach; ?>
                        <th style="width:6%;" class="disable-print">Acción</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($totalLotes === 0): ?>
                    <tr class="empty-row"><td colspan="12">Este registro no tiene harinas con lotes.</td></tr>
                <?php else: ?>
                    <?php foreach ($harinas as $hIdx => $h): foreach (($h['lotes'] ?? []) as $lIdx => $lote): ?>
                        <tr>
                            <td class="harina-col"><?= htmlspecialchars($h['harina_nombre'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($lote['referencia'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($lote['lote'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($lote['cantidad'] ?? '—') ?></td>
                            <?php foreach (array_keys($CAMPOS_CUMPLIMIENTO) as $key): ?>
                                <td><?= formatEval($lote[$key] ?? '') ?></td>
                            <?php endforeach; ?>
                            <td class="disable-print">
                                <button class="btn-editar" data-html2canvas-ignore="true"
                                    onclick="abrirEditor('<?= htmlspecialchars($reg['id_registro']) ?>', 'harinas', <?= (int)$hIdx ?>, <?= (int)$lIdx ?>)">EDITAR</button>
                            </td>
                        </tr>
                    <?php endforeach; endforeach; ?>
                    <?php foreach ($harinas_extra as $eIdx => $lote): ?>
                        <tr>
                            <td class="harina-col"><?= htmlspecialchars($lote['nombre_harina'] ?? 'Extra') ?><span class="extra-badge">EXTRA</span></td>
                            <td><?= htmlspecialchars($lote['referencia'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($lote['lote'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($lote['cantidad'] ?? '—') ?></td>
                            <?php foreach (array_keys($CAMPOS_CUMPLIMIENTO) as $key): ?>
                                <td><?= formatEval($lote[$key] ?? '') ?></td>
                            <?php endforeach; ?>
                            <td class="disable-print">
                                <button class="btn-editar" data-html2canvas-ignore="true"
                                    onclick="abrirEditor('<?= htmlspecialchars($reg['id_registro']) ?>', 'extra', null, <?= (int)$eIdx ?>)">EDITAR</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>

        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- MODAL DE EDICIÓN -->
<div id="editModal" class="modal-overlay">
    <div class="modal-box">
        <h2>EDITAR LOTE <span onclick="cerrarEditor()">[X] CERRAR</span></h2>
        <form id="editForm" onsubmit="guardarEdicion(event)">
            <div class="m-grid">
                <div><label>Referencia</label><input type="text" id="edit_referencia"></div>
                <div><label>Lote</label><input type="text" id="edit_lote"></div>
                <div><label>Cantidad</label><input type="text" id="edit_cantidad"></div>
            </div>
            <div class="m-grid">
                <?php foreach ($CAMPOS_CUMPLIMIENTO as $key => $label): ?>
                    <div>
                        <label><?= htmlspecialchars($label) ?></label>
                        <select id="edit_<?= $key ?>">
                            <option value="">---</option>
                            <option value="N/A">No Aplica</option>
                            <option value="Cumple">Cumple</option>
                            <option value="No Cumple">No Cumple</option>
                        </select>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="m-actions">
                <button type="button" class="m-btn m-btn-cancel" onclick="cerrarEditor()">CANCELAR</button>
                <button type="submit" class="m-btn m-btn-save">GUARDAR CAMBIOS</button>
            </div>
        </form>
    </div>
</div>

<script>
const FILE_NAME = <?= json_encode(basename($target_file)) ?>;
const CAMPOS_CUMPLIMIENTO = <?= json_encode(array_keys($CAMPOS_CUMPLIMIENTO)) ?>;
const REGISTROS = <?= json_encode(array_column($registros, null, 'id_registro'), JSON_UNESCAPED_UNICODE) ?>;

let editContext = null; // { idRegistro, grupo, hIdx, lIdx }

function abrirEditor(idRegistro, grupo, hIdx, lIdx) {
    const reg = REGISTROS[idRegistro];
    if (!reg) return;

    let lote;
    if (grupo === 'harinas') {
        lote = reg.datos.harinas[hIdx].lotes[lIdx];
    } else {
        lote = reg.datos.harinas_extra[lIdx];
    }

    editContext = { idRegistro, grupo, hIdx, lIdx };

    document.getElementById('edit_referencia').value = lote.referencia || '';
    document.getElementById('edit_lote').value = lote.lote || '';
    document.getElementById('edit_cantidad').value = lote.cantidad || '';
    CAMPOS_CUMPLIMIENTO.forEach(key => {
        document.getElementById('edit_' + key).value = lote[key] || '';
    });

    document.getElementById('editModal').classList.add('open');
}

function cerrarEditor() {
    document.getElementById('editModal').classList.remove('open');
    editContext = null;
}

function guardarEdicion(e) {
    e.preventDefault();
    if (!editContext) return;

    const reg = REGISTROS[editContext.idRegistro];
    let lote;
    if (editContext.grupo === 'harinas') {
        lote = reg.datos.harinas[editContext.hIdx].lotes[editContext.lIdx];
    } else {
        lote = reg.datos.harinas_extra[editContext.lIdx];
    }

    lote.referencia = document.getElementById('edit_referencia').value.trim();
    lote.lote = document.getElementById('edit_lote').value.trim();
    lote.cantidad = document.getElementById('edit_cantidad').value.trim();
    CAMPOS_CUMPLIMIENTO.forEach(key => {
        lote[key] = document.getElementById('edit_' + key).value;
    });

    fetch('actualizar_registro.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            file: FILE_NAME,
            id_registro: editContext.idRegistro,
            datos: reg.datos
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'success') {
            location.reload();
        } else {
            Swal.fire({ title: 'Error', text: data.message || 'No se pudo guardar.', icon: 'error', background: '#151A22', color: '#fff', confirmButtonColor: '#FF3366' });
        }
    })
    .catch(() => {
        Swal.fire({ title: 'Error de red', text: 'No se pudo conectar con el servidor.', icon: 'error', background: '#151A22', color: '#fff', confirmButtonColor: '#FF3366' });
    });
}

async function exportPDF() {
    const { jsPDF } = window.jspdf;
    const element = document.getElementById('divToPrint');

    try {
        const canvas = await html2canvas(element, { scale: 2, useCORS: true, logging: false });
        const imgData = canvas.toDataURL('image/jpeg', 0.95);
        if (imgData.includes('data:,')) {
            Swal.fire({ title: 'Error', text: 'Canvas vacío.', icon: 'error', background: '#151A22', color: '#fff', confirmButtonColor: '#FF3366' });
            return;
        }

        const pdf = new jsPDF('l', 'mm', 'a4');
        const pdfWidth = pdf.internal.pageSize.getWidth();
        const pdfHeight = (canvas.height * pdfWidth) / canvas.width;

        pdf.addImage(imgData, 'JPEG', 0, 0, pdfWidth, pdfHeight);
        pdf.save(`Liberaciones_<?= htmlspecialchars($sede) ?>_<?= htmlspecialchars($periodo) ?>.pdf`);
    } catch (error) {
        console.error('Error generating PDF', error);
        Swal.fire({ title: 'Error', text: 'Ocurrió un error al generar el PDF.', icon: 'error', background: '#151A22', color: '#fff', confirmButtonColor: '#FF3366' });
    }
}
</script>

</body>
</html>
