<?php
require '../sesion.php';
verificarAutenticacion();
require '../conection.php';
header('Content-Type: application/json; charset=utf-8');

$codigo = trim($_GET['codigo'] ?? '');
$anio = $_GET['anio'] ?? date('Y');
if ($codigo === '' || !preg_match('/^\d{4}$/', $anio)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Parámetros inválidos.']);
    exit;
}
$anio = (int) $anio;

$stmtMaquina = $pdomaquinas->prepare("SELECT id_maquina FROM catalogo_maquinas WHERE codigo = :codigo");
$stmtMaquina->execute([':codigo' => $codigo]);
$idMaquina = $stmtMaquina->fetchColumn();

$meses = [];
for ($m = 1; $m <= 12; $m++) {
    $meses[str_pad((string) $m, 2, '0', STR_PAD_LEFT)] = [];
}

if ($idMaquina) {
    $stmt = $pdomaquinas->prepare("
        SELECT id, titulo, descripcion, prioridad, responsable, tipo_fecha, fecha, anio, mes, semana, usuario_creador
        FROM maquinas_tareas_programadas
        WHERE id_maquina = :id_maquina
          AND (
            (tipo_fecha = 'dia' AND YEAR(fecha) = :anio1)
            OR (tipo_fecha = 'semana' AND anio = :anio2)
          )
    ");
    $stmt->execute([':id_maquina' => $idMaquina, ':anio1' => $anio, ':anio2' => $anio]);

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
            ],
        ];
    }
}

echo json_encode(['status' => 'success', 'anio' => $anio, 'meses' => $meses]);
