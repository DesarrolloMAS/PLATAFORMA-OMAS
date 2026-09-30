<?php
// Bandeja de entrada — sin restricción de rol ni área, solo sesión.
// Estructura visual tomada de cronograma_maquinas/calendario_general.php
// (cabecera, barra lateral, tarjeta principal, modal y toast), en el tema
// oscuro del hub del que se entra (menu_usuario.html).
require '../sesion.php';
verificarAutenticacion();

$area = $_SESSION['area'] ?? '';

// "Volver al menú" y paleta según desde qué hub se entró. Lista cerrada:
// cualquier otro valor de ?desde= cae en el menú de usuario de Operaciones.
$origenes = [
    'admin_calidad' => ['url' => '../menu_administracion_calidad.html', 'tema' => 'theme-invert theme-quality'],
];
$origen = $origenes[$_GET['desde'] ?? ''] ?? ['url' => '../menu_usuario.html', 'tema' => 'theme-invert'];
$nombre = $_SESSION['nombre'] ?? '';
$primerNombre = explode(' ', trim($nombre))[0] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bandeja de Entrada · Organización MAS</title>
    <link rel="stylesheet" href="../../css/index.css">
    <link rel="stylesheet" href="../../css/menu_principal.css">
    <style>
        :root {
            --tipo-info: var(--accent);
            --tipo-aviso: #f2b134;
            --tipo-alerta: var(--danger);
            --tipo-exito: var(--success);
        }

        .inbox-page {
            position: relative;
            z-index: 2;
            max-width: 1180px;
            margin: 0 auto;
            padding: 108px 20px 70px;
        }

        .inbox-head {
            text-align: center;
            margin-bottom: clamp(24px, 4vw, 36px);
            opacity: 0;
            animation: menuFadeUp 0.6s var(--ease) forwards;
        }
        .inbox-eyebrow {
            display: inline-flex; align-items: center; gap: 8px;
            font-size: 12px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase;
            color: var(--accent); margin-bottom: 10px;
        }
        .inbox-eyebrow .dot {
            width: 6px; height: 6px; border-radius: 50%;
            background: var(--accent); box-shadow: 0 0 8px var(--accent-glow);
            animation: pulse 2.2s ease-in-out infinite;
        }
        .inbox-title { font-size: clamp(22px, 3.2vw, 30px); font-weight: 700; letter-spacing: -0.01em; color: var(--text-main); margin: 0 0 8px; }
        .inbox-sub { font-size: 14px; color: var(--text-muted); margin: 0; }

        .inbox-layout { display: flex; gap: 22px; align-items: flex-start; }

        /* ---------- Barra lateral: filtros ---------- */
        .inbox-sidebar {
            width: 260px; flex-shrink: 0;
            background: var(--panel);
            backdrop-filter: blur(18px); -webkit-backdrop-filter: blur(18px);
            border: 1px solid var(--border); border-radius: var(--r-lg);
            padding: 18px;
            position: sticky; top: 100px;
            opacity: 0; animation: menuFadeUp 0.6s var(--ease) forwards; animation-delay: 0.04s;
        }
        .sidebar-title {
            font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;
            color: var(--text-muted); margin: 0 0 10px;
        }
        .sidebar-title + .filtro-lista { margin-bottom: 18px; }
        .filtro-lista { display: flex; flex-direction: column; gap: 2px; }
        .filtro-item {
            display: flex; align-items: center; gap: 10px; width: 100%;
            background: transparent; border: 1px solid transparent; border-radius: var(--r-sm);
            padding: 8px 10px; cursor: pointer; font-family: inherit; font-size: 13px;
            color: var(--text-main); text-align: left;
            transition: background 0.2s var(--ease), border-color 0.2s var(--ease);
        }
        .filtro-item:hover { background: rgba(37, 99, 235, 0.08); border-color: var(--border); }
        .filtro-item.is-active { background: rgba(37, 99, 235, 0.14); border-color: var(--border-hover); }
        .filtro-item .dot { flex-shrink: 0; width: 7px; height: 7px; border-radius: 50%; background: var(--text-faint); }
        .filtro-item .filtro-nombre { flex: 1; }
        .filtro-item .filtro-count {
            flex-shrink: 0; min-width: 22px; padding: 1px 7px; border-radius: 999px;
            background: var(--panel-solid); border: 1px solid var(--border);
            font-size: 10.5px; font-weight: 700; color: var(--text-muted); text-align: center;
        }
        .tipo-info .dot   { background: var(--tipo-info); }
        .tipo-aviso .dot  { background: var(--tipo-aviso); }
        .tipo-alerta .dot { background: var(--tipo-alerta); }
        .tipo-exito .dot  { background: var(--tipo-exito); }

        /* ---------- Tarjeta principal ---------- */
        .inbox-main { flex: 1; min-width: 0; }
        .inbox-card {
            background: var(--panel);
            backdrop-filter: blur(18px); -webkit-backdrop-filter: blur(18px);
            border: 1px solid var(--border); border-radius: var(--r-lg);
            padding: clamp(18px, 3vw, 30px);
            box-shadow: 0 20px 60px rgba(30, 64, 175, 0.14), 0 0 0 1px rgba(37, 99, 235, 0.05) inset;
            opacity: 0; animation: menuFadeUp 0.6s var(--ease) forwards; animation-delay: 0.08s;
            transition: border-color 0.4s var(--ease);
        }
        .inbox-card:hover { border-color: var(--border-hover); }

        .inbox-toolbar {
            display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap;
            gap: 16px; margin-bottom: 22px;
        }
        .inbox-toolbar-left { display: flex; align-items: center; flex-wrap: wrap; gap: 18px; }

        .view-toggle {
            display: flex; gap: 3px;
            background: var(--panel-solid); border: 1px solid var(--border); border-radius: var(--r-sm); padding: 3px;
        }
        .view-btn {
            padding: 8px 14px; border: none; border-radius: calc(var(--r-sm) - 2px);
            background: transparent; color: var(--text-muted);
            font-family: inherit; font-size: 12px; font-weight: 600; letter-spacing: 0.01em; cursor: pointer;
            transition: background 0.25s var(--ease), color 0.25s var(--ease), box-shadow 0.25s var(--ease);
        }
        .view-btn:hover { color: var(--text-main); }
        .view-btn.is-active {
            background: linear-gradient(135deg, var(--accent), var(--accent-2));
            color: #fff; box-shadow: 0 4px 14px var(--accent-glow);
        }

        .inbox-nav { display: flex; align-items: center; gap: 16px; }
        .nav-btn {
            width: 36px; height: 36px; border-radius: 50%;
            border: 1px solid var(--border); background: var(--panel-solid); color: var(--accent);
            font-size: 17px; line-height: 1; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: border-color 0.25s var(--ease), box-shadow 0.25s var(--ease), transform 0.2s var(--ease);
        }
        .nav-btn:hover { border-color: var(--border-hover); box-shadow: 0 0 0 4px var(--accent-glow); transform: translateY(-1px); }
        .nav-btn:active { transform: translateY(0) scale(0.94); }
        .nav-btn:disabled { opacity: 0.35; pointer-events: none; }
        .month-label {
            font-weight: 700; font-size: 16px; color: var(--text-main);
            letter-spacing: 0.01em; min-width: 168px; text-align: center; text-transform: capitalize;
        }

        .btn-marcar-todas {
            padding: 8px 14px; background: transparent;
            border: 1px dashed var(--border); border-radius: var(--r-sm);
            color: var(--text-muted); font-family: inherit; font-size: 12px; font-weight: 600; cursor: pointer;
            transition: color 0.2s var(--ease), border-color 0.2s var(--ease);
        }
        .btn-marcar-todas:hover { color: var(--text-main); border-color: var(--border-hover); }
        .btn-marcar-todas:disabled { opacity: 0.4; pointer-events: none; }

        /* ---------- Lista agrupada por día ---------- */
        .inbox-list { transition: opacity 0.22s var(--ease); }
        .inbox-list.is-switching { opacity: 0; }

        @keyframes inboxRowIn {
            from { opacity: 0; transform: translateY(8px) scale(0.99); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }

        .day-group + .day-group { margin-top: 18px; }
        .day-group-head {
            display: flex; align-items: center; gap: 10px;
            font-size: 11px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase;
            color: var(--text-faint); margin-bottom: 8px;
        }
        .day-group-head::after { content: ""; flex: 1; border-bottom: 1px dashed var(--border); }
        .day-group-head.is-today { color: var(--accent); }

        .ntf-row {
            position: relative;
            display: flex; align-items: flex-start; gap: 14px;
            background: var(--panel-solid); border: 1px solid var(--border); border-radius: var(--r-md);
            padding: 14px 16px; cursor: pointer;
            animation: inboxRowIn 0.4s var(--ease) backwards;
            transition: border-color 0.25s var(--ease), transform 0.2s var(--ease), box-shadow 0.25s var(--ease);
        }
        .ntf-row + .ntf-row { margin-top: 8px; }
        .ntf-row:hover { border-color: var(--border-hover); transform: translateX(2px); box-shadow: 0 10px 26px var(--accent-glow); }
        .ntf-row.is-unread { box-shadow: inset 3px 0 0 var(--tipo-color, var(--accent)); }
        .ntf-row.is-unread:hover { box-shadow: inset 3px 0 0 var(--tipo-color, var(--accent)), 0 10px 26px var(--accent-glow); }

        .ntf-icon {
            flex-shrink: 0; width: 38px; height: 38px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            background: var(--select-bg); color: var(--tipo-color, var(--accent));
            border: 1px solid var(--border);
        }
        .ntf-icon svg { width: 20px; height: 20px; }
        .ntf-body { flex: 1; min-width: 0; }
        .ntf-top { display: flex; align-items: baseline; justify-content: space-between; gap: 12px; }
        .ntf-titulo { font-size: 14px; font-weight: 600; color: var(--text-main); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .ntf-row:not(.is-unread) .ntf-titulo { font-weight: 500; color: var(--text-muted); }
        .ntf-hora { flex-shrink: 0; font-size: 11px; font-weight: 600; color: var(--text-faint); }
        .ntf-mensaje {
            margin-top: 3px; font-size: 12.5px; color: var(--text-muted); line-height: 1.45;
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
        }
        .ntf-modulo {
            display: inline-block; margin-top: 7px; padding: 2px 8px; border-radius: 999px;
            font-size: 10px; font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase;
            color: var(--accent); background: rgba(37, 99, 235, 0.10);
        }
        .ntf-unread-dot {
            position: absolute; top: 14px; right: 14px;
            width: 8px; height: 8px; border-radius: 50%;
            background: var(--tipo-color, var(--accent)); box-shadow: 0 0 8px var(--tipo-color, var(--accent));
        }
        .ntf-row.is-unread .ntf-hora { margin-right: 16px; }

        .ntf-row.tipo-info   { --tipo-color: var(--tipo-info); }
        .ntf-row.tipo-aviso  { --tipo-color: var(--tipo-aviso); }
        .ntf-row.tipo-alerta { --tipo-color: var(--tipo-alerta); }
        .ntf-row.tipo-exito  { --tipo-color: var(--tipo-exito); }

        .inbox-empty {
            display: flex; flex-direction: column; align-items: center; gap: 10px;
            padding: 48px 10px; text-align: center; color: var(--text-faint); font-size: 13px;
        }
        .inbox-empty svg { width: 42px; height: 42px; color: var(--border-hover); }
        .inbox-loading { text-align: center; color: var(--text-faint); font-size: 12.5px; padding: 18px 0 4px; }

        .inbox-footer {
            display: flex; flex-direction: column; align-items: center; gap: 10px;
            margin-top: clamp(30px, 5vw, 48px); opacity: 0;
            animation: menuFadeUp 0.6s var(--ease) forwards; animation-delay: 0.2s;
        }
        .inbox-footer img { width: min(180px, 46vw); height: auto; }

        /* ---------- Modal de detalle ---------- */
        .modal-overlay {
            position: fixed; inset: 0; z-index: 60; display: flex; align-items: center; justify-content: center;
            padding: 20px; background: rgba(2, 4, 8, 0.55);
            backdrop-filter: blur(7px); -webkit-backdrop-filter: blur(7px);
            opacity: 0; pointer-events: none; transition: opacity 0.35s var(--ease);
        }
        .modal-overlay.is-open { opacity: 1; pointer-events: auto; }
        .modal-panel {
            width: 100%; max-width: 520px; max-height: 86vh; overflow-y: auto;
            background: var(--panel-solid); border: 1px solid var(--border); border-radius: var(--r-lg);
            padding: 30px;
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.45), 0 0 0 1px rgba(37, 99, 235, 0.08) inset;
            transform: translateY(22px) scale(0.96); opacity: 0;
            transition: transform 0.4s var(--ease), opacity 0.4s var(--ease);
        }
        .modal-overlay.is-open .modal-panel { transform: translateY(0) scale(1); opacity: 1; }
        .modal-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 14px; margin-bottom: 18px; }
        .modal-title { font-size: 18px; font-weight: 700; color: var(--text-main); margin: 0; }
        .modal-close {
            flex-shrink: 0; width: 30px; height: 30px; border-radius: 50%; border: 1px solid var(--border);
            background: transparent; color: var(--text-muted); font-size: 15px; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: border-color 0.2s var(--ease), color 0.2s var(--ease), background 0.2s var(--ease);
        }
        .modal-close:hover { border-color: var(--danger); color: var(--danger); background: rgba(255, 84, 112, 0.06); }
        .modal-mensaje { font-size: 14px; line-height: 1.6; color: var(--text-main); margin: 0 0 18px; white-space: pre-line; }
        .detail-row { display: flex; gap: 10px; padding: 10px 0; border-bottom: 1px dashed var(--border); font-size: 13.5px; }
        .detail-row:last-child { border-bottom: none; }
        .detail-label {
            flex-shrink: 0; width: 96px; color: var(--text-muted); font-weight: 600; font-size: 11.5px;
            text-transform: uppercase; letter-spacing: 0.04em; padding-top: 2px;
        }
        .detail-value { color: var(--text-main); }
        .tipo-pill { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; }
        .tipo-pill .dot { width: 7px; height: 7px; border-radius: 50%; }
        .modal-actions { display: flex; gap: 12px; margin-top: 22px; }
        .modal-actions .btn-secondary, .modal-actions .btn-primary { width: auto; flex: 1; text-align: center; text-decoration: none; }

        .toast {
            position: fixed; bottom: 28px; left: 50%; transform: translateX(-50%) translateY(16px);
            display: flex; align-items: center; gap: 10px;
            background: var(--panel-solid); border: 1px solid var(--border); border-left: 3px solid var(--success);
            border-radius: var(--r-md); padding: 14px 20px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.35); font-size: 13.5px; font-weight: 500; color: var(--text-main);
            z-index: 80; opacity: 0; pointer-events: none;
            transition: opacity 0.35s var(--ease), transform 0.35s var(--ease);
        }
        .toast.is-visible { opacity: 1; transform: translateX(-50%) translateY(0); }
        .toast.is-error { border-left-color: var(--danger); }

        @media (max-width: 900px) {
            .inbox-layout { flex-direction: column; }
            .inbox-sidebar { width: 100%; position: static; }
        }
        @media (max-width: 640px) {
            .inbox-toolbar { justify-content: center; text-align: center; }
            .ntf-icon { width: 32px; height: 32px; }
            .ntf-titulo { white-space: normal; }
        }
    </style>
</head>
<body class="<?= $origen['tema'] ?>">
    <canvas id="bg-canvas"></canvas>
    <div class="bg-veil"></div>

    <a href="<?= $origen['url'] ?>" class="menu-exit menu-exit--back">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19 8 12l7-7" />
        </svg>
        <span class="exit-label">Volver al menú</span>
    </a>

    <div class="inbox-page">
        <div class="inbox-head">
            <div class="inbox-eyebrow"><span class="dot"></span><?= htmlspecialchars(mb_strtoupper($area ?: 'Usuario', 'UTF-8')) ?> · BANDEJA</div>
            <h1 class="inbox-title">Bandeja de Entrada</h1>
            <p class="inbox-sub" id="inboxSub"><?= $primerNombre !== '' ? 'Hola ' . htmlspecialchars($primerNombre) . ', ' : '' ?>aquí están tus notificaciones de la plataforma</p>
        </div>

        <div class="inbox-layout">
            <div class="inbox-sidebar">
                <div class="sidebar-title">Estado</div>
                <div class="filtro-lista" id="filtrosEstado">
                    <button type="button" class="filtro-item is-active" data-estado="todas"><span class="filtro-nombre">Todas</span><span class="filtro-count" data-count="todas">0</span></button>
                    <button type="button" class="filtro-item" data-estado="no_leidas"><span class="filtro-nombre">No leídas</span><span class="filtro-count" data-count="no_leidas">0</span></button>
                    <button type="button" class="filtro-item" data-estado="leidas"><span class="filtro-nombre">Leídas</span><span class="filtro-count" data-count="leidas">0</span></button>
                </div>

                <div class="sidebar-title">Tipo</div>
                <div class="filtro-lista" id="filtrosTipo">
                    <button type="button" class="filtro-item is-active" data-tipo=""><span class="dot"></span><span class="filtro-nombre">Todos</span></button>
                    <button type="button" class="filtro-item tipo-info" data-tipo="info"><span class="dot"></span><span class="filtro-nombre">Información</span><span class="filtro-count" data-count="info">0</span></button>
                    <button type="button" class="filtro-item tipo-aviso" data-tipo="aviso"><span class="dot"></span><span class="filtro-nombre">Avisos</span><span class="filtro-count" data-count="aviso">0</span></button>
                    <button type="button" class="filtro-item tipo-alerta" data-tipo="alerta"><span class="dot"></span><span class="filtro-nombre">Alertas</span><span class="filtro-count" data-count="alerta">0</span></button>
                    <button type="button" class="filtro-item tipo-exito" data-tipo="exito"><span class="dot"></span><span class="filtro-nombre">Completadas</span><span class="filtro-count" data-count="exito">0</span></button>
                </div>
            </div>

            <div class="inbox-main">
                <div class="inbox-card">
                    <div class="inbox-toolbar">
                        <div class="inbox-toolbar-left">
                            <div class="view-toggle">
                                <button type="button" class="view-btn is-active" id="btnVistaRecientes">Recientes</button>
                                <button type="button" class="view-btn" id="btnVistaMensual">Por mes</button>
                            </div>
                            <div class="inbox-nav" id="navMensual" style="display:none;">
                                <button type="button" class="nav-btn" id="btnMesAnterior" aria-label="Mes anterior">‹</button>
                                <div class="month-label" id="labelMes">—</div>
                                <button type="button" class="nav-btn" id="btnMesSiguiente" aria-label="Mes siguiente">›</button>
                            </div>
                        </div>
                        <button type="button" class="btn-marcar-todas" id="btnMarcarTodas">✓ Marcar todas como leídas</button>
                    </div>

                    <div class="inbox-list" id="inboxList">
                        <div class="inbox-loading">Cargando notificaciones…</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="inbox-footer">
            <img src="../../img/logo_omas_azul.png" alt="Organización MAS">
        </div>
    </div>

    <div class="modal-overlay" id="modalOverlay">
        <div class="modal-panel">
            <div class="modal-head">
                <h2 class="modal-title" id="modalTitle">Notificación</h2>
                <button type="button" class="modal-close" id="modalClose" aria-label="Cerrar">✕</button>
            </div>
            <div id="modalBody"></div>
        </div>
    </div>

    <div class="toast" id="toast"></div>

    <script src="../../dist/app.js"></script>
    <script>
        const API = 'bandeja_api.php';
        const MESES = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        const TIPO_LABEL = { info: 'Información', aviso: 'Aviso', alerta: 'Alerta', exito: 'Completada' };
        const TIPO_ICONO = {
            info: 'M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z',
            aviso: 'M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0',
            alerta: 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z',
            exito: 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
        };

        const hoy = new Date();
        let anioActual = hoy.getFullYear();
        let mesActual = hoy.getMonth() + 1;
        let vistaActual = 'recientes';
        let filtroEstado = 'todas';
        let filtroTipo = '';
        let notificaciones = [];

        const pad2 = n => String(n).padStart(2, '0');
        const fechaISO = d => `${d.getFullYear()}-${pad2(d.getMonth() + 1)}-${pad2(d.getDate())}`;

        function escapeHTML(str) {
            const div = document.createElement('div');
            div.textContent = str ?? '';
            return div.innerHTML;
        }
        const icono = tipo => `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="${TIPO_ICONO[tipo] || TIPO_ICONO.info}" /></svg>`;

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

        // ---------- API ----------
        async function api(accion, datos) {
            const opciones = { cache: 'no-store' };
            let url = API + '?accion=' + encodeURIComponent(accion);
            if (datos) {
                const body = new FormData();
                body.append('accion', accion);
                Object.entries(datos).forEach(([k, v]) => body.append(k, v));
                opciones.method = 'POST';
                opciones.body = body;
                url = API;
            }
            const res = await fetch(url, opciones);
            if (res.status === 401) {
                window.location.href = '/index.php?motivo=sesion';
                throw new Error('Sesión expirada');
            }
            const data = await res.json();
            if (data.status !== 'success') throw new Error(data.message || 'Error en la bandeja');
            return data;
        }

        async function cargar() {
            try {
                const data = await api('listar');
                notificaciones = data.notificaciones;
                render();
            } catch (err) {
                console.error(err);
                document.getElementById('inboxList').innerHTML = '<div class="inbox-empty">No se pudo cargar la bandeja. Intenta de nuevo.</div>';
            }
        }

        // ---------- Render ----------
        function etiquetaDia(iso) {
            const ayer = new Date(hoy); ayer.setDate(hoy.getDate() - 1);
            if (iso === fechaISO(hoy)) return 'Hoy';
            if (iso === fechaISO(ayer)) return 'Ayer';
            const [a, m, d] = iso.split('-').map(Number);
            const dia = new Date(a, m - 1, d).toLocaleDateString('es-CO', { weekday: 'long' });
            return `${dia} ${d} de ${MESES[m - 1]}${a !== hoy.getFullYear() ? ' ' + a : ''}`;
        }

        function actualizarContadores() {
            const set = (k, v) => document.querySelectorAll(`[data-count="${k}"]`).forEach(el => el.textContent = v);
            const noLeidas = notificaciones.filter(n => !n.leida).length;
            set('todas', notificaciones.length);
            set('no_leidas', noLeidas);
            set('leidas', notificaciones.length - noLeidas);
            ['info', 'aviso', 'alerta', 'exito'].forEach(t => set(t, notificaciones.filter(n => n.tipo === t).length));
            document.getElementById('btnMarcarTodas').disabled = noLeidas === 0;
        }

        function filtradas() {
            const prefijoMes = `${anioActual}-${pad2(mesActual)}`;
            return notificaciones.filter(n =>
                (filtroEstado === 'todas' || (filtroEstado === 'no_leidas' ? !n.leida : n.leida)) &&
                (!filtroTipo || n.tipo === filtroTipo) &&
                (vistaActual === 'recientes' || (n.fecha || '').startsWith(prefijoMes))
            );
        }

        function render() {
            actualizarContadores();
            document.getElementById('labelMes').textContent = `${MESES[mesActual - 1]} ${anioActual}`;
            document.getElementById('btnMesSiguiente').disabled =
                anioActual > hoy.getFullYear() || (anioActual === hoy.getFullYear() && mesActual >= hoy.getMonth() + 1);

            const lista = filtradas();
            const cont = document.getElementById('inboxList');
            if (!lista.length) {
                cont.innerHTML = `<div class="inbox-empty">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 0 1 2.012 1.244l.256.512a2.25 2.25 0 0 0 2.013 1.244h3.218a2.25 2.25 0 0 0 2.013-1.244l.256-.512a2.25 2.25 0 0 1 2.013-1.244h3.859m-19.5.338V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 0 0-2.15-1.588H6.911a2.25 2.25 0 0 0-2.15 1.588L2.35 13.177a2.25 2.25 0 0 0-.1.661Z" /></svg>
                    <span>${vistaActual === 'mensual' ? 'No hay notificaciones en este mes.' : 'No hay notificaciones con estos filtros.'}</span>
                </div>`;
                return;
            }

            const grupos = {};
            lista.forEach(n => (grupos[(n.fecha || '').slice(0, 10)] ??= []).push(n));
            let i = 0;
            cont.innerHTML = Object.keys(grupos).sort().reverse().map(dia => `
                <div class="day-group">
                    <div class="day-group-head${dia === fechaISO(hoy) ? ' is-today' : ''}">${escapeHTML(etiquetaDia(dia))}</div>
                    ${grupos[dia].map(n => `
                        <div class="ntf-row tipo-${escapeHTML(n.tipo)}${n.leida ? '' : ' is-unread'}" data-id="${escapeHTML(n.id)}" style="animation-delay:${Math.min(i++, 12) * 0.03}s">
                            <div class="ntf-icon">${icono(n.tipo)}</div>
                            <div class="ntf-body">
                                <div class="ntf-top">
                                    <span class="ntf-titulo">${escapeHTML(n.titulo)}</span>
                                    <span class="ntf-hora">${escapeHTML((n.fecha || '').slice(11, 16))}</span>
                                </div>
                                <div class="ntf-mensaje">${escapeHTML(n.mensaje)}</div>
                                ${n.modulo ? `<span class="ntf-modulo">${escapeHTML(n.modulo)}</span>` : ''}
                            </div>
                            ${n.leida ? '' : '<span class="ntf-unread-dot"></span>'}
                        </div>`).join('')}
                </div>`).join('');
        }

        function cambiarVista(fn) {
            const cont = document.getElementById('inboxList');
            cont.classList.add('is-switching');
            setTimeout(() => { fn(); render(); cont.classList.remove('is-switching'); }, 180);
        }

        // ---------- Modal de detalle ----------
        const modalOverlay = document.getElementById('modalOverlay');
        function closeModal() {
            modalOverlay.classList.remove('is-open');
            document.removeEventListener('keydown', onEsc);
        }
        const onEsc = e => { if (e.key === 'Escape') closeModal(); };
        document.getElementById('modalClose').addEventListener('click', closeModal);
        modalOverlay.addEventListener('click', e => { if (e.target === modalOverlay) closeModal(); });

        async function abrirNotificacion(id) {
            const n = notificaciones.find(x => x.id === id);
            if (!n) return;
            const [f, h] = (n.fecha || '').split(' ');
            document.getElementById('modalTitle').textContent = n.titulo;
            document.getElementById('modalBody').innerHTML = `
                <p class="modal-mensaje">${escapeHTML(n.mensaje)}</p>
                <div class="detail-row"><div class="detail-label">Tipo</div><div class="detail-value"><span class="tipo-pill tipo-${escapeHTML(n.tipo)}"><span class="dot"></span>${escapeHTML(TIPO_LABEL[n.tipo] || n.tipo)}</span></div></div>
                ${n.modulo ? `<div class="detail-row"><div class="detail-label">Módulo</div><div class="detail-value">${escapeHTML(n.modulo)}</div></div>` : ''}
                <div class="detail-row"><div class="detail-label">Recibida</div><div class="detail-value">${escapeHTML(etiquetaDia(f))} · ${escapeHTML((h || '').slice(0, 5))}</div></div>
                <div class="modal-actions">
                    <button type="button" class="btn-secondary" id="btnToggleLeida">Marcar como no leída</button>
                    <button type="button" class="btn-secondary" id="btnEliminar">Eliminar</button>
                    ${n.enlace ? `<a class="btn-primary" href="${escapeHTML(n.enlace)}">Ir al módulo</a>` : ''}
                </div>`;
            modalOverlay.classList.add('is-open');
            document.addEventListener('keydown', onEsc);

            document.getElementById('btnToggleLeida').onclick = async () => {
                try {
                    await api('marcar_no_leida', { id });
                    n.leida = false;
                    closeModal(); render();
                    showToast('Notificación marcada como no leída');
                } catch (err) { showToast(err.message, true); }
            };
            document.getElementById('btnEliminar').onclick = async () => {
                try {
                    await api('eliminar', { id });
                    notificaciones = notificaciones.filter(x => x.id !== id);
                    closeModal(); render();
                    showToast('Notificación eliminada');
                } catch (err) { showToast(err.message, true); }
            };

            // Abrirla la marca como leída.
            if (!n.leida) {
                try {
                    await api('marcar_leida', { id });
                    n.leida = true;
                    render();
                } catch (err) { console.error(err); }
            }
        }

        // ---------- Eventos ----------
        document.getElementById('inboxList').addEventListener('click', e => {
            const row = e.target.closest('.ntf-row');
            if (row) abrirNotificacion(row.dataset.id);
        });

        document.querySelectorAll('#filtrosEstado .filtro-item').forEach(btn => btn.addEventListener('click', () => {
            document.querySelectorAll('#filtrosEstado .filtro-item').forEach(b => b.classList.toggle('is-active', b === btn));
            cambiarVista(() => { filtroEstado = btn.dataset.estado; });
        }));
        document.querySelectorAll('#filtrosTipo .filtro-item').forEach(btn => btn.addEventListener('click', () => {
            document.querySelectorAll('#filtrosTipo .filtro-item').forEach(b => b.classList.toggle('is-active', b === btn));
            cambiarVista(() => { filtroTipo = btn.dataset.tipo; });
        }));

        document.getElementById('btnVistaRecientes').addEventListener('click', () => {
            document.getElementById('btnVistaRecientes').classList.add('is-active');
            document.getElementById('btnVistaMensual').classList.remove('is-active');
            document.getElementById('navMensual').style.display = 'none';
            cambiarVista(() => { vistaActual = 'recientes'; });
        });
        document.getElementById('btnVistaMensual').addEventListener('click', () => {
            document.getElementById('btnVistaMensual').classList.add('is-active');
            document.getElementById('btnVistaRecientes').classList.remove('is-active');
            document.getElementById('navMensual').style.display = '';
            cambiarVista(() => { vistaActual = 'mensual'; });
        });
        document.getElementById('btnMesAnterior').addEventListener('click', () => cambiarVista(() => {
            if (--mesActual < 1) { mesActual = 12; anioActual--; }
        }));
        document.getElementById('btnMesSiguiente').addEventListener('click', () => cambiarVista(() => {
            if (++mesActual > 12) { mesActual = 1; anioActual++; }
        }));

        document.getElementById('btnMarcarTodas').addEventListener('click', async () => {
            try {
                await api('marcar_todas', {});
                notificaciones.forEach(n => n.leida = true);
                render();
                showToast('Todas las notificaciones quedaron como leídas');
            } catch (err) { showToast(err.message, true); }
        });

        cargar();
    </script>
</body>
</html>
