<?php
$cmd = "node /var/www/fmt/template/generate_pdf_headless.js 'http://localhost/template/molienda_v2/plantilla_diaria.php?fecha=2026-05-04&sede=ZC' '/var/www/fmt/archivos/generados/molienda/ZC/test_curl.pdf' 'test' 'localhost' 'true' 2>&1";
$output = shell_exec($cmd);
echo "Output: " . $output;
if (file_exists('/var/www/fmt/archivos/generados/molienda/ZC/test_curl.pdf')) {
    echo "\nSuccess! PDF created.";
    unlink('/var/www/fmt/archivos/generados/molienda/ZC/test_curl.pdf');
} else {
    echo "\nFailed to create PDF.";
}
