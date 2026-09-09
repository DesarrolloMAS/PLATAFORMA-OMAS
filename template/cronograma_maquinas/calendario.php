<?php
require '../sesion.php';
verificarAutenticacion();
require '../conection.php';

$codigo = trim($_GET['codigo'] ?? '');
$stmt = $pdomaquinas->prepare("SELECT codigo, nombre, ubicacion FROM catalogo_maquinas WHERE codigo = :codigo AND activo = 1");
$stmt->execute([':codigo' => $codigo]);
$maquina = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$maquina) {
    http_response_code(404);
    echo "Máquina no encontrada en el catálogo.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($maquina['codigo']) ?> · <?= htmlspecialchars($maquina['nombre']) ?> · Organización MAS</title>
    <link rel="stylesheet" href="../../css/index.css">
    <link rel="stylesheet" href="../../css/menu_principal.css">
    <style>
        /* Tokens propios de este módulo — no tocan los de index.css, solo
           agregan los slots de prioridad que ese sistema no define (mismo
           criterio que --series-* en analitica/dashboard_bitacora.php). */
        :root {
            --priority-alta: var(--danger);
            --priority-media: #f2b134;
            --priority-baja: var(--success);
        }

        .cal-page {
            position: relative;
            z-index: 2;
            max-width: 1040px;
            margin: 0 auto;
            padding: 108px 20px 70px;
        }

        .cal-head {
            text-align: center;
            margin-bottom: clamp(28px, 5vw, 44px);
            opacity: 0;
            animation: menuFadeUp 0.6s var(--ease) forwards;
        }
        .cal-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--accent);
            margin-bottom: 10px;
        }
        .cal-eyebrow .dot {
            width: 6px; height: 6px; border-radius: 50%;
            background: var(--accent);
            box-shadow: 0 0 8px var(--accent-glow);
            animation: pulse 2.2s ease-in-out infinite;
        }
        .cal-title {
            font-size: clamp(22px, 3.2vw, 30px);
            font-weight: 700;
            letter-spacing: -0.01em;
            color: var(--text-main);
            margin: 0 0 8px;
        }
        .cal-sub {
            font-size: 14px;
            color: var(--text-muted);
            margin: 0;
        }

        .cal-card {
            background: var(--panel);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border: 1px solid var(--border);
            border-radius: var(--r-lg);
            padding: clamp(18px, 3vw, 30px);
            box-shadow:
                0 20px 60px rgba(30, 64, 175, 0.14),
                0 0 0 1px rgba(37, 99, 235, 0.05) inset;
            opacity: 0;
            animation: menuFadeUp 0.6s var(--ease) forwards;
            animation-delay: 0.08s;
            transition: border-color 0.4s var(--ease);
        }
        .cal-card:hover { border-color: var(--border-hover); }

        .cal-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 22px;
        }

        .cal-toolbar-left {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 18px;
        }

        .cal-view-toggle {
            display: flex;
            gap: 3px;
            background: var(--panel-solid);
            border: 1px solid var(--border);
            border-radius: var(--r-sm);
            padding: 3px;
        }
        .cal-view-btn {
            padding: 8px 14px;
            border: none;
            border-radius: calc(var(--r-sm) - 2px);
            background: transparent;
            color: var(--text-muted);
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.01em;
            cursor: pointer;
            transition: background 0.25s var(--ease), color 0.25s var(--ease), box-shadow 0.25s var(--ease);
        }
        .cal-view-btn:hover { color: var(--text-main); }
        .cal-view-btn.is-active {
            background: linear-gradient(135deg, var(--accent), var(--accent-2));
            color: #fff;
            box-shadow: 0 4px 14px var(--accent-glow);
        }

        .cal-nav {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .cal-nav-btn {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: 1px solid var(--border);
            background: var(--panel-solid);
            color: var(--accent);
            font-size: 17px;
            line-height: 1;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: border-color 0.25s var(--ease), box-shadow 0.25s var(--ease), transform 0.2s var(--ease);
        }
        .cal-nav-btn:hover {
            border-color: var(--border-hover);
            box-shadow: 0 0 0 4px var(--accent-glow);
            transform: translateY(-1px);
        }
        .cal-nav-btn:active { transform: translateY(0) scale(0.94); }

        .cal-month-label {
            font-weight: 700;
            font-size: 16px;
            color: var(--text-main);
            letter-spacing: 0.01em;
            min-width: 168px;
            text-align: center;
            text-transform: capitalize;
        }

        .btn-primary--sm {
            width: auto;
            padding: 11px 20px;
            font-size: 13px;
        }

        .cal-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 8px;
        }

        .cal-weekday {
            text-align: center;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: var(--text-faint);
            padding-bottom: 10px;
        }

        .cal-days { margin-top: 2px; transition: opacity 0.22s var(--ease); }
        .cal-days.is-switching { opacity: 0; }

        @keyframes calDayIn {
            from { opacity: 0; transform: translateY(8px) scale(0.98); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }

        .day-cell {
            position: relative;
            background: var(--panel-solid);
            border: 1px solid var(--border);
            border-radius: var(--r-md);
            min-height: 96px;
            padding: 8px;
            display: flex;
            flex-direction: column;
            gap: 4px;
            cursor: pointer;
            overflow: hidden;
            animation: calDayIn 0.4s var(--ease) backwards;
            transition: border-color 0.25s var(--ease), box-shadow 0.25s var(--ease), transform 0.25s var(--ease);
        }
        .day-cell:hover {
            border-color: var(--border-hover);
            box-shadow: 0 14px 30px rgba(37, 99, 235, 0.14);
            transform: translateY(-2px);
        }
        .day-cell.is-empty {
            background: transparent;
            border-color: transparent;
            cursor: default;
            box-shadow: none;
        }
        .day-cell.is-empty:hover { transform: none; box-shadow: none; border-color: transparent; }

        .day-cell.is-today {
            border-color: var(--accent);
            box-shadow: 0 0 0 1px var(--accent) inset, 0 10px 26px var(--accent-glow);
        }

        .day-number {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-faint);
        }
        .day-cell.is-today .day-number { color: var(--accent); }

        .task-chip {
            display: flex;
            align-items: center;
            gap: 5px;
            background: rgba(37, 99, 235, 0.06);
            border-radius: 6px;
            padding: 3px 6px;
            font-size: 10.5px;
            font-weight: 500;
            color: var(--text-main);
            overflow: hidden;
            transition: background 0.2s var(--ease), transform 0.2s var(--ease);
        }
        .task-chip:hover { background: rgba(37, 99, 235, 0.12); transform: translateX(1px); }
        .task-chip .dot {
            flex-shrink: 0;
            width: 6px; height: 6px; border-radius: 50%;
            background: var(--priority-media);
        }
        .task-chip.prioridad-alta .dot { background: var(--priority-alta); }
        .task-chip.prioridad-media .dot { background: var(--priority-media); }
        .task-chip.prioridad-baja .dot { background: var(--priority-baja); }
        .task-chip .label {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .task-chip.is-week .dot { border-radius: 2px; }
        .task-chip.is-week { border: 1px dashed rgba(37, 99, 235, 0.25); }

        .cal-loading {
            text-align: center;
            color: var(--text-faint);
            font-size: 12.5px;
            padding: 18px 0 4px;
            display: none;
        }

        .year-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 14px;
        }

        .month-card {
            background: var(--panel-solid);
            border: 1px solid var(--border);
            border-radius: var(--r-md);
            padding: 16px;
            cursor: pointer;
            animation: calDayIn 0.4s var(--ease) backwards;
            transition: border-color 0.25s var(--ease), box-shadow 0.25s var(--ease), transform 0.25s var(--ease);
        }
        .month-card:hover {
            border-color: var(--border-hover);
            box-shadow: 0 14px 30px rgba(37, 99, 235, 0.14);
            transform: translateY(-2px);
        }

        .month-card-head {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 14px;
            padding-bottom: 10px;
            border-bottom: 1px dashed var(--border);
        }
        .month-card-name {
            font-weight: 700;
            font-size: 14px;
            color: var(--text-main);
            text-transform: capitalize;
        }
        .month-card-count {
            font-size: 10.5px;
            font-weight: 600;
            color: var(--text-faint);
            text-transform: uppercase;
            letter-spacing: 0.03em;
            white-space: nowrap;
        }

        .month-weeks {
            display: flex;
            flex-direction: column;
            gap: 7px;
        }
        .week-bar {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 5px 7px;
            border-radius: 6px;
            transition: background 0.2s var(--ease);
        }
        .week-bar[data-clickable="1"] { cursor: pointer; }
        .week-bar[data-clickable="1"]:hover { background: rgba(37, 99, 235, 0.08); }
        .week-bar-label {
            flex-shrink: 0;
            width: 40px;
            font-size: 10px;
            font-weight: 600;
            color: var(--text-faint);
            text-transform: uppercase;
        }
        .week-bar-track {
            flex: 1;
            height: 6px;
            border-radius: 3px;
            background: var(--border);
            overflow: hidden;
        }
        .week-bar-fill {
            display: block;
            height: 100%;
            border-radius: 3px;
            width: 0%;
            transition: width 0.4s var(--ease);
        }
        .week-bar-fill.prioridad-alta { background: var(--priority-alta); }
        .week-bar-fill.prioridad-media { background: var(--priority-media); }
        .week-bar-fill.prioridad-baja { background: var(--priority-baja); }
        .week-bar-count {
            flex-shrink: 0;
            width: 16px;
            text-align: right;
            font-size: 10.5px;
            font-weight: 600;
            color: var(--text-muted);
        }
        .week-bar-count.is-empty {
            color: var(--accent);
            font-size: 13px;
            opacity: 0;
            transition: opacity 0.2s var(--ease);
        }
        .week-bar:hover .week-bar-count.is-empty { opacity: 1; }

        .week-task-row {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 4px;
            border-bottom: 1px dashed var(--border);
            cursor: pointer;
            transition: background 0.2s var(--ease);
        }
        .week-task-row:hover { background: rgba(37, 99, 235, 0.05); }
        .week-task-row:last-child { border-bottom: none; }
        .week-task-day {
            flex-shrink: 0;
            width: 30px;
            height: 30px;
            border-radius: 8px;
            background: var(--select-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
            color: var(--text-main);
        }

        .cal-footer {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            margin-top: clamp(30px, 5vw, 48px);
            opacity: 0;
            animation: menuFadeUp 0.6s var(--ease) forwards;
            animation-delay: 0.2s;
        }
        .cal-footer img { width: min(180px, 46vw); height: auto; }
        .cal-footer .tagline {
            font-size: 10.5px;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--text-faint);
            display: flex; gap: 8px; flex-wrap: wrap; justify-content: center;
        }
        .cal-footer .tagline .sep { color: var(--accent); }

        .modal-overlay {
            position: fixed;
            inset: 0;
            z-index: 60;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: rgba(6, 14, 28, 0.38);
            backdrop-filter: blur(7px);
            -webkit-backdrop-filter: blur(7px);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.35s var(--ease);
        }
        .modal-overlay.is-open { opacity: 1; pointer-events: auto; }

        .modal-panel {
            width: 100%;
            max-width: 520px;
            max-height: 86vh;
            overflow-y: auto;
            background: var(--panel-solid);
            border: 1px solid var(--border);
            border-radius: var(--r-lg);
            padding: 30px;
            box-shadow:
                0 30px 80px rgba(30, 64, 175, 0.25),
                0 0 0 1px rgba(37, 99, 235, 0.06) inset;
            transform: translateY(22px) scale(0.96);
            opacity: 0;
            transition: transform 0.4s var(--ease), opacity 0.4s var(--ease);
        }
        .modal-overlay.is-open .modal-panel {
            transform: translateY(0) scale(1);
            opacity: 1;
        }

        .modal-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 22px;
        }
        .modal-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-main);
            margin: 0;
        }
        .modal-close {
            flex-shrink: 0;
            width: 30px; height: 30px;
            border-radius: 50%;
            border: 1px solid var(--border);
            background: transparent;
            color: var(--text-muted);
            font-size: 15px;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: border-color 0.2s var(--ease), color 0.2s var(--ease), background 0.2s var(--ease);
        }
        .modal-close:hover { border-color: var(--danger); color: var(--danger); background: rgba(255, 84, 112, 0.06); }

        .modal-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 4px 16px;
        }

        #modalBody .field { opacity: 1; margin-bottom: 16px; }
        #modalBody .field:last-of-type { margin-bottom: 0; }

        .modal-actions {
            display: flex;
            gap: 12px;
            margin-top: 22px;
        }
        .modal-actions .btn-secondary,
        .modal-actions .btn-primary { width: auto; flex: 1; }

        .task-detail-row {
            display: flex;
            gap: 10px;
            padding: 10px 0;
            border-bottom: 1px dashed var(--border);
            font-size: 13.5px;
        }
        .task-detail-row:last-child { border-bottom: none; }
        .task-detail-label {
            flex-shrink: 0;
            width: 108px;
            color: var(--text-muted);
            font-weight: 600;
            font-size: 11.5px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            padding-top: 2px;
        }
        .task-detail-value { color: var(--text-main); }
        .priority-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 600;
            text-transform: capitalize;
        }
        .priority-pill .dot { width: 7px; height: 7px; border-radius: 50%; }
        .priority-pill.prioridad-alta .dot { background: var(--priority-alta); }
        .priority-pill.prioridad-media .dot { background: var(--priority-media); }
        .priority-pill.prioridad-baja .dot { background: var(--priority-baja); }

        .toast {
            position: fixed;
            bottom: 28px;
            left: 50%;
            transform: translateX(-50%) translateY(16px);
            display: flex;
            align-items: center;
            gap: 10px;
            background: var(--panel-solid);
            border: 1px solid var(--border);
            border-left: 3px solid var(--success);
            border-radius: var(--r-md);
            padding: 14px 20px;
            box-shadow: 0 20px 50px rgba(30, 64, 175, 0.2);
            font-size: 13.5px;
            font-weight: 500;
            color: var(--text-main);
            z-index: 80;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.35s var(--ease), transform 0.35s var(--ease);
        }
        .toast.is-visible { opacity: 1; transform: translateX(-50%) translateY(0); }
        .toast.is-error { border-left-color: var(--danger); }

        @media (max-width: 640px) {
            .cal-weekday span { display: none; }
            .cal-weekday::after { content: attr(data-short); }
            .day-cell { min-height: 62px; padding: 6px; }
            .task-chip .label { display: none; }
            .task-chip { justify-content: center; padding: 3px; }
            .cal-toolbar { justify-content: center; text-align: center; }
        }
    </style>
</head>
<body>
    <canvas id="bg-canvas"></canvas>
    <div class="bg-veil"></div>

    <a href="listado.php" class="menu-exit menu-exit--back">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19 8 12l7-7" />
        </svg>
        <span class="exit-label">Volver al listado</span>
    </a>

    <div class="cal-page">
        <div class="cal-head">
            <div class="cal-eyebrow"><span class="dot"></span><?= htmlspecialchars($maquina['ubicacion'] ?? 'Sin ubicación') ?> · <?= htmlspecialchars($maquina['codigo']) ?></div>
            <h1 class="cal-title"><?= htmlspecialchars($maquina['nombre']) ?></h1>
            <p class="cal-sub">Cronograma anual y programación de tareas de esta máquina</p>
        </div>

        <div class="cal-card">
            <div class="cal-toolbar">
                <div class="cal-toolbar-left">
                    <div class="cal-view-toggle">
                        <button type="button" class="cal-view-btn is-active" id="btnVistaMensual">Mensual</button>
                        <button type="button" class="cal-view-btn" id="btnVistaAnual">Anual</button>
                    </div>
                    <div class="cal-nav" id="navMensual">
                        <button type="button" class="cal-nav-btn" id="btnMesAnterior" aria-label="Mes anterior">‹</button>
                        <div class="cal-month-label" id="labelMes">—</div>
                        <button type="button" class="cal-nav-btn" id="btnMesSiguiente" aria-label="Mes siguiente">›</button>
                    </div>
                    <div class="cal-nav" id="navAnual" style="display:none;">
                        <button type="button" class="cal-nav-btn" id="btnAnioAnterior" aria-label="Año anterior">‹</button>
                        <div class="cal-month-label" id="labelAnio">—</div>
                        <button type="button" class="cal-nav-btn" id="btnAnioSiguiente" aria-label="Año siguiente">›</button>
                    </div>
                </div>
                <button type="button" class="btn-primary btn-primary--sm" id="btnNuevaTarea">+ Nueva tarea</button>
            </div>

            <div id="vistaMensualContenedor">
                <div class="cal-grid">
                    <div class="cal-weekday" data-short="D"><span>Domingo</span></div>
                    <div class="cal-weekday" data-short="L"><span>Lunes</span></div>
                    <div class="cal-weekday" data-short="M"><span>Martes</span></div>
                    <div class="cal-weekday" data-short="X"><span>Miércoles</span></div>
                    <div class="cal-weekday" data-short="J"><span>Jueves</span></div>
                    <div class="cal-weekday" data-short="V"><span>Viernes</span></div>
                    <div class="cal-weekday" data-short="S"><span>Sábado</span></div>
                </div>
                <div class="cal-grid cal-days" id="calendarGrid"></div>
            </div>

            <div id="vistaAnualContenedor" style="display:none;">
                <div class="year-grid" id="yearGrid"></div>
            </div>

            <div class="cal-loading" id="calendarLoading">Cargando tareas…</div>
        </div>

        <div class="cal-footer">
            <img src="../../img/logo_omas_azul.png" alt="Organización MAS">
            <div class="tagline">
                <span>Operaciones</span><span class="sep">•</span>
                <span>Mantenimiento</span><span class="sep">•</span>
                <span>Automatización</span><span class="sep">•</span>
                <span>Soluciones</span>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="modalOverlay">
        <div class="modal-panel" id="modalPanel">
            <div class="modal-head">
                <h2 class="modal-title" id="modalTitle">Nueva Tarea</h2>
                <button type="button" class="modal-close" id="modalClose" aria-label="Cerrar">✕</button>
            </div>
            <div id="modalBody"></div>
        </div>
    </div>

    <div class="toast" id="toast"></div>

    <script src="../../dist/app.js"></script>
    <script>
        const CODIGO_MAQUINA = <?= json_encode($maquina['codigo']) ?>;

        const MESES = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        const PRIORIDAD_LABEL = { alta: 'Alta', media: 'Media', baja: 'Baja' };
        const PRIORIDAD_RANGO = { alta: 3, media: 2, baja: 1 };

        const hoy = new Date();
        let anioActual = hoy.getFullYear();
        let mesActual = hoy.getMonth() + 1; // 1-12
        let vistaActual = 'mensual'; // 'mensual' | 'anual'

        const pad2 = n => String(n).padStart(2, '0');
        const mesISO = (anio, mes) => `${anio}-${pad2(mes)}`;

        function escapeHTML(str) {
            const div = document.createElement('div');
            div.textContent = str ?? '';
            return div.innerHTML;
        }

        // ---------- Toast ----------
        let toastTimer = null;
        function showToast(mensaje, esError) {
            const toast = document.getElementById('toast');
            toast.textContent = mensaje;
            toast.classList.toggle('is-error', !!esError);
            toast.classList.add('is-visible');
            clearTimeout(toastTimer);
            toastTimer = setTimeout(() => toast.classList.remove('is-visible'), 3200);
        }

        // ---------- Modal ----------
        const modalOverlay = document.getElementById('modalOverlay');
        const modalTitle = document.getElementById('modalTitle');
        const modalBody = document.getElementById('modalBody');

        function openModal(title, bodyHTML) {
            modalTitle.textContent = title;
            modalBody.innerHTML = bodyHTML;
            modalOverlay.classList.add('is-open');
            document.addEventListener('keydown', onEscClose);
        }
        function closeModal() {
            modalOverlay.classList.remove('is-open');
            document.removeEventListener('keydown', onEscClose);
        }
        function onEscClose(ev) { if (ev.key === 'Escape') closeModal(); }

        document.getElementById('modalClose').addEventListener('click', closeModal);
        modalOverlay.addEventListener('click', (ev) => {
            if (ev.target === modalOverlay) closeModal();
        });

        // ---------- Calendario ----------
        function cambiarMes(delta) {
            mesActual += delta;
            if (mesActual > 12) { mesActual = 1; anioActual++; }
            if (mesActual < 1) { mesActual = 12; anioActual--; }
            cargarMes();
        }

        function cargarMes() {
            const grid = document.getElementById('calendarGrid');
            const loading = document.getElementById('calendarLoading');
            document.getElementById('labelMes').textContent = `${MESES[mesActual - 1]} ${anioActual}`;

            grid.classList.add('is-switching');
            loading.style.display = 'block';

            fetch(`listar_tareas.php?codigo=${encodeURIComponent(CODIGO_MAQUINA)}&mes=${mesISO(anioActual, mesActual)}`)
                .then(r => r.json())
                .then(data => {
                    loading.style.display = 'none';
                    renderCalendario(data.status === 'success' ? (data.tareas || []) : []);
                })
                .catch(() => {
                    loading.style.display = 'none';
                    renderCalendario([]);
                });
        }

        function renderCalendario(tareas) {
            const grid = document.getElementById('calendarGrid');
            grid.innerHTML = '';
            grid.classList.remove('is-switching');

            const primerDiaSemana = new Date(anioActual, mesActual - 1, 1).getDay();
            const diasEnMes = new Date(anioActual, mesActual, 0).getDate();
            const esMesActualReal = (anioActual === hoy.getFullYear() && mesActual === hoy.getMonth() + 1);

            const tareasPorDia = {};
            const agregarEnDia = (dia, t) => { (tareasPorDia[dia] ||= []).push(t); };

            tareas.forEach(t => {
                const d = t.datos || {};
                if (d.tipo === 'semana' && d.semana) {
                    diasDeSemana(d.semana, diasEnMes).forEach(dia => agregarEnDia(dia, t));
                } else if (d.fecha) {
                    agregarEnDia(parseInt(d.fecha.split('-')[2], 10), t);
                }
            });

            let indiceCelda = 0;

            for (let i = 0; i < primerDiaSemana; i++) {
                const vacio = document.createElement('div');
                vacio.className = 'day-cell is-empty';
                grid.appendChild(vacio);
            }

            for (let dia = 1; dia <= diasEnMes; dia++) {
                const celda = document.createElement('div');
                celda.className = 'day-cell';
                if (esMesActualReal && dia === hoy.getDate()) celda.classList.add('is-today');
                celda.style.animationDelay = (indiceCelda * 0.012) + 's';
                indiceCelda++;

                const numero = document.createElement('div');
                numero.className = 'day-number';
                numero.textContent = dia;
                celda.appendChild(numero);

                (tareasPorDia[dia] || []).forEach(tarea => {
                    const prioridad = (tarea.datos && tarea.datos.prioridad) || 'media';
                    const esSemana = tarea.datos && tarea.datos.tipo === 'semana';
                    const chip = document.createElement('div');
                    chip.className = `task-chip prioridad-${prioridad}${esSemana ? ' is-week' : ''}`;
                    chip.title = esSemana ? 'Tarea de toda la semana, sin día exacto' : '';
                    chip.innerHTML = `<span class="dot"></span><span class="label"></span>`;
                    chip.querySelector('.label').textContent = (tarea.datos && tarea.datos.titulo) || 'Tarea';
                    chip.addEventListener('click', (ev) => {
                        ev.stopPropagation();
                        abrirDetalleTarea(tarea);
                    });
                    celda.appendChild(chip);
                });

                celda.addEventListener('click', () => {
                    const fechaClic = `${anioActual}-${pad2(mesActual)}-${pad2(dia)}`;
                    abrirFormularioTarea(fechaClic);
                });

                grid.appendChild(celda);
            }
        }

        // ---------- Vista (mensual / anual) ----------
        function cambiarVista(nueva) {
            if (vistaActual === nueva) return;
            vistaActual = nueva;

            document.getElementById('btnVistaMensual').classList.toggle('is-active', nueva === 'mensual');
            document.getElementById('btnVistaAnual').classList.toggle('is-active', nueva === 'anual');
            document.getElementById('navMensual').style.display = nueva === 'mensual' ? 'flex' : 'none';
            document.getElementById('navAnual').style.display = nueva === 'anual' ? 'flex' : 'none';
            document.getElementById('vistaMensualContenedor').style.display = nueva === 'mensual' ? '' : 'none';
            document.getElementById('vistaAnualContenedor').style.display = nueva === 'anual' ? '' : 'none';

            if (nueva === 'mensual') cargarMes();
            else cargarAnio();
        }

        function cambiarAnio(delta) {
            anioActual += delta;
            cargarAnio();
        }

        function cargarAnio() {
            document.getElementById('labelAnio').textContent = anioActual;
            const loading = document.getElementById('calendarLoading');
            loading.style.display = 'block';

            fetch(`listar_tareas_anio.php?codigo=${encodeURIComponent(CODIGO_MAQUINA)}&anio=${anioActual}`)
                .then(r => r.json())
                .then(data => {
                    loading.style.display = 'none';
                    renderAnio(data.status === 'success' ? (data.meses || {}) : {});
                })
                .catch(() => {
                    loading.style.display = 'none';
                    renderAnio({});
                });
        }

        function bucketSemana(dia) {
            return Math.min(4, Math.ceil(dia / 7));
        }

        function diasDeSemana(semana, diasEnMes) {
            const inicio = (semana - 1) * 7 + 1;
            const fin = semana === 4 ? diasEnMes : Math.min(semana * 7, diasEnMes);
            const dias = [];
            for (let d = inicio; d <= fin; d++) dias.push(d);
            return dias;
        }

        function renderAnio(mesesData) {
            const grid = document.getElementById('yearGrid');
            grid.innerHTML = '';

            for (let m = 1; m <= 12; m++) {
                const mesKey = pad2(m);
                const tareasMes = mesesData[mesKey] || [];

                const semanas = { 1: [], 2: [], 3: [], 4: [] };
                tareasMes.forEach(t => {
                    const d = t.datos || {};
                    if (d.tipo === 'semana' && d.semana) {
                        semanas[d.semana].push(t);
                    } else if (d.fecha) {
                        semanas[bucketSemana(parseInt(d.fecha.split('-')[2], 10))].push(t);
                    }
                });

                const card = document.createElement('div');
                card.className = 'month-card';
                card.style.animationDelay = ((m - 1) * 0.03) + 's';

                const head = document.createElement('div');
                head.className = 'month-card-head';
                head.innerHTML = `
                    <span class="month-card-name">${MESES[m - 1]}</span>
                    <span class="month-card-count">${tareasMes.length} tarea${tareasMes.length === 1 ? '' : 's'}</span>
                `;
                head.style.cursor = 'pointer';
                head.addEventListener('click', () => {
                    mesActual = m;
                    cambiarVista('mensual');
                });
                card.appendChild(head);

                const weeksWrap = document.createElement('div');
                weeksWrap.className = 'month-weeks';

                [1, 2, 3, 4].forEach(s => {
                    const tareasSemana = semanas[s];
                    const count = tareasSemana.length;

                    let prioridadTop = null;
                    tareasSemana.forEach(t => {
                        const p = (t.datos && t.datos.prioridad) || 'media';
                        if (!prioridadTop || PRIORIDAD_RANGO[p] > PRIORIDAD_RANGO[prioridadTop]) prioridadTop = p;
                    });

                    const bar = document.createElement('div');
                    bar.className = 'week-bar';
                    const ancho = count === 0 ? 0 : Math.min(100, count * 25);
                    const claseFill = prioridadTop ? `prioridad-${prioridadTop}` : '';

                    bar.innerHTML = `
                        <span class="week-bar-label">Sem ${s}</span>
                        <span class="week-bar-track"><span class="week-bar-fill ${claseFill}" style="width:${ancho}%"></span></span>
                        <span class="week-bar-count${count === 0 ? ' is-empty' : ''}">${count > 0 ? count : '+'}</span>
                    `;

                    bar.dataset.clickable = '1';
                    bar.title = count > 0 ? 'Ver tareas de esta semana' : 'Agregar tarea para esta semana';
                    bar.addEventListener('click', (ev) => {
                        ev.stopPropagation();
                        if (count > 0) {
                            abrirDetalleSemana(m, s, tareasSemana);
                        } else {
                            abrirFormularioTareaSemana(anioActual, m, s);
                        }
                    });

                    weeksWrap.appendChild(bar);
                });

                card.appendChild(weeksWrap);
                grid.appendChild(card);
            }
        }

        function abrirDetalleSemana(mesNum, semana, tareas) {
            const filas = tareas
                .slice()
                .sort((a, b) => (a.datos.fecha || '').localeCompare(b.datos.fecha || ''))
                .map((t, idx) => {
                    const d = t.datos || {};
                    const p = d.prioridad || 'media';
                    const dia = d.tipo === 'semana' ? `S${d.semana}` : (d.fecha ? d.fecha.split('-')[2] : '—');
                    return `
                        <div class="week-task-row" data-idx="${idx}">
                            <div class="week-task-day">${escapeHTML(dia)}</div>
                            <div style="flex:1;">
                                <div style="font-size:13.5px; font-weight:600; color:var(--text-main);">${escapeHTML(d.titulo || 'Tarea')}</div>
                                <span class="priority-pill prioridad-${p}" style="margin-top:4px;"><span class="dot"></span>${PRIORIDAD_LABEL[p] || p}</span>
                            </div>
                        </div>
                    `;
                })
                .join('');

            openModal(`${MESES[mesNum - 1]} · Semana ${semana}`, `
                <div id="listaSemana">${filas}</div>
                <div class="modal-actions" style="margin-top:18px;">
                    <button type="button" class="btn-primary" id="btnAgregarTareaSemana" style="width:100%;">+ Agregar tarea a esta semana</button>
                </div>
            `);

            const ordenadas = tareas.slice().sort((a, b) => (a.datos.fecha || '').localeCompare(b.datos.fecha || ''));
            document.querySelectorAll('#listaSemana .week-task-row').forEach((row) => {
                const idx = parseInt(row.dataset.idx, 10);
                row.addEventListener('click', () => abrirDetalleTarea(ordenadas[idx]));
            });
            document.getElementById('btnAgregarTareaSemana').addEventListener('click', () => {
                abrirFormularioTareaSemana(anioActual, mesNum, semana);
            });
        }

        // ---------- Ver detalle ----------
        function abrirDetalleTarea(tarea) {
            const d = tarea.datos || {};
            const prioridad = d.prioridad || 'media';
            const esSemana = d.tipo === 'semana';
            const fechaTexto = esSemana
                ? `Semana ${d.semana} de ${MESES[parseInt(d.mes, 10) - 1]} ${d.anio} (sin día exacto)`
                : (d.fecha || '—');
            openModal(escapeHTML(d.titulo || 'Tarea'), `
                <div class="task-detail-row">
                    <div class="task-detail-label">Descripción</div>
                    <div class="task-detail-value">${escapeHTML(d.descripcion || 'Sin descripción')}</div>
                </div>
                <div class="task-detail-row">
                    <div class="task-detail-label">Fecha</div>
                    <div class="task-detail-value">${escapeHTML(fechaTexto)}</div>
                </div>
                <div class="task-detail-row">
                    <div class="task-detail-label">Prioridad</div>
                    <div class="task-detail-value">
                        <span class="priority-pill prioridad-${prioridad}"><span class="dot"></span>${PRIORIDAD_LABEL[prioridad] || prioridad}</span>
                    </div>
                </div>
                <div class="task-detail-row">
                    <div class="task-detail-label">Responsable</div>
                    <div class="task-detail-value">${escapeHTML(d.responsable || '—')}</div>
                </div>
                <div class="task-detail-row">
                    <div class="task-detail-label">Creado por</div>
                    <div class="task-detail-value">${escapeHTML(tarea.usuario_sys || '—')}</div>
                </div>
            `);
        }

        // ---------- Crear tarea ----------
        let tipoTareaActual = 'dia';

        function abrirFormularioTarea(fechaPrellenada, prefillSemana) {
            tipoTareaActual = prefillSemana ? 'semana' : 'dia';

            const fecha = fechaPrellenada || `${hoy.getFullYear()}-${pad2(hoy.getMonth() + 1)}-${pad2(hoy.getDate())}`;
            const [anioDiaDefault, mesDiaDefault] = fecha.split('-');

            const anioDefault = prefillSemana ? String(prefillSemana.anio) : anioDiaDefault;
            const mesDefault = prefillSemana ? prefillSemana.mes : mesDiaDefault;
            const semanaDefault = prefillSemana ? prefillSemana.semana : 1;

            const opcionesMes = MESES.map((nombre, idx) => {
                const val = pad2(idx + 1);
                return `<option value="${val}" ${val === mesDefault ? 'selected' : ''}>${nombre}</option>`;
            }).join('');

            const opcionesSemana = [
                [1, 'Semana 1 (días 1-7)'],
                [2, 'Semana 2 (días 8-14)'],
                [3, 'Semana 3 (días 15-21)'],
                [4, 'Semana 4 (días 22 en adelante)']
            ].map(([val, label]) => `<option value="${val}" ${val === semanaDefault ? 'selected' : ''}>${label}</option>`).join('');

            openModal('Nueva Tarea de Mantenimiento', `
                <div class="cal-view-toggle" style="margin-bottom:20px;">
                    <button type="button" class="cal-view-btn${tipoTareaActual === 'dia' ? ' is-active' : ''}" id="btnTipoDia">Día específico</button>
                    <button type="button" class="cal-view-btn${tipoTareaActual === 'semana' ? ' is-active' : ''}" id="btnTipoSemana">Semana completa</button>
                </div>

                <div class="field">
                    <label for="campoTitulo">Título</label>
                    <input type="text" id="campoTitulo" placeholder="Ej: Lubricación motor principal">
                </div>
                <div class="field">
                    <label for="campoDescripcion">Descripción</label>
                    <input type="text" id="campoDescripcion" placeholder="Detalle de la tarea (opcional)">
                </div>

                <div id="bloqueTipoDia" style="${tipoTareaActual === 'semana' ? 'display:none;' : ''}">
                    <div class="field">
                        <label for="campoFecha">Fecha</label>
                        <input type="date" id="campoFecha" value="${fecha}">
                    </div>
                </div>

                <div id="bloqueTipoSemana" style="${tipoTareaActual === 'semana' ? '' : 'display:none;'}">
                    <div class="modal-grid">
                        <div class="field">
                            <label for="campoMes">Mes</label>
                            <select id="campoMes">${opcionesMes}</select>
                        </div>
                        <div class="field">
                            <label for="campoAnio">Año</label>
                            <input type="number" id="campoAnio" value="${anioDefault}" min="2020" max="2100">
                        </div>
                    </div>
                    <div class="field">
                        <label for="campoSemana">Semana del mes</label>
                        <select id="campoSemana">${opcionesSemana}</select>
                    </div>
                </div>

                <div class="modal-grid">
                    <div class="field">
                        <label for="campoPrioridad">Prioridad</label>
                        <select id="campoPrioridad">
                            <option value="baja">Baja</option>
                            <option value="media" selected>Media</option>
                            <option value="alta">Alta</option>
                        </select>
                    </div>
                    <div class="field">
                        <label for="campoResponsable">Responsable</label>
                        <input type="text" id="campoResponsable" placeholder="Opcional">
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-secondary" id="btnCancelarTarea">Cancelar</button>
                    <button type="button" class="btn-primary" id="btnCrearTarea">Crear Tarea</button>
                </div>
            `);

            document.getElementById('btnTipoDia').addEventListener('click', () => cambiarTipoTarea('dia'));
            document.getElementById('btnTipoSemana').addEventListener('click', () => cambiarTipoTarea('semana'));
            document.getElementById('btnCancelarTarea').addEventListener('click', closeModal);
            document.getElementById('btnCrearTarea').addEventListener('click', enviarTarea);
        }

        function abrirFormularioTareaSemana(anio, mes, semana) {
            abrirFormularioTarea(null, { anio, mes: pad2(mes), semana });
        }

        function cambiarTipoTarea(tipo) {
            tipoTareaActual = tipo;
            document.getElementById('btnTipoDia').classList.toggle('is-active', tipo === 'dia');
            document.getElementById('btnTipoSemana').classList.toggle('is-active', tipo === 'semana');
            document.getElementById('bloqueTipoDia').style.display = tipo === 'dia' ? '' : 'none';
            document.getElementById('bloqueTipoSemana').style.display = tipo === 'semana' ? '' : 'none';
        }

        function enviarTarea() {
            const titulo = document.getElementById('campoTitulo').value.trim();
            if (!titulo) {
                showToast('El título es obligatorio', true);
                return;
            }

            const datos = {
                codigo: CODIGO_MAQUINA,
                tipo: tipoTareaActual,
                titulo,
                descripcion: document.getElementById('campoDescripcion').value.trim(),
                prioridad: document.getElementById('campoPrioridad').value,
                responsable: document.getElementById('campoResponsable').value.trim()
            };

            if (tipoTareaActual === 'semana') {
                const mes = document.getElementById('campoMes').value;
                const anio = document.getElementById('campoAnio').value;
                const semana = document.getElementById('campoSemana').value;
                if (!mes || !anio || !semana) {
                    showToast('Selecciona mes, año y semana', true);
                    return;
                }
                datos.mes = mes;
                datos.anio = anio;
                datos.semana = parseInt(semana, 10);
            } else {
                const fecha = document.getElementById('campoFecha').value;
                if (!fecha) {
                    showToast('La fecha es obligatoria', true);
                    return;
                }
                datos.fecha = fecha;
            }

            const boton = document.getElementById('btnCrearTarea');
            boton.disabled = true;
            boton.textContent = 'Guardando…';

            fetch('procesar_tarea.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(datos)
            })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'success') {
                    closeModal();
                    showToast('Tarea creada correctamente');
                    if (vistaActual === 'anual') cargarAnio(); else cargarMes();
                } else {
                    boton.disabled = false;
                    boton.textContent = 'Crear Tarea';
                    showToast(data.message || 'No se pudo crear la tarea', true);
                }
            })
            .catch(() => {
                boton.disabled = false;
                boton.textContent = 'Crear Tarea';
                showToast('Error de conexión al guardar la tarea', true);
            });
        }

        document.getElementById('btnMesAnterior').addEventListener('click', () => cambiarMes(-1));
        document.getElementById('btnMesSiguiente').addEventListener('click', () => cambiarMes(1));
        document.getElementById('btnVistaMensual').addEventListener('click', () => cambiarVista('mensual'));
        document.getElementById('btnVistaAnual').addEventListener('click', () => cambiarVista('anual'));
        document.getElementById('btnAnioAnterior').addEventListener('click', () => cambiarAnio(-1));
        document.getElementById('btnAnioSiguiente').addEventListener('click', () => cambiarAnio(1));
        document.getElementById('btnNuevaTarea').addEventListener('click', () => {
            const hoyISO = `${hoy.getFullYear()}-${pad2(hoy.getMonth() + 1)}-${pad2(hoy.getDate())}`;
            abrirFormularioTarea(hoyISO);
        });

        cargarMes();
    </script>
</body>
</html>
