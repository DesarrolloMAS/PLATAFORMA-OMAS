<?php
function val($name, $default = '') {
    return htmlspecialchars($_POST[$name] ?? $_GET[$name] ?? $default);
}
$codigo_maquina = val('codigo_maquina');
$nombre_maquina = val('nombre_maquina');
$fecha = val('fecha', date('d/m/Y'));
$tecnico = val('usuario') ?: val('tecnico');
$tecnico_correccion = val('tecnico_correccion');

// Estado y funcionamiento (nombres exactos del JSON)
$estado_general = val('estado_general');
$puntada = val('Puntada');
$lubricacion = val('Lubricacion');
$observaciones = val('Observaciones');
$formato = val('formato');
$archivo_pdf = val('archivo_pdf');

// Imagen de la máquina y logos
$imagen_maquina = "/fmt/img/MAQUINAS/Puntada/puntada_bog.jpeg";
$logo_empresa = "/fmt/img/logo_empresa.jpeg";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Verificación de Máquina de Coser</title>
    <link rel="stylesheet" href="../Equipos/creador_html.css">
</head>
<body>
    <div class="header-mas">
        <img src="<?= $logo_empresa ?>" alt="mas logo" class="logo-mas">
        <div class="slogan">somos más que harina</div>
    </div>
    <div class="titulo">VERIFICACIÓN DE MÁQUINAS DE COSER</div>
    <div class="datos-maquina">
        <span><strong>Código de Máquina:</strong> <?= $codigo_maquina ?></span>
        <span><strong>Nombre:</strong> <?= $nombre_maquina ?></span>
        <span><strong>Fecha:</strong><?= $fecha ?></span>
    </div>
    <div class="tecnico">
        <strong>Usuario responsable:</strong> <?= $tecnico ?>
        <strong>responsable de revision:</strong> <?= $tecnico_correccion ?>
    </div>
    <div class="img-maquina-container">
        <img class="img-maquina" src="<?= $imagen_maquina ?>" alt="Imagen del equipo de laboratorio">
    </div>
    <div class="seccion-titulo">Chequeo de Estado y Funcionamiento</div>
    <div class="card-tabla">
    <table class="tabla-verificacion">
        <tr>
            <th>Chequeo</th>
            <th>Resultado</th>
        </tr>
        <tr><td>Estado general</td><td><?= $estado_general ?></td></tr>
        <tr><td>Puntada</td><td><?= $puntada ?></td></tr>
        <tr><td>Lubricación</td><td><?= $lubricacion ?></td></tr>
    </table>
    </div>
    <div class="seccion-titulo">Observaciones</div>
    <div class="observaciones"><?= nl2br($observaciones) ?></div>
</body>
</html>