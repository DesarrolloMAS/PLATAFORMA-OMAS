<?php
require 'sesion.php';
verificarAutenticacion();

// Configurar zona horaria de Colombia
date_default_timezone_set('America/Bogota');

// Obtener el nombre del usuario desde la sesión
$nombreUsuario = $_SESSION['nombre'];
$nombreCarpeta = preg_replace('/[^a-zA-Z0-9_-]/', '_', $nombreUsuario);
$dirUsuario = '/var/www/fmt/data/borradores/' . $nombreCarpeta;

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
    <title>Órdenes Pendientes</title>
    <link rel="stylesheet" href="../css/ordenes_pendientes.css">
</head>
<body>
    <div class="container-ordenes">
        <div class="header-section">
            <h1>Órdenes Pendientes</h1>
            <div class="subtitle">Usuario: <?php echo htmlspecialchars($nombreUsuario); ?></div>
        </div>
        
        <?php if (empty($archivosJSON)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">📭</div>
                <p>No tienes borradores guardados.</p>
                <a href="menu_mantenimiento.php" class="boton">Volver al Menu</a>
            </div>
        <?php else: ?>
            <div class="table-container">
                <div class="table-header">
                    <div class="table-title">Borradores Guardados</div>
                    <div class="table-count"><?php echo count($archivosJSON); ?> documento<?php echo count($archivosJSON) != 1 ? 's' : ''; ?></div>
                </div>
                
                <table class="tabla-ordenes">
                    <thead>
                        <tr>
                            <th>Nombre del Archivo</th>
                            <th>Fecha de Guardado</th>
                            <th>Tamaño</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($archivosJSON as $archivo): ?>
                            <tr>
                                <td>
                                    <div class="file-name">
                                        <div class="file-icon">📄</div>
                                        <?php echo htmlspecialchars($archivo['nombre']); ?>
                                    </div>
                                </td>
                                <td class="date-cell"><?php echo $archivo['fecha']; ?></td>
                                <td><span class="size-badge"><?php echo number_format($archivo['tamaño'] / 1024, 2); ?> KB</span></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-cargar" onclick="cargarBorrador('<?php echo htmlspecialchars($archivo['nombre']); ?>')">
                                            📂 Cargar
                                        </button>
                                        <button class="btn btn-eliminar" onclick="eliminarBorrador('<?php echo htmlspecialchars($archivo['nombre']); ?>')">
                                            🗑️ Eliminar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="footer-actions">
                <a href="formulario001.php" class="boton">Volver al Formulario</a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function cargarBorrador(nombreArchivo) {
            if (confirm('¿Deseas cargar este borrador en el formulario?')) {
                // Guardar el nombre del archivo en localStorage
                localStorage.setItem('cargarBorrador', nombreArchivo);
                // Redirigir al formulario
                window.location.href = 'formulario001.php';
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

        // Animación de entrada para las filas
        document.addEventListener('DOMContentLoaded', () => {
            const rows = document.querySelectorAll('.tabla-ordenes tbody tr');
            rows.forEach((row, index) => {
                row.style.animation = `fadeIn 0.5s ease-out ${0.1 * index}s both`;
            });
        });
    </script>
</body>
</html>