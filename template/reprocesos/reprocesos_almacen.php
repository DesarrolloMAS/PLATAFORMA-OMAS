<?php
require '../conection.php';
require '../sesion.php';
function obtenerUsuariosPorCargos($pdoUsuarios, $cargosFiltrados) {
    try {
        $placeholders = implode(',', array_fill(0, count($cargosFiltrados), '?'));

        $sql = "SELECT nombre_u FROM usuarios WHERE Cargo IN ($placeholders)";
        $stmt = $pdoUsuarios->prepare($sql);

        $stmt->execute($cargosFiltrados);

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        error_log("Error al obtener los usuarios por cargos: " . $e->getMessage());
        die("Error al obtener los usuarios. Intenta nuevamente más tarde.");
    }
}
$cargosFiltrados = ["Lider de almacen", "Auxiliar de almacen",];
$usuarios = obtenerUsuariosPorCargos($pdoUsuarios, $cargosFiltrados);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/reprocesos_almacen.css">
    <title>REPROCESOS</title>
</head>
<body>
    <h1>CONTROL DE REPROCESOS</h1>
    <h2>SECCION DE ALMACEN</h2>
    <div>
        <form action="almacen_procesado.php" method="post">
            <div>
                <label for="fecha">Fecha de Alistamiento</label>
                <input type="date" name="fecha">
            </div>
            <div>
                <label for="responable">Responsable de Alistamiento</label>
                <select name="responsable" id="responsable" required>
                <?php if (!empty($usuarios)): ?>
                <?php foreach ($usuarios as $usuarios): ?>
                <option value="<?php echo htmlspecialchars($usuarios, ENT_QUOTES, 'UTF-8'); ?>">
                    <?php echo htmlspecialchars($usuarios, ENT_QUOTES, 'UTF-8'); ?>
                </option>
                <?php endforeach; ?>
                <?php else: ?>
                <option value="">No hay Usuarios disponibles</option>
                <?php endif; ?>
                <option value="NULL">Ninguno.</option>
                </select>
            </div>
            <div>
                <label for="producto">Producto</label>
                <input type="text" name="producto">
            </div>
            <div>
                <label for="lote">Lote</label>
                <input type="text" name="lote">
            </div>
            <div>
                <label for="hora">Hora</label>
                <input type="time" name="hora">
            </div>
            <div>
                <label for="cantidad">Cantidad</label>
                <input type="number" name="cantidad" placeholder="Peso en KG">
            </div>
            <div>
                <label for="motivo">Motivo</label>
                <textarea name="motivo" id=""></textarea>
            </div>
            <div>
                <label for="proceso">Proceso Utilizado</label>
                <input type="text" name="proceso">
            </div>
            <div>
                <input type="submit" value="Guardar">
            </div>
        </form>
    </div>
</body>
</html>