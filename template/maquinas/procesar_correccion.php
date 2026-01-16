<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../conection.php';
require_once '/var/www/fmt/vendor/autoload.php';
require_once '../sesion.php';

function escribirLog($mensaje) {
    $logDir = "C:/xampp/htdocs/fmt/debug/";
    if (!file_exists($logDir)) mkdir($logDir, 0777, true);
    file_put_contents($logDir . "error_log.txt", date("Y-m-d H:i:s") . " | $mensaje\n", FILE_APPEND);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Parámetros generales
    $zona = $_POST['zona'] ?? '';
    $maquina = $_POST['maquina'] ?? '';
    $tipo_maquina = $_POST['tipo_maquina'] ?? ''; // Ej: Equipos, Camionera, Bascula
    $fecha = $_POST['fecha'] ?? date('Y-m-d');
    $codigo_maquina = $_POST['codigo_maquina'] ?? '';
    $nombre_maquina = $_POST['nombre_maquina'] ?? '';
    $usuario = $_SESSION['nombre'] ?? 'anonimo';

    //VALIDAR CODIGO DE ORDEN O DETENER EL PROCESO

    if (empty($_POST['codigo_orden'])) {
        die("❌ Debe ingresar el Código de Orden de Trabajo para continuar.");
    }

    // Nombre base de archivo
    $nombre_archivo = "{$codigo_maquina}-{$nombre_maquina}_{$fecha}";

    // Carpeta destino
    $ruta_maquina = "/var/www/fmt/archivos/generados/verificaciones/{$zona}/{$maquina}/";
    if (!file_exists($ruta_maquina)) mkdir($ruta_maquina, 0777, true);

    $ruta_pdf = $ruta_maquina . $nombre_archivo . ".pdf";
    $ruta_json = $ruta_maquina . $nombre_archivo . ".json";

    // MOVER ARCHIVOS ANTIGUOS A CARPETA DE RESPALDO

    $json_path_original = $_POST['json_path'] ?? '';
    $pdf_path_original = dirname($json_path_original) . '/' . ($_POST['archivo_pdf'] ?? '');
    

    // Determinar el creador_html.php correcto
    $carpetaCorrecciones = str_replace(
        '/archivos/generados/verificaciones/',
        '/archivos/correcciones/verificaciones/',
        dirname($json_path_original)
    );

    if (!file_exists($carpetaCorrecciones)) {
        mkdir($carpetaCorrecciones, 0777, true);
    }

    // Mover JSON original
    if (file_exists($json_path_original)) {
        rename($json_path_original, $carpetaCorrecciones . '/' . basename($json_path_original));
    }
    // Mover PDF original
    if (file_exists($pdf_path_original)) {
        rename($pdf_path_original, $carpetaCorrecciones . '/' . basename($pdf_path_original));
    }

    // Actualizar JSON
    file_put_contents($ruta_json, json_encode($_POST, JSON_PRETTY_PRINT));
    escribirLog("📝 JSON actualizado en: $ruta_json");

    $creador_html_path = __DIR__ . "/{$tipo_maquina}/creador_html.php";
    $css_path = __DIR__ . "/{$tipo_maquina}/creador_html.css";
    if (!file_exists($creador_html_path)) {
        die("❌ No existe el creador HTML para el tipo de máquina: $tipo_maquina");
    }

    // Renderizar HTML
    ob_start();
    $_POST = json_decode(json_encode($_POST), true); // Asegura que $_POST sea un array asociativo
    include $creador_html_path;
    $html = ob_get_clean();

    // Generar PDF
    $logo_empresa = "/var/www/fmt/img/logo_empresa.jpeg";
    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'margin_top' => 10,
        'margin_bottom' => 10,
        'margin_left' => 10,
        'margin_right' => 10
    ]);
    $mpdf->SetWatermarkImage($logo_empresa, 0.1, [150,150]);
    $mpdf->showWatermarkImage = true;

    if ($css_path && file_exists($css_path)) {
        $stylesheet = file_get_contents($css_path);
        $mpdf->WriteHTML($stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS);
    }
    $mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
    $mpdf->showWatermarkImage = false;
    $mpdf->Output($ruta_pdf, \Mpdf\Output\Destination::FILE);

    escribirLog("✅ PDF generado: $ruta_pdf");

    // Actualiza el campo archivo_pdf en el JSON
    $datos_json = json_decode(file_get_contents($ruta_json), true);
    $datos_json['archivo_pdf'] = $nombre_archivo . ".pdf";
    file_put_contents($ruta_json, json_encode($datos_json, JSON_PRETTY_PRINT));

    ob_clean();
    header("Location: revision_maquinas.php");
    exit;
} else {
    die("❌ No se recibieron datos para la corrección.");
}