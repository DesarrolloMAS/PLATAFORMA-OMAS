<?php
require '../../conection.php';
require '../../sesion.php';
verificarAutenticacion();
$sede = $_SESSION['sede'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revisiones Productos No Conformes</title>
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
            margin: 0; padding: 0;
            line-height: 1.6;
        }

        .header {
            background: var(--surface);
            padding: 20px 40px;
            border-bottom: 2px solid var(--accent);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            font-family: 'Space Mono', monospace;
            font-size: 20px;
            color: var(--accent);
            text-transform: uppercase;
            margin: 0;
        }

        .container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }

        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 4px;
            padding: 20px;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
        }

        .card:hover {
            border-color: var(--accent);
            box-shadow: 0 0 15px rgba(0,242,255,0.2);
            transform: translateY(-5px);
        }

        .card::before {
            content: "";
            position: absolute;
            top: -1px; left: -1px;
            width: 8px; height: 8px;
            border-top: 2px solid var(--accent);
            border-left: 2px solid var(--accent);
        }

        .card-icon {
            font-size: 30px;
            color: var(--accent);
            margin-bottom: 15px;
        }

        .card-title {
            font-family: 'Space Mono', monospace;
            font-weight: 700;
            font-size: 16px;
            margin-bottom: 5px;
        }

        .card-date {
            font-size: 12px;
            color: var(--text-muted);
        }
        
        .no-records {
            text-align: center;
            font-family: 'Space Mono', monospace;
            color: var(--text-muted);
            grid-column: 1 / -1;
            padding: 50px;
            border: 1px dashed var(--border);
        }
    </style>
</head>
<body>

<div class="header">
    <div>
        <h1>Galería de Reportes PNC - <?= htmlspecialchars($sede) ?></h1>
        <a href="../../menu_adm_calidad.html" style="color:var(--accent); text-decoration:none; font-family:'Space Mono', monospace; font-size:12px; margin-top:5px; display:inline-block; transition:0.3s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='var(--accent)'">← VOLVER AL MENÚ CALIDAD</a>
    </div>
    <span style="font-family:'Space Mono', monospace; font-size:12px; color:var(--text-muted);">ZONA: <?= htmlspecialchars($sede) ?></span>
</div>

<div class="container">
    <div class="gallery" id="galleryContainer">
        <!-- Generado por JS -->
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        fetch('listar_jsons.php')
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById('galleryContainer');
                if (data.length === 0) {
                    container.innerHTML = '<div class="no-records">NO EXISTEN REGISTROS DE PRODUCTO NO CONFORME AÚN</div>';
                    return;
                }

                data.forEach(file => {
                    const monthRaw = file.nombre.replace('.json', ''); // ex: 2026-04
                    const card = document.createElement('div');
                    card.className = 'card';
                    card.onclick = () => window.location.href = `visor_PNC.php?file=${file.nombre}`;
                    
                    card.innerHTML = `
                        <div class="card-icon">📄</div>
                        <div class="card-title">Reporte ${monthRaw}</div>
                        <div class="card-date">Última mod: ${file.fecha_mod}</div>
                    `;
                    container.appendChild(card);
                });
            })
            .catch(error => console.error('Error cargando JSONs:', error));
    });
</script>

</body>
</html>
