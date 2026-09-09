<?php
require '../sesion.php';
verificarAutenticacion();
require '../conection.php';
header('Content-Type: application/json; charset=utf-8');

$mes = $_GET['mes'] ?? date('Y-m');
if (!preg_match('/^\d{4}-\d{2}$/', $mes)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Formato de mes inválido.']);
    exit;
}
[$anio, $mesNum] = array_map('intval', explode('-', $mes));

$area = $_SESSION['area'] ?? 'Operaciones';

$stmt = $pdomaquinas->prepare("
    SELECT t.id, t.titulo, t.descripcion, t.prioridad, t.responsable, t.tipo_fecha,
           t.fecha, t.anio, t.mes, t.semana, t.usuario_creador,
           m.codigo AS codigo_maquina, m.nombre AS nombre_maquina
    FROM maquinas_tareas_programadas t
    JOIN catalogo_maquinas m ON m.id_maquina = t.id_maquina
    WHERE m.area = :area
      AND (
        (t.tipo_fecha = 'dia' AND t.fecha BETWEEN :inicio AND :fin)
        OR (t.tipo_fecha = 'semana' AND t.anio = :anio AND t.mes = :mesNum)
      )
    ORDER BY t.fecha ASC, t.id ASC
");
$stmt->execute([
    ':area' => $area,
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
            'codigo_maquina' => $fila['codigo_maquina'],
            'nombre_maquina' => $fila['nombre_maquina'],
        ],
    ];
}, $stmt->fetchAll(PDO::FETCH_ASSOC));

echo json_encode(['status' => 'success', 'mes' => $mes, 'tareas' => $tareas]);
