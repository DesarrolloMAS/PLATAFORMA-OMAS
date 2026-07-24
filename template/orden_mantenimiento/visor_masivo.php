<?php
/**
 * visor_masivo.php
 * ---------------------
 * Orquestador de la descarga de "Periodo (PDF)": pide a generar_periodo.php
 * que genere un PDF individual por cada registro del mes (un solo lote de
 * Puppeteer en el servidor) y luego los descarga uno por uno en el navegador,
 * igual que antes — solo que ahora cada archivo es un PDF real generado con
 * el motor de impresión de Chrome, no una captura de pantalla con html2pdf.js.
 */
require_once '../sesion.php';
verificarAutenticacion();

$file = $_GET['file'] ?? '';
if (!$file) {
    die('Periodo no especificado.');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Descargando periodo <?= htmlspecialchars($file) ?></title>
<style>
    body { margin: 0; font-family: sans-serif; background: #fff; color: #333; }
    #overlay {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(255,255,255,0.98); z-index: 99999;
        display: flex; flex-direction: column; align-items: center; justify-content: center;
    }
    #overlay h2 { margin-bottom: 10px; }
    #texto-progreso { font-size: 18px; text-align: center; line-height: 1.5; }
    #texto-progreso small { color: #666; }
</style>
</head>
<body>
<div id="overlay">
    <h2>⏳ Generando PDFs del Periodo</h2>
    <p id="texto-progreso">Preparando registros...<br><small>Por favor no cierres esta pestaña</small></p>
</div>
<script>
const FILE = "<?= addslashes($file) ?>";

async function iniciar() {
    const progreso = document.getElementById('texto-progreso');
    progreso.innerHTML = 'Generando PDFs en el servidor...<br><small>Esto puede tardar según la cantidad de registros</small>';

    let data;
    try {
        const res = await fetch('generar_periodo.php?file=' + encodeURIComponent(FILE));
        data = await res.json();
    } catch (e) {
        progreso.innerHTML = '❌ Error de conexión al generar los PDFs.';
        return;
    }

    if (!data.success) {
        progreso.innerHTML = '❌ ' + (data.error || 'No se pudieron generar los PDFs.');
        return;
    }

    const archivos = data.archivos || [];
    if (archivos.length === 0) {
        progreso.innerHTML = '⚠️ No se generó ningún PDF.';
        return;
    }

    for (let i = 0; i < archivos.length; i++) {
        const a = archivos[i];
        progreso.innerHTML = `Descargando ${i + 1} de ${archivos.length}...<br><small>${a.filename}</small>`;

        const link = document.createElement('a');
        link.href = 'servir_pdf_temp.php?token=' + encodeURIComponent(a.token) + '&filename=' + encodeURIComponent(a.filename);
        link.download = a.filename;
        document.body.appendChild(link);
        link.click();
        link.remove();

        // Pausa entre descargas para que el navegador no las bloquee
        await new Promise(r => setTimeout(r, 700));
    }

    const erroresTxt = (data.errores && data.errores.length)
        ? `<br><small style="color:#f59e0b;">⚠️ ${data.errores.length} registro(s) no se pudieron generar.</small>`
        : '';
    progreso.innerHTML = `✅ ¡Descarga completa! (${archivos.length}/${data.total})${erroresTxt}<br><small>Puedes cerrar esta pestaña</small>`;
    setTimeout(function() { window.close(); }, 3000);
}

window.addEventListener('load', iniciar);
</script>
</body>
</html>
