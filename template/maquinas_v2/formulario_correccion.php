<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../sesion.php';
verificarAutenticacion();

function sanear_ruta($valor) {
    return preg_replace('/_+/', '_', preg_replace('/[^A-Za-z0-9_\-]/', '_', trim($valor)));
}

$tipo   = $_GET['tipo']    ?? '';
$grupo  = $_GET['maquina'] ?? '';
$codigo = $_GET['codigo']  ?? '';
$id     = $_GET['id']      ?? '';

$archivo = __DIR__ . "/../../archivos/generados/maquinas_v2/" . sanear_ruta($tipo) . "/" . sanear_ruta($grupo) . "/" . sanear_ruta($codigo) . ".json";

if (!file_exists($archivo)) {
    die("❌ No se encontró el historial de esta máquina.");
}

$registros = json_decode(file_get_contents($archivo), true) ?: [];
$registro = null;
foreach ($registros as $r) {
    if (($r['id_registro'] ?? '') === $id) { $registro = $r; break; }
}
if (!$registro) {
    die("❌ No se encontró el registro a corregir.");
}

$datos_precarga = $registro['datos'] ?? [];
unset($datos_precarga['tipo_maquina'], $datos_precarga['codigo_maquina'], $datos_precarga['nombre_maquina']);

$payload = [
    'corrige_id' => $id,
    'datos'      => $datos_precarga,
];

$url_formulario = 'formulario.html?tipo=' . urlencode($tipo) . '&codigo=' . urlencode($codigo) . '&maquina=' . urlencode($grupo) . '&corrige_id=' . urlencode($id);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Redirigiendo a corrección…</title>
</head>
<body>
<p>Cargando corrección…</p>
<script>
sessionStorage.setItem('maquinas_v2_precarga', JSON.stringify(<?= json_encode($payload, JSON_UNESCAPED_UNICODE) ?>));
window.location.replace(<?= json_encode($url_formulario) ?>);
</script>
</body>
</html>
