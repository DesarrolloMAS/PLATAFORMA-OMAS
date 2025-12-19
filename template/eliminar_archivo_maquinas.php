<?php
session_start();
require 'sesion.php'; // Ajusta la ruta si es necesario

$usuario = $_SESSION['nombre'] ?? 'Usuario desconocido';

// Función para eliminar archivos de verificaciones de máquinas
function eliminarArchivoVerificacion($rutaRelativa, $usuario) {
    $carpetaBase = realpath('/var/www/fmt/archivos/generados/verificaciones');
    if (!$carpetaBase) {
        return "❌ No se encontró el directorio base de verificaciones.";
    }

    // Normaliza la ruta relativa para evitar ../
    $rutaRelativa = ltrim($rutaRelativa, '/\\');
    $rutaCompleta = $carpetaBase . DIRECTORY_SEPARATOR . $rutaRelativa;

    // Validar que la ruta esté dentro del directorio permitido (sin usar realpath del archivo)
    $rutaReal = realpath(dirname($rutaCompleta));
    if (!$rutaReal || strpos($rutaReal, $carpetaBase) !== 0) {
        return "❌ Archivo no válido o fuera del directorio permitido: $rutaRelativa";
    }

    if (!file_exists($rutaCompleta)) {
        return "❌ El archivo no existe: $rutaRelativa";
    }
    if (!is_file($rutaCompleta)) {
        return "❌ No es un archivo válido: $rutaRelativa";
    }
    if (unlink($rutaCompleta)) {
        // Log de la eliminación para auditoría
        $fecha = date('Y-m-d H:i:s');
        $logMessage = "[$fecha] Usuario: $usuario eliminó el archivo: $rutaRelativa (Verificaciones)\n";
        $logDir = __DIR__ . '/../../logs/';
        if (!file_exists($logDir)) {
            mkdir($logDir, 0777, true);
        }
        file_put_contents($logDir . 'eliminaciones_verificaciones.log', $logMessage, FILE_APPEND | LOCK_EX);
        return "✅ Archivo eliminado correctamente";
    } else {
        return "❌ Error al eliminar el archivo (verifique permisos): $rutaRelativa";
    }
}

// Procesar la petición POST
if (isset($_POST['archivos']) && is_array($_POST['archivos'])) {
    $mensajes = [];
    foreach ($_POST['archivos'] as $rutaRelativa) {
        $mensajes[] = eliminarArchivoVerificacion($rutaRelativa, $usuario);
    }
    $alerta = implode('\n', $mensajes);
    echo $alerta;
    exit;
}

die('❌ Solicitud inválida');
?>