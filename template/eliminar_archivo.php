<?php
session_start();

require 'sesion.php'; // Verificar que el usuario esté autenticado

$sede = $_SESSION['sede'] ?? 'ZC';

// Determinar la carpeta según la sede
if ($sede === 'ZC') {
    $carpeta = __DIR__ . '/../archivos/generados/excelS_M/';
} else {
    $carpeta = __DIR__ . '/../archivos/generados/excelS_MZS/';
}

// Función para eliminar un archivo de forma segura y registrar el log
function eliminarArchivoSeguro($archivo, $carpeta, $sede, $usuario) {
    $carpetaReal = realpath($carpeta);
    $rutaCompleta = $carpeta . $archivo;
    $archivoReal = realpath($rutaCompleta);

    if (!$archivoReal || strpos($archivoReal, $carpetaReal) !== 0) {
        return "❌ Archivo no válido o fuera del directorio permitido: $archivo";
    }
    if (!file_exists($archivoReal)) {
        return "❌ El archivo no existe: $archivo";
    }
    if (unlink($archivoReal)) {
        // Log de la eliminación para auditoría
        $fecha = date('Y-m-d H:i:s');
        $logMessage = "[$fecha] Usuario: $usuario eliminó el archivo: $archivo (Sede: $sede)\n";
        $logDir = __DIR__ . '/../logs/';
        if (!file_exists($logDir)) {
            mkdir($logDir, 0777, true);
        }
        file_put_contents($logDir . 'eliminaciones.log', $logMessage, FILE_APPEND | LOCK_EX);
        return "✅ Archivo eliminado correctamente: $archivo";
    } else {
        return "❌ Error al eliminar el archivo (verifique permisos): $archivo";
    }
}

$usuario = $_SESSION['nombre'] ?? 'Usuario desconocido';

// Eliminar múltiples archivos
if (isset($_POST['archivos']) && is_array($_POST['archivos'])) {
    $mensajes = [];
    foreach ($_POST['archivos'] as $archivo) {
        $mensajes[] = eliminarArchivoSeguro($archivo, $carpeta, $sede, $usuario);
    }
    $alerta = implode('\n', $mensajes);
    echo "<script>
        alert('$alerta');
        window.location.href = 'reformulario001.php';
    </script>";
    exit;
}

// Eliminar un solo archivo (caso anterior)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['archivo'])) {
    $mensaje = eliminarArchivoSeguro($_POST['archivo'], $carpeta, $sede, $usuario);
    echo "<script>
        alert('$mensaje');
        window.location.href = 'reformulario001.php';
    </script>";
    exit;
}

die('❌ Solicitud inválida');
?>