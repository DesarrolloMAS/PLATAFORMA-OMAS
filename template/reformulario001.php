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
    die("error2.html");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title >Lista de Archivos</title>
    <link rel="stylesheet" href="../css/revision.css"> <!-- Asegúrate de tener estilos -->
</head>
<body class="body">
    <h1 class="titulo_principal">Lista de Archivos Excel<a href="./menu_adm.html">Volver</a></h1>
    <div class="menu">
        <?php foreach ($archivos as $archivo): ?>
            <div class="file-card" >
                <h3><?php echo htmlspecialchars($archivo); ?></h3>
                <div class="file-actions">
                    <a href="revisar.php?archivo=<?php echo urlencode($archivo); ?>">Visualizar</a>
                    <a href="descargar_pdf.php?archivo=<?php echo urlencode($archivo); ?>" title="Descargar PDF" class="download-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M.5 9.9a.5.5 0 0 1 .5.5v3.1a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-3.1a.5.5 0 0 1 1 0v3.1A2 2 0 0 1 14 15H2a2 2 0 0 1-2-2v-3.1a.5.5 0 0 1 .5-.5z"/>
                            <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
                        </svg>
                    </a>
                </div>
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
            event.preventDefault(); // Prevenir redirección inmediata
            const parentCard = this.closest(".file-card");
            parentCard.classList.add("active");

            // Guardar el estado en localStorage
            const archivo = this.href; // Usa el enlace como identificador único
            localStorage.setItem(archivo, "active");

            // Redirigir después de un breve retraso
            setTimeout(() => {
                window.location.href = this.href;
            }, 200); // Esperar 200ms antes de redirigir
        });
    });
});
</script>
</html>
