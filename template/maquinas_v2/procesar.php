<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../sesion.php';
header('Content-Type: application/json; charset=utf-8');

function sanear_ruta($valor) {
    return preg_replace('/_+/', '_', preg_replace('/[^A-Za-z0-9_\-]/', '_', trim($valor)));
}

$input_json = file_get_contents("php://input");
$input = json_decode($input_json, true);

if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Cuerpo JSON inválido.']);
    exit;
}

$tipo_maquina   = trim($input['tipo_maquina']   ?? '');
$codigo_maquina = trim($input['codigo_maquina'] ?? '');
$nombre_maquina = trim($input['nombre_maquina'] ?? ''); // grupo/carpeta (ej: BALANZAS_ZC)
$accion         = trim($input['accion']         ?? 'registrar');
$corrige_id     = $input['corrige_id']          ?? null;
$codigo_orden   = trim($input['codigo_orden']   ?? '');
$datos          = $input['datos']               ?? [];

if ($tipo_maquina === '' || $codigo_maquina === '' || $nombre_maquina === '') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Faltan datos de identificación de la máquina (tipo, código o nombre).']);
    exit;
}

if ($corrige_id && $codigo_orden === '') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Debe ingresar el Código de Orden de Trabajo para registrar una corrección.']);
    exit;
}

$base_dir   = "../../archivos/generados/maquinas_v2/";
$tipo_dir   = $base_dir . sanear_ruta($tipo_maquina) . "/";
$grupo_dir  = $tipo_dir . sanear_ruta($nombre_maquina) . "/";
$archivo    = $grupo_dir . sanear_ruta($codigo_maquina) . ".json";

foreach ([$base_dir, $tipo_dir, $grupo_dir] as $dir) {
    if (!file_exists($dir)) mkdir($dir, 0777, true);
}

$registros = file_exists($archivo)
    ? (json_decode(file_get_contents($archivo), true) ?: [])
    : [];

$nuevo_registro = [
    'id_registro'   => uniqid('MAQV2_'),
    'timestamp'     => date('Y-m-d H:i:s'),
    'usuario_sys'   => $_SESSION['nombre'] ?? 'anonimo',
    'tipo_registro' => $corrige_id ? 'correccion' : 'verificacion',
    'estado'        => $accion === 'guardar' ? 'borrador' : 'verificado',
    'corrige_id'    => $corrige_id ?: null,
    'codigo_orden'  => $codigo_orden ?: null,
    'datos'         => [
        'tipo_maquina'   => $tipo_maquina,
        'codigo_maquina' => $codigo_maquina,
        'nombre_maquina' => $nombre_maquina,
    ] + $datos
];

$registros[] = $nuevo_registro;

if (@file_put_contents($archivo, json_encode($registros, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
    echo json_encode(['status' => 'success', 'message' => 'Registro guardado correctamente.', 'id' => $nuevo_registro['id_registro']]);
} else {
    $err = error_get_last();
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error de escritura: ' . ($err['message'] ?? 'desconocido')]);
}
