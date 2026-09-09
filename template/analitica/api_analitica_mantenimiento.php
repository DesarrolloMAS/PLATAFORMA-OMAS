<?php
require '../sesion.php';
verificarAutenticacion();

header('Content-Type: application/json; charset=utf-8');

// Decisión de negocio pendiente de confirmar con mantenimiento: sin fecha de
// cierre y más de este umbral desde la solicitud, la orden cuenta como vencida.
const SLA_VENCIDA_HORAS = 72;

// Duraciones fuera de este rango se tratan como error de captura (ej. cierre
// registrado antes que la solicitud, o cruce de medianoche sin ajustar fecha)
// y se excluyen del promedio en vez de contaminarlo.
const DURACION_MIN_HORAS = 0;
const DURACION_MAX_HORAS = 24 * 14; // dos semanas

const MESES_ES = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
    7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
];
const DIAS_ES = [1 => 'Lun', 2 => 'Mar', 3 => 'Mié', 4 => 'Jue', 5 => 'Vie', 6 => 'Sáb', 7 => 'Dom'];

function normalizarClave($texto) {
    $texto = trim(mb_strtolower($texto, 'UTF-8'));
    $sinAcentos = strtr($texto, [
        'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n',
    ]);
    return preg_replace('/\s+/', ' ', $sinAcentos);
}

function bucketTipo($tipoEjecucion) {
    $clave = normalizarClave($tipoEjecucion ?? '');
    if ($clave === '') return null;
    if (strpos($clave, 'preventivo') !== false) return 'preventivo';
    if (strpos($clave, 'predictivo') !== false) return 'predictivo';
    if (strpos($clave, 'correctivo') !== false) return 'correctivo';
    // Garantía se trata como trabajo reactivo/no planeado, igual que un
    // correctivo, hasta que el área de mantenimiento defina si merece
    // categoría propia.
    if (strpos($clave, 'garantia') !== false) return 'correctivo';
    return null;
}

// Un registro puede traer varios técnicos separados por "/" o " - "
// (ej. "Iván Jojoa / Harold Taramuel"). Se reparte 1 orden a cada uno en
// vez de crear una categoría falsa por cada combinación de nombres.
function extraerTecnicos($nombreCrudo) {
    $nombreCrudo = trim($nombreCrudo ?? '');
    if ($nombreCrudo === '') return [];
    $partes = preg_split('/\s*\/\s*|\s+-\s+/', $nombreCrudo);
    $resultado = [];
    foreach ($partes as $parte) {
        $parte = trim(preg_replace('/\s+/', ' ', $parte));
        if ($parte !== '') $resultado[] = $parte;
    }
    return $resultado;
}

function parsearFechaHora($fecha, $hora) {
    if (empty($fecha) || empty($hora)) return null;
    $dt = DateTime::createFromFormat('Y-m-d H:i', "$fecha $hora");
    return $dt ?: null;
}

function parsearFecha($fecha) {
    if (empty($fecha)) return null;
    $dt = DateTime::createFromFormat('Y-m-d', $fecha);
    return $dt ?: null;
}

// Promedio de la segunda mitad de la serie vs. la primera — más estable que
// comparar solo el último punto contra el penúltimo (ese es ruidoso cuando
// el bucket es un día suelto en vez de un mes completo).
function deltaMitadVsMitad($serie) {
    $n = count($serie);
    $mitad = intdiv($n, 2);
    if ($mitad === 0) return null;
    $promInicio = array_sum(array_slice($serie, 0, $mitad)) / $mitad;
    $promFin = array_sum(array_slice($serie, $mitad)) / ($n - $mitad);
    return ['inicio' => $promInicio, 'fin' => $promFin];
}

// ── 1. Sedes del sistema (README: ZS, ZC, ZB) — se filtra a las que de verdad tengan carpeta ──
$baseDir = realpath(__DIR__ . '/../../archivos/generados/orden_mantenimiento');
$sedes = [];
if ($baseDir && is_dir($baseDir)) {
    foreach (['ZC', 'ZS', 'ZB'] as $sedeCandidata) {
        if (is_dir($baseDir . '/' . $sedeCandidata)) $sedes[] = $sedeCandidata;
    }
}

// ── 1b. Sede a mostrar. Sin parámetro explícito, se asume la sede del
// perfil que está viendo el dashboard (no "todas") — el usuario elige
// manualmente ver la otra sede o el consolidado.
$sedeSesion = in_array($_SESSION['sede'] ?? null, ['ZC', 'ZS'], true) ? $_SESSION['sede'] : 'todas';
$sedeFiltro = $_GET['sede'] ?? $sedeSesion;
if (!in_array($sedeFiltro, ['ZC', 'ZS', 'todas'], true)) $sedeFiltro = $sedeSesion;
$sedesActivas = $sedeFiltro === 'todas' ? $sedes : array_values(array_intersect($sedes, [$sedeFiltro]));

// ── 1c. Tipo de mantenimiento para la línea de tiempo de resolución. Solo
// afecta esa métrica (datosSemanales y el KPI de horas promedio) — barras,
// donut y demás KPIs siguen mostrando todos los tipos.
$tipoFiltro = $_GET['tipo'] ?? 'todos';
if (!in_array($tipoFiltro, ['todos', 'preventivo', 'correctivo', 'predictivo'], true)) $tipoFiltro = 'todos';

// ── 2. Rango seleccionado y ventana de fechas resultante ──
$rango = $_GET['rango'] ?? '6m';
if (!in_array($rango, ['6m', '1m', '1s'], true)) $rango = '6m';

$hoy = new DateTime('today');
if ($rango === '6m') {
    $granularidad = 'mes';
    $inicioVentana = (clone $hoy)->modify('first day of this month')->modify('-5 months');
} elseif ($rango === '1m') {
    $granularidad = 'dia';
    $inicioVentana = (clone $hoy)->modify('-29 days');
} else { // '1s'
    $granularidad = 'dia';
    $inicioVentana = (clone $hoy)->modify('-6 days');
}
$finVentana = (clone $hoy)->setTime(23, 59, 59);

// ── 3. Qué archivos YYYY-MM tocar (la ventana puede cruzar un límite de mes) ──
$mesesArchivo = [];
$cursorMes = new DateTime($inicioVentana->format('Y-m-01'));
$finMes = new DateTime($hoy->format('Y-m-01'));
while ($cursorMes <= $finMes) {
    $mesesArchivo[] = $cursorMes->format('Y-m');
    $cursorMes->modify('+1 month');
}

// ── 4. Leer todos los registros candidatos, solo en las sedes activas ──
$candidatos = [];
$erroresLectura = [];
foreach ($sedesActivas as $sede) {
    foreach ($mesesArchivo as $ym) {
        $archivo = "$baseDir/$sede/$ym.json";
        if (!file_exists($archivo)) continue;
        $decodificado = json_decode(file_get_contents($archivo), true);
        if (!is_array($decodificado)) {
            $erroresLectura[] = "$sede/$ym.json";
            continue;
        }
        foreach ($decodificado as $registro) {
            $candidatos[] = $registro;
        }
    }
}

// ── 5. Filtrar a la ventana exacta por fecha_solicitud, y armar los buckets vacíos ──
$claveDeFecha = function (DateTime $f) use ($granularidad) {
    return $granularidad === 'mes' ? $f->format('Y-m') : $f->format('Y-m-d');
};
$etiquetaDeFecha = function (DateTime $f) use ($granularidad) {
    if ($granularidad === 'mes') return MESES_ES[(int)$f->format('n')];
    return DIAS_ES[(int)$f->format('N')] . ' ' . $f->format('d');
};

$buckets = []; // clave => ['etiqueta'=>..., 'preventivo'=>0, 'correctivo'=>0, 'predictivo'=>0]
$ordenClaves = [];
if ($granularidad === 'mes') {
    $cursor = clone $inicioVentana;
    while ($cursor <= $hoy) {
        $clave = $claveDeFecha($cursor);
        $buckets[$clave] = ['etiqueta' => $etiquetaDeFecha($cursor), 'preventivo' => 0, 'correctivo' => 0, 'predictivo' => 0];
        $ordenClaves[] = $clave;
        $cursor->modify('+1 month');
    }
} else {
    $cursor = clone $inicioVentana;
    while ($cursor <= $hoy) {
        $clave = $claveDeFecha($cursor);
        $buckets[$clave] = ['etiqueta' => $etiquetaDeFecha($cursor), 'preventivo' => 0, 'correctivo' => 0, 'predictivo' => 0];
        $ordenClaves[] = $clave;
        $cursor->modify('+1 day');
    }
}

$registrosEnVentana = [];
foreach ($candidatos as $registro) {
    $datos = $registro['datos'] ?? [];
    $fSolicitud = parsearFecha($datos['fecha_solicitud'] ?? null);
    if ($fSolicitud === null || $fSolicitud < $inicioVentana || $fSolicitud > $finVentana) continue;
    $registrosEnVentana[] = $registro;
}

// ── 6. Agregaciones sobre la ventana filtrada ──
$duracionesPorBucketLinea = []; // misma granularidad, pero anclada a fecha_cierre
$bucketsLinea = $buckets; // mismas claves/etiquetas vacías como punto de partida
$tecnicosConteo = [];
$duracionesValidas = [];
$duracionesExcluidas = 0;
$vencidas = 0;
$totalOrdenes = 0;
$totalTipificadas = 0;

foreach ($registrosEnVentana as $registro) {
    $datos = $registro['datos'] ?? [];
    $totalOrdenes++;

    $fSolicitud = parsearFecha($datos['fecha_solicitud']);
    $claveBucket = $claveDeFecha($fSolicitud);

    $bucket = bucketTipo($datos['tipo_ejecucion'] ?? null);
    if ($bucket !== null && isset($buckets[$claveBucket])) {
        $buckets[$claveBucket][$bucket]++;
        $totalTipificadas++;
    }

    foreach (extraerTecnicos($datos['nombre_responsable'] ?? '') as $nombre) {
        $clave = normalizarClave($nombre);
        if (!isset($tecnicosConteo[$clave])) {
            $tecnicosConteo[$clave] = ['nombre' => $nombre, 'ordenes' => 0];
        }
        $tecnicosConteo[$clave]['ordenes']++;
    }

    $tSolicitud = parsearFechaHora($datos['fecha_solicitud'] ?? null, $datos['hora_solicitud'] ?? null);
    $tCierre = parsearFechaHora($datos['fecha_cierre'] ?? null, $datos['hora_cierre'] ?? null);

    // El filtro de tipo solo restringe qué órdenes cuentan para la línea de
    // tiempo de resolución (y su KPI) — el resto de agregaciones arriba ya
    // corrieron con todos los tipos.
    $incluyeEnLinea = $tipoFiltro === 'todos' || $bucket === $tipoFiltro;

    if ($tCierre !== null) {
        if ($tSolicitud !== null && $incluyeEnLinea) {
            $horas = ($tCierre->getTimestamp() - $tSolicitud->getTimestamp()) / 3600;
            if ($horas >= DURACION_MIN_HORAS && $horas <= DURACION_MAX_HORAS) {
                $duracionesValidas[] = $horas;
                $claveCierre = $claveDeFecha($tCierre);
                if (isset($bucketsLinea[$claveCierre])) {
                    $duracionesPorBucketLinea[$claveCierre][] = $horas;
                }
            } else {
                $duracionesExcluidas++;
            }
        }
    } elseif ($tSolicitud !== null) {
        $horasAbierta = (time() - $tSolicitud->getTimestamp()) / 3600;
        if ($horasAbierta > SLA_VENCIDA_HORAS) $vencidas++;
    }
}

// datosMensuales (nombre histórico del campo; en realidad son los buckets de
// la granularidad activa — mes o día) en orden cronológico
$datosMensuales = [];
foreach ($ordenClaves as $clave) {
    $b = $buckets[$clave];
    $datosMensuales[] = ['mes' => $b['etiqueta'], 'preventivo' => $b['preventivo'], 'correctivo' => $b['correctivo'], 'predictivo' => $b['predictivo']];
}

// datosSemanales: promedio de horas por bucket (semana si rango=6m vía ISO,
// día si rango=1m/1s), solo se incluyen los buckets que sí tuvieron cierres
$datosSemanales = [];
if ($granularidad === 'mes') {
    // Para la vista de 6 meses la línea sigue siendo semanal (más puntos de
    // tendencia que uno por mes) — se agrupa por semana ISO de fecha_cierre.
    $porSemana = [];
    foreach ($registrosEnVentana as $registro) {
        $datos = $registro['datos'] ?? [];
        if ($tipoFiltro !== 'todos' && bucketTipo($datos['tipo_ejecucion'] ?? null) !== $tipoFiltro) continue;
        $tSolicitud = parsearFechaHora($datos['fecha_solicitud'] ?? null, $datos['hora_solicitud'] ?? null);
        $tCierre = parsearFechaHora($datos['fecha_cierre'] ?? null, $datos['hora_cierre'] ?? null);
        if ($tSolicitud === null || $tCierre === null) continue;
        $horas = ($tCierre->getTimestamp() - $tSolicitud->getTimestamp()) / 3600;
        if ($horas < DURACION_MIN_HORAS || $horas > DURACION_MAX_HORAS) continue;
        $semanaIso = $tCierre->format('o-\WW');
        $porSemana[$semanaIso][] = $horas;
    }
    ksort($porSemana);
    foreach ($porSemana as $semanaIso => $horasLista) {
        [$anioIso, $numSemana] = sscanf($semanaIso, "%d-W%d");
        $lunes = new DateTime();
        $lunes->setISODate($anioIso, $numSemana);
        $datosSemanales[] = ['semana' => $lunes->format('d/m'), 'horas' => round(array_sum($horasLista) / count($horasLista), 1)];
    }
} else {
    foreach ($ordenClaves as $clave) {
        if (!isset($duracionesPorBucketLinea[$clave])) continue;
        $horasLista = $duracionesPorBucketLinea[$clave];
        $datosSemanales[] = ['semana' => $buckets[$clave]['etiqueta'], 'horas' => round(array_sum($horasLista) / count($horasLista), 1)];
    }
}

// datosTecnicos: top 4 + "Otros" para no saturar el donut (misma paleta de 5 colores del front)
usort($tecnicosConteo, fn($a, $b) => $b['ordenes'] <=> $a['ordenes']);
$tecnicosConteo = array_values($tecnicosConteo);
$datosTecnicos = [];
$otros = 0;
foreach ($tecnicosConteo as $i => $t) {
    if ($i < 4) {
        $datosTecnicos[] = ['nombre' => $t['nombre'], 'ordenes' => $t['ordenes']];
    } else {
        $otros += $t['ordenes'];
    }
}
if ($otros > 0) $datosTecnicos[] = ['nombre' => 'Otros', 'ordenes' => $otros];

// ── 7. KPIs (con sparklines y deltas reales derivados de la misma ventana) ──
$totalPorBucket = array_map(fn($m) => $m['preventivo'] + $m['correctivo'] + $m['predictivo'], $datosMensuales);
$deltaOrdenesInfo = deltaMitadVsMitad($totalPorBucket);
$deltaOrdenes = ($deltaOrdenesInfo && $deltaOrdenesInfo['inicio'] > 0)
    ? round((($deltaOrdenesInfo['fin'] - $deltaOrdenesInfo['inicio']) / $deltaOrdenesInfo['inicio']) * 100)
    : null;

$preventivoTotal = array_sum(array_column($buckets, 'preventivo'));
$pctPreventivo = $totalTipificadas > 0 ? round($preventivoTotal / $totalTipificadas * 100) : 0;

$horasProm = count($duracionesValidas) > 0 ? array_sum($duracionesValidas) / count($duracionesValidas) : 0;
$horasSpark = array_column($datosSemanales, 'horas');
$deltaHorasInfo = deltaMitadVsMitad($horasSpark);
$deltaHoras = $deltaHorasInfo ? round($deltaHorasInfo['fin'] - $deltaHorasInfo['inicio'], 1) : null;

$etiquetaRango = ['6m' => '6 meses', '1m' => 'mes', '1s' => 'semana'][$rango];
$etiquetaComparacion = $granularidad === 'mes' ? 'vs. primeros meses' : 'vs. primeros días';
$etiquetaTipo = ['todos' => '', 'preventivo' => ' · Preventivo', 'correctivo' => ' · Correctivo', 'predictivo' => ' · Predictivo'][$tipoFiltro];

$kpis = [
    [
        'label' => "Órdenes totales ($etiquetaRango)",
        'value' => $totalOrdenes,
        'decimals' => 0,
        'suffix' => '',
        'spark' => $totalPorBucket,
        'delta' => $deltaOrdenes === null ? 'Sin datos suficientes para comparar' : (($deltaOrdenes >= 0 ? '+' : '') . $deltaOrdenes . '% ' . $etiquetaComparacion),
        'good' => $deltaOrdenes === null || $deltaOrdenes >= 0,
        'color' => 'var(--accent-2)',
    ],
    [
        'label' => '% Preventivo del total',
        'value' => $pctPreventivo,
        'decimals' => 0,
        'suffix' => '%',
        'spark' => array_map(fn($m) => ($m['preventivo'] + $m['correctivo'] + $m['predictivo']) > 0
            ? round($m['preventivo'] / ($m['preventivo'] + $m['correctivo'] + $m['predictivo']) * 100)
            : 0, $datosMensuales),
        'delta' => $pctPreventivo >= 55 ? 'Meta ≥55% cumplida' : 'Por debajo de la meta',
        'good' => $pctPreventivo >= 55,
        'color' => 'var(--series-preventivo)',
    ],
    [
        'label' => 'Horas promedio por orden' . $etiquetaTipo,
        'value' => round($horasProm, 1),
        'decimals' => 1,
        'suffix' => 'h',
        'spark' => count($horasSpark) > 0 ? $horasSpark : [0, 0],
        'delta' => $deltaHoras === null ? 'Datos insuficientes' : (($deltaHoras <= 0 ? '' : '+') . $deltaHoras . 'h ' . $etiquetaComparacion),
        'good' => $deltaHoras === null || $deltaHoras <= 0,
        'color' => 'var(--series-correctivo)',
    ],
    [
        'label' => 'Órdenes vencidas (>' . SLA_VENCIDA_HORAS . 'h sin cierre)',
        'value' => $vencidas,
        'decimals' => 0,
        'suffix' => '',
        'spark' => array_fill(0, max(2, count($totalPorBucket)), $vencidas),
        'delta' => $vencidas > 2 ? 'Requiere atención' : 'Bajo control',
        'good' => $vencidas <= 2,
        'color' => 'var(--danger)',
    ],
];

echo json_encode([
    'meta' => [
        'generado_en' => date('Y-m-d H:i:s'),
        'sedes' => $sedes,
        'sede_filtro' => $sedeFiltro,
        'sede_sesion' => $sedeSesion,
        'sedes_activas' => $sedesActivas,
        'rango' => $rango,
        'tipo_filtro' => $tipoFiltro,
        'granularidad' => $granularidad,
        'ventana_inicio' => $inicioVentana->format('Y-m-d'),
        'ventana_fin' => $hoy->format('Y-m-d'),
        'registros_leidos' => $totalOrdenes,
        'registros_sin_tipo_reconocido' => $totalOrdenes - $totalTipificadas,
        'duraciones_excluidas_por_anomalia' => $duracionesExcluidas,
        'archivos_con_error_lectura' => $erroresLectura,
        'sla_vencida_horas' => SLA_VENCIDA_HORAS,
    ],
    'datosMensuales' => $datosMensuales,
    'datosSemanales' => $datosSemanales,
    'datosTecnicos' => $datosTecnicos,
    'kpis' => $kpis,
], JSON_UNESCAPED_UNICODE);
