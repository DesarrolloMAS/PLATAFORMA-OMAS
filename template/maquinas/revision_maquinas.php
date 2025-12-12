<?php
// Este archivo solo sirve el HTML y JS, la lógica de archivos la hace rastreo_doc.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Revisión de Máquinas</title>
    <link rel="stylesheet" href="/fmt/css/revision_maquinas.css">
    <style>
        .maquina {
            border-left: 3px solid #e0e0e0;
            padding-left: 12px;
            margin-bottom: 18px;
            background: #fff;
        }
        .maquina-bogota {
            border-left: 3px solid #003366 !important;
        }
        .maquina-pasto {
            border-left: 3px solid #c0392b !important;
        }
        .maquina-titulo {
            font-weight: bold;
            margin-bottom: 4px;
        }
        .pdf-item {
            margin-bottom: 6px;
        }
        .zona-header {
            cursor: pointer;
            font-weight: bold;
            font-size: 1.1em;
            margin-top: 20px;
            margin-bottom: 8px;
            color: #003366;
        }
        .zona-header:hover {
            text-decoration: underline;
        }
        .zona-contenido {
            margin-left: 20px;
            display: none;
        }
        .zona-contenido-visible {
            display: block;
        }
    </style>
</head>
<body>
    <h1>📁 Revisión de Máquinas</h1>
    <a class="volver" href="../redireccion.php">Volver</a>
    <div id="visor-pdfs"><p>Cargando archivos...</p></div>

    <script>
    fetch('/fmt/template/maquinas/rastreo_doc.php')
      .then(res => {
        if (!res.ok) throw new Error('No se pudo cargar el backend');
        return res.json();
      })
      .then(data => {
        const container = document.getElementById('visor-pdfs');
        container.innerHTML = '';

        if (!data || Object.keys(data).length === 0) {
          container.innerHTML = '<p>No hay archivos para mostrar.</p>';
          return;
        }

        Object.entries(data).forEach(([zona, maquinas]) => {
          const zonaDiv = document.createElement('div');

          const header = document.createElement('div');
          header.classList.add('zona-header');
          header.textContent = `🔹 ${zona}`;
          header.onclick = () => {
            contenido.classList.toggle('zona-contenido-visible');
            if (contenido.classList.contains('zona-contenido-visible')) {
              contenido.style.display = 'block';
            } else {
              contenido.style.display = 'none';
            }
          };

          const contenido = document.createElement('div');
          contenido.classList.add('zona-contenido');
          contenido.style.display = 'none';

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

              // Construye el link real según la estructura de carpetas
              const link = `/fmt/archivos/generados/verificaciones/${zona}/${maquina}/${pdf}`;
              pdfDiv.innerHTML = `
                <span style="font-size:1.1em;">📄</span> ${pdf}
                <a href="${link}" target="_blank" style="margin-left:10px;">📂 Ver</a>
                <button onclick="corregirPDF(
                  '${encodeURIComponent(zona)}',
                  '${encodeURIComponent(maquina)}',
                  '${encodeURIComponent(pdf)}',
                  '${encodeURIComponent(maquina)}',
                  '${encodeURIComponent(pdf.replace('.pdf','').split('_').slice(-1)[0])}'
                )" style="margin-left:10px;">✏️ Corregir</button>
              `;
              maquinaDiv.appendChild(pdfDiv);
            });

            contenido.appendChild(maquinaDiv);
          });

          zonaDiv.appendChild(header);
          zonaDiv.appendChild(contenido);
          container.appendChild(zonaDiv);
        });
      })
      .catch(err => {
        document.getElementById('visor-pdfs').innerHTML = `<p style="color:red;">Error al cargar los datos: ${err.message}</p>`;
        console.error("Error:", err);
      });

    function corregirPDF(zona, maquina, archivo, formato, fecha) {
      window.location.href = `formulario_correccion.php?zona=${zona}&maquina=${maquina}&archivo=${archivo}&formato=${formato}&fecha=${fecha}`;
    }
    </script>
</body>
</html>