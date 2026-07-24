<?php
require '../sesion.php';
verificarAutenticacion();

$sedeSeleccionada = $_GET['sede'] ?? $_SESSION['sede'];
if (!in_array($sedeSeleccionada, ['ZC', 'ZS'])) {
    $sedeSeleccionada = 'ZC';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración de Ítems - Molienda V2</title>
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

        .header h1 {
            font-family: 'Space Mono', monospace;
            font-size: 20px;
            color: var(--accent);
        }

        .btn-volver {
            color: var(--text-muted); text-decoration: none; font-family: 'Space Mono', monospace;
            font-size: 13px; border: 1px dashed var(--border); padding: 8px 15px;
        }

        .container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }

        .tabs { display: flex; gap: 10px; margin-bottom: 20px; }
        .tab-btn {
            background: var(--surface); color: var(--text-muted); font-family: 'Space Mono', monospace;
            border: 1px solid var(--border); padding: 10px 20px; cursor: pointer; text-transform: uppercase;
        }
        .tab-btn.active { border-color: var(--accent); color: var(--accent); background: rgba(0,242,255,0.05); }

        .category-section { display: none; }
        .category-section.active { display: block; }

        .item-list {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 4px;
            padding: 20px;
        }

        .item-row {
            display: grid;
            grid-template-columns: 2fr 3fr 1fr auto auto;
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
        .btn-save { background: var(--accent); color: var(--bg); font-weight: bold; font-size: 16px; border:none; padding:15px; width: 100%; margin-top: 40px; }
        
        .zone-switch { display: flex; justify-content: center; gap: 20px; margin-bottom: 30px; }
        .zone-switch a {
            padding: 10px 30px; border: 1px solid var(--border); text-decoration: none; color: var(--text); font-family: monospace; font-size:16px;
        }
        .zone-switch a.active { border-color: var(--accent); background: var(--accent); color: var(--bg); font-weight: bold; }

    </style>
</head>
<body>

<div class="header">
    <h1>Configuración Dinámica de Insumos</h1>
    <a href="index.php" class="btn-volver">&lt; PANEL</a>
</div>

<div class="container">
    <div class="zone-switch">
        <a href="?sede=ZC" class="<?= $sedeSeleccionada === 'ZC' ? 'active' : '' ?>">ZONA CENTRO (ZC)</a>
        <a href="?sede=ZS" class="<?= $sedeSeleccionada === 'ZS' ? 'active' : '' ?>">ZONA SUR (ZS)</a>
    </div>

    <div class="tabs">
        <button class="tab-btn active" onclick="switchTab('harinas', this)">Harinas</button>
        <button class="tab-btn" onclick="switchTab('subproductos', this)">Subproductos</button>
        <button class="tab-btn" onclick="switchTab('materiales', this)">Materiales</button>
    </div>

    <!-- HARINAS -->
    <div id="sec-harinas" class="category-section active">
        <div class="item-list">
            <div class="item-row header-row">
                <div>ID Único Interno</div>
                <div>Nombre Corto (Visual)</div>
                <div>Peso (Kg)</div>
                <div></div>
            </div>
            <div id="list-harinas"></div>
            <button class="btn btn-add" onclick="addItem('harinas')">+ AGREGAR HARINA</button>
        </div>
    </div>

    <!-- SUBPRODUCTOS -->
    <div id="sec-subproductos" class="category-section">
        <div class="item-list">
            <div class="item-row header-row">
                <div>ID Único Interno</div>
                <div>Nombre Corto (Visual)</div>
                <div>Peso (Kg)</div>
                <div></div>
            </div>
            <div id="list-subproductos"></div>
            <button class="btn btn-add" onclick="addItem('subproductos')">+ AGREGAR SUBPRODUCTO</button>
        </div>
    </div>

    <!-- MATERIALES -->
    <div id="sec-materiales" class="category-section">
        <div class="item-list">
            <div class="item-row header-row">
                <div>ID Único Interno</div>
                <div>Nombre Corto (Visual)</div>
                <div>Peso Relativo (Normalmente 1)</div>
                <div></div>
            </div>
            <div id="list-materiales"></div>
            <button class="btn btn-add" onclick="addItem('materiales')">+ AGREGAR MATERIAL</button>
        </div>
    </div>

    <button class="btn btn-save" onclick="guardarConfig()">GUARDAR Y APLICAR CAMBIOS EN ZONA <?= $sedeSeleccionada ?></button>
</div>

<script>
    let configData = { harinas: [], subproductos: [], materiales: [] };
    const sedeActual = '<?= $sedeSeleccionada ?>';

    async function loadConfig() {
        try {
            const resp = await fetch('api_productos.php?sede=' + sedeActual);
            const json = await resp.json();
            if (json.status === 'ok') {
                configData = json.data;
                renderList('harinas');
                renderList('subproductos');
                renderList('materiales');
            }
        } catch (e) {
            console.error('Error cargando configuración', e);
        }
    }

    function renderList(category) {
        const container = document.getElementById('list-' + category);
        container.innerHTML = '';
        configData[category].forEach((item, index) => {
            const row = document.createElement('div');
            row.className = 'item-row';
            row.innerHTML = `
                <input type="text" class="form-control" value="${item.id}" onchange="updateItem('${category}', ${index}, 'id', this.value)" placeholder="ejem_identificador">
                <input type="text" class="form-control" value="${item.name}" onchange="updateItem('${category}', ${index}, 'name', this.value)" placeholder="NOMBRE A MOSTRAR">
                <input type="number" step="0.01" class="form-control" value="${item.weight}" onchange="updateItem('${category}', ${index}, 'weight', parseFloat(this.value))">
                <button class="btn btn-del" onclick="delItem('${category}', ${index})">✕ Eliminar</button>
            `;
            container.appendChild(row);
        });
    }

    function updateItem(category, index, field, value) {
        configData[category][index][field] = value;
    }

    function addItem(category) {
        configData[category].push({ id: `nuevo_${Date.now()}`, name: 'NUEVO ITEM', weight: 1 });
        renderList(category);
    }

    function delItem(category, index) {
        if(confirm('¿Seguro que desea eliminar este ítem? Podría afectar cómo se visualizan plantillas anteriores si se reemplaza.')){
            configData[category].splice(index, 1);
            renderList(category);
        }
    }

    async function guardarConfig() {
        document.querySelector('.btn-save').textContent = 'GUARDANDO...';
        try {
            const resp = await fetch('api_productos.php?sede=' + sedeActual, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(configData)
            });
            const result = await resp.json();
            if (result.status === 'ok') {
                alert('La configuración se guardó y aplicó a los formularios de molienda.');
            } else {
                alert('Error al guardar: ' + result.message);
            }
        } catch (e) {
            alert('Error crítico de guardado.');
        } finally {
            document.querySelector('.btn-save').textContent = 'GUARDAR Y APLICAR CAMBIOS EN ZONA ' + sedeActual;
        }
    }

    function switchTab(cat, element) {
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        element.classList.add('active');
        document.querySelectorAll('.category-section').forEach(sec => sec.classList.remove('active'));
        document.getElementById('sec-' + cat).classList.add('active');
    }

    // Inicializar
    loadConfig();
</script>

</body>
</html>
