
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/bodegasmenu.css">
    <title>MENU BODEGAS</title>
</head>
<body class="cuerpo">

    <h1 class="titulo_principal">BODEGAS DISPONIBLES</h1>
    <a href="../redireccion.php">Volver</a>

    <div class="contenedor-bodegas">
        <?php
        require __DIR__ . '/../../vendor/autoload.php';

        $carpetaDocumentos = __DIR__ . "/../../archivos/generados/excel_INS/";
        $plantillaBase = __DIR__ . "/../../archivos/formularios/formulario3.xlsx";

        $bodegas = [
            "BODEGA PNC" => "BodegaPNC.xlsx",
            "BODEGA MOGOLLA" => "BodegaMogolla.xlsx",
            "BODEGA 1" => "Bodega1.xlsx",
            "BODEGA 2" => "Bodega2.xlsx",
            "BODEGA 3" => "Bodega3.xlsx",
            "BODEGA 4" => "Bodega4.xlsx",
            "BODEGA PRE MEZCLAS" => "BodegaPreMezclas.xlsx",
            "BODEGA MEJORANTES" => "BodegaMejorantes.xlsx"
        ];
        $bodegasZS = [
            "PTFAMILIAR" => "PTfamiliarZS.xlsx",
            "PTESPECIAL" => "PTespecialZS.xlsx",
            "MATERIALES" => "materialesZS.xlsx",
            "PTINDUSTRIAL" => "PTindustrialZS.xlsx",
            "MICROINGREDIENTES" => "microingredientesZS.xlsx",
            "LABORATORIO" => "LaboratorioZS.xlsx"
        ];

        $mesActual = date('m'); // Mes actual con ceros a la izquierda

        foreach ($bodegas as $nombreBodega => $archivo) {
            $archivoConMes = str_replace('.xlsx', "_{$mesActual}.xlsx", $archivo);
            $imagen = "/img/" . str_replace(".xlsx", ".jpeg", $archivo);

            echo "<div class='card'>
                    <a href='bodegas.php?bodega=" . urlencode($archivoConMes) . "'>
                        <img src='$imagen' alt='$nombreBodega'>
                        <div class='card-body'>
                            <h3>$nombreBodega</h3>
                        </div>
                    </a>
                  </div>";
        }
        ?>
    </div>

    <div class="contenedor-bodegas">
        <h2 class="titulo_secundario">ZONA EXCLUSIVA DE ZONA SUR</h2>
        <?php
        foreach ($bodegasZS as $nombreBodegaZS => $archivoZS) {
            $archivoConMesZS = str_replace('.xlsx', "_{$mesActual}.xlsx", $archivoZS);
            $imagenZS = "/img/" . str_replace(".xlsx", ".jpeg", $archivoZS);

            echo "<div class='card'>
                    <a href='bodegas.php?bodega=" . urlencode($archivoConMesZS) . "'>
                        <img src='$imagenZS' alt='$nombreBodegaZS'>
                        <div class='card-body'>
                            <h3>$nombreBodegaZS</h3>
                        </div>
                    </a>
                  </div>";
        }
        ?>
        
        <h2 class="titulo_secundario">CHEQUEO DE CUMPLIMIENTO</h2>

        <table class="tabla-verificaciones">
            <thead>
                <tr>
                    <th>Bodega</th>
                    <th>Última Verificación</th>
                    <th>Tiempo Restante</th>
                </tr>
            </thead>
            <tbody>
            <?php
function obtenerUltimaVerificacion($bodega) {
    $archivoVerificacion = __DIR__ . "/ultima_verificacion_$bodega.txt";
    if (!file_exists($archivoVerificacion) || trim(file_get_contents($archivoVerificacion)) === "") {
        return 0;
    }
    return (int) trim(file_get_contents($archivoVerificacion));
}

foreach ($bodegas as $nombreBodega => $archivo) {
    $archivoConMes = str_replace('.xlsx', "_{$mesActual}.xlsx", $archivo);
    $ultimaVerificacion = obtenerUltimaVerificacion($archivoConMes);

    if (!is_numeric($ultimaVerificacion) || $ultimaVerificacion == 0) {
        $ultimaVerificacion = 0;
    }

    $tiempoRestante = ($ultimaVerificacion + 86400) - time();

    if ($ultimaVerificacion == 0) {
        $ultimaVerificacionTexto = "Nunca";
        $tiempoRestanteTexto = "¡Debe realizar la verificación!";
        $color = "red";
    } else {
        $ultimaVerificacionTexto = date("H:i:s", $ultimaVerificacion);
        $tiempoRestanteTexto = "<span class='cronometro' data-tiempo='$tiempoRestante'></span>";
        $color = "black";
    }

    echo "<tr>
            <td>$nombreBodega</td>
            <td>$ultimaVerificacionTexto</td>
            <td class='cronometro-cell' style='color: $color;'>$tiempoRestanteTexto</td>
          </tr>";
}

foreach ($bodegasZS as $nombreBodegaZS => $archivoZS) {
    $archivoConMesZS = str_replace('.xlsx', "_{$mesActual}.xlsx", $archivoZS);
    $ultimaVerificacion = obtenerUltimaVerificacion($archivoConMesZS);

    if (!is_numeric($ultimaVerificacion) || $ultimaVerificacion == 0) {
        $ultimaVerificacion = 0;
    }

    $tiempoRestante = ($ultimaVerificacion + 86400) - time();

    if ($ultimaVerificacion == 0) {
        $ultimaVerificacionTexto = "Nunca";
        $tiempoRestanteTexto = "¡Debe realizar la verificación!";
        $color = "red";
    } else {
        $ultimaVerificacionTexto = date("H:i:s", $ultimaVerificacion);
        $tiempoRestanteTexto = "<span class='cronometro' data-tiempo='$tiempoRestante'></span>";
        $color = "black";
    }

    echo "<tr>
            <td>$nombreBodegaZS</td>
            <td>$ultimaVerificacionTexto</td>
            <td class='cronometro-cell' style='color: $color;'>$tiempoRestanteTexto</td>
          </tr>";
}
            ?>
            </tbody>
        </table>
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        let cronometros = document.querySelectorAll(".cronometro");

        cronometros.forEach(function(cronometro) {
            let tiempoRestante = parseInt(cronometro.dataset.tiempo);
            
            function actualizarCronometro() {
                if (tiempoRestante <= 0) {
                    cronometro.innerHTML = "¡Debe realizar la verificación!";
                    cronometro.style.color = "red";
                } else {
                    let horas = Math.floor(tiempoRestante / 3600);
                    let minutos = Math.floor((tiempoRestante % 3600) / 60);
                    let segundos = tiempoRestante % 60;
                    cronometro.innerHTML = `${horas}h ${minutos}m ${segundos}s`;
                    tiempoRestante--;
                    setTimeout(actualizarCronometro, 1000);
                }
            }

            actualizarCronometro();
        });
    });
    </script>

</body>
</html>