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
        <div class="contenedor-flex">
            <div class="bloque-control">
                <div class="contenedor_horario">
                    <label for="fecha">Fecha</label>
                    <input type="date" id="fecha" name="fecha" require>
                    <label for="hora">Hora</label>
                    <input type="time" id="hora" name="hora" require>
                </div>
                <div class="contenedor_lote">
                    <label for="lote">Lote</label>
                    <input type="text" id="lote" name="lote">
                </div>
                <div>
                    <label for="pulso">Pulso de Dosificacion</label>
                    <input type="text" id="pulso" name="pulso">
                </div>
                <div>
                    <label for="unidades">Numero de Unidades (Minimo 10)</label>
                    <input type="text" id="unidades" name="unidades" required>
                </div>
                <div>
                    <label for="peso_total">Peso Total PT</label>
                    <input type="text" id="peso_total" name="peso_total">
                </div>
                <div>
                    <label for="peso_unitario">Peso Unitario Promedio (g) PU = PT/N</label>
                    <input type="text" id="peso_unitario" name="peso_unitario">
                </div>
                <div>
                    <label for="peso_presentacion">Peso de Presentación (Kg)</label>
                    <input type="text" id="peso_presentacion" name="peso_presentacion">
                </div>
                <div>
                    <label for="responsable">Responsable</label>
                    <input type="text" id="responsable" name="responsable">
                </div>
                <div>
                    <label for="cumplimiento">¿El peso cumple?</label>
                    <select id="cumplimiento" name="cumplimiento">
                        <option value="si">Sí</option>
                        <option value="no">No</option>
                    </select>
                </div>
            </div>
        </div>
        <div>
            <button type="submit">Guardar</button>
        </div>
    </form>
</body>
</html>
</body>
</html>