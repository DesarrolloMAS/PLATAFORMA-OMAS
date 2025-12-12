<?php
require __DIR__ . '/../../vendor/autoload.php'; 

use PhpOffice\PhpSpreadsheet\IOFactory;

if (isset($_GET['archivo'])) {
    $archivo = $_GET['archivo'];
    $carpeta = rtrim(__DIR__, '/') . '/../../archivos/generados/excelC_M/';
    $rutaArchivo = $carpeta . $archivo;
    if (!file_exists($rutaArchivo)) {
        die("Error: El archivo no existe o la ruta es incorrecta: $rutaArchivo");
    }
    try{
        $spreadsheet = IOFactory::load($rutaArchivo);
        $hoja = $spreadsheet->getActiveSheet();
        $datosFormulario=[
        "nummolienda" => $hoja->getCell('J4')->getValue(),
        "responsable" => $hoja->getCell('C67')->getValue(),
        "fecha" => $hoja->getCell('I8')->getValue(),
        "hora" => $hoja->getCell('K5')->getValue(),
        "codigores1" => $hoja->getCell('D45')->getValue(),
        "codigores1_2" => $hoja->getCell('D47')->getValue(),
        "codigores1_3" => $hoja->getCell('D49')->getValue(),
        "codigores1_4" => $hoja->getCell('D51')->getValue(),
        "codigores1_5" => $hoja->getCell('D53')->getValue(),
        "codigores1_6" => $hoja->getCell('D55')->getValue(),
        "codigores1_7" => $hoja->getCell('D57')->getValue(),
        "codigores1_8" => $hoja->getCell('D59')->getValue(),
        "codigores1_9" => $hoja->getCell('D61')->getValue(),
        "codigomat2_1" => $hoja->getCell('K49')->getValue(),
        "codigomat2_2" => $hoja->getCell('K51')->getValue(),
        "codigomat2_3" => $hoja->getCell('K53')->getValue(),
        "pesores1" => $hoja->getCell('C45')->getValue(),
        "pesores1_2" => $hoja->getCell('C47')->getValue(),
        "pesores1_3" => $hoja->getCell('C49')->getValue(),
        "pesores1_4" => $hoja->getCell('C51')->getValue(),
        "pesores1_5" => $hoja->getCell('C53')->getValue(),
        "pesores1_6" => $hoja->getCell('C55')->getValue(),
        "pesores1_7" => $hoja->getCell('C57')->getValue(),
        "pesores1_8" => $hoja->getCell('C59')->getValue(),
        "pesores1_9" => $hoja->getCell('C61')->getValue(),
        "valor1" => $hoja->getCell('C11')->getValue(),
        "valor2" => $hoja->getCell('C13')->getValue(),
        "valor3" => $hoja->getCell('C15')->getValue(),
        "valor4" => $hoja->getCell('C17')->getValue(),
        "valor5" => $hoja->getCell('C19')->getValue(),
        "valor6" => $hoja->getCell('C21')->getValue(),
        "valor7" => $hoja->getCell('C23')->getValue(),
        "valor8" => $hoja->getCell('C25')->getValue(),
        "valor9" => $hoja->getCell('C27')->getValue(),
        "valor10" => $hoja->getCell('C29')->getValue(),
        "valor11" => $hoja->getCell('C31')->getValue(),
        "valor12" => $hoja->getCell('C33')->getValue(),
        "valor13" => $hoja->getCell('C35')->getValue(),
        "valor14" => $hoja->getCell('C37')->getValue(),
        "valor15" => $hoja->getCell('C39')->getValue(),
        "valor16" => $hoja->getCell('C41')->getValue(),
        "valor17" => $hoja->getCell('C42')->getValue(),
        "lote1" => $hoja->getCell('D11')->getValue(),
        "lote2" => $hoja->getCell('D13')->getValue(),
        "lote3" => $hoja->getCell('D15')->getValue(),
        "lote4" => $hoja->getCell('D17')->getValue(),
        "lote5" => $hoja->getCell('D19')->getValue(),
        "lote6" => $hoja->getCell('D21')->getValue(),
        "lote7" => $hoja->getCell('D23')->getValue(),
        "lote8" => $hoja->getCell('D25')->getValue(),
        "lote9" => $hoja->getCell('D27')->getValue(),
        "lote10" => $hoja->getCell('D29')->getValue(),
        "lote11" => $hoja->getCell('D31')->getValue(),
        "lote12" => $hoja->getCell('D33')->getValue(),
        "lote13" => $hoja->getCell('D35')->getValue(),
        "lote14" => $hoja->getCell('D37')->getValue(),
        "lote15" => $hoja->getCell('D39')->getValue(),
        "lote16" => $hoja->getCell('D41')->getValue(),
        "lote17" => $hoja->getCell('D43')->getValue(),
        "codigomat1_1" => $hoja->getCell('K11')->getValue(),
        "codigomat1_2" => $hoja->getCell('K13')->getValue(),
        "codigomat1_3" => $hoja->getCell('K15')->getValue(),
        "codigomat1_4" => $hoja->getCell('K17')->getValue(),
        "codigomat1_5" => $hoja->getCell('K19')->getValue(),
        "codigomat1_6" => $hoja->getCell('K21')->getValue(),
        "codigomat1_7" => $hoja->getCell('K23')->getValue(),
        "codigomat1_8" => $hoja->getCell('K25')->getValue(),
        "codigomat1_9" => $hoja->getCell('K27')->getValue(),
        "codigomat1_10" => $hoja->getCell('K29')->getValue(),
        "codigomat1_11" => $hoja->getCell('K31')->getValue(),
        "codigomat1_12" => $hoja->getCell('K33')->getValue(),
        "codigomat1_13" => $hoja->getCell('K35')->getValue(),
        "codigomat1_14" => $hoja->getCell('K37')->getValue(),
        "codigomat1_15" => $hoja->getCell('K39')->getValue(),
        "codigomat1_16" => $hoja->getCell('K41')->getValue(),
        "codigomat1_17" => $hoja->getCell('K43')->getValue(),
        "codigomat1_18" => $hoja->getCell('K45')->getValue(),
        "codigomat1_19" => $hoja->getCell('K47')->getValue(),
        "nomgomat2_1" => $hoja->getCell('J49')->getValue(),
        "nomgomat2_2" => $hoja->getCell('J51')->getValue(),
        "nomgomat2_3" => $hoja->getCell('J53')->getValue(),
        "nomresp1_1" => $hoja->getCell('C67')->getValue(),
        "nomresp1_2" => $hoja->getCell('C68')->getValue(),
        "nomresp1_3" => $hoja->getCell('C69')->getValue(),
        "nomresp1_4" => $hoja->getCell('C70')->getValue(),
        "nomresp1_5" => $hoja->getCell('C71')->getValue(),
        "numeroAsignado" => $hoja->getCell('J4')->getValue(),
        
        //ZONA DE SEGUNDO TURNO//

        "hora-2" => $hoja->getCell('K6')->getValue(),
        "codigores1-2" => $hoja->getCell('F45')->getValue(),
        "codigores1_2-2" => $hoja->getCell('F47')->getValue(),
        "codigores1_3-2" => $hoja->getCell('F49')->getValue(),
        "codigores1_4-2" => $hoja->getCell('F51')->getValue(),
        "codigores1_5-2" => $hoja->getCell('F53')->getValue(),
        "codigores1_6-2" => $hoja->getCell('F55')->getValue(),
        "codigores1_7-2" => $hoja->getCell('F57')->getValue(),
        "codigores1_8-2" => $hoja->getCell('F59')->getValue(),
        "codigores1_9-2" => $hoja->getCell('F61')->getValue(),
        "pesores1-2" => $hoja->getCell('E45')->getValue(),
        "pesores1_2-2" => $hoja->getCell('E47')->getValue(),
        "pesores1_3-2" => $hoja->getCell('E49')->getValue(),
        "pesores1_4-2" => $hoja->getCell('E51')->getValue(),
        "pesores1_5-2" => $hoja->getCell('E53')->getValue(),
        "pesores1_6-2" => $hoja->getCell('E55')->getValue(),
        "pesores1_7-2" => $hoja->getCell('E57')->getValue(),
        "pesores1_8-2" => $hoja->getCell('E59')->getValue(),
        "pesores1_9-2" => $hoja->getCell('E61')->getValue(),
        "valor1-2" => $hoja->getCell('E11')->getValue(),
        "valor2-2" => $hoja->getCell('E13')->getValue(),
        "valor3-2" => $hoja->getCell('E15')->getValue(),
        "valor4-2" => $hoja->getCell('E17')->getValue(),
        "valor5-2" => $hoja->getCell('E19')->getValue(),
        "valor6-2" => $hoja->getCell('E21')->getValue(),
        "valor7-2" => $hoja->getCell('E23')->getValue(),
        "valor8-2" => $hoja->getCell('E25')->getValue(),
        "valor9-2" => $hoja->getCell('E27')->getValue(),
        "valor10-2" => $hoja->getCell('E29')->getValue(),
        "valor11-2" => $hoja->getCell('E31')->getValue(),
        "valor12-2" => $hoja->getCell('E33')->getValue(),
        "valor13-2" => $hoja->getCell('E35')->getValue(),
        "valor14-2" => $hoja->getCell('E37')->getValue(),
        "valor15-2" => $hoja->getCell('E39')->getValue(),
        "valor16-2" => $hoja->getCell('E41')->getValue(),
        "valor17-2" => $hoja->getCell('E42')->getValue(),
        "lote1-2" => $hoja->getCell('F11')->getValue(),
        "lote2-2" => $hoja->getCell('F13')->getValue(),
        "lote3-2" => $hoja->getCell('F15')->getValue(),
        "lote4-2" => $hoja->getCell('F17')->getValue(),
        "lote5-2" => $hoja->getCell('F19')->getValue(),
        "lote6-2" => $hoja->getCell('F21')->getValue(),
        "lote7-2" => $hoja->getCell('F23')->getValue(),
        "lote8-2" => $hoja->getCell('F25')->getValue(),
        "lote9-2" => $hoja->getCell('F27')->getValue(),
        "lote10-2" => $hoja->getCell('F29')->getValue(),
        "lote11-2" => $hoja->getCell('F31')->getValue(),
        "lote12-2" => $hoja->getCell('F33')->getValue(),
        "lote13-2" => $hoja->getCell('F35')->getValue(),
        "lote14-2" => $hoja->getCell('F37')->getValue(),
        "lote15-2" => $hoja->getCell('F39')->getValue(),
        "lote16-2" => $hoja->getCell('F41')->getValue(),
        "lote17-2" => $hoja->getCell('F43')->getValue(),
        "nomresp1_1-2" => $hoja->getCell('E67')->getValue(),
        "nomresp1_2-2" => $hoja->getCell('E68')->getValue(),
        "nomresp1_3-2" => $hoja->getCell('E69')->getValue(),
        "nomresp1_4-2" => $hoja->getCell('E70')->getValue(),
        "nomresp1_5-2" => $hoja->getCell('E71')->getValue(),


        //ZONA DE TURNO 3



        "hora-3" => $hoja->getCell('K7')->getValue(),
        "codigores1-3" => $hoja->getCell('H45')->getValue(),
        "codigores1_2-3" => $hoja->getCell('H47')->getValue(),
        "codigores1_3-3" => $hoja->getCell('H49')->getValue(),
        "codigores1_4-3" => $hoja->getCell('H51')->getValue(),
        "codigores1_5-3" => $hoja->getCell('H53')->getValue(),
        "codigores1_6-3" => $hoja->getCell('H55')->getValue(),
        "codigores1_7-3" => $hoja->getCell('H57')->getValue(),
        "codigores1_8-3" => $hoja->getCell('H59')->getValue(),
        "codigores1_9-3" => $hoja->getCell('H61')->getValue(),
        "pesores1-3" => $hoja->getCell('G45')->getValue(),
        "pesores1_2-3" => $hoja->getCell('G47')->getValue(),
        "pesores1_3-3" => $hoja->getCell('G49')->getValue(),
        "pesores1_4-3" => $hoja->getCell('G51')->getValue(),
        "pesores1_5-3" => $hoja->getCell('G53')->getValue(),
        "pesores1_6-3" => $hoja->getCell('G55')->getValue(),
        "pesores1_7-3" => $hoja->getCell('G57')->getValue(),
        "pesores1_8-3" => $hoja->getCell('G59')->getValue(),
        "pesores1_9-3" => $hoja->getCell('G61')->getValue(),
        "valor1-3" => $hoja->getCell('G11')->getValue(),
        "valor2-3" => $hoja->getCell('G13')->getValue(),
        "valor3-3" => $hoja->getCell('G15')->getValue(),
        "valor4-3" => $hoja->getCell('G17')->getValue(),
        "valor5-3" => $hoja->getCell('G19')->getValue(),
        "valor6-3" => $hoja->getCell('G21')->getValue(),
        "valor7-3" => $hoja->getCell('G23')->getValue(),
        "valor8-3" => $hoja->getCell('G25')->getValue(),
        "valor9-3" => $hoja->getCell('G27')->getValue(),
        "valor10-3" => $hoja->getCell('G29')->getValue(),
        "valor11-3" => $hoja->getCell('G31')->getValue(),
        "valor12-3" => $hoja->getCell('G33')->getValue(),
        "valor13-3" => $hoja->getCell('G35')->getValue(),
        "valor14-3" => $hoja->getCell('G37')->getValue(),
        "valor15-3" => $hoja->getCell('G39')->getValue(),
        "valor16-3" => $hoja->getCell('G41')->getValue(),
        "valor17-3" => $hoja->getCell('G42')->getValue(),
        "lote1-3" => $hoja->getCell('H11')->getValue(),
        "lote2-3" => $hoja->getCell('H13')->getValue(),
        "lote3-3" => $hoja->getCell('H15')->getValue(),
        "lote4-3" => $hoja->getCell('H17')->getValue(),
        "lote5-3" => $hoja->getCell('H19')->getValue(),
        "lote6-3" => $hoja->getCell('H21')->getValue(),
        "lote7-3" => $hoja->getCell('H23')->getValue(),
        "lote8-3" => $hoja->getCell('H25')->getValue(),
        "lote9-3" => $hoja->getCell('H27')->getValue(),
        "lote10-3" => $hoja->getCell('H29')->getValue(),
        "lote11-3" => $hoja->getCell('H31')->getValue(),
        "lote12-3" => $hoja->getCell('H33')->getValue(),
        "lote13-3" => $hoja->getCell('H35')->getValue(),
        "lote14-3" => $hoja->getCell('H37')->getValue(),
        "lote15-3" => $hoja->getCell('H39')->getValue(),
        "lote16-3" => $hoja->getCell('H41')->getValue(),
        "lote17-3" => $hoja->getCell('H43')->getValue(),
        "nomresp1_1-3" => $hoja->getCell('G67')->getValue(),
        "nomresp1_2-3" => $hoja->getCell('G68')->getValue(),
        "nomresp1_3-3" => $hoja->getCell('G69')->getValue(),
        "nomresp1_4-3" => $hoja->getCell('G70')->getValue(),
        "nomresp1_5-3" => $hoja->getCell('G71')->getValue(),



        ];   
        }catch (Exception $e) {
            die("Error al procesar el archivo: " . $e->getMessage());
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/fmt/css/procesar.css">
    <title>Revisión de Datos</title>
</head>
<body class="body">
    <div class="cuerpo">
    <h1 class="titulo_principal">Revisión de Datos del Formulario</h1>
    <form action="guardar2.php" method="post" class="formulario1">
        <div class="formulariog">
    <input type="hidden" name="archivo" value="<?php echo htmlspecialchars($archivo, ENT_QUOTES, 'UTF-8'); ?>">
<table class="tabla">
<div>
<label for="responsable">Responsable</label>
<input type="text" name="responsable" value="<?php echo htmlspecialchars($datosFormulario['responsable'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="fecha">Fecha</label>
<input type="date" name="fecha" value="<?php echo htmlspecialchars($datosFormulario['fecha'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="hora">Hora</label>
<input type="time" name="hora" value="<?php echo htmlspecialchars($datosFormulario['hora'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<!-- Código de los residuos -->
<label for="codigores1">Código Res 1</label>
<input type="text" name="codigores1" value="<?php echo htmlspecialchars($datosFormulario['codigores1'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="codigores1_2">Código Res 1.2</label>
<input type="text" name="codigores1_2" value="<?php echo htmlspecialchars($datosFormulario['codigores1_2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="codigores1_3">Código Res 1.3</label>
<input type="text" name="codigores1_3" value="<?php echo htmlspecialchars($datosFormulario['codigores1_3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="codigores1_4">Código Res 1.4</label>
<input type="text" name="codigores1_4" value="<?php echo htmlspecialchars($datosFormulario['codigores1_4'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="codigores1_5">Código Res 1.5</label>
<input type="text" name="codigores1_5" value="<?php echo htmlspecialchars($datosFormulario['codigores1_5'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="codigores1_6">Código Res 1.6</label>
<input type="text" name="codigores1_6" value="<?php echo htmlspecialchars($datosFormulario['codigores1_6'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="codigores1_7">Código Res 1.7</label>
<input type="text" name="codigores1_7" value="<?php echo htmlspecialchars($datosFormulario['codigores1_7'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="codigores1_8">Código Res 1.8</label>
<input type="text" name="codigores1_8" value="<?php echo htmlspecialchars($datosFormulario['codigores1_8'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="codigores1_9">Código Res 1.9</label>
<input type="text" name="codigores1_9" value="<?php echo htmlspecialchars($datosFormulario['codigores1_9'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<!-- Código de materiales -->
<label for="codigomat1_1">Código Mat 1</label>
<input type="text" name="codigomat1_1" value="<?php echo htmlspecialchars($datosFormulario['codigomat1_1'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="codigomat1_2">Código Mat 2</label>
<input type="text" name="codigomat1_2" value="<?php echo htmlspecialchars($datosFormulario['codigomat1_2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="codigomat1_3">Código Mat 3</label>
<input type="text" name="codigomat1_3" value="<?php echo htmlspecialchars($datosFormulario['codigomat1_3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="codigomat1_4">Código Mat 4</label>
<input type="text" name="codigomat1_4" value="<?php echo htmlspecialchars($datosFormulario['codigomat1_4'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="codigomat1_5">Código Mat 5</label>
<input type="text" name="codigomat1_5" value="<?php echo htmlspecialchars($datosFormulario['codigomat1_5'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="codigomat1_6">Código Mat 6</label>
<input type="text" name="codigomat1_6" value="<?php echo htmlspecialchars($datosFormulario['codigomat1_6'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="codigomat1_7">Código Mat 7</label>
<input type="text" name="codigomat1_7" value="<?php echo htmlspecialchars($datosFormulario['codigomat1_7'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="codigomat1_8">Código Mat 8</label>
<input type="text" name="codigomat1_8" value="<?php echo htmlspecialchars($datosFormulario['codigomat1_8'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="codigomat1_9">Código Mat 9</label>
<input type="text" name="codigomat1_9" value="<?php echo htmlspecialchars($datosFormulario['codigomat1_9'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="codigomat1_10">Código Mat 10</label>
<input type="text" name="codigomat1_10" value="<?php echo htmlspecialchars($datosFormulario['codigomat1_10'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="codigomat1_11">Código Mat 11</label>
<input type="text" name="codigomat1_11" value="<?php echo htmlspecialchars($datosFormulario['codigomat1_11'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="codigomat1_12">Código Mat 12</label>
<input type="text" name="codigomat1_12" value="<?php echo htmlspecialchars($datosFormulario['codigomat1_12'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="codigomat1_13">Código Mat 13</label>
<input type="text" name="codigomat1_13" value="<?php echo htmlspecialchars($datosFormulario['codigomat1_13'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="codigomat1_14">Código Mat 14</label>
<input type="text" name="codigomat1_14" value="<?php echo htmlspecialchars($datosFormulario['codigomat1_14'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="codigomat1_15">Código Mat 15</label>
<input type="text" name="codigomat1_15" value="<?php echo htmlspecialchars($datosFormulario['codigomat1_15'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="codigomat1_16">Código Mat 16</label>
<input type="text" name="codigomat1_16" value="<?php echo htmlspecialchars($datosFormulario['codigomat1_16'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="codigomat1_17">Código Mat 17</label>
<input type="text" name="codigomat1_17" value="<?php echo htmlspecialchars($datosFormulario['codigomat1_17'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="codigomat1_18">Código Mat 18</label>
<input type="text" name="codigomat1_18" value="<?php echo htmlspecialchars($datosFormulario['codigomat1_18'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="codigomat1_19">Código Mat 19</label>
<input type="text" name="codigomat1_19" value="<?php echo htmlspecialchars($datosFormulario['codigomat1_19'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="codigomat2_1">Código Mat 2.1</label>
<input type="text" name="codigomat2_1" value="<?php echo htmlspecialchars($datosFormulario['codigomat2_1'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="codigomat2_2">Código Mat 2.2</label>
<input type="text" name="codigomat2_2" value="<?php echo htmlspecialchars($datosFormulario['codigomat2_2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="codigomat2_3">Código Mat 2.3</label>
<input type="text" name="codigomat2_3" value="<?php echo htmlspecialchars($datosFormulario['codigomat2_3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<!-- Peso de los residuos -->
<label for="pesores1">Peso Res 1</label>
<input type="text" name="pesores1" value="<?php echo htmlspecialchars($datosFormulario['pesores1'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="pesores1_2">Peso Res 1.2</label>
<input type="text" name="pesores1_2" value="<?php echo htmlspecialchars($datosFormulario['pesores1_2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="pesores1_3">Peso Res 1.3</label>
<input type="text" name="pesores1_3" value="<?php echo htmlspecialchars($datosFormulario['pesores1_3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="pesores1_4">Peso Res 1.4</label>
<input type="text" name="pesores1_4" value="<?php echo htmlspecialchars($datosFormulario['pesores1_4'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="pesores1_5">Peso Res 1.5</label>
<input type="text" name="pesores1_5" value="<?php echo htmlspecialchars($datosFormulario['pesores1_4'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="pesores1_6">Peso Res 1.6</label>
<input type="text" name="pesores1_6" value="<?php echo htmlspecialchars($datosFormulario['pesores1_4'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="pesores1_7">Peso Res 1.7</label>
<input type="text" name="pesores1_7" value="<?php echo htmlspecialchars($datosFormulario['pesores1_4'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="pesores1_8">Peso Res 1.8</label>
<input type="text" name="pesores1_8" value="<?php echo htmlspecialchars($datosFormulario['pesores1_4'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="pesores1_9">Peso Res 1.9</label>
<input type="text" name="pesores1_9" value="<?php echo htmlspecialchars($datosFormulario['pesores1_4'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<!-- Valores -->
<label for="valor1">Valor 1</label>
<input type="text" name="valor1" value="<?php echo htmlspecialchars($datosFormulario['valor1'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="valor2">Valor 2</label>
<input type="text" name="valor2" value="<?php echo htmlspecialchars($datosFormulario['valor2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="valor3">Valor 3</label>
<input type="text" name="valor3" value="<?php echo htmlspecialchars($datosFormulario['valor3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="valor4">Valor 4</label>
<input type="text" name="valor4" value="<?php echo htmlspecialchars($datosFormulario['valor4'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="valor5">Valor 5</label>
<input type="text" name="valor5" value="<?php echo htmlspecialchars($datosFormulario['valor5'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="valor6">Valor 6</label>
<input type="text" name="valor6" value="<?php echo htmlspecialchars($datosFormulario['valor6'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="valor7">Valor 7</label>
<input type="text" name="valor7" value="<?php echo htmlspecialchars($datosFormulario['valor7'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="valor8">Valor 8</label>
<input type="text" name="valor8" value="<?php echo htmlspecialchars($datosFormulario['valor8'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="valor9">Valor 9</label>
<input type="text" name="valor9" value="<?php echo htmlspecialchars($datosFormulario['valor9'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="valor9">Valor 10</label>
<input type="text" name="valor10" value="<?php echo htmlspecialchars($datosFormulario['valor9'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="valor9">Valor 11</label>
<input type="text" name="valor11" value="<?php echo htmlspecialchars($datosFormulario['valor9'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="valor9">Valor 12</label>
<input type="text" name="valor12" value="<?php echo htmlspecialchars($datosFormulario['valor9'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="valor9">Valor 13</label>
<input type="text" name="valor13" value="<?php echo htmlspecialchars($datosFormulario['valor9'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="valor9">Valor 14</label>
<input type="text" name="valor14" value="<?php echo htmlspecialchars($datosFormulario['valor9'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="valor9">Valor 15</label>
<input type="text" name="valor15" value="<?php echo htmlspecialchars($datosFormulario['valor9'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="valor9">Valor 16</label>
<input type="text" name="valor16" value="<?php echo htmlspecialchars($datosFormulario['valor9'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="valor9">Valor 17</label>
<input type="text" name="valor17" value="<?php echo htmlspecialchars($datosFormulario['valor9'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<!-- Lotes -->
<label for="lote1">Lote 1</label>
<input type="text" name="lote1" value="<?php echo htmlspecialchars($datosFormulario['lote1'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="lote2">Lote 2</label>
<input type="text" name="lote2" value="<?php echo htmlspecialchars($datosFormulario['lote2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="lote3">Lote 3</label>
<input type="text" name="lote3" value="<?php echo htmlspecialchars($datosFormulario['lote3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="lote4">Lote 4</label>
<input type="text" name="lote4" value="<?php echo htmlspecialchars($datosFormulario['lote4'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="lote5">Lote 5</label>
<input type="text" name="lote5" value="<?php echo htmlspecialchars($datosFormulario['lote5'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="lote6">Lote 6</label>
<input type="text" name="lote6" value="<?php echo htmlspecialchars($datosFormulario['lote6'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="lote7">Lote 7</label>
<input type="text" name="lote7" value="<?php echo htmlspecialchars($datosFormulario['lote7'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="lote8">Lote 8</label>
<input type="text" name="lote8" value="<?php echo htmlspecialchars($datosFormulario['lote8'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="lote9">Lote 9</label>
<input type="text" name="lote9" value="<?php echo htmlspecialchars($datosFormulario['lote9'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="lote10">Lote 10</label>
<input type="text" name="lote10" value="<?php echo htmlspecialchars($datosFormulario['lote10'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="lote11">Lote 11</label>
<input type="text" name="lote11" value="<?php echo htmlspecialchars($datosFormulario['lote11'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="lote12">Lote 12</label>
<input type="text" name="lote12" value="<?php echo htmlspecialchars($datosFormulario['lote12'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="lote13">Lote 13</label>
<input type="text" name="lote13" value="<?php echo htmlspecialchars($datosFormulario['lote13'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="lote14">Lote 14</label>
<input type="text" name="lote14" value="<?php echo htmlspecialchars($datosFormulario['lote14'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="lote15">Lote 15</label>
<input type="text" name="lote15" value="<?php echo htmlspecialchars($datosFormulario['lote15'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<labelel for="lote16">Lote 16</label>
<input type="text" name="lote16" value="<?php echo htmlspecialchars($datosFormulario['lote16'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="lote17">Lote 17</label>
<input type="text" name="lote17" value="<?php echo htmlspecialchars($datosFormulario['lote17'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<!-- Nombres -->
<label for="nomgomat2_1">Nombre Mat 2.1</label>
<input type="text" name="nomgomat2_1" value="<?php echo htmlspecialchars($datosFormulario['nomgomat2_1'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="nomgomat2_2">Nombre Mat 2.2</label>
<input type="text" name="nomgomat2_2" value="<?php echo htmlspecialchars($datosFormulario['nomgomat2_2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="nomgomat2_3">Nombre Mat 2.3</label>
<input type="text" name="nomgomat2_3" value="<?php echo htmlspecialchars($datosFormulario['nomgomat2_3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<!-- Nombres Responsables -->
<label for="nomresp1_1">Nombre Resp 1.1</label>
<input type="text" name="nomresp1_1" value="<?php echo htmlspecialchars($datosFormulario['nomresp1_1'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="nomresp1_2">Nombre Resp 1.2</label>
<input type="text" name="nomresp1_2" value="<?php echo htmlspecialchars($datosFormulario['nomresp1_2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="nomresp1_3">Nombre Resp 1.3</label>
<input type="text" name="nomresp1_3" value="<?php echo htmlspecialchars($datosFormulario['nomresp1_3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="nomresp1_4">Nombre Resp 1.4</label>
<input type="text" name="nomresp1_4" value="<?php echo htmlspecialchars($datosFormulario['nomresp1_4'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="nomresp1_5">Nombre Resp 1.5</label>
<input type="text" name="nomresp1_5" value="<?php echo htmlspecialchars($datosFormulario['nomresp1_5'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<!-- Número Asignado -->
<label for="numeroAsignado">Número Asignado</label>
<input type="text" name="numeroAsignado" value="<?php echo htmlspecialchars($datosFormulario['numeroAsignado'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
</div>

<!-- ZONA DE TURNO 2 -->

<div>
<label for="hora-2">Hora</label>
<input type="text" name="hora-2" value="<?php echo htmlspecialchars($datosFormulario['hora-2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="codigores1-2">Código Res 1</label>
<input type="text" name="codigores1-2" value="<?php echo htmlspecialchars($datosFormulario['codigores1-2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="codigores1_2-2">Código Res 1.2</label>
<input type="text" name="codigores1_2-2" value="<?php echo htmlspecialchars($datosFormulario['codigores1_2-2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="codigores1_3-2">Código Res 1.3</label>
<input type="text" name="codigores1_3-2" value="<?php echo htmlspecialchars($datosFormulario['codigores1_3-2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="codigores1_4-2">Código Res 1.4</label>
<input type="text" name="codigores1_4-2" value="<?php echo htmlspecialchars($datosFormulario['codigores1_4-2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="codigores1_5-2">Código Res 1.5</label>
<input type="text" name="codigores1_5-2" value="<?php echo htmlspecialchars($datosFormulario['codigores1_5-2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="codigores1_6-2">Código Res 1.6</label>
<input type="text" name="codigores1_6-2" value="<?php echo htmlspecialchars($datosFormulario['codigores1_6-2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="codigores1_7-2">Código Res 1.7</label>
<input type="text" name="codigores1_7-2" value="<?php echo htmlspecialchars($datosFormulario['codigores1_7-2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="codigores1_8-2">Código Res 1.8</label>
<input type="text" name="codigores1_8-2" value="<?php echo htmlspecialchars($datosFormulario['codigores1_8-2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="codigores1_9-2">Código Res 1.9</label>
<input type="text" name="codigores1_9-2" value="<?php echo htmlspecialchars($datosFormulario['codigores1_9-2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="pesores1-2">Peso Res 1</label>
<input type="text" name="pesores1-2" value="<?php echo htmlspecialchars($datosFormulario['pesores1-2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="pesores1_2-2">Peso Res 1.2</label>
<input type="text" name="pesores1_2-2" value="<?php echo htmlspecialchars($datosFormulario['pesores1_2-2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="pesores1_3-2">Peso Res 1.3</label>
<input type="text" name="pesores1_3-2" value="<?php echo htmlspecialchars($datosFormulario['pesores1_3-2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="pesores1_4-2">Peso Res 1.4</label>
<input type="text" name="pesores1_4-2" value="<?php echo htmlspecialchars($datosFormulario['pesores1_4-2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="pesores1_5-2">Peso Res 1.5</label>
<input type="text" name="pesores1_5-2" value="<?php echo htmlspecialchars($datosFormulario['pesores1_5-2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="pesores1_6-2">Peso Res 1.6</label>
<input type="text" name="pesores1_6-2" value="<?php echo htmlspecialchars($datosFormulario['pesores1_6-2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="pesores1_7-2">Peso Res 1.7</label>
<input type="text" name="pesores1_7-2" value="<?php echo htmlspecialchars($datosFormulario['pesores1_7-2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="pesores1_8-2">Peso Res 1.8</label>
<input type="text" name="pesores1_8-2" value="<?php echo htmlspecialchars($datosFormulario['pesores1_8-2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="pesores1_9-2">Peso Res 1.9</label>
<input type="text" name="pesores1_9-2" value="<?php echo htmlspecialchars($datosFormulario['pesores1_9-2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="valor1-2">Valor 1</label>
<input type="text" name="valor1-2" value="<?php echo htmlspecialchars($datosFormulario['valor1-2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="valor2-2">Valor 2</label>
<input type="text" name="valor2-2" value="<?php echo htmlspecialchars($datosFormulario['valor2-2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="valor3-2">Valor 3</label>
<input type="text" name="valor3-2" value="<?php echo htmlspecialchars($datosFormulario['valor3-2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="valor4-2">Valor 4</label>
<input type="text" name="valor4-2" value="<?php echo htmlspecialchars($datosFormulario['valor4-2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="valor5-2">Valor 5</label>
<input type="text" name="valor5-2" value="<?php echo htmlspecialchars($datosFormulario['valor5-2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="valor6-2">Valor 6</label>
<input type="text" name="valor6-2" value="<?php echo htmlspecialchars($datosFormulario['valor6-2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="valor7-2">Valor 7</label>
<input type="text" name="valor7-2" value="<?php echo htmlspecialchars($datosFormulario['valor7-2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="valor8-2">Valor 8</label>
<input type="text" name="valor8-2" value="<?php echo htmlspecialchars($datosFormulario['valor8-2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="valor9-2">Valor 9</label>
<input type="text" name="valor9-2" value="<?php echo htmlspecialchars($datosFormulario['valor9-2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="valor10-2">Valor 10</label>
<input type="text" name="valor10-2" value="<?php echo htmlspecialchars($datosFormulario['valor10-2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="valor11-2">Valor 11</label>
<input type="text" name="valor11-2" value="<?php echo htmlspecialchars($datosFormulario['valor11-2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="valor12-2">Valor 12</label>
<input type="text" name="valor12-2" value="<?php echo htmlspecialchars($datosFormulario['valor12-2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="valor13-2">Valor 13</label>
<input type="text" name="valor13-2" value="<?php echo htmlspecialchars($datosFormulario['valor13-2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="valor14-2">Valor 14</label>
<input type="text" name="valor14-2" value="<?php echo htmlspecialchars($datosFormulario['valor14-2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="valor15-2">Valor 15</label>
<input type="text" name="valor15-2" value="<?php echo htmlspecialchars($datosFormulario['valor15-2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="valor16-2">Valor 16</label>
<input type="text" name="valor16-2" value="<?php echo htmlspecialchars($datosFormulario['valor16-2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="valor17-2">Valor 17</label>
<input type="text" name="valor17-2" value="<?php echo htmlspecialchars($datosFormulario['valor17-2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="lote1-2">Lote 1</label>
<input type="text" name="lote1-2" value="<?php echo htmlspecialchars($datosFormulario['lote1-2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="lote2-2">Lote 2</label>
<input type="text" name="lote2-2" value="<?php echo htmlspecialchars($datosFormulario['lote2-2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="lote3-2">Lote 3</label>
<input type="text" name="lote3-2" value="<?php echo htmlspecialchars($datosFormulario['lote3-2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="lote4-2">Lote 4</label>
<input type="text" name="lote4-2" value="<?php echo htmlspecialchars($datosFormulario['lote4-2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="lote5-2">Lote 5</label>
<input type="text" name="lote5-2" value="<?php echo htmlspecialchars($datosFormulario['lote5-2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="lote6-2">Lote 6</label>
<input type="text" name="lote6-2" value="<?php echo htmlspecialchars($datosFormulario['lote6-2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="lote7-2">Lote 7</label>
<input type="text" name="lote7-2" value="<?php echo htmlspecialchars($datosFormulario['lote7-2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="lote8-2">Lote 8</label>
<input type="text" name="lote8-2" value="<?php echo htmlspecialchars($datosFormulario['lote8-2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="lote9-2">Lote 9</label>
<input type="text" name="lote9-2" value="<?php echo htmlspecialchars($datosFormulario['lote9-2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="lote10-2">Lote 10</label>
<input type="text" name="lote10-2" value="<?php echo htmlspecialchars($datosFormulario['lote10-2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="lote11-2">Lote 11</label>
<input type="text" name="lote11-2" value="<?php echo htmlspecialchars($datosFormulario['lote11-2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="lote12-2">Lote 12</label>
<input type="text" name="lote12-2" value="<?php echo htmlspecialchars($datosFormulario['lote12-2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="lote13-2">Lote 13</label>
<input type="text" name="lote13-2" value="<?php echo htmlspecialchars($datosFormulario['lote13-2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="lote14-2">Lote 14</label>
<input type="text" name="lote14-2" value="<?php echo htmlspecialchars($datosFormulario['lote14-2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="lote15-2">Lote 15</label>
<input type="text" name="lote15-2" value="<?php echo htmlspecialchars($datosFormulario['lote15-2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="lote16-2">Lote 16</label>
<input type="text" name="lote16-2" value="<?php echo htmlspecialchars($datosFormulario['lote16-2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="lote17-2">Lote 17</label>
<input type="text" name="lote17-2" value="<?php echo htmlspecialchars($datosFormulario['lote17-2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="nomresp1_1-2">Nombre Resp 1.1</label>
<input type="text" name="nomresp1_1-2" value="<?php echo htmlspecialchars($datosFormulario['nomresp1_1-2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="nomresp1_2-2">Nombre Resp 1.2</label>
<input type="text" name="nomresp1_2-2" value="<?php echo htmlspecialchars($datosFormulario['nomresp1_2-2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="nomresp1_3-2">Nombre Resp 1.3</label>
<input type="text" name="nomresp1_3-2" value="<?php echo htmlspecialchars($datosFormulario['nomresp1_3-2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="nomresp1_4-2">Nombre Resp 1.4</label>
<input type="text" name="nomresp1_4-2" value="<?php echo htmlspecialchars($datosFormulario['nomresp1_4-2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="nomresp1_5-2">Nombre Resp 1.5</label>
<input type="text" name="nomresp1_5-2" value="<?php echo htmlspecialchars($datosFormulario['nomresp1_5-2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
</div>


<!-- ZONA DE TURNO 3 -->


<div>
<label for="hora-3">Hora</label>
<input type="text" name="hora-3" value="<?php echo htmlspecialchars($datosFormulario['hora-3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="codigores1-3">Código Res 1</label>
<input type="text" name="codigores1-3" value="<?php echo htmlspecialchars($datosFormulario['codigores1-3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="codigores1_2-3">Código Res 1.2</label>
<input type="text" name="codigores1_2-3" value="<?php echo htmlspecialchars($datosFormulario['codigores1_2-3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="codigores1_3-3">Código Res 1.3</label>
<input type="text" name="codigores1_3-3" value="<?php echo htmlspecialchars($datosFormulario['codigores1_3-3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="codigores1_4-3">Código Res 1.4</label>
<input type="text" name="codigores1_4-3" value="<?php echo htmlspecialchars($datosFormulario['codigores1_4-3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="codigores1_5-3">Código Res 1.5</label>
<input type="text" name="codigores1_5-3" value="<?php echo htmlspecialchars($datosFormulario['codigores1_5-3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="codigores1_6-3">Código Res 1.6</label>
<input type="text" name="codigores1_6-3" value="<?php echo htmlspecialchars($datosFormulario['codigores1_6-3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="codigores1_7-3">Código Res 1.7</label>
<input type="text" name="codigores1_7-3" value="<?php echo htmlspecialchars($datosFormulario['codigores1_7-3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="codigores1_8-3">Código Res 1.8</label>
<input type="text" name="codigores1_8-3" value="<?php echo htmlspecialchars($datosFormulario['codigores1_8-3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="codigores1_9-3">Código Res 1.9</label>
<input type="text" name="codigores1_9-3" value="<?php echo htmlspecialchars($datosFormulario['codigores1_9-3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="pesores1-3">Peso Res 1</label>
<input type="text" name="pesores1-3" value="<?php echo htmlspecialchars($datosFormulario['pesores1-3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="pesores1_2-3">Peso Res 1.2</label>
<input type="text" name="pesores1_2-3" value="<?php echo htmlspecialchars($datosFormulario['pesores1_2-3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="pesores1_3-3">Peso Res 1.3</label>
<input type="text" name="pesores1_3-3" value="<?php echo htmlspecialchars($datosFormulario['pesores1_3-3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="pesores1_4-3">Peso Res 1.4</label>
<input type="text" name="pesores1_4-3" value="<?php echo htmlspecialchars($datosFormulario['pesores1_4-3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="pesores1_5-3">Peso Res 1.5</label>
<input type="text" name="pesores1_5-3" value="<?php echo htmlspecialchars($datosFormulario['pesores1_5-3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="pesores1_6-3">Peso Res 1.6</label>
<input type="text" name="pesores1_6-3" value="<?php echo htmlspecialchars($datosFormulario['pesores1_6-3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="pesores1_7-3">Peso Res 1.7</label>
<input type="text" name="pesores1_7-3" value="<?php echo htmlspecialchars($datosFormulario['pesores1_7-3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="pesores1_8-3">Peso Res 1.8</label>
<input type="text" name="pesores1_8-3" value="<?php echo htmlspecialchars($datosFormulario['pesores1_8-3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="pesores1_9-3">Peso Res 1.9</label>
<input type="text" name="pesores1_9-3" value="<?php echo htmlspecialchars($datosFormulario['pesores1_9-3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="valor1-3">Valor 1</label>
<input type="text" name="valor1-3" value="<?php echo htmlspecialchars($datosFormulario['valor1-3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="valor2-3">Valor 2</label>
<input type="text" name="valor2-3" value="<?php echo htmlspecialchars($datosFormulario['valor2-3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="valor3-3">Valor 3</label>
<input type="text" name="valor3-3" value="<?php echo htmlspecialchars($datosFormulario['valor3-3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="valor4-3">Valor 4</label>
<input type="text" name="valor4-3" value="<?php echo htmlspecialchars($datosFormulario['valor4-3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="valor5-3">Valor 5</label>
<input type="text" name="valor5-3" value="<?php echo htmlspecialchars($datosFormulario['valor5-3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="valor6-3">Valor 6</label>
<input type="text" name="valor6-3" value="<?php echo htmlspecialchars($datosFormulario['valor6-3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="valor7-3">Valor 7</label>
<input type="text" name="valor7-3" value="<?php echo htmlspecialchars($datosFormulario['valor7-3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="valor8-3">Valor 8</label>
<input type="text" name="valor8-3" value="<?php echo htmlspecialchars($datosFormulario['valor8-3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="valor9-3">Valor 9</label>
<input type="text" name="valor9-3" value="<?php echo htmlspecialchars($datosFormulario['valor9-3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="valor10-3">Valor 10</label>
<input type="text" name="valor10-3" value="<?php echo htmlspecialchars($datosFormulario['valor10-3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="valor11-3">Valor 11</label>
<input type="text" name="valor11-3" value="<?php echo htmlspecialchars($datosFormulario['valor11-3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="valor12-3">Valor 12</label>
<input type="text" name="valor12-3" value="<?php echo htmlspecialchars($datosFormulario['valor12-3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="valor13-3">Valor 13</label>
<input type="text" name="valor13-3" value="<?php echo htmlspecialchars($datosFormulario['valor13-3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="valor14-3">Valor 14</label>
<input type="text" name="valor14-3" value="<?php echo htmlspecialchars($datosFormulario['valor14-3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="valor15-3">Valor 15</label>
<input type="text" name="valor15-3" value="<?php echo htmlspecialchars($datosFormulario['valor15-3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="valor16-3">Valor 16</label>
<input type="text" name="valor16-3" value="<?php echo htmlspecialchars($datosFormulario['valor16-3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="valor17-3">Valor 17</label>
<input type="text" name="valor17-3" value="<?php echo htmlspecialchars($datosFormulario['valor17-3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="lote1-3">Lote 1</label>
<input type="text" name="lote1-3" value="<?php echo htmlspecialchars($datosFormulario['lote1-3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="lote2-3">Lote 2</label>
<input type="text" name="lote2-3" value="<?php echo htmlspecialchars($datosFormulario['lote2-3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="lote3-3">Lote 3</label>
<input type="text" name="lote3-3" value="<?php echo htmlspecialchars($datosFormulario['lote3-3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="lote4-3">Lote 4</label>
<input type="text" name="lote4-3" value="<?php echo htmlspecialchars($datosFormulario['lote4-3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="lote5-3">Lote 5</label>
<input type="text" name="lote5-3" value="<?php echo htmlspecialchars($datosFormulario['lote5-3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="lote6-3">Lote 6</label>
<input type="text" name="lote6-3" value="<?php echo htmlspecialchars($datosFormulario['lote6-3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="lote7-3">Lote 7</label>
<input type="text" name="lote7-3" value="<?php echo htmlspecialchars($datosFormulario['lote7-3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="lote8-3">Lote 8</label>
<input type="text" name="lote8-3" value="<?php echo htmlspecialchars($datosFormulario['lote8-3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="lote9-3">Lote 9</label>
<input type="text" name="lote9-3" value="<?php echo htmlspecialchars($datosFormulario['lote9-3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="lote10-3">Lote 10</label>
<input type="text" name="lote10-3" value="<?php echo htmlspecialchars($datosFormulario['lote10-3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="lote11-3">Lote 11</label>
<input type="text" name="lote11-3" value="<?php echo htmlspecialchars($datosFormulario['lote11-3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="lote12-3">Lote 12</label>
<input type="text" name="lote12-3" value="<?php echo htmlspecialchars($datosFormulario['lote12-3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="lote13-3">Lote 13</label>
<input type="text" name="lote13-3" value="<?php echo htmlspecialchars($datosFormulario['lote13-3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="lote14-3">Lote 14</label>
<input type="text" name="lote14-3" value="<?php echo htmlspecialchars($datosFormulario['lote14-3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="lote15-3">Lote 15</label>
<input type="text" name="lote15-3" value="<?php echo htmlspecialchars($datosFormulario['lote15-3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="lote16-3">Lote 16</label>
<input type="text" name="lote16-3" value="<?php echo htmlspecialchars($datosFormulario['lote16-3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="lote17-3">Lote 17</label>
<input type="text" name="lote17-3" value="<?php echo htmlspecialchars($datosFormulario['lote17-3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="nomresp1_1-3">Nombre Resp 1.1</label>
<input type="text" name="nomresp1_1-3" value="<?php echo htmlspecialchars($datosFormulario['nomresp1_1-3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="nomresp1_2-3">Nombre Resp 1.2</label>
<input type="text" name="nomresp1_2-3" value="<?php echo htmlspecialchars($datosFormulario['nomresp1_2-3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="nomresp1_3-3">Nombre Resp 1.3</label>
<input type="text" name="nomresp1_3-3" value="<?php echo htmlspecialchars($datosFormulario['nomresp1_3-3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="nomresp1_4-3">Nombre Resp 1.4</label>
<input type="text" name="nomresp1_4-3" value="<?php echo htmlspecialchars($datosFormulario['nomresp1_4-3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<br>
<label for="nomresp1_5-3">Nombre Resp 1.5</label>
<input type="text" name="nomresp1_5-3" value="<?php echo htmlspecialchars($datosFormulario['nomresp1_5-3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
</div>
<input type="submit">