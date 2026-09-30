<?php
// Endpoint de la bandeja de entrada. Sin restricción de rol ni área: solo
// exige sesión iniciada, y cada usuario solo ve/modifica su propia bandeja
// (el id sale siempre de la sesión, nunca del request).
//   GET  ?accion=listar  → { status, notificaciones, no_leidas }
//   GET  ?accion=contar  → { status, no_leidas }
//   POST accion=marcar_leida|marcar_no_leida|eliminar (id), marcar_todas
require_once __DIR__ . '/../sesion.php';
require_once __DIR__ . '/bandeja_lib.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['id_usuario'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Sesión no iniciada.']);
    exit();
}

$idUsuario = $_SESSION['id_usuario'];
$accion = $_SERVER['REQUEST_METHOD'] === 'POST' ? ($_POST['accion'] ?? '') : ($_GET['accion'] ?? 'listar');

function contarNoLeidas(array $lista): int {
    return count(array_filter($lista, fn($n) => empty($n['leida'])));
}

try {
    switch ($accion) {
        case 'listar':
            $lista = bandejaLeer($idUsuario);
            echo json_encode(['status' => 'success', 'notificaciones' => $lista, 'no_leidas' => contarNoLeidas($lista)], JSON_UNESCAPED_UNICODE);
            break;

        case 'contar':
            echo json_encode(['status' => 'success', 'no_leidas' => contarNoLeidas(bandejaLeer($idUsuario))]);
            break;

        case 'marcar_leida':
        case 'marcar_no_leida':
        case 'eliminar':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new InvalidArgumentException('Método no permitido.');
            $id = (string)($_POST['id'] ?? '');
            $encontrada = bandejaModificar($idUsuario, function (array &$lista) use ($id, $accion) {
                foreach ($lista as $i => $n) {
                    if (($n['id'] ?? '') !== $id) continue;
                    if ($accion === 'eliminar') {
                        unset($lista[$i]);
                    } else {
                        $lista[$i]['leida'] = $accion === 'marcar_leida';
                    }
                    return true;
                }
                return false;
            });
            if (!$encontrada) throw new InvalidArgumentException('La notificación no existe.');
            echo json_encode(['status' => 'success', 'message' => 'Bandeja actualizada.']);
            break;

        case 'marcar_todas':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new InvalidArgumentException('Método no permitido.');
            bandejaModificar($idUsuario, function (array &$lista) {
                foreach ($lista as $i => $n) $lista[$i]['leida'] = true;
            });
            echo json_encode(['status' => 'success', 'message' => 'Todas las notificaciones quedaron como leídas.']);
            break;

        default:
            throw new InvalidArgumentException('Acción no válida.');
    }
} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} catch (Throwable $e) {
    error_log('bandeja_api: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'No se pudo acceder a la bandeja.']);
}
