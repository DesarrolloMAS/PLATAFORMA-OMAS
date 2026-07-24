<?php
require '../sesion.php';

if (!isset($_SESSION['nombre']) || empty($_SESSION['sede'])) {
    header('Location: ../../index.php');
    exit;
}

$sede = $_SESSION['sede'];
$target_dir = "../../archivos/generados/empaque_v2/" . preg_replace('/[^A-Za-z0-9_-]/', '', $sede) . "/";

$archivos = [];
if (file_exists($target_dir)) {
    $files = scandir($target_dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        if (strtolower(pathinfo($file, PATHINFO_EXTENSION)) === 'json') {
            
            // Extracción amigable del texto
            $lote_empaque = str_replace(['EMPAQUE_LOTE_', '.json'], '', $file); // MP-xxxxx
            $mod_time = filemtime($target_dir . $file);
            
            // Inspección del archivo para extraer metadatos maestros
            $content = json_decode(file_get_contents($target_dir . $file), true) ?: [];
            
            // Filtrar registros rápidos para que no cuenten como operaciones
            $operaciones_reales = array_filter($content, function($r) {
                return ($r['datos']['tipo_registro'] ?? '') !== 'rapido';
            });
            
            $registros_totales = count($operaciones_reales);
            $last_record = !empty($operaciones_reales) ? end($operaciones_reales) : end($content);
            
            $producto      = $last_record['datos']['producto_envasar'] ?? 'Sin producto especificado';
            $lote_producto = $last_record['datos']['lote_producto']   ?? '—';
            $nombre_emp    = $last_record['datos']['nombre_empaque']   ?? '—';
            
            $archivos[] = [
                'filename'      => $file,
                'lote'          => $lote_empaque,   // lote del empaque (MP-xxxxx)
                'lote_producto' => $lote_producto,  // lote del producto (último registro)
                'nombre_emp'    => $nombre_emp,
                'producto'      => $producto,
                'registros'     => $registros_totales,
                'timestamp'     => $mod_time,
                'fecha_mod'     => date('d M Y - H:i', $mod_time)
            ];
        }
    }
    // Ordenar: Los modificados más recientemente arriba
    usort($archivos, function($a, $b) {
        return $b['timestamp'] <=> $a['timestamp'];
    });
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Supervisión - Empaques V2</title>
    <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700&family=Space+Mono:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #0B0E14;
            --panel-bg: #151A22;
            --accent: #00F0FF;
            --accent-glow: rgba(0, 240, 255, 0.4);
            --text-main: #E2E8F0;
            --text-muted: #94A3B8;
            --border-color: #1E293B;
            --danger: #FF3366;
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
                radial-gradient(circle at top right, rgba(0, 240, 255, 0.05), transparent 40%),
                linear-gradient(rgba(0, 240, 255, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0, 240, 255, 0.03) 1px, transparent 1px);
            background-size: 100% 100%, 30px 30px, 30px 30px;
        }

        .container { max-width: 1200px; margin: 0 auto; }

        .header-box {
            background: var(--panel-bg);
            border: 1px solid var(--border-color);
            border-left: 4px solid var(--accent);
            padding: 30px;
            border-radius: var(--r-md);
            margin-bottom: 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            position: relative;
        }

        .header-box::before {
            content: "REVISOR JSON";
            position: absolute; top: -10px; right: 20px;
            background: var(--accent); color: var(--bg-color);
            font-family: 'Space Mono', monospace; font-size: 10px; font-weight: 700;
            padding: 4px 12px; border-radius: var(--r-sm);
            box-shadow: 0 0 10px var(--accent-glow);
        }

        .main-title { font-size: 24px; font-weight: 700; color: #fff; text-transform: uppercase; margin-bottom: 4px; }
        .sub-title { color: var(--text-muted); font-size: 14px; font-family: 'Space Mono', monospace; }

        .btn-back {
            background: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-main);
            padding: 10px 20px; border-radius: var(--r-sm); font-family: 'Space Mono', monospace;
            text-decoration: none; font-size: 13px; transition: all 0.3s;
        }
        .btn-back:hover { border-color: var(--accent); color: var(--accent); background: rgba(0, 240, 255, 0.05); }

        .stats-banner {
            display: flex; gap: 20px; margin-bottom: 30px;
        }
        .stat-card {
            background: var(--panel-bg); border: 1px solid var(--border-color);
            padding: 15px 25px; border-radius: var(--r-md); flex: 1;
            display: flex; flex-direction: column; justify-content: center;
        }
        .stat-val { font-size: 24px; font-weight: 700; color: var(--accent); font-family: 'Space Mono', monospace; }
        .stat-label { font-size: 12px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 20px;
        }

        .file-card {
            background: var(--panel-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--r-md);
            padding: 25px;
            display: flex;
            flex-direction: column;
            gap: 15px;
            transition: all 0.3s ease;
            position: relative;
            text-decoration: none;
        }

        .file-card:hover {
            border-color: rgba(0, 240, 255, 0.4);
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 240, 255, 0.1);
        }

        .lote-badge {
            align-self: flex-start;
            background: rgba(0, 240, 255, 0.1);
            color: var(--accent);
            border: 1px solid rgba(0, 240, 255, 0.2);
            padding: 4px 10px;
            border-radius: var(--r-sm);
            font-family: 'Space Mono', monospace;
            font-size: 11px;
            font-weight: 700;
        }

        .file-title {
            color: #fff;
            font-size: 18px;
            font-weight: 600;
            line-height: 1.3;
        }

        .file-meta {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-top: auto;
            border-top: 1px dashed var(--border-color);
            padding-top: 15px;
        }

        .meta-line {
            display: flex; justify-content: space-between; align-items: center;
            font-size: 13px; color: var(--text-muted);
        }

        .meta-val {
            color: #fff; font-family: 'Space Mono', monospace; font-size: 12px;
        }

        .btn-view {
            background: transparent;
            color: var(--accent);
            border: 1px solid var(--accent);
            padding: 10px;
            border-radius: var(--r-sm);
            text-align: center;
            font-family: 'Space Mono', monospace;
            font-weight: 700;
            font-size: 12px;
            margin-top: 5px;
            transition: all 0.3s;
        }

        .file-card:hover .btn-view {
            background: var(--accent);
            color: var(--bg-color);
            box-shadow: 0 0 15px var(--accent-glow);
        }

        .empty-state {
            grid-column: 1 / -1;
            background: var(--panel-bg); border: 1px dashed var(--border-color);
            padding: 60px 20px; border-radius: var(--r-md);
            text-align: center; color: var(--text-muted);
        }
        .empty-state h3 { color: #fff; margin-bottom: 10px; }
    </style>
</head>
<body>

<div class="container">
    <div class="header-box">
        <div>
            <h1 class="main-title">Consolidado de Empaques</h1>
            <div class="sub-title">Sede Operativa: [ <?= htmlspecialchars($sede) ?> ]</div>
        </div>
        <a href="../revisiones_almacen.html" class="btn-back">← Menú de Revisiones</a>
    </div>

    <div class="stats-banner">
        <div class="stat-card">
            <div class="stat-val"><?= count($archivos) ?></div>
            <div class="stat-label">Lotes Registrados</div>
        </div>
        <div class="stat-card">
            <?php 
                $total_operaciones = array_sum(array_column($archivos, 'registros'));
            ?>
            <div class="stat-val"><?= $total_operaciones ?></div>
            <div class="stat-label">Operaciones de Empaque Totales</div>
        </div>
    </div>

    <div class="grid">
        <?php if (empty($archivos)): ?>
            <div class="empty-state">
                <h3>Sin Registros!</h3>
                <p>Aún no se han generado registros JSON de empaques para la sede <?= htmlspecialchars($sede) ?>.</p>
            </div>
        <?php else: ?>
            <?php foreach ($archivos as $doc): ?>
                            <a href="visor_empaque_v2.php?file=<?= urlencode($doc['filename']) ?>" class="file-card">
                    <div class="lote-badge">EMPAQUE: <?= htmlspecialchars($doc['lote']) ?></div>
                    <div class="file-title"><?= htmlspecialchars($doc['nombre_emp']) ?></div>
                    <div class="file-meta">
                        <div class="meta-line"><span>Producto a envasar:</span> <span class="meta-val"><?= htmlspecialchars($doc['producto']) ?></span></div>
                        <div class="meta-line"><span>Último lote producto:</span> <span class="meta-val"><?= htmlspecialchars($doc['lote_producto']) ?></span></div>
                        <div class="meta-line"><span>Operaciones guardadas:</span> <span class="meta-val"><?= $doc['registros'] ?> registros</span></div>
                        <div class="meta-line"><span>Última actualización:</span> <span class="meta-val"><?= $doc['fecha_mod'] ?></span></div>
                    </div>
                    <div class="btn-view">INSPECCIONAR DOCUMENTO</div>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
