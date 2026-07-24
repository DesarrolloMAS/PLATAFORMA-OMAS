<?php
require_once '../../sesion.php';
verificarAutenticacion();

// Recibir el período (JSON mensual) y el día a mostrar
$periodo = isset($_GET['periodo']) ? $_GET['periodo'] : '';
$fecha   = isset($_GET['fecha'])   ? $_GET['fecha']   : '';

if (empty($periodo) || empty($fecha)) {
    echo "<script>alert('No se especificó un día. Redirigiendo...'); window.location.href='revision_muestras.php';</script>";
    exit;
}

$sede = $_SESSION['sede'];
$sede_san = preg_replace('/[^A-Za-z0-9_-]/', '', $sede);
$carpeta = '/var/www/fmt/archivos/generados/Calidad/muestras/' . $sede_san . '/';

$archivoJson = $carpeta . basename($periodo) . '.json';

if (!file_exists($archivoJson)) {
    echo "<script>alert('No hay información para ese período. Redirigiendo...'); window.location.href='revision_muestras.php';</script>";
    exit;
}

// Cargar el JSON y filtrar los ítems del día solicitado
$registros = json_decode(file_get_contents($archivoJson), true) ?: [];

$items = [];
foreach ($registros as $r) {
    $d = $r['datos'] ?? [];
    if (($d['fecha_registro'] ?? '') !== $fecha) continue;

    $items[] = [
        'id'          => $r['id'],
        'item'        => $d['item'] ?? '?',
        'hora'        => $d['hora'] ?? '',
        'producto'    => $d['producto'] ?? '',
        'lote'        => $d['lote'] ?? '',
        'fecha_m'     => $d['fecha_muestreo'] ?? '',
        'hora_m'      => $d['hora_muestreo'] ?? '',
        'responsable' => $d['responsable_muestra'] ?? '',
        'cantidad'    => $d['cantidad'] ?? '',
        // Verificar si ya tiene disposición
        'tiene_disp'  => !empty($d['disp_fecha']),
    ];
}
usort($items, fn($a, $b) => $a['item'] <=> $b['item']);
$totalItems = count($items);

// Variable usada por el encabezado, en el mismo lugar donde antes se mostraba el nombre del archivo
$archivo = $fecha;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MUESTRAS - Disposición</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --mint: #d0e9e7;
            --steel: #749abb;
            --deep: #2c4a6e;
            --glass-border: rgba(116, 154, 187, 0.2);
            --text-primary: #e8f4f3;
            --text-muted: rgba(116, 154, 187, 0.7);
            --input-bg: rgba(116, 154, 187, 0.07);
            --input-focus: rgba(208, 233, 231, 0.1);
            --accent-glow: rgba(208, 233, 231, 0.15);
            --card-bg: rgba(15, 30, 46, 0.75);
            --green-ok: #4caf8a;
            --orange-pending: #e07b3a;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: #0f1e2e;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 40px 20px 60px;
            position: relative;
            overflow-x: hidden;
        }

        /* Layered background */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse 80% 50% at 15% 20%, rgba(116, 154, 187, 0.12) 0%, transparent 60%),
                radial-gradient(ellipse 60% 40% at 85% 70%, rgba(208, 233, 231, 0.07) 0%, transparent 55%),
                radial-gradient(ellipse 40% 60% at 50% 100%, rgba(44, 74, 110, 0.3) 0%, transparent 60%);
            pointer-events: none;
            z-index: 0;
        }

        body::after {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                repeating-linear-gradient(0deg, transparent, transparent 39px, rgba(116,154,187,0.03) 39px, rgba(116,154,187,0.03) 40px),
                repeating-linear-gradient(90deg, transparent, transparent 39px, rgba(116,154,187,0.03) 39px, rgba(116,154,187,0.03) 40px);
            pointer-events: none;
            z-index: 0;
        }

        /* Layout container */
        .page-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 860px;
        }

        /* ── HEADER ── */
        .page-header {
            animation: slideDown 0.7s cubic-bezier(0.16, 1, 0.3, 1) both;
            margin-bottom: 8px;
        }

        .page-header::before {
            content: 'SISTEMA DE CONTROL — LABORATORIO';
            display: block;
            font-family: 'DM Mono', monospace;
            font-size: 10px;
            letter-spacing: 0.22em;
            color: var(--steel);
            margin-bottom: 10px;
            opacity: 0.8;
        }

        .page-header h1 {
            font-size: clamp(15px, 2vw, 20px);
            font-weight: 600;
            letter-spacing: 0.12em;
            color: var(--text-primary);
        }

        .page-header h2 {
            font-size: 12px;
            font-weight: 500;
            letter-spacing: 0.18em;
            color: var(--mint);
            margin-top: 4px;
            font-family: 'DM Mono', monospace;
            text-transform: uppercase;
        }

        .page-header p {
            margin-top: 6px;
            font-family: 'DM Mono', monospace;
            font-size: 10px;
            color: rgba(116, 154, 187, 0.5);
            letter-spacing: 0.06em;
        }

        .header-line {
            width: 100%;
            height: 1px;
            background: linear-gradient(90deg, var(--steel) 0%, var(--mint) 40%, transparent 100%);
            margin: 16px 0 28px;
            transform-origin: left;
            animation: expandLine 1s cubic-bezier(0.16, 1, 0.3, 1) 0.3s both;
        }

        /* ── STATS BAR ── */
        .stats {
            background: rgba(15, 30, 46, 0.7);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 10px;
            padding: 12px 24px;
            margin-bottom: 20px;
            display: flex;
            gap: 28px;
            align-items: center;
            font-family: 'DM Mono', monospace;
            font-size: 11px;
            animation: fadeUp 0.6s cubic-bezier(0.16,1,0.3,1) 0.2s both;
        }

        .stats .stat-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--text-muted);
            letter-spacing: 0.05em;
        }

        .stats .stat-item strong {
            font-size: 16px;
            font-family: 'DM Sans', sans-serif;
            font-weight: 600;
            color: var(--text-primary);
        }

        .stats .stat-item.ok strong   { color: var(--green-ok); }
        .stats .stat-item.pend strong { color: var(--orange-pending); }

        .stat-divider {
            width: 1px;
            height: 20px;
            background: rgba(116, 154, 187, 0.2);
        }

        /* ── ITEM CARDS ── */
        .items-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
            animation: fadeUp 0.7s cubic-bezier(0.16,1,0.3,1) 0.3s both;
        }

        .item-card {
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-left: 3px solid var(--steel);
            border-radius: 10px;
            overflow: hidden;
            transition: border-color 0.25s ease, box-shadow 0.25s ease;
        }

        .item-card.ya-tiene {
            border-left-color: var(--green-ok);
        }

        .item-card.selected {
            border-left-color: var(--mint);
            box-shadow: 0 0 0 1px rgba(208, 233, 231, 0.12), 0 4px 20px rgba(0,0,0,0.25);
        }

        /* Header row of each card */
        .item-header {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 20px;
            cursor: pointer;
            user-select: none;
            transition: background 0.2s ease;
        }

        .item-header:hover {
            background: rgba(116, 154, 187, 0.06);
        }

        /* Custom checkbox */
        .item-checkbox {
            appearance: none;
            -webkit-appearance: none;
            width: 18px;
            height: 18px;
            border: 1.5px solid rgba(116, 154, 187, 0.4);
            border-radius: 4px;
            background: var(--input-bg);
            cursor: pointer;
            flex-shrink: 0;
            position: relative;
            transition: border-color 0.2s ease, background 0.2s ease;
        }

        .item-checkbox:checked {
            background: var(--steel);
            border-color: var(--mint);
        }

        .item-checkbox:checked::after {
            content: '';
            position: absolute;
            left: 4px; top: 1px;
            width: 6px; height: 10px;
            border: 2px solid #0f1e2e;
            border-top: none; border-left: none;
            transform: rotate(45deg);
        }

        .item-num {
            font-weight: 700;
            font-size: 13px;
            color: var(--mint);
            font-family: 'DM Mono', monospace;
            min-width: 32px;
            letter-spacing: 0.05em;
        }

        .item-info {
            flex: 1;
            display: flex;
            flex-wrap: wrap;
            gap: 6px 18px;
        }

        .item-info span {
            font-size: 12px;
            color: rgba(208, 233, 231, 0.75);
        }

        .item-info .label {
            font-family: 'DM Mono', monospace;
            font-size: 10px;
            color: var(--text-muted);
            letter-spacing: 0.06em;
            display: block;
            margin-bottom: 1px;
        }

        .item-info .value {
            color: var(--text-primary);
            font-size: 12px;
            font-weight: 500;
        }

        .badge {
            font-family: 'DM Mono', monospace;
            font-size: 9px;
            letter-spacing: 0.1em;
            padding: 3px 10px;
            border-radius: 20px;
            font-weight: 600;
            flex-shrink: 0;
        }

        .badge-ok {
            background: rgba(76, 175, 138, 0.15);
            color: var(--green-ok);
            border: 1px solid rgba(76, 175, 138, 0.3);
        }

        .badge-pending {
            background: rgba(224, 123, 58, 0.12);
            color: var(--orange-pending);
            border: 1px solid rgba(224, 123, 58, 0.3);
        }

        /* ── INNER FORM ── */
        .item-form {
            display: none;
            padding: 18px 20px 20px 54px;
            border-top: 1px solid rgba(116, 154, 187, 0.1);
            background: rgba(116, 154, 187, 0.03);
        }

        .item-form.visible { display: block; }

        .section-label-inner {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 16px;
        }

        .section-label-inner::before {
            content: '';
            width: 3px;
            height: 14px;
            background: linear-gradient(180deg, var(--mint), var(--steel));
            border-radius: 2px;
            flex-shrink: 0;
        }

        .section-label-inner span {
            font-family: 'DM Mono', monospace;
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 0.18em;
            color: var(--mint);
            text-transform: uppercase;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 14px;
        }

        .form-grid .field-label {
            font-family: 'DM Mono', monospace;
            font-size: 10px;
            font-weight: 500;
            letter-spacing: 0.08em;
            color: var(--text-muted);
            text-transform: uppercase;
            display: block;
            margin-bottom: 6px;
            transition: color 0.2s;
        }

        .form-grid .field-wrap:focus-within .field-label {
            color: var(--mint);
        }

        .form-grid input[type="date"],
        .form-grid input[type="text"] {
            width: 100%;
            padding: 9px 12px;
            background: var(--input-bg);
            border: 1px solid rgba(116, 154, 187, 0.15);
            border-radius: 7px;
            color: var(--text-primary);
            font-family: 'DM Sans', sans-serif;
            font-size: 13px;
            outline: none;
            transition: background 0.2s, border-color 0.2s, box-shadow 0.2s;
        }

        .form-grid input[type="date"]:hover,
        .form-grid input[type="text"]:hover {
            border-color: rgba(116, 154, 187, 0.35);
            background: rgba(116, 154, 187, 0.1);
        }

        .form-grid input[type="date"]:focus,
        .form-grid input[type="text"]:focus {
            background: var(--input-focus);
            border-color: var(--mint);
            box-shadow: 0 0 0 3px var(--accent-glow);
        }

        .form-grid input[type="date"]::-webkit-calendar-picker-indicator {
            filter: invert(0.6) sepia(1) saturate(0.5) hue-rotate(170deg);
            opacity: 0.6;
            cursor: pointer;
        }

        /* Checkbox group (mejorante) */
        .checkbox-group {
            display: flex;
            gap: 6px;
            align-items: center;
            padding-top: 2px;
            flex-wrap: wrap;
        }

        .checkbox-group label {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            color: rgba(208, 233, 231, 0.7);
            cursor: pointer;
            padding: 5px 10px;
            border: 1px solid rgba(116, 154, 187, 0.15);
            border-radius: 6px;
            background: var(--input-bg);
            transition: background 0.2s, border-color 0.2s, color 0.2s;
            font-family: 'DM Mono', monospace;
            letter-spacing: 0.04em;
        }

        .checkbox-group label:hover {
            background: rgba(116, 154, 187, 0.12);
            border-color: rgba(116, 154, 187, 0.3);
        }

        .checkbox-group input[type="checkbox"] {
            appearance: none;
            -webkit-appearance: none;
            width: 14px; height: 14px;
            border: 1.5px solid rgba(116, 154, 187, 0.4);
            border-radius: 3px;
            background: transparent;
            cursor: pointer;
            position: relative;
            flex-shrink: 0;
            transition: background 0.2s, border-color 0.2s;
        }

        .checkbox-group input[type="checkbox"]:checked {
            background: var(--steel);
            border-color: var(--mint);
        }

        .checkbox-group input[type="checkbox"]:checked::after {
            content: '';
            position: absolute;
            left: 3px; top: 0px;
            width: 5px; height: 8px;
            border: 2px solid #0f1e2e;
            border-top: none; border-left: none;
            transform: rotate(45deg);
        }

        .checkbox-group label:has(input:checked) {
            background: rgba(116, 154, 187, 0.18);
            border-color: rgba(208, 233, 231, 0.3);
            color: var(--mint);
        }

        /* ── FORM ACTIONS ── */
        .form-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 28px;
            animation: fadeUp 0.6s cubic-bezier(0.16,1,0.3,1) 0.45s both;
        }

        .btn-submit {
            padding: 12px 32px;
            background: linear-gradient(135deg, var(--steel) 0%, #5a7fa0 50%, var(--mint) 100%);
            background-size: 200% 200%;
            background-position: 0% 50%;
            border: none;
            border-radius: 8px;
            color: #0f1e2e;
            font-family: 'DM Sans', sans-serif;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            cursor: pointer;
            transition: background-position 0.4s ease, transform 0.15s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-submit::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, transparent 30%, rgba(255,255,255,0.15) 50%, transparent 70%);
            transform: translateX(-100%);
            transition: transform 0.5s ease;
        }

        .btn-submit:hover {
            background-position: 100% 50%;
            transform: translateY(-1px);
            box-shadow: 0 6px 24px rgba(116, 154, 187, 0.35), 0 2px 8px rgba(0,0,0,0.2);
        }

        .btn-submit:hover::after { transform: translateX(100%); }
        .btn-submit:active { transform: translateY(0); }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 12px 22px;
            background: rgba(116, 154, 187, 0.1);
            border: 1px solid rgba(116, 154, 187, 0.2);
            border-radius: 8px;
            color: var(--text-muted);
            text-decoration: none;
            font-family: 'DM Mono', monospace;
            font-size: 11px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            transition: background 0.2s, color 0.2s, border-color 0.2s;
        }

        .btn-back:hover {
            background: rgba(116, 154, 187, 0.18);
            color: var(--mint);
            border-color: rgba(208, 233, 231, 0.25);
        }

        /* Empty state */
        .empty-msg {
            text-align: center;
            padding: 60px 20px;
            font-family: 'DM Mono', monospace;
            font-size: 13px;
            color: var(--text-muted);
            letter-spacing: 0.08em;
            background: var(--card-bg);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            backdrop-filter: blur(20px);
        }

        /* Animations */
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-16px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes expandLine {
            from { transform: scaleX(0); }
            to   { transform: scaleX(1); }
        }

        @media (max-width: 600px) {
            .stats { flex-wrap: wrap; gap: 12px; }
            .item-header { flex-wrap: wrap; }
            .item-form { padding-left: 20px; }
            .form-actions { flex-direction: column; align-items: flex-start; }
        }
    </style>
</head>
<body>
<div class="page-container">

    <header class="page-header">
        <h1>CONTROL INGRESO Y SALIDA DE MUESTRAS</h1>
        <h2>Disposición de Muestras</h2>
        <p>Fecha: <?php echo htmlspecialchars($archivo); ?></p>
        
    </header>

    <div class="header-line"></div>

    <div class="stats">
        <div class="stat-item">
            <strong><?php echo $totalItems; ?></strong>
            <span>Ítem(s) registrados</span>
        </div>
        <div class="stat-divider"></div>
        <div class="stat-item ok">
            <strong><?php echo count(array_filter($items, fn($i) => $i['tiene_disp'])); ?></strong>
            <span>Con disposición</span>
        </div>
        <div class="stat-divider"></div>
        <div class="stat-item pend">
            <strong><?php echo count(array_filter($items, fn($i) => !$i['tiene_disp'])); ?></strong>
            <span>Pendientes</span>
        </div>
    </div>

    <?php if ($totalItems === 0): ?>
        <div class="empty-msg">NO HAY ÍTEMS REGISTRADOS EN ESTE ARCHIVO</div>
    <?php else: ?>
        <form action="procesar_disposicion.php" method="post" id="formDisposicion">
            <button type="button" id="btn-eliminar-seleccionados" style="background:#e07b3a;color:#fff;border:none;padding:12px 32px;border-radius:8px;cursor:pointer;">
    🗑️ Eliminar seleccionados
</button>
            <input type="hidden" name="periodo" value="<?php echo htmlspecialchars($periodo); ?>">
            <input type="hidden" name="fecha" value="<?php echo htmlspecialchars($fecha); ?>">

            <div class="items-list">
            <?php foreach ($items as $item): ?>
                <div class="item-card <?php echo $item['tiene_disp'] ? 'ya-tiene' : ''; ?>" id="card_<?php echo htmlspecialchars($item['id']); ?>">

                    <div class="item-header" onclick="toggleItem('<?php echo htmlspecialchars($item['id']); ?>')">
                        <input type="checkbox"
                               class="item-checkbox"
                               name="items_seleccionados[]"
                               value="<?php echo htmlspecialchars($item['id']); ?>"
                               id="check_<?php echo htmlspecialchars($item['id']); ?>"
                               onclick="event.stopPropagation(); toggleItem('<?php echo htmlspecialchars($item['id']); ?>')">

                        <span class="item-num">#<?php echo $item['item']; ?></span>

                        <div class="item-info">
                            <div>
                                <span class="label">Producto</span>
                                <span class="value"><?php echo htmlspecialchars($item['producto']); ?></span>
                            </div>
                            <div>
                                <span class="label">Lote</span>
                                <span class="value"><?php echo htmlspecialchars($item['lote']); ?></span>
                            </div>
                            <div>
                                <span class="label">Cantidad</span>
                                <span class="value"><?php echo htmlspecialchars($item['cantidad']); ?></span>
                            </div>
                            <div>
                                <span class="label">Hora</span>
                                <span class="value"><?php echo htmlspecialchars($item['hora']); ?></span>
                            </div>
                        </div>
                        
                        <?php if ($item['tiene_disp']): ?>
                            <span class="badge badge-ok">✓ DISPOSICIÓN</span>
                        <?php else: ?>
                            <span class="badge badge-pending">PENDIENTE</span>
                        <?php endif; ?>
                    </div>

                    <div class="item-form" id="form_<?php echo htmlspecialchars($item['id']); ?>">
                        <div class="section-label-inner">
                            <span>Datos de Disposición</span>
                        </div>
                        <div class="form-grid">
                            <div class="field-wrap">
                                <label class="field-label" for="fecha_<?php echo htmlspecialchars($item['id']); ?>">Fecha Disposición</label>
                                <input type="date" name="disp[<?php echo htmlspecialchars($item['id']); ?>][fecha]" id="fecha_<?php echo htmlspecialchars($item['id']); ?>">
                            </div>
                            <div class="field-wrap">
                                <label class="field-label">Con / Sin Mejorante / Martillo</label>
                                <div class="checkbox-group">
                                    <label>
                                        <input type="checkbox" name="disp[<?php echo htmlspecialchars($item['id']); ?>][mejorante][]" value="CON MEJORANTE">
                                        CON
                                    </label>
                                    <label>
                                        <input type="checkbox" name="disp[<?php echo htmlspecialchars($item['id']); ?>][mejorante][]" value="SIN MEJORANTE">
                                        SIN
                                    </label>
                                    <label>
                                        <input type="checkbox" name="disp[<?php echo htmlspecialchars($item['id']); ?>][mejorante][]" value="MARTILLO">
                                        MARTILLO
                                    </label>
                                </div>
                            </div>
                            <div class="field-wrap">
                                <label class="field-label" for="cant_<?php echo htmlspecialchars($item['id']); ?>">Cantidad</label>
                                <input type="text" name="disp[<?php echo htmlspecialchars($item['id']); ?>][cantidad]" id="cant_<?php echo htmlspecialchars($item['id']); ?>">
                            </div>
                            <div class="field-wrap">
                                <label class="field-label" for="resp_<?php echo htmlspecialchars($item['id']); ?>">Responsable</label>
                                <input type="text" name="disp[<?php echo htmlspecialchars($item['id']); ?>][responsable]" id="resp_<?php echo htmlspecialchars($item['id']); ?>">
                            </div>
                        </div>
                    </div>

                </div>
            <?php endforeach; ?>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit">Guardar Disposiciones</button>
                <a href="revision_muestras.php" class="btn-back">← Volver</a>
            </div>

        </form>
        
    <?php endif; ?>

</div>
<script>
function toggleItem(fila) {
    const checkbox = document.getElementById('check_' + fila);
    const formDiv  = document.getElementById('form_' + fila);
    const card     = document.getElementById('card_' + fila);

    // Si se llamó desde el header (no el checkbox), toggle manual
    if (event && event.target !== checkbox) {
        checkbox.checked = !checkbox.checked;
    }

    if (checkbox.checked) {
        formDiv.classList.add('visible');
        card.classList.add('selected');
    } else {
        formDiv.classList.remove('visible');
        card.classList.remove('selected');
    }
}

// Validar que al menos 1 ítem esté seleccionado
document.getElementById('formDisposicion')?.addEventListener('submit', function(e) {
    const checked = document.querySelectorAll('.item-checkbox:checked');
    if (checked.length === 0) {
        e.preventDefault();
        alert('Selecciona al menos un ítem para registrar su disposición.');
    }
});
document.getElementById('btn-eliminar-seleccionados')?.addEventListener('click', function() {
    const checked = document.querySelectorAll('.item-checkbox:checked');
    if (checked.length === 0) {
        alert('Selecciona al menos un ítem para eliminar.');
        return;
    }
    if (!confirm(`¿Seguro que deseas eliminar ${checked.length} ítem(s)?`)) return;

    // Crear formulario oculto para enviar por POST
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'eliminar_items.php';

    // Período y fecha del día actual
    const inputPeriodo = document.createElement('input');
    inputPeriodo.type = 'hidden';
    inputPeriodo.name = 'periodo';
    inputPeriodo.value = "<?php echo htmlspecialchars($periodo); ?>";
    form.appendChild(inputPeriodo);

    const inputFecha = document.createElement('input');
    inputFecha.type = 'hidden';
    inputFecha.name = 'fecha';
    inputFecha.value = "<?php echo htmlspecialchars($fecha); ?>";
    form.appendChild(inputFecha);

    // Ítems seleccionados
    checked.forEach(cb => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'items_eliminar[]';
        input.value = cb.value;
        form.appendChild(input);
    });

    document.body.appendChild(form);
    form.submit();
});
</script>
</body>
</html>