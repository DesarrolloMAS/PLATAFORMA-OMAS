<?php
require '../sesion.php';
verificarAutenticacion();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración de Productos — Control Familiar</title>
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
            --danger: #ff0055;
            --success: #00ff88;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background: var(--bg); color: var(--text); font-family: 'Barlow', sans-serif; }

        .header {
            background: var(--surface);
            border-bottom: 2px solid var(--accent);
            padding: 25px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 { font-family: 'Space Mono', monospace; font-size: 20px; color: var(--accent); }
        .btn-volver {
            color: var(--text-muted); text-decoration: none; font-family: 'Space Mono', monospace;
            font-size: 13px; border: 1px dashed var(--border); padding: 8px 15px;
        }

        .container { max-width: 900px; margin: 40px auto; padding: 0 20px; }

        .item-list { background: var(--surface); border: 1px solid var(--border); border-radius: 4px; padding: 20px; }

        .item-row {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 15px;
            padding: 15px;
            border-bottom: 1px dashed var(--border);
            align-items: center;
        }
        .item-row.header-row {
            font-family: 'Space Mono', monospace;
            text-transform: uppercase;
            font-size: 12px;
            color: var(--text-muted);
            border-bottom: 1px solid var(--border);
        }

        .form-control {
            background: var(--bg); color: var(--text); border: 1px solid var(--border);
            padding: 10px; border-radius: 2px; font-family: 'Space Mono', monospace; font-size: 13px; width: 100%;
        }

        .btn {
            background: var(--surface2); color: var(--text); border: 1px solid var(--border);
            padding: 10px 15px; cursor: pointer; font-family: 'Space Mono', monospace; border-radius: 2px;
        }
        .btn-add { border-color: var(--success); color: var(--success); width: 100%; margin-top: 20px; }
        .btn-del { border-color: var(--danger); color: var(--danger); }
        .btn-save { background: var(--accent); color: var(--bg); font-weight: bold; font-size: 16px; border: none; padding: 15px; width: 100%; margin-top: 40px; }
    </style>
</head>
<body>

<div class="header">
    <h1>Productos — Control Familiar (Zona Sur)</h1>
    <a href="geleria_productos.php" class="btn-volver">&lt; GALERÍA</a>
</div>

<div class="container">
    <div class="item-list">
        <div class="item-row header-row">
            <div>Nombre del Producto</div>
            <div></div>
        </div>
        <div id="list-productos"></div>
        <button class="btn btn-add" onclick="addItem()">+ AGREGAR PRODUCTO</button>
    </div>

    <button class="btn btn-save" onclick="guardarConfig()">GUARDAR CATÁLOGO</button>
</div>

<script>
    let productos = [];

    async function loadConfig() {
        try {
            const resp = await fetch('api_productos_familiar.php');
            const json = await resp.json();
            if (json.status === 'ok') {
                productos = json.data;
                renderList();
            }
        } catch (e) {
            console.error('Error cargando catálogo', e);
        }
    }

    function renderList() {
        const container = document.getElementById('list-productos');
        container.innerHTML = '';
        productos.forEach((nombre, index) => {
            const row = document.createElement('div');
            row.className = 'item-row';
            row.innerHTML = `
                <input type="text" class="form-control" value="${nombre.replace(/"/g, '&quot;')}"
                    onchange="updateItem(${index}, this.value)" placeholder="Nombre del producto">
                <button class="btn btn-del" onclick="delItem(${index})">✕ Eliminar</button>
            `;
            container.appendChild(row);
        });
    }

    function updateItem(index, value) {
        productos[index] = value;
    }

    function addItem() {
        productos.push('NUEVO PRODUCTO');
        renderList();
    }

    function delItem(index) {
        if (confirm('¿Seguro que desea eliminar este producto? Podría afectar cómo se ve el catálogo si ya se usó en registros anteriores.')) {
            productos.splice(index, 1);
            renderList();
        }
    }

    async function guardarConfig() {
        const btn = document.querySelector('.btn-save');
        btn.textContent = 'GUARDANDO...';
        try {
            const resp = await fetch('api_productos_familiar.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(productos)
            });
            const result = await resp.json();
            if (result.status === 'ok') {
                productos = result.data;
                renderList();
                alert('El catálogo se guardó y aplicó a la galería de Control Familiar.');
            } else {
                alert('Error al guardar: ' + result.message);
            }
        } catch (e) {
            alert('Error crítico de guardado.');
        } finally {
            btn.textContent = 'GUARDAR CATÁLOGO';
        }
    }

    loadConfig();
</script>

</body>
</html>
