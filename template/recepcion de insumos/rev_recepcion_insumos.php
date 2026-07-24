<?php include '../sesion.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REVISIONES - RECEPCIÓN DE INSUMOS</title>
    <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700&family=Space+Mono:ital,wght@0,700;1,400&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --bg-color: #0B0E14;
            --panel-bg: #151A22;
            --accent: #00F0FF;
            --accent-glow: rgba(0,240,255,0.4);
            --text-main: #E2E8F0;
            --text-muted: #94A3B8;
            --border-color: #1E293B;
            --input-bg: #0F172A;
            --success: #10B981;
            --warning: #FFB000;
            --danger: #FF3366;
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
                linear-gradient(rgba(0,240,255,0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0,240,255,0.03) 1px, transparent 1px);
            background-size: 30px 30px;
        }
        .container { max-width: 1100px; margin: 0 auto; }
        .header-box {
            background: var(--panel-bg);
            border: 1px solid var(--border-color);
            border-left: 4px solid var(--accent);
            padding: 28px 30px;
            border-radius: var(--r-md);
            margin-bottom: 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }
        .main-title { font-size: 22px; font-weight: 700; color: #fff; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px; }
        .sub-title { color: var(--text-muted); font-size: 13px; }
        .btn-back {
            background: var(--input-bg);
            border: 1px solid var(--border-color);
            color: var(--text-main);
            padding: 10px 20px;
            border-radius: var(--r-sm);
            font-family: 'Space Mono', monospace;
            text-decoration: none;
            font-size: 13px;
            transition: all 0.3s;
            white-space: nowrap;
        }
        .btn-back:hover { border-color: var(--accent); color: var(--accent); }
        .btn-new {
            background: var(--accent); color: var(--bg-color); border: none;
            padding: 10px 20px; border-radius: var(--r-sm);
            font-family: 'Space Mono', monospace; font-size: 12px; font-weight: 700;
            text-decoration: none; text-transform: uppercase; white-space: nowrap;
            transition: all 0.3s; box-shadow: 0 0 15px var(--accent-glow);
        }
        .btn-new:hover { background: #fff; }

        .filtros-card {
            background: var(--panel-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--r-md);
            padding: 20px 24px;
            margin-bottom: 24px;
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            align-items: flex-end;
        }
        .filtro-group { display: flex; flex-direction: column; gap: 6px; }
        .filtro-label { font-size: 12px; color: var(--text-muted); font-weight: 500; text-transform: uppercase; letter-spacing: 0.3px; }
        .filtro-control {
            background: var(--input-bg);
            border: 1px solid var(--border-color);
            color: var(--text-main);
            padding: 9px 12px;
            border-radius: var(--r-sm);
            font-family: 'Barlow', sans-serif;
            font-size: 13px;
        }
        .filtro-control:focus { outline: none; border-color: var(--accent); }
        input[type="month"].filtro-control { font-family: 'Space Mono', monospace; color: var(--accent); }

        .table-wrap { overflow-x: auto; }
        .rev-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .rev-table thead th {
            background: rgba(0,240,255,0.07);
            color: var(--accent);
            font-family: 'Space Mono', monospace;
            font-size: 11px;
            font-weight: 700;
            padding: 12px 14px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
            white-space: nowrap;
        }
        .rev-table tbody tr { border-bottom: 1px solid var(--border-color); transition: background 0.15s; }
        .rev-table tbody tr:hover { background: rgba(0,240,255,0.03); }
        .rev-table td { padding: 12px 14px; vertical-align: middle; }
        .fecha-chip {
            font-family: 'Space Mono', monospace; font-size: 12px; color: var(--accent);
            background: rgba(0,240,255,0.08); border: 1px solid rgba(0,240,255,0.2);
            padding: 3px 8px; border-radius: var(--r-sm);
        }
        .ts-text { font-size: 12px; color: var(--text-muted); font-family: 'Space Mono', monospace; }
        .btn-ver {
            background: rgba(0,240,255,0.1);
            border: 1px solid rgba(0,240,255,0.35);
            color: var(--accent);
            padding: 7px 14px;
            border-radius: var(--r-sm);
            font-family: 'Space Mono', monospace;
            font-size: 11px;
            text-decoration: none;
            transition: all 0.2s;
            white-space: nowrap;
        }
        .btn-ver:hover { background: rgba(0,240,255,0.2); border-color: var(--accent); }
        .empty-state { text-align: center; padding: 60px 20px; color: var(--text-muted); font-family: 'Space Mono', monospace; font-size: 13px; }
        .count-badge { font-family: 'Space Mono', monospace; font-size: 12px; color: var(--text-muted); margin-bottom: 14px; }
        .count-badge strong { color: var(--accent); }
        .system-status {
            font-family: 'Space Mono', monospace; font-size: 12px; color: var(--text-muted);
            text-align: center; margin-top: 32px; display: flex; justify-content: center; align-items: center; gap: 8px;
        }
        .status-dot { width: 8px; height: 8px; background: #10B981; border-radius: 50%; box-shadow: 0 0 8px #10B981; animation: pulse 2s infinite; }
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(16,185,129,0.4); }
            70% { box-shadow: 0 0 0 10px rgba(16,185,129,0); }
            100% { box-shadow: 0 0 0 0 rgba(16,185,129,0); }
        }
    </style>
</head>
<body>
<div class="container">

    <div class="header-box">
        <div>
            <h1 class="main-title">Revisiones — Recepción de Insumos</h1>
            <div class="sub-title">Historial de registros por sede · <?php echo htmlspecialchars($_SESSION['sede'] ?? ''); ?></div>
        </div>
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <a href="index.html" class="btn-new">+ NUEVA RECEPCIÓN</a>
            <a href="../menu_almacen.html" class="btn-back">← VOLVER</a>
        </div>
    </div>

    <div class="filtros-card">
        <div class="filtro-group">
            <label class="filtro-label">Mes</label>
            <input type="month" id="filtroMes" class="filtro-control">
        </div>
        <div class="filtro-group">
            <label class="filtro-label">Buscar</label>
            <input type="text" id="filtroBuscar" class="filtro-control" placeholder="Proveedor, orden de compra, usuario…" style="min-width:220px;">
        </div>
    </div>

    <div class="count-badge" id="countBadge"></div>

    <div class="table-wrap">
        <table class="rev-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Proveedor</th>
                    <th>Orden de Compra</th>
                    <th>Entrada No.</th>
                    <th>Insumos</th>
                    <th>Registrado</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody id="tbodyRev"></tbody>
        </table>
        <div id="emptyState" class="empty-state" style="display:none;">Sin registros para este filtro</div>
    </div>

    <div class="system-status">
        <div class="status-dot"></div>
        SISTEMA JSON INTERCONECTADO · RECEPCIÓN DE INSUMOS
    </div>
</div>

<script>
    let todosLosRegistros = [];

    async function cargarRegistros() {
        const r = await fetch('listar_jsons.php');
        const data = await r.json();
        todosLosRegistros = data.archivos || [];
        renderTabla(todosLosRegistros);
    }

    function renderTabla(registros) {
        const tbody = document.getElementById('tbodyRev');
        const empty = document.getElementById('emptyState');
        const count = document.getElementById('countBadge');
        tbody.innerHTML = '';

        if (!registros.length) {
            empty.style.display = 'block';
            count.innerHTML = '';
            return;
        }
        empty.style.display = 'none';
        count.innerHTML = `<strong>${registros.length}</strong> registro(s) encontrado(s)`;

        registros.forEach(reg => {
            const tr = document.createElement('tr');
            const fechaFmt = reg.fecha ? reg.fecha.split('-').reverse().join('/') : '—';
            const tsFmt = reg.timestamp ? reg.timestamp.replace('T', ' ').substring(0, 16) : '—';

            tr.innerHTML = `
                <td><span class="fecha-chip">${fechaFmt}</span></td>
                <td>${escHtml(reg.proveedor || '—')}</td>
                <td>${escHtml(reg.orden_compra || '—')}</td>
                <td>${escHtml(reg.entrada_no || '—')}</td>
                <td>${reg.num_insumos ?? 0}</td>
                <td><span class="ts-text">${tsFmt}</span></td>
                <td>
                    <a href="visor_recepcion_insumos.php?id=${encodeURIComponent(reg.id)}&file=${encodeURIComponent(reg.archivo)}"
                       class="btn-ver" target="_blank">VER →</a>
                </td>`;
            tbody.appendChild(tr);
        });
    }

    function escHtml(str) {
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    function aplicarFiltros() {
        const mes    = document.getElementById('filtroMes').value;
        const buscar = document.getElementById('filtroBuscar').value.toLowerCase();

        const filtrado = todosLosRegistros.filter(reg => {
            const mesReg = reg.fecha ? reg.fecha.substring(0, 7) : '';
            if (mes && mesReg !== mes) return false;
            if (buscar) {
                const haystack = [reg.proveedor, reg.orden_compra, reg.usuario].join(' ').toLowerCase();
                if (!haystack.includes(buscar)) return false;
            }
            return true;
        });
        renderTabla(filtrado);
    }

    document.getElementById('filtroMes').addEventListener('change', aplicarFiltros);
    document.getElementById('filtroBuscar').addEventListener('input', aplicarFiltros);

    cargarRegistros();
</script>
</body>
</html>
