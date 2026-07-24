<?php include '../../sesion.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galería — Análisis de Trabajo Seguro</title>
    <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700&family=Space+Mono:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #0B0E14; --panel-bg: #151A22; --accent: #00F0FF;
            --accent-glow: rgba(0,240,255,0.4); --text-main: #E2E8F0;
            --text-muted: #94A3B8; --border-color: #1E293B; --input-bg: #0F172A;
            --danger: #FF3366; --success: #10B981; --r-md: 8px; --r-sm: 4px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Barlow', sans-serif; background: var(--bg-color);
            color: var(--text-main); min-height: 100vh; padding: 40px 20px;
            background-image:
                linear-gradient(rgba(0,240,255,0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0,240,255,0.03) 1px, transparent 1px);
            background-size: 30px 30px;
        }
        .container { max-width: 1100px; margin: 0 auto; }
        .header-box {
            background: var(--panel-bg); border: 1px solid var(--border-color);
            border-left: 4px solid var(--accent); padding: 28px;
            border-radius: var(--r-md); margin-bottom: 28px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5); position: relative; overflow: hidden;
        }
        .header-box::before {
            content: "ATS"; position: absolute; top: 16px; right: -24px;
            background: var(--accent); color: var(--bg-color);
            font-family: 'Space Mono', monospace; font-size: 11px; font-weight: 700;
            padding: 4px 38px; transform: rotate(45deg); box-shadow: 0 0 10px var(--accent-glow);
        }
        .header-nav { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
        .btn-back {
            background: var(--input-bg); border: 1px solid var(--border-color);
            color: var(--text-main); padding: 9px 18px; border-radius: var(--r-sm);
            font-family: 'Space Mono', monospace; text-decoration: none; font-size: 13px; transition: all 0.3s;
        }
        .btn-back:hover { border-color: var(--accent); color: var(--accent); background: rgba(0,240,255,0.05); }
        .main-title { font-size: 22px; font-weight: 700; color: #fff; text-transform: uppercase; letter-spacing: 1px; }
        .sub-title { color: var(--text-muted); font-size: 13px; margin-top: 4px; }
        .filtros {
            background: var(--panel-bg); border: 1px solid var(--border-color);
            border-radius: var(--r-md); padding: 20px; margin-bottom: 20px;
            display: flex; gap: 14px; flex-wrap: wrap; align-items: flex-end;
        }
        .filtros label { font-size: 12px; color: var(--text-muted); display: block; margin-bottom: 6px; text-transform: uppercase; }
        .form-control {
            background: var(--input-bg); border: 1px solid var(--border-color);
            color: var(--text-main); padding: 10px 13px; border-radius: var(--r-sm);
            font-family: 'Barlow', sans-serif; font-size: 14px; transition: all 0.3s;
        }
        .form-control:focus { outline: none; border-color: var(--accent); }
        table { width: 100%; border-collapse: collapse; }
        th {
            text-align: left; padding: 10px 12px; font-size: 11px; color: var(--text-muted);
            border-bottom: 1px solid var(--border-color); text-transform: uppercase; letter-spacing: 0.5px;
            font-family: 'Space Mono', monospace;
        }
        td { padding: 12px; border-bottom: 1px solid rgba(30,41,59,0.5); font-size: 14px; }
        tr:hover td { background: rgba(0,240,255,0.02); }
        .btn-ver {
            background: rgba(0,240,255,0.1); border: 1px solid rgba(0,240,255,0.3);
            color: var(--accent); padding: 6px 14px; border-radius: var(--r-sm);
            text-decoration: none; font-size: 12px; font-family: 'Space Mono', monospace;
            transition: all 0.2s;
        }
        .btn-ver:hover { background: rgba(0,240,255,0.2); }
        .empty-state {
            text-align: center; padding: 60px 20px;
            background: var(--panel-bg); border: 1px solid var(--border-color);
            border-radius: var(--r-md); color: var(--text-muted);
        }
        .empty-state .icon { font-size: 48px; margin-bottom: 16px; }
        .table-wrap { background: var(--panel-bg); border: 1px solid var(--border-color); border-radius: var(--r-md); overflow: hidden; }
        .system-status {
            display: flex; align-items: center; justify-content: center; gap: 10px;
            font-family: 'Space Mono', monospace; font-size: 11px; color: var(--text-muted); padding: 30px 0 10px;
        }
        .status-dot {
            width: 7px; height: 7px; border-radius: 50%;
            background: var(--success); box-shadow: 0 0 6px var(--success); animation: pulse 2s infinite;
        }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.4; } }
        .count-badge {
            font-family: 'Space Mono', monospace; font-size: 11px;
            background: rgba(0,240,255,0.08); border: 1px solid rgba(0,240,255,0.2);
            color: var(--accent); padding: 4px 10px; border-radius: var(--r-sm);
        }
    </style>
</head>
<body>
<div class="container">

    <div class="header-box">
        <div class="header-nav">
            <a href="../../menu_hseq.html" class="btn-back">← MENÚ HSEQ</a>
            <span class="count-badge" id="totalRegistros">Cargando...</span>
        </div>
        <div class="main-title">Galería — Análisis de Trabajo Seguro</div>
        <div class="sub-title">Registros históricos por sede y período</div>
    </div>

    <div class="filtros">
        <div>
            <label>Filtrar por mes</label>
            <input type="month" id="filtroMes" class="form-control">
        </div>
        <div>
            <label>Buscar</label>
            <input type="text" id="filtroBuscar" class="form-control" placeholder="Buscar por tipo, zona, usuario..." style="min-width:260px;">
        </div>
    </div>

    <div id="contenidoTabla"></div>

    <div class="system-status">
        <div class="status-dot"></div>
        SISTEMA JSON INTERCONECTADO — PLATAFORMA OMAS / HSEQ
    </div>
</div>

<script>
    let todosLosRegistros = [];

    async function cargarDatos() {
        const res = await fetch('listar_jsons.php');
        const archivos = await res.json();

        for (const arch of archivos) {
            const r = await fetch(arch.ruta);
            const arr = await r.json();
            arr.forEach(reg => {
                reg._archivo = arch.ruta;
                todosLosRegistros.push(reg);
            });
        }

        todosLosRegistros.sort((a, b) => b.timestamp.localeCompare(a.timestamp));
        renderizar(todosLosRegistros);
    }

    function renderizar(lista) {
        document.getElementById('totalRegistros').textContent = lista.length + ' registro(s)';
        const cont = document.getElementById('contenidoTabla');
        if (lista.length === 0) {
            cont.innerHTML = `<div class="empty-state"><div class="icon">📋</div><p>No hay registros de ATS para los filtros seleccionados.</p></div>`;
            return;
        }

        let html = `<div class="table-wrap"><table>
            <thead><tr>
                <th>Fecha Elaboración</th>
                <th>Tipo de Trabajo</th>
                <th>Zona de Trabajo</th>
                <th>Valoración Riesgo</th>
                <th>Registrado por</th>
                <th>Timestamp</th>
                <th></th>
            </tr></thead><tbody>`;

        lista.forEach(reg => {
            const d = reg.datos || {};
            html += `<tr>
                <td>${d.fecha_elaboracion || '—'}</td>
                <td>${d.tipo_trabajo || '—'}</td>
                <td>${d.zona_trabajo || '—'}</td>
                <td>${d.valoracion_riesgo || '—'}</td>
                <td>${reg.usuario_sys || '—'}</td>
                <td style="font-family:'Space Mono',monospace; font-size:12px; color:var(--text-muted);">${reg.timestamp}</td>
                <td><a href="visor_analisis_trabajo.php?file=${encodeURIComponent(reg._archivo)}&id=${reg.id_registro}" class="btn-ver">VER</a></td>
            </tr>`;
        });

        html += `</tbody></table></div>`;
        cont.innerHTML = html;
    }

    function aplicarFiltros() {
        const mes = document.getElementById('filtroMes').value;
        const buscar = document.getElementById('filtroBuscar').value.toLowerCase();
        let filtrados = todosLosRegistros.filter(reg => {
            const d = reg.datos || {};
            const mesOk = !mes || reg.timestamp.startsWith(mes);
            const buscarOk = !buscar || JSON.stringify(reg).toLowerCase().includes(buscar);
            return mesOk && buscarOk;
        });
        renderizar(filtrados);
    }

    document.getElementById('filtroMes').addEventListener('change', aplicarFiltros);
    document.getElementById('filtroBuscar').addEventListener('input', aplicarFiltros);

    cargarDatos();
</script>
</body>
</html>
