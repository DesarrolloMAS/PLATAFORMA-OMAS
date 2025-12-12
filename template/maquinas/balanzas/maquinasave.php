<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../../conection.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once '../debug_log.php';
require_once '../../sesion.php';

// --- SOLO GENERA EL PDF USANDO EL HTML DE creador_html.php ---

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $accion = $_POST['accion'] ?? '';
    escribirLog("Acción recibida: $accion");

    $codigo_maquina = $_POST['codigo_maquina'] ?? 'sin_codigo';
    $nombre_maquina = $_POST['nombre_maquina'] ?? 'desconocida';
    $zona = $_POST['zona'] ?? 'Balanzas';
    $formato = $_POST['formato'] ?? 'balanzas';
    $fecha_actual = date("Y-m-d");
    $usuario = $_SESSION['nombre'] ?? 'anonimo';

    // Carpeta de destino parametrizada (igual que equipos y flowbalancer)
    $ruta_maquina = "/var/www/fmt/archivos/generados/verificaciones/{$zona}/{$nombre_maquina}/";

    // Crear carpeta si no existe
    if (!file_exists($ruta_maquina)) mkdir($ruta_maquina, 0777, true);

    // Nombre base de archivos
    $nombre_archivo = "{$nombre_maquina}_{$codigo_maquina}_{$fecha_actual}";
    $ruta_pdf = $ruta_maquina . $nombre_archivo . ".pdf";
    $ruta_json = $ruta_maquina . $nombre_archivo . ".json";

    if ($accion === 'guardar') {
        file_put_contents($ruta_json, json_encode($_POST, JSON_PRETTY_PRINT));
        escribirLog("💾 JSON guardado en: $ruta_json");
        exit;
    }

    // --- Generar el HTML usando creador_html.php ---
    ob_start();
    include 'creador_html.php';
    $html = ob_get_clean();

    // --- Crear el PDF usando mPDF ---
    $logo_empresa2 = '/var/www/fmt/img/logo_empresa.jpeg';
    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'margin_top' => 10,
        'margin_bottom' => 10,
        'margin_left' => 10,
        'margin_right' => 10
    ]);
    $mpdf->SetWatermarkImage($logo_empresa2, 0.1, [150,150]);
    $mpdf->showWatermarkImage = true;
    $mpdf->WriteHTML($html);
    $mpdf->Output($ruta_pdf, \Mpdf\Output\Destination::FILE);

    escribirLog("✅ PDF generado: $ruta_pdf");

    // Guardar JSON de respaldo junto al PDF
    $datos_formulario = $_POST;
    $datos_formulario['usuario'] = $usuario;
    $datos_formulario['tecnico'] = $usuario; // Guarda el técnico inicial
    $datos_formulario['archivo_pdf'] = $nombre_archivo . ".pdf";
    file_put_contents($ruta_json, json_encode($datos_formulario, JSON_PRETTY_PRINT));
    escribirLog("📂 JSON para edición guardado en: $ruta_json");

    header("Location: ../maquinas_menu.php");
    exit;
} else {
    die("❌ No se recibieron datos del formulario.");
}