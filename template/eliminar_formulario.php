<?php
session_start();

// Validar sesión
if (!isset($_SESSION['nombre'])) {
    die("❌ Usuario no autenticado.");
}

// Validar que llegue el nombre del archivo
if (!isset($_POST['archivo'])) {
    die("❌ No se especificó el archivo a eliminar.");
}

$usuario = $_SESSION['nombre'];
$archivo = $_POST['archivo'];
$ruta_archivo = "C:/xampp/htdocs/fmt/archivos/formularios_guardados/{$usuario}/{$archivo}";

// Verificar y eliminar
if (file_exists($ruta_archivo)) {
    if (unlink($ruta_archivo)) {
        header("Location: formatos_guardados.php?msg=eliminado");
        exit;
    } else {
        die("❌ No se pudo eliminar el archivo. Verifica permisos.");
    }
} else {
    die("❌ Archivo no encontrado.");
}
?>
