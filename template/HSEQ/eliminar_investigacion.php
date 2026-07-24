<?php
require '../sesion.php';
verificarAutenticacion();

$carpeta = '/var/www/fmt/archivos/generados/HSEQ/investigacionesjson/';
$respuestas = [];

if (!empty($_POST['archivos'])) {
    foreach ($_POST['archivos'] as $archivo) {
        $ruta = $carpeta . basename($archivo) . '.json';
        if (file_exists($ruta)) {
            if (unlink($ruta)) {
                $respuestas[] = "Eliminado: $archivo";
            } else {
                $respuestas[] = "Error al eliminar: $archivo";
            }
        } else {
            $respuestas[] = "No existe: $archivo";
        }
    }
    echo implode("\n", $respuestas);
} else {
    echo "No se recibieron archivos para eliminar.";
}