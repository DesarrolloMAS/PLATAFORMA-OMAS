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
    <link rel="stylesheet" href="/../css/maquinas_menu.css">
    <title>MENU MAQUINAS</title>
</head>
<body>
    <h1 class="titulo_principal">MAQUINAS DISPONIBLES</h1>
    <div><a class="volver" href="../redireccion.php">VOLVER</a></div>
    <div class="menu-container" id="menu-container">
        <!-- 📌 Aquí se generará el menú dinámicamente -->
    </div>
</body>
</html>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Cargar ambas fuentes en paralelo
    Promise.all([
        fetch("maquinas_galeria.json").then(r => r.json()),
        fetch("rastreo.php").then(r => r.json())
    ]).then(([zonas, rastreo]) => {
        let menuContainer = document.getElementById("menu-container");

        for (let zona in zonas) {
            let zonaId = zona.replace(/\s+/g, "_");

            let zonaLabel = document.createElement("div");
            zonaLabel.classList.add("zona-container");
            zonaLabel.innerHTML = `
                <label class="desplegable" onclick="menu_desplegable('${zonaId}')">
                    🔽 ${zona.toUpperCase()}
                </label>
                <div id="${zonaId}" class="menu-maquinas" style="display: none;">
                </div>
            `;

            menuContainer.appendChild(zonaLabel);
            let zonaDiv = document.getElementById(zonaId);

            for (let grupo in zonas[zona]) {
                let codigos = zonas[zona][grupo];
                let grupoId = grupo.replace(/\s+/g, "_");

                let maquinaDiv = document.createElement("div");
                maquinaDiv.classList.add("maquina");

                let imagenSrc = `/fmt/img/MAQUINAS/${zona.replace(/\s+/g, "_")}/${grupo.replace(/\s+/g, "_")}.jpeg`;

                maquinaDiv.innerHTML = `
                    <img src="${imagenSrc}" alt="${grupo}" onerror="this.onerror=null; this.src='/fmt/img/default.png';">
                    <p class="maquinaname">${grupo}</p>
                    <br>
                    <div class="codigos-container"></div>
                `;

                let codigosContainer = maquinaDiv.querySelector(".codigos-container");

                codigos.forEach(codigo => {
                    // Normaliza el código para buscarlo en rastreo (mayúsculas y guiones bajos)
                    let codigoKey = codigo.toUpperCase().replace(/-/g, "_");
                    let fecha = null;
                    let rastreoZona = rastreo[zona];
                    let rastreoCodigo = null;

                    if (rastreoZona) {
                        for (let key in rastreoZona) {
                            if (key.toUpperCase().replace(/-/g, "_") === codigoKey) {
                                rastreoCodigo = rastreoZona[key];
                                break;
                            }
                        }
                    }

                    if (rastreoCodigo && Array.isArray(rastreoCodigo.codigos)) {
                        let encontrado = rastreoCodigo.codigos.find(c => c.codigo.toUpperCase().replace(/-/g, "_") === codigoKey);
                        if (encontrado) fecha = encontrado.ultima_verificacion;
                    }

                    // Determinar clase visual
                    let clase = "no-verificada";
                    if (fecha) {
                        clase = "verificada";
                    }

                    let textoFecha = fecha ? `(Última verificación: ${fecha})` : "(Sin verificación)";
                    let formatoArchivo = grupo.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase().replace(/\s+/g, "_") + ".php";
                    let urlFormato = `/template/maquinas/${zona.replace(/\s+/g, "_")}/${formatoArchivo}?codigo=${encodeURIComponent(codigo)}&maquina=${encodeURIComponent(grupo)}`;
                    let codigoLink = document.createElement("a");
                    codigoLink.href = urlFormato;
                    codigoLink.classList.add("codigo-link", clase);
                    codigoLink.innerHTML = `${codigo} <span style="font-size:12px; font-weight:normal;">${textoFecha}</span>`;

                    codigosContainer.appendChild(codigoLink);
                    codigosContainer.appendChild(document.createElement("br"));
                });

                zonaDiv.appendChild(maquinaDiv);
            }
        }
    }).catch(error => console.error("❌ Error al cargar las verificaciones:", error));
});

// 📌 Función para desplegar los contenedores
function menu_desplegable(zonaId) {
    const menu = document.getElementById(zonaId);
    if (menu) {
        menu.style.display = (menu.style.display === "none" || menu.style.display === "") ? "block" : "none";
    }
}
</script>