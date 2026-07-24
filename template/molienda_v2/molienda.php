<?php
require '../conection.php';
require '../sesion.php';
verificarAutenticacion();

$sede = $_SESSION['sede'];
$nombre_usuario = $_SESSION['nombre'];

// Función para obtener usuarios con cargos específicos
function obtenerUsuariosAlmacen($pdo) {
    try {
        $cargos = ["Lider de almacen", "Auxiliar de almacen", "Almacenista"];
        $placeholders = implode(',', array_fill(0, count($cargos), '?'));
        $sql = "SELECT nombre_u FROM usuarios WHERE Cargo IN ($placeholders) ORDER BY nombre_u ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($cargos);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        return [];
    }
}

$usuarios_almacen = obtenerUsuariosAlmacen($pdoUsuarios);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control de Molienda - MO-PG-PD-FO-002</title>
    <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;600&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0a0b10;
            --surface: #141620;
            --surface2: #1c1f2e;
            --border: #2d324a;
            --accent: #00f2ff; /* Cyan Cyberpunk */
            --accent2: #7000ff; /* Purple */
            --text: #e0e6ed;
            --text-muted: #7a8599;
            --danger: #ff0055;
            --success: #00ff88;
        }

        .flex-grow { flex: 1; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'Barlow', sans-serif;
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* Scanline Effect */
        body::before {
            content: " ";
            position: fixed;
            top: 0; left: 0; bottom: 0; right: 0;
            background: linear-gradient(rgba(18, 16, 16, 0) 50%, rgba(0, 0, 0, 0.1) 50%), linear-gradient(90deg, rgba(255, 0, 0, 0.03), rgba(0, 255, 0, 0.01), rgba(0, 0, 255, 0.03));
            z-index: 1000;
            background-size: 100% 4px, 3px 100%;
            pointer-events: none;
        }

        .header {
            background: var(--surface);
            border-bottom: 2px solid var(--accent);
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 0 20px rgba(0, 242, 255, 0.1);
        }

        .header-title h1 {
            font-family: 'Space Mono', monospace;
            font-size: 18px;
            letter-spacing: 2px;
            color: var(--accent);
            text-transform: uppercase;
        }

        .header-meta {
            text-align: right;
            font-family: 'Space Mono', monospace;
            font-size: 11px;
            color: var(--text-muted);
        }

        .container {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .section-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 4px;
            margin-bottom: 30px;
            padding: 25px;
            position: relative;
        }

        .section-card::before {
            content: "";
            position: absolute;
            top: -1px; left: -1px;
            width: 10px; height: 10px;
            border-top: 2px solid var(--accent);
            border-left: 2px solid var(--accent);
        }

        .section-title {
            font-family: 'Space Mono', monospace;
            font-size: 12px;
            font-weight: 700;
            color: var(--accent);
            text-transform: uppercase;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title::after {
            content: "";
            height: 1px;
            flex: 1;
            background: var(--border);
        }

        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            font-family: 'Space Mono', monospace;
            font-size: 11px;
            color: var(--text-muted);
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .form-control {
            width: 100%;
            background: var(--surface2);
            border: 1px solid var(--border);
            color: var(--text);
            padding: 10px 15px;
            font-size: 14px;
            border-radius: 2px;
            outline: none;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        .form-control:focus {
            border-color: var(--accent);
            box-shadow: 0 0 10px rgba(0, 242, 255, 0.1);
        }

        .form-control[readonly] {
            opacity: 0.7;
            cursor: not-allowed;
        }

        /* Items List (Harinas/Subproductos) */
        .items-grid {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .item-row {
            background: var(--surface2);
            border: 1px solid var(--border);
            padding: 8px 15px;
            border-radius: 2px;
            margin-bottom: 2px;
            transition: border-color 0.3s;
        }

        .item-row:hover {
            border-color: var(--accent);
        }

        .item-main-row {
            display: flex;
            align-items: center;
            gap: 12px;
            min-height: 40px;
        }

        .item-check {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: var(--accent);
        }

        .item-name {
            font-size: 13px;
            font-weight: 600;
            color: var(--text);
            text-transform: uppercase;
            font-family: 'Space Mono', monospace;
            flex: 1;
        }

        .item-lots-container {
            display: flex;
            flex-direction: column;
            gap: 5px;
            margin-left: 30px;
            margin-top: 5px;
            opacity: 0.1;
            pointer-events: none;
            display: none; /* Hidden until active */
        }

        .item-row.active .item-lots-container {
            opacity: 1;
            pointer-events: all;
            display: flex;
        }

        .lot-input-group {
            display: flex;
            gap: 5px;
            background: rgba(255,255,255,0.01);
            padding: 4px;
            border-radius: 2px;
            align-items: center;
        }

        .input-mini {
            width: 70px;
            padding: 4px 8px;
            font-size: 12px;
        }

        .input-lote {
            width: 100px;
            padding: 4px 8px;
            font-size: 11px;
            font-family: 'Space Mono', monospace;
        }

        .item-total-display {
            text-align: right;
            font-family: 'Space Mono', monospace;
            color: var(--accent);
            font-size: 11px;
            font-weight: bold;
            min-width: 100px;
        }

        .btn-add {
            background: transparent;
            border: 1px dashed var(--border);
            color: var(--text-muted);
            padding: 10px;
            width: 100%;
            cursor: pointer;
            font-family: 'Space Mono', monospace;
            font-size: 11px;
            text-transform: uppercase;
            margin-top: 10px;
            transition: all 0.3s;
        }

        .btn-add:hover {
            border-color: var(--accent);
            color: var(--accent);
            background: rgba(0, 242, 255, 0.05);
        }

        /* Signature */
        .signature-pad {
            border: 1px solid var(--border);
            background: #fff;
            width: 100%;
            height: 150px;
            border-radius: 2px;
        }

        .actions {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 40px;
        }

        .btn {
            font-family: 'Space Mono', monospace;
            padding: 12px 30px;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 1px;
            cursor: pointer;
            border: none;
            border-radius: 2px;
            transition: all 0.3s;
        }

        .btn-submit {
            background: var(--accent);
            color: var(--bg);
            box-shadow: 0 0 15px rgba(0, 242, 255, 0.3);
        }

        .btn-submit:hover {
            box-shadow: 0 0 25px rgba(0, 242, 255, 0.5);
            transform: translateY(-2px);
        }

        /* Responsives */
        @media (max-width: 600px) {
            .grid-2 { grid-template-columns: 1fr; }
            .item-row { grid-template-columns: auto 1fr; }
            .item-inputs { grid-column: 1 / -1; }
        }
    </style>
</head>
<body>

<div class="header">
    <div class="header-title">
        <h1>Control de Molienda - <?= $sede ?></h1>
    </div>
    <div class="header-meta">
        <div>CÓDIGO: MO-PG-PD-FO-002</div>
        <div>VERSIÓN: 1</div>
        <div>ZONA: <?= $sede ?></div>
    </div>
</div>

<div class="container">
    <form id="formMolienda" action="procesar.php" method="POST">
        
        <!-- DATOS INICIALES -->
        <div class="section-card">
            <h2 class="section-title">Datos del Registro</h2>
            <div class="grid-2">
                <div class="form-group">
                    <label>Responsable</label>
                    <input type="text" class="form-control" name="responsable" value="<?= $nombre_usuario ?>" readonly>
                </div>
                <div class="form-group">
                    <label>Sede / Zona</label>
                    <input type="text" class="form-control" name="sede" value="<?= $sede ?>" readonly>
                </div>
            </div>
            <div class="grid-2">
                <div class="form-group">
                    <label>Fecha</label>
                    <input type="date" class="form-control" name="fecha" value="<?= date('Y-m-d') ?>">
                </div>
                <div class="form-group">
                    <label>Hora</label>
                    <input type="time" class="form-control" name="hora" value="<?= date('H:i') ?>">
                </div>
            </div>
            <div class="form-group">
                <label>Almacenista</label>
                <select class="form-control" name="almacenista" required>
                    <option value="">Seleccione Almacenista...</option>
                    <?php foreach ($usuarios_almacen as $user): ?>
                        <option value="<?= $user ?>"><?= $user ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group" style="margin-top: 20px;">
                <label style="display: flex; align-items: center; gap: 10px;">
                    Responsables de la Intervención
                    <button type="button" style="background: transparent; border: 1px dashed var(--accent); color: var(--accent); padding: 2px 10px; cursor: pointer; border-radius: 2px; font-weight: bold; font-size: 10px; font-family: 'Space Mono', monospace;" onclick="addResponsable()">+ AGREGAR</button>
                </label>
                <div id="responsables-container" style="display: flex; flex-direction: column; gap: 8px;">
                    <div style="display: flex; gap: 10px;">
                        <input type="text" class="form-control" name="responsables_intervencion[]" placeholder="Nombre completo">
                    </div>
                </div>
            </div>
        </div>

        <!-- HARINAS -->
        <div class="section-card">
            <h2 class="section-title">Control de Harinas</h2>
            <div class="items-grid" id="harinasGrid">
                <!-- Javascript will populate this based on sede -->
            </div>
        </div>

        <!-- SUBPRODUCTOS -->
        <div class="section-card">
            <h2 class="section-title">Subproductos</h2>
            <div class="items-grid" id="subproductosGrid">
                <!-- Javascript will populate this -->
            </div>
        </div>

        <!-- MATERIALES -->
        <div class="section-card">
            <h2 class="section-title">Materiales</h2>
            <div class="items-grid" id="materialesGrid">
                <!-- Javascript will populate this -->
            </div>
        </div>

        <!-- TRIGO MOLIDO -->
        <div class="section-card" style="border-color: rgba(112,0,255,0.4);">
            <h2 class="section-title" style="color: var(--accent2);">Trigo Molido</h2>
            <div class="items-grid" id="trigoGrid">
                <!-- Filas de trigo añadidas dinámicamente -->
            </div>
            <button type="button" class="btn-add" style="border-color: var(--accent2); color: var(--accent2); margin-top: 12px;" onclick="addTrigoRow()">
                + AGREGAR TIPO DE TRIGO
            </button>
        </div>

        <!-- FIRMA -->
        <div class="section-card">
            <h2 class="section-title">Firma del Turno (Cédula)</h2>
            <div class="form-group">
                <input type="password" class="form-control" name="cedula_firma" id="cedula_firma" placeholder="Ingrese su Cédula" required>
            </div>
        </div>

        <!-- ACCIONES -->
        <div class="actions">
            <button type="button" class="btn" style="background: var(--surface2); color: var(--text-muted);" onclick="history.back()">Cancelar</button>
            <button type="submit" class="btn btn-submit">Guardar Registro</button>
        </div>

    </form>
</div>

<script>
<?php
    // Obtener la configuración específica para la sede:
    $config_url = "../../archivos/generados/molienda/config_{$sede}.json";
    $configLocal = ['harinas' => [], 'subproductos' => [], 'materiales' => []];
    if (file_exists($config_url)) {
        $configLocal = json_decode(file_get_contents($config_url), true) ?: $configLocal;
    }
?>
    const dynamicConfig = <?= json_encode($configLocal) ?>;

    function renderItems(containerId, items, prefix) {
        const container = document.getElementById(containerId);
        container.innerHTML = ''; // Limpiar pre-renderizados
        items.forEach(item => {
            const row = document.createElement('div');
            row.className = 'item-row';
            
            row.innerHTML = `
                <div class="item-main-row">
                    <input type="checkbox" class="item-check" name="${prefix}[${item.id}][active]" onchange="toggleRow(this)">
                    <div class="item-name">${item.name}</div>
                    <div class="item-total-display"><span class="total-val">0.00</span> Kg</div>
                    <button type="button" class="btn-lot-add" onclick="addLotSlot(this, '${prefix}', '${item.id}')" title="Agregar Lote Extra">+</button>
                </div>
                <div class="item-lots-container">
                    <div class="lot-input-group">
                        <input type="number" step="0.01" class="form-control input-mini lot-val" name="${prefix}[${item.id}][lotes][0][valor]" placeholder="Cant.">
                        <input type="text" class="form-control input-lote" name="${prefix}[${item.id}][lotes][0][id]" placeholder="Lote">
                    </div>
                </div>
                <input type="hidden" name="${prefix}[${item.id}][peso_unit]" value="${item.weight}">
            `;
            container.appendChild(row);
            setupCalculation(row, item.weight);
        });
    }

    function addLotSlot(btn, prefix, itemId) {
        const row = btn.closest('.item-row');
        if (!row.classList.contains('active')) {
            alert('Active primero el producto marcando el cuadro de la izquierda.');
            return;
        }
        const container = row.querySelector('.item-lots-container');
        const currentSlots = container.querySelectorAll('.lot-input-group').length;
        
        if (currentSlots >= 4) {
            alert('Máximo 4 lotes por producto.');
            return;
        }

        const div = document.createElement('div');
        div.className = 'lot-input-group';
        div.innerHTML = `
            <input type="number" step="0.01" class="form-control input-mini lot-val" name="${prefix}[${itemId}][lotes][${currentSlots}][valor]" placeholder="Cant.">
            <input type="text" class="form-control input-lote" name="${prefix}[${itemId}][lotes][${currentSlots}][id]" placeholder="Lote ${currentSlots + 1}">
        `;
        container.appendChild(div);
        
        // Re-setup calc or just add listener
        const weight = parseFloat(row.querySelector('input[name*="[peso_unit]"]').value);
        setupCalculation(row, weight);
    }

    function setupCalculation(row, weight) {
        const lotInputs = row.querySelectorAll('.lot-val');
        const totalSpan = row.querySelector('.total-val');
        
        lotInputs.forEach(input => {
            input.oninput = () => {
                let totalBultos = 0;
                row.querySelectorAll('.lot-val').forEach(inp => totalBultos += parseFloat(inp.value) || 0);
                const totalKg = (totalBultos * weight).toFixed(2);
                totalSpan.textContent = totalKg;
            };
        });
    }

    function toggleRow(checkbox) {
        const row = checkbox.closest('.item-row');
        if (checkbox.checked) {
            row.classList.add('active');
            // Si es una harina, auto-crear fila de trigo vinculada
            if (checkbox.name.startsWith('harinas[')) {
                const idMatch = checkbox.name.match(/harinas\[([^\]]+)\]/);
                if (idMatch) addTrigoRow(idMatch[1]);
            }
            updateTrigoHarinaSelects();
        } else {
            row.classList.remove('active');
            row.querySelector('.item-lots-container').innerHTML = `
                <div class="lot-input-group">
                    <input type="number" step="0.01" class="form-control input-mini lot-val" name="${checkbox.name.replace('[active]','')}[lotes][0][valor]" placeholder="Cant.">
                    <input type="text" class="form-control input-lote" name="${checkbox.name.replace('[active]','')}[lotes][0][id]" placeholder="Lote">
                </div>
            `;
            row.querySelector('.total-val').textContent = '0.00';
            setupCalculation(row, parseFloat(row.querySelector('input[name*="[peso_unit]"]').value));
            updateTrigoHarinaSelects();
        }
    }

    // Initialize Page
    renderItems('harinasGrid', dynamicConfig.harinas || [], 'harinas');
    renderItems('subproductosGrid', dynamicConfig.subproductos || [], 'subproductos');
    renderItems('materialesGrid', dynamicConfig.materiales || [], 'materiales');

    // ── TRIGO MOLIDO ──────────────────────────────────────────────────────
    let trigoRowCount = 0;

    // Devuelve las harinas actualmente marcadas como activas
    function getActiveHarinas() {
        const result = [];
        document.querySelectorAll('#harinasGrid .item-check:checked').forEach(cb => {
            const row = cb.closest('.item-row');
            const match = cb.name.match(/harinas\[([^\]]+)\]/);
            if (match) result.push({
                id:   match[1],
                name: row.querySelector('.item-name').textContent.trim()
            });
        });
        return result;
    }

    // Genera las <option> del select de harina destino
    function getHarinaOptions(selectedId = '') {
        const harinas = getActiveHarinas();
        let opts = '<option value="">← Destino (harina)</option>';
        harinas.forEach(h => {
            opts += `<option value="${h.id}" ${h.id === selectedId ? 'selected' : ''}>${h.name}</option>`;
        });
        return opts;
    }

    // Refresca todos los selects de trigo con la lista actual de harinas activas
    function updateTrigoHarinaSelects() {
        document.querySelectorAll('.trigo-harina-select').forEach(sel => {
            const current = sel.value;
            sel.innerHTML = getHarinaOptions(current);
        });
    }

    function addTrigoRow(preselectedHarinaId = '') {
        const container = document.getElementById('trigoGrid');
        const idx = trigoRowCount++;
        const div = document.createElement('div');
        div.className = 'item-row active';
        div.style.borderColor = 'rgba(112,0,255,0.4)';
        div.innerHTML = `
            <div class="item-main-row" style="flex-wrap: wrap; gap: 10px;">
                <select
                    class="form-control trigo-harina-select"
                    name="trigo[${idx}][destino_harina]"
                    style="flex: 1.5; min-width: 150px; border-color: rgba(112,0,255,0.6); color: #c084fc; background: #0d0620;">
                    ${getHarinaOptions(preselectedHarinaId)}
                </select>
                <input type="text"
                    class="form-control"
                    name="trigo[${idx}][tipo]"
                    placeholder="Tipo de trigo"
                    style="flex: 2; min-width: 140px;">
                <input type="number" step="0.01"
                    class="form-control"
                    name="trigo[${idx}][cantidad]"
                    placeholder="Cant. (Ton)"
                    style="flex: 1; min-width: 90px;">
                <input type="text"
                    class="form-control"
                    name="trigo[${idx}][lote]"
                    placeholder="Lote"
                    style="flex: 1; min-width: 90px; font-family: 'Space Mono', monospace;">
                <button type="button"
                    onclick="this.closest('.item-row').remove()"
                    style="background:transparent; border:1px solid var(--danger); color:var(--danger); padding:4px 12px; cursor:pointer; border-radius:2px; font-weight:bold; font-size:12px;"
                    title="Eliminar fila">✕</button>
            </div>
        `;
        container.appendChild(div);
    }

    // NO se crea fila por defecto — se crean automáticamente al marcar harinas
    // ─────────────────────────────────────────────────────────────────────

    // Firma digital eliminada, validación de cédula se hará en backend

    // Responsables logic
    function addResponsable() {
        const container = document.getElementById('responsables-container');
        if (container.children.length >= 5) {
            alert('Máximo 5 responsables de intervención.');
            return;
        }
        const div = document.createElement('div');
        div.style.cssText = 'display: flex; gap: 10px;';
        div.innerHTML = `
            <input type="text" class="form-control" name="responsables_intervencion[]" placeholder="Nombre completo">
            <button type="button" onclick="this.parentElement.remove()" style="background: transparent; border: 1px solid var(--danger); color: var(--danger); padding: 0 15px; cursor: pointer; border-radius: 2px; font-weight: bold;" title="Eliminar">X</button>
        `;
        container.appendChild(div);
    }

    // Form Submission
    document.getElementById('formMolienda').addEventListener('submit', async (e) => {
        e.preventDefault(); // Evitar recarga
        
        const cedula = document.getElementById('cedula_firma').value;
        if (!cedula.trim()) {
            alert('La cédula es obligatoria para firmar el turno.');
            return;
        }

        const formData = new FormData(e.target);
        const submitBtn = e.target.querySelector('.btn-submit');
        submitBtn.disabled = true;
        submitBtn.style.opacity = '0.5';
        submitBtn.innerHTML = 'PROCESANDO...';

        try {
            const resp = await fetch('procesar.php', {
                method: 'POST',
                body: formData
            });
            
            const textResp = await resp.text();
            let data;
            try {
                data = JSON.parse(textResp);
            } catch (e) {
                console.error("Error parseando JSON. Respuesta del servidor:", textResp);
                alert("Error crítico del servidor. Respuesta inválida. Consulte la consola para ver detalles.");
                submitBtn.disabled = false;
                submitBtn.style.opacity = '1';
                submitBtn.innerHTML = 'Guardar Registro';
                return;
            }

            if (data.status === 'error') {
                alert('Error: ' + data.message);
                submitBtn.disabled = false;
                submitBtn.style.opacity = '1';
                submitBtn.innerHTML = 'Guardar Registro';
                return;
            }

            // Create Cyberpunk Modal Alert
            const modal = document.createElement('div');
            modal.style.position = 'fixed';
            modal.style.top = '0';
            modal.style.left = '0';
            modal.style.width = '100vw';
            modal.style.height = '100vh';
            modal.style.background = 'rgba(10, 11, 16, 0.95)';
            modal.style.display = 'flex';
            modal.style.alignItems = 'center';
            modal.style.justifyContent = 'center';
            modal.style.zIndex = '9999';
            modal.style.backdropFilter = 'blur(5px)';
            modal.style.opacity = '0';
            modal.style.transition = 'opacity 0.3s ease';
            
            modal.innerHTML = `
                <div style="background: var(--surface); border: 1px solid var(--accent); padding: 40px 60px; text-align: center; border-radius: 4px; box-shadow: 0 0 40px rgba(0, 242, 255, 0.15); transform: translateY(20px); transition: transform 0.3s ease;" id="cyber-modal-card">
                    <div style="width: 50px; height: 50px; border-radius: 50%; background: rgba(0,242,255,0.1); border: 2px solid var(--accent); margin: 0 auto 20px; display: flex; align-items: center; justify-content: center; color: var(--accent); font-size: 24px; font-weight: bold;">✓</div>
                    <h2 style="color: var(--text); font-family: 'Space Mono', monospace; text-transform: uppercase; letter-spacing: 2px; font-size: 18px; margin-bottom: 10px;">Registro Exitoso</h2>
                    <p style="color: var(--text-muted); font-family: 'Barlow', sans-serif; font-size: 14px;">${data.message}</p>
                    <div style="margin-top: 25px; width: 100%; height: 2px; background: var(--surface2); position: relative; overflow: hidden;">
                        <div style="position: absolute; top: 0; left: 0; height: 100%; background: var(--accent); width: 0%;" id="cyber-progress"></div>
                    </div>
                    <div style="margin-top: 10px; font-family: 'Space Mono', monospace; font-size: 10px; color: var(--accent); opacity: 0.7;">INICIANDO RETORNO...</div>
                </div>
            `;
            document.body.appendChild(modal);

            // Trigger animations
            requestAnimationFrame(() => {
                modal.style.opacity = '1';
                document.getElementById('cyber-modal-card').style.transform = 'translateY(0)';
                const bar = document.getElementById('cyber-progress');
                bar.style.transition = 'width 2s linear';
                bar.style.width = '100%';
            });

            // Redirect
            setTimeout(() => {
                window.location.href = 'index.php';
            }, 2000);

        } catch (err) {
            alert('Error crítico de red. Consulte consola.');
            console.error(err);
            submitBtn.disabled = false;
            submitBtn.style.opacity = '1';
            submitBtn.innerHTML = 'Guardar Registro';
        }
    });

</script>

</body>
</html>
