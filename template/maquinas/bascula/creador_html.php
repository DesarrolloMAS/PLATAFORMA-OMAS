<?php
// Recibe datos de POST y GET
function val($name, $default = '') {
    return htmlspecialchars($_POST[$name] ?? $_GET[$name] ?? $default);
}
$codigo_orden = val('codigo_orden');
$codigo_maquina = val('codigo_maquina');
$nombre_maquina = val('nombre_maquina'); // Solo el nombre corto, ej: Balanza_Bogota
$fecha = val('fecha', date('d/m/Y'));
$tecnico = val('usuario') ?: val('tecnico');
$estado_general = val('estado_general');
$estado_limpieza = val('estado_limpieza');
$estabilidad = val('estabilidad');
$escala = val('escala');
$cargador = val('cargador');
$display = val('display');
$cables_senal = val('cables_senal');
$stickers = val('stickers');
$verificacion_estado = val('verificacion_estado');
$peso_utilizado = val('peso_utilizado');
$verificacion_masas = val('verificacion_masas');
$observaciones = val('observaciones');
$tecnico = val('usuario') ?: val('tecnico');
$tecnico_correccion = val('tecnico_correccion');

// Puntos de calibración
$peso_indicador = [];
$diferencia = [];
for ($i = 1; $i <= 6; $i++) {
    $peso_indicador[$i] = val("peso_indicador_p$i");
    $diferencia[$i] = val("diferencia_p$i");
}

// Imagen de la máquina y logos
$imagen_maquina = "/fmt/img/MAQUINAS/Balanzas/Balanza_Bogota.jpeg";
$logo_empresa = "/fmt/img/logo_empresa.jpeg";
$logo_empresa2 = "/fmt/img/logo_empresa_2.jpeg";
$img_calibracion_lateral = "/fmt/img/P1.jpeg"; // Cambia aquí la ruta de la imagen lateral

// Depuración: muestra el nombre recibido y el nombre armado en el HTML como comentario
$nombre_carpeta = $codigo_maquina . '-' . $nombre_maquina;
echo "<!-- nombre recibido: [$nombre_maquina] | codigo: [$codigo_maquina] | nombre_carpeta: [$nombre_carpeta] -->";

// Función para mostrar unidades de medida según el nombre completo de la carpeta (adaptada para balanzas)
function unidades_balanzas($nombre_carpeta) {
    $nombre = strtolower(trim($nombre_carpeta));
    if ($nombre === strtolower('SUPER SS 15KG_EMPPASBAL03-Balanzas_ZS')) {
        $unidades = ['Escala de Lectura 5g'];
    } elseif ($nombre === strtolower('PROW SS_LABPASBAL02-Balanzas_ZS')) {
        $unidades = ['Escala de Lectura 0,5g'];
    } elseif ($nombre === strtolower('OHAUS15KG_PREBOGBAL02-Balanzas_ZC')) {
        $unidades = ['Escala de Lectura 0,5g'];
    } elseif ($nombre === strtolower('OHAUS6KG_MOLBOGBAL01-Balanzas_ZC')) {
        $unidades = ['Escala de Lectura 1g'];
    } elseif ($nombre === strtolower('OHAUS3KG_LABBOGBAL03-Balanzas_ZC')) {
        $unidades = ['Escala de Lectura 0.2g'];
    } elseif ($nombre === strtolower('OHAUS TROOPER SCALE_MICPASBAL01-Balanzas_ZS')) {
        $unidades = ['Escala de Lectura 0.1g'];
    } elseif ($nombre === strtolower('JAVAR3KG_LABPASBAL01-Balanzas_ZS')) {
        $unidades = ['Escala de Lectura 0.1g'];
    } elseif ($nombre === strtolower('FE 45C 30KG_EMPPASBAL02-Balanzas_ZS')) {
        $unidades = ['Escala de Lectura 10g'];
    } elseif ($nombre === strtolower('CAS 15KG_EMPPASBAL02-Balanzas_ZS')) {
        $unidades = ['Escala de Lectura 5g'];
    } elseif ($nombre === strtolower('BBG3KG_LABBOGBAL02-Balanzas_ZC')) {
        $unidades = ['Escala de Lectura 0.5g'];
    } elseif ($nombre === strtolower('ANALITICA220G_LABBOGBAL01-Balanzas_ZC')) {
        $unidades = ['Escala de Lectura 0.02g'];
    } else {
        $unidades = ['Unidad no definida'];
    }
    echo '<div class="unidades-medida" style="text-align:center; margin-bottom:18px;">';
    echo '<strong>Escala de lectura de esta balanza:</strong>';
    echo '<ul style="display:inline-block; text-align:left; margin: 8px auto 0 auto;">';
    foreach ($unidades as $unidad) {
        echo '<li>' . htmlspecialchars($unidad) . '</li>';
    }
    echo '</ul>';
    echo '</div>';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Verificación de Balanza</title>
    <link rel="stylesheet" href="creador_html.css">
</head>
<body>
    <div class="header-mas">
        <img src="<?= $logo_empresa ?>" alt="mas logo" class="logo-mas">
        <div class="slogan">somos más que harina</div>
    </div>
    <div class="titulo">VERIFICACIÓN DE BALANZA</div>
    <div class="datos-maquina">
        <?php if (!empty($codigo_orden)): ?>
        <div class="orden-trabajo">
            <strong>Código de Orden de Trabajo:</strong> <?= htmlspecialchars($codigo_orden) ?>
        </div>
        <?php endif; ?>
        <span><strong>Código de Máquina:</strong> <?= $codigo_maquina ?></span>
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
        <img class="img-maquina" src="<?= $imagen_maquina ?>" alt="Imagen de la balanza">
    </div>

    <div class="seccion-titulo">Verificación de Estado y Funcionamiento</div>
    <div class="card-tabla">
    <table class="tabla-verificacion">
        <tr>
            <th>Chequeo</th>
            <th>Resultado</th>
        </tr>
        <tr><td>Estado general de la balanza</td><td><?= $estado_general ?></td></tr>
        <tr><td>Estado de limpieza</td><td><?= $estado_limpieza ?></td></tr>
        <tr><td>Mesa y soportes estables</td><td><?= $estabilidad ?></td></tr>
        <tr><td>Escala de la lectura en cero</td><td><?= $escala ?></td></tr>
        <tr><td>Cargador</td><td><?= $cargador ?></td></tr>
        <tr><td>Estado del Display</td><td><?= $display ?></td></tr>
        <tr><td>Cables de señal</td><td><?= $cables_senal ?></td></tr>
        <tr><td>Stickers Mant. y Calibración</td><td><?= $stickers ?></td></tr>
        <tr><td>¿Cumple verificación de estado?</td><td><?= $verificacion_estado ?></td></tr>
    </table>
    </div>
    <div class="seccion-titulo">La diferencia entre la medición de los puntos de apoyo y el peso de la masa patrón debe ser de máximo +/- una vez la escala de lectura de cada balanza</div>
    <?php unidades_balanzas($nombre_carpeta); ?>
    <div class="seccion-titulo">Verificación de Calibración</div>
    <div class="card-tabla">
    <table class="tabla-calibracion">
        <tr>
            <th rowspan="8" class="col-imagen-calibracion">
                <img src="<?= $img_calibracion_lateral ?>" alt="Icono calibración" class="img-calibracion-lateral">
            </th>
            <th>Punto</th>
            <th>Peso Indicador</th>
            <th>Diferencia</th>
        </tr>
        <?php for ($i = 1; $i <= 6; $i++): ?>
        <tr>
            <td>P<?= $i ?></td>
            <td class="td-pequeno"><?= $peso_indicador[$i] ?></td>
            <td class="td-pequeno"><?= $diferencia[$i] ?></td>
        </tr>
        <?php endfor; ?>
        <tr>
            <td colspan="2"><strong>Peso utilizado</strong></td>
            <td class="td-pequeno"><?= $peso_utilizado ?></td>
        </tr>
        <tr>
            <td colspan="2"><strong>¿Cumple la Tolerancia?</strong></td>
            <td class="td-pequeno"><?= $verificacion_masas ?></td>
        </tr>
    </table>
    </div>

    <div class="seccion-titulo">Observaciones</div>
    <div class="observaciones"><?= nl2br($observaciones) ?></div>
</body>
</html>