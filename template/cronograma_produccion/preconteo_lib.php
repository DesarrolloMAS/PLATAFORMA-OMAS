<?php
// Preconteo de formatos del Cronograma de Producción.
// Cruza lo programado con lo registrado en los dos formatos de planta, usando
// el código de producto de la lista maestra (molienda_v2/gestion_productos.php):
//   - Línea de envasado  → archivos/generados/envasado_v2/[sede]/ENV_*_[YYYY-MM].json
//                          fecha = datos.fecha, producto = datos.producto_id
//                          (lo pasa la galería según el enlace hecho en
//                          envasado_v2/admin_catalogo_productos.php)
//   - Control de empaque → archivos/generados/empaque_v2/[sede]/EMPAQUE_LOTE_*.json
//                          fecha = datos.fecha_alistamiento (o la fecha en que se
//                          guardó), producto = datos.producto_id
// Regla (por día, los formatos no registran turno): cada producto programado un
// día en una sede espera >= 1 registro de envasado y >= 1 de control de empaque
// con ese producto y esa fecha. Solo lee archivos; no guarda nada.

// Registros anteriores a esta fecha no traen producto_id (el enlace se creó el
// 2026-09-25), así que antes de ella el preconteo no aplica.
const PRECONTEO_DESDE = '2026-09-25';

function preconteoRutaFormatos(): string {
    return __DIR__ . '/../../archivos/generados';
}

// Cuenta registros por [fecha][producto_id] de un formato en un mes.
// $leerFecha recibe el registro completo y devuelve 'Y-m-d' o null.
function preconteoContar(array $archivos, string $mes, callable $leerFecha): array {
    $conteo = [];
    foreach ($archivos as $archivo) {
        $registros = json_decode(@file_get_contents($archivo) ?: '[]', true);
        if (!is_array($registros)) continue;
        foreach ($registros as $r) {
            $productoId = (string)($r['datos']['producto_id'] ?? '');
            $fecha = $leerFecha($r);
            if ($productoId === '' || !$fecha || strncmp($fecha, $mes, 7) !== 0) continue;
            $conteo[$fecha][$productoId] = ($conteo[$fecha][$productoId] ?? 0) + 1;
        }
    }
    return $conteo;
}

function preconteoEnvasado(string $sede, string $mes): array {
    $dir = preconteoRutaFormatos() . "/envasado_v2/$sede";
    // El mes del archivo sale de la fecha del registro (ver envasado_v2/procesar.php).
    return preconteoContar(glob("$dir/ENV_*_$mes.json") ?: [], $mes, function (array $r) {
        $f = (string)($r['datos']['fecha'] ?? '');
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $f) ? $f : null;
    });
}

function preconteoEmpaque(string $sede, string $mes): array {
    $dir = preconteoRutaFormatos() . "/empaque_v2/$sede";
    // Empaque se guarda por lote (no por mes): hay que recorrer todos los lotes.
    return preconteoContar(glob("$dir/EMPAQUE_LOTE_*.json") ?: [], $mes, function (array $r) {
        $f = (string)($r['datos']['fecha_alistamiento'] ?? '');
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $f)) return $f;
        $ts = (string)($r['timestamp'] ?? '');
        return preg_match('/^\d{4}-\d{2}-\d{2}/', $ts) ? substr($ts, 0, 10) : null;
    });
}

// Agrega a cada programación su preconteo:
//   ['envasado' => n, 'empaque' => n, 'estado' => ...]
// estado: completo | faltante | en_curso | futuro | no_aplica | sin_enlace
function preconteoAplicar(array $programaciones, string $sede, string $mes, ?string $hoy = null): array {
    $hoy = $hoy ?? date('Y-m-d');
    $envasado = preconteoEnvasado($sede, $mes);
    $empaque = preconteoEmpaque($sede, $mes);

    foreach ($programaciones as $i => $p) {
        $fecha = $p['fecha'] ?? '';
        $productoId = (string)($p['producto_id'] ?? '');
        $nEnv = $productoId !== '' ? ($envasado[$fecha][$productoId] ?? 0) : 0;
        $nEmp = $productoId !== '' ? ($empaque[$fecha][$productoId] ?? 0) : 0;

        if ($fecha < PRECONTEO_DESDE)      $estado = 'no_aplica';
        elseif ($productoId === '')        $estado = 'sin_enlace';
        elseif ($nEnv > 0 && $nEmp > 0)    $estado = 'completo';
        elseif ($fecha > $hoy)             $estado = 'futuro';
        elseif ($fecha === $hoy)           $estado = 'en_curso';
        else                               $estado = 'faltante';

        $programaciones[$i]['preconteo'] = ['envasado' => $nEnv, 'empaque' => $nEmp, 'estado' => $estado];
    }
    return $programaciones;
}
