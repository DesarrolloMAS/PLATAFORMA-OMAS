<?php
require '../sesion.php';
verificarAutenticacion();

// ====== ESCANEO DE INVESTIGACIONES JSON ======
$carpeta = '/var/www/fmt/archivos/generados/HSEQ/investigacionesjson/';
$archivos = [];

if (is_dir($carpeta)) {
    foreach (scandir($carpeta) as $archivo) {
        if ($archivo === '.' || $archivo === '..') continue;
        $ext = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
        if ($ext !== 'json') continue;

        $ruta = $carpeta . $archivo;
        $data = json_decode(file_get_contents($ruta), true);
        if (!$data) continue;

        $id = pathinfo($archivo, PATHINFO_FILENAME);
        $archivos[] = [
            'id'           => $id,
            'nombre'       => $archivo,
            'codigo'       => $data['post']['inv_codigo'] ?? 'Sin código',
            'fecha_inv'    => $data['post']['inv_fecha'] ?? '',
            'responsable'  => $data['post']['inv_responsable'] ?? 'Sin asignar',
            'trabajador'   => trim(($data['post']['trab_nombre1'] ?? '') . ' ' . ($data['post']['trab_apellido1'] ?? '')),
            'tipo'         => $data['post']['acc_tipo'] ?? '',
            'timestamp'    => $data['timestamp'] ?? date('Y-m-d H:i:s', filemtime($ruta)),
            'tamano'       => filesize($ruta),
            'fecha_mod'    => date('Y-m-d H:i:s', filemtime($ruta)),
        ];
    }
    usort($archivos, function($a, $b) {
        return strtotime($b['fecha_mod']) - strtotime($a['fecha_mod']);
    });
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Investigaciones de Accidentes</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/revision_maquinas.css">
</head>
<body>
    <div class="header-container">
        <h1>
            <div class="header-icon">�</div>
            Investigaciones de Accidentes e Incidentes
        </h1>
        <a class="volver" href="/template/menu_hseq_adm.html">
            <span>←</span> Volver
        </a>
    </div>

    <div class="main-container">
        <div class="toolbar">
            <div class="stats">
                <div class="stat-item">
                    <div class="stat-icon">📊</div>
                    <span id="total-archivos">Cargando estadísticas...</span>
                </div>
                <div class="stat-item">
                    <div class="stat-icon">✓</div>
                    <span id="archivos-seleccionados">0 seleccionados</span>
                </div>
                <button id="eliminar-seleccionados">
                🗑️ Eliminar seleccionados
            </button>
            </div>
            <a href="formato_investigacion_acc.html" style="text-decoration:none;background:#10b981;color:#fff;padding:8px 16px;border-radius:6px;font-size:0.85em;font-weight:600;">
                + Nueva Investigación
            </a>
        </div>

        <div id="visor-pdfs">
            <div class="loading-container">
                <div class="loading-spinner"></div>
                <p class="message">Cargando investigaciones...</p>
            </div>
        </div>
    </div>

       <script>
    const archivos = <?php echo json_encode($archivos, JSON_UNESCAPED_UNICODE); ?>;
    
    function actualizarContadorSeleccionados() {
        const checks = document.querySelectorAll('.pdf-checkbox:checked');
        document.getElementById('archivos-seleccionados').textContent =
            `${checks.length} seleccionado${checks.length !== 1 ? 's' : ''}`;
    }
    
    function formatearTamano(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / 1048576).toFixed(1) + ' MB';
    }
    
    function formatearFecha(fecha) {
        if (!fecha) return 'Sin fecha';
        const partes = fecha.split('-');
        if (partes.length === 3) return `${partes[2]}/${partes[1]}/${partes[0]}`;
        return fecha;
    }
    
    (function renderArchivos() {
        const container = document.getElementById('visor-pdfs');
        container.innerHTML = '';
    
        if (!archivos || archivos.length === 0) {
            container.innerHTML = '<p class="message">No hay investigaciones registradas aún.</p>';
            document.getElementById('total-archivos').textContent = '0 investigaciones';
            return;
        }
    
        document.getElementById('total-archivos').textContent =
            `${archivos.length} investigación${archivos.length !== 1 ? 'es' : ''} registrada${archivos.length !== 1 ? 's' : ''}`;
    
        const card = document.createElement('div');
        card.classList.add('zona-card');
    
        const header = document.createElement('div');
        header.classList.add('zona-header', 'active');
        header.innerHTML = `
            <div class="zona-title">
                <span class="zona-icon">▶</span>
                <span>Investigaciones</span>
            </div>
            <span class="zona-badge">${archivos.length} registro${archivos.length !== 1 ? 's' : ''}</span>
        `;
    
        const contenido = document.createElement('div');
        contenido.classList.add('zona-contenido', 'zona-contenido-visible');
    
        header.onclick = () => {
            header.classList.toggle('active');
            contenido.classList.toggle('zona-contenido-visible');
        };
    
        const listaDiv = document.createElement('div');
        listaDiv.classList.add('maquina');
    
        archivos.forEach((archivo) => {
            const item = document.createElement('div');
            item.classList.add('pdf-item');
    
            const trabajadorTxt = archivo.trabajador.trim() || 'Sin trabajador';
            const responsableTxt = archivo.responsable || 'Sin responsable';
    
            // Estructura robusta: referencias directas
            item.innerHTML = `
                <input type="checkbox" class="pdf-checkbox" value="${archivo.id}">
                <span class="pdf-name" style="font-weight:600;">${archivo.codigo}</span>
                <span style="color:#888; font-size:0.8em; margin-left:8px;">
                    📅 ${formatearFecha(archivo.fecha_inv)} · 👷 ${trabajadorTxt} · 👤 ${responsableTxt} · ${formatearTamano(archivo.tamano)}
                </span>
                <div class="pdf-actions">
                    <a href="formulario_investigacion_impr.php?id=${archivo.id}" target="_blank" class="btn-action btn-ver">
                        � Ver Reporte
                    </a>
                </div>
            `;
    
            listaDiv.appendChild(item);
        });
    
        contenido.appendChild(listaDiv);
        card.appendChild(header);
        card.appendChild(contenido);
        container.appendChild(card);
    })();
    
    // ============================================
    // VERIFICACION DE SESION AJAX 10 SEG
    setInterval(function() {
        verificarSesionAjax(function(activa) {});
    }, 10000);
    
    function verificarSesionAjax(callback) {
        fetch('/template/verificar_sesion.php')
            .then(response => response.json())
            .then(data => {
                if (data.activa) {
                    callback(true);
                } else {
                    alert('Tu sesión ha expirado. Por favor, inicia sesión nuevamente.');
                    window.location.href = '/index.php?motivo=sesion';
                    callback(false);
                }
            })
            .catch(() => {
                alert('Error al verificar la sesión.');
                callback(false);
            });
    }
    document.getElementById('eliminar-seleccionados').onclick = function() {
    const checks = document.querySelectorAll('.pdf-checkbox:checked');
    if (checks.length === 0) {
        alert('Selecciona al menos un archivo para eliminar.');
        return;
    }
    if (!confirm(`¿Seguro que deseas eliminar ${checks.length} archivo${checks.length !== 1 ? 's' : ''}?`)) return;

    const archivos = Array.from(checks).map(cb => cb.value);

    const formData = new FormData();
    archivos.forEach(a => formData.append('archivos[]', a));

    fetch('/template/HSEQ/eliminar_investigacion.php', { // Debes crear este PHP
        method: 'POST',
        body: formData
    })
    .then(res => res.text())
    .then(resp => {
        alert(resp.replace(/\\n/g, '\n'));
        location.reload();
    })
    .catch(err => {
        alert('Error al eliminar archivos: ' + err.message);
        console.error('Error:', err);
    });
};
    </script>
</body>
</html>