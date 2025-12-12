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
$superficie_libre_de_huecos = val('Estado_de_limpieza');
$bordes_libres_de_obstrucciones = val('Bordes_libres_de_Obstrucciones');
$topes_con_holgura = val('Topes_con_Holgura');
$tap_as_de_acceso = val('Tapas_de_Acceso');
$tarjeta_sumatoria_libre_de_humedad = val('Tarjeta_Sumatoria_libre_de_Humedad');
$cables_de_senal = val('Cables_de_señal');
$tornilleria_ajustada = val('Tornilleria_Ajustada');
$cojinetes_de_celda_sin_desgaste = val('Cojinetes_de_celda_sin_desgaste');
$carcamo_limpio = val('Carcamo_Limpio');
$codigo_orden = val('codigo_orden', '');

// Calibración - Pesas patrón (nombres exactos del JSON)
$patron_utilizado = val('Patron_Utilizado');
$celda = [];
$diferencia = [];
for ($i = 1; $i <= 8; $i++) {
    $celda[$i] = val("Celda_$i");
    $diferencia[$i] = val("Diferencia_$i");
}
$verificacion_masas = val('Verificacion_De_MASAS');

// Calibración - Vehículo (nombres exactos del JSON)
$peso_utilizado_vehiculo = val('Peso_Utilizado_Vehiculo');
$vehiculo = [];
$diferenciaV = [];
$vehiculo[1] = val('Frente');
$diferenciaV[1] = val('Diferencia_V1');
$vehiculo[2] = val('Centro_1');
$diferenciaV[2] = val('Diferencia_V2');
$vehiculo[3] = val('Atras');
$diferenciaV[3] = val('Diferencia_V3');
$vehiculo[4] = val('Centro_2');
$diferenciaV[4] = val('Diferencia_V4');
$cumplimiento_vehiculo = val('cumplimiento_vehiculo');

// Observaciones
$observaciones = val('Observaciones');

// Imagen de la máquina y logos
$imagen_maquina = "/fmt/img/MAQUINAS/Camionera/Camionera_Bog.jpeg";
$logo_empresa = "/fmt/img/logo_empresa.jpeg";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Verificación Báscula Camionera</title>
    <link rel="stylesheet" href="../camionera/creador_html.css">
</head>
<body>
    <div class="header-mas">
        <img src="<?= $logo_empresa ?>" alt="mas logo" class="logo-mas">
        <div class="slogan">somos más que harina</div>
    </div>
    <div class="titulo">VERIFICACIÓN DE BÁSCULA CAMIONERA</div>
    <div class="datos-maquina">
        <span><strong>Código de Orden de trabajo:</strong> <?= $codigo_orden ?></span>
        <span><strong>Nombre:</strong> <?= $nombre_maquina ?></span>
        <span><?= $fecha ?></span>
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
        <img class="img-maquina" src="<?= $imagen_maquina ?>" alt="Imagen de la báscula camionera">
    </div>

    <div class="seccion-titulo">Verificación de Estado y Funcionamiento</div>
    <div class="card-tabla">
    <table class="tabla-verificacion">
        <tr>
            <th>Chequeo</th>
            <th>Resultado</th>
        </tr>
        <tr><td>Estado general de la máquina</td><td><?= $estado_general ?></td></tr>
        <tr><td>Superficie Libre de Huecos</td><td><?= $superficie_libre_de_huecos ?></td></tr>
        <tr><td>Bordes libres de Obstrucciones</td><td><?= $bordes_libres_de_obstrucciones ?></td></tr>
        <tr><td>Topes con Holgura</td><td><?= $topes_con_holgura ?></td></tr>
        <tr><td>Tapas de Acceso</td><td><?= $tap_as_de_acceso ?></td></tr>
        <tr><td>Tarjeta Sumatoria libre de Humedad</td><td><?= $tarjeta_sumatoria_libre_de_humedad ?></td></tr>
        <tr><td>Cables de señal</td><td><?= $cables_de_senal ?></td></tr>
        <tr><td>Tornillería Ajustada</td><td><?= $tornilleria_ajustada ?></td></tr>
        <tr><td>Cojinetes de celda sin desgaste</td><td><?= $cojinetes_de_celda_sin_desgaste ?></td></tr>
        <tr><td>Carcamo Limpio</td><td><?= $carcamo_limpio ?></td></tr>
    </table>
    </div>

    <div class="seccion-titulo">Verificación de Calibración</div>
    <div class="card-tabla">
    <table class="tabla-calibracion">
        <tr>
            <th colspan="4" style="text-align:center;">Pesas Patrón</th>
        </tr>
        <tr>
            <th>Peso Utilizado (Kg)</th>
            <th>Celda</th>
            <th>Indicador de peso</th>
            <th>Diferencia</th>
        </tr>
        <?php for ($i = 1; $i <= 8; $i++): ?>
        <tr>
            <?php if ($i == 1): ?>
                <td rowspan="8" class="td-pequeno"><?= $patron_utilizado ?></td>
            <?php endif; ?>
            <td>Celda <?= $i ?></td>
            <td class="td-pequeno"><?= $celda[$i] ?></td>
            <td class="td-pequeno"><?= $diferencia[$i] ?></td>
        </tr>
        <?php endfor; ?>
        <tr>
            <td colspan="3"><strong>¿Cumple la Tolerancia?</strong></td>
            <td class="td-pequeno"><?= $verificacion_masas ?></td>
        </tr>
    </table>
    </div>

    <div class="seccion-titulo">Verificación Vehículo</div>
    <div class="card-tabla">
    <table class="tabla-calibracion">
        <tr>
            <th colspan="4" style="text-align:center;">Vehículo</th>
        </tr>
        <tr>
            <th>Peso Utilizado Vehículo (Kg)</th>
            <th>Posición</th>
            <th>Indicador de peso</th>
            <th>Diferencia</th>
        </tr>
        <?php
        $posiciones = [
            1 => 'Frente',
            2 => 'Centro 1',
            3 => 'Atras',
            4 => 'Centro 2'
        ];
        for ($i = 1; $i <= 4; $i++): ?>
        <tr>
            <?php if ($i == 1): ?>
                <td rowspan="4" class="td-pequeno"><?= $peso_utilizado_vehiculo ?></td>
            <?php endif; ?>
            <td><?= $posiciones[$i] ?></td>
            <td class="td-pequeno"><?= $vehiculo[$i] ?></td>
            <td class="td-pequeno"><?= $diferenciaV[$i] ?></td>
        </tr>
        <?php endfor; ?>
        <tr>
            <td colspan="3"><strong>¿Cumple la Tolerancia?</strong></td>
            <td class="td-pequeno"><?= $cumplimiento_vehiculo ?></td>
        </tr>
    </table>
    </div>

    <div class="seccion-titulo">Observaciones</div>
    <div class="observaciones"><?= nl2br($observaciones) ?></div>
</body>
</html>