<?php
require_once '../conection.php';
$filtro_cargo = 'Lider de Turno';
$query = $pdoUsuarios->prepare("SELECT id_usuario, nombre_u FROM usuarios WHERE Cargo = :filtro_cargo");
$query->bindParam(':filtro_cargo', $filtro_cargo);
$query->execute();
$usuarios = $query->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/purga.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Big+Shoulders:opsz,wght@10..72,100..900&display=swap" rel="stylesheet">
    <title>Purga de Proceso</title>
</head>
<body>
    <a href="../redireccion.php" class="volver"> Volver</a>
   <form action="purga_save.php" method="POST">
    <div class="cambio_silo">
        <h2>Cambio de Silo</h2>
        <div class="form-group">
            <label for="fecha_produccion">Fecha de Producción</label>
            <input type="date" name="fecha_produccion" id="fecha_produccion" value="<?php echo date('Y-m-d'); ?>" required>
        </div>
        <div class="form-group">
            <label for="referencia_producto">Referencia de Producto</label>
            <input type="text" name="referencia_producto" id="referencia_producto" required>
        </div>
        <div class="form-group">
            <label for="hora_inicial">Hora Inicial</label>
            <input type="time" name="hora_inicial" id="hora_inicial" required>
        </div>
        <div class="form-group">
            <label for="cantidad">Cantidad (KG)</label>
            <input type="number" name="cantidad" id="cantidad" required>
        </div>
        <div class="form-group">
            <label for="responsable_cambio">Responsable de la Purga</label>
            <select name="responsable_cambio" id="responsable_cambio">
                <option value="">Seleccione un Responsable</option>
                <?php foreach ($usuarios as $usuario): ?>
                    <option value="<?php echo $usuario['nombre_u']; ?>"><?php echo htmlspecialchars($usuario['nombre_u'], ENT_QUOTES, 'UTF-8'); ?></option>
                <?php endforeach; ?>
                <option value="Nigun Usuario Disponible">Nigun Usuario Disponible</option>
            </select>
        </div>
    </div>

    <div class="contenedor_linea">
        <h2>Regreso a Línea de Producción</h2>
        <div class="form-group">
            <label for="fecha_ingreso">Fecha de Ingreso a la Tolva</label>
            <input type="date" name="fecha_ingreso" id="fecha_ingreso" value="<?php echo date('Y-m-d'); ?>" required>
        </div>
        <div class="form-group">
            <label for="referencia_producto_linea">Referencia de Producto de Regreso a Línea</label>
            <input type="text" name="referencia_producto_linea" id="referencia_producto_linea" required>
        </div>
        <div class="form-group">
            <label for="hora_ingreso">Hora Inicial</label>
            <input type="time" name="hora_ingreso" id="hora_ingreso" required>
        </div>
        <div class="form-group">
            <label for="hora_final">Hora Final</label>
            <input type="time" name="hora_final" id="hora_final" required>
        </div>
        <div class="form-group">
            <label for="cantidad_line">Cantidad Total (KG)</label>
            <input type="number" name="cantidad_line" id="cantidad_linea" required>
        </div>
        <div class="form-group">
            <label for="responsable_linea">Responsable de la Purga</label>
            <select name="responsable_linea" id="responsable_linea">
                <option value="">Seleccione un Responsable</option>
                <?php foreach ($usuarios as $usuario): ?>
                    <option value="<?php echo $usuario['nombre_u']; ?>"><?php echo htmlspecialchars($usuario['nombre_u'], ENT_QUOTES, 'UTF-8'); ?></option>
                <?php endforeach; ?>
                <option value="Nigun Usuario Disponible">Nigun Usuario Disponible</option>
            </select>
        </div>
    </div>
    <input type="submit" value="Guardar">
</form>
    
</body>
</html>