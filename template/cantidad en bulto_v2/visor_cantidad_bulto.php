<?php
require '../sesion.php';

if (!isset($_SESSION['nombre']) || empty($_SESSION['sede'])) {
    header('Location: ../../index.php');
    exit;
}

$sede        = $_SESSION['sede'];
$target_file = $_GET['file'] ?? '';

if (empty($target_file)) {
    die("Archivo no especificado. Vuelva a la galería.");
}

$ruta_json = "../../archivos/generados/cantidad_bulto/"
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
    $fA = ($a['datos']['fecha'] ?? '') . ' ' . ($a['datos']['hora'] ?? '');
    $fB = ($b['datos']['fecha'] ?? '') . ' ' . ($b['datos']['hora'] ?? '');
    return strcmp($fA, $fB);
});

// Datos del producto desde el primer registro
$primer   = reset($registros);
$producto = $primer['datos']['harina']        ?? str_replace(['_', '.json'], [' ', ''], basename($target_file));
$peso_ref = $primer['datos']['peso_producto'] ?? '—';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visor - <?= htmlspecialchars($producto) ?></title>
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

        /* ── ACTION BAR ── */
        .action-bar {
            max-width: 99%;
            margin: 0 auto 20px;
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

        .action-bar .left { display: flex; gap: 10px; align-items: center; }

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
            transition: all 0.2s;
        }
        .btn-back:hover { background: rgba(0,240,255,0.1); }

        .btn-correct {
            background: #F59E0B;
            color: #fff;
            border: none;
            padding: 9px 18px;
            border-radius: 4px;
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
            cursor: pointer;
            transition: background 0.2s;
            margin-right: 10px;
        }
        .btn-correct:hover { background: #D97706; }
        .btn-correct.active { background: #DC2626; }
        .btn-correct.active:hover { background: #B91C1C; }

        .edit-mode .editable-dr {
            background: #FEF3C7;
            border: 1px dashed #F59E0B;
            padding: 2px 4px;
            cursor: text;
            display: inline-block;
            min-width: 20px;
            min-height: 14px;
        }
        .edit-mode .editable-dr:focus {
            background: #fff;
            outline: none;
            border: 1px solid #D97706;
        }

        .product-label {
            color: #E2E8F0;
            font-size: 13px;
            font-weight: 600;
            padding: 0 8px;
            border-left: 2px solid var(--accent);
        }

        .btn-print {
            background: #10B981;
            color: #fff;
            border: none;
            padding: 9px 18px;
            border-radius: 4px;
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-print:hover { background: #059669; }

        /* ── DOCUMENT ── */
        .page-wrap {
            max-width: 99%;
            margin: 0 auto;
            background: var(--white);
            padding: 24px 28px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        table { width: 100%; border-collapse: collapse; }
        td, th { border: 1px solid var(--border); padding: 4px 5px; vertical-align: middle; text-align: center; }

        /* ── INSTITUTIONAL HEADER ── */
        .header-table td { border: 1px solid #000; vertical-align: middle; padding: 8px 10px; }
        .header-title-main { font-size: 9.5pt; font-weight: 600; margin-bottom: 3px; }
        .header-title-doc  { font-size: 11pt; font-weight: 700; }

        .iso-meta { width: 100%; border-collapse: collapse; height: 100%; }
        .iso-meta td {
            border: 0; border-bottom: 1px solid #000;
            padding: 3px 8px; font-size: 7.5pt; text-align: left;
        }
        .iso-meta tr:last-child td { border-bottom: 0; }
        .iso-meta td:first-child { font-weight: 700; border-right: 1px solid #000; width: 45%; }

        /* ── DATA TABLE ── */
        .tabla-datos { margin-top: 0; font-size: 7.5pt; }

        .tabla-datos thead th {
            background: var(--blue);
            color: #fff;
            font-size: 7pt;
            font-weight: 600;
            text-transform: uppercase;
            padding: 6px 4px;
        }

        .tabla-datos tbody td { font-size: 8pt; padding: 5px 4px; }
        .tabla-datos tbody tr:nth-child(even) { background: #F8FAFC; }
        .tabla-datos tbody tr:hover           { background: #EFF6FF; }

        .col-promedio { font-weight: 700; background: #EFF6FF !important; color: #003366; }

        tfoot td {
            background: #F0F9FF;
            font-weight: 700;
            font-size: 7.5pt;
            border-top: 2px solid #003366;
            padding: 6px 8px;
        }

        .empty-row td { padding: 30px; color: #64748B; font-style: italic; }

        @media print {
            body        { background: #fff; padding: 0; }
            .action-bar { display: none !important; }
            .page-wrap  { box-shadow: none; padding: 10px; max-width: 100%; }
            @page       { size: landscape; margin: 8mm; }
        }
    </style>
</head>
<body>

<!-- ACTION BAR -->
<div class="action-bar">
    <div class="left">
        <a href="rev_cantidad_bulto.php" class="btn-back">← Volver al Listado</a>
        <span class="product-label"><?= htmlspecialchars($producto) ?></span>
    </div>
    <div class="right">
        <button class="btn-correct" id="btn-correct" onclick="toggleEditMode()">✏️ CORREGIR</button>
        <button class="btn-print" onclick="window.print()">🖨️ IMPRIMIR / GUARDAR PDF</button>
    </div>
</div>

<!-- DOCUMENT -->
<div class="page-wrap">

    <!-- ENCABEZADO INSTITUCIONAL -->
    <table class="header-table" style="margin-bottom:0;">
        <tr>
            <td style="width:18%; text-align:center; padding:10px;">
                <img src="/img/logo_empresa.jpeg" alt="Logo MAS"
                     style="max-height:70px; max-width:100%; object-fit:contain;">
            </td>
            <td style="width:60%; text-align:center; padding:10px;">
                <div class="header-title-main">PPR Gestión de la Producción</div>
                <div class="header-title-main" style="margin-bottom:4px;">Control de Cantidad / Producto en Bulto</div>
                <div class="header-title-doc"><?= htmlspecialchars($producto) ?> &nbsp;|&nbsp; Peso ref.: <?= htmlspecialchars($peso_ref) ?> kg &nbsp;|&nbsp; Sede: <?= htmlspecialchars($sede) ?></div>
            </td>
            <td style="width:22%; padding:0; vertical-align:top;">
                <table class="iso-meta" style="height:100%;">
                    <tr><td>Código:</td><td>GP-PD-PP-CB-FO-001</td></tr>
                    <tr><td>Versión:</td><td>2</td></tr>
                    <tr><td>Registros:</td><td><?= count($registros) ?></td></tr>
                    <tr><td>Impreso:</td><td><?= date('d/m/Y') ?></td></tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- TABLA DE REGISTROS -->
    <table class="tabla-datos">
        <thead>
            <tr>
                <th style="width:8%;">Fecha</th>
                <th style="width:5%;">Hora</th>
                <th style="width:6%;">Lote</th>
                <th style="width:5%;">Bulto 1</th>
                <th style="width:5%;">Bulto 2</th>
                <th style="width:5%;">Bulto 3</th>
                <th style="width:5%;">Bulto 4</th>
                <th style="width:5%;">Bulto 5</th>
                <th style="width:5%;">Bulto 6</th>
                <th style="width:5%;">Bulto 7</th>
                <th style="width:5%;">Bulto 8</th>
                <th style="width:5%;">Bulto 9</th>
                <th style="width:5%;">Bulto 10</th>
                <th style="width:7%;">Promedio (kg)</th>
                <th style="width:10%;">Responsable</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($registros)): ?>
            <tr class="empty-row"><td colspan="15">No hay registros.</td></tr>
        <?php else: ?>
            <?php
            $suma_promedios = 0;
            $cnt_promedios  = 0;

            foreach ($registros as $reg):
                $d = $reg['datos'];
                $id_reg = $reg['id_registro'] ?? '';

                $bultos = [];
                for ($i = 1; $i <= 10; $i++) {
                    $v = $d['bulto_' . $i] ?? null;
                    $bultos[$i] = ($v !== null && $v !== '') ? floatval($v) : null;
                }

                $vals_validos = array_filter($bultos, fn($v) => $v !== null);
                $promedio     = count($vals_validos) > 0
                    ? round(array_sum($vals_validos) / count($vals_validos), 3)
                    : null;

                if ($promedio !== null) {
                    $suma_promedios += $promedio;
                    $cnt_promedios++;
                }

            ?>
            <tr>
                <td><span class="editable-dr" data-id="<?= htmlspecialchars($id_reg) ?>" data-field="fecha"><?= htmlspecialchars($d['fecha'] ?? '') ?></span></td>
                <td><span class="editable-dr" data-id="<?= htmlspecialchars($id_reg) ?>" data-field="hora"><?= htmlspecialchars($d['hora'] ?? '') ?></span></td>
                <td><span class="editable-dr" data-id="<?= htmlspecialchars($id_reg) ?>" data-field="lote"><?= htmlspecialchars($d['lote'] ?? '') ?></span></td>
                <?php for ($i = 1; $i <= 10; $i++): ?>
                    <td><span class="editable-dr" data-id="<?= htmlspecialchars($id_reg) ?>" data-field="bulto_<?= $i ?>"><?= $bultos[$i] !== null ? htmlspecialchars($bultos[$i]) : '' ?></span></td>
                <?php endfor; ?>
                <td class="col-promedio">
                    <?= $promedio !== null ? number_format($promedio, 3) : '—' ?>
                </td>
                <td><span class="editable-dr" data-id="<?= htmlspecialchars($id_reg) ?>" data-field="responsable"><?= htmlspecialchars($d['responsable'] ?? ($reg['usuario_sys'] ?? '')) ?></span></td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>

        <?php if ($cnt_promedios > 0): ?>
        <tfoot>
            <tr>
                <td colspan="13" style="text-align:right; padding-right:12px;">
                    Promedio general (<?= count($registros) ?> registro<?= count($registros) !== 1 ? 's' : '' ?>):
                </td>
                <td class="col-promedio">
                    <?= number_format($suma_promedios / $cnt_promedios, 3) ?> kg
                </td>
                <td></td>
            </tr>
        </tfoot>
        <?php endif; ?>
    </table>

</div>
<script>
let editModeActive = false;
function toggleEditMode() {
    let btn = document.getElementById('btn-correct');
    if (!editModeActive) {
        editModeActive = true;
        document.body.classList.add('edit-mode');
        document.querySelectorAll('.editable-dr').forEach(el => el.setAttribute('contenteditable', 'true'));
        btn.innerText = '💾 GUARDAR CORRECCIÓN';
        btn.classList.add('active');
    } else {
        let updates = [];
        document.querySelectorAll('.editable-dr').forEach(el => {
            updates.push({
                id: el.getAttribute('data-id'),
                field: el.getAttribute('data-field'),
                val: el.innerText.trim()
            });
        });

        btn.innerText = 'GUARDANDO...';
        fetch('corregir_datos.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                sede: '<?= htmlspecialchars($sede) ?>',
                target_file: '<?= htmlspecialchars(basename($target_file)) ?>',
                updates: updates
            })
        })
        .then(r => r.json())
        .then(res => {
            if(res.success) {
                editModeActive = false;
                document.body.classList.remove('edit-mode');
                document.querySelectorAll('.editable-dr').forEach(el => el.removeAttribute('contenteditable'));
                btn.innerText = '¡GUARDADO!';
                btn.classList.remove('active');
                setTimeout(() => window.location.reload(), 800);
            } else {
                alert('Error al guardar: ' + res.error);
                btn.innerText = '💾 GUARDAR CORRECCIÓN';
            }
        })
        .catch(err => {
            console.error('Error fetch:', err);
            alert('Error de conexión al guardar.');
            btn.innerText = '💾 GUARDAR CORRECCIÓN';
        });
    }
}
</script>
</body>
</html>
