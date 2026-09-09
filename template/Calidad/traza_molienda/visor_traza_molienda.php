<?php
include '../../sesion.php';

$id   = trim($_GET['id']   ?? '');
$file = trim($_GET['file'] ?? '');

// Validar que el archivo esté dentro del directorio permitido
$base_dir  = realpath(__DIR__ . '/../../../archivos/generados/traza_molienda');
$real_file = realpath($file);

if (!$id || !$file || !$real_file || strpos($real_file, $base_dir) !== 0) {
    die('<p style="color:red;font-family:monospace;padding:40px;">Registro no válido o no encontrado.</p>');
}

$contenido = json_decode(file_get_contents($real_file), true) ?: [];
$registro  = null;
foreach ($contenido as $r) {
    if (($r['id_registro'] ?? '') === $id) { $registro = $r; break; }
}
if (!$registro) {
    die('<p style="color:red;font-family:monospace;padding:40px;">Registro con ID "' . htmlspecialchars($id) . '" no encontrado.</p>');
}

$d         = $registro['datos'] ?? [];
$acond     = $d['acondicionamiento'] ?? [];
// 'silos' es el formato vigente; 'lotes' se conserva como fallback de registros antiguos
$silos_a   = $acond['silos'] ?? $acond['lotes'] ?? array_fill(0, 5, []);
$mol       = $d['molienda']['turnos'] ?? array_fill(0, 5, []);
$ctrl_prod = $d['control_producto'] ?? [];
$rupturas  = $d['control_rupturas'] ?? [];

function v($arr, $key, $default = '—') {
    $val = $arr[$key] ?? '';
    return ($val !== '' && $val !== null) ? htmlspecialchars($val) : $default;
}
function hm($arr, $hk, $mk) {
    $h = $arr[$hk] ?? ''; $m = $arr[$mk] ?? '';
    if ($h === '' && $m === '') return '—';
    return htmlspecialchars($h) . 'h ' . htmlspecialchars($m) . 'm';
}
function fmtFecha($iso) {
    if (!$iso) return '—';
    $parts = explode('-', $iso);
    return count($parts) === 3 ? $parts[2].'/'.$parts[1].'/'.$parts[0] : htmlspecialchars($iso);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visor · Trazabilidad de Molienda</title>
    <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700&family=Space+Mono:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg:       #0B0E14;
            --panel:    #151A22;
            --accent:   #00F0FF;
            --ag:       rgba(0,240,255,0.4);
            --text:     #E2E8F0;
            --muted:    #94A3B8;
            --border:   #1E293B;
            --ibg:      #0F172A;
            --r-md:     8px;
            --r-sm:     4px;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family:'Barlow',sans-serif;
            background-color:var(--bg);
            color:var(--text);
            min-height:100vh;
            padding:40px 20px;
            background-image:
                linear-gradient(rgba(0,240,255,0.03) 1px, transparent 1px),
                linear-gradient(90deg,rgba(0,240,255,0.03) 1px, transparent 1px);
            background-size:30px 30px;
        }
        .container { max-width:1160px; margin:0 auto; }

        /* HEADER */
        .header-box {
            background:var(--panel);
            border:1px solid var(--border);
            border-left:4px solid var(--accent);
            padding:24px 28px;
            border-radius:var(--r-md);
            margin-bottom:28px;
            display:flex;
            justify-content:space-between;
            align-items:center;
            flex-wrap:wrap;
            gap:16px;
            box-shadow:0 10px 30px rgba(0,0,0,0.5);
        }
        .main-title { font-size:20px; font-weight:700; color:#fff; text-transform:uppercase; letter-spacing:1px; margin-bottom:6px; }
        .meta-chips { display:flex; gap:10px; flex-wrap:wrap; margin-top:4px; }
        .chip {
            font-family:'Space Mono',monospace;
            font-size:11px;
            padding:3px 10px;
            border-radius:99px;
            border:1px solid rgba(0,240,255,0.3);
            background:rgba(0,240,255,0.07);
            color:var(--accent);
        }
        .chip.grey { border-color:var(--border); background:rgba(255,255,255,0.03); color:var(--muted); }
        .header-actions { display:flex; gap:10px; }
        .btn-action {
            background:var(--ibg);
            border:1px solid var(--border);
            color:var(--text);
            padding:10px 18px;
            border-radius:var(--r-sm);
            font-family:'Space Mono',monospace;
            font-size:12px;
            text-decoration:none;
            cursor:pointer;
            transition:all 0.2s;
            white-space:nowrap;
        }
        .btn-action:hover { border-color:var(--accent); color:var(--accent); }
        .btn-action.primary { background:rgba(0,240,255,0.1); border-color:rgba(0,240,255,0.4); color:var(--accent); }
        .btn-action.primary:hover { background:rgba(0,240,255,0.2); }

        /* SECCIÓN CARD */
        .section-card {
            background:var(--panel);
            border:1px solid var(--border);
            border-radius:var(--r-md);
            padding:22px 24px;
            margin-bottom:22px;
            box-shadow:0 4px 14px rgba(0,0,0,0.3);
        }
        .section-title {
            display:flex;
            align-items:center;
            gap:10px;
            font-size:14px;
            font-weight:700;
            color:var(--accent);
            margin-bottom:18px;
            padding-bottom:12px;
            border-bottom:1px dashed var(--border);
            text-transform:uppercase;
            letter-spacing:0.5px;
        }
        .section-title .num {
            font-family:'Space Mono',monospace;
            font-size:11px;
            background:rgba(0,240,255,0.1);
            border:1px solid rgba(0,240,255,0.2);
            padding:3px 8px;
            border-radius:var(--r-sm);
        }

        /* DATOS GENERALES */
        .datos-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:18px; }
        .dato-item { display:flex; flex-direction:column; gap:4px; }
        .dato-label { font-size:11px; color:var(--muted); text-transform:uppercase; letter-spacing:0.4px; font-weight:600; }
        .dato-val {
            font-family:'Space Mono',monospace;
            font-size:14px;
            color:var(--accent);
            background:rgba(0,240,255,0.05);
            border:1px solid rgba(0,240,255,0.15);
            padding:8px 12px;
            border-radius:var(--r-sm);
        }
        .dato-val.plain { font-family:'Barlow',sans-serif; font-size:14px; color:var(--text); background:rgba(255,255,255,0.03); border-color:var(--border); }

        /* TABLAS */
        .table-scroll { overflow-x:auto; }
        .v-table {
            width:100%;
            border-collapse:collapse;
            font-size:13px;
        }
        .v-table th {
            background:rgba(0,240,255,0.07);
            color:var(--accent);
            font-family:'Space Mono',monospace;
            font-size:11px;
            font-weight:700;
            padding:10px 10px;
            text-align:center;
            border:1px solid var(--border);
            white-space:nowrap;
            letter-spacing:0.3px;
        }
        .v-table td {
            border:1px solid var(--border);
            padding:8px 10px;
            vertical-align:middle;
            text-align:center;
            color:var(--text);
            font-size:13px;
        }
        .v-table .row-label {
            text-align:left;
            color:var(--muted);
            font-size:12px;
            font-weight:600;
            text-transform:uppercase;
            letter-spacing:0.3px;
            background:rgba(255,255,255,0.02);
            white-space:nowrap;
            padding:8px 14px;
        }
        .v-table td.empty-val { color:var(--border); font-family:'Space Mono',monospace; font-size:12px; }
        .v-table tr:hover td { background:rgba(0,240,255,0.02); }

        /* TOTALES */
        .totales-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:14px; margin-top:20px; }

        /* SUBSECTION */
        .subsection-label {
            font-size:11px;
            font-weight:700;
            color:var(--muted);
            text-transform:uppercase;
            letter-spacing:0.5px;
            margin:20px 0 10px;
            padding-bottom:6px;
            border-bottom:1px solid var(--border);
        }

        /* FIRMAS */
        .firmas-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:16px; margin-top:22px; }

        /* STATUS BAR */
        .status-bar {
            font-family:'Space Mono',monospace;
            font-size:11px;
            color:var(--muted);
            text-align:center;
            margin-top:32px;
            display:flex;
            justify-content:center;
            align-items:center;
            gap:8px;
        }
        .status-dot { width:7px; height:7px; background:#10B981; border-radius:50%; box-shadow:0 0 8px #10B981; }

        /* PRINT */
        @media print {
            body { background:#fff; color:#000; padding:10px; background-image:none; }
            .header-actions, .btn-action { display:none !important; }
            .section-card { border:1px solid #ccc; box-shadow:none; page-break-inside:avoid; }
            .v-table th { background:#e8f8ff; color:#006080; }
            .v-table td { color:#000; }
            .v-table .row-label { color:#444; background:#f5f5f5; }
            .dato-val { background:#f0fbff; border-color:#b0e8f0; color:#006080; }
            .chip { color:#006080; background:#e0f8ff; border-color:#90d8e8; }
            .status-bar { color:#888; }
        }
    </style>
</head>
<body>
<div class="container">

    <!-- HEADER -->
    <div class="header-box">
        <div>
            <div class="main-title">Trazabilidad de Molienda</div>
            <div class="meta-chips">
                <span class="chip"><?= fmtFecha($d['fecha'] ?? '') ?> · <?= htmlspecialchars($d['dia'] ?? '—') ?></span>
                <span class="chip grey"><?= htmlspecialchars($registro['sede_sys'] ?? '—') ?></span>
                <span class="chip grey"><?= htmlspecialchars($registro['usuario_sys'] ?? '—') ?></span>
                <span class="chip grey"><?= htmlspecialchars(substr($registro['timestamp'] ?? '', 0, 16)) ?></span>
            </div>
        </div>
        <div class="header-actions">
            <button class="btn-action primary" onclick="window.print()">⎙ IMPRIMIR</button>
            <a href="exportar_excel.php" class="btn-action" style="background:rgba(34,197,94,0.1);border-color:rgba(34,197,94,0.45);color:#22c55e;" download>↓ EXCEL</a>
            <a href="rev_traza_molienda.php" class="btn-action">← VOLVER</a>
        </div>
    </div>

    <!-- SECCIÓN 1: DATOS GENERALES -->
    <div class="section-card">
        <div class="section-title"><span class="num">01</span> Datos Generales</div>
        <div class="datos-grid">
            <div class="dato-item">
                <span class="dato-label">Fecha</span>
                <span class="dato-val"><?= fmtFecha($d['fecha'] ?? '') ?></span>
            </div>
            <div class="dato-item">
                <span class="dato-label">Día</span>
                <span class="dato-val"><?= v($d, 'dia') ?></span>
            </div>
            <div class="dato-item">
                <span class="dato-label">Sede</span>
                <span class="dato-val plain"><?= htmlspecialchars($registro['sede_sys'] ?? '—') ?></span>
            </div>
            <div class="dato-item">
                <span class="dato-label">Registrado por</span>
                <span class="dato-val plain"><?= htmlspecialchars($registro['usuario_sys'] ?? '—') ?></span>
            </div>
        </div>
    </div>

    <!-- SECCIÓN 2: ACONDICIONAMIENTO -->
    <div class="section-card">
        <div class="section-title"><span class="num">02</span> Acondicionamiento</div>
        <div class="table-scroll">
            <table class="v-table">
                <thead>
                    <tr>
                        <th style="width:160px;text-align:left;">Campo</th>
                        <?php for($i=1;$i<=5;$i++): ?>
                        <th>Silo <?= $i ?></th>
                        <?php endfor; ?>
                    </tr>
                </thead>
                <tbody>
                <?php
                $acond_rows = [
                    ['Ref. Harina',       fn($l) => v($l,'referencia_harina')],
                    ['Silo Acond.',        fn($l) => v($l,'silo')],
                    ['Fecha Mojo',        fn($l) => fmtFecha($l['fecha_mojo'] ?? '')],
                    ['HI Mojo',           fn($l) => hm($l,'hi_mojo_hrs','hi_mojo_min')],
                    ['HF Mojo',           fn($l) => hm($l,'hf_mojo_hrs','hf_mojo_min')],
                    ['Paradas HI-HF',     fn($l) => v($l,'paradas_hi_hf')],
                    ['Paradas Motivo',    fn($l) => v($l,'paradas_motivo')],
                    ['Horas Mojo',        fn($l) => hm($l,'horas_mojo_hrs','horas_mojo_min')],
                    // Fallback: en registros antiguos estos totales eran únicos por día (no por silo)
                    ['Total Trigo (kg)',       fn($l, $i) => v($l,'total_trigo')       !== '—' ? v($l,'total_trigo')       : ($i === 0 ? v($acond,'total_trigo')       : '—')],
                    ['Total Trigo MYFC (kg)',  fn($l, $i) => v($l,'total_trigo_myfc')  !== '—' ? v($l,'total_trigo_myfc')  : ($i === 0 ? v($acond,'total_trigo_myfc')  : '—')],
                    ['Total Agua (L)',         fn($l, $i) => v($l,'total_agua')        !== '—' ? v($l,'total_agua')        : ($i === 0 ? v($acond,'total_agua')        : '—')],
                ];
                foreach($acond_rows as [$label, $fn]):
                ?>
                    <tr>
                        <td class="row-label"><?= $label ?></td>
                        <?php foreach($silos_a as $i => $silo): ?>
                        <td><?= $fn($silo, $i) ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php
        $huboMezclas = false;
        foreach ($silos_a as $silo) { if (!empty($silo['mezclas_trigo'])) { $huboMezclas = true; break; } }
        $mezclasLegacy = $acond['mezclas_trigo'] ?? [];
        ?>
        <?php if ($huboMezclas): ?>
            <?php foreach ($silos_a as $i => $silo): ?>
                <?php $mezSilo = $silo['mezclas_trigo'] ?? []; if (empty($mezSilo)) continue; ?>
                <div class="subsection-label">Mezclas de Trigo — Silo <?= $i + 1 ?></div>
                <div class="table-scroll">
                    <table class="v-table">
                        <thead>
                            <tr>
                                <th style="width:36px;">#</th>
                                <th style="text-align:left;">Trigo (variedad)</th>
                                <th style="text-align:left;">Lote</th>
                                <th>% Mezcla</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach($mezSilo as $j => $mez): ?>
                            <tr>
                                <td style="font-family:'Space Mono',monospace;font-size:11px;color:var(--muted);"><?= $j+1 ?></td>
                                <td style="text-align:left;"><?= v($mez,'trigo') ?></td>
                                <td style="text-align:left;"><?= v($mez,'lote') ?></td>
                                <td><?= v($mez,'porcentaje') ?><?= ($mez['porcentaje'] ?? '') !== '' ? ' %' : '' ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endforeach; ?>
        <?php elseif (!empty($mezclasLegacy)): ?>
            <div class="subsection-label">Mezclas de Trigo (registro histórico)</div>
            <div class="table-scroll">
                <table class="v-table">
                    <thead>
                        <tr>
                            <th style="width:36px;">#</th>
                            <th style="text-align:left;">Trigo (variedad)</th>
                            <th style="text-align:left;">Lote</th>
                            <th>% Mezcla</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach($mezclasLegacy as $i => $mez): ?>
                        <tr>
                            <td style="font-family:'Space Mono',monospace;font-size:11px;color:var(--muted);"><?= $i+1 ?></td>
                            <td style="text-align:left;"><?= v($mez,'trigo') ?></td>
                            <td style="text-align:left;"><?= v($mez,'lote') ?></td>
                            <td><?= v($mez,'porcentaje') ?><?= ($mez['porcentaje'] ?? '') !== '' ? ' %' : '' ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- SECCIÓN 3: MOLIENDA -->
    <div class="section-card">
        <div class="section-title"><span class="num">03</span> Molienda</div>
        <div class="table-scroll">
            <table class="v-table">
                <thead>
                    <tr>
                        <th style="width:160px;text-align:left;">Campo</th>
                        <?php for($i=1;$i<=5;$i++): ?>
                        <th>Turno <?= $i ?></th>
                        <?php endfor; ?>
                    </tr>
                </thead>
                <tbody>
                <?php
                $mol_rows = [
                    ['HI Molienda',       fn($t) => hm($t,'hi_hrs','hi_min')],
                    ['HF Molienda',       fn($t) => hm($t,'hf_hrs','hf_min')],
                    ['Paradas HI-HF',     fn($t) => v($t,'paradas_hi_hf')],
                    ['Paradas Motivo',    fn($t) => v($t,'paradas_motivo')],
                    ['Horas Molienda',    fn($t) => hm($t,'horas_molienda_hrs','horas_molienda_min')],
                    ['Horas Reposo',      fn($t) => hm($t,'horas_reposo_hrs','horas_reposo_min')],
                ];
                $turnos = array_pad($mol, 5, []);
                foreach($mol_rows as [$label, $fn]):
                ?>
                    <tr>
                        <td class="row-label"><?= $label ?></td>
                        <?php foreach($turnos as $t): ?>
                        <td><?= $fn($t) ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- SECCIÓN 4: CONTROL PRODUCTO EN PROCESO -->
    <div class="section-card">
        <div class="section-title"><span class="num">04</span> Control Producto en Proceso</div>
        <?php if(!empty($ctrl_prod)): ?>
        <div class="table-scroll">
            <table class="v-table">
                <thead>
                    <tr>
                        <th style="width:32px;">#</th>
                        <th>Hora</th>
                        <th>Hum. Trigo (%)</th>
                        <th>Hum. Harina (%)</th>
                        <th>Granulometría</th>
                        <th>Almidón Dañado</th>
                        <th>Cenizas</th>
                        <th>Ác. Ascórbico</th>
                        <th>Verif. Bultos</th>
                        <th>Peso Prom. (kg)</th>
                        <th>Líder</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach($ctrl_prod as $i => $cp): ?>
                    <tr>
                        <td style="font-family:'Space Mono',monospace;font-size:11px;color:var(--muted);"><?= $i+1 ?></td>
                        <td style="font-family:'Space Mono',monospace;color:var(--accent);"><?= v($cp,'hora') ?></td>
                        <td><?= v($cp,'humedad_trigo') ?></td>
                        <td><?= v($cp,'humedad_harina') ?></td>
                        <td><?= v($cp,'granulometria') ?></td>
                        <td><?= v($cp,'almidon_danado') ?></td>
                        <td><?= v($cp,'cenizas') ?></td>
                        <td><?= v($cp,'acido_ascorbico') ?></td>
                        <td><?= v($cp,'verificacion_bultos') ?></td>
                        <td><?= v($cp,'peso_promedio') ?></td>
                        <td style="text-align:left;"><?= v($cp,'lider') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <p style="color:var(--muted);font-size:13px;font-family:'Space Mono',monospace;">Sin filas registradas.</p>
        <?php endif; ?>

        <?php
        // 'lider' por fila es el formato vigente; lider_turno_dia/noche se muestran solo si vienen de un registro antiguo
        $liderDiaLegacy   = v($d,'lider_turno_dia', '');
        $liderNocheLegacy = v($d,'lider_turno_noche', '');
        ?>
        <div class="firmas-grid">
            <?php if ($liderDiaLegacy !== ''): ?>
            <div class="dato-item">
                <span class="dato-label">Líder Turno Día (histórico)</span>
                <span class="dato-val plain"><?= $liderDiaLegacy ?></span>
            </div>
            <?php endif; ?>
            <?php if ($liderNocheLegacy !== ''): ?>
            <div class="dato-item">
                <span class="dato-label">Líder Turno Noche (histórico)</span>
                <span class="dato-val plain"><?= $liderNocheLegacy ?></span>
            </div>
            <?php endif; ?>
            <div class="dato-item">
                <span class="dato-label">Analista de Calidad</span>
                <span class="dato-val plain"><?= v($d,'analista_calidad') ?></span>
            </div>
        </div>
    </div>

    <!-- SECCIÓN 5: CONTROL DE RUPTURAS -->
    <div class="section-card">
        <div class="section-title"><span class="num">05</span> Control de Rupturas</div>
        <?php if(!empty($rupturas)): ?>
        <div class="table-scroll">
            <table class="v-table">
                <thead>
                    <tr>
                        <th style="width:32px;">#</th>
                        <th colspan="2" style="background:rgba(0,240,255,0.04);">Banco T1</th>
                        <th colspan="2" style="background:rgba(0,240,255,0.04);">Banco T2</th>
                        <th colspan="2" style="background:rgba(0,240,255,0.04);">Banco T3</th>
                        <th>Granulometría</th>
                    </tr>
                    <tr>
                        <th></th>
                        <th>Derecha</th><th>Izquierda</th>
                        <th>Derecha</th><th>Izquierda</th>
                        <th>Derecha</th><th>Izquierda</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach($rupturas as $i => $r): ?>
                    <tr>
                        <td style="font-family:'Space Mono',monospace;font-size:11px;color:var(--muted);"><?= $i+1 ?></td>
                        <td><?= v($r,'t1_derecha') ?></td>
                        <td><?= v($r,'t1_izquierda') ?></td>
                        <td><?= v($r,'t2_derecha') ?></td>
                        <td><?= v($r,'t2_izquierda') ?></td>
                        <td><?= v($r,'t3_derecha') ?></td>
                        <td><?= v($r,'t3_izquierda') ?></td>
                        <td><?= v($r,'granulometria') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <p style="color:var(--muted);font-size:13px;font-family:'Space Mono',monospace;">Sin filas registradas.</p>
        <?php endif; ?>
    </div>

    <div class="status-bar">
        <div class="status-dot"></div>
        ID: <?= htmlspecialchars($registro['id_registro'] ?? '') ?> · <?= htmlspecialchars(substr($registro['timestamp'] ?? '', 0, 16)) ?>
    </div>

</div>
</body>
</html>
