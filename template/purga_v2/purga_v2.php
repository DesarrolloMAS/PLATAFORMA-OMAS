<?php
require '../conection.php';
require_once '../sesion.php';
verificarAutenticacion();

$filtro_cargo = 'Lider de Turno';
$query = $pdoUsuarios->prepare("SELECT id_usuario, nombre_u FROM usuarios WHERE Cargo = :filtro_cargo");
$query->bindParam(':filtro_cargo', $filtro_cargo);
$query->execute();
$usuarios = $query->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PURGA DE PROCESO V2</title>
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

        select.form-control option { background: var(--input-bg); color: var(--text-main); }

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
            <div class="main-title">Purga de Proceso</div>
            <div class="sub-title">Sede: <?= htmlspecialchars($_SESSION['sede'] ?? '') ?></div>
        </div>
        <a href="rev_purga_v2.php" class="btn-back">← Galería</a>
    </div>

    <form id="formPurga">

        <!-- CAMBIO DE SILO -->
        <div class="section-card">
            <div class="section-title">// Cambio de Silo</div>
            <div class="form-grid">
                <div class="form-group">
                    <label for="fecha_produccion">Fecha de Producción</label>
                    <input type="date" id="fecha_produccion" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="referencia_producto">Referencia de Producto</label>
                    <input type="text" id="referencia_producto" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="hora_inicial">Hora Inicial</label>
                    <input type="time" id="hora_inicial" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="cantidad">Cantidad (KG)</label>
                    <input type="number" step="0.01" id="cantidad" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="responsable_cambio">Responsable de la Purga</label>
                    <select id="responsable_cambio" class="form-control" required>
                        <option value="">Seleccione un Responsable</option>
                        <?php foreach ($usuarios as $usuario): ?>
                            <option value="<?= htmlspecialchars($usuario['nombre_u'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($usuario['nombre_u'], ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                        <option value="Ningún Usuario Disponible">Ningún Usuario Disponible</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- REGRESO A LÍNEA DE PRODUCCIÓN -->
        <div class="section-card">
            <div class="section-title">// Regreso a Línea de Producción</div>
            <div class="form-grid">
                <div class="form-group">
                    <label for="fecha_ingreso">Fecha de Ingreso a la Tolva</label>
                    <input type="date" id="fecha_ingreso" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="referencia_producto_linea">Referencia de Producto de Regreso a Línea</label>
                    <input type="text" id="referencia_producto_linea" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="hora_ingreso">Hora Inicial</label>
                    <input type="time" id="hora_ingreso" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="hora_final">Hora Final</label>
                    <input type="time" id="hora_final" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="cantidad_line">Cantidad Total (KG)</label>
                    <input type="number" step="0.01" id="cantidad_line" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="responsable_linea">Responsable de la Purga</label>
                    <select id="responsable_linea" class="form-control" required>
                        <option value="">Seleccione un Responsable</option>
                        <?php foreach ($usuarios as $usuario): ?>
                            <option value="<?= htmlspecialchars($usuario['nombre_u'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($usuario['nombre_u'], ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                        <option value="Ningún Usuario Disponible">Ningún Usuario Disponible</option>
                    </select>
                </div>
            </div>
        </div>

        <button type="submit" class="btn-submit" id="btnGuardar">GUARDAR REGISTRO</button>
    </form>

</div>

<script>
    const now = new Date();
    const hoy = now.toISOString().split('T')[0];
    document.getElementById('fecha_produccion').value = hoy;
    document.getElementById('fecha_ingreso').value = hoy;

    document.getElementById('formPurga').addEventListener('submit', async function (e) {
        e.preventDefault();

        const jsonData = {
            fecha_produccion: document.getElementById('fecha_produccion').value,
            referencia_producto: document.getElementById('referencia_producto').value.trim(),
            hora_inicial: document.getElementById('hora_inicial').value,
            cantidad: document.getElementById('cantidad').value,
            responsable_cambio: document.getElementById('responsable_cambio').value,
            fecha_ingreso: document.getElementById('fecha_ingreso').value,
            referencia_producto_linea: document.getElementById('referencia_producto_linea').value.trim(),
            hora_ingreso: document.getElementById('hora_ingreso').value,
            hora_final: document.getElementById('hora_final').value,
            cantidad_line: document.getElementById('cantidad_line').value,
            responsable_linea: document.getElementById('responsable_linea').value,
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
                }).then(() => window.location.href = 'rev_purga_v2.php');
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
