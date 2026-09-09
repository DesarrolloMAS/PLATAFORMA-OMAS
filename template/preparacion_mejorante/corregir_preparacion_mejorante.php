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
$target_dir = "../../archivos/generados/preparacion_mejorante/" . $sede_saneada . "/";

$input       = json_decode(file_get_contents('php://input'), true);
$file        = basename($input['file'] ?? '');
$id_registro = trim((string)($input['id_registro'] ?? ''));
$updates     = $input['updates'] ?? [];
$mejorantes  = $input['mejorantes'] ?? null;

if ($file === '' || !preg_match('/^PMEJ_.*\.json$/i', $file) || $id_registro === '' || !is_array($updates)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Datos inválidos.']);
    exit;
}

$ruta = $target_dir . $file;

if (!file_exists($ruta)) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'El archivo no existe o fue eliminado.']);
    exit;
}

$registros = json_decode(file_get_contents($ruta), true);
if (!is_array($registros)) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'El archivo está dañado.']);
    exit;
}

$idx = null;
foreach ($registros as $i => $r) {
    if (($r['id_registro'] ?? '') === $id_registro) { $idx = $i; break; }
}

if ($idx === null) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'El registro no existe o fue eliminado.']);
    exit;
}

function parseFechaDMY($valor) {
    $valor = trim((string)$valor);
    if ($valor === '') return '';
    $dt = DateTime::createFromFormat('d/m/Y', $valor);
    if (!$dt) return false;
    return $dt->format('Y-m-d');
}

$campos_permitidos = [
    'fecha', 'referencia', 'lote', 'vence', 'tiempo_mezcla_min',
    'total', 'devolucion', 'realiza', 'verifica', 'observaciones',
];

$cambios = 0;

foreach ($updates as $campo => $valor) {
    if (!in_array($campo, $campos_permitidos, true)) continue;

    if ($campo === 'fecha' || $campo === 'vence') {
        $parsed = parseFechaDMY($valor);
        if ($parsed === false) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Formato de fecha inválido. Use dd/mm/aaaa.']);
            exit;
        }
        $registros[$idx]['datos'][$campo] = $parsed;
        $cambios++;
        continue;
    }

    $valor = is_string($valor) ? trim($valor) : $valor;
    if ($campo === 'tiempo_mezcla_min' && $valor !== '' && is_numeric($valor)) {
        $valor = (strpos($valor, '.') !== false) ? (float)$valor : (int)$valor;
    }
    $registros[$idx]['datos'][$campo] = $valor;
    $cambios++;
}

if ($mejorantes !== null) {
    if (!is_array($mejorantes)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Formato de mejorantes inválido.']);
        exit;
    }

    $nuevos = [];
    foreach ($mejorantes as $m) {
        $nombre     = trim((string)($m['nombre'] ?? ''));
        $lote       = trim((string)($m['lote'] ?? ''));
        $fecha_venc = trim((string)($m['fecha_venc'] ?? ''));
        $cantidad   = trim((string)($m['cantidad'] ?? ''));

        // Descarta filas de mejorantes fijos que quedaron sin datos (sin uso ese día)
        if ($nombre === '' || ($lote === '' && $fecha_venc === '' && $cantidad === '')) continue;

        $fecha_parsed = parseFechaDMY($fecha_venc);
        if ($fecha_parsed === false) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => "Fecha de vencimiento inválida para \"$nombre\". Use dd/mm/aaaa."]);
            exit;
        }

        $nuevos[] = [
            'nombre'     => $nombre,
            'lote'       => $lote,
            'fecha_venc' => $fecha_parsed,
            'cantidad'   => $cantidad,
        ];
    }

    $registros[$idx]['datos']['mejorantes'] = $nuevos;
    $cambios++;
}

if ($cambios === 0) {
    echo json_encode(['status' => 'error', 'message' => 'No hay cambios para guardar.']);
    exit;
}

if (file_put_contents($ruta, json_encode($registros, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
    echo json_encode(['status' => 'success', 'message' => 'Cambios guardados correctamente.']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'No se pudo guardar el archivo (permisos de disco).']);
}
