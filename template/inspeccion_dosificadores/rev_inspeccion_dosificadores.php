<?php
require '../sesion.php';

if (!isset($_SESSION['nombre']) || empty($_SESSION['sede'])) {
    header('Location: ../../index.php');
    exit;
}

$sede = $_SESSION['sede'];
$sede_saneada = preg_replace('/[^A-Za-z0-9_-]/', '', $sede);
$target_dir = "../../archivos/generados/inspeccion_dosificadores/" . $sede_saneada . "/";

$from = $_GET['from'] ?? 'calidad';
$backUrl = ($from === 'mantenimiento') ? '../menu_mantenimiento.html' : '../menu_adm_calidad.html';
$backLabel = ($from === 'mantenimiento') ? '← Menú Mantenimiento' : '← Menú Calidad';

$archivos = [];

if (file_exists($target_dir)) {
    $files = scandir($target_dir);
    foreach ($files as $file) {
        // Cada inspección vive en su propio archivo "INSPECCION_...json" —
        // se excluye explícitamente el catálogo de dosificadores y cualquier
        // otro json que no sea una inspección individual.
        if (!preg_match('/^INSPECCION_.*\.json$/i', $file)) continue;

        $filepath = $target_dir . $file;
        $reg = json_decode(file_get_contents($filepath), true);
        if (!is_array($reg) || empty($reg['datos'])) continue;

        $d = $reg['datos'];

        $archivos[] = [
            'filename'       => $file,
            'id_registro'    => $reg['id_registro'] ?? '',
            'ultima_fecha'   => $d['fecha']            ?? '—',
            'dosificador'    => $d['dosificador']       ?? 'Sin dosificador',
            'microingrediente' => $d['microingrediente'] ?? '—',
            'cumple'         => $d['cumple']            ?? null,
            'ultimo_usuario' => $reg['usuario_sys']     ?? '—',
            'mod_time'       => filemtime($filepath),
            'fecha_mod'      => date('d M Y - H:i', filemtime($filepath)),
        ];
    }

    usort($archivos, fn($a, $b) => $b['mod_time'] <=> $a['mod_time']);
}

$total_archivos = count($archivos);

// Conteo de cumplimiento del último registro de cada archivo
$cumples = array_column($archivos, 'cumple');
$total_cumple    = count(array_filter($cumples, fn($c) => $c === 'CUMPLE'));
$total_no_cumple = count(array_filter($cumples, fn($c) => $c === 'NO CUMPLE'));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revisiones - Inspección de Dosificadores</title>
    <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700&family=Space+Mono:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        .stat-val.danger { color: var(--danger); }
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

        .cumple-badge {
            padding: 4px 10px;
            border-radius: var(--r-sm);
            font-family: 'Space Mono', monospace;
            font-size: 11px; font-weight: 700;
            white-space: nowrap;
        }
        .cumple-badge.good   { background: rgba(16, 185, 129, 0.15); color: var(--success); border: 1px solid rgba(16,185,129,0.3); }
        .cumple-badge.danger { background: rgba(255, 51, 102, 0.15); color: var(--danger);   border: 1px solid rgba(255,51,102,0.3); }
        .cumple-badge.na     { background: rgba(148, 163, 184, 0.1); color: var(--text-muted); border: 1px solid var(--border-color); }

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
        .file-card:hover .btn-view { background: var(--accent); color: var(--bg-color); box-shadow: 0 0 15px var(--accent-glow); }

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

        .card-wrap { position: relative; }
        .card-wrap.hidden-by-search { display: none; }

        .btn-delete-card {
            position: absolute; top: -10px; right: -10px; z-index: 5;
            width: 28px; height: 28px; border-radius: 50%;
            background: var(--panel-bg); border: 1px solid var(--danger);
            color: var(--danger); font-size: 13px; font-weight: 700;
            cursor: pointer; display: flex; align-items: center; justify-content: center;
            transition: all 0.2s; line-height: 1;
        }
        .btn-delete-card:hover { background: var(--danger); color: #fff; box-shadow: 0 0 12px var(--danger-glow); }
    </style>
</head>
<body>

<div class="container">

    <div class="header-box">
        <div>
            <h1 class="main-title">Inspecciones de Dosificadores</h1>
            <div class="sub-title">Sede Operativa: [ <?= htmlspecialchars($sede) ?> ] &nbsp;|&nbsp; Un archivo por inspección</div>
        </div>
        <a href="<?= htmlspecialchars($backUrl) ?>" class="btn-back"><?= htmlspecialchars($backLabel) ?></a>
    </div>

    <div class="stats-banner">
        <div class="stat-card">
            <div class="stat-val"><?= $total_archivos ?></div>
            <div class="stat-label">Inspecciones Totales</div>
        </div>
        <div class="stat-card">
            <div class="stat-val good"><?= $total_cumple ?></div>
            <div class="stat-label">Cumplen</div>
        </div>
        <div class="stat-card">
            <div class="stat-val danger"><?= $total_no_cumple ?></div>
            <div class="stat-label">No Cumplen</div>
        </div>
    </div>

    <div class="search-bar">
        <input type="text" class="search-input" id="searchInput"
               placeholder="Buscar por dosificador, microingrediente o fecha..."
               oninput="filtrarTarjetas(this.value)">
        <a href="menu_dosificadores.php?from=<?= urlencode($from) ?>" class="btn-new">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            NUEVA INSPECCIÓN
        </a>
    </div>

    <div class="grid" id="gridContainer">
        <?php if (empty($archivos)): ?>
            <div class="empty-state">
                <h3>Sin Registros</h3>
                <p>Aún no hay inspecciones de dosificadores guardadas para la sede <strong><?= htmlspecialchars($sede) ?></strong>.</p>
                <a href="menu_dosificadores.php?from=<?= urlencode($from) ?>">+ Crear Primera Inspección</a>
            </div>
        <?php else: ?>
            <?php foreach ($archivos as $doc):
                $c = $doc['cumple'];
                $badge_cls = 'na';
                if ($c === 'CUMPLE') $badge_cls = 'good';
                elseif ($c === 'NO CUMPLE') $badge_cls = 'danger';
                $badge_text = $c ?: 'N/D';
            ?>
                <div class="card-wrap"
                     data-search="<?= htmlspecialchars(strtolower($doc['ultima_fecha'] . ' ' . $doc['dosificador'] . ' ' . $doc['microingrediente'])) ?>">
                <button type="button" class="btn-delete-card" title="Eliminar registro"
                        data-filename="<?= htmlspecialchars($doc['filename']) ?>"
                        data-label="<?= htmlspecialchars($doc['dosificador'] . ' — ' . $doc['ultima_fecha']) ?>"
                        onclick="eliminarInspeccion(this)">✕</button>
                <a href="visor_inspeccion_dosificadores.php?file=<?= urlencode($doc['filename']) ?>&from=<?= urlencode($from) ?>"
                   class="file-card">

                    <div class="card-header">
                        <span class="periodo-badge">📅 <?= htmlspecialchars($doc['ultima_fecha']) ?></span>
                        <span class="cumple-badge <?= $badge_cls ?>"><?= htmlspecialchars($badge_text) ?></span>
                    </div>

                    <div>
                        <div class="card-title"><?= htmlspecialchars($doc['dosificador']) ?></div>
                        <div class="card-sub">Microingrediente: <?= htmlspecialchars($doc['microingrediente']) ?></div>
                    </div>

                    <div class="file-meta">
                        <div class="meta-line">
                            <span>Registrado por</span>
                            <span class="meta-val"><?= htmlspecialchars($doc['ultimo_usuario']) ?></span>
                        </div>
                        <div class="meta-line">
                            <span>Guardado</span>
                            <span class="meta-val"><?= $doc['fecha_mod'] ?></span>
                        </div>
                    </div>

                    <div class="btn-view">VER INSPECCIÓN →</div>
                </a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>

<script>
    function filtrarTarjetas(query) {
        const q = query.toLowerCase().trim();
        document.querySelectorAll('.card-wrap').forEach(card => {
            const searchData = card.dataset.search || '';
            card.classList.toggle('hidden-by-search', q !== '' && !searchData.includes(q));
        });
    }

    async function eliminarInspeccion(btn) {
        const filename = btn.dataset.filename;
        const label = btn.dataset.label;

        const confirmacion = await Swal.fire({
            title: '¿Eliminar registro?',
            text: `Se eliminará permanentemente la inspección "${label}". Esta acción no se puede deshacer.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            background: '#151A22', color: '#fff', confirmButtonColor: '#FF3366', cancelButtonColor: '#3a3f4b'
        });

        if (!confirmacion.isConfirmed) return;

        try {
            const resp = await fetch('eliminar_inspeccion.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ file: filename })
            });
            const data = await resp.json();

            if (data.status === 'success') {
                Swal.fire({
                    title: 'Eliminado', icon: 'success', timer: 1200, showConfirmButton: false,
                    background: '#151A22', color: '#fff'
                }).then(() => location.reload());
            } else {
                Swal.fire({ title: 'Error', text: data.message || 'No se pudo eliminar.', icon: 'error', background: '#151A22', color: '#fff', confirmButtonColor: '#FF3366' });
            }
        } catch (e) {
            Swal.fire({ title: 'Error de conexión', icon: 'error', background: '#151A22', color: '#fff', confirmButtonColor: '#FF3366' });
        }
    }
</script>

</body>
</html>
