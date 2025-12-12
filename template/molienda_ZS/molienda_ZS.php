<?php
require '../conection.php'; // Conexión a la base de datos
require '../sesion.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$zonaSeleccionada = $_SESSION['sede'];

if ($zonaSeleccionada === 'ZC') {
    die("Acceso denegado. Esta página es solo para la Zona Sur.");
}
// Buscar registro pendiente de la zona
$query = "SELECT id_proceso, turn1, turn2 
FROM control_molienda_zs 
WHERE zona = ? AND (turn1 = 0 OR turn2 = 0)
ORDER BY id_proceso DESC
LIMIT 1";

$stmt = $pdoControl_zs->prepare($query);
$stmt->execute([$zonaSeleccionada]);
$turnos = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$turnos) {
    // Si no hay registro pendiente, crear uno nuevo
    $queryInsert = "INSERT INTO control_molienda_zs (fecha, archivogen, turn1, turn2, creador, zona) VALUES (NOW(), 0, 0, 0, ?, ?)";
    $stmtInsert = $pdoControl_zs->prepare($queryInsert);
    $stmtInsert->execute([$_SESSION['id_usuario'], $zonaSeleccionada]);

    // Obtener el nuevo registro recién creado
    $stmt->execute([$zonaSeleccionada]);
    $turnos = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Mostrar botones según el estado de los turnos
$mostrarTurno1 = ($turnos && $turnos['turn1'] == 0);
$mostrarTurno2 = ($turnos && $turnos['turn2'] == 0);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/molienda.css">
    <title>Turnos de Molienda - <?= htmlspecialchars($zonaSeleccionada) ?></title>
</head>
<body class="cuerpoform">
    <h1 class="titulotabla">TURNOS DE MOLIENDA - ZONA <?= htmlspecialchars($zonaSeleccionada) ?></h1>
    <a href="../redireccion.php">Volver</a>

    <h2>Zona asignada: <strong><?= htmlspecialchars($zonaSeleccionada) ?></strong></h2>

    <div class="contenedor">
        <table class="tabla">
            <tbody>
                <tr>
                    <?php if ($mostrarTurno1): ?>
                        <td><a class="boton" href="formulario002_zs.php">TURNO 1</a></td>
                    <?php else: ?>
                        <td><button class="boton bloqueado" disabled>TURNO 1 COMPLETADO</button></td>
                    <?php endif; ?>

                    <?php if ($mostrarTurno2): ?>
                        <td><a class="boton" href="formulario002c_zs.php">TURNO 2</a></td>
                    <?php else: ?>
                        <td><button class="boton bloqueado" disabled>TURNO 2 COMPLETADO</button></td>
                    <?php endif; ?>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>
