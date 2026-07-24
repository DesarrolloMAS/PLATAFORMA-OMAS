<?php
require '../../sesion.php';

// Validar credenciales de sesión (Ajustar según necesidad)
// verificarAutenticacion(); // Si tienes esta función en sesion.php

// Configuración de la base de datos PostgreSQL
$host = '127.0.0.1';
$db = 'bitacora_mantenimiento';
$user = 'bitacora_user';
$pass = 'bitacora2026';

try {
    $dsn = "pgsql:host=$host;port=5432;dbname=$db;";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Error crítico: No se pudo conectar a la base de datos PostgreSQL de la bitácora.");
}

$filtro_zona = $_GET['zona'] ?? 'Todas';
$filtro_especialidad = $_GET['especialidad'] ?? 'Todas';

// --- LÓGICA DE OBTENCIÓN DE DATOS ---
try {
    $sql = "SELECT * FROM bitacora WHERE 1=1";
    $params = [];

    if ($filtro_zona !== 'Todas') {
        $sql .= " AND zona = :zona";
        $params[':zona'] = $filtro_zona;
    }
    if ($filtro_especialidad !== 'Todas') {
        $sql .= " AND especialidad = :especialidad";
        $params[':especialidad'] = $filtro_especialidad;
    }
    $sql .= " ORDER BY fecha DESC, hora DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $registros = $stmt->fetchAll();
} catch (PDOException $e) {
    $registros = []; // Fallback
    error_log("Error al leer la bitácora: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bitácora Maestra de Mantenimiento</title>
    <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700&family=Space+Mono:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #0B0E14;
            --panel-bg: #151A22;
            --accent: #00F0FF; /* Azul Neón */
            --accent-glow: rgba(0, 240, 255, 0.4);
            --danger: #FF3366;
            --warning: #F59E0B;
            --text-main: #E2E8F0;
            --text-muted: #94A3B8;
            --border-color: #1E293B;
            --table-hover: #1E293B;
            --r-lg: 12px;
            --r-md: 8px;
            --r-sm: 4px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Barlow', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            min-height: 100vh;
            padding: 40px 20px;
            background-image: 
                linear-gradient(rgba(0, 240, 255, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0, 240, 255, 0.03) 1px, transparent 1px);
            background-size: 30px 30px;
        }

        .container { max-width: 1400px; margin: 0 auto; }

        .header-box {
            background: var(--panel-bg);
            border: 1px solid var(--border-color);
            border-left: 4px solid var(--accent);
            padding: 30px;
            border-radius: var(--r-md);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            position: relative;
        }

        .header-box::before {
            content: "POSTGRESQL DATABASE";
            position: absolute; top: -10px; right: 20px;
            background: var(--accent); color: #000;
            font-family: 'Space Mono', monospace; font-size: 10px; font-weight: 700;
            padding: 4px 12px; border-radius: var(--r-sm);
            box-shadow: 0 0 10px var(--accent-glow);
        }

        .main-title { font-size: 28px; font-weight: 700; color: #fff; text-transform: uppercase; margin-bottom: 4px; }
        .sub-title { color: var(--text-muted); font-size: 14px; font-family: 'Space Mono', monospace; }

        .btn-back {
            background: transparent; border: 1px solid var(--text-muted); color: var(--text-main);
            padding: 10px 16px; border-radius: var(--r-sm); font-family: 'Space Mono', monospace;
            text-decoration: none; font-size: 13px; font-weight: bold; transition: all 0.3s;
        }
        .btn-back:hover { background: rgba(255,255,255,0.1); }

        .stats-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;
        }

        .stat-card {
            background: var(--panel-bg); border: 1px solid var(--border-color);
            padding: 20px; border-radius: var(--r-md); text-align: center;
        }

        .stat-value { font-size: 32px; font-weight: 700; color: var(--accent); font-family: 'Space Mono', monospace; margin-bottom: 5px; }
        .stat-label { font-size: 13px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; }

        .table-container {
            background: var(--panel-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--r-md);
            overflow-x: auto;
            box-shadow: 0 10px 30px rgba(0,0,0,0.4);
        }

        .data-table {
            width: 100%; border-collapse: collapse; text-align: left;
        }

        .data-table th {
            background: rgba(0, 240, 255, 0.05); color: var(--accent);
            font-family: 'Space Mono', monospace; font-size: 12px;
            padding: 15px; border-bottom: 1px solid var(--border-color);
            text-transform: uppercase; letter-spacing: 1px; white-space: nowrap;
        }

        .data-table td {
            padding: 15px; border-bottom: 1px solid rgba(255,255,255,0.02);
            font-size: 14px; vertical-align: top;
        }

        .data-table tr:last-child td { border-bottom: none; }
        .data-table tr:hover td { background: var(--table-hover); }

        .badge {
            display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 11px;
            font-family: 'Space Mono', monospace; font-weight: bold; text-transform: uppercase;
        }
        .badge.preventiva { background: rgba(0, 240, 255, 0.1); color: var(--accent); border: 1px solid var(--accent); }
        .badge.correctiva { background: rgba(255, 51, 102, 0.1); color: var(--danger); border: 1px solid var(--danger); }
        .badge.predictiva { background: rgba(245, 158, 11, 0.1); color: var(--warning); border: 1px solid var(--warning); }
        .badge.default { background: rgba(255, 255, 255, 0.1); color: #fff; border: 1px solid #666; }

        .machine-name { font-weight: 700; color: #fff; display: block; margin-bottom: 4px; }
        .machine-code { font-family: 'Space Mono', monospace; font-size: 11px; color: var(--text-muted); }

        .desc-cell { max-width: 300px; }
        .desc-text {
            font-size: 13px; color: #cbd5e1; line-height: 1.5;
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
        }

        .empty-state {
            padding: 50px; text-align: center; color: var(--text-muted);
        }
        .empty-state h3 { color: #fff; margin-bottom: 10px; }
        .empty-state p { font-size: 14px; }

        .filter-select {
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid var(--border-color);
            color: var(--text-main);
            padding: 8px 15px;
            border-radius: var(--r-sm);
            font-family: 'Space Mono', monospace;
            font-size: 13px;
            outline: none;
            cursor: pointer;
            transition: all 0.3s;
        }
        .filter-select:hover { border-color: var(--accent); }

        /* --- ESTILOS MODO ADMINISTRADOR --- */
        .admin-col { display: none; width: 60px; text-align: center; }
        .btn-delete {
            background: rgba(255, 51, 102, 0.1);
            border: 1px solid var(--danger);
            color: var(--danger);
            padding: 6px 10px;
            border-radius: var(--r-sm);
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: all 0.3s;
            line-height: 1;
        }
        .btn-delete:hover {
            background: var(--danger);
            color: #fff;
            box-shadow: 0 0 12px rgba(255, 51, 102, 0.6);
            transform: scale(1.1);
        }
        .btn-admin-active {
            background: var(--accent) !important;
            color: #000 !important;
            border-color: var(--accent) !important;
            box-shadow: 0 0 15px var(--accent-glow) !important;
        }

    </style>
</head>
<body>

<div class="container">
    <div class="header-box">
        <div>
            <h1 class="main-title">Bitácora de Mantenimiento</h1>
            <div class="sub-title">Control central de intervenciones a equipos (Global)</div>
        </div>
        <div style="display: flex; align-items: center; gap: 10px;">
            <!-- Botones de exportación paramétricos -->
            <button id="btn-admin" class="btn-back" style="background: rgba(245, 158, 11, 0.1); border-color: #f59e0b; color: #f59e0b;" onclick="toggleAdmin()">⚙️ ADMINISTRAR</button>
            <a href="visor_impresion_bitacora.php?zona=<?= urlencode($filtro_zona) ?>&especialidad=<?= urlencode($filtro_especialidad) ?>" class="btn-back" style="background: rgba(255,255,255,0.05); border-color: #e2e8f0; color: #fff;">📄 PDF</a>
            <a href="exportar_excel.php" class="btn-back" style="background: rgba(34, 197, 94, 0.1); border-color: #22c55e; color: #22c55e;">📊 EXCEL</a>
            <a href="../../menu_adm.html" class="btn-back">← INICIO</a>
        </div>
    </div>

    <!-- Barra de Filtros -->
    <div style="margin-bottom: 25px; display: flex; gap: 15px; background: var(--panel-bg); padding: 15px; border-radius: var(--r-md); border: 1px solid var(--border-color); align-items: center; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
        <form method="GET" style="display: flex; gap: 15px; width: 100%; align-items: center;">
            <span style="font-family: 'Space Mono', monospace; color: var(--text-muted); font-size: 11px; text-transform: uppercase; letter-spacing: 1px; border-right: 1px solid var(--border-color); padding-right: 15px; margin-right: 5px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 5px;">
                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                </svg>
                Filtrar Registros:
            </span>
            
            <select name="zona" class="filter-select" onchange="this.form.submit()">
                <option value="Todas" <?= $filtro_zona == 'Todas' ? 'selected' : '' ?>>🌐 Todas las Zonas</option>
                <option value="Sur" <?= $filtro_zona == 'Sur' ? 'selected' : '' ?>>📍 Zona Sur</option>
                <option value="Centro" <?= $filtro_zona == 'Centro' ? 'selected' : '' ?>>📍 Zona Centro</option>
            </select>
            
            <select name="especialidad" class="filter-select" onchange="this.form.submit()">
                <option value="Todas" <?= $filtro_especialidad == 'Todas' ? 'selected' : '' ?>>🛠️ Todas las Especialidades</option>
                <option value="Locativos" <?= $filtro_especialidad == 'Locativos' ? 'selected' : '' ?>>🏗️ Locativos</option>
                <option value="Mecánicos" <?= $filtro_especialidad == 'Mecánicos' ? 'selected' : '' ?>>⚙️ Mecánicos</option>
                <option value="General" <?= $filtro_especialidad == 'General' ? 'selected' : '' ?>>📋 General</option>
            </select>

            <?php if($filtro_zona !== 'Todas' || $filtro_especialidad !== 'Todas'): ?>
                <a href="dashboard_bitacora.php" style="color: var(--danger); font-size: 11px; text-decoration: none; font-family: 'Space Mono', monospace; text-transform: uppercase; margin-left: auto;">Limpiar Filtros ×</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value"><?= count($registros) ?></div>
            <div class="stat-label">Intervenciones Registradas</div>
        </div>
        <?php 
            $correctivos = 0;
            $preventivos = 0;
            foreach($registros as $reg) {
                if(stripos($reg['tipo_mantenimiento'], 'correctivo') !== false) $correctivos++;
                if(stripos($reg['tipo_mantenimiento'], 'preventivo') !== false) $preventivos++;
            }
        ?>
        <div class="stat-card">
            <div class="stat-value" style="color: var(--danger);"><?= $correctivos ?></div>
            <div class="stat-label">Mantenimientos Correctivos</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color: var(--accent);"><?= $preventivos ?></div>
            <div class="stat-label">Mantenimientos Preventivos</div>
        </div>
    </div>

    <div class="table-container">
        <?php if (empty($registros)): ?>
            <div class="empty-state">
                <h3>Bóveda Vacía</h3>
                <p>Aún no se ha capturado ningún registro de mantenimiento en la base de datos.</p>
                <p style="margin-top:10px; font-size: 12px; color: #64748B;">Recuerda que los registros se autoguardarán al generar el PDF de la solicitud.</p>
            </div>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID / Fecha</th>
                        <th>Equipo / Especialidad</th>
                        <th>Zona / Ubicación</th>
                        <th>Tipo Mantenimiento</th>
                        <th>Descripción de la Falla</th>
                        <th>Técnico Responsable</th>
                        <th class="admin-col">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($registros as $reg): 
                        
                        $tipo = strtolower($reg['tipo_mantenimiento'] ?? '');
                        $badgeClass = 'default';
                        if (strpos($tipo, 'correctivo') !== false) $badgeClass = 'correctiva';
                        elseif (strpos($tipo, 'preventivo') !== false) $badgeClass = 'preventiva';
                        elseif (strpos($tipo, 'predictivo') !== false) $badgeClass = 'predictiva';
                    ?>
                    <tr>
                        <td style="white-space: nowrap;">
                            <strong style="color: #fff;">#<?= str_pad($reg['id'], 4, '0', STR_PAD_LEFT) ?></strong><br>
                            <span style="font-family: 'Space Mono', monospace; font-size:11px; color: var(--text-muted);">
                                <?= htmlspecialchars($reg['fecha'] ?? '---') ?><br>
                                <?= htmlspecialchars($reg['hora'] ?? '---') ?>
                            </span>
                        </td>
                        <td>
                            <span class="machine-name"><?= htmlspecialchars($reg['maquina'] ?? 'N/A') ?></span>
                            <span class="machine-code">COD: <?= htmlspecialchars($reg['codigo'] ?: 'N/A') ?></span>
                            <div style="margin-top: 6px;">
                                <span style="font-size: 10px; color: var(--text-main); background: rgba(255,255,255,0.1); padding: 2px 6px; border-radius: 3px; font-family: 'Space Mono', monospace; text-transform: uppercase;">
                                    <?= htmlspecialchars($reg['especialidad'] ?? 'General') ?>
                                </span>
                            </div>
                        </td>
                        <td>
                            <strong style="color: var(--accent); font-size: 13px; font-family: 'Space Mono', monospace;">[ZONA <?= strtoupper(htmlspecialchars($reg['zona'] ?? 'Centro')) ?>]</strong><br>
                            <span style="color: #cbd5e1; font-size: 13px; margin-top: 3px; display: inline-block;">
                                <?= htmlspecialchars($reg['ubicacion'] ?? 'N/A') ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge <?= $badgeClass ?>">
                                <?= htmlspecialchars($reg['tipo_mantenimiento'] ?? 'General') ?>
                            </span>
                        </td>
                        <td class="desc-cell">
                            <div class="desc-text" title="<?= htmlspecialchars($reg['descripcion_falla'] ?? '') ?>">
                                <?= htmlspecialchars($reg['descripcion_falla'] ?: 'Sin descripción registrada.') ?>
                            </div>
                        </td>
                        <td>
                            <span style="color: #e2e8f0; font-size: 13px; font-weight: 500;">
                                <?= htmlspecialchars($reg['tecnico'] ?? 'N/A') ?>
                            </span>
                        </td>
                        <td class="admin-col" style="vertical-align: middle; text-align: center;">
                            <button class="btn-delete" onclick="eliminarRegistro(<?= $reg['id'] ?>, this)" title="Eliminar Registro">×</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<script>
    function toggleAdmin() {
        const adminCols = document.querySelectorAll('.admin-col');
        const btnAdmin = document.getElementById('btn-admin');
        const isActive = btnAdmin.classList.toggle('btn-admin-active');
        
        adminCols.forEach(col => {
            col.style.display = isActive ? 'table-cell' : 'none';
        });

        if(isActive) {
            btnAdmin.innerHTML = '🛡️ MODO ADMIN ON';
        } else {
            btnAdmin.innerHTML = '⚙️ ADMINISTRAR';
        }
    }

    async function eliminarRegistro(id, el) {
        if (!confirm(`¿Estás seguro de eliminar el registro #${id.toString().padStart(4, '0')}?\nEsta acción es irreversible.`)) return;

        try {
            const formData = new FormData();
            formData.append('id', id);

            const response = await fetch('eliminar_registro_bitacora.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            
            if (result.success) {
                // Notificación visual rápida antes de recargar
                const row = el.closest('tr');
                row.style.transition = 'all 0.5s';
                row.style.opacity = '0';
                row.style.transform = 'translateX(50px)';
                
                setTimeout(() => {
                    location.reload();
                }, 500);
            } else {
                alert('Error al eliminar: ' + result.error);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Ocurrió un error en la comunicación con el servidor.');
        }
    }
</script>

</body>
</html>
