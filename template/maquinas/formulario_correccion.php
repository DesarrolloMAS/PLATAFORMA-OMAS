<?php
require_once '../sesion.php';
require_once '/var/www/html/fmt/vendor/autoload.php';

$formato = $_GET['formato'] ?? '';
$zona = $_GET['zona'] ?? '';
$archivo = $_GET['archivo'] ?? '';
$maquina = $_GET['maquina']?? 'Deconocido';
$fecha = $_GET['fecha'] ?? date('Y-m-d');


// Extraer nombre, código y fecha desde el nombre del archivo PDF
$archivo_sin_extension = pathinfo($archivo, PATHINFO_FILENAME);  // sin .pdf
$partes = explode('_', $archivo_sin_extension);

$nombre_maquina = $partes[2] ?? 'nombre';
$codigo_maquina = $partes[3] ?? 'codigo';
$fecha_actual = $partes[4] ?? date('Y-m-d');


// Armar nombre del JSON
$nombreJson = "{$nombre_maquina}_{$codigo_maquina}_{$fecha_actual}.json";

$archivo_json = preg_replace('/\.pdf$/i', '.json', $archivo);
$json_path = "/var/www/fmt/archivos/generados/verificaciones/{$zona}/{$maquina}/{$archivo_json}";


if (!file_exists($json_path)) {
    die("❌ JSON no encontrado para: $json_path");
}

$datos = json_decode(file_get_contents($json_path), true);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>✏️ Corregir Formulario</title>
    <link rel="stylesheet" href="/css/formulario_correccion.css">
</head>
<body>
    <h2>✍️ Corrección de Verificación - <?= htmlspecialchars($maquina) ?></h2>

    <form action="procesar_correccion.php" method="POST">
        <?php foreach ($datos as $campo => $valor): ?>
            <?php if ($campo === 'archivo_pdf') continue; ?>
            <label><?= ucwords(str_replace('_', ' ', $campo)) ?>:</label><br>
            <input type="text" name="<?= htmlspecialchars($campo) ?>" value="<?= htmlspecialchars($valor) ?>"><br><br>
        <?php endforeach; ?>
        <input type="hidden" name="tecnico_correccion" value="<?= htmlspecialchars($_SESSION['nombre'] ?? '') ?>">
        <!-- Campo adicional: Código de Orden de Trabajo -->
        <label><strong>Código de Orden de Trabajo:</strong></label><br>
        <input type="text" name="codigo_orden" required><br><br>

        <!-- Hidden: para saber qué PDF y JSON corregir -->
        <input type="hidden" name="json_path" value="<?= htmlspecialchars($json_path) ?>">
        <input type="hidden" name="zona" value="<?= htmlspecialchars($zona) ?>">
        <input type="hidden" name="maquina" value="<?= htmlspecialchars($maquina) ?>">
        <input type="hidden" name="archivo_pdf" value="<?= htmlspecialchars($datos['archivo_pdf'] ?? $archivo) ?>">
        <input type="hidden" name="formato" value="<?= htmlspecialchars($formato) ?>">
        <input type="hidden" name="fecha" value="<?= htmlspecialchars($fecha) ?>">

        <button type="submit">✅ Guardar Corrección</button>
    </form>
</body>
</html>
