<?php
// Visibilidad de los botones de menu_adm.html (menú principal de
// Operaciones) según el área operativa del cargo — la misma que se asigna
// en "Cargos y Áreas Operativas" de menu_admin.php
// (archivos/generados/admin/cargo_areas.json).
//
// Qué área ve qué botón se configura en menu_admin.php → "Visibilidad del
// Menú de Operaciones" y se guarda en
// archivos/generados/admin/menu_operaciones.json:
//   { "<nodo>": ["<area_operativa>", ...], ... }
// Los administradores (rol 'adm') ven siempre todos los botones.

require_once __DIR__ . '/../area_operativa_lib.php';

// Botones de menu_adm.html (atributo data-nodo). "fijo" = visible para todos,
// no se puede quitar desde el panel (Usuario es la puerta a la bandeja de
// entrada, que no tiene restricción).
const MENU_OPERACIONES_NODOS = [
    'usuario'            => ['label' => 'Usuario',            'fijo' => true],
    'almacen'            => ['label' => 'Almacén',            'fijo' => false],
    'produccion'         => ['label' => 'Producción',         'fijo' => false],
    'mantenimiento'      => ['label' => 'Mantenimiento',      'fijo' => false],
    'central_documental' => ['label' => 'Central Documental', 'fijo' => false],
    'calendario'         => ['label' => 'Calendario',         'fijo' => false],
    // No es un nodo del hub sino el botón "Datos" (arriba a la derecha) que
    // lleva al dashboard de analítica (analitica/dashboard_bitacora.php).
    'datos'              => ['label' => 'Datos (Dashboard)',  'fijo' => false],
];

const MENU_OPERACIONES_AREAS = ['sin_asignar', 'administracion', 'almacen', 'mantenimiento', 'produccion'];

function menuOperacionesRuta(): string {
    return __DIR__ . '/../../archivos/generados/admin/menu_operaciones.json';
}

function menuOperacionesPorDefecto(): array {
    return [
        'usuario'            => MENU_OPERACIONES_AREAS,
        'almacen'            => ['almacen'],
        'produccion'         => ['produccion'],
        'mantenimiento'      => ['mantenimiento'],
        'central_documental' => MENU_OPERACIONES_AREAS,
        'calendario'         => MENU_OPERACIONES_AREAS,
        'datos'              => MENU_OPERACIONES_AREAS, // por defecto igual que antes: todos
    ];
}

// Configuración vigente: la guardada, completada con los valores por defecto
// para botones que todavía no tengan entrada, y con los fijos forzados.
function menuOperacionesConfig(): array {
    $guardada = json_decode(@file_get_contents(menuOperacionesRuta()) ?: '[]', true) ?: [];
    $config = [];
    foreach (menuOperacionesPorDefecto() as $nodo => $areasDefecto) {
        if (MENU_OPERACIONES_NODOS[$nodo]['fijo']) {
            $config[$nodo] = MENU_OPERACIONES_AREAS;
            continue;
        }
        $areas = isset($guardada[$nodo]) && is_array($guardada[$nodo]) ? $guardada[$nodo] : $areasDefecto;
        $config[$nodo] = array_values(array_intersect(MENU_OPERACIONES_AREAS, $areas));
    }
    return $config;
}

// Devuelve false si no se pudo escribir el archivo (ej. permisos).
function menuOperacionesGuardar(array $config): bool {
    $limpia = [];
    foreach (MENU_OPERACIONES_NODOS as $nodo => $info) {
        $areas = $info['fijo'] ? MENU_OPERACIONES_AREAS : ($config[$nodo] ?? []);
        $limpia[$nodo] = array_values(array_intersect(MENU_OPERACIONES_AREAS, (array)$areas));
    }
    $ruta = menuOperacionesRuta();
    if (!is_dir(dirname($ruta))) mkdir(dirname($ruta), 0777, true);
    return @file_put_contents($ruta, json_encode($limpia, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false;
}

// Botones que la sesión actual puede ver en menu_adm.html.
function menuOperacionesVisiblesSesion(): array {
    if (($_SESSION['rol'] ?? '') === 'adm') {
        return array_keys(MENU_OPERACIONES_NODOS);
    }
    $area = obtenerAreaOperativa();
    $visibles = [];
    foreach (menuOperacionesConfig() as $nodo => $areas) {
        if (in_array($area, $areas, true)) $visibles[] = $nodo;
    }
    return $visibles;
}

// ¿La sesión puede usar este botón/destino? Solo gobierna a Operaciones
// (y Desarrollo): las áreas operativas son una subdivisión interna de
// Operaciones, así que a Calidad/HSEQ no se les aplica esta configuración.
function menuOperacionesPuedeVer(string $nodo): bool {
    if (!in_array($_SESSION['area'] ?? '', ['Operaciones', 'Desarrollo'], true)) return true;
    return in_array($nodo, menuOperacionesVisiblesSesion(), true);
}

// Candado del lado del servidor para las páginas/APIs detrás de un botón del
// menú (ej. 'datos' → analitica/). Ocultar el botón en menu_adm.html no basta:
// sin esto se podría entrar escribiendo la URL.
function exigirAccesoMenuOperaciones(string $nodo, bool $esApi = false): void {
    if (menuOperacionesPuedeVer($nodo)) return;
    http_response_code(403);
    if ($esApi) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'error', 'message' => 'Tu área no tiene acceso a esta sección.']);
    } else {
        echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Acceso restringido</title>'
            . '<style>body{font-family:Inter,system-ui,sans-serif;background:#05070b;color:#f4f8fd;display:flex;'
            . 'align-items:center;justify-content:center;min-height:100vh;margin:0;text-align:center}'
            . 'a{color:#60a5fa}</style></head><body><div><h1 style="font-size:20px">Acceso restringido</h1>'
            . '<p style="color:#aab8cc">Tu área operativa no tiene acceso a esta sección.</p>'
            . '<p><a href="/template/menu_adm.html">Volver al menú</a></p></div></body></html>';
    }
    exit();
}
