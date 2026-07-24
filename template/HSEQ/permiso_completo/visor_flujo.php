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

        /* ── SECCIÓN DE DOCUMENTO ── */
        .doc-section { margin-bottom: 30px; page-break-inside: avoid; }

        .doc-header {
            background: #151A22; border: 1px solid #1E293B; border-left: 4px solid #00F0FF;
            padding: 14px 20px; border-radius: 8px; display: flex;
            align-items: center; justify-content: space-between; margin-bottom: 0;
            cursor: pointer;
        }
        .doc-header.ats { border-left-color: #8B5CF6; }
        .doc-header.ins { border-left-color: #10B981; }
        .doc-header.apoyo { border-left-color: #FFB000; }

        .doc-header-left { display: flex; align-items: center; gap: 14px; }
        .doc-num { font-family: 'Space Mono', monospace; font-size: 9px; color: #94A3B8; background: #0F172A; border: 1px solid #1E293B; padding: 2px 8px; border-radius: 4px; }
        .doc-name { font-size: 15px; font-weight: 700; color: #fff; }
        .doc-ts { font-family: 'Space Mono', monospace; font-size: 9px; color: #94A3B8; }
        .doc-toggle { font-size: 18px; color: #94A3B8; transition: transform 0.2s; }
        .doc-body { display: none; }
        .doc-body.open { display: block; }

        /* ── TABLA DOCUMENTO (estilo físico imprimible) ── */
        .doc-print {
            background: #fff; color: #000; padding: 24px; border-radius: 0 0 8px 8px;
            font-family: 'Barlow', sans-serif; font-size: 8.5pt;
            border: 1px solid #1E293B; border-top: none;
        }
        .doc-print table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .doc-print td, .doc-print th { border: 1px solid #000; padding: 3px 5px; vertical-align: top; }
        .doc-print .lbl { font-weight: bold; background: #f1f5f9; width: 22%; font-size: 8pt; }
        .doc-print .sec { background: #1e3a8a; color: #fff; font-weight: 700; text-align: center; text-transform: uppercase; font-size: 8.5pt; padding: 4px; }
        .doc-print .sub-th { background: #e2e8f0; font-size: 7.5pt; font-weight: 700; text-align: left; }
        .doc-print .header-doc { text-align: center; font-size: 12pt; font-weight: bold; padding: 6px; }
        .doc-print .sub-meta { font-size: 7pt; text-align: left; vertical-align: top; }
        .firma-img { max-height: 45px; max-width: 120px; }

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
            body { background: #fff; color: #000; }
            .action-bar { display: none !important; }
            .portada { background: #fff; border: 2px solid #000; border-left: 5px solid #000; color: #000; page-break-after: always; }
            .folio-tag { color: #000; }
            .info-label, .info-val, .bar-sub, .tl, .doc-ts, .doc-num, .doc-name, .track-title { color: #000; }
            .td-done { background: #000; color: #fff; box-shadow: none; }
            .td-pend { background: #ccc; color: #555; border-color: #aaa; }
            .tc.done { background: #000; }
            .doc-header { background: #f1f5f9; border-left: 4px solid #000; color: #000; }
            .doc-body { display: block !important; }
            .doc-toggle { display: none; }
            .apoyo-chip { border: 1px solid #000; color: #000; background: transparent; }
            .doc-section { page-break-inside: avoid; page-break-before: auto; }
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
    </div>

    <!-- ══ PASO 01: PERMISO DE TRABAJO ══ -->
    <div class="doc-section">
        <div class="doc-header" onclick="toggleDoc(this)">
            <div class="doc-header-left">
                <span class="doc-num">PASO 01</span>
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
            <table>
                <tr><td class="lbl" style="width:28%">Vigía Designado</td><td><?= e($d['vigia_nombre']??'') ?> | <?= e($d['vigia_doc']??'') ?></td></tr>
                <tr><td class="lbl">Resp. Plan Emergencia</td><td><?= e($d['emergencia_nombre']??'') ?> | <?= e($d['emergencia_doc']??'') ?></td></tr>
            </table>
            <table><tr><td class="sec" colspan="4">3. EQUIPOS Y SISTEMAS</td></tr>
                <tr><td>Arnés: <b><?= e($d['eq_arnes']??'') ?></b></td><td>L.V. Vertical: <b><?= e($d['eq_lv_vertical']??'') ?></b></td><td>Eslinga Pos.: <b><?= e($d['eq_eslinga_pos']??'') ?></b></td><td>L.V. Horizontal: <b><?= e($d['eq_lv_horizontal']??'') ?></b></td></tr>
                <tr><td>Eslinga Abs.: <b><?= e($d['eq_eslinga_abs']??'') ?></b></td><td>Anclaje Fijo: <b><?= e($d['eq_anclaje_fijo']??'') ?></b></td><td>Freno: <b><?= e($d['eq_freno']??'') ?></b></td><td>Anclaje Móvil: <b><?= e($d['eq_anclaje_movil']??'') ?></b></td></tr>
                <tr><td colspan="2">Sistema: <b><?= e($d['sistema_tipo']??'') ?></b></td><td colspan="2">Acceso: <b><?= e($d['sistema_acceso']??'') ?></b></td></tr>
            </table>
            <table><tr><td class="sec" colspan="4">4. OBSERVACIONES Y AUTORIZACIÓN</td></tr>
                <tr><td style="width:50%" colspan="2"><b>Observaciones:</b><br><?= ef($d['observaciones']??'') ?></td>
                    <td colspan="2">¿Aprueba?: <b><?= e($d['aprueba']??'') ?></b><br><?= e($d['motivo_negacion']??'') ?></td></tr>
            </table>
            <table><tr><th class="sub-th">Rol</th><th class="sub-th">Nombre</th><th class="sub-th">Cédula</th><th class="sub-th">Firma</th></tr>
                <?php for ($i=0; $i<=3; $i++):
                    $roles = ['Jefe Encargado','Jefe Suplente','Coord. Trabajo Seguro','Supervisor SST'];
                ?>
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
    <div class="doc-section">
        <div class="doc-header ats" onclick="toggleDoc(this)">
            <div class="doc-header-left">
                <span class="doc-num">PASO 02</span>
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
                    </td>
                    <td class="sub-meta">Código: FOR-HSEQ-006<br><?= e($regAts['timestamp']??'') ?></td>
                </tr>
            </table>
            <table><tr><td class="sec" colspan="4">1. GENERALIDADES</td></tr>
                <tr><td class="lbl">Tipo Trabajo</td><td><?= e($d['tipo_trabajo']??'') ?></td><td class="lbl">Zona</td><td><?= e($d['zona_trabajo']??'') ?></td></tr>
                <tr><td class="lbl">Fecha Inicio</td><td><?= e($d['fecha_inicio']??'') ?></td><td class="lbl">Fecha Fin</td><td><?= e($d['fecha_fin']??'') ?></td></tr>
                <tr><td class="lbl">Hora Inicio</td><td><?= e($d['hora_inicio']??'') ?></td><td class="lbl">Hora Fin</td><td><?= e($d['hora_fin']??'') ?></td></tr>
                <tr><td class="lbl">Valoración Riesgo</td><td><?= e($d['valoracion_riesgo']??'') ?></td><td class="lbl">Altura máx.</td><td><?= e($d['altura_maxima']??'') ?> m</td></tr>
                <tr><td class="lbl" colspan="4" style="text-align:center">Descripción de la Actividad</td></tr>
                <tr><td colspan="4"><?= ef($d['descripcion_actividad']??'') ?></td></tr>
            </table>
            <table><tr><td class="sec" colspan="5">2. TRABAJADORES</td></tr>
                <tr><th class="sub-th">Nombre</th><th class="sub-th">Tipo Doc</th><th class="sub-th">N° Doc</th><th class="sub-th">Cargo</th><th class="sub-th">Día</th></tr>
                <?php for ($i=1; $i<=3; $i++): if (!empty($d["trab_nombre_$i"])): ?>
                <tr>
                    <td><?= e($d["trab_nombre_$i"]??'') ?></td><td><?= e($d["trab_tipo_doc_$i"]??'') ?></td>
                    <td><?= e($d["trab_doc_$i"]??'') ?></td><td><?= e($d["trab_cargo_$i"]??'') ?></td>
                    <td><?= e($d["trab_dia_$i"]??'') ?></td>
                </tr>
                <?php endif; endfor; ?>
            </table>
            <table><tr><td class="sec" colspan="4">3. CONTROLES Y EPP</td></tr>
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
            </table>
            <table><tr><td class="sec" colspan="4">4. FIRMAS DE AUTORIZACIÓN</td></tr>
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
    <div class="doc-section">
        <div class="doc-header ins" onclick="toggleDoc(this)">
            <div class="doc-header-left">
                <span class="doc-num">PASO 03</span>
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
                    </td>
                    <td class="sub-meta">Código: FOR-HSEQ-007<br><?= e($regIns['timestamp']??'') ?></td>
                </tr>
            </table>
            <div style="margin-bottom:6px">
                <?php
                    $campos_ins = ['empresa','responsable','tipo_trabajo','fecha_inspeccion','area','ciudad'];
                    $labels_ins = ['Empresa','Responsable','Tipo Trabajo','Fecha Inspección','Área','Ciudad'];
                ?>
                <table>
                    <?php foreach (array_chunk(array_combine($labels_ins, $campos_ins), 2) as $par): ?>
                    <tr>
                        <?php foreach ($par as $lbl => $campo): ?>
                        <td class="lbl" style="width:14%"><?= e($lbl) ?></td><td style="width:36%"><?= e($d[$campo]??'') ?></td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
            <table><tr><td class="sec" colspan="4">FIRMAS</td></tr>
                <tr><th class="sub-th">Rol</th><th class="sub-th">Nombre</th><th class="sub-th">Cédula</th><th class="sub-th">Firma</th></tr>
                <?php
                $f_ins = [
                    ['Inspector',     $d['firma_inspector_nombre']??'',       $d['firma_inspector_doc']??'',       $d['firma_inspector_firma']??''],
                    ['Supervisor',    $d['firma_supervisor_nombre']??'',      $d['firma_supervisor_doc']??'',      $d['firma_supervisor_firma']??''],
                    ['Resp. SST',     $d['firma_resp_sst_nombre']??'',        $d['firma_resp_sst_doc']??'',        $d['firma_resp_sst_firma']??''],
                    ['Resp. Activ.',  $d['firma_resp_actividad_nombre']??'',  $d['firma_resp_actividad_doc']??'',  $d['firma_resp_actividad_firma']??''],
                ];
                foreach ($f_ins as [$rol, $nom, $ced, $firma]):
                ?>
                <tr>
                    <td><?= e($rol) ?></td><td><?= e($nom) ?></td><td><?= e($ced) ?></td>
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
    <div class="doc-section">
        <div class="doc-header apoyo" onclick="toggleDoc(this)">
            <div class="doc-header-left">
                <span class="doc-num">APOYO</span>
                <span class="doc-name"><?= e($inf['icon'] . ' ' . $inf['label']) ?></span>
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
                    </td>
                    <td class="sub-meta"><?= e($regAp['timestamp']??'') ?><br>Folio: <?= e($flujo['folio']??'') ?></td>
                </tr>
            </table>
            <?php $d = $regAp['datos'] ?? []; ?>
            <table><tr><td class="sec" colspan="4">DATOS DEL CERTIFICADO</td></tr>
                <?php foreach (array_slice($d, 0, 16) as $k => $v):
                    if (is_array($v) || str_starts_with($k, 'firma') || str_ends_with($k, '_firma') || str_ends_with($k, 'base64')) continue;
                ?>
                <tr>
                    <td class="lbl" style="width:22%"><?= e(ucwords(str_replace('_', ' ', $k))) ?></td>
                    <td colspan="3"><?= ef(is_array($v) ? implode(', ', $v) : $v) ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php
            $firmaKeys = array_filter(array_keys($d), fn($k) => str_contains($k, 'firma') && !empty($d[$k]) && is_string($d[$k]) && str_starts_with($d[$k], 'data:'));
            if (!empty($firmaKeys)):
            ?>
            <table><tr><td class="sec" colspan="<?= count($firmaKeys) ?>">FIRMAS</td></tr><tr>
                <?php foreach ($firmaKeys as $fk): ?>
                <td style="text-align:center;padding:8px;">
                    <div style="font-size:7pt;margin-bottom:4px"><?= e(ucwords(str_replace('_', ' ', $fk))) ?></div>
                    <img src="<?= e($d[$fk]) ?>" class="firma-img">
                </td>
                <?php endforeach; ?>
            </tr></table>
            <?php endif; ?>
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
    section.className = 'doc-section';
    section.innerHTML = `
        <div class="doc-header apoyo" onclick="toggleDoc(this)">
            <div class="doc-header-left">
                <span class="doc-num">ANEXO</span>
                <span class="doc-name">📄 ${escHtml(DOCUMENTO_PDF.nombre_original)}</span>
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
