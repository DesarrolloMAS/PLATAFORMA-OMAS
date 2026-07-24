<?php
require_once '../sesion.php';
verificarAutenticacion();
$harina      = $_GET['harina']  ?? '';
$empaque     = $_GET['empaque'] ?? '';
$responsable = $_SESSION['nombre'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LÍNEA DE ENVASADO V2</title>
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

        .container { max-width: 900px; margin: 0 auto; }

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

        .product-badges { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 6px; }

        .product-badge {
            display: inline-block;
            background: rgba(0, 240, 255, 0.1);
            border: 1px solid rgba(0, 240, 255, 0.3);
            color: var(--accent);
            font-family: 'Space Mono', monospace;
            font-size: 11px;
            padding: 4px 12px;
            border-radius: var(--r-sm);
        }

        .empaque-badge {
            background: rgba(255, 176, 0, 0.1);
            border: 1px solid rgba(255, 176, 0, 0.3);
            color: var(--warning);
            font-family: 'Space Mono', monospace;
            font-size: 11px;
            padding: 4px 12px;
            border-radius: var(--r-sm);
        }

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

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 18px;
        }

        .form-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        @media (max-width: 600px) {
            .form-grid-2 { grid-template-columns: 1fr; }
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
        .form-control[readonly] {
            opacity: 0.6;
            cursor: not-allowed;
        }

        select.form-control option {
            background: var(--input-bg);
            color: var(--text-main);
        }

        .checks-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 14px;
        }

        .check-row {
            display: grid;
            grid-template-columns: 1fr 160px;
            gap: 16px;
            align-items: center;
            padding: 12px 16px;
            background: rgba(255,255,255,0.02);
            border: 1px solid var(--border-color);
            border-radius: var(--r-sm);
            transition: border-color 0.3s;
        }
        .check-row:hover { border-color: rgba(0, 240, 255, 0.2); }

        .check-label {
            font-size: 13px;
            color: var(--text-main);
            line-height: 1.4;
        }

        .check-select {
            background: var(--input-bg);
            border: 1px solid var(--border-color);
            color: var(--text-main);
            padding: 9px 12px;
            border-radius: var(--r-sm);
            font-family: 'Space Mono', monospace;
            font-size: 13px;
            font-weight: 700;
            text-align: center;
            transition: all 0.3s;
            width: 100%;
            cursor: pointer;
        }
        .check-select:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(0, 240, 255, 0.1);
        }
        .check-select.val-si    { border-color: var(--success); color: var(--success); }
        .check-select.val-no    { border-color: var(--danger);  color: var(--danger); }
        .check-select.val-na    { border-color: var(--text-muted); color: var(--text-muted); }

        textarea.form-control {
            resize: vertical;
            min-height: 90px;
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
        .btn-submit:hover {
            background: #fff;
            box-shadow: 0 0 30px rgba(255,255,255,0.4);
        }
        .btn-submit:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        @media (max-width: 600px) {
            .check-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="container">

    <!-- HEADER -->
    <div class="header-box">
        <div>
            <div class="main-title">Comprobaciones — Línea de Envasado</div>
            <div class="product-badges">
                <span class="product-badge" id="harinaBadge">Cargando...</span>
                <span class="empaque-badge" id="empaqueBadge"></span>
            </div>
        </div>
        <a href="geleria_productos.php" class="btn-back">← Galería</a>
    </div>

    <form id="formEnvasado">

        <!-- DATOS GENERALES -->
        <div class="section-card">
            <div class="section-title">// Datos Generales</div>
            <div class="form-grid">
                <div class="form-group">
                    <label for="fecha">Fecha</label>
                    <input type="date" id="fecha" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="hora">Hora</label>
                    <input type="time" id="hora" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="loteP">Lote de Producto</label>
                    <input type="text" id="loteP" class="form-control" placeholder="Ej: L-2026-001" required>
                </div>
                <div class="form-group">
                    <label for="fechaVencimiento">Fecha de Vencimiento</label>
                    <input type="text" id="fechaVencimiento" class="form-control" placeholder="Ej: 2027-01" required>
                </div>
                <div class="form-group">
                    <label for="responsable">Responsable</label>
                    <input type="text" id="responsable" class="form-control"
                           value="<?= htmlspecialchars($responsable) ?>" required>
                </div>
            </div>
        </div>

        <!-- ELEMENTOS A COMPROBAR -->
        <div class="section-card">
            <div class="section-title">// Elementos a Comprobar</div>
            <div class="checks-grid">

                <div class="check-row">
                    <span class="check-label">¿La línea de envasado ha sido purgada cuando se realizó el cambio de producto?</span>
                    <select id="purgada" class="check-select" onchange="colorSelect(this)">
                        <option value="">---</option>
                        <option value="SI">SI</option>
                        <option value="NO">NO</option>
                        <option value="N/A">N/A</option>
                    </select>
                </div>

                <div class="check-row">
                    <span class="check-label">¿La referencia del empaque que está en la línea de envasado corresponde a la del producto a envasar?</span>
                    <select id="Penvasado" class="check-select" onchange="colorSelect(this)">
                        <option value="">---</option>
                        <option value="SI">SI</option>
                        <option value="NO">NO</option>
                        <option value="N/A">N/A</option>
                    </select>
                </div>

                <div class="check-row">
                    <span class="check-label">¿El lote y fecha de vencimiento timbrado en los empaques es el correcto?</span>
                    <select id="timbrado" class="check-select" onchange="colorSelect(this)">
                        <option value="">---</option>
                        <option value="SI">SI</option>
                        <option value="NO">NO</option>
                        <option value="N/A">N/A</option>
                    </select>
                </div>

                <div class="check-row">
                    <span class="check-label">¿Si aplica etiqueta, los datos registrados coinciden con los timbrados en el empaque?</span>
                    <select id="etiqueta" class="check-select" onchange="colorSelect(this)">
                        <option value="">---</option>
                        <option value="SI">SI</option>
                        <option value="NO">NO</option>
                        <option value="N/A">N/A</option>
                    </select>
                </div>

                <div class="check-row">
                    <span class="check-label">¿Se aprueba el uso de los empaques recibidos en la línea de envasado?</span>
                    <select id="aprobacion" class="check-select" onchange="colorSelect(this)">
                        <option value="">---</option>
                        <option value="SI">SI</option>
                        <option value="NO">NO</option>
                        <option value="N/A">N/A</option>
                    </select>
                </div>

            </div>
        </div>

        <!-- OBSERVACIONES -->
        <div class="section-card">
            <div class="section-title">// Observaciones</div>
            <div class="form-group">
                <textarea id="observaciones" class="form-control"
                          placeholder="Ingrese observaciones relevantes..."></textarea>
            </div>
        </div>

        <button type="submit" class="btn-submit" id="btnGuardar">GUARDAR REGISTRO</button>
    </form>

</div>

<script>
    const params  = new URLSearchParams(window.location.search);
    const harina  = params.get('harina')  || '<?= htmlspecialchars($harina, ENT_QUOTES) ?>';
    const empaque = params.get('empaque') || '<?= htmlspecialchars($empaque, ENT_QUOTES) ?>';

    document.getElementById('harinaBadge').textContent  = harina  || 'Producto no especificado';
    document.getElementById('empaqueBadge').textContent = empaque || '';

    const now = new Date();
    document.getElementById('fecha').value = now.toISOString().split('T')[0];
    document.getElementById('hora').value  = now.toTimeString().slice(0, 5);

    function colorSelect(sel) {
        sel.classList.remove('val-si', 'val-no', 'val-na');
        if (sel.value === 'SI')  sel.classList.add('val-si');
        if (sel.value === 'NO')  sel.classList.add('val-no');
        if (sel.value === 'N/A') sel.classList.add('val-na');
    }

    document.getElementById('formEnvasado').addEventListener('submit', async function(e) {
        e.preventDefault();

        const jsonData = {
            harina:           harina,
            empaque:          empaque,
            fecha:            document.getElementById('fecha').value,
            hora:             document.getElementById('hora').value,
            loteP:            document.getElementById('loteP').value.trim(),
            fechaVencimiento: document.getElementById('fechaVencimiento').value.trim(),
            responsable:      document.getElementById('responsable').value.trim(),
            purgada:          document.getElementById('purgada').value,
            Penvasado:        document.getElementById('Penvasado').value,
            timbrado:         document.getElementById('timbrado').value,
            etiqueta:         document.getElementById('etiqueta').value,
            aprobacion:       document.getElementById('aprobacion').value,
            observaciones:    document.getElementById('observaciones').value.trim()
        };

        const btn = document.getElementById('btnGuardar');
        btn.disabled = true;
        btn.textContent = 'GUARDANDO...';

        try {
            const res  = await fetch('procesar.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(jsonData)
            });
            const data = await res.json();

            if (data.status === 'success') {
                Swal.fire({
                    title: '¡REGISTRADO!',
                    text: 'Comprobación guardada correctamente.',
                    icon: 'success',
                    background: '#151A22',
                    color: '#fff',
                    confirmButtonColor: '#00F0FF',
                    confirmButtonText: 'Aceptar'
                }).then(() => window.location.href = 'geleria_productos.php');
            } else {
                throw new Error(data.message || 'Error desconocido.');
            }
        } catch (err) {
            Swal.fire({
                title: 'ERROR',
                text: err.message,
                icon: 'error',
                background: '#151A22',
                color: '#fff',
                confirmButtonColor: '#FF3366'
            });
            btn.disabled = false;
            btn.textContent = 'GUARDAR REGISTRO';
        }
    });
</script>
</body>
</html>
