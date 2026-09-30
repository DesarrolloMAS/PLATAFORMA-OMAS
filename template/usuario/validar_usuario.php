<?php
// Validación del usuario en sesión para menu_usuario.html.
// No confía solo en $_SESSION: vuelve a leer el registro en la tabla
// `usuarios` por id_usuario y compara cargo/rol/área/sede con lo que quedó
// guardado al iniciar sesión (si un admin cambió el cargo o el rol después
// del login, la sesión queda desactualizada y se reporta como discrepancia).
//
// Por ahora solo está integrada el área Operaciones (y Desarrollo, que en
// todo el sistema se comporta igual — ver redireccion.php y
// area_operativa_lib.php). Las demás áreas se validan igual, pero se
// devuelven con integrado=false.
require_once __DIR__ . '/../sesion.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['id_usuario'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Sesión no iniciada.']);
    exit();
}

require __DIR__ . '/../conection.php'; // $pdoUsuarios
require_once __DIR__ . '/roles_lib.php';

const AREAS_INTEGRADAS = ['Operaciones', 'Desarrollo'];

function etiquetaAreaOperativa(string $area): string {
    $etiquetas = [
        'administracion' => 'Administración',
        'almacen'        => 'Almacén',
        'mantenimiento'  => 'Mantenimiento',
        'produccion'     => 'Producción',
    ];
    return $etiquetas[$area] ?? 'Sin asignar';
}

try {
    $stmt = $pdoUsuarios->prepare(
        'SELECT id_usuario, nombre_u, Cargo, sede, rol, Area FROM usuarios WHERE id_usuario = :id'
    );
    $stmt->execute([':id' => $_SESSION['id_usuario']]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('validar_usuario: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'No se pudo consultar la base de usuarios.']);
    exit();
}

if (!$usuario) {
    echo json_encode([
        'status'  => 'success',
        'valido'  => false,
        'message' => 'El usuario de la sesión ya no existe en la base de usuarios.',
    ]);
    exit();
}

$comparar = [
    'nombre' => ['nombre', 'nombre_u'],
    'cargo'  => ['cargo',  'Cargo'],
    'rol'    => ['rol',    'rol'],
    'area'   => ['area',   'Area'],
    'sede'   => ['sede',   'sede'],
];
$discrepancias = [];
foreach ($comparar as $campo => [$claveSesion, $columna]) {
    if ((string)($_SESSION[$claveSesion] ?? '') !== (string)($usuario[$columna] ?? '')) {
        $discrepancias[] = $campo;
    }
}

$area = (string)$usuario['Area'];
$rol  = (string)$usuario['rol'];

// Mismo mapeo Cargo → área operativa que area_operativa_lib.php, pero con el
// cargo de la BD (obtenerAreaOperativa() usa el de la sesión, que puede estar
// desactualizado justo en el caso que esta validación quiere detectar).
$mapaAreas = json_decode(@file_get_contents(__DIR__ . '/../../archivos/generados/admin/cargo_areas.json'), true) ?: [];
$areaOperativa = $mapaAreas[$usuario['Cargo']] ?? 'sin_asignar';

echo json_encode([
    'status'        => 'success',
    'valido'        => empty($discrepancias),
    'integrado'     => in_array($area, AREAS_INTEGRADAS, true),
    'discrepancias' => $discrepancias,
    'usuario'       => [
        'nombre'               => $usuario['nombre_u'],
        'sede'                 => $usuario['sede'],
        'cargo'                => $usuario['Cargo'],
        'rol'                  => $rol,
        'rol_label'            => etiquetaRol($rol),
        'area'                 => $area,
        'area_operativa'       => $areaOperativa,
        'area_operativa_label' => etiquetaAreaOperativa($areaOperativa),
    ],
], JSON_UNESCAPED_UNICODE);
