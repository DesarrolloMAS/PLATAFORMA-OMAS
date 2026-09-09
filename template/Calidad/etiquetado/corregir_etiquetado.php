<?php
require_once '../../conection.php';
require_once '../../sesion.php';
verificarAutenticacion();
header('Content-Type: application/json; charset=utf-8');

$zonas_validas = ['ZC', 'ZS'];

$input   = json_decode(file_get_contents('php://input'), true);
$zona    = $input['zona'] ?? '';
$file    = basename($input['file'] ?? '');
$updates = $input['updates'] ?? [];

if (!in_array($zona, $zonas_validas, true) || $file === '' || pathinfo($file, PATHINFO_EXTENSION) !== 'json' || !is_array($updates)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Datos inválidos.']);
    exit;
}

$ruta = __DIR__ . '/../../../archivos/generados/Calidad/etiquetado/' . $zona . '/' . $file;

if (!file_exists($ruta)) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'El registro no existe o fue eliminado.']);
    exit;
}

$contenido = json_decode(file_get_contents($ruta), true);
if (!is_array($contenido)) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'El archivo está dañado.']);
    exit;
}

// Solo estas raíces del documento pueden corregirse desde el visor.
$campos_permitidos = ['fecha_inspeccion', 'hora_inspeccion', 'responsable', 'seccion2', 'seccion3', 'seccion4', 'dinamicas'];

function limpiarValor($v) {
    if (is_array($v)) {
        $out = [];
        foreach ($v as $k => $vv) $out[$k] = limpiarValor($vv);
        return $out;
    }
    return is_string($v) ? trim($v) : $v;
}

$cambios = 0;

foreach ($updates as $campo => $valor) {
    if (!in_array($campo, $campos_permitidos, true)) continue;

    if ($campo === 'fecha_inspeccion') {
        $valor = trim((string)$valor);
        if ($valor === '' || $valor === '—') {
            $contenido['fecha_inspeccion'] = '';
        } else {
            $dt = DateTime::createFromFormat('d/m/Y', $valor);
            if (!$dt) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Formato de fecha de inspección inválido. Use dd/mm/aaaa.']);
                exit;
            }
            $contenido['fecha_inspeccion'] = $dt->format('Y-m-d');
        }
        $cambios++;
        continue;
    }

    $valor = limpiarValor($valor);

    if (is_array($valor) && is_array($contenido[$campo] ?? null)) {
        $contenido[$campo] = array_replace_recursive($contenido[$campo], $valor);
    } else {
        $contenido[$campo] = $valor;
    }
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
