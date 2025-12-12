<?php
$harina = $_GET['harina'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../css/envasado.css">
    <title>LINEA DE ENVASADO</title>
</head>
<body>
    <form action="envasado_save.php" method="post">
    <h1>Comprobaciones en linea de envasado</h1>
    <input type="hidden" name="harina" value="<?php echo htmlspecialchars($_GET['harina'] ?? ''); ?>">
    <div>
    <h2>Recepcion empaque para envasado</h2>
    <label for="fecha">Fecha</label>
    <input type="date" name="fecha" id="fecha" required>
    <label for="hora">Hora</label>
    <input type="time" name="hora" id="hora" required>
    <h3>TIMBRADO</h3>
    <div>
    <label for="loteP">Lote de producto</label>
    <input type="text" name="loteP" id="loteP" required>
    <label for="">Fecha de Vencimiento</label>
    <input type="text" name="fechaVencimiento" id="fechaVencimiento" required>
    </div>
    <label for="responsable">Responsable</label>
    <input type="text" name="responsable" id="responsable" required>
    </div>
    <h2>ElEMENTOS A COMPROBAR</h2>
    <div>
        <div>
    <label for="purgada">¿La línea de envasado ha sido purgada cuando se realizó el cambio de producto?</label>
    <select name="purgada" id="purgada">
        <option value="">---</option>
        <option value="SI">SI</option>
        <option value="NO">NO</option>
        <option value="N/A">N/A</option>
    </select>
    </div>
    <div>
    <label for="Penvasado">¿La referencia del empaque que está en la linea de envasado corresponde a la del producto a envasar?</label>
    <select name="Penvasado" id="Penvasado">
        <option value="">---</option>
        <option value="SI">SI</option>
        <option value="NO">NO</option>
        <option value="N/A">N/A</option>
    </select>
    </div>
    <div>
    <label for="timbrado">¿El lote y fecha de vencimiento timbrado en los empaques es el correcto?</label>
    <select name="timbrado" id="timbrado">
        <option value="">---</option>
        <option value="SI">SI</option>
        <option value="NO">NO</option>
        <option value="N/A">N/A</option>
    </select>
    </div>
    <div>
    <label for="etiqueta">¿Si aplica etiqueta, los datos registrados coinciden con los timbrados en el empaque?</label>
    <select name="etiqueta" id="etiqueta">
        <option value="">---</option>
        <option value="SI">SI</option>
        <option value="NO">NO</option>
        <option value="N/A">N/A</option>
    </select>
    </div>
    <div>
    <label for="aprobacion">¿Se aprueba el uso de los empaques recibidos en la línea de envasado?</label>
    <select name="aprobacion" id="aprobacion">
        <option value="">---</option>
        <option value="SI">SI</option>
        <option value="NO">NO</option>
        <option value="N/A">N/A</option>
    </select>
    </div>
    <div>
        <label for="observaciones">Observaciones</label>
        <textarea name="observaciones" id="observaciones"></textarea>
    </div>
    </div>
    <input type="submit" value="Guardar">
    </form>
</body>
</html>