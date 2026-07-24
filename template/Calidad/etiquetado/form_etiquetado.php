<?php
require_once "../../sesion.php";
verificarAutenticacion();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FORMULARIO ETIQUETADO</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=Barlow+Condensed:wght@300;400;600;700;900&family=Barlow:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0d0f12;
            --surface: #13161b;
            --surface2: #1a1e26;
            --surface3: #1f2430;
            --border: #2a3040;
            --border-glow: #3a4560;
            --accent: #e8c840;
            --accent2: #4fc3f7;
            --accent3: #ef5350;
            --text: #cdd6f4;
            --text-dim: #7a8496;
            --text-bright: #ffffff;
            --success: #a6e3a1;
            --mono: 'Space Mono', monospace;
            --display: 'Barlow Condensed', sans-serif;
            --body: 'Barlow', sans-serif;
            --transition: 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: var(--body);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* ─── BACKGROUND TEXTURE ─── */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                repeating-linear-gradient(0deg, transparent, transparent 39px, rgba(255,255,255,0.015) 40px),
                repeating-linear-gradient(90deg, transparent, transparent 39px, rgba(255,255,255,0.015) 40px);
            pointer-events: none;
            z-index: 0;
        }

        /* ─── SCANLINE OVERLAY ─── */
        body::after {
            content: '';
            position: fixed;
            inset: 0;
            background: repeating-linear-gradient(
                to bottom,
                transparent 0px,
                transparent 3px,
                rgba(0,0,0,0.08) 4px
            );
            pointer-events: none;
            z-index: 1;
        }

        /* ─── MAIN WRAPPER ─── */
        .form-wrapper {
            position: relative;
            z-index: 2;
            max-width: 960px;
            margin: 0 auto;
            padding: 40px 24px 80px;
            animation: fadeInPage 0.6s ease both;
        }

        @keyframes fadeInPage {
            from { opacity: 0; transform: translateY(16px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* ─── TOP HEADER ─── */
        .form-header {
            position: relative;
            border-bottom: 2px solid var(--accent);
            padding-bottom: 28px;
            margin-bottom: 48px;
        }

        .form-header::before {
            content: 'SYS // CALIDAD v2.1';
            display: block;
            font-family: var(--mono);
            font-size: 11px;
            color: var(--text-dim);
            letter-spacing: 0.2em;
            margin-bottom: 12px;
            animation: blink 3s infinite;
        }

        @keyframes blink {
            0%, 90%, 100% { opacity: 1; }
            95% { opacity: 0.3; }
        }

        .form-header h1 {
            font-family: var(--display);
            font-size: clamp(36px, 6vw, 64px);
            font-weight: 900;
            color: var(--text-bright);
            letter-spacing: 0.05em;
            line-height: 1;
            text-transform: uppercase;
        }

        .form-header h1 span {
            color: var(--accent);
        }

        .header-meta {
            display: flex;
            gap: 24px;
            margin-top: 12px;
            flex-wrap: wrap;
        }

        .header-meta span {
            font-family: var(--mono);
            font-size: 11px;
            color: var(--text-dim);
            letter-spacing: 0.15em;
        }

        .status-dot {
            display: inline-block;
            width: 6px;
            height: 6px;
            background: var(--success);
            border-radius: 50%;
            margin-right: 6px;
            animation: pulse-dot 2s infinite;
        }

        @keyframes pulse-dot {
            0%, 100% { opacity: 1; box-shadow: 0 0 0 0 rgba(166, 227, 161, 0.4); }
            50% { opacity: 0.8; box-shadow: 0 0 0 4px rgba(166, 227, 161, 0); }
        }

        /* ─── SECTION CARDS ─── */
        .section-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 2px;
            margin-bottom: 32px;
            overflow: hidden;
            transition: border-color var(--transition);
            animation: slideUp 0.5s ease both;
        }

        .section-card:hover {
            border-color: var(--border-glow);
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(24px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .section-card:nth-child(2) { animation-delay: 0.05s; }
        .section-card:nth-child(3) { animation-delay: 0.1s; }
        .section-card:nth-child(4) { animation-delay: 0.15s; }
        .section-card:nth-child(5) { animation-delay: 0.2s; }
        .section-card:nth-child(6) { animation-delay: 0.25s; }

        .section-header {
            display: flex;
            align-items: center;
            gap: 16px;
            background: var(--surface2);
            padding: 14px 24px;
            border-bottom: 1px solid var(--border);
        }

        .section-number {
            font-family: var(--mono);
            font-size: 11px;
            color: var(--accent);
            letter-spacing: 0.1em;
            background: rgba(232, 200, 64, 0.1);
            border: 1px solid rgba(232, 200, 64, 0.3);
            padding: 3px 8px;
            border-radius: 2px;
            white-space: nowrap;
        }

        .section-header h2 {
            font-family: var(--display);
            font-size: 18px;
            font-weight: 700;
            color: var(--text-bright);
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .section-body {
            padding: 28px 28px 32px;
        }

        /* ─── GENERAL SECTION FIELDS ─── */
        .fields-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        @media (max-width: 600px) {
            .fields-row { grid-template-columns: 1fr; }
        }

        /* ─── FIELD GROUPS ─── */
        .field-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .field-group label,
        label.field-label {
            font-family: var(--mono);
            font-size: 10px;
            color: var(--text-dim);
            letter-spacing: 0.15em;
            text-transform: uppercase;
        }

        input[type="date"],
        input[type="time"],
        input[type="text"],
        select,
        textarea {
            background: var(--surface3);
            border: 1px solid var(--border);
            border-radius: 2px;
            color: var(--text);
            font-family: var(--mono);
            font-size: 13px;
            padding: 10px 14px;
            width: 100%;
            outline: none;
            transition: border-color var(--transition), box-shadow var(--transition), background var(--transition);
            appearance: none;
            -webkit-appearance: none;
        }

        input[type="date"]:focus,
        input[type="time"]:focus,
        input[type="text"]:focus,
        select:focus,
        textarea:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 2px rgba(232, 200, 64, 0.12), inset 0 0 0 1px rgba(232, 200, 64, 0.06);
            background: #22273a;
        }

        input[type="date"]:hover,
        input[type="time"]:hover,
        input[type="text"]:hover,
        select:hover {
            border-color: var(--border-glow);
        }

        /* Select custom arrow */
        select {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%23e8c840' stroke-width='1.5' fill='none' stroke-linecap='square'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
            padding-right: 36px;
            cursor: pointer;
        }

        select option {
            background: var(--surface2);
            color: var(--text);
        }

        /* Select value coloring */
        select[data-value="Cumple"] { color: var(--success); border-color: rgba(166, 227, 161, 0.3); }
        select[data-value="No Cumple"] { color: var(--accent3); border-color: rgba(239, 83, 80, 0.3); }
        select[data-value="N/A"] { color: var(--accent2); border-color: rgba(79, 195, 247, 0.3); }

        /* ─── CHECKBOX GROUP ─── */
        .checkbox-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px;
        }

        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border: 1px solid var(--border);
            border-radius: 2px;
            cursor: pointer;
            transition: border-color var(--transition), background var(--transition);
            user-select: none;
            background: var(--surface3);
        }

        .checkbox-item:hover {
            border-color: var(--accent);
            background: rgba(232, 200, 64, 0.04);
        }

        .checkbox-item input[type="checkbox"] {
            display: none;
        }

        .checkbox-item .check-box {
            width: 16px;
            height: 16px;
            border: 1.5px solid var(--border-glow);
            border-radius: 2px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all var(--transition);
            background: var(--surface);
        }

        .checkbox-item input[type="checkbox"]:checked ~ .check-box,
        .checkbox-item.checked .check-box {
            background: var(--accent);
            border-color: var(--accent);
        }

        .checkbox-item input[type="checkbox"]:checked ~ .check-box::after,
        .checkbox-item.checked .check-box::after {
            content: '';
            display: block;
            width: 9px;
            height: 5px;
            border-left: 2px solid #000;
            border-bottom: 2px solid #000;
            transform: rotate(-45deg) translateY(-1px);
        }

        .checkbox-item .check-label {
            font-family: var(--body);
            font-size: 13px;
            font-weight: 500;
            color: var(--text);
            transition: color var(--transition);
        }

        .checkbox-item input[type="checkbox"]:checked ~ .check-label {
            color: var(--text-bright);
        }

        /* ─── DYNAMIC SECTIONS ─── */
        .seccion-producto {
            background: var(--surface2);
            border: 1px solid var(--border);
            border-left: 3px solid var(--accent2);
            border-radius: 2px;
            margin-top: 16px;
            overflow: hidden;
            animation: expandIn 0.35s cubic-bezier(0.4, 0, 0.2, 1) both;
        }

        @keyframes expandIn {
            from { opacity: 0; transform: scaleY(0.95); transform-origin: top; }
            to { opacity: 1; transform: scaleY(1); }
        }

        .seccion-producto h3 {
            font-family: var(--display);
            font-size: 14px;
            font-weight: 700;
            color: var(--accent2);
            text-transform: uppercase;
            letter-spacing: 0.12em;
            padding: 14px 20px 12px;
            background: rgba(79, 195, 247, 0.05);
            border-bottom: 1px solid var(--border);
        }

        #mini-categorias-Materias\ Primas,
        [id^="mini-categorias-"] {
            padding: 16px 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            border-bottom: 1px solid var(--border);
        }

        [id^="mini-categorias-"] label {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 7px 12px;
            border: 1px solid var(--border);
            border-radius: 2px;
            cursor: pointer;
            font-size: 12px;
            color: var(--text-dim);
            background: var(--surface);
            transition: all var(--transition);
        }

        [id^="mini-categorias-"] label:hover {
            border-color: var(--accent2);
            color: var(--accent2);
            background: rgba(79, 195, 247, 0.05);
        }

        [id^="mini-categorias-"] input[type="checkbox"] {
            width: 12px;
            height: 12px;
            accent-color: var(--accent2);
        }

        [id^="mini-seccion-"] {
            padding: 12px 20px 20px;
        }

        /* ─── CATEGORY SECTIONS ─── */
        [id^="cat-"] {
            background: var(--surface3);
            border: 1px solid var(--border);
            border-radius: 2px;
            padding: 18px;
            margin-top: 12px;
            animation: expandIn 0.3s ease both;
        }

        [id^="cat-"] h4 {
            font-family: var(--display);
            font-size: 13px;
            font-weight: 700;
            color: var(--accent);
            text-transform: uppercase;
            letter-spacing: 0.15em;
            margin-bottom: 14px;
            padding-bottom: 8px;
            border-bottom: 1px dashed var(--border);
        }

        [id^="cat-"] label {
            display: flex;
            flex-direction: column;
            gap: 5px;
            margin-bottom: 10px;
            font-family: var(--mono);
            font-size: 10px;
            color: var(--text-dim);
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }

        [id^="cat-"] input,
        [id^="cat-"] select {
            margin-top: 2px;
        }

        /* ─── FIELDSET GROUPS ─── */
        fieldset {
            border: 1px solid var(--border);
            border-radius: 2px;
            padding: 14px 16px;
            margin: 10px 0;
        }

        legend {
            font-family: var(--mono);
            font-size: 10px;
            color: var(--accent2);
            letter-spacing: 0.15em;
            text-transform: uppercase;
            padding: 0 8px;
        }

        /* ─── PNC SECTIONS ─── */
        .pnc-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        @media (max-width: 680px) {
            .pnc-grid { grid-template-columns: 1fr; }
        }

        .pnc-block {
            background: var(--surface2);
            border: 1px solid var(--border);
            border-radius: 2px;
            padding: 18px;
        }

        .pnc-block h3 {
            font-family: var(--display);
            font-size: 13px;
            font-weight: 700;
            color: var(--text-dim);
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 1px solid var(--border);
        }

        .select-field {
            margin-bottom: 12px;
        }

        .select-field label {
            display: block;
            font-family: var(--mono);
            font-size: 10px;
            color: var(--text-dim);
            letter-spacing: 0.12em;
            text-transform: uppercase;
            margin-bottom: 5px;
            line-height: 1.4;
        }

        /* ─── SUBMIT BUTTON ─── */
        .form-footer {
            margin-top: 40px;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 20px;
        }

        .submit-btn {
            position: relative;
            background: var(--accent);
            color: #000;
            border: none;
            border-radius: 2px;
            font-family: var(--display);
            font-size: 16px;
            font-weight: 900;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            padding: 14px 48px;
            cursor: pointer;
            transition: all var(--transition);
            overflow: hidden;
        }

        .submit-btn::before {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(255,255,255,0.15);
            transform: translateX(-100%);
            transition: transform 0.4s ease;
        }

        .submit-btn:hover::before {
            transform: translateX(0);
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(232, 200, 64, 0.3);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .form-footer-meta {
            font-family: var(--mono);
            font-size: 10px;
            color: var(--text-dim);
            letter-spacing: 0.1em;
        }

        /* ─── DIVIDERS ─── */
        .section-divider {
            height: 1px;
            background: linear-gradient(to right, transparent, var(--border), transparent);
            margin: 24px 0;
        }

        /* ─── CORNER DECORATION ─── */
        .corner-deco {
            position: fixed;
            top: 0;
            right: 0;
            width: 200px;
            height: 200px;
            pointer-events: none;
            z-index: 0;
            opacity: 0.15;
        }

        /* ─── TOAST ─── */
        .toast {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: var(--surface2);
            border: 1px solid var(--accent);
            border-radius: 2px;
            padding: 14px 20px;
            font-family: var(--mono);
            font-size: 12px;
            color: var(--accent);
            letter-spacing: 0.1em;
            z-index: 999;
            transform: translateY(80px);
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .toast.show {
            transform: translateY(0);
            opacity: 1;
        }

        /* ─── UTILITY ─── */
        .mt-4 { margin-top: 16px; }
        .tag {
            display: inline-block;
            font-family: var(--mono);
            font-size: 10px;
            color: var(--text-dim);
            letter-spacing: 0.1em;
            padding: 2px 8px;
            border: 1px solid var(--border);
            border-radius: 1px;
            margin-right: 6px;
        }
    </style>
</head>
<body>

<svg class="corner-deco" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
    <circle cx="200" cy="0" r="160" stroke="#e8c840" stroke-width="1"/>
    <circle cx="200" cy="0" r="120" stroke="#e8c840" stroke-width="0.5"/>
    <circle cx="200" cy="0" r="80" stroke="#e8c840" stroke-width="0.5"/>
    <line x1="200" y1="0" x2="0" y2="200" stroke="#e8c840" stroke-width="0.5"/>
    <line x1="200" y1="0" x2="60" y2="200" stroke="#e8c840" stroke-width="0.3"/>
</svg>

<div class="form-wrapper">

    <div class="form-header">
        <h1>Inspección de <span>Etiquetado</span></h1>
        <div class="header-meta">
            <span><span class="status-dot"></span>SISTEMA ACTIVO</span>
            <span>CTRL // CALIDAD</span>
            <span>REV 2.1</span>
        </div>
    </div>

    <form action="">

        <!-- SECCIÓN GENERAL -->
        <div class="section-card">
            <div class="section-header">
                <a class="section-number" href="/template/menu_adm_calidad.html">Volver</a>
                <h2>Sección General</h2>
            </div>
            <div class="section-body">
                <div class="fields-row">
                    <div class="field-group">
                        <label for="fecha_inspeccion">Fecha de Inspección</label>
                        <input type="date" id="fecha_inspeccion" name="fecha_inspeccion">
                    </div>
                    <div class="field-group">
                        <label for="hora_inspeccion">Hora de Inspección</label>
                        <input type="time" id="hora_inspeccion" name="hora_inspeccion">
                    </div>
                    <input type="hidden" id="nombre_usuario" value="<?php echo $_SESSION['nombre']; ?>">
                    <input type="hidden" id="sede_usuario" value="<?php echo $_SESSION['sede']; ?>">
                </div>
            </div>
        </div>

        <!-- SECCIÓN 1 -->
        <div class="section-card">
            <div class="section-header">
                <span class="section-number">SEC — 01</span>
                <h2>Materiales de Proceso — Insumos y Productos</h2>
            </div>
            <div class="section-body">
                <div id="tipo-insumo-checklist" class="checkbox-grid">
                    <label class="checkbox-item">
                        <input type="checkbox" value="Materias Primas">
                        <span class="check-box"></span>
                        <span class="check-label">Materias Primas</span>
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" value="Materiales de Envasado">
                        <span class="check-box"></span>
                        <span class="check-label">Materiales de Envasado</span>
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" value="Productos en Proceso">
                        <span class="check-box"></span>
                        <span class="check-label">Productos en Proceso</span>
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" value="Subproductos">
                        <span class="check-box"></span>
                        <span class="check-label">Subproductos</span>
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" value="Productos Terminados">
                        <span class="check-box"></span>
                        <span class="check-label">Productos Terminados</span>
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" value="Productos en Reproceso">
                        <span class="check-box"></span>
                        <span class="check-label">Productos en Reproceso</span>
                    </label>
                </div>
                <div id="secciones-dinamicas"></div>
            </div>
        </div>

        <!-- SECCIÓN 2: PNC's -->
        <div class="section-card">
            <div class="section-header">
                <span class="section-number">SEC — 02</span>
                <h2>PNC's</h2>
            </div>
            <div class="section-body">
                <div class="pnc-grid">
                    <div class="pnc-block">
                        <h3>Lotes con 10 bultos o menos</h3>
                        <div class="select-field">
                            <label for="rotulo_correspondiente">Presentan el rótulo de PNC correspondiente</label>
                            <select name="rotulo_correspondiente" id="rotulo_correspondiente">
                                <option value="">— Seleccionar —</option>
                                <option value="Cumple">Cumple</option>
                                <option value="No Cumple">No Cumple</option>
                                <option value="N/A">No Aplica</option>
                            </select>
                        </div>
                        <div class="select-field">
                            <label for="rotulo_condiciones">El rótulo PNC está en buenas condiciones y sin desprender</label>
                            <select name="rotulo_condiciones" id="rotulo_condiciones">
                                <option value="">— Seleccionar —</option>
                                <option value="Cumple">Cumple</option>
                                <option value="No Cumple">No Cumple</option>
                                <option value="N/A">No Aplica</option>
                            </select>
                        </div>
                        <div class="select-field">
                            <label for="rotulo_legible">El contenido de todos los rótulos PNC es legible e indeleble</label>
                            <select name="rotulo_legible" id="rotulo_legible">
                                <option value="">— Seleccionar —</option>
                                <option value="Cumple">Cumple</option>
                                <option value="No Cumple">No Cumple</option>
                                <option value="N/A">No Aplica</option>
                            </select>
                        </div>
                    </div>
                    <div class="pnc-block">
                        <h3>Planchas de PNC — Lotes con más de 10 bultos</h3>
                        <div class="select-field">
                            <label for="identificacion_correspondiente">Presentan el formato de identificación correspondiente</label>
                            <select name="identificacion_correspondiente" id="identificacion_correspondiente">
                                <option value="">— Seleccionar —</option>
                                <option value="Cumple">Cumple</option>
                                <option value="No Cumple">No Cumple</option>
                                <option value="N/A">No Aplica</option>
                            </select>
                        </div>
                        <div class="select-field">
                            <label for="identificacion_condiciones">Formato de identificación en buenas condiciones y sin desprender</label>
                            <select name="identificacion_condiciones" id="identificacion_condiciones">
                                <option value="">— Seleccionar —</option>
                                <option value="Cumple">Cumple</option>
                                <option value="No Cumple">No Cumple</option>
                                <option value="N/A">No Aplica</option>
                            </select>
                        </div>
                        <div class="select-field">
                            <label for="identificacion_legible">El contenido de todos los formatos de identificación es legible e indeleble</label>
                            <select name="identificacion_legible" id="identificacion_legible">
                                <option value="">— Seleccionar —</option>
                                <option value="Cumple">Cumple</option>
                                <option value="No Cumple">No Cumple</option>
                                <option value="N/A">No Aplica</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECCIÓN 3 -->
        <div class="section-card">
            <div class="section-header">
                <span class="section-number">SEC — 03</span>
                <h2>Insumos en Líneas de Producción</h2>
            </div>
            <div class="section-body">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div class="select-field">
                        <label for="identifempaques_vencimiento">Empaques timbrados con lote y fecha de vencimiento</label>
                        <select name="empaques_vencimiento" id="identifempaques_vencimiento">
                            <option value="">— Seleccionar —</option>
                            <option value="Cumple">Cumple</option>
                            <option value="No Cumple">No Cumple</option>
                            <option value="N/A">No Aplica</option>
                        </select>
                    </div>
                    <div class="select-field">
                        <label for="envasado_correspondiente">Empaques, hilos y rótulos adhesivos asignados correctamente a la línea de envasado</label>
                        <select name="envasado_correspondiente" id="envasado_correspondiente">
                            <option value="">— Seleccionar —</option>
                            <option value="Cumple">Cumple</option>
                            <option value="No Cumple">No Cumple</option>
                            <option value="N/A">No Aplica</option>
                        </select>
                    </div>
                    <div class="select-field">
                        <label for="hilos_identificados">Rollos de hilo (Unidades) claramente identificados</label>
                        <select name="hilos_identificados" id="hilos_identificados">
                            <option value="">— Seleccionar —</option>
                            <option value="Cumple">Cumple</option>
                            <option value="No Cumple">No Cumple</option>
                            <option value="N/A">No Aplica</option>
                        </select>
                    </div>
                    <div class="select-field">
                        <label for="aditivos_etiquetados">Aditivos alimentarios debidamente etiquetados</label>
                        <select name="aditivos_etiquetados" id="aditivos_etiquetados">
                            <option value="">— Seleccionar —</option>
                            <option value="Cumple">Cumple</option>
                            <option value="No Cumple">No Cumple</option>
                            <option value="N/A">No Aplica</option>
                        </select>
                    </div>
                    <div class="select-field">
                        <label for="mejorantes_etiquetados">Mejorantes debidamente etiquetados</label>
                        <select name="mejorantes_etiquetados" id="mejorantes_etiquetados">
                            <option value="">— Seleccionar —</option>
                            <option value="Cumple">Cumple</option>
                            <option value="No Cumple">No Cumple</option>
                            <option value="N/A">No Aplica</option>
                        </select>
                    </div>
                    <div class="select-field">
                        <label for="empaques_identificados">Empaques (Por unidad) debidamente identificados</label>
                        <select name="empaques_identificados" id="empaques_identificados">
                            <option value="">— Seleccionar —</option>
                            <option value="Cumple">Cumple</option>
                            <option value="No Cumple">No Cumple</option>
                            <option value="N/A">No Aplica</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECCIÓN 4 -->
        <div class="section-card">
            <div class="section-header">
                <span class="section-number">SEC — 04</span>
                <h2>Etiquetas y Empaques Defectuosos y/o Obsoletos</h2>
            </div>
            <div class="section-body">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div class="select-field">
                        <label for="no_conformes_identificados">Bodega de insumos con mejorantes de empaque no conformes debidamente identificados y aislados</label>
                        <select name="no_conformes_identificados" id="no_conformes_identificados">
                            <option value="">— Seleccionar —</option>
                            <option value="Cumple">Cumple</option>
                            <option value="No Cumple">No Cumple</option>
                            <option value="N/A">No Aplica</option>
                        </select>
                    </div>
                    <div class="select-field">
                        <label for="bodegas_libres">Bodegas de producto terminado libres de este material</label>
                        <select name="bodegas_libres" id="bodegas_libres">
                            <option value="">— Seleccionar —</option>
                            <option value="Cumple">Cumple</option>
                            <option value="No Cumple">No Cumple</option>
                            <option value="N/A">No Aplica</option>
                        </select>
                    </div>
                    <div class="select-field">
                        <label for="lineas_libres">Líneas de producción libres de este material</label>
                        <select name="lineas_libres" id="lineas_libres">
                            <option value="">— Seleccionar —</option>
                            <option value="Cumple">Cumple</option>
                            <option value="No Cumple">No Cumple</option>
                            <option value="N/A">No Aplica</option>
                        </select>
                    </div>
                    <div class="select-field">
                        <label for="contenedor_recoleccion">Uso de contenedor o bolsas para la recolección de estos materiales</label>
                        <select name="contenedor_recoleccion" id="contenedor_recoleccion">
                            <option value="">— Seleccionar —</option>
                            <option value="Cumple">Cumple</option>
                            <option value="No Cumple">No Cumple</option>
                            <option value="N/A">No Aplica</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="section-card">
            <div class="section-header">
                <h2>OBSERVACIONES Y COMENTARIOS</h2>
            </div>
            <div class="section-body">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div class="select-field">
                        <textarea name="observaciones_comentarios" id="observaciones_comentarios"></textarea>
                    </div>
                </div>
            </div>
        </div>
        <!-- FOOTER -->
        <div class="form-footer">
            <span class="form-footer-meta">TODOS LOS CAMPOS REQUERIDOS</span>
            <button type="submit" id="enviar-formulario" class="submit-btn">Enviar Inspección</button>
        </div>
        

    </form>
</div>

<!-- TOAST -->
<div class="toast" id="toast">✓ DATOS ENVIADOS CORRECTAMENTE</div>

<script>
    // ─── SELECT COLOR FEEDBACK ───
    document.addEventListener('change', function(e) {
        if (e.target.tagName === 'SELECT') {
            e.target.setAttribute('data-value', e.target.value);
        }
    });

    // ─── CHECKBOX VISUAL SYNC ───
    document.querySelectorAll('.checkbox-item').forEach(item => {
        const input = item.querySelector('input[type="checkbox"]');
        if (!input) return;
        input.addEventListener('change', () => {
            item.classList.toggle('checked', input.checked);
        });
    });

    // ─── ORIGINAL DATA ───
    const camposPorTipo = {
        "Materias Primas": [],
        "Materiales de Envasado": [],
        "Productos en Proceso": [],
        "Subproductos": [],
        "Productos Terminados": [],
        "Productos en Reproceso": []
    };

    const categoria_producto = {
        "Aditivos Alimentarios": [
            { label: "Nombre", type: "text" },
            { label: "Lote Proveedor", type: "text" },
            { label: "Lote Interno", type: "text" },
            { label: "Fecha Vencimiento", type: "date" },
            { label: "Proveedor", type: "text", value: "Organizacion MAS" },
            { label: "Ubicacion del Insumo / Producto", type: "select", options: ["— Seleccionar —", "BODEGA PNC", "BODEGA MOGOLLA", "BODEGA 1", "BODEGA 2", "BODEGA 3", "BODEGA 4", "BODEGA PRE MEZCLAS", "BODEGA MEJORANTES", "PTFAMILIAR", "PTESPECIAL", "BODEGA DE EMPAQUE", "PTINDUSTRIAL", "MICROINGREDIENTES", "LABORATORIO"]},
            { label: "Etiqueta Completa y sin desprender", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { label: "Contenido legible o indeleble", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { label: "Declaracion de Alergenos completa", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { label: "Cumplimiento de etiquetado del insumo/producto (%)", type: "text"}
        ],
        "Mejorantes": [
            { label: "Nombre", type: "text" },
            { label: "Lote Interno", type: "text" },
            { label: "Fecha Vencimiento", type: "date" },
            { label: "Proveedor", type: "text", value: "Organizacion MAS" },
            { label: "Ubicacion del Insumo / Producto", type: "select", options: ["— Seleccionar —", "BODEGA PNC", "BODEGA MOGOLLA", "BODEGA 1", "BODEGA 2", "BODEGA 3", "BODEGA 4", "BODEGA PRE MEZCLAS", "BODEGA MEJORANTES", "PTFAMILIAR", "PTESPECIAL", "BODEGA DE EMPAQUE", "PTINDUSTRIAL", "MICROINGREDIENTES", "LABORATORIO"]},
            { label: "Etiqueta Completa y sin desprender", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { label: "Contenido legible o indeleble", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { label: "Declaracion de Alergenos completa", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { label: "Cumplimiento de etiquetado del insumo/producto (%)", type: "text"}
        ],
        "Granos y semillas Nacional": [
            { label: "Nombre", type: "text" },
            { label: "Lote Proveedor", type: "text" },
            { label: "Lote Interno", type: "text" },
            { label: "Fecha Vencimiento", type: "date" },
            { label: "Proveedor", type: "text", value: "Organizacion MAS" },
            { label: "Ubicacion del Insumo / Producto", type: "select", options: ["— Seleccionar —", "BODEGA PNC", "BODEGA MOGOLLA", "BODEGA 1", "BODEGA 2", "BODEGA 3", "BODEGA 4", "BODEGA PRE MEZCLAS", "BODEGA MEJORANTES", "PTFAMILIAR", "PTESPECIAL", "BODEGA DE EMPAQUE", "PTINDUSTRIAL", "MICROINGREDIENTES", "LABORATORIO"]},
            { label: "Etiqueta Completa y sin desprender", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { label: "Contenido legible o indeleble", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { label: "Declaracion de Alergenos completa", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { label: "Cumplimiento de etiquetado del insumo/producto (%)", type: "text"}
        ],
    };

    const categoria_envasado = {
        "Empaques": [
            { label: "Nombre", type: "text" },
            { label: "Lote Proveedor", type: "text" },
            { label: "Lote Interno", type: "text" },
            { label: "Proveedor", type: "text", value: "Organizacion MAS" },
            { label: "Ubicacion del Insumo / Producto", type: "select", options: ["— Seleccionar —", "BODEGA PNC", "BODEGA MOGOLLA", "BODEGA 1", "BODEGA 2", "BODEGA 3", "BODEGA 4", "BODEGA PRE MEZCLAS", "BODEGA MEJORANTES", "PTFAMILIAR", "PTESPECIAL", "BODEGA DE EMPAQUE", "PTINDUSTRIAL", "MICROINGREDIENTES", "LABORATORIO"]},
            { group: "Etiquetado impreso en el empaque"},
            { label: "Etiquetado en buen estado y legible", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { label: "Etiquetado completo e indeleble", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { group: "Rotulado con adhesivos/tarjetas"},
            { label: "Etiqueta Completa y sin desprender", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { label: "Contenido legible o indeleble", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { group: ""},
            { label: "Declaracion de Alergenos completa", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { label: "Cumplimiento de etiquetado del insumo/producto (%)", type: "text"},
            { label: "Observaciones", type: "text"}
        ],
        "Hilos": [
            { label: "Nombre", type: "text" },
            { label: "Lote Proveedor", type: "text" },
            { label: "Lote Interno", type: "text" },
            { label: "Proveedor", type: "text", value: "Organizacion MAS" },
            { label: "Ubicacion del Insumo / Producto", type: "select", options: ["— Seleccionar —", "BODEGA PNC", "BODEGA MOGOLLA", "BODEGA 1", "BODEGA 2", "BODEGA 3", "BODEGA 4", "BODEGA PRE MEZCLAS", "BODEGA MEJORANTES", "PTFAMILIAR", "PTESPECIAL", "BODEGA DE EMPAQUE", "PTINDUSTRIAL", "MICROINGREDIENTES", "LABORATORIO"]},
            { group: "Rotulado con adhesivos/tarjetas"},
            { label: "Etiqueta Completa y sin desprender", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { label: "Contenido legible o indeleble", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { group: ""},
            { label: "Declaracion de Alergenos completa", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { label: "Cumplimiento de etiquetado del insumo/producto (%)", type: "text"}
        ],
    };

    const categoria_en_proceso = {
        "Producto": [
            { label: "Nombre del Producto", type: "text" },
            { label: "lote interno", type: "text"},
            { label: "fecha de vencimiento", type: "date"},
            { label: "Ubicacion del Insumo / Producto", type: "select", options: ["— Seleccionar —", "BODEGA PNC", "BODEGA MOGOLLA", "BODEGA 1", "BODEGA 2", "BODEGA 3", "BODEGA 4", "BODEGA PRE MEZCLAS", "BODEGA MEJORANTES", "PTFAMILIAR", "PTESPECIAL", "BODEGA DE EMPAQUE", "PTINDUSTRIAL", "MICROINGREDIENTES", "LABORATORIO"]},
            { label: "Ubicacion en el molino", type: "select", options: ["— Seleccionar —", "Piso 1", "Piso 2"]},
            { group: "Etiquetado impreso en el empaque"},
            { label: "En buen estado del etiquetado impreso en el empaque", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { label: "El etiquetado impreso en el empaque es legible", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { label: "El etiquetado impreso en el empaque es completo e indeleble", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { group: "Lote y Fecha de vencimiento timbrado en el empaque"},
            { label: "En buen estado y legible el lote y fecha de vencimiento timbrado en el empaque", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { label: "El lote y fecha de vencimiento timbrado en el empaque es completo e indeleble", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { group: ""},
            { group: "Rotulado con adhesivos / tarjetas"},
            { label: "Etiqueta Completa y sin desprender", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { label: "Contenido legible o indeleble", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { group: ""},
            { label: "Declaracion de Alergenos completa", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { label: "Cumplimiento de etiquetado del insumo/producto (%)", type: "text"}
        ]
    };

    const categoria_subproducto = {
        "SubProducto": [
            { label: "Nombre del Producto", type: "text" },
            { label: "lote interno", type: "text"},
            { label: "fecha de vencimiento", type: "date"},
            { label: "Ubicacion del Insumo / Producto", type: "select", options: ["— Seleccionar —", "BODEGA PNC", "BODEGA MOGOLLA", "BODEGA 1", "BODEGA 2", "BODEGA 3", "BODEGA 4", "BODEGA PRE MEZCLAS", "BODEGA MEJORANTES", "PTFAMILIAR", "PTESPECIAL", "BODEGA DE EMPAQUE", "PTINDUSTRIAL", "MICROINGREDIENTES", "LABORATORIO"]},
            { group: "Etiquetado impreso en el empaque"},
            { label: "En buen estado del etiquetado impreso en el empaque", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { label: "El etiquetado impreso en el empaque es legible", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { label: "El etiquetado impreso en el empaque es completo e indeleble", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { group: "Lote y Fecha de vencimiento timbrado en el empaque"},
            { label: "En buen estado y legible el lote y fecha de vencimiento timbrado en el empaque", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { label: "El lote y fecha de vencimiento timbrado en el empaque es completo e indeleble", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { group: ""},
            { label: "Declaracion de Alergenos completa", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { label: "Cumplimiento de etiquetado del insumo/producto (%)", type: "text"}
        ]
    };

    const categoria_productos_terminados = {
        "Concentrados": [
            { label: "Nombre del Producto", type: "text" },
            { label: "lote interno", type: "text"},
            { label: "fecha de vencimiento", type: "date"},
            { label: "Ubicacion del Insumo / Producto", type: "select", options: ["— Seleccionar —", "BODEGA PNC", "BODEGA MOGOLLA", "BODEGA 1", "BODEGA 2", "BODEGA 3", "BODEGA 4", "BODEGA PRE MEZCLAS", "BODEGA MEJORANTES", "PTFAMILIAR", "PTESPECIAL", "BODEGA DE EMPAQUE", "PTINDUSTRIAL", "MICROINGREDIENTES", "LABORATORIO"]},
            { group: "Etiquetado impreso en el empaque"},
            { label: "En buen estado del etiquetado impreso en el empaque", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { label: "El etiquetado impreso en el empaque es completo e indeleble", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { group: "Lote y Fecha de vencimiento timbrado en el empaque"},
            { label: "En buen estado y legible el lote y fecha de vencimiento timbrado en el empaque", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { label: "El lote y fecha de vencimiento timbrado en el empaque es completo e indeleble", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { group: "Rotulado con adhesivos / tarjetas"},
            { label: "Etiqueta Completa y sin desprender", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { label: "Contenido legible o indeleble", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { group: ""},
            { label: "Declaracion de Alergenos completa", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { label: "Cumplimiento de etiquetado del insumo/producto (%)", type: "text"}
        ],
        "Harinas De trigo": [
            { label: "Nombre del Producto", type: "text" },
            { label: "lote interno", type: "text"},
            { label: "fecha de vencimiento", type: "date"},
            { label: "Ubicacion del Insumo / Producto", type: "select", options: ["— Seleccionar —", "BODEGA PNC", "BODEGA MOGOLLA", "BODEGA 1", "BODEGA 2", "BODEGA 3", "BODEGA 4", "BODEGA PRE MEZCLAS", "BODEGA MEJORANTES", "PTFAMILIAR", "PTESPECIAL", "BODEGA DE EMPAQUE", "PTINDUSTRIAL", "MICROINGREDIENTES", "LABORATORIO"]},
            { group: "Etiquetado impreso en el empaque"},
            { label: "En buen estado del etiquetado impreso en el empaque", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { label: "El etiquetado impreso en el empaque es completo e indeleble", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { group: "Lote y Fecha de vencimiento timbrado en el empaque"},
            { label: "En buen estado y legible el lote y fecha de vencimiento timbrado en el empaque", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { label: "El lote y fecha de vencimiento timbrado en el empaque es completo e indeleble", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { group: "Rotulado con adhesivos / tarjetas"},
            { label: "Etiqueta Completa y sin desprender", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { label: "Contenido legible o indeleble", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { group: ""},
            { label: "Declaracion de Alergenos completa", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { label: "Cumplimiento de etiquetado del insumo/producto (%)", type: "text"}
        ],
        "Harinas Especiales": [
            { label: "Nombre del Producto", type: "text" },
            { label: "lote interno", type: "text"},
            { label: "fecha de vencimiento", type: "date"},
            { label: "Ubicacion del Insumo / Producto", type: "select", options: ["— Seleccionar —", "BODEGA PNC", "BODEGA MOGOLLA", "BODEGA 1", "BODEGA 2", "BODEGA 3", "BODEGA 4", "BODEGA PRE MEZCLAS", "BODEGA MEJORANTES", "PTFAMILIAR", "PTESPECIAL", "BODEGA DE EMPAQUE", "PTINDUSTRIAL", "MICROINGREDIENTES", "LABORATORIO"]},
            { group: "Etiquetado impreso en el empaque"},
            { label: "En buen estado del etiquetado impreso en el empaque", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { label: "El etiquetado impreso en el empaque es completo e indeleble", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { group: "Lote y Fecha de vencimiento timbrado en el empaque"},
            { label: "En buen estado y legible el lote y fecha de vencimiento timbrado en el empaque", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { label: "El lote y fecha de vencimiento timbrado en el empaque es completo e indeleble", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { group: "Rotulado con adhesivos / tarjetas"},
            { label: "Etiqueta Completa y sin desprender", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { label: "Contenido legible o indeleble", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { group: ""},
            { label: "Declaracion de Alergenos completa", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { label: "Cumplimiento de etiquetado del insumo/producto (%)", type: "text"}
        ],
        "Productos Especiales": [
            { label: "Nombre del Producto", type: "text" },
            { label: "lote interno", type: "text"},
            { label: "fecha de vencimiento", type: "date"},
            { label: "Ubicacion del Insumo / Producto", type: "select", options: ["— Seleccionar —", "BODEGA PNC", "BODEGA MOGOLLA", "BODEGA 1", "BODEGA 2", "BODEGA 3", "BODEGA 4", "BODEGA PRE MEZCLAS", "BODEGA MEJORANTES", "PTFAMILIAR", "PTESPECIAL", "BODEGA DE EMPAQUE", "PTINDUSTRIAL", "MICROINGREDIENTES", "LABORATORIO"]},
            { group: "Etiquetado impreso en el empaque"},
            { label: "En buen estado del etiquetado impreso en el empaque", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { label: "El etiquetado impreso en el empaque es completo e indeleble", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { group: "Lote y Fecha de vencimiento timbrado en el empaque"},
            { label: "En buen estado y legible el lote y fecha de vencimiento timbrado en el empaque", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { label: "El lote y fecha de vencimiento timbrado en el empaque es completo e indeleble", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { group: "Rotulado con adhesivos / tarjetas"},
            { label: "Etiqueta Completa y sin desprender", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { label: "Contenido legible o indeleble", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { group: ""},
            { label: "Declaracion de Alergenos completa", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { label: "Cumplimiento de etiquetado del insumo/producto (%)", type: "text"}
        ]
    };

    const categoria_reproceso = {
        "Purgas de proceso": [
            { label: "Nombre del Producto", type: "text" },
            { label: "Ubicacion del Insumo / Producto", type: "select", options: ["— Seleccionar —", "BODEGA PNC", "BODEGA MOGOLLA", "BODEGA 1", "BODEGA 2", "BODEGA 3", "BODEGA 4", "BODEGA PRE MEZCLAS", "BODEGA MEJORANTES", "PTFAMILIAR", "PTESPECIAL", "BODEGA DE EMPAQUE", "PTINDUSTRIAL", "MICROINGREDIENTES", "LABORATORIO"]},
            { group: "Etiquetado impreso en el empaque"},
            { label: "En buen estado del etiquetado impreso en el empaque", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { label: "El etiquetado impreso en el empaque es completo e indeleble", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { group: "Lote y Fecha de vencimiento timbrado en el empaque"},
            { label: "En buen estado y legible el lote y fecha de vencimiento timbrado en el empaque", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { label: "El lote y fecha de vencimiento timbrado en el empaque es completo e indeleble", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { group: "Rotulado con adhesivos / tarjetas"},
            { label: "Etiqueta Completa y sin desprender", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { label: "Contenido legible o indeleble", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { group: ""},
            { label: "Declaracion de Alergenos completa", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { label: "Cumplimiento de etiquetado del insumo/producto (%)", type: "text"}
        ],
        "Coneccion de PNC": [
            { label: "Nombre del Producto", type: "text" },
            { label: "lote interno", type: "text"},
            { label: "fecha de vencimiento", type: "date"},
            { label: "Ubicacion del Insumo / Producto", type: "select", options: ["— Seleccionar —", "BODEGA PNC", "BODEGA MOGOLLA", "BODEGA 1", "BODEGA 2", "BODEGA 3", "BODEGA 4", "BODEGA PRE MEZCLAS", "BODEGA MEJORANTES", "PTFAMILIAR", "PTESPECIAL", "BODEGA DE EMPAQUE", "PTINDUSTRIAL", "MICROINGREDIENTES", "LABORATORIO"]},
            { group: "Etiquetado impreso en el empaque"},
            { label: "En buen estado del etiquetado impreso en el empaque", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { label: "El etiquetado impreso en el empaque es completo e indeleble", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { group: "Lote y Fecha de vencimiento timbrado en el empaque"},
            { label: "En buen estado y legible el lote y fecha de vencimiento timbrado en el empaque", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { label: "El lote y fecha de vencimiento timbrado en el empaque es completo e indeleble", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { group: "Rotulado con adhesivos / tarjetas"},
            { label: "Etiqueta Completa y sin desprender", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { label: "Contenido legible o indeleble", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { group: ""},
            { label: "Declaracion de Alergenos completa", type: "select", options: ["Cumple", "No Cumple", "No Aplica"]},
            { label: "Cumplimiento de etiquetado del insumo/producto (%)", type: "text"}
        ]
    };

    function crearCampos(campos) {
        let html = '';
        let inGroup = false;
        campos.forEach(campo => {
            if (campo.group !== undefined) {
                if (inGroup) { html += '</fieldset>'; inGroup = false; }
                if (campo.group) {
                    html += `<fieldset><legend>${campo.group}</legend>`;
                    inGroup = true;
                }
            } else if (campo.type === "select") {
                html += `
                    <label>${campo.label}:
                        <select name="${campo.label}" onchange="this.setAttribute('data-value',this.value)">
                            ${campo.options.map(opt => `<option value="${opt === 'No Aplica' ? 'N/A' : opt}">${opt}</option>`).join('')}
                        </select>
                    </label>`;
            } else if (campo.type === "textarea") {
                html += `<label>${campo.label}:<textarea name="${campo.label}">${campo.value || ""}</textarea></label>`;
            } else {
                html += `<label>${campo.label}:<input type="${campo.type}" name="${campo.label}" value="${campo.value || ""}"></label>`;
            }
        });
        if (inGroup) html += '</fieldset>';
        return html;
    }

    function crearSeccion(tipo) {
        const div = document.createElement('div');
        div.className = 'seccion-producto';
        div.id = `seccion-${tipo.replace(/\s/g, '-')}`;
        let categoriasObj;
        if (tipo === "Materiales de Envasado") categoriasObj = categoria_envasado;
        else if (tipo === "Productos en Proceso") categoriasObj = categoria_en_proceso;
        else if (tipo === "Subproductos") categoriasObj = categoria_subproducto;
        else if (tipo === "Productos Terminados") categoriasObj = categoria_productos_terminados;
        else if (tipo === "Productos en Reproceso") categoriasObj = categoria_reproceso;
        else categoriasObj = categoria_producto;

        const categoriasHtml = Object.keys(categoriasObj).map(cat => `
            <label>
                <input type="checkbox" class="mini-categoria-checkbox" data-tipo="${tipo}" value="${cat}">
                ${cat}
            </label>
        `).join('');

        div.innerHTML = `
            <h3>${tipo}</h3>
            <div id="mini-categorias-${tipo}">${categoriasHtml}</div>
            <div id="mini-seccion-${tipo}"></div>
        `;
        return div;
    }

    document.querySelectorAll('#tipo-insumo-checklist input[type="checkbox"]').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const contenedor = document.getElementById('secciones-dinamicas');
            const tipo = checkbox.value;
            const idSeccion = `seccion-${tipo.replace(/\s/g, '-')}`;
            if (checkbox.checked) {
                if (!document.getElementById(idSeccion)) {
                    contenedor.appendChild(crearSeccion(tipo));
                }
            } else {
                const seccion = document.getElementById(idSeccion);
                if (seccion) {
                    seccion.style.animation = 'none';
                    seccion.style.opacity = '0';
                    seccion.style.transform = 'scaleY(0.95)';
                    seccion.style.transition = 'opacity 0.2s, transform 0.2s';
                    setTimeout(() => seccion.remove(), 200);
                }
            }
        });
    });

    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('mini-categoria-checkbox')) {
            const tipoPadre = e.target.getAttribute('data-tipo');
            const contenedorMini = document.getElementById(`mini-seccion-${tipoPadre}`);
            const categoria = e.target.value;
            let categoriasObj;
            if (tipoPadre === "Materiales de Envasado") categoriasObj = categoria_envasado;
            else if (tipoPadre === "Productos en Proceso") categoriasObj = categoria_en_proceso;
            else if (tipoPadre === "Subproductos") categoriasObj = categoria_subproducto;
            else if (tipoPadre === "Productos Terminados") categoriasObj = categoria_productos_terminados;
            else if (tipoPadre === "Productos en Reproceso") categoriasObj = categoria_reproceso;
            else categoriasObj = categoria_producto;

            if (e.target.checked) {
                const idCat = `cat-${tipoPadre.replace(/\s/g, '-')}-${categoria.replace(/\s/g, '-')}`;
                if (!document.getElementById(idCat)) {
                    const div = document.createElement('div');
                    div.id = idCat;
                    div.innerHTML = `<h4>${categoria}</h4>` + crearCampos(categoriasObj[categoria]);
                    contenedorMini.appendChild(div);
                }
            } else {
                const div = document.getElementById(`cat-${tipoPadre.replace(/\s/g, '-')}-${categoria.replace(/\s/g, '-')}`);
                if (div) div.remove();
            }
        }
    });

    // ─── SUBMIT ───
    document.querySelector('form').addEventListener('submit', function(event) {
        event.preventDefault();
        const datos = {
            fecha_inspeccion: document.getElementById('fecha_inspeccion').value,
            hora_inspeccion: document.getElementById('hora_inspeccion').value,
            responsable: document.getElementById('nombre_usuario').value,
            sede: document.getElementById('sede_usuario').value,
            seccion1: {},
            seccion2: {},
            seccion3: {},
            seccion4: {},
            dinamicas: {}
        };
                datos.observaciones_comentarios = document.getElementById('observaciones_comentarios').value;

        datos.seccion1.tipos = [];
        document.querySelectorAll('#tipo-insumo-checklist input[type="checkbox"]:checked').forEach(cb => {
            datos.seccion1.tipos.push(cb.value);
        });

        ['rotulo_correspondiente','rotulo_condiciones','rotulo_legible','identificacion_correspondiente','identificacion_condiciones','identificacion_legible',
         'identifempaques_vencimiento','envasado_correspondiente','hilos_identificados','aditivos_etiquetados','mejorantes_etiquetados','empaques_identificados',
         'no_conformes_identificados','bodegas_libres','lineas_libres','contenedor_recoleccion'
        ].forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                if (id.startsWith('rotulo') || id.startsWith('identificacion')) datos.seccion2[id] = el.value;
                else if (id === 'identifempaques_vencimiento' || id.startsWith('envasado') || id.startsWith('hilos') || id.startsWith('aditivos') || id.startsWith('mejorantes') || id === 'empaques_identificados') datos.seccion3[id] = el.value;
                else datos.seccion4[id] = el.value;
            }
        });

        datos.dinamicas = {};
        document.querySelectorAll('.seccion-producto').forEach(seccion => {
            const tipo = seccion.querySelector('h3').textContent;
            datos.dinamicas[tipo] = {};
            seccion.querySelectorAll('.mini-categoria-checkbox:checked').forEach(catCb => {
                const categoria = catCb.value;
                datos.dinamicas[tipo][categoria] = {};
                const divCampos = seccion.querySelector(`#cat-${tipo.replace(/\s/g, '-')}-${categoria.replace(/\s/g, '-')}`);
                if (divCampos) {
                    divCampos.querySelectorAll('input, select, textarea').forEach(input => {
                        datos.dinamicas[tipo][categoria][input.name] = input.value;
                    });
                }
            });
        });

        fetch('guard_etiquetado.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(datos)
        })
        .then(res => res.json())
        .then(resp => showToast('✓ DATOS ENVIADOS CORRECTAMENTE'))
        .catch(err => showToast('⚠ ERROR AL ENVIAR DATOS'));
    });

    function showToast(msg) {
        const toast = document.getElementById('toast');
        toast.textContent = msg;
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 3500);
    }
</script>
</body>
</html>