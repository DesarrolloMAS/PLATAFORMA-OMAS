<?php
require '../sesion.php';
verificarAutenticacion();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analítica · Tickets mIA</title>
    <link rel="stylesheet" href="../../css/index.css">
    <link rel="stylesheet" href="../../css/menu_principal.css">
    <script src="https://cdn.jsdelivr.net/npm/d3@7/dist/d3.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
    <style>
        /* Paleta categórica de las gráficas — misma metodología que
           dashboard_bitacora.php (skill dataviz, scripts/validate_palette.js),
           validada contra la superficie oscura real de este tema
           (--panel-solid bajo theme-invert, #12161f): lightness band, piso de
           croma, separación CVD y contraste, todo en PASS. Orden fijo por
           severidad (baja→crítica), nunca cíclico. */
        :root {
            --series-baja: #16a672;
            --series-normal: #4C8BF5;
            --series-alta: #BD8016;
            --series-critica: #D6455E;
            --status-warning: #f2b134;
        }

        .analytics-page {
            position: relative;
            z-index: 2;
            max-width: 1180px;
            margin: 0 auto;
            padding: 100px 20px 70px;
        }

        .page-head { margin-bottom: 34px; opacity: 0; }
        .page-eyebrow {
            font-size: 12px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase;
            color: var(--accent-2); margin-bottom: 8px; display: flex; align-items: center; gap: 8px;
        }
        .page-eyebrow .dot {
            width: 6px; height: 6px; border-radius: 50%; background: var(--accent-2);
            box-shadow: 0 0 8px var(--accent-2); animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(56, 189, 248, 0.5); }
            70% { box-shadow: 0 0 0 8px rgba(56, 189, 248, 0); }
            100% { box-shadow: 0 0 0 0 rgba(56, 189, 248, 0); }
        }
        .page-title { font-size: 26px; font-weight: 800; color: var(--text-main); letter-spacing: -0.01em; }
        .page-sub { color: var(--text-muted); font-size: 14px; margin-top: 6px; max-width: 640px; }

        /* ── Panel de vidrio, mismo lenguaje que .auth-card ── */
        .glass-card {
            background: var(--panel);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border: 1px solid var(--border);
            border-radius: var(--r-lg);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.35), 0 0 0 1px rgba(37, 99, 235, 0.06) inset;
            opacity: 0;
            transition: border-color 0.4s var(--ease), box-shadow 0.4s var(--ease), transform 0.4s var(--ease);
            will-change: transform;
        }
        .glass-card:hover {
            border-color: var(--border-hover);
            box-shadow: 0 24px 70px rgba(0, 0, 0, 0.4), 0 0 40px rgba(56, 189, 248, 0.08), 0 0 0 1px rgba(37, 99, 235, 0.08) inset;
        }

        /* ── KPI tiles ── */
        .kpi-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            margin-bottom: 26px;
        }
        .kpi-tile { padding: 22px 24px; display: flex; flex-direction: column; gap: 7px; }
        .kpi-label { font-size: 12px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; }
        .kpi-value { font-size: 32px; font-weight: 800; color: var(--text-main); font-variant-numeric: tabular-nums; }
        .kpi-delta { font-size: 12.5px; display: flex; align-items: center; gap: 5px; font-weight: 600; }
        .kpi-delta.good { color: var(--success); }
        .kpi-delta.bad { color: var(--danger); }
        .kpi-spark { margin-top: 2px; width: 100%; height: 30px; display: block; overflow: visible; }
        .kpi-spark path.spark-line { fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
        .kpi-spark path.spark-fill { stroke: none; }

        /* ── Chart cards ── */
        .chart-card { padding: 26px 28px; margin-bottom: 22px; }
        .section-title-row { display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 10px; margin-bottom: 4px; }
        .section-title { font-size: 15px; font-weight: 700; color: var(--text-main); }
        .section-desc { color: var(--text-muted); font-size: 13px; margin-bottom: 18px; }

        .btn-table-toggle {
            background: transparent; border: 1px solid var(--border); color: var(--text-muted);
            font-size: 12px; font-weight: 600; padding: 7px 14px; border-radius: 999px;
            cursor: pointer; white-space: nowrap; transition: all 0.25s var(--ease);
        }
        .btn-table-toggle:hover { border-color: var(--border-hover); color: var(--accent-2); }

        /* ── Selectores de rango y sede — mismo lenguaje de píldora que .btn-table-toggle ── */
        .filtros-bar {
            display: flex; flex-wrap: wrap; gap: 12px; align-items: center; margin-bottom: 26px;
        }
        .rango-selector, .sede-selector {
            display: inline-flex; flex-wrap: wrap; gap: 4px; padding: 4px;
            background: var(--panel); border: 1px solid var(--border); border-radius: 999px;
            opacity: 0;
        }
        .sede-selector { border-color: rgba(245, 158, 11, 0.35); }
        .rango-btn, .sede-btn {
            background: transparent; border: none; color: var(--text-muted);
            font-size: 12.5px; font-weight: 600; padding: 8px 18px; border-radius: 999px;
            cursor: pointer; white-space: nowrap; transition: all 0.25s var(--ease);
        }
        .rango-btn:hover, .sede-btn:hover { color: var(--text-main); }
        .rango-btn.active {
            background: var(--accent-2); color: #04121f;
            box-shadow: 0 0 15px rgba(56, 189, 248, 0.35);
        }
        .sede-btn.active {
            background: #f59e0b; color: #1a0f00;
            box-shadow: 0 0 15px rgba(245, 158, 11, 0.4);
        }
        .rango-btn:disabled, .sede-btn:disabled { opacity: 0.5; cursor: wait; }

        /* ── Filtro de prioridad, local a la tarjeta de Tiempo Promedio de Resolución ── */
        .tipo-selector {
            display: inline-flex; flex-wrap: wrap; gap: 4px; padding: 3px;
            background: rgba(0, 0, 0, 0.15); border: 1px solid var(--border); border-radius: 999px;
            margin: 4px 0 18px; opacity: 0;
        }
        .tipo-btn {
            background: transparent; border: none; color: var(--text-muted);
            font-size: 11.5px; font-weight: 600; padding: 6px 14px; border-radius: 999px;
            cursor: pointer; white-space: nowrap; transition: all 0.25s var(--ease);
        }
        .tipo-btn:hover { color: var(--text-main); }
        .tipo-btn.active {
            background: var(--tipo-color, var(--accent-2)); color: #04121f;
        }
        .tipo-btn:disabled { opacity: 0.5; cursor: wait; }

        .chart-legend { display: flex; gap: 18px; flex-wrap: wrap; margin-bottom: 14px; font-size: 13px; color: var(--text-main); }
        .legend-item { display: flex; align-items: center; gap: 7px; }
        .legend-key { width: 16px; height: 3px; border-radius: 2px; display: inline-block; }
        .legend-key.dot { width: 10px; height: 10px; border-radius: 50%; }

        .chart-wrap { position: relative; width: 100%; }
        svg.chart-svg { width: 100%; display: block; overflow: visible; }
        .axis text { fill: var(--text-muted); font-size: 11px; font-family: 'Inter', sans-serif; }
        .axis path, .axis line { stroke: var(--border); }
        .gridline { stroke: var(--border); stroke-width: 1; }

        /* ── Marcas interactivas: glow + lift/pop en vez de solo atenuar
           opacidad — mismo lenguaje de "hover ilumina" que .auth-card y
           .brand-logo-frame en index.css, aplicado a los datos. ── */
        #chartBarras rect {
            transition: transform 0.28s var(--ease), filter 0.28s var(--ease);
            transform-box: fill-box;
            transform-origin: bottom center;
        }
        #chartBarras rect.bar-baja:hover { transform: translateY(-4px); filter: brightness(1.25) drop-shadow(0 6px 14px rgba(22,166,114,0.55)); }
        #chartBarras rect.bar-normal:hover { transform: translateY(-4px); filter: brightness(1.25) drop-shadow(0 6px 14px rgba(76,139,245,0.55)); }
        #chartBarras rect.bar-alta:hover { transform: translateY(-4px); filter: brightness(1.25) drop-shadow(0 6px 14px rgba(189,128,22,0.55)); }
        #chartBarras rect.bar-critica:hover { transform: translateY(-4px); filter: brightness(1.25) drop-shadow(0 6px 14px rgba(214,69,94,0.55)); }

        #chartDonut path.donut-arc {
            transition: transform 0.3s var(--ease), filter 0.3s var(--ease);
            transform-box: fill-box;
            transform-origin: center;
            filter: drop-shadow(0 0 0 transparent);
        }
        #chartDonut path.donut-arc:hover {
            transform: scale(1.045);
            filter: drop-shadow(0 0 14px var(--mark-glow));
        }

        #chartLinea path.line-main { filter: drop-shadow(0 0 6px var(--series-baja)); }
        #chartLinea circle.dot { transition: r 0.25s var(--ease), filter 0.25s var(--ease); filter: drop-shadow(0 0 4px var(--series-baja)); }
        #chartLinea circle.dot:hover { filter: drop-shadow(0 0 10px var(--series-baja)); }
        #chartLinea circle.crosshair-dot { filter: drop-shadow(0 0 8px var(--series-baja)); }

        /* ── Tooltip ── */
        .viz-tooltip {
            position: absolute; pointer-events: none;
            background: var(--panel-solid); border: 1px solid var(--border); border-radius: var(--r-sm);
            padding: 10px 12px; font-size: 12.5px; box-shadow: 0 10px 24px rgba(0,0,0,0.45);
            opacity: 0; transition: opacity 0.12s; z-index: 20; min-width: 150px;
        }
        .viz-tooltip .tt-title { color: var(--text-muted); font-size: 11px; margin-bottom: 6px; font-weight: 600; }
        .viz-tooltip .tt-row { display: flex; align-items: center; gap: 8px; justify-content: space-between; }
        .viz-tooltip .tt-row + .tt-row { margin-top: 4px; }
        .viz-tooltip .tt-key { width: 10px; height: 2px; border-radius: 1px; flex-shrink: 0; }
        .viz-tooltip .tt-name { color: var(--text-muted); flex: 1; }
        .viz-tooltip .tt-val { color: var(--text-main); font-weight: 700; font-variant-numeric: tabular-nums; }

        /* ── Table view (accesibilidad) ── */
        .data-table { width: 100%; border-collapse: collapse; margin-top: 16px; display: none; font-size: 13px; }
        .data-table.visible { display: table; }
        .data-table th {
            text-align: left; font-size: 10.5px; text-transform: uppercase; letter-spacing: 0.04em;
            color: var(--text-muted); padding: 8px 10px; border-bottom: 1px solid var(--border);
        }
        .data-table td { padding: 7px 10px; border-bottom: 1px solid rgba(255,255,255,0.04); font-variant-numeric: tabular-nums; }
        .data-table tbody tr:hover { background: rgba(56, 189, 248, 0.05); }

        .status-chip {
            display: inline-flex; align-items: center; gap: 5px; font-size: 11px; font-weight: 700;
            padding: 3px 9px; border-radius: 999px;
        }
        .status-chip.completado { background: rgba(22,166,114,0.14); color: var(--success); border: 1px solid rgba(22,166,114,0.3); }
        .status-chip.pendiente { background: rgba(242,177,52,0.14); color: var(--status-warning); border: 1px solid rgba(242,177,52,0.3); }
        .status-chip.vencido { background: rgba(255,84,112,0.14); color: var(--danger); border: 1px solid rgba(255,84,112,0.3); }

        .footer-status {
            font-size: 12px; color: var(--text-faint); text-align: center; margin-top: 30px;
            display: flex; justify-content: center; align-items: center; gap: 8px; opacity: 0;
        }

        @media (max-width: 640px) {
            .analytics-page { padding-top: 84px; }
        }
    </style>
</head>
<body class="theme-invert">
    <canvas id="bg-canvas"></canvas>
    <div class="bg-veil"></div>

    <a href="../menu_adm.html" class="menu-exit menu-exit--back">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19 8 12l7-7" />
        </svg>
        <span class="exit-label">Volver al menú</span>
    </a>

    <a href="../logout.php" class="menu-exit">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
        </svg>
        <span class="exit-label">Cerrar sesión</span>
    </a>

    <div class="analytics-page">
        <div class="page-head">
            <div class="page-eyebrow"><span class="dot"></span>DATOS REALES · MESA DE AYUDA (mIA)</div>
            <div class="page-title">Analítica · Tickets mIA</div>
            <div class="page-sub" id="pageSub">D3.js + GSAP sobre los tickets reales sincronizados desde mIA (mesa MANTENIMIENTO) — mismo sistema visual y de movimiento que el resto de menús.</div>
        </div>

        <div class="filtros-bar">
            <div class="rango-selector" id="rangoSelector">
                <button class="rango-btn" data-rango="6m">6 Meses</button>
                <button class="rango-btn" data-rango="1m">1 Mes</button>
                <button class="rango-btn" data-rango="1s">Semana</button>
            </div>
            <div class="sede-selector" id="sedeSelector">
                <button class="sede-btn" data-sede="todas">Todas</button>
                <button class="sede-btn" data-sede="Molino Bogota">Bogotá</button>
                <button class="sede-btn" data-sede="Molino Pasto">Pasto</button>
                <button class="sede-btn" data-sede="Artesa Panaderia">Artesa</button>
                <button class="sede-btn" data-sede="Molino Buga">Buga</button>
            </div>
            <a href="../gobierno_datos/integracion_mia/panel.php"
               style="display:inline-flex; align-items:center; gap:6px; padding:8px 16px; border-radius:999px; background: var(--panel); border: 1px solid var(--accent-2); color: var(--accent-2); text-decoration:none; font-size: 13px;"
               title="Traer tickets nuevos desde mIA">
                🔄 Sync mIA
            </a>
            <a href="dashboard_bitacora.php"
               style="display:inline-flex; align-items:center; gap:6px; padding:8px 16px; border-radius:999px; background: var(--panel); border: 1px solid var(--border); color: var(--text-muted); text-decoration:none; font-size: 13px;">
                📊 Bitácora de Mantenimiento
            </a>
        </div>

        <div class="kpi-row" id="kpiRow"></div>

        <div class="glass-card chart-card" id="cardBarras">
            <div class="section-title-row">
                <div>
                    <div class="section-title" id="tituloBarras">Tickets por Mes y Prioridad</div>
                    <div class="section-desc" id="descBarras">Últimos 6 meses · baja, normal, alta y crítica</div>
                </div>
                <button class="btn-table-toggle" data-target="tableBarras">VER COMO TABLA</button>
            </div>
            <div class="chart-legend" id="legendBarras"></div>
            <div class="chart-wrap">
                <svg class="chart-svg" id="chartBarras"></svg>
                <div class="viz-tooltip" id="ttBarras"></div>
            </div>
            <table class="data-table" id="tableBarras"></table>
        </div>

        <div class="glass-card chart-card" id="cardLinea">
            <div class="section-title-row">
                <div>
                    <div class="section-title">Tiempo Promedio de Resolución</div>
                    <div class="section-desc" id="descLinea">Horas promedio por ticket, semanal</div>
                </div>
                <button class="btn-table-toggle" data-target="tableLinea">VER COMO TABLA</button>
            </div>
            <div class="tipo-selector" id="prioridadSelector">
                <button class="tipo-btn active" data-tipo="todas">Todas</button>
                <button class="tipo-btn" data-tipo="baja" style="--tipo-color: var(--series-baja)">Baja</button>
                <button class="tipo-btn" data-tipo="normal" style="--tipo-color: var(--series-normal)">Normal</button>
                <button class="tipo-btn" data-tipo="alta" style="--tipo-color: var(--series-alta)">Alta</button>
                <button class="tipo-btn" data-tipo="critica" style="--tipo-color: var(--series-critica)">Crítica</button>
            </div>
            <div class="chart-wrap">
                <svg class="chart-svg" id="chartLinea"></svg>
                <div class="viz-tooltip" id="ttLinea"></div>
            </div>
            <table class="data-table" id="tableLinea"></table>
        </div>

        <div class="glass-card chart-card" id="cardDonut">
            <div class="section-title-row">
                <div>
                    <div class="section-title">Distribución por Especialista</div>
                    <div class="section-desc">Participación en el total de tickets del período</div>
                </div>
                <button class="btn-table-toggle" data-target="tableDonut">VER COMO TABLA</button>
            </div>
            <div class="chart-legend" id="legendDonut"></div>
            <div class="chart-wrap" style="max-width: 420px; margin: 0 auto;">
                <svg class="chart-svg" id="chartDonut"></svg>
                <div class="viz-tooltip" id="ttDonut"></div>
            </div>
            <table class="data-table" id="tableDonut"></table>
        </div>

        <div class="footer-status" id="footerStatus">
            CONECTADO · Cargando datos reales…
        </div>
    </div>

    <script src="../../dist/app.js"></script>
    <script>
// ═══════════════════════════════════════════════════════════════════
// DATOS REALES — vienen de api_analitica_mia.php, que lee y agrega en el
// servidor los tickets de mia_datos.tickets_mia (ver template/integracion-mia.md).
// ═══════════════════════════════════════════════════════════════════
const PRIORIDADES = [
    { key: 'baja', label: 'Baja', color: 'var(--series-baja)', light: '#5EE7B8' },
    { key: 'normal', label: 'Normal', color: 'var(--series-normal)', light: '#8FB8FF' },
    { key: 'alta', label: 'Alta', color: 'var(--series-alta)', light: '#F0B255' },
    { key: 'critica', label: 'Crítica', color: 'var(--series-critica)', light: '#FF8FA3' },
];

const EASE_SETTLE = 'expo.out';
const EASE_POP = 'back.out(1.5)';
const EASE_DRAW = 'expo.inOut';

let datosMensuales = [];
let datosSemanales = [];
let datosEspecialistas = [];
let kpisData = [];

function cssVar(name) {
    const m = /var\((--[\w-]+)\)/.exec(name);
    const key = m ? m[1] : name;
    return getComputedStyle(document.body).getPropertyValue(key).trim();
}

// ═══════════════════════════════════════════════════════════════════
// UTIL — tooltip
// ═══════════════════════════════════════════════════════════════════
function showTooltip(el, container, html, x, y) {
    el.innerHTML = '';
    const title = document.createElement('div');
    title.className = 'tt-title';
    el.appendChild(title);
    html(el, title);
    const rect = container.getBoundingClientRect();
    el.style.left = Math.min(x + 14, rect.width - 160) + 'px';
    el.style.top = Math.max(y - 10, 0) + 'px';
    el.style.opacity = '1';
}
function hideTooltip(el) { el.style.opacity = '0'; }

function addTooltipRow(el, name, value, color) {
    const row = document.createElement('div');
    row.className = 'tt-row';
    const key = document.createElement('span');
    key.className = 'tt-key';
    key.style.background = color;
    const nameEl = document.createElement('span');
    nameEl.className = 'tt-name';
    nameEl.textContent = name;
    const valEl = document.createElement('span');
    valEl.className = 'tt-val';
    valEl.textContent = value;
    row.appendChild(key);
    row.appendChild(nameEl);
    row.appendChild(valEl);
    el.appendChild(row);
}

// ═══════════════════════════════════════════════════════════════════
// 1. KPI TILES — GSAP count-up
// ═══════════════════════════════════════════════════════════════════
function renderKPIs() {
    const tiles = kpisData;
    const row = document.getElementById('kpiRow');
    row.innerHTML = '';
    tiles.forEach(t => {
        const tile = document.createElement('div');
        tile.className = 'glass-card kpi-tile';
        tile.innerHTML = `
            <div class="kpi-label"></div>
            <div class="kpi-value">0</div>
            <svg class="kpi-spark"></svg>
            <div class="kpi-delta ${t.good ? 'good' : 'bad'}">${t.good ? '▲' : '▼'} <span></span></div>
        `;
        tile.querySelector('.kpi-label').textContent = t.label;
        tile.querySelector('.kpi-delta span').textContent = t.delta;
        row.appendChild(tile);

        const valueEl = tile.querySelector('.kpi-value');
        const counter = { n: 0 };
        gsap.to(counter, {
            n: t.value,
            duration: 1.4,
            ease: EASE_SETTLE,
            onUpdate: () => { valueEl.textContent = counter.n.toFixed(t.decimals) + (t.suffix || ''); },
        });

        const sparkSvg = d3.select(tile.querySelector('.kpi-spark'));
        const sw = 180, sh = 30;
        sparkSvg.attr('viewBox', `0 0 ${sw} ${sh}`);
        const sx = d3.scalePoint().domain(d3.range(t.spark.length)).range([2, sw - 2]);
        const sy = d3.scaleLinear().domain([d3.min(t.spark) * 0.9, d3.max(t.spark) * 1.1]).range([sh - 4, 4]);
        const sparkColor = cssVar(t.color);
        const sline = d3.line().x((d, i) => sx(i)).y(d => sy(d)).curve(d3.curveMonotoneX);
        const sarea = d3.area().x((d, i) => sx(i)).y0(sh).y1(d => sy(d)).curve(d3.curveMonotoneX);

        sparkSvg.append('path').datum(t.spark).attr('class', 'spark-fill').attr('d', sarea).attr('fill', sparkColor).attr('opacity', 0.12);
        const sparkPath = sparkSvg.append('path').datum(t.spark).attr('class', 'spark-line').attr('d', sline).attr('stroke', sparkColor);
        const sparkLen = sparkPath.node().getTotalLength();
        sparkPath.attr('stroke-dasharray', sparkLen).attr('stroke-dashoffset', sparkLen);
        gsap.to(sparkPath.node(), { strokeDashoffset: 0, duration: 1.1, delay: 0.3, ease: EASE_DRAW });

        const rotX = gsap.quickTo(tile, 'rotationX', { duration: 0.5, ease: EASE_SETTLE });
        const rotY = gsap.quickTo(tile, 'rotationY', { duration: 0.5, ease: EASE_SETTLE });
        tile.style.transformPerspective = 800;
        tile.addEventListener('pointermove', (e) => {
            const rect = tile.getBoundingClientRect();
            const px = (e.clientX - rect.left) / rect.width - 0.5;
            const py = (e.clientY - rect.top) / rect.height - 0.5;
            rotY(px * 4);
            rotX(py * -4);
        });
        tile.addEventListener('pointerleave', () => { rotX(0); rotY(0); });
    });

    gsap.to('.kpi-tile', { opacity: 1, y: 0, duration: 0.7, stagger: 0.1, ease: EASE_SETTLE });
}

// ═══════════════════════════════════════════════════════════════════
// 2. BARRAS AGRUPADAS — D3 escalas + GSAP para el crecimiento
// ═══════════════════════════════════════════════════════════════════
function renderBarras() {
    const svg = d3.select('#chartBarras');
    const wrap = document.getElementById('chartBarras').closest('.chart-wrap');
    const width = wrap.clientWidth;
    const height = 320;
    const margin = { top: 10, right: 10, bottom: 30, left: 40 };
    svg.attr('viewBox', `0 0 ${width} ${height}`);

    const innerW = width - margin.left - margin.right;
    const innerH = height - margin.top - margin.bottom;

    const x0 = d3.scaleBand().domain(datosMensuales.map(d => d.mes)).range([0, innerW]).paddingInner(0.35);
    const x1 = d3.scaleBand().domain(PRIORIDADES.map(t => t.key)).range([0, x0.bandwidth()]).paddingInner(0.12);
    const maxY = d3.max(datosMensuales, d => Math.max(d.baja, d.normal, d.alta, d.critica));
    const y = d3.scaleLinear().domain([0, maxY * 1.15]).nice().range([innerH, 0]);

    const g = svg.append('g').attr('transform', `translate(${margin.left},${margin.top})`);

    const defs = svg.append('defs');
    PRIORIDADES.forEach(tipo => {
        const grad = defs.append('linearGradient')
            .attr('id', 'gradBar-' + tipo.key)
            .attr('x1', '0').attr('y1', '1').attr('x2', '0').attr('y2', '0');
        grad.append('stop').attr('offset', '0%').attr('stop-color', cssVar(tipo.color));
        grad.append('stop').attr('offset', '100%').attr('stop-color', tipo.light);
    });

    g.append('g')
        .selectAll('line')
        .data(y.ticks(5))
        .join('line')
        .attr('class', 'gridline')
        .attr('x1', 0).attr('x2', innerW)
        .attr('y1', d => y(d)).attr('y2', d => y(d));

    const ejeX = d3.axisBottom(x0).tickSize(0);
    if (datosMensuales.length > 10) {
        const paso = Math.ceil(datosMensuales.length / 10);
        ejeX.tickValues(x0.domain().filter((d, i) => i % paso === 0));
    }
    g.append('g').attr('class', 'axis').attr('transform', `translate(0,${innerH})`)
        .call(ejeX).call(g => g.select('.domain').remove());
    g.append('g').attr('class', 'axis')
        .call(d3.axisLeft(y).ticks(5).tickSize(0)).call(g => g.select('.domain').remove());

    const groups = g.selectAll('.month-group')
        .data(datosMensuales)
        .join('g')
        .attr('class', 'month-group')
        .attr('transform', d => `translate(${x0(d.mes)},0)`);

    const barWidth = Math.min(20, x1.bandwidth());
    const barOffset = (x1.bandwidth() - barWidth) / 2;

    const tooltipEl = document.getElementById('ttBarras');

    PRIORIDADES.forEach(tipo => {
        groups.append('rect')
            .attr('class', 'bar-' + tipo.key)
            .attr('x', () => x1(tipo.key) + barOffset)
            .attr('width', barWidth)
            .attr('rx', 4).attr('ry', 4)
            .attr('fill', `url(#gradBar-${tipo.key})`)
            .attr('y', innerH)
            .attr('height', 0)
            .style('cursor', 'pointer')
            .on('pointermove', function (event, d) {
                const [mx, my] = d3.pointer(event, wrap);
                showTooltip(tooltipEl, wrap, (el, title) => {
                    title.textContent = d.mes;
                    addTooltipRow(el, tipo.label, d[tipo.key] + ' tickets', cssVar(tipo.color));
                }, mx, my);
            })
            .on('pointerleave', function () { hideTooltip(tooltipEl); });
    });

    let i = 0;
    const groupNodes = groups.nodes();
    PRIORIDADES.forEach(tipo => {
        datosMensuales.forEach((d, idx) => {
            const finalH = innerH - y(d[tipo.key]);
            const finalY = y(d[tipo.key]);
            const barEl = groupNodes[idx].querySelector('.bar-' + tipo.key);
            gsap.to(barEl, {
                attr: { y: finalY, height: finalH },
                duration: 1,
                delay: i * 0.035,
                ease: EASE_POP,
            });
            i++;
        });
    });

    const legend = document.getElementById('legendBarras');
    legend.innerHTML = '';
    PRIORIDADES.forEach(tipo => {
        const item = document.createElement('div');
        item.className = 'legend-item';
        item.innerHTML = `<span class="legend-key" style="background:${cssVar(tipo.color)}"></span><span>${tipo.label}</span>`;
        legend.appendChild(item);
    });

    const table = document.getElementById('tableBarras');
    table.innerHTML = `
        <thead><tr><th>Mes</th><th>Baja</th><th>Normal</th><th>Alta</th><th>Crítica</th></tr></thead>
        <tbody>${datosMensuales.map(d => `<tr><td>${d.mes}</td><td>${d.baja}</td><td>${d.normal}</td><td>${d.alta}</td><td>${d.critica}</td></tr>`).join('')}</tbody>
    `;
}

// ═══════════════════════════════════════════════════════════════════
// 3. LÍNEA — path animado (stroke-dashoffset) con crosshair
// ═══════════════════════════════════════════════════════════════════
function renderLinea() {
    const svg = d3.select('#chartLinea');
    const wrap = document.getElementById('chartLinea').closest('.chart-wrap');
    const width = wrap.clientWidth;
    const height = 260;
    const margin = { top: 10, right: 20, bottom: 30, left: 40 };
    svg.attr('viewBox', `0 0 ${width} ${height}`);

    const innerW = width - margin.left - margin.right;
    const innerH = height - margin.top - margin.bottom;

    const x = d3.scalePoint().domain(datosSemanales.map(d => d.semana)).range([0, innerW]);
    const y = d3.scaleLinear().domain([0, d3.max(datosSemanales, d => d.horas) * 1.2]).nice().range([innerH, 0]);
    const color = cssVar('var(--series-baja)');

    const g = svg.append('g').attr('transform', `translate(${margin.left},${margin.top})`);

    const defs = svg.append('defs');
    const areaGrad = defs.append('linearGradient')
        .attr('id', 'gradArea-linea')
        .attr('x1', '0').attr('y1', '0').attr('x2', '0').attr('y2', '1');
    areaGrad.append('stop').attr('offset', '0%').attr('stop-color', color).attr('stop-opacity', 0.32);
    areaGrad.append('stop').attr('offset', '100%').attr('stop-color', color).attr('stop-opacity', 0);

    g.append('g').selectAll('line')
        .data(y.ticks(5)).join('line')
        .attr('class', 'gridline')
        .attr('x1', 0).attr('x2', innerW).attr('y1', d => y(d)).attr('y2', d => y(d));

    const ejeXLinea = d3.axisBottom(x).tickSize(0);
    if (datosSemanales.length > 10) {
        const paso = Math.ceil(datosSemanales.length / 10);
        ejeXLinea.tickValues(x.domain().filter((d, i) => i % paso === 0));
    }
    g.append('g').attr('class', 'axis').attr('transform', `translate(0,${innerH})`)
        .call(ejeXLinea).call(g => g.select('.domain').remove());
    g.append('g').attr('class', 'axis')
        .call(d3.axisLeft(y).ticks(5).tickSize(0).tickFormat(d => d + 'h')).call(g => g.select('.domain').remove());

    const area = d3.area().x(d => x(d.semana)).y0(innerH).y1(d => y(d.horas)).curve(d3.curveMonotoneX);
    const areaPath = g.append('path').datum(datosSemanales).attr('d', area).attr('fill', 'url(#gradArea-linea)').style('opacity', 0);
    gsap.to(areaPath.node(), { opacity: 1, duration: 1.2, delay: 0.4, ease: EASE_SETTLE });

    const line = d3.line().x(d => x(d.semana)).y(d => y(d.horas)).curve(d3.curveMonotoneX);
    const path = g.append('path')
        .attr('class', 'line-main')
        .datum(datosSemanales)
        .attr('d', line)
        .attr('fill', 'none')
        .attr('stroke', color)
        .attr('stroke-width', 2)
        .attr('stroke-linecap', 'round')
        .attr('stroke-linejoin', 'round');

    const totalLength = path.node().getTotalLength();
    path.attr('stroke-dasharray', totalLength).attr('stroke-dashoffset', totalLength);
    gsap.to(path.node(), { strokeDashoffset: 0, duration: 1.7, ease: EASE_DRAW });

    const dots = g.selectAll('.dot')
        .data(datosSemanales).join('circle')
        .attr('class', 'dot')
        .attr('cx', d => x(d.semana))
        .attr('cy', d => y(d.horas))
        .attr('r', 0)
        .attr('fill', color)
        .attr('stroke', cssVar('var(--panel-solid)'))
        .attr('stroke-width', 2);
    gsap.to(dots.nodes(), {
        attr: { r: 4 }, duration: 0.6, delay: 1.4, stagger: 0.04, ease: EASE_POP,
        onComplete() {
            gsap.to(dots.nodes(), {
                attr: { r: 5 }, duration: 1.6, ease: 'sine.inOut',
                stagger: { each: 0.15, repeat: -1, yoyo: true },
            });
        },
    });

    const tooltipEl = document.getElementById('ttLinea');
    const crosshair = g.append('line')
        .attr('y1', 0).attr('y2', innerH)
        .attr('stroke', cssVar('var(--border-hover)'))
        .attr('stroke-width', 1)
        .style('opacity', 0);
    const crosshairDot = g.append('circle')
        .attr('class', 'crosshair-dot')
        .attr('r', 5)
        .attr('fill', color)
        .attr('stroke', cssVar('var(--panel-solid)'))
        .attr('stroke-width', 2)
        .style('opacity', 0);

    const overlay = g.append('rect')
        .attr('width', innerW).attr('height', innerH)
        .attr('fill', 'transparent')
        .style('cursor', 'crosshair');

    overlay.on('pointermove', function (event) {
        const [mx] = d3.pointer(event);
        const step = innerW / (datosSemanales.length - 1);
        const idx = Math.max(0, Math.min(datosSemanales.length - 1, Math.round(mx / step)));
        const d = datosSemanales[idx];
        crosshair.attr('x1', x(d.semana)).attr('x2', x(d.semana)).style('opacity', 1);
        crosshairDot.attr('cx', x(d.semana)).attr('cy', y(d.horas)).style('opacity', 1);
        const [px, py] = d3.pointer(event, wrap);
        showTooltip(tooltipEl, wrap, (el, title) => {
            title.textContent = d.semana;
            addTooltipRow(el, 'Tiempo promedio', d.horas + ' h', color);
        }, px, y(d.horas) + margin.top);
    }).on('pointerleave', function () {
        crosshair.style('opacity', 0);
        crosshairDot.style('opacity', 0);
        hideTooltip(tooltipEl);
    });

    const table = document.getElementById('tableLinea');
    table.innerHTML = `
        <thead><tr><th>Semana</th><th>Horas promedio</th></tr></thead>
        <tbody>${datosSemanales.map(d => `<tr><td>${d.semana}</td><td>${d.horas} h</td></tr>`).join('')}</tbody>
    `;
}

// ═══════════════════════════════════════════════════════════════════
// 4. DONUT — arco animado con GSAP interpolando el ángulo final
// ═══════════════════════════════════════════════════════════════════
function renderDonut() {
    const svg = d3.select('#chartDonut');
    const wrap = document.getElementById('chartDonut').closest('.chart-wrap');
    const size = Math.min(wrap.clientWidth, 340);
    const radius = size / 2;
    svg.attr('viewBox', `0 0 ${size} ${size}`);

    const g = svg.append('g').attr('transform', `translate(${size / 2},${size / 2})`);

    const colores = ['#0EA5B8', '#C97400', '#8C6CDD', '#2563eb', '#e879a8'];

    const defs = svg.append('defs');
    colores.forEach((c, i) => {
        const grad = defs.append('radialGradient').attr('id', 'gradDonut-' + i);
        grad.append('stop').attr('offset', '55%').attr('stop-color', c).attr('stop-opacity', 0.92);
        grad.append('stop').attr('offset', '100%').attr('stop-color', c);
    });

    const pie = d3.pie().value(d => d.tickets).sort(null).padAngle(0.02);
    const arcs = pie(datosEspecialistas);
    const total = d3.sum(datosEspecialistas, d => d.tickets);

    const arcGen = d3.arc().innerRadius(radius * 0.58).outerRadius(radius * 0.92).cornerRadius(4);

    const tooltipEl = document.getElementById('ttDonut');

    const paths = g.selectAll('path')
        .data(arcs).join('path')
        .attr('class', 'donut-arc')
        .attr('fill', (d, i) => `url(#gradDonut-${i})`)
        .style('--mark-glow', (d, i) => colores[i])
        .style('cursor', 'pointer')
        .on('pointermove', function (event, d) {
            const [mx, my] = d3.pointer(event, wrap);
            const pct = Math.round((d.data.tickets / total) * 100);
            showTooltip(tooltipEl, wrap, (el, title) => {
                title.textContent = d.data.nombre;
                addTooltipRow(el, 'Tickets', d.data.tickets + ' (' + pct + '%)', colores[arcs.indexOf(d)]);
            }, mx, my);
        })
        .on('pointerleave', function () { hideTooltip(tooltipEl); });

    paths.each(function (d, i) {
        const el = this;
        const interp = d3.interpolate(d.startAngle, d.endAngle);
        gsap.to({ t: 0 }, {
            t: 1,
            duration: 1.1,
            delay: i * 0.09,
            ease: 'back.out(1.15)',
            onUpdate() {
                const partial = { ...d, endAngle: interp(this.targets()[0].t) };
                el.setAttribute('d', arcGen(partial));
            },
        });
    });

    const centerLabel = g.append('text')
        .attr('text-anchor', 'middle')
        .attr('dy', '-0.2em')
        .attr('fill', cssVar('var(--text-main)'))
        .attr('font-size', 26)
        .attr('font-weight', 800)
        .text('0');
    g.append('text')
        .attr('text-anchor', 'middle')
        .attr('dy', '1.6em')
        .attr('fill', cssVar('var(--text-muted)'))
        .attr('font-size', 11)
        .text('TICKETS TOTALES');
    gsap.to({ n: 0 }, {
        n: total, duration: 1.2, ease: EASE_SETTLE,
        onUpdate() { centerLabel.text(Math.round(this.targets()[0].n)); },
    });

    const legend = document.getElementById('legendDonut');
    legend.innerHTML = '';
    datosEspecialistas.forEach((t, i) => {
        const item = document.createElement('div');
        item.className = 'legend-item';
        item.innerHTML = `<span class="legend-key dot" style="background:${colores[i]}"></span><span>${t.nombre}</span>`;
        legend.appendChild(item);
    });

    const table = document.getElementById('tableDonut');
    table.innerHTML = `
        <thead><tr><th>Especialista</th><th>Tickets</th><th>% del total</th></tr></thead>
        <tbody>${datosEspecialistas.map(d => `<tr><td>${d.nombre}</td><td>${d.tickets}</td><td>${Math.round((d.tickets / total) * 100)}%</td></tr>`).join('')}</tbody>
    `;
}

// ═══════════════════════════════════════════════════════════════════
// TOGGLE DE TABLA + ENTRADA GENERAL
// ═══════════════════════════════════════════════════════════════════
document.querySelectorAll('.btn-table-toggle').forEach(btn => {
    btn.addEventListener('click', () => {
        const table = document.getElementById(btn.dataset.target);
        const showing = table.classList.toggle('visible');
        btn.textContent = showing ? 'VER COMO GRÁFICO' : 'VER COMO TABLA';
    });
});

async function cargarDatosReales(rango, sede, prioridad) {
    let url = 'api_analitica_mia.php?rango=' + encodeURIComponent(rango);
    if (sede) url += '&sede=' + encodeURIComponent(sede);
    if (prioridad && prioridad !== 'todas') url += '&prioridad=' + encodeURIComponent(prioridad);
    const res = await fetch(url);
    if (!res.ok) throw new Error('HTTP ' + res.status);
    return res.json();
}

const ETIQUETAS_RANGO = {
    '6m': { desc: 'Últimos 6 meses', tituloBarras: 'Tickets por Mes y Prioridad', granularidadLinea: 'semanal' },
    '1m': { desc: 'Último mes', tituloBarras: 'Tickets por Día y Prioridad', granularidadLinea: 'diaria' },
    '1s': { desc: 'Última semana', tituloBarras: 'Tickets por Día y Prioridad', granularidadLinea: 'diaria' },
};
const ETIQUETAS_SEDE = {
    todas: 'Todas las sedes',
    'Molino Bogota': 'Bogotá',
    'Molino Pasto': 'Pasto',
    'Artesa Panaderia': 'Artesa',
    'Molino Buga': 'Buga',
    'No Especificado': 'Sin sede registrada',
};

let rangoActual = '6m';
let sedeActual = 'todas';
let prioridadActual = 'todas';

function marcarBotonActivo(grupo, valor) {
    document.querySelectorAll(`.${grupo}-btn`).forEach(b => b.classList.toggle('active', b.dataset[grupo] === valor));
}

async function cargarYRenderizar(rango, sede, prioridad, { animarEntrada = false } = {}) {
    const footer = document.getElementById('footerStatus');
    const botones = document.querySelectorAll('.rango-btn, .sede-btn, .tipo-btn');
    botones.forEach(b => b.disabled = true);

    let payload;
    try {
        payload = await cargarDatosReales(rango, sede, prioridad);
    } catch (err) {
        console.error('Error cargando datos de analítica mIA:', err);
        footer.textContent = 'ERROR AL CARGAR DATOS REALES · revisa la sesión o intenta de nuevo';
        if (animarEntrada) gsap.to('#footerStatus', { opacity: 1, duration: 0.5 });
        botones.forEach(b => b.disabled = false);
        return;
    }

    datosMensuales = payload.datosMensuales;
    datosSemanales = payload.datosSemanales;
    datosEspecialistas = payload.datosEspecialistas;
    kpisData = payload.kpis;

    const meta = payload.meta;
    sedeActual = meta.sede_filtro;
    marcarBotonActivo('sede', sedeActual);

    footer.textContent = `CONECTADO · ${meta.registros_leidos} tickets · sede: ${ETIQUETAS_SEDE[sedeActual] || sedeActual} · actualizado ${meta.generado_en}`;
    if (meta.duraciones_excluidas_por_anomalia > 0) {
        footer.textContent += ` · ${meta.duraciones_excluidas_por_anomalia} registro(s) excluido(s) del promedio de horas por fecha inconsistente`;
    }

    const info = ETIQUETAS_RANGO[rango];
    document.getElementById('tituloBarras').textContent = info.tituloBarras;
    document.getElementById('descBarras').textContent = `${info.desc} · ${ETIQUETAS_SEDE[sedeActual] || sedeActual} · baja, normal, alta y crítica`;
    const etiquetaPrioridadDesc = prioridad && prioridad !== 'todas' ? ` · ${prioridad[0].toUpperCase()}${prioridad.slice(1)}` : '';
    document.getElementById('descLinea').textContent = `Horas promedio por ticket, ${info.granularidadLinea}${etiquetaPrioridadDesc}`;

    ['chartBarras', 'chartLinea', 'chartDonut'].forEach(id => { document.getElementById(id).innerHTML = ''; });

    renderKPIs();
    renderBarras();
    renderLinea();
    renderDonut();

    botones.forEach(b => b.disabled = false);

    if (animarEntrada) {
        const tl = gsap.timeline();
        tl.to('.page-head', { opacity: 1, duration: 0.6, ease: EASE_SETTLE })
          .to('#rangoSelector', { opacity: 1, duration: 0.5, ease: EASE_SETTLE }, '-=0.3')
          .to('#sedeSelector', { opacity: 1, duration: 0.5, ease: EASE_SETTLE }, '-=0.3')
          .to('#cardBarras', { opacity: 1, y: 0, scale: 1, duration: 0.75, ease: EASE_SETTLE }, '-=0.2')
          .to('#cardLinea', { opacity: 1, y: 0, scale: 1, duration: 0.75, ease: EASE_SETTLE }, '-=0.55')
          .to('#prioridadSelector', { opacity: 1, duration: 0.4, ease: EASE_SETTLE }, '-=0.4')
          .to('#cardDonut', { opacity: 1, y: 0, scale: 1, duration: 0.75, ease: EASE_SETTLE }, '-=0.55')
          .to('#footerStatus', { opacity: 1, duration: 0.5 }, '-=0.2');
    }
}

document.addEventListener('DOMContentLoaded', async () => {
    gsap.set('.kpi-tile', { y: 12 });
    gsap.set('.chart-card', { y: 24, scale: 0.97 });

    document.querySelectorAll('.rango-btn').forEach(btn => {
        if (btn.dataset.rango === rangoActual) btn.classList.add('active');
        btn.addEventListener('click', () => {
            if (btn.dataset.rango === rangoActual || btn.disabled) return;
            rangoActual = btn.dataset.rango;
            marcarBotonActivo('rango', rangoActual);
            cargarYRenderizar(rangoActual, sedeActual, prioridadActual);
        });
    });

    document.querySelectorAll('.sede-btn').forEach(btn => {
        if (btn.dataset.sede === sedeActual) btn.classList.add('active');
        btn.addEventListener('click', () => {
            if (btn.dataset.sede === sedeActual || btn.disabled) return;
            sedeActual = btn.dataset.sede;
            marcarBotonActivo('sede', sedeActual);
            cargarYRenderizar(rangoActual, sedeActual, prioridadActual);
        });
    });

    document.querySelectorAll('#prioridadSelector .tipo-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            if (btn.dataset.tipo === prioridadActual || btn.disabled) return;
            prioridadActual = btn.dataset.tipo;
            document.querySelectorAll('#prioridadSelector .tipo-btn').forEach(b => b.classList.toggle('active', b.dataset.tipo === prioridadActual));
            cargarYRenderizar(rangoActual, sedeActual, prioridadActual);
        });
    });

    await cargarYRenderizar(rangoActual, sedeActual, prioridadActual, { animarEntrada: true });
});

window.addEventListener('resize', () => {
    document.getElementById('chartBarras').innerHTML = '';
    document.getElementById('chartLinea').innerHTML = '';
    document.getElementById('chartDonut').innerHTML = '';
    renderBarras();
    renderLinea();
    renderDonut();
});
    </script>
</body>
</html>
