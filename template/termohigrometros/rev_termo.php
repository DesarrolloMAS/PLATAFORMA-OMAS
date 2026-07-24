<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Visor de Termohigrómetros</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet"/>
<style>
:root{
  --mint:#d0e9e7;--steel:#749abb;--deep:#2c4a6e;
  --glass-border:rgba(116,154,187,0.2);
  --text-primary:#e8f4f3;--text-secondary:rgba(208,233,231,0.6);
  --text-muted:rgba(116,154,187,0.7);
  --input-bg:rgba(116,154,187,0.07);
  --ok:#6ee7b7;--ok-bg:rgba(110,231,183,0.10);--ok-bd:rgba(110,231,183,0.25);
  --fail:#f87171;--fail-bg:rgba(248,113,113,0.10);--fail-bd:rgba(248,113,113,0.25);
  --na:#fbbf24;--na-bg:rgba(251,191,36,0.10);--na-bd:rgba(251,191,36,0.25);
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
  position:relative;z-index:1;width:100%;max-width:860px;
  margin-bottom:8px;
  animation:slideDown .7s cubic-bezier(.16,1,.3,1) both;
}
header::before{
  content:'SISTEMA DE CONTROL — AMBIENTAL';
  display:block;font-family:'DM Mono',monospace;
  font-size:10px;letter-spacing:.22em;color:var(--steel);margin-bottom:10px;opacity:.8;
}
h1{font-size:clamp(16px,2.2vw,22px);font-weight:600;letter-spacing:.12em;color:var(--text-primary);}
h1 span{color:var(--mint);}
.header-line{
  width:100%;max-width:860px;height:1px;
  background:linear-gradient(90deg,var(--steel) 0%,var(--mint) 40%,transparent 100%);
  margin-bottom:32px;position:relative;z-index:1;
  animation:expandLine 1s cubic-bezier(.16,1,.3,1) .3s both;transform-origin:left;
}

.wrapper{
  position:relative;z-index:1;
  width:100%;max-width:860px;
  display:flex;flex-direction:column;gap:16px;
  animation:fadeUp .8s cubic-bezier(.16,1,.3,1) .15s both;
}

.card{
  background:rgba(15,30,46,0.75);
  backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);
  border:1px solid var(--glass-border);border-radius:16px;overflow:hidden;
}

.topbar{
  background:linear-gradient(90deg,rgba(116,154,187,.08),rgba(208,233,231,.05) 50%,rgba(116,154,187,.08));
  padding:10px 28px;display:flex;align-items:center;justify-content:space-between;
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
.topbar-id{font-family:'DM Mono',monospace;font-size:10px;color:rgba(116,154,187,.4);letter-spacing:.1em;}

.file-list{padding:20px 28px;}
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
  text-align:center;padding:40px 20px;
  font-family:'DM Mono',monospace;font-size:11px;
  color:var(--text-muted);letter-spacing:.08em;
}

.btn-volver{
  position: absolute;
  top: 40px;
  left: 20px;
  background: rgba(116,154,187,0.1);
  border: 1px solid var(--glass-border);
  color: var(--mint);
  padding: 8px 16px;
  border-radius: 8px;
  cursor: pointer;
  font-family: 'DM Mono', monospace;
  font-size: 11px;
  text-decoration: none;
  transition: all 0.3s;
}
.btn-volver:hover{
  background: rgba(116,154,187,0.2);
  border-color: var(--mint);
}

@keyframes slideDown{from{opacity:0;transform:translateY(-14px)}to{opacity:1;transform:translateY(0)}}
@keyframes fadeUp{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:translateY(0)}}
@keyframes expandLine{from{transform:scaleX(0)}to{transform:scaleX(1)}}
@keyframes pulse{0%,100%{opacity:1;box-shadow:0 0 8px var(--mint)}50%{opacity:.4;box-shadow:0 0 3px var(--mint)}}
</style>
</head>
<body>

<a href="../revisiones_almacen.html" class="btn-volver">VOLVER</a>

<header>
  <h1>VISOR DE <span>TERMOHIGRÓMETROS</span></h1>
</header>
<div class="header-line"></div>

<div class="wrapper">
  <div class="card" id="list-card">
    <div class="topbar">
      <span class="status-dot">ARCHIVOS EN SERVIDOR</span>
      <span class="topbar-id">/archivos/generados/termohigrometros/...</span>
    </div>
    <div class="file-list">
      <div class="file-count" id="file-count">0 archivos encontrados</div>
      <div id="file-rows"></div>
    </div>
  </div>
</div>

<script>
function cargarListaArchivos() {
  fetch('listar_jsons.php')
    .then(r => r.json())
    .then(files => {
      document.getElementById("file-count").textContent = `${files.length} archivo${files.length!==1?"s":""} encontrado${files.length!==1?"s":""}`;
      renderList(files);
    });
}

function renderList(files){
  const rows = document.getElementById("file-rows");
  rows.innerHTML = "";
  if (!files.length) {
    rows.innerHTML = `<div class="empty-state">No se encontraron archivos JSON en la carpeta del servidor.</div>`;
    return;
  }
  files.forEach(name=>{
    const row = document.createElement("div");
    row.className = "file-row";
    row.innerHTML = `<div class="file-icon">📈</div>
      <div class="file-info"><div class="file-name">${name}</div></div>
      <span class="file-arrow">▶</span>`;
    row.addEventListener("click", ()=>{
      // name ya puede incluir la zona (ej: "General/2026-03.json")
      window.open('visor_termo.php?file=' + encodeURIComponent(name), '_blank');
    });
    rows.appendChild(row);
  });
}

window.onload = cargarListaArchivos;
</script>
</body>
</html>
