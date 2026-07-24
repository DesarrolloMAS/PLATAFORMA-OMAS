<?php
require_once '../sesion.php';
verificarAutenticacion();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['exito' => false, 'mensaje' => 'Método no permitido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['mes']) || !isset($input['id'])) {
    echo json_encode(['exito' => false, 'mensaje' => 'Faltan parámetros']);
    exit;
}

$mes = $input['mes'];
$id_registro = $input['id'];
$sede = $_SESSION['sede'] ?? 'NA';

$archivo_json = realpath(__DIR__ . "/../../archivos/generados/orden_mantenimiento/{$sede}/{$mes}.json");

if (!$archivo_json || !file_exists($archivo_json)) {
    echo json_encode(['exito' => false, 'mensaje' => 'Archivo de registros no encontrado']);
    exit;
}

$datos = json_decode(file_get_contents($archivo_json), true);

if (!is_array($datos)) {
    echo json_encode(['exito' => false, 'mensaje' => 'Error al leer el archivo de registros']);
    exit;
}

$registro_encontrado = null;
foreach ($datos as $registro) {
    if (isset($registro['id']) && $registro['id'] === $id_registro) {
        $registro_encontrado = $registro;
        break;
    }
}

if (!$registro_encontrado) {
    echo json_encode(['exito' => false, 'mensaje' => 'Registro no encontrado en el archivo']);
    exit;
}

$datos_v2 = $registro_encontrado['datos'] ?? [];

// Especialidad lógica (similar a la original)
$clasificacion = $datos_v2['clasificacion'] ?? 'General';
if (stripos($clasificacion, 'locativ') !== false) {
    $especialidad = 'Locativos';
} elseif (stripos($clasificacion, 'mecanic') !== false) {
    $especialidad = 'Mecánicos';
} else {
    $especialidad = 'General';
}

// Código a reportar en la bitácora: en mantenimientos Locativos no siempre
// hay un equipo con código de inventario asociado, así que se usa el N° de
// Orden de Trabajo (SAP) editado desde el visor. Para el resto de tipos
// (Mecánico, Eléctrico, etc.) se mantiene el código interno del equipo.
$codigo_bitacora = ($especialidad === 'Locativos')
    ? ($datos_v2['numero_orden'] ?? $registro_encontrado['id'])
    : ($datos_v2['codigo_equipo'] ?? '');

// Preparar los datos exactos que espera recoleccion_envio.php
$datosAEnviar = [
    'fechainicial' => $datos_v2['fecha_solicitud'] ?? date('Y-m-d'),
    'horainicial' => $datos_v2['hora_solicitud'] ?? date('H:i'),
    'objeto_dañado' => $datos_v2['objeto_dañado'] ?? 'No especificado',
    'cod' => $codigo_bitacora,
    'ubi' => $datos_v2['ubicacion'] ?? '',
    'descripcion_daños' => $datos_v2['descripcion_falla'] ?? '',
    'responsable-Miembro_De_La_Compañia_0' => $datos_v2['nombre_responsable'] ?? 'No especificado',
    'tipo_mantenimiento_especial' => $clasificacion,
    'zona' => ($sede === 'ZC') ? 'Centro' : 'Sur',
    'especialidad' => $especialidad
];

// Enviar a la bitácora mediante petición HTTP interna
$url_post = 'http://localhost/template/gobierno_datos/bitacora_mantenimiento/recoleccion_envio.php';
$options = [
    'http' => [
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => http_build_query($datosAEnviar),
        'ignore_errors' => true // Permite leer la respuesta incluso si hay un código de error HTTP
    ],
];

$context = stream_context_create($options);
$response = @file_get_contents($url_post, false, $context);

if ($response === false) {
    echo json_encode(['exito' => false, 'mensaje' => 'Error al conectar con el servidor de la bitácora']);
    exit;
}

// Intentar leer los cabezales de respuesta para verificar el código HTTP
$http_code = 0;
if (isset($http_response_header) && is_array($http_response_header)) {
    if (preg_match('/HTTP\/[\d\.]+ (\d+)/', $http_response_header[0], $matches)) {
        $http_code = intval($matches[1]);
    }
}

if ($http_code === 200) {
    echo json_encode(['exito' => true, 'mensaje' => 'Registro cargado exitosamente en la bitácora']);
} else {
    echo json_encode(['exito' => false, 'mensaje' => 'El servidor de la bitácora devolvió un error (Code: ' . $http_code . ')']);
}
