<?php
// Aumentar límites para manejar JSON con firmas en base64
ini_set('post_max_size', '32M');
ini_set('upload_max_filesize', '32M');
ini_set('memory_limit', '256M');

include '../../sesion.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['nombre']) || empty($_SESSION['sede'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Sin sesión activa o sede asignada.']);
    exit;
}

$sede = $_SESSION['sede'];

// Leer cuerpo JSON
$input_json = file_get_contents("php://input");

if (empty($input_json)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Sin datos recibidos. El JSON puede superar el límite del servidor (post_max_size).']);
    exit;
}

$input_array = json_decode($input_json, true);

if (!$input_array) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'JSON inválido: ' . json_last_error_msg()]);
    exit;
}

$mes_actual = date('Y-m');

$nuevo_registro = [
    'id_registro'  => uniqid('PERMISO_'),
    'timestamp'    => date('Y-m-d H:i:s'),
    'usuario_sys'  => $_SESSION['nombre'],
    'sede_sys'     => $sede,
    'id_flujo'     => $input_array['id_flujo'] ?? null,
    'datos'        => $input_array
];

$base_dir  = realpath(__DIR__ . '/../../../archivos/generados/permiso_trabajo') . '/';
$sede_slug = preg_replace('/[^A-Za-z0-9_-]/', '', $sede);

if (empty($sede_slug)) {
    $sede_slug = 'General';
}

$sede_dir    = $base_dir . $sede_slug . '/';
$archivo_json = $sede_dir . 'PERMISO_' . $mes_actual . '.json';

// Crear directorios si no existen
if (!is_dir($base_dir)) {
    if (!mkdir($base_dir, 0777, true)) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'No se pudo crear directorio base: ' . $base_dir]);
        exit;
    }
}

if (!is_dir($sede_dir)) {
    if (!mkdir($sede_dir, 0777, true)) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'No se pudo crear directorio de sede: ' . $sede_dir]);
        exit;
    }
}

if (!is_writable($sede_dir)) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Sin permiso de escritura en: ' . $sede_dir]);
    exit;
}

// Leer registros existentes del mes
$datos_existentes = [];
if (file_exists($archivo_json)) {
    $contenido = file_get_contents($archivo_json);
    if (!empty($contenido)) {
        $decoded = json_decode($contenido, true);
        if (is_array($decoded)) {
            $datos_existentes = $decoded;
        }
    }
}

$datos_existentes[] = $nuevo_registro;

$resultado = file_put_contents(
    $archivo_json,
    json_encode($datos_existentes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
    LOCK_EX
);

if ($resultado !== false) {
    echo json_encode(['status' => 'success', 'id' => $nuevo_registro['id_registro']]);
} else {
    $err = error_get_last();
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Error de escritura en disco.',
        'detalle' => $err['message'] ?? 'desconocido',
        'ruta'    => $archivo_json
    ]);
}
?>
