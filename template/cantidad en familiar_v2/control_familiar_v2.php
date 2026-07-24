<?php
require_once '../sesion.php';
verificarAutenticacion();
$harina      = $_GET['harina'] ?? '';
$responsable = $_SESSION['nombre'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CONTROL DE CANTIDAD / PRODUCTO FAMILIAR</title>
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
            --danger-glow: rgba(255, 51, 102, 0.4);
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

        .product-badge {
            display: inline-block;
            background: rgba(0, 240, 255, 0.1);
            border: 1px solid rgba(0, 240, 255, 0.3);
            color: var(--accent);
            font-family: 'Space Mono', monospace;
            font-size: 12px;
            padding: 4px 12px;
            border-radius: var(--r-sm);
            margin-top: 6px;
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

        .bultos-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 14px;
        }

        @media (max-width: 600px) {
            .bultos-grid { grid-template-columns: repeat(2, 1fr); }
        }

        .bulto-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .bulto-group label {
            font-size: 10px;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 1px;
            font-family: 'Space Mono', monospace;
        }
        .bulto-input {
            background: var(--input-bg);
            border: 1px solid var(--border-color);
            color: var(--text-main);
            padding: 11px 10px;
            border-radius: var(--r-sm);
            font-family: 'Space Mono', monospace;
            font-size: 14px;
            text-align: center;
            transition: all 0.3s;
            width: 100%;
        }
        .bulto-input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(0, 240, 255, 0.1);
        }

        .promedio-display {
            background: rgba(0, 240, 255, 0.05);
            border: 1px solid rgba(0, 240, 255, 0.2);
            border-radius: var(--r-sm);
            padding: 14px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 18px;
        }
        .promedio-label {
            font-family: 'Space Mono', monospace;
            font-size: 11px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .promedio-value {
            font-family: 'Space Mono', monospace;
            font-size: 20px;
            font-weight: 700;
            color: var(--accent);
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
    </style>
</head>
<body>
<div class="container">

    <!-- HEADER -->
    <div class="header-box">
        <div>
            <div class="main-title">Control de Cantidad / Producto Familiar</div>
            <span class="product-badge" id="productoBadge">Cargando producto...</span>
        </div>
        <a href="geleria_productos_familiar_v2.php" class="btn-back">← Galería de Productos</a>
    </div>

    <form id="formFamiliar">
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
                    <label for="lote">Lote</label>
                    <input type="text" id="lote" class="form-control" placeholder="Ej: L-2026-01">
                </div>
                <div class="form-group">
                    <label for="responsable">Responsable</label>
                    <input type="text" id="responsable" class="form-control"
                           value="<?= htmlspecialchars($responsable) ?>" readonly>
                </div>
            </div>
        </div>

        <!-- MUESTREO DE BULTOS -->
        <div class="section-card">
            <div class="section-title">// Muestreo de Producto</div>
            <div class="bultos-grid">
                <?php for ($i = 1; $i <= 10; $i++): ?>
                <div class="bulto-group">
                    <label for="bulto_<?= $i ?>">Bulto <?= $i ?></label>
                    <input type="number" step="any" id="bulto_<?= $i ?>"
                           class="bulto-input" placeholder="0.00">
                </div>
                <?php endfor; ?>
            </div>

            <div class="promedio-display">
                <span class="promedio-label">Promedio de Bultos</span>
                <span class="promedio-value" id="promedioVal">— kg</span>
            </div>
        </div>

        <button type="submit" class="btn-submit" id="btnGuardar">GUARDAR REGISTRO</button>
    </form>

</div>

<script>
    const params = new URLSearchParams(window.location.search);
    const harina = params.get('harina') || '<?= htmlspecialchars($harina, ENT_QUOTES) ?>';

    document.getElementById('productoBadge').textContent = harina || 'Producto no especificado';

    const now = new Date();
    document.getElementById('fecha').value = now.toISOString().split('T')[0];
    document.getElementById('hora').value  = now.toTimeString().slice(0, 5);

    function calcularPromedio() {
        const vals = [];
        for (let i = 1; i <= 10; i++) {
            const v = parseFloat(document.getElementById('bulto_' + i).value);
            if (!isNaN(v)) vals.push(v);
        }
        const el = document.getElementById('promedioVal');
        if (vals.length === 0) { el.textContent = '— kg'; return; }
        const prom = vals.reduce((a, b) => a + b, 0) / vals.length;
        el.textContent = prom.toFixed(3) + ' kg';
    }

    for (let i = 1; i <= 10; i++) {
        document.getElementById('bulto_' + i).addEventListener('input', calcularPromedio);
    }

    document.getElementById('formFamiliar').addEventListener('submit', async function(e) {
        e.preventDefault();

        const bultos = {};
        for (let i = 1; i <= 10; i++) {
            const val = document.getElementById('bulto_' + i).value.trim();
            bultos['bulto_' + i] = val !== '' ? val : null;
        }

        const jsonData = {
            harina:      harina,
            fecha:       document.getElementById('fecha').value,
            hora:        document.getElementById('hora').value,
            lote:        document.getElementById('lote').value.trim(),
            responsable: document.getElementById('responsable').value.trim(),
            ...bultos
        };

        const btn = document.getElementById('btnGuardar');
        btn.disabled = true;
        btn.textContent = 'GUARDANDO...';

        try {
            const res  = await fetch('procesar_familiar_v2.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(jsonData)
            });
            const data = await res.json();

            if (data.status === 'success') {
                Swal.fire({
                    title: '¡REGISTRADO!',
                    text: 'Registro guardado correctamente.',
                    icon: 'success',
                    background: '#151A22',
                    color: '#fff',
                    confirmButtonColor: '#00F0FF',
                    confirmButtonText: 'Aceptar'
                }).then(() => window.location.href = 'geleria_productos_familiar_v2.php');
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
