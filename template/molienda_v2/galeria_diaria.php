<?php
require '../sesion.php';
verificarAutenticacion();

// Extracción optimizada de Fechas (Días Consolidados) por Zona
function obtenerDiasMolienda($sede) {
    $dias = [];
    $directorio = "../../archivos/generados/molienda/$sede/";
    if (!is_dir($directorio)) return [];

    $archivos = glob($directorio . "*.json");
    foreach ($archivos as $archivo) {
        $contenido = file_get_contents($archivo);
        $registros = json_decode($contenido, true);
        if ($registros && is_array($registros)) {
            foreach ($registros as $r) {
                if (isset($r['fecha'])) {
                    $dias[$r['fecha']] = true;
                }
            }
        }
    }
    
    $listaDias = array_keys($dias);
    // Ordenar cronológicamente (más nuevo arriba)
    rsort($listaDias);
    return $listaDias;
}

$diasZC = obtenerDiasMolienda('ZC');
$diasZS = obtenerDiasMolienda('ZS');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galería de Planillas - Molienda V2</title>
    <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;600&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0a0b10;
            --surface: #141620;
            --surface2: #1c1f2e;
            --border: #2d324a;
            --accent: #00f2ff;
            --text: #e0e6ed;
            --text-muted: #7a8599;
            --success: #00ff88;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'Barlow', sans-serif;
            text-align: center;
        }

        /* Scanline Effect */
        body::before {
            content: " ";
            position: fixed;
            top: 0; left: 0; bottom: 0; right: 0;
            background: linear-gradient(rgba(18, 16, 16, 0) 50%, rgba(0, 0, 0, 0.1) 50%);
            z-index: 1000;
            background-size: 100% 4px;
            pointer-events: none;
        }

        .header {
            background: var(--surface);
            border-bottom: 2px solid var(--accent);
            padding: 25px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 0 20px rgba(0, 242, 255, 0.1);
        }

        .header h1 {
            font-family: 'Space Mono', monospace;
            font-size: 20px;
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
        }

        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }

        @media (max-width: 800px) {
            .container { grid-template-columns: 1fr; }
        }

        .zona-column {
            background: var(--surface);
            border: 1px solid var(--border);
            padding: 30px;
            border-radius: 4px;
            position: relative;
        }

        .zona-column::before {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 2px;
            background: linear-gradient(90deg, transparent, var(--success), transparent);
        }

        .zona-title {
            font-family: 'Space Mono', monospace;
            font-size: 24px;
            font-weight: bold;
            color: var(--text);
            margin-bottom: 30px;
            letter-spacing: 1px;
            text-shadow: 0 0 10px rgba(255,255,255,0.1);
        }

        .grid-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 15px;
        }

        .day-card {
            background: var(--surface2);
            border: 1px solid var(--border);
            padding: 20px;
            text-decoration: none;
            color: var(--text);
            border-radius: 4px;
            transition: all 0.3s;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .day-card:hover {
            border-color: var(--accent);
            background: rgba(0, 242, 255, 0.05);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 242, 255, 0.15);
        }

        .day-icon {
            width: 40px;
            height: 40px;
            margin-bottom: 10px;
            color: var(--accent);
        }

        .day-text {
            font-family: 'Space Mono', monospace;
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .day-subtext {
            font-size: 11px;
            color: var(--text-muted);
            margin-top: 5px;
            text-transform: uppercase;
        }

        .empty-state {
            padding: 40px;
            color: var(--text-muted);
            font-family: 'Space Mono', monospace;
            font-size: 14px;
            border: 1px dashed var(--border);
            border-radius: 4px;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>Galería de Planillas Consolidadas</h1>
    <a class="btn-volver" href="index.php">&lt; VOLVER</a>
</div>

<div class="container">
    <!-- COLUMNA ZONA CENTRO -->
    <div class="zona-column">
        <div class="zona-title">ZONA CENTRO (ZC)</div>
        <?php if (empty($diasZC)): ?>
            <div class="empty-state">No hay planillas generadas aún.</div>
        <?php else: ?>
            <div class="grid-cards">
                <?php foreach ($diasZC as $dia): ?>
                    <a href="plantilla_diaria.php?fecha=<?= $dia ?>&sede=ZC" class="day-card" target="_blank">
                        <!-- File Icon -->
                        <svg class="day-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <span class="day-text"><?= $dia ?></span>
                        <span class="day-subtext">Ver Consolidado</span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- COLUMNA ZONA SUR -->
    <div class="zona-column">
        <div class="zona-title">ZONA SUR (ZS)</div>
        <?php if (empty($diasZS)): ?>
            <div class="empty-state">No hay planillas generadas aún.</div>
        <?php else: ?>
            <div class="grid-cards">
                <?php foreach ($diasZS as $dia): ?>
                    <a href="plantilla_diaria.php?fecha=<?= $dia ?>&sede=ZS" class="day-card" target="_blank">
                        <!-- File Icon -->
                        <svg class="day-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <span class="day-text"><?= $dia ?></span>
                        <span class="day-subtext">Ver Consolidado</span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
