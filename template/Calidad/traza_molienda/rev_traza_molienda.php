<?php include '../../sesion.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REVISIONES - TRAZABILIDAD MOLIENDA</title>
    <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700&family=Space+Mono:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
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

        /* FILTROS */
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
            transition: border-color 0.2s;
        }
        .filtro-control:focus { outline: none; border-color: var(--accent); }
        input[type="month"].filtro-control { font-family: 'Space Mono', monospace; color: var(--accent); }

        /* TABLA */
        .table-wrap { overflow-x: auto; }
        .rev-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
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
            letter-spacing: 0.3px;
        }
        .rev-table tbody tr {
            border-bottom: 1px solid var(--border-color);
            transition: background 0.15s;
        }
        .rev-table tbody tr:hover { background: rgba(0,240,255,0.03); }
        .rev-table td { padding: 12px 14px; vertical-align: middle; }
        .fecha-chip {
            font-family: 'Space Mono', monospace;
            font-size: 12px;
            color: var(--accent);
            background: rgba(0,240,255,0.08);
            border: 1px solid rgba(0,240,255,0.2);
            padding: 3px 8px;
            border-radius: var(--r-sm);
        }
        .dia-badge {
            font-size: 11px;
            color: var(--text-muted);
            background: rgba(255,255,255,0.04);
            border: 1px solid var(--border-color);
            padding: 2px 8px;
            border-radius: 99px;
        }
        .ts-text { font-size: 12px; color: var(--text-muted); font-family: 'Space Mono', monospace; }
        .usuario-text { font-size: 13px; color: var(--text-main); }
        .btn-ver {
            background: rgba(0,240,255,0.1);
            border: 1px solid rgba(0,240,255,0.35);
            color: var(--accent);
            padding: 7px 14px;
            border-radius: var(--r-sm);
            font-family: 'Space Mono', monospace;
            font-size: 11px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
            white-space: nowrap;
        }
        .btn-ver:hover { background: rgba(0,240,255,0.2); border-color: var(--accent); }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-muted);
            font-family: 'Space Mono', monospace;
            font-size: 13px;
        }
        .empty-state svg { margin-bottom: 16px; opacity: 0.3; }
        .count-badge {
            font-family: 'Space Mono', monospace;
            font-size: 12px;
            color: var(--text-muted);
            margin-bottom: 14px;
        }
        .count-badge strong { color: var(--accent); }
        .system-status {
            font-family: 'Space Mono', monospace;
            font-size: 12px;
            color: var(--text-muted);
            text-align: center;
            margin-top: 32px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
        }
        .status-dot {
            width: 8px; height: 8px;
            background: #10B981;
            border-radius: 50%;
            box-shadow: 0 0 8px #10B981;
            animation: pulse 2s infinite;
        }
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
            <h1 class="main-title">Revisiones — Trazabilidad de Molienda</h1>
            <div class="sub-title">Historial de registros por sede · <?php echo htmlspecialchars($_SESSION['sede'] ?? ''); ?></div>
        </div>
        <a href="/template/menu_revisiones_formatos.html" class="btn-back">← VOLVER</a>
    </div>

    <!-- FILTROS -->
    <div class="filtros-card">
        <div class="filtro-group">
            <label class="filtro-label">Mes</label>
            <input type="month" id="filtroMes" class="filtro-control">
        </div>
        <div class="filtro-group">
            <label class="filtro-label">Buscar</label>
            <input type="text" id="filtroBuscar" class="filtro-control" placeholder="Fecha, usuario, día…" style="min-width:220px;">
        </div>
    </div>

    <div class="count-badge" id="countBadge"></div>

    <div class="table-wrap">
        <table class="rev-table">
            <thead>
                <tr>
                    <th>Fecha Registro</th>
                    <th>Día</th>
                    <th>Registrado</th>
                    <th>Usuario</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody id="tbodyRev"></tbody>
        </table>
        <div id="emptyState" class="empty-state" style="display:none;">
            <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z"/></svg>
            <div>Sin registros para este filtro</div>
        </div>
    </div>

    <div class="system-status">
        <div class="status-dot"></div>
        SISTEMA JSON INTERCONECTADO · TRAZABILIDAD DE MOLIENDA
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
            const fechaFmt = reg.fecha
                ? reg.fecha.split('-').reverse().join('/')
                : '—';
            const tsFmt = reg.timestamp
                ? reg.timestamp.replace('T', ' ').substring(0, 16)
                : '—';

            tr.innerHTML = `
                <td><span class="fecha-chip">${fechaFmt}</span></td>
                <td><span class="dia-badge">${reg.dia || '—'}</span></td>
                <td><span class="ts-text">${tsFmt}</span></td>
                <td><span class="usuario-text">${escHtml(reg.usuario || '—')}</span></td>
                <td>
                    <a href="visor_traza_molienda.php?id=${encodeURIComponent(reg.id)}&file=${encodeURIComponent(reg.archivo)}"
                       class="btn-ver" target="_blank">VER →</a>
                </td>`;
            tbody.appendChild(tr);
        });
    }

    function escHtml(str) {
        return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    function aplicarFiltros() {
        const mes    = document.getElementById('filtroMes').value;       // "YYYY-MM"
        const buscar = document.getElementById('filtroBuscar').value.toLowerCase();

        const filtrado = todosLosRegistros.filter(reg => {
            const mesReg = reg.fecha ? reg.fecha.substring(0, 7) : '';
            if (mes && mesReg !== mes) return false;
            if (buscar) {
                const haystack = [reg.fecha, reg.dia, reg.usuario, reg.timestamp].join(' ').toLowerCase();
                if (!haystack.includes(buscar)) return false;
            }
            return true;
        });
        renderTabla(filtrado);
    }

    // Mes por defecto = mes actual
    const hoy = new Date();
    document.getElementById('filtroMes').value =
        hoy.getFullYear() + '-' + String(hoy.getMonth() + 1).padStart(2, '0');

    document.getElementById('filtroMes').addEventListener('change', aplicarFiltros);
    document.getElementById('filtroBuscar').addEventListener('input', aplicarFiltros);

    cargarRegistros();
</script>
</body>
</html>
