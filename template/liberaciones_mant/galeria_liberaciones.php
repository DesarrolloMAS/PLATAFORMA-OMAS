<?php
if (isset($_GET['json'])) {
    $folder = '/var/www/fmt/archivos/generados/liberaciones_mant';
    $files = glob($folder . '/*.json');
    $jsonFiles = array_map('basename', $files);
    header('Content-Type: application/json');
    echo json_encode($jsonFiles);
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Galería de Liberaciones</title>
  <link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Barlow:wght@300;400;600&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --bg:       #0f1117;
      --surface:  #1a1d27;
      --surface2: #22263a;
      --border:   #2a2f42;
      --gold:     #c8922a;
      --green:    #2ecc71;
      --text:     #c9cdd8;
      --muted:    #5a6070;
      --mono:     'Share Tech Mono', monospace;
      --sans:     'Barlow', sans-serif;
    }

    body {
      background: var(--bg);
      color: var(--text);
      font-family: var(--sans);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    /* ── HEADER ─────────────────────────────── */
    header {
      background: var(--surface);
      border-bottom: 2px solid var(--gold);
      padding: 14px 32px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .header-left .title {
      font-family: var(--mono);
      font-size: 15px;
      color: var(--gold);
      letter-spacing: 0.08em;
      text-transform: uppercase;
    }
    .header-left .subtitle {
      font-family: var(--mono);
      font-size: 11px;
      color: var(--muted);
      margin-top: 3px;
      letter-spacing: 0.06em;
    }
    .header-right {
      font-family: var(--mono);
      font-size: 12px;
      color: var(--muted);
      border: 1px solid var(--border);
      padding: 5px 12px;
      border-radius: 2px;
    }

    /* ── MAIN ───────────────────────────────── */
    main {
      flex: 1;
      max-width: 900px;
      width: 100%;
      margin: 0 auto;
      padding: 48px 24px 32px;
    }

    .section-label {
      font-family: var(--mono);
      font-size: 11px;
      letter-spacing: 0.12em;
      color: var(--muted);
      text-transform: uppercase;
      margin-bottom: 28px;
    }
    .section-label span { color: var(--gold); font-weight: 600; }

    /* ── GRID ───────────────────────────────── */
    #galeria {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
      gap: 16px;
    }

    /* ── CARD ───────────────────────────────── */
    .json-card {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: 4px;
      padding: 22px 24px 20px;
      cursor: pointer;
      position: relative;
      transition: border-color .2s, background .2s;
      opacity: 0;
      transform: translateY(10px);
      animation: fadeUp .35s forwards;
    }
    .json-card:hover {
      border-color: var(--gold);
      background: var(--surface2);
    }
    .json-card:hover .arrow { color: var(--gold); transform: translateX(4px); }

    @keyframes fadeUp {
      to { opacity: 1; transform: translateY(0); }
    }

    .card-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: rgba(46,204,113,.12);
      border: 1px solid var(--green);
      color: var(--green);
      font-family: var(--mono);
      font-size: 10px;
      letter-spacing: 0.1em;
      padding: 3px 10px;
      border-radius: 2px;
      margin-bottom: 14px;
      text-transform: uppercase;
    }
    .card-badge::before { content: '✓  '; }

    .card-name {
      font-size: 20px;
      font-weight: 600;
      color: #e8eaf0;
      margin-bottom: 16px;
    }

    .card-meta {
      font-family: var(--mono);
      font-size: 11px;
      color: var(--muted);
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .arrow {
      font-size: 16px;
      color: var(--muted);
      transition: color .2s, transform .2s;
    }

    /* ── FOOTER BAR ─────────────────────────── */
    footer {
      max-width: 900px;
      width: 100%;
      margin: 0 auto 32px;
      padding: 0 24px;
    }
    .footer-bar {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: 4px;
      padding: 14px 20px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      flex-wrap: wrap;
    }
    .refresh-time {
      font-family: var(--mono);
      font-size: 11px;
      color: var(--muted);
    }
    .refresh-time span { color: var(--text); }

    .btn {
      font-family: var(--mono);
      font-size: 11px;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      border-radius: 2px;
      padding: 7px 18px;
      cursor: pointer;
      transition: all .18s;
    }
    .btn-refresh {
      background: transparent;
      border: 1px solid var(--border);
      color: var(--text);
    }
    .btn-refresh:hover { border-color: var(--gold); color: var(--gold); }
    .btn-back {
      background: transparent;
      border: none;
      color: var(--muted);
      text-decoration: underline;
      text-underline-offset: 3px;
    }
    .btn-back:hover { color: var(--text); }

    .empty {
      grid-column: 1/-1;
      text-align: center;
      padding: 60px 0;
      font-family: var(--mono);
      color: var(--muted);
      font-size: 13px;
    }
  </style>
</head>
<body>

<header>
  <div class="header-left">
    <div class="title">PPR — Galería de Liberaciones</div>
    <div class="subtitle">GM-MM-MQ-ME-FO-005 · Seleccione el documento a visualizar</div>
  </div>
  <div class="header-right" id="fecha-header">—</div>
</header>

<main>
  <div class="section-label">
    Documentos disponibles — <span id="fecha-label">cargando...</span>
  </div>
  <div id="galeria"></div>
</main>

<footer>
  <div class="footer-bar">
    <div class="refresh-time">Último refresco: <span id="hora-refresco">—</span></div>
    <div style="display:flex;gap:10px;align-items:center;">
      <button class="btn btn-refresh" onclick="cargarArchivos()">⟳ &nbsp;Refrescar</button>
      <button class="btn btn-back" onclick="history.back()">Volver</button>
    </div>
  </div>
</footer>

<script>
  function formatFechaLarga(d) {
    const dias  = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
    const meses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio',
                   'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
    return `${dias[d.getDay()]}, ${d.getDate()} de ${meses[d.getMonth()]} de ${d.getFullYear()}`;
  }
  function formatISO(d)  { return d.toISOString().slice(0,10); }
  function formatHora(d) { return d.toLocaleTimeString('es-CO',{hour:'2-digit',minute:'2-digit',second:'2-digit'}); }

  const ahora = new Date();
  document.getElementById('fecha-header').textContent = formatISO(ahora);
  document.getElementById('fecha-label').textContent  = formatFechaLarga(ahora).toUpperCase();

  function cargarArchivos() {
    document.getElementById('hora-refresco').textContent = formatHora(new Date());
    const galeria = document.getElementById('galeria');
    galeria.innerHTML = '';

    fetch('galeria_liberaciones.php?json=1')
      .then(r => r.json())
      .then(files => {
        if (!files.length) {
          galeria.innerHTML = '<div class="empty">No se encontraron documentos JSON.</div>';
          return;
        }
        files.forEach((file, i) => {
          const nombre = file.replace('.json','').replace(/_/g,' ');
          const card = document.createElement('div');
          card.className = 'json-card';
          card.style.animationDelay = (i * 70) + 'ms';
          card.innerHTML = `
            <div class="card-badge">Disponible</div>
            <div class="card-name">${nombre}</div>
            <div class="card-meta">
              <span>${file}</span>
              <span class="arrow">→</span>
            </div>
          `;
          card.onclick = () => verDocumento(file);
          galeria.appendChild(card);
        });
      })
      .catch(() => {
        galeria.innerHTML = '<div class="empty">Error al cargar los documentos.</div>';
      });
  }

  function verDocumento(file) {
    const rutaJson = '/archivos/generados/liberaciones_mant/' + file;
    window.open('plantilla_liberaciones.html?json=' + encodeURIComponent(rutaJson), '_blank');
  }

  cargarArchivos();
</script>
</body>
</html>