<?php
require_once '../sesion.php';
verificarAutenticacion();

$file = $_GET['file'] ?? '';
$id = $_GET['id'] ?? '';
$sede = $_SESSION['sede'] ?? 'NA';

$registro = null;
$path = "../../archivos/generados/orden_mantenimiento/" . $sede . "/" . $file . ".json";

if (file_exists($path)) {
    $data = json_decode(file_get_contents($path), true);
    foreach ($data as $reg) {
        if ($reg['id'] === $id) {
            $registro = $reg;
            break;
        }
    }
}

if (!$registro) {
    die("Registro no encontrado.");
}

$d = $registro['datos'];
$ev = $registro['evidencias'] ?? [];
$sigs = $registro['firmas'] ?? [];

// Cargar plantilla
$template_path = "../plantillas/formulario001.html";
if (!file_exists($template_path)) {
    die("Error: No se encontró la plantilla en " . $template_path);
}
$html = file_get_contents($template_path);

// Función para formatear imagen de firma
$getSigTag = function($fileName) {
    if (!$fileName) return "";
    $path = "../../archivos/generados/orden_mantenimiento/evidencias/" . $fileName;
    return '<img src="' . $path . '" style="max-width: 150px; max-height: 80px;">';
};

$getPhotoTag = function($fileName) {
    if (!$fileName) return "Sin evidencia";
    $path = "../../archivos/generados/orden_mantenimiento/evidencias/" . $fileName;
    return '<img src="' . $path . '" style="max-width: 100%; max-height: 250px; border-radius: 4px;">';
};

// Mapeo de reemplazos básicos
$replacements = [
    '{{fechainicial}}' => $d['fecha_solicitud'] ?? '',
    '{{horainicial}}' => $d['hora_solicitud'] ?? '',
    '{{ordendetrabajo}}' => $d['numero_orden'] ?? $id,
    '{{cargo_solicitante}}' => $d['cargo_solicitante'] ?? '',
    '{{nombre_solicitante}}' => $d['nombre_solicitante'] ?? '',
    '{{objeto_dañado}}' => $d['objeto_dañado'] ?? '',
    '{{cod}}' => $d['codigo_equipo'] ?? '',
    '{{marca}}' => $d['marca'] ?? '',
    '{{ubi}}' => $d['ubicacion'] ?? '',
    '{{descripcion_daños}}' => nl2br($d['descripcion_falla'] ?? ''),
    '{{tipomantenimiento}}' => $d['tipo_ejecucion'] ?? '',
    '{{descripcion_trabajo}}' => nl2br($d['trabajo_realizado'] ?? ''),
    '{{fecha_cierre}}' => $d['fecha_cierre'] ?? '',
    '{{hora_cierre}}' => $d['hora_cierre'] ?? '',
    '{{VoBo}}' => $d['vobo_mantenimiento'] ?? '',
    '{{descripcion_inocuidad}}' => $d['insumos_riesgo'] ?? '',
    '{{retiro_inocuidad}}' => $d['retirados_zona'] ?? '',
    '{{descripcion_novedad}}' => $d['novedad_inocuidad'] ?? '',
    '{{riesgo_inocuidad}}' => $d['condiciones_higienicas'] ?? '',
    '{{implementos}}' => $d['implementos_limpieza'] ?? '',
    '{{fecha_revisionl}}' => $d['fecha_revisionl'] ?? '',
    '{{hora_revisionl}}' => $d['hora_revisionl'] ?? '',
    '{{Vobo_ingreso}}' => $d['vobo_ingreso_control'] ?? '',
    '{{Vobo_salida}}' => $d['vobo_salida_control'] ?? '',
    '{{control_responsable}}' => $d['control_responsable'] ?? '',
    '{{cargo_control}}' => $d['cargo_control'] ?? '',
    '{{fechacontrol}}' => $d['fechacontrol'] ?? '',
    '{{trabajo_realizar}}' => $d['trabajo_realizar_control'] ?? '',
    '{{nprov}}' => (($d['tipo_responsable'] ?? '') === 'Proveedor' ? ($d['nombre_responsable'] ?? 'N/A') : 'N/A'),
    '{{responsable-Miembro_De_La_Compañia_0}}' => (($d['tipo_responsable'] ?? '') === 'Miembro De La Compañia' ? ($d['nombre_responsable'] ?? 'N/A') : 'N/A'),
    
    // Firmas
    '{{firma_solicitante}}' => $getSigTag($sigs['solicitante'] ?? null),
    '{{firma_autorizado}}' => $getSigTag($sigs['autorizado'] ?? null),
    '{{firma_respLim}}' => $getSigTag($sigs['limpieza'] ?? null),
    '{{firma_respLim2}}' => $getSigTag($sigs['revisa_limpieza'] ?? null),
    
    // Fotos
    '{{evidencia_antes_1}}'       => $getPhotoTag($ev['antes']    ?? null),
    '{{evidencia_despues_1}}'     => $getPhotoTag($ev['despues']  ?? null),
    '{{evidencia_antes_2}}'       => $getPhotoTag($ev['antes2']   ?? null),
    '{{evidencia_despues_2}}'     => $getPhotoTag($ev['despues2'] ?? null),
    '{{evidencia_antes_2_style}}' => empty($ev['antes2'])   ? 'display:none;' : '',
    '{{evidencia_despues_2_style}}' => empty($ev['despues2']) ? 'display:none;' : '',

    '{{mediciones_style}}' => (($d['usa_mediciones'] ?? '0') === '1' ? '' : 'display: none;'),
];

// Procesar Herramientas (Max 8) — solo datos de la sección "1. Herramientas"
// del formulario (tool_cant/tool_desc/tool_salida). No se mezclan con
// Piezas y Repuestos (part_*), que es una sección distinta.
for ($i = 0; $i < 8; $i++) {
    $hasData = !empty($d['tool_cant'][$i]) || !empty($d['tool_desc'][$i]);
    $replacements['{{row_tool_' . ($i + 1) . '_style}}'] = $hasData ? '' : 'display: none;';

    $replacements['{{herramientas_cantidad' . ($i + 1) . '}}'] = $d['tool_cant'][$i] ?? '';
    $replacements['{{descripcion_herramientas' . ($i + 1) . '}}'] = $d['tool_desc'][$i] ?? '';
    $replacements['{{herramientas_salida' . ($i + 1) . '}}'] = $d['tool_salida'][$i] ?? '';
}

// Procesar Materiales (Max 8)
for ($i = 0; $i < 8; $i++) {
    $hasData = !empty($d['mat_cant'][$i]) || !empty($d['mat_desc'][$i]);
    $replacements['{{row_mat_' . ($i + 1) . '_style}}'] = $hasData ? '' : 'display: none;';

    $replacements['{{materiales_cantidad' . ($i + 1) . '}}'] = $d['mat_cant'][$i] ?? '';
    $replacements['{{medida_materiales' . ($i + 1) . '}}'] = $d['mat_unidad'][$i] ?? '';
    $replacements['{{descripcion_materiales' . ($i + 1) . '}}'] = $d['mat_desc'][$i] ?? '';
    $replacements['{{materiales_utilizados' . ($i + 1) . '}}'] = $d['mat_used'][$i] ?? '';
    $replacements['{{verificacion_material' . ($i + 1) . '}}'] = $d['mat_verif'][$i] ?? '';
}

// Procesar Mediciones Dinámicas (Max 10)
$med_equipos = $d['med_equipo_name'] ?? [];
$num_mediciones = is_array($med_equipos) ? count($med_equipos) : 0;

for ($i = 0; $i < 10; $i++) {
    $hasData = ($i < $num_mediciones) && !empty($med_equipos[$i]);
    $replacements['{{row_med_' . ($i + 1) . '_style}}'] = ($hasData && ($d['usa_mediciones'] ?? '0') === '1') ? '' : 'display: none;';
    
    $replacements['{{equipo_name' . ($i + 1) . '}}'] = $med_equipos[$i] ?? '';
    $replacements['{{parte' . ($i + 1) . '}}'] = $d['med_parte'][$i] ?? '';
    $replacements['{{termografia' . ($i + 1) . '}}'] = $d['med_termografia'][$i] ?? '';
    $replacements['{{vibraciones' . ($i + 1) . '}}'] = $d['med_vibraciones'][$i] ?? '';
    $replacements['{{rango' . ($i + 1) . '}}'] = $d['med_rango'][$i] ?? '';
    $replacements['{{amperaje' . ($i + 1) . '}}'] = $d['med_amperaje'][$i] ?? '';
    $replacements['{{observaciones' . ($i + 1) . '}}'] = $d['med_obs'][$i] ?? '';
}

// Aplicar reemplazos
$final_html = str_replace(array_keys($replacements), array_values($replacements), $html);

// Fix CSS path
$final_html = str_replace('href="formulario001.css"', 'href="../plantillas/formulario001.css"', $final_html);

// ── Helpers para el modal ────────────────────────────────────────────────────
function he($v) { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
function hej($v) { return json_encode($v ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE); }
function sigImg($sigs, $key, $label) {
    $file = $sigs[$key] ?? null;
    if ($file) {
        return '<div style="margin-bottom:4px;font-size:11px;color:#555;">Firma actual — ' . he($label) . '</div>'
             . '<img src="../../archivos/generados/orden_mantenimiento/evidencias/' . he($file) . '" '
             . 'style="max-width:200px;max-height:80px;border:1px solid #ccc;border-radius:3px;display:block;margin-bottom:6px;">';
    }
    return '<div style="margin-bottom:6px;font-size:11px;color:#999;">Sin firma registrada — ' . he($label) . '</div>';
}
function evImg($ev, $key, $label) {
    $file = $ev[$key] ?? null;
    if ($file) {
        return '<div style="font-size:11px;color:#555;margin-bottom:4px;">' . he($label) . ' (actual)</div>'
             . '<img src="../../archivos/generados/orden_mantenimiento/evidencias/' . he($file) . '" '
             . 'style="max-width:220px;max-height:160px;border:1px solid #ccc;border-radius:4px;display:block;margin-bottom:8px;">';
    }
    return '<div style="font-size:11px;color:#999;margin-bottom:6px;">Sin foto — ' . he($label) . '</div>';
}

// Preparar arrays dinámicos para el modal
$tool_rows = max(count($d['tool_cant'] ?? []), count($d['tool_desc'] ?? []), 1);
$mat_rows  = max(count($d['mat_cant']  ?? []), count($d['mat_desc']  ?? []), 1);
$med_rows  = max(count($d['med_equipo_name'] ?? []), 1);

// ── Construir modal HTML ─────────────────────────────────────────────────────
ob_start();
?>
<!-- ═══════════════════════════════ MODAL CORRECCIÓN ═══════════════════════════════ -->
<style>
.som-modal-overlay{display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,.82);z-index:99999;overflow-y:auto;padding:24px 16px}
.som-modal-overlay.active{display:block}
.som-modal{background:#fff;max-width:900px;margin:0 auto;border-radius:6px;overflow:hidden;font-family:Arial,sans-serif;color:#1a1d2e}
.som-modal-header{background:#1a1d2e;color:#fff;padding:18px 28px;display:flex;justify-content:space-between;align-items:center}
.som-modal-header h2{font-size:16px;text-transform:uppercase;letter-spacing:.05em;margin:0}
.som-btn-close{background:none;border:none;color:#fff;font-size:22px;cursor:pointer;padding:0 4px}
.som-modal-body{padding:28px}
.som-sec-title{background:#f4f4f4;border:1px solid #ccc;padding:6px 12px;font-weight:700;font-size:12px;text-transform:uppercase;margin:20px 0 10px;letter-spacing:.04em}
.som-grid{display:grid;gap:10px;margin-bottom:10px}
.som-grid-2{grid-template-columns:1fr 1fr}
.som-grid-3{grid-template-columns:1fr 1fr 1fr}
.som-grid-4{grid-template-columns:1fr 1fr 1fr 1fr}
.som-fg{display:flex;flex-direction:column;gap:3px}
.som-fg label{font-size:10px;font-weight:700;color:#555;text-transform:uppercase}
.som-fg input,.som-fg textarea,.som-fg select{border:1px solid #ccc;padding:6px 9px;font-size:12px;border-radius:3px;font-family:inherit;width:100%;box-sizing:border-box}
.som-fg textarea{resize:vertical;min-height:65px}
.som-tbl{width:100%;border-collapse:collapse;font-size:11px;margin-bottom:6px}
.som-tbl th{background:#f4f4f4;border:1px solid #ddd;padding:5px 6px;text-align:left;font-size:10px;text-transform:uppercase;white-space:nowrap}
.som-tbl td{border:1px solid #ddd;padding:2px}
.som-tbl input{width:100%;border:none;padding:4px 6px;font-size:11px;font-family:inherit;box-sizing:border-box}
.som-btn-addrow{background:#f0f0f0;border:1px dashed #aaa;padding:4px 14px;cursor:pointer;font-size:11px;border-radius:3px;margin-top:4px}
.som-btn-delrow{background:#fee2e2;border:none;cursor:pointer;padding:3px 8px;font-size:11px;color:#dc2626;border-radius:2px;white-space:nowrap}
.som-sig-block{border:1px solid #e0e0e0;border-radius:4px;padding:14px;margin-bottom:10px}
.som-sig-label{font-weight:700;font-size:12px;text-transform:uppercase;margin-bottom:8px;color:#1a1d2e}
.som-canvas{border:2px solid #1a1d2e;cursor:crosshair;background:#fff;touch-action:none;display:block}
.som-btn-clear{background:#fee2e2;border:1px solid #dc2626;color:#dc2626;padding:4px 12px;cursor:pointer;font-size:11px;border-radius:3px;margin-top:6px}
.som-ev-block{border:1px solid #e0e0e0;border-radius:4px;padding:14px;margin-bottom:10px}
.som-ev-upload{border:2px dashed #ccc;border-radius:4px;padding:14px;text-align:center;cursor:pointer;font-size:12px;color:#555;margin-top:8px}
.som-ev-upload:hover{border-color:#1a1d2e;background:#fafafa}
.som-preview{max-width:100%;max-height:200px;border-radius:4px;display:block;margin-top:8px}
.som-modal-footer{padding:20px 28px;border-top:1px solid #eee;display:flex;justify-content:flex-end;gap:10px}
.som-btn-cancel{background:#f0f0f0;border:1px solid #ccc;padding:10px 24px;cursor:pointer;font-size:13px;border-radius:4px;font-family:inherit}
.som-btn-save{background:#1a1d2e;color:#fff;border:none;padding:10px 28px;cursor:pointer;font-size:13px;border-radius:4px;font-weight:700;font-family:inherit}
.som-btn-save:disabled{background:#999;cursor:not-allowed}
</style>

<div class="som-modal-overlay" id="som-modal-overlay">
<div class="som-modal">
    <div class="som-modal-header">
        <h2>Corregir Registro — Orden de Mantenimiento</h2>
        <button class="som-btn-close" onclick="somCerrar()">✕</button>
    </div>
    <div class="som-modal-body">
        <p style="font-size:12px;color:#777;margin-bottom:4px;">⚠️ El <strong>N° de Orden de Trabajo</strong> no se modifica aquí (usa el botón <em>✏️ N° Orden</em>).</p>

        <!-- ═══ DATOS DE SOLICITUD ═══ -->
        <div class="som-sec-title">Datos de la Solicitud</div>
        <div class="som-grid som-grid-4">
            <div class="som-fg"><label>Fecha Solicitud</label><input type="date" id="sc_fecha_solicitud" value="<?= he($d['fecha_solicitud'] ?? '') ?>"></div>
            <div class="som-fg"><label>Hora Solicitud</label><input type="time" id="sc_hora_solicitud" value="<?= he($d['hora_solicitud'] ?? '') ?>"></div>
            <div class="som-fg"><label>Cargo Solicitante</label><input type="text" id="sc_cargo_solicitante" value="<?= he($d['cargo_solicitante'] ?? '') ?>"></div>
            <div class="som-fg"><label>Nombre Solicitante</label><input type="text" id="sc_nombre_solicitante" value="<?= he($d['nombre_solicitante'] ?? '') ?>"></div>
        </div>
        <div class="som-grid som-grid-4">
            <div class="som-fg"><label>Objeto Dañado</label><input type="text" id="sc_objeto_danado" value="<?= he($d['objeto_dañado'] ?? '') ?>"></div>
            <div class="som-fg"><label>Código Equipo</label><input type="text" id="sc_codigo_equipo" value="<?= he($d['codigo_equipo'] ?? '') ?>"></div>
            <div class="som-fg"><label>Marca</label><input type="text" id="sc_marca" value="<?= he($d['marca'] ?? '') ?>"></div>
            <div class="som-fg"><label>Ubicación</label><input type="text" id="sc_ubicacion" value="<?= he($d['ubicacion'] ?? '') ?>"></div>
        </div>
        <div class="som-grid">
            <div class="som-fg"><label>Descripción de la Falla</label><textarea id="sc_descripcion_falla"><?= he($d['descripcion_falla'] ?? '') ?></textarea></div>
        </div>

        <!-- ═══ EJECUCIÓN DEL TRABAJO ═══ -->
        <div class="som-sec-title">Ejecución del Trabajo</div>
        <div class="som-grid som-grid-4">
            <div class="som-fg"><label>Tipo de Ejecución</label><input type="text" id="sc_tipo_ejecucion" value="<?= he($d['tipo_ejecucion'] ?? '') ?>"></div>
            <div class="som-fg"><label>Fecha Cierre</label><input type="date" id="sc_fecha_cierre" value="<?= he($d['fecha_cierre'] ?? '') ?>"></div>
            <div class="som-fg"><label>Hora Cierre</label><input type="time" id="sc_hora_cierre" value="<?= he($d['hora_cierre'] ?? '') ?>"></div>
            <div class="som-fg"><label>VoBo Mantenimiento</label><input type="text" id="sc_vobo_mantenimiento" value="<?= he($d['vobo_mantenimiento'] ?? '') ?>"></div>
        </div>
        <div class="som-grid">
            <div class="som-fg"><label>Trabajo Realizado</label><textarea id="sc_trabajo_realizado"><?= he($d['trabajo_realizado'] ?? '') ?></textarea></div>
        </div>
        <div class="som-grid som-grid-2">
            <div class="som-fg"><label>Tipo Responsable</label>
                <select id="sc_tipo_responsable">
                    <option value="Miembro De La Compañia" <?= (($d['tipo_responsable']??'') === 'Miembro De La Compañia') ? 'selected' : '' ?>>Miembro De La Compañía</option>
                    <option value="Proveedor" <?= (($d['tipo_responsable']??'') === 'Proveedor') ? 'selected' : '' ?>>Proveedor</option>
                </select>
            </div>
            <div class="som-fg"><label>Nombre Responsable</label><input type="text" id="sc_nombre_responsable" value="<?= he($d['nombre_responsable'] ?? '') ?>"></div>
        </div>

        <!-- ═══ INOCUIDAD ═══ -->
        <div class="som-sec-title">Inocuidad y Limpieza</div>
        <div class="som-grid som-grid-2">
            <div class="som-fg"><label>Insumos con Riesgo</label><textarea id="sc_insumos_riesgo"><?= he($d['insumos_riesgo'] ?? '') ?></textarea></div>
            <div class="som-fg"><label>Retirados de la Zona</label><textarea id="sc_retirados_zona"><?= he($d['retirados_zona'] ?? '') ?></textarea></div>
        </div>
        <div class="som-grid som-grid-2">
            <div class="som-fg"><label>Novedad Inocuidad</label><textarea id="sc_novedad_inocuidad"><?= he($d['novedad_inocuidad'] ?? '') ?></textarea></div>
            <div class="som-fg"><label>Condiciones Higiénicas</label><textarea id="sc_condiciones_higienicas"><?= he($d['condiciones_higienicas'] ?? '') ?></textarea></div>
        </div>
        <div class="som-grid som-grid-3">
            <div class="som-fg"><label>Implementos de Limpieza</label><input type="text" id="sc_implementos_limpieza" value="<?= he($d['implementos_limpieza'] ?? '') ?>"></div>
            <div class="som-fg"><label>Fecha Revisión Limp.</label><input type="date" id="sc_fecha_revisionl" value="<?= he($d['fecha_revisionl'] ?? '') ?>"></div>
            <div class="som-fg"><label>Hora Revisión Limp.</label><input type="time" id="sc_hora_revisionl" value="<?= he($d['hora_revisionl'] ?? '') ?>"></div>
        </div>

        <!-- ═══ CONTROL DE PARTES / INGRESO ═══ -->
        <div class="som-sec-title">Control de Partes e Ingreso</div>
        <div class="som-grid som-grid-2">
            <div class="som-fg"><label>VoBo Ingreso Control</label><input type="text" id="sc_vobo_ingreso_control" value="<?= he($d['vobo_ingreso_control'] ?? '') ?>"></div>
            <div class="som-fg"><label>VoBo Salida Control</label><input type="text" id="sc_vobo_salida_control" value="<?= he($d['vobo_salida_control'] ?? '') ?>"></div>
        </div>
        <div class="som-grid som-grid-2">
            <div class="som-fg"><label>Responsable Control</label><input type="text" id="sc_control_responsable" value="<?= he($d['control_responsable'] ?? '') ?>"></div>
            <div class="som-fg"><label>Cargo Control</label><input type="text" id="sc_cargo_control" value="<?= he($d['cargo_control'] ?? '') ?>"></div>
        </div>
        <div class="som-grid som-grid-2">
            <div class="som-fg"><label>Fecha Control</label><input type="date" id="sc_fechacontrol" value="<?= he($d['fechacontrol'] ?? '') ?>"></div>
            <div class="som-fg"><label>Trabajo a Realizar (Control)</label><input type="text" id="sc_trabajo_realizar_control" value="<?= he($d['trabajo_realizar_control'] ?? '') ?>"></div>
        </div>

        <!-- ═══ TABLA HERRAMIENTAS / PIEZAS ═══ -->
        <div class="som-sec-title">Herramientas y Piezas (máx 8 filas)</div>
        <table class="som-tbl" id="sc-tbl-herr">
            <thead>
                <tr>
                    <th>Cant.</th><th>Descripción Herramienta</th><th>Salida</th>
                    <th>Cant. Piezas</th><th>Desc. Piezas</th><th>Usadas</th><th>Sin Usar</th><th>Quitadas</th><th>Verificación</th><th></th>
                </tr>
            </thead>
            <tbody>
                <?php for ($i = 0; $i < $tool_rows; $i++): ?>
                <tr>
                    <td><input type="text" name="tool_cant[]" value="<?= he($d['tool_cant'][$i] ?? '') ?>"></td>
                    <td><input type="text" name="tool_desc[]" value="<?= he($d['tool_desc'][$i] ?? '') ?>"></td>
                    <td><input type="text" name="tool_salida[]" value="<?= he($d['tool_salida'][$i] ?? '') ?>"></td>
                    <td><input type="text" name="part_cant[]" value="<?= he($d['part_cant'][$i] ?? '') ?>"></td>
                    <td><input type="text" name="part_desc[]" value="<?= he($d['part_desc'][$i] ?? '') ?>"></td>
                    <td><input type="text" name="part_used[]" value="<?= he($d['part_used'][$i] ?? '') ?>"></td>
                    <td><input type="text" name="part_unused[]" value="<?= he($d['part_unused'][$i] ?? '') ?>"></td>
                    <td><input type="text" name="part_removed[]" value="<?= he($d['part_removed'][$i] ?? '') ?>"></td>
                    <td><input type="text" name="part_verif[]" value="<?= he($d['part_verif'][$i] ?? '') ?>"></td>
                    <td><button type="button" class="som-btn-delrow" onclick="somDelRow(this)">✕</button></td>
                </tr>
                <?php endfor; ?>
            </tbody>
        </table>
        <button type="button" class="som-btn-addrow" onclick="somAddRowHerr()">+ Fila</button>

        <!-- ═══ TABLA MATERIALES ═══ -->
        <div class="som-sec-title">Materiales (máx 8 filas)</div>
        <table class="som-tbl" id="sc-tbl-mat">
            <thead>
                <tr>
                    <th>Cant.</th><th>Unidad</th><th>Descripción</th><th>Utilizados</th><th>Verificación</th><th></th>
                </tr>
            </thead>
            <tbody>
                <?php for ($i = 0; $i < $mat_rows; $i++): ?>
                <tr>
                    <td><input type="text" name="mat_cant[]" value="<?= he($d['mat_cant'][$i] ?? '') ?>"></td>
                    <td><input type="text" name="mat_unidad[]" value="<?= he($d['mat_unidad'][$i] ?? '') ?>"></td>
                    <td><input type="text" name="mat_desc[]" value="<?= he($d['mat_desc'][$i] ?? '') ?>"></td>
                    <td><input type="text" name="mat_used[]" value="<?= he($d['mat_used'][$i] ?? '') ?>"></td>
                    <td><input type="text" name="mat_verif[]" value="<?= he($d['mat_verif'][$i] ?? '') ?>"></td>
                    <td><button type="button" class="som-btn-delrow" onclick="somDelRow(this)">✕</button></td>
                </tr>
                <?php endfor; ?>
            </tbody>
        </table>
        <button type="button" class="som-btn-addrow" onclick="somAddRowMat()">+ Fila</button>

        <!-- ═══ MEDICIONES PREDICTIVAS ═══ -->
        <div class="som-sec-title">Mediciones Predictivas</div>
        <div class="som-grid som-grid-2" style="margin-bottom:10px;">
            <div class="som-fg">
                <label>¿Usa Mediciones?</label>
                <select id="sc_usa_mediciones">
                    <option value="0" <?= (($d['usa_mediciones']??'0') !== '1') ? 'selected' : '' ?>>No</option>
                    <option value="1" <?= (($d['usa_mediciones']??'0') === '1')  ? 'selected' : '' ?>>Sí</option>
                </select>
            </div>
        </div>
        <table class="som-tbl" id="sc-tbl-med">
            <thead>
                <tr>
                    <th>Equipo</th><th>Parte</th><th>Termografía</th><th>Vibraciones</th><th>Rango</th><th>Amperaje</th><th>Observaciones</th><th></th>
                </tr>
            </thead>
            <tbody>
                <?php for ($i = 0; $i < $med_rows; $i++): ?>
                <tr>
                    <td><input type="text" name="med_equipo_name[]" value="<?= he($d['med_equipo_name'][$i] ?? '') ?>"></td>
                    <td><input type="text" name="med_parte[]"       value="<?= he($d['med_parte'][$i] ?? '') ?>"></td>
                    <td><input type="text" name="med_termografia[]" value="<?= he($d['med_termografia'][$i] ?? '') ?>"></td>
                    <td><input type="text" name="med_vibraciones[]" value="<?= he($d['med_vibraciones'][$i] ?? '') ?>"></td>
                    <td><input type="text" name="med_rango[]"       value="<?= he($d['med_rango'][$i] ?? '') ?>"></td>
                    <td><input type="text" name="med_amperaje[]"    value="<?= he($d['med_amperaje'][$i] ?? '') ?>"></td>
                    <td><input type="text" name="med_obs[]"         value="<?= he($d['med_obs'][$i] ?? '') ?>"></td>
                    <td><button type="button" class="som-btn-delrow" onclick="somDelRow(this)">✕</button></td>
                </tr>
                <?php endfor; ?>
            </tbody>
        </table>
        <button type="button" class="som-btn-addrow" onclick="somAddRowMed()">+ Fila</button>

        <!-- ═══ FIRMAS ═══ -->
        <div class="som-sec-title">Firmas Digitales</div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">

            <div class="som-sig-block">
                <div class="som-sig-label">Solicitante</div>
                <?= sigImg($sigs, 'solicitante', 'Solicitante') ?>
                <p style="font-size:11px;color:#777;margin:0 0 6px;">Dibuja para reemplazar (deja en blanco para conservar):</p>
                <canvas class="som-canvas" id="canvas_solicitante" width="350" height="120"></canvas>
                <button type="button" class="som-btn-clear" onclick="somLimpiar('canvas_solicitante')">Limpiar</button>
            </div>

            <div class="som-sig-block">
                <div class="som-sig-label">Autorizado</div>
                <?= sigImg($sigs, 'autorizado', 'Autorizado') ?>
                <p style="font-size:11px;color:#777;margin:0 0 6px;">Dibuja para reemplazar:</p>
                <canvas class="som-canvas" id="canvas_autorizado" width="350" height="120"></canvas>
                <button type="button" class="som-btn-clear" onclick="somLimpiar('canvas_autorizado')">Limpiar</button>
            </div>

            <div class="som-sig-block">
                <div class="som-sig-label">Responsable Limpieza</div>
                <?= sigImg($sigs, 'limpieza', 'Resp. Limpieza') ?>
                <p style="font-size:11px;color:#777;margin:0 0 6px;">Dibuja para reemplazar:</p>
                <canvas class="som-canvas" id="canvas_limpieza" width="350" height="120"></canvas>
                <button type="button" class="som-btn-clear" onclick="somLimpiar('canvas_limpieza')">Limpiar</button>
            </div>

            <div class="som-sig-block">
                <div class="som-sig-label">Revisa Limpieza</div>
                <?= sigImg($sigs, 'revisa_limpieza', 'Revisa Limpieza') ?>
                <p style="font-size:11px;color:#777;margin:0 0 6px;">Dibuja para reemplazar:</p>
                <canvas class="som-canvas" id="canvas_revisa_limpieza" width="350" height="120"></canvas>
                <button type="button" class="som-btn-clear" onclick="somLimpiar('canvas_revisa_limpieza')">Limpiar</button>
            </div>

        </div>

        <!-- ═══ EVIDENCIAS FOTOGRÁFICAS ═══ -->
        <div class="som-sec-title">Evidencias Fotográficas</div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">

            <div class="som-ev-block">
                <div class="som-sig-label">Foto ANTES</div>
                <?= evImg($ev, 'antes', 'Foto Antes') ?>
                <div id="preview_antes"></div>
                <div class="som-ev-upload" onclick="document.getElementById('input_foto_antes').click()">
                    📷 Seleccionar nueva foto ANTES<br><span style="font-size:11px;">JPG, PNG, WEBP</span>
                </div>
                <input type="file" id="input_foto_antes" accept="image/*" style="display:none" onchange="somPreviewFoto(this,'preview_antes')">
            </div>

            <div class="som-ev-block">
                <div class="som-sig-label">Foto DESPUÉS</div>
                <?= evImg($ev, 'despues', 'Foto Después') ?>
                <div id="preview_despues"></div>
                <div class="som-ev-upload" onclick="document.getElementById('input_foto_despues').click()">
                    📷 Seleccionar nueva foto DESPUÉS<br><span style="font-size:11px;">JPG, PNG, WEBP</span>
                </div>
                <input type="file" id="input_foto_despues" accept="image/*" style="display:none" onchange="somPreviewFoto(this,'preview_despues')">
            </div>

        </div>
    </div><!-- /som-modal-body -->

    <div class="som-modal-footer">
        <button type="button" class="som-btn-cancel" onclick="somCerrar()">Cancelar</button>
        <button type="button" class="som-btn-save" id="som-btn-save" onclick="somGuardar()">💾 Guardar Correcciones</button>
    </div>
</div><!-- /som-modal -->
</div><!-- /som-modal-overlay -->

<script>
(function() {
// ── Canvas: setup ────────────────────────────────────────────────────────────
const CANVAS_IDS = ['canvas_solicitante','canvas_autorizado','canvas_limpieza','canvas_revisa_limpieza'];
const ctxMap = {};

function initCanvas(id) {
    const cv = document.getElementById(id);
    if (!cv) return;
    const ctx = cv.getContext('2d');
    ctx.fillStyle = '#fff';
    ctx.fillRect(0, 0, cv.width, cv.height);
    ctx.strokeStyle = '#000';
    ctx.lineWidth = 2;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
    ctxMap[id] = { cv, ctx, drawing: false };

    const getPos = (e, rect) => {
        if (e.touches) {
            return { x: e.touches[0].clientX - rect.left, y: e.touches[0].clientY - rect.top };
        }
        return { x: e.clientX - rect.left, y: e.clientY - rect.top };
    };
    const start = (e) => { e.preventDefault(); const s = ctxMap[id]; s.drawing = true; const r = cv.getBoundingClientRect(); const p = getPos(e,r); s.ctx.beginPath(); s.ctx.moveTo(p.x, p.y); };
    const move  = (e) => { e.preventDefault(); const s = ctxMap[id]; if (!s.drawing) return; const r = cv.getBoundingClientRect(); const p = getPos(e,r); s.ctx.lineTo(p.x,p.y); s.ctx.stroke(); };
    const stop  = ()  => { if (ctxMap[id]) ctxMap[id].drawing = false; };

    cv.addEventListener('mousedown',  start);
    cv.addEventListener('mousemove',  move);
    cv.addEventListener('mouseup',    stop);
    cv.addEventListener('mouseleave', stop);
    cv.addEventListener('touchstart', start, { passive: false });
    cv.addEventListener('touchmove',  move,  { passive: false });
    cv.addEventListener('touchend',   stop);
}

function isBlank(id) {
    const cv = document.getElementById(id);
    if (!cv) return true;
    const blank = document.createElement('canvas');
    blank.width = cv.width; blank.height = cv.height;
    const bctx = blank.getContext('2d');
    bctx.fillStyle = '#fff';
    bctx.fillRect(0, 0, blank.width, blank.height);
    return cv.toDataURL() === blank.toDataURL();
}

window.somLimpiar = function(id) {
    const s = ctxMap[id];
    if (s) { s.ctx.fillStyle = '#fff'; s.ctx.fillRect(0, 0, s.cv.width, s.cv.height); }
};

// ── Rows dinámicos ────────────────────────────────────────────────────────────
window.somDelRow = function(btn) { btn.closest('tr').remove(); };

window.somAddRowHerr = function() {
    const tb = document.querySelector('#sc-tbl-herr tbody');
    const tr = document.createElement('tr');
    tr.innerHTML = '<td><input type="text" name="tool_cant[]"></td><td><input type="text" name="tool_desc[]"></td><td><input type="text" name="tool_salida[]"></td><td><input type="text" name="part_cant[]"></td><td><input type="text" name="part_desc[]"></td><td><input type="text" name="part_used[]"></td><td><input type="text" name="part_unused[]"></td><td><input type="text" name="part_removed[]"></td><td><input type="text" name="part_verif[]"></td><td><button type="button" class="som-btn-delrow" onclick="somDelRow(this)">✕</button></td>';
    tb.appendChild(tr);
};

window.somAddRowMat = function() {
    const tb = document.querySelector('#sc-tbl-mat tbody');
    const tr = document.createElement('tr');
    tr.innerHTML = '<td><input type="text" name="mat_cant[]"></td><td><input type="text" name="mat_unidad[]"></td><td><input type="text" name="mat_desc[]"></td><td><input type="text" name="mat_used[]"></td><td><input type="text" name="mat_verif[]"></td><td><button type="button" class="som-btn-delrow" onclick="somDelRow(this)">✕</button></td>';
    tb.appendChild(tr);
};

window.somAddRowMed = function() {
    const tb = document.querySelector('#sc-tbl-med tbody');
    const tr = document.createElement('tr');
    tr.innerHTML = '<td><input type="text" name="med_equipo_name[]"></td><td><input type="text" name="med_parte[]"></td><td><input type="text" name="med_termografia[]"></td><td><input type="text" name="med_vibraciones[]"></td><td><input type="text" name="med_rango[]"></td><td><input type="text" name="med_amperaje[]"></td><td><input type="text" name="med_obs[]"></td><td><button type="button" class="som-btn-delrow" onclick="somDelRow(this)">✕</button></td>';
    tb.appendChild(tr);
};

// ── Preview evidencias ────────────────────────────────────────────────────────
window.somPreviewFoto = function(input, previewId) {
    const file = input.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
        const div = document.getElementById(previewId);
        div.innerHTML = '<img class="som-preview" src="' + e.target.result + '" alt="Preview">';
    };
    reader.readAsDataURL(file);
};

// ── Abrir / Cerrar modal ──────────────────────────────────────────────────────
window.somAbrir = function() {
    document.getElementById('som-modal-overlay').classList.add('active');
    document.body.style.overflow = 'hidden';
    CANVAS_IDS.forEach(initCanvas);
};

window.somCerrar = function() {
    document.getElementById('som-modal-overlay').classList.remove('active');
    document.body.style.overflow = '';
};

// ── Guardar ────────────────────────────────────────────────────────────────────
window.somGuardar = async function() {
    const btn = document.getElementById('som-btn-save');
    btn.disabled = true;
    btn.textContent = 'Guardando…';

    const fd = new FormData();
    fd.append('file', <?= hej($file) ?>);
    fd.append('id',   <?= hej($id) ?>);

    // Datos básicos
    const textFields = {
        fecha_solicitud:        'sc_fecha_solicitud',
        hora_solicitud:         'sc_hora_solicitud',
        cargo_solicitante:      'sc_cargo_solicitante',
        nombre_solicitante:     'sc_nombre_solicitante',
        objeto_dañado:          'sc_objeto_danado',
        codigo_equipo:          'sc_codigo_equipo',
        marca:                  'sc_marca',
        ubicacion:              'sc_ubicacion',
        descripcion_falla:      'sc_descripcion_falla',
        tipo_ejecucion:         'sc_tipo_ejecucion',
        trabajo_realizado:      'sc_trabajo_realizado',
        fecha_cierre:           'sc_fecha_cierre',
        hora_cierre:            'sc_hora_cierre',
        vobo_mantenimiento:     'sc_vobo_mantenimiento',
        tipo_responsable:       'sc_tipo_responsable',
        nombre_responsable:     'sc_nombre_responsable',
        insumos_riesgo:         'sc_insumos_riesgo',
        retirados_zona:         'sc_retirados_zona',
        novedad_inocuidad:      'sc_novedad_inocuidad',
        condiciones_higienicas: 'sc_condiciones_higienicas',
        implementos_limpieza:   'sc_implementos_limpieza',
        fecha_revisionl:        'sc_fecha_revisionl',
        hora_revisionl:         'sc_hora_revisionl',
        vobo_ingreso_control:   'sc_vobo_ingreso_control',
        vobo_salida_control:    'sc_vobo_salida_control',
        control_responsable:    'sc_control_responsable',
        cargo_control:          'sc_cargo_control',
        fechacontrol:           'sc_fechacontrol',
        trabajo_realizar_control: 'sc_trabajo_realizar_control',
        usa_mediciones:         'sc_usa_mediciones',
    };
    for (const [key, elId] of Object.entries(textFields)) {
        const el = document.getElementById(elId);
        if (el) fd.append(key, el.value);
    }

    // Tablas dinámicas — recolectar por nombre
    const collectArray = (name) => {
        document.querySelectorAll('[name="' + name + '[]"]').forEach(inp => fd.append(name + '[]', inp.value));
    };
    ['tool_cant','tool_desc','tool_salida','part_cant','part_desc','part_used','part_unused','part_removed','part_verif',
     'mat_cant','mat_unidad','mat_desc','mat_used','mat_verif',
     'med_equipo_name','med_parte','med_termografia','med_vibraciones','med_rango','med_amperaje','med_obs'
    ].forEach(collectArray);

    // Firmas (solo si canvas tiene dibujo)
    const firmasMap = [
        ['canvas_solicitante', 'firma_solicitante'],
        ['canvas_autorizado',  'firma_autorizado'],
        ['canvas_limpieza',    'firma_respLim'],
        ['canvas_revisa_limpieza', 'firma_respLim2'],
    ];
    firmasMap.forEach(([canvasId, fieldName]) => {
        if (!isBlank(canvasId)) {
            fd.append(fieldName, document.getElementById(canvasId).toDataURL('image/png'));
        }
    });

    // Fotos de evidencia
    const fotoAntes   = document.getElementById('input_foto_antes');
    const fotoDespues = document.getElementById('input_foto_despues');
    if (fotoAntes.files[0])   fd.append('foto_antes',   fotoAntes.files[0]);
    if (fotoDespues.files[0]) fd.append('foto_despues', fotoDespues.files[0]);

    try {
        const res  = await fetch('corregir_registro.php', { method: 'POST', body: fd });
        const data = await res.json();
        if (data.success) {
            alert('✅ Registro corregido correctamente.');
            location.reload();
        } else {
            alert('❌ Error: ' + (data.error || 'Error desconocido'));
            btn.disabled = false;
            btn.textContent = '💾 Guardar Correcciones';
        }
    } catch (e) {
        alert('❌ Error de red al guardar.');
        btn.disabled = false;
        btn.textContent = '💾 Guardar Correcciones';
    }
};
})();
</script>
<?php
$modal_html = ob_get_clean();

// ── Inyectar controles de impresión ──────────────────────────────────────────
$numero_orden_actual = htmlspecialchars($d['numero_orden'] ?? $id, ENT_QUOTES);
$controls = '
<div class="no-print" style="position: fixed; top: 20px; right: 20px; z-index: 9999; background: rgba(0,0,0,0.8); padding: 15px; border-radius: 8px; display: flex; gap: 10px; align-items: center;">
    <a href="galeria.php" style="color: white; text-decoration: none; background: #555; padding: 8px 15px; border-radius: 4px; font-family: sans-serif; font-size: 14px;">Volver</a>
    <button onclick="editarNumeroOrden()" style="color: white; background: #f59e0b; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; font-family: sans-serif; font-size: 14px; font-weight: bold;" title="Editar # de Orden de Trabajo">✏️ N° Orden</button>
    <button onclick="somAbrir()" style="color: white; background: #3b82f6; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; font-family: sans-serif; font-size: 14px; font-weight: bold;" title="Corregir datos del registro">✏️ Corregir Datos</button>
    <button onclick="window.print()" style="color: white; background: #22C55E; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; font-family: sans-serif; font-size: 14px; font-weight: bold;">Imprimir PDF</button>
</div>
<style>
    @media print { .no-print { display: none !important; } }
    body { padding-top: 20px; }
</style>
<script>
function editarNumeroOrden() {
    var actual = "' . $numero_orden_actual . '";
    var nuevo = prompt("Ingrese el nuevo # de Orden de Trabajo:", actual);
    if (nuevo === null) return;
    nuevo = nuevo.trim();
    if (nuevo === "") { alert("El número de orden no puede estar vacío."); return; }

    fetch("editar_orden.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            file: "' . addslashes($file) . '",
            id:   "' . addslashes($id) . '",
            numero: nuevo
        })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            alert("✅ Número de orden actualizado correctamente.");
            location.reload();
        } else {
            alert("❌ Error: " + (data.error || "Error desconocido"));
        }
    })
    .catch(function() { alert("❌ Error de red al conectar con el servidor."); });
}
' . ((isset($_GET['print']) && $_GET['print'] == 1) ? '
window.addEventListener("load", function() {
    setTimeout(function() { window.print(); }, 800);
});
' : '') . '
</script>
';

$final_html = str_replace('</body>', $modal_html . $controls . '</body>', $final_html);

echo $final_html;
