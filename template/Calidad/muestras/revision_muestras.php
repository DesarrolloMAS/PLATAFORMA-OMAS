<?php
require_once '../../sesion.php';
verificarAutenticacion();

$sede = $_SESSION['sede'];
$sede_san = preg_replace('/[^A-Za-z0-9_-]/', '', $sede);
$sede_dir = "/var/www/fmt/archivos/generados/Calidad/muestras/" . $sede_san . "/";

$dias = [];

if (is_dir($sede_dir)) {
    foreach (glob($sede_dir . '*.json') as $archivo) {
        $periodo   = basename($archivo, '.json');
        $registros = json_decode(file_get_contents($archivo), true) ?: [];

        foreach ($registros as $r) {
            $d = $r['datos'] ?? [];
            $fecha = $d['fecha_registro'] ?? null;
            if (!$fecha) continue;

            if (!isset($dias[$fecha])) {
                $dias[$fecha] = [
                    'fecha'          => $fecha,
                    'periodo'        => $periodo,
                    'total'          => 0,
                    'con_disp'       => 0,
                    'ultimo_usuario' => $r['usuario_sys'] ?? '—',
                ];
            }
            $dias[$fecha]['total']++;
            if (!empty($d['disp_fecha'])) {
                $dias[$fecha]['con_disp']++;
            }
        }
    }
}

$listaDias = array_values($dias);
usort($listaDias, fn($a, $b) => strcmp($b['fecha'], $a['fecha']));

$total_dias  = count($listaDias);
$total_items = array_sum(array_column($listaDias, 'total'));
$total_pend  = array_sum(array_map(fn($d) => $d['total'] - $d['con_disp'], $listaDias));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revisión de Muestras</title>
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

        .stats-banner { display: flex; gap: 20px; margin-bottom: 25px; flex-wrap: wrap; }

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
        .stat-val.warn { color: var(--warning); }
        .stat-label { font-size: 11px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; }

        .toolbar {
            display: flex; gap: 12px; align-items: center; margin-bottom: 25px; flex-wrap: wrap;
        }
        .search-input {
            flex: 1; min-width: 220px;
            background: var(--input-bg); border: 1px solid var(--border-color);
            color: var(--text-main); padding: 12px 16px;
            border-radius: var(--r-sm); font-family: 'Barlow', sans-serif; font-size: 14px;
            transition: all 0.3s;
        }
        .search-input:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 3px rgba(0, 240, 255, 0.1); }
        .search-input::placeholder { color: var(--text-muted); }

        .btn-danger {
            background: transparent; border: 1px solid var(--danger); color: var(--danger);
            padding: 12px 20px; border-radius: var(--r-sm);
            font-family: 'Space Mono', monospace; font-size: 12px; font-weight: 700;
            cursor: pointer; text-transform: uppercase; white-space: nowrap;
            transition: all 0.3s;
        }
        .btn-danger:hover { background: rgba(255,51,102,0.1); box-shadow: 0 0 15px var(--danger-glow); }

        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }

        .day-card {
            background: var(--panel-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--r-md);
            padding: 22px;
            display: flex; flex-direction: column; gap: 14px;
            transition: all 0.3s ease;
            position: relative;
        }
        .day-card:hover { border-color: rgba(0, 240, 255, 0.4); box-shadow: 0 10px 25px rgba(0, 240, 255, 0.08); }
        .day-card.hidden-by-search { display: none; }

        .day-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; }
        .day-check { display: flex; align-items: center; gap: 10px; }
        .day-fecha { font-size: 16px; font-weight: 700; color: #fff; font-family: 'Space Mono', monospace; }

        .badge {
            padding: 4px 10px; border-radius: var(--r-sm);
            font-family: 'Space Mono', monospace; font-size: 10px; font-weight: 700;
            white-space: nowrap;
        }
        .badge.good   { background: rgba(16, 185, 129, 0.15); color: var(--success); border: 1px solid rgba(16,185,129,0.3); }
        .badge.pend   { background: rgba(255, 176, 0, 0.15); color: var(--warning); border: 1px solid rgba(255,176,0,0.3); }

        .day-meta { font-size: 12px; color: var(--text-muted); font-family: 'Space Mono', monospace; }

        .day-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
        .btn-action {
            background: rgba(0,240,255,0.06);
            border: 1px solid rgba(0,240,255,0.25);
            color: var(--accent);
            padding: 9px 10px;
            border-radius: var(--r-sm);
            font-family: 'Space Mono', monospace;
            font-size: 10.5px; font-weight: 700;
            text-align: center; text-decoration: none;
            transition: all 0.2s; cursor: pointer;
        }
        .btn-action:hover { background: rgba(0,240,255,0.14); box-shadow: 0 0 10px rgba(0,240,255,0.12); }

        .empty-state {
            grid-column: 1 / -1;
            background: var(--panel-bg); border: 1px dashed var(--border-color);
            padding: 70px 20px; border-radius: var(--r-md);
            text-align: center; color: var(--text-muted);
        }
        .empty-state h3 { color: #fff; font-size: 20px; margin-bottom: 10px; }
    </style>
</head>
<body>

<div class="container">
    <div class="header-box">
        <div>
            <h1 class="main-title">Revisión de Muestras</h1>
            <div class="sub-title">Sede Operativa: [ <?= htmlspecialchars($sede) ?> ] &nbsp;|&nbsp; Registros por día</div>
        </div>
        <a href="/template/menu_adm_calidad.html" class="btn-back">← Menú Calidad</a>
    </div>

    <div class="stats-banner">
        <div class="stat-card">
            <div class="stat-val"><?= $total_dias ?></div>
            <div class="stat-label">Días Registrados</div>
        </div>
        <div class="stat-card">
            <div class="stat-val"><?= $total_items ?></div>
            <div class="stat-label">Muestras Totales</div>
        </div>
        <div class="stat-card">
            <div class="stat-val warn"><?= $total_pend ?></div>
            <div class="stat-label">Pendientes de Disposición</div>
        </div>
    </div>

    <div class="toolbar">
        <input type="text" class="search-input" id="searchInput" placeholder="Buscar por fecha (YYYY-MM-DD)..." oninput="filtrarTarjetas(this.value)">
        <button class="btn-danger" id="btnEliminarSel">🗑️ Eliminar seleccionados</button>
    </div>

    <div class="grid" id="grid">
        <?php if (empty($listaDias)): ?>
            <div class="empty-state">
                <h3>Sin Registros</h3>
                <p>Aún no hay muestras registradas para la sede <strong><?= htmlspecialchars($sede) ?></strong>.</p>
            </div>
        <?php else: ?>
            <?php foreach ($listaDias as $dia):
                $pendientes = $dia['total'] - $dia['con_disp'];
                $completo = $pendientes === 0;
            ?>
                <div class="day-card" data-search="<?= htmlspecialchars($dia['fecha']) ?>" data-periodo="<?= htmlspecialchars($dia['periodo']) ?>" data-fecha="<?= htmlspecialchars($dia['fecha']) ?>">
                    <div class="day-top">
                        <div class="day-check">
                            <input type="checkbox" class="day-checkbox" data-periodo="<?= htmlspecialchars($dia['periodo']) ?>" data-fecha="<?= htmlspecialchars($dia['fecha']) ?>">
                            <span class="day-fecha">📅 <?= htmlspecialchars($dia['fecha']) ?></span>
                        </div>
                        <span class="badge <?= $completo ? 'good' : 'pend' ?>"><?= $completo ? '✓ COMPLETO' : $pendientes . ' PENDIENTE' . ($pendientes !== 1 ? 'S' : '') ?></span>
                    </div>
                    <div class="day-meta"><?= $dia['total'] ?> muestra<?= $dia['total'] !== 1 ? 's' : '' ?> · registrado por <?= htmlspecialchars($dia['ultimo_usuario']) ?></div>
                    <div class="day-actions">
                        <a class="btn-action" href="editar_dia.php?periodo=<?= urlencode($dia['periodo']) ?>&fecha=<?= urlencode($dia['fecha']) ?>">📂 Corregir</a>
                        <a class="btn-action" href="muestras_form2.php?periodo=<?= urlencode($dia['periodo']) ?>&fecha=<?= urlencode($dia['fecha']) ?>">✏️ Disposición</a>
                        <a class="btn-action" href="imprimir_muestra.php?periodo=<?= urlencode($dia['periodo']) ?>&fecha=<?= urlencode($dia['fecha']) ?>" target="_blank">🖨️ Imprimir</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function filtrarTarjetas(query) {
    const q = query.toLowerCase().trim();
    document.querySelectorAll('.day-card').forEach(card => {
        const searchData = card.dataset.search || '';
        card.classList.toggle('hidden-by-search', q !== '' && !searchData.includes(q));
    });
}

document.getElementById('btnEliminarSel').addEventListener('click', function () {
    const checks = document.querySelectorAll('.day-checkbox:checked');
    if (checks.length === 0) {
        alert('Selecciona al menos un día para eliminar.');
        return;
    }
    if (!confirm(`¿Seguro que deseas eliminar ${checks.length} día(s) completo(s) de muestras? Esta acción no se puede deshacer.`)) return;

    const dias = Array.from(checks).map(cb => ({ periodo: cb.dataset.periodo, fecha: cb.dataset.fecha }));

    fetch('eliminar_dia.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ dias })
    })
    .then(r => r.json())
    .then(data => {
        alert(data.message || (data.status === 'success' ? 'Eliminado correctamente.' : 'Error al eliminar.'));
        if (data.status === 'success') location.reload();
    })
    .catch(() => alert('Error de conexión al eliminar.'));
});

// ============================================
// VERIFICACION DE SESION AJAX 10 SEG
setInterval(function() {
    verificarSesionAjax(function(activa) {});
}, 10000);
function verificarSesionAjax(callback) {
    fetch('/template/verificar_sesion.php')
        .then(response => response.json())
        .then(data => {
            if (data.activa) {
                callback(true);
            } else {
                alert('Tu sesión ha expirado. Por favor, inicia sesión nuevamente.');
                window.location.href = '/index.php?motivo=sesion';
                callback(false);
            }
        })
        .catch(() => callback(false));
}
</script>
</body>
</html>
