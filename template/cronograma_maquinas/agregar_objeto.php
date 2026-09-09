<?php
require '../sesion.php';
verificarAutenticacion();
require '../conection.php';
require '../area_operativa_lib.php';
header('Content-Type: application/json; charset=utf-8');

if (!puedeGestionarCalendario()) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'No tienes permiso para agregar ' . mb_strtolower(terminoObjeto(true), 'UTF-8') . '.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$codigo = trim($input['codigo'] ?? '');
$nombre = trim($input['nombre'] ?? '');
$ubicacion = trim($input['ubicacion'] ?? '');

if ($codigo === '' || $nombre === '') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Código y nombre son obligatorios.']);
    exit;
}

$area = $_SESSION['area'] ?? 'Operaciones';

// El código es único en todo el catálogo (comparte tabla entre áreas), pero
// si ya existe y pertenece a OTRA área no se debe filtrar esa colisión —
// simplemente se informa como "ya existe", sin revelar a qué área pertenece.
$stmtExiste = $pdomaquinas->prepare("SELECT area FROM catalogo_maquinas WHERE codigo = :codigo");
$stmtExiste->execute([':codigo' => $codigo]);
if ($stmtExiste->fetchColumn() !== false) {
    http_response_code(409);
    echo json_encode(['status' => 'error', 'message' => 'Ese código ya existe en el catálogo.']);
    exit;
}

$stmt = $pdomaquinas->prepare("
    INSERT INTO catalogo_maquinas (codigo, area, nombre, ubicacion, proceso, activo)
    VALUES (:codigo, :area, :nombre, :ubicacion, :proceso, 1)
");
$stmt->execute([
    ':codigo' => $codigo,
    ':area' => $area,
    ':nombre' => $nombre,
    ':ubicacion' => $ubicacion !== '' ? $ubicacion : null,
    ':proceso' => substr($codigo, 0, 3),
]);

echo json_encode(['status' => 'success', 'message' => terminoObjeto() . ' ' . terminoObjetoVerbo('agregada', 'agregado') . ' correctamente.']);
