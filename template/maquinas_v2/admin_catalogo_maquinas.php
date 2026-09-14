<?php
require '../sesion.php';

// Validar credenciales de administrador (Roles '1' y 'adm' autorizados),
// mismo criterio que empaque_v2/admin_catalogo_empaques.php. maquinas_v2 no
// tenía ningún control de rol antes de esto.
if (!isset($_SESSION['rol']) || ($_SESSION['rol'] != '1' && $_SESSION['rol'] != 'adm')) {
    die("Acceso Denegado. Solo administradores pueden configurar el catálogo.");
}

// Catálogo real de máquinas a verificar: vive junto al código (no en
// archivos/generados) porque así lo consume maquinas_menu.php (fetch
// relativo) y formulario.html. Estructura: { tipo: { grupo: [códigos...] } }.
$catalogo_file = "maquinas_galeria.json";

if (!file_exists($catalogo_file)) {
    die("Catálogo no encontrado (maquinas_galeria.json).");
}

$catalogo_data = json_decode(file_get_contents($catalogo_file), true) ?: [];

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $tipo = $_POST['tipo'] ?? '';

    if (isset($catalogo_data[$tipo])) {
        $grupo = $_POST['grupo'] ?? '';

        if ($action === 'add_grupo') {
            $nuevoGrupo = trim($_POST['nuevo_grupo'] ?? '');
            if ($nuevoGrupo !== '' && !isset($catalogo_data[$tipo][$nuevoGrupo])) {
                $catalogo_data[$tipo][$nuevoGrupo] = [];
                $mensaje = 'Grupo "' . htmlspecialchars($nuevoGrupo) . '" creado en "' . htmlspecialchars($tipo) . '".';
            }
        } elseif ($action === 'delete_grupo') {
            if (isset($catalogo_data[$tipo][$grupo])) {
                unset($catalogo_data[$tipo][$grupo]);
                $mensaje = 'Grupo "' . htmlspecialchars($grupo) . '" eliminado (con todas sus máquinas).';
            }
        } elseif ($action === 'add_codigo') {
            // Crear una máquina nueva exige nombre + código de máquina por
            // separado (no un solo campo libre como antes): se concatenan
            // con "_" para respetar el formato "NOMBRE_CODIGOINTERNO" que ya
            // usan todas las máquinas existentes del catálogo.
            $nombreNuevo = trim($_POST['nombre'] ?? '');
            $codigoMaquina = trim($_POST['codigo_maquina'] ?? '');
            if (isset($catalogo_data[$tipo][$grupo]) && $nombreNuevo !== '' && $codigoMaquina !== '') {
                $catalogo_data[$tipo][$grupo][] = $nombreNuevo . '_' . $codigoMaquina;
                $mensaje = 'Máquina añadida a "' . htmlspecialchars($grupo) . '".';
            } elseif ($codigoMaquina === '') {
                $mensaje = 'No se agregó: el código de máquina es obligatorio.';
            }
        } elseif ($action === 'edit_codigo') {
            $index = $_POST['index'] ?? -1;
            $codigo = trim($_POST['codigo'] ?? '');
            if (isset($catalogo_data[$tipo][$grupo][$index]) && $codigo !== '') {
                $catalogo_data[$tipo][$grupo][$index] = $codigo;
                $mensaje = 'Máquina actualizada en "' . htmlspecialchars($grupo) . '".';
            }
        } elseif ($action === 'delete_codigo') {
            $index = $_POST['index'] ?? -1;
            if (isset($catalogo_data[$tipo][$grupo][$index])) {
                array_splice($catalogo_data[$tipo][$grupo], $index, 1);
                $mensaje = 'Máquina eliminada de "' . htmlspecialchars($grupo) . '".';
            }
        }

        file_put_contents($catalogo_file, json_encode($catalogo_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración de Máquinas - Mantenimiento</title>
    <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700&family=Space+Mono:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #0B0E14;
            --panel-bg: #151A22;
            --accent: #FF3366; /* Rojo Admin, mismo criterio que admin_catalogo_empaques.php */
            --accent-glow: rgba(255, 51, 102, 0.4);
            --text-main: #E2E8F0;
            --text-muted: #94A3B8;
            --border-color: #1E293B;
            --input-bg: #0F172A;
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
            background-image: linear-gradient(rgba(255, 51, 102, 0.03) 1px, transparent 1px),
                              linear-gradient(90deg, rgba(255, 51, 102, 0.03) 1px, transparent 1px);
            background-size: 30px 30px;
        }

        .container { max-width: 1200px; margin: 0 auto; }

        .header-box {
            background: var(--panel-bg);
            border: 1px solid var(--border-color);
            border-left: 4px solid var(--accent);
            padding: 30px;
            border-radius: var(--r-md);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            position: relative;
            overflow: hidden;
        }

        .header-box::before {
            content: "ROOT PRIVILEGES";
            position: absolute; top: -10px; right: 20px;
            background: var(--accent); color: #fff;
            font-family: 'Space Mono', monospace; font-size: 10px; font-weight: 700;
            padding: 4px 12px; border-radius: var(--r-sm);
            box-shadow: 0 0 10px var(--accent-glow);
        }

        .main-title { font-size: 24px; font-weight: 700; color: #fff; text-transform: uppercase; margin-bottom: 4px; }
        .sub-title { color: var(--text-muted); font-size: 14px; font-family: 'Space Mono', monospace; }

        .btn-back {
            background: transparent; border: 1px solid var(--text-muted); color: var(--text-main);
            padding: 8px 16px; border-radius: var(--r-sm); font-family: 'Space Mono', monospace;
            text-decoration: none; font-size: 12px; transition: all 0.3s;
        }
        .btn-back:hover { background: rgba(255,255,255,0.1); }

        .sys-msg {
            background: rgba(16, 185, 129, 0.1); border: 1px solid #10B981; color: #10B981;
            padding: 15px; border-radius: var(--r-md); margin-bottom: 20px; font-weight: bold;
            font-family: 'Space Mono', monospace; font-size: 13px; text-align: center;
        }

        .warn-box {
            background: rgba(255, 176, 0, 0.08); border: 1px solid #FFB000; color: #FFB000;
            padding: 14px 18px; border-radius: var(--r-md); margin-bottom: 30px;
            font-size: 13px; line-height: 1.5;
        }

        .tipo-section {
            background: var(--panel-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--r-md);
            padding: 22px 25px;
            margin-bottom: 22px;
        }

        .tipo-title {
            font-size: 17px; font-weight: 700; color: var(--accent);
            text-transform: uppercase; letter-spacing: 0.5px;
            border-bottom: 1px dashed var(--border-color);
            padding-bottom: 12px; margin-bottom: 18px;
        }

        .grupos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 18px;
        }

        .grupo-card {
            background: var(--input-bg); border: 1px solid var(--border-color);
            border-radius: var(--r-sm); padding: 16px;
        }

        .grupo-card-header {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 12px;
        }

        .grupo-card-title { font-size: 13px; font-weight: 700; color: #fff; font-family: 'Space Mono', monospace; }

        .ref-list { list-style: none; display: flex; flex-direction: column; gap: 8px; margin-bottom: 14px; }

        .ref-item {
            display: flex; gap: 6px; align-items: center;
            background: var(--panel-bg); padding: 8px 10px; border-radius: var(--r-sm);
            border: 1px solid rgba(255,255,255,0.05);
        }

        .ref-edit-form { display: flex; gap: 6px; flex: 1; align-items: center; }

        .ref-input, .add-input {
            flex: 1; min-width: 0; background: var(--panel-bg); border: 1px solid var(--border-color);
            color: #fff; padding: 6px 8px; border-radius: var(--r-sm); font-family: 'Space Mono', monospace; font-size: 11px;
        }
        .ref-input:focus, .add-input:focus { outline: none; border-color: var(--accent); }

        .btn-save, .btn-del, .btn-add-inline {
            border: none; cursor: pointer; border-radius: var(--r-sm); padding: 6px 9px;
            font-size: 10px; font-weight: bold; white-space: nowrap; transition: all 0.2s;
        }
        .btn-save { background: transparent; border: 1px solid #10B981; color: #10B981; }
        .btn-save:hover { background: #10B981; color: #fff; }
        .btn-del { background: transparent; border: 1px solid #FF3366; color: #FF3366; }
        .btn-del:hover { background: #FF3366; color: #fff; }

        .add-form { display: flex; gap: 6px; margin-top: 4px; }
        .btn-add-inline { background: var(--accent); color: #fff; padding: 6px 12px; }
        .btn-add-inline:hover { box-shadow: 0 0 10px var(--accent-glow); }

        .grupo-actions { margin-top: 10px; text-align: right; }
        .btn-del-grupo {
            background: transparent; border: 1px solid var(--text-muted); color: var(--text-muted);
            cursor: pointer; border-radius: var(--r-sm); padding: 4px 10px; font-size: 10px;
            font-family: 'Space Mono', monospace; transition: all 0.2s;
        }
        .btn-del-grupo:hover { border-color: #FF3366; color: #FF3366; }

        .add-grupo-form {
            display: flex; gap: 10px; margin-top: 16px; padding-top: 16px;
            border-top: 1px dashed var(--border-color);
        }
        .add-grupo-input {
            flex: 1; background: var(--input-bg); border: 1px solid var(--border-color);
            color: #fff; padding: 10px; border-radius: var(--r-sm); font-family: 'Barlow'; font-size: 13px;
        }
        .add-grupo-input:focus { outline: none; border-color: var(--accent); }
        .btn-add-grupo {
            background: var(--accent); color: #fff; border: none; padding: 10px 16px;
            border-radius: var(--r-sm); font-weight: bold; cursor: pointer; font-size: 12px; transition: 0.3s;
        }
        .btn-add-grupo:hover { box-shadow: 0 0 15px var(--accent-glow); }
    </style>
</head>
<body>

<div class="container">
    <div class="header-box">
        <div>
            <h1 class="main-title">Administración de Máquinas</h1>
            <div class="sub-title">Catálogo de verificación (tipo → grupo/sede → código)</div>
        </div>
        <a href="maquinas_menu.php" class="btn-back">← VOLVER AL MENÚ</a>
    </div>

    <?php if ($mensaje): ?>
        <div class="sys-msg"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>

    <div class="warn-box">
        ⚠️ Renombrar o borrar un código aquí <strong>no mueve ni borra</strong> su historial de verificaciones ya
        guardado (carpeta <code>archivos/generados/maquinas_v2/</code>) — ese historial queda huérfano si el código
        cambia. Esta es la primera versión: agregar/editar/borrar máquinas y grupos funciona; migrar historial al
        renombrar es un pendiente para una siguiente vuelta.
    </div>

    <?php foreach ($catalogo_data as $tipo => $grupos): ?>
    <div class="tipo-section">
        <div class="tipo-title"><?= htmlspecialchars(strtoupper($tipo)) ?></div>

        <div class="grupos-grid">
            <?php foreach ($grupos as $grupo => $codigos): ?>
            <div class="grupo-card">
                <div class="grupo-card-header">
                    <span class="grupo-card-title"><?= htmlspecialchars($grupo) ?></span>
                </div>

                <ul class="ref-list">
                    <?php foreach ($codigos as $index => $codigo): ?>
                    <li class="ref-item">
                        <form method="post" class="ref-edit-form">
                            <input type="hidden" name="action" value="edit_codigo">
                            <input type="hidden" name="tipo" value="<?= htmlspecialchars($tipo) ?>">
                            <input type="hidden" name="grupo" value="<?= htmlspecialchars($grupo) ?>">
                            <input type="hidden" name="index" value="<?= $index ?>">
                            <input type="text" name="codigo" class="ref-input" value="<?= htmlspecialchars($codigo) ?>" required>
                            <button type="submit" class="btn-save">✓</button>
                        </form>
                        <form method="post" onsubmit="return confirm('¿Eliminar esta máquina de forma permanente?');" style="margin:0;">
                            <input type="hidden" name="action" value="delete_codigo">
                            <input type="hidden" name="tipo" value="<?= htmlspecialchars($tipo) ?>">
                            <input type="hidden" name="grupo" value="<?= htmlspecialchars($grupo) ?>">
                            <input type="hidden" name="index" value="<?= $index ?>">
                            <button type="submit" class="btn-del">✕</button>
                        </form>
                    </li>
                    <?php endforeach; ?>
                    <?php if (empty($codigos)): ?>
                    <li style="color:var(--text-muted); font-size:12px; font-family:'Space Mono', monospace;">Sin máquinas en este grupo.</li>
                    <?php endif; ?>
                </ul>

                <form method="post" class="add-form">
                    <input type="hidden" name="action" value="add_codigo">
                    <input type="hidden" name="tipo" value="<?= htmlspecialchars($tipo) ?>">
                    <input type="hidden" name="grupo" value="<?= htmlspecialchars($grupo) ?>">
                    <input type="text" name="nombre" class="add-input" placeholder="Nombre máquina..." required>
                    <input type="text" name="codigo_maquina" class="add-input" placeholder="Código máquina..." required>
                    <button type="submit" class="btn-add-inline">+</button>
                </form>

                <div class="grupo-actions">
                    <form method="post" onsubmit="return confirm('¿Eliminar el grupo \'<?= htmlspecialchars($grupo, ENT_QUOTES) ?>\' con las <?= count($codigos) ?> máquinas que contiene?');" style="display:inline;">
                        <input type="hidden" name="action" value="delete_grupo">
                        <input type="hidden" name="tipo" value="<?= htmlspecialchars($tipo) ?>">
                        <input type="hidden" name="grupo" value="<?= htmlspecialchars($grupo) ?>">
                        <button type="submit" class="btn-del-grupo">Borrar grupo</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <form method="post" class="add-grupo-form">
            <input type="hidden" name="action" value="add_grupo">
            <input type="hidden" name="tipo" value="<?= htmlspecialchars($tipo) ?>">
            <input type="text" name="nuevo_grupo" class="add-grupo-input" placeholder="Nombre del grupo nuevo (ej: BALANZAS_ZC)..." required>
            <button type="submit" class="btn-add-grupo">+ Agregar grupo</button>
        </form>
    </div>
    <?php endforeach; ?>
</div>

</body>
</html>
