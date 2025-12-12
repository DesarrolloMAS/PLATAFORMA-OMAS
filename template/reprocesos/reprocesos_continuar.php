<?php
require '../sesion.php';
require '../conection.php';
$sede = $_SESSION['sede'];
if($sede === 'ZC'){
    $carpetaReprocesos = 'procesos/';
}else{
    $carpetaReprocesos = 'procesos_zs/';
}
// Verificar si la carpeta existe
if (!is_dir($carpetaReprocesos)) {
    die("La carpeta de reprocesos no existe.");
}

// Obtener la lista de archivos JSON en la carpeta
$archivos = glob($carpetaReprocesos . '*.json');

// Verificar si hay archivos disponibles
if (empty($archivos)) {
    die("No hay archivos de reprocesos disponibles.");
}

// Mostrar una lista de archivos para seleccionar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['archivo'])) {
    // Redirigir al documento con los datos del archivo seleccionado
    $archivoSeleccionado = $_POST['archivo'];
    header("Location: reprocesos_molino.php?archivo=" . urlencode($carpetaReprocesos . $archivoSeleccionado));
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/reprocesos_continuar.css">
    <title>Seleccionar Reproceso</title>
</head>
<body>
    <h1>Seleccionar Reproceso</h1>
    <form method="post">
        <label for="archivo">Seleccione un archivo de reproceso:</label>
        <select name="archivo" id="archivo" required>
            <?php foreach ($archivos as $archivo): ?>
                <option value="<?php echo htmlspecialchars(basename($archivo), ENT_QUOTES, 'UTF-8'); ?>">
                    <?php echo htmlspecialchars(basename($archivo), ENT_QUOTES, 'UTF-8'); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Ver Reproceso</button>
    </form>
</body>
</html>