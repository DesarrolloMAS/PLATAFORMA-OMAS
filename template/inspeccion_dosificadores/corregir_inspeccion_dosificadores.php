<?php
include '../sesion.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['nombre']) || empty($_SESSION['sede'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Sin sesión activa.']);
    exit;
}

$sede = $_SESSION['sede'];
$sede_saneada = preg_replace('/[^A-Za-z0-9_-]/', '', $sede);
$target_dir = "../../archivos/generados/inspeccion_dosificadores/" . $sede_saneada . "/";

$input   = json_decode(file_get_contents('php://input'), true);
$file    = basename($input['file'] ?? '');
$updates = $input['updates'] ?? [];

if ($file === '' || !preg_match('/^INSPECCION_.*\.json$/i', $file) || !is_array($updates)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Datos inválidos.']);
    exit;
}

$ruta = $target_dir . $file;

if (!file_exists($ruta)) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'El registro no existe o fue eliminado.']);
    exit;
}

$contenido = json_decode(file_get_contents($ruta), true);
if (!is_array($contenido) || empty($contenido['datos'])) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'El archivo está dañado.']);
    exit;
}

$campos_permitidos = [
    'dosificador', 'microingrediente', 'fecha',
    'cantidad_bulto_50kg', 'carga_trigo', 'extraccion_pct',
    'bultos_por_hora', 'micro_por_minuto', 'micro_por_hora',
    'micro_min_limite_inferior', 'micro_min_limite_superior',
    'micro_hora_limite_inferior', 'micro_hora_limite_superior',
    'porcentaje_dosificador', 'frecuencia_dosificador',
    'inspeccionado_por', 'verificado_por',
    'gramos_prueba_1', 'gramos_prueba_2', 'gramos_prueba_3', 'gramos_prueba_4', 'gramos_prueba_5',
    'gramos_prueba_6', 'gramos_prueba_7', 'gramos_prueba_8', 'gramos_prueba_9', 'gramos_prueba_10',
    'promedio_min', 'gramos_hora', 'cumple', 'observaciones',
];

$cambios = 0;

foreach ($updates as $campo => $valor) {
    if (!in_array($campo, $campos_permitidos, true)) continue;

    if ($campo === 'fecha') {
        $valor = trim((string)$valor);
        if ($valor === '') {
            $contenido['datos']['fecha'] = '';
        } else {
            $dt = DateTime::createFromFormat('d/m/Y', $valor);
            if (!$dt) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Formato de fecha inválido. Use dd/mm/aaaa.']);
                exit;
            }
            $contenido['datos']['fecha'] = $dt->format('Y-m-d');
        }
        $cambios++;
        continue;
    }

    if ($campo === 'cumple') {
        $valor = trim((string)$valor);
        if (!in_array($valor, ['CUMPLE', 'NO CUMPLE', 'N/A', ''], true)) continue;
        $contenido['datos']['cumple'] = $valor;
        $cambios++;
        continue;
    }

    $valor = is_string($valor) ? trim($valor) : $valor;
    if (is_string($valor) && $valor !== '' && is_numeric($valor)) {
        $valor = (strpos($valor, '.') !== false) ? (float)$valor : (int)$valor;
    }
    $contenido['datos'][$campo] = $valor;
    $cambios++;
}

if ($cambios === 0) {
    echo json_encode(['status' => 'error', 'message' => 'No hay cambios para guardar.']);
    exit;
}

if (file_put_contents($ruta, json_encode($contenido, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
    echo json_encode(['status' => 'success', 'message' => 'Cambios guardados correctamente.']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'No se pudo guardar el archivo (permisos de disco).']);
}
?>
