<?php
require '../sesion.php';
verificarAutenticacion();
require_once '../area_operativa_lib.php';
// Lista maestra de productos: la consulta cualquiera, la modifican solo
// 'adm' o rol 1 de producción (ver puedeGestionarProductos()).
$puedeEditar = puedeGestionarProductos();

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
<style>
    .aviso-lectura { margin: 0 0 18px; padding: 12px 16px; border: 1px dashed #f2b134; border-radius: 6px; color: #f2b134; font-size: 13px; line-height: 1.5; }
    .aviso-lectura--info { border-color: rgba(0, 229, 255, 0.35); color: #9fb3c8; }
    .form-control[readonly] { opacity: 0.6; cursor: not-allowed; }
    .btn-add[hidden] { display: none; }
</style>
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

    <?php if (!$puedeEditar): ?>
    <div class="aviso-lectura">👁 Modo consulta — solo administradores o rol 1 del área de producción pueden modificar esta lista.</div>
    <?php else: ?>
    <div class="aviso-lectura aviso-lectura--info">Esta es la lista maestra de productos: la usan molienda, el cronograma de producción y los formatos de envasado y control de empaque. El <strong>ID</strong> de un producto ya guardado no se puede cambiar, porque lo usan los registros; el nombre y el peso sí.</div>
    <?php endif; ?>

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
            <button class="btn btn-add" <?= $puedeEditar ? '' : 'hidden' ?> onclick="addItem('harinas')">+ AGREGAR HARINA</button>
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
            <button class="btn btn-add" <?= $puedeEditar ? '' : 'hidden' ?> onclick="addItem('subproductos')">+ AGREGAR SUBPRODUCTO</button>
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
            <button class="btn btn-add" <?= $puedeEditar ? '' : 'hidden' ?> onclick="addItem('materiales')">+ AGREGAR MATERIAL</button>
        </div>
    </div>

    <?php if ($puedeEditar): ?>
    <button class="btn btn-save" onclick="guardarConfig()">GUARDAR Y APLICAR CAMBIOS EN ZONA <?= $sedeSeleccionada ?></button>
    <?php endif; ?>
</div>

<script>
    let configData = { harinas: [], subproductos: [], materiales: [] };
    const sedeActual = '<?= $sedeSeleccionada ?>';
    const PUEDE_EDITAR = <?= json_encode($puedeEditar) ?>;
    // IDs que ya estaban guardados al cargar: quedan de solo lectura, porque
    // los registros (molienda, cronograma, envasado, empaque) los referencian.
    let idsGuardados = new Set();
    const esc = v => String(v ?? '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;');

    async function loadConfig() {
        try {
            const resp = await fetch('api_productos.php?sede=' + sedeActual);
            const json = await resp.json();
            if (json.status === 'ok') {
                configData = json.data;
                idsGuardados = new Set(['harinas', 'subproductos', 'materiales'].flatMap(c => (configData[c] || []).map(i => c + ':' + i.id)));
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
            const idBloqueado = !PUEDE_EDITAR || idsGuardados.has(category + ':' + item.id);
            const bloqueo = PUEDE_EDITAR ? '' : 'disabled';
            row.innerHTML = `
                <input type="text" class="form-control" value="${esc(item.id)}" onchange="updateItem('${category}', ${index}, 'id', this.value)" placeholder="ejem_identificador" ${idBloqueado ? 'readonly title="El ID de un producto guardado no se puede cambiar"' : ''} ${bloqueo}>
                <input type="text" class="form-control" value="${esc(item.name)}" onchange="updateItem('${category}', ${index}, 'name', this.value)" placeholder="NOMBRE A MOSTRAR" ${bloqueo}>
                <input type="number" step="0.01" class="form-control" value="${esc(item.weight)}" onchange="updateItem('${category}', ${index}, 'weight', parseFloat(this.value))" ${bloqueo}>
                ${PUEDE_EDITAR ? `<button class="btn btn-del" onclick="delItem('${category}', ${index})">✕ Eliminar</button>` : '<div></div>'}
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
                configData = result.data;
                idsGuardados = new Set(['harinas', 'subproductos', 'materiales'].flatMap(c => (configData[c] || []).map(i => c + ':' + i.id)));
                ['harinas', 'subproductos', 'materiales'].forEach(renderList);
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
