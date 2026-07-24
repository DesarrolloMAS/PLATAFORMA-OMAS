<?php
require '../sesion.php';
verificarAutenticacion();

$sede = $_SESSION['sede'];
$file = $_GET['file'] ?? '';
$highlight_id = $_GET['id'] ?? '';

if (!$file) {
    die("Falta el parámetro 'file'.");
}

$file_path = "../../archivos/generados/proceso_v2/" . preg_replace('/[^A-Za-z0-9_-]/', '', $sede) . "/" . basename($file);
if (!file_exists($file_path)) {
    die("El archivo de registros no existe.");
}

$records = json_decode(file_get_contents($file_path), true) ?: [];
$periodo = str_replace(['PROCESO_MOLIENDA_', '.json'], '', basename($file));

function campo($data, $key, $default = '') {
    return isset($data[$key]) && $data[$key] !== '' ? htmlspecialchars($data[$key]) : $default;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Proceso de Molienda - <?= htmlspecialchars($periodo) ?></title>
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
            max-width: 1600px;
            margin: 0 auto;
            padding: 30px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            position: relative;
            overflow-x: auto;
        }

        table { border-collapse: collapse; }

        .header-table {
            width: 100%;
            margin-bottom: 0;
        }

        .header-table td {
            border: 1px solid #000;
            padding: 8px 12px;
            vertical-align: middle;
        }

        .logo-cell { width: 140px; text-align: center; }
        .logo-img { max-width: 110px; }
        .title-cell { font-weight: 700; font-size: 15px; text-transform: uppercase; text-align: center; }
        .meta-cell { font-size: 11px; width: 160px; text-align: left; }
        .meta-cell strong { display: inline-block; min-width: 60px; }

        .zona-bar {
            width: 100%;
        }
        .zona-bar td {
            border: 1px solid #000;
            border-top: none;
            padding: 6px 12px;
            font-size: 12px;
        }
        .zona-label {
            background: #1F4E5F;
            color: #fff;
            font-weight: 700;
            width: 25%;
            text-transform: uppercase;
        }
        .zona-value { font-weight: 600; }

        .section-bar {
            background: #1F4E5F;
            color: #fff;
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
            text-align: center;
            padding: 6px;
            border: 1px solid #000;
            border-top: none;
        }

        .data-table-full {
            width: 100%;
            margin-bottom: 25px;
        }

        .data-table-full th, .data-table-full td {
            border: 1px solid #000;
            padding: 6px 8px;
            font-size: 11px;
            text-align: center;
        }

        .data-table-full thead th {
            background: #1F4E5F;
            color: #fff;
            text-transform: uppercase;
            font-size: 10px;
        }

        .data-table-full tbody tr:nth-child(even) {
            background: #f7f9fa;
        }

        .data-table-full tbody tr.highlight {
            background: #fff3cd !important;
            outline: 2px solid #FFB000;
        }

        .empty-row td {
            font-style: italic;
            color: #888;
            padding: 15px;
        }

        .actions {
            position: fixed;
            top: 20px;
            right: 20px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            z-index: 10;
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
        .btn-edit { background: #FFB000; color: #1a1d2e; }
        .btn:disabled { opacity: 0.4; cursor: not-allowed; }

        .data-table-full tbody tr { cursor: pointer; }
        .data-table-full tbody tr:hover { background: #e8f4ff; }
        .data-table-full tbody tr.empty-row { cursor: default; }
        .data-table-full tbody tr.empty-row:hover { background: inherit; }

        @media print {
            .actions { display: none; }
            body { background: #fff; padding: 0; }
            .document-page { box-shadow: none; margin: 0; max-width: 100%; }
            @page { size: landscape; margin: 8mm; }
        }
    </style>
</head>
<body>

<div class="actions">
    <button class="btn" onclick="window.location.href='rev_proceso_v2.php'">Volver</button>
    <button class="btn btn-edit" id="btnCorregir" onclick="irACorregir()" <?= $highlight_id ? '' : 'disabled title="Seleccione una fila del documento para corregirla"' ?>>✏️ Corregir</button>
    <button class="btn btn-pdf" onclick="exportPDF()">Exportar PDF</button>
</div>

<div class="document-page" id="reportContent">
    <table class="header-table">
        <tr>
            <td rowspan="4" class="logo-cell">
                <img src="/archivos/formularios/logomas.png" class="logo-img" alt="Logo">
            </td>
            <td rowspan="4" class="title-cell">PPR GESTIÓN DE LA PRODUCCIÓN<br>"CONTROL DEL PROCESO DE MOLIENDA"</td>
            <td class="meta-cell"><strong>CÓDIGO:</strong></td>
        </tr>
        <tr><td class="meta-cell"><strong>VERSIÓN:</strong></td></tr>
        <tr><td class="meta-cell"><strong>FECHA:</strong></td></tr>
        <tr><td class="meta-cell"><strong>PÁGINA:</strong> 1 de 1</td></tr>
    </table>
    <table class="zona-bar">
        <tr>
            <td class="zona-label">ZONA</td>
            <td class="zona-value"><?= htmlspecialchars($sede) ?></td>
        </tr>
    </table>

    <div class="section-bar">Harina de Trigo — Período: <?= htmlspecialchars($periodo) ?></div>
    <table class="data-table-full">
        <thead>
            <tr>
                <th rowspan="2">Fecha</th>
                <th rowspan="2">Líder de Turno</th>
                <th rowspan="2">Silo de Moje</th>
                <th rowspan="2">Presentación del Producto</th>
                <th rowspan="2">Referencia de Producto</th>
                <th rowspan="2">Hora de Inicio de Molienda</th>
                <th rowspan="2">Hora Final Molienda</th>
                <th rowspan="2">Báscula de Trigo (Kg)</th>
                <th rowspan="2">Báscula de Harina (Kg)</th>
                <th rowspan="2">Bultos de Harina (Unidad)</th>
                <th rowspan="2">Lote de Harina</th>
                <th colspan="2">Harina Granel</th>
            </tr>
            <tr>
                <th>Cantidad (Kg)</th>
                <th>Silo</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($records)): ?>
                <tr class="empty-row"><td colspan="13">No hay registros en este período.</td></tr>
            <?php else: foreach ($records as $rec): $d = $rec['datos'] ?? []; ?>
                <tr class="<?= ($rec['id_registro'] === $highlight_id) ? 'highlight' : '' ?>" data-id="<?= htmlspecialchars($rec['id_registro']) ?>">
                    <td><?= campo($d, 'fecha') ?></td>
                    <td><?= campo($d, 'lider_turno') ?></td>
                    <td><?= campo($d, 'silo_moje') ?></td>
                    <td><?= campo($d, 'presentacion_producto') ?></td>
                    <td><?= campo($d, 'referencia_producto') ?></td>
                    <td><?= campo($d, 'hora_inicio') ?></td>
                    <td><?= campo($d, 'hora_final') ?></td>
                    <td><?= campo($d, 'bascula_trigo') ?></td>
                    <td><?= campo($d, 'bascula_harina') ?></td>
                    <td><?= campo($d, 'bultos_harina') ?></td>
                    <td><?= campo($d, 'lote_harina') ?></td>
                    <td><?= campo($d, 'granel_cantidad_kg') ?></td>
                    <td><?= campo($d, 'granel_silo') ?></td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>

    <div class="section-bar">Subproductos — Período: <?= htmlspecialchars($periodo) ?></div>
    <table class="data-table-full">
        <thead>
            <tr>
                <th rowspan="2">Fecha</th>
                <th colspan="2">Mogolla</th>
                <th colspan="2">Salvado</th>
                <th colspan="2">Segunda</th>
                <th colspan="2">Germen</th>
                <th rowspan="2">Sémola Fina / Granza (Bultos)</th>
                <th rowspan="2">Varadas / Observaciones</th>
            </tr>
            <tr>
                <th>Bultos</th><th>Hilo</th>
                <th>Bultos</th><th>Hilo</th>
                <th>Bultos</th><th>Hilo</th>
                <th>Bultos</th><th>Hilo</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($records)): ?>
                <tr class="empty-row"><td colspan="10">No hay registros en este período.</td></tr>
            <?php else: foreach ($records as $rec): $d = $rec['datos'] ?? []; ?>
                <tr class="<?= ($rec['id_registro'] === $highlight_id) ? 'highlight' : '' ?>" data-id="<?= htmlspecialchars($rec['id_registro']) ?>">
                    <td><?= campo($d, 'fecha') ?></td>
                    <td><?= campo($d, 'mogolla_bultos') ?></td>
                    <td><?= campo($d, 'mogolla_hilo') ?></td>
                    <td><?= campo($d, 'salvado_bultos') ?></td>
                    <td><?= campo($d, 'salvado_hilo') ?></td>
                    <td><?= campo($d, 'segunda_bultos') ?></td>
                    <td><?= campo($d, 'segunda_hilo') ?></td>
                    <td><?= campo($d, 'germen_bultos') ?></td>
                    <td><?= campo($d, 'germen_hilo') ?></td>
                    <td><?= campo($d, 'semola_granza_bultos') ?></td>
                    <td style="text-align:left;"><?= campo($d, 'varadas') ?></td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<script>
    const fileParam = <?= json_encode($file) ?>;
    let selectedId = <?= json_encode($highlight_id ?: null) ?>;

    function updateSelection() {
        document.querySelectorAll('tr[data-id]').forEach(row => {
            row.classList.toggle('highlight', row.dataset.id === selectedId);
        });
        const btn = document.getElementById('btnCorregir');
        btn.disabled = !selectedId;
        btn.title = selectedId ? '' : 'Seleccione una fila del documento para corregirla';
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('tr[data-id]').forEach(row => {
            row.addEventListener('click', () => {
                selectedId = row.dataset.id;
                updateSelection();
            });
        });

        updateSelection();
        const highlighted = document.querySelector('tr.highlight');
        if (highlighted) highlighted.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });

    function irACorregir() {
        if (!selectedId) return;
        window.location.href = `corregir_proceso_v2.php?file=${encodeURIComponent(fileParam)}&id=${encodeURIComponent(selectedId)}`;
    }

    async function exportPDF() {
        const { jsPDF } = window.jspdf;
        const element = document.getElementById('reportContent');

        const canvas = await html2canvas(element, {
            scale: 2,
            useCORS: true
        });

        const imgData = canvas.toDataURL('image/png');
        const pdf = new jsPDF('l', 'mm', 'a4');
        const imgProps = pdf.getImageProperties(imgData);
        const pdfWidth = pdf.internal.pageSize.getWidth();
        const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;

        pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);
        pdf.save(`Proceso_Molienda_<?= htmlspecialchars($periodo) ?>.pdf`);
    }
</script>

</body>
</html>
