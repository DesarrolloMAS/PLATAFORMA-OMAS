<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/control_formulario.css">
    <title>CONTROL DE CANTIDAD-PRODUCTO EN BULTO</title>
</head>
<body>
    <form action="procesamiento_control.php" method="post" class="formulario-control">
        <h1>Control de Cantidad/Producto en Bulto</h1>
        <input type="hidden" name="harina" value="<?php echo isset($_GET['harina']) ? htmlspecialchars($_GET['harina']) : ''; ?>">
        <input type="hidden" name="peso_producto" value="<?php echo isset($_GET['peso']) ? htmlspecialchars($_GET['peso']) : ''; ?>">
        <div class="contenedor-flex">
            <div class="bloque-control">
                <div class="contenedor_horario">
                    <label for="fecha">Fecha</label>
                    <input type="date" id="fecha" name="fecha" required>
                    <label for="hora">Hora</label>
                    <input type="time" id="hora" name="hora" required>
                </div>
                <div class="contenedor_lote">
                    <label for="lote">Lote</label>
                    <input type="text" id="lote" name="lote">
                </div>
                <div>
                    <label for="responsable">Responsable</label>
                    <input type="text" id="responsable" name="responsable">
                </div>
                <div>
                    <label for="peso_producto_display">Peso del Producto (kg)</label>
                    <input type="text" id="peso_producto_display" value="<?php echo isset($_GET['peso']) ? htmlspecialchars($_GET['peso']) : ''; ?>" readonly>
                </div>
            </div>
            <div class="bloque-muestreo">
                <h2>Muestreo de Producto</h2>
                <div class="bultos-grid">
                    <label for="bulto_1">Bulto 1</label>
                    <input type="number" step="any" id="bulto_1" name="bulto_1">
                    <label for="bulto_2">Bulto 2</label>
                    <input type="number" step="any" id="bulto_2" name="bulto_2">
                    <label for="bulto_3">Bulto 3</label>
                    <input type="number" step="any" id="bulto_3" name="bulto_3">
                    <label for="bulto_4">Bulto 4</label>
                    <input type="number" step="any" id="bulto_4" name="bulto_4">
                    <label for="bulto_5">Bulto 5</label>
                    <input type="number" step="any" id="bulto_5" name="bulto_5">
                    <label for="bulto_6">Bulto 6</label>
                    <input type="number" step="any" id="bulto_6" name="bulto_6">
                    <label for="bulto_7">Bulto 7</label>
                    <input type="number" step="any" id="bulto_7" name="bulto_7">
                    <label for="bulto_8">Bulto 8</label>
                    <input type="number" step="any" id="bulto_8" name="bulto_8">
                    <label for="bulto_9">Bulto 9</label>
                    <input type="number" step="any" id="bulto_9" name="bulto_9">
                    <label for="bulto_10">Bulto 10</label>
                    <input type="number" step="any" id="bulto_10" name="bulto_10">
                </div>
            </div>
        </div>
        <div>
            <button type="submit">Guardar</button>
        </div>
    </form>
</body>
</html>