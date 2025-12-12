<?php
function escribirLog($mensaje) {
    $archivo = "C:/xampp/htdocs/fmt/debug/error_log.txt";  // Ajusta esta ruta si la necesitas diferente
    $fecha = date("Y-m-d H:i:s");
    $linea = "[$fecha] $mensaje" . PHP_EOL;
    file_put_contents($archivo, $linea, FILE_APPEND);
}
?>
