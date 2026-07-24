<?php
require '../../conection.php';
require '../../sesion.php';
verificarAutenticacion();

$sede = $_SESSION['sede'];
$nombre_usuario = $_SESSION['nombre'];
$file = $_GET['file'] ?? '';

if (empty($file) || !preg_match('/^\d{4}-\d{2}\.json$/', $file)) {
    die("Archivo no válido");
}

$file_path = "../../../archivos/generados/PNC/" . $sede . "/" . $file;
if (!file_exists($file_path)) {
    die("El registro no existe");
}

$json_data = file_get_contents($file_path);
$registros = json_decode($json_data, true) ?: [];

// Mes textual para PDF
$mes_num = substr($file, 5, 2);
$año = substr($file, 0, 4);
$meses = ['01'=>'Enero','02'=>'Febrero','03'=>'Marzo','04'=>'Abril','05'=>'Mayo','06'=>'Junio','07'=>'Julio','08'=>'Agosto','09'=>'Septiembre','10'=>'Octubre','11'=>'Noviembre','12'=>'Diciembre'];
$mes_texto = strtoupper($meses[$mes_num] ?? '') . " " . $año;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visor PNC - <?= htmlspecialchars($file) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <style>
        body {
            font-family: 'Barlow', sans-serif;
            background-color: #f0f2f5;
            color: #111;
            margin: 0; padding: 20px;
        }

        .action-bar {
            background: #141620;
            padding: 15px 30px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .btn {
            background: #00f2ff;
            color: #0a0b10;
            border: none;
            padding: 10px 20px;
            font-weight: bold;
            text-transform: uppercase;
            border-radius: 4px;
            cursor: pointer;
            font-family: inherit;
        }
        .btn-back { background: #2d324a; color: #fff; }

        .btn-verificar {
            background: rgba(112,0,255,0.1);
            color: #c682ff;
            border: 1px dashed #c682ff;
            padding: 4px 8px;
            font-size: 10px;
            cursor: pointer;
            border-radius: 2px;
            font-family: 'Space Mono', monospace;
            transition: all 0.2s;
        }
        .btn-verificar:hover {
            background: rgba(112,0,255,0.4);
            color: white;
        }
        
        .btn-editar {
            background: rgba(0,242,255,0.1);
            color: #00f2ff;
            border: 1px dashed #00f2ff;
            padding: 4px 8px;
            font-size: 10px;
            cursor: pointer;
            border-radius: 2px;
            font-family: 'Space Mono', monospace;
            transition: all 0.2s;
            margin-top: 5px;
            display: block;
            width: 100%;
        }
        .btn-editar:hover {
            background: rgba(0,242,255,0.4);
            color: white;
        }

        .report-wrapper {
            max-width: 1300px;
            margin: auto;
        }

        /* Estilo Documental Formal para Exportar */
        .page-container {
            background: white;
            padding: 40px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            border-radius: 4px;
        }

        .doc-header {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .doc-header td {
            border: 1px solid #000;
            padding: 10px;
            text-align: center;
            vertical-align: middle;
        }
        .logo-cell { width: 20%; }
        .logo-cell img { max-width: 120px; height: auto; }
        .title-cell {
            font-weight: bold;
            font-size: 16px;
            width: 50%;
        }
        .meta-cell { font-size: 12px; width: 30%; text-align: left; padding-left: 15px !important; }
        .meta-cell strong { display: inline-block; width: 70px; }

        .info-row {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }
        .data-table th, .data-table td {
            border: 1px solid #000;
            padding: 6px 4px;
            text-align: center;
            vertical-align: middle;
        }
        .data-table th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-transform: uppercase;
        }
        .firma-img {
            max-height: 40px;
            width: auto;
            display: block;
            margin: auto;
        }
    </style>
</head>
<body>

<div class="action-bar disable-print">
    <button class="btn btn-back" onclick="history.back()">Volver a Galería</button>
    <button class="btn" onclick="exportPDF()">Exportar PDF</button>
</div>

<div class="report-wrapper" id="divToPrint">
    <div class="page-container">
        
        <!-- ENCABEZADO INSTITUCIONAL -->
        <table class="doc-header">
            <tr>
                <td class="logo-cell" rowspan="4">
                    <img src="/archivos/formularios/logomas.png" alt="Logo OMAS" onerror="this.src=''; this.alt='[LOGO OMAS]'">
                </td>
                <td class="title-cell" rowspan="4">CONTROL DE PRODUCTO NO CONFORME<br>(PNC)</td>
                <td class="meta-cell"><strong>CÓDIGO:</strong> C&amp;DP-PNC-FO-001</td>
            </tr>
            <tr>
                <td class="meta-cell"><strong>VERSIÓN:</strong> 4</td>
            </tr>
            <tr>
                <td class="meta-cell"><strong>FECHA:</strong> 11/2/2026</td>
            </tr>
            <tr>
                <td class="meta-cell"><strong>PÁGINAS:</strong> 1 de 1</td>
            </tr>
        </table>

        <!-- DATOS DEL MES Y ZONA -->
        <div class="info-row">
            <div>MES: <?= htmlspecialchars($mes_texto) ?></div>
            <div>SEDE / UBICACIÓN: <?= htmlspecialchars($sede) ?></div>
        </div>

        <!-- TABLA DE DATOS -->
        <table class="data-table">
            <thead>
                <tr>
                    <th>FECHA</th>
                    <th>QUIEN REPORTA</th>
                    <th>PRODUCTO</th>
                    <th>NÚMERO DE LOTE</th>
                    <th>CANTIDAD NO CONFORME</th>
                    <th>DESCRIPCIÓN DEL EVENTO/MOTIVO</th>
                    <th>VERIFICA IDENTIFICACIÓN</th>
                    <th>CORRECCIÓN / DESTINO / ALMACENAMIENTO</th>
                    <th>N° NC/DOC</th>
                    <th>RESPONSABLE DE LA CORRECC.</th>
                    <th>FECHA CORRECC.</th>
                    <th>VERIFICA CORRECCIÓN</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($registros) === 0): ?>
                    <tr><td colspan="12" style="padding: 20px;">No hay registros para este mes.</td></tr>
                <?php else: ?>
                    <?php foreach ($registros as $r): ?>
                        <tr>
                            <td><?= htmlspecialchars($r['fecha_reporte'] ?? '') ?></td>
                            <td><?= htmlspecialchars($r['quien_reporta'] ?? '') ?></td>
                            <td><?= htmlspecialchars($r['producto'] ?? '') ?></td>
                            <td><?= htmlspecialchars($r['numero_lote'] ?? '') ?></td>
                            <td><?= htmlspecialchars($r['cantidad_nc'] ?? '') ?></td>
                            <td style="max-width:150px; text-align:left;"><?= nl2br(htmlspecialchars($r['descripcion_evento'] ?? '')) ?></td>
                            <td>
                                <?= htmlspecialchars($r['verifica_identificacion'] ?? '') ?><br>
                                <?php if(!empty($r['firma'])): ?>
                                    <img src="<?= $r['firma'] ?>" class="firma-img">
                                <?php endif; ?>
                            </td>
                            <td style="max-width:150px; text-align:left;"><?= nl2br(htmlspecialchars($r['correccion_destino'] ?? '')) ?></td>
                            <td><?= htmlspecialchars($r['num_documento'] ?? '') ?></td>
                            <td><?= htmlspecialchars($r['responsable_correccion'] ?? '') ?></td>
                            <td><?= htmlspecialchars($r['fecha_correccion'] ?? '') ?></td>
                            <td>
                                <?php if(empty($r['verifica_correccion'])): ?>
                                    <button class="btn-verificar disable-print" data-html2canvas-ignore="true" onclick="firmarCorreccion('<?= htmlspecialchars($file) ?>', '<?= $r['id'] ?>')" style="width: 100%;">VERIFICAR</button>
                                <?php else: ?>
                                    <?= htmlspecialchars($r['verifica_correccion']) ?>
                                <?php endif; ?>
                                <button class="btn-editar disable-print" data-html2canvas-ignore="true" onclick='abrirEditor(<?= htmlspecialchars(json_encode($r), ENT_QUOTES, "UTF-8") ?>)'>EDITAR</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        
    </div>
</div>

<!-- MODAL DE EDICIÓN CYBERPUNK -->
<div id="editModal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.85); align-items:center; justify-content:center; backdrop-filter:blur(3px);">
    <div style="background:#141620; border:1px solid #00f2ff; box-shadow:0 0 30px rgba(0,242,255,0.15); width:90%; max-width:800px; padding:25px; border-radius:4px; max-height:90vh; overflow-y:auto; color:#e0e6ed; font-family:'Barlow', sans-serif;">
        <h2 style="font-family:'Space Mono', monospace; color:#00f2ff; border-bottom:1px solid #2d324a; padding-bottom:10px; margin-top:0; display:flex; justify-content:space-between; align-items:center;">
            MODO CORRECCIÓN PNC
            <span style="font-size:12px; color:#c682ff; cursor:pointer;" onclick="document.getElementById('editModal').style.display='none'">[X] CERRAR</span>
        </h2>
        <form id="editForm" onsubmit="guardarEdicion(event)" style="margin-top:20px;">
            <input type="hidden" id="edit_id">
            
            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:15px; margin-bottom:15px;">
                <div><label style="font-size:11px; color:#c682ff; font-weight:bold;">FECHA REPORTE</label><input type="date" id="edit_fecha_reporte" style="width:100%; padding:8px; background:#0a0b10; border:1px solid #2d324a; border-radius:2px; color:#fff; box-sizing:border-box; margin-top:4px;"></div>
                <div><label style="font-size:11px; color:#c682ff; font-weight:bold;">QUIEN REPORTA</label><input type="text" id="edit_quien_reporte" style="width:100%; padding:8px; background:#0a0b10; border:1px solid #2d324a; border-radius:2px; color:#fff; box-sizing:border-box; margin-top:4px;"></div>
                <div><label style="font-size:11px; color:#c682ff; font-weight:bold;">PRODUCTO</label><input type="text" id="edit_producto" style="width:100%; padding:8px; background:#0a0b10; border:1px solid #2d324a; border-radius:2px; color:#fff; box-sizing:border-box; margin-top:4px;"></div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:15px; margin-bottom:15px;">
                <div><label style="font-size:11px; color:#c682ff; font-weight:bold;">LOTE</label><input type="text" id="edit_numero_lote" style="width:100%; padding:8px; background:#0a0b10; border:1px solid #2d324a; border-radius:2px; color:#fff; box-sizing:border-box; margin-top:4px;"></div>
                <div><label style="font-size:11px; color:#c682ff; font-weight:bold;">CANTIDAD NC</label><input type="text" id="edit_cantidad_nc" style="width:100%; padding:8px; background:#0a0b10; border:1px solid #2d324a; border-radius:2px; color:#fff; box-sizing:border-box; margin-top:4px;"></div>
                <div><label style="font-size:11px; color:#c682ff; font-weight:bold;">VERIFICA IDENTIFIC.</label><input type="text" id="edit_verifica_identificacion" style="width:100%; padding:8px; background:#0a0b10; border:1px solid #2d324a; border-radius:2px; color:#fff; box-sizing:border-box; margin-top:4px;"></div>
            </div>
            
            <div style="margin-bottom:15px;">
                <label style="font-size:11px; color:#c682ff; font-weight:bold;">DESCRIPCIÓN DEL EVENTO</label>
                <textarea id="edit_descripcion_evento" rows="3" style="width:100%; padding:8px; background:#0a0b10; border:1px solid #2d324a; border-radius:2px; color:#fff; box-sizing:border-box; margin-top:4px;"></textarea>
            </div>
            <div style="margin-bottom:15px;">
                <label style="font-size:11px; color:#c682ff; font-weight:bold;">CORRECCIÓN / DESTINO</label>
                <textarea id="edit_correccion_destino" rows="3" style="width:100%; padding:8px; background:#0a0b10; border:1px solid #2d324a; border-radius:2px; color:#fff; box-sizing:border-box; margin-top:4px;"></textarea>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:15px; margin-bottom:25px;">
                <div><label style="font-size:11px; color:#c682ff; font-weight:bold;">N° DOC NC</label><input type="text" id="edit_num_documento" style="width:100%; padding:8px; background:#0a0b10; border:1px solid #2d324a; border-radius:2px; color:#fff; box-sizing:border-box; margin-top:4px;"></div>
                <div><label style="font-size:11px; color:#c682ff; font-weight:bold;">RESPONSABLE CORRECCIÓN</label><input type="text" id="edit_responsable_correccion" style="width:100%; padding:8px; background:#0a0b10; border:1px solid #2d324a; border-radius:2px; color:#fff; box-sizing:border-box; margin-top:4px;"></div>
                <div><label style="font-size:11px; color:#c682ff; font-weight:bold;">FECHA CORRECCIÓN</label><input type="date" id="edit_fecha_correccion" style="width:100%; padding:8px; background:#0a0b10; border:1px solid #2d324a; border-radius:2px; color:#fff; box-sizing:border-box; margin-top:4px;"></div>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" class="btn" style="background:#2d324a; color:#fff; padding:10px 15px; font-size:12px;" onclick="document.getElementById('editModal').style.display='none'">CANCELAR</button>
                <button type="submit" class="btn" style="padding:10px 15px; font-size:12px; box-shadow:0 0 10px rgba(0,242,255,0.2);">GUARDAR CAMBIOS</button>
            </div>
        </form>
    </div>
</div>

<script>
async function exportPDF() {
    const { jsPDF } = window.jspdf;
    const element = document.getElementById('divToPrint');
    
    try {
        const canvas = await html2canvas(element, { 
            scale: 2, 
            useCORS: true,
            logging: false
        });
        
        const imgData = canvas.toDataURL('image/jpeg', 0.95);
        if(imgData.includes('data:,')){
            alert("Error: Canvas vacío");
            return;
        }

        // Orientación landscape porque la tabla es muy ancha
        const pdf = new jsPDF('l', 'mm', 'a4'); 
        const pdfWidth = pdf.internal.pageSize.getWidth();
        const pdfHeight = (canvas.height * pdfWidth) / canvas.width;
        
        pdf.addImage(imgData, 'JPEG', 0, 0, pdfWidth, pdfHeight);
        pdf.save(`Reporte_PNC_<?= $sede ?>_<?= $file ?>.pdf`);
        
    } catch (error) {
        console.error("Error generating PDF", error);
        alert("Ocurrió un error al generar el PDF.");
    }
}

function firmarCorreccion(filename, recordId) {
    if (!confirm(`¿Confirmas que deseas firmar/verificar esta corrección usando tú usuario actualmente conectado (${'<?= htmlspecialchars($nombre_usuario) ?>'})?`)) {
        return;
    }

    fetch('api_firmar.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ file: filename, id: recordId })
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'ok') {
            location.reload();
        } else {
            alert('Atención: ' + data.message);
        }
    })
    .catch(err => {
        console.error(err);
        alert('Ocurrió un error de conexión al firmar el documento.');
    });
}

function abrirEditor(r) {
    document.getElementById('edit_id').value = r.id || '';
    document.getElementById('edit_fecha_reporte').value = r.fecha_reporte || '';
    document.getElementById('edit_quien_reporte').value = r.quien_reporta || '';
    document.getElementById('edit_producto').value = r.producto || '';
    document.getElementById('edit_numero_lote').value = r.numero_lote || '';
    document.getElementById('edit_cantidad_nc').value = r.cantidad_nc || '';
    document.getElementById('edit_verifica_identificacion').value = r.verifica_identificacion || '';
    document.getElementById('edit_descripcion_evento').value = r.descripcion_evento || '';
    document.getElementById('edit_correccion_destino').value = r.correccion_destino || '';
    document.getElementById('edit_num_documento').value = r.num_documento || '';
    document.getElementById('edit_responsable_correccion').value = r.responsable_correccion || '';
    document.getElementById('edit_fecha_correccion').value = r.fecha_correccion || '';
    document.getElementById('editModal').style.display = 'flex';
}

function guardarEdicion(e) {
    e.preventDefault();
    const payload = {
        file: '<?= htmlspecialchars($file) ?>',
        id: document.getElementById('edit_id').value,
        fecha_reporte: document.getElementById('edit_fecha_reporte').value,
        quien_reporta: document.getElementById('edit_quien_reporte').value,
        producto: document.getElementById('edit_producto').value,
        numero_lote: document.getElementById('edit_numero_lote').value,
        cantidad_nc: document.getElementById('edit_cantidad_nc').value,
        verifica_identificacion: document.getElementById('edit_verifica_identificacion').value,
        descripcion_evento: document.getElementById('edit_descripcion_evento').value,
        correccion_destino: document.getElementById('edit_correccion_destino').value,
        num_documento: document.getElementById('edit_num_documento').value,
        responsable_correccion: document.getElementById('edit_responsable_correccion').value,
        fecha_correccion: document.getElementById('edit_fecha_correccion').value
    };

    fetch('api_editar_pnc.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'ok') {
            document.getElementById('editModal').style.display = 'none';
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(e => alert('Error de red al guardar la edición.'));
}
</script>

</body>
</html>
