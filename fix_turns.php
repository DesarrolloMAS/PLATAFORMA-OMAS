<?php
$file = '/var/www/fmt/archivos/generados/molienda/ZC/2026-04.json';
$data = json_decode(file_get_contents($file), true);

$temp = $data[11];
$data[11] = $data[12];
$data[12] = $temp;

file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));

require_once '/var/www/fmt/gobierno_datos/bitacora_produccion/sincronizador.php';
$sync = new ProduccionSincronizador();
$sync->sincronizar('2026-04-13', 'ZC');
echo "Swap and Sync successful.\n";
?>
