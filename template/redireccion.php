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
    case 'Desarrollo': // Misma configuración que Operaciones (ver notas del área en admin/menu_admin.php)
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
            default:
                session_destroy();
                header('Location: ../login.html'); // Redirigir al inicio de sesión si el rol no es válido
                exit();
        }
        break;

    case 'Calidad':
        // Redirigir según el rol en el área de Calidad.
        // Único rol que cuenta como administrador de Calidad: 'adm' (igual
        // que admin/menu_admin.php, que también exige ese literal). '1' NO
        // es admin aquí — a diferencia de Operaciones, donde sí lo es: la
        // única cuenta real de Calidad hoy (cargo "Gestion Calidad") tiene
        // rol '1' y debe ir directo a los formularios, no a administración.
        switch ($_SESSION['rol']) {
            case 'adm': // Administrador de Calidad
                header('Location: menu_administracion_calidad.html'); // Menú de administración (usuarios, dashboard, formularios)
                exit();
            case '1': // Rol bajo en Calidad
                header('Location: menu_adm_calidad.html'); // Va directo a los formularios, sin pasar por administración
                exit();
            case '3': // Rol bajo en Calidad
                header('Location: menu_adm_calidad.html'); // Va directo a los formularios, sin pasar por administración
                exit();
            default:
                session_destroy();
                header('Location: ../login.html'); // Redirigir al inicio de sesión si el rol no es válido
                exit();
        }
        break;
    case 'HSEQ':
        // Redirigir según el rol en el área de HSEQ. Único rol admin: 'adm'
        // (igual que admin/menu_admin.php) — mismo patrón que Calidad.
        switch ($_SESSION['rol']) {
            case 'adm': // Administrador de HSEQ
                header('Location: menu_administracion_hseq.html'); // Menú de administración (usuarios, dashboard, formularios)
                exit();
            case '1': // Rol bajo en HSEQ
                header('Location: menu_hseq_adm.html'); // Va directo a los formularios, sin pasar por administración
                exit();
            case '3': // Rol bajo en HSEQ
                header('Location: menu_hseq_adm.html'); // Va directo a los formularios, sin pasar por administración
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