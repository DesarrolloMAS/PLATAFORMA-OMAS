<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700&family=Space+Mono:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/maquinas_v2.css">
    <title>Máquinas V2 · Mantenimiento</title>
</head>
<body>
    <div class="container">
        <div class="header-box">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
                <div>
                    <h1 class="main-title">⚙️ Verificación de Máquinas</h1>
                    <p class="sub-title">Catálogo de equipos por zona · Bogotá / Pasto</p>
                    <div class="badge-mantenimiento">🔧 Área de Mantenimiento · Mecánicos</div>
                </div>
                <div style="display:flex; gap:10px; flex-wrap:wrap;">
                    <a class="btn-back" href="revision_maquinas.php">📁 Revisión</a>
                    <a class="btn-back" href="../redireccion.php">← Volver</a>
                </div>
            </div>
        </div>

        <div id="menu-container"></div>

        <div class="system-status">
            <div class="status-dot"></div>
            SISTEMA JSON INTERCONECTADO — V2 MANTENIMIENTO
        </div>
    </div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    Promise.all([
        fetch("maquinas_galeria.json").then(r => r.json()),
        fetch("rastreo.php").then(r => r.json())
    ]).then(([zonas, rastreo]) => {
        const menuContainer = document.getElementById("menu-container");

        for (const zona in zonas) {
            const zonaId = zona.replace(/\s+/g, "_");

            const zonaWrap = document.createElement("div");
            zonaWrap.classList.add("zona-container");
            zonaWrap.innerHTML = `
                <div class="zona-header" onclick="menuDesplegable('${zonaId}', this)">
                    <span><span class="zona-icon">▶</span> ⚙️ ${zona.toUpperCase()}</span>
                </div>
                <div id="${zonaId}" class="zona-contenido"></div>
            `;
            menuContainer.appendChild(zonaWrap);
            const zonaDiv = zonaWrap.querySelector(`#${CSS.escape(zonaId)}`);

            for (const grupo in zonas[zona]) {
                const codigos = zonas[zona][grupo];

                const grupoDiv = document.createElement("div");
                grupoDiv.classList.add("grupo-maquina");

                const imagenSrc = `/fmt/img/MAQUINAS/${zona.replace(/\s+/g, "_")}/${grupo.replace(/\s+/g, "_")}.jpeg`;

                grupoDiv.innerHTML = `
                    <div class="grupo-maquina-header">
                        <img src="${imagenSrc}" alt="${grupo}" onerror="this.onerror=null; this.src='/fmt/img/default.png';">
                        <span class="grupo-maquina-nombre">${grupo}</span>
                    </div>
                    <div class="codigos-container"></div>
                `;

                const codigosContainer = grupoDiv.querySelector(".codigos-container");

                codigos.forEach(codigo => {
                    const codigoKey = codigo.toUpperCase().replace(/-/g, "_");
                    let fecha = null;
                    const rastreoZona = rastreo[zona];

                    if (rastreoZona) {
                        for (const key in rastreoZona) {
                            if (key.toUpperCase().replace(/-/g, "_") === codigoKey) {
                                const encontrado = (rastreoZona[key].codigos || []).find(
                                    c => c.codigo.toUpperCase().replace(/-/g, "_") === codigoKey
                                );
                                if (encontrado) fecha = encontrado.ultima_verificacion;
                                break;
                            }
                        }
                    }

                    const clase = fecha ? "verificada" : "no-verificada";
                    const textoFecha = fecha ? `Última verificación: ${fecha}` : "Sin verificación";
                    const urlFormato = `formulario.html?tipo=${encodeURIComponent(zona)}&codigo=${encodeURIComponent(codigo)}&maquina=${encodeURIComponent(grupo)}`;

                    const link = document.createElement("a");
                    link.href = urlFormato;
                    link.classList.add("codigo-link", clase);
                    link.innerHTML = `<span>${fecha ? "✅" : "🛠️"} ${codigo}</span><span class="codigo-fecha">${textoFecha}</span>`;

                    codigosContainer.appendChild(link);
                });

                zonaDiv.appendChild(grupoDiv);
            }
        }
    }).catch(err => {
        document.getElementById("menu-container").innerHTML =
            `<p style="color:var(--danger)">❌ Error al cargar el catálogo de máquinas: ${err.message}</p>`;
        console.error(err);
    });
});

function menuDesplegable(zonaId, headerEl) {
    const menu = document.getElementById(zonaId);
    if (!menu) return;
    const visible = menu.classList.toggle("visible");
    headerEl.classList.toggle("activa", visible);
}
</script>
</body>
</html>
