<?php
require '../sesion.php';
verificarAutenticacion();

$sede = $_SESSION['sede'];
$file = $_GET['file'] ?? '';
$id = $_GET['id'] ?? '';

if (!$file || !$id) {
    die("Faltan parámetros de búsqueda (file/id).");
}

$file_path = "../../archivos/generados/proceso_v2/" . preg_replace('/[^A-Za-z0-9_-]/', '', $sede) . "/" . basename($file);
if (!file_exists($file_path)) {
    die("El archivo de registros no existe.");
}

$records = json_decode(file_get_contents($file_path), true) ?: [];
$registro = null;
foreach ($records as $r) {
    if ($r['id_registro'] === $id) {
        $registro = $r;
        break;
    }
}

if (!$registro) {
    die("Registro no encontrado.");
}

$d = $registro['datos'] ?? [];

function val($d, $key) {
    return htmlspecialchars($d[$key] ?? '');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CORREGIR PROCESO DE MOLIENDA</title>
    <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700&family=Space+Mono:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --bg-color: #0B0E14;
            --panel-bg: #151A22;
            --accent: #FFB000;
            --accent-glow: rgba(255, 176, 0, 0.4);
            --accent-hover: #FFC533;
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

        .btn-back {
            background: var(--input-bg);
            border: 1px solid var(--border-color);
            color: var(--text-main);
            padding: 10px 20px;
            border-radius: var(--r-sm);
            font-family: 'Space Mono', monospace;
            text-decoration: none;
            font-size: 13px;
            transition: all 0.3s;
            white-space: nowrap;
            display: inline-block;
        }
        .btn-back:hover {
            border-color: var(--accent);
            color: var(--accent);
            background: rgba(255, 176, 0, 0.05);
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
                linear-gradient(rgba(255, 176, 0, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 176, 0, 0.03) 1px, transparent 1px);
            background-size: 30px 30px;
        }

        .container { max-width: 1000px; margin: 0 auto; }

        .header-box {
            background: var(--panel-bg);
            border: 1px solid var(--border-color);
            border-left: 4px solid var(--accent);
            padding: 30px;
            border-radius: var(--r-md);
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            position: relative;
            overflow: hidden;
        }

        .header-box::before {
            content: "CORRECCIÓN";
            position: absolute;
            top: 20px;
            right: -30px;
            background: var(--accent);
            color: var(--bg-color);
            font-family: 'Space Mono', monospace;
            font-size: 11px;
            font-weight: 700;
            padding: 4px 40px;
            transform: rotate(45deg);
            box-shadow: 0 0 10px var(--accent-glow);
        }

        .main-title { font-size: 24px; font-weight: 700; color: #fff; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 1px; }
        .sub-title { color: var(--text-muted); font-size: 14px; }

        .section-card {
            background: var(--panel-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--r-md);
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }

        .section-title {
            display: flex; align-items: center; gap: 12px;
            font-size: 16px; font-weight: 600; color: var(--accent);
            margin-bottom: 20px; padding-bottom: 12px;
            border-bottom: 1px dashed var(--border-color);
            text-transform: uppercase; letter-spacing: 0.5px;
        }

        .section-title span {
            font-family: 'Space Mono', monospace; font-size: 12px;
            background: rgba(255, 176, 0, 0.1); color: var(--accent);
            padding: 4px 8px; border-radius: var(--r-sm);
            border: 1px solid rgba(255, 176, 0, 0.2);
        }

        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; }
        .form-group { display: flex; flex-direction: column; gap: 8px; }
        .form-group.full { grid-column: 1 / -1; }
        .form-label { font-size: 13px; color: var(--text-muted); font-weight: 500; }

        .form-control {
            background: var(--input-bg); border: 1px solid var(--border-color);
            color: var(--text-main); padding: 12px 14px; border-radius: var(--r-sm);
            font-family: 'Barlow', sans-serif; font-size: 14px; width: 100%;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            outline: none; border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(255, 176, 0, 0.1);
            background: rgba(255, 176, 0, 0.02);
        }

        textarea.form-control { resize: vertical; min-height: 80px; }

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23FFB000%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E");
            background-repeat: no-repeat; background-position: right 14px top 50%;
            background-size: 10px auto; padding-right: 30px; cursor: pointer;
        }
        select.form-control option { background-color: var(--panel-bg); color: var(--text-main); }

        input[type="date"].form-control, input[type="time"].form-control {
            font-family: 'Space Mono', monospace; font-size: 13px; color: var(--accent);
        }

        .btn-submit {
            background: var(--accent); color: var(--bg-color); border: none;
            padding: 16px 30px; font-size: 15px; font-weight: 700;
            font-family: 'Space Mono', monospace; border-radius: var(--r-sm);
            cursor: pointer; width: 100%; text-transform: uppercase; letter-spacing: 1px;
            transition: all 0.3s ease; margin-top: 10px;
            box-shadow: 0 0 15px var(--accent-glow);
            display: flex; justify-content: center; align-items: center; gap: 10px;
        }
        .btn-submit:hover { background: #fff; color: var(--bg-color); box-shadow: 0 0 25px rgba(255, 255, 255, 0.6); transform: translateY(-2px); }
        .btn-submit:disabled { cursor: not-allowed; transform: none; }

        .system-status {
            font-family: 'Space Mono', monospace; font-size: 12px; color: var(--text-muted);
            text-align: center; margin-top: 30px; display: flex; justify-content: center;
            align-items: center; gap: 8px;
        }
        .status-dot {
            width: 8px; height: 8px; background: var(--accent); border-radius: 50%;
            box-shadow: 0 0 8px var(--accent); animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(255, 176, 0, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(255, 176, 0, 0); }
            100% { box-shadow: 0 0 0 0 rgba(255, 176, 0, 0); }
        }

        .subproducto-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px; }
        .subproducto-block { background: var(--input-bg); border: 1px solid var(--border-color); border-radius: var(--r-sm); padding: 15px; }
        .subproducto-block h3 { font-family: 'Space Mono', monospace; font-size: 13px; color: var(--accent); margin-bottom: 12px; text-transform: uppercase; }
        .subproducto-block .form-grid { grid-template-columns: 1fr 1fr; gap: 10px; }
    </style>
</head>
<body>

<div class="container">
    <div class="header-box">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div>
                <h1 class="main-title">Corregir Proceso de Molienda</h1>
                <div class="sub-title">Editando registro <?= htmlspecialchars($id) ?> — Período <?= htmlspecialchars(str_replace(['PROCESO_MOLIENDA_', '.json'], '', basename($file))) ?></div>
            </div>
            <a href="visor_proceso_v2.php?file=<?= urlencode($file) ?>&id=<?= urlencode($id) ?>" class="btn-back">← CANCELAR</a>
        </div>
    </div>

    <form id="correccionForm">
        <input type="hidden" name="file" value="<?= htmlspecialchars($file) ?>">
        <input type="hidden" name="id_registro" value="<?= htmlspecialchars($id) ?>">

        <div class="section-card">
            <div class="section-title"><span>01</span> Datos Generales del Proceso</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Fecha</label>
                    <input type="date" name="fecha" class="form-control" value="<?= val($d, 'fecha') ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Líder de Turno</label>
                    <select name="lider_turno" id="liderTurno" class="form-control" required data-current="<?= val($d, 'lider_turno') ?>">
                        <option value="<?= val($d, 'lider_turno') ?>"><?= val($d, 'lider_turno') ?: 'Cargando...' ?></option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Hora Inicio</label>
                    <input type="time" name="hora_inicio" class="form-control" value="<?= val($d, 'hora_inicio') ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Hora Final</label>
                    <input type="time" name="hora_final" class="form-control" value="<?= val($d, 'hora_final') ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Silo de Moje</label>
                    <input type="number" name="silo_moje" class="form-control" value="<?= val($d, 'silo_moje') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Presentación del Producto</label>
                    <input type="text" name="presentacion_producto" class="form-control" value="<?= val($d, 'presentacion_producto') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Referencia del Producto</label>
                    <input type="text" name="referencia_producto" class="form-control" value="<?= val($d, 'referencia_producto') ?>" required>
                </div>
            </div>
        </div>

        <div class="section-card">
            <div class="section-title"><span>02</span> Báscula y Empaque de Harina</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Báscula de Trigo</label>
                    <input type="number" step="0.01" name="bascula_trigo" class="form-control" value="<?= val($d, 'bascula_trigo') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Báscula de Harina</label>
                    <input type="text" name="bascula_harina" class="form-control" value="<?= val($d, 'bascula_harina') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Bultos de Harina (unidades)</label>
                    <input type="text" name="bultos_harina" class="form-control" value="<?= val($d, 'bultos_harina') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Lote de Harina</label>
                    <input type="text" name="lote_harina" class="form-control" value="<?= val($d, 'lote_harina') ?>">
                </div>
            </div>
        </div>

        <div class="section-card">
            <div class="section-title"><span>03</span> Harina Granel</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Cantidad KG</label>
                    <input type="text" name="granel_cantidad_kg" class="form-control" value="<?= val($d, 'granel_cantidad_kg') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Silo</label>
                    <input type="text" name="granel_silo" class="form-control" value="<?= val($d, 'granel_silo') ?>">
                </div>
            </div>
        </div>

        <div class="section-card">
            <div class="section-title"><span>04</span> Subproductos</div>
            <div class="subproducto-grid">
                <div class="subproducto-block">
                    <h3>Mogolla</h3>
                    <div class="form-grid">
                        <div class="form-group"><label class="form-label">Bultos</label><input type="number" name="mogolla_bultos" class="form-control" value="<?= val($d, 'mogolla_bultos') ?>"></div>
                        <div class="form-group"><label class="form-label">Hilo</label><input type="text" name="mogolla_hilo" class="form-control" value="<?= val($d, 'mogolla_hilo') ?>"></div>
                    </div>
                </div>
                <div class="subproducto-block">
                    <h3>Salvado</h3>
                    <div class="form-grid">
                        <div class="form-group"><label class="form-label">Bultos</label><input type="number" name="salvado_bultos" class="form-control" value="<?= val($d, 'salvado_bultos') ?>"></div>
                        <div class="form-group"><label class="form-label">Hilo</label><input type="text" name="salvado_hilo" class="form-control" value="<?= val($d, 'salvado_hilo') ?>"></div>
                    </div>
                </div>
                <div class="subproducto-block">
                    <h3>Segunda</h3>
                    <div class="form-grid">
                        <div class="form-group"><label class="form-label">Bultos</label><input type="number" name="segunda_bultos" class="form-control" value="<?= val($d, 'segunda_bultos') ?>"></div>
                        <div class="form-group"><label class="form-label">Hilo</label><input type="text" name="segunda_hilo" class="form-control" value="<?= val($d, 'segunda_hilo') ?>"></div>
                    </div>
                </div>
                <div class="subproducto-block">
                    <h3>Germen</h3>
                    <div class="form-grid">
                        <div class="form-group"><label class="form-label">Bultos</label><input type="number" name="germen_bultos" class="form-control" value="<?= val($d, 'germen_bultos') ?>"></div>
                        <div class="form-group"><label class="form-label">Hilo</label><input type="text" name="germen_hilo" class="form-control" value="<?= val($d, 'germen_hilo') ?>"></div>
                    </div>
                </div>
                <div class="subproducto-block">
                    <h3>Sémola Fina / Granza</h3>
                    <div class="form-grid" style="grid-template-columns: 1fr;">
                        <div class="form-group"><label class="form-label">Cantidad (Bultos)</label><input type="number" name="semola_granza_bultos" class="form-control" value="<?= val($d, 'semola_granza_bultos') ?>"></div>
                    </div>
                </div>
            </div>
            <div class="form-group full" style="margin-top: 15px;">
                <label class="form-label">Varadas / Observaciones</label>
                <textarea name="varadas" class="form-control"><?= val($d, 'varadas') ?></textarea>
            </div>
        </div>

        <button type="submit" class="btn-submit" id="btnGuardar">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z"></path></svg>
            GUARDAR CORRECCIÓN
        </button>

        <div class="system-status">
            <div class="status-dot"></div>
            MODO CORRECCIÓN — LOS CAMBIOS SOBREESCRIBEN EL REGISTRO ORIGINAL
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const select = document.getElementById('liderTurno');
        const current = select.dataset.current || '';

        fetch('datos_iniciales.php')
            .then(r => r.json())
            .then(data => {
                select.innerHTML = '<option value="">Seleccione un Responsable</option>';
                (data.lideres || []).forEach(nombre => {
                    const opt = document.createElement('option');
                    opt.value = nombre;
                    opt.textContent = nombre;
                    if (nombre === current) opt.selected = true;
                    select.appendChild(opt);
                });
                const optNone = document.createElement('option');
                optNone.value = 'Ningun Usuario Disponible';
                optNone.textContent = 'Ningún Usuario Disponible';
                if (current === 'Ningun Usuario Disponible') optNone.selected = true;
                select.appendChild(optNone);
            })
            .catch(() => { /* conserva la opción actual precargada si falla la carga */ });
    });

    document.getElementById('correccionForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const btn = document.getElementById('btnGuardar');
        const overlayText = btn.innerHTML;
        btn.innerHTML = 'GUARDANDO CORRECCIÓN...';
        btn.disabled = true;
        btn.style.opacity = '0.7';

        const formData = new FormData(this);
        const jsonData = {};
        formData.forEach((value, key) => { jsonData[key] = value; });

        fetch('actualizar.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(jsonData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({
                    title: '¡CORREGIDO!',
                    text: 'El registro fue actualizado correctamente.',
                    icon: 'success',
                    background: '#151A22', color: '#fff', confirmButtonColor: '#FFB000'
                }).then(() => {
                    window.location.href = `visor_proceso_v2.php?file=${encodeURIComponent(jsonData.file)}&id=${encodeURIComponent(jsonData.id_registro)}`;
                });
            } else {
                Swal.fire({
                    title: 'Error del Sistema',
                    text: data.message || 'No se pudo guardar la corrección.',
                    icon: 'error',
                    background: '#151A22', color: '#fff', confirmButtonColor: '#FF3366'
                });
                btn.innerHTML = overlayText; btn.disabled = false; btn.style.opacity = '1';
            }
        })
        .catch(err => {
            console.error('Error:', err);
            Swal.fire({
                title: 'Error de Red',
                text: 'No se pudo contactar actualizar.php.',
                icon: 'error',
                background: '#151A22', color: '#fff', confirmButtonColor: '#FF3366'
            });
            btn.innerHTML = overlayText; btn.disabled = false; btn.style.opacity = '1';
        });
    });
</script>

</body>
</html>
