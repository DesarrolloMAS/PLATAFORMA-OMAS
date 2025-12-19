<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revisión de Máquinas</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/revision_maquinas.css">
</head>
<body>
    <div class="header-container">
        <h1>
            <div class="header-icon">📁</div>
            Revisión de Máquinas
        </h1>
        <a class="volver" href="../redireccion.php">
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
            </div>
            <button id="eliminar-seleccionados">
                🗑️ Eliminar seleccionados
            </button>
        </div>

        <div id="visor-pdfs">
            <div class="loading-container">
                <div class="loading-spinner"></div>
                <p class="message">Cargando archivos...</p>
            </div>
        </div>
    </div>

    <script>
    let totalArchivos = 0;

    // Actualizar contador de seleccionados
    function actualizarContadorSeleccionados() {
        const checks = document.querySelectorAll('.pdf-checkbox:checked');
        document.getElementById('archivos-seleccionados').textContent = 
            `${checks.length} seleccionado${checks.length !== 1 ? 's' : ''}`;
    }

    // Cargar datos desde el backend
    fetch('/template/maquinas/rastreo_doc.php')
      .then(res => {
        if (!res.ok) throw new Error('No se pudo cargar el backend');
        return res.json();
      })
      .then(data => {
        const container = document.getElementById('visor-pdfs');
        container.innerHTML = '';

        if (!data || Object.keys(data).length === 0) {
          container.innerHTML = '<p class="message">No hay archivos para mostrar.</p>';
          return;
        }

        // Calcular total de archivos
        totalArchivos = 0;
        Object.values(data).forEach(maquinas => {
          Object.values(maquinas).forEach(info => {
            totalArchivos += info.pdfs.length;
          });
        });
        document.getElementById('total-archivos').textContent = 
          `${totalArchivos} archivo${totalArchivos !== 1 ? 's' : ''} total${totalArchivos !== 1 ? 'es' : ''}`;

        // Renderizar zonas y máquinas
        Object.entries(data).forEach(([zona, maquinas], index) => {
          const zonaCard = document.createElement('div');
          zonaCard.classList.add('zona-card');
          zonaCard.style.animationDelay = `${0.3 + index * 0.05}s`;

          const numMaquinas = Object.keys(maquinas).length;
          let numArchivos = 0;
          Object.values(maquinas).forEach(info => {
            numArchivos += info.pdfs.length;
          });

          const header = document.createElement('div');
          header.classList.add('zona-header');
          header.innerHTML = `
            <div class="zona-title">
              <span class="zona-icon">▶</span>
              <span>${zona}</span>
            </div>
            <span class="zona-badge">${numArchivos} archivo${numArchivos !== 1 ? 's' : ''}</span>
          `;

          const contenido = document.createElement('div');
          contenido.classList.add('zona-contenido');

          header.onclick = () => {
            header.classList.toggle('active');
            contenido.classList.toggle('zona-contenido-visible');
          };

          Object.entries(maquinas).forEach(([maquina, info]) => {
            const maquinaDiv = document.createElement('div');
            let colorClass = '';
            if (maquina.toLowerCase().includes('bog')) colorClass = 'maquina-bogota';
            else if (maquina.toLowerCase().includes('past')) colorClass = 'maquina-pasto';
            maquinaDiv.classList.add('maquina');
            if (colorClass) maquinaDiv.classList.add(colorClass);

            const titulo = document.createElement('div');
            titulo.className = 'maquina-titulo';
            titulo.textContent = maquina;
            maquinaDiv.appendChild(titulo);

            info.pdfs.forEach(pdf => {
              const pdfDiv = document.createElement('div');
              pdfDiv.classList.add('pdf-item');

              const link = `/archivos/generados/verificaciones/${zona}/${maquina}/${pdf}`;
              
              pdfDiv.innerHTML = `
                <input type="checkbox" class="pdf-checkbox" value="${link}">
                <span class="pdf-icon">📄</span>
                <span class="pdf-name">${pdf}</span>
                <div class="pdf-actions">
                  <a href="${link}" target="_blank" class="btn-action btn-ver">
                    📂 Ver
                  </a>
                  <button onclick="corregirPDF(
                    '${encodeURIComponent(zona)}',
                    '${encodeURIComponent(maquina)}',
                    '${encodeURIComponent(pdf)}',
                    '${encodeURIComponent(maquina)}',
                    '${encodeURIComponent(pdf.replace('.pdf','').split('_').slice(-1)[0])}'
                  )" class="btn-action btn-corregir">
                    ✏️ Corregir
                  </button>
                </div>
              `;

              const checkbox = pdfDiv.querySelector('.pdf-checkbox');
              checkbox.addEventListener('change', actualizarContadorSeleccionados);

              maquinaDiv.appendChild(pdfDiv);
            });

            contenido.appendChild(maquinaDiv);
          });

          zonaCard.appendChild(header);
          zonaCard.appendChild(contenido);
          container.appendChild(zonaCard);
        });
      })
      .catch(err => {
        document.getElementById('visor-pdfs').innerHTML = 
          `<p class="message error-message">Error al cargar los datos: ${err.message}</p>`;
        console.error("Error:", err);
      });

    // Función para redirigir al formulario de corrección
    function corregirPDF(zona, maquina, archivo, formato, fecha) {
      window.location.href = `formulario_correccion.php?zona=${zona}&maquina=${maquina}&archivo=${archivo}&formato=${formato}&fecha=${fecha}`;
    }

    // Eliminar archivos seleccionados
    document.getElementById('eliminar-seleccionados').onclick = function() {
      const checks = document.querySelectorAll('.pdf-checkbox:checked');
      if (checks.length === 0) {
        alert('Selecciona al menos un archivo para eliminar.');
        return;
      }
      if (!confirm(`¿Seguro que deseas eliminar ${checks.length} archivo${checks.length !== 1 ? 's' : ''}?`)) return;

      const archivos = Array.from(checks).map(cb => {
        const rutaCompleta = decodeURIComponent(cb.value);
        return rutaCompleta.replace('/archivos/generados/verificaciones/', '');
      });

      const formData = new FormData();
      archivos.forEach(a => formData.append('archivos[]', a));

      fetch('/template/eliminar_archivo_maquinas.php', {
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