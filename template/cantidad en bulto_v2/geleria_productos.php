<?php
require '../sesion.php';
verificarAutenticacion();
$sede = $_SESSION['sede'];
if (!in_array($sede, ['ZC', 'ZS'])) $sede = 'ZC';

$config_file = "../../archivos/generados/molienda/config_{$sede}.json";
$harinas = [];
$subproductos = [];

if (file_exists($config_file)) {
    $config = json_decode(file_get_contents($config_file), true);
    foreach (($config['harinas'] ?? []) as $item) {
        $harinas[] = ['nombre' => $item['name'], 'peso' => $item['weight']];
    }
    foreach (($config['subproductos'] ?? []) as $item) {
        $subproductos[] = ['nombre' => $item['name'], 'peso' => $item['weight']];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galería de Productos — Control Cantidad en Bulto</title>
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
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 16px;
        }

        .producto-card {
            background: var(--panel-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--r-md);
            padding: 20px 16px;
            text-align: center;
            display: flex;
            flex-direction: column;
            gap: 10px;
            transition: all 0.3s;
            cursor: pointer;
            text-decoration: none;
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
        .producto-peso {
            color: var(--text-muted);
            font-size: 12px;
            font-family: 'Space Mono', monospace;
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
        }
        .btn-seleccionar:hover { background: #fff; }

        .section-divider {
            grid-column: 1 / -1;
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 10px 0 4px;
        }
        .section-divider-line {
            flex: 1;
            height: 1px;
            background: var(--border-color);
        }
        .section-divider-label {
            font-family: 'Space Mono', monospace;
            font-size: 10px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 2px;
            white-space: nowrap;
        }
    </style>
</head>
<body>
<div class="container">

    <!-- HEADER -->
    <div class="header-box">
        <div>
            <div class="main-title">Galería de Productos</div>
            <div class="sub-title">Control Cantidad en Bulto &nbsp;|&nbsp; Sede: <?= htmlspecialchars($sede) ?></div>
        </div>
        <div class="header-actions">
            <a href="rev_cantidad_bulto.php" class="btn-rev">📋 Ver Revisiones</a>
            <a href="../menu_produccion.html" class="btn-secondary">← Volver</a>
        </div>
    </div>

    <!-- BÚSQUEDA -->
    <div class="search-bar">
        <input type="text" class="search-input" id="searchInput"
               placeholder="Buscar producto..."
               oninput="filtrarProductos(this.value)">
    </div>

    <!-- GRID DE PRODUCTOS -->
    <div class="grid" id="gridProductos">

        <?php foreach ($harinas as $producto): ?>
            <div class="producto-card" data-section="harinas"
                 data-nombre="<?= htmlspecialchars(strtolower($producto['nombre'])) ?>">
                <div class="producto-nombre"><?= htmlspecialchars($producto['nombre']) ?></div>
                <div class="producto-peso"><?= $producto['peso'] ?> kg / bulto</div>
                <button class="btn-seleccionar"
                    onclick="window.location.href='cantidad_bulto.php?harina=<?= urlencode($producto['nombre']) ?>&peso=<?= $producto['peso'] ?>'">
                    SELECCIONAR
                </button>
            </div>
        <?php endforeach; ?>

        <?php if (!empty($subproductos)): ?>
        <div class="section-divider" id="dividerSubproductos">
            <div class="section-divider-line"></div>
            <span class="section-divider-label">Subproductos</span>
            <div class="section-divider-line"></div>
        </div>
        <?php endif; ?>

        <?php foreach ($subproductos as $producto): ?>
            <div class="producto-card" data-section="subproductos"
                 data-nombre="<?= htmlspecialchars(strtolower($producto['nombre'])) ?>">
                <div class="producto-nombre"><?= htmlspecialchars($producto['nombre']) ?></div>
                <div class="producto-peso"><?= $producto['peso'] ?> kg / bulto</div>
                <button class="btn-seleccionar"
                    onclick="window.location.href='cantidad_bulto.php?harina=<?= urlencode($producto['nombre']) ?>&peso=<?= $producto['peso'] ?>'">
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
        const divider = document.getElementById('dividerSubproductos');
        if (divider) {
            const anyVisible = [...document.querySelectorAll('.producto-card[data-section="subproductos"]')]
                .some(c => !c.classList.contains('hidden'));
            divider.style.display = anyVisible ? '' : 'none';
        }
    }
</script>
</body>
</html>
