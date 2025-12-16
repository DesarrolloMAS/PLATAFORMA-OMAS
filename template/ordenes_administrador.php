<?php
require 'sesion.php';
verificarAutenticacion();

// Configurar zona horaria de Colombia
date_default_timezone_set('America/Bogota');

// Verificar que el usuario actual sea administrador
$usuarioActual = $_SESSION['nombre'];
$rolActual = $_SESSION['rol'] ?? '';

// Lista de roles con permisos de administrador (ajustado para coincidir con el menú)
$rolesAdmin = ['adm', '1'];
$esAdmin = in_array($rolActual, $rolesAdmin);

if (!$esAdmin) {
    header('Location: menu_mantenimiento.php');
    exit();
}

// Directorio base de borradores
$dirBase = '/var/www/fmt/data/borradores/';

// Obtener filtro de usuario si existe
$filtroUsuario = isset($_GET['usuario']) ? $_GET['usuario'] : 'todos';

// Función para obtener todos los usuarios que tienen borradores
function obtenerUsuariosConBorradores($dirBase) {
    $usuarios = [];
    if (is_dir($dirBase)) {
        $carpetas = scandir($dirBase);
        foreach ($carpetas as $carpeta) {
            if ($carpeta !== '.' && $carpeta !== '..' && is_dir($dirBase . $carpeta)) {
                // Verificar si la carpeta tiene archivos JSON
                $rutaCarpeta = $dirBase . $carpeta;
                $archivos = scandir($rutaCarpeta);
                $tieneJSON = false;
                foreach ($archivos as $archivo) {
                    if (pathinfo($archivo, PATHINFO_EXTENSION) === 'json') {
                        $tieneJSON = true;
                        break;
                    }
                }
                if ($tieneJSON) {
                    $usuarios[] = $carpeta;
                }
            }
        }
    }
    sort($usuarios);
    return $usuarios;
}

// Función para obtener todos los archivos de un usuario específico o todos
function obtenerArchivosJSON($dirBase, $filtroUsuario = 'todos') {
    $archivosJSON = [];
    
    if (is_dir($dirBase)) {
        $carpetas = scandir($dirBase);
        foreach ($carpetas as $carpeta) {
            if ($carpeta !== '.' && $carpeta !== '..' && is_dir($dirBase . $carpeta)) {
                
                // Si hay filtro de usuario y no coincide, saltar
                if ($filtroUsuario !== 'todos' && $carpeta !== $filtroUsuario) {
                    continue;
                }
                
                $rutaCarpeta = $dirBase . $carpeta;
                $archivos = scandir($rutaCarpeta);
                
                foreach ($archivos as $archivo) {
                    if (pathinfo($archivo, PATHINFO_EXTENSION) === 'json') {
                        $rutaCompleta = $rutaCarpeta . '/' . $archivo;
                        
                        // Convertir nombre de carpeta de vuelta a nombre de usuario
                        $nombreUsuario = str_replace('_', ' ', $carpeta);
                        
                        $archivosJSON[] = [
                            'nombre' => $archivo,
                            'usuario' => $nombreUsuario,
                            'carpeta_usuario' => $carpeta,
                            'ruta' => $rutaCompleta,
                            'fecha' => date("Y-m-d H:i:s", filemtime($rutaCompleta)),
                            'tamaño' => filesize($rutaCompleta)
                        ];
                    }
                }
            }
        }
    }
    
    // Ordenar por fecha (más reciente primero)
    usort($archivosJSON, function($a, $b) {
        return strtotime($b['fecha']) - strtotime($a['fecha']);
    });
    
    return $archivosJSON;
}

$usuariosDisponibles = obtenerUsuariosConBorradores($dirBase);
$archivosJSON = obtenerArchivosJSON($dirBase, $filtroUsuario);
$totalArchivos = count($archivosJSON);
$totalUsuarios = count($usuariosDisponibles);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administrador - Órdenes</title>
    <link rel="stylesheet" href="../css/ordenes_pendientes.css">
    <style>
        /* Estilos adicionales para el panel de administrador */
        .admin-header {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow-lg);
        }

        .admin-title {
            font-size: 24px;
            font-weight: 600;
            margin: 0;
        }

        .admin-subtitle {
            font-size: 14px;
            opacity: 0.9;
            margin-top: 5px;
        }

        .admin-stats {
            display: flex;
            gap: 20px;
            font-size: 14px;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 24px;
            font-weight: bold;
            display: block;
        }

        .filters-section {
            background: var(--card-bg);
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: var(--shadow-md);
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .filter-label {
            font-weight: 600;
            color: var(--text-primary);
        }

        .filter-select {
            padding: 10px 15px;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            font-size: 14px;
            background: white;
            min-width: 200px;
            transition: all 0.3s ease;
        }

        .filter-select:focus {
            border-color: var(--accent-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(254, 229, 184, 0.3);
        }

        .btn-filtrar {
            background: var(--primary-color);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-filtrar:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
        }

        .user-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }

        .admin-actions {
            display: flex;
            gap: 10px;
        }

        .btn-ver {
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            color: white;
        }

        .btn-ver:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(39, 174, 96, 0.4);
        }

        .btn-cargar-admin {
            background: linear-gradient(135deg, #2196F3 0%, #0b7dda 100%);
            color: white;
        }

        .btn-cargar-admin:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(33, 150, 243, 0.4);
        }

        .empty-filter {
            text-align: center;
            padding: 40px;
            color: var(--text-secondary);
        }

        .empty-filter-icon {
            font-size: 48px;
            margin-bottom: 15px;
            opacity: 0.3;
        }

        @media (max-width: 768px) {
            .admin-header {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }

            .filter-group {
                flex-direction: column;
                align-items: stretch;
            }

            .filter-select {
                min-width: auto;
                width: 100%;
            }

            .admin-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container-ordenes">
        <!-- Header de Administrador -->
        <div class="admin-header">
            <div>
                <h1 class="admin-title">🛠️ Panel de Administrador</h1>
                <div class="admin-subtitle">Administrador: <?php echo htmlspecialchars($usuarioActual); ?></div>
            </div>
            <div class="admin-stats">
                <div class="stat-item">
                    <span class="stat-number"><?php echo $totalUsuarios; ?></span>
                    <span>Usuarios</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?php echo $totalArchivos; ?></span>
                    <span>Órdenes</span>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="filters-section">
            <form method="GET" class="filter-group">
                <label class="filter-label" for="usuario">Filtrar por usuario:</label>
                <select name="usuario" id="usuario" class="filter-select">
                    <option value="todos" <?php echo $filtroUsuario === 'todos' ? 'selected' : ''; ?>>
                        👥 Todos los usuarios (<?php echo $totalUsuarios; ?>)
                    </option>
                    <?php foreach ($usuariosDisponibles as $usuario): ?>
                        <option value="<?php echo htmlspecialchars($usuario); ?>" 
                                <?php echo $filtroUsuario === $usuario ? 'selected' : ''; ?>>
                            👤 <?php echo htmlspecialchars(str_replace('_', ' ', $usuario)); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn-filtrar">🔍 Filtrar</button>
            </form>
        </div>

        <?php if (empty($archivosJSON)): ?>
            <div class="empty-state">
                <?php if ($filtroUsuario === 'todos'): ?>
                    <div class="empty-state-icon">📭</div>
                    <p>No hay órdenes guardadas en el sistema.</p>
                <?php else: ?>
                    <div class="empty-filter-icon">🔍</div>
                    <p>No se encontraron órdenes para el usuario seleccionado.</p>
                    <a href="?usuario=todos" class="boton">Ver todas las órdenes</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="table-container">
                <div class="table-header">
                    <div class="table-title">
                        <?php if ($filtroUsuario === 'todos'): ?>
                            📋 Todas las Órdenes
                        <?php else: ?>
                            👤 Órdenes de <?php echo htmlspecialchars(str_replace('_', ' ', $filtroUsuario)); ?>
                        <?php endif; ?>
                    </div>
                    <div class="table-count"><?php echo $totalArchivos; ?> documento<?php echo $totalArchivos != 1 ? 's' : ''; ?></div>
                </div>
                
                <table class="tabla-ordenes">
                    <thead>
                        <tr>
                            <th>Usuario</th>
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
                                    <span class="user-badge">
                                        <?php echo htmlspecialchars($archivo['usuario']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="file-name">
                                        <div class="file-icon">📄</div>
                                        <?php echo htmlspecialchars($archivo['nombre']); ?>
                                    </div>
                                </td>
                                <td class="date-cell"><?php echo $archivo['fecha']; ?></td>
                                <td><span class="size-badge"><?php echo number_format($archivo['tamaño'] / 1024, 2); ?> KB</span></td>
                                <td>
                                    <div class="admin-actions">
                                        <button class="btn btn-ver" onclick="verArchivo('<?php echo htmlspecialchars($archivo['carpeta_usuario']); ?>', '<?php echo htmlspecialchars($archivo['nombre']); ?>')">
                                            👁️ Ver
                                        </button>
                                        <button class="btn btn-cargar-admin" onclick="cargarBorradorAdmin('<?php echo htmlspecialchars($archivo['nombre']); ?>')">
                                            � Cargar
                                        </button>
                                        <button class="btn btn-eliminar" onclick="eliminarArchivo('<?php echo htmlspecialchars($archivo['carpeta_usuario']); ?>', '<?php echo htmlspecialchars($archivo['nombre']); ?>')">
                                            🗑️ Eliminar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        
        <div class="footer-actions">
            <a href="../template/menu_mantenimiento.php" class="boton">Volver al Menú Principal</a>
        </div>
    </div>

    <script>
        function verArchivo(carpetaUsuario, nombreArchivo) {
            // Crear ventana modal para mostrar el contenido del archivo
            fetch('ver_archivo_admin.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ 
                    carpeta_usuario: carpetaUsuario, 
                    archivo: nombreArchivo 
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarModalContenido(data.contenido, nombreArchivo);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error al ver archivo: ' + error.message);
            });
        }

        function cargarBorradorAdmin(nombreArchivo) {
            if (confirm('¿Deseas cargar este borrador en el formulario?\nSe redirigirá al formulario de mantenimiento.')) {
                // Guardar el nombre del archivo en localStorage (igual que en la versión normal)
                localStorage.setItem('cargarBorrador', nombreArchivo);
                // Redirigir al formulario
                window.location.href = 'formulario001.php';
            }
        }

        function eliminarArchivo(carpetaUsuario, nombreArchivo) {
            if (confirm(`¿Estás seguro de eliminar el archivo "${nombreArchivo}"?\nEsta acción no se puede deshacer.`)) {
                fetch('eliminar_archivo_admin.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ 
                        carpeta_usuario: carpetaUsuario, 
                        archivo: nombreArchivo 
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Archivo eliminado correctamente');
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

        function mostrarModalContenido(contenido, nombreArchivo) {
            // Crear modal dinámico
            const modal = document.createElement('div');
            modal.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.8);
                z-index: 10000;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            `;

            const modalContent = document.createElement('div');
            modalContent.style.cssText = `
                background: white;
                border-radius: 12px;
                max-width: 800px;
                max-height: 80vh;
                overflow-y: auto;
                padding: 30px;
                position: relative;
            `;

            modalContent.innerHTML = `
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 15px;">
                    <h3 style="margin: 0; color: #2c3e50;">📄 ${nombreArchivo}</h3>
                    <button onclick="this.closest('[style*=fixed]').remove()" style="background: #e74c3c; color: white; border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer;">✕ Cerrar</button>
                </div>
                <pre style="background: #f8f9fa; padding: 20px; border-radius: 8px; overflow-x: auto; font-size: 12px; line-height: 1.4;">${JSON.stringify(contenido, null, 2)}</pre>
            `;

            modal.appendChild(modalContent);
            document.body.appendChild(modal);

            // Cerrar modal al hacer clic fuera
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.remove();
                }
            });
        }

        // Animación de entrada para las filas
        document.addEventListener('DOMContentLoaded', () => {
            const rows = document.querySelectorAll('.tabla-ordenes tbody tr');
            rows.forEach((row, index) => {
                row.style.animation = `fadeIn 0.5s ease-out ${0.1 * index}s both`;
            });
        });

        // Auto-envío del formulario al cambiar el select
        document.getElementById('usuario').addEventListener('change', function() {
            this.form.submit();
        });
    </script>
</body>
</html>
