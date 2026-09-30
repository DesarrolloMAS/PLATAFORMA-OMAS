<?php
// Endpoint del Cronograma de Producción.
//   GET  ?accion=listar&mes=YYYY-MM[&sede=XX]  → { status, sede, turnos, puede_editar, programaciones }
//   POST accion=crear    (JSON) fecha_inicio, fecha_fin, producto…  → una entrada por día del rango
//   POST accion=editar   (JSON) id, fecha, producto…
//   POST accion=eliminar (JSON) id, fecha
// Permisos: regla fija de area_operativa_lib.php (veCronogramaProduccion /
// puedeEditarCronogramaProduccion). La sede es la de la sesión; solo un
// admin puede consultar/editar otra sede.
require '../sesion.php';
require_once '../area_operativa_lib.php';
require_once 'produccion_lib.php';
require_once 'preconteo_lib.php';
header('Content-Type: application/json; charset=utf-8');

function responder(int $codigo, array $datos): void {
    http_response_code($codigo);
    echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    exit();
}

if (!isset($_SESSION['id_usuario'])) responder(401, ['status' => 'error', 'message' => 'Sesión no iniciada.']);
if (!veCronogramaProduccion()) responder(403, ['status' => 'error', 'message' => 'Tu área no tiene acceso al cronograma de producción.']);

$esAdmin = ($_SESSION['rol'] ?? '') === 'adm';
$metodo = $_SERVER['REQUEST_METHOD'];
$input = $metodo === 'POST' ? (json_decode(file_get_contents('php://input'), true) ?: []) : $_GET;
$accion = $input['accion'] ?? 'listar';

$sede = $_SESSION['sede'] ?? '';
if ($esAdmin && isset($input['sede']) && in_array($input['sede'], PRODUCCION_SEDES, true)) {
    $sede = $input['sede'];
}
if (!in_array($sede, PRODUCCION_SEDES, true)) responder(400, ['status' => 'error', 'message' => 'Sede inválida.']);

try {
    if ($accion === 'listar') {
        $mes = (string)($input['mes'] ?? date('Y-m'));
        responder(200, [
            'status'         => 'success',
            'sede'           => $sede,
            'turnos'         => turnosSede($sede),
            'puede_editar'   => puedeEditarCronogramaProduccion(),
            'preconteo_desde'=> PRECONTEO_DESDE,
            // Cada programación trae 'preconteo': registros de envasado y de
            // control de empaque encontrados para ese producto y día.
            'programaciones' => preconteoAplicar(produccionLeerMes($sede, $mes), $sede, $mes),
        ]);
    }

    if ($metodo !== 'POST') responder(405, ['status' => 'error', 'message' => 'Método no permitido.']);
    if (!puedeEditarCronogramaProduccion()) {
        responder(403, ['status' => 'error', 'message' => 'Solo administradores o rol 1 del área de producción pueden editar el cronograma.']);
    }
    $usuario = $_SESSION['nombre'] ?? '';
    $ahora = date('Y-m-d H:i:s');

    switch ($accion) {
        case 'crear':
            $campos = produccionNormalizar($input, $sede);
            $inicio = new DateTime(produccionValidarFecha((string)($input['fecha_inicio'] ?? '')));
            $fin = new DateTime(produccionValidarFecha((string)($input['fecha_fin'] ?? $input['fecha_inicio'] ?? '')));
            if ($fin < $inicio) throw new InvalidArgumentException('La fecha final no puede ser anterior a la inicial.');
            $dias = (int)$inicio->diff($fin)->days + 1;
            if ($dias > PRODUCCION_MAX_DIAS) throw new InvalidArgumentException('El rango no puede superar ' . PRODUCCION_MAX_DIAS . ' días.');

            // Agrupar los días por mes para abrir cada archivo una sola vez.
            $porMes = [];
            for ($d = clone $inicio; $d <= $fin; $d->modify('+1 day')) {
                $porMes[$d->format('Y-m')][] = $d->format('Y-m-d');
            }
            foreach ($porMes as $mes => $fechas) {
                produccionModificarMes($sede, $mes, function (array &$lista) use ($fechas, $campos, $usuario, $ahora) {
                    foreach ($fechas as $fecha) {
                        $lista[] = ['id' => uniqid('prod_'), 'fecha' => $fecha] + $campos + [
                            'creado_por' => $usuario, 'creado_en' => $ahora,
                            'editado_por' => null, 'editado_en' => null,
                        ];
                    }
                });
            }
            responder(200, ['status' => 'success', 'message' => $dias === 1 ? 'Producción programada.' : "Producción programada en $dias días."]);

        case 'editar':
        case 'eliminar':
            $id = (string)($input['id'] ?? '');
            $fecha = produccionValidarFecha((string)($input['fecha'] ?? ''));
            $campos = $accion === 'editar' ? produccionNormalizar($input, $sede) : [];
            $ok = produccionModificarMes($sede, substr($fecha, 0, 7), function (array &$lista) use ($id, $accion, $campos, $usuario, $ahora) {
                foreach ($lista as $i => $p) {
                    if (($p['id'] ?? '') !== $id) continue;
                    if ($accion === 'eliminar') {
                        unset($lista[$i]);
                    } else {
                        $lista[$i] = array_diff_key(
                            array_merge($p, $campos, ['editado_por' => $usuario, 'editado_en' => $ahora]),
                            array_flip(PRODUCCION_CAMPOS_RETIRADOS)
                        );
                    }
                    return true;
                }
                return false;
            });
            if (!$ok) responder(404, ['status' => 'error', 'message' => 'La programación no existe.']);
            responder(200, ['status' => 'success', 'message' => $accion === 'editar' ? 'Programación actualizada.' : 'Programación eliminada.']);

        default:
            responder(400, ['status' => 'error', 'message' => 'Acción no válida.']);
    }
} catch (InvalidArgumentException $e) {
    responder(400, ['status' => 'error', 'message' => $e->getMessage()]);
} catch (Throwable $e) {
    error_log('cronograma_produccion/api: ' . $e->getMessage());
    responder(500, ['status' => 'error', 'message' => 'No se pudo procesar el cronograma.']);
}
