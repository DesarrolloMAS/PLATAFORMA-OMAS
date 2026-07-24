<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Revisión de Tara Seca</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet"/>
<style>
:root{
  --mint:#d0e9e7;--steel:#749abb;--deep:#2c4a6e;
  --glass-border:rgba(116, 154, 187, 0.2);
  --text-primary:#e8f4f3;--text-secondary:rgba(208, 233, 231, 0.6);
  --text-muted:rgba(116, 154, 187, 0.7);
  --input-bg:rgba(116, 154, 187, 0.07);
}
*{box-sizing:border-box;margin:0;padding:0;}
body{
  font-family:'DM Sans',sans-serif;
  background:#0f1e2e;min-height:100vh;
  display:flex;flex-direction:row;
  padding:0;position:relative;overflow:hidden;
}
/* SIDEBAR LIST */
.sidebar{
  width:360px;height:100vh;
  background:rgba(15,30,46,0.8);
  backdrop-filter:blur(20px);
  border-right:1px solid var(--glass-border);
  display:flex;flex-direction:column;
  z-index:10;
}
.sidebar-header{
  padding:32px 24px 20px;
  border-bottom:1px solid rgba(116,154,187,.1);
}
.sidebar-header h1{font-size:18px;font-weight:600;color:var(--text-primary);letter-spacing:.05em;}
.sidebar-header h1 span{color:var(--mint);}
.sidebar-header p{font-family:'DM Mono',monospace;font-size:10px;color:var(--text-muted);margin-top:6px;text-transform:uppercase;letter-spacing:.1em;}

.file-list{flex:1;overflow-y:auto;padding:12px 16px;}
.file-row{
  padding:14px 16px;border-radius:10px;cursor:pointer;
  border:1px solid transparent;transition:all .2s;
  margin-bottom:8px;display:flex;align-items:center;gap:12px;
}
.file-row:hover{background:rgba(116,154,187,.08);border-color:rgba(116,154,187,.15);}
.file-row.active{background:rgba(208,233,231,.08);border-color:rgba(208,233,231,.2);}
.file-icon{font-size:18px;opacity:.6;}
.file-info{flex:1;min-width:0;}
.file-name{font-size:13px;font-weight:500;color:var(--text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.file-date{font-family:'DM Mono',monospace;font-size:9px;color:var(--text-muted);margin-top:2px;}

/* MAIN PREVIEW */
.main-preview{
  flex:1;height:100vh;overflow-y:auto;
  padding:40px;position:relative;
  background:
    radial-gradient(ellipse 80% 50% at 15% 20%,rgba(116,154,187,0.08) 0%,transparent 60%),
    radial-gradient(ellipse 40% 60% at 85% 70%,rgba(208,233,231,0.05) 0%,transparent 55%);
}
.preview-card{
  max-width:800px;margin:0 auto;
  background:rgba(15,30,46,0.6);
  backdrop-filter:blur(20px);
  border:1px solid var(--glass-border);
  border-radius:20px;overflow:hidden;
  animation:fadeUp .6s cubic-bezier(.16,1,.3,1) both;
  display:none;
}
.preview-card.visible{display:block;}

.preview-header{
  padding:24px 32px;
  background:rgba(116,154,187,.05);
  border-bottom:1px solid rgba(116,154,187,.1);
  display:flex;justify-content:space-between;align-items:center;
}
.preview-title h2{font-size:16px;font-weight:600;color:var(--mint);}
.preview-title p{font-family:'DM Mono',monospace;font-size:10px;color:var(--text-muted);margin-top:2px;}

.btn-print{
  background:var(--mint);color:#0f1e2e;
  border:none;padding:8px 20px;border-radius:6px;
  font-family:'DM Sans',sans-serif;font-size:12px;font-weight:600;
  cursor:pointer;transition:transform .15s;
}
.btn-print:hover{transform:translateY(-1px);}

.preview-body{padding:32px;}
.data-section{margin-bottom:32px;}
.section-lbl{
  font-family:'DM Mono',monospace;font-size:10px;font-weight:600;
  color:var(--steel);text-transform:uppercase;letter-spacing:.15em;
  margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid rgba(116,154,187,.1);
}

.kv-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;}
.kv-item{background:rgba(116,154,187,.05);padding:12px 16px;border-radius:10px;border:1px solid rgba(116,154,187,.1);}
.kv-lbl{font-family:'DM Mono',monospace;font-size:9px;color:var(--text-muted);text-transform:uppercase;margin-bottom:4px;}
.kv-val{font-size:13px;color:var(--text-primary);font-weight:500;}

.weights-table{width:100%;border-collapse:collapse;margin-top:12px;}
.weights-table th{
  font-family:'DM Mono',monospace;font-size:9px;color:var(--text-muted);
  text-align:left;padding:8px 12px;background:rgba(116,154,187,.08);
}
.weights-table td{padding:10px 12px;border-bottom:1px solid rgba(116,154,187,.05);font-size:13px;color:var(--text-primary);font-family:'DM Mono',monospace;}

.avg-card{
  background:linear-gradient(135deg,rgba(116,154,187,.1),rgba(208,233,231,.1));
  padding:24px;border-radius:12px;display:flex;align-items:center;justify-content:space-between;
  border:1px solid rgba(208,233,231,.2);
}
.avg-lbl{font-family:'DM Mono',monospace;font-size:11px;color:var(--mint);text-transform:uppercase;letter-spacing:.1em;}
.avg-val{font-size:32px;font-weight:700;color:var(--text-primary);font-family:'DM Mono',monospace;}

.empty-state{
  height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;
  color:var(--text-muted);opacity:.5;
}
.empty-state span{font-size:48px;margin-bottom:16px;}

@keyframes fadeUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
</style>
</head>
<body>

<div class="sidebar">
  <div class="sidebar-header">
    <h1>REVISIÓN <span>TARA SECA</span></h1>
    <p>Historial de Registros JSON</p>
  </div>
  <div class="file-list" id="file-list">
    <!-- Cargando... -->
  </div>
</div>

<div class="main-preview" id="main-preview">
  <div class="empty-state">
    <span>📁</span>
    <p>Selecciona un registro para ver los detalles</p>
  </div>
</div>

<template id="preview-template">
  <div class="preview-card">
    <div class="preview-header">
      <div class="preview-title">
        <h2 id="prev-lote">Lote: —</h2>
        <p id="prev-file">archivo.json</p>
      </div>
      <button class="btn-print" id="btn-print">🖨️ VER PARA IMPRIMIR</button>
    </div>
    <div class="preview-body">
      <div class="data-section">
        <div class="section-lbl">Información General</div>
        <div class="kv-grid">
          <div class="kv-item"><div class="kv-lbl">Fecha</div><div class="kv-val" id="val-fecha">—</div></div>
          <div class="kv-item"><div class="kv-lbl">Responsable</div><div class="kv-val" id="val-nombre">—</div></div>
          <div class="kv-item"><div class="kv-lbl">Cargo</div><div class="kv-val" id="val-cargo">—</div></div>
          <div class="kv-item"><div class="kv-lbl">Tamaño Muestra</div><div class="kv-val" id="val-tamano">—</div></div>
        </div>
      </div>
      
      <div class="data-section">
        <div class="section-lbl">Pesos Registrados (Primeros 10)</div>
        <table class="weights-table">
          <thead><tr><th>N°</th><th>Peso (g)</th><th>N°</th><th>Peso (g)</th></tr></thead>
          <tbody id="weights-body">
            <!-- Dinámico -->
          </tbody>
        </table>
      </div>

      <div class="avg-card">
        <span class="avg-lbl">Peso Promedio Final</span>
        <span class="avg-val" id="val-avg">—</span>
      </div>
    </div>
  </div>
</template>

<script>
function cargarArchivos() {
  fetch('listar_jsons_tara.php')
    .then(r => r.json())
    .then(files => {
      const list = document.getElementById('file-list');
      list.innerHTML = '';
      if(files.length === 0){
        list.innerHTML = '<p style="text-align:center;padding:20px;color:#666;">No hay registros.</p>';
        return;
      }
      files.forEach(f => {
        const row = document.createElement('div');
        row.className = 'file-row';
        row.innerHTML = `
          <div class="file-icon">📄</div>
          <div class="file-info">
            <div class="file-name">${f}</div>
            <div class="file-date">${f.split('_').slice(-2).join(' ').replace('.json','')}</div>
          </div>
        `;
        row.onclick = () => verDetalle(f, row);
        list.appendChild(row);
      });
    });
}

function verDetalle(file, rowEl) {
  document.querySelectorAll('.file-row').forEach(r => r.classList.remove('active'));
  rowEl.classList.add('active');

  fetch('leer_json_tara.php?file=' + encodeURIComponent(file))
    .then(r => r.json())
    .then(data => {
      const main = document.getElementById('main-preview');
      const temp = document.getElementById('preview-template').content.cloneNode(true);
      
      temp.querySelector('#prev-lote').textContent = 'Lote: ' + (data.lote || '—');
      temp.querySelector('#prev-file').textContent = file;
      temp.querySelector('#val-fecha').textContent = data.fecha || '—';
      temp.querySelector('#val-nombre').textContent = data.nombre || '—';
      temp.querySelector('#val-cargo').textContent = data.cargo || '—';
      temp.querySelector('#val-tamano').textContent = data.tamano || '—';
      temp.querySelector('#val-avg').textContent = data.pesoPromedio || '—';
      
      // Pesos (primeros 10 pares)
      const tbody = temp.querySelector('#weights-body');
      const weights = data.pesos || [];
      for(let i=0; i<5; i++){
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>${i+1}</td><td>${weights[i]||'—'}</td>
          <td>${i+16}</td><td>${weights[i+15]||'—'}</td>
        `;
        tbody.appendChild(tr);
      }

      temp.querySelector('#btn-print').onclick = () => {
        window.open('ver_tara.php?file=' + encodeURIComponent(file), '_blank');
      };

      main.innerHTML = '';
      main.appendChild(temp);
      main.querySelector('.preview-card').classList.add('visible');
    });
}

window.onload = cargarArchivos;
</script>
</body>
</html>
