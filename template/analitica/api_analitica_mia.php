<?php
require '../sesion.php';
verificarAutenticacion();
require_once '../admin/menu_operaciones_lib.php';
exigirAccesoMenuOperaciones('datos', true); // botón "Datos" de menu_adm.html — ver admin/menu_admin.php

// Conexión propia y aislada a mia_datos — NUNCA la de conection.php (ver
// template/integracion-mia.md). Esta API solo LEE tickets_mia; nunca escribe.
require '../gobierno_datos/integracion_mia/conexion_mia.php'; // $pdoMia

header('Content-Type: application/json; charset=utf-8');

// Decisión de negocio pendiente de confirmar con el equipo de mIA/Mesa de
// Ayuda: sin fecha de cierre y más de este umbral desde la creación, el
// ticket cuenta como vencido. Mismo criterio que api_analitica_mantenimiento.
const SLA_VENCIDA_HORAS = 72;

// Duraciones fuera de este rango se tratan como error de captura y se
// excluyen del promedio en vez de contaminarlo (mismo criterio que la API de
// Bitácora de Mantenimiento).
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

function bucketPrioridad($prioridad) {
    $clave = normalizarClave($prioridad ?? '');
    if ($clave === '') return null;
    if (strpos($clave, 'critica') !== false) return 'critica';
    if (strpos($clave, 'alta') !== false) return 'alta';
    if (strpos($clave, 'normal') !== false) return 'normal';
    if (strpos($clave, 'baja') !== false) return 'baja';
    return null;
}

// tickets_mia trae un solo especialista por ticket (a diferencia de
// nombre_responsable en Bitácora de Mantenimiento, que puede traer varios
// separados por "/") — solo hace falta convertir el correo en un nombre legible.
function nombreDesdeEmail($email) {
    $email = trim($email ?? '');
    if ($email === '') return 'Sin asignar';
    $local = strstr($email, '@', true);
    if ($local === false) $local = $email;
    $partes = array_filter(preg_split('/[._]+/', $local), fn($p) => $p !== '');
    if (!$partes) return $email;
    return implode(' ', array_map(
        fn($p) => mb_strtoupper(mb_substr($p, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($p, 1, null, 'UTF-8'),
        $partes
    ));
}

// Promedio de la segunda mitad de la serie vs. la primera — más estable que
// comparar solo el último punto contra el penúltimo.
function deltaMitadVsMitad($serie) {
    $n = count($serie);
    $mitad = intdiv($n, 2);
    if ($mitad === 0) return null;
    $promInicio = array_sum(array_slice($serie, 0, $mitad)) / $mitad;
    $promFin = array_sum(array_slice($serie, $mitad)) / ($n - $mitad);
    return ['inicio' => $promInicio, 'fin' => $promFin];
}

// ── 1. Sedes reales de mIA (ver template/integracion-mia.md). A diferencia
// de Bitácora de Mantenimiento, aquí no existe un mapeo entre la sede de
// sesión (ZC/ZS/ZB) y estos nombres reales de sitio, así que el default es
// "todas" — el usuario elige manualmente la sede que quiere ver. ──
const SEDES_MIA = ['Molino Bogota', 'Molino Buga', 'Molino Pasto', 'Artesa Panaderia', 'No Especificado'];

$sedeFiltro = $_GET['sede'] ?? 'todas';
if ($sedeFiltro !== 'todas' && !in_array($sedeFiltro, SEDES_MIA, true)) $sedeFiltro = 'todas';

// ── 1b. Prioridad para la línea de tiempo de resolución. Solo afecta esa
// métrica (datosSemanales y su KPI) — barras, donut y demás KPIs siguen
// mostrando todas las prioridades. ──
$prioridadFiltro = $_GET['prioridad'] ?? 'todas';
if (!in_array($prioridadFiltro, ['todas', 'critica', 'alta', 'normal', 'baja'], true)) $prioridadFiltro = 'todas';

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

// ── 3. Traer los tickets de la ventana directo de SQL — más simple que el
// escaneo de archivos JSON por sede/mes de Bitácora de Mantenimiento, porque
// tickets_mia ya es una sola tabla. ──
$sql = 'SELECT estado, prioridad, sede, especialista_email, fecha_creacion, fecha_cierre
        FROM tickets_mia
        WHERE fecha_creacion BETWEEN :inicio AND :fin';
$params = [
    ':inicio' => $inicioVentana->format('Y-m-d 00:00:00'),
    ':fin' => $finVentana->format('Y-m-d H:i:s'),
];
if ($sedeFiltro !== 'todas') {
    $sql .= ' AND sede = :sede';
    $params[':sede'] = $sedeFiltro;
}
$stmt = $pdoMia->prepare($sql);
$stmt->execute($params);
$registrosEnVentana = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ── 4. Buckets vacíos en la granularidad activa (mes o día) ──
$claveDeFecha = function (DateTime $f) use ($granularidad) {
    return $granularidad === 'mes' ? $f->format('Y-m') : $f->format('Y-m-d');
};
$etiquetaDeFecha = function (DateTime $f) use ($granularidad) {
    if ($granularidad === 'mes') return MESES_ES[(int)$f->format('n')];
    return DIAS_ES[(int)$f->format('N')] . ' ' . $f->format('d');
};

$buckets = [];
$ordenClaves = [];
$cursor = clone $inicioVentana;
$paso = $granularidad === 'mes' ? '+1 month' : '+1 day';
while ($cursor <= $hoy) {
    $clave = $claveDeFecha($cursor);
    $buckets[$clave] = [
        'etiqueta' => $etiquetaDeFecha($cursor),
        'critica' => 0, 'alta' => 0, 'normal' => 0, 'baja' => 0,
        'total' => 0, 'cerrados' => 0,
    ];
    $ordenClaves[] = $clave;
    $cursor->modify($paso);
}

// ── 5. Agregaciones sobre la ventana filtrada ──
$duracionesPorBucketLinea = [];
$bucketsLinea = $buckets;
$especialistasConteo = [];
$duracionesValidas = [];
$duracionesExcluidas = 0;
$vencidos = 0;
$cerrados = 0;
$totalTickets = 0;
$totalTipificados = 0;

foreach ($registrosEnVentana as $t) {
    $totalTickets++;

    $fCreacion = new DateTime($t['fecha_creacion']);
    $claveBucket = $claveDeFecha($fCreacion);
    $estaCerrado = in_array($t['estado'], ['Cerrado', 'Cancelado'], true);

    if (isset($buckets[$claveBucket])) {
        $buckets[$claveBucket]['total']++;
        if ($estaCerrado) $buckets[$claveBucket]['cerrados']++;
    }

    $prio = bucketPrioridad($t['prioridad']);
    if ($prio !== null && isset($buckets[$claveBucket])) {
        $buckets[$claveBucket][$prio]++;
        $totalTipificados++;
    }

    $nombre = nombreDesdeEmail($t['especialista_email']);
    $claveNombre = normalizarClave($nombre);
    if (!isset($especialistasConteo[$claveNombre])) {
        $especialistasConteo[$claveNombre] = ['nombre' => $nombre, 'tickets' => 0];
    }
    $especialistasConteo[$claveNombre]['tickets']++;

    if ($estaCerrado) $cerrados++;

    // El filtro de prioridad solo restringe qué tickets cuentan para la
    // línea de tiempo de resolución (y su KPI) — el resto de agregaciones
    // arriba ya corrieron con todas las prioridades.
    $incluyeEnLinea = $prioridadFiltro === 'todas' || $prio === $prioridadFiltro;

    if (!empty($t['fecha_cierre'])) {
        if ($incluyeEnLinea) {
            $fCierre = new DateTime($t['fecha_cierre']);
            $horas = ($fCierre->getTimestamp() - $fCreacion->getTimestamp()) / 3600;
            if ($horas >= DURACION_MIN_HORAS && $horas <= DURACION_MAX_HORAS) {
                $duracionesValidas[] = $horas;
                $claveCierre = $claveDeFecha($fCierre);
                if (isset($bucketsLinea[$claveCierre])) $duracionesPorBucketLinea[$claveCierre][] = $horas;
            } else {
                $duracionesExcluidas++;
            }
        }
    } elseif (!$estaCerrado) {
        $horasAbierto = (time() - $fCreacion->getTimestamp()) / 3600;
        if ($horasAbierto > SLA_VENCIDA_HORAS) $vencidos++;
    }
}

// datosMensuales (mismo nombre histórico que la API hermana; son los buckets
// de la granularidad activa) en orden cronológico
$datosMensuales = [];
$pctCerradosSpark = [];
foreach ($ordenClaves as $clave) {
    $b = $buckets[$clave];
    $datosMensuales[] = ['mes' => $b['etiqueta'], 'critica' => $b['critica'], 'alta' => $b['alta'], 'normal' => $b['normal'], 'baja' => $b['baja']];
    $pctCerradosSpark[] = $b['total'] > 0 ? round($b['cerrados'] / $b['total'] * 100) : 0;
}

// datosSemanales: promedio de horas por bucket (semana ISO si rango=6m,
// día si rango=1m/1s) — mismo criterio que Bitácora de Mantenimiento.
$datosSemanales = [];
if ($granularidad === 'mes') {
    $porSemana = [];
    foreach ($registrosEnVentana as $t) {
        if ($prioridadFiltro !== 'todas' && bucketPrioridad($t['prioridad']) !== $prioridadFiltro) continue;
        if (empty($t['fecha_cierre'])) continue;
        $fCreacion = new DateTime($t['fecha_creacion']);
        $fCierre = new DateTime($t['fecha_cierre']);
        $horas = ($fCierre->getTimestamp() - $fCreacion->getTimestamp()) / 3600;
        if ($horas < DURACION_MIN_HORAS || $horas > DURACION_MAX_HORAS) continue;
        $semanaIso = $fCierre->format('o-\WW');
        $porSemana[$semanaIso][] = $horas;
    }
    ksort($porSemana);
    foreach ($porSemana as $semanaIso => $horasLista) {
        [$anioIso, $numSemana] = sscanf($semanaIso, '%d-W%d');
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

// datosEspecialistas: top 4 + "Otros" para no saturar el donut
usort($especialistasConteo, fn($a, $b) => $b['tickets'] <=> $a['tickets']);
$especialistasConteo = array_values($especialistasConteo);
$datosEspecialistas = [];
$otros = 0;
foreach ($especialistasConteo as $i => $e) {
    if ($i < 4) {
        $datosEspecialistas[] = ['nombre' => $e['nombre'], 'tickets' => $e['tickets']];
    } else {
        $otros += $e['tickets'];
    }
}
if ($otros > 0) $datosEspecialistas[] = ['nombre' => 'Otros', 'tickets' => $otros];

// ── 6. KPIs (con sparklines y deltas reales derivados de la misma ventana) ──
$totalPorBucket = array_map(fn($m) => $m['critica'] + $m['alta'] + $m['normal'] + $m['baja'], $datosMensuales);
$deltaTicketsInfo = deltaMitadVsMitad($totalPorBucket);
$deltaTickets = ($deltaTicketsInfo && $deltaTicketsInfo['inicio'] > 0)
    ? round((($deltaTicketsInfo['fin'] - $deltaTicketsInfo['inicio']) / $deltaTicketsInfo['inicio']) * 100)
    : null;

$pctCerrados = $totalTickets > 0 ? round($cerrados / $totalTickets * 100) : 0;

$horasProm = count($duracionesValidas) > 0 ? array_sum($duracionesValidas) / count($duracionesValidas) : 0;
$horasSpark = array_column($datosSemanales, 'horas');
$deltaHorasInfo = deltaMitadVsMitad($horasSpark);
$deltaHoras = $deltaHorasInfo ? round($deltaHorasInfo['fin'] - $deltaHorasInfo['inicio'], 1) : null;

$etiquetaRango = ['6m' => '6 meses', '1m' => 'mes', '1s' => 'semana'][$rango];
$etiquetaComparacion = $granularidad === 'mes' ? 'vs. primeros meses' : 'vs. primeros días';
$etiquetaPrioridad = ['todas' => '', 'critica' => ' · Crítica', 'alta' => ' · Alta', 'normal' => ' · Normal', 'baja' => ' · Baja'][$prioridadFiltro];

$kpis = [
    [
        'label' => "Tickets totales ($etiquetaRango)",
        'value' => $totalTickets,
        'decimals' => 0,
        'suffix' => '',
        'spark' => $totalPorBucket,
        'delta' => $deltaTickets === null ? 'Sin datos suficientes para comparar' : (($deltaTickets >= 0 ? '+' : '') . $deltaTickets . '% ' . $etiquetaComparacion),
        'good' => $deltaTickets === null || $deltaTickets >= 0,
        'color' => 'var(--accent-2)',
    ],
    [
        'label' => '% Cerrados del total',
        'value' => $pctCerrados,
        'decimals' => 0,
        'suffix' => '%',
        'spark' => $pctCerradosSpark,
        'delta' => $pctCerrados >= 90 ? 'Meta ≥90% cumplida' : 'Por debajo de la meta',
        'good' => $pctCerrados >= 90,
        'color' => 'var(--series-baja)',
    ],
    [
        'label' => 'Tiempo promedio de resolución' . $etiquetaPrioridad,
        'value' => round($horasProm, 1),
        'decimals' => 1,
        'suffix' => 'h',
        'spark' => count($horasSpark) > 0 ? $horasSpark : [0, 0],
        'delta' => $deltaHoras === null ? 'Datos insuficientes' : (($deltaHoras <= 0 ? '' : '+') . $deltaHoras . 'h ' . $etiquetaComparacion),
        'good' => $deltaHoras === null || $deltaHoras <= 0,
        'color' => 'var(--series-alta)',
    ],
    [
        'label' => 'Tickets vencidos (>' . SLA_VENCIDA_HORAS . 'h sin cierre)',
        'value' => $vencidos,
        'decimals' => 0,
        'suffix' => '',
        'spark' => array_fill(0, max(2, count($totalPorBucket)), $vencidos),
        'delta' => $vencidos > 5 ? 'Requiere atención' : 'Bajo control',
        'good' => $vencidos <= 5,
        'color' => 'var(--danger)',
    ],
];

echo json_encode([
    'meta' => [
        'generado_en' => date('Y-m-d H:i:s'),
        'sedes' => SEDES_MIA,
        'sede_filtro' => $sedeFiltro,
        'rango' => $rango,
        'prioridad_filtro' => $prioridadFiltro,
        'granularidad' => $granularidad,
        'ventana_inicio' => $inicioVentana->format('Y-m-d'),
        'ventana_fin' => $hoy->format('Y-m-d'),
        'registros_leidos' => $totalTickets,
        'registros_sin_prioridad_reconocida' => $totalTickets - $totalTipificados,
        'duraciones_excluidas_por_anomalia' => $duracionesExcluidas,
        'sla_vencida_horas' => SLA_VENCIDA_HORAS,
    ],
    'datosMensuales' => $datosMensuales,
    'datosSemanales' => $datosSemanales,
    'datosEspecialistas' => $datosEspecialistas,
    'kpis' => $kpis,
], JSON_UNESCAPED_UNICODE);
