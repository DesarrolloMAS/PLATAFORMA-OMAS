<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Liberacion de Equipos de Proceso - Zona Empaque</title>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;600&family=IBM+Plex+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/css/formato_liberaciones.css">
</head>
<body>

<div class="header">
  <div class="header-left">
    <h1>PPR — Liberación de Equipos de Proceso Zona Empaque</h1>
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

</body>
<script>
    var equipos = [
  {codigo:"EMPPASBAL01",nombre:"Balanza CAS",ubicacion:"Zona Empaque"},
  {codigo:"EMPPASBAL02",nombre:"Balanza FE-45 C",ubicacion:"Zona Empaque"},
  {codigo:"EMPPASBAL03",nombre:"Balanza SUPER-SS",ubicacion:"Zona Empaque"},
  {codigo:"EMPPASBAS01",nombre:"Bascula Vector VI405",ubicacion:"Zona Empaque"},
  {codigo:"EMPPASBAS02",nombre:"Bascula Subproducto",ubicacion:"Zona Empaque"},
  {codigo:"EMPPASBAS03",nombre:"Bascula FE-66C",ubicacion:"Zona Empaque"},
  {codigo:"EMPPASBAS04",nombre:"Bascula Vector VI110",ubicacion:"Zona Empaque"},
  {codigo:"EMPPASBAS05",nombre:"Bascula LP7510A",ubicacion:"Zona Empaque"},
  {codigo:"EMPPASBAT01",nombre:"Banda Transportadora Despachos",ubicacion:"Bodega PT"},
  {codigo:"EMPPASBAT02",nombre:"Banda Transportadora Arrume",ubicacion:"Bodega PT"},
  {codigo:"EMPPASBAT03",nombre:"Banda Transportadora Horizontal 5 mts",ubicacion:"Bodega PT"},
  {codigo:"EMPPASBAT04",nombre:"Banda Transportadora Horizontal 7 mts",ubicacion:"Bodega PT"},
  {codigo:"EMPPASCOD01",nombre:"Codificador Codimarket",ubicacion:"Bodega Materiales"},
  {codigo:"EMPPASCOD02",nombre:"Codificador Nalinsumos",ubicacion:"Bodega Materiales"},
  {codigo:"EMPPASCOS01",nombre:"Cosedora Fischbein 1610002 F",ubicacion:"Zona Empaque"},
  {codigo:"EMPPASCOS02",nombre:"Cosedora Fischbein 404115 F",ubicacion:"Zona Empaque"},
  {codigo:"EMPPASCOS03",nombre:"Cosedora Fischbein 109277 F",ubicacion:"Zona Empaque"},
  {codigo:"EMPPASCOS05",nombre:"Cosedora Fischbein 54682",ubicacion:"Zona Empaque"},
  {codigo:"EMPPASCOS06",nombre:"Cosedora Yao Han F300A",ubicacion:"Zona Empaque"},
  {codigo:"EMPPASCOS07",nombre:"Cosedora Yao Han 343972",ubicacion:"Zona Empaque"},
  {codigo:"EMPPASCOS08",nombre:"Cosedora Yao Han F300A",ubicacion:"Zona Empaque"},
  {codigo:"EMPPASCOS09",nombre:"Cosedora Yao Han F300A",ubicacion:"Zona Empaque"},
  {codigo:"EMPPASCOS10",nombre:"Cosedora Yao Han 361733",ubicacion:"Zona Empaque"},
  {codigo:"EMPPASEMP01",nombre:"Empacadora de Harina Bulto",ubicacion:"Zona Empaque"},
  {codigo:"EMPPASEMP02",nombre:"Empacadora de Tornillo Cuarto",ubicacion:"Zona Empaque"},
  {codigo:"EMPPASEMP03",nombre:"Empacadora de Tornillo Kilo",ubicacion:"Zona Empaque"},
  {codigo:"EMPPASEMP04",nombre:"Empacadora de Tornillo Libra",ubicacion:"Zona Empaque"},
  {codigo:"EMPPASEMP05",nombre:"Empacadora de Tornillo kilo/Libra",ubicacion:"Zona Empaque"},
  {codigo:"EMPPASEST03",nombre:"Estibador Hidraulico",ubicacion:"Bodega PT"},
  {codigo:"EMPPASEST04",nombre:"Estibador Hidraulico",ubicacion:"Bodega PT"},
  {codigo:"EMPPASEST05",nombre:"Estibador Hidraulico",ubicacion:"Bodega PT"},
  {codigo:"EMPPASEST06",nombre:"Estibador Hidraulico",ubicacion:"Bodega PT"},
  {codigo:"EMPPASEST08",nombre:"Estibador Hidraulico",ubicacion:"Bodega PT"},
  {codigo:"EMPPASEST09",nombre:"Estibador Hidraulico",ubicacion:"Bodega PT"},
  {codigo:"EMPPASEST07",nombre:"Estibador Hidraulico - Electrico",ubicacion:"Bodega PT"},
  {codigo:"EMPPASSEL01",nombre:"Selladora Libra",ubicacion:"Zona Empaque"},
  {codigo:"EMPPASSEL02",nombre:"Selladora Libra",ubicacion:"Zona Empaque"}
];

var state={};
equipos.forEach(function(e){state[e.codigo]={status:null,obs:""};});
var libStatus=null;

function getZones(){
  var z={};
  equipos.forEach(function(e){
    if(!z[e.ubicacion])z[e.ubicacion]=[];
    z[e.ubicacion].push(e);
  });
  return z;
}

function updateProgress(){
  var total=equipos.length;
  var done=Object.values(state).filter(function(s){return s.status!==null;}).length;
  var pct=Math.round((done/total)*100);
  document.getElementById("progressBar").style.width=pct+"%";
  document.getElementById("progressLabel").textContent=pct+"%";
  document.getElementById("countBadge").textContent=done+" / "+total+" equipos";
}

function setStatus(codigo,status){
  state[codigo].status=(state[codigo].status===status)?null:status;
  var row=document.getElementById("row-"+codigo);
  row.className=state[codigo].status?"status-"+state[codigo].status:"";
  document.getElementById("cb-"+codigo).classList.toggle("active",state[codigo].status==="cumple");
  document.getElementById("nb-"+codigo).classList.toggle("active",state[codigo].status==="no-cumple");
  updateProgress();
}

function toggleLib(v){
  libStatus=(libStatus===v)?null:v;
  document.getElementById("btn-si").className="yn-btn"+(libStatus==="si"?" active-si":"");
  document.getElementById("btn-no").className="yn-btn"+(libStatus==="no"?" active-no":"");
}

function exportarJSON(){
  var data={
    formulario:"GM-MM-MQ-ME-FO-005",zona:"Zona Empaque",
    fecha:document.getElementById("fechaInspeccion").value,
    inspector:document.getElementById("inspector").value,
    hora_inicio:document.getElementById("hora_inicio").value,
    hora_final:document.getElementById("hora_final").value,
    equipos:equipos.map(function(e){return Object.assign({},e,state[e.codigo]);}),
    liberacion:{aprobada:libStatus,
      responsable:document.getElementById("responsableLib").value,
      condiciones:document.getElementById("condicionesLib").value}
  };
  var blob=new Blob([JSON.stringify(data,null,2)],{type:"application/json"});
  var url=URL.createObjectURL(blob);
  var a=document.createElement("a");
  a.href=url;
  a.download="liberacion-empaque-"+(data.fecha||"sin-fecha")+".json";
  a.click();
}

function guardar(){
  var p=equipos.filter(function(e){return state[e.codigo].status===null;});
  if(p.length>0&&!confirm("Hay "+p.length+" equipos sin revisar. Desea continuar?"))return;
  alert("Registro guardado. "+(equipos.length-p.length)+" equipos revisados.");
}

function buildRow(e){
  return "<tr id=\"row-"+e.codigo+"\">"
    +"<td class=\"td-code\">"+e.codigo+"</td>"
    +"<td class=\"td-name\">"+e.nombre+"</td>"
    +"<td class=\"td-ubicacion\">"+e.ubicacion+"</td>"
    +"<td class=\"td-check\"><button class=\"check-btn cumple-btn\" id=\"cb-"+e.codigo+"\" "
    +"data-c=\""+e.codigo+"\" data-s=\"cumple\" "
    +"onclick=\"setStatus(this.dataset.c,this.dataset.s)\" title=\"Cumple\">&#10003;</button></td>"
    +"<td class=\"td-check\"><button class=\"check-btn nocumple-btn\" id=\"nb-"+e.codigo+"\" "
    +"data-c=\""+e.codigo+"\" data-s=\"no-cumple\" "
    +"onclick=\"setStatus(this.dataset.c,this.dataset.s)\" title=\"No cumple\">&#10007;</button></td>"
    +"<td class=\"td-obs\"><input type=\"text\" placeholder=\"Observacion...\" "
    +"data-c=\""+e.codigo+"\" onchange=\"state[this.dataset.c].obs=this.value\"></td>"
    +"</tr>";
}

function buildTable(items){
  var rows=items.map(buildRow).join("");
  return "<table><thead><tr><th>Codigo</th><th>Nombre</th><th>Ubicacion</th>"
    +"<th style=\"text-align:center\">Cumple</th><th style=\"text-align:center\">No Cumple</th>"
    +"<th>Observaciones</th></tr></thead><tbody>"+rows+"</tbody></table>";
}

function render(){
  var zones=getZones();
  var container=document.getElementById("mainContainer");
  var html="";
  Object.entries(zones).forEach(function(entry){
    var zona=entry[0];var items=entry[1];
    html+="<div class=\"zone-section\"><div class=\"zone-header\">"
      +"<span class=\"zone-label\">"+zona+"</span>"
      +"<div class=\"zone-line\"></div>"
      +"<span class=\"zone-stats\">"+items.length+" equipos</span></div>"
      +buildTable(items)+"</div>";
  });
  html+="<div class=\"bottom-section\"><h2>Liberacion de Proceso</h2>"
    +"<div class=\"liberacion-grid\"><div>"
    +"<div class=\"liberacion-group\"><label>Liberacion aprobada?</label>"
    +"<div class=\"yn-group\">"
    +"<button class=\"yn-btn\" id=\"btn-si\" onclick=\"toggleLib('si')\">SI</button>"
    +"<button class=\"yn-btn\" id=\"btn-no\" onclick=\"toggleLib('no')\">NO</button>"
    +"</div></div>"
    +"<div class=\"liberacion-group\"><label>Responsable de Liberacion</label>"
    +"<input type=\"text\" class=\"responsable-input\" id=\"responsableLib\" placeholder=\"Nombre y cargo\"></div></div>"
    +"<div class=\"liberacion-group\"><label>Condiciones de Liberacion</label>"
    +"<textarea class=\"condiciones-textarea\" id=\"condicionesLib\" placeholder=\"Describa las condiciones...\"></textarea></div></div>"
    +"<div class=\"actions-bar\">"
    +"<button class=\"btn-secondary\" onclick=\"exportarJSON()\">Guardar</button>"
    +"<button class=\"btn-secondary\" onclick=\"window.history.back()\">Volver</button>"
    +"<button class=\"btn-primary\" onclick=\"enviarRegistro()\">Enviar Registro</button>"
    +"</div></div>";
  container.innerHTML=html;
  updateProgress();
}

document.addEventListener("DOMContentLoaded",function(){
  document.getElementById("fechaInspeccion").value=new Date().toISOString().split("T")[0];
  render();
});
function enviarRegistro(){
  var p = equipos.filter(function(e){return state[e.codigo].status===null;});
  if(p.length>0 && !confirm("Hay "+p.length+" equipos sin revisar. ¿Desea continuar?")) return;

  var data = {
    formulario: "GM-MM-MQ-ME-FO-005",
    zona: "Zona Empaque",
    fecha: document.getElementById("fechaInspeccion").value,
    inspector: document.getElementById("inspector").value,
    hora_inicio: document.getElementById("hora_inicio").value,
    hora_final: document.getElementById("hora_final").value,
    equipos: equipos.map(function(e){
      return {
        codigo: e.codigo,
        nombre: e.nombre,
        ubicacion: e.ubicacion,
        status: state[e.codigo].status,
        observacion: state[e.codigo].obs
      };
    }),
    liberacion: {
      aprobada: libStatus,
      responsable: document.getElementById("responsableLib").value,
      condiciones: document.getElementById("condicionesLib").value
    }
  };

  fetch('guardado_liberaciones.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify(data)
  })
  .then(res => res.json())
  .then(resp => {
    if(resp.ok){
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
</script>
</html>