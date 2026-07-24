<?php
require '../conection.php';
require '../sesion.php';
verificarAutenticacion();

$sede = $_SESSION['sede'];
$configFile = "../../archivos/generados/termohigrometros/config_ubicaciones_{$sede}.json";
$ubicaciones = [];

if (file_exists($configFile)) {
    $ubicaciones = json_decode(file_get_contents($configFile), true) ?: [];
} else {
    // Generar algunas por defecto si no hay o crear array vacío
    $ubicaciones = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Termohigrómetros</title>
    <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;600&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0f1e2e;
            --surface: rgba(15,30,46,0.75);
            --surface2: #1c2e42;
            --border: rgba(116,154,187,0.3);
            --accent: #6ee7b7; /* Mint */
            --text: #e8f4f3;
            --text-muted: rgba(208,233,231,0.6);
            --danger: #f87171;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'Barlow', sans-serif;
            text-align: center;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        body::before {
            content:'';position:fixed;inset:0;
            background:
                radial-gradient(ellipse 80% 50% at 15% 20%,rgba(116,154,187,0.12) 0%,transparent 60%),
                radial-gradient(ellipse 40% 60% at 50% 100%,rgba(44,74,110,0.3) 0%,transparent 60%);
            pointer-events:none;z-index:0;
        }

        .header {
            background: var(--surface);
            border-bottom: 2px solid var(--accent);
            padding: 25px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            z-index: 10;
        }

        .header h1 {
            font-family: 'Space Mono', monospace;
            font-size: 22px;
            letter-spacing: 2px;
            color: var(--accent);
            text-transform: uppercase;
        }

        .btn-volver {
            color: var(--text-muted);
            text-decoration: none;
            font-family: 'Space Mono', monospace;
            font-size: 13px;
            border: 1px dashed var(--border);
            padding: 8px 15px;
            border-radius: 2px;
            transition: all 0.3s;
        }

        .btn-volver:hover {
            border-color: var(--accent);
            color: var(--accent);
            background: rgba(110, 231, 183, 0.05);
        }

        .container {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 20px;
            flex-grow: 1;
            position: relative;
            z-index: 10;
        }

        .status-hero {
            background: var(--surface);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border);
            border-left: 4px solid var(--accent);
            padding: 20px;
            margin-bottom: 40px;
            text-align: left;
            border-radius: 4px;
        }

        .status-hero h2 {
            font-family: 'Space Mono', monospace;
            font-size: 16px;
            color: var(--text);
            font-weight: normal;
        }

        .grid-ubicaciones {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .ubi-card {
            background: var(--surface2);
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 30px 20px;
            text-decoration: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: all 0.3s;
        }

        .ubi-card:hover {
            border-color: var(--accent);
            transform: translateY(-4px);
            box-shadow: 0 5px 20px rgba(110, 231, 183, 0.2);
            background: rgba(110, 231, 183, 0.05);
        }

        .ubi-icon {
            font-size: 30px;
            margin-bottom: 15px;
        }

        .ubi-title {
            font-family: 'Space Mono', monospace;
            font-size: 15px;
            font-weight: 700;
            color: var(--text);
            text-transform: uppercase;
        }

        .ubi-card:hover .ubi-title { color: var(--accent); }

        .links-extra {
            margin-top: 60px;
            padding-top: 30px;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .btn-secundario {
            display: inline-block;
            padding: 12px 25px;
            background: var(--surface2);
            color: var(--text);
            text-decoration: none;
            font-family: 'Space Mono', monospace;
            font-size: 12px;
            text-transform: uppercase;
            border: 1px solid var(--border);
            border-radius: 4px;
            transition: all 0.3s;
        }

        .btn-secundario:hover {
            border-color: var(--accent);
            color: var(--accent);
            box-shadow: 0 0 15px rgba(110, 231, 183, 0.1);
        }

        .empty-state {
            padding: 40px;
            background: var(--surface2);
            border: 1px dashed var(--danger);
            border-radius: 6px;
            color: var(--text-muted);
        }
    </style>
</head>
<body>

<div class="header">
    <h1>Termohigrómetros</h1>
    <a class="btn-volver" href="../menu_almacen.html">&lt; MENÚ ALMACÉN</a>
</div>

<div class="container">
    <div class="status-hero">
        <h2>CENTRO DE MEDICIÓN | ZONA: <strong style="color:var(--accent);"><?= htmlspecialchars($sede) ?></strong></h2>
        <p style="font-size: 13px; color: var(--text-muted); margin-top: 5px; font-family: 'Space Mono', monospace;">
            Seleccione la ubicación preestablecida para registrar la medición de temperatura y humedad a la hora correspondiente.
        </p>
    </div>

    <?php if (empty($ubicaciones)): ?>
        <div class="empty-state">
            <h3>No hay ubicaciones configuradas.</h3>
            <p style="margin-top:10px;">Diríjase a "Configurar Ubicaciones" para crear los almacenes/bodegas correspondientes a esta zona.</p>
        </div>
    <?php else: ?>
        <div class="grid-ubicaciones">
            <?php foreach ($ubicaciones as $ubi): ?>
                <a href="termohigrometros.php?ubicacion=<?= urlencode($ubi['id']) ?>" class="ubi-card">
                    <div class="ubi-icon">🌡️</div>
                    <div class="ubi-title"><?= htmlspecialchars($ubi['nombre']) ?></div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="links-extra">
        <a href="rev_termo.php" class="btn-secundario" style="border-color: #38bdf8; color: #38bdf8;">Ver Historial / Registros</a>
        <a href="gestion_ubicaciones.php" class="btn-secundario" style="border-color: var(--accent); color: var(--accent);">Configurar Ubicaciones</a>
    </div>
</div>

</body>
</html>
