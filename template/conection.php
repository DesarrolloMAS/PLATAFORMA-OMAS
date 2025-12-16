<?php
$host = 'localhost';
$dbnameUsuarios = 'usuarios';
$dbnameControl = 'control_molienda';
$dbnameControl_zs = 'control_molienda_zs';
$dbnamemaquinas = 'maquinas';
$username = 'root'; // Cambia esto según tu configuración
$password = '0000'; // Cambia esto según tu configuración

// Conexión a la base de datos "usuarios"
try {
    $pdoUsuarios = new PDO("mysql:host=$host;dbname=$dbnameUsuarios", $username, $password);
    $pdoUsuarios->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error al conectar a la base de datos 'usuarios': " . $e->getMessage());
}

// Conexión a la base de datos "control_molienda"
try {
    $pdoControl = new PDO("mysql:host=$host;dbname=$dbnameControl", $username, $password);
    $pdoControl->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error al conectar a la base de datos 'control_molienda': " . $e->getMessage());
}
// Conexion a la base de datos "control_molienda_zs"
try {
    $pdoControl_zs = new PDO("mysql:host=$host;dbname=$dbnameControl_zs", $username, $password);
    $pdoControl_zs->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error al conectar a la base de datos 'control_molienda-zs': " . $e->getMessage());
}
try {
    $pdomaquinas = new PDO("mysql:host=$host;dbname=$dbnamemaquinas", $username, $password);
    $pdomaquinas->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error al conectar a la base de datos 'maquinas': " . $e->getMessage());
}
?>