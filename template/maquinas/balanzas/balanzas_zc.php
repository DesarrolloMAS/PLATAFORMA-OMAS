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

$codigo_maquina = isset($_GET['codigo']) ? $_GET['codigo'] : "";
$nombre_maquina = isset($_GET['maquina']) ? $_GET['maquina'] : "";

if (!empty($codigo_maquina)) {
    $_SESSION['codigo_maquina'] = $codigo_maquina;
    $_SESSION['nombre_maquina'] = $nombre_maquina;
} else {
    $codigo_maquina = isset($_SESSION['codigo_maquina']) ? $_SESSION['codigo_maquina'] : "";
    $nombre_maquina = isset($_SESSION['nombre_maquina']) ? $_SESSION['nombre_maquina'] : "";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="/css/maquinas.css">
  <title>Formulario Máquina</title>
</head>
<body>
  <h1>Basculas Electrónicas</h1>
  <p><strong>Código de Máquina:</strong> <?php echo htmlspecialchars($codigo_maquina); ?></p>

  <div>
    <h5>(Es una imagen genérica, no representa la máquina verificada)</h5>
    <img src="/img/MAQUINAS/Balanzas/Balanza_ZC.jpeg" alt="">
  </div>

  <div>
    <form action="maquinasave.php" method="POST">
      <input type="hidden" name="codigo_maquina" value="<?php echo htmlspecialchars($codigo_maquina); ?>">
      <input type="hidden" name="nombre_maquina" value="<?php echo htmlspecialchars($nombre_maquina); ?>">
      <input type="hidden" name="tipo_maquina" value="balanzas">

      <h2>Verificación de Estado y Funcionamiento</h2>

      <div>
        <label for="estado_general">Estado general de la máquina</label>
        <input id="estado_general" name="estado_general" type="text" placeholder="Indique el estado general de la máquina">
      </div>

      <div>
        <label for="estado_limpieza">Estado de limpieza</label>
        <select name="estado_limpieza" id="estado_limpieza">
          <option value="Cumple">Cumple</option>
          <option value="No Cumple">No Cumple</option>
        </select>
      </div>

      <div>
        <label for="nivelada">Nivelada</label>
        <select name="nivelada" id="nivelada">
          <option value="Cumple">Cumple</option>
          <option value="No Cumple">No Cumple</option>
        </select>
      </div>

      <div>
        <label for="escala">Escala de lectura en cero</label>
        <select name="escala" id="escala">
          <option value="Cumple">Cumple</option>
          <option value="No Cumple">No Cumple</option>
        </select>
      </div>

      <div>
        <label for="cargador">Cargador</label>
        <select name="cargador" id="cargador">
          <option value="Cumple">Cumple</option>
          <option value="No Cumple">No Cumple</option>
        </select>
      </div>

      <div>
        <label for="display">Estado del Display</label>
        <select name="display" id="display">
          <option value="Cumple">Cumple</option>
          <option value="No Cumple">No Cumple</option>
        </select>
      </div>

      <div>
        <label for="cables_senal">Cables de señal</label>
        <select name="cables_senal" id="cables_senal">
          <option value="Cumple">Cumple</option>
          <option value="No Cumple">No Cumple</option>
        </select>
      </div>

      <div>
        <label for="stickers">Stickers Mant. y Calibración</label>
        <select name="stickers" id="stickers">
          <option value="Cumple">Cumple</option>
          <option value="No Cumple">No Cumple</option>
          <option value="N/A">N/A</option>
        </select>
      </div>

      <div>
        <label for="verificacion_estado">¿Cumple verificación de estado?</label>
        <select name="verificacion_estado" id="verificacion_estado">
          <option value="No">No</option>
          <option value="Si">Sí</option>
        </select>
      </div>

      <h2>Verificación de Calibración</h2>
      <h4>(La diferencia entre la medición de los puntos de apoyo y el peso de la masa patrón debe ser de maximo  +/- una vez la escala de lectura de cada balanza)</h4>
      <div>
        <h3>Gráfico de Excentricidad</h3>
        <img src="/fmt/img/MAQUINAS/Bascula/P1.jpeg" alt="">
      </div>

      <div>
        <label for="peso_utilizado">Peso utilizado</label>
        <input type="text" id="peso_utilizado" name="peso_utilizado" placeholder="Ingrese valor en gramos">
      </div>

      <?php for ($i = 1; $i <= 6; $i++): ?>
      <div class="celda-container">
        <h4>P<?= $i ?></h4>
        <div class="calibracion-row">
          <input type="number" step="any" name="peso_indicador_p<?= $i ?>" placeholder="Peso Indicador">
          <input type="number" step="any" name="diferencia_p<?= $i ?>" placeholder="Diferencia">
        </div>
      </div>
      <?php endfor; ?>
      <div>
        <label for="verificacion_masas">¿Cumple la Tolerancia?</label>
        <select name="verificacion_masas" id="verificacion_masas">
          <option value="No">No</option>
          <option value="Si">Sí</option>
        </select>
      </div>

      <div>
        <label for="observaciones">Observaciones</label>
        <textarea name="observaciones" id="observaciones"></textarea>
      </div>
      <input type="submit" value="Generar Reporte PDF">
      <input type="hidden" name="formato" value="verificacion_balanzas_ZC">
      <button name="accion" value="guardar">💾 Guardar</button>
    </form>
  </div>

  

  <script>
    document.addEventListener("DOMContentLoaded", function () {
      const pesoUtilizadoInput = document.getElementById("peso_utilizado");

      function calcularDiferencia(pesoUtilizado, pesoIndicador) {
        return (pesoUtilizado - pesoIndicador).toFixed(2);
      }

      function actualizarDiferencias() {
        for (let i = 1; i <= 6; i++) {
          const pesoIndicador = document.querySelector(`input[name='peso_indicador_p${i}']`);
          const diferencia = document.querySelector(`input[name='diferencia_p${i}']`);

          if (pesoIndicador && diferencia) {
            pesoIndicador.addEventListener("input", () => {
              const pesoU = parseFloat(pesoUtilizadoInput.value);
              const pesoI = parseFloat(pesoIndicador.value);
              if (!isNaN(pesoU) && !isNaN(pesoI)) {
                diferencia.value = calcularDiferencia(pesoU, pesoI);
              }
            });

            pesoUtilizadoInput.addEventListener("input", () => {
              const pesoU = parseFloat(pesoUtilizadoInput.value);
              const pesoI = parseFloat(pesoIndicador.value);
              if (!isNaN(pesoU) && !isNaN(pesoI)) {
                diferencia.value = calcularDiferencia(pesoU, pesoI);
              }
            });
          }
        }
      }

      actualizarDiferencias();
    });
  </script>
</body>
</html>
