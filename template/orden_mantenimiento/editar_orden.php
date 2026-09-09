<?php
require_once '../sesion.php';
verificarAutenticacion();

header('Content-Type: application/json');

// Solo el líder de mantenimiento (o un admin) puede asignar el N° de Orden
// — antes cualquier usuario autenticado podía hacerlo. Se gatea por Cargo,
// no por `rol`, a propósito: `rol` decide a qué menú aterriza cada quien al
// iniciar sesión (ver menu_adm.html/menu.html), y "Lider de Mantenimiento"
// hoy comparte el rol genérico '1' con cualquier operario — reutilizar ese
// campo aquí habría significado subirlos también a rol 2/adm y cambiarles
// el menú de entrada, un efecto colateral no buscado por este cambio.
$cargosAutorizadosNumeroOrden = ['Lider de Mantenimiento', 'Lider de Mantenimiento Locativo', 'Lider de Mantenimiento Mecanico'];
$esAdmin = ($_SESSION['rol'] ?? '') === 'adm';
$esLiderMantenimiento = in_array($_SESSION['cargo'] ?? '', $cargosAutorizadosNumeroOrden, true);
if (!$esAdmin && !$esLiderMantenimiento) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Solo el líder de mantenimiento puede asignar el número de orden.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$file        = $data['file']   ?? '';
$id          = $data['id']     ?? '';
$nuevo_numero = trim($data['numero'] ?? '');
$sede        = $_SESSION['sede'] ?? 'NA';

if (!$file || !$id || $nuevo_numero === '') {
    echo json_encode(['success' => false, 'error' => 'Parámetros incompletos']);
    exit;
}

// Validar que no contenga caracteres peligrosos en el nombre del archivo
if (!preg_match('/^\d{4}-\d{2}$/', $file)) {
    echo json_encode(['success' => false, 'error' => 'Nombre de archivo inválido']);
    exit;
}

$path = "../../archivos/generados/orden_mantenimiento/" . $sede . "/" . $file . ".json";

if (!file_exists($path)) {
    echo json_encode(['success' => false, 'error' => 'Archivo no encontrado']);
    exit;
}

$registros = json_decode(file_get_contents($path), true) ?: [];
$updated   = false;
$registroActualizado = null;

foreach ($registros as &$reg) {
    if ($reg['id'] === $id) {
        $reg['datos']['numero_orden'] = $nuevo_numero;
        $updated = true;
        $registroActualizado = $reg;
        break;
    }
}
unset($reg);

if ($updated) {
    file_put_contents($path, json_encode($registros, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    // Captura permanente hacia la base `maquinas` — el JSON sigue siendo el
    // operativo (se puede seguir editando/borrando desde la galería), pero
    // esta fila ya no depende de que ese archivo siga existiendo. Si la
    // captura falla, no debe tumbar la respuesta: el N° de Orden ya quedó
    // guardado en el JSON, que es lo que el líder está esperando ver.
    try {
        require_once 'historico_lib.php';
        require_once '../conection.php';
        registrarHistoricoOrden($registroActualizado, $file, $sede, $pdomaquinas);
    } catch (Throwable $e) {
        error_log('registrarHistoricoOrden falló para id=' . $id . ': ' . $e->getMessage());
    }

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Registro no encontrado']);
}
