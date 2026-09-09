<?php
require_once '../sesion.php';
verificarAutenticacion();
$responsable = $_SESSION['nombre'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PREMEZCLAS Y HARINAS ESPECIALES V2</title>
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
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 18px;
        }

        .form-group { display: flex; flex-direction: column; gap: 6px; }

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

        textarea.form-control { resize: vertical; min-height: 90px; }

        /* Bloques dinámicos: harinas especiales */
        .harina-block {
            background: rgba(255,255,255,0.02);
            border: 1px solid var(--border-color);
            border-radius: var(--r-sm);
            padding: 18px;
            margin-bottom: 14px;
            position: relative;
        }
        .harina-block .form-grid { grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 12px; }
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

        /* Tabla de insumos */
        .tabla-wrap { overflow-x: auto; }
        table.tabla-insumos { width: 100%; border-collapse: collapse; }
        table.tabla-insumos th {
            font-size: 10px;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 1px;
            font-family: 'Space Mono', monospace;
            text-align: left;
            padding: 8px 8px;
            border-bottom: 1px solid var(--border-color);
        }
        table.tabla-insumos td { padding: 6px 8px; }
        table.tabla-insumos .form-control { padding: 8px 10px; font-size: 13px; }
        table.tabla-insumos .col-accion { width: 40px; text-align: center; }

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
            margin-top: 8px;
        }
        .btn-add:hover { background: var(--accent); color: var(--bg-color); }

        .btn-remove-row {
            background: transparent;
            border: none;
            color: var(--danger);
            cursor: pointer;
            font-size: 16px;
        }

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

        /* Firma manuscrita */
        .firma-wrap { display: flex; flex-direction: column; gap: 10px; align-items: flex-start; }
        .firma-canvas {
            width: 100%;
            max-width: 500px;
            height: 200px;
            background: #fff;
            border-radius: var(--r-sm);
            border: 1px solid var(--border-color);
            cursor: crosshair;
            touch-action: none;
        }
        .firma-actions { display: flex; gap: 10px; }
        .btn-firma {
            background: var(--input-bg);
            border: 1px solid var(--border-color);
            color: var(--text-main);
            font-family: 'Space Mono', monospace;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            padding: 8px 16px;
            border-radius: var(--r-sm);
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-firma:hover { border-color: var(--accent); color: var(--accent); }
        .btn-firma.guardado { border-color: var(--success); color: var(--success); }
        .firma-status { font-size: 12px; color: var(--text-muted); font-family: 'Space Mono', monospace; }
    </style>
</head>
<body>
<div class="container">

    <div class="header-box">
        <div>
            <div class="main-title">Control de Producción — Premezclas y Harinas Especiales</div>
            <div class="sub-title">Responsable: <?= htmlspecialchars($responsable) ?></div>
        </div>
        <a href="rev_premezclas_v2.php" class="btn-back">← Galería</a>
    </div>

    <form id="formPremezclas">

        <!-- DATOS GENERALES -->
        <div class="section-card">
            <div class="section-title">// Datos Generales</div>
            <div class="form-grid">
                <div class="form-group">
                    <label for="fecha">Fecha</label>
                    <input type="date" id="fecha" class="form-control" required>
                </div>
            </div>
        </div>

        <!-- HARINAS ESPECIALES -->
        <div class="section-card">
            <div class="section-title">
                <span>// Harinas Especiales</span>
            </div>
            <div id="harinasContainer"></div>
            <button type="button" class="btn-add" id="btnAddHarina">➕ Agregar Harina</button>
        </div>

        <!-- INSUMOS -->
        <div class="section-card">
            <div class="section-title">// Insumos</div>
            <div class="tabla-wrap">
                <table class="tabla-insumos">
                    <thead>
                        <tr>
                            <th>Insumo</th>
                            <th>Lote</th>
                            <th>Cantidad</th>
                            <th class="col-accion"></th>
                        </tr>
                    </thead>
                    <tbody id="insumosBody"></tbody>
                </table>
            </div>
            <button type="button" class="btn-add" id="btnAddInsumo">➕ Agregar Insumo</button>
        </div>

        <!-- OBSERVACIONES -->
        <div class="section-card">
            <div class="section-title">// Observaciones</div>
            <div class="form-group">
                <textarea id="observaciones" class="form-control" placeholder="Ingrese observaciones relevantes..."></textarea>
            </div>
        </div>

        <!-- FIRMA -->
        <div class="section-card">
            <div class="section-title">// Firma del Turno</div>
            <div class="firma-wrap">
                <canvas id="canvasFirma" class="firma-canvas"></canvas>
                <div class="firma-actions">
                    <button type="button" class="btn-firma" id="btnLimpiarFirma">Limpiar</button>
                    <button type="button" class="btn-firma" id="btnGuardarFirma">Guardar Firma</button>
                </div>
                <span class="firma-status" id="firmaStatus">Sin firmar</span>
                <input type="hidden" id="firma_turno" required>
            </div>
        </div>

        <button type="submit" class="btn-submit" id="btnGuardar">GUARDAR REGISTRO</button>
    </form>

</div>

<script>
    const MAX_HARINAS = 6;
    const MAX_INSUMOS = 30;
    let harinaCount = 0;
    let insumoCount = 0;

    document.getElementById('fecha').value = new Date().toISOString().split('T')[0];

    function crearBloqueHarina() {
        harinaCount++;
        const div = document.createElement('div');
        div.className = 'harina-block';
        div.innerHTML = `
            <button type="button" class="btn-remove-block">✕ Quitar</button>
            <div class="form-grid">
                <div class="form-group">
                    <label>Producto</label>
                    <input type="text" class="form-control campo-producto">
                </div>
                <div class="form-group">
                    <label>Lote</label>
                    <input type="text" class="form-control campo-lote">
                </div>
                <div class="form-group">
                    <label>Cantidad</label>
                    <input type="number" step="0.01" class="form-control campo-cantidad">
                </div>
                <div class="form-group">
                    <label>Operario</label>
                    <input type="text" class="form-control campo-operario">
                </div>
                <div class="form-group">
                    <label>Hora Inicio</label>
                    <input type="time" class="form-control campo-hora-inicio">
                </div>
                <div class="form-group">
                    <label>Hora Final</label>
                    <input type="time" class="form-control campo-hora-final">
                </div>
            </div>
        `;
        div.querySelector('.btn-remove-block').addEventListener('click', () => {
            div.remove();
            harinaCount--;
        });
        return div;
    }

    document.getElementById('btnAddHarina').addEventListener('click', () => {
        if (harinaCount >= MAX_HARINAS) {
            Swal.fire({ title: 'Límite alcanzado', text: `Máximo ${MAX_HARINAS} harinas especiales.`, icon: 'warning', background: '#151A22', color: '#fff', confirmButtonColor: '#00F0FF' });
            return;
        }
        document.getElementById('harinasContainer').appendChild(crearBloqueHarina());
    });

    function crearFilaInsumo() {
        insumoCount++;
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><input type="text" class="form-control campo-insumo"></td>
            <td><input type="text" class="form-control campo-lote-insumo"></td>
            <td><input type="text" class="form-control campo-cantidad-insumo"></td>
            <td class="col-accion"><button type="button" class="btn-remove-row" title="Quitar">✕</button></td>
        `;
        tr.querySelector('.btn-remove-row').addEventListener('click', () => {
            tr.remove();
            insumoCount--;
        });
        return tr;
    }

    document.getElementById('btnAddInsumo').addEventListener('click', () => {
        if (insumoCount >= MAX_INSUMOS) {
            Swal.fire({ title: 'Límite alcanzado', text: `Máximo ${MAX_INSUMOS} insumos.`, icon: 'warning', background: '#151A22', color: '#fff', confirmButtonColor: '#00F0FF' });
            return;
        }
        document.getElementById('insumosBody').appendChild(crearFilaInsumo());
    });

    // Arrancar con 1 harina y 3 insumos, como el formulario original
    document.getElementById('harinasContainer').appendChild(crearBloqueHarina());
    for (let i = 0; i < 3; i++) document.getElementById('insumosBody').appendChild(crearFilaInsumo());

    // FIRMA MANUSCRITA (canvas)
    const canvasFirma = document.getElementById('canvasFirma');
    const ctxFirma = canvasFirma.getContext('2d');
    const firmaInput = document.getElementById('firma_turno');
    const firmaStatus = document.getElementById('firmaStatus');
    let dibujando = false;
    let firmaGuardada = false;

    function ajustarTamanoCanvas() {
        const rect = canvasFirma.getBoundingClientRect();
        canvasFirma.width = rect.width;
        canvasFirma.height = rect.height;
        ctxFirma.lineWidth = 2;
        ctxFirma.lineCap = 'round';
        ctxFirma.strokeStyle = '#000';
    }
    ajustarTamanoCanvas();
    window.addEventListener('resize', ajustarTamanoCanvas);

    function obtenerPosicion(e) {
        const rect = canvasFirma.getBoundingClientRect();
        const punto = e.touches ? e.touches[0] : e;
        return { x: punto.clientX - rect.left, y: punto.clientY - rect.top };
    }

    function iniciarDibujo(e) {
        dibujando = true;
        const pos = obtenerPosicion(e);
        ctxFirma.beginPath();
        ctxFirma.moveTo(pos.x, pos.y);
    }
    function dibujar(e) {
        if (!dibujando) return;
        e.preventDefault();
        const pos = obtenerPosicion(e);
        ctxFirma.lineTo(pos.x, pos.y);
        ctxFirma.stroke();
    }
    function detenerDibujo() {
        dibujando = false;
        ctxFirma.beginPath();
        marcarSinGuardar();
    }
    function esCanvasVacio() {
        const blanco = document.createElement('canvas');
        blanco.width = canvasFirma.width;
        blanco.height = canvasFirma.height;
        return canvasFirma.toDataURL() === blanco.toDataURL();
    }
    function marcarSinGuardar() {
        firmaGuardada = false;
        firmaInput.value = '';
        document.getElementById('btnGuardarFirma').classList.remove('guardado');
        firmaStatus.textContent = 'Firma sin guardar — pulsa "Guardar Firma"';
    }

    canvasFirma.addEventListener('mousedown', iniciarDibujo);
    canvasFirma.addEventListener('mousemove', dibujar);
    canvasFirma.addEventListener('mouseup', detenerDibujo);
    canvasFirma.addEventListener('mouseleave', () => { if (dibujando) detenerDibujo(); });
    canvasFirma.addEventListener('touchstart', iniciarDibujo);
    canvasFirma.addEventListener('touchmove', dibujar);
    canvasFirma.addEventListener('touchend', detenerDibujo);

    document.getElementById('btnLimpiarFirma').addEventListener('click', () => {
        ctxFirma.clearRect(0, 0, canvasFirma.width, canvasFirma.height);
        marcarSinGuardar();
        firmaStatus.textContent = 'Sin firmar';
    });

    document.getElementById('btnGuardarFirma').addEventListener('click', (e) => {
        if (esCanvasVacio()) {
            Swal.fire({ title: 'Firma vacía', text: 'Realice una firma antes de guardar.', icon: 'warning', background: '#151A22', color: '#fff', confirmButtonColor: '#00F0FF' });
            return;
        }
        firmaInput.value = canvasFirma.toDataURL('image/png');
        firmaGuardada = true;
        e.target.classList.add('guardado');
        firmaStatus.textContent = '✔ Firma guardada';
    });

    document.getElementById('formPremezclas').addEventListener('submit', async function (e) {
        e.preventDefault();

        if (!firmaGuardada || !firmaInput.value) {
            Swal.fire({ title: 'Firma requerida', text: 'Dibuje la firma y pulse "Guardar Firma" antes de enviar el registro.', icon: 'warning', background: '#151A22', color: '#fff', confirmButtonColor: '#00F0FF' });
            return;
        }

        const harinas_especiales = [...document.querySelectorAll('.harina-block')].map(b => ({
            producto: b.querySelector('.campo-producto').value.trim(),
            lote: b.querySelector('.campo-lote').value.trim(),
            cantidad: b.querySelector('.campo-cantidad').value.trim(),
            operario: b.querySelector('.campo-operario').value.trim(),
            hora_inicio: b.querySelector('.campo-hora-inicio').value,
            hora_final: b.querySelector('.campo-hora-final').value,
        })).filter(h => h.producto || h.lote || h.cantidad);

        const insumos = [...document.querySelectorAll('#insumosBody tr')].map(tr => ({
            insumo: tr.querySelector('.campo-insumo').value.trim(),
            lote: tr.querySelector('.campo-lote-insumo').value.trim(),
            cantidad: tr.querySelector('.campo-cantidad-insumo').value.trim(),
        })).filter(i => i.insumo || i.lote || i.cantidad);

        const jsonData = {
            fecha: document.getElementById('fecha').value,
            harinas_especiales,
            insumos,
            observaciones: document.getElementById('observaciones').value.trim(),
            firma: firmaInput.value,
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
                    title: '¡REGISTRADO!', text: 'Comprobación guardada correctamente.', icon: 'success',
                    background: '#151A22', color: '#fff', confirmButtonColor: '#00F0FF', confirmButtonText: 'Aceptar'
                }).then(() => window.location.href = 'rev_premezclas_v2.php');
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
