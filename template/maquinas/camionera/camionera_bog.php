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
    <h1>Bascula Camionera</h1>
            <p><strong>Código de Máquina:</strong> <?php echo htmlspecialchars($codigo_maquina); ?></p>
    <div>
        <h5>(Es una imagen generica no representa la maquina a la que se esta verificando en cuestion...)</h5>
        <img src="/img/MAQUINAS/Camionera/Camionera_Bog.jpeg" alt="">
        <h4></h4>
    </div>

    <div>
        <form action="maquinasave.php" method="POST">
        <input type="hidden" name="codigo_maquina" value="<?php echo htmlspecialchars($codigo_maquina); ?>">
        <input type="hidden" name="nombre_maquina" value="<?php echo htmlspecialchars($nombre_maquina); ?>">
        <input type="hidden" name="tipo_maquina" value="camionera">
            <h2>Verificacion de Estado y Funcionamiento</h2>
            <!-- Campo oculto para enviar el código de la máquina -->

            <div>
                <label for="estado_general">Estado general de la maquina</label>
                <input id="estado_general" name="estado_general" type="text" placeholder="Indique el estado general de la maquina">
            </div>

            <div>
                <label for="Superficie Libre de Huecos">Superficie Libre de Huecos</label>
                <select name="Estado de limpieza" id="Estado de limpieza">
                    <option value="Cumple">Cumple</option>
                    <option value="No cumple">No cumple</option>
                </select>
            </div>

            <div>
                <label for="Bordes libres de Obstrucciones">Bordes libres de Obstrucciones</label>
                <select name="Bordes libres de Obstrucciones" id="Bordes libres de Obstrucciones">
                    <option value="Cumple">Cumple</option>
                    <option value="No cumple">No cumple</option>
                </select>
            </div>
            <div>
                <label for="Topes con Holgura">Topes con Holgura</label>
                <select name="Topes con Holgura" id="Topes con Holgura">
                    <option value="Cumple">Cumple</option>
                    <option value="No cumple">No cumple</option>
                </select>
            </div>
            <div>
                <label for="Tapas de Acceso">Tapas de Acceso</label>
                <select name="cargador" id="cargador">
                <option value="Cumple">Cumple</option>
                <option value="No cumple">No cumple</option>
                </select>
            </div>
            <div>
                <label for="Tarjeta Sumatoria libre de Humedad">Tarjeta Sumatoria libre de Humedad</label>
                <select name="Tarjeta Sumatoria libre de Humedad" id="Tarjeta Sumatoria libre de Humedad">
                    <option value="Cumple">Cumple</option>
                    <option value="No Cumple">No Cumple</option>
                </select>
            </div>
            <div>
                <label for="Cables de señal">Cables de señal</label>
                <select name="Cables de señal" id="Cables de señal">
                <option value="Cumple">Cumple</option>
                <option value="No cumple">No cumple</option>
                </select>
            </div>
            <div>
                <label for="Tornilleria Ajustada">Tornilleria Ajustada</label>
                <select name="Tornilleria Ajustada" id="Tornilleria Ajustada">
                <option value="Cumple">Cumple</option>
                <option value="No cumple">No cumple</option>
                </select>
            </div>
            <div>
                <label for="Cojinetes de celda sin desgaste">Cojinetes de celda sin desgaste</label>
                <select name="Cojinetes de celda sin desgaste" id="Cojinetes de celda sin desgaste">
                <option value="Cumple">Cumple</option>
                <option value="No cumple">No cumple</option>
                </select>
            </div>
            <div>
                <label for="Carcamo Limpio">Carcamo Limpio</label>
                <select name="Carcamo Limpio" id="Carcamo Limpio">
                    <option value="Cumple">Cumple</option>
                    <option value="No Cumple">No Cumple</option>
                </select>
            </div>
        <div>
            <h2>Verificacion de Calibracion</h2>
            <h3>Pesas Patron</h3>
            <div class="celda-container">
                <label for="Peso Utilizado KG">Peso Utilizado KG</label>
                <input type="number" name="Patron Utilizado" placeholder="Inserte Valor en Kg">
            </div>
            <div class="celda-container">
                <label for="Celda 1">Celda 1</label>
                <input type="text" name="Celda 1" placeholder="Indicador de peso">
                <input type="text" name="Diferencia 1" placeholder="Diferencia">
            </div>
            <div class="celda-container">
                <label for="Celda 2">Celda 2</label>
                <input type="text" name="Celda 2" placeholder="Indicador de peso">
                <input type="text" name="Diferencia 2" placeholder="Diferencia">
            </div>
            <div class="celda-container">
                <label for="Celda 3">Celda 3</label>
                <input type="text" name="Celda 3" placeholder="Indicador de peso">
                <input type="text" name="Diferencia 3" placeholder="Diferencia">
            </div>
            <div class="celda-container">
                <label for="Celda 4">Celda 4</label>
                <input type="text" name="Celda 4" placeholder="Indicador de peso">
                <input type="text" name="Diferencia 4" placeholder="Diferencia">
            </div>
            <div class="celda-container">
                <label for="Celda 5">Celda 5</label>
                <input type="text" name="Celda 5" placeholder="Indicador de peso">
                <input type="text" name="Diferencia 5" placeholder="Diferencia">
            </div>
            <div class="celda-container">
                <label for="Celda 6">Celda 6</label>
                <input type="text" name="Celda 6" placeholder="Indicador de peso">
                <input type="text" name="Diferencia 6" placeholder="Diferencia">
            </div>
            <div class="celda-container">
                <label for="Celda 7">Celda 7</label>
                <input type="text" name="Celda 7" placeholder="Indicador de peso">
                <input type="text" name="Diferencia 7" placeholder="Diferencia">
            </div>
            <div class="celda-container" >
                <label for="Celda 8">Celda 8</label>
                <input type="text" name="Celda 8" placeholder="Indicador de peso">
                <input type="text" name="Diferencia 8" placeholder="Diferencia">
            </div>
                <label for="Verificacion De MASAS">Cumple la Tolerancia?</label>
                <select name="Verificacion De MASAS" id="Verificacion De MASAS">
                    <option value="No">No</option>
                    <option value="Si">Si</option>
                </select>
            </div>
            <h3>Vehiculo</h3>
            <div class="celda-container">
                <label for="Peso Utilizado Vehiculo">Peso Utilizado Vehiculo</label>
                <input type="text" id="Peso Utilizado Vehiculo" name="Peso Utilizado Vehiculo" placeholder="Peso Utilizado Kg">
            </div>
            <div class="celda-container">
                <label for="1 (Frente)">1 (Frente)</label>
                <input type="text" name="1 (Frente)" placeholder="Indicador de peso">
                <input type="text" name="Diferencia V1" placeholder="Diferencia">
            </div>
            <div class="celda-container">
                <label for="2 (Centro)">2 (Centro)</label>
                <input type="text" name="Celda 2" placeholder="Indicador de peso">
                <input type="text" name="Diferencia V2" placeholder="Diferencia">
            </div>
            <div class="celda-container">
                <label for="3 (Atras)">3 (Atras)</label>
                <input type="text" name="3 (Atras)" placeholder="Indicador de peso">
                <input type="text" name="Difrenecia V3" placeholder="Diferencia">
            </div>
            <div class="celda-container">
                <label for="4 (Centro)">4 (Centro)</label>
                <input type="text" name="4 (Centro)" placeholder="Indicador de peso">
                <input type="text" name="Diferencia V4" placeholder="Diferencia">
            </div>
            <div>
                <label for="cumplimiento vehiculo">Cumple La Tolerancia?</label>
                <select name="cumplimiento vehiculo" id="cumplimiento vehiculo">
                    <option value="Si">Si</option>
                    <option value="No">No</option>
                </select>
            </div>
            <div>
                <label for="Observaciones">Observaciones</label>
                <textarea name="Observaciones" id="Observaciones"></textarea>
            </div>
        </div>
        <input type="submit" value="Generar Reporte PDF">
        <input type="hidden" name="formato" value="verificacion_camionera_ZC">
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
