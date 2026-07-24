<?php
/**
 * descargar_pdf.php
 * ---------------------
 * Descarga el PDF de un único registro, generado con el mismo motor de
 * impresión (Puppeteer) que usa el Ctrl+P de visor.php — reemplaza el
 * antiguo flujo visor.php?download=1 basado en html2pdf.js.
 */
require_once '../sesion.php';
verificarAutenticacion();

$file = $_GET['file'] ?? '';
$id   = $_GET['id']   ?? '';
$sede = $_SESSION['sede'] ?? 'NA';

if (!$file || !$id) {
    http_response_code(400);
    die('Parámetros incompletos.');
}

$path = "../../archivos/generados/orden_mantenimiento/" . $sede . "/" . $file . ".json";
if (!file_exists($path)) {
    http_response_code(404);
    die('Periodo no encontrado.');
}

$registros = json_decode(file_get_contents($path), true) ?: [];
$registro = null;
foreach ($registros as $r) {
    if ($r['id'] === $id) { $registro = $r; break; }
}
if (!$registro) {
    http_response_code(404);
    die('Registro no encontrado.');
}

$numero_orden = $registro['datos']['numero_orden'] ?? $id;
$numero_orden_safe = preg_replace('/[^A-Za-z0-9_\-]/', '_', $numero_orden);

$host      = $_SERVER['HTTP_HOST'];
$sessionId = session_id();

$tempDir = "../../archivos/generados/orden_mantenimiento/TEMP_PDF/";
if (!is_dir($tempDir)) {
    mkdir($tempDir, 0777, true);
}

$token   = uniqid('orden_', true);
$pdfPath = $tempDir . $token . '.pdf';
$url     = "http://{$host}/template/orden_mantenimiento/visor.php?file=" . urlencode($file) . "&id=" . urlencode($id);

$manifestPath = $tempDir . 'manifest_' . uniqid('', true) . '.json';
file_put_contents($manifestPath, json_encode([
    'sessionId' => $sessionId,
    'host'      => $host,
    'jobs'      => [['id' => 0, 'url' => $url, 'pdfPath' => $pdfPath]],
]));

// CRÍTICO: liberar el bloqueo de sesión antes de invocar a Puppeteer,
// para que la petición interna de visor.php pueda leer la sesión.
session_write_close();

$cmd = "node " . escapeshellarg(__DIR__ . "/generar_pdf.js") . " --batch " . escapeshellarg($manifestPath) . " 2>&1";
$out = shell_exec($cmd);
@unlink($manifestPath);

$decoded = json_decode($out, true);
$exito = is_array($decoded) && !empty($decoded['results'][0]['success']) && file_exists($pdfPath);

if (!$exito) {
    $errorMsg = is_array($decoded) ? ($decoded['results'][0]['error'] ?? 'Error desconocido') : substr((string) $out, 0, 500);
    http_response_code(500);
    die('No se pudo generar el PDF: ' . htmlspecialchars($errorMsg));
}

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="Orden_Mantenimiento_' . $numero_orden_safe . '.pdf"');
header('Content-Length: ' . filesize($pdfPath));
readfile($pdfPath);
@unlink($pdfPath);
exit;
