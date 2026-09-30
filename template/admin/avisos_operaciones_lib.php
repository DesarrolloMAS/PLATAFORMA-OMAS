<?php
// Destinatarios de los avisos automáticos de Operaciones.
// Se configuran en admin/menu_admin.php → "Visibilidad del Menú de Operaciones"
// → "Destinatarios del aviso semanal de formatos", y se guardan en
// archivos/generados/admin/avisos_operaciones.json:
//   { "aviso_semanal_formatos": { "areas": ["produccion", ...], "cargos": ["Lider de Turno", ...] } }
// Un usuario recibe el aviso si su cargo está en "cargos" o si el área
// operativa de su cargo (cargo_areas.json) está en "areas". El script que
// envía (cronograma_produccion/aviso_semanal.php) además filtra por sede:
// cada sede avisa solo a usuarios de esa sede.
// Sin configuración no hay destinatarios: el aviso no se envía (y se registra).

const AVISO_SEMANAL_FORMATOS = 'aviso_semanal_formatos';
const AVISOS_AREAS_VALIDAS = ['sin_asignar', 'administracion', 'almacen', 'mantenimiento', 'produccion'];

function avisosOperacionesRuta(): string {
    return __DIR__ . '/../../archivos/generados/admin/avisos_operaciones.json';
}

function avisosOperacionesConfig(string $aviso = AVISO_SEMANAL_FORMATOS): array {
    $todo = json_decode(@file_get_contents(avisosOperacionesRuta()) ?: '[]', true) ?: [];
    $cfg = $todo[$aviso] ?? [];
    return [
        'areas'  => array_values(array_intersect(AVISOS_AREAS_VALIDAS, (array)($cfg['areas'] ?? []))),
        'cargos' => array_values(array_filter(array_map('strval', (array)($cfg['cargos'] ?? [])), 'strlen')),
    ];
}

// Devuelve false si no se pudo escribir (ej. permisos).
function avisosOperacionesGuardar(array $config, string $aviso = AVISO_SEMANAL_FORMATOS): bool {
    $ruta = avisosOperacionesRuta();
    $todo = json_decode(@file_get_contents($ruta) ?: '[]', true) ?: [];
    $cargos = array_values(array_unique(array_filter(array_map('strval', (array)($config['cargos'] ?? [])), 'strlen')));
    sort($cargos);
    $todo[$aviso] = [
        'areas'  => array_values(array_intersect(AVISOS_AREAS_VALIDAS, (array)($config['areas'] ?? []))),
        'cargos' => $cargos,
    ];
    if (!is_dir(dirname($ruta))) mkdir(dirname($ruta), 0777, true);
    return @file_put_contents($ruta, json_encode($todo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false;
}

// Cargos que reciben el aviso: los elegidos uno a uno + los de las áreas
// operativas elegidas, según el mapeo cargo → área de cargo_areas.json.
function avisosCargosDestino(string $aviso = AVISO_SEMANAL_FORMATOS): array {
    $cfg = avisosOperacionesConfig($aviso);
    $mapa = json_decode(@file_get_contents(__DIR__ . '/../../archivos/generados/admin/cargo_areas.json') ?: '[]', true) ?: [];
    $porArea = array_keys(array_filter($mapa, fn($area) => in_array($area, $cfg['areas'], true)));
    return array_values(array_unique(array_merge($cfg['cargos'], $porArea)));
}
