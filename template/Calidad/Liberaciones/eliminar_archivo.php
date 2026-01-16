<?php
session_start();
require_once '../../sesion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['archivo'])) {
    die('❌ Solicitud inválida');
}

$archivo = $_POST['archivo'];
$sede = $_SESSION['sede'] ?? 'ZC';

// Determinar la carpeta según la sede
if ($sede === 'ZS') {
    $carpeta = '/var/www/fmt/archivos/generados/Calidad/liberaciones_zs/';
} else {
    $carpeta = '/var/www/fmt/archivos/generados/Calidad/liberaciones/';
}

// Validar que el archivo existe y está en la carpeta correcta
$rutaCompleta = $carpeta . $archivo;

// Verificar que el archivo está dentro de la carpeta permitida (seguridad)
$carpetaReal = realpath($carpeta);
$archivoReal = realpath($rutaCompleta);

if (!$archivoReal || strpos($archivoReal, $carpetaReal) !== 0) {
    die('❌ Archivo no válido o fuera del directorio permitido');
}

// Verificar que el archivo existe
if (!file_exists($archivoReal)) {
    die('❌ El archivo no existe');
}

// Intentar eliminar el archivo
if (unlink($archivoReal)) {
    // Log de la eliminación para auditoría
    $usuario = $_SESSION['nombre'] ?? 'Usuario desconocido';
    $fecha = date('Y-m-d H:i:s');
    $logMessage = "[$fecha] Usuario: $usuario eliminó el archivo: $archivo (Sede: $sede)\n";
    
    // Crear directorio de logs si no existe
    $logDir = __DIR__ . '/../logs/';
    if (!file_exists($logDir)) {
        mkdir($logDir, 0777, true);
    }
    
    // Escribir en el log
    file_put_contents($logDir . 'eliminaciones.log', $logMessage, FILE_APPEND | LOCK_EX);
    
    // Redirigir de vuelta con mensaje de éxito
    echo "<script>
        alert('✅ Archivo eliminado correctamente: $archivo');
        window.location.href = 'galeria_liberaciones.php';
    </script>";
} else {
    echo "<script>
        alert('❌ Error al eliminar el archivo. Verifique los permisos.');
        window.location.href = 'galeria_liberaciones.php';
    </script>";
}
?>