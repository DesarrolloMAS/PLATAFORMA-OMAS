<?php
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SESSION = [
    'id_usuario' => 1,
    'area' => 'Sistemas',
    'sede' => 'ZC',
    'nombre' => 'Test User'
];
$_POST = [
    'fecha' => '2026-04-24',
    'hora' => '10:00',
    'responsable' => 'Test',
    'almacenista' => 'Test',
    'cedula_firma' => '123456789'
];
require '/var/www/fmt/template/molienda_v2/procesar.php';
