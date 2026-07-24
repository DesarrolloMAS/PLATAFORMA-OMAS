<?php
include '../../sesion.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['nombre']) || empty($_SESSION['sede'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Sin sesión.']);
    exit;
}

$sede_san      = preg_replace('/[^A-Za-z0-9_-]/', '', $_SESSION['sede']);
$input         = json_decode(file_get_contents("php://input"), true);
$apoyos_valid  = ['alturas', 'confinados', 'caliente', 'electrico', 'energizadas', 'izaje'];

if (!$input || empty($input['id_flujo']) || empty($input['apoyo_key'])
    || !in_array($input['apoyo_key'], $apoyos_valid)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Datos incompletos o tipo de apoyo inválido.']);
    exit;
}

$sede_dir    = "../../../archivos/generados/flujo_permisos/" . $sede_san . "/";
$actualizado = false;

foreach (glob($sede_dir . "*.json") as $archivo) {
    $datos = json_decode(file_get_contents($archivo), true) ?: [];
    foreach ($datos as &$flujo) {
        if ($flujo['id_flujo'] !== $input['id_flujo']) continue;
        if (!isset($flujo['apoyos'])) $flujo['apoyos'] = [];
        $existe = false;
        foreach ($flujo['apoyos'] as &$a) {
            if ($a['key'] === $input['apoyo_key']) {
                $existe = true;
                if (empty($a['id_registro']) && !empty($input['id_registro'])) {
                    $a['id_registro'] = $input['id_registro'];
                    $a['timestamp']   = date('Y-m-d H:i:s');
                    $a['usuario']     = $_SESSION['nombre'];
                }
                break;
            }
        }
        unset($a);
        if (!$existe) {
            $flujo['apoyos'][] = [
                'key'         => $input['apoyo_key'],
                'id_registro' => $input['id_registro'] ?? null,
                'timestamp'   => date('Y-m-d H:i:s'),
                'usuario'     => $_SESSION['nombre'],
            ];
        }
        $actualizado = true;
        break;
    }
    unset($flujo);
    if ($actualizado) {
        file_put_contents($archivo, json_encode($datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        break;
    }
}

echo json_encode($actualizado
    ? ['status' => 'success']
    : ['status' => 'error', 'message' => 'Flujo no encontrado.']
);
?>
