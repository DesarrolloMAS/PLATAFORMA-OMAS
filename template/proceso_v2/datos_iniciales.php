<?php
require '../conection.php';
require '../sesion.php';
verificarAutenticacion();

header('Content-Type: application/json; charset=utf-8');

$lideres = [];
try {
    $stmt = $pdoUsuarios->prepare("SELECT nombre_u FROM usuarios WHERE Cargo = :cargo ORDER BY nombre_u ASC");
    $stmt->execute(['cargo' => 'Lider de Turno']);
    $lideres = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    error_log("Error consultando lideres de turno (proceso_v2): " . $e->getMessage());
}

echo json_encode([
    'status'   => 'success',
    'sede'     => $_SESSION['sede'],
    'nombre'   => $_SESSION['nombre'],
    'lideres'  => $lideres
]);
