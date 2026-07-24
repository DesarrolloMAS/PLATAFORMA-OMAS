<?php
require '../sesion.php';

if (!isset($_SESSION['nombre']) || empty($_SESSION['sede'])) {
    header('Location: ../../index.php');
    exit;
}

$sede       = $_SESSION['sede'];
$sede_dir   = preg_replace('/[^A-Za-z0-9_-]/', '', $sede);
$target_dir = "../../archivos/generados/preparacion_mejorante/" . $sede_dir . "/";

$archivos = [];

if (file_exists($target_dir)) {
    $files = scandir($target_dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        if (strtolower(pathinfo($file, PATHINFO_EXTENSION)) !== 'json') continue;

        $filepath = $target_dir . $file;
        $content  = json_decode(file_get_contents($filepath), true) ?: [];

        $total  = count($content);
        $ultimo = !empty($content) ? end($content) : null;

        $last_fecha     = $ultimo['datos']['fecha']       ?? '—';
        $last_referencia = $ultimo['datos']['referencia'] ?? '—';
        $last_realiza   = $ultimo['datos']['realiza']     ?? '—';
        $last_verifica  = $ultimo['datos']['verifica']    ?? '—';
        $last_usuario   = $ultimo['usuario_sys']          ?? '—';

        $periodo = str_replace(['PMEJ_', '.json'], '', $file);

        $archivos[] = [
            'filename'        => $file,
            'periodo'         => $periodo,
            'registros'       => $total,
            'ultima_fecha'    => $last_fecha,
            'referencia'      => $last_referencia,
            'realiza'         => $last_realiza,
            'verifica'        => $last_verifica,
            'ultimo_usuario'  => $last_usuario,
            'mod_time'        => filemtime($filepath),
            'fecha_mod'       => date('d M Y - H:i', filemtime($filepath)),
        ];
    }

    usort($archivos, fn($a, $b) => $b['mod_time'] <=> $a['mod_time']);
}

$total_registros_global = array_sum(array_column($archivos, 'registros'));
$total_archivos         = count($archivos);

$ultima_actividad = !empty($archivos) ? $archivos[0]['fecha_mod'] : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revisiones - Preparación de Mejorante</title>
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
        .sub-title  { color: var(--text-muted); font-size: 13px; font-family: 'Space Mono', monospace; }

        .btn-back {
            background: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-main);
            padding: 10px 20px; border-radius: var(--r-sm); font-family: 'Space Mono', monospace;
            text-decoration: none; font-size: 13px; transition: all 0.3s; white-space: nowrap;
        }
        .btn-back:hover { border-color: var(--accent); color: var(--accent); background: rgba(0, 240, 255, 0.05); }

        /* ── Stats ── */
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

        .stat-val   { font-size: 28px; font-weight: 700; color: var(--accent); font-family: 'Space Mono', monospace; }
        .stat-label { font-size: 11px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; }
        .stat-sub   { font-size: 11px; color: var(--text-muted); font-family: 'Space Mono', monospace; margin-top: 2px; }

        /* ── Barra de búsqueda ── */
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

        /* ── Grid de tarjetas ── */
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
            cursor: pointer;
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
            font-size: 11px; font-weight: 700; white-space: nowrap;
        }

        .count-badge {
            background: rgba(16, 185, 129, 0.12);
            color: var(--success);
            border: 1px solid rgba(16, 185, 129, 0.25);
            padding: 4px 10px;
            border-radius: var(--r-sm);
            font-family: 'Space Mono', monospace;
            font-size: 11px; font-weight: 700; white-space: nowrap;
        }

        .card-title { color: #fff; font-size: 17px; font-weight: 600; line-height: 1.4; }
        .card-sub   { color: var(--text-muted); font-size: 13px; margin-top: 3px; }

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

    <!-- HEADER -->
    <div class="header-box">
        <div>
            <h1 class="main-title">Preparación de Mejorante</h1>
            <div class="sub-title">Sede Operativa: [ <?= htmlspecialchars($sede) ?> ] &nbsp;|&nbsp; Galería de Registros</div>
        </div>
        <a href="../menu_almacen.html" class="btn-back">← Menú</a>
    </div>

    <!-- STATS -->
    <div class="stats-banner">
        <div class="stat-card">
            <div class="stat-val"><?= $total_archivos ?></div>
            <div class="stat-label">Archivos Mensuales</div>
        </div>
        <div class="stat-card">
            <div class="stat-val"><?= $total_registros_global ?></div>
            <div class="stat-label">Preparaciones Totales</div>
        </div>
        <div class="stat-card">
            <div class="stat-val" style="font-size:16px; padding-top:4px;">
                <?= $ultima_actividad ?? '—' ?>
            </div>
            <div class="stat-label">Última Actividad</div>
        </div>
    </div>

    <!-- BÚSQUEDA + BOTÓN NUEVO -->
    <div class="search-bar">
        <input type="text" class="search-input" id="searchInput"
               placeholder="Buscar por período, referencia o responsable..."
               oninput="filtrarTarjetas(this.value)">
        <a href="index.html" class="btn-new">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            NUEVA PREPARACIÓN
        </a>
    </div>

    <!-- GRID DE TARJETAS -->
    <div class="grid" id="gridContainer">
        <?php if (empty($archivos)): ?>
            <div class="empty-state">
                <h3>Sin Registros</h3>
                <p>Aún no hay preparaciones de mejorante guardadas para la sede <strong><?= htmlspecialchars($sede) ?></strong>.</p>
                <a href="index.html">+ Crear Primera Preparación</a>
            </div>
        <?php else: ?>
            <?php foreach ($archivos as $doc): ?>
                <a href="visor_preparacion_mejorante.php?file=<?= urlencode($doc['filename']) ?>"
                   class="file-card"
                   data-search="<?= htmlspecialchars(strtolower(
                       $doc['periodo'] . ' ' .
                       $doc['referencia'] . ' ' .
                       $doc['realiza'] . ' ' .
                       $doc['verifica'] . ' ' .
                       $doc['ultimo_usuario']
                   )) ?>">

                    <div class="card-header">
                        <span class="periodo-badge">📅 <?= htmlspecialchars($doc['periodo']) ?></span>
                        <span class="count-badge"><?= $doc['registros'] ?> preparación<?= $doc['registros'] !== 1 ? 'es' : '' ?></span>
                    </div>

                    <div>
                        <div class="card-title">Mejorante — <?= htmlspecialchars($doc['periodo']) ?></div>
                        <div class="card-sub">Ref: <?= htmlspecialchars($doc['referencia']) ?></div>
                    </div>

                    <div class="file-meta">
                        <div class="meta-line">
                            <span>Última fecha</span>
                            <span class="meta-val"><?= htmlspecialchars($doc['ultima_fecha']) ?></span>
                        </div>
                        <div class="meta-line">
                            <span>Realizó</span>
                            <span class="meta-val"><?= htmlspecialchars($doc['realiza']) ?></span>
                        </div>
                        <div class="meta-line">
                            <span>Verificó</span>
                            <span class="meta-val"><?= htmlspecialchars($doc['verifica']) ?></span>
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

                    <div class="btn-view">VER PREPARACIONES →</div>
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
