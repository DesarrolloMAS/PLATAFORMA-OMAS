<?php
/**
 * eliminar_items.php
 * ---------------------
 * Reemplaza a eliminar_fila.php: elimina del JSON los ítems seleccionados
 * (por id, ya no por número de fila de Excel) y renumera el campo "item"
 * de los que queden con la misma fecha_registro.
 */
require_once '../../sesion.php';
verificarAutenticacion();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['items_eliminar']) || !is_array($_POST['items_eliminar'])) {
    echo "<script>alert('Acceso no permitido.'); window.location.href='revision_muestras.php';</script>";
    exit;
}

$sede = $_SESSION['sede'];
$sede_san = preg_replace('/[^A-Za-z0-9_-]/', '', $sede);

$periodo = $_POST['periodo'] ?? '';
$fecha   = $_POST['fecha'] ?? '';
$idsEliminar = $_POST['items_eliminar'];

if (empty($periodo)) {
    die('Error: No se recibió el período de origen.');
}

$archivo_json = "/var/www/fmt/archivos/generados/Calidad/muestras/" . $sede_san . "/" . basename($periodo) . ".json";

if (!file_exists($archivo_json)) {
    die('Error: El período "' . htmlspecialchars($periodo) . '" no existe.');
}

$registros = json_decode(file_get_contents($archivo_json), true) ?: [];

$restantes = array_values(array_filter($registros, fn($r) => !in_array($r['id'], $idsEliminar, true)));
$eliminados = count($registros) - count($restantes);

// Renumerar el campo "item" de los registros que compartan la misma fecha_registro
$contadorPorFecha = [];
foreach ($restantes as &$r) {
    $f = $r['datos']['fecha_registro'] ?? '';
    $contadorPorFecha[$f] = ($contadorPorFecha[$f] ?? 0) + 1;
    $r['datos']['item'] = $contadorPorFecha[$f];
}
unset($r);

if (@file_put_contents($archivo_json, json_encode($restantes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
    echo "<p>✓ Se eliminaron {$eliminados} ítem(s) y se reordenaron los ítems del " . htmlspecialchars($fecha) . ".</p>";
    echo '<a href="muestras_form2.php?periodo=' . urlencode($periodo) . '&fecha=' . urlencode($fecha) . '">← Volver</a>';
} else {
    echo '<p>✗ No se pudo guardar (permisos de disco).</p>';
    echo '<a href="revision_muestras.php">← Volver a revisión</a>';
}
?>
