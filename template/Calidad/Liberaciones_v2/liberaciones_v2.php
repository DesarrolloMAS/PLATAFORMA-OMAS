<?php
require_once '../../sesion.php';
verificarAutenticacion();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REGISTRO DE LIBERACIONES</title>
    <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700&family=Space+Mono:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --bg-color: #0B0E14;
            --panel-bg: #151A22;
            --accent: #00F0FF;
            --accent-glow: rgba(0, 240, 255, 0.4);
            --accent-hover: #00D1DF;
            --text-main: #E2E8F0;
            --text-muted: #94A3B8;
            --border-color: #1E293B;
            --input-bg: #0F172A;
            --danger: #FF3366;
            --warning: #FFB000;
            --success: #10B981;
            --r-lg: 12px;
            --r-md: 8px;
            --r-sm: 4px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Barlow', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            line-height: 1.5;
            min-height: 100vh;
            padding: 40px 20px;
            background-image:
                linear-gradient(rgba(0, 240, 255, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0, 240, 255, 0.03) 1px, transparent 1px);
            background-size: 30px 30px;
        }

        .container { max-width: 1000px; margin: 0 auto; }

        .header-box {
            background: var(--panel-bg);
            border: 1px solid var(--border-color);
            border-left: 4px solid var(--accent);
            padding: 28px 30px;
            border-radius: var(--r-md);
            margin-bottom: 28px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            position: relative;
            overflow: hidden;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-box::before {
            content: "V2 JSON";
            position: absolute;
            top: -10px; right: 20px;
            background: var(--accent);
            color: var(--bg-color);
            font-family: 'Space Mono', monospace;
            font-size: 10px; font-weight: 700;
            padding: 4px 12px;
            border-radius: var(--r-sm);
            box-shadow: 0 0 10px var(--accent-glow);
        }

        .main-title {
            font-size: 22px;
            font-weight: 700;
            color: #fff;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }

        .sub-title { color: var(--text-muted); font-size: 13px; font-family: 'Space Mono', monospace; }

        .header-actions { display: flex; gap: 10px; flex-shrink: 0; }

        .btn-back {
            background: var(--input-bg);
            border: 1px solid var(--border-color);
            color: var(--text-main);
            padding: 10px 20px;
            border-radius: var(--r-sm);
            font-family: 'Space Mono', monospace;
            text-decoration: none;
            font-size: 12px;
            transition: all 0.3s;
            white-space: nowrap;
        }
        .btn-back:hover {
            border-color: var(--accent);
            color: var(--accent);
            background: rgba(0, 240, 255, 0.05);
        }

        .section-card {
            background: var(--panel-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--r-md);
            padding: 28px;
            margin-bottom: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }

        .section-title {
            font-size: 11px;
            font-weight: 700;
            color: var(--accent);
            text-transform: uppercase;
            letter-spacing: 2px;
            font-family: 'Space Mono', monospace;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-color);
        }

        .harina-toggle {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            cursor: pointer;
            text-transform: none;
            letter-spacing: normal;
            width: 100%;
        }
        .harina-toggle-left { display: flex; align-items: center; gap: 10px; }
        .harina-toggle input[type="checkbox"] {
            width: 18px; height: 18px; accent-color: var(--accent); cursor: pointer;
        }
        .harina-toggle span { font-size: 13px; }
        .harina-chevron {
            color: var(--text-muted);
            transition: transform 0.3s ease, color 0.3s ease;
            flex-shrink: 0;
        }

        /* Estado activo: replica la señal visual del formato anterior,
           donde la barra de la harina seleccionada quedaba resaltada. */
        .section-card.harina-activa {
            border-color: rgba(0, 240, 255, 0.35);
            box-shadow: 0 4px 20px rgba(0,0,0,0.3), 0 0 0 1px rgba(0, 240, 255, 0.15) inset;
        }
        .section-card.harina-activa .section-title {
            color: #fff;
            border-bottom-color: rgba(0, 240, 255, 0.3);
        }
        .section-card.harina-activa .harina-chevron {
            color: var(--accent);
            transform: rotate(180deg);
        }

        /* ── BARRA DE DESPLIEGUE ──
           Reinvención de la barra lateral fija "LIBERACIONES" del formato
           original: aquí es una barra horizontal, ubicada justo después de
           la fecha, que despliega/colapsa todo el registro con una
           animación fluida basada en grid-template-rows (0fr → 1fr). */
        .despliegue-bar {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            background: var(--panel-bg);
            border: 1px solid var(--border-color);
            border-left: 4px solid var(--accent);
            border-radius: var(--r-md);
            padding: 20px 26px;
            margin-bottom: 20px;
            cursor: pointer;
            font-family: 'Barlow', sans-serif;
            color: var(--text-main);
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            transition: border-color 0.3s ease, box-shadow 0.3s ease, transform 0.2s ease;
            animation: despliegue-pulse 2.6s ease-in-out infinite;
        }
        .despliegue-bar:hover {
            border-color: var(--accent);
            box-shadow: 0 4px 20px rgba(0,0,0,0.3), 0 0 25px var(--accent-glow);
            transform: translateY(-1px);
        }
        .despliegue-bar.abierto {
            animation: none;
            border-color: var(--accent);
            box-shadow: 0 4px 20px rgba(0,0,0,0.3), 0 0 0 1px rgba(0, 240, 255, 0.15) inset;
        }

        @keyframes despliegue-pulse {
            0%, 100% { border-left-color: var(--accent); }
            50% { border-left-color: var(--accent-hover); }
        }

        .despliegue-bar-left { display: flex; align-items: center; gap: 12px; }
        .despliegue-bar-right { display: flex; align-items: center; gap: 12px; }

        .despliegue-dot {
            width: 9px; height: 9px; border-radius: 50%;
            background: var(--accent);
            box-shadow: 0 0 8px var(--accent-glow);
            animation: despliegue-dot-blink 1.8s ease-in-out infinite;
            flex-shrink: 0;
        }
        @keyframes despliegue-dot-blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.35; }
        }

        .despliegue-titulo {
            font-family: 'Space Mono', monospace;
            font-weight: 700;
            font-size: 15px;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: #fff;
        }

        .despliegue-contador {
            font-family: 'Space Mono', monospace;
            font-size: 10px;
            color: var(--text-muted);
            background: rgba(0, 240, 255, 0.08);
            border: 1px solid rgba(0, 240, 255, 0.2);
            padding: 3px 10px;
            border-radius: 20px;
            white-space: nowrap;
        }

        .despliegue-hint {
            font-family: 'Space Mono', monospace;
            font-size: 11px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: none;
        }
        @media (min-width: 640px) {
            .despliegue-hint { display: inline; }
        }

        .despliegue-chevron {
            color: var(--accent);
            transition: transform 0.4s cubic-bezier(0.65, 0, 0.35, 1);
            flex-shrink: 0;
        }
        .despliegue-bar.abierto .despliegue-chevron { transform: rotate(180deg); }

        .despliegue-wrap {
            display: grid;
            grid-template-rows: 0fr;
            opacity: 0;
            transition: grid-template-rows 0.6s cubic-bezier(0.65, 0, 0.35, 1), opacity 0.5s ease 0.05s;
            pointer-events: none;
        }
        .despliegue-wrap.abierto {
            grid-template-rows: 1fr;
            opacity: 1;
            pointer-events: auto;
        }
        .despliegue-inner { overflow: hidden; min-height: 0; }
        .despliegue-inner > * {
            transform: translateY(-14px);
            opacity: 0;
            transition: transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) 0.15s, opacity 0.4s ease 0.15s;
        }
        .despliegue-wrap.abierto .despliegue-inner > * {
            transform: translateY(0);
            opacity: 1;
        }

        .cumplimiento-heading {
            font-size: 10px;
            font-weight: 700;
            color: var(--accent);
            text-transform: uppercase;
            letter-spacing: 2px;
            font-family: 'Space Mono', monospace;
            margin-top: 18px;
            padding-top: 14px;
            border-top: 1px dashed var(--border-color);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 18px;
        }

        .cumplimiento-grid {
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            margin-top: 14px;
        }

        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-group.hidden { display: none; }

        .form-group label {
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 1px;
            font-family: 'Space Mono', monospace;
        }

        .form-control {
            background: var(--input-bg);
            border: 1px solid var(--border-color);
            color: var(--text-main);
            padding: 11px 14px;
            border-radius: var(--r-sm);
            font-family: 'Barlow', sans-serif;
            font-size: 14px;
            transition: all 0.3s;
            width: 100%;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(0, 240, 255, 0.1);
        }

        select.form-control option { background: var(--input-bg); color: var(--text-main); }

        .lotes-container { display: none; flex-direction: column; gap: 14px; margin-bottom: 14px; }

        .lote-block {
            background: rgba(255,255,255,0.02);
            border: 1px solid var(--border-color);
            border-radius: var(--r-sm);
            padding: 18px;
            position: relative;
        }

        .btn-remove-block {
            position: absolute;
            top: 10px; right: 10px;
            background: rgba(255, 51, 102, 0.1);
            border: 1px solid rgba(255, 51, 102, 0.3);
            color: var(--danger);
            font-family: 'Space Mono', monospace;
            font-size: 11px;
            padding: 5px 10px;
            border-radius: var(--r-sm);
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-remove-block:hover { background: var(--danger); color: #fff; }

        .btn-add {
            background: rgba(0, 240, 255, 0.1);
            border: 1px solid rgba(0, 240, 255, 0.3);
            color: var(--accent);
            font-family: 'Space Mono', monospace;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            padding: 8px 16px;
            border-radius: var(--r-sm);
            cursor: pointer;
            transition: all 0.3s;
            display: none;
        }
        .btn-add:hover { background: var(--accent); color: var(--bg-color); }
        .btn-add.visible { display: inline-block; }

        .btn-submit {
            width: 100%;
            padding: 16px;
            background: var(--accent);
            color: var(--bg-color);
            border: none;
            border-radius: var(--r-sm);
            font-family: 'Space Mono', monospace;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 0 20px var(--accent-glow);
            margin-top: 10px;
        }
        .btn-submit:hover { background: #fff; box-shadow: 0 0 30px rgba(255,255,255,0.4); }
        .btn-submit:disabled { opacity: 0.5; cursor: not-allowed; }
    </style>
</head>
<body>
<div class="container">

    <div class="header-box">
        <div>
            <div class="main-title">Registro de Liberaciones</div>
            <div class="sub-title">Sede: <?= htmlspecialchars($_SESSION['sede'] ?? '') ?></div>
        </div>
        <div class="header-actions">
            <a href="../../menu_adm_calidad.html" class="btn-back">← Volver</a>
            <a href="rev_liberaciones_v2.php" class="btn-back">← Galería</a>
        </div>
    </div>

    <form id="formLiberaciones">

        <div class="section-card">
            <div class="section-title">// Datos Generales</div>
            <div class="form-grid">
                <div class="form-group">
                    <label for="fecha_produccion">Fecha de Producción</label>
                    <input type="date" id="fecha_produccion" class="form-control" required>
                </div>
            </div>
        </div>

        <button type="button" class="despliegue-bar" id="btnDespliegue" aria-expanded="false" aria-controls="despliegueWrap">
            <span class="despliegue-bar-left">
                <span class="despliegue-dot"></span>
                <span class="despliegue-titulo">Liberaciones</span>
                <span class="despliegue-contador" id="despliegueContador">0 harinas activas</span>
            </span>
            <span class="despliegue-bar-right">
                <span class="despliegue-hint">Toca para desplegar el registro</span>
                <svg class="despliegue-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
            </span>
        </button>

        <div class="despliegue-wrap" id="despliegueWrap">
            <div class="despliegue-inner">

                <div class="section-card">
                    <div class="section-title">// Harinas para Liberar</div>
                    <div id="harinasWrap"></div>
                </div>

                <div class="section-card">
                    <div class="section-title">// Harinas Extra (no listadas arriba)</div>
                    <div id="extraHarinasContainer"></div>
                    <button type="button" class="btn-add visible" id="btnAddExtra">➕ Agregar Harina Extra</button>
                </div>

                <button type="submit" class="btn-submit" id="btnGuardar">GUARDAR REGISTRO</button>
            </div>
        </div>
    </form>

</div>

<script>
    const MAX_LOTES_POR_HARINA = 5;
    const MAX_HARINAS_EXTRA = 5;

    const HARINAS = [
        { id: 'extrapan',  nombre: 'Harina Extrapan' },
        { id: 'proteina',  nombre: 'Harina Alta Proteína' },
        { id: 'artesanal', nombre: 'Harina Artesanal' },
        { id: 'exclusiva', nombre: 'Harina Exclusiva' },
        { id: 'integral',  nombre: 'Harina Integral' },
        { id: 'fuerte',    nombre: 'Harina Fuerte' },
        { id: 'especial',  nombre: 'Harina Especial' },
        { id: 'mogolla',   nombre: 'Mogolla' },
        { id: 'salvado',   nombre: 'Salvado' },
        { id: 'segunda',   nombre: 'Segunda' },
        { id: 'germen',    nombre: 'Germen' },
    ];
    const REF_OPTIONS = ['50 KG', '25 KG', '10 KG', 'Granel'];

    const CAMPOS_CUMPLIMIENTO = [
        { key: 'bitacora',               label: 'Cumple con especificaciones de FT o Bitácora de análisis' },
        { key: 'panificacion',           label: '¿Cumple los análisis de panificación?' },
        { key: 'laboratorios_externos',  label: '¿Cumple los análisis de laboratorios externos?' },
        { key: 'fortificacion',          label: '¿Cumple la adición de fortificación?' },
        { key: 'mejorantes',             label: '¿Cumple con adición de mejorantes?' },
        { key: 'empaque',                label: '¿Cumple las condiciones y empaque rotulado?' },
        { key: 'certificado',            label: '¿Cumple el certificado de calidad?' },
    ];

    document.getElementById('fecha_produccion').value = new Date().toISOString().split('T')[0];

    // Barra de despliegue: reemplaza el checkbox #toggle_harinas del formato
    // original. Un click expande/colapsa con animación fluida (grid-rows).
    const btnDespliegue = document.getElementById('btnDespliegue');
    const despliegueWrap = document.getElementById('despliegueWrap');
    btnDespliegue.addEventListener('click', () => {
        const abierto = !despliegueWrap.classList.contains('abierto');
        despliegueWrap.classList.toggle('abierto', abierto);
        btnDespliegue.classList.toggle('abierto', abierto);
        btnDespliegue.setAttribute('aria-expanded', abierto ? 'true' : 'false');
        if (abierto) {
            setTimeout(() => despliegueWrap.scrollIntoView({ behavior: 'smooth', block: 'nearest' }), 150);
        }
    });

    function actualizarContadorHarinas() {
        const activas = document.querySelectorAll('.chk-harina:checked').length;
        document.getElementById('despliegueContador').textContent =
            `${activas} harina${activas === 1 ? '' : 's'} activa${activas === 1 ? '' : 's'}`;
    }

    function cumplimientoHtml() {
        return CAMPOS_CUMPLIMIENTO.map(c => `
            <div class="form-group">
                <label>${c.label}</label>
                <select class="form-control campo-${c.key}">
                    <option value="">---</option>
                    <option value="N/A">No Aplica</option>
                    <option value="Cumple">Cumple</option>
                    <option value="No Cumple">No Cumple</option>
                </select>
            </div>`).join('');
    }

    function crearBloqueLote() {
        const div = document.createElement('div');
        div.className = 'lote-block';
        const refOptionsHtml = REF_OPTIONS.map(r => `<option value="${r}">${r}</option>`).join('')
            + `<option value="custom">Otro (Especificar)</option>`;
        div.innerHTML = `
            <button type="button" class="btn-remove-block">✕ Quitar Lote</button>
            <div class="form-grid">
                <div class="form-group">
                    <label>Referencia</label>
                    <select class="form-control campo-referencia">${refOptionsHtml}</select>
                </div>
                <div class="form-group campo-referencia-custom-wrap hidden">
                    <label>Especifique Referencia</label>
                    <input type="text" class="form-control campo-referencia-custom">
                </div>
                <div class="form-group">
                    <label>Lote</label>
                    <input type="text" class="form-control campo-lote">
                </div>
                <div class="form-group">
                    <label>Cantidad Liberada</label>
                    <input type="text" class="form-control campo-cantidad">
                </div>
            </div>
            <div class="cumplimiento-heading">// Área de Cumplimiento</div>
            <div class="form-grid cumplimiento-grid">${cumplimientoHtml()}</div>
        `;
        div.querySelector('.btn-remove-block').addEventListener('click', () => div.remove());
        const refSelect = div.querySelector('.campo-referencia');
        const customWrap = div.querySelector('.campo-referencia-custom-wrap');
        refSelect.addEventListener('change', () => {
            customWrap.classList.toggle('hidden', refSelect.value !== 'custom');
        });
        return div;
    }

    function crearBloqueExtra() {
        const div = crearBloqueLote();
        div.classList.add('extra-harina-block');
        const nombreGroup = document.createElement('div');
        nombreGroup.className = 'form-group';
        nombreGroup.innerHTML = `<label>Nombre de la Harina</label><input type="text" class="form-control campo-nombre-harina">`;
        div.querySelector('.form-grid').prepend(nombreGroup);
        return div;
    }

    function leerLote(block) {
        const refSelect = block.querySelector('.campo-referencia');
        let referencia = refSelect.value;
        if (referencia === 'custom') {
            referencia = block.querySelector('.campo-referencia-custom').value.trim();
        }
        const lote = {
            referencia,
            lote: block.querySelector('.campo-lote').value.trim(),
            cantidad: block.querySelector('.campo-cantidad').value.trim(),
        };
        CAMPOS_CUMPLIMIENTO.forEach(c => {
            lote[c.key] = block.querySelector(`.campo-${c.key}`).value;
        });
        return lote;
    }

    // Renderiza las 11 tarjetas de harinas fijas, cada una con checkbox de
    // activación (igual UX que el formato original) y su propio contenedor
    // de lotes con límite MAX_LOTES_POR_HARINA.
    const harinasWrap = document.getElementById('harinasWrap');
    HARINAS.forEach(h => {
        const card = document.createElement('div');
        card.className = 'section-card';
        card.innerHTML = `
            <div class="section-title">
                <label class="harina-toggle">
                    <span class="harina-toggle-left">
                        <input type="checkbox" class="chk-harina" id="chk_${h.id}">
                        <span>${h.nombre}</span>
                    </span>
                    <svg class="harina-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                </label>
            </div>
            <div class="lotes-container" id="lotes_${h.id}"></div>
            <button type="button" class="btn-add btn-add-lote">➕ Agregar Lote</button>
        `;
        harinasWrap.appendChild(card);

        const chk = card.querySelector('.chk-harina');
        const lotesContainer = card.querySelector(`#lotes_${h.id}`);
        const btnAdd = card.querySelector('.btn-add-lote');

        chk.addEventListener('change', () => {
            const activo = chk.checked;
            lotesContainer.style.display = activo ? 'flex' : 'none';
            btnAdd.classList.toggle('visible', activo);
            card.classList.toggle('harina-activa', activo);
            actualizarContadorHarinas();
            if (activo && lotesContainer.children.length === 0) {
                lotesContainer.appendChild(crearBloqueLote());
            }
        });

        btnAdd.addEventListener('click', () => {
            if (lotesContainer.children.length >= MAX_LOTES_POR_HARINA) {
                Swal.fire({ title: 'Límite alcanzado', text: `Máximo ${MAX_LOTES_POR_HARINA} lotes por harina.`, icon: 'warning', background: '#151A22', color: '#fff', confirmButtonColor: '#00F0FF' });
                return;
            }
            lotesContainer.appendChild(crearBloqueLote());
        });
    });

    document.getElementById('btnAddExtra').addEventListener('click', () => {
        if (document.querySelectorAll('.extra-harina-block').length >= MAX_HARINAS_EXTRA) {
            Swal.fire({ title: 'Límite alcanzado', text: `Máximo ${MAX_HARINAS_EXTRA} harinas extra.`, icon: 'warning', background: '#151A22', color: '#fff', confirmButtonColor: '#00F0FF' });
            return;
        }
        document.getElementById('extraHarinasContainer').appendChild(crearBloqueExtra());
    });

    document.getElementById('formLiberaciones').addEventListener('submit', async function (e) {
        e.preventDefault();

        const harinas = [];
        HARINAS.forEach(h => {
            const chk = document.getElementById(`chk_${h.id}`);
            if (!chk.checked) return;
            const lotesContainer = document.getElementById(`lotes_${h.id}`);
            const lotes = [...lotesContainer.querySelectorAll('.lote-block')]
                .map(leerLote)
                .filter(l => l.referencia || l.lote || l.cantidad);
            if (lotes.length) harinas.push({ harina_id: h.id, harina_nombre: h.nombre, lotes });
        });

        const harinas_extra = [...document.querySelectorAll('.extra-harina-block')].map(b => {
            const l = leerLote(b);
            l.nombre_harina = b.querySelector('.campo-nombre-harina').value.trim();
            return l;
        }).filter(l => l.nombre_harina || l.referencia || l.lote || l.cantidad);

        if (harinas.length === 0 && harinas_extra.length === 0) {
            Swal.fire({ title: 'Sin datos', text: 'Active y complete al menos una harina antes de guardar.', icon: 'warning', background: '#151A22', color: '#fff', confirmButtonColor: '#00F0FF' });
            return;
        }

        const jsonData = {
            fecha_produccion: document.getElementById('fecha_produccion').value,
            harinas,
            harinas_extra,
        };

        const btn = document.getElementById('btnGuardar');
        btn.disabled = true;
        btn.textContent = 'GUARDANDO...';

        try {
            const res = await fetch('procesar.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(jsonData)
            });
            const data = await res.json();

            if (data.status === 'success') {
                Swal.fire({
                    title: '¡REGISTRADO!', text: 'Liberación guardada correctamente.', icon: 'success',
                    background: '#151A22', color: '#fff', confirmButtonColor: '#00F0FF', confirmButtonText: 'Aceptar'
                }).then(() => window.location.href = 'rev_liberaciones_v2.php');
            } else {
                throw new Error(data.message || 'Error desconocido.');
            }
        } catch (err) {
            Swal.fire({ title: 'ERROR', text: err.message, icon: 'error', background: '#151A22', color: '#fff', confirmButtonColor: '#FF3366' });
            btn.disabled = false;
            btn.textContent = 'GUARDAR REGISTRO';
        }
    });
</script>
</body>
</html>
