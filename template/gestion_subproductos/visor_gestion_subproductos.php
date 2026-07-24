<?php
require_once '../sesion.php';
verificarAutenticacion();

$sede = $_SESSION['sede'] ?? 'Sin_Sede';
$fileName = $_GET['file'] ?? '';

if (empty($fileName) || strpos($fileName, '..') !== false) {
    die("Archivo inválido.");
}

$filePath = "../../archivos/generados/gestion_subproductos/" . $sede . "/" . $fileName;

if (!file_exists($filePath)) {
    die("El archivo no existe.");
}

$jsonData = file_get_contents($filePath);
$registros = json_decode($jsonData, true) ?? [];

// Extraer hallazgos
$hallazgos = [];
foreach ($registros as $r) {
    if (!empty($r['hallazgo'])) {
        $hallazgos[] = $r['hallazgo'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visor Documental - Gestión de Subproductos</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #555;
            margin: 0;
            padding: 20px;
        }
        .document-wrapper {
            background-color: #fff;
            width: 297mm; /* A4 landscape width */
            min-height: 210mm; /* A4 landscape height */
            margin: 0 auto;
            padding: 10mm;
            box-sizing: border-box;
            box-shadow: 0 0 10px rgba(0,0,0,0.5);
            color: #000;
        }
        .controls {
            width: 297mm;
            margin: 0 auto 20px auto;
            display: flex;
            justify-content: space-between;
        }
        .btn {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        
        /* TABLAS DE REPORTE */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        table, th, td {
            border: 1px solid #000;
        }
        th, td {
            padding: 4px;
            text-align: center;
            vertical-align: middle;
        }
        
        .header-table {
            margin-bottom: 0;
        }
        .header-table td {
            padding: 5px;
        }
        .logo-cell {
            width: 150px;
            text-align: center;
        }
        .logo-cell img {
            max-width: 120px;
            height: auto;
        }
        .title-cell {
            font-size: 16px;
            font-weight: bold;
            text-align: center;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
        }
        .meta-table td {
            border: none;
            border-bottom: 1px solid #000;
            text-align: left;
            padding: 3px 5px;
            font-size: 10px;
            font-weight: bold;
        }
        .meta-table tr:last-child td {
            border-bottom: none;
        }
        .meta-label {
            display: inline-block;
            width: 50px;
        }
        
        .instructions-row {
            display: flex;
            border: 1px solid #000;
            border-top: none;
        }
        .instructions-row > div {
            padding: 5px;
            font-size: 10px;
        }
        .estab-cell {
            width: 20%;
            border-right: 1px solid #000;
            font-weight: bold;
            background-color: #e6e6e6;
        }
        .inst-cell {
            width: 80%;
            text-align: left;
        }
        .inst-cell strong {
            text-transform: uppercase;
        }
        
        .main-table {
            margin-top: 10px;
        }
        .main-table th {
            background-color: #e6e6e6;
            font-weight: bold;
        }
        .th-items-evaluar {
            text-align: center;
            letter-spacing: 2px;
            padding: 3px !important;
        }
        .col-fecha { width: 60px; }
        .col-resp { width: 90px; }
        .col-item { width: 9%; }
        
        .hallazgos-table {
            margin-top: 20px;
        }
        .hallazgos-table th {
            background-color: #e6e6e6;
        }
        
        @media print {
            body {
                background-color: transparent;
                padding: 0;
            }
            .document-wrapper {
                box-shadow: none;
                margin: 0;
                padding: 0;
                width: 100%;
            }
            .controls {
                display: none;
            }
            @page {
                size: A4 landscape;
                margin: 10mm;
            }
        }
    </style>
</head>
<body>

    <div class="controls">
        <a href="rev_gestion_subproductos.php" class="btn">Volver a la Galería</a>
        <button class="btn" onclick="window.print()">Imprimir / Guardar como PDF</button>
    </div>

    <div class="document-wrapper" id="documento">
        <!-- HEADER ROW -->
        <table class="header-table">
            <tr>
                <td class="logo-cell" rowspan="4">
                    <img src="../../archivos/formularios/logomas.png" alt="Logo MAS" onerror="this.src=''; this.alt='[LOGO MAS]';">
                </td>
                <td class="title-cell" rowspan="4">
                    PPR Gestión de Subproductos<br>
                    "Inspección Gestión de Subproductos"
                </td>
                <td style="padding:0; width:200px;">
                    <table class="meta-table">
                        <tr>
                            <td><span class="meta-label">Código:</span> GP-PD-FP-SB-FO-001</td>
                        </tr>
                        <tr>
                            <td><span class="meta-label">Versión:</span> 1</td>
                        </tr>
                        <tr>
                            <td><span class="meta-label">Fecha:</span> 25/11/2020</td>
                        </tr>
                        <tr>
                            <td><span class="meta-label">Página:</span> 1 de 2</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        
        <!-- INSTRUCCIONES -->
        <div class="instructions-row">
            <div class="estab-cell">
                Establecimiento:<br>
                <span style="font-weight:normal;"><?php echo htmlspecialchars($sede); ?></span>
            </div>
            <div class="inst-cell">
                <strong>Instrucciones para el registro:</strong> Por cada ítem a evaluar, escribir <strong>1</strong> si cumple ó <strong>0</strong> si no cumple. Escribir <strong>NA</strong> sobre las casillas de los ítems que no aplican para la evaluación, y no incluirlos en el total de ítems evaluados.
            </div>
        </div>

        <!-- MAIN TABLE -->
        <table class="main-table">
            <thead>
                <tr>
                    <th rowspan="2" class="col-fecha">Fecha</th>
                    <th colspan="8" class="th-items-evaluar">ÍTEMS A EVALUAR</th>
                    <th rowspan="2" style="width: 50px;">TOTAL<br>=<br>Suma de ítems</th>
                    <th rowspan="2" style="width: 70px;">% de<br>Cumplimiento<br>=<br>(Total / # ítems evaluados) * 100</th>
                    <th rowspan="2" class="col-resp">Responsable Inspección</th>
                </tr>
                <tr>
                    <th class="col-item">Directriz de Subproductos<br><span style="font-weight:normal;">(debidamente firmada por la Alta Dirección)</span></th>
                    <th class="col-item">Subproductos y líneas de origen<br><span style="font-weight:normal;">(debidamente identificados y documentados)</span></th>
                    <th class="col-item">Uso previsto y Consumo<br><span style="font-weight:normal;">(de los subproductos, claramente definidos y documentados)</span></th>
                    <th class="col-item">Rutas de circulación de Subproductos<br><span style="font-weight:normal;">(debidamente documentadas y actualizadas)</span></th>
                    <th class="col-item">Productos con marca del cliente<br><span style="font-weight:normal;">(se cuenta con medidas para llevar a cabo operaciones de "maquila")</span></th>
                    <th class="col-item">Prevención de la contaminación<br><span style="font-weight:normal;">(se aplican las medidas pertinentes para prevenir la contaminación de subproductos)</span></th>
                    <th class="col-item">Control de PNC<br><span style="font-weight:normal;">(se aplican medidas para controlar subproductos NO CONFORMES)</span></th>
                    <th class="col-item">Trazabilidad<br><span style="font-weight:normal;">(el sistema de trazabilidad permite rastrear todo origen y destino de lotes de subproductos)</span></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($registros)): ?>
                    <tr>
                        <td colspan="12" style="padding: 20px;">No hay registros de inspección en este documento.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($registros as $r): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($r['fecha_inspeccion'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($r['evaluaciones']['item_1']['resultado'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($r['evaluaciones']['item_2']['resultado'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($r['evaluaciones']['item_3']['resultado'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($r['evaluaciones']['item_4']['resultado'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($r['evaluaciones']['item_5']['resultado'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($r['evaluaciones']['item_6']['resultado'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($r['evaluaciones']['item_7']['resultado'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($r['evaluaciones']['item_8']['resultado'] ?? ''); ?></td>
                            <td style="font-weight:bold;"><?php echo htmlspecialchars($r['total_suma'] ?? ''); ?></td>
                            <td style="font-weight:bold;"><?php echo htmlspecialchars($r['porcentaje_cumplimiento'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($r['responsable_inspeccion'] ?? ''); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- HALLAZGOS Y OBSERVACIONES -->
        <?php if (!empty($hallazgos)): ?>
            <table class="hallazgos-table">
                <thead>
                    <tr>
                        <th colspan="4" style="text-align: left; padding: 5px;">HALLAZGOS Y OBSERVACIONES</th>
                    </tr>
                    <tr>
                        <th style="width: 80px;">Fecha</th>
                        <th>Hallazgos / Observaciones</th>
                        <th style="width: 150px;">Responsable Inspección</th>
                        <th style="width: 150px;">Responsable Verificación</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($hallazgos as $h): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($h['fecha'] ?? ''); ?></td>
                            <td style="text-align: left;"><?php echo nl2br(htmlspecialchars($h['observacion'] ?? '')); ?></td>
                            <td><?php echo htmlspecialchars($h['responsable_inspeccion'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($h['responsable_verificacion'] ?? ''); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

    </div>

</body>
</html>
