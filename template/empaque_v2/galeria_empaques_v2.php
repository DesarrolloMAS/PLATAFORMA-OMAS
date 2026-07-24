<?php
require '../sesion.php';

if (!isset($_SESSION['nombre'])) {
    header('Location: ../../index.php');
    exit;
}

$sede = $_SESSION['sede'] ?? '';

// Archivo maestro de catálogo dinámico
$catalogo_file = "../../archivos/generados/empaque_v2/catalogo_referencias.json";

if (!file_exists(dirname($catalogo_file))) {
    mkdir(dirname($catalogo_file), 0777, true);
}

// Generación inicial sembrada si el archivo no existe
if (!file_exists($catalogo_file)) {
    $catalogo_default = [
        'ZC' => [
            'Mogolla', 'Salvado', 'Empaque Extrapan x50', 'Empaque Extrapan x25', 
            'Empaque Extrapan x10', 'Segunda', 'Empaque Galeras Rojo X50', 
            'Empaque Galeras Verde X50', 'Empaque Galeras Cafe X50', 
            'Empaque Galeras Cafe X25', 'Empaque Galeras Azul X50', 
            'Empaque Galeras Naranja X25', 'Empaque Galeras Kraft X25', 
            'Empaque Galeras Multi Beige X25', 'Empaque Fuerte de Exportacion'
        ],
        'ZS' => [
            'Mogolla', 'Salvado', 'Centeno', 'Empaque Extrapan x50', 
            'Empaque x25', 'Empaque x10', 'Segunda', 'Empaque Galeras Rojo X50', 
            'Empaque Galeras Verde X50', 'Empaque Galeras Cafe X50', 
            'Empaque Galeras Azul X50', 'Empaque Galeras Naranja X25', 
            'Empaque Galeras Kraft X25', 'Empaque Galeras Multi Beige X50'
        ]
    ];
    file_put_contents($catalogo_file, json_encode($catalogo_default, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

$catalogo_data = json_decode(file_get_contents($catalogo_file), true);

// Renderizar el catálogo según la sede. Si no es ZC, damos por defecto ZS u otra llave disponible.
$zona_catalogo = isset($catalogo_data[$sede]) ? $sede : 'ZS';
$productos = $catalogo_data[$zona_catalogo] ?? [];

// Validación estricta para botón admin (Asumiendo que rol 1 y rol 'adm' tienen privilegios)
$es_admin = (isset($_SESSION['rol']) && ($_SESSION['rol'] == '1' || $_SESSION['rol'] == 'adm'));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galería de Referencias - V2 JSON</title>
    <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700&family=Space+Mono:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #0B0E14;
            --panel-bg: #151A22;
            --accent: #00F0FF;
            --accent-glow: rgba(0, 240, 255, 0.4);
            --accent-hover: #00D1DF;
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
            background-image: linear-gradient(rgba(0, 240, 255, 0.03) 1px, transparent 1px),
                              linear-gradient(90deg, rgba(0, 240, 255, 0.03) 1px, transparent 1px);
            background-size: 30px 30px;
        }

        .container { max-width: 1200px; margin: 0 auto; }

        .header-box {
            background: var(--panel-bg);
            border: 1px solid var(--border-color);
            border-left: 4px solid var(--accent);
            padding: 30px;
            border-radius: var(--r-md);
            margin-bottom: 40px;
            position: relative;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header-box h1 { font-size: 24px; font-weight: 700; color: #fff; text-transform: uppercase; margin-bottom: 8px; }
        .header-box p { color: var(--text-muted); font-size: 14px; }
        .btn-back {
            background: transparent; border: 1px solid var(--accent); color: var(--accent);
            padding: 8px 16px; border-radius: var(--r-sm); font-family: 'Space Mono', monospace;
            text-decoration: none; font-size: 12px; transition: all 0.3s;
        }
        .btn-back:hover { background: var(--accent-glow); box-shadow: 0 0 15px var(--accent-glow); }

        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }

        .product-card {
            background: var(--panel-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--r-md);
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .product-card:hover {
            border-color: rgba(0, 240, 255, 0.4);
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 240, 255, 0.1);
        }

        .product-card::before {
            content: ""; position: absolute; top: 0; left: 0; width: 4px; height: 100%;
            background: var(--accent); opacity: 0; transition: opacity 0.3s;
        }
        .product-card:hover::before { opacity: 1; }

        .product-title {
            font-size: 17px; font-weight: 600; color: #fff; margin-bottom: 20px;
            line-height: 1.3; flex-grow: 1;
        }

        .action-area form { display: flex; flex-direction: column; gap: 12px; }

        .inputs-hidden {
            display: none;
            flex-direction: column;
            gap: 12px;
            animation: fadeIn 0.3s ease forwards;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .custom-input {
            background: var(--input-bg); border: 1px solid var(--border-color);
            color: var(--text-main); padding: 10px 12px;
            border-radius: var(--r-sm); font-family: 'Barlow', sans-serif; font-size: 13px;
            width: 100%; transition: all 0.3s;
        }
        .custom-input:focus {
            outline: none; border-color: var(--accent); box-shadow: 0 0 0 2px rgba(0, 240, 255, 0.1);
        }

        .btn-select {
            background: var(--accent); color: var(--bg-color); border: none;
            padding: 10px; font-size: 13px; font-weight: 700;
            font-family: 'Space Mono', monospace; border-radius: var(--r-sm);
            cursor: pointer; text-transform: uppercase; width: 100%;
            transition: all 0.3s ease;
        }
        .btn-select:hover {
            background: #fff; box-shadow: 0 0 15px var(--accent-glow);
        }

        .btn-continue {
            background: #10B981; color: #000; border: none;
            padding: 10px; font-size: 13px; font-weight: 700;
            font-family: 'Space Mono', monospace; border-radius: var(--r-sm);
            cursor: pointer; text-transform: uppercase; width: 100%;
            transition: all 0.3s ease;
            box-shadow: 0 0 10px rgba(16, 185, 129, 0.3);
        }
        .btn-continue:hover {
            background: #34D399; box-shadow: 0 0 20px rgba(16, 185, 129, 0.5);
        }

    </style>
</head>
<body>

<div class="container">
    <div class="header-box">
        <div>
            <h1>Catálogo de Referencias V2</h1>
            <p>Selecciona el tipo de empaque para iniciar el diligenciamiento</p>
        </div>
        <div style="display: flex; gap: 15px; align-items: center;">
            <?php if ($es_admin): ?>
                <a href="admin_catalogo_empaques.php" style="background: var(--danger); border: none; color: #fff; padding: 10px 16px; border-radius: var(--r-sm); font-family: 'Space Mono', monospace; text-decoration: none; font-size: 13px; font-weight: bold; box-shadow: 0 0 15px var(--danger-glow); transition: all 0.3s; display: flex; align-items: center; gap: 8px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20a8 8 0 1 0 0-16 8 8 0 0 0 0 16Z"/><path d="M12 14a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>
                    CONFIGURAR CATÁLOGO
                </a>
            <?php endif; ?>
            <a href="../menu_almacen.html" class="btn-back">← VOLVER AL MENÚ</a>
        </div>
    </div>

    <div class="gallery-grid">
        <?php foreach ($productos as $producto): ?>
            <div class="product-card">
                <div class="product-title"><?= htmlspecialchars($producto) ?></div>
                <div class="action-area">
                    <form action="empaque_v2.html" method="get" onsubmit="return validateForm(this);">
                        <input type="hidden" name="producto" value="<?= htmlspecialchars($producto) ?>">
                        
                        <div class="inputs-hidden">
                            <input type="text" name="lote" placeholder="Lote de producción" class="custom-input lote-input">
                            <input type="number" name="cantidad_empaques" placeholder="Cantidad de empaques" class="custom-input">
                            <button type="submit" class="btn-continue">CONTINUAR AL FORMATO</button>
                        </div>
                        
                        <button type="button" class="btn-select" onclick="expandCard(this)">SELECCIONAR</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
    function expandCard(btn) {
        // Cerrar todos los demás previamente abiertos
        document.querySelectorAll('.inputs-hidden').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.btn-select').forEach(el => el.style.display = 'block');
        
        const form = btn.closest('form');
        const hiddenArea = form.querySelector('.inputs-hidden');
        
        btn.style.display = 'none';
        hiddenArea.style.display = 'flex';
        form.querySelector('.lote-input').focus();
    }

    function validateForm(form) {
        const lote = form.querySelector('.lote-input').value.trim();
        if (!lote) {
            alert('❌ El Lote de producción es obligatorio.');
            return false;
        }
        return true;
    }
</script>

</body>
</html>
