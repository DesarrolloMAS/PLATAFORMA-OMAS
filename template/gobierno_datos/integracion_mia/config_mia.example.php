<?php
/**
 * Plantilla de configuración — copiar a config_mia.local.php (gitignored)
 * y poner los valores reales ahí. NUNCA escribir la llave real aquí.
 *
 * MIA_API_KEY debe coincidir EXACTO con NOVA_PULL_API_KEY del lado de mIA
 * (Backend/.env / docker-compose.prod.yml). Es NOVA quien llama a mIA, nunca
 * al revés — mIA no tiene ni necesita saber la dirección de NOVA.
 */
define('MIA_API_KEY', 'CAMBIAR_POR_LA_LLAVE_REAL');
define('MIA_BASE_URL', 'https://miachat.organizacionmas.com'); // URL real de mIA en producción
