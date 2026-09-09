<?php
require '../sesion.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['nombre']) || empty($_SESSION['sede']) || empty($_SESSION['area'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Sin sesión activa, sede o área asignada. Recarga la página.']);
    exit;
}

$sede = $_SESSION['sede'];
$area = $_SESSION['area'];

$input_json = file_get_contents("php://input");
$input_array = json_decode($input_json, true);

if (!$input_array || empty($input_array['titulo'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'El título es obligatorio.']);
    exit;
}

$prioridades_validas = ['baja', 'media', 'alta'];
if (empty($input_array['prioridad']) || !in_array($input_array['prioridad'], $prioridades_validas, true)) {
    $input_array['prioridad'] = 'media';
}

// Dos modalidades de tarea:
// - 'dia'    (default, compatible con las tareas creadas antes de este
//             campo): fecha exacta YYYY-MM-DD.
// - 'semana' : sin día exacto — se ubica en uno de los 4 bloques fijos de 7
//              días del mes (ver bucketSemana() en el frontend). El
//              calendario mensual la muestra repetida en todos los días de
//              ese bloque.
$tipo = ($input_array['tipo'] ?? 'dia') === 'semana' ? 'semana' : 'dia';
$input_array['tipo'] = $tipo;

if ($tipo === 'semana') {
    $anio = $input_array['anio'] ?? '';
    $mes  = $input_array['mes'] ?? '';
    $semana = $input_array['semana'] ?? null;

    if (!preg_match('/^\d{4}$/', $anio) || !preg_match('/^(0?[1-9]|1[0-2])$/', $mes) || !in_array((int) $semana, [1, 2, 3, 4], true)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Mes, año y semana son obligatorios y deben ser válidos.']);
        exit;
    }

    $mes = str_pad($mes, 2, '0', STR_PAD_LEFT);
    $input_array['mes'] = $mes;
    $input_array['anio'] = $anio;
    $input_array['semana'] = (int) $semana;
    $mes_tarea = "$anio-$mes";
} else {
    $fecha = $input_array['fecha'] ?? '';
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'La fecha es obligatoria y debe ser válida.']);
        exit;
    }
    // El archivo se rota por el mes de la fecha PROGRAMADA de la tarea, no
    // por la fecha de creación — así el calendario la encuentra al navegar
    // al mes en que va a ejecutarse, sin importar cuándo se haya registrado.
    $mes_tarea = substr($fecha, 0, 7);
}

$nuevo_registro = [
    'id_registro'  => uniqid('TAREA_'),
    'timestamp'    => date('Y-m-d H:i:s'),
    'usuario_sys'  => $_SESSION['nombre'],
    'sede_sys'     => $sede,
    'area_sys'     => $area,
    'estado'       => 'pendiente',
    'datos'        => $input_array
];

// El cronograma se divide por ÁREA además de por sede: Operaciones tiene su
// propio calendario, independiente del que eventualmente tengan Calidad o
// HSEQ, aunque compartan la misma sede física.
$base_dir = "../../archivos/generados/cronograma_mantenimiento/";
$area_dir = $base_dir . preg_replace('/[^A-Za-z0-9_-]/', '', $area) . "/";
$sede_dir = $area_dir . preg_replace('/[^A-Za-z0-9_-]/', '', $sede) . "/";
$archivo_json = $sede_dir . $mes_tarea . ".json";

if (!file_exists($base_dir)) { mkdir($base_dir, 0777, true); }
if (!file_exists($area_dir)) { mkdir($area_dir, 0777, true); }
if (!file_exists($sede_dir)) { mkdir($sede_dir, 0777, true); }

$datos_existentes = file_exists($archivo_json)
    ? (json_decode(file_get_contents($archivo_json), true) ?: [])
    : [];

$datos_existentes[] = $nuevo_registro;

if (@file_put_contents($archivo_json, json_encode($datos_existentes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
    echo json_encode(['status' => 'success', 'message' => 'Tarea creada correctamente.', 'id' => $nuevo_registro['id_registro']]);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'No se pudo guardar la tarea.']);
}
