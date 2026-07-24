<?php
require_once __DIR__ . '/sincronizador.php';

$sync = new ProduccionSincronizador();
echo "Iniciando prueba de sincronización para 2026-04-08...\n";

$resultado = $sync->sincronizar('2026-04-08', 'ZC');

echo "Resultado:\n";
print_r($resultado);
