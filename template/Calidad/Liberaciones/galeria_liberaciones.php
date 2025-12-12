<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once '/var/www/html/fmt/vendor/autoload.php';
require_once '../../sesion.php';
require '../../conection.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
$sede = $_GET['sede'] ?? $_SESSION['sede'];
// Escanear la carpeta de archivos Excel
if ($sede === 'ZS'){
    $carpeta =  '/var/www/html/fmt/archivos/generados/Calidad/liberaciones_zs';
}else
    $carpeta =  '/var/www/html/fmt/archivos/generados/Calidad/liberaciones';
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
    <link rel="stylesheet" href="/css/revision.css"><!-- Asegúrate de tener estilos -->
</head>
<body class="body">
    <h1 class="titulo_principal">Lista de Archivos Excel<a href="../../menu_adm_calidad.html">Volver</a></h1>
    <div class="menu">
        <?php foreach ($archivos as $archivo): ?>
            <div class="file-card" >
                <h3><?php echo htmlspecialchars($archivo); ?></h3>
                <a href="liberaciones_visualizacion.php?archivo=<?php echo urlencode($archivo); ?>">Visualizar</a>
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
