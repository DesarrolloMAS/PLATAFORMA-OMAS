<script>
setInterval(function() {
    verificarSesionAjax(function(activa) {
        // Si no está activa, ya se redirigió
    });
}, 10000); // Cada 10 segundos
function verificarSesionAjax(callback) {
    fetch('/template/verificar_sesion.php')
        .then(response => response.json())
        .then(data => {
            if (data.activa) {
                callback(true);
            } else {
                // Muestra mensaje o redirige
                alert('Tu sesión ha expirado. Por favor, inicia sesión nuevamente.');
                window.location.href = '/index.php?motivo=sesion';
                callback(false);
            }
        })
        .catch(() => {
            alert('Error al verificar la sesión.');
            callback(false);
        });
}

// Ejemplo: antes de enviar un formulario
const form = document.querySelector('form');
if (form) {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        verificarSesionAjax(function(activa) {
            if (activa) {
                form.submit();
            }
            // Si no está activa, ya se redirigió
        });
    });
}
</script>
<?php
require __DIR__ . '/../../vendor/autoload.php';
require '../conection.php'; // Conexión a la base de datos
require '../sesion.php';
verificarAutenticacion();
$sede = $_SESSION['sede'];
use PhpOffice\PhpSpreadsheet\IOFactory;
// Escanear la carpeta de archivos Excel
if ($sede === 'ZC') {
    $carpeta = '/var/www/fmt/archivos/generados/empaque/';
} else {
    $carpeta = '/var/www/fmt/archivos/generados/empaque_zs/';
}
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
</head>
<body class="body">
    <h1 class="titulo_principal">Lista de Archivos Excel<a href="../revisiones_almacen.html">Volver</a></h1>
    <div class="menu">
        <?php foreach ($archivos as $archivo): ?>
            <div class="file-card" >
                <h3><?php echo htmlspecialchars($archivo); ?></h3>
                <a href="observar_empaques.php?archivo=<?php echo urlencode($archivo); ?>">Visualizar</a>
            </div>
        <?php endforeach; ?>
    </div>
    <br>
</body>
<script>
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
