<?php
require '../sesion.php';
verificarAutenticacion();

$moduloId = $_GET['modulo'] ?? '';
$sede = $_SESSION['sede'];

// Zonas válidas para el visor multi-sede
$ZONAS_VALIDAS = ['ZC', 'ZS', 'ZB'];
$ZONAS_LABEL   = ['ZC' => 'Zona Centro', 'ZS' => 'Zona Sur', 'ZB' => 'Buga'];
$ZONA_COLOR    = ['ZC' => '#00f2ff', 'ZS' => '#ffb45c', 'ZB' => '#a855f7'];

$sedesSeleccionadas = array_values(array_intersect(
    array_filter(array_map('trim', explode(',', $_GET['sedes'] ?? ''))),
    $ZONAS_VALIDAS
));
if (empty($sedesSeleccionadas)) {
    $sedesSeleccionadas = [$sede];
}
$multiSede = count($sedesSeleccionadas) > 1;

// Mapa Maestro de Rutas (Unificado). Las rutas con {SEDE} se escanean una vez
// por cada sede seleccionada (sede_scoped) y se fusionan con diferenciador visual.
$modulosMap = [
    // PRODUCCION
    'molienda_v2' => ['nombre' => 'Molienda V2', 'ruta' => '../../archivos/generados/molienda/{SEDE}/', 'tipo' => 'json_daily', 'sede_scoped' => true],
    'envasado' => ['nombre' => 'Envasado', 'ruta' => ($sede === 'ZS' ? '../../archivos/generados/envasado_zs/' : '../../archivos/generados/envasado/'), 'tipo' => 'excel'],
    'empaque_v2' => ['nombre' => 'Empaque V2', 'ruta' => '../../archivos/generados/empaque_v2/{SEDE}/', 'tipo' => 'json', 'sede_scoped' => true],

    // ALMACÉN
    'bodegas_v2' => ['nombre' => 'Inspección de Bodegas V2', 'ruta' => '../../archivos/generados/bodegas_v2/{SEDE}/', 'tipo' => 'json', 'sede_scoped' => true],
    'premezclas' => ['nombre' => 'Premezclas', 'ruta' => '../../archivos/generados/premezclas/', 'tipo' => 'excel'],
    'reprocesos_zc' => ['nombre' => 'Reprocesos ZC', 'ruta' => '../../archivos/generados/reprocesos_zc/', 'tipo' => 'excel'],
    'reprocesos_zs' => ['nombre' => 'Reprocesos ZS', 'ruta' => '../../archivos/generados/reprocesos_zs/', 'tipo' => 'excel'],
    'purga' => ['nombre' => 'Purga de Proceso', 'ruta' => '../../archivos/generados/Purga De proceso/', 'tipo' => 'excel'],
    'control_familiar' => ['nombre' => 'Control Familiar', 'ruta' => '../../archivos/generados/control_familiar/', 'tipo' => 'excel'],
    'control_cantidad' => ['nombre' => 'Control Cantidad ZC', 'ruta' => '../../archivos/generados/control_cantidad/', 'tipo' => 'excel'],
    'control_cantidad_zs' => ['nombre' => 'Control Cantidad ZS', 'ruta' => '../../archivos/generados/control_cantidad_zs/', 'tipo' => 'excel'],
    'cantidad_bulto' => ['nombre' => 'Cantidad en Bulto V2', 'ruta' => '../../archivos/generados/cantidad_bulto/{SEDE}/', 'tipo' => 'json', 'sede_scoped' => true],

    // CALIDAD
    'liberaciones' => ['nombre' => 'Liberaciones ZC', 'ruta' => '../../archivos/generados/Calidad/liberaciones/', 'tipo' => 'excel'],
    'liberaciones_zs' => ['nombre' => 'Liberaciones ZS', 'ruta' => '../../archivos/generados/Calidad/liberaciones_zs/', 'tipo' => 'excel'],
    'muestras' => ['nombre' => 'Muestras ZC', 'ruta' => '../../archivos/generados/Calidad/muestras/', 'tipo' => 'excel'],
    'muestras_zs' => ['nombre' => 'Muestras ZS', 'ruta' => '../../archivos/generados/Calidad/muestras_zs/', 'tipo' => 'excel'],
    'pnc' => ['nombre' => 'PNC', 'ruta' => '../../archivos/generados/PNC/', 'tipo' => 'json'],
    'tara_seca' => ['nombre' => 'Tara Seca', 'ruta' => '../../archivos/generados/Calidad/tara_seca/', 'tipo' => 'excel'],

    // MANTENIMIENTO
    'mantenimiento_zc' => ['nombre' => 'O.T ZC', 'ruta' => '../../archivos/generados/excelS_M/', 'tipo' => 'excel'],
    'mantenimiento_zs' => ['nombre' => 'O.T ZS', 'ruta' => '../../archivos/generados/excelS_MZS/', 'tipo' => 'excel'],
    'mant_calidad_zc' => ['nombre' => 'Mant Calidad ZC', 'ruta' => '../../archivos/generados/excelC_M/', 'tipo' => 'excel'],
    'mant_calidad_zs' => ['nombre' => 'Mant Calidad ZS', 'ruta' => '../../archivos/generados/excelC_MZS/', 'tipo' => 'excel'],
    'lib_mantenimiento' => ['nombre' => 'Lib. Mant', 'ruta' => '../../archivos/generados/liberaciones_mant/', 'tipo' => 'json'],

    // VERIFICACIONES
    'ver_balanzas' => ['nombre' => 'Balanzas', 'ruta' => '../../archivos/generados/verificaciones/Balanzas/', 'tipo' => 'excel'],
    'ver_bascula' => ['nombre' => 'Báscula', 'ruta' => '../../archivos/generados/verificaciones/Bascula/', 'tipo' => 'excel'],
    'ver_camionera' => ['nombre' => 'Camionera', 'ruta' => '../../archivos/generados/verificaciones/camionera/', 'tipo' => 'excel'],
    'ver_equipos' => ['nombre' => 'Equipos', 'ruta' => '../../archivos/generados/verificaciones/equipos/', 'tipo' => 'excel'],
    'ver_flow' => ['nombre' => 'Flowbalancer', 'ruta' => '../../archivos/generados/verificaciones/Flowbalancer/', 'tipo' => 'excel'],
    'ver_iman' => ['nombre' => 'Imanes', 'ruta' => '../../archivos/generados/verificaciones/Iman/', 'tipo' => 'excel'],
    'ver_puntada' => ['nombre' => 'Puntada', 'ruta' => '../../archivos/generados/verificaciones/puntada/', 'tipo' => 'excel'],

    // MANTENIMIENTO (V2 - JSON consolidado)
    'maquinas_v2' => ['nombre' => 'Verificación de Máquinas V2', 'ruta' => '../../archivos/generados/maquinas_v2/', 'tipo' => 'maquinas_nested'],

    // HSEQ & OTROS
    'hseq' => ['nombre' => 'HSEQ', 'ruta' => '../../archivos/generados/HSEQ/investigacionesjson/', 'tipo' => 'json'],
    'termohigrometros' => ['nombre' => 'Termohigrómetros', 'ruta' => '../../archivos/generados/termohigrometros/{SEDE}/', 'tipo' => 'json_daily', 'sede_scoped' => true],
    'ins_hist' => ['nombre' => 'Insumos Histórico', 'ruta' => '../../archivos/generados/excel_INS/historico/', 'tipo' => 'excel'],
    'logs' => ['nombre' => 'Logs', 'ruta' => '../../archivos/generados/LOGS/', 'tipo' => 'text'],
];

if (!isset($modulosMap[$moduloId])) {
    die("Error: Módulo incorreto o no configurado.");
}

$mod = $modulosMap[$moduloId];
$sedeScoped = !empty($mod['sede_scoped']);

// Lista de (sede, dir) a escanear: varias si es sede_scoped, una sola si no.
$sedesAEscanear = $sedeScoped ? $sedesSeleccionadas : [$sede];
$dirsExistentes = 0;

$items = [];
foreach ($sedesAEscanear as $sedeItem) {
    $dirItem = $sedeScoped ? str_replace('{SEDE}', $sedeItem, $mod['ruta']) : $mod['ruta'];
    if (!is_dir($dirItem)) continue;
    $dirsExistentes++;

    if ($mod['tipo'] === 'json_daily') {
        $files = glob($dirItem . "*.json");
        foreach ($files as $f) {
            $content = json_decode(file_get_contents($f), true);
            if ($content) {
                foreach ($content as $reg) {
                    if (isset($reg['fecha'])) {
                        // Clave por sede+fecha: deduplica dentro de una misma sede
                        // sin pisar el registro de la otra sede al fusionar ambas.
                        $items[$sedeItem . '|' . $reg['fecha']] = [
                            'label' => 'Día: ' . $reg['fecha'],
                            'info' => 'Consolidado Diario (ID: ' . ($reg['id'] ?? '?') . ')',
                            'file' => basename($f),
                            'date' => $reg['fecha'],
                            'dir'  => $dirItem,
                            'sede' => $sedeItem,
                        ];
                    }
                }
            }
        }
    } elseif ($mod['tipo'] === 'maquinas_nested') {
        // Estructura de 3 niveles: tipo/ -> grupo/ -> codigo.json (array de registros históricos)
        foreach (scandir($dirItem) as $tipoM) {
            if ($tipoM === '.' || $tipoM === '..') continue;
            $tipoDir = $dirItem . $tipoM . '/';
            if (!is_dir($tipoDir)) continue;

            foreach (scandir($tipoDir) as $grupoM) {
                if ($grupoM === '.' || $grupoM === '..') continue;
                $grupoDir = $tipoDir . $grupoM . '/';
                if (!is_dir($grupoDir)) continue;

                foreach (glob($grupoDir . '*.json') as $f) {
                    $codigoM = pathinfo($f, PATHINFO_FILENAME);
                    $registros = json_decode(file_get_contents($f), true) ?: [];

                    foreach ($registros as $reg) {
                        $estadoBadge = ($reg['tipo_registro'] ?? '') === 'correccion' ? '✏️ Corrección'
                            : ((($reg['estado'] ?? '') === 'borrador') ? '📝 Borrador' : '✅ Verificado');

                        $items[$f . '|' . ($reg['id_registro'] ?? uniqid())] = [
                            'label' => "$codigoM ($tipoM)",
                            'info' => "$estadoBadge · $grupoM",
                            'file' => basename($f),
                            'date' => $reg['timestamp'] ?? '',
                            'dir'  => $grupoDir,
                            'sede' => $sedeItem,
                            'tipo_maquina' => $tipoM,
                            'grupo_maquina' => $grupoM,
                            'codigo_maquina' => $codigoM,
                            'id_registro' => $reg['id_registro'] ?? '',
                        ];
                    }
                }
            }
        }
    } else {
        $files = scandir($dirItem);
        foreach ($files as $f) {
            if ($f === '.' || $f === '..' || $f[0] === '_') continue;
            if (is_dir($dirItem . $f)) continue; // Saltar subdirectorios
            $items[] = [
                'label' => $f,
                'info' => 'Documento Original (' . strtoupper($mod['tipo']) . ')',
                'file' => $f,
                'date' => date("Y-m-d H:i", filemtime($dirItem . $f)),
                'dir'  => $dirItem,
                'sede' => $sedeItem,
            ];
        }
    }
}

// Orden cronológico único, independiente de la sede de origen.
$items = array_values($items);
usort($items, function($a, $b) { return strcmp($b['date'], $a['date']); });

if ($dirsExistentes === 0) {
    $rutasIntentadas = implode(', ', array_map(
        fn($s) => $sedeScoped ? str_replace('{SEDE}', $s, $mod['ruta']) : $mod['ruta'],
        $sedesAEscanear
    ));
    $dirError = "Carpeta no encontrada: " . $rutasIntentadas;
}

// Conteo por sede para el indicador visual (solo relevante si sede_scoped)
$conteoPorSede = [];
foreach ($items as $it) {
    $s = $it['sede'] ?? $sede;
    $conteoPorSede[$s] = ($conteoPorSede[$s] ?? 0) + 1;
}
$dir = $items[0]['dir'] ?? ($sedeScoped ? str_replace('{SEDE}', $sedesAEscanear[0], $mod['ruta']) : $mod['ruta']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Galería Unificada - <?= $mod['nombre'] ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Barlow:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #050608;
            --surface: #0f111a;
            --surface-hover: #161a29;
            --border: #1e243a;
            --accent: #00f2ff;
            --accent-sp: #0078d4;
            --accent-sp-glow: rgba(0, 120, 212, 0.3);
            --success: #3fb950;
            --danger: #f85149;
            --warn: #d29922;
            --text-main: #e0e6ed;
            --text-dim: #7a8599;
        }
        body { background: var(--bg); color: var(--text-main); font-family: 'Barlow', sans-serif; padding: 40px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; border-bottom: 1px solid var(--border); padding-bottom: 20px; }
        .header h1 { font-family: 'Orbitron', sans-serif; color: var(--accent); font-size: 1.5rem; letter-spacing: 2px; }
        
        .controls { background: var(--surface); padding: 15px 25px; border-radius: 8px; margin-bottom: 30px; display: flex; gap: 15px; align-items: center; border: 1px solid var(--border); flex-wrap: wrap; }
        .controls-separator { width: 1px; height: 30px; background: var(--border); margin: 0 5px; }
        .btn { border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-family: 'Orbitron', sans-serif; font-size: 0.7rem; letter-spacing: 1px; transition: 0.3s; }
        .btn-primary { background: var(--accent); color: #000; box-shadow: 0 0 15px rgba(0, 242, 255, 0.2); }
        .btn-outline { background: transparent; border: 1px solid var(--border); color: var(--text-dim); }
        .btn:hover { transform: translateY(-2px); opacity: 0.9; }
        .btn:disabled { opacity: 0.4; cursor: not-allowed; transform: none; }

        /* SharePoint Button */
        .btn-sharepoint {
            background: linear-gradient(135deg, #0078d4 0%, #106ebe 100%);
            color: #fff;
            box-shadow: 0 0 20px var(--accent-sp-glow);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 22px;
            position: relative;
            overflow: hidden;
        }
        .btn-sharepoint::before {
            content: '';
            position: absolute;
            top: 0; left: -100%; width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
            transition: left 0.6s;
        }
        .btn-sharepoint:hover::before { left: 100%; }
        .btn-sharepoint:hover {
            box-shadow: 0 0 30px var(--accent-sp-glow), 0 4px 15px rgba(0,0,0,0.4);
        }
        .btn-sharepoint svg { width: 16px; height: 16px; fill: currentColor; }

        .grid-results { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 15px; }
        .item-card { background: var(--surface); border: 1px solid var(--border); border-radius: 6px; padding: 20px; position: relative; transition: 0.3s; overflow: hidden; }
        .item-card:hover { border-color: var(--accent); }
        .item-card.selected { border-color: var(--accent); background: rgba(0, 242, 255, 0.05); }
        .item-card.uploaded { border-color: var(--success); }
        .item-card.uploaded::after {
            content: '☁️ SUBIDO';
            position: absolute;
            bottom: 8px;
            right: 12px;
            font-size: 0.6rem;
            font-family: 'Orbitron', sans-serif;
            color: var(--success);
            letter-spacing: 1px;
        }

        /* ── DIFERENCIADOR VISUAL POR SEDE (modo multi-sede) ── */
        .item-card.multi-sede { border-left: 4px solid var(--sede-color, var(--border)); padding-left: 18px; }
        .sede-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-family: 'Orbitron', sans-serif;
            font-size: 0.58rem;
            letter-spacing: 1px;
            padding: 3px 9px;
            border-radius: 20px;
            margin-bottom: 8px;
            color: var(--sede-color, var(--text-dim));
            background: color-mix(in srgb, var(--sede-color, var(--text-dim)) 15%, transparent);
            border: 1px solid color-mix(in srgb, var(--sede-color, var(--text-dim)) 35%, transparent);
        }

        .sede-legend { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
        .sede-legend-chip {
            font-family: 'Orbitron', sans-serif;
            font-size: 0.65rem;
            letter-spacing: 1px;
            padding: 8px 16px;
            border-radius: 8px;
            color: var(--sede-color);
            background: color-mix(in srgb, var(--sede-color) 12%, transparent);
            border: 1px solid color-mix(in srgb, var(--sede-color) 35%, transparent);
        }

        .cb-container { position: absolute; top: 15px; right: 15px; }
        .cb-container input { width: 18px; height: 18px; cursor: pointer; }

        .label { font-weight: 600; font-size: 0.9rem; color: #fff; margin-bottom: 5px; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .info { font-size: 0.75rem; color: var(--text-dim); }
        
        .back-link { text-decoration: none; color: var(--text-dim); font-size: 0.7rem; text-transform: uppercase; margin-bottom: 10px; display: inline-block; }
        .error-box { background: rgba(255,0,0,0.1); border: 1px solid red; padding: 20px; border-radius: 8px; text-align: center; }

        /* ── MODAL OVERLAY ── */
        .sp-modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(5, 6, 8, 0.85);
            backdrop-filter: blur(8px);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }
        .sp-modal-overlay.active { display: flex; }

        .sp-modal {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            width: 95%;
            max-width: 560px;
            padding: 0;
            box-shadow: 0 20px 60px rgba(0,0,0,0.7), 0 0 40px var(--accent-sp-glow);
            overflow: hidden;
            animation: modalIn 0.3s ease-out;
        }
        @keyframes modalIn {
            from { transform: scale(0.92) translateY(20px); opacity: 0; }
            to   { transform: scale(1) translateY(0); opacity: 1; }
        }

        .sp-modal-header {
            background: linear-gradient(135deg, #0078d4 0%, #106ebe 100%);
            padding: 20px 28px;
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .sp-modal-header svg { width: 28px; height: 28px; fill: #fff; }
        .sp-modal-header h2 {
            font-family: 'Orbitron', sans-serif;
            font-size: 1rem;
            letter-spacing: 2px;
            color: #fff;
            margin: 0;
        }

        .sp-modal-body { padding: 28px; }

        .sp-status {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
            font-size: 0.85rem;
        }
        .sp-status .spinner {
            width: 20px; height: 20px;
            border: 2px solid var(--border);
            border-top-color: var(--accent-sp);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        .sp-progress-bar {
            width: 100%;
            height: 6px;
            background: var(--border);
            border-radius: 3px;
            overflow: hidden;
            margin-bottom: 20px;
        }
        .sp-progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #0078d4, #00bcf2);
            border-radius: 3px;
            transition: width 0.5s ease;
            width: 0%;
        }

        .sp-log {
            background: #0a0c14;
            border: 1px solid var(--border);
            border-radius: 6px;
            max-height: 200px;
            overflow-y: auto;
            padding: 14px;
            font-family: 'Courier New', monospace;
            font-size: 0.72rem;
            color: var(--text-dim);
            line-height: 1.7;
        }
        .sp-log .success { color: var(--success); }
        .sp-log .error { color: var(--danger); }
        .sp-log .info { color: var(--accent-sp); font-size: 0.72rem; }

        .sp-summary {
            margin-top: 20px;
            padding: 14px 18px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
            display: none;
        }
        .sp-summary.ok {
            display: block;
            background: rgba(63, 185, 80, 0.1);
            border: 1px solid var(--success);
            color: var(--success);
        }
        .sp-summary.partial {
            display: block;
            background: rgba(210, 153, 34, 0.1);
            border: 1px solid var(--warn);
            color: var(--warn);
        }
        .sp-summary.fail {
            display: block;
            background: rgba(248, 81, 73, 0.1);
            border: 1px solid var(--danger);
            color: var(--danger);
        }

        .sp-modal-footer {
            padding: 16px 28px;
            border-top: 1px solid var(--border);
            text-align: right;
        }
        .sp-modal-footer .btn { font-size: 0.65rem; }
    </style>
</head>
<body>

<div class="header">
    <div>
        <a href="hub_reportes.php" class="back-link">← VOLVER AL HUB</a>
        <h1>GALERÍA: <?= strtoupper($mod['nombre']) ?></h1>
        <?php if ($sedeScoped): ?>
            <div class="info" style="margin-top:4px;">
                📍 Mostrando: <?= implode(' + ', array_map(fn($s) => $ZONAS_LABEL[$s] ?? $s, $sedesAEscanear)) ?>
            </div>
        <?php endif; ?>
    </div>
    <div id="sel-count" class="info">0 ELEMENTOS SELECCIONADOS</div>
</div>

<?php if ($multiSede && !empty($conteoPorSede)): ?>
    <div class="sede-legend">
        <?php foreach ($conteoPorSede as $s => $count): ?>
            <span class="sede-legend-chip" style="--sede-color: <?= $ZONA_COLOR[$s] ?? '#7a8599' ?>;">
                📍 <?= htmlspecialchars($ZONAS_LABEL[$s] ?? $s) ?> · <?= $count ?> documento<?= $count !== 1 ? 's' : '' ?>
            </span>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if (isset($dirError)): ?>
    <div class="error-box"><?= $dirError ?></div>
<?php else: ?>
    <div class="controls">
        <button class="btn btn-outline" onclick="toggleAll(true)">SELECCIONAR TODO</button>
        <button class="btn btn-outline" onclick="toggleAll(false)">QUITAR SELECCIÓN</button>
        <div class="controls-separator"></div>
        <button class="btn btn-primary" onclick="generateBatch()">GENERAR PDF COMBINADO</button>
        <div class="controls-separator"></div>
        <button class="btn btn-sharepoint" id="btnSharepoint" onclick="uploadToSharePoint()">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12.5 2C14.43 2 16.21 2.63 17.64 3.72C18.24 3.27 18.97 3 19.75 3C21.55 3 23 4.45 23 6.25C23 6.72 22.91 7.17 22.73 7.58C23.53 8.83 24 10.36 24 12C24 16.42 20.42 20 16 20H7C3.13 20 0 16.87 0 13C0 9.56 2.46 6.72 5.72 6.13C6.85 3.69 9.45 2 12.5 2M12.5 4C10.27 4 8.32 5.24 7.42 7.1L7.05 7.87L6.2 7.95C3.91 8.17 2 10.14 2 13C2 15.76 4.24 18 7 18H16C19.31 18 22 15.31 22 12C22 10.77 21.58 9.64 20.86 8.72L20.37 8.11L20.66 7.39C20.88 6.87 21 6.56 21 6.25C21 5.56 20.44 5 19.75 5C19.44 5 19.15 5.11 18.92 5.32L18.34 5.83L17.73 5.38C16.5 4.5 14.56 4 12.5 4M13 9V13H16L12 17L8 13H11V9H13Z"/></svg>
            SUBIR A SHAREPOINT
        </button>
    </div>

    <div class="grid-results">
        <?php if (empty($items)): ?>
            <div class="info" style="grid-column: 1/-1; text-align: center; padding: 40px;">No se encontraron registros en este módulo.</div>
        <?php endif; ?>
        <?php foreach ($items as $it):
            $itSede  = $it['sede'] ?? $sede;
            $itColor = $ZONA_COLOR[$itSede] ?? '#7a8599';
        ?>
            <div class="item-card<?= $multiSede ? ' multi-sede' : '' ?>"
                 data-filepath="<?= htmlspecialchars($it['dir'] . $it['file']) ?>"
                 <?= $multiSede ? 'style="--sede-color: ' . $itColor . ';"' : '' ?>>
                <div class="cb-container">
                    <input type="checkbox" class="rec-check" data-file="<?= $it['file'] ?>" data-date="<?= $it['date'] ?>" data-sede="<?= htmlspecialchars($itSede) ?>"
                        <?php if (isset($it['id_registro'])): ?>
                        data-tipo-maquina="<?= htmlspecialchars($it['tipo_maquina']) ?>"
                        data-grupo-maquina="<?= htmlspecialchars($it['grupo_maquina']) ?>"
                        data-codigo-maquina="<?= htmlspecialchars($it['codigo_maquina']) ?>"
                        data-id-registro="<?= htmlspecialchars($it['id_registro']) ?>"
                        <?php endif; ?>
                        onchange="updateUI()">
                </div>
                <?php if ($multiSede): ?>
                    <span class="sede-badge" style="--sede-color: <?= $itColor ?>;">📍 <?= htmlspecialchars($ZONAS_LABEL[$itSede] ?? $itSede) ?></span><br>
                <?php endif; ?>
                <span class="label"><?= $it['label'] ?></span>
                <span class="info"><?= $it['info'] ?></span>
                <div class="info" style="margin-top: 10px; font-size: 0.65rem;">Actualizado: <?= $it['date'] ?></div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Modal SharePoint Upload -->
<div class="sp-modal-overlay" id="spModal">
    <div class="sp-modal">
        <div class="sp-modal-header">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12.5 2C14.43 2 16.21 2.63 17.64 3.72C18.24 3.27 18.97 3 19.75 3C21.55 3 23 4.45 23 6.25C23 6.72 22.91 7.17 22.73 7.58C23.53 8.83 24 10.36 24 12C24 16.42 20.42 20 16 20H7C3.13 20 0 16.87 0 13C0 9.56 2.46 6.72 5.72 6.13C6.85 3.69 9.45 2 12.5 2M12.5 4C10.27 4 8.32 5.24 7.42 7.1L7.05 7.87L6.2 7.95C3.91 8.17 2 10.14 2 13C2 15.76 4.24 18 7 18H16C19.31 18 22 15.31 22 12C22 10.77 21.58 9.64 20.86 8.72L20.37 8.11L20.66 7.39C20.88 6.87 21 6.56 21 6.25C21 5.56 20.44 5 19.75 5C19.44 5 19.15 5.11 18.92 5.32L18.34 5.83L17.73 5.38C16.5 4.5 14.56 4 12.5 4M13 9V13H16L12 17L8 13H11V9H13Z"/></svg>
            <h2>MIGRACIÓN SHAREPOINT</h2>
        </div>
        <div class="sp-modal-body">
            <div class="sp-status" id="spStatus">
                <div class="spinner"></div>
                <span id="spStatusText">Preparando subida...</span>
            </div>
            <div class="sp-progress-bar">
                <div class="sp-progress-fill" id="spProgressFill"></div>
            </div>
            <div class="sp-log" id="spLog"></div>
            <div class="sp-summary" id="spSummary"></div>
        </div>
        <div class="sp-modal-footer">
            <button class="btn btn-outline" id="spCloseBtn" onclick="closeSpModal()" style="display:none;">CERRAR</button>
        </div>
    </div>
</div>

<script>
    const MODULO_RUTA = '<?= addslashes($dir) ?>';

    function updateUI() {
        const selected = document.querySelectorAll('.rec-check:checked').length;
        document.getElementById('sel-count').innerText = `${selected} ELEMENTOS SELECCIONADOS`;
        
        document.querySelectorAll('.item-card').forEach(card => {
            if(card.querySelector('.rec-check').checked) card.classList.add('selected');
            else card.classList.remove('selected');
        });
    }

    function toggleAll(val) {
        document.querySelectorAll('.rec-check').forEach(cb => cb.checked = val);
        updateUI();
    }

    function generateBatch() {
        alert("Función de Impresión Masiva en preparación...");
    }

    // ── SharePoint Upload Logic ──
    function getSelectedFilesInfo() {
        const checked = document.querySelectorAll('.rec-check:checked');
        const files = [];
        checked.forEach(cb => {
            const card = cb.closest('.item-card');
            const filepath = card.getAttribute('data-filepath');
            const date = cb.getAttribute('data-date');
            const sede = cb.getAttribute('data-sede');
            if (!filepath) return;

            const info = { path: filepath, date: date, sede: sede };
            const idRegistro = cb.getAttribute('data-id-registro');
            if (idRegistro) {
                info.tipo_maquina = cb.getAttribute('data-tipo-maquina');
                info.grupo_maquina = cb.getAttribute('data-grupo-maquina');
                info.codigo_maquina = cb.getAttribute('data-codigo-maquina');
                info.id_registro = idRegistro;
            }
            files.push(info);
        });
        return files;
    }

    function spLog(msg, type = '') {
        const log = document.getElementById('spLog');
        const line = document.createElement('div');
        line.className = type;
        line.textContent = msg;
        log.appendChild(line);
        log.scrollTop = log.scrollHeight;
    }

    function closeSpModal() {
        document.getElementById('spModal').classList.remove('active');
    }

    async function uploadToSharePoint() {
        const archivosInfo = getSelectedFilesInfo();

        if (archivosInfo.length === 0) {
            alert('Selecciona al menos un archivo para subir al SharePoint.');
            return;
        }

        // Confirmar
        const confirmar = confirm(
            `¿Deseas subir ${archivosInfo.length} archivo(s) al SharePoint?\n\n` +
            `Destino: Documentos Generados OMAS / ${new Date().getFullYear()}-${String(new Date().getMonth()+1).padStart(2,'0')}`
        );
        if (!confirmar) return;

        // Abrir modal
        const modal = document.getElementById('spModal');
        const log = document.getElementById('spLog');
        const statusText = document.getElementById('spStatusText');
        const progressFill = document.getElementById('spProgressFill');
        const summary = document.getElementById('spSummary');
        const closeBtn = document.getElementById('spCloseBtn');
        const spStatus = document.getElementById('spStatus');

        // Reset modal
        log.innerHTML = '';
        summary.className = 'sp-summary';
        summary.style.display = 'none';
        progressFill.style.width = '0%';
        closeBtn.style.display = 'none';
        spStatus.querySelector('.spinner').style.display = 'block';
        statusText.textContent = 'Conectando con SharePoint...';
        modal.classList.add('active');

        // Simular progreso mientras esperamos la respuesta
        progressFill.style.width = '15%';
        spLog(`📋 ${archivosInfo.length} archivo(s) seleccionados para migración`, 'info');
        spLog(`📁 Módulo: <?= addslashes($mod['nombre']) ?>`, 'info');
        spLog('─'.repeat(50));

        archivosInfo.forEach((a, i) => {
            spLog(`  ${i+1}. ${a.path.split('/').pop()}`);
        });

        spLog('─'.repeat(50));
        spLog('🔄 Generando PDFs y enviando al servidor...', 'info');
        progressFill.style.width = '30%';
        statusText.textContent = `Procesando ${archivosInfo.length} archivos...`;

        // Deshabilitar botón de SharePoint
        document.getElementById('btnSharepoint').disabled = true;

        try {
            const response = await fetch('sharepoint_upload.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ archivos: archivosInfo, modulo: '<?= $moduloId ?>' })
            });

            progressFill.style.width = '80%';

            const result = await response.json();

            progressFill.style.width = '100%';
            spStatus.querySelector('.spinner').style.display = 'none';

            if (result.success) {
                statusText.textContent = '✅ Migración completada';

                if (result.uploaded && result.uploaded.length > 0) {
                    spLog('');
                    spLog('═'.repeat(50));
                    result.uploaded.forEach(u => {
                        spLog(`✅ ${u.file} → ${u.path}`, 'success');
                        // Marcar visualmente la card como subida
                        document.querySelectorAll('.item-card').forEach(card => {
                            const cb = card.querySelector('.rec-check');
                            if (cb && card.getAttribute('data-filepath')?.endsWith(u.file)) {
                                card.classList.add('uploaded');
                            }
                        });
                    });
                }

                if (result.errors && result.errors.length > 0) {
                    result.errors.forEach(e => {
                        spLog(`❌ ${e.file}: ${e.error}`, 'error');
                    });
                    summary.className = 'sp-summary partial';
                    summary.textContent = `⚠️ ${result.uploaded?.length || 0} de ${result.total} archivos subidos. ${result.errors.length} error(es).`;
                    summary.style.display = 'block';
                } else {
                    summary.className = 'sp-summary ok';
                    summary.textContent = `🎉 ${result.uploaded?.length || 0} archivo(s) migrados exitosamente al SharePoint.`;
                    summary.style.display = 'block';
                }
            } else {
                statusText.textContent = '❌ Error en la migración';
                spLog('');
                spLog(`❌ ${result.error || 'Error desconocido'}`, 'error');
                summary.className = 'sp-summary fail';
                summary.textContent = 'La migración falló. Revisa los detalles arriba.';
                summary.style.display = 'block';
            }
        } catch (err) {
            progressFill.style.width = '100%';
            spStatus.querySelector('.spinner').style.display = 'none';
            statusText.textContent = '❌ Error de red';
            spLog(`❌ Error de red: ${err.message}`, 'error');
            summary.className = 'sp-summary fail';
            summary.textContent = 'No se pudo conectar con el servidor. Verifica tu conexión.';
            summary.style.display = 'block';
        }

        closeBtn.style.display = 'inline-block';
        document.getElementById('btnSharepoint').disabled = false;
    }
</script>

</body>
</html>
