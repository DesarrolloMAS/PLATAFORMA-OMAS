<?php
require __DIR__ . '/../../vendor/autoload.php';
require '../sesion.php';
require '../conection.php';
$sede = $_SESSION['sede'];
if($sede === 'ZC'){
    $carpetaDestino = 'procesos/';
}else{
    $carpetaDestino = 'procesos_zs/';
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Crear la carpeta si no existe
    if (!is_dir($carpetaDestino)) {
        mkdir($carpetaDestino, 0777, true);
    }

    // Recoger los datos del formulario
    $datos = [
        'fecha' => $_POST['fecha'] ?? '',
        'responsable' => $_POST['responsable'] ?? '',
        'producto' => $_POST['producto'] ?? '',
        'lote' => $_POST['lote'] ?? '',
        'hora' => $_POST['hora'] ?? '',
        'cantidad' => $_POST['cantidad'] ?? '',
        'motivo' => $_POST['motivo'] ?? '',
        'proceso' => $_POST['proceso'] ?? '',
    ];
    $producto = $_POST['producto'] ?? '';
    $producto = preg_replace('/[^a-zA-Z0-9_-]/', '_', $producto);

    // Nombre base del archivo JSON
    $fechaHoy = date('Ymd');
    $nombreBase = $producto . '_' . $fechaHoy;
    $nombreArchivo = $nombreBase . '.json';
    $rutaArchivo = $carpetaDestino . $nombreArchivo;

    // Si el archivo existe, agregar un sufijo incremental
    $contador = 1;
    while (file_exists($rutaArchivo)) {
        $nombreArchivo = $nombreBase . '_' . $contador . '.json';
        $rutaArchivo = $carpetaDestino . $nombreArchivo;
        $contador++;
    }

    // Guardar los datos en el archivo JSON
    try {
        file_put_contents($rutaArchivo, json_encode($datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        header('Location: reprocesos_menu.php');
    } catch (Exception $e) {
        echo "Error al guardar los datos: " . $e->getMessage();
    }
} else {
    echo "Método no permitido.";
}
?>