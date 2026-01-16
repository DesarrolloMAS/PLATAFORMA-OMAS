<?php
require 'sesion.php'; // Incluye el archivo de sesión

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../index.php'); // Redirigir al inicio de sesión si no está autenticado
    exit();
}

// Redirigir según el área y el rol
switch ($_SESSION['area']) {
    case 'Operaciones':
        // Redirigir según el rol en el área de Operaciones
        switch ($_SESSION['rol']) {
            case 'adm': // Rol alto en Operaciones
                header('Location: menu_adm.html'); // Menú para administradores
                exit();
            case '1': // Rol alto en Operaciones
                header('Location: menu_adm.html'); // Menú para administradores
                exit();
            case '2': // Rol alto en Operaciones
                header('Location: menu_adm.html'); // Menú para administradores
                exit();
            case '3': // Rol bajo en Operaciones
                header('Location: menu.html'); // Menú para empacadores
                exit();
            default:
                session_destroy();
                header('Location: ../login.html'); // Redirigir al inicio de sesión si el rol no es válido
                exit();
        }
        break;

    case 'Calidad':
        // Redirigir según el rol en el área de Calidad
        switch ($_SESSION['rol']) {
            case 'adm': // Rol alto en Calidad
                header('Location: menu_adm_calidad.html'); // Menú para administradores de Calidad
                exit();
            case '1': // Rol alto en Calidad
                header('Location: menu_adm_calidad.html'); // Menú para administradores de Calidad
                exit();
            case '3': // Rol bajo en Calidad
                header('Location: menu_calidad.html'); // Menú para usuarios básicos de Calidad
                exit();
            default:
                session_destroy();
                header('Location: ../login.html'); // Redirigir al inicio de sesión si el rol no es válido
                exit();
        }
        break;
    case 'HSEQ':
        // Redirigir según el rol en el área de Calidad
        switch ($_SESSION['rol']) {
            case 'adm': // Rol alto en Calidad
                header('Location: menu_hseq_adm.html'); // Menú para administradores de Calidad
                exit();
            case '1': // Rol alto en Calidad
                header('Location: menu_hseq_adm.html'); // Menú para administradores de Calidad
                exit();
            case '3': // Rol bajo en Calidad
                header('Location: menu_hseq_adm.html'); // Menú para usuarios básicos de Calidad
                exit();
            default:
                session_destroy();
                header('Location: ../login.html'); // Redirigir al inicio de sesión si el rol no es válido
                exit();
        }
        break;
    

    default:
        // Si el área no coincide con Operaciones o Calidad
        session_destroy();
        header('Location: ../login.html'); // Redirigir al inicio de sesión
        exit();
}
?>