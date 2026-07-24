<?php
require '../sesion.php';
verificarAutenticacion();

$sede = $_SESSION['sede'];

$BODEGAS_PRINCIPALES = [
    'BodegaPNC'         => 'BODEGA PNC',
    'BodegaMogolla'     => 'BODEGA MOGOLLA',
    'Bodega1'           => 'BODEGA 1',
    'Bodega2'           => 'BODEGA 2',
    'Bodega3'           => 'BODEGA 3',
    'Bodega4'           => 'BODEGA 4',
    'BodegaPreMezclas'  => 'BODEGA PRE MEZCLAS',
    'BodegaMejorantes'  => 'BODEGA MEJORANTES',
];

$BODEGAS_ZS = [
    'PTfamiliarZS'        => 'PT FAMILIAR',
    'PTespecialZS'        => 'PT ESPECIAL',
    'materialesZS'        => 'MATERIALES',
    'PTindustrialZS'      => 'PT INDUSTRIAL',
    'microingredientesZS' => 'MICROINGREDIENTES',
    'LaboratorioZS'       => 'LABORATORIO',
];

$bodegas = ($sede === 'ZS') ? $BODEGAS_ZS : $BODEGAS_PRINCIPALES;
$sede_san = preg_replace('/[^A-Za-z0-9_-]/', '', $sede);

function obtenerUltimaVerificacion($bodegaKey, $sedeSan) {
    $base_dir = __DIR__ . "/../../archivos/generados/bodegas_v2/{$sedeSan}/";
    $mes_actual   = date('Y-m');
    $mes_anterior = date('Y-m', strtotime('first day of last month'));

    foreach ([$mes_actual, $mes_anterior] as $mes) {
        $archivo = $base_dir . "{$bodegaKey}_{$mes}.json";
        if (!file_exists($archivo)) continue;
        $registros = json_decode(file_get_contents($archivo), true) ?: [];
        if (empty($registros)) continue;
        $ultimo = end($registros);
        if (!empty($ultimo['timestamp'])) {
            return strtotime($ultimo['timestamp']);
        }
    }
    return 0;
}

$estados = [];
foreach ($bodegas as $key => $nombre) {
    $ts = obtenerUltimaVerificacion($key, $sede_san);
    $estados[$key] = $ts;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INSPECCIÓN DE BODEGAS V2</title>
    <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700&family=Space+Mono:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #0B0E14;
            --panel-bg: #151A22;
            --accent: #00F0FF;
            --accent-glow: rgba(0, 240, 255, 0.4);
            --text-main: #E2E8F0;
            --text-muted: #94A3B8;
            --border-color: #1E293B;
            --input-bg: #0F172A;
            --danger: #FF3366;
            --danger-glow: rgba(255, 51, 102, 0.4);
            --warning: #FFB000;
            --success: #10B981;
            --r-lg: 12px;
            --r-md: 8px;
            --r-sm: 4px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Barlow', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            line-height: 1.5;
            min-height: 100vh;
            padding: 40px 20px;
            background-image:
                linear-gradient(rgba(0, 240, 255, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0, 240, 255, 0.03) 1px, transparent 1px);
            background-size: 30px 30px;
        }

        .container { max-width: 1300px; margin: 0 auto; }

        /* ── HEADER ── */
        .header-box {
            background: var(--panel-bg);
            border: 1px solid var(--border-color);
            border-left: 4px solid var(--accent);
            padding: 30px;
            border-radius: var(--r-md);
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            position: relative;
            overflow: hidden;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .header-box::before {
            content: "V2 JSON";
            position: absolute;
            top: 20px; right: -30px;
            background: var(--accent);
            color: var(--bg-color);
            font-family: 'Space Mono', monospace;
            font-size: 11px; font-weight: 700;
            padding: 4px 40px;
            transform: rotate(45deg);
            box-shadow: 0 0 10px var(--accent-glow);
        }

        .main-title { font-size: 24px; font-weight: 700; color: #fff; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px; }
        .sub-title { color: var(--text-muted); font-size: 13px; font-family: 'Space Mono', monospace; }

        .header-actions { display: flex; gap: 10px; flex-wrap: wrap; }

        .btn-back, .btn-hist {
            background: var(--input-bg);
            border: 1px solid var(--border-color);
            color: var(--text-main);
            padding: 10px 18px;
            border-radius: var(--r-sm);
            font-family: 'Space Mono', monospace;
            text-decoration: none;
            font-size: 12px;
            transition: all 0.2s;
            white-space: nowrap;
        }
        .btn-back:hover, .btn-hist:hover { border-color: var(--accent); color: var(--accent); background: rgba(0,240,255,0.05); }

        /* ── GRID ── */
        .steps-label {
            font-family: 'Space Mono', monospace;
            font-size: 10px; color: var(--text-muted);
            text-transform: uppercase; letter-spacing: 2px;
            margin-bottom: 13px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .card {
            background: var(--panel-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--r-md);
            overflow: hidden;
            display: flex; flex-direction: column;
            text-decoration: none; color: inherit;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }

        .card:hover {
            border-color: rgba(0, 240, 255, 0.4);
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(0, 240, 255, 0.08);
        }

        .card-img-wrap {
            width: 100%; height: 150px;
            background: var(--input-bg);
            overflow: hidden;
        }
        .card-img-wrap img { width: 100%; height: 100%; object-fit: cover; display: block; transition: transform 0.4s; }
        .card:hover .card-img-wrap img { transform: scale(1.06); }

        .card-body { padding: 16px 18px 18px; display: flex; flex-direction: column; gap: 10px; flex: 1; }

        .card-title { font-size: 15px; font-weight: 700; color: #fff; text-transform: uppercase; letter-spacing: 0.5px; }

        .estado-row {
            display: flex; flex-direction: column; gap: 6px;
            border-top: 1px dashed var(--border-color);
            padding-top: 10px; margin-top: auto;
        }

        .estado-linea { display: flex; justify-content: space-between; align-items: center; font-size: 11px; }
        .estado-label { color: var(--text-muted); font-family: 'Space Mono', monospace; text-transform: uppercase; letter-spacing: 0.5px; font-size: 10px; }
        .estado-valor { font-family: 'Space Mono', monospace; font-size: 11px; color: var(--text-main); }

        .cronometro-badge {
            display: inline-flex; align-items: center; gap: 6px;
            font-family: 'Space Mono', monospace;
            font-size: 11px; font-weight: 700;
            padding: 5px 10px; border-radius: 99px;
            width: fit-content;
        }
        .cronometro-badge.ok {
            background: rgba(16,185,129,0.12); border: 1px solid rgba(16,185,129,0.4); color: var(--success);
        }
        .cronometro-badge.vencido {
            background: rgba(255,51,102,0.14); border: 1px solid rgba(255,51,102,0.5); color: var(--danger);
            box-shadow: 0 0 10px var(--danger-glow);
            animation: alerta-pulse 1.6s infinite;
        }
        @keyframes alerta-pulse {
            0%,100% { box-shadow: 0 0 0 0 rgba(255,51,102,0.4); }
            50%     { box-shadow: 0 0 0 5px rgba(255,51,102,0.05); }
        }

        .cta-row {
            display: flex; align-items: center; justify-content: space-between;
            font-family: 'Space Mono', monospace; font-size: 11px; font-weight: 700;
            color: var(--accent); text-transform: uppercase;
        }

        /* ── FOOTER ── */
        .system-status {
            display: flex; align-items: center; justify-content: center;
            gap: 10px;
            font-family: 'Space Mono', monospace; font-size: 10px;
            color: var(--text-muted); padding-top: 22px;
        }
        .status-dot {
            width: 7px; height: 7px; border-radius: 50%;
            background: var(--success); box-shadow: 0 0 6px var(--success);
            animation: pulse 2s infinite;
        }
        @keyframes pulse { 0%,100% { opacity:1; } 50% { opacity:0.4; } }
    </style>
</head>
<body>
<div class="container">

    <div class="header-box">
        <div>
            <div class="main-title">Inspección de Bodegas</div>
            <div class="sub-title">Sede Operativa: [ <?= htmlspecialchars($sede) ?> ] — Seleccione una bodega para iniciar</div>
        </div>
        <div class="header-actions">
            <a href="rev_bodegas_v2.php" class="btn-hist">📁 Historial de Inspecciones</a>
            <a href="../menu_almacen.html" class="btn-back">← Menú Almacén</a>
        </div>
    </div>

    <div class="steps-label">// bodegas disponibles — sede <?= htmlspecialchars($sede) ?></div>
    <div class="grid">
        <?php foreach ($bodegas as $key => $nombre): ?>
            <?php $ts = $estados[$key]; ?>
            <a class="card" href="inspeccion_bodega.html?bodega=<?= urlencode($key) ?>&nombre=<?= urlencode($nombre) ?>">
                <div class="card-img-wrap">
                    <img src="/img/<?= htmlspecialchars($key) ?>.jpeg" alt="<?= htmlspecialchars($nombre) ?>" loading="lazy">
                </div>
                <div class="card-body">
                    <div class="card-title"><?= htmlspecialchars($nombre) ?></div>
                    <div class="estado-row">
                        <div class="estado-linea">
                            <span class="estado-label">Última revisión</span>
                            <span class="estado-valor"><?= $ts > 0 ? date('d M Y - H:i', $ts) : 'Nunca' ?></span>
                        </div>
                        <?php if ($ts > 0): ?>
                            <span class="cronometro-badge ok cronometro" data-tiempo="<?= ($ts + 86400) - time() ?>">⏱ —</span>
                        <?php else: ?>
                            <span class="cronometro-badge vencido">⚠ ¡Debe realizar la verificación!</span>
                        <?php endif; ?>
                    </div>
                    <div class="cta-row">
                        <span>Iniciar Inspección</span>
                        <span>→</span>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="system-status">
        <div class="status-dot"></div>
        SISTEMA JSON INTERCONECTADO — PLATAFORMA OMAS / ALMACÉN
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.cronometro').forEach(function (el) {
        let tiempoRestante = parseInt(el.dataset.tiempo, 10);

        function actualizar() {
            if (tiempoRestante <= 0) {
                el.textContent = '⚠ ¡Debe realizar la verificación!';
                el.classList.remove('ok');
                el.classList.add('vencido');
                return;
            }
            const horas   = Math.floor(tiempoRestante / 3600);
            const minutos = Math.floor((tiempoRestante % 3600) / 60);
            const segundos = tiempoRestante % 60;
            el.textContent = `⏱ ${horas}h ${minutos}m ${segundos}s restantes`;
            tiempoRestante--;
            setTimeout(actualizar, 1000);
        }
        actualizar();
    });
});
</script>
</body>
</html>
