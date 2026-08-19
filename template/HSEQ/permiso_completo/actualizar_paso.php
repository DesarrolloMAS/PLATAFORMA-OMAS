<?php
include '../../sesion.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['nombre']) || empty($_SESSION['sede'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Sin sesión.']);
    exit;
}

$sede_san = preg_replace('/[^A-Za-z0-9_-]/', '', $_SESSION['sede']);
$input    = json_decode(file_get_contents("php://input"), true);

if (!$input || empty($input['id_flujo']) || empty($input['paso'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Datos incompletos.']);
    exit;
}

if (!in_array($input['paso'], ['permiso', 'analisis', 'inspeccion'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Paso inválido.']);
    exit;
}

$sede_dir   = "../../../archivos/generados/flujo_permisos/" . $sede_san . "/";
$actualizado = false;

foreach (glob($sede_dir . "*.json") as $archivo) {
    $datos = json_decode(file_get_contents($archivo), true) ?: [];
    foreach ($datos as &$flujo) {
        if ($flujo['id_flujo'] !== $input['id_flujo']) continue;
        $flujo['pasos'][$input['paso']]['completado']   = true;
        $flujo['pasos'][$input['paso']]['timestamp']    = date('Y-m-d H:i:s');
        if (!empty($input['id_registro'])) {
            $flujo['pasos'][$input['paso']]['id_registro'] = $input['id_registro'];
        }
        $todos_ok = $flujo['pasos']['permiso']['completado']
                 && $flujo['pasos']['analisis']['completado']
                 && $flujo['pasos']['inspeccion']['completado'];
        if ($todos_ok) $flujo['estado'] = 'completado';
        $actualizado = true;
        break;
    }
    unset($flujo);
    if ($actualizado) {
        file_put_contents($archivo, json_encode($datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
        break;
    }
}

echo json_encode($actualizado
    ? ['status' => 'success']
    : ['status' => 'error', 'message' => 'Flujo no encontrado.']
);
?>
