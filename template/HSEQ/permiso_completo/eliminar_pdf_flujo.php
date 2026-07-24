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

if (!$input || empty($input['id_flujo'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Datos incompletos.']);
    exit;
}

$sede_dir    = "../../../archivos/generados/flujo_permisos/" . $sede_san . "/";
$upload_dir  = $sede_dir . "documentos_pdf/";
$actualizado = false;

foreach (glob($sede_dir . "*.json") as $archivo) {
    $datos = json_decode(file_get_contents($archivo), true) ?: [];
    foreach ($datos as &$flujo) {
        if ($flujo['id_flujo'] !== $input['id_flujo']) continue;
        if (!empty($flujo['documento_pdf']['nombre_archivo'])) {
            @unlink($upload_dir . $flujo['documento_pdf']['nombre_archivo']);
        }
        $flujo['documento_pdf'] = null;
        $actualizado = true;
        break;
    }
    unset($flujo);
    if ($actualizado) {
        file_put_contents($archivo, json_encode($datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        break;
    }
}

echo json_encode($actualizado
    ? ['status' => 'success']
    : ['status' => 'error', 'message' => 'Flujo no encontrado.']
);
?>
