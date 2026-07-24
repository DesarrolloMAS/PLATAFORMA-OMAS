<?php
/**
 * editar_inspeccion_insumos.php
 * ---------------------
 * Herramienta de corrección para registros ya guardados de "Inspección de
 * Insumos". Carga el registro por id_registro y muestra un formulario
 * editable (mismos campos que index.html) para corregir datos mal
 * digitados sin tener que crear un registro nuevo.
 */
require_once '../../sesion.php';
verificarAutenticacion();

$sede     = $_SESSION['sede'] ?? '';
$sede_san = preg_replace('/[^A-Za-z0-9_-]/', '', $sede);

$id   = trim($_GET['id']   ?? '');
$file = trim($_GET['file'] ?? '');

$base_dir  = realpath(__DIR__ . '/../../../archivos/generados/inspeccion_insumos/' . $sede_san);
$real_file = realpath($file);

if (!$id || !$file || !$real_file || !$base_dir || strpos($real_file, $base_dir) !== 0) {
    die('<p style="color:red;font-family:monospace;padding:40px;">Registro no válido o no pertenece a su sede.</p>');
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

function evalSelect($name, $selected) {
    $selected = (string)$selected;
    $opts = ['' => '—', '1' => 'CUMPLE (1)', '0' => 'NO CUMPLE (0)'];
    $html = '<select class="form-control" name="' . htmlspecialchars($name) . '">';
    foreach ($opts as $val => $label) {
        $sel = ((string)$val === $selected) ? ' selected' : '';
        $html .= '<option value="' . $val . '"' . $sel . '>' . $label . '</option>';
    }
    $html .= '</select>';
    return $html;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Corregir Inspección de Insumos</title>
    <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700&family=Space+Mono:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --bg-color: #0B0E14; --panel-bg: #151A22; --accent: #00F0FF;
            --accent-glow: rgba(0, 240, 255, 0.4); --text-main: #E2E8F0; --text-muted: #94A3B8;
            --border-color: #1E293B; --input-bg: #0F172A; --danger: #FF3366; --success: #10B981;
            --r-md: 8px; --r-sm: 4px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Barlow', sans-serif; background: var(--bg-color); color: var(--text-main);
            padding: 40px 20px;
            background-image: linear-gradient(rgba(0,240,255,0.03) 1px, transparent 1px), linear-gradient(90deg, rgba(0,240,255,0.03) 1px, transparent 1px);
            background-size: 30px 30px;
        }
        .container { max-width: 1100px; margin: 0 auto; }
        .header-box {
            background: var(--panel-bg); border: 1px solid var(--border-color); border-left: 4px solid var(--accent);
            padding: 25px; border-radius: var(--r-md); margin-bottom: 25px;
            display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;
        }
        .main-title { font-size: 20px; font-weight: 700; color: #fff; text-transform: uppercase; }
        .sub-title { color: var(--text-muted); font-size: 13px; font-family: 'Space Mono', monospace; margin-top: 4px; }
        .btn-back {
            background: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-main);
            padding: 9px 18px; border-radius: var(--r-sm); font-family: 'Space Mono', monospace;
            text-decoration: none; font-size: 12px; transition: all 0.3s;
        }
        .btn-back:hover { border-color: var(--accent); color: var(--accent); }

        .section-card { background: var(--panel-bg); border: 1px solid var(--border-color); border-radius: var(--r-md); padding: 20px; margin-bottom: 20px; }
        .section-title { color: var(--accent); font-size: 14px; font-weight: 700; text-transform: uppercase; margin-bottom: 16px; letter-spacing: 0.5px; }

        .item-card { background: var(--panel-bg); border: 1px solid var(--border-color); border-radius: var(--r-md); padding: 20px; margin-bottom: 16px; }
        .item-num { font-family: 'Space Mono', monospace; color: var(--accent); font-size: 13px; font-weight: 700; margin-bottom: 14px; }
        .fields-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; }
        .field.full { grid-column: 1 / -1; }
        .field label { display: block; font-size: 11px; color: var(--text-muted); margin-bottom: 6px; font-family: 'Space Mono', monospace; text-transform: uppercase; letter-spacing: 0.5px; }
        .field input, .field select {
            width: 100%; background: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-main);
            padding: 10px 12px; border-radius: var(--r-sm); font-family: 'Barlow', sans-serif; font-size: 13px;
        }
        .field input:focus, .field select:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 3px rgba(0,240,255,0.1); }

        .form-actions { display: flex; gap: 10px; margin-top: 10px; }
        .btn-submit {
            background: var(--accent); color: var(--bg-color); border: none; padding: 14px 26px;
            border-radius: var(--r-sm); font-family: 'Space Mono', monospace; font-size: 13px; font-weight: 700;
            cursor: pointer; text-transform: uppercase; letter-spacing: 1px; box-shadow: 0 0 15px var(--accent-glow);
        }
        .btn-submit:hover { background: #fff; }
    </style>
</head>
<body>
<div class="container">
    <div class="header-box">
        <div>
            <div class="main-title">Corregir Inspección de Insumos</div>
            <div class="sub-title">📅 <?= htmlspecialchars($d['fecha_inspeccion'] ?? '') ?> · Sede: <?= htmlspecialchars($sede) ?></div>
        </div>
        <a href="rev_inspeccion_insumos.php" class="btn-back">← Volver a Revisión</a>
    </div>

    <form action="guardar_edicion_inspeccion_insumos.php" method="post">
        <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
        <input type="hidden" name="file" value="<?= htmlspecialchars($file) ?>">

        <div class="section-card">
            <div class="section-title">Datos Generales</div>
            <div class="fields-grid">
                <div class="field">
                    <label>Fecha Inspección</label>
                    <input type="date" name="fecha_inspeccion" value="<?= htmlspecialchars($d['fecha_inspeccion'] ?? '') ?>">
                </div>
                <div class="field">
                    <label>Hora Inspección</label>
                    <input type="time" name="hora_inspeccion" value="<?= htmlspecialchars($d['hora_inspeccion'] ?? '') ?>">
                </div>
                <div class="field">
                    <label>Planta</label>
                    <input type="text" name="planta" value="<?= htmlspecialchars($d['planta'] ?? '') ?>">
                </div>
                <div class="field">
                    <label>Inspeccionado Por</label>
                    <input type="text" name="inspeccionado_por" value="<?= htmlspecialchars($d['inspeccionado_por'] ?? '') ?>">
                </div>
                <div class="field">
                    <label>Verificado Por</label>
                    <input type="text" name="verificado_por" value="<?= htmlspecialchars($d['verificado_por'] ?? '') ?>">
                </div>
            </div>
        </div>

        <?php if (empty($insumos)): ?>
            <div class="section-card">Sin materias primas registradas.</div>
        <?php else: foreach ($insumos as $idx => $it): ?>
            <div class="item-card">
                <div class="item-num">MATERIA PRIMA #<?= $idx + 1 ?></div>
                <div class="fields-grid">
                    <div class="field">
                        <label>Materia Prima</label>
                        <input type="text" name="items[<?= $idx ?>][materia_prima]" value="<?= htmlspecialchars($it['materia_prima'] ?? '') ?>">
                    </div>
                    <div class="field">
                        <label>Lote Interno</label>
                        <input type="text" name="items[<?= $idx ?>][lote_interno]" value="<?= htmlspecialchars($it['lote_interno'] ?? '') ?>">
                    </div>
                    <div class="field">
                        <label>Lote Proveedor</label>
                        <input type="text" name="items[<?= $idx ?>][lote_proveedor]" value="<?= htmlspecialchars($it['lote_proveedor'] ?? '') ?>">
                    </div>
                    <div class="field">
                        <label>Fecha Vencimiento</label>
                        <input type="date" name="items[<?= $idx ?>][fecha_vencimiento]" value="<?= htmlspecialchars($it['fecha_vencimiento'] ?? '') ?>">
                    </div>
                    <div class="field">
                        <label>Proveedor</label>
                        <input type="text" name="items[<?= $idx ?>][proveedor]" value="<?= htmlspecialchars($it['proveedor'] ?? '') ?>">
                    </div>
                    <div class="field">
                        <label>¿Producto vigente?</label>
                        <?= evalSelect("items[$idx][eval_vigente]", $it['eval_vigente'] ?? '') ?>
                    </div>
                    <div class="field">
                        <label>¿Producto etiquetado?</label>
                        <?= evalSelect("items[$idx][eval_etiquetado]", $it['eval_etiquetado'] ?? '') ?>
                    </div>
                    <div class="field">
                        <label>¿Libre de plagas?</label>
                        <?= evalSelect("items[$idx][eval_plagas]", $it['eval_plagas'] ?? '') ?>
                    </div>
                    <div class="field">
                        <label>¿Envase en buen estado?</label>
                        <?= evalSelect("items[$idx][eval_envase]", $it['eval_envase'] ?? '') ?>
                    </div>
                    <div class="field">
                        <label>¿Lote corresponde al SAP?</label>
                        <?= evalSelect("items[$idx][eval_sap]", $it['eval_sap'] ?? '') ?>
                    </div>
                    <div class="field full">
                        <label>Observaciones</label>
                        <input type="text" name="items[<?= $idx ?>][observaciones]" value="<?= htmlspecialchars($it['observaciones'] ?? '') ?>">
                    </div>
                </div>
            </div>
        <?php endforeach; endif; ?>

        <div class="form-actions">
            <button type="submit" class="btn-submit">💾 Guardar Correcciones</button>
        </div>
    </form>
</div>
</body>
</html>
