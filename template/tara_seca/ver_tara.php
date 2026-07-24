<?php
// ver_tara.php - Visor de un registro JSON específico
$file = $_GET['file'] ?? '';
if (!$file || !preg_match('/^tara_.*\.json$/', $file)) {
    die('Archivo no válido.');
}

$path = __DIR__ . '/../../archivos/generados/Calidad/tara_seca/' . $file;
if (!file_exists($path)) {
    die('Registro no encontrado.');
}

$data = json_decode(file_get_contents($path), true);
$fecha = $data['fecha'] ?? '';
$nombre = $data['nombre'] ?? '';
$cargo = $data['cargo'] ?? '';
$lote = $data['lote'] ?? '';
$tamano = $data['tamano'] ?? '';
$pesos = $data['pesos'] ?? [];
$pesoPromedio = $data['pesoPromedio'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Control de Tara Seca - MO-PG-PD-FO-017</title>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;600&family=IBM+Plex+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { background: #f0f0f0; font-family: 'IBM Plex Sans', sans-serif; padding: 40px; display: flex; justify-content: center; }
  .doc { background: #fff; width: 800px; padding: 40px; border: 1px solid #ccc; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
  .header { display: flex; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 20px; }
  .logo { width: 150px; }
  .title-block { flex: 1; text-align: center; }
  .meta-block { width: 200px; font-size: 10px; font-family: 'IBM Plex Mono', monospace; }
  .section-title { background: #eee; padding: 8px 12px; font-weight: 600; text-transform: uppercase; font-size: 12px; margin: 20px 0 10px; }
  .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
  .info-table td { border: 1px solid #ddd; padding: 10px; font-size: 13px; }
  .label { font-weight: 600; background: #fafafa; width: 160px; }
  .weights-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
  .w-table { width: 100%; border-collapse: collapse; }
  .w-table th, .w-table td { border: 1px solid #ddd; padding: 6px; text-align: center; font-family: 'IBM Plex Mono', monospace; }
  .footer { margin-top: 30px; display: flex; justify-content: space-between; align-items: center; border-top: 2px solid #333; padding-top: 20px; }
  .avg-box { font-size: 24px; font-weight: 700; }
  @media print {
    body { background: #fff; padding: 0; }
    .doc { box-shadow: none; border: none; width: 100%; }
    .no-print { display: none; }
  }
</style>
</head>
<body>
<div class="doc">
  <div class="header">
    <div class="logo"><img src="/img/logo_empresa.jpeg" style="width:100%;"></div>
    <div class="title-block">
      <h1 style="font-size:16px;">Control de Tara Seca</h1>
      <p style="font-size:12px; color:#666;">Harina de Trigo Fuerte Exportación</p>
    </div>
    <div class="meta-block">
      <p>Código: MO-PG-PD-FO-017</p>
      <p>Versión: 1</p>
      <p>Fecha: 30/04/2024</p>
    </div>
  </div>

  <div class="section-title">Datos del Registro</div>
  <table class="info-table">
    <tr><td class="label">Fecha</td><td><?php echo $fecha; ?></td></tr>
    <tr><td class="label">Lote</td><td><?php echo $lote; ?></td></tr>
    <tr><td class="label">Responsable</td><td><?php echo $nombre; ?></td></tr>
    <tr><td class="label">Cargo</td><td><?php echo $cargo; ?></td></tr>
    <tr><td class="label">Tamaño Muestra</td><td><?php echo $tamano; ?></td></tr>
  </table>

  <div class="section-title">Resultados de Pesaje (g)</div>
  <div class="weights-grid">
    <table class="w-table">
      <thead><tr><th>N°</th><th>Peso</th></tr></thead>
      <tbody>
        <?php for($i=0; $i<15; $i++) { 
          echo "<tr><td>".($i+1)."</td><td>".($pesos[$i] ?? '-')."</td></tr>";
        } ?>
      </tbody>
    </table>
    <table class="w-table">
      <thead><tr><th>N°</th><th>Peso</th></tr></thead>
      <tbody>
        <?php for($i=15; $i<30; $i++) { 
          echo "<tr><td>".($i+1)."</td><td>".($pesos[$i] ?? '-')."</td></tr>";
        } ?>
      </tbody>
    </table>
  </div>

  <div class="footer">
    <div>
      <p style="font-size:10px; color:#666;">Documento generado automáticamente</p>
      <p style="font-size:10px; color:#666;">ID: <?php echo $file; ?></p>
    </div>
    <div class="avg-box">
      <span style="font-size:12px; font-weight:400;">Peso Promedio: </span>
      <?php echo $pesoPromedio; ?> g
    </div>
  </div>

  <div class="no-print" style="margin-top:40px; text-align:right;">
    <button onclick="window.print()" style="padding:10px 20px; cursor:pointer;">🖨️ IMPRIMIR REGISTRO</button>
  </div>
</div>
</body>
</html>
