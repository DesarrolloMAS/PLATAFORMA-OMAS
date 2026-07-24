<?php
$path = '/var/www/fmt/archivos/generados/molienda/ZC/2026-04.json';
$data = json_decode(file_get_contents($path), true);

// Intercambiar el objeto en la posición 0 con el de la posición 1
$temp = $data[0];
$data[0] = $data[1];
$data[1] = $temp;

// Guardar el archivo
file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));
echo "Se intercambió el turno 1 del día 8 con el turno 1 del día 9 correctamente.\n";
