<?php
require '../sesion.php';
require '../conection.php';
$sede = $_SESSION['sede'];
if($sede === 'ZC'){
    $carpetaReprocesos = 'procesos/';
}else{
    $carpetaReprocesos = 'procesos_zs/';
}

// Verificar si se ha pasado un archivo como parámetro
if (!isset($_GET['archivo'])) {
    die("No se ha especificado un archivo.");
}

$archivoSeleccionado = $_GET['archivo'];
$rutaArchivo = $carpetaReprocesos . basename($archivoSeleccionado);

// Verificar si el archivo existe
if (!file_exists($rutaArchivo)) {
    die("El archivo seleccionado no existe.");
}

// Leer los datos del archivo JSON
$datos = json_decode(file_get_contents($rutaArchivo), true);
if (!$datos) {
    die("Error al leer los datos del archivo JSON.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/reprocesos_molino.css">
    <title>Reproceso Molino</title>
</head>
<body>
    <h1>Datos del Reproceso</h1>
    <!-- Formulario para enviar todos los datos -->
    <form action="procesar.php" method="post">
        <!-- Datos originales del archivo JSON (campos ocultos) -->
        <?php foreach ($datos as $key => $value): ?>
            <input type="hidden" name="json[<?php echo htmlspecialchars($key, ENT_QUOTES, 'UTF-8'); ?>]" value="<?php echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?>">
        <?php endforeach; ?>
            <input type="hidden" name="json_file" value="<?php echo htmlspecialchars($rutaArchivo, ENT_QUOTES, 'UTF-8'); ?>">
        <!-- Mostrar los datos originales (solo lectura) -->
        <h2>Datos De Almacen</h2>
        <?php foreach ($datos as $key => $value): ?>
            <div>
                <label><?php echo ucfirst($key); ?>:</label>
                <input type="text" value="<?php echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?>" readonly>
            </div>
        <?php endforeach; ?>

        <!-- Nuevos datos del formulario -->
        <h2>Control Reproceso</h2>
        <div>
            <label for="Fecha_Reproceso">Fecha De Reproceso:</label>
            <input type="date" name="Fecha_Reproceso" id="Fecha_Reproceso" required>
        </div>
        <div>
            <label for="Referencia">Referencia de Producto:</label>
            <input type="text" name="Referencia" id="Referencia" required>
        </div>
        <div>
            <label for="hora_inicio">Hora de Inicio:</label>
            <input type="time" name="hora_inicio" id="hora_inicio" required>
        </div>
        <div>
            <label for="hora_fin">Hora Final:</label>
            <input type="time" name="hora_fin" id="hora_fin" required>
        </div>
        <div>
            <label for="cantidad">Cantidad:</label>
            <input type="number" name="cantidad" id="cantidad" placeholder="Ingrese Peso en KG" required>
        </div>
        <div>
            <label for="responsable">Responsable de Ejecucion:</label>
            <input type="text" name="responsable" id="responsable" required>
        </div>
        <button type="submit">Enviar</button>
    </form>
</body>
</html>