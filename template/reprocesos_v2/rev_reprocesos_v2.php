<?php
require '../sesion.php';

if (!isset($_SESSION['nombre']) || empty($_SESSION['sede'])) {
    header('Location: ../../index.php');
    exit;
}

$sede = $_SESSION['sede'];
$sede_saneada = preg_replace('/[^A-Za-z0-9_-]/', '', $sede);
$nombre_archivo = "REPROCESOS_" . $sede_saneada . ".json";
$archivo_json = "../../archivos/generados/reprocesos_v2/" . $nombre_archivo;

$registros = [];
if (file_exists($archivo_json)) {
    $registros = json_decode(file_get_contents($archivo_json), true) ?: [];
}

usort($registros, fn($a, $b) => strtotime($b['timestamp'] ?? '') <=> strtotime($a['timestamp'] ?? ''));

$total_registros = count($registros);
$pendientes = array_filter($registros, fn($r) => ($r['datos']['estado'] ?? '') === 'pendiente');
$completados = array_filter($registros, fn($r) => ($r['datos']['estado'] ?? '') === 'completado');
$total_kg_pendiente = array_sum(array_map(fn($r) => floatval($r['datos']['cantidad'] ?? 0), $pendientes));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revisiones - Control de Reprocesos</title>
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
        .stat-val.warn { color: var(--warning); }
        .stat-val.good { color: var(--success); }
        .stat-label { font-size: 11px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; }

        .search-bar { margin-bottom: 25px; display: flex; gap: 12px; align-items: center; flex-wrap: wrap; }
        .search-input {
            flex: 1; min-width: 220px;
            background: var(--input-bg); border: 1px solid var(--border-color);
            color: var(--text-main); padding: 12px 16px;
            border-radius: var(--r-sm); font-family: 'Barlow', sans-serif; font-size: 14px;
            transition: all 0.3s;
        }
        .search-input:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 3px rgba(0, 240, 255, 0.1); }
        .search-input::placeholder { color: var(--text-muted); }

        .filter-tabs { display: flex; gap: 8px; }
        .filter-tab {
            background: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-muted);
            padding: 11px 18px; border-radius: var(--r-sm); font-family: 'Space Mono', monospace;
            font-size: 12px; font-weight: 700; text-transform: uppercase; cursor: pointer; transition: all 0.3s;
        }
        .filter-tab.active { background: var(--accent); color: var(--bg-color); border-color: var(--accent); }

        .btn-new {
            background: var(--accent); color: var(--bg-color); border: none;
            padding: 12px 20px; border-radius: var(--r-sm);
            font-family: 'Space Mono', monospace; font-size: 12px; font-weight: 700;
            text-decoration: none; text-transform: uppercase; white-space: nowrap;
            transition: all 0.3s; box-shadow: 0 0 15px var(--accent-glow);
            display: flex; align-items: center; gap: 8px;
        }
        .btn-new:hover { background: #fff; box-shadow: 0 0 25px rgba(255,255,255,0.4); }

        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px; }

        .file-card {
            background: var(--panel-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--r-md);
            padding: 25px;
            display: flex; flex-direction: column; gap: 15px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .file-card::after {
            content: ''; position: absolute; bottom: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--accent), transparent);
            opacity: 0; transition: opacity 0.3s;
        }
        .file-card:hover { border-color: rgba(0, 240, 255, 0.4); transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0, 240, 255, 0.08); }
        .file-card:hover::after { opacity: 1; }

        .card-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; }

        .estado-badge {
            padding: 4px 10px; border-radius: var(--r-sm); font-family: 'Space Mono', monospace;
            font-size: 11px; font-weight: 700; white-space: nowrap; text-transform: uppercase;
        }
        .estado-badge.pendiente { background: rgba(255, 176, 0, 0.15); color: var(--warning); border: 1px solid rgba(255,176,0,0.3); }
        .estado-badge.completado { background: rgba(16, 185, 129, 0.15); color: var(--success); border: 1px solid rgba(16,185,129,0.3); }

        .lote-badge {
            background: rgba(0, 240, 255, 0.1); color: var(--accent); border: 1px solid rgba(0, 240, 255, 0.2);
            padding: 4px 10px; border-radius: var(--r-sm); font-family: 'Space Mono', monospace;
            font-size: 11px; font-weight: 700; white-space: nowrap;
        }

        .card-title { color: #fff; font-size: 17px; font-weight: 600; line-height: 1.4; }
        .card-sub { color: var(--text-muted); font-size: 13px; }

        .file-meta { display: flex; flex-direction: column; gap: 7px; border-top: 1px dashed var(--border-color); padding-top: 14px; margin-top: auto; }
        .meta-line { display: flex; justify-content: space-between; font-size: 13px; color: var(--text-muted); }
        .meta-val { color: #fff; font-family: 'Space Mono', monospace; font-size: 11px; text-align: right; max-width: 55%; word-break: break-word; }

        .btn-view, .btn-ejecutar {
            background: transparent; color: var(--accent); border: 1px solid var(--accent); padding: 10px;
            border-radius: var(--r-sm); text-align: center; font-family: 'Space Mono', monospace;
            font-weight: 700; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;
            transition: all 0.3s; text-decoration: none; cursor: pointer; width: 100%;
        }
        .file-card:hover .btn-view { background: var(--accent); color: var(--bg-color); box-shadow: 0 0 15px var(--accent-glow); }
        .btn-ejecutar { color: var(--warning); border-color: var(--warning); }
        .btn-ejecutar:hover { background: var(--warning); color: var(--bg-color); box-shadow: 0 0 15px rgba(255,176,0,0.4); }

        .empty-state {
            grid-column: 1 / -1;
            background: var(--panel-bg); border: 1px dashed var(--border-color);
            padding: 70px 20px; border-radius: var(--r-md); text-align: center; color: var(--text-muted);
        }
        .empty-state h3 { color: #fff; font-size: 20px; margin-bottom: 10px; }
        .empty-state p { margin-bottom: 25px; }
        .empty-state a {
            background: var(--accent); color: var(--bg-color); padding: 12px 24px;
            border-radius: var(--r-sm); font-family: 'Space Mono', monospace;
            font-weight: 700; text-decoration: none; font-size: 13px; display: inline-block;
        }

        .file-card.hidden-by-search, .file-card.hidden-by-filter { display: none; }

        /* ── MODAL DE EJECUCIÓN ── */
        .modal-overlay {
            display: none;
            position: fixed; inset: 0; background: rgba(0,0,0,0.7);
            z-index: 100; align-items: center; justify-content: center; padding: 20px;
        }
        .modal-overlay.open { display: flex; }
        .modal-box {
            background: var(--panel-bg); border: 1px solid var(--border-color); border-radius: var(--r-md);
            max-width: 520px; width: 100%; max-height: 90vh; overflow-y: auto; padding: 25px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.6);
        }
        .modal-title { color: #fff; font-size: 18px; font-weight: 700; margin-bottom: 4px; text-transform: uppercase; }
        .modal-sub { color: var(--text-muted); font-size: 13px; margin-bottom: 20px; }
        .modal-box .form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; }
        .modal-box .form-label { font-size: 12px; color: var(--text-muted); }
        .modal-box .form-control {
            background: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-main);
            padding: 10px 12px; border-radius: var(--r-sm); font-family: 'Barlow', sans-serif; font-size: 14px; width: 100%;
        }
        .modal-box .form-control:focus { outline: none; border-color: var(--accent); }
        .firma-canvas { background: #fff; border-radius: var(--r-sm); width: 100%; height: 120px; touch-action: none; cursor: crosshair; }
        .firma-actions { display: flex; gap: 8px; margin-top: 6px; }
        .firma-actions button {
            background: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-muted);
            padding: 6px 12px; border-radius: var(--r-sm); font-size: 11px; cursor: pointer; font-family: 'Space Mono', monospace;
        }
        .modal-actions { display: flex; gap: 10px; margin-top: 20px; }
        .modal-actions .btn-cancel {
            flex: 1; background: transparent; border: 1px solid var(--border-color); color: var(--text-muted);
            padding: 12px; border-radius: var(--r-sm); cursor: pointer; font-family: 'Space Mono', monospace; font-size: 13px;
        }
        .modal-actions .btn-confirm {
            flex: 2; background: var(--accent); border: none; color: var(--bg-color);
            padding: 12px; border-radius: var(--r-sm); cursor: pointer; font-family: 'Space Mono', monospace;
            font-weight: 700; text-transform: uppercase; font-size: 13px;
        }
    </style>
</head>
<body>

<div class="container">

    <div class="header-box">
        <div>
            <h1 class="main-title">Control de Reprocesos</h1>
            <div class="sub-title">Sede Operativa: [ <?= htmlspecialchars($sede) ?> ] &nbsp;|&nbsp; Archivo corriente por sede</div>
        </div>
        <a href="../menu_produccion.html" class="btn-back">← Menú Producción</a>
    </div>

    <div class="stats-banner">
        <div class="stat-card">
            <div class="stat-val"><?= $total_registros ?></div>
            <div class="stat-label">Registros Totales</div>
        </div>
        <div class="stat-card">
            <div class="stat-val warn"><?= count($pendientes) ?></div>
            <div class="stat-label">Pendientes</div>
        </div>
        <div class="stat-card">
            <div class="stat-val good"><?= count($completados) ?></div>
            <div class="stat-label">Completados</div>
        </div>
        <div class="stat-card">
            <div class="stat-val warn"><?= number_format($total_kg_pendiente, 1) ?></div>
            <div class="stat-label">KG Pendientes por Reprocesar</div>
        </div>
    </div>

    <div class="search-bar">
        <div class="filter-tabs">
            <button class="filter-tab" data-filter="todos">Todos</button>
            <button class="filter-tab active" data-filter="pendiente">Pendientes</button>
            <button class="filter-tab" data-filter="completado">Completados</button>
        </div>
        <input type="text" class="search-input" id="searchInput"
               placeholder="Buscar por lote o producto..."
               oninput="filtrarTarjetas()">
    </div>

    <div class="grid" id="gridContainer">
        <?php if (empty($registros)): ?>
            <div class="empty-state">
                <h3>Sin Registros</h3>
                <p>Aún no hay reprocesos registrados para la sede <strong><?= htmlspecialchars($sede) ?></strong>. Los pendientes se registran desde el menú de Almacén.</p>
            </div>
        <?php else: ?>
            <?php foreach ($registros as $reg):
                $d = $reg['datos'];
                $estado = $d['estado'] ?? 'pendiente';
            ?>
                <div class="file-card"
                     data-estado="<?= htmlspecialchars($estado) ?>"
                     data-search="<?= htmlspecialchars(strtolower(($d['lote'] ?? '') . ' ' . ($d['producto'] ?? ''))) ?>">

                    <div class="card-header">
                        <span class="lote-badge">🏷 <?= htmlspecialchars($d['lote'] ?? '—') ?></span>
                        <span class="estado-badge <?= htmlspecialchars($estado) ?>"><?= htmlspecialchars($estado) ?></span>
                    </div>

                    <div>
                        <div class="card-title"><?= htmlspecialchars($d['producto'] ?? 'Sin producto') ?></div>
                        <div class="card-sub"><?= htmlspecialchars(number_format(floatval($d['cantidad'] ?? 0), 1)) ?> KG · <?= htmlspecialchars($d['fecha_alistamiento'] ?? '—') ?></div>
                    </div>

                    <div class="file-meta">
                        <div class="meta-line"><span>Motivo</span><span class="meta-val"><?= htmlspecialchars($d['motivo'] ?? '—') ?></span></div>
                        <div class="meta-line"><span>Alistado por</span><span class="meta-val"><?= htmlspecialchars($d['responsable_alistamiento'] ?? '—') ?></span></div>
                        <?php if ($estado === 'completado' && !empty($d['ejecucion'])): ?>
                        <div class="meta-line"><span>Ejecutado por</span><span class="meta-val"><?= htmlspecialchars($d['ejecucion']['responsable_ejecucion'] ?? '—') ?></span></div>
                        <?php endif; ?>
                    </div>

                    <?php if ($estado === 'pendiente'): ?>
                        <button class="btn-ejecutar"
                                onclick="abrirModal('<?= htmlspecialchars($reg['id_registro'], ENT_QUOTES) ?>', '<?= htmlspecialchars($d['producto'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($d['lote'] ?? '', ENT_QUOTES) ?>', <?= floatval($d['cantidad'] ?? 0) ?>)">
                            ⚙ EJECUTAR REPROCESO
                        </button>
                    <?php else: ?>
                        <a class="btn-view" href="visor_reprocesos_v2.php?file=<?= urlencode($nombre_archivo) ?>&id=<?= urlencode($reg['id_registro']) ?>">VER DOCUMENTO →</a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>

<!-- ══ MODAL EJECUCIÓN ══ -->
<div class="modal-overlay" id="modalEjecucion">
    <div class="modal-box">
        <div class="modal-title">Ejecutar Reproceso</div>
        <div class="modal-sub" id="modalSub">—</div>

        <form id="formEjecucion">
            <input type="hidden" id="mod_id_registro" name="id_registro">

            <div class="form-group">
                <label class="form-label">Fecha de Reproceso</label>
                <input type="date" name="fecha" id="mod_fecha" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Referencia de Producto</label>
                <input type="text" name="referencia" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Hora de Inicio</label>
                <input type="time" name="hora_inicio" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Hora Final</label>
                <input type="time" name="hora_fin" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Cantidad Procesada (KG)</label>
                <input type="number" step="any" min="0" name="cantidad_procesada" id="mod_cantidad" class="form-control" required>
                <span style="font-size:11px; color:var(--text-muted);">Si procesas menos de lo solicitado, el saldo queda como un pendiente nuevo.</span>
            </div>
            <div class="form-group">
                <label class="form-label">Responsable de Ejecución</label>
                <input type="text" name="responsable_ejecucion" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Firma</label>
                <canvas class="firma-canvas" id="firmaCanvas"></canvas>
                <div class="firma-actions">
                    <button type="button" id="btnLimpiarFirma">Limpiar firma</button>
                </div>
                <input type="hidden" name="firma" id="mod_firma">
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="cerrarModal()">Cancelar</button>
                <button type="submit" class="btn-confirm" id="btnConfirmarEjecucion">Cerrar Reproceso</button>
            </div>
        </form>
    </div>
</div>

<script>
    function filtrarTarjetas() {
        const q = document.getElementById('searchInput').value.toLowerCase().trim();
        const filtroActivo = document.querySelector('.filter-tab.active').dataset.filter;
        document.querySelectorAll('.file-card').forEach(card => {
            const searchData = card.dataset.search || '';
            const estado = card.dataset.estado || '';
            const pasaBusqueda = q === '' || searchData.includes(q);
            const pasaFiltro = filtroActivo === 'todos' || estado === filtroActivo;
            card.classList.toggle('hidden-by-search', !pasaBusqueda);
            card.classList.toggle('hidden-by-filter', !pasaFiltro);
        });
    }

    document.addEventListener('DOMContentLoaded', filtrarTarjetas);

    document.querySelectorAll('.filter-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            filtrarTarjetas();
        });
    });

    // ── MODAL + FIRMA ──
    let ctx, dibujando = false;

    function abrirModal(idRegistro, producto, lote, cantidad) {
        document.getElementById('mod_id_registro').value = idRegistro;
        document.getElementById('modalSub').textContent = producto + ' · Lote ' + lote + ' · ' + cantidad + ' KG pendientes';
        document.getElementById('mod_cantidad').max = cantidad;
        document.getElementById('mod_cantidad').value = cantidad;
        document.getElementById('mod_fecha').value = new Date().toISOString().split('T')[0];
        document.getElementById('modalEjecucion').classList.add('open');

        const canvas = document.getElementById('firmaCanvas');
        const rect = canvas.getBoundingClientRect();
        canvas.width = rect.width;
        canvas.height = rect.height;
        ctx = canvas.getContext('2d');
        ctx.lineWidth = 2;
        ctx.lineCap = 'round';
        ctx.strokeStyle = '#000';
    }

    function cerrarModal() {
        document.getElementById('modalEjecucion').classList.remove('open');
        document.getElementById('formEjecucion').reset();
        document.getElementById('mod_firma').value = '';
    }

    function posicion(e, canvas) {
        const rect = canvas.getBoundingClientRect();
        const t = e.touches ? e.touches[0] : e;
        return { x: t.clientX - rect.left, y: t.clientY - rect.top };
    }

    const firmaCanvas = document.getElementById('firmaCanvas');
    firmaCanvas.addEventListener('mousedown', e => { dibujando = true; const p = posicion(e, firmaCanvas); ctx.beginPath(); ctx.moveTo(p.x, p.y); });
    firmaCanvas.addEventListener('mouseup', () => dibujando = false);
    firmaCanvas.addEventListener('mouseleave', () => dibujando = false);
    firmaCanvas.addEventListener('mousemove', e => { if (!dibujando) return; const p = posicion(e, firmaCanvas); ctx.lineTo(p.x, p.y); ctx.stroke(); });
    firmaCanvas.addEventListener('touchstart', e => { dibujando = true; const p = posicion(e, firmaCanvas); ctx.beginPath(); ctx.moveTo(p.x, p.y); });
    firmaCanvas.addEventListener('touchend', () => dibujando = false);
    firmaCanvas.addEventListener('touchmove', e => { e.preventDefault(); if (!dibujando) return; const p = posicion(e, firmaCanvas); ctx.lineTo(p.x, p.y); ctx.stroke(); });

    document.getElementById('btnLimpiarFirma').addEventListener('click', () => {
        ctx.clearRect(0, 0, firmaCanvas.width, firmaCanvas.height);
    });

    document.getElementById('formEjecucion').addEventListener('submit', function(e) {
        e.preventDefault();

        document.getElementById('mod_firma').value = firmaCanvas.toDataURL();

        const btn = document.getElementById('btnConfirmarEjecucion');
        btn.disabled = true;
        btn.textContent = 'PROCESANDO...';

        const formData = new FormData(this);
        const jsonData = {};
        formData.forEach((value, key) => { jsonData[key] = value; });

        fetch('ejecutar_reproceso.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(jsonData)
        })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({
                    title: '¡REPROCESO CERRADO!', text: data.message || '', icon: 'success',
                    background: '#151A22', color: '#fff', confirmButtonColor: '#00F0FF'
                }).then(() => window.location.reload());
            } else {
                Swal.fire({
                    title: 'No se pudo cerrar', text: data.message || 'Error desconocido.', icon: 'error',
                    background: '#151A22', color: '#fff', confirmButtonColor: '#FF3366'
                }).then(() => window.location.reload());
            }
        })
        .catch(() => {
            Swal.fire({ title: 'Error de conexión', icon: 'error', background: '#151A22', color: '#fff', confirmButtonColor: '#FF3366' });
            btn.disabled = false;
            btn.textContent = 'Cerrar Reproceso';
        });
    });
</script>

</body>
</html>
