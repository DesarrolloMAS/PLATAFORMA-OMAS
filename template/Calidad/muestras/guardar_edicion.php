<?php
/**
 * guardar_edicion.php
 * ---------------------
 * Reemplaza a guardar_correccion.php (que subía un .xlsx binario reescrito
 * por SheetJS). Aquí solo se actualizan los campos base de los ítems
 * indicados por id dentro del JSON del período.
 */
require_once '../../sesion.php';
verificarAutenticacion();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Método no permitido.');
}

$sede = $_SESSION['sede'];
$sede_san = preg_replace('/[^A-Za-z0-9_-]/', '', $sede);

$periodo = $_POST['periodo'] ?? '';
$fecha   = $_POST['fecha'] ?? '';
$items   = $_POST['items'] ?? [];

if (!preg_match('/^\d{4}-\d{2}$/', $periodo) || empty($items) || !is_array($items)) {
    die('Datos incompletos o inválidos.');
}

$archivo_json = "/var/www/fmt/archivos/generados/Calidad/muestras/" . $sede_san . "/" . $periodo . ".json";

if (!file_exists($archivo_json)) {
    die('El período no existe.');
}

$registros = json_decode(file_get_contents($archivo_json), true) ?: [];
$actualizados = 0;

$camposEditables = ['hora', 'producto', 'lote', 'fecha_muestreo', 'hora_muestreo', 'responsable_muestra', 'cantidad'];

foreach ($registros as &$r) {
    if (!isset($items[$r['id']])) continue;
    foreach ($camposEditables as $campo) {
        if (isset($items[$r['id']][$campo])) {
            $r['datos'][$campo] = trim($items[$r['id']][$campo]);
        }
    }
    $actualizados++;
}
unset($r);

if (@file_put_contents($archivo_json, json_encode($registros, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
    echo "<p>✓ Se corrigieron {$actualizados} ítem(s) del " . htmlspecialchars($fecha) . ".</p>";
    echo '<a href="revision_muestras.php">← Volver a revisión</a>';
} else {
    echo '<p>✗ No se pudo guardar (permisos de disco).</p>';
    echo '<a href="revision_muestras.php">← Volver a revisión</a>';
}
?>
