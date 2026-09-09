<?php
require '../../sesion.php';

if (!isset($_SESSION['nombre']) || empty($_SESSION['sede'])) {
    header('Location: ../../../index.php');
    exit;
}

$sede = $_SESSION['sede'];
$target_file = $_GET['file'] ?? '';

if (empty($target_file)) die("Archivo no especificado.");

$ruta_json = "../../../archivos/generados/permiso_trabajo/" . preg_replace('/[^A-Za-z0-9_-]/', '', $sede) . "/" . basename($target_file);
if (!file_exists($ruta_json)) die("El archivo no existe.");

$registros = json_decode(file_get_contents($ruta_json), true) ?: [];
if (empty($registros)) die("El archivo está vacío.");

usort($registros, function($a, $b) {
    return strtotime($b['timestamp'] ?? '') <=> strtotime($a['timestamp'] ?? '');
});

function e($v) { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

// Campo editable — registra su nombre en $usados para que la sección "Otros Campos"
// (al final de cada registro) no repita nada y para garantizar que ningún dato del
// JSON quede fuera de la vista, aunque no tenga una sección propia definida aquí.
function campo(array &$usados, string $name, $value, string $type = 'text'): string {
    $usados[$name] = true;
    if ($type === 'textarea') {
        return '<textarea class="edit-field" data-field="' . e($name) . '" rows="2">' . e($value) . '</textarea>';
    }
    return '<input type="' . e($type) . '" class="edit-field" data-field="' . e($name) . '" value="' . e($value) . '">';
}

function campoArr(array &$usados, string $name, int $i, $value): string {
    $usados[$name] = true;
    return '<input type="text" class="edit-field" data-field="' . e($name) . '" data-index="' . $i . '" value="' . e($value) . '">';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Visor Permiso de Trabajo</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Roboto', sans-serif; font-size: 8pt; margin: 0; padding: 20px; background: #e2e8f0; }
        .action-bar { margin-bottom: 20px; padding: 15px; background: #0f172a; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; }
        .btn-back { color: #00f0ff; text-decoration: none; border: 1px solid #00f0ff; padding: 8px 15px; border-radius: 4px; }

        .page-wrap { max-width: 100%; margin: 0 auto; background: #fff; padding: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); margin-bottom: 20px; page-break-after: always; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        td, th { border: 1px solid #000; padding: 4px; vertical-align: top; }

        .header-table { margin-bottom: 15px; }
        .header-table td { text-align: center; vertical-align: middle; }
        .header-title-main { font-size: 10pt; font-weight: bold; }
        .header-title-doc { font-size: 12pt; font-weight: bold; }

        .section-title { background: #1e3a8a; color: #fff; font-weight: bold; text-align: center; text-transform: uppercase; font-size: 9pt; padding: 4px; }

        .label { font-weight: bold; background: #f1f5f9; width: 20%; font-size: 8pt; }
        .val { width: 30%; font-size: 8pt; }

        .sub-table th { background: #e2e8f0; font-size: 7.5pt; text-align: left; }
        .firma-img { max-height: 40px; max-width: 120px; }

        /* ── EDICIÓN EN LÍNEA ── */
        .edit-field {
            width: 100%; border: none; background: transparent; font-family: inherit; color: inherit;
            padding: 2px 3px; border-radius: 2px; box-sizing: border-box; font-size: 8pt;
        }
        .edit-field:hover { background: #fff8db; outline: 1px dashed #cbd5e1; }
        .edit-field:focus { background: #fff; outline: 2px solid #0891B2; }
        textarea.edit-field { resize: vertical; min-height: 34px; }
        .save-bar { display: flex; justify-content: flex-end; align-items: center; gap: 12px; margin-top: 10px; }
        .btn-save {
            background: #0891B2; color: #fff; border: none; padding: 10px 24px; border-radius: 4px;
            font-weight: bold; cursor: pointer; font-size: 10pt; font-family: 'Roboto', sans-serif;
        }
        .btn-save:hover { background: #0e7490; }
        .btn-save:disabled { opacity: .6; cursor: default; }
        .save-status { font-size: 9pt; font-weight: bold; }

        @media print {
            body { background: #fff; padding: 0; }
            .action-bar, .save-bar { display: none !important; }
            .page-wrap { box-shadow: none; padding: 0; margin: 0; }
            .edit-field { border: none !important; outline: none !important; background: transparent !important; }
            @page { size: portrait; margin: 10mm; }
        }
    </style>
</head>
<body>

<div class="action-bar">
    <a href="rev_permiso_trabajo.php" class="btn-back">← Volver</a>
</div>

<?php foreach ($registros as $idx => $reg): $d = $reg['datos'] ?? []; $usados = []; ?>
<div class="page-wrap" data-id-registro="<?= e($reg['id_registro'] ?? '') ?>">

    <table class="header-table">
        <tr>
            <td style="width:20%;"><img src="/img/logo_empresa.jpeg" alt="Logo" style="max-height:60px;"></td>
            <td style="width:60%;">
                <div class="header-title-main">Sistema de Gestión de Seguridad y Salud en el Trabajo</div>
                <div class="header-title-doc">Permiso de Trabajo - HSEQ</div>
            </td>
            <td style="width:20%; text-align:left; font-size:7pt;">
                Código: FOR-HSEQ-005<br>
                Versión: 2<br>
                Fecha Emisión: 8/5/2021<br>
                Registro: <?= e($reg['timestamp'] ?? '') ?>
            </td>
        </tr>
    </table>

    <div class="section-title">1. GENERALIDADES</div>
    <table>
        <tr>
            <td class="label">Frecuencia</td><td class="val"><?= campo($usados, 'frecuencia', $d['frecuencia']??'') ?></td>
            <td class="label">Área</td><td class="val"><?= campo($usados, 'area_realiza', $d['area_realiza']??'') ?></td>
        </tr>
        <tr>
            <td class="label">Tipo Trabajo</td><td class="val"><?= campo($usados, 'tipo_trabajo', $d['tipo_trabajo']??'') ?></td>
            <td class="label">Altura aprox.</td><td class="val"><?= campo($usados, 'altura_aprox', $d['altura_aprox']??'') ?></td>
        </tr>
        <tr>
            <td class="label">Fecha Inicio</td><td class="val"><?= campo($usados, 'fecha_inicio', $d['fecha_inicio']??'', 'date') ?></td>
            <td class="label">Fecha Fin</td><td class="val"><?= campo($usados, 'fecha_fin', $d['fecha_fin']??'', 'date') ?></td>
        </tr>
        <tr>
            <td class="label">Hora Inicio</td><td class="val"><?= campo($usados, 'hora_inicio', $d['hora_inicio']??'', 'time') ?></td>
            <td class="label">Hora Fin</td><td class="val"><?= campo($usados, 'hora_fin', $d['hora_fin']??'', 'time') ?></td>
        </tr>
        <tr>
            <td class="label">Ciudad</td><td class="val"><?= campo($usados, 'ciudad', $d['ciudad']??'') ?></td>
            <td class="label">Sede</td><td class="val"><?= campo($usados, 'sede', $d['sede']??'') ?></td>
        </tr>
        <tr>
            <td class="label" colspan="4" style="text-align:center;">Descripción de la Actividad</td>
        </tr>
        <tr>
            <td colspan="4"><?= campo($usados, 'descripcion_actividad', $d['descripcion_actividad']??'', 'textarea') ?></td>
        </tr>
    </table>

    <div class="section-title">2. TRABAJADORES AUTORIZADOS Y RESPONSABLES</div>
    <table class="sub-table">
        <tr>
            <th>Nombres y Apellidos</th>
            <th>Tipo Doc</th>
            <th>N° Documento</th>
            <th>Fecha Curso</th>
            <th>Día</th>
            <th>Firma</th>
        </tr>
        <?php
        $nombres = $d['t_nombre'] ?? [];
        for($i=0; $i<count($nombres); $i++):
        ?>
        <tr>
            <td><?= campoArr($usados, 't_nombre', $i, $d['t_nombre'][$i] ?? '') ?></td>
            <td><?= campoArr($usados, 't_tipo', $i, $d['t_tipo'][$i] ?? '') ?></td>
            <td><?= campoArr($usados, 't_doc', $i, $d['t_doc'][$i] ?? '') ?></td>
            <td><?= campoArr($usados, 't_fecha_curso', $i, $d['t_fecha_curso'][$i] ?? '') ?></td>
            <td><?= campoArr($usados, 't_dia', $i, $d['t_dia'][$i] ?? '') ?></td>
            <td><?php if (!empty($d['t_firma'][$i])): ?><img src="<?= e($d['t_firma'][$i]) ?>" class="firma-img"><?php endif; ?></td>
        </tr>
        <?php endfor; ?>
    </table>

    <table>
        <tr>
            <td class="label" style="width:22%">Vigía Designado</td>
            <td style="width:38%"><?= campo($usados, 'vigia_nombre', $d['vigia_nombre']??'') ?> <?= campo($usados, 'vigia_tipo_doc', $d['vigia_tipo_doc']??'') ?> <?= campo($usados, 'vigia_doc', $d['vigia_doc']??'') ?> <?= campo($usados, 'vigia_dia', $d['vigia_dia']??'') ?></td>
            <td style="width:15%">Firma</td>
            <td><?php if (!empty($d['vigia_firma'])): ?><img src="<?= e($d['vigia_firma']) ?>" class="firma-img"><?php endif; ?></td>
        </tr>
        <tr>
            <td class="label">Resp. Plan de Emergencia</td>
            <td><?= campo($usados, 'emergencia_nombre', $d['emergencia_nombre']??'') ?> <?= campo($usados, 'emergencia_tipo_doc', $d['emergencia_tipo_doc']??'') ?> <?= campo($usados, 'emergencia_doc', $d['emergencia_doc']??'') ?> <?= campo($usados, 'emergencia_dia', $d['emergencia_dia']??'') ?></td>
            <td>Firma</td>
            <td><?php if (!empty($d['emergencia_firma'])): ?><img src="<?= e($d['emergencia_firma']) ?>" class="firma-img"><?php endif; ?></td>
        </tr>
    </table>

    <div class="section-title">3. EQUIPOS REQUERIDOS Y SISTEMAS</div>
    <table style="font-size:7pt;">
        <tr>
            <td>Arnés: <?= campo($usados, 'eq_arnes', $d['eq_arnes']??'') ?></td>
            <td>Línea Vida Vert: <?= campo($usados, 'eq_lv_vertical', $d['eq_lv_vertical']??'') ?></td>
            <td>Eslinga Posicionamiento: <?= campo($usados, 'eq_eslinga_pos', $d['eq_eslinga_pos']??'') ?></td>
            <td>Línea Vida Horiz: <?= campo($usados, 'eq_lv_horizontal', $d['eq_lv_horizontal']??'') ?></td>
        </tr>
        <tr>
            <td>Eslinga Absorbedor: <?= campo($usados, 'eq_eslinga_abs', $d['eq_eslinga_abs']??'') ?></td>
            <td>Anclaje Fijo: <?= campo($usados, 'eq_anclaje_fijo', $d['eq_anclaje_fijo']??'') ?></td>
            <td>Freno: <?= campo($usados, 'eq_freno', $d['eq_freno']??'') ?></td>
            <td>Anclaje Móvil: <?= campo($usados, 'eq_anclaje_movil', $d['eq_anclaje_movil']??'') ?></td>
        </tr>
        <tr>
            <td>Red Seguridad: <?= campo($usados, 'eq_red_seg', $d['eq_red_seg']??'') ?></td>
            <td>Malla Restricción: <?= campo($usados, 'eq_malla', $d['eq_malla']??'') ?></td>
            <?php
            $sistemaTipo   = $d['sistema_tipo']   ?? '';
            $sistemaAcceso = $d['sistema_acceso'] ?? '';
            $sistemaTipoTxt   = is_array($sistemaTipo) ? implode(', ', $sistemaTipo) : $sistemaTipo;
            $sistemaAccesoTxt = is_array($sistemaAcceso) ? implode(', ', $sistemaAcceso) : $sistemaAcceso;
            ?>
            <td>Sistema: <?= campo($usados, 'sistema_tipo', $sistemaTipoTxt) ?></td>
            <td>Acceso: <?= campo($usados, 'sistema_acceso', $sistemaAccesoTxt) ?></td>
        </tr>
        <tr>
            <td colspan="2"><?= campo($usados, 'eq_otro_nombre_1', $d['eq_otro_nombre_1']??'') ?>: <?= campo($usados, 'eq_otro_valor_1', $d['eq_otro_valor_1']??'') ?></td>
            <td colspan="2"><?= campo($usados, 'eq_otro_nombre_2', $d['eq_otro_nombre_2']??'') ?>: <?= campo($usados, 'eq_otro_valor_2', $d['eq_otro_valor_2']??'') ?></td>
        </tr>
    </table>

    <div class="section-title">4. MEDIDAS PREVENTIVAS Y COLECTIVAS</div>
    <table style="font-size:7pt;">
        <tr>
            <td colspan="4" class="label">Medidas de Prevención</td>
        </tr>
        <tr>
            <td>Análisis otros peligros: <?= campo($usados, 'prev_peligros', $d['prev_peligros']??'') ?></td>
            <td>Capacitación SST: <?= campo($usados, 'prev_cap', $d['prev_cap']??'') ?></td>
            <td>Sistemas ingeniería: <?= campo($usados, 'prev_ing', $d['prev_ing']??'') ?></td>
            <td>Procedimientos: <?= campo($usados, 'prev_proc', $d['prev_proc']??'') ?></td>
        </tr>
        <tr>
            <td colspan="4">Trabajos en suspensión: <?= campo($usados, 'prev_susp', $d['prev_susp']??'') ?></td>
        </tr>
        <tr>
            <td colspan="4" class="label">Medidas Colectivas</td>
        </tr>
        <tr>
            <td>Delimitación área: <?= campo($usados, 'col_delim', $d['col_delim']??'') ?></td>
            <td>Control de acceso: <?= campo($usados, 'col_acceso', $d['col_acceso']??'') ?></td>
            <td>Línea advertencia: <?= campo($usados, 'col_adv', $d['col_adv']??'') ?></td>
            <td>Manejo desniveles: <?= campo($usados, 'col_desniv', $d['col_desniv']??'') ?></td>
        </tr>
        <tr>
            <td>Señalización: <?= campo($usados, 'col_senal', $d['col_senal']??'') ?></td>
            <td>Control superficies: <?= campo($usados, 'col_sup', $d['col_sup']??'') ?></td>
            <td>Bandas: <?= campo($usados, 'col_bandas', $d['col_bandas']??'') ?></td>
            <td>Ayudante o vigía: <?= campo($usados, 'col_vigia', $d['col_vigia']??'') ?></td>
        </tr>
    </table>

    <div class="section-title">5. CONDICIONES DE SEGURIDAD Y VERIFICACIÓN DIARIA</div>
    <table style="font-size:7pt;" class="sub-table">
        <?php for($i=0; $i<16; $i++): if(isset($d["preg_t_$i"])): $usados["preg_t_$i"] = true; ?>
        <tr>
            <td style="width:70%"><?= e($d["preg_t_$i"]??'') ?></td>
            <td style="width:15%">Día: <?= campo($usados, "preg_d_$i", $d["preg_d_$i"] ?? '') ?></td>
            <td style="width:15%"><?= campo($usados, "preg_v_$i", $d["preg_v_$i"] ?? '') ?></td>
        </tr>
        <?php endif; endfor; ?>
    </table>

    <div class="section-title">6. RESPONSABLES DE VERIFICACIÓN Y AUTORIZACIÓN</div>
    <table style="font-size:7pt;" class="sub-table">
        <tr><th>Responsable</th><th style="width:15%">Día</th><th style="width:16%">Firma</th></tr>
        <?php
        $respRoles = [
            1 => 'Firma de verificación de quien autoriza el trabajo',
            2 => 'Firma de verificación de coordinador',
            3 => 'Firma de verificación de quien autoriza el trabajo suplente',
            4 => 'Firma de verificación de coordinador suplente',
        ];
        foreach ($respRoles as $i => $rol): ?>
        <tr>
            <td><?= e($rol) ?></td>
            <td><?= campo($usados, "resp_dia_$i", $d["resp_dia_$i"]??'') ?></td>
            <td><?php if (!empty($d["resp_firma_$i"])): ?><img src="<?= e($d["resp_firma_$i"]) ?>" class="firma-img"><?php endif; ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <div class="section-title">7. HERRAMIENTAS A UTILIZAR</div>
    <table style="font-size:7pt;">
        <tr>
            <?php foreach (['Manuales', 'Eléctricas', 'Neumáticas', 'Hidráulicas', 'Mecánicas', 'Otra'] as $i => $lbl): ?>
            <td><?= e($lbl) ?>: <?= campo($usados, "herr_$i", $d["herr_$i"]??'') ?></td>
            <?php endforeach; ?>
        </tr>
    </table>

    <div class="section-title">8. SUSPENSIÓN DEL PERMISO</div>
    <table style="font-size:7pt;">
        <tr><td class="label">Coordinador</td><td><?= campo($usados, 'susp_nombre', $d['susp_nombre']??'') ?></td><td class="label">Tipo Doc</td><td><?= campo($usados, 'susp_tipo_doc', $d['susp_tipo_doc']??'') ?></td></tr>
        <tr><td class="label">N° Documento</td><td><?= campo($usados, 'susp_num_doc', $d['susp_num_doc']??'') ?></td><td class="label">Día</td><td><?= campo($usados, 'susp_dia', $d['susp_dia']??'') ?></td></tr>
        <tr><td class="label">Fecha</td><td><?= campo($usados, 'susp_fecha', $d['susp_fecha']??'', 'date') ?></td><td class="label">Motivo</td><td><?= campo($usados, 'susp_motivo', $d['susp_motivo']??'') ?></td></tr>
        <tr><td class="label">Causa / Descripción</td><td colspan="3"><?= campo($usados, 'susp_causa', $d['susp_causa']??'') ?></td></tr>
        <tr><td class="label">Firma Coordinador</td><td><?php if (!empty($d['susp_firma'])): ?><img src="<?= e($d['susp_firma']) ?>" class="firma-img"><?php endif; ?></td>
            <td class="label">Firma Reactivación</td><td><?php if (!empty($d['susp_coord_firma'])): ?><img src="<?= e($d['susp_coord_firma']) ?>" class="firma-img"><?php endif; ?></td></tr>
    </table>

    <div class="section-title">9. OBSERVACIONES Y AUTORIZACIÓN</div>
    <table>
        <tr>
            <td style="width:50%;"><b>Observaciones:</b><br><?= campo($usados, 'observaciones', $d['observaciones']??'', 'textarea') ?></td>
            <td style="width:50%;">
                ¿Aprueba?: <?= campo($usados, 'aprueba', $d['aprueba']??'') ?><br>
                Motivo Negación: <?= campo($usados, 'motivo_negacion', $d['motivo_negacion']??'') ?>
            </td>
        </tr>
    </table>

    <table style="font-size:7pt;" class="sub-table">
        <tr>
            <th>Rol</th><th>Nombre</th><th>Identificación</th><th>Firma</th>
        </tr>
        <?php
        $rolesAutorizacion = ['Jefe Encargado', 'Jefe Encargado Suplente', 'Coordinador Trabajo Seguro', 'Coordinador Suplente'];
        foreach ($rolesAutorizacion as $i => $rol): ?>
        <tr>
            <td><?= e($rol) ?></td>
            <td><?= campo($usados, "firma_n_$i", $d["firma_n_$i"]??'') ?></td>
            <td><?= campo($usados, "firma_id_$i", $d["firma_id_$i"]??'') ?></td>
            <td><?php if (!empty($d["firma_f_$i"])): ?><img src="<?= e($d["firma_f_$i"]) ?>" class="firma-img"><?php endif; ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <?php
    // ── OTROS CAMPOS: cualquier dato del JSON que no tenga una sección propia
    // definida arriba cae aquí — así nunca se pierde algo que exista en el registro. ──
    $otros = [];
    foreach ($d as $k => $v) {
        if (isset($usados[$k])) continue;
        if ($k === 'id_flujo') continue;
        if (is_array($v)) continue;
        if (str_contains($k, 'firma')) continue;
        $otros[$k] = $v;
    }
    if (!empty($otros)):
    ?>
    <div class="section-title">OTROS CAMPOS</div>
    <table style="font-size:7pt;">
        <?php foreach ($otros as $k => $v): ?>
        <tr><td class="label"><?= e(ucwords(str_replace('_', ' ', $k))) ?></td><td colspan="3"><?= campo($usados, $k, $v) ?></td></tr>
        <?php endforeach; ?>
    </table>
    <?php endif; ?>

    <div style="font-size:6pt; margin-top:5px; text-align:justify; color:#555;">
        <b>NOTA NORMATIVA:</b> El presente Permiso de Trabajo se emite y ejecuta en cumplimiento de la normatividad vigente en Seguridad y Salud en el Trabajo en Colombia, especialmente lo establecido en la Resolución 4272 de 2021, la Resolución 0491 de 2020 y el RETIE, aplicable a las actividades que involucren riesgo eléctrico o intervención de instalaciones energizadas.
    </div>

    <div class="save-bar">
        <span class="save-status"></span>
        <button type="button" class="btn-save">💾 Guardar Cambios</button>
    </div>

</div>
<?php endforeach; ?>

<script>
const ARCHIVO_ACTUAL = <?= json_encode($target_file) ?>;

document.querySelectorAll('.page-wrap').forEach(wrap => {
    const btn = wrap.querySelector('.btn-save');
    const status = wrap.querySelector('.save-status');
    const idRegistro = wrap.dataset.idRegistro;

    btn.addEventListener('click', () => {
        const datos = {};
        wrap.querySelectorAll('.edit-field').forEach(el => {
            const field = el.dataset.field;
            if (el.dataset.index !== undefined) {
                if (!Array.isArray(datos[field])) datos[field] = [];
                datos[field][+el.dataset.index] = el.value;
            } else {
                datos[field] = el.value;
            }
        });

        btn.disabled = true;
        btn.textContent = 'Guardando...';
        status.textContent = '';

        fetch('actualizar_registro.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ file: ARCHIVO_ACTUAL, id_registro: idRegistro, datos })
        })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success') {
                status.style.color = '#10B981';
                status.textContent = '✓ Guardado correctamente';
            } else {
                status.style.color = '#FF3366';
                status.textContent = '✗ ' + (data.message || 'No se pudo guardar.');
            }
        })
        .catch(() => {
            status.style.color = '#FF3366';
            status.textContent = '✗ Error de conexión.';
        })
        .finally(() => {
            btn.disabled = false;
            btn.textContent = '💾 Guardar Cambios';
            setTimeout(() => { status.textContent = ''; }, 5000);
        });
    });
});
</script>

</body>
</html>
