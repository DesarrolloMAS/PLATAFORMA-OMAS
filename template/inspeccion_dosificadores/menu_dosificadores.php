<?php
require '../sesion.php';
verificarAutenticacion();

$sede = $_SESSION['sede'];
$from = $_GET['from'] ?? 'calidad';
$backUrl = ($from === 'mantenimiento') ? '../menu_mantenimiento.html' : '../menu_adm_calidad.html';
$backLabel = ($from === 'mantenimiento') ? '← Menú Mantenimiento' : '← Menú Calidad';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DOSIFICADORES</title>
    <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700&family=Space+Mono:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --bg-color: #0B0E14;
            --panel-bg: #151A22;
            --accent: #00F0FF;
            --accent-glow: rgba(0, 240, 255, 0.4);
            --text-main: #E2E8F0;
            --text-muted: #94A3B8;
            --border-color: #1E293B;
            --input-bg: #0F172A;
            --danger: #FF3366;
            --danger-glow: rgba(255, 51, 102, 0.4);
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

        .container { max-width: 1100px; margin: 0 auto; }

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
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .header-box::before {
            content: "CATÁLOGO";
            position: absolute;
            top: 20px; right: -30px;
            background: var(--accent);
            color: var(--bg-color);
            font-family: 'Space Mono', monospace;
            font-size: 11px; font-weight: 700;
            padding: 4px 40px;
            transform: rotate(45deg);
            box-shadow: 0 0 10px var(--accent-glow);
        }

        .main-title { font-size: 24px; font-weight: 700; color: #fff; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px; }
        .sub-title { color: var(--text-muted); font-size: 13px; font-family: 'Space Mono', monospace; }

        .header-actions { display: flex; gap: 10px; flex-wrap: wrap; }

        .btn-back, .btn-hist {
            background: var(--input-bg);
            border: 1px solid var(--border-color);
            color: var(--text-main);
            padding: 10px 18px;
            border-radius: var(--r-sm);
            font-family: 'Space Mono', monospace;
            text-decoration: none;
            font-size: 12px;
            transition: all 0.2s;
            white-space: nowrap;
        }
        .btn-back:hover, .btn-hist:hover { border-color: var(--accent); color: var(--accent); background: rgba(0,240,255,0.05); }

        .btn-agregar {
            width: 100%;
            background: linear-gradient(135deg, rgba(0,240,255,0.1), rgba(0,240,255,0.04));
            border: 1px solid rgba(0,240,255,0.4);
            color: var(--accent);
            padding: 15px 24px;
            border-radius: var(--r-md);
            font-family: 'Space Mono', monospace;
            font-size: 13px; font-weight: 700;
            cursor: pointer; transition: all 0.3s;
            margin-bottom: 25px;
            text-transform: uppercase; letter-spacing: 1px;
        }
        .btn-agregar:hover {
            background: linear-gradient(135deg, rgba(0,240,255,0.17), rgba(0,240,255,0.08));
            border-color: var(--accent);
            box-shadow: 0 0 20px rgba(0,240,255,0.18);
        }

        .steps-label {
            font-family: 'Space Mono', monospace;
            font-size: 10px; color: var(--text-muted);
            text-transform: uppercase; letter-spacing: 2px;
            margin-bottom: 13px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 20px;
        }

        .card {
            background: var(--panel-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--r-md);
            padding: 22px;
            display: flex; flex-direction: column; gap: 14px;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            position: relative;
        }

        .card:hover {
            border-color: rgba(0, 240, 255, 0.4);
            box-shadow: 0 10px 25px rgba(0, 240, 255, 0.08);
        }

        .card-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; }

        .card-title { font-size: 16px; font-weight: 700; color: #fff; text-transform: uppercase; letter-spacing: 0.3px; word-break: break-word; }

        .btn-delete {
            background: transparent;
            border: 1px solid var(--border-color);
            color: var(--text-muted);
            width: 28px; height: 28px;
            border-radius: var(--r-sm);
            cursor: pointer; font-size: 13px;
            flex-shrink: 0;
            transition: all 0.2s;
            display: flex; align-items: center; justify-content: center;
        }
        .btn-delete:hover { border-color: var(--danger); color: var(--danger); background: rgba(255,51,102,0.06); }

        .card-meta { font-size: 11px; color: var(--text-muted); font-family: 'Space Mono', monospace; }

        .btn-iniciar {
            background: rgba(0,240,255,0.07);
            border: 1px solid rgba(0,240,255,0.3);
            color: var(--accent);
            padding: 10px 14px;
            border-radius: var(--r-sm);
            font-family: 'Space Mono', monospace;
            font-size: 11px; font-weight: 700;
            cursor: pointer; transition: all 0.2s;
            text-align: center; text-decoration: none;
            display: block;
        }
        .btn-iniciar:hover { background: rgba(0,240,255,0.14); box-shadow: 0 0 12px rgba(0,240,255,0.15); }

        .empty-state {
            grid-column: 1 / -1;
            background: var(--panel-bg); border: 1px dashed var(--border-color);
            padding: 60px 20px; border-radius: var(--r-md);
            text-align: center; color: var(--text-muted);
        }
        .empty-state h3 { color: #fff; font-size: 18px; margin-bottom: 8px; }

        .system-status {
            display: flex; align-items: center; justify-content: center;
            gap: 10px;
            font-family: 'Space Mono', monospace; font-size: 10px;
            color: var(--text-muted); padding-top: 22px;
        }
        .status-dot {
            width: 7px; height: 7px; border-radius: 50%;
            background: var(--success); box-shadow: 0 0 6px var(--success);
            animation: pulse 2s infinite;
        }
        @keyframes pulse { 0%,100% { opacity:1; } 50% { opacity:0.4; } }
    </style>
</head>
<body>
<div class="container">

    <div class="header-box">
        <div>
            <div class="main-title">Dosificadores</div>
            <div class="sub-title">Sede Operativa: [ <?= htmlspecialchars($sede) ?> ] — Seleccione un dosificador o agregue uno nuevo</div>
        </div>
        <div class="header-actions">
            <a href="rev_inspeccion_dosificadores.php?from=<?= urlencode($from) ?>" class="btn-hist">📁 Historial de Inspecciones</a>
            <a href="<?= htmlspecialchars($backUrl) ?>" class="btn-back"><?= htmlspecialchars($backLabel) ?></a>
        </div>
    </div>

    <button class="btn-agregar" onclick="agregarDosificador()">+ AGREGAR DOSIFICADOR</button>

    <div class="steps-label">// dosificadores registrados — sede <?= htmlspecialchars($sede) ?></div>
    <div class="grid" id="grid">
        <div class="empty-state"><h3>Cargando...</h3></div>
    </div>

    <div class="system-status">
        <div class="status-dot"></div>
        SISTEMA JSON INTERCONECTADO — PLATAFORMA OMAS / CALIDAD
    </div>

</div>

<script>
    const FROM = <?= json_encode($from) ?>;

    async function cargarDosificadores() {
        const grid = document.getElementById('grid');
        try {
            const resp = await fetch('listar_dosificadores.php');
            const data = await resp.json();

            if (data.status !== 'success') {
                grid.innerHTML = `<div class="empty-state"><h3>Error al cargar</h3><p>${data.message || ''}</p></div>`;
                return;
            }

            const dosificadores = data.dosificadores || [];
            if (dosificadores.length === 0) {
                grid.innerHTML = `<div class="empty-state"><h3>Sin dosificadores registrados</h3><p>Usa el botón "+ Agregar Dosificador" para crear el primero.</p></div>`;
                return;
            }

            grid.innerHTML = dosificadores.map(d => `
                <div class="card">
                    <div class="card-top">
                        <div class="card-title">${esc(d.nombre)}</div>
                        <button class="btn-delete" title="Eliminar dosificador" onclick="eliminarDosificador('${esc(d.id)}', '${escAttr(d.nombre)}')">✕</button>
                    </div>
                    <div class="card-meta">Creado: ${esc(d.fecha_creacion || '—')}</div>
                    <a class="btn-iniciar" href="index.html?dosificador=${encodeURIComponent(d.nombre)}&dosificador_id=${encodeURIComponent(d.id)}&from=${encodeURIComponent(FROM)}">
                        → INICIAR INSPECCIÓN
                    </a>
                </div>
            `).join('');
        } catch (err) {
            grid.innerHTML = `<div class="empty-state"><h3>Error de conexión</h3></div>`;
        }
    }

    function esc(str) {
        if (str === null || str === undefined) return '';
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }
    function escAttr(str) {
        return esc(str).replace(/'/g, "\\'");
    }

    async function agregarDosificador() {
        const { value: nombre } = await Swal.fire({
            title: 'Nuevo Dosificador',
            input: 'text',
            inputLabel: 'Nombre del equipo (Ej: Dosificador 1)',
            inputPlaceholder: 'Nombre del dosificador',
            showCancelButton: true,
            confirmButtonText: 'Agregar',
            cancelButtonText: 'Cancelar',
            background: '#151A22', color: '#fff', confirmButtonColor: '#00F0FF', cancelButtonColor: '#3a3f4b',
            inputValidator: (value) => !value.trim() ? 'El nombre no puede estar vacío.' : undefined
        });

        if (!nombre) return;

        try {
            const resp = await fetch('agregar_dosificador.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ nombre: nombre.trim() })
            });
            const data = await resp.json();
            if (data.status === 'success') {
                cargarDosificadores();
            } else {
                Swal.fire({ title: 'Error', text: data.message || 'No se pudo agregar.', icon: 'error', background: '#151A22', color: '#fff', confirmButtonColor: '#FF3366' });
            }
        } catch (e) {
            Swal.fire({ title: 'Error de conexión', icon: 'error', background: '#151A22', color: '#fff', confirmButtonColor: '#FF3366' });
        }
    }

    async function eliminarDosificador(id, nombre) {
        const confirm = await Swal.fire({
            title: '¿Eliminar dosificador?',
            text: `Se eliminará "${nombre}" del catálogo. El histórico de inspecciones ya guardadas no se ve afectado.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            background: '#151A22', color: '#fff', confirmButtonColor: '#FF3366', cancelButtonColor: '#3a3f4b'
        });

        if (!confirm.isConfirmed) return;

        try {
            const resp = await fetch('eliminar_dosificador.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            });
            const data = await resp.json();
            if (data.status === 'success') {
                cargarDosificadores();
            } else {
                Swal.fire({ title: 'Error', text: data.message || 'No se pudo eliminar.', icon: 'error', background: '#151A22', color: '#fff', confirmButtonColor: '#FF3366' });
            }
        } catch (e) {
            Swal.fire({ title: 'Error de conexión', icon: 'error', background: '#151A22', color: '#fff', confirmButtonColor: '#FF3366' });
        }
    }

    cargarDosificadores();
</script>
</body>
</html>
