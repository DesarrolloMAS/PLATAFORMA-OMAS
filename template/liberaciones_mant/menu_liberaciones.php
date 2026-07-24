<?php
function getLastLiberacionByZona($zona) {
    $dir = '/var/www/fmt/archivos/generados/liberaciones_mant/';
    $pattern = "registro-liberacion-zona-{$zona}-*.json";
    $files = glob($dir . $pattern);
    if (!$files) return null;
    usort($files, function($a, $b) {
        return strcmp($b, $a);
    });
    $lastFile = basename($files[0]);
    if (preg_match('/registro-liberacion-zona-'.$zona.'-(\d{4}-\d{2}-\d{2})-(\d{6})\.json/', $lastFile, $m)) {
        $fecha = $m[1];
        $hora = substr($m[2], 0, 2) . ':' . substr($m[2], 2, 2) . ':' . substr($m[2], 4, 2);
        return "$fecha $hora";
    }
    return null;
}
function existeLiberacionHoy($zona) {
    $dir = '/var/www/fmt/archivos/generados/liberaciones_mant/';
    $hoy = date('Y-m-d');
    $pattern = "registro-liberacion-zona-{$zona}-{$hoy}-*.json";
    $files = glob($dir . $pattern);
    return $files && count($files) > 0;
}
$ultimaEmpaque = getLastLiberacionByZona('empaque');
$ultimaAlmacen = getLastLiberacionByZona('almacen');
$ultimaMolienda = getLastLiberacionByZona('molienda');
$completoEmpaque = existeLiberacionHoy('empaque');
$completoAlmacen = existeLiberacionHoy('almacen');
$completoMolienda = existeLiberacionHoy('molienda');
?><!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PPR — Centro de Control de Liberación</title>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;600&family=IBM+Plex+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
:root {
    --bg: #0f1117;
    --surface: #1a1d27;
    --surface2: #222637;
    --border: #2e3349;
    --accent: #f0b429;
    --accent2: #3ecf8e;
    --danger: #f87171;
    --text: #e2e8f0;
    --text-muted: #64748b;
    --text-dim: #94a3b8;
  }

  * { box-sizing: border-box; margin: 0; padding: 0; }

  body {
    background: var(--bg);
    color: var(--text);
    font-family: 'IBM Plex Sans', sans-serif;
    font-size: 14px;
    min-height: 100vh;
  }

  .header {
    background: var(--surface);
    border-bottom: 2px solid var(--accent);
    padding: 20px 32px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: sticky;
    top: 0;
    z-index: 100;
  }

  .header-left h1 {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 15px;
    font-weight: 600;
    color: var(--accent);
    letter-spacing: 0.05em;
    text-transform: uppercase;
  }

  .header-left p {
    font-size: 12px;
    color: var(--text-muted);
    margin-top: 2px;
    font-family: 'IBM Plex Mono', monospace;
  }

  .header-right {
    display: flex;
    align-items: center;
    gap: 16px;
  }

  .badge {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 11px;
    padding: 4px 10px;
    border-radius: 3px;
    border: 1px solid var(--border);
    color: var(--text-muted);
  }

  .progress-bar-wrap {
    background: var(--surface2);
    height: 4px;
    width: 120px;
    border-radius: 2px;
    overflow: hidden;
  }

  .progress-bar {
    height: 100%;
    background: var(--accent);
    border-radius: 2px;
    transition: width 0.3s ease;
  }

  .progress-label {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 11px;
    color: var(--accent);
    min-width: 40px;
    text-align: right;
  }

  .meta-bar {
    background: var(--surface2);
    border-bottom: 1px solid var(--border);
    padding: 12px 32px;
    display: flex;
    gap: 24px;
    align-items: center;
    flex-wrap: wrap;
  }

  .meta-field {
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .meta-field label {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 11px;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.08em;
    white-space: nowrap;
  }

  .meta-field input {
    background: var(--surface);
    border: 1px solid var(--border);
    color: var(--text);
    font-family: 'IBM Plex Sans', sans-serif;
    font-size: 13px;
    padding: 5px 10px;
    border-radius: 4px;
    outline: none;
    transition: border-color 0.2s;
  }

  .meta-field input:focus {
    border-color: var(--accent);
  }

  .meta-field input[type="date"] { width: 148px; }
  .meta-field input[type="text"] { width: 180px; }

  .container {
    padding: 24px 32px;
    max-width: 1400px;
    margin: 0 auto;
  }

  .zone-section {
    margin-bottom: 32px;
  }

  .zone-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
  }

  .zone-label {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    color: var(--accent);
    padding: 3px 10px;
    border: 1px solid var(--accent);
    border-radius: 2px;
  }

  .zone-line {
    flex: 1;
    height: 1px;
    background: var(--border);
  }

  .zone-stats {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 11px;
    color: var(--text-muted);
  }

  table {
    width: 100%;
    border-collapse: collapse;
    border: 1px solid var(--border);
    border-radius: 6px;
    overflow: hidden;
  }

  thead tr {
    background: var(--surface2);
    border-bottom: 1px solid var(--border);
  }

  thead th {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--text-muted);
    padding: 10px 14px;
    text-align: left;
  }

  tbody tr {
    border-bottom: 1px solid var(--border);
    background: var(--surface);
    transition: background 0.15s;
  }

  tbody tr:last-child { border-bottom: none; }
  tbody tr:hover { background: var(--surface2); }

  tbody tr.status-cumple { border-left: 3px solid var(--accent2); }
  tbody tr.status-no-cumple { border-left: 3px solid var(--danger); }

  td {
    padding: 9px 14px;
    vertical-align: middle;
  }

  .td-code {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 11px;
    color: var(--text-dim);
    white-space: nowrap;
    min-width: 160px;
  }

  .td-name {
    color: var(--text);
    font-size: 13px;
    min-width: 240px;
  }

  .td-ubicacion {
    font-size: 12px;
    color: var(--text-dim);
    white-space: nowrap;
  }

  .td-check {
    text-align: center;
    width: 90px;
  }

  .check-btn {
    width: 32px;
    height: 32px;
    border-radius: 4px;
    border: 1.5px solid var(--border);
    background: transparent;
    cursor: pointer;
    transition: all 0.15s;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    font-size: 16px;
  }

  .check-btn.cumple-btn:hover { border-color: var(--accent2); background: rgba(62,207,142,0.08); }
  .check-btn.cumple-btn.active { border-color: var(--accent2); background: rgba(62,207,142,0.2); }

  .check-btn.nocumple-btn:hover { border-color: var(--danger); background: rgba(248,113,113,0.08); }
  .check-btn.nocumple-btn.active { border-color: var(--danger); background: rgba(248,113,113,0.2); }

  .td-obs input {
    background: transparent;
    border: none;
    border-bottom: 1px solid var(--border);
    color: var(--text);
    font-family: 'IBM Plex Sans', sans-serif;
    font-size: 12px;
    width: 100%;
    padding: 4px 2px;
    outline: none;
    transition: border-color 0.2s;
    min-width: 180px;
  }

  .td-obs input:focus { border-bottom-color: var(--accent); }
  .td-obs input::placeholder { color: var(--text-muted); }

  .bottom-section {
    margin-top: 32px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 6px;
    padding: 24px;
  }

  .bottom-section h2 {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--accent);
    margin-bottom: 16px;
    padding-bottom: 10px;
    border-bottom: 1px solid var(--border);
  }

  .liberacion-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
  }

  .liberacion-group label {
    display: block;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 11px;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.08em;
    margin-bottom: 8px;
  }

  .yn-group {
    display: flex;
    gap: 8px;
  }

  .yn-btn {
    padding: 8px 24px;
    border-radius: 4px;
    border: 1.5px solid var(--border);
    background: transparent;
    color: var(--text-dim);
    font-family: 'IBM Plex Mono', monospace;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.15s;
  }

  .yn-btn:hover { border-color: var(--accent); color: var(--accent); }
  .yn-btn.active-si { border-color: var(--accent2); background: rgba(62,207,142,0.15); color: var(--accent2); }
  .yn-btn.active-no { border-color: var(--danger); background: rgba(248,113,113,0.15); color: var(--danger); }

  .responsable-input {
    background: var(--surface2);
    border: 1px solid var(--border);
    color: var(--text);
    font-family: 'IBM Plex Sans', sans-serif;
    font-size: 13px;
    padding: 8px 12px;
    border-radius: 4px;
    outline: none;
    width: 100%;
    transition: border-color 0.2s;
  }

  .responsable-input:focus { border-color: var(--accent); }

  .condiciones-textarea {
    background: var(--surface2);
    border: 1px solid var(--border);
    color: var(--text);
    font-family: 'IBM Plex Sans', sans-serif;
    font-size: 13px;
    padding: 10px 12px;
    border-radius: 4px;
    outline: none;
    width: 100%;
    min-height: 80px;
    resize: vertical;
    transition: border-color 0.2s;
  }

  .condiciones-textarea:focus { border-color: var(--accent); }

  .actions-bar {
    margin-top: 24px;
    display: flex;
    gap: 12px;
    justify-content: flex-end;
  }

  .btn-primary {
    background: var(--accent);
    color: #0f1117;
    border: none;
    padding: 10px 28px;
    border-radius: 4px;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    cursor: pointer;
    transition: opacity 0.15s;
  }

  .btn-primary:hover { opacity: 0.85; }

  .btn-secondary {
    background: transparent;
    color: var(--text-dim);
    border: 1px solid var(--border);
    padding: 10px 28px;
    border-radius: 4px;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    cursor: pointer;
    transition: border-color 0.15s, color 0.15s;
  }

  .btn-secondary:hover { border-color: var(--text-dim); color: var(--text); }

  @media print {
    body { background: white; color: #111; }
    .header { background: white; border-bottom: 2px solid #ccc; position: static; }
    .actions-bar, .meta-bar { display: none; }
    .check-btn { border: 1px solid #ccc; }
  }
</style>
<style>
.menu-container{max-width:900px;margin:60px auto;padding:0 32px;}
.menu-title{font-family:'IBM Plex Mono',monospace;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.14em;color:var(--text-muted);margin-bottom:28px;}
.menu-title span{color:var(--accent);}
.cards-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
.card{background:var(--surface);border:1px solid var(--border);border-radius:6px;padding:24px;text-decoration:none;color:inherit;display:block;transition:border-color .15s,background .15s;position:relative;overflow:hidden;}
.card:hover{border-color:var(--accent);background:var(--surface2);cursor:pointer;}
.card-top{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:16px;}
.card-code{font-family:'IBM Plex Mono',monospace;font-size:10px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.1em;}
.status-badges{display:flex;gap:6px;flex-wrap:wrap;justify-content:flex-end;}
.badge-status{font-family:'IBM Plex Mono',monospace;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.08em;padding:3px 8px;border-radius:3px;border:1px solid;white-space:nowrap;}
.badge-done{color:var(--accent2);border-color:var(--accent2);background:rgba(62,207,142,.1);}
.badge-pending{color:var(--accent);border-color:var(--accent);background:rgba(240,180,41,.1);}
.badge-missing{color:var(--danger);border-color:var(--danger);background:rgba(248,113,113,.1);}
.badge-idle{color:var(--text-muted);border-color:var(--border);background:transparent;}
.card-title{font-family:'IBM Plex Sans',sans-serif;font-size:17px;font-weight:500;color:var(--text);margin-bottom:5px;}
.card-subtitle{font-family:'IBM Plex Mono',monospace;font-size:11px;color:var(--text-muted);margin-bottom:20px;}
.card-footer{display:flex;justify-content:space-between;align-items:center;padding-top:16px;border-top:1px solid var(--border);}
.card-meta{font-family:'IBM Plex Mono',monospace;font-size:11px;color:var(--text-muted);}
.card-meta strong{color:var(--text-dim);}
.card-arrow{font-size:18px;color:var(--text-muted);transition:color .15s,transform .15s;}
.card:hover .card-arrow{color:var(--accent);transform:translateX(4px);}
.info-bar{margin-top:24px;padding:14px 20px;background:var(--surface);border:1px solid var(--border);border-radius:6px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;}
.info-bar-left{font-family:'IBM Plex Mono',monospace;font-size:11px;color:var(--text-muted);}
.info-bar-left span{color:var(--text-dim);}
.refresh-btn{font-family:'IBM Plex Mono',monospace;font-size:11px;text-transform:uppercase;letter-spacing:.08em;padding:5px 14px;border-radius:3px;border:1px solid var(--border);background:transparent;color:var(--text-muted);cursor:pointer;transition:border-color .15s,color .15s;}
.refresh-btn:hover{border-color:var(--accent);color:var(--accent);}
.spinner{display:inline-block;width:10px;height:10px;border:1.5px solid var(--border);border-top-color:var(--accent);border-radius:50%;animation:spin .7s linear infinite;margin-right:4px;vertical-align:middle;}
@keyframes spin{to{transform:rotate(360deg);}}
@media(max-width:600px){.cards-grid{grid-template-columns:1fr;}}
</style>
</head>
<body>

<div class="header">
  <div class="header-left">
    <h1>PPR — Centro de Control de Liberación</h1>
    <p>GM-MM-MQ-ME-FO-005 · Seleccione el formulario a diligenciar</p>
  </div>
  <div class="header-right">
    <div class="badge" id="todayBadge">—</div>
  </div>
</div>

<div class="menu-container">
  <div class="menu-title">Formularios disponibles — <span id="dateLabel">cargando...</span></div>
  <div class="cards-grid">

    <a class="card" href="form_liberaciones_empaque.php" id="card-sur">
      <div class="card-top">
        <div class="status-badges" id="badges-sur"><span class="badge-status badge-idle"><span class="spinner"></span>cargando</span></div>
      </div>
      <div class="card-title">Empaque</div>
      <div class="card-footer">
        <div class="card-meta">36 equipos &middot; <strong id="meta-sur">—</strong></div>
        <span class="card-arrow">&rarr;</span>
      </div>
    </a>

    <a class="card" href="form_liberaciones_almacen.php" id="card-empaque">
      <div class="card-top">
        <div class="status-badges" id="badges-empaque"><span class="badge-status badge-idle"><span class="spinner"></span>cargando</span></div>
      </div>
      <div class="card-title">Recepcion y Almacen</div>
      <div class="card-footer">
        <div class="card-meta">15 equipos &middot; <strong id="meta-empaque">—</strong></div>
        <span class="card-arrow">&rarr;</span>
      </div>
    </a>

    <a class="card" href="form_liberaciones_molienda.php" id="card-recepcion">
      <div class="card-top">
        <div class="status-badges" id="badges-recepcion"><span class="badge-status badge-idle"><span class="spinner"></span>cargando</span></div>
      </div>
      <div class="card-title">Molienda</div>
      <div class="card-footer">
        <div class="card-meta">120 equipos &middot; <strong id="meta-recepcion">—</strong></div>
        <span class="card-arrow">&rarr;</span>
      </div>
    </a>

  </div>

  <div class="info-bar">
    <div class="info-bar-left">Ultimo refresco: <span id="lastRefresh">—</span></div>
    <button class="refresh-btn" onclick="loadStatuses()">↻ Refrescar</button>
    <a class="refresh-btn" href="../menu_mantenimiento.html">Volver</a>
  </div>
</div>

<script>
var FORMS = ['sur','empaque','recepcion'];

function todayISO(){return new Date().toISOString().split('T')[0];}
function nowStr(){return new Date().toLocaleTimeString('es-CO',{hour:'2-digit',minute:'2-digit'});}

function setDateLabel(){
  var d=new Date();
  var opts={weekday:'long',year:'numeric',month:'long',day:'numeric'};
  document.getElementById('dateLabel').textContent=d.toLocaleDateString('es-CO',opts);
  document.getElementById('todayBadge').textContent=todayISO();
}
window.ULTIMAS_LIBERACIONES = {
  empaque: "<?= $ultimaEmpaque ?: '' ?>",
  almacen: "<?= $ultimaAlmacen ?: '' ?>",
  molienda: "<?= $ultimaMolienda ?: '' ?>"
};
window.LIBERACIONES_COMPLETADAS = {
  sur: <?= $completoEmpaque ? 'true' : 'false' ?>,
  empaque: <?= $completoAlmacen ? 'true' : 'false' ?>,
  recepcion: <?= $completoMolienda ? 'true' : 'false' ?>
};

var FORMS = ['sur','empaque','recepcion'];

function todayISO(){return new Date().toISOString().split('T')[0];}
function nowStr(){return new Date().toLocaleTimeString('es-CO',{hour:'2-digit',minute:'2-digit'});}

function setDateLabel(){
  var d=new Date();
  var opts={weekday:'long',year:'numeric',month:'long',day:'numeric'};
  document.getElementById('dateLabel').textContent=d.toLocaleDateString('es-CO',opts);
  document.getElementById('todayBadge').textContent=todayISO();
}

function renderBadges(form, completado, fechaHora) {
  var container = document.getElementById('badges-' + form);
  var meta = document.getElementById('meta-' + form);
  if (completado) {
    container.innerHTML = '<span class="badge-status badge-done">&#10003; Completado</span>';
    meta.textContent = fechaHora ? 'Última: ' + fechaHora : '';
  } else {
    container.innerHTML = '<span class="badge-status badge-missing">Pendiente</span>';
    meta.textContent = 'Sin registrar hoy';
  }
}

function loadStatuses() {
  document.getElementById('lastRefresh').textContent = nowStr();
  FORMS.forEach(function(f) {
    document.getElementById('badges-' + f).innerHTML =
      '<span class="badge-status badge-idle"><span class="spinner"></span>cargando</span>';
  });

  renderBadges('sur', window.LIBERACIONES_COMPLETADAS.sur, window.ULTIMAS_LIBERACIONES.empaque);
  renderBadges('empaque', window.LIBERACIONES_COMPLETADAS.empaque, window.ULTIMAS_LIBERACIONES.almacen);
  renderBadges('recepcion', window.LIBERACIONES_COMPLETADAS.recepcion, window.ULTIMAS_LIBERACIONES.molienda);
}

document.addEventListener('DOMContentLoaded', function(){
  setDateLabel();
  loadStatuses();
});
</script>
</body>
</html>