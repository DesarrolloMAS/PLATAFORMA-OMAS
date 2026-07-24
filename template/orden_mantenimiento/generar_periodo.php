<?php
/**
 * generar_periodo.php
 * ---------------------
 * Genera un PDF individual por cada registro del periodo solicitado, en un
 * solo lote de Puppeteer (un navegador, una pestaña por registro). Devuelve
 * un manifiesto {token, filename} por archivo generado para que el frontend
 * (visor_masivo.php) los descargue uno por uno vía servir_pdf_temp.php.
 *
 * Reemplaza el antiguo flujo de visor_masivo.php basado en html2pdf.js.
 */
require_once '../sesion.php';
verificarAutenticacion();
header('Content-Type: application/json; charset=utf-8');

$file = $_GET['file'] ?? '';
$sede = $_SESSION['sede'] ?? 'NA';

if (!$file) {
    echo json_encode(['success' => false, 'error' => 'Periodo no especificado.']);
    exit;
}

$path = "../../archivos/generados/orden_mantenimiento/" . $sede . "/" . $file . ".json";
if (!file_exists($path)) {
    echo json_encode(['success' => false, 'error' => 'Periodo no encontrado.']);
    exit;
}

$registros = json_decode(file_get_contents($path), true) ?: [];
if (empty($registros)) {
    echo json_encode(['success' => false, 'error' => 'El periodo no contiene registros.']);
    exit;
}

$host      = $_SERVER['HTTP_HOST'];
$sessionId = session_id();

$tempDir = "../../archivos/generados/orden_mantenimiento/TEMP_PDF/";
if (!is_dir($tempDir)) {
    mkdir($tempDir, 0777, true);
}

// Limpieza de temporales huérfanos de intentos previos (>30 min sin servirse)
foreach (glob($tempDir . '*') as $old) {
    if (is_file($old) && (time() - filemtime($old)) > 1800) {
        @unlink($old);
    }
}

$jobs = [];
$meta = [];
foreach ($registros as $i => $registro) {
    $numero_orden      = $registro['datos']['numero_orden'] ?? $registro['id'];
    $numero_orden_safe = preg_replace('/[^A-Za-z0-9_\-]/', '_', $numero_orden);
    $token             = uniqid('pdf_', true);
    $pdfPath           = $tempDir . $token . '.pdf';
    $url               = "http://{$host}/template/orden_mantenimiento/visor.php?file=" . urlencode($file) . "&id=" . urlencode($registro['id']);

    $jobs[] = ['id' => $i, 'url' => $url, 'pdfPath' => $pdfPath];
    $meta[$i] = ['token' => $token, 'filename' => 'Orden_Mantenimiento_' . $numero_orden_safe . '.pdf'];
}

// CRÍTICO: liberar el bloqueo de sesión antes de invocar a Puppeteer.
session_write_close();

$manifestPath = $tempDir . 'manifest_' . uniqid('', true) . '.json';
file_put_contents($manifestPath, json_encode(['sessionId' => $sessionId, 'host' => $host, 'jobs' => $jobs]));

$cmd = "node " . escapeshellarg(__DIR__ . "/generar_pdf.js") . " --batch " . escapeshellarg($manifestPath) . " 2>&1";
$out = shell_exec($cmd);
@unlink($manifestPath);

$decoded = json_decode($out, true);
if (!is_array($decoded) || empty($decoded['results'])) {
    echo json_encode(['success' => false, 'error' => 'Error ejecutando el generador de PDF: ' . substr((string) $out, 0, 1000)]);
    exit;
}

$archivos = [];
$errores  = [];
foreach ($decoded['results'] as $r) {
    $i = $r['id'];
    if (!empty($r['success']) && file_exists($jobs[$i]['pdfPath'])) {
        $archivos[] = ['token' => $meta[$i]['token'], 'filename' => $meta[$i]['filename']];
    } else {
        $errores[] = ['filename' => $meta[$i]['filename'], 'error' => $r['error'] ?? 'desconocido'];
    }
}

echo json_encode(['success' => true, 'total' => count($registros), 'archivos' => $archivos, 'errores' => $errores]);
