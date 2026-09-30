<?php
// Cronograma de Producción — calendario mensual de qué se produce y qué días.
// Estructura visual tomada de cronograma_maquinas/calendario_general.php, con
// el acento morado de Producción (theme-production).
// Permisos: regla fija de area_operativa_lib.php — ven 'produccion' y
// 'almacen' (y admins); editan admins y rol 1 de 'produccion'.
require '../sesion.php';
verificarAutenticacion();
require_once '../area_operativa_lib.php';
require_once 'produccion_lib.php';

if (!veCronogramaProduccion()) {
    header('Location: ../cronograma_maquinas/calendario_general.php');
    exit();
}

$esAdmin = ($_SESSION['rol'] ?? '') === 'adm';
$puedeEditar = puedeEditarCronogramaProduccion();
$sede = in_array($_SESSION['sede'] ?? '', PRODUCCION_SEDES, true) ? $_SESSION['sede'] : 'ZC';
$SEDES = ['ZC' => 'Zona Centro', 'ZS' => 'Zona Sur', 'ZB' => 'Buga'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cronograma de Producción · Organización MAS</title>
    <link rel="stylesheet" href="../../css/index.css">
    <link rel="stylesheet" href="../../css/menu_principal.css">
    <style>
        :root {
            --cat-harinas: var(--accent);
            --cat-subproductos: #f2b134;
            --cat-otro: var(--success);
        }

        .cal-page { position: relative; z-index: 2; max-width: 1320px; margin: 0 auto; padding: 108px 20px 70px; }

        .cal-head { text-align: center; margin-bottom: clamp(24px, 4vw, 36px); opacity: 0; animation: menuFadeUp 0.6s var(--ease) forwards; }
        .cal-eyebrow {
            display: inline-flex; align-items: center; gap: 8px;
            font-size: 12px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase;
            color: var(--accent); margin-bottom: 10px;
        }
        .cal-eyebrow .dot {
            width: 6px; height: 6px; border-radius: 50%; background: var(--accent);
            box-shadow: 0 0 8px var(--accent-glow); animation: pulse 2.2s ease-in-out infinite;
        }
        .cal-title { font-size: clamp(22px, 3.2vw, 30px); font-weight: 700; letter-spacing: -0.01em; color: var(--text-main); margin: 0 0 8px; }
        .cal-sub { font-size: 14px; color: var(--text-muted); margin: 0; }
        .cal-head-links { display: flex; justify-content: center; flex-wrap: wrap; gap: 10px; margin-top: 14px; }
        .head-link {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 7px 14px; border-radius: 999px; border: 1px solid var(--border);
            background: var(--panel); color: var(--text-muted);
            font-size: 12px; font-weight: 600; text-decoration: none; font-family: inherit; cursor: pointer;
            transition: color 0.2s var(--ease), border-color 0.2s var(--ease);
        }
        .head-link:hover { color: var(--accent); border-color: var(--border-hover); }
        select.head-link { appearance: auto; }
        .badge-lectura {
            display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; border-radius: 999px;
            border: 1px dashed var(--border); color: var(--text-faint); font-size: 12px; font-weight: 600;
        }

        .cal-layout { display: flex; gap: 22px; align-items: flex-start; }

        /* ---------- Barra lateral: resumen del mes ---------- */
        .cal-sidebar {
            width: 290px; flex-shrink: 0;
            background: var(--panel); backdrop-filter: blur(18px); -webkit-backdrop-filter: blur(18px);
            border: 1px solid var(--border); border-radius: var(--r-lg); padding: 18px;
            position: sticky; top: 100px; max-height: calc(100vh - 130px); display: flex; flex-direction: column;
            opacity: 0; animation: menuFadeUp 0.6s var(--ease) forwards; animation-delay: 0.04s;
        }
        .cal-sidebar-title { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); margin-bottom: 10px; }
        .cal-sidebar-hint { font-size: 11.5px; color: var(--text-faint); margin-bottom: 12px; line-height: 1.4; }
        .btn-programar {
            width: 100%; margin-bottom: 16px; padding: 11px 14px; border: none; border-radius: var(--r-sm);
            background: linear-gradient(135deg, var(--accent), var(--accent-2)); color: #fff;
            font-family: inherit; font-size: 13px; font-weight: 600; cursor: pointer;
            box-shadow: 0 6px 18px var(--accent-glow); transition: transform 0.2s var(--ease), box-shadow 0.2s var(--ease);
        }
        .btn-programar:hover { transform: translateY(-1px); box-shadow: 0 10px 24px var(--accent-glow); }
        .resumen-lista { overflow-y: auto; flex: 1; min-height: 0; }
        .resumen-lista::-webkit-scrollbar { width: 5px; }
        .resumen-lista::-webkit-scrollbar-thumb { background: var(--border-hover); border-radius: 4px; }
        .resumen-item {
            display: flex; align-items: center; gap: 9px; padding: 8px 9px; border-radius: var(--r-sm);
            border-bottom: 1px dashed var(--border); font-size: 12.5px;
        }
        .resumen-item:last-child { border-bottom: none; }
        .resumen-item .dot { flex-shrink: 0; width: 7px; height: 7px; border-radius: 50%; background: var(--cat-otro); }
        .resumen-item .nombre { flex: 1; min-width: 0; color: var(--text-main); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .resumen-item .total { flex-shrink: 0; text-align: right; font-weight: 700; color: var(--text-main); font-size: 12px; }
        .resumen-item .total small { display: block; font-weight: 500; color: var(--text-faint); font-size: 10.5px; }
        .resumen-vacio { text-align: center; color: var(--text-faint); font-size: 12px; padding: 20px 0; }

        .cat-harinas .dot, .cat-harinas.dot { background: var(--cat-harinas); }
        .cat-subproductos .dot, .cat-subproductos.dot { background: var(--cat-subproductos); }
        .cat-otro .dot, .cat-otro.dot { background: var(--cat-otro); }

        /* ---------- Calendario ---------- */
        .cal-main { flex: 1; min-width: 0; }
        .cal-card {
            background: var(--panel); backdrop-filter: blur(18px); -webkit-backdrop-filter: blur(18px);
            border: 1px solid var(--border); border-radius: var(--r-lg); padding: clamp(18px, 3vw, 30px);
            box-shadow: 0 20px 60px var(--accent-glow), 0 0 0 1px rgba(139, 92, 246, 0.05) inset;
            opacity: 0; animation: menuFadeUp 0.6s var(--ease) forwards; animation-delay: 0.08s;
            transition: border-color 0.4s var(--ease);
        }
        .cal-card:hover { border-color: var(--border-hover); }
        .cal-toolbar { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; margin-bottom: 22px; }
        .cal-nav { display: flex; align-items: center; gap: 16px; }
        .cal-nav-btn {
            width: 36px; height: 36px; border-radius: 50%; border: 1px solid var(--border);
            background: var(--panel-solid); color: var(--accent); font-size: 17px; line-height: 1; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: border-color 0.25s var(--ease), box-shadow 0.25s var(--ease), transform 0.2s var(--ease);
        }
        .cal-nav-btn:hover { border-color: var(--border-hover); box-shadow: 0 0 0 4px var(--accent-glow); transform: translateY(-1px); }
        .cal-nav-btn:active { transform: translateY(0) scale(0.94); }
        .cal-month-label { font-weight: 700; font-size: 16px; color: var(--text-main); min-width: 168px; text-align: center; text-transform: capitalize; }
        .btn-hoy {
            padding: 8px 14px; border: 1px solid var(--border); border-radius: var(--r-sm); background: var(--panel-solid);
            color: var(--text-muted); font-family: inherit; font-size: 12px; font-weight: 600; cursor: pointer;
            transition: color 0.2s var(--ease), border-color 0.2s var(--ease);
        }
        .btn-hoy:hover { color: var(--accent); border-color: var(--border-hover); }
        .leyenda { display: flex; gap: 14px; flex-wrap: wrap; font-size: 11.5px; color: var(--text-muted); }
        .leyenda span { display: inline-flex; align-items: center; gap: 6px; }
        .leyenda .dot { width: 7px; height: 7px; border-radius: 50%; }

        .cal-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 8px; }
        .cal-weekday { text-align: center; font-size: 11px; font-weight: 600; letter-spacing: 0.06em; text-transform: uppercase; color: var(--text-faint); padding-bottom: 10px; }
        .cal-days { margin-top: 2px; transition: opacity 0.22s var(--ease); }
        .cal-days.is-switching { opacity: 0; }
        @keyframes calDayIn { from { opacity: 0; transform: translateY(8px) scale(0.98); } to { opacity: 1; transform: translateY(0) scale(1); } }

        .day-cell {
            position: relative; background: var(--panel-solid); border: 1px solid var(--border); border-radius: var(--r-md);
            min-height: 108px; padding: 8px; display: flex; flex-direction: column; gap: 4px; overflow: hidden;
            animation: calDayIn 0.4s var(--ease) backwards;
            transition: border-color 0.2s var(--ease);
        }
        .day-cell.is-empty { background: transparent; border-color: transparent; }
        .day-cell.is-today { border-color: var(--accent); box-shadow: 0 0 0 1px var(--accent) inset, 0 10px 26px var(--accent-glow); }
        .day-cell.is-editable { cursor: pointer; }
        .day-cell.is-editable:hover { border-color: var(--border-hover); }
        .day-cell.is-editable:hover .day-add { opacity: 1; }
        .day-head { display: flex; align-items: center; justify-content: space-between; }
        .day-number { font-size: 12px; font-weight: 600; color: var(--text-faint); }
        .day-cell.is-today .day-number { color: var(--accent); }
        .day-add { font-size: 14px; line-height: 1; color: var(--accent); opacity: 0; transition: opacity 0.2s var(--ease); }

        .prod-chip {
            display: flex; align-items: center; gap: 5px; background: rgba(139, 92, 246, 0.08);
            border-radius: 6px; padding: 3px 6px; font-size: 10px; font-weight: 500; color: var(--text-main);
            overflow: hidden; cursor: pointer; transition: background 0.2s var(--ease), transform 0.2s var(--ease);
        }
        .prod-chip:hover { background: rgba(139, 92, 246, 0.16); transform: translateX(1px); }
        .prod-chip .dot { flex-shrink: 0; width: 6px; height: 6px; border-radius: 50%; }
        .prod-chip .label { flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .prod-chip .turno { flex-shrink: 0; font-size: 9px; font-weight: 700; color: var(--text-faint); }

        /* ---------- Preconteo de formatos (envasado + control de empaque) ---------- */
        :root {
            --pc-completo: var(--success);
            --pc-faltante: var(--danger);
            --pc-en-curso: #f2b134;
        }
        .pc { flex-shrink: 0; font-size: 10px; font-weight: 800; line-height: 1; }
        .pc--completo { color: var(--pc-completo); }
        .pc--faltante { color: var(--pc-faltante); }
        .pc--en_curso { color: var(--pc-en-curso); }
        .pc--sin_enlace { color: var(--text-faint); }
        .prod-chip.pc-faltante { background: rgba(255, 84, 112, 0.10); box-shadow: inset 2px 0 0 var(--pc-faltante); }
        .prod-chip.pc-en_curso { box-shadow: inset 2px 0 0 var(--pc-en-curso); }
        .prod-chip.pc-completo { box-shadow: inset 2px 0 0 var(--pc-completo); }

        .pc-resumen { display: grid; grid-template-columns: repeat(3, 1fr); gap: 6px; margin-bottom: 18px; }
        .pc-caja {
            border: 1px solid var(--border); border-radius: var(--r-sm); background: var(--panel-solid);
            padding: 8px 6px; text-align: center;
        }
        .pc-caja .n { display: block; font-size: 18px; font-weight: 800; line-height: 1.1; }
        .pc-caja .t { display: block; font-size: 9.5px; font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase; color: var(--text-faint); margin-top: 2px; }
        .pc-caja--completo .n { color: var(--pc-completo); }
        .pc-caja--faltante .n { color: var(--pc-faltante); }
        .pc-caja--en_curso .n { color: var(--pc-en-curso); }

        .pc-formatos { display: flex; flex-direction: column; gap: 6px; margin-top: 4px; }
        .pc-formato {
            display: flex; align-items: center; gap: 10px; padding: 9px 12px;
            border: 1px solid var(--border); border-radius: var(--r-sm); font-size: 13px;
        }
        .pc-formato .nombre { flex: 1; color: var(--text-main); font-weight: 600; }
        .pc-formato .cuenta { font-size: 12px; font-weight: 700; }
        .pc-formato.ok .cuenta { color: var(--pc-completo); }
        .pc-formato.falta .cuenta { color: var(--pc-faltante); }
        .pc-formato.pendiente .cuenta { color: var(--text-muted); }
        .pc-formato a { font-size: 11.5px; color: var(--accent); text-decoration: none; font-weight: 600; }
        .pc-nota { font-size: 11.5px; color: var(--text-faint); margin-top: 8px; line-height: 1.45; }
        .cal-loading { text-align: center; color: var(--text-faint); font-size: 12.5px; padding: 18px 0 4px; display: none; }

        .cal-footer { display: flex; flex-direction: column; align-items: center; gap: 10px; margin-top: clamp(30px, 5vw, 48px); opacity: 0; animation: menuFadeUp 0.6s var(--ease) forwards; animation-delay: 0.2s; }
        .cal-footer img { width: min(180px, 46vw); height: auto; }

        /* ---------- Modal ---------- */
        .modal-overlay {
            position: fixed; inset: 0; z-index: 60; display: flex; align-items: center; justify-content: center;
            padding: 20px; background: rgba(6, 14, 28, 0.38); backdrop-filter: blur(7px); -webkit-backdrop-filter: blur(7px);
            opacity: 0; pointer-events: none; transition: opacity 0.35s var(--ease);
        }
        .modal-overlay.is-open { opacity: 1; pointer-events: auto; }
        .modal-panel {
            width: 100%; max-width: 540px; max-height: 88vh; overflow-y: auto;
            background: var(--panel-solid); border: 1px solid var(--border); border-radius: var(--r-lg); padding: 30px;
            box-shadow: 0 30px 80px rgba(76, 29, 149, 0.25), 0 0 0 1px rgba(139, 92, 246, 0.06) inset;
            transform: translateY(22px) scale(0.96); opacity: 0; transition: transform 0.4s var(--ease), opacity 0.4s var(--ease);
        }
        .modal-overlay.is-open .modal-panel { transform: translateY(0) scale(1); opacity: 1; }
        .modal-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 14px; margin-bottom: 22px; }
        .modal-title { font-size: 18px; font-weight: 700; color: var(--text-main); margin: 0; }
        .modal-close {
            flex-shrink: 0; width: 30px; height: 30px; border-radius: 50%; border: 1px solid var(--border);
            background: transparent; color: var(--text-muted); font-size: 15px; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: border-color 0.2s var(--ease), color 0.2s var(--ease), background 0.2s var(--ease);
        }
        .modal-close:hover { border-color: var(--danger); color: var(--danger); background: rgba(255, 84, 112, 0.06); }
        .modal-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 0 16px; }
        #modalBody .field { opacity: 1; margin-bottom: 16px; }
        .field-hint { margin-top: 6px; font-size: 11.5px; color: var(--text-faint); }
        .modal-actions { display: flex; gap: 12px; margin-top: 22px; }
        .modal-actions .btn-secondary, .modal-actions .btn-primary { width: auto; flex: 1; }
        .detail-row { display: flex; gap: 10px; padding: 10px 0; border-bottom: 1px dashed var(--border); font-size: 13.5px; }
        .detail-row:last-child { border-bottom: none; }
        .detail-label { flex-shrink: 0; width: 108px; color: var(--text-muted); font-weight: 600; font-size: 11.5px; text-transform: uppercase; letter-spacing: 0.04em; padding-top: 2px; }
        .detail-value { color: var(--text-main); }

        .toast {
            position: fixed; bottom: 28px; left: 50%; transform: translateX(-50%) translateY(16px);
            display: flex; align-items: center; gap: 10px; background: var(--panel-solid); border: 1px solid var(--border);
            border-left: 3px solid var(--success); border-radius: var(--r-md); padding: 14px 20px;
            box-shadow: 0 20px 50px rgba(76, 29, 149, 0.2); font-size: 13.5px; font-weight: 500; color: var(--text-main);
            z-index: 80; opacity: 0; pointer-events: none; transition: opacity 0.35s var(--ease), transform 0.35s var(--ease);
        }
        .toast.is-visible { opacity: 1; transform: translateX(-50%) translateY(0); }
        .toast.is-error { border-left-color: var(--danger); }

        @media (max-width: 900px) {
            .cal-layout { flex-direction: column; }
            .cal-sidebar { width: 100%; position: static; max-height: 320px; }
        }
        @media (max-width: 640px) {
            .cal-weekday span { display: none; }
            .cal-weekday::after { content: attr(data-short); }
            .day-cell { min-height: 62px; padding: 6px; }
            .prod-chip .label, .prod-chip .turno { display: none; }
            .prod-chip { justify-content: center; padding: 3px; }
            .cal-toolbar { justify-content: center; text-align: center; }
        }
    </style>
</head>
<body class="theme-production">
    <canvas id="bg-canvas"></canvas>
    <div class="bg-veil"></div>

    <a href="../menu_adm.html" class="menu-exit menu-exit--back">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19 8 12l7-7" />
        </svg>
        <span class="exit-label">Volver al menú</span>
    </a>

    <div class="cal-page">
        <div class="cal-head">
            <div class="cal-eyebrow"><span class="dot"></span>PRODUCCIÓN · CRONOGRAMA · <span id="eyebrowSede"><?= htmlspecialchars(mb_strtoupper($SEDES[$sede], 'UTF-8')) ?></span></div>
            <h1 class="cal-title">Cronograma de Producción</h1>
            <p class="cal-sub"><?= $puedeEditar
                ? 'Toca un día para programar qué se va a producir, o programa un rango de días desde la izquierda'
                : 'Producción programada para los próximos días' ?></p>
            <div class="cal-head-links">
                <?php if (!$puedeEditar): ?><span class="badge-lectura">👁 Solo consulta</span><?php endif; ?>
                <?php if ($esAdmin): ?>
                <select class="head-link" id="selectorSede" aria-label="Sede">
                    <?php foreach ($SEDES as $codigo => $nombre): ?>
                    <option value="<?= $codigo ?>" <?= $codigo === $sede ? 'selected' : '' ?>>Sede: <?= htmlspecialchars($nombre) ?></option>
                    <?php endforeach; ?>
                </select>
                <a class="head-link" href="../cronograma_maquinas/calendario_general.php">Ver cronograma de mantenimiento →</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="cal-layout">
            <div class="cal-sidebar">
                <?php if ($puedeEditar): ?>
                <button type="button" class="btn-programar" id="btnProgramar">+ Programar producción</button>
                <?php endif; ?>
                <div class="cal-sidebar-title">Preconteo de formatos</div>
                <div class="cal-sidebar-hint">Envasado + control de empaque registrados para cada producto programado (desde el <span id="pcDesde">—</span>).</div>
                <div class="pc-resumen" id="pcResumen"></div>
                <div class="cal-sidebar-title">Resumen del mes</div>
                <div class="cal-sidebar-hint">Días programados por producto en el mes visible.</div>
                <div class="resumen-lista" id="resumenLista"><div class="resumen-vacio">Cargando…</div></div>
            </div>

            <div class="cal-main">
                <div class="cal-card">
                    <div class="cal-toolbar">
                        <div class="cal-nav">
                            <button type="button" class="cal-nav-btn" id="btnMesAnterior" aria-label="Mes anterior">‹</button>
                            <div class="cal-month-label" id="labelMes">—</div>
                            <button type="button" class="cal-nav-btn" id="btnMesSiguiente" aria-label="Mes siguiente">›</button>
                            <button type="button" class="btn-hoy" id="btnHoy">Hoy</button>
                        </div>
                        <div class="leyenda">
                            <span class="cat-harinas"><span class="dot"></span>Harinas</span>
                            <span class="cat-subproductos"><span class="dot"></span>Subproductos</span>
                            <span class="cat-otro"><span class="dot"></span>Otros</span>
                            <span><span class="pc pc--completo">✓</span> Formatos completos</span>
                            <span><span class="pc pc--en_curso">◐</span> Hoy, pendientes</span>
                            <span><span class="pc pc--faltante">!</span> Faltan formatos</span>
                        </div>
                    </div>

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
                    <div class="cal-loading" id="calendarLoading">Cargando programación…</div>
                </div>
            </div>
        </div>

        <div class="cal-footer">
            <img src="../../img/logo_omas_azul.png" alt="Organización MAS">
        </div>
    </div>

    <div class="modal-overlay" id="modalOverlay">
        <div class="modal-panel">
            <div class="modal-head">
                <h2 class="modal-title" id="modalTitle">Programar producción</h2>
                <button type="button" class="modal-close" id="modalClose" aria-label="Cerrar">✕</button>
            </div>
            <div id="modalBody"></div>
        </div>
    </div>

    <div class="toast" id="toast"></div>

    <script src="../../dist/app.js"></script>
    <script>
        const PUEDE_EDITAR = <?= json_encode($puedeEditar) ?>;
        const SEDES = <?= json_encode($SEDES, JSON_UNESCAPED_UNICODE) ?>;
        const MESES = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        const CATEGORIA_LABEL = { harinas: 'Harina', subproductos: 'Subproducto', otro: 'Otro' };

        const hoy = new Date();
        let anioActual = hoy.getFullYear();
        let mesActual = hoy.getMonth() + 1;
        let sedeActual = <?= json_encode($sede) ?>;
        let turnosSede = 3;
        let programaciones = [];
        let catalogo = { harinas: [], subproductos: [] };
        let preconteoDesde = '';

        // Preconteo: icono y texto por estado (ver preconteo_lib.php).
        const PC = {
            completo:   { icono: '✓', texto: 'Formatos completos' },
            faltante:   { icono: '!', texto: 'Faltan formatos' },
            en_curso:   { icono: '◐', texto: 'Hoy — faltan formatos por registrar' },
            futuro:     { icono: '',  texto: 'Aún no llega el día' },
            no_aplica:  { icono: '',  texto: 'Anterior al inicio del preconteo' },
            sin_enlace: { icono: '?', texto: 'Producto sin código ("Otro"): no se puede contar' },
        };
        const pcEstado = p => (p.preconteo && p.preconteo.estado) || 'no_aplica';

        const pad2 = n => String(n).padStart(2, '0');
        const mesISO = () => `${anioActual}-${pad2(mesActual)}`;
        const hoyISO = `${hoy.getFullYear()}-${pad2(hoy.getMonth() + 1)}-${pad2(hoy.getDate())}`;
        const turnoLabel = t => t === 'todos' ? 'Todo el día' : `Turno ${t}`;
        function escapeHTML(str) { const d = document.createElement('div'); d.textContent = str ?? ''; return d.innerHTML; }

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
        function openModal(title, html) {
            document.getElementById('modalTitle').textContent = title;
            document.getElementById('modalBody').innerHTML = html;
            modalOverlay.classList.add('is-open');
            document.addEventListener('keydown', onEsc);
        }
        function closeModal() { modalOverlay.classList.remove('is-open'); document.removeEventListener('keydown', onEsc); }
        const onEsc = e => { if (e.key === 'Escape') closeModal(); };
        document.getElementById('modalClose').addEventListener('click', closeModal);
        modalOverlay.addEventListener('click', e => { if (e.target === modalOverlay) closeModal(); });

        // ---------- API ----------
        async function api(accion, datos) {
            const opciones = { cache: 'no-store' };
            let url = `api.php?accion=listar&mes=${mesISO()}&sede=${sedeActual}`;
            if (accion !== 'listar') {
                url = 'api.php';
                opciones.method = 'POST';
                opciones.headers = { 'Content-Type': 'application/json' };
                opciones.body = JSON.stringify({ accion, sede: sedeActual, ...datos });
            }
            const res = await fetch(url, opciones);
            if (res.status === 401) { window.location.href = '/index.php?motivo=sesion'; throw new Error('Sesión expirada'); }
            const data = await res.json();
            if (data.status !== 'success') throw new Error(data.message || 'Error en el cronograma');
            return data;
        }

        // Catálogo de productos de molienda de la sede (harinas + subproductos).
        async function cargarCatalogo() {
            catalogo = { harinas: [], subproductos: [] };
            try {
                const res = await fetch(`../molienda_v2/api_productos.php?sede=${sedeActual}`, { cache: 'no-store' });
                const data = await res.json();
                if (data.status === 'ok' && data.data) {
                    catalogo.harinas = data.data.harinas || [];
                    catalogo.subproductos = data.data.subproductos || [];
                }
            } catch (err) { console.error(err); } // sin catálogo (ej. ZB): queda solo "Otro"
        }

        async function cargarMes() {
            const loading = document.getElementById('calendarLoading');
            loading.style.display = 'block';
            try {
                const data = await api('listar');
                programaciones = data.programaciones;
                turnosSede = data.turnos;
                preconteoDesde = data.preconteo_desde || '';
            } catch (err) {
                programaciones = [];
                showToast(err.message, true);
            } finally {
                loading.style.display = 'none';
            }
            render();
        }

        // ---------- Render ----------
        function render() {
            document.getElementById('labelMes').textContent = `${MESES[mesActual - 1]} ${anioActual}`;
            renderCalendario();
            renderResumen();
            renderPreconteo();
        }

        // Cuenta por DÍA + PRODUCTO (varios turnos del mismo producto el mismo
        // día comparten el mismo estado, porque los formatos no traen turno).
        function renderPreconteo() {
            const [a, m, d] = (preconteoDesde || '').split('-').map(Number);
            document.getElementById('pcDesde').textContent = preconteoDesde
                ? new Date(a, m - 1, d).toLocaleDateString('es-CO', { day: 'numeric', month: 'short', year: 'numeric' }) : '—';
            const vistos = {};
            programaciones.forEach(p => { vistos[p.fecha + '|' + (p.producto_id || p.producto)] = pcEstado(p); });
            const cuenta = { completo: 0, faltante: 0, en_curso: 0 };
            Object.values(vistos).forEach(e => { if (e in cuenta) cuenta[e]++; });
            document.getElementById('pcResumen').innerHTML = [
                ['completo', 'Completos'], ['en_curso', 'Hoy'], ['faltante', 'Faltantes'],
            ].map(([k, t]) => `<div class="pc-caja pc-caja--${k}"><span class="n">${cuenta[k]}</span><span class="t">${t}</span></div>`).join('');
        }

        function renderCalendario() {
            const grid = document.getElementById('calendarGrid');
            const primerDia = new Date(anioActual, mesActual - 1, 1).getDay();
            const diasMes = new Date(anioActual, mesActual, 0).getDate();
            const porDia = {};
            programaciones.forEach(p => (porDia[p.fecha] ??= []).push(p));

            let html = '';
            for (let i = 0; i < primerDia; i++) html += '<div class="day-cell is-empty"></div>';
            for (let d = 1; d <= diasMes; d++) {
                const fecha = `${mesISO()}-${pad2(d)}`;
                const chips = (porDia[fecha] || []).map(p => {
                    const est = pcEstado(p);
                    const pc = p.preconteo || { envasado: 0, empaque: 0 };
                    const detallePc = ['futuro', 'no_aplica', 'sin_enlace'].includes(est) ? PC[est].texto
                        : `${PC[est].texto} (envasado ${pc.envasado}, empaque ${pc.empaque})`;
                    return `
                    <div class="prod-chip cat-${escapeHTML(p.categoria)} pc-${est}" data-id="${escapeHTML(p.id)}" title="${escapeHTML(p.producto)} · ${escapeHTML(turnoLabel(p.turno))} · ${escapeHTML(detallePc)}">
                        <span class="dot"></span>
                        <span class="label">${escapeHTML(p.producto)}</span>
                        ${p.turno !== 'todos' ? `<span class="turno">T${escapeHTML(p.turno)}</span>` : ''}
                        ${PC[est].icono ? `<span class="pc pc--${est}">${PC[est].icono}</span>` : ''}
                    </div>`;
                }).join('');
                html += `<div class="day-cell${fecha === hoyISO ? ' is-today' : ''}${PUEDE_EDITAR ? ' is-editable' : ''}" data-fecha="${fecha}" style="animation-delay:${Math.min(d, 20) * 0.012}s">
                    <div class="day-head"><span class="day-number">${d}</span>${PUEDE_EDITAR ? '<span class="day-add">+</span>' : ''}</div>
                    ${chips}
                </div>`;
            }
            grid.innerHTML = html;
        }

        function renderResumen() {
            const cont = document.getElementById('resumenLista');
            const totales = {};
            programaciones.forEach(p => {
                totales[p.producto] ??= { producto: p.producto, categoria: p.categoria, dias: new Set(), turnos: 0 };
                totales[p.producto].dias.add(p.fecha);
                totales[p.producto].turnos++;
            });
            const lista = Object.values(totales).sort((a, b) => b.dias.size - a.dias.size || a.producto.localeCompare(b.producto));
            if (!lista.length) {
                cont.innerHTML = '<div class="resumen-vacio">No hay producción programada en este mes.</div>';
                return;
            }
            cont.innerHTML = lista.map(t => `
                <div class="resumen-item cat-${escapeHTML(t.categoria)}">
                    <span class="dot"></span>
                    <span class="nombre" title="${escapeHTML(t.producto)}">${escapeHTML(t.producto)}</span>
                    <span class="total">${t.dias.size} ${t.dias.size === 1 ? 'día' : 'días'}${t.turnos > t.dias.size ? `<small>${t.turnos} programaciones</small>` : ''}</span>
                </div>`).join('');
        }

        function cambiarMes(delta) {
            const grid = document.getElementById('calendarGrid');
            grid.classList.add('is-switching');
            setTimeout(async () => {
                mesActual += delta;
                if (mesActual < 1) { mesActual = 12; anioActual--; }
                if (mesActual > 12) { mesActual = 1; anioActual++; }
                await cargarMes();
                grid.classList.remove('is-switching');
            }, 180);
        }

        // ---------- Formulario (crear / editar) ----------
        function opcionesProducto(seleccionId) {
            const grupo = (lista, cat, label) => lista.length ? `<optgroup label="${label}">${lista.map(p =>
                `<option value="${escapeHTML(p.id)}" data-cat="${cat}" data-nombre="${escapeHTML(p.name)}" ${p.id === seleccionId ? 'selected' : ''}>${escapeHTML(p.name)}</option>`
            ).join('')}</optgroup>` : '';
            return `<option value="" disabled ${seleccionId === undefined ? 'selected' : ''}>Selecciona un producto</option>`
                + grupo(catalogo.harinas, 'harinas', 'Harinas')
                + grupo(catalogo.subproductos, 'subproductos', 'Subproductos')
                + `<option value="__otro" ${seleccionId === '__otro' ? 'selected' : ''}>Otro (escribir)</option>`;
        }

        function abrirFormulario(fecha, existente) {
            if (!PUEDE_EDITAR) return;
            const esEdicion = !!existente;
            const enCatalogo = esEdicion && existente.producto_id &&
                [...catalogo.harinas, ...catalogo.subproductos].some(p => p.id === existente.producto_id);
            const seleccion = esEdicion ? (enCatalogo ? existente.producto_id : '__otro') : undefined;
            const turnos = ['todos', ...Array.from({ length: turnosSede }, (_, i) => String(i + 1))];
            const f = fecha || hoyISO;

            openModal(esEdicion ? 'Editar programación' : 'Programar producción', `
                <div class="field">
                    <label for="campoProducto">Producto</label>
                    <select id="campoProducto">${opcionesProducto(seleccion)}</select>
                </div>
                <div class="field" id="bloqueOtro" style="${seleccion === '__otro' ? '' : 'display:none;'}">
                    <label for="campoOtro">Nombre del producto</label>
                    <input type="text" id="campoOtro" maxlength="120" placeholder="Ej: Harina especial pedido cliente" value="${esEdicion && !enCatalogo ? escapeHTML(existente.producto) : ''}">
                </div>
                <div class="field">
                    <label for="campoTurno">Turno</label>
                    <select id="campoTurno">${turnos.map(t => `<option value="${t}" ${esEdicion && existente.turno === t ? 'selected' : ''}>${turnoLabel(t)}</option>`).join('')}</select>
                </div>
                ${esEdicion ? `
                <div class="field">
                    <label>Fecha</label>
                    <input type="date" value="${escapeHTML(existente.fecha)}" disabled>
                    <div class="field-hint">Para moverla de día, elimínala y prográmala de nuevo.</div>
                </div>` : `
                <div class="modal-grid">
                    <div class="field">
                        <label for="campoDesde">Desde</label>
                        <input type="date" id="campoDesde" value="${f}">
                    </div>
                    <div class="field">
                        <label for="campoHasta">Hasta</label>
                        <input type="date" id="campoHasta" value="${f}">
                        <div class="field-hint">Mismo día = solo ese día.</div>
                    </div>
                </div>`}
                <div class="field">
                    <label for="campoObs">Observaciones</label>
                    <input type="text" id="campoObs" maxlength="500" placeholder="Opcional" value="${esEdicion ? escapeHTML(existente.observaciones) : ''}">
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-secondary" id="btnCancelar">Cancelar</button>
                    <button type="button" class="btn-primary" id="btnGuardar">${esEdicion ? 'Guardar cambios' : 'Programar'}</button>
                </div>`);

            const selProducto = document.getElementById('campoProducto');
            selProducto.addEventListener('change', () => {
                document.getElementById('bloqueOtro').style.display = selProducto.value === '__otro' ? '' : 'none';
            });
            const desde = document.getElementById('campoDesde');
            const hasta = document.getElementById('campoHasta');
            if (desde) desde.addEventListener('change', () => { if (hasta.value < desde.value) hasta.value = desde.value; });

            document.getElementById('btnCancelar').addEventListener('click', closeModal);
            document.getElementById('btnGuardar').addEventListener('click', async () => {
                const opt = selProducto.selectedOptions[0];
                if (!selProducto.value) return showToast('Selecciona un producto.', true);
                const esOtro = selProducto.value === '__otro';
                const datos = {
                    producto_id: esOtro ? '' : selProducto.value,
                    producto: esOtro ? document.getElementById('campoOtro').value.trim() : opt.dataset.nombre,
                    categoria: esOtro ? 'otro' : opt.dataset.cat,
                    turno: document.getElementById('campoTurno').value,
                    observaciones: document.getElementById('campoObs').value.trim(),
                };
                if (!datos.producto) return showToast('Escribe el nombre del producto.', true);
                try {
                    const r = esEdicion
                        ? await api('editar', { ...datos, id: existente.id, fecha: existente.fecha })
                        : await api('crear', { ...datos, fecha_inicio: desde.value, fecha_fin: hasta.value });
                    closeModal();
                    showToast(r.message);
                    await cargarMes();
                } catch (err) { showToast(err.message, true); }
            });
        }

        // ---------- Detalle ----------
        // Bloque "Formatos del día": envasado y control de empaque encontrados
        // para este producto y fecha, con acceso a cada formato.
        function bloquePreconteo(p) {
            const est = pcEstado(p);
            if (est === 'no_aplica') {
                return `<div class="pc-nota">El preconteo de formatos empieza el ${escapeHTML(preconteoDesde)}; este día es anterior.</div>`;
            }
            if (est === 'sin_enlace') {
                return `<div class="pc-nota">⚠ Este producto se programó como "Otro" (sin código de la lista de productos), así que sus formatos no se pueden contar. Edítalo y elige el producto de la lista.</div>`;
            }
            const pc = p.preconteo;
            const pendiente = est === 'futuro';
            const formato = (nombre, n, url) => {
                const clase = n > 0 ? 'ok' : (pendiente ? 'pendiente' : 'falta');
                const txt = n > 0 ? `✓ ${n} registro${n === 1 ? '' : 's'}` : (pendiente ? 'Pendiente' : '✕ Sin registro');
                return `<div class="pc-formato ${clase}"><span class="nombre">${nombre}</span><span class="cuenta">${txt}</span><a href="${url}">Abrir →</a></div>`;
            };
            return `
                <div class="detail-row" style="border-bottom:none;padding-bottom:0;"><div class="detail-label">Formatos</div><div class="detail-value">${escapeHTML(PC[est].texto)}</div></div>
                <div class="pc-formatos">
                    ${formato('Línea de envasado', pc.envasado, '../envasado_v2/geleria_productos.php')}
                    ${formato('Control de empaque', pc.empaque, '../empaque_v2/galeria_empaques_v2.php')}
                </div>
                <div class="pc-nota">Se cuentan los registros de ese día con este mismo producto (por código). En envasado, el producto de la galería debe estar enlazado en su catálogo; en empaque, elegido de la lista (no "Otro").</div>`;
        }

        function abrirDetalle(p) {
            const [a, m, d] = p.fecha.split('-').map(Number);
            const fechaTxt = new Date(a, m - 1, d).toLocaleDateString('es-CO', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
            const fila = (l, v) => `<div class="detail-row"><div class="detail-label">${l}</div><div class="detail-value">${v}</div></div>`;
            openModal(p.producto, `
                ${fila('Fecha', escapeHTML(fechaTxt))}
                ${fila('Turno', escapeHTML(turnoLabel(p.turno)))}
                ${fila('Tipo', escapeHTML(CATEGORIA_LABEL[p.categoria] || p.categoria))}
                ${p.observaciones ? fila('Notas', escapeHTML(p.observaciones)) : ''}
                ${fila('Programó', escapeHTML(p.creado_por || '—') + (p.editado_por ? `<br><small style="color:var(--text-faint)">Editado por ${escapeHTML(p.editado_por)}</small>` : ''))}
                ${bloquePreconteo(p)}
                ${PUEDE_EDITAR ? `
                <div class="modal-actions">
                    <button type="button" class="btn-secondary" id="btnEliminar">Eliminar</button>
                    <button type="button" class="btn-primary" id="btnEditar">Editar</button>
                </div>` : ''}`);
            if (!PUEDE_EDITAR) return;
            document.getElementById('btnEditar').addEventListener('click', () => abrirFormulario(null, p));
            document.getElementById('btnEliminar').addEventListener('click', async () => {
                if (!confirm(`¿Eliminar la producción de "${p.producto}" del ${fechaTxt}?`)) return;
                try {
                    const r = await api('eliminar', { id: p.id, fecha: p.fecha });
                    closeModal(); showToast(r.message); await cargarMes();
                } catch (err) { showToast(err.message, true); }
            });
        }

        // ---------- Eventos ----------
        document.getElementById('calendarGrid').addEventListener('click', e => {
            const chip = e.target.closest('.prod-chip');
            if (chip) {
                const p = programaciones.find(x => x.id === chip.dataset.id);
                if (p) abrirDetalle(p);
                return;
            }
            const cell = e.target.closest('.day-cell.is-editable');
            if (cell) abrirFormulario(cell.dataset.fecha);
        });
        document.getElementById('btnMesAnterior').addEventListener('click', () => cambiarMes(-1));
        document.getElementById('btnMesSiguiente').addEventListener('click', () => cambiarMes(1));
        document.getElementById('btnHoy').addEventListener('click', () => {
            const delta = (hoy.getFullYear() - anioActual) * 12 + (hoy.getMonth() + 1 - mesActual);
            if (delta) cambiarMes(delta);
        });
        document.getElementById('btnProgramar')?.addEventListener('click', () => abrirFormulario());
        document.getElementById('selectorSede')?.addEventListener('change', async e => {
            sedeActual = e.target.value;
            document.getElementById('eyebrowSede').textContent = SEDES[sedeActual].toUpperCase();
            await cargarCatalogo();
            await cargarMes();
        });

        (async () => { await cargarCatalogo(); await cargarMes(); })();
    </script>
</body>
</html>
