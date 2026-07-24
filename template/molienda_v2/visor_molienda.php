<?php
require '../sesion.php';
verificarAutenticacion();

$sede = $_SESSION['sede'];
$file = $_GET['file'] ?? '';
$id = $_GET['id'] ?? '';

if (!$file || !$id) {
    die("Faltan parámetros de búsqueda.");
}

$file_path = "../../archivos/generados/molienda/" . $sede . "/" . $file;
if (!file_exists($file_path)) {
    die("El archivo de registros no existe.");
}

$records = json_decode(file_get_contents($file_path), true);
$data = null;
foreach ($records as $r) {
    if ($r['id'] === $id) {
        $data = $r;
        break;
    }
}

if (!$data) {
    die("Registro no encontrado.");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Molienda - <?= $id ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;600&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <style>
        body {
            background: #f0f2f5;
            font-family: 'Barlow', sans-serif;
            padding: 40px;
            color: #1a1d2e;
        }

        .document-page {
            background: #fff;
            width: 800px;
            margin: 0 auto;
            padding: 40px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            position: relative;
        }

        /* Encabezado Institucional */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .header-table td {
            border: 1px solid #000;
            padding: 10px;
            text-align: center;
            vertical-align: middle;
        }

        .logo-cell { width: 150px; }
        .logo-img { max-width: 120px; }
        .title-cell { font-weight: 700; font-size: 16px; text-transform: uppercase; }
        .info-cell { font-size: 10px; width: 180px; text-align: left !important; }

        .section-title {
            background: #f4f4f4;
            padding: 5px 10px;
            font-weight: 700;
            font-size: 12px;
            border: 1px solid #000;
            margin-top: 20px;
            text-transform: uppercase;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }

        .data-table td, .data-table th {
            border: 1px solid #000;
            padding: 8px;
            font-size: 12px;
        }

        .label-cell { background: #fafafa; font-weight: 600; width: 150px; }

        .results-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: -1px;
        }

        .results-table th { background: #f4f4f4; font-size: 11px; text-transform: uppercase; border: 1px solid #000; padding: 5px; }
        .results-table td { border: 1px solid #000; padding: 5px 10px; font-size: 12px; }

        .signature-box {
            margin-top: 30px;
            text-align: center;
            width: 300px;
        }

        .signature-img {
            max-width: 200px;
            border-bottom: 1px solid #000;
            margin-bottom: 5px;
        }

        .signature-label { font-size: 11px; font-weight: 600; text-transform: uppercase; }

        .actions {
            position: fixed;
            top: 40px;
            right: 40px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .btn {
            padding: 12px 25px;
            background: #1a1d2e;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-family: 'Space Mono', monospace;
            font-size: 12px;
            text-transform: uppercase;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .btn-pdf { background: #e11d48; }

        @media print {
            .actions { display: none; }
            body { background: #fff; padding: 0; }
            .document-page { box-shadow: none; margin: 0; width: 100%; }
        }
    </style>
</head>
<body>

<div class="actions">
    <button class="btn" onclick="window.location.href='rev_molienda.php'">Volver</button>
    <button class="btn btn-pdf" onclick="exportPDF()">Exportar PDF</button>
</div>

<div class="document-page" id="reportContent">
    <!-- Encabezado -->
    <table class="header-table">
        <tr>
            <td rowspan="3" class="logo-cell">
                <img src="/archivos/formularios/logomas.png" class="logo-img" alt="Logo">
            </td>
            <td rowspan="3" class="title-cell">CONTROL DE MOLIENDA</td>
            <td class="info-cell">CÓDIGO: MO-PG-PD-FO-002</td>
        </tr>
        <tr>
            <td class="info-cell">VERSIÓN: 1</td>
        </tr>
        <tr>
            <td class="info-cell">FECHA: 01/04/2026</td>
        </tr>
    </table>

    <div class="section-title">Datos Generales</div>
    <table class="data-table">
        <tr>
            <td class="label-cell">Responsable</td>
            <td><?= $data['responsable'] ?></td>
            <td class="label-cell">Sede</td>
            <td><?= $data['sede'] ?></td>
        </tr>
        <tr>
            <td class="label-cell">Fecha Reporte</td>
            <td><?= $data['fecha'] ?></td>
            <td class="label-cell">Hora Reporte</td>
            <td><?= $data['hora'] ?></td>
        </tr>
        <tr>
            <td class="label-cell">Almacenista</td>
            <td colspan="3"><?= $data['almacenista'] ?></td>
        </tr>
        <?php if (!empty($data['responsables_intervencion'])): ?>
        <tr>
            <td class="label-cell">Responsables Intervención</td>
            <td colspan="3">
                <?= implode(' / ', $data['responsables_intervencion']) ?>
            </td>
        </tr>
        <?php endif; ?>
    </table>

    <div class="section-title">Resultados de Molienda (Harinas)</div>
    <table class="results-table">
        <thead>
            <tr>
                <th style="text-align: left;">Producto</th>
                <th>Peso Unit.</th>
                <th>Cant. Bultos</th>
                <th>Lote</th>
                <th>Total Kg</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $has_harinas = false;
            foreach ($data['harinas'] as $id => $item): 
                if (isset($item['active'])):
                    $has_harinas = true;
                    $first_lot = true;
                    $lotes_validos = array_filter($item['lotes'], function($l) { return !empty($l['valor']); });
                    $num_lotes = count($lotes_validos) ?: 1;
                    
                    if (empty($lotes_validos)) $lotes_validos = [['valor' => 0, 'id' => '-']];

                    foreach ($lotes_validos as $lot):
            ?>
                <tr>
                    <?php if ($first_lot): ?>
                        <td rowspan="<?= $num_lotes ?>" style="font-weight: 600;"><?= str_replace('_', ' ', strtoupper($id)) ?></td>
                        <td rowspan="<?= $num_lotes ?>" style="text-align: center;"><?= $item['peso_unit'] ?> Kg</td>
                    <?php endif; ?>
                    <td style="text-align: center;"><?= $lot['valor'] ?></td>
                    <td style="text-align: center;"><?= $lot['id'] ?></td>
                    <?php if ($first_lot): 
                        $total_bultos = array_reduce($lotes_validos, function($a, $b) { return $a + $b['valor']; }, 0);
                        $total_kg = $total_bultos * $item['peso_unit'];
                    ?>
                        <td rowspan="<?= $num_lotes ?>" style="text-align: right; font-weight: 700; color: #000;"><?= number_format($total_kg, 2) ?></td>
                    <?php endif; ?>
                </tr>
            <?php 
                        $first_lot = false;
                    endforeach; 
                endif;
            endforeach; 
            if (!$has_harinas) echo "<tr><td colspan='5' style='text-align:center; color:#999;'>No se registraron harinas</td></tr>";
            ?>
        </tbody>
    </table>

    <?php if (!empty($data['subproductos'])): ?>
    <div class="section-title">Subproductos</div>
    <table class="results-table">
        <thead>
            <tr>
                <th style="text-align: left;">Subproducto</th>
                <th>Peso Unit.</th>
                <th>Cant. Bultos</th>
                <th>Lote</th>
                <th>Total Kg</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data['subproductos'] as $id => $item): 
                if (isset($item['active'])):
                    $lotes_validos = array_filter($item['lotes'], function($l) { return !empty($l['valor']); });
                    $num_lotes = count($lotes_validos) ?: 1;
                    if (empty($lotes_validos)) $lotes_validos = [['valor' => 0, 'id' => '-']];
                    $first_lot = true;
                    foreach ($lotes_validos as $lot):
            ?>
                <tr>
                    <?php if ($first_lot): ?>
                        <td rowspan="<?= $num_lotes ?>"><?= str_replace('_', ' ', strtoupper($id)) ?></td>
                        <td rowspan="<?= $num_lotes ?>" style="text-align: center;"><?= $item['peso_unit'] ?> Kg</td>
                    <?php endif; ?>
                    <td style="text-align: center;"><?= $lot['valor'] ?></td>
                    <td style="text-align: center;"><?= $lot['id'] ?></td>
                    <?php if ($first_lot): 
                        $total_bultos = array_reduce($lotes_validos, function($a, $b) { return $a + $b['valor']; }, 0);
                        $total_kg = $total_bultos * $item['peso_unit'];
                    ?>
                        <td rowspan="<?= $num_lotes ?>" style="text-align: right; font-weight: 700;"><?= number_format($total_kg, 2) ?></td>
                    <?php endif; ?>
                </tr>
            <?php $first_lot = false; endforeach; endif; endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <?php if (!empty($data['materiales'])): ?>
    <div class="section-title">Materiales de Empaque y Otros</div>
    <table class="results-table">
        <thead>
            <tr>
                <th style="text-align: left;">Material</th>
                <th>Cantidad</th>
                <th>Lote</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data['materiales'] as $id => $item): 
                if (isset($item['active'])):
                    $lotes_validos = array_filter($item['lotes'], function($l) { return !empty($l['valor']); });
                    foreach ($lotes_validos as $lot):
            ?>
                <tr>
                    <td><?= str_replace(['_','emp_'], [' ','EMPAQUE '], strtoupper($id)) ?></td>
                    <td style="text-align: center;"><?= $lot['valor'] ?></td>
                    <td style="text-align: center;"><?= $lot['id'] ?: '-' ?></td>
                </tr>
            <?php endforeach; endif; endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <div class="signature-box">
        <?php if ($data['firma']): ?>
            <?php if (strpos($data['firma'], 'data:image') === 0): ?>
                <img src="<?= $data['firma'] ?>" class="signature-img">
            <?php else: ?>
                <strong style="display:block; font-family:'Space Mono', monospace; font-size:16px; margin-top:10px; color:var(--text);"><?= htmlspecialchars($data['firma']) ?></strong>
            <?php endif; ?>
        <?php else: ?>
            <div style="height: 100px; border-bottom: 1px solid #000; margin-bottom: 5px;"></div>
        <?php endif; ?>
        <div class="signature-label">Firma Turno</div>
    </div>

</div>

<script>
    async function exportPDF() {
        const { jsPDF } = window.jspdf;
        const element = document.getElementById('reportContent');
        
        // Opcional: Ajustar escala para mejor calidad
        const canvas = await html2canvas(element, {
            scale: 2,
            useCORS: true
        });
        
        const imgData = canvas.toDataURL('image/png');
        const pdf = new jsPDF('p', 'mm', 'a4');
        const imgProps = pdf.getImageProperties(imgData);
        const pdfWidth = pdf.internal.pageSize.getWidth();
        const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;
        
        pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);
        pdf.save(`Reporte_Molienda_${'<?= $id ?>'}.pdf`);
    }
</script>

</body>
</html>
