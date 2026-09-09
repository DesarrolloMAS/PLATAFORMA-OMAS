<?php
/**
 * Poller activo — NOVA llama a mIA, nunca al revés (NOVA no tiene salida
 * pública). Pensado para ejecutarse periódicamente por cron, mismo patrón
 * que ya usa la plataforma en template/activador.js (node-cron).
 *
 * Ejecutar manualmente: php consultar_mia.php
 * En producción, programar en cron cada 15 min, ej.:
 *   /15 * * * * php /var/www/fmt/template/gobierno_datos/integracion_mia/consultar_mia.php >> /var/log/mia_sync.log 2>&1
 *
 * La lógica real vive en sync_logic.php (compartida con panel.php, el botón
 * manual del dashboard de Bitácora de Mantenimiento) — ver integracion-mia.md.
 */

if (file_exists(__DIR__ . '/config_mia.local.php')) {
    require_once __DIR__ . '/config_mia.local.php';
} else {
    require_once __DIR__ . '/config_mia.example.php';
}
require_once __DIR__ . '/conexion_mia.php';
require_once __DIR__ . '/sync_logic.php';

$resultado = ejecutar_sync_tickets($pdoMia);
echo ($resultado['ok'] ? 'OK: ' : 'ERROR: ') . $resultado['mensaje'] . "\n";
exit($resultado['ok'] ? 0 : 1);
