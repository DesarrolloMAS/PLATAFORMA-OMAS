<?php
require '../sesion.php';

// Validar credenciales de administrador (Roles '1' y 'adm' autorizados)
if (!isset($_SESSION['rol']) || ($_SESSION['rol'] != '1' && $_SESSION['rol'] != 'adm')) {
    die("Acceso Denegado. Solo administradores pueden configurar el catálogo.");
}

$catalogo_file = "../../archivos/generados/empaque_v2/catalogo_referencias.json";

if (!file_exists($catalogo_file)) {
    die("Catálogo no encontrado. Ingrese primero a la galería para inicializarlo.");
}

$catalogo_data = json_decode(file_get_contents($catalogo_file), true);

$mensaje = '';

// Procesamiento de peticiones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $zona = $_POST['zona'] ?? '';
    
    if (isset($catalogo_data[$zona])) {
        if ($action === 'add') {
            $nuevo_producto = trim($_POST['nuevo_producto'] ?? '');
            if ($nuevo_producto !== '') {
                $catalogo_data[$zona][] = $nuevo_producto;
                $mensaje = "Referencia añadida con éxito a $zona.";
            }
        } elseif ($action === 'delete') {
            $index = $_POST['index'] ?? -1;
            if (isset($catalogo_data[$zona][$index])) {
                array_splice($catalogo_data[$zona], $index, 1);
                $mensaje = "Referencia eliminada con éxito de $zona.";
            }
        }
        
        // Guardar cambios
        file_put_contents($catalogo_file, json_encode($catalogo_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración de Catálogo - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700&family=Space+Mono:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #0B0E14;
            --panel-bg: #151A22;
            --accent: #FF3366; /* Rojo Admin */
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

        .container { max-width: 1000px; margin: 0 auto; }

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
            padding: 15px; border-radius: var(--r-md); margin-bottom: 30px; font-weight: bold;
            font-family: 'Space Mono', monospace; font-size: 13px; text-align: center;
        }

        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }

        .zone-card {
            background: var(--panel-bg); border: 1px solid var(--border-color);
            padding: 25px; border-radius: var(--r-md);
        }

        .zone-title {
            font-size: 18px; font-weight: 700; color: var(--accent); border-bottom: 1px dashed var(--border-color);
            padding-bottom: 15px; margin-bottom: 20px;
        }

        .ref-list {
            list-style: none; display: flex; flex-direction: column; gap: 10px; margin-bottom: 25px;
        }

        .ref-item {
            display: flex; justify-content: space-between; align-items: center;
            background: var(--input-bg); padding: 10px 15px; border-radius: var(--r-sm);
            border: 1px solid rgba(255,255,255,0.05); font-size: 14px;
        }

        .btn-del {
            background: transparent; border: 1px solid #FF3366; color: #FF3366;
            cursor: pointer; border-radius: var(--r-sm); padding: 4px 8px; font-size: 11px;
            font-weight: bold; transition: all 0.2s;
        }
        .btn-del:hover { background: #FF3366; color: #fff; }

        .add-form {
            display: flex; gap: 10px;
        }

        .add-input {
            flex: 1; background: var(--input-bg); border: 1px solid var(--border-color);
            color: #fff; padding: 10px; border-radius: var(--r-sm); font-family: 'Barlow'; font-size: 14px;
        }
        .add-input:focus { outline: none; border-color: var(--accent); }

        .btn-add {
            background: var(--accent); color: #fff; border: none; padding: 10px 15px;
            border-radius: var(--r-sm); font-weight: bold; cursor: pointer; transition: 0.3s;
        }
        .btn-add:hover { box-shadow: 0 0 15px var(--accent-glow); }

        @media (max-width: 768px) {
            .grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header-box">
        <div>
            <h1 class="main-title">Administración de Catálogo</h1>
            <div class="sub-title">Control global de referencias de empaques V2</div>
        </div>
        <a href="galeria_empaques_v2.php" class="btn-back">← VOLVER A GALERÍA</a>
    </div>

    <?php if ($mensaje): ?>
        <div class="sys-msg"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>

    <div class="grid">
        <?php foreach ($catalogo_data as $zona => $referencias): ?>
        <div class="zone-card">
            <h2 class="zone-title">ZONA <?= htmlspecialchars($zona) ?> (<?= count($referencias) ?> refs)</h2>
            
            <ul class="ref-list">
                <?php foreach ($referencias as $index => $ref): ?>
                <li class="ref-item">
                    <span><?= htmlspecialchars($ref) ?></span>
                    <form method="post" onsubmit="return confirm('¿Eliminar referencia de forma permanente?');" style="margin:0;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="zona" value="<?= htmlspecialchars($zona) ?>">
                        <input type="hidden" name="index" value="<?= $index ?>">
                        <button type="submit" class="btn-del">BORRAR</button>
                    </form>
                </li>
                <?php endforeach; ?>
            </ul>

            <form method="post" class="add-form">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="zona" value="<?= htmlspecialchars($zona) ?>">
                <input type="text" name="nuevo_producto" class="add-input" placeholder="Nombre de nueva referencia..." required>
                <button type="submit" class="btn-add">+</button>
            </form>
        </div>
        <?php endforeach; ?>
    </div>
</div>

</body>
</html>
