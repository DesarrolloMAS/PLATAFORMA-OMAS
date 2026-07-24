<?php
/**
 * guardar_edicion_inspeccion_insumos.php
 * ---------------------
 * Recibe las correcciones del formulario editar_inspeccion_insumos.php,
 * localiza el registro por id_registro dentro del JSON del mes/sede y
 * sobrescribe solo los campos editables (whitelist). Recalcula el
 * porcentaje de cumplimiento por ítem y el promedio general con la misma
 * fórmula usada al crear el registro (index.html).
 */
require_once '../../sesion.php';
verificarAutenticacion();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Método no permitido.');
}

$sede     = $_SESSION['sede'] ?? '';
$sede_san = preg_replace('/[^A-Za-z0-9_-]/', '', $sede);

$id   = trim($_POST['id']   ?? '');
$file = trim($_POST['file'] ?? '');

$base_dir  = realpath(__DIR__ . '/../../../archivos/generados/inspeccion_insumos/' . $sede_san);
$real_file = realpath($file);

if (!$id || !$file || !$real_file || !$base_dir || strpos($real_file, $base_dir) !== 0) {
    die('<p style="color:red;font-family:monospace;padding:40px;">Registro no válido o no pertenece a su sede.</p>');
}

$camposHeader  = ['fecha_inspeccion', 'hora_inspeccion', 'planta', 'inspeccionado_por', 'verificado_por'];
$camposItem    = ['materia_prima', 'lote_interno', 'lote_proveedor', 'fecha_vencimiento', 'proveedor', 'observaciones'];
$camposEval    = ['eval_vigente', 'eval_etiquetado', 'eval_plagas', 'eval_envase', 'eval_sap'];

$contenido = json_decode(file_get_contents($real_file), true) ?: [];
$encontrado = false;

foreach ($contenido as &$r) {
    if (($r['id_registro'] ?? '') !== $id) continue;
    $encontrado = true;

    foreach ($camposHeader as $campo) {
        if (isset($_POST[$campo])) {
            $r['datos'][$campo] = trim($_POST[$campo]);
        }
    }

    $itemsPost = $_POST['items'] ?? [];
    $sumaPct = 0; $conPct = 0;

    $insumos = $r['datos']['insumos'] ?? [];
    foreach ($insumos as $idx => &$it) {
        if (!isset($itemsPost[$idx])) continue;

        foreach ($camposItem as $campo) {
            if (isset($itemsPost[$idx][$campo])) {
                $it[$campo] = trim($itemsPost[$idx][$campo]);
            }
        }
        foreach ($camposEval as $campo) {
            if (isset($itemsPost[$idx][$campo]) && in_array($itemsPost[$idx][$campo], ['0', '1', ''], true)) {
                $it[$campo] = $itemsPost[$idx][$campo];
            }
        }

        $suma = 0; $evaluados = 0;
        foreach ($camposEval as $campo) {
            if (($it[$campo] ?? '') === '1' || ($it[$campo] ?? '') === '0') {
                $evaluados++;
                $suma += (int)$it[$campo];
            }
        }
        $it['porcentaje_cumplimiento'] = $evaluados > 0 ? round(($suma / $evaluados) * 100, 2) : null;

        if ($it['porcentaje_cumplimiento'] !== null) {
            $sumaPct += $it['porcentaje_cumplimiento'];
            $conPct++;
        }
    }
    unset($it);
    $r['datos']['insumos'] = $insumos;

    $r['datos']['promedio_cumplimiento'] = number_format($conPct > 0 ? $sumaPct / $conPct : 0, 2, '.', '');
    break;
}
unset($r);

if (!$encontrado) {
    die('<p style="color:red;font-family:monospace;padding:40px;">Registro con ID "' . htmlspecialchars($id) . '" no encontrado.</p>');
}

$guardado = @file_put_contents($real_file, json_encode($contenido, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Corrección guardada</title>
    <style>
        body { font-family: 'Barlow', sans-serif; background: #0B0E14; color: #E2E8F0; padding: 60px 20px; text-align: center; }
        .box { max-width: 480px; margin: 0 auto; background: #151A22; border: 1px solid #1E293B; border-radius: 8px; padding: 30px; }
        a { display: inline-block; margin-top: 16px; color: #00F0FF; text-decoration: none; font-family: monospace; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="box">
        <?php if ($guardado): ?>
            <p>✓ Registro corregido correctamente.</p>
        <?php else: ?>
            <p style="color:#FF3366;">✗ No se pudo guardar la corrección (permisos de disco).</p>
        <?php endif; ?>
        <a href="visor_inspeccion_insumos.php?id=<?= urlencode($id) ?>&file=<?= urlencode($file) ?>">Ver registro corregido →</a><br>
        <a href="rev_inspeccion_insumos.php">← Volver a la galería</a>
    </div>
</body>
</html>
