<?php
require '../sesion.php';

if (!isset($_SESSION['nombre']) || empty($_SESSION['sede'])) {
    header('Location: ../../index.php');
    exit;
}

$sede        = $_SESSION['sede'];
$target_file = $_GET['file'] ?? '';

if (empty($target_file)) {
    die("Archivo no especificado.");
}

$ruta_json = "../../archivos/generados/premezclas_v2/"
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
    return strcmp($a['datos']['fecha'] ?? '', $b['datos']['fecha'] ?? '');
});

$periodo = str_replace(['PREMEZCLA_', '.json'], '', basename($target_file));

function e($v) { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

/** Campo escalar editable (ej: fecha, observaciones). */
function campo(string $field, $value, string $type = 'text'): string {
    if ($type === 'textarea') {
        return '<textarea class="edit-field" data-field="' . e($field) . '" readonly rows="2">' . e($value) . '</textarea>';
    }
    return '<input type="' . e($type) . '" class="edit-field" data-field="' . e($field) . '" readonly value="' . e($value) . '">';
}

/** Campo de un array de objetos (ej: harinas_especiales[i].producto, insumos[i].lote). */
function campoArr(string $field, int $i, string $sub, $value, string $type = 'text'): string {
    return '<input type="' . e($type) . '" class="edit-field" data-field="' . e($field) . '" data-index="' . $i . '" data-subfield="' . e($sub) . '" readonly value="' . e($value) . '">';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Premezclas V2 — <?= e($periodo) ?></title>
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

        .action-bar {
            max-width: 99%;
            margin: 0 auto 20px;
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

        .action-bar .left { display: flex; gap: 10px; align-items: center; }

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
            transition: all 0.2s;
        }
        .btn-back:hover { background: rgba(0,240,255,0.1); }

        .periodo-label {
            color: #E2E8F0;
            font-size: 13px;
            font-weight: 600;
            padding: 0 8px;
            border-left: 2px solid var(--accent);
        }

        .btn-print {
            background: #10B981;
            color: #fff;
            border: none;
            padding: 9px 18px;
            border-radius: 4px;
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-print:hover { background: #059669; }

        .page-wrap {
            max-width: 99%;
            margin: 0 auto;
            background: var(--white);
            padding: 24px 28px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        table { width: 100%; border-collapse: collapse; }
        td, th { border: 1px solid var(--border); padding: 4px 5px; vertical-align: middle; text-align: center; }

        .header-table td { border: 1px solid #000; vertical-align: middle; padding: 8px 10px; }
        .header-title-main { font-size: 9.5pt; font-weight: 600; margin-bottom: 3px; }
        .header-title-doc  { font-size: 11pt; font-weight: 700; }

        .iso-meta { width: 100%; border-collapse: collapse; height: 100%; }
        .iso-meta td {
            border: 0; border-bottom: 1px solid #000;
            padding: 3px 8px; font-size: 7.5pt; text-align: left;
        }
        .iso-meta tr:last-child td { border-bottom: 0; }
        .iso-meta td:first-child { font-weight: 700; border-right: 1px solid #000; width: 45%; }

        .registro-block { margin-top: 18px; page-break-inside: avoid; }
        .registro-head {
            background: var(--blue);
            color: #fff;
            font-size: 8.5pt;
            font-weight: 700;
            padding: 6px 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
        }
        .registro-actions { display: flex; gap: 8px; }

        .btn-verificacion, .btn-autorizar, .btn-guardar {
            border: none;
            border-radius: 4px;
            padding: 6px 14px;
            font-weight: 700;
            font-size: 8pt;
            text-transform: uppercase;
            cursor: pointer;
            font-family: 'Roboto', sans-serif;
        }
        .btn-verificacion { background: #E2E8F0; color: #0F172A; }
        .btn-verificacion.activo { background: #0891B2; color: #fff; }
        .btn-autorizar { background: #FFB000; color: #1a1200; }
        .btn-guardar { background: #10B981; color: #fff; display: none; }
        .btn-guardar.visible { display: inline-block; }

        .tabla-datos { margin-top: 0; font-size: 7.5pt; }
        .tabla-datos thead th {
            background: #E2E8F0;
            color: #000;
            font-size: 7pt;
            font-weight: 600;
            text-transform: uppercase;
            padding: 5px 4px;
        }
        .tabla-datos tbody td { font-size: 8pt; padding: 5px 4px; }
        .tabla-datos tbody tr:nth-child(even) { background: #F8FAFC; }
        .td-obs { text-align: left; max-width: 300px; font-size: 7.5pt; }
        .sub-titulo { font-size: 7.5pt; font-weight: 700; text-align: left; padding: 4px 6px; background: #F0F9FF; }

        .empty-row td { padding: 20px; color: #64748B; font-style: italic; }

        /* Campos editables (mismo patrón que visor_permiso_trabajo.php de HSEQ) */
        .edit-field {
            width: 100%; border: none; background: transparent; font-family: inherit; color: inherit;
            padding: 2px 3px; border-radius: 2px; box-sizing: border-box; font-size: 8pt; text-align: center;
        }
        textarea.edit-field { resize: vertical; min-height: 34px; text-align: left; }
        .registro-block.modo-verificacion .edit-field { background: #FEF9C3; }
        .registro-block.modo-verificacion .edit-field:hover { outline: 1px dashed #cbd5e1; }
        .registro-block.modo-verificacion .edit-field:focus { background: #fff; outline: 2px solid #0891B2; }

        /* Firmas finales: RESPONSABLE | JEFE DE PRODUCCIÓN */
        .tabla-firmas { margin-top: 10px; }
        .firma-label { font-weight: 700; font-size: 8pt; background: #F1F5F9; padding: 6px; }
        .firma-box { height: 100px; padding: 4px; vertical-align: middle; }
        .firma-box img { max-height: 90px; max-width: 100%; object-fit: contain; }
        .firma-box .firma-vacia { color: #94A3B8; font-size: 7.5pt; font-style: italic; }

        /* Modal de autorización (canvas de firma del jefe de producción) */
        .modal-overlay {
            display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6);
            z-index: 999; align-items: center; justify-content: center;
        }
        .modal-overlay.abierto { display: flex; }
        .modal-box {
            background: #fff; border-radius: 8px; padding: 24px; width: 92%; max-width: 480px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.4);
        }
        .modal-box h3 { font-size: 13pt; margin-bottom: 14px; color: #0F172A; }
        .modal-canvas {
            width: 100%; height: 200px; background: #f8fafc; border: 1px solid #cbd5e1;
            border-radius: 4px; cursor: crosshair; touch-action: none;
        }
        .modal-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 14px; }
        .modal-actions button {
            border: none; border-radius: 4px; padding: 9px 18px; font-weight: 700; font-size: 10pt; cursor: pointer;
            font-family: 'Roboto', sans-serif;
        }
        .btn-modal-cancelar { background: #E2E8F0; color: #0F172A; }
        .btn-modal-limpiar { background: #F1F5F9; color: #0F172A; border: 1px solid #cbd5e1 !important; }
        .btn-modal-guardar { background: #10B981; color: #fff; }

        @media print {
            body        { background: #fff; padding: 0; }
            .action-bar, .registro-actions, .modal-overlay { display: none !important; }
            .page-wrap  { box-shadow: none; padding: 10px; max-width: 100%; }
            .edit-field { border: none !important; outline: none !important; background: transparent !important; }
            @page       { size: landscape; margin: 8mm; }
        }
    </style>
</head>
<body>

<div class="action-bar">
    <div class="left">
        <a href="rev_premezclas_v2.php" class="btn-back">← Volver al Listado</a>
        <span class="periodo-label">Período: <?= e($periodo) ?> | <?= count($registros) ?> registro<?= count($registros) !== 1 ? 's' : '' ?></span>
    </div>
    <button class="btn-print" onclick="window.print()">🖨️ IMPRIMIR / PDF</button>
</div>

<div class="page-wrap">

    <table class="header-table" style="margin-bottom:0;">
        <tr>
            <td style="width:18%; text-align:center; padding:10px;">
                <img src="/img/logo_empresa.jpeg" alt="Logo MAS"
                     style="max-height:70px; max-width:100%; object-fit:contain;">
            </td>
            <td style="width:60%; text-align:center; padding:10px;">
                <div class="header-title-main">PPR Gestión de la Producción</div>
                <div class="header-title-main" style="margin-bottom:4px;">Control de Producción — Premezclas y Harinas Especiales</div>
                <div class="header-title-doc">Período: <?= e($periodo) ?> &nbsp;|&nbsp; Sede: <?= e($sede) ?></div>
            </td>
            <td style="width:22%; padding:0; vertical-align:top;">
                <table class="iso-meta" style="height:100%;">
                    <tr><td>Código:</td><td>GP-PD-PP-PRE-FO-001</td></tr>
                    <tr><td>Versión:</td><td>2</td></tr>
                    <tr><td>Registros:</td><td><?= count($registros) ?></td></tr>
                    <tr><td>Impreso:</td><td><?= date('d/m/Y') ?></td></tr>
                </table>
            </td>
        </tr>
    </table>

    <?php foreach ($registros as $reg):
        $d = $reg['datos'];
        $fecha_fmt = !empty($d['fecha']) ? date('d/m/Y', strtotime($d['fecha'])) : '—';
        $harinas = $d['harinas_especiales'] ?? [];
        $insumos = $d['insumos'] ?? [];
        $idReg   = $reg['id_registro'] ?? '';
    ?>
    <div class="registro-block" data-id-registro="<?= e($idReg) ?>">
        <div class="registro-head">
            <span>📅 <?= e($fecha_fmt) ?> (<?= campo('fecha', $d['fecha'] ?? '', 'date') ?>) &nbsp;|&nbsp; Registrado por: <?= e($reg['usuario_sys'] ?? '—') ?></span>
            <div class="registro-actions">
                <button type="button" class="btn-verificacion">VERIFICACIÓN</button>
                <button type="button" class="btn-autorizar">AUTORIZAR</button>
                <button type="button" class="btn-guardar">💾 Guardar Cambios</button>
            </div>
        </div>

        <table class="tabla-datos">
            <tr><td colspan="6" class="sub-titulo">HARINAS ESPECIALES</td></tr>
            <thead>
                <tr>
                    <th style="width:22%;">Producto</th>
                    <th style="width:14%;">Lote</th>
                    <th style="width:12%;">Cantidad</th>
                    <th style="width:18%;">Operario</th>
                    <th style="width:10%;">Hora Inicio</th>
                    <th style="width:10%;">Hora Final</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($harinas)): ?>
                <tr class="empty-row"><td colspan="6">Sin harinas especiales registradas.</td></tr>
            <?php else: foreach ($harinas as $idx => $h): ?>
                <tr>
                    <td><?= campoArr('harinas_especiales', $idx, 'producto', $h['producto'] ?? '') ?></td>
                    <td><?= campoArr('harinas_especiales', $idx, 'lote', $h['lote'] ?? '') ?></td>
                    <td><?= campoArr('harinas_especiales', $idx, 'cantidad', $h['cantidad'] ?? '') ?></td>
                    <td><?= campoArr('harinas_especiales', $idx, 'operario', $h['operario'] ?? '') ?></td>
                    <td><?= campoArr('harinas_especiales', $idx, 'hora_inicio', $h['hora_inicio'] ?? '', 'time') ?></td>
                    <td><?= campoArr('harinas_especiales', $idx, 'hora_final', $h['hora_final'] ?? '', 'time') ?></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>

        <table class="tabla-datos" style="margin-top:6px;">
            <tr><td colspan="3" class="sub-titulo">INSUMOS</td></tr>
            <thead>
                <tr>
                    <th style="width:40%;">Insumo</th>
                    <th style="width:30%;">Lote</th>
                    <th style="width:30%;">Cantidad</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($insumos)): ?>
                <tr class="empty-row"><td colspan="3">Sin insumos registrados.</td></tr>
            <?php else: foreach ($insumos as $idx => $i): ?>
                <tr>
                    <td><?= campoArr('insumos', $idx, 'insumo', $i['insumo'] ?? '') ?></td>
                    <td><?= campoArr('insumos', $idx, 'lote', $i['lote'] ?? '') ?></td>
                    <td><?= campoArr('insumos', $idx, 'cantidad', $i['cantidad'] ?? '') ?></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>

        <table class="tabla-datos" style="margin-top:6px;">
            <tr><td class="td-obs" style="text-align:left;"><strong>Observaciones:</strong><br><?= campo('observaciones', $d['observaciones'] ?? '', 'textarea') ?></td></tr>
        </table>

        <table class="tabla-firmas">
            <tr>
                <td class="firma-label" style="width:50%;">RESPONSABLE</td>
                <td class="firma-label" style="width:50%;">JEFE DE PRODUCCIÓN</td>
            </tr>
            <tr>
                <td class="firma-box">
                    <?php if (!empty($d['firma'])): ?>
                        <img src="<?= e($d['firma']) ?>" alt="Firma responsable">
                    <?php else: ?>
                        <span class="firma-vacia">Sin firma</span>
                    <?php endif; ?>
                </td>
                <td class="firma-box firma-jefe-box">
                    <?php if (!empty($d['firma_jefe_produccion'])): ?>
                        <img src="<?= e($d['firma_jefe_produccion']) ?>" alt="Firma jefe de producción">
                    <?php else: ?>
                        <span class="firma-vacia">Pendiente de autorización</span>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
    </div>
    <?php endforeach; ?>

</div>

<!-- MODAL: canvas de firma del Jefe de Producción -->
<div class="modal-overlay" id="modalAutorizar">
    <div class="modal-box">
        <h3>Autorización — Firma del Jefe de Producción</h3>
        <canvas class="modal-canvas" id="canvasJefe"></canvas>
        <div class="modal-actions">
            <button type="button" class="btn-modal-cancelar" id="btnCancelarAutorizar">Cancelar</button>
            <button type="button" class="btn-modal-limpiar" id="btnLimpiarAutorizar">Limpiar</button>
            <button type="button" class="btn-modal-guardar" id="btnGuardarAutorizar">Guardar Firma</button>
        </div>
    </div>
</div>

<script>
const ARCHIVO_ACTUAL = <?= json_encode($target_file) ?>;

// ── VERIFICACIÓN (modo corrección, mismo patrón que HSEQ/permiso_trabajo) ──
document.querySelectorAll('.registro-block').forEach(block => {
    const btnVerif  = block.querySelector('.btn-verificacion');
    const btnGuardar = block.querySelector('.btn-guardar');
    const campos = block.querySelectorAll('.edit-field');

    btnVerif.addEventListener('click', () => {
        const activar = !block.classList.contains('modo-verificacion');
        block.classList.toggle('modo-verificacion', activar);
        btnVerif.classList.toggle('activo', activar);
        btnVerif.textContent = activar ? 'CANCELAR VERIFICACIÓN' : 'VERIFICACIÓN';
        btnGuardar.classList.toggle('visible', activar);
        campos.forEach(c => c.readOnly = !activar);
        if (!activar) location.reload(); // descartar cambios no guardados al cancelar
    });

    btnGuardar.addEventListener('click', () => {
        const idRegistro = block.dataset.idRegistro;
        const datos = {};
        campos.forEach(el => {
            const field = el.dataset.field;
            const idx = el.dataset.index;
            const sub = el.dataset.subfield;
            if (idx !== undefined && sub !== undefined) {
                if (!Array.isArray(datos[field])) datos[field] = [];
                if (!datos[field][+idx]) datos[field][+idx] = {};
                datos[field][+idx][sub] = el.value;
            } else {
                datos[field] = el.value;
            }
        });

        btnGuardar.disabled = true;
        btnGuardar.textContent = 'Guardando...';

        fetch('actualizar_registro.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ file: ARCHIVO_ACTUAL, id_registro: idRegistro, datos })
        })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({ title: '¡Guardado!', icon: 'success', timer: 1400, showConfirmButton: false })
                    .then(() => location.reload());
            } else {
                Swal.fire('Error', data.message || 'No se pudo guardar.', 'error');
                btnGuardar.disabled = false;
                btnGuardar.textContent = '💾 Guardar Cambios';
            }
        })
        .catch(() => {
            Swal.fire('Error', 'Error de conexión al guardar.', 'error');
            btnGuardar.disabled = false;
            btnGuardar.textContent = '💾 Guardar Cambios';
        });
    });
});

// ── AUTORIZAR (canvas de firma del Jefe de Producción) ──
const modal = document.getElementById('modalAutorizar');
const canvasJefe = document.getElementById('canvasJefe');
const ctxJefe = canvasJefe.getContext('2d');
let idRegistroActivo = null;
let dibujandoJefe = false;

function ajustarCanvasJefe() {
    const rect = canvasJefe.getBoundingClientRect();
    canvasJefe.width = rect.width;
    canvasJefe.height = rect.height;
    ctxJefe.lineWidth = 2;
    ctxJefe.lineCap = 'round';
    ctxJefe.strokeStyle = '#000';
}

function posicionJefe(e) {
    const rect = canvasJefe.getBoundingClientRect();
    const p = e.touches ? e.touches[0] : e;
    return { x: p.clientX - rect.left, y: p.clientY - rect.top };
}

canvasJefe.addEventListener('mousedown', e => { dibujandoJefe = true; const p = posicionJefe(e); ctxJefe.beginPath(); ctxJefe.moveTo(p.x, p.y); });
canvasJefe.addEventListener('mousemove', e => { if (!dibujandoJefe) return; const p = posicionJefe(e); ctxJefe.lineTo(p.x, p.y); ctxJefe.stroke(); });
canvasJefe.addEventListener('mouseup', () => dibujandoJefe = false);
canvasJefe.addEventListener('mouseleave', () => dibujandoJefe = false);
canvasJefe.addEventListener('touchstart', e => { dibujandoJefe = true; const p = posicionJefe(e); ctxJefe.beginPath(); ctxJefe.moveTo(p.x, p.y); });
canvasJefe.addEventListener('touchmove', e => { e.preventDefault(); if (!dibujandoJefe) return; const p = posicionJefe(e); ctxJefe.lineTo(p.x, p.y); ctxJefe.stroke(); });
canvasJefe.addEventListener('touchend', () => dibujandoJefe = false);

document.querySelectorAll('.btn-autorizar').forEach(btn => {
    btn.addEventListener('click', () => {
        idRegistroActivo = btn.closest('.registro-block').dataset.idRegistro;
        modal.classList.add('abierto');
        setTimeout(() => { ajustarCanvasJefe(); ctxJefe.clearRect(0, 0, canvasJefe.width, canvasJefe.height); }, 0);
    });
});

document.getElementById('btnCancelarAutorizar').addEventListener('click', () => {
    modal.classList.remove('abierto');
    idRegistroActivo = null;
});

document.getElementById('btnLimpiarAutorizar').addEventListener('click', () => {
    ctxJefe.clearRect(0, 0, canvasJefe.width, canvasJefe.height);
});

document.getElementById('btnGuardarAutorizar').addEventListener('click', () => {
    const blanco = document.createElement('canvas');
    blanco.width = canvasJefe.width;
    blanco.height = canvasJefe.height;
    if (canvasJefe.toDataURL() === blanco.toDataURL()) {
        Swal.fire({ title: 'Firma vacía', text: 'Dibuje la firma antes de guardar.', icon: 'warning' });
        return;
    }

    const firmaDataUrl = canvasJefe.toDataURL('image/png');
    const btn = document.getElementById('btnGuardarAutorizar');
    btn.disabled = true;
    btn.textContent = 'Guardando...';

    fetch('actualizar_registro.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ file: ARCHIVO_ACTUAL, id_registro: idRegistroActivo, datos: { firma_jefe_produccion: firmaDataUrl } })
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.textContent = 'Guardar Firma';
        if (data.status === 'success') {
            modal.classList.remove('abierto');
            Swal.fire({ title: '¡Autorizado!', icon: 'success', timer: 1400, showConfirmButton: false })
                .then(() => location.reload());
        } else {
            Swal.fire('Error', data.message || 'No se pudo guardar la autorización.', 'error');
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.textContent = 'Guardar Firma';
        Swal.fire('Error', 'Error de conexión al guardar.', 'error');
    });
});
</script>

</body>
</html>
