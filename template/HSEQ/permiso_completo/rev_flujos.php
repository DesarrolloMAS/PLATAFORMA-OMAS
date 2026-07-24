<?php
require '../../sesion.php';

if (!isset($_SESSION['nombre']) || empty($_SESSION['sede'])) {
    header('Location: ../../../index.php');
    exit;
}

$sede     = $_SESSION['sede'];
$sede_san = preg_replace('/[^A-Za-z0-9_-]/', '', $sede);
$sede_dir = "../../../archivos/generados/flujo_permisos/" . $sede_san . "/";

$flujos = [];
if (file_exists($sede_dir)) {
    foreach (scandir($sede_dir) as $f) {
        if (strtolower(pathinfo($f, PATHINFO_EXTENSION)) !== 'json') continue;
        $arr    = json_decode(file_get_contents($sede_dir . $f), true) ?: [];
        $flujos = array_merge($flujos, $arr);
    }
}
usort($flujos, fn($a, $b) => strcmp($b['timestamp'] ?? '', $a['timestamp'] ?? ''));

$total       = count($flujos);
$completados = count(array_filter($flujos, fn($f) => ($f['estado'] ?? '') === 'completado'));
$en_progreso = $total - $completados;
$este_mes    = count(array_filter($flujos, fn($f) => str_starts_with($f['timestamp'] ?? '', date('Y-m'))));

$empresas = array_unique(array_filter(array_column($flujos, 'empresa')));
sort($empresas);

function e($v) { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
function fmtTs($ts) {
    if (!$ts) return '—';
    return date('d M Y', strtotime($ts));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galería — Permisos de Trabajo</title>
    <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700&family=Space+Mono:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #0B0E14; --panel-bg: #151A22; --accent: #00F0FF;
            --accent-glow: rgba(0,240,255,0.4); --text-main: #E2E8F0;
            --text-muted: #94A3B8; --border-color: #1E293B; --input-bg: #0F172A;
            --danger: #FF3366; --warning: #FFB000; --success: #10B981;
            --r-lg: 12px; --r-md: 8px; --r-sm: 4px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Barlow', sans-serif; background: var(--bg-color);
            color: var(--text-main); min-height: 100vh; padding: 36px 20px;
            background-image:
                radial-gradient(circle at top right, rgba(0,240,255,0.05), transparent 45%),
                linear-gradient(rgba(0,240,255,0.025) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0,240,255,0.025) 1px, transparent 1px);
            background-size: 100% 100%, 32px 32px, 32px 32px;
        }
        .container { max-width: 1340px; margin: 0 auto; }

        /* ── HEADER ── */
        .header-box {
            background: var(--panel-bg); border: 1px solid var(--border-color);
            border-left: 4px solid var(--accent); padding: 28px 32px;
            border-radius: var(--r-md); margin-bottom: 28px;
            display: flex; align-items: center; justify-content: space-between;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5); position: relative; overflow: hidden;
        }
        .header-box::before {
            content: "HSEQ"; position: absolute; top: 18px; right: -28px;
            background: var(--accent); color: var(--bg-color);
            font-family: 'Space Mono', monospace; font-size: 10px; font-weight: 700;
            padding: 4px 38px; transform: rotate(45deg);
            box-shadow: 0 0 10px var(--accent-glow);
        }
        .main-title { font-size: 22px; font-weight: 700; color: #fff; text-transform: uppercase; margin-bottom: 3px; }
        .sub-title   { font-family: 'Space Mono', monospace; font-size: 11px; color: var(--text-muted); }
        .btn-back {
            background: var(--input-bg); border: 1px solid var(--border-color);
            color: var(--text-main); padding: 9px 18px; border-radius: var(--r-sm);
            font-family: 'Space Mono', monospace; text-decoration: none; font-size: 11px;
            transition: all 0.2s; white-space: nowrap;
        }
        .btn-back:hover { border-color: var(--accent); color: var(--accent); }

        /* ── STATS ── */
        .stats-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 26px; }
        .stat-card {
            background: var(--panel-bg); border: 1px solid var(--border-color);
            border-radius: var(--r-md); padding: 18px 22px;
            display: flex; flex-direction: column; gap: 4px; transition: border-color 0.2s;
        }
        .stat-card:hover { border-color: rgba(0,240,255,0.25); }
        .stat-val   { font-family: 'Space Mono', monospace; font-size: 26px; font-weight: 700; color: var(--accent); }
        .stat-val.warn { color: var(--warning); }
        .stat-val.succ { color: var(--success); }
        .stat-label { font-size: 11px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; }

        /* ── FILTERS ── */
        .filter-bar {
            background: var(--panel-bg); border: 1px solid var(--border-color);
            border-radius: var(--r-md); padding: 16px 20px;
            display: flex; gap: 12px; align-items: center;
            flex-wrap: wrap; margin-bottom: 24px;
        }
        .filter-input, .filter-select {
            background: var(--input-bg); border: 1px solid var(--border-color);
            color: var(--text-main); padding: 9px 13px; border-radius: var(--r-sm);
            font-family: 'Barlow', sans-serif; font-size: 13px;
            transition: border-color 0.2s;
        }
        .filter-input { flex: 1; min-width: 220px; }
        .filter-select { min-width: 180px; cursor: pointer; }
        .filter-input:focus, .filter-select:focus { outline: none; border-color: var(--accent); }
        .filter-select option { background: var(--panel-bg); }
        .filter-label { font-family: 'Space Mono', monospace; font-size: 9px; color: var(--text-muted); white-space: nowrap; }
        .results-count { font-family: 'Space Mono', monospace; font-size: 10px; color: var(--text-muted); margin-left: auto; }

        /* ── GRID ── */
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(360px, 1fr)); gap: 18px; }

        /* ── FLUJO CARD ── */
        .flujo-card {
            background: var(--panel-bg); border: 1px solid var(--border-color);
            border-radius: var(--r-md); padding: 22px;
            display: flex; flex-direction: column; gap: 0;
            transition: all 0.3s; position: relative; overflow: hidden;
            text-decoration: none; color: inherit;
        }
        .flujo-card:hover { border-color: rgba(0,240,255,0.35); transform: translateY(-3px); box-shadow: 0 10px 28px rgba(0,240,255,0.07); }
        .flujo-card.completado { border-color: rgba(16,185,129,0.2); }
        .flujo-card.completado:hover { border-color: rgba(16,185,129,0.45); }

        .card-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 8px; margin-bottom: 12px; }
        .card-folio { font-family: 'Space Mono', monospace; font-size: 11px; font-weight: 700; color: var(--accent); }
        .flujo-card.completado .card-folio { color: var(--success); }

        .badge {
            font-family: 'Space Mono', monospace; font-size: 8px; font-weight: 700;
            padding: 2px 8px; border-radius: 99px; white-space: nowrap;
        }
        .badge-progreso { background: rgba(255,176,0,0.12); border: 1px solid rgba(255,176,0,0.35); color: var(--warning); }
        .badge-completo { background: rgba(16,185,129,0.12); border: 1px solid rgba(16,185,129,0.35); color: var(--success); }

        .card-empresa { font-size: 16px; font-weight: 700; color: #fff; margin-bottom: 3px; }
        .card-resp    { font-size: 12px; color: var(--text-muted); margin-bottom: 12px; }

        .card-meta { display: flex; flex-direction: column; gap: 5px; margin-bottom: 14px; }
        .meta-row  { display: flex; justify-content: space-between; font-size: 12px; color: var(--text-muted); }
        .meta-val  { color: var(--text-main); font-family: 'Space Mono', monospace; font-size: 10px; }

        /* ── PROGRESS TRACK ── */
        .progress-track { display: flex; align-items: flex-start; margin-bottom: 14px; }
        .track-step { display: flex; flex-direction: column; align-items: center; flex-shrink: 0; width: 56px; }
        .track-connector { flex: 1; height: 2px; background: var(--border-color); margin-top: 12px; }
        .track-connector.done { background: var(--accent); }
        .track-dot {
            width: 24px; height: 24px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 10px; font-weight: 700; font-family: 'Space Mono', monospace;
        }
        .step-done .track-dot  { background: var(--accent); color: var(--bg-color); box-shadow: 0 0 7px rgba(0,240,255,0.5); }
        .step-pend .track-dot  { background: var(--border-color); border: 2px solid var(--border-color); color: var(--text-muted); }
        .track-label { font-family: 'Space Mono', monospace; font-size: 7px; color: var(--text-muted); text-align: center; margin-top: 3px; text-transform: uppercase; }
        .step-done .track-label { color: var(--accent); }

        /* ── APOYO CHIPS ── */
        .apoyo-chips { display: flex; flex-wrap: wrap; gap: 4px; margin-bottom: 14px; min-height: 0; }
        .apoyo-chip {
            font-family: 'Space Mono', monospace; font-size: 8px; color: var(--success);
            background: rgba(16,185,129,0.07); border: 1px solid rgba(16,185,129,0.2);
            padding: 2px 7px; border-radius: 99px;
        }
        .no-apoyos { font-family: 'Space Mono', monospace; font-size: 8px; color: var(--text-muted); }

        /* ── BTN VER ── */
        .btn-ver {
            margin-top: auto; padding: 10px 16px;
            background: transparent; border: 1px solid var(--border-color);
            color: var(--text-muted); border-radius: var(--r-sm);
            font-family: 'Space Mono', monospace; font-size: 10px; font-weight: 700;
            text-align: center; text-decoration: none; transition: all 0.25s;
            text-transform: uppercase;
        }
        .flujo-card:hover .btn-ver { background: var(--accent); border-color: var(--accent); color: var(--bg-color); box-shadow: 0 0 14px var(--accent-glow); }
        .flujo-card.completado:hover .btn-ver { background: var(--success); border-color: var(--success); box-shadow: 0 0 14px rgba(16,185,129,0.4); }

        /* ── EMPTY ── */
        .empty-state {
            grid-column: 1/-1; background: var(--panel-bg);
            border: 1px dashed var(--border-color); border-radius: var(--r-md);
            padding: 70px 20px; text-align: center; color: var(--text-muted);
        }
        .empty-state h3 { color: #fff; font-size: 18px; margin-bottom: 8px; }
        .empty-btn { background: var(--accent); color: var(--bg-color); padding: 11px 22px; border-radius: var(--r-sm); font-family: 'Space Mono', monospace; font-weight: 700; text-decoration: none; font-size: 12px; display: inline-block; margin-top: 14px; }

        .hidden-card { display: none; }

        @media (max-width: 640px) {
            .stats-row { grid-template-columns: 1fr 1fr; }
        }
    </style>
</head>
<body>
<div class="container">

    <div class="header-box">
        <div>
            <div class="main-title">Galería de Permisos de Trabajo</div>
            <div class="sub-title">SEDE OPERATIVA: [ <?= e($sede) ?> ] &nbsp;·&nbsp; FLUJOS MULTI-FORMATO</div>
        </div>
        <a href="index.html" class="btn-back">← Menú Permisos</a>
    </div>

    <div class="stats-row">
        <div class="stat-card"><div class="stat-val"><?= $total ?></div><div class="stat-label">Permisos Total</div></div>
        <div class="stat-card"><div class="stat-val warn"><?= $en_progreso ?></div><div class="stat-label">En Progreso</div></div>
        <div class="stat-card"><div class="stat-val succ"><?= $completados ?></div><div class="stat-label">Completados</div></div>
        <div class="stat-card"><div class="stat-val"><?= $este_mes ?></div><div class="stat-label">Este Mes</div></div>
    </div>

    <div class="filter-bar">
        <span class="filter-label">// FILTROS</span>
        <input type="text" class="filter-input" id="inputBuscar" placeholder="Buscar por empresa, responsable, tipo, área, folio..." oninput="filtrar()">
        <select class="filter-select" id="selEmpresa" onchange="filtrar()">
            <option value="">— Todas las empresas —</option>
            <?php foreach ($empresas as $emp): ?>
            <option value="<?= e(strtolower($emp)) ?>"><?= e($emp) ?></option>
            <?php endforeach; ?>
        </select>
        <select class="filter-select" id="selEstado" onchange="filtrar()">
            <option value="">— Todos los estados —</option>
            <option value="en_progreso">En Progreso</option>
            <option value="completado">Completado</option>
        </select>
        <span class="results-count" id="resultCount"><?= $total ?> registro(s)</span>
    </div>

    <div class="grid" id="grid">
        <?php if (empty($flujos)): ?>
        <div class="empty-state">
            <h3>Sin Permisos Registrados</h3>
            <p>No hay flujos de permisos para la sede <strong><?= e($sede) ?></strong>.</p>
            <a href="index.html" class="empty-btn">Ir al Menú de Permisos →</a>
        </div>
        <?php else: ?>
        <?php foreach ($flujos as $fl):
            $estado    = $fl['estado'] ?? 'en_progreso';
            $completado = $estado === 'completado';
            $p1 = !empty($fl['pasos']['permiso']['completado']);
            $p2 = !empty($fl['pasos']['analisis']['completado']);
            $p3 = !empty($fl['pasos']['inspeccion']['completado']);
            $apoyos = $fl['apoyos'] ?? [];
            $apoyoLabels = [
                'alturas' => '🦺 Alturas', 'confinados' => '🛢️ Confinados',
                'caliente' => '🔥 Caliente', 'electrico' => '⚡ Eléctrico',
                'energizadas' => '🔴 Energizadas', 'izaje' => '🏗️ Izaje',
            ];
            $searchStr = strtolower(implode(' ', [
                $fl['folio'] ?? '', $fl['empresa'] ?? '', $fl['responsable'] ?? '',
                $fl['tipo_trabajo'] ?? '', $fl['area'] ?? '', $estado,
            ]));
        ?>
        <a href="visor_flujo.php?id=<?= urlencode($fl['id_flujo']) ?>"
           class="flujo-card <?= $completado ? 'completado' : '' ?>"
           data-search="<?= e($searchStr) ?>"
           data-estado="<?= e($estado) ?>"
           data-empresa="<?= e(strtolower($fl['empresa'] ?? '')) ?>">

            <div class="card-top">
                <span class="card-folio"><?= e($fl['folio'] ?? '—') ?></span>
                <span class="badge <?= $completado ? 'badge-completo' : 'badge-progreso' ?>">
                    <?= $completado ? '✓ COMPLETADO' : '● EN PROGRESO' ?>
                </span>
            </div>

            <div class="card-empresa"><?= e($fl['empresa'] ?? '—') ?></div>
            <div class="card-resp"><?= e($fl['responsable'] ?? '—') ?></div>

            <div class="card-meta">
                <div class="meta-row"><span>Tipo de trabajo</span><span class="meta-val"><?= e($fl['tipo_trabajo'] ?? '—') ?></span></div>
                <div class="meta-row"><span>Área</span><span class="meta-val"><?= e($fl['area'] ?? '—') ?></span></div>
                <div class="meta-row"><span>Fecha inicio</span><span class="meta-val"><?= e($fl['fecha_inicio'] ?? '—') ?></span></div>
                <div class="meta-row"><span>Creado por</span><span class="meta-val"><?= e($fl['usuario_sys'] ?? '—') ?></span></div>
            </div>

            <div class="progress-track">
                <div class="track-step <?= $p1 ? 'step-done' : 'step-pend' ?>">
                    <div class="track-dot"><?= $p1 ? '✓' : '01' ?></div>
                    <div class="track-label">Permiso</div>
                </div>
                <div class="track-connector <?= $p1 ? 'done' : '' ?>"></div>
                <div class="track-step <?= $p2 ? 'step-done' : 'step-pend' ?>">
                    <div class="track-dot"><?= $p2 ? '✓' : '02' ?></div>
                    <div class="track-label">ATS</div>
                </div>
                <div class="track-connector <?= $p2 ? 'done' : '' ?>"></div>
                <div class="track-step <?= $p3 ? 'step-done' : 'step-pend' ?>">
                    <div class="track-dot"><?= $p3 ? '✓' : '03' ?></div>
                    <div class="track-label">Inspección</div>
                </div>
            </div>

            <div class="apoyo-chips">
                <?php if (empty($apoyos)): ?>
                    <span class="no-apoyos">Sin certificados de apoyo adjuntos</span>
                <?php else: ?>
                    <?php foreach ($apoyos as $ap): ?>
                        <span class="apoyo-chip"><?= e($apoyoLabels[$ap['key']] ?? ('📋 ' . $ap['key'])) ?></span>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <span class="btn-ver">VER EXPEDIENTE COMPLETO →</span>
        </a>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>

<script>
function filtrar() {
    const q       = document.getElementById('inputBuscar').value.toLowerCase().trim();
    const empresa = document.getElementById('selEmpresa').value;
    const estado  = document.getElementById('selEstado').value;
    let visible = 0;

    document.querySelectorAll('.flujo-card').forEach(card => {
        const matchQ       = !q       || (card.dataset.search || '').includes(q);
        const matchEmpresa = !empresa || (card.dataset.empresa || '') === empresa;
        const matchEstado  = !estado  || (card.dataset.estado  || '') === estado;
        const show = matchQ && matchEmpresa && matchEstado;
        card.classList.toggle('hidden-card', !show);
        if (show) visible++;
    });

    document.getElementById('resultCount').textContent = visible + ' registro(s)';
}
</script>
</body>
</html>
