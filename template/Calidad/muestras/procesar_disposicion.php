<?php
/**
 * procesar_disposicion.php
 * ---------------------
 * Reemplaza el bloque principal de muestras_pros2.php: guarda los datos
 * de disposición (fecha, mejorante, cantidad, responsable) de los ítems
 * seleccionados, actualizándolos por id dentro del JSON del período.
 */
require_once '../../sesion.php';
verificarAutenticacion();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "<script>alert('Acceso no permitido.'); window.location.href='revision_muestras.php';</script>";
    exit;
}

$sede = $_SESSION['sede'];
$sede_san = preg_replace('/[^A-Za-z0-9_-]/', '', $sede);

$periodo = $_POST['periodo'] ?? '';
$fecha   = $_POST['fecha'] ?? '';

if (empty($periodo)) {
    die('Error: No se recibió el período de origen.');
}

$archivo_json = "/var/www/fmt/archivos/generados/Calidad/muestras/" . $sede_san . "/" . basename($periodo) . ".json";

if (!file_exists($archivo_json)) {
    die('Error: El período "' . htmlspecialchars($periodo) . '" no existe.');
}

$itemsSeleccionados = $_POST['items_seleccionados'] ?? [];
$datosDisp          = $_POST['disp'] ?? [];

if (empty($itemsSeleccionados)) {
    die('Error: No se seleccionó ningún ítem.');
}

$registros = json_decode(file_get_contents($archivo_json), true) ?: [];
$contador = 0;

foreach ($registros as &$r) {
    if (!in_array($r['id'], $itemsSeleccionados, true)) continue;

    $datos = $datosDisp[$r['id']] ?? [];
    if (empty($datos)) continue;

    $mejorante = '';
    if (isset($datos['mejorante']) && is_array($datos['mejorante'])) {
        $mejorante = implode(', ', $datos['mejorante']);
    }

    $r['datos']['disp_fecha']       = $datos['fecha'] ?? '';
    $r['datos']['disp_mejorante']   = $mejorante;
    $r['datos']['disp_cantidad']    = $datos['cantidad'] ?? '';
    $r['datos']['disp_responsable'] = $datos['responsable'] ?? '';

    $contador++;
}
unset($r);

if (@file_put_contents($archivo_json, json_encode($registros, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
    echo "<p>✓ Se registró la disposición de <strong>{$contador}</strong> ítem(s) del " . htmlspecialchars($fecha) . ".</p>";
    echo '<a href="revision_muestras.php">← Volver a revisión</a>';
} else {
    echo '<p>✗ No se pudo guardar (permisos de disco).</p>';
    echo '<a href="revision_muestras.php">← Volver a revisión</a>';
}
?>
