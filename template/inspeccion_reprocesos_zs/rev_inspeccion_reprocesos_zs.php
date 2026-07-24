<?php
require '../sesion.php';

if (!isset($_SESSION['nombre']) || empty($_SESSION['sede'])) {
    header('Location: ../../index.php');
    exit;
}

$sede        = $_SESSION['sede'];
$sede_dir    = preg_replace('/[^A-Za-z0-9_-]/', '', $sede);
$target_dir  = "../../archivos/generados/inspeccion_reprocesos_zs/" . $sede_dir . "/";

$archivos = [];

if (file_exists($target_dir)) {
    $files = scandir($target_dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        if (strtolower(pathinfo($file, PATHINFO_EXTENSION)) !== 'json') continue;

        $filepath = $target_dir . $file;
        $content  = json_decode(file_get_contents($filepath), true) ?: [];

        $total    = count($content);
        $ultimo   = !empty($content) ? end($content) : null;

        $last_fecha       = $ultimo['datos']['fecha']                    ?? '—';
        $last_responsable = $ultimo['datos']['responsable_nombre']       ?? 'Sin registro';
        $last_porcentaje  = isset($ultimo['datos']['porcentaje_cumplimiento'])
                            ? floatval($ultimo['datos']['porcentaje_cumplimiento'])
                            : null;
        $last_usuario     = $ultimo['usuario_sys'] ?? '—';

        $periodo = str_replace(['INGREPROC_', '.json'], '', $file);

        $archivos[] = [
            'filename'      => $file,
            'periodo'       => $periodo,
            'registros'     => $total,
            'ultima_fecha'  => $last_fecha,
            'responsable'   => $last_responsable,
            'porcentaje'    => $last_porcentaje,
            'ultimo_usuario'=> $last_usuario,
            'mod_time'      => filemtime($filepath),
            'fecha_mod'     => date('d M Y - H:i', filemtime($filepath)),
        ];
    }

    usort($archivos, fn($a, $b) => $b['mod_time'] <=> $a['mod_time']);
}

$total_registros_global = array_sum(array_column($archivos, 'registros'));
$total_archivos         = count($archivos);

$porcentajes = array_filter(array_column($archivos, 'porcentaje'), fn($p) => $p !== null);
$prom_cumplimiento = count($porcentajes) > 0
    ? round(array_sum($porcentajes) / count($porcentajes), 1)
    : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revisiones - Inspección Gestión de Reprocesos</title>
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
            --success: #10B981;
            --warning: #FFB000;
            --r-lg: 12px;
            --r-md: 8px;
            --r-sm: 4px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Barlow', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            min-height: 100vh;
            padding: 40px 20px;
            background-image:
                radial-gradient(circle at top right, rgba(0, 240, 255, 0.05), transparent 40%),
                linear-gradient(rgba(0, 240, 255, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0, 240, 255, 0.03) 1px, transparent 1px);
            background-size: 100% 100%, 30px 30px, 30px 30px;
        }

        .container { max-width: 1200px; margin: 0 auto; }

        .header-box {
            background: var(--panel-bg);
            border: 1px solid var(--border-color);
            border-left: 4px solid var(--accent);
            padding: 30px;
            border-radius: var(--r-md);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            position: relative;
            overflow: hidden;
        }

        .header-box::before {
            content: "REVISOR JSON";
            position: absolute; top: -10px; right: 20px;
            background: var(--accent); color: var(--bg-color);
            font-family: 'Space Mono', monospace; font-size: 10px; font-weight: 700;
            padding: 4px 12px; border-radius: var(--r-sm);
            box-shadow: 0 0 10px var(--accent-glow);
        }

        .main-title { font-size: 24px; font-weight: 700; color: #fff; text-transform: uppercase; margin-bottom: 4px; }
        .sub-title { color: var(--text-muted); font-size: 13px; font-family: 'Space Mono', monospace; }

        .btn-back {
            background: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-main);
            padding: 10px 20px; border-radius: var(--r-sm); font-family: 'Space Mono', monospace;
            text-decoration: none; font-size: 13px; transition: all 0.3s; white-space: nowrap;
        }
        .btn-back:hover { border-color: var(--accent); color: var(--accent); background: rgba(0, 240, 255, 0.05); }

        .stats-banner { display: flex; gap: 20px; margin-bottom: 30px; flex-wrap: wrap; }

        .stat-card {
            background: var(--panel-bg);
            border: 1px solid var(--border-color);
            padding: 20px 28px;
            border-radius: var(--r-md);
            flex: 1; min-width: 160px;
            display: flex; flex-direction: column; gap: 4px;
            transition: border-color 0.3s;
        }
        .stat-card:hover { border-color: rgba(0, 240, 255, 0.3); }

        .stat-val { font-size: 28px; font-weight: 700; color: var(--accent); font-family: 'Space Mono', monospace; }
        .stat-val.good   { color: var(--success); }
        .stat-val.warn   { color: var(--warning); }
        .stat-val.danger { color: var(--danger);  }
        .stat-label { font-size: 11px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; }

        .search-bar { margin-bottom: 25px; display: flex; gap: 12px; align-items: center; }

        .search-input {
            flex: 1;
            background: var(--input-bg); border: 1px solid var(--border-color);
            color: var(--text-main); padding: 12px 16px;
            border-radius: var(--r-sm); font-family: 'Barlow', sans-serif; font-size: 14px;
            transition: all 0.3s;
        }
        .search-input:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 3px rgba(0, 240, 255, 0.1); }
        .search-input::placeholder { color: var(--text-muted); }

        .btn-new {
            background: var(--accent); color: var(--bg-color); border: none;
            padding: 12px 20px; border-radius: var(--r-sm);
            font-family: 'Space Mono', monospace; font-size: 12px; font-weight: 700;
            text-decoration: none; text-transform: uppercase; white-space: nowrap;
            transition: all 0.3s; box-shadow: 0 0 15px var(--accent-glow);
            display: flex; align-items: center; gap: 8px;
        }
        .btn-new:hover { background: #fff; box-shadow: 0 0 25px rgba(255,255,255,0.4); }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 20px;
        }

        .file-card {
            background: var(--panel-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--r-md);
            padding: 25px;
            display: flex; flex-direction: column; gap: 15px;
            transition: all 0.3s ease;
            text-decoration: none;
            position: relative;
            overflow: hidden;
        }

        .file-card::after {
            content: ''; position: absolute; bottom: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--accent), transparent);
            opacity: 0; transition: opacity 0.3s;
        }

        .file-card:hover {
            border-color: rgba(0, 240, 255, 0.4);
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 240, 255, 0.08);
        }
        .file-card:hover::after { opacity: 1; }

        .card-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; }

        .periodo-badge {
            background: rgba(0, 240, 255, 0.1);
            color: var(--accent);
            border: 1px solid rgba(0, 240, 255, 0.2);
            padding: 4px 10px;
            border-radius: var(--r-sm);
            font-family: 'Space Mono', monospace;
            font-size: 11px; font-weight: 700;
            white-space: nowrap;
        }

        .cumplimiento-badge {
            padding: 4px 10px;
            border-radius: var(--r-sm);
            font-family: 'Space Mono', monospace;
            font-size: 12px; font-weight: 700;
            white-space: nowrap;
        }
        .cumplimiento-badge.good   { background: rgba(16, 185, 129, 0.15); color: var(--success); border: 1px solid rgba(16,185,129,0.3); }
        .cumplimiento-badge.warn   { background: rgba(255, 176, 0, 0.15);  color: var(--warning);  border: 1px solid rgba(255,176,0,0.3); }
        .cumplimiento-badge.danger { background: rgba(255, 51, 102, 0.15); color: var(--danger);   border: 1px solid rgba(255,51,102,0.3); }

        .card-title { color: #fff; font-size: 17px; font-weight: 600; line-height: 1.4; }
        .card-sub   { color: var(--text-muted); font-size: 13px; }

        .file-meta {
            display: flex; flex-direction: column; gap: 7px;
            border-top: 1px dashed var(--border-color); padding-top: 14px;
            margin-top: auto;
        }

        .meta-line { display: flex; justify-content: space-between; font-size: 13px; color: var(--text-muted); }
        .meta-val  { color: #fff; font-family: 'Space Mono', monospace; font-size: 11px; text-align: right; max-width: 55%; word-break: break-word; }

        .btn-view {
            background: transparent; color: var(--accent);
            border: 1px solid var(--accent); padding: 10px;
            border-radius: var(--r-sm); text-align: center;
            font-family: 'Space Mono', monospace; font-weight: 700; font-size: 12px;
            text-transform: uppercase; letter-spacing: 0.5px;
            transition: all 0.3s;
        }
        .file-card:hover .btn-view {
            background: var(--accent); color: var(--bg-color);
            box-shadow: 0 0 15px var(--accent-glow);
        }

        .empty-state {
            grid-column: 1 / -1;
            background: var(--panel-bg); border: 1px dashed var(--border-color);
            padding: 70px 20px; border-radius: var(--r-md);
            text-align: center; color: var(--text-muted);
        }
        .empty-state h3 { color: #fff; font-size: 20px; margin-bottom: 10px; }
        .empty-state p  { margin-bottom: 25px; }
        .empty-state a  {
            background: var(--accent); color: var(--bg-color); padding: 12px 24px;
            border-radius: var(--r-sm); font-family: 'Space Mono', monospace;
            font-weight: 700; text-decoration: none; font-size: 13px; display: inline-block;
        }

        .file-card.hidden-by-search { display: none; }
    </style>
</head>
<body>

<div class="container">

    <div class="header-box">
        <div>
            <h1 class="main-title">Inspección Gestión de Reprocesos</h1>
            <div class="sub-title">Sede Operativa: [ <?= htmlspecialchars($sede) ?> ] &nbsp;|&nbsp; OPE-GR-FO-002</div>
        </div>
        <a href="../menu_seccion_sur.html" class="btn-back">← Menú</a>
    </div>

    <div class="stats-banner">
        <div class="stat-card">
            <div class="stat-val"><?= $total_archivos ?></div>
            <div class="stat-label">Archivos Mensuales</div>
        </div>
        <div class="stat-card">
            <div class="stat-val"><?= $total_registros_global ?></div>
            <div class="stat-label">Inspecciones Totales</div>
        </div>
        <div class="stat-card">
            <?php
                $cls = 'good';
                if ($prom_cumplimiento !== null) {
                    if ($prom_cumplimiento < 60)     $cls = 'danger';
                    elseif ($prom_cumplimiento < 80) $cls = 'warn';
                }
            ?>
            <div class="stat-val <?= $cls ?>">
                <?= $prom_cumplimiento !== null ? $prom_cumplimiento . '%' : '—' ?>
            </div>
            <div class="stat-label">% Cumplimiento Promedio</div>
        </div>
    </div>

    <div class="search-bar">
        <input type="text" class="search-input" id="searchInput"
               placeholder="Buscar por período o responsable..."
               oninput="filtrarTarjetas(this.value)">
        <a href="index.html" class="btn-new">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            NUEVA INSPECCIÓN
        </a>
    </div>

    <div class="grid" id="gridContainer">
        <?php if (empty($archivos)): ?>
            <div class="empty-state">
                <h3>Sin Registros</h3>
                <p>Aún no hay inspecciones de reprocesos guardadas para la sede <strong><?= htmlspecialchars($sede) ?></strong>.</p>
                <a href="index.html">+ Crear Primera Inspección</a>
            </div>
        <?php else: ?>
            <?php foreach ($archivos as $doc):
                $pct = $doc['porcentaje'];
                $badge_cls = 'good';
                if ($pct !== null) {
                    if ($pct < 60)     $badge_cls = 'danger';
                    elseif ($pct < 80) $badge_cls = 'warn';
                }
                $badge_text = $pct !== null ? number_format($pct, 1) . '%' : 'N/D';
            ?>
                <a href="visor_inspeccion_reprocesos_zs.php?file=<?= urlencode($doc['filename']) ?>"
                   class="file-card"
                   data-search="<?= htmlspecialchars(strtolower($doc['periodo'] . ' ' . $doc['responsable'] . ' ' . $doc['ultimo_usuario'])) ?>">

                    <div class="card-header">
                        <span class="periodo-badge">📅 <?= htmlspecialchars($doc['periodo']) ?></span>
                        <span class="cumplimiento-badge <?= $badge_cls ?>"><?= $badge_text ?></span>
                    </div>

                    <div>
                        <div class="card-title">Reprocesos — <?= htmlspecialchars($doc['periodo']) ?></div>
                        <div class="card-sub">Último responsable: <?= htmlspecialchars($doc['responsable']) ?></div>
                    </div>

                    <div class="file-meta">
                        <div class="meta-line">
                            <span>Inspecciones guardadas</span>
                            <span class="meta-val"><?= $doc['registros'] ?> registro(s)</span>
                        </div>
                        <div class="meta-line">
                            <span>Última inspección</span>
                            <span class="meta-val"><?= htmlspecialchars($doc['ultima_fecha']) ?></span>
                        </div>
                        <div class="meta-line">
                            <span>Registrado por</span>
                            <span class="meta-val"><?= htmlspecialchars($doc['ultimo_usuario']) ?></span>
                        </div>
                        <div class="meta-line">
                            <span>Modificado</span>
                            <span class="meta-val"><?= $doc['fecha_mod'] ?></span>
                        </div>
                    </div>

                    <div class="btn-view">VER INSPECCIONES →</div>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>

<script>
    function filtrarTarjetas(query) {
        const q = query.toLowerCase().trim();
        document.querySelectorAll('.file-card').forEach(card => {
            const data = card.dataset.search || '';
            card.classList.toggle('hidden-by-search', q !== '' && !data.includes(q));
        });
    }
</script>

</body>
</html>
