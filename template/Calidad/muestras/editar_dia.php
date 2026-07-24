<?php
/**
 * editar_dia.php
 * ---------------------
 * Reemplaza al antiguo muestras_html.php (editor de grilla Excel cruda vía
 * SheetJS). Aquí se corrigen los campos base de cada ítem (hora, producto,
 * lote, fecha/hora de muestreo, responsable, cantidad) con un formulario
 * real, no una grilla libre. Es una pantalla separada de "Disposición".
 */
require_once '../../sesion.php';
verificarAutenticacion();

$sede = $_SESSION['sede'];
$sede_san = preg_replace('/[^A-Za-z0-9_-]/', '', $sede);

$periodo = $_GET['periodo'] ?? '';
$fecha   = $_GET['fecha'] ?? '';

if (!preg_match('/^\d{4}-\d{2}$/', $periodo) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
    die('Parámetros inválidos.');
}

$archivo_json = "/var/www/fmt/archivos/generados/Calidad/muestras/" . $sede_san . "/" . $periodo . ".json";

if (!file_exists($archivo_json)) {
    die('No se encontró información para ese período.');
}

$registros = json_decode(file_get_contents($archivo_json), true) ?: [];
$items = [];
foreach ($registros as $r) {
    if (($r['datos']['fecha_registro'] ?? '') === $fecha) {
        $items[] = ['id' => $r['id'], 'datos' => $r['datos']];
    }
}
usort($items, fn($a, $b) => ($a['datos']['item'] ?? 0) <=> ($b['datos']['item'] ?? 0));

if (empty($items)) {
    die('No hay ítems registrados para el ' . htmlspecialchars($fecha) . '.');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Corregir Muestras - <?= htmlspecialchars($fecha) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700&family=Space+Mono:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
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

        .item-card { background: var(--panel-bg); border: 1px solid var(--border-color); border-radius: var(--r-md); padding: 20px; margin-bottom: 16px; }
        .item-num { font-family: 'Space Mono', monospace; color: var(--accent); font-size: 13px; font-weight: 700; margin-bottom: 14px; }
        .fields-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; }
        .field label { display: block; font-size: 11px; color: var(--text-muted); margin-bottom: 6px; font-family: 'Space Mono', monospace; text-transform: uppercase; letter-spacing: 0.5px; }
        .field input {
            width: 100%; background: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-main);
            padding: 10px 12px; border-radius: var(--r-sm); font-family: 'Barlow', sans-serif; font-size: 13px;
        }
        .field input:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 3px rgba(0,240,255,0.1); }

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
            <div class="main-title">Corregir Muestras</div>
            <div class="sub-title">📅 <?= htmlspecialchars($fecha) ?> · Sede: <?= htmlspecialchars($sede) ?></div>
        </div>
        <a href="revision_muestras.php" class="btn-back">← Volver a Revisión</a>
    </div>

    <form action="guardar_edicion.php" method="post">
        <input type="hidden" name="periodo" value="<?= htmlspecialchars($periodo) ?>">
        <input type="hidden" name="fecha" value="<?= htmlspecialchars($fecha) ?>">

        <?php foreach ($items as $item): $d = $item['datos']; ?>
            <div class="item-card">
                <div class="item-num">ÍTEM #<?= htmlspecialchars($d['item'] ?? '?') ?></div>
                <div class="fields-grid">
                    <div class="field">
                        <label>Hora de Registro</label>
                        <input type="time" name="items[<?= htmlspecialchars($item['id']) ?>][hora]" value="<?= htmlspecialchars($d['hora'] ?? '') ?>">
                    </div>
                    <div class="field">
                        <label>Producto</label>
                        <input type="text" name="items[<?= htmlspecialchars($item['id']) ?>][producto]" value="<?= htmlspecialchars($d['producto'] ?? '') ?>">
                    </div>
                    <div class="field">
                        <label>Número de Lote</label>
                        <input type="text" name="items[<?= htmlspecialchars($item['id']) ?>][lote]" value="<?= htmlspecialchars($d['lote'] ?? '') ?>">
                    </div>
                    <div class="field">
                        <label>Fecha de Muestreo</label>
                        <input type="date" name="items[<?= htmlspecialchars($item['id']) ?>][fecha_muestreo]" value="<?= htmlspecialchars($d['fecha_muestreo'] ?? '') ?>">
                    </div>
                    <div class="field">
                        <label>Hora de Muestreo</label>
                        <input type="time" name="items[<?= htmlspecialchars($item['id']) ?>][hora_muestreo]" value="<?= htmlspecialchars($d['hora_muestreo'] ?? '') ?>">
                    </div>
                    <div class="field">
                        <label>Responsable de la Toma</label>
                        <input type="text" name="items[<?= htmlspecialchars($item['id']) ?>][responsable_muestra]" value="<?= htmlspecialchars($d['responsable_muestra'] ?? '') ?>">
                    </div>
                    <div class="field">
                        <label>Cantidad (g)</label>
                        <input type="text" name="items[<?= htmlspecialchars($item['id']) ?>][cantidad]" value="<?= htmlspecialchars($d['cantidad'] ?? '') ?>">
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="form-actions">
            <button type="submit" class="btn-submit">💾 Guardar Correcciones</button>
        </div>
    </form>
</div>
</body>
</html>
