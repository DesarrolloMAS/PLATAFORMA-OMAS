<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once '/var/www/fmt/vendor/autoload.php';
require_once '../../sesion.php';
require '../../conection.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
$sede = $_GET['sede'] ?? $_SESSION['sede'];
// Escanear la carpeta de archivos Excel
if ($sede === 'ZS'){
    $carpeta =  '/var/www/fmt/archivos/generados/Calidad/liberaciones_zs';
}else
    $carpeta =  '/var/www/fmt/archivos/generados/Calidad/liberaciones';
$archivos = array_filter(scandir($carpeta), function($archivo) use ($carpeta) {
    return is_file("$carpeta/$archivo") && preg_match('/\.(xlsx|xls)$/i', $archivo);
});

if (empty($archivos)) {
   die (header("Location: /template/error2.html"));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title >Lista de Archivos</title>
    <link rel="stylesheet" href="/css/revision_prev.css"><!-- Asegúrate de tener estilos -->
    <style>
        .delete-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 8px;
            border-radius: 4px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.2s;
            margin-left: 5px;
        }
        
        .delete-btn:hover {
            background: #c82333;
        }
        
        .file-actions {
            display: flex;
            gap: 5px;
            align-items: center;
        }
        
        .download-btn {
            background: #007bff;
            color: white;
            padding: 8px;
            border-radius: 4px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.2s;
        }
        
        .download-btn:hover {
            background: #0056b3;
        }
    </style>
</head>

<body class="body">
    <h1 class="titulo_principal">Lista de Archivos Excel<a href="../../menu_adm_calidad.html">Volver</a></h1>
    <div class="menu">
<?php foreach ($archivos as $archivo): ?>
    <div class="file-card" >
        <h3><?php echo htmlspecialchars($archivo); ?></h3>
        <div class="file-actions">
            <a href="liberaciones_visualizacion.php?archivo=<?php echo urlencode($archivo); ?>&sede=<?php echo urlencode($sede); ?>">Visualizar</a>
            <a href="../../descargar_pdf.php?archivo=<?php echo urlencode($archivo); ?>&sede=<?php echo urlencode($sede); ?>&tipo=liberaciones" title="Descargar PDF" class="download-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M.5 9.9a.5.5 0 0 1 .5.5v3.1a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-3.1a.5.5 0 0 1 1 0v3.1A2 2 0 0 1 14 15H2a2 2 0 0 1-2-2v-3.1a.5.5 0 0 1 .5-.5z"/>
                    <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
                </svg>
            </a>
            <button onclick="eliminarArchivo('<?php echo htmlspecialchars($archivo); ?>')" title="Eliminar archivo" class="delete-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M6.5 1h3a.5.5 0 0 1 .5.5v1H6v-1a.5.5 0 0 1 .5-.5ZM11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3A1.5 1.5 0 0 0 5 1.5v1H2.506a.58.58 0 0 0-.01 0H1.5a.5.5 0 0 0 0 1h.538l.853 10.66A2 2 0 0 0 4.885 16h6.23a2 2 0 0 0 1.994-1.84L14.962 3.5H15.5a.5.5 0 0 0 0-1h-1.004a.58.58 0 0 0-.01 0H11Zm1.958 1-.846 10.58a1 1 0 0 1-.997.92h-6.23a1 1 0 0 1-.997-.92L3.042 3.5h9.916Zm-7.487 1a.5.5 0 0 1 .528.47l.5 8.5a.5.5 0 0 1-.998.06L5 5.03a.5.5 0 0 1 .47-.53Zm5.058 0a.5.5 0 0 1 .47.53l-.5 8.5a.5.5 0 1 1-.998-.06l.5-8.5a.5.5 0 0 1 .528-.47ZM8 4.5a.5.5 0 0 1 .5.5v8.5a.5.5 0 0 1-1 0V5a.5.5 0 0 1 .5-.5Z"/>
                        </svg>
                    </button>
        </div>
    </div>
<?php endforeach; ?>
    </div>
    <br>
</body>
<script>
       function eliminarArchivo(nombreArchivo) {
        if (confirm('¿Está seguro de que desea eliminar el archivo "' + nombreArchivo + '"? Esta acción no se puede deshacer.')) {
            // Crear formulario para enviar la petición de eliminación
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
    document.addEventListener("DOMContentLoaded", function () {
        const cards = document.querySelectorAll(".file-card a");

        // Cargar estados previos desde localStorage
        cards.forEach(button => {
            const parentCard = button.closest(".file-card");
            const archivo = button.href; // Usa el enlace como identificador único
            if (localStorage.getItem(archivo) === "active") {
                parentCard.classList.add("active");
            }
        });

        // Manejar clics para actualizar estados
        cards.forEach(button => {
            button.addEventListener("click", function (event) {
                const parentCard = this.closest(".file-card");
                parentCard.classList.add("active");

                // Guardar el estado en localStorage
                const archivo = this.href; // Usa el enlace como identificador único
                localStorage.setItem(archivo, "active");

                // Redirigir después de un breve retraso
                setTimeout(() => {
                    window.location.href = this.href;
                }, 100);
            });
        });
    });
</script>
</html>