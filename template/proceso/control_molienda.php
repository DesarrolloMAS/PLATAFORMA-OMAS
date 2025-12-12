<?php
require '../sesion.php';
require '../conection.php';
$filtro_cargo = 'Lider de Turno';
$query = $pdoUsuarios->prepare("SELECT id_usuario, nombre_u FROM usuarios WHERE Cargo = :filtro_cargo");
$query->execute(['filtro_cargo' => $filtro_cargo]);
$usuarios = $query->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CONTROL DE PROCESO DE MOLIENDA</title>
    <link rel="stylesheet" href="/css/proceso_moliendo.css">
</head>
<body>
    <h1>CONTROL DE PROCESO DE MOLIENDA</h1>
    <form action="control_save.php" method="POST">
    <a class="volver" href="../redireccion.php">VOLVER</a>
        <div class="contenedor-harina">
            <h2>Datos de Harina</h2>
            <div class="form-group">
                <label for="fecha">FECHA</label>
                <input type="date" id="fecha" name="fecha">
            </div>
            <div class="form-group">
                <label for="lider">Lider de Turno</label>
                <select name="lider" id="lider">
                    <option value="">Seleccione un Responsable</option>
                    <?php foreach ($usuarios as $usuario): ?>
                        <option value="<?php echo $usuario['nombre_u']; ?>"><?php echo $usuario['nombre_u']; ?></option>
                    <?php endforeach; ?>
                    <option value="Nigun Usuario Disponible">Nigun Usuario Disponible</option>
                </select>
            </div>
            <div class="form-group">
                <label for="moje">Silo de Moje</label>
                <input type="number" id="moje" name="moje">
            </div>
            <div class="form-group">
                <label for="producto">Presentacion Del Producto</label>
                <input type="text" id="producto" name="producto">
            </div>
            <div class="form-group">
                <label for="referencia">Referencia del Producto</label>
                <input type="text" id="referencia" name="referencia">
            </div>
            <div class="form-group">
                <label for="hora_inicio">Hora Inicio</label>
                <input type="time" id="hora_inicio" name="hora_inicio">
            </div>
            <div class="form-group">
                <label for="hora_final">Hora Final</label>
                <input type="time" id="hora_final" name="hora_final">
            </div>
            <div class="form-group">
                <label for="bascula">Bascula de Trigo</label>
                <input type="number" id="bascula" name="bascula">
            </div>
            <div class="form-group">
                <label for="bascula_harina">Bascula de Harina</label>
                <input type="text" id="bascula_harina" name="bascula_harina">
            </div>
            <div class="form-group">
                <label for="bultos_harina">Bultos Bolsas de Harina (unidades)</label>
                <input type="text" id="bultos_harina" name="bultos_harina">
            </div>
            <div class="form-group">
                <label for="lote_harina">Lote de Harina</label>
                <input type="text" id="lote_harina" name="lote_harina">
            </div>
            <div class="form-group">
                <h2>HARINA GRANEL</h2>
                <label for="cantidad_kg">Cantidad KG</label>
                <input type="text" id="cantidad_kg" name="cantidad_kg">
                <label for="silo_granel">Silo</label>
                <input type="text" id="silo_granel" name="silo_granel">
            </div>
        </div>
        <div class="contenedor-subproductos">
            <h2>Subproductos</h2>
            <div class="subproducto-block">
                <h3>MOGOLLA</h3>
                <div class="form-group">
                    <label for="bultos_mogolla">Bultos</label>
                    <input type="number" id="bultos_mogolla" name="bultos_mogolla">
                </div>
                <div class="form-group">
                    <label for="hilo_mogolla">Hilo</label>
                    <input type="text" id="hilo_mogolla" name="hilo_mogolla">
                </div>
            </div>
            <div class="subproducto-block">
                <h3>SALVADO</h3>
                <div class="form-group">
                    <label for="bultos_salvado">Bultos</label>
                    <input type="number" id="bultos_salvado" name="bultos_salvado">
                </div>
                <div class="form-group">
                    <label for="hilo_salvado">Hilo</label>
                    <input type="text" id="hilo_salvado" name="hilo_salvado">
                </div>
            </div>
            <div class="subproducto-block">
                <h3>SEGUNDA</h3>
                <div class="form-group">
                    <label for="bultos_segunda">Bultos</label>
                    <input type="number" id="bultos_segunda" name="bultos_segunda">
                </div>
                <div class="form-group">
                    <label for="hilo_segunda">Hilo</label>
                    <input type="text" id="hilo_segunda" name="hilo_segunda">
                </div>
            </div>
            <div class="subproducto-block">
                <h3>GERMEN</h3>
                <div class="form-group">
                    <label for="bultos_germen">Bultos</label>
                    <input type="number" id="bultos_germen" name="bultos_germen">
                </div>
                <div class="form-group">
                    <label for="hilo_germen">Hilo</label>
                    <input type="text" id="hilo_germen" name="hilo_germen">
                </div>
            </div>
            <div class="subproducto-block">
                <h3>SEMOLA FINA</h3>
                <div class="form-group">
                    <label for="cantidad_granza">Cantidad (Bultos)</label>
                    <input type="number" id="cantidad_granza" name="cantidad_granza">
                </div>
            </div>
            <div class="subproducto-block">
                <h3>VARADAS</h3>
                <div class="form-group">
                    <label for="varadas">Varadas</label>
                    <textarea name="varadas" id="varadas"></textarea>
                </div>
            </div>
        </div>
        <input type="submit" value="Guardar">
    </form>
</body>
</html>