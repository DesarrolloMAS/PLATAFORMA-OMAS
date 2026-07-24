<?php
require '../../sesion.php';

$host = '127.0.0.1';
$db = 'bitacora_mantenimiento';
$user = 'bitacora_user';
$pass = 'bitacora2026';

try {
    $dsn = "pgsql:host=$host;port=5432;dbname=$db;";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Error crítico: No se pudo conectar a la base de datos.");
}

$filtro_zona = $_GET['zona'] ?? 'Todas';
$filtro_especialidad = $_GET['especialidad'] ?? 'Todas';

$sql = "SELECT * FROM bitacora WHERE 1=1";
$params = [];

if ($filtro_zona !== 'Todas') {
    $sql .= " AND zona = :zona";
    $params[':zona'] = $filtro_zona;
}
if ($filtro_especialidad !== 'Todas') {
    $sql .= " AND especialidad = :especialidad";
    $params[':especialidad'] = $filtro_especialidad;
}
$sql .= " ORDER BY fecha DESC, hora DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $registros = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error al leer la bitácora: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=devicewidth, initial-scale=1.0">
    <title>Reporte Documental - Bitácora de Mantenimiento</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 20px;
        }

        .document-container {
            width: 210mm; /* A4 width */
            min-height: 297mm; /* A4 height */
            margin: 0 auto;
            background: #fff;
            padding: 20mm;
            box-shadow: 0 0 10px rgba(0,0,0,0.2);
            box-sizing: border-box;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .header-table th, .header-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
        }

        .logo-cell { width: 25%; }
        .logo-cell img { max-width: 120px; height: auto; }
        .title-cell { width: 50%; font-weight: bold; font-size: 18px; text-transform: uppercase; }
        .info-cell { width: 25%; font-size: 11px; text-align: left; }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        .data-table th, .data-table td {
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
            vertical-align: middle;
        }

        .data-table th {
            background-color: #f4f4f4;
            font-weight: bold;
        }

        .txt-left { text-align: left !important; }

        .print-toolbar {
            text-align: center;
            margin-bottom: 20px;
        }

        .btn-print {
            padding: 10px 20px;
            background-color: #2563eb;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .btn-print:hover { background-color: #1d4ed8; }

        @media print {
            body { background: none; padding: 0; }
            .document-container { box-shadow: none; padding: 0; width: 100%; }
            .print-toolbar { display: none; }
        }
    </style>
</head>
<body>

<div class="print-toolbar">
    <button class="btn-print" onclick="window.print()">IMPRIMIR / GUARDAR COMO PDF</button>
    <a href="dashboard_bitacora.php" style="margin-left:15px; text-decoration:none; color:#475569; font-family:sans-serif;">Volver</a>
</div>

<div class="document-container">
    <table class="header-table">
        <tr>
            <td class="logo-cell" rowspan="3">
                <!-- Se asume que el logo genérico está en la carpeta de imágenes -->
                <img src="/archivos/formularios/imagen_logo.jpeg" alt="Logo MAS" onerror="this.src='../../img/logo.png'">
            </td>
            <td class="title-cell" rowspan="3">
                SISTEMA DE GESTIÓN DE CALIDAD<br>
                BITÁCORA GENERAL DE MANTENIMIENTO<br>
                <span style="font-size: 10px; font-weight: normal; color: #444;">
                    ZONA: <?= strtoupper($filtro_zona) ?> | ESPECIALIDAD: <?= strtoupper($filtro_especialidad) ?>
                </span>
            </td>
            <td class="info-cell"><strong>CÓDIGO:</strong> GP-MT-FO-010</td>
        </tr>
        <tr>
            <td class="info-cell"><strong>VERSIÓN:</strong> 1</td>
        </tr>
        <tr>
            <td class="info-cell"><strong>FECHA:</strong> 01/2026</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">ID</th>
                <th style="width: 10%;">FECHA</th>
                <th style="width: 15%;">MÁQUINA/EQUIPO</th>
                <th style="width: 10%;">CÓDIGO</th>
                <th style="width: 25%;">DESCRIPCIÓN DE LA FALLA O TRABAJO</th>
                <th style="width: 15%;">TIPO MANT.</th>
                <th style="width: 20%;">TÉCNICO EJECUTOR</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($registros)): ?>
                <tr>
                    <td colspan="7">No hay registros de mantenimiento almacenados en la base de datos PostgreSQL.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($registros as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['id']) ?></td>
                    <td>
                        <?= htmlspecialchars($row['fecha']) ?><br>
                        <small><?= htmlspecialchars($row['hora']) ?></small>
                    </td>
                    <td><strong><?= htmlspecialchars($row['maquina']) ?></strong></td>
                    <td><?= htmlspecialchars($row['codigo'] ?: 'N/A') ?></td>
                    <td class="txt-left"><?= htmlspecialchars($row['descripcion_falla']) ?></td>
                    <td><?= htmlspecialchars($row['tipo_mantenimiento']) ?></td>
                    <td><?= htmlspecialchars($row['tecnico']) ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    
    <div style="margin-top: 30px; font-size: 11px; text-align: center;">
        <p>Documento generado electrónicamente desde la base de datos PostgreSQL automatizada.</p>
    </div>
</div>

</body>
</html>
