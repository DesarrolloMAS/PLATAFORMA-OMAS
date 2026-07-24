<?php
require_once '../sesion.php';
verificarAutenticacion();

// Configurar zona horaria de Colombia
date_default_timezone_set('America/Bogota');

// Obtener el nombre del usuario desde la sesión
$nombreUsuario = $_SESSION['nombre'];
$nombreCarpeta = preg_replace('/[^a-zA-Z0-9_-]/', '_', $nombreUsuario);
$dirUsuario = __DIR__ . '/guardados/' . $nombreCarpeta;

// Obtener todos los archivos JSON del usuario
$archivosJSON = [];
if (is_dir($dirUsuario)) {
    $archivos = scandir($dirUsuario);
    foreach ($archivos as $archivo) {
        if (pathinfo($archivo, PATHINFO_EXTENSION) === 'json') {
            $rutaCompleta = $dirUsuario . '/' . $archivo;
            $archivosJSON[] = [
                'nombre' => $archivo,
                'ruta' => $rutaCompleta,
                'fecha' => date("Y-m-d H:i:s", filemtime($rutaCompleta)),
                'tamaño' => filesize($rutaCompleta)
            ];
        }
    }
}
// Ordenar por fecha (más reciente primero)
usort($archivosJSON, function($a, $b) {
    return strtotime($b['fecha']) - strtotime($a['fecha']);
});
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Órdenes Pendientes V2</title>
    <!-- Reutilizando el CSS de la versión original -->
    <link rel="stylesheet" href="../../css/ordenes_pendientes.css">
    <style>
        :root {
            --primary-v2: #e8c840;
            --bg-v2: #0f172a;
        }
        body {
            background-color: var(--bg-v2);
            color: #f8fafc;
        }
        .container-ordenes {
            width: 95%;
            max-width: 900px;
            margin: 40px auto;
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(232, 200, 64, 0.2);
            border-radius: 12px;
            padding: 20px 30px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            box-sizing: border-box;
        }
        .table-container {
            width: 100%;
            overflow-x: auto;
        }
        h1 { color: var(--primary-v2); text-transform: uppercase; letter-spacing: 2px; }
        .btn-cargar { background-color: var(--primary-v2); color: #000; font-weight: bold; }
        .btn-cargar:hover { background-color: #d4b535; }
        .btn-eliminar { background-color: #ef4444; color: #fff; }
        .boton {
            display: inline-block;
            padding: 10px 20px;
            background: transparent;
            border: 1px solid var(--primary-v2);
            color: var(--primary-v2);
            text-decoration: none;
            border-radius: 6px;
            transition: all 0.3s;
        }
        .boton:hover {
            background: var(--primary-v2);
            color: #000;
        }
    </style>
</head>
<body>
    <div class="container-ordenes">
        <div class="header-section">
            <h1>Órdenes Pendientes <span style="font-size: 0.6em; vertical-align: middle; padding: 2px 8px; border: 1px solid; border-radius: 4px;">V2</span></h1>
            <div class="subtitle">Usuario: <?php echo htmlspecialchars($nombreUsuario); ?></div>
        </div>
        
        <?php if (empty($archivosJSON)): ?>
            <div class="empty-state" style="text-align: center; padding: 50px 0;">
                <div class="empty-state-icon" style="font-size: 4em; margin-bottom: 20px;">📭</div>
                <p>No tienes borradores guardados en la nueva versión.</p>
                <br>
                <a href="../menu_mantenimiento.html" class="boton">Volver al Menú</a>
            </div>
        <?php else: ?>
            <div class="table-container">
                <div class="table-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid rgba(232, 200, 64, 0.3); padding-bottom: 10px;">
                    <div class="table-title" style="font-weight: bold;">Borradores Guardados</div>
                    <div class="table-count"><?php echo count($archivosJSON); ?> documento<?php echo count($archivosJSON) != 1 ? 's' : ''; ?></div>
                </div>
                
                <table class="tabla-ordenes" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="text-align: left; color: var(--primary-v2);">
                            <th style="padding: 12px;">Nombre del Archivo</th>
                            <th style="padding: 12px;">Fecha de Guardado</th>
                            <th style="padding: 12px;">Tamaño</th>
                            <th style="padding: 12px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($archivosJSON as $archivo): ?>
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                <td style="padding: 12px;">
                                    <div class="file-name">
                                        <span class="file-icon">📄</span>
                                        <?php echo htmlspecialchars($archivo['nombre']); ?>
                                    </div>
                                </td>
                                <td class="date-cell" style="padding: 12px;"><?php echo $archivo['fecha']; ?></td>
                                <td style="padding: 12px;"><span class="size-badge" style="background: rgba(255,255,255,0.1); padding: 2px 6px; border-radius: 4px; font-size: 0.85em;"><?php echo number_format($archivo['tamaño'] / 1024, 2); ?> KB</span></td>
                                <td style="padding: 12px;">
                                    <div class="action-buttons" style="display: flex; gap: 8px;">
                                        <button class="btn btn-cargar" style="padding: 6px 12px; border-radius: 4px; border: none; cursor: pointer;" onclick="cargarBorrador('<?php echo htmlspecialchars($archivo['nombre']); ?>')">
                                            📂 Cargar
                                        </button>
                                        <button class="btn btn-eliminar" style="padding: 6px 12px; border-radius: 4px; border: none; cursor: pointer;" onclick="eliminarBorrador('<?php echo htmlspecialchars($archivo['nombre']); ?>')">
                                            🗑️ Eliminar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="footer-actions" style="margin-top: 30px; text-align: center;">
                <a href="index.php" class="boton">Ir al Formulario V2</a>
                <a href="../menu_mantenimiento.html" class="boton" style="margin-left: 10px;">Volver al Menú</a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function cargarBorrador(nombreArchivo) {
            if (confirm('¿Deseas cargar este borrador en el formulario S.O.M. V2?')) {
                // Guardar el nombre del archivo en localStorage
                localStorage.setItem('cargarBorradorV2', nombreArchivo);
                // Redirigir al formulario
                window.location.href = 'index.php';
            }
        }

        function eliminarBorrador(nombreArchivo) {
            if (confirm('¿Estás seguro de eliminar este borrador?\nEsta acción no se puede deshacer.')) {
                fetch('borrar_guardado.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ archivo: nombreArchivo })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Borrador eliminado correctamente');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Error al eliminar: ' + error.message);
                });
            }
        }
    </script>
</body>
</html>
