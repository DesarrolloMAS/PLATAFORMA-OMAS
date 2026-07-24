<?php
require '../sesion.php';

if (!isset($_SESSION['nombre']) || empty($_SESSION['sede'])) {
    header('Location: ../../index.php');
    exit;
}

$sede        = $_SESSION['sede'];
$target_file = $_GET['file'] ?? '';

if (empty($target_file)) {
    die("Archivo no especificado. Vuelva a la Galería.");
}

$ruta_json = "../../archivos/generados/control_mejorante/"
           . preg_replace('/[^A-Za-z0-9_-]/', '', $sede) . "/"
           . basename($target_file);

if (!file_exists($ruta_json)) {
    die("El archivo no existe o fue eliminado.");
}

$registros = json_decode(file_get_contents($ruta_json), true) ?: [];

if (empty($registros)) {
    die("El archivo está vacío.");
}

// Ordenar cronológicamente
usort($registros, function($a, $b) {
    $fA = $a['datos']['fecha'] ?? '';
    $fB = $b['datos']['fecha'] ?? '';
    if ($fA !== $fB) return strtotime($fA) <=> strtotime($fB);
    return strtotime($a['timestamp'] ?? '') <=> strtotime($b['timestamp'] ?? '');
});

$periodo = str_replace(['MEJORANTE_', '.json'], '', basename($target_file));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visor - Control de Mejorante | <?= htmlspecialchars($periodo) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
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

        .action-bar .left { display: flex; gap: 10px; flex-wrap: wrap; }

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

        .page-wrap {
            max-width: 99%;
            margin: 0 auto;
            background: var(--white);
            padding: 24px 28px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            page-break-after: always;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td, th {
            border: 1px solid var(--border);
            padding: 4px 5px;
            vertical-align: middle;
            text-align: center;
        }

        .header-table td {
            border: 1px solid #000;
            vertical-align: middle;
            padding: 8px 10px;
        }

        .header-title-main {
            font-size: 9.5pt;
            font-weight: 600;
            margin-bottom: 3px;
        }
        .header-title-doc {
            font-size: 11pt;
            font-weight: 700;
        }

        .iso-meta { width: 100%; border-collapse: collapse; height: 100%; }
        .iso-meta td {
            border: 0;
            border-bottom: 1px solid #000;
            padding: 3px 8px;
            font-size: 7.5pt;
            text-align: left;
        }
        .iso-meta tr:last-child td { border-bottom: 0; }
        .iso-meta td:first-child { font-weight: 700; border-right: 1px solid #000; width: 45%; }

        .zona-row {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0;
        }
        .zona-row td {
            border: 1px solid #000;
            padding: 5px 10px;
            font-size: 8pt;
        }
        .zona-label {
            font-weight: 700;
            width: 55px;
            background: #F1F5F9;
        }

        .tabla-datos {
            margin-top: 10px;
            font-size: 7pt;
        }

        .tabla-datos thead th {
            background: #f1f5f9;
            color: #000;
            font-size: 7pt;
            font-weight: 700;
            line-height: 1.2;
            padding: 5px;
        }

        .tabla-datos tbody td {
            font-size: 7.5pt;
            padding: 5px 4px;
            vertical-align: middle;
        }

        .section-title {
            background: #e2e8f0;
            font-weight: bold;
            text-align: left;
            padding-left: 10px;
        }

        @media print {
            body        { background: #fff; padding: 0; }
            .action-bar { display: none !important; }
            .page-wrap  { box-shadow: none; padding: 10px; max-width: 100%; page-break-after: always; }
            @page       { size: portrait; margin: 8mm; }
        }
    </style>
</head>
<body>

<div class="action-bar">
    <div class="left">
        <a href="rev_control_mejorante.php" class="btn-back">← Volver al Listado</a>
    </div>
    <button class="btn-print" onclick="window.print()">
        🖨️ IMPRIMIR / GUARDAR PDF
    </button>
</div>

<?php foreach ($registros as $index => $reg): $d = $reg['datos']; ?>
<div class="page-wrap">
    <table class="header-table" style="margin-bottom:0;">
        <tr>
            <td style="width:20%; text-align:center; padding:10px;">
                <img src="/img/logo_empresa.jpeg" alt="Logo MAS" style="max-height:60px; max-width:100%; object-fit:contain;">
            </td>
            <td style="width:60%; text-align:center; padding:10px;">
                <div class="header-title-main">Gestión de la Producción</div>
                <div class="header-title-doc">"Preparación y Control de Mejorante"</div>
            </td>
            <td style="width:20%; padding:0; vertical-align:top;">
                <table class="iso-meta" style="height:100%;">
                    <tr><td>Código:</td><td>FO-PR-00X</td></tr>
                    <tr><td>Versión:</td><td>1</td></tr>
                    <tr><td>Fecha:</td><td><?= date('d/m/Y') ?></td></tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="zona-row">
        <tr>
            <td class="zona-label">ZONA:</td>
            <td style="width:200px; font-weight:700; font-size:9pt;"><?= htmlspecialchars($sede) ?></td>
            <td class="zona-label" style="width:100px;">FECHA REGISTRO:</td>
            <td style="font-weight:700; font-size:9pt;"><?= htmlspecialchars($d['fecha'] ?? '') ?></td>
        </tr>
    </table>

    <table class="tabla-datos">
        <tr>
            <td class="section-title" colspan="4">INFORMACIÓN GENERAL</td>
        </tr>
        <tr>
            <th width="25%">Referencia</th>
            <td width="25%"><?= htmlspecialchars($d['referencia'] ?? '') ?></td>
            <th width="25%">Lote</th>
            <td width="25%"><?= htmlspecialchars($d['lote'] ?? '') ?></td>
        </tr>
        <tr>
            <th>Vence</th>
            <td><?= htmlspecialchars($d['vence'] ?? '') ?></td>
            <th>Tiempo Mezcla (Min)</th>
            <td><?= htmlspecialchars($d['tiempo_mezcla'] ?? '') ?></td>
        </tr>
    </table>

    <table class="tabla-datos">
        <thead>
            <tr>
                <th width="40%">MEJORANTE</th>
                <th width="20%">Lote</th>
                <th width="20%">Fecha Vencimiento</th>
                <th width="20%">Cantidad</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $mejorantes = [
                'grindamyl_a1000' => 'Grindamyl A1000',
                'powerbake_7200'  => 'Powerbake 7200',
                'ada_50_'         => 'ADA 50%',
                '_cido_asc_rbico' => 'Ácido Ascórbico',
                'surebake_900'    => 'Surebake 900',
                'powerbake_4200'  => 'Powerbake 4200',
                'granozyme_oxd'   => 'Granozyme OXD'
            ];
            foreach ($mejorantes as $id => $nombre):
            ?>
            <tr>
                <td style="text-align:left; font-weight:bold;"><?= $nombre ?></td>
                <td><?= htmlspecialchars($d['lote_'.$id] ?? '') ?></td>
                <td><?= htmlspecialchars($d['vencimiento_'.$id] ?? '') ?></td>
                <td><?= htmlspecialchars($d['cantidad_'.$id] ?? '') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" style="text-align:right; font-weight:bold;">TOTAL:</td>
                <td style="font-weight:bold;"><?= htmlspecialchars($d['total'] ?? '') ?></td>
            </tr>
        </tfoot>
    </table>

    <table class="tabla-datos">
        <tr>
            <th width="25%">DEVOLUCIÓN</th>
            <td width="75%" style="text-align:left;"><?= htmlspecialchars($d['devolucion'] ?? '') ?></td>
        </tr>
        <tr>
            <th>OBSERVACIONES</th>
            <td style="text-align:left;"><?= nl2br(htmlspecialchars($d['observaciones'] ?? '')) ?></td>
        </tr>
    </table>

    <table class="tabla-datos" style="margin-top:20px;">
        <tr>
            <td style="height:60px; vertical-align:bottom;">
                <hr style="width:80%; margin:0 auto; border-color:#000;">
                <br><?= htmlspecialchars($d['realiza'] ?? '') ?>
            </td>
            <td style="height:60px; vertical-align:bottom;">
                <hr style="width:80%; margin:0 auto; border-color:#000;">
                <br><?= htmlspecialchars($d['verifica'] ?? '') ?>
            </td>
        </tr>
        <tr>
            <th width="50%">REALIZA</th>
            <th width="50%">VERIFICA</th>
        </tr>
    </table>
</div>
<?php endforeach; ?>

</body>
</html>
