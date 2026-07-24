<?php
/**
 * procesar.php
 * ---------------------
 * Reemplaza al antiguo muestras_pros.php (Excel/PhpSpreadsheet).
 * Guarda cada muestra como un registro JSON individual con su propia
 * fecha real de registro (fecha_registro), en vez de decidir "el archivo
 * de hoy" a partir de filemtime() — eso es lo que causaba que una jornada
 * que cruza la medianoche se partiera o se confundiera con otra.
 */
require_once '../../sesion.php';
verificarAutenticacion();

$esAjax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';

function responder($success, $extra = []) {
    global $esAjax;
    if ($esAjax) {
        header('Content-Type: application/json');
        echo json_encode(array_merge(['success' => $success], $extra));
    } else {
        echo $success ? '<p>Datos guardados correctamente.</p>' : ('<p>Error: ' . htmlspecialchars($extra['error'] ?? '') . '</p>');
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(false, ['error' => 'Método no permitido.']);
}

$sede = $_SESSION['sede'];
$sede_san = preg_replace('/[^A-Za-z0-9_-]/', '', $sede);

$hora                = $_POST['hora'] ?? '';
$producto            = $_POST['producto'] ?? '';
$lote                = $_POST['lote'] ?? '';
$fecha_muestreo      = $_POST['fecha_muestreo'] ?? '';
$hora_muestreo       = $_POST['hora_muestreo'] ?? '';
$responsable_muestra = $_POST['responsable_muestra'] ?? '';
$cantidad            = $_POST['cantidad'] ?? '';

$base_dir = "/var/www/fmt/archivos/generados/Calidad/muestras/";
$sede_dir = $base_dir . $sede_san . "/";
if (!is_dir($sede_dir)) { mkdir($sede_dir, 0775, true); }

$mes_actual      = date('Y-m');
$fecha_registro  = date('Y-m-d'); // ← fecha real de negocio, no filemtime()
$archivo_json    = $sede_dir . $mes_actual . '.json';

$registros = file_exists($archivo_json)
    ? (json_decode(file_get_contents($archivo_json), true) ?: [])
    : [];

// Numeración del ítem dentro del día: cuenta cuántos registros ya
// existen con esta misma fecha_registro (no la fecha del archivo).
$itemNum = 1;
foreach ($registros as $r) {
    if (($r['datos']['fecha_registro'] ?? '') === $fecha_registro) {
        $itemNum++;
    }
}

$nuevo = [
    'id'          => uniqid('MUE_'),
    'timestamp'   => date('Y-m-d H:i:s'),
    'usuario_sys' => $_SESSION['nombre'],
    'sede_sys'    => $sede,
    'datos'       => [
        'item'                => $itemNum,
        'fecha_registro'      => $fecha_registro,
        'hora'                => $hora,
        'producto'            => $producto,
        'lote'                => $lote,
        'fecha_muestreo'      => $fecha_muestreo,
        'hora_muestreo'       => $hora_muestreo,
        'responsable_muestra' => $responsable_muestra,
        'cantidad'            => $cantidad,
        'disp_fecha'          => null,
        'disp_mejorante'      => null,
        'disp_cantidad'       => null,
        'disp_responsable'    => null,
    ],
];

$registros[] = $nuevo;

if (@file_put_contents($archivo_json, json_encode($registros, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
    responder(true, ['id' => $nuevo['id']]);
} else {
    responder(false, ['error' => 'No se pudo guardar el registro (permisos de disco).']);
}
?>
