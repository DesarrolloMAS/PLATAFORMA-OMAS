<?php
require '../sesion.php';
require_once __DIR__ . '/../flujo_helpers.php';

if (!isset($_SESSION['nombre']) || empty($_SESSION['sede'])) {
    header('Location: ../../index.php');
    exit;
}

$sede = $_SESSION['sede'];
$sede_saneada = preg_replace('/[^A-Za-z0-9_-]/', '', $sede);

$filename = isset($_GET['file']) ? basename($_GET['file']) : '';
$id_registro = isset($_GET['id']) ? $_GET['id'] : '';

$filepath = "../../archivos/generados/inspeccion_trabajo/" . $sede_saneada . "/" . $filename;

$registros = [];
if ($filename && file_exists($filepath)) {
    $registros = json_decode(file_get_contents($filepath), true) ?: [];
}

$registro = null;
if ($id_registro) {
    foreach ($registros as $r) {
        if ($r['id_registro'] === $id_registro) { $registro = $r; break; }
    }
} elseif (!empty($registros)) {
    $registro = end($registros);
}

$d = $registro['datos'] ?? [];

$notas = [];
if (!empty($registro['id_flujo'])) {
    $flujoRef = obtenerFlujoPorId($sede_saneada, $registro['id_flujo']);
    if ($flujoRef) $notas = $flujoRef['notas'] ?? [];
}

function val($arr, $key, $default = '—') {
    return htmlspecialchars($arr[$key] ?? $default);
}
function badge($valor) {
    $color = match($valor) {
        'Cumple'    => '#10B981',
        'No Cumple' => '#FF3366',
        'N/A'       => '#94A3B8',
        'Alto'      => '#FF3366',
        'Medio'     => '#FFB000',
        'Bajo'      => '#10B981',
        default     => '#94A3B8',
    };
    return '<span style="background:' . $color . '22; color:' . $color . '; border:1px solid ' . $color . '44; padding:2px 8px; border-radius:3px; font-size:11px; font-family:\'Space Mono\',monospace; white-space:nowrap;">' . htmlspecialchars($valor ?: '—') . '</span>';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visor — Inspección de Trabajo</title>
    <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700&family=Space+Mono:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
        :root {
            --bg-color: #0B0E14; --panel-bg: #151A22; --accent: #00F0FF; --accent-glow: rgba(0, 240, 255, 0.4);
            --text-main: #E2E8F0; --text-muted: #94A3B8; --border-color: #1E293B; --input-bg: #0F172A;
            --danger: #FF3366; --warning: #FFB000; --success: #10B981;
            --r-md: 8px; --r-sm: 4px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Barlow', sans-serif; background-color: var(--bg-color); color: var(--text-main);
            min-height: 100vh; padding: 40px 20px;
            background-image: linear-gradient(rgba(0, 240, 255, 0.03) 1px, transparent 1px),
                              linear-gradient(90deg, rgba(0, 240, 255, 0.03) 1px, transparent 1px);
            background-size: 30px 30px;
        }
        .container { max-width: 960px; margin: 0 auto; }
        .action-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; gap: 12px; flex-wrap: wrap; }
        .btn-back {
            background: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-main);
            padding: 10px 20px; border-radius: var(--r-sm); font-family: 'Space Mono', monospace;
            text-decoration: none; font-size: 13px; transition: all 0.3s;
        }
        .btn-back:hover { border-color: var(--accent); color: var(--accent); }
        .btn-pdf {
            background: var(--accent); color: var(--bg-color); border: none; padding: 10px 20px; border-radius: var(--r-sm);
            font-family: 'Space Mono', monospace; font-size: 13px; font-weight: 700; cursor: pointer;
            box-shadow: 0 0 15px var(--accent-glow); transition: all 0.3s; text-transform: uppercase;
        }
        .btn-pdf:hover { background: #fff; box-shadow: 0 0 25px rgba(255,255,255,0.5); }

        /* DOCUMENTO */
        #documento {
            background: var(--panel-bg); border: 1px solid var(--border-color); border-radius: var(--r-md);
            padding: 40px; box-shadow: 0 10px 40px rgba(0,0,0,0.5);
        }

        .doc-header {
            display: flex; justify-content: space-between; align-items: flex-start;
            border-bottom: 2px solid var(--accent); padding-bottom: 20px; margin-bottom: 30px;
        }
        .doc-title { font-size: 22px; font-weight: 700; color: #fff; text-transform: uppercase; letter-spacing: 1px; }
        .doc-subtitle { color: var(--text-muted); font-size: 13px; margin-top: 4px; }
        .doc-meta { text-align: right; font-family: 'Space Mono', monospace; font-size: 11px; color: var(--text-muted); line-height: 1.8; }
        .doc-meta strong { color: var(--accent); }

        .section { margin-bottom: 28px; }
        .section-header {
            background: rgba(0, 240, 255, 0.06); border-left: 3px solid var(--accent);
            padding: 8px 14px; font-family: 'Space Mono', monospace; font-size: 12px;
            color: var(--accent); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 14px;
        }

        .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; }
        .info-item { display: flex; flex-direction: column; gap: 3px; }
        .info-label { font-size: 11px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.3px; }
        .info-value { font-size: 14px; color: var(--text-main); font-weight: 500; }

        .verif-doc-table { width: 100%; border-collapse: collapse; }
        .verif-doc-table th {
            padding: 9px 12px; font-size: 11px; font-family: 'Space Mono', monospace;
            color: var(--text-muted); text-transform: uppercase; border-bottom: 1px solid var(--border-color);
            background: rgba(0,240,255,0.03); text-align: left;
        }
        .verif-doc-table td { padding: 9px 12px; font-size: 13px; border-bottom: 1px solid rgba(30,41,59,0.5); vertical-align: middle; }
        .verif-doc-table tbody tr:hover { background: rgba(0,240,255,0.02); }

        .trabajadores-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .trabajadores-table th {
            padding: 9px 10px; font-size: 11px; font-family: 'Space Mono', monospace; color: var(--text-muted);
            text-transform: uppercase; border-bottom: 1px solid var(--border-color); background: rgba(0,240,255,0.03); text-align: left;
        }
        .trabajadores-table td { padding: 8px 10px; border-bottom: 1px solid rgba(30,41,59,0.5); }
        .trabajadores-table tbody tr:hover { background: rgba(0,240,255,0.02); }

        .firmas-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; }
        .firma-box {
            border: 1px solid var(--border-color); border-radius: var(--r-sm); padding: 16px;
            background: rgba(0, 240, 255, 0.02);
        }
        .firma-title { font-family: 'Space Mono', monospace; font-size: 11px; color: var(--accent); text-transform: uppercase; margin-bottom: 10px; border-bottom: 1px dashed var(--border-color); padding-bottom: 6px; }
        .firma-line { display: flex; flex-direction: column; gap: 3px; margin-bottom: 8px; }
        .firma-label { font-size: 11px; color: var(--text-muted); }
        .firma-value { font-size: 14px; color: var(--text-main); }

        .empty-doc { text-align: center; padding: 80px 20px; color: var(--text-muted); }
        .empty-doc h2 { color: #fff; margin-bottom: 10px; }

        .reg-nav { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin-bottom: 20px; }
        .reg-link {
            background: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-muted);
            padding: 7px 14px; border-radius: var(--r-sm); font-family: 'Space Mono', monospace; font-size: 11px;
            text-decoration: none; transition: all 0.2s;
        }
        .reg-link.active, .reg-link:hover { border-color: var(--accent); color: var(--accent); background: rgba(0,240,255,0.05); }

        @media print {
            body { background: #fff; padding: 0; }
            .action-bar, .reg-nav { display: none; }
            #documento { background: #fff; color: #000; border: none; padding: 20px; box-shadow: none; }
            .section-header { background: #e0e0e0; color: #000; }
            .info-value, .firma-value { color: #000; }
            .doc-title { color: #000; }
        }
    </style>
</head>
<body>
<div class="container">

    <div class="action-bar">
        <a href="rev_inspeccion_trabajo.php" class="btn-back">← Volver a Galería</a>
        <button class="btn-pdf" onclick="exportarPDF()">⬇ EXPORTAR PDF</button>
    </div>

    <?php if (!empty($registros)): ?>
    <div class="reg-nav">
        <span style="font-family:'Space Mono',monospace; font-size:11px; color:var(--text-muted);">REGISTROS:</span>
        <?php foreach ($registros as $r): ?>
            <a href="?file=<?= urlencode($filename) ?>&id=<?= urlencode($r['id_registro']) ?>"
               class="reg-link <?= ($r['id_registro'] === ($registro['id_registro'] ?? '')) ? 'active' : '' ?>">
                <?= htmlspecialchars($r['datos']['fecha'] ?? $r['timestamp']) ?>
            </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div id="documento">
        <?php if (!$registro): ?>
            <div class="empty-doc">
                <h2>Sin registros</h2>
                <p>No se encontró el registro solicitado.</p>
            </div>
        <?php else: ?>

        <!-- ENCABEZADO DEL DOCUMENTO -->
        <div class="doc-header">
            <div>
                <div class="doc-title">Inspección de Trabajo — HSEQ</div>
                <div class="doc-subtitle">Formato de Inspección y Control / Seguridad y Salud en el Trabajo</div>
            </div>
            <div class="doc-meta">
                <div><strong>ID:</strong> <?= htmlspecialchars($registro['id_registro']) ?></div>
                <div><strong>Generado:</strong> <?= htmlspecialchars($registro['timestamp']) ?></div>
                <div><strong>Sede:</strong> <?= htmlspecialchars($registro['sede_sys']) ?></div>
                <div><strong>Usuario:</strong> <?= htmlspecialchars($registro['usuario_sys']) ?></div>
            </div>
        </div>

        <!-- GENERALIDADES -->
        <div class="section">
            <div class="section-header">01 — Generalidades</div>
            <div class="info-grid">
                <div class="info-item"><div class="info-label">Fecha</div><div class="info-value"><?= val($d, 'fecha') ?></div></div>
                <div class="info-item"><div class="info-label">Hora</div><div class="info-value"><?= val($d, 'hora') ?></div></div>
                <div class="info-item"><div class="info-label">Empresa</div><div class="info-value"><?= val($d, 'empresa') ?></div></div>
                <div class="info-item"><div class="info-label">Sede</div><div class="info-value"><?= val($d, 'sede') ?></div></div>
                <div class="info-item"><div class="info-label">Área</div><div class="info-value"><?= val($d, 'area') ?></div></div>
                <div class="info-item"><div class="info-label">Lugar</div><div class="info-value"><?= val($d, 'lugar') ?></div></div>
                <div class="info-item"><div class="info-label">Inspector Responsable</div><div class="info-value"><?= val($d, 'inspector_responsable') ?></div></div>
                <div class="info-item"><div class="info-label">Cargo</div><div class="info-value"><?= val($d, 'cargo') ?></div></div>
                <div class="info-item"><div class="info-label">Tipo de Actividad</div><div class="info-value"><?= val($d, 'tipo_actividad') ?></div></div>
                <div class="info-item"><div class="info-label">Actividad a Realizar</div><div class="info-value"><?= val($d, 'actividad_realizar') ?></div></div>
            </div>
        </div>

        <!-- PELIGROS -->
        <div class="section">
            <div class="section-header">02 — Peligros propios de la ejecución del trabajo</div>
            <div class="info-grid">
                <div class="info-item"><div class="info-label">Biológico</div><div class="info-value"><?= badge($d['peligro_biologico'] ?? '') ?></div></div>
                <div class="info-item"><div class="info-label">Químico</div><div class="info-value"><?= badge($d['peligro_quimico'] ?? '') ?></div></div>
                <div class="info-item"><div class="info-label">Biomecánico</div><div class="info-value"><?= badge($d['peligro_biomecanico'] ?? '') ?></div></div>
                <div class="info-item"><div class="info-label">Físico</div><div class="info-value"><?= badge($d['peligro_fisico'] ?? '') ?></div></div>
                <div class="info-item"><div class="info-label">Psicosocial</div><div class="info-value"><?= badge($d['peligro_psicosocial'] ?? '') ?></div></div>
                <div class="info-item"><div class="info-label">Fenómenos Naturales</div><div class="info-value"><?= badge($d['peligro_fenomenos'] ?? '') ?></div></div>
                <div class="info-item" style="grid-column: 1 / -1;"><div class="info-label">Condiciones de Seguridad</div><div class="info-value"><?= badge($d['peligro_condiciones_seg'] ?? '') ?></div></div>
            </div>
        </div>

        <!-- VERIFICACION DOCUMENTAL -->
        <div class="section">
            <div class="section-header">03 — Verificación Documental</div>
            <table class="verif-doc-table">
                <thead><tr><th>Ítem</th><th>Estado</th><th>Observación</th></tr></thead>
                <tbody>
                    <?php
                    $vd_items = [
                        ['Permiso de trabajo aprobado', 'vd_permiso_trabajo', 'vd_permiso_obs'],
                        ['ATS socializado', 'vd_ats', 'vd_ats_obs'],
                        ['Personal autorizado/certificado', 'vd_personal', 'vd_personal_obs'],
                        ['Plan de emergencia disponible', 'vd_plan_emergencia', 'vd_plan_emergencia_obs'],
                        ['Procedimiento de trabajo disponible', 'vd_procedimiento', 'vd_procedimiento_obs'],
                    ];
                    foreach ($vd_items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item[0]) ?></td>
                        <td><?= badge($d[$item[1]] ?? '') ?></td>
                        <td style="color:var(--text-muted);"><?= val($d, $item[2], '') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- CONTROLES CRITICOS -->
        <div class="section">
            <div class="section-header">04 — Inspección de Controles Críticos</div>
            <table class="verif-doc-table">
                <thead><tr><th>Control</th><th>Estado</th><th>Observación</th></tr></thead>
                <tbody>
                    <?php
                    $cc_items = [
                        ['Delimitación y señalización del área', 'cc_delimitacion', 'cc_delimitacion_obs'],
                        ['Uso correcto de EPP', 'cc_epp', 'cc_epp_obs'],
                        ['Herramientas/equipos en buen estado', 'cc_herramientas', 'cc_herramientas_obs'],
                        ['Supervisión designada', 'cc_supervision', 'cc_supervision_obs'],
                        ['Plan de rescate/emergencia', 'cc_rescate', 'cc_rescate_obs'],
                        ['Orden y aseo del área', 'cc_orden_aseo', 'cc_orden_aseo_obs'],
                    ];
                    foreach ($cc_items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item[0]) ?></td>
                        <td><?= badge($d[$item[1]] ?? '') ?></td>
                        <td style="color:var(--text-muted);"><?= val($d, $item[2], '') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- TRABAJADORES -->
        <div class="section">
            <div class="section-header">05 — Trabajadores Autorizados</div>
            <?php
            $nombres   = $d['t_nombre']    ?? [];
            $tipos     = $d['t_tipo_doc']  ?? [];
            $docs      = $d['t_documento'] ?? [];
            $cargos    = $d['t_cargo']     ?? [];
            $dias      = $d['t_dia']       ?? [];
            $firmas    = $d['t_firma']     ?? [];
            ?>
            <?php if (!empty($nombres)): ?>
            <table class="trabajadores-table">
                <thead>
                    <tr>
                        <th>N°</th><th>Nombres y Apellidos</th><th>Tipo Doc</th>
                        <th>N° Documento</th><th>Cargo</th><th>Día</th><th>Firma</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($nombres as $i => $nombre): ?>
                    <tr>
                        <td style="color:var(--accent);font-family:'Space Mono',monospace;"><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($nombre) ?></td>
                        <td><?= htmlspecialchars($tipos[$i] ?? '—') ?></td>
                        <td><?= htmlspecialchars($docs[$i] ?? '—') ?></td>
                        <td><?= htmlspecialchars($cargos[$i] ?? '—') ?></td>
                        <td><?= htmlspecialchars($dias[$i] ?? '—') ?></td>
                        <td><?= htmlspecialchars($firmas[$i] ?? '—') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
                <p style="color:var(--text-muted); font-size:13px; padding:10px 0;">Sin trabajadores registrados.</p>
            <?php endif; ?>
        </div>

        <!-- FIRMAS -->
        <div class="section">
            <div class="section-header">06 — Firmas de Autorización</div>
            <div class="firmas-grid">
                <?php
                $firmas_config = [
                    ['Inspector', 'firma_inspector'],
                    ['Supervisor', 'firma_supervisor'],
                    ['Responsable SST', 'firma_resp_sst'],
                    ['Responsable de la Actividad', 'firma_resp_actividad'],
                ];
                foreach ($firmas_config as $fc): ?>
                <div class="firma-box">
                    <div class="firma-title"><?= htmlspecialchars($fc[0]) ?></div>
                    <div class="firma-line">
                        <div class="firma-label">Nombre</div>
                        <div class="firma-value"><?= val($d, $fc[1] . '_nombre') ?></div>
                    </div>
                    <div class="firma-line">
                        <div class="firma-label">Firma</div>
                        <div class="firma-value"><?= val($d, $fc[1] . '_firma') ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- OBSERVACIONES -->
        <div class="section">
            <div class="section-header">07 — Observaciones</div>
            <?php if (!empty($notas)): ?>
                <?php foreach ($notas as $n): ?>
                <div style="margin-bottom:10px; padding:10px 12px; border-left:3px solid var(--accent); background:rgba(0,240,255,0.03); border-radius:var(--r-sm);">
                    <div style="font-family:'Space Mono',monospace; font-size:11px; color:var(--text-muted); margin-bottom:4px;">
                        <?= htmlspecialchars($n['fecha_hora'] ?? '') ?> · <?= htmlspecialchars($n['usuario'] ?? '') ?>
                    </div>
                    <div style="font-size:13px; white-space:pre-wrap;"><?= htmlspecialchars($n['texto'] ?? '') ?></div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color:var(--text-muted); font-size:13px; padding:10px 0;">Sin observaciones registradas.</p>
            <?php endif; ?>
        </div>

        <?php endif; ?>
    </div>
</div>

<script>
    async function exportarPDF() {
        const btn = document.querySelector('.btn-pdf');
        btn.textContent = 'GENERANDO...';
        btn.disabled = true;

        const { jsPDF } = window.jspdf;
        const elemento = document.getElementById('documento');

        try {
            const canvas = await html2canvas(elemento, {
                scale: 2, useCORS: true, backgroundColor: '#151A22',
                windowWidth: elemento.scrollWidth, windowHeight: elemento.scrollHeight
            });

            const imgData = canvas.toDataURL('image/png');
            const pdf = new jsPDF({ orientation: 'p', unit: 'mm', format: 'a4' });

            const pageW = pdf.internal.pageSize.getWidth();
            const pageH = pdf.internal.pageSize.getHeight();
            const imgW = pageW - 20;
            const imgH = (canvas.height * imgW) / canvas.width;

            let y = 10;
            let heightLeft = imgH;
            pdf.addImage(imgData, 'PNG', 10, y, imgW, imgH);
            heightLeft -= (pageH - 20);

            while (heightLeft > 0) {
                y = heightLeft - imgH + 10;
                pdf.addPage();
                pdf.addImage(imgData, 'PNG', 10, y, imgW, imgH);
                heightLeft -= (pageH - 20);
            }

            pdf.save('Inspeccion_Trabajo_<?= urlencode($d['fecha'] ?? date('Y-m-d')) ?>.pdf');
        } catch (e) {
            console.error(e);
            window.print();
        }

        btn.textContent = '⬇ EXPORTAR PDF';
        btn.disabled = false;
    }
</script>
</body>
</html>
