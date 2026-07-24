<?php
session_start();
$_SESSION['sede'] = 'ZC';
$sessionId = session_id() ?: 'test_session';
$host = 'localhost'; // Simulamos el host local
$modulo = 'molienda_v2';
$ruta = '/var/www/fmt/archivos/generados/molienda/ZC/2026-05.json';
$date = '2026-05-04';
$filename = basename($ruta);

echo "Ruta Original: $ruta\n";
$pdfPath = dirname($ruta) . "/Molienda_ZC_{$date}.pdf";
$urlToRender = "http://{$host}/template/molienda_v2/plantilla_diaria.php?fecha={$date}&sede=ZC";

echo "Intentando generar PDF en: $pdfPath\n";
echo "URL a renderizar: $urlToRender\n";

$cmd = "node " . escapeshellarg(__DIR__ . "/generate_pdf_headless.js") . " " . escapeshellarg($urlToRender) . " " . escapeshellarg($pdfPath) . " " . escapeshellarg($sessionId) . " " . escapeshellarg($host) . " true 2>&1";
echo "Comando a ejecutar: $cmd\n";

$output = shell_exec($cmd);
echo "Salida de Node: $output\n";

if (file_exists($pdfPath)) {
    echo "¡Éxito! El archivo PDF existe.\n";
} else {
    echo "¡Error! El archivo PDF no se creó.\n";
}
