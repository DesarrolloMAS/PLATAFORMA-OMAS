<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Liberacion de Equipos de Proceso - Zona Sur</title>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;600&family=IBM+Plex+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/css/formato_liberaciones.css">
</head>
<body>
<div class="header">
  <div class="header-left">
    <h1>PPR — Liberación de Equipos de Proceso Zona Sur</h1>
    <p>GM-MM-MQ-ME-FO-005 · Versión 1 · 02/12/2020</p>
  </div>
  <div class="header-right">
    <div class="progress-bar-wrap"><div class="progress-bar" id="progressBar"></div></div>
    <div class="progress-label" id="progressLabel">0%</div>
    <div class="badge" id="countBadge">0 / 0 equipos</div>
  </div>
</div>

<div class="meta-bar">
  <div class="meta-field">
    <label>Fecha Inspección</label>
    <input type="date" id="fechaInspeccion">
  </div>
  <div class="meta-field">
    <label>Inspector</label>
    <input type="text" id="inspector" placeholder="Nombre del inspector">
  </div>
  <div class="meta-field">
    <label>Hora Inicio</label>
    <input type="time" id="hora_inicio">
  </div>
  <div class="meta-field">
    <label>Hora Final</label>
    <input type="time" id="hora_final">
  </div>
</div>

<div class="container" id="mainContainer"></div>
<script src="app.js"></script>
</body>
<script>
  const equipos = [
  { codigo: "PLIPASELE01", nombre: "Elevador Trigo Sucio", ubicacion: "Bodega 1" },
  { codigo: "PLIPASTAE01", nombre: "Tablero Eléctrico Control Limpia", ubicacion: "Bodega 1" },
  { codigo: "PLIPASROT12", nombre: "Rosca Transportadora Descargue M1, M2, B1, B2", ubicacion: "Bodega 1, 2" },
  { codigo: "MOLPASROT02", nombre: "Rosca Transportadora Descargue Silos A, B, C, D", ubicacion: "Zona Mezclas" },
  { codigo: "SLAPASDOV01", nombre: "Dosificador Volumétrico Trigo Silo 1-2", ubicacion: "Zona Mezclas" },
  { codigo: "SLAPASDOV02", nombre: "Dosificador Volumétrico Trigo Silo 3-4", ubicacion: "Zona Mezclas" },
  { codigo: "SLAPASDOV03", nombre: "Dosificador Volumétrico Trigo Silo 5-6", ubicacion: "Zona Mezclas" },
  { codigo: "SLAPASDOV04", nombre: "Dosificador Volumétrico Trigo Silo 7-8", ubicacion: "Zona Mezclas" },
  { codigo: "SLAPASDOV05", nombre: "Dosificador Volumétrico Trigo Silo 9", ubicacion: "Zona Mezclas" },
  { codigo: "SLAPASDOV06", nombre: "Dosificador Volumétrico Trigo Silo 9", ubicacion: "Zona Mezclas" },
  { codigo: "SLAPASDOV07", nombre: "Dosificador Volumétrico Trigo Silo 9", ubicacion: "Zona Mezclas" },
  { codigo: "SLAPASDOV08", nombre: "Dosificador Volumétrico Trigo Silo 9", ubicacion: "Zona Mezclas" },
  { codigo: "SLAPASROT02", nombre: "Rosca Transportadora Descargue Silo 9", ubicacion: "Zona Mezclas" },
  { codigo: "SLAPASROT03", nombre: "Rosca Transportadora Descargue Silo 9", ubicacion: "Zona Mezclas" },
  { codigo: "SLAPASROT04", nombre: "Rosca Transportadora Descargue Silos 1 al 8", ubicacion: "Zona Mezclas" },
  { codigo: "MOLPASMEZ01", nombre: "Mezcladora Subproducto", ubicacion: "Zona Remolido" },
  { codigo: "MOLPASMOM01", nombre: "Molino Martillo", ubicacion: "Zona Remolido" },
  { codigo: "MOLPASCOM01", nombre: "Compresor Principal Neumático", ubicacion: "Zona Empaque" },
  { codigo: "MOLPASDIS01", nombre: "Disgregador C1A", ubicacion: "Zona Empaque" },
  { codigo: "MOLPASDIS02", nombre: "Disgregador C1B", ubicacion: "Zona Empaque" },
  { codigo: "MOLPASDIS03", nombre: "Disgregador C2", ubicacion: "Zona Empaque" },
  { codigo: "MOLPASDIS04", nombre: "Disgregador C3", ubicacion: "Zona Empaque" },
  { codigo: "MOLPASDIS05", nombre: "Disgregador C4", ubicacion: "Zona Empaque" },
  { codigo: "MOLPASDIS06", nombre: "Disgregador C5", ubicacion: "Zona Empaque" },
  { codigo: "MOLPASDIS07", nombre: "Disgregador C7", ubicacion: "Zona Empaque" },
  { codigo: "MOLPASDIS08", nombre: "Disgregador C8", ubicacion: "Zona Empaque" },
  { codigo: "MOLPASFLO01", nombre: "Flowbalancer MZAL-12 Silo A", ubicacion: "Primer Piso" },
  { codigo: "MOLPASFLO02", nombre: "Flowbalancer MZAL-12 Silo B", ubicacion: "Primer Piso" },
  { codigo: "MOLPASFLO03", nombre: "Flowbalancer MZAL-12 Silo C", ubicacion: "Primer Piso" },
  { codigo: "MOLPASFLO04", nombre: "Flowbalancer MZAL-12 Silo D", ubicacion: "Primer Piso" },
  { codigo: "MOLPASTAE03", nombre: "Tablero Eléctrico Control Turbina", ubicacion: "Primer Piso" },
  { codigo: "MOLPASBAN01", nombre: "Banco de Molienda T1", ubicacion: "Segundo Piso" },
  { codigo: "MOLPASBAN02", nombre: "Banco de Molienda T2", ubicacion: "Segundo Piso" },
  { codigo: "MOLPASBAN03", nombre: "Banco de Molienda T3", ubicacion: "Segundo Piso" },
  { codigo: "MOLPASBAN04", nombre: "Banco de Molienda T4G", ubicacion: "Segundo Piso" },
  { codigo: "MOLPASBAN05", nombre: "Banco de Molienda T4F", ubicacion: "Segundo Piso" },
  { codigo: "MOLPASBAN06", nombre: "Banco de Molienda T5F", ubicacion: "Segundo Piso" },
  { codigo: "MOLPASBAN07", nombre: "Banco de Molienda C1A", ubicacion: "Segundo Piso" },
  { codigo: "MOLPASBAN08", nombre: "Banco de Molienda C1B", ubicacion: "Segundo Piso" },
  { codigo: "MOLPASBAN09", nombre: "Banco de Molienda C2", ubicacion: "Segundo Piso" },
  { codigo: "MOLPASBAN10", nombre: "Banco de Molienda C3", ubicacion: "Segundo Piso" },
  { codigo: "MOLPASBAN11", nombre: "Banco de Molienda C4A", ubicacion: "Segundo Piso" },
  { codigo: "MOLPASBAN12", nombre: "Banco de Molienda C4B", ubicacion: "Segundo Piso" },
  { codigo: "MOLPASBAN13", nombre: "Banco de Molienda C5", ubicacion: "Segundo Piso" },
  { codigo: "MOLPASBAN14", nombre: "Banco de Molienda C6", ubicacion: "Segundo Piso" },
  { codigo: "MOLPASBAN15", nombre: "Banco de Molienda C7", ubicacion: "Segundo Piso" },
  { codigo: "MOLPASBAN16", nombre: "Banco de Molienda C8", ubicacion: "Segundo Piso" },
  { codigo: "MOLPASDOV01", nombre: "Dosificador Volumétrico Repaso T3", ubicacion: "Segundo Piso" },
  { codigo: "MOLPASDOV02", nombre: "Dosificador Volumétrico Repaso Filtro", ubicacion: "Segundo Piso" },
  { codigo: "MICPASDOG01", nombre: "Dosificador Gravimétrico MSDC-20/1", ubicacion: "Segundo Piso" },
  { codigo: "MICPASDOV01", nombre: "Dosificador Volumétrico Acrison 97137-03", ubicacion: "Segundo Piso" },
  { codigo: "MICPASDOV02", nombre: "Dosificador Volumétrico Acrison 97137-02", ubicacion: "Segundo Piso" },
  { codigo: "MICPASDOV03", nombre: "Dosificador Volumétrico Acrison 97031-03", ubicacion: "Segundo Piso" },
  { codigo: "MICPASROT01", nombre: "Rosca Transportadora Mejorantes", ubicacion: "Segundo Piso" },
  { codigo: "MOLPASTAE01", nombre: "Tablero Eléctrico Principal", ubicacion: "Segundo Piso" },
  { codigo: "MOLPASTAE02", nombre: "Tablero Eléctrico Condensadores", ubicacion: "Segundo Piso" },
  { codigo: "SLAPASREH01", nombre: "Regulador de Humedad MYFE-10", ubicacion: "Segundo Piso" },
  { codigo: "SLAPASREH02", nombre: "Regulador de Humedad MOZH-1000-C", ubicacion: "Segundo Piso" },
  { codigo: "SLAPASTAE01", nombre: "Tablero Eléctrico Variadores", ubicacion: "Segundo Piso" },
  { codigo: "MOLPASBAS01", nombre: "Báscula Electrónica MSDM-40 Trigo", ubicacion: "Tercer Piso" },
  { codigo: "MOLPASBAS02", nombre: "Báscula Electrónica MSDM-80 Harina", ubicacion: "Tercer Piso" },
  { codigo: "MOLPASCEP01", nombre: "Cepilladora Cep.1", ubicacion: "Tercer Piso" },
  { codigo: "MOLPASCEP03", nombre: "Cepilladora Cep.3", ubicacion: "Tercer Piso" },
  { codigo: "MOLPASCEP04", nombre: "Cepilladora Cep.4", ubicacion: "Tercer Piso" },
  { codigo: "MOLPASCEP05", nombre: "Cepilladora Cep.5", ubicacion: "Tercer Piso" },
  { codigo: "MOLPASDIS09", nombre: "Disgregador Dv1", ubicacion: "Tercer Piso" },
  { codigo: "MOLPASELE03", nombre: "Elevador Remolido", ubicacion: "Tercer Piso" },
  { codigo: "MOLPASFIL03", nombre: "Filtro Remolido", ubicacion: "Tercer Piso" },
  { codigo: "MOLPASIMA01", nombre: "Imán T1 Báscula", ubicacion: "Tercer Piso" },
  { codigo: "MOLPASIMA02", nombre: "Imán T1", ubicacion: "Segundo Piso" },
  { codigo: "MOLPASIMA03", nombre: "Imán Elevador Harina", ubicacion: "Primer Piso" },
  { codigo: "MOLPASROT01", nombre: "Rosca Transportadora Alimentación Harina", ubicacion: "Tercer Piso" },
  { codigo: "MOLPASSAS01", nombre: "Sasor 1", ubicacion: "Tercer Piso" },
  { codigo: "MOLPASSAS02", nombre: "Sasor 2", ubicacion: "Tercer Piso" },
  { codigo: "SLAPASCOM02", nombre: "Compresor Limpia", ubicacion: "Tercer Piso" },
  { codigo: "SLAPASDCH01", nombre: "Deschinadora", ubicacion: "Tercer Piso" },
  { codigo: "MOLPASCEP02", nombre: "Cepilladora Cep.2", ubicacion: "Cuarto Piso" },
  { codigo: "MOLPASCER01", nombre: "Cernedor MPAO 426", ubicacion: "Cuarto Piso" },
  { codigo: "MOLPASCER02", nombre: "Cernedor MPAO 626", ubicacion: "Cuarto Piso" },
  { codigo: "MOLPASTUR02", nombre: "Turbina Sasor", ubicacion: "Cuarto Piso" },
  { codigo: "SLAPASTUR01", nombre: "Turbina Deschinadora", ubicacion: "Cuarto Piso" },
  { codigo: "SLAPASCOM01", nombre: "Compresor Acondicionamiento", ubicacion: "Cuarto Piso" },
  { codigo: "SLAPASSEP01", nombre: "Separadora Segunda Limpieza", ubicacion: "Cuarto Piso" },
  { codigo: "MOLPASDES01", nombre: "Desatador C6", ubicacion: "Quinto Piso" },
  { codigo: "MOLPASELE01", nombre: "Elevador Molienda", ubicacion: "Quinto Piso" },
  { codigo: "MOLPASELE02", nombre: "Elevador Harina", ubicacion: "Quinto Piso" },
  { codigo: "MOLPASESC01", nombre: "Esclusa T1", ubicacion: "Quinto Piso" },
  { codigo: "MOLPASESC02", nombre: "Esclusa T2", ubicacion: "Quinto Piso" },
  { codigo: "MOLPASESC03", nombre: "Esclusa T3", ubicacion: "Quinto Piso" },
  { codigo: "MOLPASESC04", nombre: "Esclusa T4", ubicacion: "Quinto Piso" },
  { codigo: "MOLPASESC05", nombre: "Esclusa T4G", ubicacion: "Quinto Piso" },
  { codigo: "MOLPASESC06", nombre: "Esclusa T5", ubicacion: "Quinto Piso" },
  { codigo: "MOLPASESC07", nombre: "Esclusa C1A", ubicacion: "Quinto Piso" },
  { codigo: "MOLPASESC08", nombre: "Esclusa C1B", ubicacion: "Quinto Piso" },
  { codigo: "MOLPASESC09", nombre: "Esclusa C2", ubicacion: "Quinto Piso" },
  { codigo: "MOLPASESC10", nombre: "Esclusa C3", ubicacion: "Quinto Piso" },
  { codigo: "MOLPASESC11", nombre: "Esclusa C4", ubicacion: "Quinto Piso" },
  { codigo: "MOLPASESC12", nombre: "Esclusa C5", ubicacion: "Quinto Piso" },
  { codigo: "MOLPASESC13", nombre: "Esclusa C6", ubicacion: "Quinto Piso" },
  { codigo: "MOLPASESC14", nombre: "Esclusa C7", ubicacion: "Quinto Piso" },
  { codigo: "MOLPASESC15", nombre: "Esclusa C8", ubicacion: "Quinto Piso" },
  { codigo: "MOLPASESC16", nombre: "Esclusa DB1", ubicacion: "Quinto Piso" },
  { codigo: "MOLPASESC17", nombre: "Esclusa DV1", ubicacion: "Quinto Piso" },
  { codigo: "MOLPASESC18", nombre: "Esclusa S1", ubicacion: "Quinto Piso" },
  { codigo: "MOLPASESC19", nombre: "Esclusa S2", ubicacion: "Quinto Piso" },
  { codigo: "MOLPASESC20", nombre: "Esclusa Segunda", ubicacion: "Quinto Piso" },
  { codigo: "MOLPASESC21", nombre: "Esclusa Mogolla", ubicacion: "Quinto Piso" },
  { codigo: "MOLPASESC22", nombre: "Esclusa Salvado", ubicacion: "Quinto Piso" },
  { codigo: "MOLPASFIL01", nombre: "Filtro Sistema Neumático", ubicacion: "Quinto Piso" },
  { codigo: "MOLPASFIL02", nombre: "Filtro Sasores", ubicacion: "Quinto Piso" },
  { codigo: "MOLPASPUL01", nombre: "Pulidor T1", ubicacion: "Quinto Piso" },
  { codigo: "MOLPASTUR01", nombre: "Turbina Sistema Neumático", ubicacion: "Quinto Piso" },
  { codigo: "PLIPASELE02", nombre: "Elevador Prelimpia", ubicacion: "Quinto Piso" },
  { codigo: "PLIPASFIL01", nombre: "Filtro Primera Limpieza", ubicacion: "Quinto Piso" },
  { codigo: "PLIPASTUR01", nombre: "Turbina Filtro Limpia", ubicacion: "Quinto Piso" },
  { codigo: "SLAPASELE01", nombre: "Elevador Limpia", ubicacion: "Quinto Piso" },
  { codigo: "SLAPASIMA01", nombre: "Imán Limpia", ubicacion: "Quinto Piso" },
  { codigo: "SLAPASMOJ01", nombre: "Mojadora", ubicacion: "Quinto Piso" },
  { codigo: "SLAPASROT05", nombre: "Rosca Transportadora Alimentación Silos A, B, C, D", ubicacion: "Quinto Piso" },
  { codigo: "PLIPASROT01", nombre: "Rosca Transportadora Alimentación Granza", ubicacion: "Terraza" },
  { codigo: "PLIPASSEP01", nombre: "Separadora Primera Limpieza", ubicacion: "Terraza" },
  { codigo: "SLAPASMOT01", nombre: "Motobomba", ubicacion: "Terraza" },
  { codigo: "SLAPASTAN01", nombre: "Tanque Acondicionamiento 1000 Lts", ubicacion: "Terraza" },
  { codigo: "SLAPASTAN02", nombre: "Tanque Acondicionamiento 1000 Lts", ubicacion: "Terraza" },
];

// State
const state = {};
equipos.forEach(e => { state[e.codigo] = { status: null, obs: "" }; });

function getZones() {
  const zones = {};
  equipos.forEach(e => {
    if (!zones[e.ubicacion]) zones[e.ubicacion] = [];
    zones[e.ubicacion].push(e);
  });
  return zones;
}

function updateProgress() {
  const total = equipos.length;
  const done = Object.values(state).filter(s => s.status !== null).length;
  const pct = Math.round((done / total) * 100);
  document.getElementById('progressBar').style.width = pct + '%';
  document.getElementById('progressLabel').textContent = pct + '%';
  document.getElementById('countBadge').textContent = `${done} / ${total} equipos`;
}

function setStatus(codigo, status) {
  if (state[codigo].status === status) {
    state[codigo].status = null;
  } else {
    state[codigo].status = status;
  }
  const row = document.getElementById('row-' + codigo);
  row.className = state[codigo].status ? 'status-' + state[codigo].status.replace(' ', '-') : '';
  const cb = document.getElementById('cb-' + codigo);
  const nb = document.getElementById('nb-' + codigo);
  cb.classList.toggle('active', state[codigo].status === 'cumple');
  nb.classList.toggle('active', state[codigo].status === 'no-cumple');
  updateProgress();
}

function buildTable(items) {
  return `<table>
    <thead>
      <tr>
        <th>Código</th>
        <th>Nombre</th>
        <th>Ubicación</th>
        <th style="text-align:center">Cumple</th>
        <th style="text-align:center">No Cumple</th>
        <th>Observaciones</th>
      </tr>
    </thead>
    <tbody>
      ${items.map(e => `
      <tr id="row-${e.codigo}">
        <td class="td-code">${e.codigo}</td>
        <td class="td-name">${e.nombre}</td>
        <td class="td-ubicacion">${e.ubicacion}</td>
        <td class="td-check">
          <button class="check-btn cumple-btn" id="cb-${e.codigo}" onclick="setStatus('${e.codigo}','cumple')" title="Cumple">✓</button>
        </td>
        <td class="td-check">
          <button class="check-btn nocumple-btn" id="nb-${e.codigo}" onclick="setStatus('${e.codigo}','no-cumple')" title="No cumple">✗</button>
        </td>
        <td class="td-obs">
          <input type="text" placeholder="Observación..." onchange="state['${e.codigo}'].obs=this.value">
        </td>
      </tr>`).join('')}
    </tbody>
  </table>`;
}

function render() {
  const zones = getZones();
  const container = document.getElementById('mainContainer');
  let html = '';
  for (const [zona, items] of Object.entries(zones)) {
    html += `<div class="zone-section">
      <div class="zone-header">
        <span class="zone-label">${zona}</span>
        <div class="zone-line"></div>
        <span class="zone-stats">${items.length} equipos</span>
      </div>
      ${buildTable(items)}
    </div>`;
  }

  html += `<div class="bottom-section">
    <h2>Liberación de Proceso</h2>
    <div class="liberacion-grid">
      <div>
        <div class="liberacion-group" style="margin-bottom:16px">
          <label>¿Liberación aprobada?</label>
          <div class="yn-group">
            <button class="yn-btn" id="btn-si" onclick="toggleLib('si')">SI</button>
            <button class="yn-btn" id="btn-no" onclick="toggleLib('no')">NO</button>
          </div>
        </div>
        <div class="liberacion-group">
          <label>Responsable de Liberación</label>
          <input type="text" class="responsable-input" id="responsableLib" placeholder="Nombre y cargo">
        </div>
      </div>
      <div class="liberacion-group">
        <label>Condiciones de Liberación</label>
        <textarea class="condiciones-textarea" id="condicionesLib" placeholder="Describa las condiciones de liberación..."></textarea>
      </div>
    </div>
    <div class="actions-bar">
      <button class="btn-secondary" onclick="exportarJSON()">Guardar</button>
      <button class="btn-secondary" onclick="window.history.back()">Volver</button>
      <button class="btn-primary" onclick="enviarRegistro()">Enviar Registro</button>
    </div>
  </div>`;

  container.innerHTML = html;
  updateProgress();
}

let libStatus = null;
function toggleLib(v) {
  libStatus = (libStatus === v) ? null : v;
  document.getElementById('btn-si').className = 'yn-btn' + (libStatus === 'si' ? ' active-si' : '');
  document.getElementById('btn-no').className = 'yn-btn' + (libStatus === 'no' ? ' active-no' : '');
}

function exportarJSON() {
  const data = {
    formulario: "GM-MM-MQ-ME-FO-005",
    zona: "Zona Molienda",
    fecha: document.getElementById('fechaInspeccion').value,
    inspector: document.getElementById('inspector').value,
    hora_inicio: document.getElementById('hora_inicio').value,
    hora_final: document.getElementById('hora_final').value,
    equipos: equipos.map(e => ({ ...e, ...state[e.codigo] })),
    liberacion: {
      aprobada: libStatus,
      responsable: document.getElementById('responsableLib').value,
      condiciones: document.getElementById('condicionesLib').value
    }
  };
  const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a'); a.href = url;
  a.download = `liberacion-molienda-${data.fecha || 'sin-fecha'}.json`;
  a.click();
}

function guardar() {
  const pendientes = equipos.filter(e => state[e.codigo].status === null);
  if (pendientes.length > 0) {
    if (!confirm(`Hay ${pendientes.length} equipos sin revisar. ¿Desea continuar?`)) return;
  }
  alert(`✔ Registro guardado\n${equipos.length - pendientes.length} equipos revisados\nUse "Exportar JSON" para descargar los datos.`);
}
function enviarRegistro() {
  const pendientes = equipos.filter(e => state[e.codigo].status === null);
  if (pendientes.length > 0 && !confirm(`Hay ${pendientes.length} equipos sin revisar. ¿Desea continuar?`)) return;

  const data = {
    formulario: "GM-MM-MQ-ME-FO-005",
    zona: "Zona Molienda",
    fecha: document.getElementById('fechaInspeccion').value,
    inspector: document.getElementById('inspector').value,
    hora_inicio: document.getElementById('hora_inicio').value,
    hora_final: document.getElementById('hora_final').value,
    equipos: equipos.map(e => ({ 
      codigo: e.codigo, 
      nombre: e.nombre, 
      ubicacion: e.ubicacion,
      status: state[e.codigo].status,
      observacion: state[e.codigo].obs
    })),
    liberacion: {
      aprobada: libStatus,
      responsable: document.getElementById('responsableLib').value,
      condiciones: document.getElementById('condicionesLib').value
    }
  };

  fetch('guardado_liberaciones.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(data)
  })
  .then(res => res.json())
  .then(resp => {
    if (resp.ok) {
      alert("Registro guardado en el servidor como: " + resp.file);
      window.location.href = "menu_liberaciones.php";
    } else {
      alert("Error al guardar: " + resp.msg);
    }
  })
  .catch(err => {
    alert("Error de red o servidor: " + err);
  });
}
// Set today as default date
document.addEventListener('DOMContentLoaded', () => {
  document.getElementById('fechaInspeccion').value = new Date().toISOString().split('T')[0];
  render();
});
</script>
</html>