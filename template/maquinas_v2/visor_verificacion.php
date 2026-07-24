<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../sesion.php';
verificarAutenticacion();

function sanear_ruta($valor) {
    return preg_replace('/_+/', '_', preg_replace('/[^A-Za-z0-9_\-]/', '_', trim($valor)));
}

$tipo   = $_GET['tipo']   ?? '';
$grupo  = $_GET['maquina'] ?? '';
$codigo = $_GET['codigo'] ?? '';
$id     = $_GET['id']    ?? '';

$archivo = __DIR__ . "/../../archivos/generados/maquinas_v2/" . sanear_ruta($tipo) . "/" . sanear_ruta($grupo) . "/" . sanear_ruta($codigo) . ".json";

if (!file_exists($archivo)) {
    die("❌ No se encontró el historial de esta máquina.");
}

$registros = json_decode(file_get_contents($archivo), true) ?: [];
if (empty($registros)) {
    die("❌ Esta máquina no tiene registros.");
}

$registro = null;
foreach ($registros as $r) {
    if (($r['id_registro'] ?? '') === $id) { $registro = $r; break; }
}
if (!$registro) { $registro = end($registros); } // por defecto, el más reciente

$datos = $registro['datos'] ?? [];

$config_json = json_decode(file_get_contents(__DIR__ . '/config_formularios.json'), true);
$cfg = $config_json[$tipo] ?? null;
if (!$cfg) {
    die("❌ No existe configuración para el tipo de máquina: " . htmlspecialchars($tipo));
}

// Si es una corrección, ubicar el registro original al que corrige
$registro_original = null;
if (($registro['tipo_registro'] ?? '') === 'correccion' && !empty($registro['corrige_id'])) {
    foreach ($registros as $r) {
        if (($r['id_registro'] ?? '') === $registro['corrige_id']) { $registro_original = $r; break; }
    }
}

function valor($datos, $name) {
    return htmlspecialchars((string)($datos[$name] ?? ''));
}

$logo_empresa = '/fmt/img/logo_empresa.jpeg';
$fecha_doc = substr($registro['timestamp'] ?? date('Y-m-d H:i:s'), 0, 10);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700&family=Space+Mono:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/maquinas_v2.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<title>Visor · <?= htmlspecialchars($cfg['titulo']) ?></title>
</head>
<body>
<div class="container">
    <div class="header-box no-print">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
            <div>
                <h1 class="main-title">📄 Visor de Verificación</h1>
                <div class="badge-mantenimiento">🔧 Área de Mantenimiento · Mecánicos</div>
            </div>
            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <button class="btn-submit" onclick="exportarPDF()">⬇️ Descargar PDF</button>
                <a class="btn-back" href="revision_maquinas.php">← Volver a revisión</a>
            </div>
        </div>
    </div>

    <div class="visor-doc" id="documento">
        <div class="visor-watermark"><img src="<?= $logo_empresa ?>" alt=""></div>

        <div class="visor-header-mas">
            <img src="<?= $logo_empresa ?>" alt="MAS">
            <div>
                <div style="font-weight:700; text-transform:uppercase;">Somos Más que Harina</div>
                <div style="font-size:12px; color:#666;">Código: OPE-ME-FO-002 · Versión 4</div>
            </div>
        </div>

        <div class="visor-titulo"><?= htmlspecialchars($cfg['titulo']) ?></div>

        <div class="visor-datos-maquina">
            <span><strong>Código de Máquina:</strong> <?= htmlspecialchars($codigo) ?></span>
            <span><strong>Grupo / Modelo:</strong> <?= htmlspecialchars($grupo) ?></span>
            <span><strong>Fecha:</strong> <?= htmlspecialchars($fecha_doc) ?></span>
            <?php if (!empty($registro['codigo_orden'])): ?>
            <span><strong>Orden de Trabajo:</strong> <?= htmlspecialchars($registro['codigo_orden']) ?></span>
            <?php endif; ?>
        </div>

        <div class="visor-datos-maquina">
            <span><strong>Técnico:</strong> <?= htmlspecialchars($registro_original['usuario_sys'] ?? $registro['usuario_sys'] ?? '—') ?></span>
            <?php if ($registro_original): ?>
            <span><strong>Técnico que revisa (corrección):</strong> <?= htmlspecialchars($registro['usuario_sys'] ?? '—') ?></span>
            <?php endif; ?>
        </div>

        <div class="visor-seccion-titulo">Verificación de Estado y Funcionamiento</div>
        <table class="visor-tabla">
            <tr><th>Chequeo</th><th>Resultado</th></tr>
            <?php foreach ($cfg['campos_estado'] as $campo): ?>
            <tr><td><?= htmlspecialchars($campo['label']) ?></td><td><?= valor($datos, $campo['name']) ?></td></tr>
            <?php endforeach; ?>
        </table>

        <?php if (!empty($cfg['escalas_lectura'][$codigo])): ?>
        <div class="visor-seccion-titulo">Escala de Lectura de esta Máquina</div>
        <p><?= htmlspecialchars($cfg['escalas_lectura'][$codigo]) ?></p>
        <?php endif; ?>

        <?php if (!empty($cfg['rangos_emp'][$codigo])): ?>
        <div class="visor-seccion-titulo">Rangos EMP de esta Máquina (NTC 2031)</div>
        <p><?= nl2br(htmlspecialchars($cfg['rangos_emp'][$codigo])) ?></p>
        <p style="font-size:11px; color:#888; font-style:italic;">Información de referencia según NTC 2031. Uso exclusivamente informativo.</p>
        <?php endif; ?>

        <?php foreach (($cfg['bloques_calibracion'] ?? []) as $bloque): ?>
        <div class="visor-seccion-titulo"><?= htmlspecialchars($bloque['titulo']) ?></div>
        <?php if (!empty($bloque['nota'])): ?><p style="font-size:12px; color:#666;"><?= htmlspecialchars($bloque['nota']) ?></p><?php endif; ?>
        <p><strong><?= htmlspecialchars($bloque['campo_base']['label']) ?>:</strong> <?= valor($datos, $bloque['campo_base']['name']) ?></p>
        <table class="visor-tabla">
            <tr>
                <th>Punto</th>
                <?php foreach ($bloque['campos_por_punto'] as $cp): ?><th><?= htmlspecialchars($cp['label']) ?></th><?php endforeach; ?>
            </tr>
            <?php foreach ($bloque['puntos'] as $i => $etiqueta): ?>
            <tr>
                <td><?= htmlspecialchars($etiqueta) ?></td>
                <?php foreach ($bloque['campos_por_punto'] as $cp): ?>
                <td><?= valor($datos, $bloque['prefijo_punto'] . ($i + 1) . '_' . $cp['sufijo']) ?></td>
                <?php endforeach; ?>
            </tr>
            <?php endforeach; ?>
        </table>
        <p><strong><?= htmlspecialchars($bloque['campo_resultado']['label']) ?>:</strong> <?= valor($datos, $bloque['campo_resultado']['name']) ?></p>
        <?php endforeach; ?>

        <?php if (!empty($cfg['campos_extra'])): ?>
        <div class="visor-seccion-titulo"><?= htmlspecialchars($cfg['campos_extra']['titulo']) ?></div>
        <table class="visor-tabla">
            <tr><th>Campo</th><th>Valor</th></tr>
            <?php foreach ($cfg['campos_extra']['campos'] as $campo): ?>
            <tr><td><?= htmlspecialchars($campo['label']) ?></td><td><?= valor($datos, $campo['name']) ?></td></tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>

        <?php if (!empty($cfg['observaciones'])): ?>
        <div class="visor-seccion-titulo">Observaciones</div>
        <div class="visor-observaciones"><?= nl2br(valor($datos, 'observaciones')) ?></div>
        <?php endif; ?>
    </div>
</div>

<script>
async function exportarPDF() {
    const { jsPDF } = window.jspdf;
    const elemento = document.getElementById('documento');

    const canvas = await html2canvas(elemento, { scale: 2, useCORS: true });
    const imgData = canvas.toDataURL('image/png');
    const pdf = new jsPDF('p', 'mm', 'a4');
    const propsImg = pdf.getImageProperties(imgData);
    const anchoPdf = pdf.internal.pageSize.getWidth();
    const altoPdf = (propsImg.height * anchoPdf) / propsImg.width;

    pdf.addImage(imgData, 'PNG', 0, 0, anchoPdf, altoPdf);
    pdf.save(`Verificacion_<?= htmlspecialchars(sanear_ruta($tipo)) ?>_<?= htmlspecialchars(sanear_ruta($codigo)) ?>_<?= htmlspecialchars($fecha_doc) ?>.pdf`);
}
</script>
</body>
</html>
