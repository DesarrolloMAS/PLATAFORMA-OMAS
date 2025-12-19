<?php
require '../vendor/autoload.php'; // PhpSpreadsheet
require 'conection.php'; // Conexión a la base de datos
require 'sesion.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

// Escanear la carpeta de archivos Excel
$carpeta = __DIR__ . '/../archivos/generados/excelS_M/';
$carpetaZS = __DIR__ . '/../archivos/generados/excelS_MZS/';
$sede = $_SESSION['sede'];
if ($sede === 'ZC'){
    $archivos = array_filter(scandir($carpeta), function($archivo) use ($carpeta) {
        return is_file("$carpeta/$archivo") && preg_match('/\.(xlsx|xls)$/i', $archivo);
    });
} else{
    $archivos = array_filter(scandir($carpetaZS), function($archivo) use ($carpetaZS) {
        return is_file("$carpetaZS/$archivo") && preg_match('/\.(xlsx|xls)$/i', $archivo);
    });
}
if (empty($archivos)) {
    die(header("Location: error2.html"));
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Archivos Excel</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/revision.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <h1 class="header-title">Gestión de Archivos Excel</h1>
            <a href="./menu_adm.html" class="back-button">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M10 12L6 8l4-4"/>
                </svg>
                Volver al menú
            </a>
        </div>
    </header>

    <!-- Main Container -->
    <div class="container">
        <!-- Toolbar -->
        <div class="toolbar">
            <div class="toolbar-info">
                <div class="file-count">
                    <svg width="18" height="18" viewBox="0 0 16 16" fill="currentColor">
                        <path d="M4 2a2 2 0 0 1 2-2h4.5L14 3.5V14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V2z"/>
                    </svg>
                    <span id="fileCount"><?php echo count($archivos); ?> archivo<?php echo count($archivos) !== 1 ? 's' : ''; ?></span>
                </div>
            </div>
            <button id="eliminarSeleccionados" class="delete-selected-btn">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                    <path d="M6.5 1h3a.5.5 0 0 1 .5.5v1H6v-1a.5.5 0 0 1 .5-.5ZM11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3A1.5 1.5 0 0 0 5 1.5v1H2.506a.58.58 0 0 0-.01 0H1.5a.5.5 0 0 0 0 1h.538l.853 10.66A2 2 0 0 0 4.885 16h6.23a2 2 0 0 0 1.994-1.84L14.962 3.5H15.5a.5.5 0 0 0 0-1h-1.004a.58.58 0 0 0-.01 0H11Z"/>
                </svg>
                Eliminar seleccionados
            </button>
        </div>

        <!-- Files Grid -->
        <div class="files-grid">
            <?php foreach ($archivos as $archivo): ?>
            <div class="file-card">
                <div class="file-card-header">
                    <input type="checkbox" class="file-checkbox archivo-checkbox" value="<?php echo htmlspecialchars($archivo); ?>">
                    <div class="file-icon">
                        <svg width="24" height="24" viewBox="0 0 16 16" fill="currentColor" style="color: #22C55E;">
                            <path d="M4 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0H4z"/>
                            <path d="M9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1zM5.5 7a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1h-5zM5.5 9a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1h-5zm0 2a.5.5 0 0 0 0 1h2a.5.5 0 0 0 0-1h-2z"/>
                        </svg>
                    </div>
                    <div class="file-name"><?php echo htmlspecialchars($archivo); ?></div>
                </div>
                <div class="file-actions">
                    <a href="revisar.php?archivo=<?php echo urlencode($archivo); ?>" class="action-btn view-btn">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M1 8s3-5 7-5 7 5 7 5-3 5-7 5-7-5-7-5z"/>
                            <circle cx="8" cy="8" r="2"/>
                        </svg>
                        Visualizar
                    </a>
                    <a href="descargar_pdf.php?archivo=<?php echo urlencode($archivo); ?>" class="action-btn download-btn" title="Descargar PDF">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                            <path d="M.5 9.9a.5.5 0 0 1 .5.5v3.1a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-3.1a.5.5 0 0 1 1 0v3.1A2 2 0 0 1 14 15H2a2 2 0 0 1-2-2v-3.1a.5.5 0 0 1 .5-.5z"/>
                            <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
                        </svg>
                        PDF
                    </a>
                    <button onclick="eliminarArchivo('<?php echo htmlspecialchars($archivo); ?>')" class="action-btn delete-btn" title="Eliminar archivo">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                            <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                            <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1z"/>
                        </svg>
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        // Función para eliminar archivo individual
        function eliminarArchivo(nombreArchivo) {
            if (confirm('¿Está seguro de que desea eliminar el archivo "' + nombreArchivo + '"? Esta acción no se puede deshacer.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'eliminar_archivo.php';
                
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'archivo';
                input.value = nombreArchivo;
                
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Eliminar archivos seleccionados
        document.getElementById('eliminarSeleccionados').addEventListener('click', function() {
            const checkboxes = document.querySelectorAll('.archivo-checkbox:checked');
            if (checkboxes.length === 0) {
                alert('Seleccione al menos un archivo para eliminar.');
                return;
            }
            if (!confirm('¿Está seguro de que desea eliminar los archivos seleccionados? Esta acción no se puede deshacer.')) {
                return;
            }
            
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'eliminar_archivo.php';

            checkboxes.forEach(cb => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'archivos[]';
                input.value = cb.value;
                form.appendChild(input);
            });

            document.body.appendChild(form);
            form.submit();
        });

        // Gestión de estados activos
        document.addEventListener("DOMContentLoaded", function () {
            const cards = document.querySelectorAll(".file-card .view-btn");

            // Cargar estados previos desde localStorage
            cards.forEach(button => {
                const parentCard = button.closest(".file-card");
                const archivo = button.href;
                if (localStorage.getItem(archivo) === "active") {
                    parentCard.classList.add("active");
                }
            });

            // Manejar clics para actualizar estados
            cards.forEach(button => {
                button.addEventListener("click", function (event) {
                    event.preventDefault();
                    const parentCard = this.closest(".file-card");
                    parentCard.classList.add("active");

                    const archivo = this.href;
                    localStorage.setItem(archivo, "active");

                    setTimeout(() => {
                        window.location.href = this.href;
                    }, 200);
                });
            });
        });
    </script>
</body>
</html>