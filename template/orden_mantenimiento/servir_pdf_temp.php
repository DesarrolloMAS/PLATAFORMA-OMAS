<?php
/**
 * servir_pdf_temp.php
 * ---------------------
 * Sirve un PDF ya generado por generar_periodo.php (identificado por token)
 * como descarga, y lo elimina del disco inmediatamente después de servirlo.
 */
require_once '../sesion.php';
verificarAutenticacion();

$token = $_GET['token'] ?? '';
$token = preg_replace('/[^A-Za-z0-9_.\-]/', '', $token);

if (!$token) {
    http_response_code(400);
    die('Token inválido.');
}

$tempDir = "../../archivos/generados/orden_mantenimiento/TEMP_PDF/";
$path    = $tempDir . $token . '.pdf';

if (!file_exists($path)) {
    http_response_code(404);
    die('El archivo ya no está disponible (pudo haber expirado).');
}

$filename = $_GET['filename'] ?? ($token . '.pdf');
$filename = preg_replace('/[^A-Za-z0-9_.\- ]/', '_', $filename);

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($path));
readfile($path);
@unlink($path);
exit;
