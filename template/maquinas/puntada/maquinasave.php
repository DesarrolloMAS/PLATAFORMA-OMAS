<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../../conection.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once '../debug_log.php';
require_once '../../sesion.php';

class CustomPDF extends TCPDF {
    public function Header() {
        if ($this->getPage() == 1) {
        $logo = '/var/www/fmt/img/logo_empresa.jpeg'; 
        $fecha = date('d/m/Y');

        $html = '
        <table border="1" cellpadding="4" cellspacing="0" width="190">
            <tr>
                <td rowspan="3" width="183" align="center">
                    <img src="' . $logo . '" height="100">
                </td>
                <td rowspan="3" width="183" align="center">
                    <strong>PPR de Metrología</strong><br>
                    <span style="font-size:10px;">"Verificación de estado de Equipos De Medición"</span>
                </td>
                <td rowspan="3" width="183">
                    <strong>Código:</strong> OPE-ME-FO-002<br><br>
                    <strong>Fecha:</strong> ' . $fecha . '<br><br>
                    <strong>Versión:</strong> 4<br>
                    <strong>Página:</strong> ' . $this->getAliasNumPage() . ' de ' . $this->getAliasNbPages() . '
                </td>s
            </tr>
            <tr></tr><tr></tr>
        </table>';

        $this->SetY(5);
        $this->writeHTMLCell(0, 0, 10, 5, $html, 0, 1, false, true, 'L', true);
        $this->Ln(5);
    }
}
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $accion = $_POST['accion'] ?? '';
    escribirLog("Acción recibida: $accion");

    $codigo_maquina = $_POST['codigo_maquina'] ?? 'sin_codigo';
    $nombre_maquina = $_POST['nombre_maquina'] ?? 'desconocida';
    $formato = $_POST['formato'] ?? 'formato';
    $zona = $_POST['zona'] ?? 'zona';
    $fecha_actual = date("Y-m-d");
    $usuario = $_SESSION['nombre'] ?? 'anonimo';

    // Carpeta fija de destino según estructura anterior
    $ruta_maquina = "/var/www/fmt/archivos/generados/verificaciones/puntada/{$codigo_maquina}-{$nombre_maquina}/";

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

    // Crear el PDF
    $pdf = new CustomPDF();
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    $pdf->AddPage();

    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetXY(10, 43);
    $pdf->Cell(0, 10, "Código de Máquina: $codigo_maquina", 0, 1, 'L');
    $pdf->SetXY(100, 55);
    $pdf->Cell(0, 10, "Código de Orden de Trabajo: ", 0, 1, 'L');
    $pdf->SetXY(100, 65);
    $pdf->Cell(0, 10, "Fecha de La Revisión: $fecha_actual", 0, 1, 'L');
    $pdf->SetXY(100, 75);
    $pdf->Cell(0, 10, "Tecnico Encargado: $usuario", 0, 1, 'L');

    $directorio_imagenes = '/var/www/fmt/img/MAQUINAS/Puntada/';
    $imagen_path = '/var/www/fmt/img/default.jpeg';
    foreach (['jpeg', 'jpg', 'png'] as $ext) {
        $img_try = $directorio_imagenes . strtolower($codigo_maquina) . '.' . $ext;
        if (file_exists($img_try)) {
            $imagen_path = $img_try;
            break;
        }
    }
    $pdf->Image($imagen_path, 15, $pdf->GetY(), 80, 50);
    $pdf->Ln(55);

    $pdf->SetFillColor(0, 51, 102);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->Cell(80, 10, 'Descripción', 1, 0, 'C', true);
    $pdf->Cell(100, 10, 'Valor', 1, 1, 'C', true);

    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor(0, 0, 0);
    foreach ($_POST as $campo => $valor) {
        if (!in_array($campo, ['codigo_maquina', 'nombre_maquina', 'observaciones'])) {
            $pdf->Cell(80, 8, ucwords(str_replace("_", " ", $campo)), 1, 0, 'L');
            $pdf->Cell(100, 8, $valor, 1, 1, 'L');
        }
    }

    if (!empty($_POST['observaciones'])) {
        $pdf->Ln(5);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->SetFillColor(0, 51, 102);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(180, 10, 'Observaciones', 1, 1, 'C', true);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->MultiCell(180, 10, $_POST['observaciones'], 1, 'L');
    }

    $pdf->Output($ruta_pdf, 'F');
    escribirLog("✅ PDF generado: $ruta_pdf");

    // Guardar JSON de respaldo junto al PDF
    $datos_formulario = $_POST;
    $datos_formulario['usuario'] = $usuario;
    $datos_formulario['archivo_pdf'] = $nombre_archivo . ".pdf";
    file_put_contents($ruta_json, json_encode($datos_formulario, JSON_PRETTY_PRINT));
    escribirLog("📂 JSON para edición guardado en: $ruta_json");

    header("Location: ../maquinas_menu.php");
    exit;
} else {
    die("❌ No se recibieron datos del formulario.");
}
