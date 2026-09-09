<?php
require '../sesion.php';
verificarAutenticacion();
require '../conection.php';
header('Content-Type: application/json; charset=utf-8');

$codigo = trim($_GET['codigo'] ?? '');
$mes = $_GET['mes'] ?? date('Y-m');
if ($codigo === '' || !preg_match('/^\d{4}-\d{2}$/', $mes)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Parámetros inválidos.']);
    exit;
}

$stmtMaquina = $pdomaquinas->prepare("SELECT id_maquina FROM catalogo_maquinas WHERE codigo = :codigo");
$stmtMaquina->execute([':codigo' => $codigo]);
$idMaquina = $stmtMaquina->fetchColumn();
if (!$idMaquina) {
    echo json_encode(['status' => 'success', 'mes' => $mes, 'tareas' => []]);
    exit;
}

[$anio, $mesNum] = array_map('intval', explode('-', $mes));

$stmt = $pdomaquinas->prepare("
    SELECT id, titulo, descripcion, prioridad, responsable, tipo_fecha, fecha, anio, mes, semana, usuario_creador
    FROM maquinas_tareas_programadas
    WHERE id_maquina = :id_maquina
      AND (
        (tipo_fecha = 'dia' AND fecha BETWEEN :inicio AND :fin)
        OR (tipo_fecha = 'semana' AND anio = :anio AND mes = :mesNum)
      )
    ORDER BY fecha ASC, id ASC
");
$stmt->execute([
    ':id_maquina' => $idMaquina,
    ':inicio' => "$mes-01",
    ':fin' => date('Y-m-t', strtotime("$mes-01")),
    ':anio' => $anio,
    ':mesNum' => $mesNum,
]);

$tareas = array_map(function ($fila) {
    return [
        'id_registro' => 'TAREA_' . $fila['id'],
        'usuario_sys' => $fila['usuario_creador'],
        'datos' => [
            'tipo' => $fila['tipo_fecha'],
            'titulo' => $fila['titulo'],
            'descripcion' => $fila['descripcion'],
            'prioridad' => $fila['prioridad'],
            'responsable' => $fila['responsable'],
            'fecha' => $fila['fecha'],
            'anio' => $fila['anio'],
            'mes' => $fila['mes'] ? str_pad((string) $fila['mes'], 2, '0', STR_PAD_LEFT) : null,
            'semana' => $fila['semana'],
        ],
    ];
}, $stmt->fetchAll(PDO::FETCH_ASSOC));

echo json_encode(['status' => 'success', 'mes' => $mes, 'tareas' => $tareas]);
