<?php
require '../conection.php';
require '../sesion.php';
verificarAutenticacion();

$sede = $_SESSION['sede'];
$configFile = "../../archivos/generados/termohigrometros/config_ubicaciones_{$sede}.json";
$ubicaciones = [];

if (file_exists($configFile)) {
    $ubicaciones = json_decode(file_get_contents($configFile), true) ?: [];
}

// Procesar Guardado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    if ($accion === 'agregar') {
        $nombre = strtoupper(trim($_POST['nombre'] ?? ''));
        if ($nombre) {
            $id = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $nombre));
            $ubicaciones[] = ['id' => $id, 'nombre' => $nombre];
            file_put_contents($configFile, json_encode($ubicaciones, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
    } elseif ($accion === 'eliminar') {
        $id_eliminar = $_POST['id'] ?? '';
        $ubicaciones = array_filter($ubicaciones, function($u) use ($id_eliminar) {
            return $u['id'] !== $id_eliminar;
        });
        // Re-indexar
        $ubicaciones = array_values($ubicaciones);
        file_put_contents($configFile, json_encode($ubicaciones, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    // Recargar para evitar reenvío de formulario
    header("Location: gestion_ubicaciones.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Ubicaciones</title>
    <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;600&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0f1e2e;
            --surface: rgba(15,30,46,0.75);
            --surface2: #1c2e42;
            --border: rgba(116,154,187,0.3);
            --accent: #6ee7b7;
            --text: #e8f4f3;
            --text-muted: rgba(208,233,231,0.6);
            --danger: #f87171;
            --danger-hover: #ef4444;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'Barlow', sans-serif;
            padding: 40px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .container {
            width: 100%;
            max-width: 600px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }

        h2 {
            font-family: 'Space Mono', monospace;
            color: var(--accent);
            margin-bottom: 25px;
            text-align: center;
            font-size: 20px;
            text-transform: uppercase;
        }

        .form-add {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
        }

        .form-add input {
            flex: 1;
            padding: 12px 15px;
            background: var(--surface2);
            border: 1px solid var(--border);
            color: var(--text);
            border-radius: 4px;
            font-family: 'Space Mono', monospace;
            font-size: 13px;
        }

        .form-add input:focus {
            outline: none;
            border-color: var(--accent);
        }

        .btn {
            background: var(--accent);
            color: #000;
            border: none;
            padding: 0 20px;
            font-family: 'Space Mono', monospace;
            font-weight: 700;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn:hover { background: #34d399; }

        .btn-danger {
            background: rgba(248, 113, 113, 0.1);
            color: var(--danger);
            border: 1px dashed var(--danger);
        }

        .btn-danger:hover {
            background: var(--danger);
            color: #fff;
        }

        .lista {
            list-style: none;
        }

        .lista li {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background: var(--surface2);
            border: 1px solid var(--border);
            margin-bottom: 10px;
            border-radius: 4px;
            font-family: 'Space Mono', monospace;
            font-size: 14px;
        }

        .btn-volver {
            display: inline-block;
            margin-top: 30px;
            color: var(--text-muted);
            text-decoration: none;
            font-family: 'Space Mono', monospace;
            font-size: 13px;
        }
        .btn-volver:hover { color: var(--text); }

    </style>
</head>
<body>

<div class="container">
    <h2>Ubicaciones Termohigrómetros</h2>
    
    <form method="POST" class="form-add">
        <input type="hidden" name="accion" value="agregar">
        <input type="text" name="nombre" placeholder="Nombre (Ej: Bodega Central)" required autocomplete="off">
        <button type="submit" class="btn">AÑADIR</button>
    </form>

    <ul class="lista">
        <?php foreach ($ubicaciones as $ubi): ?>
            <li>
                <span><?= htmlspecialchars($ubi['nombre']) ?></span>
                <form method="POST" style="margin:0;">
                    <input type="hidden" name="accion" value="eliminar">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($ubi['id']) ?>">
                    <button type="submit" class="btn btn-danger" style="padding: 6px 12px; font-size:11px;" onclick="return confirm('¿Eliminar esta ubicación?')">ELIMINAR</button>
                </form>
            </li>
        <?php endforeach; ?>
        <?php if(empty($ubicaciones)): ?>
            <li style="justify-content:center; color:var(--text-muted);">No hay ubicaciones creadas.</li>
        <?php endif; ?>
    </ul>

    <div style="text-align: center;">
        <a href="index.php" class="btn-volver">&lt; Volver al Panel</a>
    </div>
</div>

</body>
</html>
