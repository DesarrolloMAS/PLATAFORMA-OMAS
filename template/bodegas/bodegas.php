<?php
// Capturar el nombre del archivo de la bodega desde la URL
$bodegaSeleccionada = isset($_GET['bodega']) ? $_GET['bodega'] : '';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inspección de Bodegas</title>
    <link rel="stylesheet" href="/css/bodegains.css">
</head>
<body class="cuerpo">

    <h1 class="titulo_principal">INSPECCIÓN - <?php echo htmlspecialchars($bodegaSeleccionada); ?></h1>

    <div class="contenedor-formulario">
        <form action="bodegasave.php" method="post">
            <input type="hidden" name="bodega" value="<?php echo htmlspecialchars($bodegaSeleccionada); ?>">

            <h2 class="titulo-seccion">Verificación de Condiciones</h2>
            <table class="tabla-inspeccion">
                <thead>
                    <tr>
                        <th>Pregunta</th>
                        <th>SI</th>
                        <th>NO</th>
                        <th>N/A</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>Registro de temperatura y humedad del lugar</td><td><input type="radio" name="opcion1" value="SI" required></td><td><input type="radio" name="opcion1" value="NO"></td><td><input type="radio" name="opcion1" value="N/A"></td></tr>
                    <tr><td>Pisos y paredes limpios, libres de derrames</td><td><input type="radio" name="opcion2" value="SI" required></td><td><input type="radio" name="opcion2" value="NO"></td><td><input type="radio" name="opcion2" value="N/A"></td></tr>
                    <tr><td>Bodega libre de insectos, roedores y palomas</td><td><input type="radio" name="opcion3" value="SI" required></td><td><input type="radio" name="opcion3" value="NO"></td><td><input type="radio" name="opcion3" value="N/A"></td></tr>
                    <tr><td>Vías de tránsito en bodega libres de obstáculos</td><td><input type="radio" name="opcion4" value="SI" required></td><td><input type="radio" name="opcion4" value="NO"></td><td><input type="radio" name="opcion4" value="N/A"></td></tr>
                    <tr><td>Productos almacenados debidamente ordenados</td><td><input type="radio" name="opcion5" value="SI" required></td><td><input type="radio" name="opcion5" value="NO"></td><td><input type="radio" name="opcion5" value="N/A"></td></tr>
                    <tr><td>Estibas y estantes limpios y en buen estado</td><td><input type="radio" name="opcion6" value="SI" required></td><td><input type="radio" name="opcion6" value="NO"></td><td><input type="radio" name="opcion6" value="N/A"></td></tr>
                    <tr><td>Materiales almacenados a mínimo 15cm del suelo</td><td><input type="radio" name="opcion7" value="SI" required></td><td><input type="radio" name="opcion7" value="NO"></td><td><input type="radio" name="opcion7" value="N/A"></td></tr>
                    <tr><td>Ventilación adecuada</td><td><input type="radio" name="opcion8" value="SI" required></td><td><input type="radio" name="opcion8" value="NO"></td><td><input type="radio" name="opcion8" value="N/A"></td></tr>
                    <tr><td>Iluminación adecuada</td><td><input type="radio" name="opcion9" value="SI" required></td><td><input type="radio" name="opcion9" value="NO"></td><td><input type="radio" name="opcion9" value="N/A"></td></tr>
                    <tr><td>Elementos almacenados protegidos de la luz solar</td><td><input type="radio" name="opcion10" value="SI" required></td><td><input type="radio" name="opcion10" value="NO"></td><td><input type="radio" name="opcion10" value="N/A"></td></tr>
                    <tr><td>Ausencia de productos no conformes</td><td><input type="radio" name="opcion11" value="SI" required></td><td><input type="radio" name="opcion11" value="NO"></td><td><input type="radio" name="opcion11" value="N/A"></td></tr>
                    <tr><td>Productos no conformes aislados</td><td><input type="radio" name="opcion12" value="SI" required></td><td><input type="radio" name="opcion12" value="NO"></td><td><input type="radio" name="opcion12" value="N/A"></td></tr>
                    <tr><td>Insumos alérgenos aislados</td><td><input type="radio" name="opcion13" value="SI" required></td><td><input type="radio" name="opcion13" value="NO"></td><td><input type="radio" name="opcion13" value="N/A"></td></tr>
                    <tr><td>Pisos y superficies libres de residuos</td><td><input type="radio" name="opcion14" value="SI" required></td><td><input type="radio" name="opcion14" value="NO"></td><td><input type="radio" name="opcion14" value="N/A"></td></tr>
                </tbody>
            </table>

            <h2 class="titulo-seccion">Hallazgos</h2>
            <div class="campo">
                <label for="diah">Día del hallazgo:</label>
                <input type="date" name="diah">
            </div>
            <div class="campo">
                <label for="hallazgo">Hallazgo:</label>
                <input type="text" name="hallazgo">
            </div>
            <div class="campo">
                <label for="plan">Plan de Acción:</label>
                <input type="text" name="plan">
            </div>
            <div class="campo">
                <label for="diaacc">Fecha de Acción:</label>
                <input type="date" name="diaacc">
            </div>
            <div class="campo">
                <label for="diaacc">Resultado Esperado:</label>
                <input type="text" name="result">
            </div>
            <button type="submit" class="boton-enviar">Guardar Inspección</button>
            
        </form>
    </div>

</body>
</html>

