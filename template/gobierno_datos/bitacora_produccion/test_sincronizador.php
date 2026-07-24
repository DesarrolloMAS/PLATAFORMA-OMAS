<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require '/var/www/fmt/template/gobierno_datos/bitacora_produccion/sincronizador.php';
$sync = new ProduccionSincronizador();
$result = $sync->sincronizar(date('Y-m-d'), 'ZC');
var_dump($result);
