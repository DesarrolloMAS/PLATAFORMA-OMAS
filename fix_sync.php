<?php
require_once '/var/www/fmt/template/gobierno_datos/bitacora_produccion/sincronizador.php';
$sync = new ProduccionSincronizador();
$sync->sincronizar('2026-04-13', 'ZC');
echo "Sync successful.\n";
?>
