<?php
// Funciones compartidas entre permiso_completo y certificado_apoyo para
// ubicar un flujo por id y verificar el estado de sus 3 pasos principales.

function obtenerFlujoPorId(string $sedeSanitizada, string $idFlujo): ?array {
    $sede_dir = __DIR__ . '/../../archivos/generados/flujo_permisos/' . $sedeSanitizada . '/';
    if (!is_dir($sede_dir)) {
        return null;
    }
    foreach (glob($sede_dir . '*.json') as $archivo) {
        $datos = json_decode(file_get_contents($archivo), true) ?: [];
        foreach ($datos as $flujo) {
            if (($flujo['id_flujo'] ?? null) === $idFlujo) {
                return $flujo;
            }
        }
    }
    return null;
}

function flujoPasosCompletos(array $flujo): bool {
    return !empty($flujo['pasos']['permiso']['completado'])
        && !empty($flujo['pasos']['analisis']['completado'])
        && !empty($flujo['pasos']['inspeccion']['completado']);
}

// Carpeta real de almacenamiento (dentro de archivos/generados/) para cada
// paso principal y cada certificado de apoyo. Única fuente de verdad — no
// duplicar este mapa en otros archivos.
function directorioDeModulo(string $clave): ?string {
    $mapa = [
        'permiso'     => 'permiso_trabajo',
        'analisis'    => 'analisis_trabajo',
        'inspeccion'  => 'inspeccion_trabajo',
        'alturas'     => 'cert_apoyo_alturas',
        'confinados'  => 'cert_apoyo_espacios_conf',
        'caliente'    => 'cert_apoyo_caliente',
        'electrico'   => 'cert_apoyo_electrico',
        'energizadas' => 'cert_apoyo_lineas_energ',
        'izaje'       => 'cert_apoyo_izaje',
    ];
    return $mapa[$clave] ?? null;
}

function buscarRegistroPorId(string $dir, string $idRegistro): ?array {
    if (!is_dir($dir)) return null;
    foreach (glob($dir . '*.json') as $archivo) {
        $arr = json_decode(file_get_contents($archivo), true) ?: [];
        foreach ($arr as $reg) {
            if (($reg['id_registro'] ?? '') === $idRegistro) return $reg;
        }
    }
    return null;
}

// Dado un flujo ya cargado, devuelve el registro completo (con su 'datos')
// del paso principal o certificado de apoyo indicado por $clave
// ('permiso'|'analisis'|'inspeccion'|'alturas'|'confinados'|'caliente'|
// 'electrico'|'energizadas'|'izaje'), o null si aún no se ha diligenciado.
function obtenerRegistroDelPaso(array $flujo, string $clave, string $sedeSanitizada): ?array {
    $idRegistro = $flujo['pasos'][$clave]['id_registro'] ?? null;
    if ($idRegistro === null) {
        foreach ($flujo['apoyos'] ?? [] as $a) {
            if (($a['key'] ?? null) === $clave) {
                $idRegistro = $a['id_registro'] ?? null;
                break;
            }
        }
    }
    if (empty($idRegistro)) return null;

    $dir = directorioDeModulo($clave);
    if (!$dir) return null;

    return buscarRegistroPorId(__DIR__ . '/../../archivos/generados/' . $dir . '/' . $sedeSanitizada . '/', $idRegistro);
}
