<?php
require_once __DIR__ . '/../sesion.php';
$zona = isset($_GET['zona']) ? htmlspecialchars($_GET['zona']) : (isset($_SESSION['sede']) ? $_SESSION['sede'] : 'General');
$file = isset($_GET['file']) ? htmlspecialchars($_GET['file']) : '';

if (!$file) {
    die("No se especificó un archivo.");
}

$base_path = "../../archivos/generados/termohigrometros/";

if (strpos($file, '/') !== false) {
    // Si el archivo ya trae una ruta (ej: General/2026-03.json)
    $ruta_archivo = $base_path . $file;
} else {
    // Legacy o archivo en la raíz
    $ruta_archivo = $base_path . $zona . "/" . $file;
    if (!file_exists($ruta_archivo)) {
        $ruta_archivo = $base_path . $file;
    }
}

if (!file_exists($ruta_archivo)) {
    die("El archivo no existe ($file).");
}

$json_content = file_get_contents($ruta_archivo);
$registros = json_decode($json_content, true);

if (!$registros) {
    die("Error al leer los datos del archivo.");
}

// Extraer nombre de ubicación real
$ubiName = isset($registros[0]['ubicacion_nombre']) ? $registros[0]['ubicacion_nombre'] : $zona;

// Dividir registros para dos columnas si es necesario (el usuario mostró dos tablas en la imagen)
// Pero los registros JSON pueden ser muchos. Veremos cómo ajustarlos.
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reporte Termohigrómetro - <?php echo $file; ?></title>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@300;400;600&family=IBM+Plex+Mono:wght@400;600&display=swap" rel="stylesheet">
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
        background: #f0f0f0;
        font-family: 'IBM Plex Sans', sans-serif;
        font-size: 11px;
        padding: 40px;
        display: flex;
        justify-content: center;
    }
    .doc-container {
        background: #fff;
        width: max-content;
        min-width: 1000px;
        max-width: 100%;
        min-height: 700px;
        padding: 30px;
        box-shadow: 0 0 20px rgba(0,0,0,0.1);
        position: relative;
    }
    /* HEADER ESTRUCTURA */
    .header-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }
    .header-table td {
        border: 1px solid #000;
        vertical-align: middle;
        padding: 8px;
    }
    .logo-cell { width: 180px; text-align: center; }
    .title-cell { text-align: center; }
    .meta-cell { width: 220px; font-size: 9px; padding: 0 !important; }
    
    .logo-img { max-height: 60px; }
    .main-title { font-weight: 700; font-size: 14px; text-transform: uppercase; }
    .sub-title { font-size: 11px; margin-top: 4px; }

    .meta-table { width: 100%; border-collapse: collapse; border: none; }
    .meta-table td { border: none; border-bottom: 1px solid #000; border-left: 1px solid #000; padding: 4px 8px; }
    .meta-table tr:last-child td { border-bottom: none; }
    .meta-label { font-weight: 700; background: #eee; width: 80px; }

    .ubicacion-line {
        margin-bottom: 15px;
        font-size: 12px;
        font-weight: 600;
        border-bottom: 1px solid #000;
        display: inline-block;
        padding-right: 50px;
    }

    /* TABLA DE DATOS */
    .data-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 10px;
    }
    .data-table th, .data-table td {
        border: 1px solid #000;
        padding: 4px 2px;
        text-align: center;
    }
    .data-table th {
        background: #f2f2f2;
        font-weight: 700;
        font-size: 9px;
    }
    
    /* Layout de dos columnas como en la imagen */
    .tables-wrapper {
        display: flex;
        gap: 10px;
    }
    .column-table { flex: 1; }

    .btn-float {
        position: fixed;
        bottom: 30px;
        right: 30px;
        background: #000;
        color: #fff;
        border: none;
        padding: 12px 24px;
        border-radius: 4px;
        cursor: pointer;
        font-family: inherit;
        font-weight: 600;
        z-index: 1000;
    }

    @media print {
        @page { size: landscape; }
        body { background: white; padding: 0; display: block; }
        .doc-container { box-shadow: none; border: none; width: 100%; max-width: none; padding: 0; }
        .btn-float { display: none; }
    }
</style>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
</head>
<body>

    <button class="btn-float" onclick="descargarPDF()">Descargar PDF</button>

    <div class="doc-container" id="doc-to-print">
        <!-- HEADER -->
        <table class="header-table">
            <tr>
                <td class="logo-cell">
                    <img src="/img/logo_empresa.jpeg" alt="Logo" class="logo-img">
                </td>
                <td class="title-cell">
                    <div class="sub-title">Programa de Producción</div>
                    <div class="main-title">"Control de Termohigrómetro"</div>
                </td>
                <td class="meta-cell">
                    <table class="meta-table">
                        <tr><td class="meta-label">Código:</td><td>GP-PD-PG-FO-011</td></tr>
                        <tr><td class="meta-label">Versión:</td><td>2</td></tr>
                        <tr><td class="meta-label">Fecha:</td><td>19/03/2020</td></tr>
                        <tr><td class="meta-label">Página:</td><td>1 de 1</td></tr>
                    </table>
                </td>
            </tr>
        </table>

        <div class="ubicacion-line">Ubicación: <span style="text-transform:uppercase; font-weight:800;"><?php echo htmlspecialchars($ubiName); ?></span></div>

        <!-- TABLAS DE DATOS (Dos columnas) -->
        <div class="tables-wrapper">
            <?php
            // Dividir registros en dos mitades para las dos columnas
            $total = count($registros);
            $half = ceil($total / 2);
            $chunks = array_chunk($registros, $half);
            
            foreach ($chunks as $chunkIndex => $chunk):
            ?>
            <div class="column-table">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Temp (°C)</th>
                            <th>Hum (%)</th>
                            <th>Hum Máx</th>
                            <th>Temp Máx</th>
                            <th>Hum Mín</th>
                            <th>Temp Mín</th>
                            <th>Verifica</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($chunk as $reg): ?>
                        <tr>
                            <td><?php echo date('d/m/y', strtotime($reg['fecha'])); ?></td>
                            <td><?php echo $reg['hora']; ?></td>
                            <td><?php echo $reg['temperatura']; ?></td>
                            <td><?php echo $reg['humedad']; ?></td>
                            <td><?php echo $reg['humedad_max']; ?></td>
                            <td><?php echo $reg['temp_max']; ?></td>
                            <td><?php echo $reg['humedad_min']; ?></td>
                            <td><?php echo $reg['temp_min']; ?></td>
                            <td><?php echo $reg['verificacion']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php 
                        // Rellenar filas vacías para que las tablas tengan el mismo tamaño (opcional)
                        $rem = $half - count($chunk);
                        for ($i=0; $i<$rem; $i++): ?>
                        <tr>
                            <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
                        </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        async function descargarPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('l', 'pt', 'a4'); // Paisaje para que quepan las dos tablas bien
            const content = document.getElementById('doc-to-print');
            
            // Usamos html2canvas para mayor fidelidad visual
            const canvas = await html2canvas(content, {
                scale: 2,
                useCORS: true
            });
            
            const imgData = canvas.toDataURL('image/png');
            const imgProps = doc.getImageProperties(imgData);
            const pdfWidth = doc.internal.pageSize.getWidth();
            const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;
            
            doc.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);
            doc.save('Reporte_Termohigrometro_<?php echo $zona; ?>_<?php echo preg_replace("/[^a-zA-Z0-9]/", "_", $ubiName); ?>_<?php echo str_replace(".json", "", basename($file)); ?>.pdf');
        }
        
        // Auto-descarga si se desea, o dejar el botón flotante. 
        // El usuario dijo "sistema de impresion", dejaré el botón bien visible.
    </script>
</body>
</html>
