<?php
require '/var/www/fmt/template/conection.php';
$stmt = $pdoUsuarios->query("SELECT nombre_u, sede FROM usuarios WHERE sede NOT IN ('ZC', 'ZS')");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($rows);
?>
