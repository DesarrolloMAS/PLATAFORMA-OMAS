<?php
require '../sesion.php';
verificarAutenticacion();
$sede = $_SESSION['sede'];
if (!in_array($sede, ['ZC', 'ZS', 'ZB'])) $sede = 'ZC';

$productos_zc = [
    'Mogolla'                => 'Empaque Galeras Mogolla',
    'Salvado'                => 'Empaque Galeras Salvado',
    'Fuerte x25'             => 'Empaque Galeras Letra Naranja X25',
    'Natural x50'            => 'Empaque Galeras Letra Verde X50',
    'Harina de Centeno'      => 'Empaque Galeras Multi Beige X25',
    'Exclusiva x50'          => 'Empaque Galeras Letra Cafe X50',
    'Artesanal x50'          => 'Empaque Galeras Letra Roja X50',
    'Artesanal x25'          => 'Empaque Galeras Papel Kraft X25',
    'Extrapan x50'           => 'Empaque Extrapan X50',
    'Extrapan x25'           => 'Empaque Extrapan x25',
    'Extrapan x10'           => 'Empaque Extrapan Laminado x10',
    'Extrapan x11.4'         => 'Empaque Extrapan x11.4',
    'Segunda'                => 'Empaque Galeras Segunda',
    'Fuerte de Exportación'  => 'Empaque Harina Fuerte de Exportación',
    'Especial x50'           => 'Empaque Galeras Letra Azul X50',
    'Especial x25'           => 'Empaque Galeras Letra Naranja X25',
    'Harina T1 x50'          => 'Empaque Galeras Letra Verde X50',
    'Harina Integral'        => 'Empaque Galeras Multi Beige X25',
    'Grano entero fino'      => 'Empaque Galeras Multi Beige X25',
    'Trigo entero'           => 'Empaque Galeras Multi Beige X25',
    'Manitoba'               => 'Empaque Galeras Letra Naranja X25',
    'Centeno Pepa'           => 'Empaque Galeras Multi Beige X25'
];

$productos_zs = [
    'Mogolla'               => 'Empaque Galeras Mogolla',
    'Salvado'               => 'Empaque Galeras Salvado',
    'Extrapan x50'          => 'Empaque Extrapan x50',
    'Extrapan x25'          => 'Empaque Extrapan x25',
    'Extrapan x10'          => 'Empaque Extrapan x10',
    'Artesanal x50'         => 'Empaque Galeras Rojo x50',
    'Natural x50'           => 'Empaque Galeras Verde x50',
    'Exclusiva x50'         => 'Empaque Galeras Cafe x50',
    'Especial x50'          => 'Empaque Galeras Azul x50',
    'Harina Fuerte x50'     => 'Empaque Galeras Naranja x50',
    'Artesanal Kraft x50'   => 'Empaque Galeras Kraft x50',
    'Harina Integral'       => 'Empaque Galeras Biege x50',
    'Segunda'               => 'Empaque Galeras Segunda'
];

$productos = ($sede === 'ZS') ? $productos_zs : $productos_zc;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galería de Productos — Envasado V2</title>
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
            --input-bg: #0F172A;
            --warning: #FFB000;
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

        .container { max-width: 1100px; margin: 0 auto; }

        .header-box {
            background: var(--panel-bg);
            border: 1px solid var(--border-color);
            border-left: 4px solid var(--accent);
            padding: 28px 30px;
            border-radius: var(--r-md);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            position: relative;
            overflow: hidden;
        }

        .header-box::before {
            content: "SELECCIONAR PRODUCTO";
            position: absolute; top: -10px; right: 20px;
            background: var(--accent); color: var(--bg-color);
            font-family: 'Space Mono', monospace; font-size: 10px; font-weight: 700;
            padding: 4px 12px; border-radius: var(--r-sm);
            box-shadow: 0 0 10px var(--accent-glow);
        }

        .main-title { font-size: 22px; font-weight: 700; color: #fff; text-transform: uppercase; margin-bottom: 4px; }
        .sub-title   { color: var(--text-muted); font-size: 13px; font-family: 'Space Mono', monospace; }

        .header-actions { display: flex; gap: 12px; flex-shrink: 0; }

        .btn-secondary {
            background: var(--input-bg);
            border: 1px solid var(--border-color);
            color: var(--text-muted);
            padding: 10px 18px;
            border-radius: var(--r-sm);
            font-family: 'Space Mono', monospace;
            text-decoration: none;
            font-size: 12px;
            transition: all 0.3s;
            white-space: nowrap;
        }
        .btn-secondary:hover { border-color: var(--accent); color: var(--accent); }

        .btn-rev {
            background: rgba(0, 240, 255, 0.1);
            border: 1px solid rgba(0, 240, 255, 0.3);
            color: var(--accent);
            padding: 10px 18px;
            border-radius: var(--r-sm);
            font-family: 'Space Mono', monospace;
            text-decoration: none;
            font-size: 12px;
            font-weight: 700;
            transition: all 0.3s;
            white-space: nowrap;
        }
        .btn-rev:hover { background: var(--accent); color: var(--bg-color); box-shadow: 0 0 15px var(--accent-glow); }

        .search-bar { margin-bottom: 24px; }
        .search-input {
            width: 100%;
            background: var(--panel-bg);
            border: 1px solid var(--border-color);
            color: var(--text-main);
            padding: 12px 16px;
            border-radius: var(--r-sm);
            font-family: 'Barlow', sans-serif;
            font-size: 14px;
            transition: all 0.3s;
        }
        .search-input:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 3px rgba(0,240,255,0.1); }
        .search-input::placeholder { color: var(--text-muted); }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 16px;
        }

        .producto-card {
            background: var(--panel-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--r-md);
            padding: 20px 16px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            transition: all 0.3s;
        }
        .producto-card:hover {
            border-color: var(--accent);
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 240, 255, 0.1);
        }
        .producto-card.hidden { display: none; }

        .producto-nombre {
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            line-height: 1.3;
        }

        .producto-empaque {
            color: var(--warning);
            font-size: 11px;
            font-family: 'Space Mono', monospace;
            line-height: 1.3;
        }

        .btn-seleccionar {
            background: var(--accent);
            color: var(--bg-color);
            border: none;
            padding: 8px 0;
            border-radius: var(--r-sm);
            font-family: 'Space Mono', monospace;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 0 10px var(--accent-glow);
            width: 100%;
            margin-top: auto;
        }
        .btn-seleccionar:hover { background: #fff; }
    </style>
</head>
<body>
<div class="container">

    <div class="header-box">
        <div>
            <div class="main-title">Galería de Productos</div>
            <div class="sub-title">Línea de Envasado V2 &nbsp;|&nbsp; Sede: <?= htmlspecialchars($sede) ?></div>
        </div>
        <div class="header-actions">
            <a href="rev_envasado_v2.php" class="btn-rev">📋 Ver Revisiones</a>
            <a href="../menu_produccion.html" class="btn-secondary">← Volver</a>
        </div>
    </div>

    <div class="search-bar">
        <input type="text" class="search-input" id="searchInput"
               placeholder="Buscar producto..."
               oninput="filtrarProductos(this.value)">
    </div>

    <div class="grid" id="gridProductos">
        <?php foreach ($productos as $producto => $empaque): ?>
            <div class="producto-card"
                 data-nombre="<?= htmlspecialchars(strtolower($producto)) ?>">
                <div class="producto-nombre"><?= htmlspecialchars($producto) ?></div>
                <div class="producto-empaque"><?= htmlspecialchars($empaque) ?></div>
                <button class="btn-seleccionar"
                    onclick="window.location.href='envasado_v2.php?harina=<?= urlencode($producto) ?>&empaque=<?= urlencode($empaque) ?>'">
                    SELECCIONAR
                </button>
            </div>
        <?php endforeach; ?>
    </div>

</div>
<script>
    function filtrarProductos(query) {
        const q = query.toLowerCase().trim();
        document.querySelectorAll('.producto-card').forEach(card => {
            const nombre = card.dataset.nombre || '';
            card.classList.toggle('hidden', q !== '' && !nombre.includes(q));
        });
    }
</script>
</body>
</html>
