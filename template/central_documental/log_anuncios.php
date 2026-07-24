<?php
/**
 * log_anuncios.php
 * Registra cada hora los anuncios activos en LOGS/anuncios.log
 * Ejecutado por cron: 0 * * * * /usr/bin/php /var/www/fmt/template/central_documental/log_anuncios.php
 */

$JSON_PATH = __DIR__ . '/../../archivos/generados/anuncios/anuncios.json';
$LOG_PATH  = __DIR__ . '/../../archivos/generados/LOGS/anuncios.log';

// Leer el JSON
$raw = file_get_contents($JSON_PATH);
if ($raw === false) {
    file_put_contents($LOG_PATH, "[" . date('Y-m-d H:i:s') . "] ERROR: No se pudo leer anuncios.json\n", FILE_APPEND);
    exit(1);
}

$anuncios = json_decode($raw, true);
if (!is_array($anuncios)) {
    file_put_contents($LOG_PATH, "[" . date('Y-m-d H:i:s') . "] ERROR: JSON malformado en anuncios.json\n", FILE_APPEND);
    exit(1);
}

$activos = array_filter($anuncios, fn($a) => ($a['activo'] ?? true) !== false);

// Construir entrada de log
$timestamp = date('Y-m-d H:i:s');
$lineas = [];
$lineas[] = "┌─────────────────────────────────────────────────────────────";
$lineas[] = "│ SNAPSHOT ANUNCIOS ACTIVOS · {$timestamp}";
$lineas[] = "│ Total activos: " . count($activos) . " / " . count($anuncios) . " anuncios";
$lineas[] = "├─────────────────────────────────────────────────────────────";

if (empty($activos)) {
    $lineas[] = "│  (sin anuncios activos)";
} else {
    foreach (array_values($activos) as $i => $a) {
        $tipo   = strtoupper($a['tipo']   ?? 'info');
        $titulo = $a['titulo'] ?? '(sin título)';
        $texto  = $a['texto']  ?? '';
        $fecha  = $a['fecha']  ?? '';
        $lineas[] = "│  [{$tipo}] {$titulo}";
        $lineas[] = "│       {$texto}";
        if ($fecha) {
            $lineas[] = "│       Fecha: {$fecha}";
        }
        if ($i < count($activos) - 1) {
            $lineas[] = "│  ···";
        }
    }
}

$lineas[] = "└─────────────────────────────────────────────────────────────";
$lineas[] = "";

file_put_contents($LOG_PATH, implode("\n", $lineas), FILE_APPEND);
