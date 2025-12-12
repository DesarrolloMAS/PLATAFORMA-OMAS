<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
function verificarAutenticacion() {
    if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['area'])) {
        header('Location: ../index.php'); 
        exit();
    }
}

if (!isset($_SESSION['nombre'])) {
    die("Error: Usuario no hay nombre.");
}

function verificarRol($rolRequerido) {
    if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== $rolRequerido) {
        header('Location: acceso_denegado.php'); // Redirige a una página de error
        exit();
    }
}


function verificarRolesPermitidos($rolesPermitidos) {
    if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], $rolesPermitidos)) {
        header('Location: acceso_denegado.php'); // Redirige a una página de error
        exit();
    }
}