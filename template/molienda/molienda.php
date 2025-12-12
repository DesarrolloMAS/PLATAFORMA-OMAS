<?php
require '../conection.php'; // Conexión a la base de datos
require '../sesion.php';

// Obtener la zona desde la sesión del usuario
$zonaSeleccionada = $_SESSION['sede'];

// Obtener el último registro de turnos de la zona asignada al usuario
$query = $query = "SELECT id_proceso, turn1, turn2, turn3 
FROM control_molienda 
WHERE zona = ? 
AND id_proceso = (SELECT MAX(id_proceso) FROM control_molienda WHERE zona = ?)";
$stmt = $pdoControl->prepare($query);
$stmt->execute([$zonaSeleccionada, $zonaSeleccionada]);
$turnos = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$turnos) {
    // Si no hay registros en la zona, se debe crear el primer registro
    $queryInsert = "INSERT INTO control_molienda (fecha, archivogen, turn1, turn2, turn3, creador, zona) VALUES (NOW(), 0, 0, 0, 0, ?, ?)";
    $stmtInsert = $pdoControl->prepare($queryInsert);
    $stmtInsert->execute([$_SESSION['id_usuario'], $zonaSeleccionada]);

    // Obtener el nuevo registro recién creado
    $stmt->execute([$zonaSeleccionada, $zonaSeleccionada]); // <- Corrección aquí
    $turnos = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Verificar si los 3 turnos están completos
if ($turnos['turn1'] == 1 && $turnos['turn2'] == 1 && $turnos['turn3'] == 1) {
    // Crear un nuevo registro para la misma zona con turnos en 0
    $queryInsert = "INSERT INTO control_molienda (fecha, archivogen, turn1, turn2, turn3, creador, zona) VALUES (NOW(), 0, 0, 0, 0, ?, ?)";
    $stmtInsert = $pdoControl->prepare($queryInsert);
    $stmtInsert->execute([$_SESSION['id_usuario'], $zonaSeleccionada]);

    // Obtener el nuevo registro creado
    $stmt->execute([$zonaSeleccionada, $zonaSeleccionada]); // <- Corrección aquí
    $turnos = $stmt->fetch(PDO::FETCH_ASSOC);
}


// Verificar qué turnos están disponibles
$mostrarTurno1 = ($turnos['turn1'] == 0);
$mostrarTurno2 = ($turnos['turn2'] == 0);
$mostrarTurno3 = ($turnos['turn3'] == 0);
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
    <a class="boton" href="../redireccion.php">Volver</a>

    <h2>Zona asignada: <strong><?= htmlspecialchars($zonaSeleccionada) ?></strong></h2>

    <div class="contenedor">
        <table class="tabla">
            <tbody>
                <tr>
                    <?php if ($mostrarTurno1): ?>
                        <td><a class="boton" href="formulario002.php">TURNO 1</a></td>
                    <?php else: ?>
                        <td><button class="boton bloqueado" disabled>TURNO 1 COMPLETADO</button></td>
                    <?php endif; ?>

                    <?php if ($mostrarTurno2): ?>
                        <td><a class="boton" href="formulario002m.php">TURNO 2</a></td>
                    <?php else: ?>
                        <td><button class="boton bloqueado" disabled>TURNO 2 COMPLETADO</button></td>
                    <?php endif; ?>

                    <?php if ($mostrarTurno3): ?>
                        <td><a class="boton" href="formulario002c.php">TURNO 3</a></td>
                    <?php else: ?>
                        <td><button class="boton bloqueado" disabled>TURNO 3 COMPLETADO</button></td>
                    <?php endif; ?>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>
