<?php
session_start();

$usuario = $_SESSION['nombre'] ?? 'default';
$archivo = $_POST['archivo'] ?? '';
$ruta = "C:/xampp/htdocs/fmt/archivos/formularios_guardados/$usuario/$archivo";

if (!file_exists($ruta)) {
    die("❌ No se encontró el archivo.");
}

$datos = json_decode(file_get_contents($ruta), true);
$_SESSION['formulario_cargado'] = $datos;
$_SESSION['precargar_formulario'] = true;

$formato = $datos['formato'];
$mapa_formatos = require 'mapa_formatos.php';
if (!isset($mapa_formatos[$formato])) {
    die("❌ El formato '$formato' no está definido en el sistema.");
}
$ruta_destino = $mapa_formatos[$formato];
header("Location: $ruta_destino");
exit;
?>
