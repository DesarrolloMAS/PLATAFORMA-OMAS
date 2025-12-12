<?php
function val($name, $default = '') {
    return htmlspecialchars($_POST[$name] ?? $_GET[$name] ?? $default);
}
$codigo_maquina = val('codigo_maquina');
$nombre_maquina = val('nombre_maquina');
$fecha = val('fecha', date('d/m/Y'));
$tecnico = val('usuario');
$tecnico_correccion = val('tecnico_correccion');

// Estado y funcionamiento (nombres exactos del JSON)

$estado_general = val('estado_general');
$codigo_orden = val('codigo_orden');
$acople = val('Acople');
$soporte = val('Soporte');
$manija = val('Manija');
$estructura = val('Estructura');
$tornilleria = val('Tornilleria_Completa_y_Ajustada1');
$observaciones = val('Observaciones');

// Imagen de la máquina y logos
$imagen_maquina = "/fmt/img/MAQUINAS/Otros/equipo_bog.jpeg";
$logo_empresa = "/fmt/img/logo_empresa.jpeg";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Verificación de Imanes</title>
    <link rel="stylesheet" href="../iman/creador_html.css">
</head>
<body>
    <div class="header-mas">
        <img src="<?= $logo_empresa ?>" alt="mas logo" class="logo-mas">
        <div class="slogan">somos más que harina</div>
    </div>
    <div class="titulo">VERIFICACIÓN DE IMAN</div>
    <div class="datos-maquina">
        <span><strong>Código de Máquina:</strong> <?= $codigo_maquina ?></span>
        <span><strong>Nombre:</strong> <?= $nombre_maquina ?></span>
        <span><?= $fecha ?></span>
        <?php if (!empty($codigo_orden)): ?>
        <div class="orden-trabajo">
            <strong>Código de Orden de Trabajo:</strong> <?= htmlspecialchars($codigo_orden) ?>
        </div><?php endif; ?>
    </div>
    <div class="tecnico">
        <strong>Técnico:</strong> <?= $tecnico ?>
    </div>
    <?php if (!empty($tecnico_correccion)): ?>
    <div class="tecnico-correccion" style="margin-bottom:18px;">
        <strong>Técnico que revisa:</strong> <?= htmlspecialchars($tecnico_correccion) ?>
    </div>
    <?php endif; ?>
    <div class="img-maquina-container">
        <img class="img-maquina" src="<?= $imagen_maquina ?>" alt="Imagen del equipo de laboratorio">
    </div>

    <div class="seccion-titulo">Verificación de Estado y Funcionamiento</div>
    <div class="card-tabla">
<table class="tabla-verificacion">
    <tr>
        <th>Chequeo</th>
        <th>Resultado</th>
    </tr>
    <tr><td>Estado general de la máquina</td><td><?= $estado_general ?></td></tr>
    <tr><td>Acople</td><td><?= $acople ?></td></tr>
    <tr><td>Soporte</td><td><?= $soporte ?></td></tr>
    <tr><td>Manija</td><td><?= $manija ?></td></tr>
    <tr><td>Estructura</td><td><?= $estructura ?></td></tr>
    <tr><td>Tornillería completa y ajustada</td><td><?= $tornilleria ?></td></tr>
</table>
    </div>

    <div class="seccion-titulo">Observaciones</div>
    <div class="observaciones"><?= nl2br($observaciones) ?></div>
</body>
</html>