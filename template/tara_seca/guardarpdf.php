<?php
require_once __DIR__ . '/../../vendor/autoload.php'; // Ajusta la ruta según tu estructura

use Dompdf\Dompdf;
use Dompdf\Options;

// Recibe los datos POST
$fecha = isset($_POST['fecha']) ? htmlspecialchars($_POST['fecha']) : '';
$nombre = isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : '';
$cargo = isset($_POST['cargo']) ? htmlspecialchars($_POST['cargo']) : '';
$lote = isset($_POST['lote']) ? htmlspecialchars($_POST['lote']) : '';
$tamano = isset($_POST['tamano']) ? htmlspecialchars($_POST['tamano']) : '';
$pesos = isset($_POST['peso']) ? $_POST['peso'] : [];
$pesoPromedio = isset($_POST['pesoPromedio']) ? htmlspecialchars($_POST['pesoPromedio']) : '';

// Construye el HTML igual que en procesar_tara.php
ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Control de Tara Seca - MO-PG-PD-FO-017</title>
<style>
  body { font-family: 'Arial', sans-serif; font-size: 13px; }
  .doc { width: 780px; margin: 0 auto; }
  .doc-header { display: flex; border-bottom: 1px solid #555; }
  .doc-logo { width: 140px; border-right: 1px solid #555; display: flex; align-items: center; justify-content: center; padding: 10px; }
  .doc-logo-inner img { max-width: 100%; max-height: 80px; }
  .doc-title-block { flex: 1; padding: 10px 14px; text-align: center; border-right: 1px solid #555; }
  .doc-title-block .ppr { font-size: 9px; color: #666; margin-bottom: 3px; font-style: italic; }
  .doc-title-block .main-title { font-weight: 600; font-size: 12px; color: #111; }
  .doc-title-block .sub-title { font-size: 11px; color: #333; margin-top: 2px; font-style: italic; }
  .doc-meta { width: 190px; }
  .doc-meta-row { display: flex; border-bottom: 1px solid #555; }
  .doc-meta-row:last-child { border-bottom: none; }
  .doc-meta-key { background: #f0f0f0; font-size: 9px; font-weight: 600; text-transform: uppercase; color: #333; padding: 3px 6px; width: 70px; border-right: 1px solid #555; }
  .doc-meta-val { font-size: 10px; color: #111; padding: 3px 8px; flex: 1; }
  .section-bar { background: #e8e8e8; border-top: 1px solid #555; border-bottom: 1px solid #555; text-align: center; font-size: 11px; font-weight: 600; color: #111; padding: 5px; text-transform: uppercase; letter-spacing: .08em; }
  .datos-table { width: 100%; border-collapse: collapse; }
  .datos-table td { border: 1px solid #aaa; padding: 5px 10px; font-size: 12px; }
  .datos-table .dk { background: #f5f5f5; font-weight: 600; color: #333; width: 160px; font-size: 11px; }
  .datos-table .dv { color: #111; font-size: 12px; }
  .samples-section { padding: 12px 0 0; }
  .samples-wrap { border: 1px solid #aaa; margin-top: 4px; }
  table.st { width: 100%; border-collapse: collapse; }
  table.st thead tr { background: #ececec; }
  table.st thead th { border: 1px solid #aaa; padding: 6px 10px; font-size: 10px; font-weight: 600; text-transform: uppercase; color: #333; text-align: center; }
  td.c-n { border: 1px solid #ccc; text-align: center; font-size: 11px; color: #555; padding: 6px 8px; width: 70px; }
  td.c-v { border: 1px solid #ccc; text-align: center; font-size: 13px; color: #111; padding: 6px 8px; font-weight: 500; }
  .promedio-row { display: flex; border-top: 1.5px solid #555; margin: 12px 0 0; }
  .promedio-lbl { background: #ececec; border: 1px solid #aaa; border-right: none; font-size: 11px; font-weight: 600; color: #333; padding: 8px 16px; width: 160px; }
  .promedio-val { border: 1px solid #aaa; flex: 1; font-size: 20px; font-weight: 700; color: #111; padding: 8px 24px; }
</style>
</head>
<body>
<div class="doc">
  <div class="doc-header">
    <div class="doc-logo">
      <div class="doc-logo-inner">
        <img src="/img/logo_empresa.jpeg" alt="Logo">
      </div>
    </div>
    <div class="doc-title-block">
      <div class="ppr">PPR Gesti&oacute;n de la Producci&oacute;n</div>
      <div class="main-title">Procedimiento Control de Cantidad</div>
      <div class="sub-title">&ldquo;Control de Tara Seca para la harina de trigo Fuerte Exportaci&oacute;n&rdquo;</div>
    </div>
    <div class="doc-meta">
      <div class="doc-meta-row"><div class="doc-meta-key">C&oacute;digo</div><div class="doc-meta-val">MO-PG-PD-FO-017</div></div>
      <div class="doc-meta-row"><div class="doc-meta-key">Versi&oacute;n</div><div class="doc-meta-val">1</div></div>
      <div class="doc-meta-row"><div class="doc-meta-key">Fecha</div><div class="doc-meta-val">30/04/2024</div></div>
      <div class="doc-meta-row"><div class="doc-meta-key">P&aacute;gina</div><div class="doc-meta-val">1 de 1</div></div>
    </div>
  </div>
  <div class="section-bar">Datos Iniciales</div>
  <table class="datos-table">
    <tr><td class="dk">Fecha</td><td class="dv"><?= $fecha ?></td></tr>
    <tr><td class="dk">Nombre</td><td class="dv"><?= $nombre ?></td></tr>
    <tr><td class="dk">Cargo</td><td class="dv"><?= $cargo ?></td></tr>
    <tr><td class="dk">Lote del Empaque</td><td class="dv"><?= $lote ?></td></tr>
    <tr><td class="dk">Tama&ntilde;o de la muestra</td><td class="dv"><?= $tamano ?></td></tr>
  </table>
  <div class="samples-section">
    <div class="samples-wrap">
      <table class="st">
        <thead>
          <tr>
            <th>N&deg;</th>
            <th>Peso Empaque (g)</th>
            <th>N&deg;</th>
            <th>Peso Empaque (g)</th>
          </tr>
        </thead>
        <tbody>
        <?php
        $total = count($pesos);
        for ($i = 0; $i < $total/2; $i++) {
          $n1 = $i+1;
          $n2 = $i+1+($total/2);
          $v1 = isset($pesos[$i]) && $pesos[$i] !== '' ? htmlspecialchars($pesos[$i]) : '-';
          $v2 = isset($pesos[$i+($total/2)]) && $pesos[$i+($total/2)] !== '' ? htmlspecialchars($pesos[$i+($total/2)]) : '-';
          echo "<tr>
            <td class=\"c-n\">$n1</td><td class=\"c-v\">$v1</td>
            <td class=\"c-n\">$n2</td><td class=\"c-v\">$v2</td>
          </tr>";
        }
        ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="promedio-row">
    <div class="promedio-lbl">Peso Promedio</div>
    <div class="promedio-val"><?= $pesoPromedio ?></div>
  </div>
</div>
</body>
</html>
<?php
$html = ob_get_clean();

// Opciones Dompdf
$options = new Options();
$options->set('isRemoteEnabled', true); // Para cargar imágenes externas si es necesario

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Ruta y nombre del archivo
$nombreArchivo = 'control-tara-' . ($lote ? $lote : 'sin-lote') . '-' . date('Ymd_His') . '.pdf';
$rutaGuardado = __DIR__ . '/../../pdf_registros/' . $nombreArchivo;

// Guardar el PDF en el servidor
file_put_contents($rutaGuardado, $dompdf->output());

// Puedes devolver una respuesta JSON o redirigir, según tu flujo
echo "PDF guardado en: $rutaGuardado";
?>