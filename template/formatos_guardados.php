<?php
require 'sesion.php';
$usuario = $_SESSION['nombre'];
$ruta = "C:/xampp/htdocs/fmt/archivos/formularios_guardados/$usuario/";
$archivos = glob($ruta . "*.json");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/formatos_guardados.css">
    <title>FORMULARIOS GUARDADOS</title>
</head>
<body>
    <h1>Formatos Guardados</h1>
    <?php if (empty($archivos)): ?>
        <p>No hay formularios guardados.</p>
        <?php else: ?>
            <?php foreach ($archivos as $archivo): 
        $nombre = basename($archivo);
        $contenido = json_decode(file_get_contents($archivo), true);
        $formato = $contenido['formato'] ?? 'Desconocido';
    ?>
        <div class="card">
            <h3>📝 <?= $nombre ?></h3>
            <p><strong>Formato:</strong> <?= ucfirst(str_replace("_", " ", $formato)) ?></p>
            <div class="botones">
                <!-- Botón para retomar -->
                <form action="retomar_formulario.php" method="POST">
                    <input type="hidden" name="archivo" value="<?= $nombre ?>">
                    <button type="submit">✏️ Retomar</button>
                </form>

                <!-- Botón para eliminar -->
                <form action="eliminar_formulario.php" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este archivo?');">
                    <input type="hidden" name="archivo" value="<?= $nombre ?>">
                    <button type="submit">🗑️ Eliminar</button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
</body>
</html>