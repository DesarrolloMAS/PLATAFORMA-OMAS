<?php
require '../../conection.php';
require '../../sesion.php';
verificarAutenticacion();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "<script>alert('Método no permitido'); history.back();</script>";
    exit;
}

$sede = $_SESSION['sede'];

// Validaciones para prevenir guardados vacíos (Safety check)
$producto = trim($_POST['producto'] ?? '');
$cantidad = trim($_POST['cantidad_nc'] ?? '');
if ($producto === '' || $cantidad === '') {
    echo "<script>alert('Falta información crítica (Producto o Cantidad). Reporte rechazado.'); history.back();</script>";
    exit;
}

$fecha_reporte = $_POST['fecha_reporte'] ?? date('Y-m-d');
$mes = date('Y-m', strtotime($fecha_reporte));

// Carpeta de destino segmentada por Sede
$base_dir = "../../../archivos/generados/PNC/" . $sede . "/";
if (!is_dir($base_dir)) {
    mkdir($base_dir, 0777, true);
}

$json_file = $base_dir . $mes . ".json";

$nuevo_registro = [
    "id" => uniqid("pnc_"),
    "sede" => $sede,
    "quien_reporta" => $_POST['quien_reporta'] ?? '',
    "fecha_reporte" => $fecha_reporte,
    "producto" => $producto,
    "numero_lote" => trim($_POST['numero_lote'] ?? ''),
    "cantidad_nc" => floatval($cantidad),
    "descripcion_evento" => trim($_POST['descripcion_evento'] ?? ''),
    "verifica_identificacion" => trim($_POST['verifica_identificacion'] ?? ''),
    "correccion_destino" => trim($_POST['correccion_destino'] ?? ''),
    "num_documento" => trim($_POST['num_documento'] ?? ''),
    "responsable_correccion" => trim($_POST['responsable_correccion'] ?? ''),
    "fecha_correccion" => trim($_POST['fecha_correccion'] ?? ''),
    "created_at" => date('Y-m-d H:i:s')
];

$datos_existentes = [];
if (file_exists($json_file)) {
    $contenido = file_get_contents($json_file);
    $datos_existentes = json_decode($contenido, true) ?: [];
}

$datos_existentes[] = $nuevo_registro;

if (file_put_contents($json_file, json_encode($datos_existentes, JSON_PRETTY_PRINT)) === false) {
    echo "<script>alert('Error al guardar el archivo JSON persistente.'); history.back();</script>";
    exit;
}

echo "<script>
    alert('Registro de Producto No Conforme (PNC) almacenado con éxito.');
    window.location.href = '../../menu_adm_calidad.html';
</script>";
?>
