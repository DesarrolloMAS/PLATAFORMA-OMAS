<?php
// ── Cargar datos ──
$id = $_GET['id'] ?? '';
if (!$id || !preg_match('/^investigacion_\d{8}_\d{6}$/', $id)) {
    die('ID de investigación no válido.');
}

$json_file = '/var/www/fmt/archivos/generados/HSEQ/investigacionesjson/' . $id . '.json';
if (!file_exists($json_file)) {
    die('Investigación no encontrada.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['firma_index'], $_POST['firma_data'])) {
    $data = json_decode(file_get_contents($json_file), true);
    $idx = (int)$_POST['firma_index'];
    if (!isset($data['post']['equipo_firma'])) {
        $data['post']['equipo_firma'] = array_fill(0, count($data['post']['equipo_nombre'] ?? [0]), '');
    }
    $data['post']['equipo_firma'][$idx] = $_POST['firma_data'];
    file_put_contents($json_file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo json_encode(['status' => 'ok']);
    exit;
}

// ── Handler: Corrección de equipo investigador ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion_equipo']) && $_POST['accion_equipo'] === 'corregir') {
    $data = json_decode(file_get_contents($json_file), true);
    $data['post']['equipo_nombre']  = $_POST['eq_nombre']  ?? [];
    $data['post']['equipo_apellido'] = $_POST['eq_apellido'] ?? [];
    $data['post']['equipo_cargo']   = $_POST['eq_cargo']   ?? [];
    $data['post']['equipo_tipo_id'] = $_POST['eq_tipo_id']  ?? [];
    $data['post']['equipo_numero']  = $_POST['eq_numero']   ?? [];
    // Preserve existing signatures, extend array if new rows were added
    $oldFirmas = $data['post']['equipo_firma'] ?? [];
    $newCount = count($data['post']['equipo_nombre']);
    $data['post']['equipo_firma'] = array_pad($oldFirmas, $newCount, '');
    // Trim to match new count (in case rows were removed)
    $data['post']['equipo_firma'] = array_slice($data['post']['equipo_firma'], 0, $newCount);
    file_put_contents($json_file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo json_encode(['status' => 'ok']);
    exit;
}

$data = json_decode(file_get_contents($json_file), true);
if (!$data) {
    die('Error al leer los datos.');
}

$post = $data['post'] ?? [];
$archivos = $data['archivos'] ?? [];
function e($v) { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
function val($v, $class = '') {
    $empty = (is_array($v) ? empty(array_filter($v, fn($x)=>trim($x)!=='')) : empty(trim($v ?? '')));
    $cls = 'field-value' . ($class ? " $class" : '') . ($empty ? ' empty' : '');
    if (is_array($v)) $v = implode(', ', array_map('e', $v));
    return '<div class="' . $cls . '">' . ($empty ? '—' : e($v)) . '</div>';
}
function label($k) {
    $k = preg_replace('/^(inv|emp|trab|acc|desc|plan|equipo|testigo|porques|causa|carac)_/','',$k);
    $k = str_replace('_',' ', $k);
    return ucwords($k);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Investigación de Accidentes e Incidentes de Trabajo</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,300&family=DM+Mono:wght@400;500&family=Fraunces:opsz,wght@9..144,300;9..144,600;9..144,700&display=swap" rel="stylesheet">
    <style>
        :root { --navy:#0d1f3c; --navy-mid:#152d54; --navy-light:#1e3f6f; --accent:#c0932a; --accent-lt:#e8b84b; --accent-pale:#fdf4e1; --slate:#4a5f7a; --slate-lt:#8399b4; --fog:#eef2f7; --white:#ffffff; --ink:#0d1f3c; --ink-soft:#3d5070; --r-sm:4px; --r-md:8px; --r-lg:12px; --shadow-card:0 2px 12px rgba(13,31,60,0.09),0 1px 3px rgba(13,31,60,0.07); --shadow-section:0 4px 20px rgba(13,31,60,0.12);}
        @page { size: Letter; margin: 1.2cm 1.3cm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DM Sans', sans-serif; font-size: 9.5pt; line-height: 1.5; color: var(--ink); background: #f0f4f9;}
        .page-wrap { max-width: 960px; margin: 0 auto; padding: 24px 20px; }
        .doc-header { background: var(--white); border: 1.5px solid #c8d3e0; border-radius: var(--r-lg); overflow: hidden; margin-bottom: 24px; box-shadow: var(--shadow-section);}
        .header-band { height: 5px; background: linear-gradient(90deg, var(--navy) 0%, var(--navy-light) 40%, var(--accent) 100%);}
        .header-body { display: grid; grid-template-columns: 160px 1fr auto; align-items: stretch; min-height: 90px;}
        .header-logo-cell { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 14px 18px; border-right: 1.5px solid #c8d3e0; gap: 6px;}
        .logo-mark { width: 56px; height: 56px;}
        .logo-tagline { font-size: 6.5pt; font-style: italic; color: var(--slate); text-align: center; letter-spacing: 0.2px;}
        .header-title-cell { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 14px 24px; gap: 5px; text-align: center;}
        .header-label { font-size: 7pt; font-weight: 600; color: var(--slate); text-transform: uppercase; letter-spacing: 1.2px;}
        .header-main-title { font-family: 'Fraunces', serif; font-size: 15pt; font-weight: 700; color: var(--navy); line-height: 1.2;}
        .header-sub { font-size: 8pt; color: var(--slate); font-weight: 400;}
        .header-meta-cell { display: flex; flex-direction: column; border-left: 1.5px solid #c8d3e0; min-width: 170px;}
        .meta-row { display: grid; grid-template-columns: 90px 1fr; border-bottom: 1px solid #c8d3e0; flex: 1;}
        .meta-row:last-child { border-bottom: none;}
        .meta-key { padding: 0 10px; display: flex; align-items: center; font-size: 7pt; font-weight: 600; color: var(--slate); text-transform: uppercase; letter-spacing: 0.6px; background: var(--fog); border-right: 1px solid #c8d3e0;}
        .meta-val { padding: 0 10px; display: flex; align-items: center; font-family: 'DM Mono', monospace; font-size: 8pt; color: var(--ink);}
        .section { background: var(--white); border-radius: var(--r-lg); overflow: hidden; margin-bottom: 18px; box-shadow: var(--shadow-card); border: 1px solid #dde4ef; page-break-inside: avoid;}
        .section-header { display: flex; align-items: center; gap: 11px; padding: 11px 18px; background: var(--navy); color: var(--white); position: relative; overflow: hidden;}
        .section-header::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 4px; background: var(--accent);}
        .section-header::after { content: ''; position: absolute; right: -20px; top: -20px; width: 60px; height: 60px; background: rgba(255,255,255,0.04); border-radius: 50%;}
        .section-number { font-family: 'DM Mono', monospace; font-size: 7.5pt; font-weight: 500; color: var(--accent-lt); letter-spacing: 0.5px;}
        .section-title { font-family: 'Fraunces', serif; font-size: 10.5pt; font-weight: 600; letter-spacing: 0.2px; flex: 1;}
        .section-body { padding: 18px 20px;}
        .field-grid   { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px 14px; margin-bottom: 14px;}
        .field-grid-2 { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px 14px; margin-bottom: 14px;}
        .field-full   { grid-column: 1 / -1;}
        .field { display: flex; flex-direction: column;}
        .field-label { font-size: 7pt; font-weight: 600; text-transform: uppercase; letter-spacing: 0.7px; color: var(--slate); margin-bottom: 4px;}
        .field-value { font-size: 9pt; color: var(--ink); padding: 7px 10px; background: var(--fog); border: 1px solid #d2dce9; border-radius: var(--r-sm); min-height: 30px; line-height: 1.45;}
        .field-value.locked { background: #f7f9fc; color: var(--slate); border-style: dashed;}
        .field-value.tall { min-height: 50px;}
        .field-value.empty { color: var(--slate-lt); font-style: italic;}
        .data-table { width: 100%; border-collapse: collapse; margin: 14px 0; font-size: 8.5pt;}
        .data-table thead th { background: var(--navy-mid); color: var(--white); font-size: 7pt; font-weight: 600; text-transform: uppercase; letter-spacing: 0.7px; padding: 9px 12px; text-align: left;}
        .data-table tbody td { padding: 8px 12px; border-bottom: 1px solid #e4ecf4; color: var(--ink); vertical-align: top;}
        .data-table tbody tr:nth-child(even) td { background: #f8fafd;}
        .data-table tbody tr:last-child td { border-bottom: none;}
        .photo-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 12px;}
        .photo-item img { width: 100%; border-radius: var(--r-sm); border: 1px solid #d2dce9;}
        .photo-item .photo-caption { font-size: 7pt; color: var(--slate); text-align: center; margin-top: 4px;}
        .no-photos { font-size: 8pt; color: var(--slate-lt); font-style: italic; padding: 16px; text-align: center;}
        .result-box { background: linear-gradient(135deg, #f0f5ff 0%, #fdf8ee 100%); border: 1px solid #d2dce9; border-left: 4px solid var(--navy); border-radius: var(--r-md); padding: 16px 18px; font-size: 9pt; color: var(--ink); line-height: 1.65;}
        .doc-footer { margin-top: 24px; display: flex; align-items: center; justify-content: space-between; padding: 10px 16px; border-top: 2px solid #d2dce9; font-size: 7pt; color: var(--slate-lt); font-family: 'DM Mono', monospace;}
        .footer-brand { display: flex; align-items: center; gap: 6px; color: var(--slate);}
        .pre-section-container { display: flex; flex-direction: column; gap: 12px; margin-bottom: 20px; }
        .creation-date-box { align-self: flex-start; display: inline-flex; align-items: center; gap: 8px; padding: 6px 12px; background: var(--fog); border: 1px solid #d2dce9; border-radius: var(--r-sm); font-size: 8pt; }
        .creation-date-label { font-weight: 600; color: var(--slate); text-transform: uppercase; letter-spacing: 0.5px; font-size: 7pt; }
        .creation-date-val { font-family: 'DM Mono', monospace; font-weight: 500; color: var(--ink); }
        .instruction-box { display: flex; border: 1px solid var(--ink); border-radius: 0; overflow: hidden; background: var(--white); box-shadow: var(--shadow-card); }
        .instruction-title { background: var(--navy); color: var(--white); padding: 12px; font-size: 8pt; font-weight: 600; text-align: center; display: flex; align-items: center; justify-content: center; width: 170px; flex-shrink: 0; letter-spacing: 0.2px; line-height: 1.3;}
        .instruction-text { padding: 8px 14px; font-size: 8.5pt; color: var(--ink); display: flex; flex-direction: column; justify-content: center; flex: 1; border-left: 1px solid var(--ink); }
        /* Botón corregir en header de sección */
        .btn-corregir-equipo { margin-left:auto; display:inline-flex; align-items:center; gap:5px; padding:4px 12px; font-size:8pt; font-weight:600; background:rgba(255,255,255,0.15); color:var(--accent-lt); border:1px solid rgba(255,255,255,0.25); border-radius:5px; cursor:pointer; transition:all .2s; z-index:1; }
        .btn-corregir-equipo:hover { background:var(--accent); color:var(--navy); border-color:var(--accent); }
        /* Inputs dentro del modal de corrección */
        #tablaEquipoEdit input, #tablaEquipoEdit select { width:100%; padding:6px 8px; font-size:8.5pt; font-family:'DM Sans',sans-serif; border:1px solid #d2dce9; border-radius:4px; background:#fff; color:var(--ink); transition:border-color .2s; box-sizing:border-box; }
        #tablaEquipoEdit input:focus, #tablaEquipoEdit select:focus { outline:none; border-color:var(--accent); box-shadow:0 0 0 2px rgba(192,147,42,0.15); }
        #tablaEquipoEdit tbody td { padding:6px 8px; }
        .btn-eliminar-fila { background:none; border:none; cursor:pointer; color:var(--slate-lt); padding:4px; border-radius:4px; transition:all .2s; display:flex; align-items:center; justify-content:center; }
        .btn-eliminar-fila:hover { color:#c0392b; background:#fdecea; }
        #btnAgregarFila:hover { background:var(--accent-pale); border-color:var(--accent); }
        @media print { body { background: #fff; } .page-wrap { padding: 0; max-width: 100%; } .section { page-break-inside: avoid; box-shadow: none; } .doc-header { box-shadow: none; } .no-print { display: none !important; } .instruction-box { box-shadow: none; } }
    </style>
</head>
<body>
<div class="page-wrap">

    <!-- ENCABEZADO INSTITUCIONAL ISO -->
    <table style="width:100%; border-collapse: collapse; border: 1px solid var(--ink); margin-bottom: 24px; background: var(--white);">
        <tr>
            <td style="width: 25%; border-right: 1px solid var(--ink); text-align: center; padding: 12px; vertical-align: middle;">
                <!-- Si el logo no carga, cambia esta ruta por la de tu logo correcto, como /archivos/formularios/logomas.png -->
                <img src="/img/logo_empresa.jpeg" alt="Logo MAS" style="max-height: 80px; max-width: 100%; object-fit: contain;">
            </td>
            <td style="width: 50%; border-right: 1px solid var(--ink); text-align: center; padding: 12px; vertical-align: middle;">
                <div style="font-size: 10pt; font-weight: 600; margin-bottom: 4px; color: var(--ink);">Manual del Sistema de Seguridad y Salud en el Trabajo</div>
                <div style="font-size: 10pt; font-weight: 400; color: var(--ink);">"Formato de Investigación de Accidentes e Incidentes de Trabajo"</div>
            </td>
            <td style="width: 25%; padding: 0; vertical-align: top;">
                <table style="width: 100%; height: 100%; border-collapse: collapse; font-size: 8.5pt;">
                    <tr>
                        <td style="border-bottom: 1px solid var(--ink); border-right: 1px solid var(--ink); padding: 5px 8px; font-weight: 600; color: var(--ink); width: 60px;">Código:</td>
                        <td style="border-bottom: 1px solid var(--ink); padding: 5px 8px; color: var(--ink);">GQ-HS-EJ-MN-FO-01</td>
                    </tr>
                    <tr>
                        <td style="border-bottom: 1px solid var(--ink); border-right: 1px solid var(--ink); padding: 5px 8px; font-weight: 600; color: var(--ink);">Versión:</td>
                        <td style="border-bottom: 1px solid var(--ink); padding: 5px 8px; color: var(--ink);">2</td>
                    </tr>
                    <tr>
                        <td style="border-bottom: 1px solid var(--ink); border-right: 1px solid var(--ink); padding: 5px 8px; font-weight: 600; color: var(--ink);">Fecha:</td>
                        <td style="border-bottom: 1px solid var(--ink); padding: 5px 8px; color: var(--ink);">24-09-2022</td>
                    </tr>
                    <tr>
                        <td style="border-right: 1px solid var(--ink); padding: 5px 8px; font-weight: 600; color: var(--ink);">Página:</td>
                        <td style="padding: 5px 8px; color: var(--ink);">1 de 1</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- CONTENEDOR DE FECHA DE CREACIÓN E INSTRUCCIONES -->
    <div class="pre-section-container">
        <div class="creation-date-box">
            <span class="creation-date-label">Fecha de Creación del Formato:</span>
            <span class="creation-date-val"><?= !empty($data['timestamp']) ? date('d/m/Y h:i A', strtotime($data['timestamp'])) : 'No registrada' ?></span>
        </div>

        <div class="instruction-box">
            <div class="instruction-title">INSTRUCCIONES DE<br>DILIGENCIAMIENTO</div>
            <div class="instruction-text">
                <span>Responda cada una de las preguntas indicadas, con letra imprenta clara o a máquina de escribir.</span>
                <span style="font-weight: 600; margin-top: 2px;">Nota: TODA LAS CASILLAS DEBEN SER DILIGENCIADAS</span>
            </div>
        </div>
    </div>

    <!-- 1. INFORMACIÓN DE LA INVESTIGACIÓN -->
    <div class="section">
        <div class="section-header"><span class="section-number">01</span><span class="section-title">Información de la Investigación</span></div>
        <div class="section-body">
            <div class="field-grid">
                <?php foreach ($post as $k => $v): if(strpos($k,'inv_')===0): ?>
                <div class="field"><label class="field-label"><?= label($k) ?></label><?= val($v) ?></div>
                <?php endif; endforeach; ?>
            </div>
        </div>
    </div>

    <!-- 2. INFORMACIÓN DE LA EMPRESA -->
    <div class="section">
        <div class="section-header"><span class="section-number">02</span><span class="section-title">Información de la Empresa</span></div>
        <div class="section-body">
            <div class="field-grid">
                <?php foreach ($post as $k => $v): if(strpos($k,'emp_')===0): ?>
                <div class="field"><label class="field-label"><?= label($k) ?></label><?= val($v) ?></div>
                <?php endif; endforeach; ?>
            </div>
        </div>
    </div>

    <!-- 3. DATOS DEL TRABAJADOR -->
    <div class="section">
        <div class="section-header"><span class="section-number">03</span><span class="section-title">Datos Generales del Trabajador</span></div>
        <div class="section-body">
            <div class="field-grid">
                <?php foreach ($post as $k => $v): if(strpos($k,'trab_')===0): ?>
                <div class="field"><label class="field-label"><?= label($k) ?></label><?= val($v) ?></div>
                <?php endif; endforeach; ?>
            </div>
        </div>
    </div>

    <!-- 4. INFORMACIÓN DEL ACCIDENTE -->
    <div class="section">
        <div class="section-header"><span class="section-number">04</span><span class="section-title">Información sobre el Accidente</span></div>
        <div class="section-body">
            <div class="field-grid">
                <?php foreach ($post as $k => $v): if(strpos($k,'acc_')===0): ?>
                <div class="field"><label class="field-label"><?= label($k) ?></label><?= val($v) ?></div>
                <?php endif; endforeach; ?>
            </div>
        </div>
    </div>

    <!-- 5. DESCRIPCIÓN DEL ACCIDENTE -->
    <div class="section">
        <div class="section-header"><span class="section-number">05</span><span class="section-title">Descripción del Accidente</span></div>
        <div class="section-body">
            <div class="field-grid-2">
                <?php foreach ($post as $k => $v): if(strpos($k,'desc_')===0): ?>
                <div class="field"><label class="field-label"><?= label($k) ?></label><?= val($v, 'tall') ?></div>
                <?php endif; endforeach; ?>
            </div>
            <?php if(!empty($post['testigo_nombre'])): ?>
            <div class="subsection"><span class="subsection-dot"></span><span class="subsection-title">Testigos</span><span class="subsection-line"></span></div>
            <table class="data-table">
                <thead><tr><th>Nombre</th><th>Documento</th><th>Cargo</th></tr></thead>
                <tbody>
                <?php
                $nombres = $post['testigo_nombre'];
                $docs = $post['testigo_documento'];
                $cargos = $post['testigo_cargo'];
                for($i=0;$i<count($nombres);$i++): ?>
                    <tr>
                        <td><?= e($nombres[$i]) ?></td>
                        <td><?= e($docs[$i]) ?></td>
                        <td><?= e($cargos[$i]) ?></td>
                    </tr>
                <?php endfor; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- 6. REGISTRO FOTOGRÁFICO -->
 <div class="section">
    <div class="section-header"><span class="section-number">06</span><span class="section-title">Registro Fotográfico</span></div>
    <div class="section-body">
        <div class="field-grid-2">
            <?php foreach(['tomo1','tomo2','tomo3','tomo4'] as $toma): ?>
            <div class="field">
                <label class="field-label"><?= strtoupper($toma) ?></label>
                <?php if(!empty($archivos[$toma])): ?>
                <div class="photo-grid">
                    <?php foreach($archivos[$toma] as $foto): ?>
                    <div class="photo-item">
                        <img src="<?= str_replace('/var/www/fmt','',$foto['ruta']) ?>" alt="Foto <?= $toma ?>">
                        <div class="photo-caption"><?= htmlspecialchars($foto['nombre_original']) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="no-photos">No hay fotos registradas</div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

    <!-- 7. CARACTERIZACIÓN DEL ACCIDENTE LABORAL -->
    <div class="section">
        <div class="section-header"><span class="section-number">07</span><span class="section-title">Caracterización del Accidente Laboral (Según Norma ANSI Z 16.2)</span></div>
        <div class="section-body">
            <div class="field-grid-2">
                <?php 
                $caracLabels = [
                    'carac_lesion' => 'Naturaleza de la lesión',
                    'carac_cuerpo' => 'Parte del cuerpo afectada',
                    'carac_mecanismo' => 'Mecanismo o fuente del accidente',
                    'carac_agente' => 'Agente del accidente',
                    'carac_tipo_acc' => 'Tipo de accidente'
                ];
                foreach ($post as $k => $v): if(strpos($k,'carac_')===0): 
                    $isEmpty = (is_array($v) ? empty(array_filter($v, fn($x)=>trim($x)!=='')) : empty(trim($v ?? '')));
                    if (!$isEmpty): 
                        $lbl = $caracLabels[$k] ?? label($k);
                ?>
                <div class="field field-full"><label class="field-label"><?= htmlspecialchars($lbl) ?></label><?= val($v) ?></div>
                <?php endif; endif; endforeach; ?>
            </div>
        </div>
    </div>

    <!-- 8. TÉCNICA 5 PORQUÉS -->
    <div class="section">
        <div class="section-header"><span class="section-number">08</span><span class="section-title">Técnica de los 5 ¿Por Qué?</span></div>
        <div class="section-body">
            <?php
            $categorias = [
                'mano_obra'      => 'Mano de Obra',
                'maquinaria'     => 'Maquinaria',
                'medio_ambiente' => 'Medio Ambiente',
                'material'       => 'Material',
                'medicion'       => 'Medición',
            ];
            foreach($categorias as $cat=>$label):
                $metodo = $post["porques_{$cat}_metodo"] ?? '';
                $hay = $metodo;
                for($i=1;$i<=5;$i++) if(!empty($post["porques_{$cat}_{$i}"]??'')) $hay = true;
                if(!$hay) continue;
            ?>
            <div class="why-block">
                <div class="why-block-header"><?= $label ?></div>
                <?php if($metodo): ?>
                <div style="padding:10px 14px;font-size:8.5pt;color:var(--ink);border-bottom:1px solid #d2dce9;background:#fff;">
                    <span style="font-size:7pt;font-weight:600;text-transform:uppercase;letter-spacing:0.6px;color:var(--slate);">Descripción del método</span><br>
                    <?= e($metodo) ?>
                </div>
                <?php endif; ?>
                <div class="why-grid">
                    <?php for($i=1;$i<=5;$i++): ?>
                    <div class="why-item">
                        <div class="why-badge"><?= $i ?></div>
                        <div class="why-q">¿Por qué?</div>
                        <div class="why-answer"><?= e($post["porques_{$cat}_{$i}"] ?? '') ?></div>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>
            <?php endforeach; ?>
            <div class="field field-full" style="margin-top:4px;">
                <label class="field-label">Causa Raíz (Conclusión)</label>
                <?= val($post['porques_causa_raiz'] ?? '', 'tall') ?>
            </div>
        </div>
    </div>

    <!-- 9. ANÁLISIS DE CAUSA -->
    <div class="section">
        <div class="section-header"><span class="section-number">09</span><span class="section-title">Análisis de Causa</span></div>
        <div class="section-body">
            <?php 
            $gruposCausa = [
                'Causas Directas e Inmediatas' => [
                    'Condiciones Inseguras' => 'causa_condicion_insegura',
                    'Actos Inseguros'       => 'causa_acto_inseguro'
                ],
                'Causas Indirectas o Básicas' => [
                    'Factores de Trabajo'   => 'causa_factor_trabajo',
                    'Factores Personales'   => 'causa_factor_personal'
                ]
            ];

            foreach ($gruposCausa as $tituloGrupo => $subgrupos): 
                $grupoFilled = false;
                foreach ($subgrupos as $prefijo) {
                    for($i=1; $i<=4; $i++) {
                        if(!empty(trim($post["{$prefijo}_{$i}"] ?? '')) || !empty(trim($post["{$prefijo}_texto_{$i}"] ?? ''))) {
                            $grupoFilled = true;
                            break 2;
                        }
                    }
                }
                if (!$grupoFilled) continue;
            ?>
            <div style="background:var(--fog); padding:8px 12px; margin-bottom:12px; border-left:4px solid var(--accent); border-radius:var(--r-sm);">
                <h4 style="color:var(--navy); font-size:9pt; margin:0; text-transform:uppercase; letter-spacing:0.5px;"><?= $tituloGrupo ?></h4>
            </div>
            
            <div class="field-grid-2" style="margin-bottom:16px;">
            <?php foreach ($subgrupos as $tituloSub => $prefijo): 
                $subFilled = false;
                for($i=1; $i<=4; $i++) {
                    if(!empty(trim($post["{$prefijo}_{$i}"] ?? '')) || !empty(trim($post["{$prefijo}_texto_{$i}"] ?? ''))) {
                        $subFilled = true; break;
                    }
                }
                if (!$subFilled) continue;
            ?>
                <div class="field field-full" style="background:#fff; border:1px solid #d2dce9; border-radius:var(--r-sm); overflow:hidden;">
                    <div style="background:var(--navy-mid); color:var(--white); padding:6px 10px; font-size:7.5pt; font-weight:600; text-transform:uppercase; letter-spacing:0.5px;">
                        <?= $tituloSub ?>
                    </div>
                    <div style="padding:10px; display:flex; flex-direction:column; gap:8px;">
                        <?php for($i=1; $i<=4; $i++): 
                            $v = trim($post["{$prefijo}_{$i}"] ?? '');
                            $t = trim($post["{$prefijo}_texto_{$i}"] ?? '');
                            if(!empty($v) || !empty($t)): 
                                $vClean = ($v === 'otro' || strtolower($v) === 'otro - especificar') ? '' : $v;
                                $finalText = trim($vClean . ($vClean && $t ? ' - ' : '') . $t);
                        ?>
                            <div style="font-size:8pt; line-height:1.4;">
                                <span style="font-weight:600; color:var(--navy); margin-right:4px;">•</span> 
                                <?= e($finalText) ?>
                            </div>
                        <?php endif; endfor; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- 10. PLAN DE ACCIONES -->
    <div class="section">
        <div class="section-header"><span class="section-number">10</span><span class="section-title">Plan de Acciones Correctivas y Preventivas</span></div>
        <div class="section-body">
            <?php if(!empty($post['plan_fecha'])): ?>
            <table class="data-table">
                <thead><tr><th>Fecha</th><th>Medida</th><th>Control</th><th>Responsable</th></tr></thead>
                <tbody>
                <?php
                $fechas = $post['plan_fecha'];
                $medidas = $post['plan_medida'];
                $controles = $post['plan_control'];
                $responsables = $post['plan_responsable'];
                for($i=0;$i<count($fechas);$i++): ?>
                    <tr>
                        <td><?= e($fechas[$i]) ?></td>
                        <td><?= e($medidas[$i]) ?></td>
                        <td><?= e($controles[$i]) ?></td>
                        <td><?= e($responsables[$i]) ?></td>
                    </tr>
                <?php endfor; ?>
                </tbody>
            </table>
            <?php else: ?><div class="no-photos">No hay acciones registradas</div><?php endif; ?>
        </div>
    </div>

    <!-- 11. RESULTADO DEL ANÁLISIS -->
    <div class="section">
        <div class="section-header"><span class="section-number">11</span><span class="section-title">Resultado del Análisis del Accidente de Trabajo</span></div>
        <div class="section-body">
            <label class="field-label" style="display:block;margin-bottom:8px;">Conclusiones y Recomendaciones del Equipo Investigador</label>
            <div class="result-box"><?= !empty(trim($post['resultado_analisis'] ?? '')) ? nl2br(e($post['resultado_analisis'])) : '<span style="color:var(--slate-lt);font-style:italic;">Sin conclusiones registradas</span>' ?></div>
        </div>
    </div>

    <!-- 12. EQUIPO INVESTIGADOR -->
<div class="section">
    <div class="section-header">
        <span class="section-number">12</span><span class="section-title">Equipo Investigador</span>
        <button type="button" class="no-print btn-corregir-equipo" onclick="abrirCorreccionEquipo()" title="Corregir datos del equipo">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Corregir
        </button>
    </div>
    <div class="section-body">
        <?php if(!empty($post['equipo_nombre'])): ?>
        <table class="data-table" id="tablaEquipo">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Cargo</th>
                    <th>Tipo ID</th>
                    <th>Número</th>
                    <th>Firma</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $nombres = $post['equipo_nombre'];
            $apellidos = $post['equipo_apellido'];
            $cargos = $post['equipo_cargo'];
            $tipos = $post['equipo_tipo_id'];
            $numeros = $post['equipo_numero'];
            $firmas = $post['equipo_firma'] ?? [];
            $n = count($nombres);
            for($i=0;$i<$n;$i++): ?>
                <tr>
                    <td><?= e($nombres[$i]) ?></td>
                    <td><?= e($apellidos[$i]) ?></td>
                    <td><?= e($cargos[$i]) ?></td>
                    <td><?= e($tipos[$i]) ?></td>
                    <td><?= e($numeros[$i]) ?></td>
                    <td>
                        <?php if(!empty($firmas[$i])): ?>
                            <img src="<?= e($firmas[$i]) ?>" alt="Firma" style="max-height:40px; max-width:120px; border:1px solid #bbb; border-radius:4px; background:#fafafa; display:block;">
                            <button type="button" class="no-print" onclick="abrirFirma(<?= $i ?>, '<?= e($firmas[$i]) ?>')" style="font-size:8pt; padding:2px 6px; margin-top:4px; cursor:pointer; border:1px solid #ccc; border-radius:4px; background:#f0f0f0;">Cambiar firma</button>
                        <?php else: ?>
                            <span style="color:#bbb;font-style:italic; display:block;">Sin firma</span>
                            <button type="button" class="no-print" onclick="abrirFirma(<?= $i ?>, '')" style="font-size:8pt; padding:4px 8px; margin-top:4px; cursor:pointer; background:var(--navy); color:white; border:none; border-radius:4px;">Firmar</button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endfor; ?>
            </tbody>
        </table>
        <?php else: ?><div class="no-photos">No hay equipo registrado</div><?php endif; ?>
    </div>
</div>

<!-- MODAL CORRECCIÓN EQUIPO INVESTIGADOR -->
<div id="equipoModal" class="no-print" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(13,31,60,0.55); z-index:9998; align-items:center; justify-content:center; backdrop-filter:blur(3px);">
  <div style="background:#fff; padding:0; border-radius:12px; box-shadow:0 8px 40px rgba(13,31,60,0.25); position:relative; width:90%; max-width:860px; max-height:85vh; display:flex; flex-direction:column; overflow:hidden;">
    <!-- Header del modal -->
    <div style="background:var(--navy); color:white; padding:16px 22px; display:flex; align-items:center; gap:10px;">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
      <span style="font-family:'Fraunces',serif; font-size:13pt; font-weight:600;">Corrección — Equipo Investigador</span>
      <button type="button" onclick="cerrarCorreccionEquipo()" style="margin-left:auto; background:none; border:none; color:white; cursor:pointer; font-size:18pt; line-height:1;">&times;</button>
    </div>
    <!-- Body del modal -->
    <div style="padding:20px 22px; overflow-y:auto; flex:1;">
      <p style="font-size:8.5pt; color:var(--slate); margin-bottom:14px;">Modifique los datos que necesite corregir, agregue nuevas filas o elimine las innecesarias. Las firmas existentes se conservarán.</p>
      <table class="data-table" id="tablaEquipoEdit" style="margin:0;">
        <thead>
          <tr>
            <th>Nombre</th>
            <th>Apellido</th>
            <th>Cargo</th>
            <th>Tipo ID</th>
            <th>Número ID</th>
            <th style="width:50px;"></th>
          </tr>
        </thead>
        <tbody id="equipoEditBody">
          <!-- Filas se generan por JS -->
        </tbody>
      </table>
      <button type="button" id="btnAgregarFila" onclick="agregarFilaEquipo()" style="margin-top:12px; display:inline-flex; align-items:center; gap:6px; padding:7px 14px; font-size:8.5pt; font-weight:600; background:var(--fog); border:1px dashed var(--slate-lt); border-radius:6px; color:var(--navy); cursor:pointer; transition:all .2s;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Agregar integrante
      </button>
    </div>
    <!-- Footer del modal -->
    <div style="padding:14px 22px; border-top:1px solid #e4ecf4; display:flex; align-items:center; justify-content:flex-end; gap:10px; background:#f8fafd;">
      <span id="equipoSaveStatus" style="font-size:8pt; color:var(--slate); margin-right:auto;"></span>
      <button type="button" onclick="cerrarCorreccionEquipo()" style="padding:8px 18px; font-size:9pt; cursor:pointer; border:1px solid #ccc; border-radius:6px; background:#fff; color:var(--ink);">Cancelar</button>
      <button type="button" onclick="guardarCorreccionEquipo()" id="btnGuardarEquipo" style="padding:8px 22px; font-size:9pt; font-weight:600; cursor:pointer; background:var(--navy); color:white; border:none; border-radius:6px; transition:all .2s;">
        Guardar cambios
      </button>
    </div>
  </div>
</div> 

    <!-- 13. CAMPOS ADICIONALES NO CLASIFICADOS -->
    <?php
    $clasificados = ['inv_','emp_','trab_','acc_','desc_','testigo_','carac_','plan_','equipo_','porques_','causa_','resultado_analisis'];
    $otros = [];
    foreach($post as $k=>$v) {
        $found = false;
        foreach($clasificados as $pref) if(strpos($k,$pref)===0) $found=true;
        if(!$found) $otros[$k]=$v;
    }
    if($otros):
    ?>
    <div class="section">
        <div class="section-header"><span class="section-number">13</span><span class="section-title">Otros Campos</span></div>
        <div class="section-body">
            <div class="field-grid">
                <?php foreach($otros as $k=>$v): ?>
                <div class="field"><label class="field-label"><?= label($k) ?></label><?= val($v) ?></div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- FOOTER -->
    <div class="doc-footer">
        <div class="footer-brand">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M8 12l2 2 4-4"/></svg>
            Sistema de Gestión HSEQ — Documento generado electrónicamente
        </div>
        <span><?= e($post['inv_codigo'] ?? 'FOR-HSEQ-001') ?> · Versión 2.0 · Emisión: <?= date('d/m/Y') ?></span>
    </div>
</div>

<!-- MODAL DE FIRMA -->
<div id="signatureModal" class="no-print" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
  <div style="background:#fff; padding:24px; border-radius:8px; box-shadow:0 2px 16px #0002; position:relative; min-width:320px;">
    <div style="font-weight:600; margin-bottom:8px; color:var(--navy);">Dibuje la firma</div>
    <canvas id="signatureCanvas" width="320" height="150" style="border:1px solid #888; border-radius:4px; background:#fafafa; cursor:crosshair;"></canvas>
    <div style="margin-top:10px; display:flex; gap:8px;">
      <button type="button" id="clearSignature" style="padding:6px 12px; cursor:pointer; border:1px solid #ccc; border-radius:4px; background:#f0f0f0;">Limpiar</button>
      <button type="button" id="saveSignature" style="padding:6px 12px; background:var(--navy); color:white; border:none; border-radius:4px; cursor:pointer;">Guardar</button>
      <button type="button" id="closeSignature" style="padding:6px 12px; cursor:pointer; border:1px solid #ccc; border-radius:4px; background:#f0f0f0;">Cancelar</button>
    </div>
  </div>
</div>

<script>
let currentFirmaIndex = null;
const modal = document.getElementById('signatureModal');
const canvas = document.getElementById('signatureCanvas');
const ctx = canvas.getContext('2d');
let drawing = false, lastX = 0, lastY = 0;

function abrirFirma(index, existingData = '') {
    currentFirmaIndex = index;
    ctx.clearRect(0,0,canvas.width,canvas.height);
    if (existingData) {
        const img = new window.Image();
        img.onload = () => ctx.drawImage(img,0,0,canvas.width,canvas.height);
        img.src = existingData;
    }
    modal.style.display = 'flex';
}

function closeSignatureModal() {
    modal.style.display = 'none';
}

canvas.addEventListener('mousedown', e => { drawing = true; [lastX, lastY] = [e.offsetX, e.offsetY]; });
canvas.addEventListener('mousemove', e => {
    if (!drawing) return;
    ctx.lineWidth = 2; ctx.lineCap = 'round'; ctx.strokeStyle = '#222';
    ctx.beginPath(); ctx.moveTo(lastX, lastY); ctx.lineTo(e.offsetX, e.offsetY); ctx.stroke();
    [lastX, lastY] = [e.offsetX, e.offsetY];
});
canvas.addEventListener('mouseup', () => drawing = false);
canvas.addEventListener('mouseleave', () => drawing = false);

// Soporte táctil (móviles/tablets)
canvas.addEventListener('touchstart', e => {
    e.preventDefault();
    drawing = true;
    const rect = canvas.getBoundingClientRect();
    const touch = e.touches[0];
    [lastX, lastY] = [touch.clientX - rect.left, touch.clientY - rect.top];
});
canvas.addEventListener('touchmove', e => {
    e.preventDefault();
    if (!drawing) return;
    const rect = canvas.getBoundingClientRect();
    const touch = e.touches[0];
    const offsetX = touch.clientX - rect.left;
    const offsetY = touch.clientY - rect.top;
    ctx.lineWidth = 2; ctx.lineCap = 'round'; ctx.strokeStyle = '#222';
    ctx.beginPath(); ctx.moveTo(lastX, lastY); ctx.lineTo(offsetX, offsetY); ctx.stroke();
    [lastX, lastY] = [offsetX, offsetY];
});
canvas.addEventListener('touchend', () => drawing = false);

document.getElementById('clearSignature').onclick = () => ctx.clearRect(0,0,canvas.width,canvas.height);
document.getElementById('closeSignature').onclick = closeSignatureModal;

document.getElementById('saveSignature').onclick = () => {
    const dataUrl = canvas.toDataURL();
    const formData = new FormData();
    formData.append('firma_index', currentFirmaIndex);
    formData.append('firma_data', dataUrl);

    document.getElementById('saveSignature').textContent = 'Guardando...';
    document.getElementById('saveSignature').disabled = true;

    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if(data.status === 'ok') {
            window.location.reload(); // Recargar para mostrar la nueva firma
        } else {
            alert('Error al guardar la firma');
            document.getElementById('saveSignature').textContent = 'Guardar';
            document.getElementById('saveSignature').disabled = false;
        }
    })
    .catch(err => {
        console.error(err);
        alert('Error de conexión');
        document.getElementById('saveSignature').textContent = 'Guardar';
        document.getElementById('saveSignature').disabled = false;
    });
};

// ── Corrección Equipo Investigador ──
const equipoModal = document.getElementById('equipoModal');
const equipoEditBody = document.getElementById('equipoEditBody');
const tiposIdOptions = ['CC','Cedula de extranjeria','Pasaporte','Permiso por proteccion temporal','Salvo conducto de permanencia'];

// Datos actuales del equipo (inyectados desde PHP)
const equipoData = <?= json_encode([
    'nombre'  => $post['equipo_nombre']  ?? [],
    'apellido'=> $post['equipo_apellido'] ?? [],
    'cargo'   => $post['equipo_cargo']   ?? [],
    'tipo_id' => $post['equipo_tipo_id'] ?? [],
    'numero'  => $post['equipo_numero']  ?? [],
], JSON_UNESCAPED_UNICODE) ?>;

function crearFilaEquipo(nombre='', apellido='', cargo='', tipoId='', numero='') {
    const tr = document.createElement('tr');
    tr.style.animation = 'fadeIn .25s ease';
    const optsTipo = tiposIdOptions.map(t =>
        `<option value="${t}"${t===tipoId?' selected':''}>${t}</option>`
    ).join('');
    tr.innerHTML = `
        <td><input type="text" name="eq_nombre" value="${nombre.replace(/"/g,'&quot;')}" placeholder="Nombre"></td>
        <td><input type="text" name="eq_apellido" value="${apellido.replace(/"/g,'&quot;')}" placeholder="Apellido"></td>
        <td><input type="text" name="eq_cargo" value="${cargo.replace(/"/g,'&quot;')}" placeholder="Cargo"></td>
        <td><select name="eq_tipo_id">${optsTipo}</select></td>
        <td><input type="text" name="eq_numero" value="${numero.replace(/"/g,'&quot;')}" placeholder="Número"></td>
        <td>
            <button type="button" class="btn-eliminar-fila" onclick="eliminarFilaEquipo(this)" title="Eliminar fila">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </td>
    `;
    return tr;
}

function abrirCorreccionEquipo() {
    equipoEditBody.innerHTML = '';
    const n = equipoData.nombre.length;
    if (n === 0) {
        equipoEditBody.appendChild(crearFilaEquipo());
    } else {
        for (let i = 0; i < n; i++) {
            equipoEditBody.appendChild(crearFilaEquipo(
                equipoData.nombre[i] || '',
                equipoData.apellido[i] || '',
                equipoData.cargo[i] || '',
                equipoData.tipo_id[i] || 'CC',
                equipoData.numero[i] || ''
            ));
        }
    }
    document.getElementById('equipoSaveStatus').textContent = '';
    equipoModal.style.display = 'flex';
}

function cerrarCorreccionEquipo() {
    equipoModal.style.display = 'none';
}

function agregarFilaEquipo() {
    equipoEditBody.appendChild(crearFilaEquipo());
    equipoEditBody.lastElementChild.querySelector('input').focus();
}

function eliminarFilaEquipo(btn) {
    const tbody = btn.closest('tbody');
    if (tbody.children.length <= 1) {
        alert('Debe haber al menos un integrante.');
        return;
    }
    btn.closest('tr').remove();
}

function guardarCorreccionEquipo() {
    const rows = equipoEditBody.querySelectorAll('tr');
    const fd = new FormData();
    fd.append('accion_equipo', 'corregir');
    rows.forEach(tr => {
        fd.append('eq_nombre[]',   tr.querySelector('[name="eq_nombre"]').value.trim());
        fd.append('eq_apellido[]', tr.querySelector('[name="eq_apellido"]').value.trim());
        fd.append('eq_cargo[]',    tr.querySelector('[name="eq_cargo"]').value.trim());
        fd.append('eq_tipo_id[]',  tr.querySelector('[name="eq_tipo_id"]').value);
        fd.append('eq_numero[]',   tr.querySelector('[name="eq_numero"]').value.trim());
    });

    const btn = document.getElementById('btnGuardarEquipo');
    const status = document.getElementById('equipoSaveStatus');
    btn.textContent = 'Guardando...';
    btn.disabled = true;
    status.textContent = '';

    fetch(window.location.href, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'ok') {
                status.textContent = '✓ Cambios guardados correctamente';
                status.style.color = '#27ae60';
                setTimeout(() => window.location.reload(), 600);
            } else {
                status.textContent = '✗ Error al guardar';
                status.style.color = '#c0392b';
                btn.textContent = 'Guardar cambios';
                btn.disabled = false;
            }
        })
        .catch(err => {
            console.error(err);
            status.textContent = '✗ Error de conexión';
            status.style.color = '#c0392b';
            btn.textContent = 'Guardar cambios';
            btn.disabled = false;
        });
}

// Cerrar modal con Escape
document.addEventListener('keydown', e => {
    if (e.key === 'Escape' && equipoModal.style.display === 'flex') cerrarCorreccionEquipo();
});
</script>
</body>
</html>