<?php
require '../../conection.php';
require '../../sesion.php';
verificarAutenticacion();
$sede_sesion = $_SESSION['sede'] ?? 'ZC';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Revisados — Etiquetado</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet"/>
<style>
:root{
  --mint:#d0e9e7;--steel:#749abb;--deep:#2c4a6e;
  --glass-border:rgba(116,154,187,0.2);
  --text-primary:#e8f4f3;--text-secondary:rgba(208,233,231,0.6);
  --text-muted:rgba(116,154,187,0.7);
  --ok:#6ee7b7;--ok-bg:rgba(110,231,183,0.10);--ok-bd:rgba(110,231,183,0.25);
  --fail:#f87171;--fail-bg:rgba(248,113,113,0.10);--fail-bd:rgba(248,113,113,0.25);
  --na:#fbbf24;--na-bg:rgba(251,191,36,0.10);--na-bd:rgba(251,191,36,0.25);
  --zc:#60a5fa;--zc-bg:rgba(96,165,250,0.12);--zc-bd:rgba(96,165,250,0.3);
  --zs:#a78bfa;--zs-bg:rgba(167,139,250,0.12);--zs-bd:rgba(167,139,250,0.3);
}
*{box-sizing:border-box;margin:0;padding:0;}
body{
  font-family:'DM Sans',sans-serif;
  background:#0f1e2e;min-height:100vh;
  display:flex;flex-direction:column;align-items:center;
  padding:40px 20px 80px;position:relative;overflow-x:hidden;
}
body::before{
  content:'';position:fixed;inset:0;
  background:
    radial-gradient(ellipse 80% 50% at 15% 20%,rgba(116,154,187,0.12) 0%,transparent 60%),
    radial-gradient(ellipse 60% 40% at 85% 70%,rgba(208,233,231,0.07) 0%,transparent 55%),
    radial-gradient(ellipse 40% 60% at 50% 100%,rgba(44,74,110,0.3) 0%,transparent 60%);
  pointer-events:none;z-index:0;
}
body::after{
  content:'';position:fixed;inset:0;
  background-image:
    repeating-linear-gradient(0deg,transparent,transparent 39px,rgba(116,154,187,0.03) 39px,rgba(116,154,187,0.03) 40px),
    repeating-linear-gradient(90deg,transparent,transparent 39px,rgba(116,154,187,0.03) 39px,rgba(116,154,187,0.03) 40px);
  pointer-events:none;z-index:0;
}

header{
  position:relative;z-index:1;width:100%;max-width:900px;
  margin-bottom:8px;
  animation:slideDown .7s cubic-bezier(.16,1,.3,1) both;
}
header::before{
  content:'SISTEMA DE CONTROL — LABORATORIO';
  display:block;font-family:'DM Mono',monospace;
  font-size:10px;letter-spacing:.22em;color:var(--steel);margin-bottom:10px;opacity:.8;
}
h1{font-size:clamp(16px,2.2vw,22px);font-weight:600;letter-spacing:.12em;color:var(--text-primary);}
h1 span{color:var(--mint);}
.header-line{
  width:100%;max-width:900px;height:1px;
  background:linear-gradient(90deg,var(--steel) 0%,var(--mint) 40%,transparent 100%);
  margin-bottom:28px;position:relative;z-index:1;
  animation:expandLine 1s cubic-bezier(.16,1,.3,1) .3s both;transform-origin:left;
}

.wrapper{
  position:relative;z-index:1;
  width:100%;max-width:900px;
  display:flex;flex-direction:column;gap:20px;
  animation:fadeUp .8s cubic-bezier(.16,1,.3,1) .15s both;
}

/* ── TABS ── */
.tabs{
  display:flex;gap:8px;margin-bottom:4px;
}
.tab-btn{
  display:flex;align-items:center;gap:8px;
  padding:10px 22px;border-radius:10px;cursor:pointer;
  font-family:'DM Mono',monospace;font-size:12px;font-weight:600;
  letter-spacing:.12em;text-transform:uppercase;
  border:1.5px solid transparent;
  transition:all .25s;user-select:none;
}
.tab-btn.zc{
  background:var(--zc-bg);border-color:var(--zc-bd);color:var(--zc);
}
.tab-btn.zc.active{
  background:rgba(96,165,250,0.22);box-shadow:0 0 16px rgba(96,165,250,0.25);
}
.tab-btn.zs{
  background:var(--zs-bg);border-color:var(--zs-bd);color:var(--zs);
}
.tab-btn.zs.active{
  background:rgba(167,139,250,0.22);box-shadow:0 0 16px rgba(167,139,250,0.25);
}
.tab-btn .zone-dot{
  width:8px;height:8px;border-radius:50%;flex-shrink:0;
}
.tab-btn.zc .zone-dot{background:var(--zc);}
.tab-btn.zs .zone-dot{background:var(--zs);}
.tab-count{
  font-size:10px;opacity:.7;
  background:rgba(255,255,255,0.08);border-radius:999px;
  padding:1px 7px;
}

/* ── PANEL ── */
.tab-panel{display:none;}
.tab-panel.active{display:block;}

/* ── CARD ── */
.card{
  background:rgba(15,30,46,0.75);
  backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);
  border:1px solid var(--glass-border);border-radius:16px;overflow:hidden;
}

.topbar{
  background:linear-gradient(90deg,rgba(116,154,187,.08),rgba(208,233,231,.05) 50%,rgba(116,154,187,.08));
  padding:10px 24px;display:flex;align-items:center;justify-content:space-between;
  border-bottom:1px solid rgba(116,154,187,.1);
}
.status-dot{
  display:flex;align-items:center;gap:8px;
  font-family:'DM Mono',monospace;font-size:10px;color:var(--text-muted);letter-spacing:.1em;
}
.status-dot::before{
  content:'';width:6px;height:6px;border-radius:50%;
  background:var(--mint);box-shadow:0 0 8px var(--mint);
  animation:pulse 2.5s ease-in-out infinite;
}
.topbar-zona{
  font-family:'DM Mono',monospace;font-size:10px;letter-spacing:.12em;font-weight:600;
  padding:3px 10px;border-radius:6px;
}
.topbar-zona.zc{background:var(--zc-bg);border:1px solid var(--zc-bd);color:var(--zc);}
.topbar-zona.zs{background:var(--zs-bg);border:1px solid var(--zs-bd);color:var(--zs);}

.file-list{padding:0 24px 20px;}
.file-count{
  font-family:'DM Mono',monospace;font-size:10px;
  color:var(--text-muted);letter-spacing:.12em;text-transform:uppercase;
  padding:16px 0 12px;border-bottom:1px solid rgba(116,154,187,.1);
  margin-bottom:12px;
}

.file-row{
  display:flex;align-items:center;gap:14px;
  padding:12px 16px;border-radius:10px;cursor:pointer;
  border:1px solid transparent;
  transition:background .2s,border-color .2s;
  margin-bottom:6px;
}
.file-row:hover{background:rgba(116,154,187,.08);border-color:rgba(116,154,187,.15);}
.file-icon{
  width:36px;height:36px;border-radius:8px;
  background:rgba(116,154,187,.1);border:1px solid rgba(116,154,187,.2);
  display:flex;align-items:center;justify-content:center;flex-shrink:0;
  font-size:16px;
}
.file-info{flex:1;min-width:0;}
.file-name{
  font-size:13px;font-weight:500;color:var(--text-primary);
  white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-bottom:3px;
}
.file-meta{font-family:'DM Mono',monospace;font-size:10px;color:var(--text-muted);letter-spacing:.06em;}
.file-arrow{font-size:12px;color:var(--steel);flex-shrink:0;opacity:.5;}

.empty-state{
  text-align:center;padding:48px 20px;
  font-family:'DM Mono',monospace;font-size:11px;
  color:var(--text-muted);letter-spacing:.08em;
}

@keyframes slideDown{from{opacity:0;transform:translateY(-14px)}to{opacity:1;transform:translateY(0)}}
@keyframes fadeUp{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:translateY(0)}}
@keyframes expandLine{from{transform:scaleX(0)}to{transform:scaleX(1)}}
@keyframes pulse{0%,100%{opacity:1;box-shadow:0 0 8px var(--mint)}50%{opacity:.4;box-shadow:0 0 3px var(--mint)}}
</style>
</head>
<body>

<header>
  <h1>REVISADOS — <span>ETIQUETADO</span></h1>
</header>
<div class="header-line"></div>

<div class="wrapper">

  <div class="tabs">
    <div class="tab-btn zc active" onclick="switchTab('ZC')">
      <span class="zone-dot"></span>
      ZONA CENTRO
      <span class="tab-count" id="count-ZC">—</span>
    </div>
    <div class="tab-btn zs" onclick="switchTab('ZS')">
      <span class="zone-dot"></span>
      ZONA SUR
      <span class="tab-count" id="count-ZS">—</span>
    </div>
  </div>

  <!-- PANEL ZC -->
  <div class="tab-panel active" id="panel-ZC">
    <div class="card">
      <div class="topbar">
        <span class="status-dot">ARCHIVOS EN SERVIDOR</span>
        <span class="topbar-zona zc">ZC — ZONA CENTRO</span>
      </div>
      <div class="file-list">
        <div class="file-count" id="file-count-ZC">Cargando…</div>
        <div id="file-rows-ZC"></div>
      </div>
    </div>
  </div>

  <!-- PANEL ZS -->
  <div class="tab-panel" id="panel-ZS">
    <div class="card">
      <div class="topbar">
        <span class="status-dot">ARCHIVOS EN SERVIDOR</span>
        <span class="topbar-zona zs">ZS — ZONA SUR</span>
      </div>
      <div class="file-list">
        <div class="file-count" id="file-count-ZS">—</div>
        <div id="file-rows-ZS"></div>
      </div>
    </div>
  </div>

</div>

<script>
const zonaActiva = {};
let tabActual = '<?php echo in_array($sede_sesion, ['ZC','ZS']) ? $sede_sesion : 'ZC'; ?>';

function switchTab(zona) {
  ['ZC','ZS'].forEach(z => {
    document.querySelector('.tab-btn.' + z.toLowerCase()).classList.toggle('active', z === zona);
    document.getElementById('panel-' + z).classList.toggle('active', z === zona);
  });
  tabActual = zona;
  if (!zonaActiva[zona]) cargarZona(zona);
}

function cargarZona(zona) {
  zonaActiva[zona] = true;
  const countEl = document.getElementById('file-count-' + zona);
  const rowsEl  = document.getElementById('file-rows-' + zona);
  countEl.textContent = 'Cargando…';
  rowsEl.innerHTML = '';

  fetch('listar_jsons.php?zona=' + encodeURIComponent(zona))
    .then(r => r.json())
    .then(files => {
      const n = files.length;
      countEl.textContent = n + ' archivo' + (n !== 1 ? 's' : '') + ' encontrado' + (n !== 1 ? 's' : '');
      document.getElementById('count-' + zona).textContent = n;
      renderList(rowsEl, files, zona);
    })
    .catch(() => {
      countEl.textContent = 'Error al cargar';
      document.getElementById('count-' + zona).textContent = '!';
    });
}

function renderList(container, files, zona) {
  if (!files.length) {
    container.innerHTML = '<div class="empty-state">No hay registros de etiquetado para esta zona.</div>';
    return;
  }
  files.forEach(name => {
    const row = document.createElement('div');
    row.className = 'file-row';

    // Parse fecha y hora del nombre: etiquetado_YYYYMMDD_HHmmss.json
    let meta = '';
    const m = name.match(/(\d{4})(\d{2})(\d{2})_(\d{2})(\d{2})(\d{2})/);
    if (m) meta = `${m[3]}/${m[2]}/${m[1]} · ${m[4]}:${m[5]}`;

    row.innerHTML = `
      <div class="file-icon">📋</div>
      <div class="file-info">
        <div class="file-name">${name}</div>
        ${meta ? '<div class="file-meta">' + meta + '</div>' : ''}
      </div>
      <span class="file-arrow">▶</span>`;

    row.addEventListener('click', () => {
      window.open(
        'pros_etiquetado.html?zona=' + encodeURIComponent(zona) + '&file=' + encodeURIComponent(name),
        '_blank'
      );
    });
    container.appendChild(row);
  });
}

// Carga inicial de la zona activa al arrancar
cargarZona(tabActual);
// Pre-activa visualmente el tab correcto si la sesión no es ZC
if (tabActual !== 'ZC') switchTab(tabActual);
</script>
</body>
</html>
