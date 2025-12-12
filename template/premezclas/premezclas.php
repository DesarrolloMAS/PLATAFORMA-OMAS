<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <link rel="stylesheet" href="/css/premezclas.css">
  <title>Control Producción Premezclas</title>
</head>
<body>

<h1>CONTROL DE PRODUCCIÓN - PREMEZCLAS Y HARINAS ESPECIALES</h1>
<div>
  <a href="../menu_adm.html">VOLVER</a>
</div>

<form action="premezclas_save.php" method="POST">

  <!-- Harinas Especiales -->
  <h2>HARINAS ESPECIALES</h2>
  <div id="harinasContainer">
  <div class="bloque-harina">
  <div class="form-group">
    <label>Producto:</label>
    <input type="text" name="harina[]">
  </div>

  <div class="form-group">
    <label>Lote:</label>
    <input type="text" name="harina_lote[]">
  </div>

  <div class="form-group">
    <label>Cantidad:</label>
    <input type="number" name="harina_cantidad[]">
  </div>

  <div class="form-group">
    <label>Operario:</label>
    <input type="text" name="operarios[]">
  </div>

  <div class="form-group">
    <label>Hora Inicio:</label>
    <input type="time" name="harina_inicio[]">
  </div>

  <div class="form-group">
    <label>Hora Final:</label>
    <input type="time" name="harina_final[]">
  </div>

  <div class="form-group full-width">
  </div>
</div>


      <button type="button" onclick="agregarHarina()">➕ Agregar Harina</button>
    </div>
  </div>

  <hr>

  <!-- Insumos -->
  <h2>INSUMOS</h2>
  <table id="tablaInsumos">
    <thead>
      <tr>
        <th>Insumo</th>
        <th>Lote</th>
        <th>Cantidad</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <!-- 3 filas por defecto -->
      <tr class="fila-insumo">
        <td><input type="text" name="data[0][insumo]"></td>
        <td><input type="text" name="data[0][lote]"></td>
        <td><input type="text" name="data[0][cantidad]"></td>
        <td></td>
      </tr>
      <tr class="fila-insumo">
        <td><input type="text" name="data[1][insumo]"></td>
        <td><input type="text" name="data[1][lote]"></td>
        <td><input type="text" name="data[1][cantidad]"></td>
        <td></td>
      </tr>
      <tr class="fila-insumo">
        <td><input type="text" name="data[2][insumo]"></td>
        <td><input type="text" name="data[2][lote]"></td>
        <td><input type="text" name="data[2][cantidad]"></td>
        <td></td>
      </tr>
    </tbody>
  </table>
  <button type="button" onclick="agregarFila()">➕ Agregar Insumo</button>

  <hr>

  <label for="observaciones">Observaciones:</label>
  <textarea name="observaciones" id="observaciones" cols="50" rows="5"></textarea>
  <div class="contenedorfirma">
            <label class="labelfir" for="canvasfirma1">Firma Turno</label>
            <canvas class="campofirm" id="canvasfirma1" width="400" height="200" style="border: 1px solid #000;"></canvas>
            <button class="botonext" type="button" id="limpiarFirma1">Limpiar</button>
            <button class="botonext" type="button" id="guardarFirma1">Guardar Firma</button>
            <input type="hidden" name="firma_turn1" id="firma_turn1">
        </div> 
  <br><br>
  <input type="submit" value="Guardar Formulario">
</form>

<script>
  // HARINAS DINÁMICAS
  let contadorHarinas = 1;
  const maxHarinas = 6;

  function agregarHarina() {
    if (contadorHarinas >= maxHarinas) {
      alert("Máximo de 6 harinas especiales alcanzado.");
      return;
    }

    const container = document.getElementById("harinasContainer");
    const original = container.querySelector(".bloque-harina");
    const clon = original.cloneNode(true);

    // Limpiar valores del clon
    clon.querySelectorAll("input").forEach(input => input.value = "");

    // Remover botones anteriores
    clon.querySelectorAll("button").forEach(b => b.remove());

    // Botón para eliminar bloque
    const btnEliminar = document.createElement("button");
    btnEliminar.type = "button";
    btnEliminar.textContent = "🗑️ Eliminar";
    btnEliminar.onclick = function () {
      container.removeChild(clon);
      contadorHarinas--;
    };
    clon.appendChild(btnEliminar);

    container.appendChild(clon);
    contadorHarinas++;
  }

  // INSUMOS DINÁMICOS
  let contadorFilas = 3;
  const maxFilas = 30;

  function agregarFila() {
    if (contadorFilas >= maxFilas) {
      alert("Máximo de 30 insumos alcanzado.");
      return;
    }

    const tabla = document.getElementById("tablaInsumos").querySelector("tbody");
    const fila = document.createElement("tr");
    fila.classList.add("fila-insumo");

    fila.innerHTML = `
      <td><input type="text" name="data[${contadorFilas}][insumo]"></td>
      <td><input type="text" name="data[${contadorFilas}][lote]"></td>
      <td><input type="text" name="data[${contadorFilas}][cantidad]"></td>
      <td><button type="button" onclick="eliminarFila(this)">🗑️</button></td>
    `;

    tabla.appendChild(fila);
    contadorFilas++;
  }

  function eliminarFila(boton) {
    const fila = boton.closest("tr");
    fila.remove();
    contadorFilas--;
  }
  // Inicializar el canvas de forma dinámica
  function inicializarCanvas(idCanvas, idLimpiarBtn, idGuardarBtn, idInputHidden) {
    const canvas = document.getElementById(idCanvas);
    const ctx = canvas.getContext('2d');
    const limpiarBtn = document.getElementById(idLimpiarBtn);
    const guardarBtn = document.getElementById(idGuardarBtn);
    const inputHidden = document.getElementById(idInputHidden);

    let dibujando = false;

    // Ajustar dimensiones dinámicamente para que sea responsivo
    function ajustarTamañoCanvas() {
        const rect = canvas.getBoundingClientRect();
        canvas.width = rect.width;
        canvas.height = rect.height;
        ctx.lineWidth = 2; // Configurar grosor del pincel
        ctx.lineCap = 'round'; // Configurar extremos del trazo
        ctx.strokeStyle = 'black'; // Configurar color del trazo
    }
    ajustarTamañoCanvas();
    window.addEventListener('resize', ajustarTamañoCanvas);

    function obtenerPosicion(e) {
        const rect = canvas.getBoundingClientRect();
        if (e.touches) {
            return {
                x: e.touches[0].clientX - rect.left,
                y: e.touches[0].clientY - rect.top
            };
        } else {
            return {
                x: e.clientX - rect.left,
                y: e.clientY - rect.top
            };
        }
    }

    function iniciarDibujo(e) {
        dibujando = true;
        const pos = obtenerPosicion(e);
        ctx.beginPath();
        ctx.moveTo(pos.x, pos.y);
    }

    function detenerDibujo() {
        dibujando = false;
        ctx.beginPath();
    }

    function dibujar(e) {
        if (!dibujando) return;
        const pos = obtenerPosicion(e);
        ctx.lineTo(pos.x, pos.y);
        ctx.stroke();
    }
    function isCanvasBlank(canvas) {
    const blank = document.createElement('canvas');
    blank.width = canvas.width;
    blank.height = canvas.height;
    return canvas.toDataURL() === blank.toDataURL();
    }
    document.addEventListener("DOMContentLoaded", function () {
    let canvas = document.getElementById("canvasfirma1");
    let ctx = canvas.getContext("2d");
    let isDrawing = false;
    let firmaGuardada = false; // Para verificar si se firmó

    // Evento para detectar dibujo
    canvas.addEventListener("mousedown", function () {
        isDrawing = true;
    });
    canvas.addEventListener("mouseup", function () {
        isDrawing = false;
        firmaGuardada = true; // Marca como firmado
    });
    document.querySelector("form").addEventListener("submit", function (event) {
        if (document.getElementById("firma_turn1").value.trim() === "") {
            alert("La firma es obligatoria.");
            event.preventDefault(); // Evita el envío del formulario
        }
    });
});
    // Eventos para escritorio
    canvas.addEventListener('mousedown', iniciarDibujo);
    canvas.addEventListener('mouseup', detenerDibujo);
    canvas.addEventListener('mousemove', dibujar);

    // Eventos para dispositivos táctiles
    canvas.addEventListener('touchstart', iniciarDibujo);
    canvas.addEventListener('touchend', detenerDibujo);
    canvas.addEventListener('touchmove', (e) => {
        e.preventDefault(); // Evitar el scroll al dibujar
        dibujar(e);
    });

    // Botón para limpiar el canvas
    limpiarBtn.addEventListener('click', () => {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        inputHidden.value = ''; // Borra el contenido del campo oculto
    });

    // Botón para guardar la firma
    guardarBtn.addEventListener('click', () => {
        if (isCanvasBlank(canvas)) {
            alert('Por favor, realice una firma antes de guardar.');
        } else {
            const dataURL = canvas.toDataURL();
            inputHidden.value = dataURL; // Almacena la firma en el campo oculto
            alert('Firma guardada correctamente.');
            // Mantén la firma visible en el canvas
            const img = new Image();
            img.src = dataURL;
            img.onload = () => {
                ctx.clearRect(0, 0, canvas.width, canvas.height); // Limpia el canvas
                ctx.drawImage(img, 0, 0); // Vuelve a dibujar la firma
            };
        }
    });
}


    // Inicializar ambos canvas con sus botones y campos ocultos
    inicializarCanvas('canvasfirma1', 'limpiarFirma1', 'guardarFirma1', 'firma_turn1');
</script>

</body>
</html>
