<?php
session_start(); 
$datos = null;
if (isset($_SESSION['precargar_formulario']) && $_SESSION['precargar_formulario'] === true) {
  $datos = $_SESSION['formulario_cargado'] ?? null;
  
  // Ya no se vuelve a precargar después de esto
  unset($_SESSION['formulario_cargado']);
  unset($_SESSION['precargar_formulario']);
}
?>
<script>
const datosPrecargados = <?= json_encode($datos, JSON_UNESCAPED_UNICODE) ?>;

window.addEventListener('DOMContentLoaded', () => {
    for (const [campo, valor] of Object.entries(datosPrecargados)) {
        const input = document.querySelector(`[name="${campo}"]`);
        if (input) {
            if (input.tagName === "SELECT" || input.tagName === "TEXTAREA") {
                input.value = valor;
            } else if (input.type === "checkbox" || input.type === "radio") {
                input.checked = valor == input.value;
            } else {
                input.value = valor;
            }
        }
    }
});
</script>
<?php

// Obtener los datos desde la URL
$codigo_maquina = isset($_GET['codigo']) ? $_GET['codigo'] : "";
$nombre_maquina = isset($_GET['maquina']) ? $_GET['maquina'] : "";

// Evitar mezcla de datos entre formatos diferentes
if (!empty($codigo_maquina)) {
    $_SESSION['codigo_maquina'] = $codigo_maquina;
    $_SESSION['nombre_maquina'] = $nombre_maquina;
} else {
    // Si no hay código en la URL, verificar si hay una sesión activa
    $codigo_maquina = isset($_SESSION['codigo_maquina']) ? $_SESSION['codigo_maquina'] : "";
    $nombre_maquina = isset($_SESSION['nombre_maquina']) ? $_SESSION['nombre_maquina'] : "";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/maquinas.css">
    <title>Document</title>
</head>
<body>
    <h1>Otros Equipos</h1>
            <p><strong>Código de Máquina:</strong> <?php echo htmlspecialchars($codigo_maquina); ?></p>
    <div>
        <h5>(Es una imagen generica no representa la maquina a la que se esta verificando en cuestion...)</h5>
        <img src="/fmt/img/MAQUINAS/Otros Equipos de Medicion/equipo_bog.jpeg" alt="">
        <h4></h4>
    </div>

    <div>
        <form action="maquinasave.php" method="POST">
        <input type="hidden" name="codigo_maquina" value="<?php echo htmlspecialchars($codigo_maquina); ?>">
        <input type="hidden" name="nombre_maquina" value="<?php echo htmlspecialchars($nombre_maquina); ?>">
        <input type="hidden" name="zona" value="equipos">
        <input type="hidden" name="tipo_maquina" value="equipos">
        <input type="hidden" name="formato" value="equipos_zs"> <!-- o equipos_zs -->
            <h2>Verificacion de Estado y Funcionamiento</h2>
            <!-- Campo oculto para enviar el código de la máquina -->

            <div>
                <label for="estado_general">Estado general de la maquina</label>
                <input id="estado_general" name="estado_general" type="text" placeholder="Indique el estado general de la maquina">
            </div>

            <div>
                <label for="Estado de limpieza">Estado de limpieza</label>
                <select name="Estado de limpieza" id="Estado de limpieza">
                <option value="Cumple">Cumple</option>
                    <option value="No cumple">No cumple</option>
                    <option value="N/A">N/A</option>
                </select>
            </div>

            <div>
                <label for="Conexion USB">Conexion USB</label>
                <select name="Conexion USB" id="Estabilidad">
                <option value="Cumple">Cumple</option>
                    <option value="No cumple">No cumple</option>
                    <option value="N/A">N/A</option>
                </select>
            </div>
            <div>
                <label for="Pulsador">Pulsador</label>
                <select name="Pulsador" id="escala">
                <option value="Cumple">Cumple</option>
                    <option value="No cumple">No cumple</option>
                    <option value="N/A">N/A</option>
                </select>
            </div>
            <div>
                <label for="Celda de Carga">Cargador</label>
                <select name="cargador" id="cargador">
                <option value="Cumple">Cumple</option>
                    <option value="No cumple">No cumple</option>
                    <option value="N/A">N/A</option>
                </select>
            </div>
            <div>
                <label for="Display">Estado del Display</label>
                <select name="Display" id="Display">
                <option value="Cumple">Cumple</option>
                    <option value="No cumple">No cumple</option>
                    <option value="N/A">N/A</option>
                </select>
            </div>
            <div>
                <label for="Bateria">Bateria</label>
                <select name="Bateria" id="Cables de señal">
                <option value="Cumple">Cumple</option>
                    <option value="No cumple">No cumple</option>
                    <option value="N/A">N/A</option>
                </select>
            </div>
            <div>
                <label for="Recipiente">Recipiente</label>
                <select name="Recipiente" id="Stickers Mant y Calibracion">
                    <option value="Cumple">Cumple</option>
                    <option value="No cumple">No cumple</option>
                    <option value="N/A">N/A</option>
                </select>
                <div>
                <label for="Limpieza Filtro">Limpieza Filtro</label>
                <select name="Limpieza Filtro" id="Stickers Mant y Calibracion">
                    <option value="Cumple">Cumple</option>
                    <option value="No cumple">No cumple</option>
                    <option value="N/A">N/A</option>
                </select>
            </div>
            <div>
                <label for="Verificacion De Estado">Cumple la verificacion de estado?</label>
                <select name="Verificacion De Estado" id="Verificacion De Estado">
                    <option value="No">No</option>
                    <option value="Si">Si</option>
                </select>
            </div>
            <div>
                <label for="Observaciones">Observaciones</label>
                <textarea name="Observaciones" id="Observaciones"></textarea>
            </div>
        </div>
        <input type="submit" value="Generar Reporte PDF">
      <input type="hidden" name="formato" value="verificacion_equipos_ZS">
      <button name="accion" value="guardar">💾 Guardar</button>
        </form>
    </div>  
</body>
</html>
<script>
    document.addEventListener("DOMContentLoaded", function() {
    let codigoMaquina = "<?php echo htmlspecialchars($codigo_maquina); ?>";
    let nombreMaquina = "<?php echo htmlspecialchars($nombre_maquina); ?>";

    if (codigoMaquina) {
        localStorage.setItem("codigo_maquina", codigoMaquina);
        localStorage.setItem("nombre_maquina", nombreMaquina);
    } else {
        let storedCodigo = localStorage.getItem("codigo_maquina");
        let storedNombre = localStorage.getItem("nombre_maquina");

        if (storedCodigo) {
            document.querySelector('input[name="codigo_maquina"]').value = storedCodigo;
        }
        if (storedNombre) {
            document.querySelector('input[name="nombre_maquina"]').value = storedNombre;
        }
    }

    document.querySelector("form").addEventListener("submit", function() {
        localStorage.clear();
    });
});
</script>
