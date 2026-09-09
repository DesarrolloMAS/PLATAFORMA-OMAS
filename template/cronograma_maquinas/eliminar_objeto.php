<?php
require '../sesion.php';
verificarAutenticacion();
require '../conection.php';
require '../area_operativa_lib.php';
header('Content-Type: application/json; charset=utf-8');

if (!puedeGestionarCalendario()) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'No tienes permiso para eliminar ' . mb_strtolower(terminoObjeto(true), 'UTF-8') . '.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$codigo = trim($input['codigo'] ?? '');
if ($codigo === '') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Código obligatorio.']);
    exit;
}

$area = $_SESSION['area'] ?? 'Operaciones';

// Baja lógica (activo=0), no DELETE — igual que con las órdenes de
// mantenimiento: el catálogo puede "olvidar" el objeto de la lista activa,
// pero las tareas ya programadas para él no deben desaparecer del
// historial. Se limita a la propia área: no se puede dar de baja un
// objeto de otra área aunque se conozca su código.
$stmt = $pdomaquinas->prepare("UPDATE catalogo_maquinas SET activo = 0 WHERE codigo = :codigo AND area = :area");
$stmt->execute([':codigo' => $codigo, ':area' => $area]);

if ($stmt->rowCount() === 0) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'No se encontró ese código en tu área.']);
    exit;
}

echo json_encode(['status' => 'success', 'message' => terminoObjeto() . ' ' . terminoObjetoVerbo('eliminada', 'eliminado') . ' correctamente.']);
