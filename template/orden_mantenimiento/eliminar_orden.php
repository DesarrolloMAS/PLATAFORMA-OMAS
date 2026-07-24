<?php
require_once '../sesion.php';
verificarAutenticacion();
header('Content-Type: application/json');

// Solo administradores pueden eliminar
$rol = $_SESSION['rol'] ?? '';
if ($rol !== 'adm' && $rol !== '1') {
    echo json_encode(['success' => false, 'error' => 'Sin permisos para eliminar registros']);
    exit;
}

$input  = json_decode(file_get_contents('php://input'), true);
$accion = $input['accion'] ?? ''; // 'una' | 'periodo'
$mes    = $input['mes']    ?? '';
$id     = $input['id']     ?? '';

$sede = $_SESSION['sede'] ?? 'NA';

// Sanitizar período
$mes = basename($mes);
if (!preg_match('/^\d{4}-\d{2}$/', $mes)) {
    echo json_encode(['success' => false, 'error' => 'Período inválido']);
    exit;
}

$json_path = "../../archivos/generados/orden_mantenimiento/{$sede}/{$mes}.json";
$evid_dir  = "../../archivos/generados/orden_mantenimiento/evidencias/";

if (!file_exists($json_path)) {
    echo json_encode(['success' => false, 'error' => 'Archivo de período no encontrado']);
    exit;
}

$registros = json_decode(file_get_contents($json_path), true) ?: [];

// Elimina los archivos físicos (firmas + evidencias) de un registro
function borrarArchivosRegistro($reg, $evid_dir) {
    foreach ($reg['firmas'] ?? [] as $fname) {
        if ($fname && file_exists($evid_dir . $fname)) @unlink($evid_dir . $fname);
    }
    foreach ($reg['evidencias'] ?? [] as $fname) {
        if ($fname && file_exists($evid_dir . $fname)) @unlink($evid_dir . $fname);
    }
}

// ── Eliminar una sola orden ───────────────────────────────────────────────────
if ($accion === 'una') {
    if (!$id) {
        echo json_encode(['success' => false, 'error' => 'Falta el ID del registro']);
        exit;
    }

    $nuevos    = [];
    $eliminado = false;

    foreach ($registros as $reg) {
        if ($reg['id'] === $id) {
            borrarArchivosRegistro($reg, $evid_dir);
            $eliminado = true;
        } else {
            $nuevos[] = $reg;
        }
    }

    if (!$eliminado) {
        echo json_encode(['success' => false, 'error' => 'Registro no encontrado']);
        exit;
    }

    if (empty($nuevos)) {
        @unlink($json_path); // Borrar el JSON si quedó vacío
    } else {
        file_put_contents($json_path, json_encode(array_values($nuevos), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    echo json_encode(['success' => true, 'message' => 'Orden eliminada correctamente']);

// ── Eliminar todo el período (mes completo) ───────────────────────────────────
} elseif ($accion === 'periodo') {

    foreach ($registros as $reg) {
        borrarArchivosRegistro($reg, $evid_dir);
    }
    @unlink($json_path);

    echo json_encode(['success' => true, 'message' => 'Período eliminado correctamente (' . count($registros) . ' registros)']);

} else {
    echo json_encode(['success' => false, 'error' => 'Acción no reconocida']);
}
