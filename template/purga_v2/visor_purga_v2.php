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

$ruta_json = "../../archivos/generados/purga_v2/"
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
    return strcmp($a['datos']['fecha_produccion'] ?? '', $b['datos']['fecha_produccion'] ?? '');
});

$periodo = str_replace(['PURGA_', '.json'], '', basename($target_file));

function e($v) { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

/** Campo escalar editable (mismo patrón que visor_permiso_trabajo.php de HSEQ / visor_premezclas_v2.php). */
function campo(string $field, $value, string $type = 'text'): string {
    return '<input type="' . e($type) . '" class="edit-field" data-field="' . e($field) . '" readonly value="' . e($value) . '">';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purga de Proceso V2 — <?= e($periodo) ?></title>
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

        .tabla-datos { margin-top: 0; font-size: 7.5pt; }
        .tabla-datos thead th {
            background: var(--blue);
            color: #fff;
            font-size: 7pt;
            font-weight: 600;
            text-transform: uppercase;
            padding: 6px 4px;
        }
        .tabla-datos tbody td { font-size: 8pt; padding: 5px 4px; }
        .tabla-datos tbody tr:nth-child(even) { background: #F8FAFC; }
        .sub-titulo { font-size: 7.5pt; font-weight: 700; text-align: left; padding: 5px 8px; background: #F0F9FF; }

        .empty-row td { padding: 20px; color: #64748B; font-style: italic; }

        /* Campos editables (mismo patrón que HSEQ/permiso_trabajo y premezclas_v2) */
        .edit-field {
            width: 100%; border: none; background: transparent; font-family: inherit; color: inherit;
            padding: 2px 3px; border-radius: 2px; box-sizing: border-box; font-size: 8pt; text-align: center;
        }
        tr.modo-correccion .edit-field { background: #FEF9C3; }
        tr.modo-correccion .edit-field:hover { outline: 1px dashed #cbd5e1; }
        tr.modo-correccion .edit-field:focus { background: #fff; outline: 2px solid #0891B2; }

        .col-acciones { width: 90px; white-space: nowrap; }
        .btn-corregir, .btn-guardar-fila {
            border: none; border-radius: 4px; padding: 5px 10px; font-weight: 700; font-size: 7.5pt;
            text-transform: uppercase; cursor: pointer; font-family: 'Roboto', sans-serif; margin: 1px 0;
        }
        .btn-corregir { background: #E2E8F0; color: #0F172A; }
        .btn-corregir.activo { background: #0891B2; color: #fff; }
        .btn-guardar-fila { background: #10B981; color: #fff; display: none; }
        .btn-guardar-fila.visible { display: inline-block; }

        @media print {
            body        { background: #fff; padding: 0; }
            .action-bar { display: none !important; }
            .col-acciones { display: none !important; }
            .page-wrap  { box-shadow: none; padding: 10px; max-width: 100%; }
            .edit-field { border: none !important; outline: none !important; background: transparent !important; }
            @page       { size: landscape; margin: 8mm; }
        }
    </style>
</head>
<body>

<div class="action-bar">
    <div class="left">
        <a href="rev_purga_v2.php" class="btn-back">← Volver al Listado</a>
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
                <div class="header-title-main" style="margin-bottom:4px;">Purga de Proceso</div>
                <div class="header-title-doc">Período: <?= e($periodo) ?> &nbsp;|&nbsp; Sede: <?= e($sede) ?></div>
            </td>
            <td style="width:22%; padding:0; vertical-align:top;">
                <table class="iso-meta" style="height:100%;">
                    <tr><td>Código:</td><td>GP-PD-PP-PUR-FO-001</td></tr>
                    <tr><td>Versión:</td><td>2</td></tr>
                    <tr><td>Registros:</td><td><?= count($registros) ?></td></tr>
                    <tr><td>Impreso:</td><td><?= date('d/m/Y') ?></td></tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="tabla-datos" style="margin-top:12px;">
        <tr><td colspan="6" class="sub-titulo">CAMBIO DE SILO</td></tr>
        <thead>
            <tr>
                <th style="width:13%;">Fecha Producción</th>
                <th style="width:24%;">Referencia de Producto</th>
                <th style="width:11%;">Hora Inicial</th>
                <th style="width:13%;">Cantidad (KG)</th>
                <th style="width:29%;">Responsable</th>
                <th class="col-acciones">Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($registros)): ?>
            <tr class="empty-row"><td colspan="6">No hay registros.</td></tr>
        <?php else: foreach ($registros as $reg):
            $d = $reg['datos'];
            $idReg = $reg['id_registro'] ?? '';
        ?>
            <tr data-id-registro="<?= e($idReg) ?>">
                <td><?= campo('fecha_produccion', $d['fecha_produccion'] ?? '', 'date') ?></td>
                <td><?= campo('referencia_producto', $d['referencia_producto'] ?? '') ?></td>
                <td><?= campo('hora_inicial', $d['hora_inicial'] ?? '', 'time') ?></td>
                <td><?= campo('cantidad', $d['cantidad'] ?? '') ?></td>
                <td><?= campo('responsable_cambio', $d['responsable_cambio'] ?? '') ?></td>
                <td class="col-acciones">
                    <button type="button" class="btn-corregir">Corregir</button><br>
                    <button type="button" class="btn-guardar-fila">Guardar</button>
                </td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>

    <table class="tabla-datos" style="margin-top:10px;">
        <tr><td colspan="6" class="sub-titulo">REGRESO A LÍNEA DE PRODUCCIÓN</td></tr>
        <thead>
            <tr>
                <th style="width:12%;">Fecha Ingreso Tolva</th>
                <th style="width:24%;">Referencia de Producto</th>
                <th style="width:10%;">Hora Inicial</th>
                <th style="width:10%;">Hora Final</th>
                <th style="width:12%;">Cantidad Total (KG)</th>
                <th style="width:32%;">Responsable</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($registros)): ?>
            <tr class="empty-row"><td colspan="6">No hay registros.</td></tr>
        <?php else: foreach ($registros as $reg):
            $d = $reg['datos'];
            $idReg = $reg['id_registro'] ?? '';
        ?>
            <tr data-id-registro="<?= e($idReg) ?>">
                <td><?= campo('fecha_ingreso', $d['fecha_ingreso'] ?? '', 'date') ?></td>
                <td><?= campo('referencia_producto_linea', $d['referencia_producto_linea'] ?? '') ?></td>
                <td><?= campo('hora_ingreso', $d['hora_ingreso'] ?? '', 'time') ?></td>
                <td><?= campo('hora_final', $d['hora_final'] ?? '', 'time') ?></td>
                <td><?= campo('cantidad_line', $d['cantidad_line'] ?? '') ?></td>
                <td><?= campo('responsable_linea', $d['responsable_linea'] ?? '') ?></td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" style="text-align:right; padding-right:12px; background:#F0F9FF; font-weight:700;">
                    Total: <?= count($registros) ?> registro<?= count($registros) !== 1 ? 's' : '' ?> — Generado: <?= date('d/m/Y H:i') ?>
                </td>
            </tr>
        </tfoot>
    </table>

</div>

<script>
const ARCHIVO_ACTUAL = <?= json_encode($target_file) ?>;

// ── CORREGIR (mismo mecanismo que HSEQ/permiso_trabajo y premezclas_v2,
// pero abarcando las dos filas —Cambio de Silo y Regreso a Línea— que
// comparten el mismo id_registro). ──
document.querySelectorAll('.btn-corregir').forEach(btnCorregir => {
    const filaPrincipal = btnCorregir.closest('tr');
    const idRegistro = filaPrincipal.dataset.idRegistro;
    const filas = document.querySelectorAll(`tr[data-id-registro="${idRegistro}"]`);
    const btnGuardar = filaPrincipal.querySelector('.btn-guardar-fila');

    btnCorregir.addEventListener('click', () => {
        const activar = !btnCorregir.classList.contains('activo');
        btnCorregir.classList.toggle('activo', activar);
        btnCorregir.textContent = activar ? 'Cancelar' : 'Corregir';
        btnGuardar.classList.toggle('visible', activar);
        filas.forEach(f => {
            f.classList.toggle('modo-correccion', activar);
            f.querySelectorAll('.edit-field').forEach(c => c.readOnly = !activar);
        });
        if (!activar) location.reload(); // descartar cambios no guardados al cancelar
    });

    btnGuardar.addEventListener('click', () => {
        const datos = {};
        filas.forEach(f => {
            f.querySelectorAll('.edit-field').forEach(el => {
                datos[el.dataset.field] = el.value;
            });
        });

        btnGuardar.disabled = true;
        btnGuardar.textContent = '...';

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
                btnGuardar.textContent = 'Guardar';
            }
        })
        .catch(() => {
            Swal.fire('Error', 'Error de conexión al guardar.', 'error');
            btnGuardar.disabled = false;
            btnGuardar.textContent = 'Guardar';
        });
    });
});
</script>

</body>
</html>
