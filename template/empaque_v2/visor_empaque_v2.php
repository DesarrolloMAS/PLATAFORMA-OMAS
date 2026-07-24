<?php
require '../sesion.php';

if (!isset($_SESSION['nombre']) || empty($_SESSION['sede'])) {
    header('Location: ../../index.php');
    exit;
}

$sede = $_SESSION['sede'];
$target_file = $_GET['file'] ?? '';

if (empty($target_file)) {
    die("Archivo no especificado. Vuelva a la Galería.");
}

$ruta_json = "../../archivos/generados/empaque_v2/" . preg_replace('/[^A-Za-z0-9_-]/', '', $sede) . "/" . basename($target_file);

if (!file_exists($ruta_json)) {
    die("El archivo de lote no existe o fue eliminado.");
}

$json_content = file_get_contents($ruta_json);
$registros = json_decode($json_content, true) ?: [];

if (empty($registros)) {
    die("El archivo está vacío.");
}

// Ordenar registros por fecha desde la más antigua a la más nueva cronológicamente 
usort($registros, function($a, $b) {
    // Principalmente usamos la fecha de alistamiento de la operación:
    $fechaA = $a['datos']['fecha_alistamiento'] ?? '';
    $fechaB = $b['datos']['fecha_alistamiento'] ?? '';
    if ($fechaA !== $fechaB) {
        return strtotime($fechaA) <=> strtotime($fechaB);
    }
    // Secundariamente el timestamp de registro interno (por si ocurrieron el mismo día)
    $timeA = $a['timestamp'] ?? '';
    $timeB = $b['timestamp'] ?? '';
    return strtotime($timeA) <=> strtotime($timeB);
});

// Extraer metadatos maestros (usamos el primer registro para los datos fijos del lote)
$primer_registro = $registros[0]['datos'];
$referencia = $primer_registro['nombre_empaque'] ?? '';
$lote_interno = $primer_registro['lote_empaque'] ?? '';
$total_unidades = $primer_registro['cantidad_galeria'] ?? 0;

if (!is_numeric($total_unidades)) $total_unidades = 0;

$saldo_actual = $total_unidades;

$proveedor = $primer_registro['proveedor'] ?? 'COMPAÑÍA DE EMPAQUES';
$lote_proveedor = $primer_registro['lote_proveedor'] ?? '';


?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Visor de Empaque - <?= htmlspecialchars($lote_interno) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,300;0,400;0,700;1,400&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            --navy: #0F172A;
            --blue: #003366;
            --white: #FFFFFF;
            --border: #000000;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background: #E2E8F0;
            padding: 20px;
            color: #000;
        }

        .action-bar {
            max-width: 1400px;
            margin: 0 auto 20px auto;
            display: flex;
            justify-content: space-between;
            background: var(--navy);
            padding: 15px 25px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .action-bar button {
            background: #10B981; color: #fff; padding: 10px 20px;
            border: none; border-radius: 4px; font-weight: bold;
            cursor: pointer; text-transform: uppercase;
        }

        .action-bar .btn-back {
            background: transparent; border: 1px solid #00F0FF; color: #00F0FF;
            text-decoration: none; display: flex; align-items: center;
            padding: 10px 20px; border-radius: 4px; font-weight: bold;
        }

        .page-wrap {
            max-width: 1400px;
            margin: 0 auto;
            background: var(--white);
            padding: 30px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }

        /* TABLA MANTENIDA ESTRICTA TIPO EXCEL */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
        }
        
        td, th {
            border: 1px solid var(--border);
            padding: 5px 4px;
            text-align: center;
            vertical-align: middle;
        }

        /* ENCABEZADO INSTITUCIONAL */
        .iso-meta { width: 100%; border-collapse: collapse; height: 100%; }
        .iso-meta td { border: 1px solid var(--border); padding: 5px 8px; }
        .iso-meta tr:first-child td { border-top: none; }
        .iso-meta tr:last-child td { border-bottom: none; }
        .iso-meta td:first-child { border-left: none; width: 60px; font-weight: bold; }
        .iso-meta td:last-child { border-right: none; }

        /* METADATOS TIPO EXCEL */
        .tabla-meta { margin-bottom: 20px; margin-top: 20px; }
        .tabla-meta th { background: var(--blue); color: var(--white); font-size: 8pt; text-transform: uppercase;}
        .tabla-meta td { font-weight: bold; font-size: 9pt;}

        /* TABLA MAESTRA DATOS */
        .tabla-datos th { background: var(--blue); color: var(--white); font-size: 7.5pt; text-transform: uppercase; }
        .tabla-datos td { font-size: 8pt; }
        .bg-gray { background: #F1F5F9; font-weight: bold; }

        @media print {
            body { background: #fff; padding: 0; margin: 0; }
            .action-bar { display: none; }
            .page-wrap { box-shadow: none; padding: 0; max-width: 100%; margin: 0; }
            @page { size: landscape; margin: 10mm; }
        }

        /* ESTILOS MODO CORRECCION */
        .btn-correct { background: #1E293B !important; color: #fff !important; }
        .btn-correct.active { background: #E11D48 !important; }
        .btn-save { background: #10B981 !important; display: none; }
        
        .editable-field {
            transition: all 0.3s;
            border: 1px solid transparent;
        }
        
        .edit-mode .editable-field {
            background: #FEF08A;
            border: 1px dashed #CA8A04;
            cursor: text;
        }
        
        .edit-mode .editable-field:focus {
            background: #FFF;
            outline: 2px solid #CA8A04;
        }

    </style>
</head>
<body>

<div class="action-bar">
    <div style="display: flex; gap: 10px;">
        <a href="rev_empaque_v2.php" class="btn-back">VOLVER AL LISTADO</a>
        <button id="btn-correct" class="btn-correct" onclick="toggleEditMode()">MODO CORRECCIÓN</button>
        <button id="btn-save" class="btn-save" onclick="saveChanges()">GUARDAR CAMBIOS</button>
    </div>
    <button onclick="window.print()">IMPRIMIR O GUARDAR PDF</button>
</div>

<div class="page-wrap" id="document_content">
    
    <!-- ENCABEZADO INSTITUCIONAL ISO -->
    <table style="width:100%; border-collapse: collapse; border: 1px solid #000; margin-bottom: 0;">
        <tr>
            <td style="width: 25%; border-right: 1px solid #000; text-align: center; padding: 12px; vertical-align: middle;">
                <img src="/img/logo_empresa.jpeg" alt="Logo MAS" style="max-height: 80px; max-width: 100%; object-fit: contain;">
            </td>
            <td style="width: 50%; border-right: 1px solid #000; text-align: center; padding: 12px; vertical-align: middle;">
                <div style="font-size: 9pt; font-weight: 600; margin-bottom: 2px;">PPR GESTION DE LA PRODUCCION - Procesamiento Control de Materiales de Empaque</div>
                <div style="font-size: 10.5pt; font-weight: 700;">"Control y Alistamiento de Materiales de Empaque en Almacén"</div>
            </td>
            <td style="width: 25%; padding: 0; vertical-align: top;">
                <table class="iso-meta">
                    <tr><td style="font-size: 8pt;">Código:</td><td style="font-size: 8pt;">GP-PD-FP-GP-FO-003</td></tr>
                    <tr><td style="font-size: 8pt;">Versión:</td><td style="font-size: 8pt;">2</td></tr>
                    <tr><td style="font-size: 8pt;">Fecha:</td><td style="font-size: 8pt;">7/1/2021</td></tr>
                    <tr><td style="font-size: 8pt;">Página:</td><td style="font-size: 8pt;">1 de 1</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- METADATOS LOTE -->
    <table class="tabla-meta" style="width: 100%;">
        <tr>
            <th style="width: 12%;">ZONA</th>
            <td style="width: 20%;"><?= htmlspecialchars($sede) ?></td>
            <th style="width: 15%;">REFERENCIA</th>
            <td style="width: 25%;"><?= htmlspecialchars($referencia) ?></td>
            <th style="width: 15%;">LOTE INTERNO</th>
            <td style="width: 13%;"><?= htmlspecialchars($lote_interno) ?></td>
        </tr>
        <tr>
            <th>PROVEEDOR</th>
            <td id="field-proveedor" class="editable-field" data-field="proveedor"><?= htmlspecialchars($proveedor) ?></td>
            <th>LOTE PROVEEDOR</th>
            <td id="field-lote-proveedor" class="editable-field" data-field="lote_proveedor"><?= htmlspecialchars($lote_proveedor) ?></td>
            <th>TOTAL UNIDADES</th>
            <td id="field-total-unidades" class="editable-field" data-field="total_unidades" style="font-family: 'Space Mono', monospace; font-size: 10pt; color: #000;"><?= $total_unidades ?></td>
        </tr>
    </table>

    <!-- TABLA MAESTRA DE REGISTROS -->
    <table class="tabla-datos">
        <thead>
            <tr>
                <th rowspan="2" style="width: 6%;">FECHA<br>ALISTAM.</th>
                <th rowspan="2" style="width: 8%;">RESPONSABLE</th>
                <th rowspan="2" style="width: 12%;">PRODUCTO A ENVASAR</th>
                <th colspan="3">TIMBRADO</th>
                <th rowspan="2" style="width: 6%;">FECHA<br>ENTREGA</th>
                <th rowspan="2" style="width: 8%;">ENTREGADO A</th>
                <th colspan="3">NUMERO DE EMPAQUES</th>
                <th colspan="2">NO CONFORMES</th>
                <th rowspan="2" class="bg-gray" style="width: 6%;">SALDO<br>LOTE</th>
            </tr>
            <tr>
                <th style="width: 8%;">LOTE PRODUCTO</th>
                <th style="width: 7%;">FECHA VENC.</th>
                <th style="width: 5%;">ETIQ.<br>ADHESIVA</th>
                <th style="width: 5%;">SOLICITADOS</th>
                <th style="width: 5%;">DEVUELTOS</th>
                <th style="width: 5%;">TOTAL ENT.</th>
                <th style="width: 4%;">DE FABRICA</th>
                <th style="width: 4%;">EN PLANTA</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($registros as $reg): 
                $d = $reg['datos'];
                
                // Ignorar registros rápidos que solo sirven para inicializar el JSON
                if (($d['tipo_registro'] ?? '') === 'rapido') continue;
                
                // Parse values safely
                $solicitados = (int)($d['cantidad_solicitada'] ?? 0);
                $devueltos = (int)($d['cantidad_devueltas'] ?? 0);
                $total_entregados = (int)($d['cantidad_total_entregadas'] ?? 0);
                $no_conf_fabrica = (int)($d['cantidad_no_conformes_fabrica'] ?? 0);
                $no_conf_planta = (int)($d['cantidad_no_conformes_planta'] ?? 0);

                // Cálculo estricto del saldo descendente (Formula: Saldo anterior - Total Entregados)
                // Nota: Asumo que en la lógica contable el total entregado es lo que se deduce del inventario general. 
                // Si la planta y fábrica afectan adicional, ajustaremos, pero matemáticamente coincide con lo general.
                $saldo_actual = $saldo_actual - $total_entregados;
            ?>
            <tr>
                <td><?= htmlspecialchars($d['fecha_alistamiento'] ?? '') ?></td>
                <td><?= htmlspecialchars($d['responsable_alistamiento'] ?? '') ?></td>
                <td><?= htmlspecialchars($d['producto_envasar'] ?? '') ?></td>
                
                <td><?= htmlspecialchars($d['lote_producto'] ?? '') ?></td>
                <td><?= htmlspecialchars($d['fecha_vencimiento'] ?? '') ?></td>
                <td><?= htmlspecialchars($d['etiquetas_adhesivas'] ?? '') ?></td>
                
                <td><?= htmlspecialchars($d['fecha_entrega_salida'] ?? '') ?></td>
                <td><?= htmlspecialchars($d['entregado_a'] ?? '') ?></td>
                
                <td><?= $solicitados ?: '0' ?></td>
                <td><?= $devueltos ?: '0' ?></td>
                <td><?= $total_entregados ?: '0' ?></td>
                
                <td><?= $no_conf_fabrica ?: '0' ?></td>
                <td><?= $no_conf_planta ?: '0' ?></td>
                
                <td class="bg-gray" style="text-align: right; padding-right: 8px; font-family: 'Space Mono', monospace;">
                    <?= $saldo_actual ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</div>

<script>
    let isEditMode = false;

    function toggleEditMode() {
        isEditMode = !isEditMode;
        const btn = document.getElementById('btn-correct');
        const btnSave = document.getElementById('btn-save');
        const documentContent = document.getElementById('document_content');

        if (isEditMode) {
            btn.classList.add('active');
            btn.innerText = 'CANCELAR CORRECCIÓN';
            btnSave.style.display = 'block';
            documentContent.classList.add('edit-mode');
            
            // Hacer campos editables
            document.querySelectorAll('.editable-field').forEach(el => {
                el.contentEditable = true;
            });
        } else {
            btn.classList.remove('active');
            btn.innerText = 'MODO CORRECCIÓN';
            btnSave.style.display = 'none';
            documentContent.classList.remove('edit-mode');
            
            // Quitar editabilidad
            document.querySelectorAll('.editable-field').forEach(el => {
                el.contentEditable = false;
            });
            
            // Recargar para deshacer cambios visuales no guardados
            location.reload();
        }
    }

    async function saveChanges() {
        const updates = {
            proveedor: document.getElementById('field-proveedor').innerText.trim(),
            lote_proveedor: document.getElementById('field-lote-proveedor').innerText.trim(),
            total_unidades: document.getElementById('field-total-unidades').innerText.trim()
        };

        // Validar que total_unidades sea número
        if (isNaN(updates.total_unidades) || updates.total_unidades === '') {
            Swal.fire('Error', 'El total de unidades debe ser un número válido.', 'error');
            return;
        }

        try {
            const response = await fetch('corregir_empaque.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    file: '<?= basename($target_file) ?>',
                    updates: updates
                })
            });

            const result = await response.json();

            if (result.success) {
                Swal.fire({
                    title: '¡Guardado!',
                    text: 'Los cambios se han aplicado correctamente.',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('Error', result.error || 'No se pudieron guardar los cambios.', 'error');
            }
        } catch (error) {
            console.error('Error saving changes:', error);
            Swal.fire('Error', 'Hubo un problema de conexión al guardar.', 'error');
        }
    }
</script>

</body>
</html>
