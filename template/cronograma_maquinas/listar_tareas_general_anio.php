<?php
require '../sesion.php';
verificarAutenticacion();
require '../conection.php';
header('Content-Type: application/json; charset=utf-8');

$anio = $_GET['anio'] ?? date('Y');
if (!preg_match('/^\d{4}$/', $anio)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Año inválido.']);
    exit;
}
$anio = (int) $anio;

$meses = [];
for ($m = 1; $m <= 12; $m++) {
    $meses[str_pad((string) $m, 2, '0', STR_PAD_LEFT)] = [];
}

$area = $_SESSION['area'] ?? 'Operaciones';

$stmt = $pdomaquinas->prepare("
    SELECT t.id, t.titulo, t.descripcion, t.prioridad, t.responsable, t.tipo_fecha,
           t.fecha, t.anio, t.mes, t.semana, t.usuario_creador,
           m.codigo AS codigo_maquina, m.nombre AS nombre_maquina
    FROM maquinas_tareas_programadas t
    JOIN catalogo_maquinas m ON m.id_maquina = t.id_maquina
    WHERE m.area = :area
      AND (
        (t.tipo_fecha = 'dia' AND YEAR(t.fecha) = :anio1)
        OR (t.tipo_fecha = 'semana' AND t.anio = :anio2)
      )
");
$stmt->execute([':area' => $area, ':anio1' => $anio, ':anio2' => $anio]);

foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
    $mesKey = $fila['tipo_fecha'] === 'dia'
        ? substr($fila['fecha'], 5, 2)
        : str_pad((string) $fila['mes'], 2, '0', STR_PAD_LEFT);

    $meses[$mesKey][] = [
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
}

echo json_encode(['status' => 'success', 'anio' => $anio, 'meses' => $meses]);
