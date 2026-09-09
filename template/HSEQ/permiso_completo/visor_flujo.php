<?php
require '../../sesion.php';
require_once __DIR__ . '/../flujo_helpers.php';

if (!isset($_SESSION['nombre']) || empty($_SESSION['sede'])) {
    header('Location: ../../../index.php');
    exit;
}

$sede     = $_SESSION['sede'];
$sede_san = preg_replace('/[^A-Za-z0-9_-]/', '', $sede);
$id_flujo = trim($_GET['id'] ?? '');

if (!$id_flujo) die('ID de flujo no especificado.');

/* ── Busca el flujo en los JSON mensuales ── */
$flujo    = null;
$sede_dir = "../../../archivos/generados/flujo_permisos/" . $sede_san . "/";
foreach (glob($sede_dir . "*.json") as $archivo) {
    $arr = json_decode(file_get_contents($archivo), true) ?: [];
    foreach ($arr as $f) {
        if (($f['id_flujo'] ?? '') === $id_flujo) { $flujo = $f; break 2; }
    }
}
if (!$flujo) die('Permiso no encontrado.');

/* ── Carga registros de cada paso (vía flujo_helpers.php) ── */
$regPer = obtenerRegistroDelPaso($flujo, 'permiso', $sede_san);
$regAts = obtenerRegistroDelPaso($flujo, 'analisis', $sede_san);
$regIns = obtenerRegistroDelPaso($flujo, 'inspeccion', $sede_san);

/* ── Helpers ── */
function e($v)      { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
function ef($v)     { return nl2br(htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8')); }
function fmtTs($ts) { return $ts ? date('d/m/Y H:i', strtotime($ts)) : '—'; }
function fmtD($d)   { return $d  ? date('d/m/Y',    strtotime($d))   : '—'; }

$apoyoLabels = [
    'alturas'     => ['icon' => '🦺', 'label' => 'Trabajo en Alturas'],
    'confinados'  => ['icon' => '🛢️', 'label' => 'Espacios Confinados'],
    'caliente'    => ['icon' => '🔥', 'label' => 'Trabajo en Caliente'],
    'electrico'   => ['icon' => '⚡', 'label' => 'Riesgo Eléctrico'],
    'energizadas' => ['icon' => '🔴', 'label' => 'Líneas Energizadas'],
    'izaje'       => ['icon' => '🏗️', 'label' => 'Izaje de Cargas'],
];

// Identidad visual por formato — un color/código/ícono propio para cada uno de los 9
// formatos del menú de permisos, para que se distingan a simple vista dentro del PDF
// consolidado (tanto en pantalla como impreso — ver reglas .fmt-* generadas más abajo).
$formatStyles = [
    'permiso'     => ['code' => '01 · PT',    'label' => 'Permiso de Trabajo',         'icon' => '📋', 'color' => '#2E5C8A'],
    'analisis'    => ['code' => '02 · ATS',   'label' => 'Análisis de Trabajo Seguro', 'icon' => '🛡️', 'color' => '#7A4E96'],
    'inspeccion'  => ['code' => '03 · INSP',  'label' => 'Inspección de Trabajo',      'icon' => '🔍', 'color' => '#2F7A4E'],
    'alturas'     => ['code' => 'CA · ALT',   'label' => 'Trabajo en Alturas',         'icon' => '🦺', 'color' => '#2E7D8C'],
    'confinados'  => ['code' => 'CA · CONF',  'label' => 'Espacios Confinados',        'icon' => '🛢️', 'color' => '#8A5A2E'],
    'caliente'    => ['code' => 'CA · CAL',   'label' => 'Trabajo en Caliente',        'icon' => '🔥', 'color' => '#B33A3A'],
    'electrico'   => ['code' => 'CA · ELEC',  'label' => 'Riesgo Eléctrico',           'icon' => '⚡', 'color' => '#B8860B'],
    'energizadas' => ['code' => 'CA · ENE',   'label' => 'Líneas Energizadas',         'icon' => '🔴', 'color' => '#C1561B'],
    'izaje'       => ['code' => 'CA · IZA',   'label' => 'Izaje de Cargas',            'icon' => '🏗️', 'color' => '#A6336B'],
];

// Checklist de "controles" propio de cada certificado de apoyo — reflejan exactamente
// las mismas claves/etiquetas que cada visor_*.php individual (fuente de verdad).
$apoyoControlesLabels = [
    'alturas' => [
        'clima' => 'Condiciones climáticas apropiadas', 'anclaje' => 'Puntos de anclaje aprobados',
        'inspeccion_equipos' => 'Equipos e instalaciones inspeccionados', 'epp' => 'Equipos de protección adecuados',
        'condicion_fisica' => 'Sin fatiga/mareo que afecte seguridad', 'psicoactivas' => 'No uso de sustancias psicoactivas',
        'rescate' => 'Equipos de rescate disponibles', 'certificado' => 'Certificado seguro en altura vigente',
        'plan_rescate' => 'Plan de rescate disponible', 'arnes' => 'Inspección preoperacional arnés y equipos',
    ],
    'confinados' => [
        'atmosfera' => '¿Se ha verificado la atmósfera interior (CO2, O2, Explosividad)?',
        'monitoreo' => '¿Monitoreo continuo de la atmósfera?', 'ventilacion' => '¿Ventilación adecuada (natural o forzada)?',
        'senalizacion' => '¿Área señalizada y delimitada?', 'vias' => '¿Vías de ingreso y salida seguras?',
        'loto' => '¿Aislamiento de energías peligrosas (LOTO aplicado)?', 'equipos' => '¿Equipos en buen estado y certificados?',
        'personal' => '¿Personal autorizado y capacitado en espacios confinados?', 'certificacion' => '¿Certificación vigente (si aplica)?',
        'epp' => '¿Uso de EPP?',
    ],
    'caliente' => [
        'sustancias' => 'Sitio libre de sustancias combustibles o inflamables', 'lonas' => 'Equipos y materiales cubiertos con lonas',
        'mamparas' => 'Mamparas instaladas para aislar áreas vecinas', 'extintores' => 'Extintores adecuados dispuestos en el sitio de trabajo',
        'cables' => 'Cables y conexiones de equipos en buenas condiciones', 'instrucciones' => 'Ejecutor recibió instrucciones y precauciones de la tarea',
        'epp' => 'Elementos de protección personal apropiados disponibles', 'otros_permisos' => 'Consulta de otros permisos (alturas/espacios confinados)',
        'conocimiento' => 'Ejecutor conoce el equipo y los procedimientos del permiso', 'otros_controles' => 'Otros controles requeridos según la actividad',
    ],
    'electrico' => [
        'desenergizacion' => 'Desenergización y/o aislamiento de energías', 'evaluacion' => 'Evaluación previa del riesgo',
        'atmosferas' => 'Eliminación o control de atmósferas peligrosas', 'caidas' => 'Sistemas de protección contra caídas',
        'ignicion' => 'Control de fuentes de ignición', 'delimitacion' => 'Delimitación y señalización del área',
        'vigilancia' => 'Vigilancia y monitoreo continuo', 'epp' => 'Uso correcto de EPP crítico',
        'competencia' => 'Competencia y capacitación del personal', 'emergencia' => 'Plan de emergencia y rescate',
    ],
    'energizadas' => [
        'analisis_riesgo' => 'Análisis de riesgo eléctrico y arco eléctrico', 'distancias' => 'Distancias de seguridad',
        'herramientas' => 'Uso de herramientas aisladas certificadas', 'proteccion_arco' => 'Equipos de protección contra arco eléctrico',
        'guantes' => 'Guantes dieléctricos certificados', 'delimitacion' => 'Delimitación y control del área',
        'supervision' => 'Supervisión permanente', 'personal' => 'Personal altamente competente y autorizado',
        'aislantes' => 'Verificación del estado de los elementos aislantes', 'emergencia' => 'Plan de emergencia y respuesta',
    ],
    'izaje' => [
        'plan_izaje' => 'Plan de izaje aprobado', 'peso_carga' => 'Verificación del peso de la carga',
        'inspeccion_equipo' => 'Inspección de equipos de izaje', 'capacidad_carga' => 'Capacidad de carga del equipo',
        'aparejos' => 'Selección correcta de aparejos', 'delimitacion' => 'Delimitación del área de izaje',
        'terreno' => 'Condiciones del terreno y estabilidad', 'clima' => 'Condiciones climáticas',
        'personal' => 'Personal competente', 'comunicacion' => 'Comunicación efectiva',
    ],
];

$p1 = !empty($flujo['pasos']['permiso']['completado']);
$p2 = !empty($flujo['pasos']['analisis']['completado']);
$p3 = !empty($flujo['pasos']['inspeccion']['completado']);
$completado = ($flujo['estado'] ?? '') === 'completado';
$apoyos = $flujo['apoyos'] ?? [];
$documentoPdf = $flujo['documento_pdf'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Expediente <?= e($flujo['folio'] ?? '') ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <style>
        /* ── PANTALLA ── */
        body { font-family: 'Barlow', sans-serif; font-size: 13px; background: #0B0E14; color: #E2E8F0; margin: 0; padding: 0; }

        .action-bar {
            background: #0F172A; border-bottom: 1px solid #1E293B;
            padding: 12px 28px; display: flex; align-items: center;
            justify-content: space-between; gap: 12px; position: sticky; top: 0; z-index: 100;
        }
        .bar-left  { display: flex; align-items: center; gap: 14px; }
        .bar-right { display: flex; align-items: center; gap: 10px; }
        .bar-title { font-family: 'Space Mono', monospace; font-size: 12px; color: #00F0FF; font-weight: 700; }
        .bar-sub   { font-family: 'Space Mono', monospace; font-size: 10px; color: #94A3B8; }
        .btn-nav {
            background: transparent; border: 1px solid #1E293B; color: #94A3B8;
            padding: 7px 14px; border-radius: 4px; text-decoration: none;
            font-family: 'Space Mono', monospace; font-size: 10px; transition: all 0.2s; cursor: pointer;
        }
        .btn-nav:hover { border-color: #00F0FF; color: #00F0FF; }
        .btn-print { background: #10B981; border: none; color: #fff; padding: 8px 18px; border-radius: 4px; font-family: 'Space Mono', monospace; font-size: 11px; font-weight: 700; cursor: pointer; transition: all 0.2s; }
        .btn-print:hover { background: #059669; }

        .print-wrap { max-width: 1100px; margin: 0 auto; padding: 30px 20px; }

        /* ── PORTADA ── */
        .portada {
            background: #151A22; border: 1px solid #1E293B; border-left: 5px solid #00F0FF;
            border-radius: 10px; padding: 32px; margin-bottom: 28px;
        }
        .portada-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 20px; margin-bottom: 24px; }
        .folio-tag { font-family: 'Space Mono', monospace; font-size: 22px; font-weight: 700; color: #00F0FF; }
        .estado-badge {
            font-family: 'Space Mono', monospace; font-size: 10px; font-weight: 700;
            padding: 4px 14px; border-radius: 99px;
        }
        .estado-completo { background: rgba(16,185,129,0.12); border: 1px solid rgba(16,185,129,0.4); color: #10B981; }
        .estado-progreso { background: rgba(255,176,0,0.1); border: 1px solid rgba(255,176,0,0.35); color: #FFB000; }

        .info-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px 24px; }
        .info-item { display: flex; flex-direction: column; gap: 3px; }
        .info-label { font-family: 'Space Mono', monospace; font-size: 9px; color: #94A3B8; text-transform: uppercase; letter-spacing: 1px; }
        .info-val   { font-size: 14px; font-weight: 600; color: #fff; }

        /* ── PROGRESS TRACK ── */
        .track-section { margin-top: 22px; }
        .track-title { font-family: 'Space Mono', monospace; font-size: 9px; color: #94A3B8; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 10px; }
        .track { display: flex; align-items: flex-start; }
        .ts { display: flex; flex-direction: column; align-items: center; flex-shrink: 0; width: 80px; }
        .tc { flex: 1; height: 2px; background: #1E293B; margin-top: 13px; }
        .tc.done { background: #00F0FF; }
        .td {
            width: 28px; height: 28px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 11px; font-weight: 700; font-family: 'Space Mono', monospace;
        }
        .td-done { background: #00F0FF; color: #0B0E14; box-shadow: 0 0 10px rgba(0,240,255,0.5); }
        .td-pend { background: #1E293B; color: #94A3B8; border: 2px solid #1E293B; }
        .tl { font-family: 'Space Mono', monospace; font-size: 8px; color: #94A3B8; text-align: center; margin-top: 4px; line-height: 1.4; }
        .ts.done .tl { color: #00F0FF; }

        .apoyos-row { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 14px; }
        .apoyo-chip {
            font-family: 'Space Mono', monospace; font-size: 9px; color: #10B981;
            background: rgba(16,185,129,0.08); border: 1px solid rgba(16,185,129,0.22);
            padding: 3px 9px; border-radius: 99px;
        }

        /* ── LEYENDA DE FORMATOS ── */
        .legend-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 7px; margin-top: 14px; }
        .legend-chip {
            display: flex; align-items: center; gap: 7px; padding: 6px 9px;
            border-radius: 6px; background: #0F172A; border: 1px solid #1E293B;
            border-left: 5px solid var(--c); transition: opacity 0.2s;
        }
        .legend-chip.inactivo { opacity: 0.32; }
        .legend-code { font-family: 'Space Mono', monospace; font-size: 9px; font-weight: 700; color: var(--c); white-space: nowrap; }
        .legend-label { font-size: 10px; color: #E2E8F0; line-height: 1.3; }

        /* ── SECCIÓN DE DOCUMENTO ── */
        .doc-section { margin-bottom: 30px; page-break-inside: avoid; }

        .doc-header {
            background: #151A22; border: 1px solid #1E293B; border-left: 6px solid #00F0FF;
            padding: 14px 20px; border-radius: 8px; display: flex;
            align-items: center; justify-content: space-between; margin-bottom: 0;
            cursor: pointer;
        }

        .doc-header-left { display: flex; align-items: center; gap: 14px; }
        .doc-icon { font-size: 20px; line-height: 1; flex-shrink: 0; }
        .doc-num { font-family: 'Space Mono', monospace; font-size: 10px; font-weight: 700; color: #fff; background: #0F172A; border: 1px solid #1E293B; padding: 3px 9px; border-radius: 4px; white-space: nowrap; }
        .doc-name { font-size: 15px; font-weight: 700; color: #fff; }
        .doc-ts { font-family: 'Space Mono', monospace; font-size: 9px; color: #94A3B8; }
        .doc-toggle { font-size: 18px; color: #94A3B8; transition: transform 0.2s; }
        .doc-body { display: none; }
        .doc-body.open { display: block; }

        /* ── TABLA DOCUMENTO (estilo físico imprimible) ── */
        .doc-print {
            background: #fff; color: #000; padding: 24px; border-radius: 0 0 8px 8px;
            font-family: 'Barlow', sans-serif; font-size: 8.5pt;
            border: 1px solid #1E293B; border-top: 7px solid #00F0FF;
        }
        .doc-print table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .doc-print td, .doc-print th { border: 1px solid #000; padding: 3px 5px; vertical-align: top; }
        .doc-print .lbl { font-weight: bold; background: #f1f5f9; width: 22%; font-size: 8pt; }
        .doc-print .sec { background: #1e3a8a; color: #fff; font-weight: 700; text-align: center; text-transform: uppercase; font-size: 8.5pt; padding: 4px; }
        .doc-print .sub-th { background: #e2e8f0; font-size: 7.5pt; font-weight: 700; text-align: left; }
        .doc-print .header-doc { text-align: center; font-size: 12pt; font-weight: bold; padding: 6px; }
        .doc-print .sub-meta { font-size: 7pt; text-align: left; vertical-align: top; }
        .doc-print .header-doc-tag {
            display: inline-block; font-family: 'Space Mono', monospace; font-size: 8pt; font-weight: 700;
            color: #fff; padding: 2px 10px; border-radius: 99px; margin-top: 4px;
        }
        .firma-img { max-height: 45px; max-width: 120px; }

        <?php foreach ($formatStyles as $fmtKey => $fs): ?>
        .doc-section.fmt-<?= $fmtKey ?> .doc-header { border-left-color: <?= $fs['color'] ?>; background: linear-gradient(90deg, <?= $fs['color'] ?>26 0%, #151A22 55%); }
        .doc-section.fmt-<?= $fmtKey ?> .doc-num { background: <?= $fs['color'] ?>; border-color: <?= $fs['color'] ?>; color: #fff; }
        .doc-section.fmt-<?= $fmtKey ?> .doc-print { border-top-color: <?= $fs['color'] ?>; }
        .doc-section.fmt-<?= $fmtKey ?> .doc-print .sec { background: <?= $fs['color'] ?>; }
        .doc-section.fmt-<?= $fmtKey ?> .doc-print .header-doc-tag { background: <?= $fs['color'] ?>; }
        <?php endforeach; ?>

        /* El PDF adjunto no es uno de los 9 formatos del menú — identidad neutra aparte */
        .doc-section.fmt-anexo .doc-header { border-left-color: #64748B; background: linear-gradient(90deg, #64748B26 0%, #151A22 55%); }
        .doc-section.fmt-anexo .doc-num { background: #64748B; border-color: #64748B; color: #fff; }

        /* ── SIN DATOS ── */
        .no-data {
            background: rgba(255,176,0,0.05); border: 1px dashed rgba(255,176,0,0.3);
            border-radius: 0 0 8px 8px; padding: 18px 22px;
            font-family: 'Space Mono', monospace; font-size: 10px; color: #FFB000;
        }
        .no-data a { color: #00F0FF; }

        /* ── PÁGINAS DE PDF ADJUNTO ── */
        .pdf-page-img { display: block; width: 100%; margin-bottom: 10px; border: 1px solid #1E293B; }
        @media print { .pdf-page-img { border: none; page-break-after: always; } }

        /* ── PRINT ── */
        @media print {
            * { print-color-adjust: exact !important; -webkit-print-color-adjust: exact !important; }
            body { background: #fff; color: #000; }
            .action-bar { display: none !important; }
            .portada { background: #fff; border: 2px solid #000; border-left: 5px solid #000; color: #000; page-break-after: always; }
            .folio-tag { color: #000; }
            .info-label, .info-val, .bar-sub, .tl, .doc-ts, .doc-name, .track-title { color: #000; }
            .td-done { background: #000; color: #fff; box-shadow: none; }
            .td-pend { background: #ccc; color: #555; border-color: #aaa; }
            .tc.done { background: #000; }
            .doc-header { background: #fff; color: #000; }
            .doc-body { display: block !important; }
            .doc-toggle { display: none; }
            .apoyo-chip { border: 1px solid #000; color: #000; background: transparent; }
            .doc-section { page-break-inside: avoid; page-break-before: auto; }
            .legend-chip { background: #fff; }
            .legend-label { color: #000; }
            .legend-chip.inactivo { opacity: 0.4; }
        }
    </style>
</head>
<body>

<div class="action-bar">
    <div class="bar-left">
        <a href="rev_flujos.php" class="btn-nav">← Galería</a>
        <div>
            <div class="bar-title"><?= e($flujo['folio'] ?? '—') ?></div>
            <div class="bar-sub"><?= e($flujo['empresa'] ?? '—') ?> &nbsp;·&nbsp; <?= e($sede) ?></div>
        </div>
    </div>
    <div class="bar-right">
        <button class="btn-nav" onclick="toggleAll()">☰ Expandir Todo</button>
        <button class="btn-print" id="btn-print" onclick="window.print()" disabled>⏳ Cargando documento...</button>
    </div>
</div>

<div class="print-wrap">

    <!-- ══ PORTADA ══ -->
    <div class="portada">
        <div class="portada-top">
            <div class="folio-tag"><?= e($flujo['folio'] ?? '—') ?></div>
            <span class="estado-badge <?= $completado ? 'estado-completo' : 'estado-progreso' ?>">
                <?= $completado ? '✓ PERMISO COMPLETADO' : '● EN PROGRESO' ?>
            </span>
        </div>

        <div class="info-grid">
            <div class="info-item"><span class="info-label">Empresa / Contratista</span><span class="info-val"><?= e($flujo['empresa'] ?? '—') ?></span></div>
            <div class="info-item"><span class="info-label">Responsable del Trabajo</span><span class="info-val"><?= e($flujo['responsable'] ?? '—') ?></span></div>
            <div class="info-item"><span class="info-label">Tipo de Trabajo</span><span class="info-val"><?= e($flujo['tipo_trabajo'] ?? '—') ?></span></div>
            <div class="info-item"><span class="info-label">Área de Ejecución</span><span class="info-val"><?= e($flujo['area'] ?? '—') ?></span></div>
            <div class="info-item"><span class="info-label">Fecha de Inicio</span><span class="info-val"><?= fmtD($flujo['fecha_inicio'] ?? '') ?></span></div>
            <div class="info-item"><span class="info-label">Registrado por</span><span class="info-val"><?= e($flujo['usuario_sys'] ?? '—') ?> · <?= e($sede) ?></span></div>
        </div>

        <div class="track-section">
            <div class="track-title">// Estado de Documentos</div>
            <div class="track">
                <div class="ts <?= $p1 ? 'done' : '' ?>">
                    <div class="td <?= $p1 ? 'td-done' : 'td-pend' ?>"><?= $p1 ? '✓' : '01' ?></div>
                    <div class="tl">Permiso<br><?= $p1 ? fmtTs($flujo['pasos']['permiso']['timestamp'] ?? '') : 'Pendiente' ?></div>
                </div>
                <div class="tc <?= $p1 ? 'done' : '' ?>"></div>
                <div class="ts <?= $p2 ? 'done' : '' ?>">
                    <div class="td <?= $p2 ? 'td-done' : 'td-pend' ?>"><?= $p2 ? '✓' : '02' ?></div>
                    <div class="tl">ATS<br><?= $p2 ? fmtTs($flujo['pasos']['analisis']['timestamp'] ?? '') : 'Pendiente' ?></div>
                </div>
                <div class="tc <?= $p2 ? 'done' : '' ?>"></div>
                <div class="ts <?= $p3 ? 'done' : '' ?>">
                    <div class="td <?= $p3 ? 'td-done' : 'td-pend' ?>"><?= $p3 ? '✓' : '03' ?></div>
                    <div class="tl">Inspección<br><?= $p3 ? fmtTs($flujo['pasos']['inspeccion']['timestamp'] ?? '') : 'Pendiente' ?></div>
                </div>
            </div>
        </div>

        <?php if (!empty($apoyos)): ?>
        <div class="apoyos-row">
            <?php foreach ($apoyos as $ap): $inf = $apoyoLabels[$ap['key']] ?? ['icon' => '📋', 'label' => $ap['key']]; ?>
                <span class="apoyo-chip"><?= e($inf['icon'] . ' ' . $inf['label']) ?></span>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="track-section">
            <div class="track-title">// Leyenda de Formatos (colores del expediente)</div>
            <div class="legend-grid">
                <?php
                $apoyoKeysPresentes = array_column($apoyos, 'key');
                foreach ($formatStyles as $fmtKey => $fs):
                    $presente = match ($fmtKey) {
                        'permiso'    => $p1,
                        'analisis'   => $p2,
                        'inspeccion' => $p3,
                        default      => in_array($fmtKey, $apoyoKeysPresentes),
                    };
                ?>
                <div class="legend-chip<?= $presente ? '' : ' inactivo' ?>" style="--c: <?= $fs['color'] ?>">
                    <span class="legend-code"><?= $fs['icon'] ?> <?= e($fs['code']) ?></span>
                    <span class="legend-label"><?= e($fs['label']) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- ══ PASO 01: PERMISO DE TRABAJO ══ -->
    <div class="doc-section fmt-permiso">
        <div class="doc-header" onclick="toggleDoc(this)">
            <div class="doc-header-left">
                <span class="doc-icon"><?= $formatStyles['permiso']['icon'] ?></span>
                <span class="doc-num"><?= e($formatStyles['permiso']['code']) ?></span>
                <span class="doc-name">Permiso de Trabajo</span>
                <?php if ($p1): ?><span class="doc-ts">✓ <?= fmtTs($flujo['pasos']['permiso']['timestamp'] ?? '') ?></span><?php endif; ?>
            </div>
            <span class="doc-toggle">▼</span>
        </div>
        <div class="doc-body">
        <?php if ($regPer): $d = $regPer['datos']; ?>
        <div class="doc-print">
            <table>
                <tr>
                    <td style="width:18%"><img src="/img/logo_empresa.jpeg" alt="Logo" style="max-height:55px;"></td>
                    <td style="text-align:center;vertical-align:middle;">
                        <div style="font-size:9pt;font-weight:bold;">Sistema de Gestión de Seguridad y Salud en el Trabajo</div>
                        <div class="header-doc">Permiso de Trabajo — HSEQ</div>
                        <span class="header-doc-tag"><?= $formatStyles['permiso']['icon'] ?> <?= e($formatStyles['permiso']['code']) ?></span>
                    </td>
                    <td class="sub-meta">Código: FOR-HSEQ-005<br>Versión: 2<br>Fecha: 8/5/2021<br><?= e($regPer['timestamp'] ?? '') ?></td>
                </tr>
            </table>
            <table><tr><td class="sec" colspan="4">1. GENERALIDADES</td></tr>
                <tr><td class="lbl">Frecuencia</td><td><?= e($d['frecuencia']??'') ?></td><td class="lbl">Área</td><td><?= e($d['area_realiza']??'') ?></td></tr>
                <tr><td class="lbl">Tipo Trabajo</td><td><?= e($d['tipo_trabajo']??'') ?></td><td class="lbl">Altura aprox.</td><td><?= e($d['altura_aprox']??'') ?></td></tr>
                <tr><td class="lbl">Fecha Inicio</td><td><?= e($d['fecha_inicio']??'') ?></td><td class="lbl">Fecha Fin</td><td><?= e($d['fecha_fin']??'') ?></td></tr>
                <tr><td class="lbl">Hora Inicio</td><td><?= e($d['hora_inicio']??'') ?></td><td class="lbl">Hora Fin</td><td><?= e($d['hora_fin']??'') ?></td></tr>
                <tr><td class="lbl">Ciudad</td><td><?= e($d['ciudad']??'') ?></td><td class="lbl">Sede</td><td><?= e($regPer['sede_sys']??'') ?></td></tr>
                <tr><td class="lbl" colspan="4" style="text-align:center">Descripción de la Actividad</td></tr>
                <tr><td colspan="4"><?= ef($d['descripcion_actividad']??'') ?></td></tr>
            </table>
            <table><tr><td class="sec" colspan="6">2. TRABAJADORES AUTORIZADOS</td></tr>
                <tr><th class="sub-th">Nombre</th><th class="sub-th">Tipo Doc</th><th class="sub-th">N° Doc</th><th class="sub-th">Fecha Curso</th><th class="sub-th">Día</th><th class="sub-th">Firma</th></tr>
                <?php $noms = $d['t_nombre'] ?? []; for ($i=0; $i<count($noms); $i++): ?>
                <tr>
                    <td><?= e($d['t_nombre'][$i]??'') ?></td><td><?= e($d['t_tipo'][$i]??'') ?></td>
                    <td><?= e($d['t_doc'][$i]??'') ?></td><td><?= e($d['t_fecha_curso'][$i]??'') ?></td>
                    <td><?= e($d['t_dia'][$i]??'') ?></td>
                    <td><?php if (!empty($d['t_firma'][$i])): ?><img src="<?= e($d['t_firma'][$i]) ?>" class="firma-img"><?php endif; ?></td>
                </tr>
                <?php endfor; ?>
            </table>
            <table><tr><td class="sec" colspan="4">3. VIGÍA Y RESPONSABLE DE EMERGENCIA</td></tr>
                <tr><th class="sub-th">Rol</th><th class="sub-th">Nombre / Documento / Día</th><th class="sub-th" style="width:16%">Firma</th></tr>
                <tr>
                    <td class="lbl">Vigía Designado</td>
                    <td><?= e(trim(($d['vigia_nombre']??'') . ' — ' . ($d['vigia_tipo_doc']??'') . ' ' . ($d['vigia_doc']??'') . ' — ' . ($d['vigia_dia']??''), " —")) ?></td>
                    <td><?php if (!empty($d['vigia_firma'])): ?><img src="<?= e($d['vigia_firma']) ?>" class="firma-img"><?php endif; ?></td>
                </tr>
                <tr>
                    <td class="lbl">Resp. Plan Emergencia</td>
                    <td><?= e(trim(($d['emergencia_nombre']??'') . ' — ' . ($d['emergencia_tipo_doc']??'') . ' ' . ($d['emergencia_doc']??'') . ' — ' . ($d['emergencia_dia']??''), " —")) ?></td>
                    <td><?php if (!empty($d['emergencia_firma'])): ?><img src="<?= e($d['emergencia_firma']) ?>" class="firma-img"><?php endif; ?></td>
                </tr>
            </table>
            <table><tr><td class="sec" colspan="4">4. EQUIPOS Y SISTEMAS</td></tr>
                <?php
                $equiposLabels = [
                    'eq_arnes' => 'Arnés', 'eq_lv_vertical' => 'Línea de Vida Vertical',
                    'eq_eslinga_pos' => 'Eslinga de Posicionamiento', 'eq_lv_horizontal' => 'Línea de Vida Horizontal',
                    'eq_eslinga_abs' => 'Eslinga con Absorbedor', 'eq_anclaje_fijo' => 'Punto de Anclaje Fijo',
                    'eq_freno' => 'Freno', 'eq_anclaje_movil' => 'Punto de Anclaje Móvil',
                    'eq_red_seg' => 'Red de Seguridad', 'eq_malla' => 'Malla o Línea de Restricción',
                ];
                $equiposPares = array_chunk($equiposLabels, 4, true);
                foreach ($equiposPares as $par): ?>
                <tr><?php foreach ($par as $k => $lbl): ?><td><?= e($lbl) ?>: <b><?= e($d[$k]??'') ?></b></td><?php endforeach; ?></tr>
                <?php endforeach; ?>
                <?php if (!empty($d['eq_otro_nombre_1']) || !empty($d['eq_otro_nombre_2'])): ?>
                <tr>
                    <td colspan="2"><?= e($d['eq_otro_nombre_1']??'Otro equipo 1') ?>: <b><?= e($d['eq_otro_valor_1']??'') ?></b></td>
                    <td colspan="2"><?= e($d['eq_otro_nombre_2']??'Otro equipo 2') ?>: <b><?= e($d['eq_otro_valor_2']??'') ?></b></td>
                </tr>
                <?php endif; ?>
                <?php
                $sistemaTipo   = $d['sistema_tipo']   ?? '';
                $sistemaAcceso = $d['sistema_acceso'] ?? '';
                ?>
                <tr><td colspan="2">Tipo de Sistema: <b><?= e(is_array($sistemaTipo) ? implode(', ', $sistemaTipo) : $sistemaTipo) ?></b></td>
                    <td colspan="2">Acceso: <b><?= e(is_array($sistemaAcceso) ? implode(', ', $sistemaAcceso) : $sistemaAcceso) ?></b></td></tr>
            </table>
            <table><tr><td class="sec" colspan="4">5. MEDIDAS PREVENTIVAS Y COLECTIVAS</td></tr>
                <?php
                $prevLabels = [
                    'prev_peligros' => 'Análisis otros peligros', 'prev_cap' => 'Capacitación SST',
                    'prev_ing' => 'Sistemas ingeniería', 'prev_proc' => 'Procedimientos',
                    'prev_susp' => 'Trabajos en suspensión',
                ];
                $colLabels = [
                    'col_delim' => 'Delimitación área', 'col_acceso' => 'Control de acceso',
                    'col_adv' => 'Línea advertencia', 'col_desniv' => 'Manejo desniveles',
                    'col_senal' => 'Señalización', 'col_sup' => 'Control superficies',
                    'col_bandas' => 'Bandas', 'col_vigia' => 'Ayudante o vigía',
                ];
                foreach (array_chunk($prevLabels, 4, true) as $par): ?>
                <tr><?php foreach ($par as $k => $lbl): ?><td><?= e($lbl) ?>: <b><?= e($d[$k]??'') ?></b></td><?php endforeach; ?></tr>
                <?php endforeach; ?>
                <?php foreach (array_chunk($colLabels, 4, true) as $par): ?>
                <tr><?php foreach ($par as $k => $lbl): ?><td><?= e($lbl) ?>: <b><?= e($d[$k]??'') ?></b></td><?php endforeach; ?></tr>
                <?php endforeach; ?>
            </table>
            <table><tr><td class="sec" colspan="3">6. CONDICIONES DE SEGURIDAD Y VERIFICACIÓN DIARIA</td></tr>
                <tr><th class="sub-th">Pregunta</th><th class="sub-th" style="width:15%">Día</th><th class="sub-th" style="width:15%">Verificación</th></tr>
                <?php for ($i = 0; $i <= 15; $i++):
                    if (!isset($d["preg_t_$i"])) continue;
                ?>
                <tr>
                    <td><?= e($d["preg_t_$i"]??'') ?></td>
                    <td><?= e($d["preg_d_$i"]??'') ?></td>
                    <td><?= e($d["preg_v_$i"]??'') ?></td>
                </tr>
                <?php endfor; ?>
            </table>
            <table><tr><td class="sec" colspan="3">7. RESPONSABLES DE VERIFICACIÓN Y AUTORIZACIÓN</td></tr>
                <tr><th class="sub-th">Responsable</th><th class="sub-th" style="width:15%">Día</th><th class="sub-th" style="width:16%">Firma</th></tr>
                <?php
                $respRoles = [
                    1 => 'Firma de verificación de quien autoriza el trabajo',
                    2 => 'Firma de verificación de coordinador',
                    3 => 'Firma de verificación de quien autoriza el trabajo suplente',
                    4 => 'Firma de verificación de coordinador suplente',
                ];
                foreach ($respRoles as $i => $rol): ?>
                <tr>
                    <td><?= e($rol) ?></td>
                    <td><?= e($d["resp_dia_$i"]??'') ?></td>
                    <td><?php if (!empty($d["resp_firma_$i"])): ?><img src="<?= e($d["resp_firma_$i"]) ?>" class="firma-img"><?php endif; ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <table><tr><td class="sec" colspan="6">8. HERRAMIENTAS A UTILIZAR</td></tr>
                <?php
                $herrLabels = ['Manuales', 'Eléctricas', 'Neumáticas', 'Hidráulicas', 'Mecánicas', 'Otra'];
                ?>
                <tr><?php foreach ($herrLabels as $i => $lbl): ?><td><?= e($lbl) ?>: <b><?= e($d["herr_$i"]??'') ?></b></td><?php endforeach; ?></tr>
            </table>
            <?php
            $tieneSuspension = !empty($d['susp_nombre']) || !empty($d['susp_causa']) || !empty($d['susp_motivo']);
            if ($tieneSuspension): ?>
            <table><tr><td class="sec" colspan="4">9. SUSPENSIÓN DEL PERMISO</td></tr>
                <tr><td class="lbl">Coordinador</td><td><?= e($d['susp_nombre']??'') ?></td><td class="lbl">Documento</td><td><?= e(trim(($d['susp_tipo_doc']??'') . ' ' . ($d['susp_num_doc']??''))) ?></td></tr>
                <tr><td class="lbl">Día</td><td><?= e($d['susp_dia']??'') ?></td><td class="lbl">Fecha</td><td><?= e($d['susp_fecha']??'') ?></td></tr>
                <tr><td class="lbl">Motivo</td><td><?= e($d['susp_motivo']??'') ?></td><td class="lbl">Causa / Descripción</td><td><?= e($d['susp_causa']??'') ?></td></tr>
                <tr><td class="lbl">Firma Coordinador</td><td><?php if (!empty($d['susp_firma'])): ?><img src="<?= e($d['susp_firma']) ?>" class="firma-img"><?php endif; ?></td>
                    <td class="lbl">Firma Reactivación</td><td><?php if (!empty($d['susp_coord_firma'])): ?><img src="<?= e($d['susp_coord_firma']) ?>" class="firma-img"><?php endif; ?></td></tr>
            </table>
            <?php endif; ?>
            <table><tr><td class="sec" colspan="4">10. OBSERVACIONES Y AUTORIZACIÓN</td></tr>
                <tr><td style="width:50%" colspan="2"><b>Observaciones:</b><br><?= ef($d['observaciones']??'') ?></td>
                    <td colspan="2">¿Aprueba?: <b><?= e($d['aprueba']??'') ?></b><br><?= e($d['motivo_negacion']??'') ?></td></tr>
            </table>
            <table><tr><td class="sec" colspan="4">11. FIRMAS DE AUTORIZACIÓN</td></tr>
                <tr><th class="sub-th">Rol</th><th class="sub-th">Nombre</th><th class="sub-th">Cédula</th><th class="sub-th">Firma</th></tr>
                <?php
                    $roles = ['Jefe Encargado','Jefe Encargado Suplente','Coordinador Trabajo Seguro','Coordinador Suplente'];
                    for ($i=0; $i<=3; $i++): ?>
                <tr>
                    <td><?= e($roles[$i]??'') ?></td>
                    <td><?= e($d["firma_n_$i"]??'') ?></td>
                    <td><?= e($d["firma_id_$i"]??'') ?></td>
                    <td><?php if (!empty($d["firma_f_$i"])): ?><img src="<?= e($d["firma_f_$i"]) ?>" class="firma-img"><?php endif; ?></td>
                </tr>
                <?php endfor; ?>
            </table>
        </div>
        <?php elseif ($p1): ?>
        <div class="no-data">
            ℹ️ Este permiso fue registrado antes de vincular el ID de documento al flujo.
            <a href="../permiso_trabajo/rev_permiso_trabajo.php" target="_blank">Ver registros de Permiso de Trabajo →</a>
        </div>
        <?php else: ?>
        <div class="no-data">⚠️ Este paso aún no ha sido completado.</div>
        <?php endif; ?>
        </div>
    </div>

    <!-- ══ PASO 02: ANÁLISIS DE TRABAJO SEGURO ══ -->
    <div class="doc-section fmt-analisis">
        <div class="doc-header" onclick="toggleDoc(this)">
            <div class="doc-header-left">
                <span class="doc-icon"><?= $formatStyles['analisis']['icon'] ?></span>
                <span class="doc-num"><?= e($formatStyles['analisis']['code']) ?></span>
                <span class="doc-name">Análisis de Trabajo Seguro (ATS)</span>
                <?php if ($p2): ?><span class="doc-ts">✓ <?= fmtTs($flujo['pasos']['analisis']['timestamp'] ?? '') ?></span><?php endif; ?>
            </div>
            <span class="doc-toggle">▼</span>
        </div>
        <div class="doc-body">
        <?php if ($regAts): $d = $regAts['datos']; ?>
        <div class="doc-print">
            <table>
                <tr>
                    <td style="width:18%"><img src="/img/logo_empresa.jpeg" alt="Logo" style="max-height:55px;"></td>
                    <td style="text-align:center;vertical-align:middle;">
                        <div style="font-size:9pt;font-weight:bold;">Sistema de Gestión de Seguridad y Salud en el Trabajo</div>
                        <div class="header-doc">Análisis de Trabajo Seguro (ATS)</div>
                        <span class="header-doc-tag"><?= $formatStyles['analisis']['icon'] ?> <?= e($formatStyles['analisis']['code']) ?></span>
                    </td>
                    <td class="sub-meta">Código: FOR-HSEQ-006<br><?= e($regAts['timestamp']??'') ?></td>
                </tr>
            </table>
            <table><tr><td class="sec" colspan="4">1. GENERALIDADES</td></tr>
                <tr><td class="lbl">Tipo Trabajo</td><td><?= e($d['tipo_trabajo']??'') ?></td><td class="lbl">Zona</td><td><?= e($d['zona_trabajo']??'') ?></td></tr>
                <tr><td class="lbl">Fecha Elaboración</td><td><?= e($d['fecha_elaboracion']??'') ?></td><td class="lbl">Dependencia</td><td><?= e($d['dependencia']??'') ?></td></tr>
                <tr><td class="lbl">Fecha Inicio</td><td><?= e($d['fecha_inicio']??'') ?></td><td class="lbl">Fecha Fin</td><td><?= e($d['fecha_fin']??'') ?></td></tr>
                <tr><td class="lbl">Hora Inicio</td><td><?= e($d['hora_inicio']??'') ?></td><td class="lbl">Hora Fin</td><td><?= e($d['hora_fin']??'') ?></td></tr>
                <tr><td class="lbl">Valoración Riesgo</td><td><?= e($d['valoracion_riesgo']??'') ?></td><td class="lbl">Altura máx.</td><td><?= e($d['altura_maxima']??'') ?> m</td></tr>
                <tr><td class="lbl">Frecuencia</td><td><?= e($d['frecuencia']??'') ?></td><td class="lbl">Equipo / Sistema</td><td><?= e($d['equipo_sistema']??'') ?></td></tr>
                <tr><td class="lbl" colspan="4" style="text-align:center">Descripción de la Actividad</td></tr>
                <tr><td colspan="4"><?= ef($d['descripcion_actividad']??'') ?></td></tr>
            </table>
            <table><tr><td class="sec" colspan="6">2. TRABAJADORES</td></tr>
                <tr><th class="sub-th">Nombre</th><th class="sub-th">Tipo Doc</th><th class="sub-th">N° Doc</th><th class="sub-th">Cargo</th><th class="sub-th">Día</th><th class="sub-th">Firma</th></tr>
                <?php foreach (($d['trabajadores'] ?? []) as $trab): if (empty($trab['nombre'])) continue; ?>
                <tr>
                    <td><?= e($trab['nombre']??'') ?></td><td><?= e($trab['tipo_doc']??'') ?></td>
                    <td><?= e($trab['documento']??'') ?></td><td><?= e($trab['cargo']??'') ?></td>
                    <td><?= e($trab['dia']??'') ?></td>
                    <td><?php if (!empty($trab['firma'])): ?><img src="<?= e($trab['firma']) ?>" class="firma-img"><?php endif; ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php
            $tieneCriticas = !empty($d['actividades_criticas']) || !empty($d['riesgos_criticos_detalle']);
            if ($tieneCriticas):
                $actDetalle = $d['actividad_critica_detalle'] ?? '';
                $riesgoDetalle = $d['riesgos_criticos_detalle'] ?? '';
            ?>
            <table><tr><td class="sec" colspan="4">3. ACTIVIDADES Y RIESGOS CRÍTICOS ASOCIADOS</td></tr>
                <tr><td class="lbl">¿Actividades críticas adicionales?</td><td><?= e($d['actividades_criticas']??'') ?></td>
                    <td class="lbl">Actividad(es)</td><td><?= e(is_array($actDetalle) ? implode(', ', $actDetalle) : $actDetalle) ?></td></tr>
                <tr><td class="lbl" colspan="4">Riesgos críticos: <?= e(is_array($riesgoDetalle) ? implode(', ', $riesgoDetalle) : $riesgoDetalle) ?></td></tr>
            </table>
            <?php endif; ?>
            <table><tr><td class="sec" colspan="6">4. ELEMENTOS, HERRAMIENTAS Y EQUIPOS A UTILIZAR</td></tr>
                <tr>
                    <td>Manuales: <b><?= e($d['herr_manuales']??'') ?></b></td>
                    <td>Eléctricas: <b><?= e($d['herr_electricas']??'') ?></b></td>
                    <td>Neumáticas: <b><?= e($d['herr_neumaticas']??'') ?></b></td>
                </tr>
                <tr>
                    <td>Hidráulicas: <b><?= e($d['herr_hidraulicas']??'') ?></b></td>
                    <td>Mecánicas: <b><?= e($d['herr_mecanicas']??'') ?></b></td>
                    <td>Otras: <b><?= e($d['herr_otras']??'') ?></b></td>
                </tr>
            </table>
            <table><tr><td class="sec" colspan="4">5. CONTROLES</td></tr>
                <tr>
                    <td>LOTO: <b><?= e($d['ctrl_loto']??'') ?></b></td>
                    <td>ATS: <b><?= e($d['ctrl_ats']??'') ?></b></td>
                    <td>Atmósferas: <b><?= e($d['ctrl_atmosferas']??'') ?></b></td>
                    <td>Caídas: <b><?= e($d['ctrl_caidas']??'') ?></b></td>
                </tr>
                <tr>
                    <td>Ignición: <b><?= e($d['ctrl_ignicion']??'') ?></b></td>
                    <td>Delimitación: <b><?= e($d['ctrl_delimitacion']??'') ?></b></td>
                    <td>Vigía: <b><?= e($d['ctrl_vigia']??'') ?></b></td>
                    <td>EPP: <b><?= e($d['ctrl_epp']??'') ?></b></td>
                </tr>
                <tr>
                    <td>Personal competente: <b><?= e($d['ctrl_personal']??'') ?></b></td>
                    <td colspan="3">Plan de emergencia y rescate: <b><?= e($d['ctrl_emergencia']??'') ?></b></td>
                </tr>
            </table>
            <table><tr><td class="sec" colspan="4">6. ELEMENTOS DE PROTECCIÓN PERSONAL (EPP)</td></tr>
                <?php
                $eppLabels = [
                    'epp_casco' => 'Casco', 'epp_auditivo' => 'Protector Auditivo',
                    'epp_chaleco' => 'Chaleco', 'epp_casco_dielectrico' => 'Casco Dieléctrico',
                    'epp_respiratorio' => 'Prot. Respiratorio', 'epp_overol' => 'Overol',
                    'epp_barbuquejo' => 'Barbuquejo', 'epp_botas' => 'Botas de Seguridad',
                    'epp_overol_ignifugo' => 'Overol Ignífugo', 'epp_visual' => 'Protector Visual',
                    'epp_guantes_carnaza' => 'Guantes Carnaza', 'epp_careta_soldar' => 'Careta Soldar',
                    'epp_delantal' => 'Delantal', 'epp_guantes_poli' => 'Guantes Poliuretano',
                    'epp_mascarilla' => 'Mascarilla', 'epp_polainas' => 'Polainas',
                    'epp_guantes_dielectrico' => 'Guantes Dieléctrico', 'epp_mangas_dielectricas' => 'Mangas Dieléctricas',
                ];
                foreach (array_chunk($eppLabels, 4, true) as $par): ?>
                <tr><?php foreach ($par as $k => $lbl): ?><td><?= e($lbl) ?>: <b><?= e($d[$k]??'') ?></b></td><?php endforeach; ?></tr>
                <?php endforeach; ?>
            </table>
            <?php
            $tieneEmergencia = !empty($d['tipo_emergencia']) || !empty($d['ruta_evacuacion']) || !empty($d['brigadista_responsable']);
            if ($tieneEmergencia): ?>
            <table><tr><td class="sec" colspan="4">7. PLAN DE EMERGENCIA</td></tr>
                <tr><td class="lbl">Tipo de Emergencia</td><td><?= e($d['tipo_emergencia']??'') ?></td><td class="lbl">Punto de Encuentro</td><td><?= e($d['punto_encuentro']??'') ?></td></tr>
                <tr><td class="lbl">Ruta de Evacuación</td><td><?= e($d['ruta_evacuacion']??'') ?></td><td class="lbl">Brigadista Responsable</td><td><?= e($d['brigadista_responsable']??'') ?></td></tr>
                <tr><td class="lbl">Equipos Disponibles</td><td colspan="3"><?= e($d['equipos_disponibles']??'') ?></td></tr>
            </table>
            <?php endif; ?>
            <table><tr><td class="sec" colspan="4">8. FIRMAS DE AUTORIZACIÓN</td></tr>
                <tr><th class="sub-th">Rol</th><th class="sub-th">Nombre</th><th class="sub-th">Cédula</th><th class="sub-th">Firma</th></tr>
                <?php
                $firmantes = [
                    ['Jefe',         $d['jefe_nombre']??'',           $d['jefe_id']??'',           $d['jefe_firma']??''],
                    ['Jefe Sup.',    $d['jefe_suplente_nombre']??'',  $d['jefe_suplente_id']??'',  $d['jefe_suplente_firma']??''],
                    ['Coordinador',  $d['coord_nombre']??'',          $d['coord_id']??'',          $d['coord_firma']??''],
                    ['Coord. Sup.',  $d['coord_suplente_nombre']??'', $d['coord_suplente_id']??'', $d['coord_suplente_firma']??''],
                ];
                foreach ($firmantes as [$rol, $nom, $ced, $firma]):
                    if (!$nom && !$ced) continue;
                ?>
                <tr>
                    <td><?= e($rol) ?></td><td><?= e($nom) ?></td><td><?= e($ced) ?></td>
                    <td><?php if ($firma): ?><img src="<?= e($firma) ?>" class="firma-img"><?php endif; ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <?php elseif ($p2): ?>
        <div class="no-data">
            ℹ️ ATS registrado antes de vincular ID al flujo.
            <a href="../analisis_trabajo/rev_analisis_trabajo.php" target="_blank">Ver registros ATS →</a>
        </div>
        <?php else: ?>
        <div class="no-data">⚠️ Este paso aún no ha sido completado.</div>
        <?php endif; ?>
        </div>
    </div>

    <!-- ══ PASO 03: INSPECCIÓN DE TRABAJO ══ -->
    <div class="doc-section fmt-inspeccion">
        <div class="doc-header" onclick="toggleDoc(this)">
            <div class="doc-header-left">
                <span class="doc-icon"><?= $formatStyles['inspeccion']['icon'] ?></span>
                <span class="doc-num"><?= e($formatStyles['inspeccion']['code']) ?></span>
                <span class="doc-name">Inspección de Trabajo</span>
                <?php if ($p3): ?><span class="doc-ts">✓ <?= fmtTs($flujo['pasos']['inspeccion']['timestamp'] ?? '') ?></span><?php endif; ?>
            </div>
            <span class="doc-toggle">▼</span>
        </div>
        <div class="doc-body">
        <?php if ($regIns): $d = $regIns['datos']; ?>
        <div class="doc-print">
            <table>
                <tr>
                    <td style="width:18%"><img src="/img/logo_empresa.jpeg" alt="Logo" style="max-height:55px;"></td>
                    <td style="text-align:center;vertical-align:middle;">
                        <div style="font-size:9pt;font-weight:bold;">Sistema de Gestión de Seguridad y Salud en el Trabajo</div>
                        <div class="header-doc">Inspección de Trabajo — HSEQ</div>
                        <span class="header-doc-tag"><?= $formatStyles['inspeccion']['icon'] ?> <?= e($formatStyles['inspeccion']['code']) ?></span>
                    </td>
                    <td class="sub-meta">Código: FOR-HSEQ-007<br><?= e($regIns['timestamp']??'') ?></td>
                </tr>
            </table>
            <table><tr><td class="sec" colspan="4">1. GENERALIDADES</td></tr>
                <tr><td class="lbl">Fecha</td><td><?= e($d['fecha']??'') ?></td><td class="lbl">Hora</td><td><?= e($d['hora']??'') ?></td></tr>
                <tr><td class="lbl">Empresa</td><td><?= e($d['empresa']??'') ?></td><td class="lbl">Sede</td><td><?= e($d['sede']??'') ?></td></tr>
                <tr><td class="lbl">Área</td><td><?= e($d['area']??'') ?></td><td class="lbl">Lugar</td><td><?= e($d['lugar']??'') ?></td></tr>
                <tr><td class="lbl">Inspector Responsable</td><td><?= e($d['inspector_responsable']??'') ?></td><td class="lbl">Cargo</td><td><?= e($d['cargo']??'') ?></td></tr>
                <tr><td class="lbl">Tipo de Actividad</td><td colspan="3"><?= e($d['tipo_actividad']??'') ?></td></tr>
                <tr><td class="lbl" colspan="4" style="text-align:center">Actividad a Realizar</td></tr>
                <tr><td colspan="4"><?= ef($d['actividad_realizar']??'') ?></td></tr>
            </table>
            <table><tr><td class="sec" colspan="6">2. PELIGROS ASOCIADOS AL TRABAJO</td></tr>
                <?php
                $peligrosLabels = [
                    'peligro_biologico' => 'Biológico', 'peligro_quimico' => 'Químico',
                    'peligro_biomecanico' => 'Biomecánico', 'peligro_fisico' => 'Físico',
                    'peligro_psicosocial' => 'Psicosocial', 'peligro_fenomenos' => 'Fenómenos Naturales',
                ];
                foreach (array_chunk($peligrosLabels, 3, true) as $par): ?>
                <tr><?php foreach ($par as $k => $lbl): ?><td><?= e($lbl) ?>: <b><?= e($d[$k]??'') ?></b></td><?php endforeach; ?></tr>
                <?php endforeach; ?>
                <tr><td colspan="3">Condiciones de Seguridad: <b><?= e($d['peligro_condiciones_seg']??'') ?></b></td></tr>
            </table>
            <table><tr><td class="sec" colspan="3">3. VERIFICACIÓN DOCUMENTAL</td></tr>
                <tr><th class="sub-th">Requisito</th><th class="sub-th" style="width:15%">Estado</th><th class="sub-th">Observación</th></tr>
                <?php
                $verifDocLabels = [
                    'vd_permiso_trabajo' => 'Permiso de trabajo aprobado',
                    'vd_ats'              => 'ATS socializado',
                    'vd_personal'         => 'Personal autorizado/certificado',
                    'vd_plan_emergencia'  => 'Plan de emergencia disponible',
                    'vd_procedimiento'    => 'Procedimiento de trabajo disponible',
                ];
                foreach ($verifDocLabels as $k => $lbl): ?>
                <tr><td><?= e($lbl) ?></td><td style="text-align:center"><?= e($d[$k]??'') ?></td><td><?= e($d[$k.'_obs']??'') ?></td></tr>
                <?php endforeach; ?>
            </table>
            <table><tr><td class="sec" colspan="3">4. INSPECCIÓN DE CONTROLES CRÍTICOS</td></tr>
                <tr><th class="sub-th">Control</th><th class="sub-th" style="width:15%">Estado</th><th class="sub-th">Observación</th></tr>
                <?php
                $ccLabels = [
                    'cc_delimitacion' => 'Delimitación y señalización del área',
                    'cc_epp'          => 'Uso correcto de EPP',
                    'cc_herramientas' => 'Herramientas/equipos en buen estado',
                    'cc_supervision'  => 'Supervisión designada',
                    'cc_rescate'      => 'Plan de rescate/emergencia',
                    'cc_orden_aseo'   => 'Orden y aseo del área',
                ];
                foreach ($ccLabels as $k => $lbl): ?>
                <tr><td><?= e($lbl) ?></td><td style="text-align:center"><?= e($d[$k]??'') ?></td><td><?= e($d[$k.'_obs']??'') ?></td></tr>
                <?php endforeach; ?>
            </table>
            <table><tr><td class="sec" colspan="6">5. TRABAJADORES AUTORIZADOS</td></tr>
                <tr><th class="sub-th">Nombre</th><th class="sub-th">Tipo Doc</th><th class="sub-th">N° Doc</th><th class="sub-th">Cargo</th><th class="sub-th">Día</th><th class="sub-th">Firma</th></tr>
                <?php $tnoms = $d['t_nombre'] ?? []; for ($i=0; $i<count($tnoms); $i++): ?>
                <tr>
                    <td><?= e($d['t_nombre'][$i]??'') ?></td><td><?= e($d['t_tipo_doc'][$i]??'') ?></td>
                    <td><?= e($d['t_documento'][$i]??'') ?></td><td><?= e($d['t_cargo'][$i]??'') ?></td>
                    <td><?= e($d['t_dia'][$i]??'') ?></td>
                    <td><?php if (!empty($d['t_firma'][$i])): ?><img src="<?= e($d['t_firma'][$i]) ?>" class="firma-img"><?php endif; ?></td>
                </tr>
                <?php endfor; ?>
            </table>
            <table><tr><td class="sec" colspan="3">6. FIRMAS</td></tr>
                <tr><th class="sub-th">Rol</th><th class="sub-th">Nombre</th><th class="sub-th">Firma</th></tr>
                <?php
                $f_ins = [
                    ['Inspector',     $d['firma_inspector_nombre']??'',       $d['firma_inspector_firma']??''],
                    ['Supervisor',    $d['firma_supervisor_nombre']??'',      $d['firma_supervisor_firma']??''],
                    ['Resp. SST',     $d['firma_resp_sst_nombre']??'',        $d['firma_resp_sst_firma']??''],
                    ['Resp. Activ.',  $d['firma_resp_actividad_nombre']??'',  $d['firma_resp_actividad_firma']??''],
                ];
                foreach ($f_ins as [$rol, $nom, $firma]):
                ?>
                <tr>
                    <td><?= e($rol) ?></td><td><?= e($nom) ?></td>
                    <td><?php if ($firma): ?><img src="<?= e($firma) ?>" class="firma-img"><?php endif; ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <?php elseif ($p3): ?>
        <div class="no-data">
            ℹ️ Inspección registrada antes de vincular ID al flujo.
            <a href="../inspeccion_trabajo/rev_inspeccion_trabajo.php" target="_blank">Ver registros Inspección →</a>
        </div>
        <?php else: ?>
        <div class="no-data">⚠️ Este paso aún no ha sido completado.</div>
        <?php endif; ?>
        </div>
    </div>

    <!-- ══ CERTIFICADOS DE APOYO ══ -->
    <?php if (!empty($apoyos)):
        $apoyoRevLinks = [
            'alturas'     => '../certificado_apoyo/trabajo_alturas/rev_trabajo_alturas.php',
            'confinados'  => '../certificado_apoyo/espacios_confinados/rev_espacios_confinados.php',
            'caliente'    => '../certificado_apoyo/trabajo_caliente/rev_trabajo_caliente.php',
            'electrico'   => '../certificado_apoyo/riesgo_electrico/rev_riesgo_electrico.php',
            'energizadas' => '../certificado_apoyo/lineas_energizadas/rev_lineas_energizadas.php',
            'izaje'       => '../certificado_apoyo/izaje_cargas/rev_izaje_cargas.php',
        ];
        foreach ($apoyos as $idx => $ap):
            $inf = $apoyoLabels[$ap['key']] ?? ['icon' => '📋', 'label' => $ap['key']];
            $regAp = empty($ap['id_registro']) ? null : obtenerRegistroDelPaso($flujo, $ap['key'], $sede_san);
    ?>
    <div class="doc-section fmt-<?= $ap['key'] ?>">
        <div class="doc-header" onclick="toggleDoc(this)">
            <div class="doc-header-left">
                <span class="doc-icon"><?= e($formatStyles[$ap['key']]['icon'] ?? $inf['icon']) ?></span>
                <span class="doc-num"><?= e($formatStyles[$ap['key']]['code'] ?? 'APOYO') ?></span>
                <span class="doc-name"><?= e($inf['label']) ?></span>
                <span class="doc-ts">✓ <?= fmtTs($ap['timestamp'] ?? '') ?> · <?= e($ap['usuario'] ?? '') ?></span>
            </div>
            <span class="doc-toggle">▼</span>
        </div>
        <div class="doc-body">
        <?php if ($regAp): ?>
        <div class="doc-print">
            <table>
                <tr>
                    <td style="width:18%"><img src="/img/logo_empresa.jpeg" alt="Logo" style="max-height:55px;"></td>
                    <td style="text-align:center;vertical-align:middle;">
                        <div style="font-size:9pt;font-weight:bold;">Sistema de Gestión de Seguridad y Salud en el Trabajo</div>
                        <div class="header-doc">Certificado de Apoyo — <?= e($inf['label']) ?></div>
                        <span class="header-doc-tag"><?= e($formatStyles[$ap['key']]['icon'] ?? '') ?> <?= e($formatStyles[$ap['key']]['code'] ?? '') ?></span>
                    </td>
                    <td class="sub-meta"><?= e($regAp['timestamp']??'') ?><br>Folio: <?= e($flujo['folio']??'') ?></td>
                </tr>
            </table>
            <?php
            $d = $regAp['datos'] ?? [];
            $controlesAp = $d['controles'] ?? [];
            $controlesLabelsAp = $apoyoControlesLabels[$ap['key']] ?? [];
            ?>
            <table><tr><td class="sec" colspan="4">GENERALIDADES</td></tr>
                <tr><td class="lbl">Fecha</td><td><?= e($d['fecha']??'') ?></td><td class="lbl">Hora</td><td><?= e($d['hora']??'') ?></td></tr>
                <tr><td class="lbl">Valoración Riesgo</td><td><?= e($d['valoracion_riesgo']??'') ?></td><td class="lbl">Frecuencia</td><td><?= e($d['frecuencia']??'') ?></td></tr>
                <tr><td class="lbl">Zona de Trabajo</td><td><?= e($d['zona_trabajo']??'') ?></td><td class="lbl">Dependencia</td><td><?= e($d['dependencia']??'') ?></td></tr>
                <?php if ($ap['key'] === 'alturas'): ?>
                <tr><td class="lbl">Equipo / Sistema</td><td><?= e($d['equipo_sistema']??'') ?></td><td class="lbl">Altura Máxima</td><td><?= e($d['altura_maxima']??'') ?> m</td></tr>
                <?php else: ?>
                <tr><td class="lbl">Equipo / Sistema</td><td colspan="3"><?= e($d['equipo_sistema']??'') ?></td></tr>
                <?php endif; ?>
                <tr><td class="lbl" colspan="4" style="text-align:center">Descripción de la Actividad</td></tr>
                <tr><td colspan="4"><?= ef($d['descripcion_actividad']??'') ?></td></tr>
            </table>
            <?php if ($ap['key'] === 'alturas'): $rc = $d['rc'] ?? []; ?>
            <table><tr><td class="sec" colspan="4">REQUERIMIENTO DE CLARIDAD (RC)</td></tr>
                <tr>
                    <td class="lbl">Altura Colaborador</td><td><?= isset($rc['altura_colaborador']) ? e(number_format($rc['altura_colaborador'], 2, ',', '.')) . ' m' : '—' ?></td>
                    <td class="lbl">Longitud Eslinga</td><td><?= isset($rc['longitud_eslinga']) ? e(number_format($rc['longitud_eslinga'], 2, ',', '.')) . ' m' : '—' ?></td>
                </tr>
                <tr>
                    <td class="lbl">Absorbedor Impacto</td><td><?= isset($rc['absorbedor_impacto']) ? e(number_format($rc['absorbedor_impacto'], 2, ',', '.')) . ' m' : '—' ?></td>
                    <td class="lbl">Total RC</td><td><b><?= isset($rc['total']) ? e(number_format($rc['total'], 2, ',', '.')) . ' m' : '—' ?></b></td>
                </tr>
            </table>
            <?php endif; ?>
            <table><tr><td class="sec" colspan="4">¿ACTIVIDADES CRÍTICAS ADICIONALES?</td></tr>
                <tr><td class="lbl">¿Incluye actividades críticas?</td><td><?= e($d['tiene_actividades_criticas']??'') ?></td>
                    <td class="lbl">Actividad(es)</td><td><?= e($d['actividades_criticas']??'') ?></td></tr>
            </table>
            <table><tr><td class="sec" colspan="3">PLANEACIÓN Y CONTROLES</td></tr>
                <tr><th class="sub-th">Ítem de Control</th><th class="sub-th" style="width:15%">Resultado</th><th class="sub-th">Observación</th></tr>
                <?php foreach ($controlesLabelsAp as $k => $lbl):
                    $ctrl = $controlesAp[$k] ?? [];
                ?>
                <tr>
                    <td><?= e($lbl) ?></td>
                    <td style="text-align:center"><?= e($ctrl['resultado']??'') ?></td>
                    <td><?= e($ctrl['observacion']??'') ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <?php else: ?>
        <div class="no-data">
            ℹ️ Certificado vinculado al flujo.
            <?php if (!empty($apoyoRevLinks[$ap['key']])): ?>
            <a href="<?= e($apoyoRevLinks[$ap['key']]) ?>" target="_blank">Ver registros de <?= e($inf['label']) ?> →</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        </div>
    </div>
    <?php endforeach; endif; ?>

    <!-- ══ PDF ADJUNTO (renderizado como imágenes por página) ══ -->
    <div id="pdf-doc-container"></div>

</div><!-- /print-wrap -->

<script>
function toggleDoc(header) {
    const body   = header.nextElementSibling;
    const toggle = header.querySelector('.doc-toggle');
    const open   = body.classList.toggle('open');
    toggle.style.transform = open ? 'rotate(180deg)' : '';
}

let allOpen = false;
function toggleAll() {
    allOpen = !allOpen;
    document.querySelectorAll('.doc-body').forEach(b  => b.classList.toggle('open', allOpen));
    document.querySelectorAll('.doc-toggle').forEach(t => t.style.transform = allOpen ? 'rotate(180deg)' : '');
    document.querySelector('.btn-nav[onclick="toggleAll()"]').textContent = allOpen ? '☰ Colapsar Todo' : '☰ Expandir Todo';
}
</script>

<!-- ── RENDERIZADO DEL PDF ADJUNTO COMO IMÁGENES (para que quede incluido al imprimir/exportar el expediente) ── -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
pdfjsLib.GlobalWorkerOptions.workerSrc = "https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js";

const DOCUMENTO_PDF   = <?= json_encode($documentoPdf) ?>;
const MAX_PAGINAS_PDF = 20;
const ESCALA_RENDER   = 1.5;

function escHtml(s) {
    return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function habilitarImprimir() {
    const btn = document.getElementById('btn-print');
    btn.disabled = false;
    btn.textContent = '🖨 IMPRIMIR EXPEDIENTE';
}

async function renderPdfDoc() {
    if (!DOCUMENTO_PDF) { habilitarImprimir(); return; }

    const cont = document.getElementById('pdf-doc-container');
    const section = document.createElement('div');
    section.className = 'doc-section fmt-anexo';
    section.innerHTML = `
        <div class="doc-header" onclick="toggleDoc(this)">
            <div class="doc-header-left">
                <span class="doc-icon">📄</span>
                <span class="doc-num">ANEXO</span>
                <span class="doc-name">${escHtml(DOCUMENTO_PDF.nombre_original)}</span>
                <span class="doc-ts">✓ ${escHtml(DOCUMENTO_PDF.timestamp)} · ${escHtml(DOCUMENTO_PDF.usuario)}</span>
            </div>
            <span class="doc-toggle">▼</span>
        </div>
        <div class="doc-body open"></div>`;
    const body = section.querySelector('.doc-body');
    cont.appendChild(section);

    try {
        const resp = await fetch(DOCUMENTO_PDF.ruta);
        if (!resp.ok) throw new Error('HTTP ' + resp.status);
        const buffer = await resp.arrayBuffer();
        const pdf    = await pdfjsLib.getDocument({ data: buffer }).promise;

        const totalPaginas = Math.min(pdf.numPages, MAX_PAGINAS_PDF);
        for (let i = 1; i <= totalPaginas; i++) {
            const page     = await pdf.getPage(i);
            const viewport = page.getViewport({ scale: ESCALA_RENDER });
            const canvas   = document.createElement('canvas');
            canvas.width   = viewport.width;
            canvas.height  = viewport.height;
            const ctx      = canvas.getContext('2d');

            await page.render({ canvasContext: ctx, viewport }).promise;

            const img = document.createElement('img');
            img.src = canvas.toDataURL('image/jpeg', 0.85);
            img.className = 'pdf-page-img';
            body.appendChild(img);

            canvas.width = 0;
            canvas.height = 0;
        }

        if (pdf.numPages > MAX_PAGINAS_PDF) {
            const warn = document.createElement('div');
            warn.className = 'no-data';
            warn.textContent = `⚠️ El documento tiene ${pdf.numPages} páginas; solo se muestran las primeras ${MAX_PAGINAS_PDF}.`;
            body.appendChild(warn);
        }
    } catch (err) {
        body.innerHTML = `<div class="no-data">⚠️ No se pudo renderizar el PDF adjunto: ${escHtml(err.message)}</div>`;
    }

    habilitarImprimir();
}

renderPdfDoc();
</script>
</body>
</html>
