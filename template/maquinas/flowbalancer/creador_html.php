<?php
function val($name, $default = '') {
    return htmlspecialchars($_POST[$name] ?? $_GET[$name] ?? $default);
}
$codigo_maquina = val('codigo_maquina');
$nombre_maquina = val('nombre_maquina');
$fecha = val('fecha', date('d/m/Y'));
$tecnico = val('usuario') ?: val('tecnico');
$tecnico_correccion = val('tecnico_correccion');
$codigo_orden = val('codigo_orden');

// Estado y funcionamiento (nombres exactos del JSON)
$estado_general = val('estado_general');
$estado_limpieza = val('Estado_de_limpieza');
$estabilidad = val('Estabilidad');
$escala = val('escala');
$cargador = val('cargador');
$display = val('Display');
$cables_senal = val('Cables_de_señal');
$membrana_presion = val('Membrana_de_Presion');
$verificacion_estado = val('Verificacion_De_Estado');

// Masas patrón
$patron_utilizado = val('Patron_Utilizado');
$con_masas_patron_wt = val('Con_masas_patron_WT');
$sin_masas_patron_wt = val('Sin_masas_patron_WT');
$con_masas_patron_zero = val('Con_masas_patron_ZERO');
$sin_masas_patron_zero = val('Sin_masas_patron_ZERO');
$verificacion_masas = val('Verificacion_De_MASAS');

$observaciones = val('Observaciones');

// Imagen de la máquina y logos
$imagen_maquina = "/fmt/img/MAQUINAS/flowbalancer/flowbalancer_Bogota.jpeg";
$logo_empresa = "/fmt/img/logo_empresa.jpeg";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Verificación Flowbalancer</title>
    <link rel="stylesheet" href="../flowbalancer/creador_html.css">
</head>
<body>
    <div class="header-mas">
        <img src="<?= $logo_empresa ?>" alt="mas logo" class="logo-mas">
        <div class="header-center">
            <div class="slogan">Manual de Gestión del Mantenimiento</div>
            <div class="slogan"><b>PPR Metrología</b></div>
            <div class="slogan">"Verificación mensual de básculas"</div>
        </div> 
        </div> 
    <div class="titulo">VERIFICACIÓN DE FLOWBALANCER</div>
    <div class="datos-maquina">
        <span><strong>Código de Máquina:</strong> <?= $codigo_maquina ?></span>
        <span><strong>Nombre:</strong> <?= $nombre_maquina ?></span>
        <span><?= $fecha ?></span>
        <?php if (!empty($codigo_orden)): ?>
        <div class="orden-trabajo">
            <strong>Código de Orden de Trabajo:</strong> <?= htmlspecialchars($codigo_orden) ?>
        </div>
        <?php endif; ?>
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
        <img class="img-maquina" src="<?= $imagen_maquina ?>" alt="Imagen del flowbalancer">
    </div>

    <div class="seccion-titulo">Verificación de Estado y Funcionamiento</div>
    <div class="card-tabla">
    <table class="tabla-verificacion">
        <tr>
            <th>Chequeo</th>
            <th>Resultado</th>
        </tr>
        <tr><td>Estado general de la máquina</td><td><?= $estado_general ?></td></tr>
        <tr><td>Estado de limpieza</td><td><?= $estado_limpieza ?></td></tr>
        <tr><td>Mesa y soportes estables</td><td><?= $estabilidad ?></td></tr>
        <tr><td>Escala de la lectura en cero</td><td><?= $escala ?></td></tr>
        <tr><td>Celda de carga</td><td><?= $cargador ?></td></tr>
        <tr><td>Estado del Display</td><td><?= $display ?></td></tr>
        <tr><td>Cables de señal</td><td><?= $cables_senal ?></td></tr>
        <tr><td>Membrana de Presión</td><td><?= $membrana_presion ?></td></tr>
        <tr><td>¿Cumple la verificación de estado?</td><td><?= $verificacion_estado ?></td></tr>
    </table>
    </div>

    <div class="seccion-titulo">Verificación de Masas Patrón</div>
    <div class="card-tabla">
    <table class="tabla-verificacion">
        <tr>
            <th>Chequeo</th>
            <th>Valor</th>
        </tr>
        <tr><td>Patrón utilizado en KG</td><td><?= $patron_utilizado ?></td></tr>
        <tr><td>Con masas patrón WT</td><td><?= $con_masas_patron_wt ?></td></tr>
        <tr><td>Sin masas patrón WT</td><td><?= $sin_masas_patron_wt ?></td></tr>
        <tr><td>Con masas patrón ZERO</td><td><?= $con_masas_patron_zero ?></td></tr>
        <tr><td>Sin masas patrón ZERO</td><td><?= $sin_masas_patron_zero ?></td></tr>
        <tr><td>¿Cumple la verificación de MASAS?</td><td><?= $verificacion_masas ?></td></tr>
    </table>
    </div>

    <div class="seccion-titulo">Observaciones</div>
    <div class="observaciones"><?= nl2br($observaciones) ?></div>
</body>
</html>