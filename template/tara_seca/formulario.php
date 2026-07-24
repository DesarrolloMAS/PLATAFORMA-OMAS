<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Control de Tara Seca - MO-PG-PD-FO-017</title>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;600&family=IBM+Plex+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
:root{--bg:#0f1117;--surface:#1a1d27;--surface2:#222637;--border:#2e3349;--accent:#f0b429;--accent2:#3ecf8e;--danger:#f87171;--text:#e2e8f0;--text-muted:#64748b;--text-dim:#94a3b8;}
*{box-sizing:border-box;margin:0;padding:0;}
body{background:var(--bg);color:var(--text);font-family:'IBM Plex Sans',sans-serif;font-size:14px;min-height:100vh;}
.header{background:var(--surface);border-bottom:2px solid var(--accent);padding:20px 32px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100;}
.header-left h1{font-family:'IBM Plex Mono',monospace;font-size:14px;font-weight:600;color:var(--accent);letter-spacing:.05em;text-transform:uppercase;}
.header-left p{font-size:12px;color:var(--text-muted);margin-top:2px;font-family:'IBM Plex Mono',monospace;}
.header-right{display:flex;flex-direction:column;align-items:flex-end;gap:4px;}
.meta-tag{font-family:'IBM Plex Mono',monospace;font-size:10px;color:var(--text-muted);padding:2px 8px;border:1px solid var(--border);border-radius:2px;}
.meta-tag span{color:var(--text-dim);}
.container{padding:32px;max-width:780px;margin:0 auto;}
.section-label{font-family:'IBM Plex Mono',monospace;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.12em;color:var(--accent);padding:3px 10px;border:1px solid var(--accent);border-radius:2px;display:inline-block;margin-bottom:16px;}
.datos-grid{background:var(--surface);border:1px solid var(--border);border-radius:6px;overflow:hidden;margin-bottom:32px;}
.dato-row{display:flex;border-bottom:1px solid var(--border);}
.dato-row:last-child{border-bottom:none;}
.dato-label{font-family:'IBM Plex Mono',monospace;font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.08em;padding:12px 16px;width:210px;min-width:210px;background:var(--surface2);border-right:1px solid var(--border);display:flex;align-items:center;}
.dato-input{flex:1;}
.dato-input input{width:100%;background:transparent;border:none;color:var(--text);font-family:'IBM Plex Sans',sans-serif;font-size:13px;padding:12px 16px;outline:none;transition:background .15s;}
.dato-input input:focus{background:var(--surface2);}
.dato-input input::placeholder{color:var(--text-muted);}
.samples-wrap{background:var(--surface);border:1px solid var(--border);border-radius:6px;overflow:hidden;margin-bottom:24px;}
.samples-table{width:100%;border-collapse:collapse;}
.samples-table thead tr{background:var(--surface2);}
.samples-table thead th{font-family:'IBM Plex Mono',monospace;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.1em;color:var(--text-muted);padding:10px 20px;text-align:center;border-bottom:1px solid var(--border);}
.samples-table thead th.accent-th{color:var(--accent);}
.samples-table tbody tr{border-bottom:1px solid var(--border);transition:background .12s;}
.samples-table tbody tr:last-child{border-bottom:none;}
.samples-table tbody tr:hover{background:var(--surface2);}
.col-n{font-family:'IBM Plex Mono',monospace;font-size:12px;color:var(--text-muted);text-align:center;padding:7px 20px;width:100px;}
.col-peso{text-align:center;padding:5px 20px;}
.col-peso input{width:200px;background:transparent;border:none;border-bottom:1px solid var(--border);color:var(--text);font-family:'IBM Plex Mono',monospace;font-size:14px;text-align:center;padding:6px 8px;outline:none;transition:border-color .15s,background .15s;}
.col-peso input:focus{border-bottom-color:var(--accent);background:rgba(240,180,41,.04);}
.col-peso input::placeholder{color:var(--border);font-size:12px;}
.promedio-bar{background:var(--surface);border:1px solid var(--border);border-radius:6px;padding:20px 28px;display:flex;align-items:center;justify-content:space-between;}
.promedio-label{font-family:'IBM Plex Mono',monospace;font-size:11px;text-transform:uppercase;letter-spacing:.1em;color:var(--text-muted);}
.promedio-right{display:flex;align-items:baseline;gap:8px;}
.promedio-value{font-family:'IBM Plex Mono',monospace;font-size:32px;font-weight:600;color:var(--accent);}
.promedio-unit{font-family:'IBM Plex Mono',monospace;font-size:12px;color:var(--text-muted);}
.promedio-count{font-family:'IBM Plex Mono',monospace;font-size:11px;color:var(--text-muted);}
.actions-bar{margin-top:24px;display:flex;gap:12px;justify-content:flex-end;}
.btn-primary{background:var(--accent);color:#0f1117;border:none;padding:10px 28px;border-radius:4px;font-family:'IBM Plex Mono',monospace;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.08em;cursor:pointer;transition:opacity .15s;}
.btn-primary:hover{opacity:.85;}
.btn-secondary{background:transparent;color:var(--text-dim);border:1px solid var(--border);padding:10px 28px;border-radius:4px;font-family:'IBM Plex Mono',monospace;font-size:12px;text-transform:uppercase;letter-spacing:.08em;cursor:pointer;transition:border-color .15s,color .15s;}
.btn-secondary:hover{border-color:var(--text-dim);color:var(--text);}
@media print{body{background:white;color:#111;}.header{background:white;border-bottom:2px solid #ccc;position:static;}.actions-bar{display:none;}.dato-input input,.col-peso input{color:#111;border-bottom-color:#ccc;}}
</style>
</head>
<body>

<div class="header">
  <div class="header-left">
    <h1>Control de Tara Seca &mdash; Harina de Trigo Fuerte Exportaci&oacute;n</h1>
    <p>PPR Gesti&oacute;n de la Producci&oacute;n &middot; Procedimiento Control de Cantidad</p>
  </div>
  <div class="header-right">
    <div class="meta-tag">C&oacute;digo: <span>MO-PG-PD-FO-017</span></div>
    <div class="meta-tag">Versi&oacute;n: <span>1</span></div>
    <div class="meta-tag">Fecha: <span>30/04/2024</span></div>
    <div class="meta-tag">P&aacute;gina: <span>1 de 1</span></div>
  </div>
</div>

<div class="container">
<form method="post" action="procesar_tara.php" id="taraForm">
  <span class="section-label">Datos Iniciales</span>
  <div class="datos-grid">
    <div class="dato-row">
      <div class="dato-label">Fecha</div>
      <div class="dato-input"><input type="date" id="fecha" name="fecha"></div>
    </div>
    <div class="dato-row">
      <div class="dato-label">Nombre</div>
      <div class="dato-input"><input type="text" id="nombre" name="nombre" placeholder="Nombre completo"></div>
    </div>
    <div class="dato-row">
      <div class="dato-label">Cargo</div>
      <div class="dato-input"><input type="text" id="cargo" name="cargo" placeholder="Cargo / Rol"></div>
    </div>
    <div class="dato-row">
      <div class="dato-label">Lote del Empaque</div>
      <div class="dato-input"><input type="text" id="lote" name="lote" placeholder="Ej. MB.01940"></div>
    </div>
    <div class="dato-row">
      <div class="dato-label">Tama&ntilde;o de la muestra</div>
      <div class="dato-input"><input type="number" id="tamano" name="tamano" min="1"></div>
    </div>
  </div>

  <span class="section-label">Registro de Pesos</span>
  <div class="samples-wrap">
    <table class="samples-table">
      <thead>
        <tr>
          <th>N&deg;</th>
          <th class="accent-th">Peso Empaque (g)</th>
        </tr>
      </thead>
      <tbody>
    <tr>
      <td class="col-n">1</td>
      <td class="col-peso"><input type="number" step="0.1" min="0" placeholder="0.0" data-idx="0" name="peso[]" oninput="updatePeso(this)"></td>
    </tr>
    <tr>
      <td class="col-n">2</td>
      <td class="col-peso"><input type="number" step="0.1" min="0" placeholder="0.0" data-idx="1" name="peso[]" oninput="updatePeso(this)"></td>
    </tr>
    <tr>
      <td class="col-n">3</td>
      <td class="col-peso"><input type="number" step="0.1" min="0" placeholder="0.0" data-idx="2" name="peso[]" oninput="updatePeso(this)"></td>
    </tr>
    <tr>
      <td class="col-n">4</td>
      <td class="col-peso"><input type="number" step="0.1" min="0" placeholder="0.0" data-idx="3" name="peso[]" oninput="updatePeso(this)"></td>
    </tr>
    <tr>
      <td class="col-n">5</td>
      <td class="col-peso"><input type="number" step="0.1" min="0" placeholder="0.0" data-idx="4" name="peso[]" oninput="updatePeso(this)"></td>
    </tr>
    <tr>
      <td class="col-n">6</td>
      <td class="col-peso"><input type="number" step="0.1" min="0" placeholder="0.0" data-idx="5" name="peso[]" oninput="updatePeso(this)"></td>
    </tr>
    <tr>
      <td class="col-n">7</td>
      <td class="col-peso"><input type="number" step="0.1" min="0" placeholder="0.0" data-idx="6" name="peso[]" oninput="updatePeso(this)"></td>
    </tr>
    <tr>
      <td class="col-n">8</td>
      <td class="col-peso"><input type="number" step="0.1" min="0" placeholder="0.0" data-idx="7" name="peso[]" oninput="updatePeso(this)"></td>
    </tr>
    <tr>
      <td class="col-n">9</td>
      <td class="col-peso"><input type="number" step="0.1" min="0" placeholder="0.0" data-idx="8" name="peso[]" oninput="updatePeso(this)"></td>
    </tr>
    <tr>
      <td class="col-n">10</td>
      <td class="col-peso"><input type="number" step="0.1" min="0" placeholder="0.0" data-idx="9" name="peso[]" oninput="updatePeso(this)"></td>
    </tr>
    <tr>
      <td class="col-n">11</td>
      <td class="col-peso"><input type="number" step="0.1" min="0" placeholder="0.0" data-idx="10" name="peso[]" oninput="updatePeso(this)"></td>
    </tr>
    <tr>
      <td class="col-n">12</td>
      <td class="col-peso"><input type="number" step="0.1" min="0" placeholder="0.0" data-idx="11" name="peso[]" oninput="updatePeso(this)"></td>
    </tr>
    <tr>
      <td class="col-n">13</td>
      <td class="col-peso"><input type="number" step="0.1" min="0" placeholder="0.0" data-idx="12" name="peso[]" oninput="updatePeso(this)"></td>
    </tr>
    <tr>
      <td class="col-n">14</td>
      <td class="col-peso"><input type="number" step="0.1" min="0" placeholder="0.0" data-idx="13" name="peso[]" oninput="updatePeso(this)"></td>
    </tr>
    <tr>
      <td class="col-n">15</td>
      <td class="col-peso"><input type="number" step="0.1" min="0" placeholder="0.0" data-idx="14" name="peso[]" oninput="updatePeso(this)"></td>
    </tr>
    <tr>
      <td class="col-n">16</td>
      <td class="col-peso"><input type="number" step="0.1" min="0" placeholder="0.0" data-idx="15" name="peso[]" oninput="updatePeso(this)"></td>
    </tr>
    <tr>
      <td class="col-n">17</td>
      <td class="col-peso"><input type="number" step="0.1" min="0" placeholder="0.0" data-idx="16" name="peso[]" oninput="updatePeso(this)"></td>
    </tr>
    <tr>
      <td class="col-n">18</td>
      <td class="col-peso"><input type="number" step="0.1" min="0" placeholder="0.0" data-idx="17" name="peso[]" oninput="updatePeso(this)"></td>
    </tr>
    <tr>
      <td class="col-n">19</td>
      <td class="col-peso"><input type="number" step="0.1" min="0" placeholder="0.0" data-idx="18" name="peso[]" oninput="updatePeso(this)"></td>
    </tr>
    <tr>
      <td class="col-n">20</td>
      <td class="col-peso"><input type="number" step="0.1" min="0" placeholder="0.0" data-idx="19" name="peso[]" oninput="updatePeso(this)"></td>
    </tr>
    <tr>
      <td class="col-n">21</td>
      <td class="col-peso"><input type="number" step="0.1" min="0" placeholder="0.0" data-idx="20" name="peso[]" oninput="updatePeso(this)"></td>
    </tr>
    <tr>
      <td class="col-n">22</td>
      <td class="col-peso"><input type="number" step="0.1" min="0" placeholder="0.0" data-idx="21" name="peso[]" oninput="updatePeso(this)"></td>
    </tr>
    <tr>
      <td class="col-n">23</td>
      <td class="col-peso"><input type="number" step="0.1" min="0" placeholder="0.0" data-idx="22" name="peso[]" oninput="updatePeso(this)"></td>
    </tr>
    <tr>
      <td class="col-n">24</td>
      <td class="col-peso"><input type="number" step="0.1" min="0" placeholder="0.0" data-idx="23" name="peso[]" oninput="updatePeso(this)"></td>
    </tr>
    <tr>
      <td class="col-n">25</td>
      <td class="col-peso"><input type="number" step="0.1" min="0" placeholder="0.0" data-idx="24" name="peso[]" oninput="updatePeso(this)"></td>
    </tr>
    <tr>
      <td class="col-n">26</td>
      <td class="col-peso"><input type="number" step="0.1" min="0" placeholder="0.0" data-idx="25" name="peso[]" oninput="updatePeso(this)"></td>
    </tr>
    <tr>
      <td class="col-n">27</td>
      <td class="col-peso"><input type="number" step="0.1" min="0" placeholder="0.0" data-idx="26" name="peso[]" oninput="updatePeso(this)"></td>
    </tr>
    <tr>
      <td class="col-n">28</td>
      <td class="col-peso"><input type="number" step="0.1" min="0" placeholder="0.0" data-idx="27" name="peso[]" oninput="updatePeso(this)"></td>
    </tr>
    <tr>
      <td class="col-n">29</td>
      <td class="col-peso"><input type="number" step="0.1" min="0" placeholder="0.0" data-idx="28" name="peso[]" oninput="updatePeso(this)"></td>
    </tr>
    <tr>
      <td class="col-n">30</td>
      <td class="col-peso"><input type="number" step="0.1" min="0" placeholder="0.0" data-idx="29" name="peso[]" oninput="updatePeso(this)"></td>
    </tr>
      </tbody>
    </table>
  </div>

  <div class="promedio-bar">
    <span class="promedio-label">Peso Promedio</span>
    <div class="promedio-right">
      <span class="promedio-value" id="promedioVal">&mdash;</span>
      <span class="promedio-unit">g</span>
      <span class="promedio-count" id="promedioCount"></span>
    </div>
    <input type="hidden" name="pesoPromedio" id="pesoPromedioHidden">
  </div>

  <div class="actions-bar">
    <button class="btn-secondary" type="submit">Imprimir</button>
    <button class="btn-primary" type="button">Guardar Registro</button>
  </div>
  </form>
</div>

<script>
var pesos = new Array(30).fill('');

function updatePeso(el){
  pesos[parseInt(el.dataset.idx)] = el.value !== '' ? parseFloat(el.value) : '';
  calcPromedio();
}

function calcPromedio(){
  var vals = pesos.filter(function(v){ return v !== '' && !isNaN(v); });
  var countEl = document.getElementById('promedioCount');
  if(vals.length === 0){
    document.getElementById('promedioVal').textContent = '—';
    countEl.textContent = '';
    document.getElementById('pesoPromedioHidden').value = '';
    return;
  }
  var sum = vals.reduce(function(a,b){ return a+b; }, 0);
  var promedio = (sum/vals.length).toFixed(1);
  document.getElementById('promedioVal').textContent = promedio;
  countEl.textContent = '(' + vals.length + ' de 30)';
  document.getElementById('pesoPromedioHidden').value = promedio;
}

function exportarJSON(){
  var data = {
    formulario:'MO-PG-PD-FO-017',
    fecha:document.getElementById('fecha').value,
    nombre:document.getElementById('nombre').value,
    cargo:document.getElementById('cargo').value,
    lote:document.getElementById('lote').value,
    tamano:document.getElementById('tamano').value,
    pesos:pesos,
    pesoPromedio:document.getElementById('promedioVal').textContent
  };
  var blob = new Blob([JSON.stringify(data,null,2)],{type:'application/json'});
  var a = document.createElement('a');
  a.href = URL.createObjectURL(blob);
  a.download = 'control-tara-'+(data.lote||'sin-lote')+'.json';
  a.click();
}

document.addEventListener('DOMContentLoaded', function(){
  document.getElementById('fecha').value = new Date().toISOString().split('T')[0];
  document.getElementById('pesoPromedioHidden').value = '';

  // Lógica para Guardar Registro
  const btnGuardar = document.querySelector('.btn-primary');
  btnGuardar.addEventListener('click', function() {
    const data = {
      formulario: 'MO-PG-PD-FO-017',
      fecha: document.getElementById('fecha').value,
      nombre: document.getElementById('nombre').value,
      cargo: document.getElementById('cargo').value,
      lote: document.getElementById('lote').value,
      tamano: document.getElementById('tamano').value,
      pesos: pesos,
      pesoPromedio: document.getElementById('promedioVal').textContent
    };

    if (!data.lote || !data.nombre) {
      alert('Por favor, completa el Lote y el Nombre antes de guardar.');
      return;
    }

    btnGuardar.disabled = true;
    btnGuardar.textContent = 'Guardando...';

    fetch('guardar_tara.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    })
    .then(r => r.json())
    .then(resp => {
      if (resp.status === 'ok') {
        alert('Registro guardado correctamente.');
        if (confirm('¿Deseas ir a la revisión de registros?')) {
          window.location.href = 'revision_tara.php';
        }
      } else {
        alert('Error al guardar: ' + resp.message);
      }
    })
    .catch(err => alert('Error de conexión: ' + err))
    .finally(() => {
      btnGuardar.disabled = false;
      btnGuardar.textContent = 'Guardar Registro';
    });
  });
});
</script>
</body>
</html>