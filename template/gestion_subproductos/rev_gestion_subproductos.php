<?php
require_once '../sesion.php';
verificarAutenticacion();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galería de Revisiones - Gestión de Subproductos</title>
    <link rel="stylesheet" href="../../css/orden_mantenimiento.css">
    <style>
        .file-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 20px;
        }
        .file-card {
            background: rgba(0, 240, 255, 0.05);
            padding: 15px;
            border: 1px solid var(--border);
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .file-card:hover {
            border-color: var(--primary);
            box-shadow: 0 0 10px rgba(0, 240, 255, 0.2);
        }
        .file-info h3 {
            margin: 0 0 5px 0;
            color: var(--primary);
        }
        .file-info p {
            margin: 0;
            font-size: 0.9em;
            color: var(--text-color);
        }
        .btn-view {
            background-color: transparent;
            border: 1px solid var(--primary);
            color: var(--primary);
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
        }
        .btn-view:hover {
            background-color: var(--primary);
            color: #000;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Galería de Registros</h1>
            <p>Historial de Evaluación - Gestión de Subproductos (Sede: <?php echo $_SESSION['sede'] ?? ''; ?>)</p>
        </header>

        <div class="section-card">
            <div class="section-title">Archivos Mensuales Generados</div>
            <div id="fileContainer" class="file-list">
                <!-- Se cargará mediante JS -->
                <p>Cargando archivos...</p>
            </div>
        </div>
        
        <div style="margin-top: 20px;">
            <a href="../menu_seccion_sur.html" class="btn-view">Volver</a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            fetch('listar_jsons.php')
            .then(res => res.json())
            .then(data => {
                const container = document.getElementById('fileContainer');
                container.innerHTML = '';
                
                if(data.status === 'success' && data.archivos.length > 0) {
                    data.archivos.forEach(file => {
                        const card = document.createElement('div');
                        card.className = 'file-card';
                        
                        card.innerHTML = `
                            <div class="file-info">
                                <h3>${file.nombre}</h3>
                                <p>Última modificación: ${file.fecha_mod} | Tamaño: ${(file.tamano / 1024).toFixed(2)} KB</p>
                            </div>
                            <a href="visor_gestion_subproductos.php?file=${encodeURIComponent(file.nombre)}" class="btn-view">Ver Registros</a>
                        `;
                        container.appendChild(card);
                    });
                } else {
                    container.innerHTML = '<p>No se encontraron registros en su sede.</p>';
                }
            })
            .catch(err => {
                document.getElementById('fileContainer').innerHTML = '<p>Error al cargar el historial.</p>';
            });
        });
    </script>
</body>
</html>
