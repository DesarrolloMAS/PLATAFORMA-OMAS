<?php
/**
 * eliminar_dia.php
 * ---------------------
 * Reemplaza a eliminar_muestras.php: elimina TODOS los registros de los
 * días seleccionados en la galería (equivalente a borrar "el archivo"
 * completo en el sistema legacy).
 */
require_once '../../sesion.php';
verificarAutenticacion();
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$dias = $input['dias'] ?? [];

if (empty($dias) || !is_array($dias)) {
    echo json_encode(['status' => 'error', 'message' => 'No se recibieron días para eliminar.']);
    exit;
}

$sede = $_SESSION['sede'];
$sede_san = preg_replace('/[^A-Za-z0-9_-]/', '', $sede);
$sede_dir = "/var/www/fmt/archivos/generados/Calidad/muestras/" . $sede_san . "/";

// Agrupar por periodo para tocar cada archivo una sola vez
$porPeriodo = [];
foreach ($dias as $d) {
    $periodo = preg_replace('/[^A-Za-z0-9_-]/', '', $d['periodo'] ?? '');
    $fecha   = $d['fecha'] ?? '';
    if (!$periodo || !$fecha) continue;
    $porPeriodo[$periodo][] = $fecha;
}

$eliminados = 0;
$errores = 0;

foreach ($porPeriodo as $periodo => $fechas) {
    $archivo_json = $sede_dir . $periodo . '.json';
    if (!file_exists($archivo_json)) { $errores++; continue; }

    $registros = json_decode(file_get_contents($archivo_json), true) ?: [];
    $antes = count($registros);

    $restantes = array_values(array_filter($registros, function ($r) use ($fechas) {
        return !in_array($r['datos']['fecha_registro'] ?? '', $fechas, true);
    }));

    $eliminados += ($antes - count($restantes));

    if (!@file_put_contents($archivo_json, json_encode($restantes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
        $errores++;
    }
}

if ($errores > 0) {
    echo json_encode(['status' => 'success', 'message' => "Se eliminaron $eliminados registro(s). Hubo $errores error(es) de escritura."]);
} else {
    echo json_encode(['status' => 'success', 'message' => "Se eliminaron $eliminados registro(s) de " . count($dias) . " día(s) correctamente."]);
}
?>
