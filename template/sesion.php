<?php
// ============================================
// SESION MODIFICADA PARA QUE DURE UN TOTAL DE 2 HORAS
// ============================================
ini_set('session.gc_maxlifetime', 7200);
session_set_cookie_params(7200);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
function verificarAutenticacion() {
    if (
        !isset($_SESSION['id_usuario']) ||
        !isset($_SESSION['area']) ||
        !isset($_SESSION['sede']) ||
        !isset($_SESSION['nombre'])
    ) {
        header('Location: /index.php?motivo=sesion'); 
        exit();
    }
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