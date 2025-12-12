<?php
session_start(); // Mantener información temporal por sesión

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
    <h1>Basculas Electronicas</h1>
            <p><strong>Código de Máquina:</strong> <?php echo htmlspecialchars($codigo_maquina); ?></p>
            <p><strong>Nombre de Máquina:</strong> <?php echo htmlspecialchars($nombre_maquina); ?></p>
    <div>
        <h5>(Es una imagen generica no representa la maquina a la que se esta verificando en cuestion...)</h5>
        <img src="/img/MAQUINAS/flowbalancer/flowbalancer_Pasto.jpeg" alt="">
        <h4></h4>
    </div>

    <div>
        <form action="maquinasave.php" method="POST">
        <input type="hidden" name="codigo_maquina" value="<?php echo htmlspecialchars($codigo_maquina); ?>">
        <input type="hidden" name="nombre_maquina" value="<?php echo htmlspecialchars($nombre_maquina); ?>">
        <input type="hidden" name="tipo_maquina" value="flowbalancer">
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
                </select>
            </div>

            <div>
                <label for="Estabilidad">Mesa y soportes estables</label>
                <select name="Estabilidad" id="Estabilidad">
                <option value="Cumple">Cumple</option>
                <option value="No cumple">No cumple</option>
                <option value="N/A">No Aplica</option>
                </select>
            </div>
            <div>
                <label for="escala">Escala de la lectura en cero</label>
                <select name="escala" id="escala">
                <option value="Cumple">Cumple</option>
                <option value="No cumple">No cumple</option>
                <option value="N/A">No Aplica</option>
                </select>
            </div>
            <div>
                <label for="Celda de Carga">Celda de Carga</label>
                <select name="cargador" id="cargador">
                <option value="Cumple">Cumple</option>
                <option value="No cumple">No cumple</option>
                <option value="N/A">No Aplica</option>
                </select>
            </div>
            <div>
                <label for="Display">Estado del Display</label>
                <select name="Display" id="Display">
                <option value="Cumple">Cumple</option>
                <option value="No cumple">No cumple</option>
                <option value="N/A">No Aplica</option>
                </select>
            </div>
            <div>
                <label for="Cables de señal">Cables de señal</label>
                <select name="Cables de señal" id="Cables de señal">
                <option value="Cumple">Cumple</option>
                <option value="No cumple">No cumple</option>
                <option value="N/A">No Aplica</option>
                </select>
            </div>
            <div>
                <label for="Membrana de Presion">Membrana de Presion</label>
                <select name="Membrana de Presion" id="Membrana de Presion">
                <option value="Cumple">Cumple</option>
                <option value="No cumple">No cumple</option>
                <option value="N/A">No Aplica</option>
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
            <h2>Masas Patron</h2>
            <div>
                <label for="Patron Utilizado">Patron utilizado en KG</label>
                <input type="number" name="Patron Utilizado" placeholder="Inserte Valor en Kg">
            </div>
            <div>
                <label for="Con masas patron WT">Con masas patron WT</label>
                <input type="number" name="Con masas patron WT">
            </div>
            <div>
                <label for="Sin masas patron WT">Sin masas patron WT</label>
                <input type="number" name="Sin masas patron WT">
            </div>
            <div>
                <label for="Con masas patron ZERO">Con masas patron ZERO</label>
                <input type="number" name="Con masas patron ZERO">
            </div>
            <div>
                <label for="Sin masas patron ZERO">Sin masas patron ZERO</label>
                <input type="number" name="Sin masas patron ZERO">
            </div>
            <div>
                <label for="Verificacion De MASAS">Cumple la verificacion de MASAS?</label>
                <select name="Verificacion De MASAS" id="Verificacion De MASAS">
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
      <input type="hidden" name="formato" value="verificacion_flowbalancer_ZS">
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
