<?php
// Qué botones de menu_adm.html puede ver el usuario en sesión.
// Lo consume el propio menu_adm.html al cargar (ver filtrarMenu() ahí).
// A diferencia de menu_admin.php, NO exige rol 'adm': lo llama cualquiera
// que entre al menú de Operaciones; los admins simplemente reciben todo.
require_once __DIR__ . '/../sesion.php';
require_once __DIR__ . '/menu_operaciones_lib.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['id_usuario'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Sesión no iniciada.']);
    exit();
}

echo json_encode([
    'status'         => 'success',
    'es_admin'       => ($_SESSION['rol'] ?? '') === 'adm',
    'area_operativa' => obtenerAreaOperativa(),
    'nodos'          => menuOperacionesVisiblesSesion(),
]);
