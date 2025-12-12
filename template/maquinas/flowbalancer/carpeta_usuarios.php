<?php
session_start();
if (!isset($_SESSION['id_usuario'])){
    echo "<script>
        alert('⚠️ No hay un usuario autenticado. No se puede guardar el formulario.');
        window.location.href = '../../../index.php'; // o donde quieras redirigir
    </script>";
    exit;
}
$formato= $_POST['formato']?? 'Formato desconocido';
$usuario= $_SESSION['nombre']?? 'Usuario No Definido';
$datos_post= $_POST;
function guardarFormularioComoJson($usuario, $formato, $datos_post) {
    $ruta_base = "C:/xampp/htdocs/fmt/archivos/formularios_guardados/";
    $carpeta_usuario = $ruta_base . $usuario . "/";

    if (!file_exists($carpeta_usuario)) {
        mkdir($carpeta_usuario, 0777, true);
    }

    $fecha_actual = date("Y-m-d");
    
    // Limpiar el nombre de la máquina (opcional pero recomendable)

    $nombre_archivo = "{$formato}_{$fecha_actual}.json";
    $ruta_final = $carpeta_usuario . $nombre_archivo;

    $contenido = json_encode($datos_post, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    file_put_contents($ruta_final, $contenido);

    return $ruta_final;
}
// 5. Ejecutar
guardarFormularioComoJson($usuario, $formato, $datos_post);

// 6. Confirmar al usuario
echo "<script>
    alert('✅ Formulario guardado correctamente.');
    window.location.href = '../maquinas_menu.php'; // Redirige donde quieras
</script>";
exit;
?>
