<?php
require '../sesion.php';
verificarAutenticacion();
$sede = $_SESSION['sede'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galería de Revisiones - Proceso de Molienda</title>
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
        }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'Barlow', sans-serif;
            padding: 40px;
        }

        .header {
            max-width: 1000px;
            margin: 0 auto 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid var(--accent);
            padding-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .header h1 {
            font-family: 'Space Mono', monospace;
            font-size: 20px;
            color: var(--accent);
            text-transform: uppercase;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        .month-selector {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 40px;
        }

        .month-card {
            background: var(--surface);
            border: 1px solid var(--border);
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            font-family: 'Space Mono', monospace;
            text-transform: uppercase;
            font-size: 13px;
        }

        .month-card:hover, .month-card.active {
            border-color: var(--accent);
            background: rgba(0, 242, 255, 0.05);
            color: var(--accent);
        }

        .records-table {
            width: 100%;
            border-collapse: collapse;
            background: var(--surface);
            border: 1px solid var(--border);
        }

        .records-table th {
            text-align: left;
            padding: 15px;
            border-bottom: 1px solid var(--border);
            font-family: 'Space Mono', monospace;
            font-size: 11px;
            color: var(--text-muted);
            text-transform: uppercase;
        }

        .records-table td {
            padding: 15px;
            border-bottom: 1px solid var(--border);
            font-size: 14px;
        }

        .records-table tr:hover {
            background: var(--surface2);
            cursor: pointer;
        }

        .btn-view {
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
        }

        .btn-new {
            display: inline-block;
            padding: 10px 20px;
            background: var(--accent);
            color: var(--bg);
            text-decoration: none;
            font-family: 'Space Mono', monospace;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            border-radius: 2px;
        }

        .empty-state {
            text-align: center;
            padding: 60px;
            color: var(--text-muted);
            font-family: 'Space Mono', monospace;
        }

        .back-link {
            color: var(--text-muted);
            text-decoration: none;
            font-size: 13px;
            font-family: 'Space Mono', monospace;
        }

        .back-link:hover { color: var(--accent); }
    </style>
</head>
<body>

<div class="header">
    <h1>Galería de Revisiones: Proceso de Molienda <?= htmlspecialchars($sede) ?></h1>
    <div style="display:flex; gap:15px; align-items:center;">
        <a href="index.html" class="btn-new">+ NUEVO REGISTRO</a>
        <a href="../redireccion.php" class="back-link">&lt; VOLVER</a>
    </div>
</div>

<div class="container">
    <div class="month-selector" id="monthSelector">
        <!-- Los meses se cargan aquí -->
    </div>

    <div id="recordsContainer">
        <div class="empty-state">Seleccione un mes para ver los registros</div>
    </div>
</div>

<script>
    async function init() {
        const resp = await fetch('listar_jsons.php');
        const files = await resp.json();

        const selector = document.getElementById('monthSelector');
        if (files.length === 0) {
            selector.innerHTML = '<div class="empty-state">No se encontraron registros históricos</div>';
            return;
        }

        files.forEach(file => {
            const card = document.createElement('div');
            card.className = 'month-card';
            card.textContent = file.display;
            card.onclick = () => loadMonth(file.path, card);
            selector.appendChild(card);
        });

        selector.firstChild.click();
    }

    async function loadMonth(filePath, selectedCard) {
        document.querySelectorAll('.month-card').forEach(c => c.classList.remove('active'));
        selectedCard.classList.add('active');

        const container = document.getElementById('recordsContainer');
        container.innerHTML = '<div class="empty-state">Cargando registros...</div>';

        const sede = '<?= htmlspecialchars($sede) ?>';
        const resp = await fetch(`../../archivos/generados/proceso_v2/${sede}/${filePath}`);
        const records = await resp.json();

        if (!records || records.length === 0) {
            container.innerHTML = '<div class="empty-state">No hay registros en este mes</div>';
            return;
        }

        let html = `
            <div style="margin-bottom: 20px; text-align: right;">
                <a href="visor_proceso_v2.php?file=${filePath}" class="btn-new">📄 VER DOCUMENTO COMPLETO DEL MES</a>
            </div>
            <table class="records-table">
                <thead>
                    <tr>
                        <th>Fecha / Hora</th>
                        <th>Líder de Turno</th>
                        <th>Referencia Producto</th>
                        <th>Registrado por</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
        `;

        records.slice().reverse().forEach(rec => {
            const d = rec.datos || {};
            html += `
                <tr onclick="window.location.href='visor_proceso_v2.php?file=${filePath}&id=${rec.id_registro}'">
                    <td>${d.fecha || '—'} ${d.hora_inicio ? '| ' + d.hora_inicio : ''}</td>
                    <td style="color: var(--accent); font-weight: bold;">${d.lider_turno || '—'}</td>
                    <td>${d.referencia_producto || '—'}</td>
                    <td>${rec.usuario_sys || '—'}</td>
                    <td><a href="visor_proceso_v2.php?file=${filePath}&id=${rec.id_registro}" class="btn-view">VER EN DOCUMENTO →</a></td>
                </tr>
            `;
        });

        html += '</tbody></table>';
        container.innerHTML = html;
    }

    init();
</script>

</body>
</html>
