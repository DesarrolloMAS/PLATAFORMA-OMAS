<?php
require '../sesion.php';
verificarAutenticacion();
require '../conection.php';
require '../area_operativa_lib.php';
header('Content-Type: application/json; charset=utf-8');

// Ocultar la barra lateral en el frontend no alcanza — el mismo endpoint
// podría llamarse directo. Ver area_operativa_lib.php: en Operaciones, solo
// área operativa "mantenimiento"; en cualquier otra área, rol '1'; un admin
// siempre puede.
if (!puedeGestionarCalendario()) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'No tienes permiso para programar tareas.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$codigo = trim($input['codigo'] ?? '');
$titulo = trim($input['titulo'] ?? '');
if ($codigo === '' || $titulo === '') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Código y título son obligatorios.']);
    exit;
}

$area = $_SESSION['area'] ?? 'Operaciones';

// Se limita a la propia área: aunque alguien conozca el código de un objeto
// de otra área, no puede programarle tareas.
$stmtMaquina = $pdomaquinas->prepare("SELECT id_maquina FROM catalogo_maquinas WHERE codigo = :codigo AND area = :area AND activo = 1");
$stmtMaquina->execute([':codigo' => $codigo, ':area' => $area]);
$idMaquina = $stmtMaquina->fetchColumn();
if (!$idMaquina) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'No encontrado en el catálogo de tu área.']);
    exit;
}

$prioridadesValidas = ['baja', 'media', 'alta'];
$prioridad = in_array($input['prioridad'] ?? '', $prioridadesValidas, true) ? $input['prioridad'] : 'media';

$tipo = ($input['tipo'] ?? 'dia') === 'semana' ? 'semana' : 'dia';

$fecha = null; $anio = null; $mes = null; $semana = null;

if ($tipo === 'semana') {
    $anio = $input['anio'] ?? '';
    $mes  = $input['mes'] ?? '';
    $semana = $input['semana'] ?? null;
    if (!preg_match('/^\d{4}$/', $anio) || !preg_match('/^(0?[1-9]|1[0-2])$/', $mes) || !in_array((int) $semana, [1, 2, 3, 4], true)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Mes, año y semana son obligatorios y deben ser válidos.']);
        exit;
    }
    $mes = (int) $mes;
    $anio = (int) $anio;
    $semana = (int) $semana;
} else {
    $fecha = $input['fecha'] ?? '';
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'La fecha es obligatoria y debe ser válida.']);
        exit;
    }
}

$stmt = $pdomaquinas->prepare("
    INSERT INTO maquinas_tareas_programadas
        (id_maquina, titulo, descripcion, prioridad, responsable, tipo_fecha, fecha, anio, mes, semana, usuario_creador, sede)
    VALUES
        (:id_maquina, :titulo, :descripcion, :prioridad, :responsable, :tipo_fecha, :fecha, :anio, :mes, :semana, :usuario_creador, :sede)
");
$stmt->execute([
    ':id_maquina'      => $idMaquina,
    ':titulo'          => $titulo,
    ':descripcion'     => trim($input['descripcion'] ?? ''),
    ':prioridad'       => $prioridad,
    ':responsable'     => trim($input['responsable'] ?? ''),
    ':tipo_fecha'      => $tipo,
    ':fecha'           => $fecha,
    ':anio'            => $anio,
    ':mes'             => $mes,
    ':semana'          => $semana,
    ':usuario_creador' => $_SESSION['nombre'] ?? null,
    ':sede'            => $_SESSION['sede'] ?? null,
]);

echo json_encode(['status' => 'success', 'message' => 'Tarea creada correctamente.', 'id' => $pdomaquinas->lastInsertId()]);
