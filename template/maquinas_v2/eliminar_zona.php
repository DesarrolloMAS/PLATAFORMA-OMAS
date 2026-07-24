<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../sesion.php';
verificarAutenticacion();

header('Content-Type: application/json; charset=utf-8');

function sanear_ruta($valor) {
    return preg_replace('/_+/', '_', preg_replace('/[^A-Za-z0-9_\-]/', '_', trim($valor)));
}

function eliminar_directorio($ruta) {
    foreach (scandir($ruta) as $item) {
        if ($item === '.' || $item === '..') continue;
        $ruta_item = $ruta . '/' . $item;
        if (is_dir($ruta_item)) {
            eliminar_directorio($ruta_item);
        } else {
            unlink($ruta_item);
        }
    }
    rmdir($ruta);
}

$input = json_decode(file_get_contents("php://input"), true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Cuerpo JSON inválido.']);
    exit;
}

$tipo = trim($input['tipo'] ?? '');
if ($tipo === '') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Falta el tipo de máquina a eliminar.']);
    exit;
}

$directorio = __DIR__ . "/../../archivos/generados/maquinas_v2/" . sanear_ruta($tipo);

if (!is_dir($directorio)) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'No hay registros para este tipo de máquina.']);
    exit;
}

$total = 0;
$iterador = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directorio, FilesystemIterator::SKIP_DOTS));
foreach ($iterador as $archivo) {
    if (strtolower($archivo->getExtension()) === 'json') {
        $registros = json_decode(file_get_contents($archivo->getPathname()), true);
        if (is_array($registros)) $total += count($registros);
    }
}

eliminar_directorio($directorio);

echo json_encode(['status' => 'success', 'message' => "Se eliminaron {$total} registros del tipo '{$tipo}'.", 'eliminados' => $total]);
