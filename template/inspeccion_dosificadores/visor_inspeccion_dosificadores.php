<?php
require '../sesion.php';

if (!isset($_SESSION['nombre']) || empty($_SESSION['sede'])) {
    header('Location: ../../index.php');
    exit;
}

$sede        = $_SESSION['sede'];
$target_file = $_GET['file'] ?? '';
$from        = $_GET['from'] ?? 'calidad';

if (empty($target_file)) {
    die("Archivo no especificado. Vuelva a la Galería.");
}

$ruta_json = "../../archivos/generados/inspeccion_dosificadores/"
           . preg_replace('/[^A-Za-z0-9_-]/', '', $sede) . "/"
           . basename($target_file);

if (!file_exists($ruta_json)) {
    die("El archivo no existe o fue eliminado.");
}

$contenido = json_decode(file_get_contents($ruta_json), true);

if (empty($contenido)) {
    die("El archivo está vacío o dañado.");
}

// Cada archivo contiene exactamente una inspección. Se mantiene compatibilidad
// de lectura por si quedara algún archivo del formato antiguo (array con
// varios registros mezclados).
$registros = array_is_list($contenido) ? $contenido : [$contenido];

$doc = $registros[0]['datos'] ?? [];
$titulo_visor = trim(($doc['dosificador'] ?? 'Dosificador') . ' — ' . ($doc['fecha'] ?? ''));

function ie($v) { return $v !== null && $v !== '' ? htmlspecialchars($v) : '—'; }

function cumpleBadge($val) {
    if ($val === 'CUMPLE')    return '<span style="color:#166534;font-weight:700;">CUMPLE</span>';
    if ($val === 'NO CUMPLE') return '<span style="color:#991B1B;font-weight:700;">NO CUMPLE</span>';
    if ($val === 'N/A')       return '<span style="color:#92400E;font-weight:700;">N/A</span>';
    return '<span style="color:#9CA3AF;">—</span>';
}

// Solo el primer registro es editable (formato estándar: un archivo = una inspección).
// Nota: en celdas editables NO se usa el placeholder "—" de ie() — el modo corrección
// reenvía el texto de todas las celdas al guardar, y el guion largo se guardaría
// como valor literal en campos que en realidad están vacíos (rompiendo, por ejemplo,
// el parseo de fecha en el backend).
function editableTd($idx, $field, $value, $class, $colspan = null) {
    $cls = $class;
    $attrs = $colspan ? ' colspan="' . (int)$colspan . '"' : '';
    if ($idx === 0) {
        $cls .= ' editable-field';
        $attrs .= ' id="field-' . htmlspecialchars($field) . '" data-field="' . htmlspecialchars($field) . '"';
        $texto = $value !== null && $value !== '' ? htmlspecialchars($value) : '';
    } else {
        $texto = ie($value);
    }
    return '<td class="' . $cls . '"' . $attrs . '>' . $texto . '</td>';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visor - Inspección de Dosificadores | <?= htmlspecialchars($titulo_visor) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --navy:   #0F172A;
            --blue:   #003366;
            --white:  #FFFFFF;
            --border: #000000;
            --accent: #00F0FF;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Roboto', sans-serif;
            background: #E2E8F0;
            padding: 20px;
            color: #000;
        }

        .action-bar {
            max-width: 99%;
            margin: 0 auto 20px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--navy);
            padding: 14px 24px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            gap: 10px;
            flex-wrap: wrap;
        }

        .action-bar .left { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }

        .btn-back {
            background: transparent;
            border: 1px solid var(--accent);
            color: var(--accent);
            text-decoration: none;
            padding: 9px 18px;
            border-radius: 4px;
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.2s;
        }
        .btn-back:hover { background: rgba(0,240,255,0.1); }

        .doc-label { color: #E2E8F0; font-size: 13px; font-weight: 600; padding: 0 8px; border-left: 2px solid var(--accent); }

        .btn-print {
            background: #10B981;
            color: #fff;
            border: none;
            padding: 9px 18px;
            border-radius: 4px;
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-print:hover { background: #059669; }

        .page-wrap { max-width: 99%; margin: 0 auto; }

        .registro-block {
            background: var(--white);
            padding: 20px 24px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin-bottom: 24px;
        }

        table { width: 100%; border-collapse: collapse; }
        td, th { border: 1px solid var(--border); padding: 6px 8px; vertical-align: middle; }

        .header-table td { border: 1px solid #000; vertical-align: middle; padding: 8px 10px; }
        .header-title-main { font-size: 11pt; font-weight: 700; }
        .header-title-doc  { font-size: 10.5pt; font-style: italic; margin-top: 3px; }

        .iso-meta { width: 100%; border-collapse: collapse; height: 100%; }
        .iso-meta td { border: 0; border-bottom: 1px solid #000; padding: 3px 8px; font-size: 8pt; text-align: left; }
        .iso-meta tr:last-child td { border-bottom: 0; }
        .iso-meta td:first-child { font-weight: 700; border-right: 1px solid #000; width: 45%; }

        .sec-title {
            background: var(--blue); color: #fff;
            font-size: 9pt; font-weight: 700; text-transform: uppercase;
            padding: 5px 8px; letter-spacing: 0.5px;
        }

        .spec-label {
            background: #D9E1F2;
            font-size: 8.5pt; font-weight: 700;
            width: 22%;
        }
        .spec-val { font-size: 9pt; width: 11.3%; }

        .pruebas-hdr { background: #1E3A5F; color: #fff; font-size: 8pt; font-weight: 700; text-align: center; }
        .pruebas-num { background: #F1F5F9; font-weight: 700; text-align: center; width: 8%; }
        .pruebas-val { text-align: center; width: 12%; }

        .criterio-label { background: #D9E1F2; font-weight: 700; font-size: 8.5pt; width: 18%; }
        .criterio-desc { font-size: 8pt; text-align: left; padding: 8px 10px; }

        .resumen-label { background: #F1F5F9; font-weight: 700; font-size: 8.5pt; }
        .resumen-val { font-size: 9pt; text-align: center; }

        .plan-row td, .obs-row td { font-size: 8.5pt; text-align: left; padding: 8px 10px; }

        .meta-row { font-size: 8pt; color: #444; padding: 6px 10px; text-align: left; }

        .separator { height: 12px; }

        @media print {
            body        { background: #fff; padding: 0; }
            .action-bar { display: none !important; }
            .registro-block { box-shadow: none; padding: 10px; margin-bottom: 12px; }
            @page { size: A4; margin: 8mm; }
        }

        /* MODO CORRECCIÓN */
        .btn-correct { background: #1E293B; color: #fff; border: none; padding: 9px 18px; border-radius: 4px; font-weight: 700; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; cursor: pointer; transition: background 0.2s; }
        .btn-correct:hover { background: #334155; }
        .btn-correct.active { background: #E11D48; }
        .btn-save { background: #10B981; color: #fff; border: none; padding: 9px 18px; border-radius: 4px; font-weight: 700; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; cursor: pointer; display: none; transition: background 0.2s; }
        .btn-save:hover { background: #059669; }

        .editable-field { transition: all 0.2s; border: 1px solid transparent; }
        .edit-mode .editable-field { background: #FEF08A; border: 1px dashed #CA8A04; cursor: text; }
        .edit-mode .editable-field:focus { background: #fff; outline: 2px solid #CA8A04; }
    </style>
</head>
<body>

<div class="action-bar">
    <div class="left">
        <a href="rev_inspeccion_dosificadores.php?from=<?= urlencode($from) ?>" class="btn-back">← Volver al Listado</a>
        <span class="doc-label"><?= htmlspecialchars($titulo_visor) ?></span>
    </div>
    <div style="display:flex; gap:10px;">
        <button id="btn-correct" class="btn-correct" onclick="toggleEditMode()">✎ CORRECCIÓN</button>
        <button id="btn-save" class="btn-save" onclick="guardarCambios()">GUARDAR CAMBIOS</button>
        <button class="btn-print" onclick="window.print()">🖨️ IMPRIMIR / PDF</button>
    </div>
</div>

<div class="page-wrap" id="document_content">
<?php foreach ($registros as $idx => $reg):
    $d = $reg['datos'];
    $fecha_fmt = !empty($d['fecha']) ? date('d/m/Y', strtotime($d['fecha'])) : '—';
?>

<div class="registro-block">

    <!-- ENCABEZADO INSTITUCIONAL -->
    <table class="header-table" style="margin-bottom:0;">
        <tr>
            <td style="width:18%; text-align:center; padding:10px;">
                <img src="/img/logo_empresa.jpeg" alt="Logo MAS" style="max-height:60px; max-width:100%; object-fit:contain;">
            </td>
            <td style="width:60%; text-align:center; padding:10px;">
                <div class="header-title-main">PPR Metrología</div>
                <div class="header-title-doc">"Inspección del Funcionamiento de los Dosificadores"</div>
            </td>
            <td style="width:22%; padding:0; vertical-align:top;">
                <table class="iso-meta" style="height:100%;">
                    <tr><td>Código:</td><td>GM-MM-ME-MR-FO-009C</td></tr>
                    <tr><td>Versión:</td><td>3</td></tr>
                    <tr><td>Fecha:</td><td>1/07/2022</td></tr>
                    <tr><td>Página:</td><td>1 de 2</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="meta-row" style="border:1px solid #000; border-top:0;">
        Sede: <strong><?= htmlspecialchars($reg['sede_sys'] ?? $sede) ?></strong> &nbsp;|&nbsp;
        Registrado: <?= htmlspecialchars($reg['timestamp'] ?? '—') ?> &nbsp;|&nbsp;
        ID: <?= htmlspecialchars($reg['id_registro'] ?? '—') ?>
    </div>

    <!-- 1. ESPECIFICACIONES -->
    <table style="margin-top:10px;">
        <tr><td class="sec-title" colspan="6">1. Especificaciones</td></tr>
        <tr>
            <td class="spec-label">Fecha:</td><?= editableTd($idx, 'fecha', $fecha_fmt, 'spec-val') ?>
            <td class="spec-label">Dosificador:</td><?= editableTd($idx, 'dosificador', $d['dosificador'] ?? null, 'spec-val') ?>
            <td class="spec-label">Microingrediente:</td><?= editableTd($idx, 'microingrediente', $d['microingrediente'] ?? null, 'spec-val') ?>
        </tr>
        <tr>
            <td class="spec-label">Cantidad Bulto 50kg:</td><?= editableTd($idx, 'cantidad_bulto_50kg', $d['cantidad_bulto_50kg'] ?? null, 'spec-val') ?>
            <td class="spec-label">Carga Trigo:</td><?= editableTd($idx, 'carga_trigo', $d['carga_trigo'] ?? null, 'spec-val') ?>
            <td class="spec-label">Extracción (%):</td><?= editableTd($idx, 'extraccion_pct', $d['extraccion_pct'] ?? null, 'spec-val') ?>
        </tr>
        <tr>
            <td class="spec-label">Bultos por hora:</td><?= editableTd($idx, 'bultos_por_hora', $d['bultos_por_hora'] ?? null, 'spec-val') ?>
            <td class="spec-label">Microingrediente por minuto:</td><?= editableTd($idx, 'micro_por_minuto', $d['micro_por_minuto'] ?? null, 'spec-val') ?>
            <td class="spec-label">Microingrediente por hora:</td><?= editableTd($idx, 'micro_por_hora', $d['micro_por_hora'] ?? null, 'spec-val') ?>
        </tr>
        <tr>
            <td class="spec-label">Micro. por min. límite inferior:</td><?= editableTd($idx, 'micro_min_limite_inferior', $d['micro_min_limite_inferior'] ?? null, 'spec-val') ?>
            <td class="spec-label">Micro. por min. límite superior:</td><?= editableTd($idx, 'micro_min_limite_superior', $d['micro_min_limite_superior'] ?? null, 'spec-val') ?>
            <td class="spec-label">Micro. por hora límite inferior:</td><?= editableTd($idx, 'micro_hora_limite_inferior', $d['micro_hora_limite_inferior'] ?? null, 'spec-val') ?>
        </tr>
        <tr>
            <td class="spec-label">Porcentaje dosificador:</td><?= editableTd($idx, 'porcentaje_dosificador', $d['porcentaje_dosificador'] ?? null, 'spec-val') ?>
            <td class="spec-label">Frecuencia dosificador:</td><?= editableTd($idx, 'frecuencia_dosificador', $d['frecuencia_dosificador'] ?? null, 'spec-val') ?>
            <td class="spec-label">Micro. por hora límite superior:</td><?= editableTd($idx, 'micro_hora_limite_superior', $d['micro_hora_limite_superior'] ?? null, 'spec-val') ?>
        </tr>
        <tr>
            <td class="spec-label">Inspeccionado por:</td><?= editableTd($idx, 'inspeccionado_por', $d['inspeccionado_por'] ?? null, 'spec-val', 3) ?>
            <td class="spec-label">Verificado por:</td><?= editableTd($idx, 'verificado_por', $d['verificado_por'] ?? null, 'spec-val') ?>
        </tr>
    </table>

    <!-- 2. RESULTADOS -->
    <table style="margin-top:0;">
        <tr><td class="sec-title" colspan="4">2. Resultados</td></tr>
        <tr>
            <th class="pruebas-hdr" colspan="4">Gramos por Minuto</th>
        </tr>
        <tr>
            <th class="pruebas-hdr">N° Prueba</th><th class="pruebas-hdr">Gramos</th>
            <th class="pruebas-hdr">N° Prueba</th><th class="pruebas-hdr">Gramos</th>
        </tr>
        <?php for ($i = 1; $i <= 5; $i++): $j = $i + 5; ?>
        <tr>
            <td class="pruebas-num"><?= $i ?></td><?= editableTd($idx, 'gramos_prueba_' . $i, $d['gramos_prueba_' . $i] ?? null, 'pruebas-val') ?>
            <td class="pruebas-num"><?= $j ?></td><?= editableTd($idx, 'gramos_prueba_' . $j, $d['gramos_prueba_' . $j] ?? null, 'pruebas-val') ?>
        </tr>
        <?php endfor; ?>
        <tr>
            <td class="resumen-label">Promedio Min:</td><?= editableTd($idx, 'promedio_min', $d['promedio_min'] ?? null, 'resumen-val') ?>
            <td class="resumen-label">Gramos hora:</td><?= editableTd($idx, 'gramos_hora', $d['gramos_hora'] ?? null, 'resumen-val') ?>
        </tr>
        <tr>
            <td class="resumen-label">¿Cumple?</td>
            <td class="resumen-val<?= $idx === 0 ? ' editable-cumple' : '' ?>" colspan="3"
                <?= $idx === 0 ? 'id="field-cumple" data-field="cumple" data-current="' . htmlspecialchars($d['cumple'] ?? '') . '"' : '' ?>><?= cumpleBadge($d['cumple'] ?? null) ?></td>
        </tr>
    </table>

    <!-- CRITERIOS DE ACEPTACIÓN -->
    <table style="margin-top:0;">
        <tr><td class="sec-title" colspan="2">Criterios de Aceptación</td></tr>
        <tr>
            <td class="criterio-label">Fortificación colombiana:</td>
            <td class="criterio-desc">El promedio de gramos por minuto y los gramos hora no deben estar por debajo de su respectiva especificación.</td>
        </tr>
        <tr>
            <td class="criterio-label">Fortificación ecuatoriana:</td>
            <td class="criterio-desc">El promedio de gramos por minuto y los gramos hora pueden variar ± 4% respecto a su especificación.</td>
        </tr>
        <tr>
            <td class="criterio-label">Mejoradores de harina:</td>
            <td class="criterio-desc">El promedio de gramos por minuto y los gramos hora no deben estar por debajo de su especificación y máximo 2% por encima.</td>
        </tr>
    </table>

    <!-- PLAN DE ACCIÓN Y OBSERVACIONES -->
    <table style="margin-top:0;">
        <tr class="plan-row">
            <td style="width:22%; font-weight:700; background:#D9E1F2;">Plan de Acción en caso de NC:</td>
            <td>Ajustar microdosificador y verificar nuevamente el muestreo por minuto.</td>
        </tr>
        <tr class="obs-row">
            <td style="width:22%; font-weight:700; background:#D9E1F2;">Observaciones:</td>
            <td<?= $idx === 0 ? ' class="editable-field" id="field-observaciones" data-field="observaciones"' : '' ?>><?php
                $obs = $d['observaciones'] ?? null;
                echo $idx === 0
                    ? nl2br($obs !== null && $obs !== '' ? htmlspecialchars($obs) : '')
                    : nl2br(ie($obs));
            ?></td>
        </tr>
    </table>

</div>

<?php if ($idx < count($registros) - 1): ?>
    <div class="separator"></div>
<?php endif; ?>

<?php endforeach; ?>
</div>

<script>
    let isEditMode = false;

    function toggleEditMode() {
        isEditMode = !isEditMode;
        const btn = document.getElementById('btn-correct');
        const btnSave = document.getElementById('btn-save');
        const documentContent = document.getElementById('document_content');
        const cumpleCell = document.getElementById('field-cumple');

        if (isEditMode) {
            btn.classList.add('active');
            btn.innerText = '✕ CANCELAR';
            btnSave.style.display = 'inline-block';
            documentContent.classList.add('edit-mode');

            document.querySelectorAll('.editable-field').forEach(el => { el.contentEditable = true; });

            if (cumpleCell) {
                const current = cumpleCell.dataset.current || '';
                const opciones = ['', 'CUMPLE', 'NO CUMPLE', 'N/A'];
                cumpleCell.innerHTML = '<select id="select-cumple" style="font-size:9pt; padding:4px; border:1px dashed #CA8A04; background:#FEF08A;">'
                    + opciones.map(op => `<option value="${op}" ${op === current ? 'selected' : ''}>${op === '' ? '—' : op}</option>`).join('')
                    + '</select>';
            }
        } else {
            btn.classList.remove('active');
            btn.innerText = '✎ CORRECCIÓN';
            btnSave.style.display = 'none';
            documentContent.classList.remove('edit-mode');
            location.reload();
        }
    }

    async function guardarCambios() {
        const updates = {};
        document.querySelectorAll('.editable-field[data-field]').forEach(el => {
            updates[el.dataset.field] = el.innerText.trim();
        });

        const cumpleSelect = document.getElementById('select-cumple');
        if (cumpleSelect) updates['cumple'] = cumpleSelect.value;

        try {
            const resp = await fetch('corregir_inspeccion_dosificadores.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ file: <?= json_encode(basename($target_file)) ?>, updates })
            });
            const result = await resp.json();

            if (result.status === 'success') {
                Swal.fire({ title: '¡Guardado!', text: 'Los cambios se han aplicado correctamente.', icon: 'success', timer: 1500, showConfirmButton: false })
                    .then(() => location.reload());
            } else {
                Swal.fire('Error', result.message || 'No se pudieron guardar los cambios.', 'error');
            }
        } catch (e) {
            Swal.fire('Error', 'Hubo un problema de conexión al guardar.', 'error');
        }
    }
</script>

</body>
</html>
