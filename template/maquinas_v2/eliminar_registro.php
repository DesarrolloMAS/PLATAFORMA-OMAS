<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../sesion.php';
verificarAutenticacion();

header('Content-Type: application/json; charset=utf-8');

function sanear_ruta($valor) {
    return preg_replace('/_+/', '_', preg_replace('/[^A-Za-z0-9_\-]/', '_', trim($valor)));
}

$input = json_decode(file_get_contents("php://input"), true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Cuerpo JSON inválido.']);
    exit;
}

$tipo   = trim($input['tipo']    ?? '');
$grupo  = trim($input['maquina'] ?? '');
$codigo = trim($input['codigo']  ?? '');
$id     = trim($input['id']      ?? '');

if ($tipo === '' || $grupo === '' || $codigo === '' || $id === '') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Faltan parámetros para eliminar el registro.']);
    exit;
}

$archivo = __DIR__ . "/../../archivos/generados/maquinas_v2/" . sanear_ruta($tipo) . "/" . sanear_ruta($grupo) . "/" . sanear_ruta($codigo) . ".json";

if (!file_exists($archivo)) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'No se encontró el historial de esta máquina.']);
    exit;
}

$registros = json_decode(file_get_contents($archivo), true) ?: [];
$originales = count($registros);
$registros = array_values(array_filter($registros, fn($r) => ($r['id_registro'] ?? '') !== $id));

if (count($registros) === $originales) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'El registro no existe o ya fue eliminado.']);
    exit;
}

if (empty($registros)) {
    // Sin registros restantes: se elimina el archivo de la máquina por completo
    unlink($archivo);
} else {
    file_put_contents($archivo, json_encode($registros, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

echo json_encode(['status' => 'success', 'message' => 'Registro eliminado correctamente.']);
