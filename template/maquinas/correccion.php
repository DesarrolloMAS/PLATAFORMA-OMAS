<?php
require '../../vendor/autoload.php';
require '../sesion.php';

if (!isset($_GET['archivo'])) {
    die("❌ No se especificó el archivo JSON.");
}

$jsonFile = $_GET['archivo'];  // ejemplo: verificacion_balanzas_ZC_2025-04-11.json
$partes = explode('_', $jsonFile);

if (count($partes) < 4) {
    die("❌ Nombre de archivo inválido.");
}

$formato = $partes[1]; // "balanzas"

$jsonPath = "C:/xampp/htdocs/fmt/archivos/verificaciones/{$formato}/{$jsonFile}";

if (!file_exists($jsonPath)) {
    die("❌ Archivo JSON no encontrado: $jsonPath");
}

$datos = json_decode(file_get_contents($jsonPath), true);
if (!$datos) {
    die("❌ No se pudieron leer los datos del archivo JSON.");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Corregir Verificación</title>
</head>
<body>
    <h2>✏️ Corregir Verificación</h2>
    <form method="POST" action="guardar_correccion.php">
        <?php foreach ($datos as $campo => $valor): ?>
            <?php if ($campo !== 'archivo_pdf'): ?>
                <label><?= ucwords(str_replace("_", " ", $campo)) ?>:</label><br>
                <input type="text" name="<?= htmlspecialchars($campo) ?>" value="<?= htmlspecialchars($valor) ?>"><br><br>
            <?php endif; ?>
        <?php endforeach; ?>

        <!-- Campo adicional para el código de orden -->
        <label><strong>Código de Orden de Trabajo:</strong></label><br>
        <input type="text" name="codigo_orden" required><br><br>

        <input type="hidden" name="archivo_json" value="<?= htmlspecialchars($jsonFile) ?>">
        <input type="hidden" name="formato" value="<?= htmlspecialchars($formato) ?>">
        <input type="hidden" name="archivo_pdf" value="<?= htmlspecialchars($datos['archivo_pdf'] ?? '') ?>">

        <button type="submit">✅ Guardar y Reemplazar PDF</button>
    </form>
</body>
</html>
