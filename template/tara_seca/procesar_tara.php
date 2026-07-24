<?php
// Recibe los datos POST
$fecha = isset($_POST['fecha']) ? htmlspecialchars($_POST['fecha']) : '';
$nombre = isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : '';
$cargo = isset($_POST['cargo']) ? htmlspecialchars($_POST['cargo']) : '';
$lote = isset($_POST['lote']) ? htmlspecialchars($_POST['lote']) : '';
$tamano = isset($_POST['tamano']) ? htmlspecialchars($_POST['tamano']) : '';
$pesos = isset($_POST['peso']) ? $_POST['peso'] : [];
$pesoPromedio = isset($_POST['pesoPromedio']) ? htmlspecialchars($_POST['pesoPromedio']) : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Control de Tara Seca - MO-PG-PD-FO-017</title>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;600&family=IBM+Plex+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  /* ... (todo tu CSS igual, sin cambios) ... */
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    background: #d0d0d0;
    font-family: 'IBM Plex Sans', sans-serif;
    font-size: 13px;
    padding: 32px;
    display: flex;
    justify-content: center;
  }
  .doc {
    background: #fff;
    width: 780px;
    border: 1px solid #888;
    box-shadow: 0 4px 24px rgba(0,0,0,.22);
  }
  .doc-header {
    display: grid;
    grid-template-columns: 140px 1fr 190px;
    border-bottom: 1px solid #555;
  }
  .doc-logo {
    border-right: 1px solid #555;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 10px;
  }
  .doc-logo-inner {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 22px;
    font-weight: 700;
    letter-spacing: .1em;
    color: #111;
    line-height: 1;
  }
  .doc-logo-inner .sub {
    font-size: 8px;
    font-weight: 400;
    display: block;
    letter-spacing: .05em;
    color: #555;
    margin-top: 3px;
    text-transform: uppercase;
  }
  .doc-title-block {
    padding: 10px 14px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    text-align: center;
    border-right: 1px solid #555;
  }
  .doc-title-block .ppr {
    font-size: 9px;
    color: #666;
    margin-bottom: 3px;
    font-style: italic;
  }
  .doc-title-block .main-title {
    font-weight: 600;
    font-size: 12px;
    color: #111;
  }
  .doc-title-block .sub-title {
    font-size: 11px;
    color: #333;
    margin-top: 2px;
    font-style: italic;
  }
  .doc-meta {
    display: grid;
    grid-template-rows: repeat(4, 1fr);
  }
  .doc-meta-row {
    display: grid;
    grid-template-columns: 70px 1fr;
    border-bottom: 1px solid #555;
  }
  .doc-meta-row:last-child { border-bottom: none; }
  .doc-meta-key {
    background: #f0f0f0;
    font-size: 9px;
    font-weight: 600;
    text-transform: uppercase;
    color: #333;
    padding: 3px 6px;
    display: flex;
    align-items: center;
    border-right: 1px solid #555;
  }
  .doc-meta-val {
    font-size: 10px;
    color: #111;
    padding: 3px 8px;
    display: flex;
    align-items: center;
    font-family: 'IBM Plex Mono', monospace;
  }
  .section-bar {
    background: #e8e8e8;
    border-top: 1px solid #555;
    border-bottom: 1px solid #555;
    text-align: center;
    font-size: 11px;
    font-weight: 600;
    color: #111;
    padding: 5px;
    text-transform: uppercase;
    letter-spacing: .08em;
  }
  .datos-table {
    width: 100%;
    border-collapse: collapse;
  }
  .datos-table td {
    border: 1px solid #aaa;
    padding: 5px 10px;
    font-size: 12px;
  }
  .datos-table .dk {
    background: #f5f5f5;
    font-weight: 600;
    color: #333;
    width: 160px;
    font-size: 11px;
  }
  .datos-table .dv {
    color: #111;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 12px;
  }
  .samples-section {
    padding: 12px 16px 0;
  }
  .samples-wrap {
    border: 1px solid #aaa;
    margin-top: 4px;
  }
  table.st {
    width: 100%;
    border-collapse: collapse;
  }
  table.st thead tr {
    background: #ececec;
  }
  table.st thead th {
    border: 1px solid #aaa;
    padding: 6px 10px;
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: #333;
    text-align: center;
  }
  table.st tbody tr { border-bottom: 1px solid #ccc; }
  table.st tbody tr:last-child { border-bottom: none; }
  td.c-n {
    border: 1px solid #ccc;
    text-align: center;
    font-size: 11px;
    color: #555;
    padding: 6px 8px;
    width: 70px;
    font-family: 'IBM Plex Mono', monospace;
  }
  td.c-v {
    border: 1px solid #ccc;
    text-align: center;
    font-size: 13px;
    color: #111;
    padding: 6px 8px;
    font-family: 'IBM Plex Mono', monospace;
    font-weight: 500;
  }
  .promedio-row {
    display: flex;
    border-top: 1.5px solid #555;
    margin: 12px 16px 0;
  }
  .promedio-lbl {
    background: #ececec;
    border: 1px solid #aaa;
    border-right: none;
    font-size: 11px;
    font-weight: 600;
    color: #333;
    padding: 8px 16px;
    display: flex;
    align-items: center;
    width: 160px;
    text-transform: uppercase;
    letter-spacing: .05em;
  }
  .promedio-val {
    border: 1px solid #aaa;
    flex: 1;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 20px;
    font-weight: 700;
    color: #111;
    padding: 8px 24px;
    display: flex;
    align-items: center;
  }
  .doc-footer {
    margin-top: 20px;
    padding: 12px 16px;
    border-top: 1px solid #ccc;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
  }
  .btn-print {
    background: #111;
    color: #fff;
    border: none;
    padding: 8px 22px;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: .08em;
    cursor: pointer;
    border-radius: 3px;
  }
  .btn-print:hover { background: #333; }
  .btn-export {
    background: transparent;
    color: #333;
    border: 1px solid #999;
    padding: 8px 22px;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: .08em;
    cursor: pointer;
    border-radius: 3px;
  }
  .btn-export:hover { background: #f0f0f0; }
  @media print {
    body { background: white; padding: 0; }
    .doc { box-shadow: none; border: none; width: 100%; }
    .doc-footer { display: none; }
  }
</style>
<!-- jsPDF CDN para PDF automático -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
</head>
<body>
<div class="doc" id="doc-content">

  <!-- HEADER -->
  <div class="doc-header">
    <div class="doc-logo">
      <div class="doc-logo-inner">
        <img src="/img/logo_empresa.jpeg" alt="Logo" style="max-width:100%;max-height:180px;">
      </div>
    </div>
    <div class="doc-title-block">
      <div class="ppr">PPR Gesti&oacute;n de la Producci&oacute;n</div>
      <div class="main-title">Procedimiento Control de Cantidad</div>
      <div class="sub-title">&ldquo;Control de Tara Seca para la harina de trigo Fuerte Exportaci&oacute;n&rdquo;</div>
    </div>
    <div class="doc-meta">
      <div class="doc-meta-row">
        <div class="doc-meta-key">C&oacute;digo</div>
        <div class="doc-meta-val">MO-PG-PD-FO-017</div>
      </div>
      <div class="doc-meta-row">
        <div class="doc-meta-key">Versi&oacute;n</div>
        <div class="doc-meta-val">1</div>
      </div>
      <div class="doc-meta-row">
        <div class="doc-meta-key">Fecha</div>
        <div class="doc-meta-val">30/04/2024</div>
      </div>
      <div class="doc-meta-row">
        <div class="doc-meta-key">P&aacute;gina</div>
        <div class="doc-meta-val">1 de 1</div>
      </div>
    </div>
  </div>

  <!-- DATOS INICIALES -->
  <div class="section-bar">Datos Iniciales</div>
  <table class="datos-table">
    <tr><td class="dk">Fecha</td><td class="dv"><?php echo $fecha; ?></td></tr>
    <tr><td class="dk">Nombre</td><td class="dv"><?php echo $nombre; ?></td></tr>
    <tr><td class="dk">Cargo</td><td class="dv"><?php echo $cargo; ?></td></tr>
    <tr><td class="dk">Lote del Empaque</td><td class="dv"><?php echo $lote; ?></td></tr>
    <tr><td class="dk">Tama&ntilde;o de la muestra</td><td class="dv"><?php echo $tamano; ?></td></tr>
  </table>

  <!-- TABLA PESOS -->
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
        // Imprime los pesos en dos columnas
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

  <!-- PROMEDIO -->
  <div class="promedio-row">
    <div class="promedio-lbl">Peso Promedio</div>
    <div class="promedio-val"><?php echo $pesoPromedio; ?></div>
  </div>

  <!-- FOOTER BUTTONS -->
  <div class="doc-footer">
    <!-- <button class="btn-print" onclick="window.print()">&#128438; Imprimir</button> -->
  </div>

</div>

<script>
// Exportar JSON con los datos actuales
function exportarJSON(){
  var data = {
    formulario:'MO-PG-PD-FO-017',
    fecha:'<?php echo $fecha; ?>',
    nombre:'<?php echo $nombre; ?>',
    cargo:'<?php echo $cargo; ?>',
    lote:'<?php echo $lote; ?>',
    tamano:'<?php echo $tamano; ?>',
    pesos:<?php echo json_encode($pesos); ?>,
    pesoPromedio:'<?php echo $pesoPromedio; ?>'
  };
  var blob = new Blob([JSON.stringify(data,null,2)],{type:'application/json'});
  var a = document.createElement('a');
  a.href = URL.createObjectURL(blob);
  a.download = 'control-tara-'+(data.lote||'sin-lote')+'.json';
  a.click();
}

// PDF automático al cargar
window.onload = function() {
  setTimeout(function() {
    var { jsPDF } = window.jspdf;
    var doc = new jsPDF('p', 'pt', 'a4');
    doc.html(document.getElementById('doc-content'), {
      callback: function (pdf) {
        pdf.save('control-tara-<?php echo $lote ? $lote : 'sin-lote'; ?>.pdf');
      },
      margin: [20, 20, 20, 20],
      autoPaging: 'text',
      x: 0,
      y: 0,
      width: 560 // ajusta si es necesario
    });
  }, 800); // Espera para que cargue el DOM y estilos
};
</script>
</body>
</html>