<?php
require '../vendor/autoload.php'; // Asegúrate de tener PhpSpreadsheet instalado
require 'sesion.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
session_start();
$_SESSION['pagina_anterior'] = $_SERVER['HTTP_REFERER'];
if (isset($_GET['archivo'])) {
    $archivo = $_GET['archivo'];
    $sede = $_SESSION['sede'];
    if ($sede === 'ZC'){
        $carpeta = rtrim(__DIR__, '/') . '/../archivos/generados/excelS_M/';
        $rutaArchivo = $carpeta . $archivo;
    }else{
        $carpetaZS = rtrim(__DIR__, '/') . '/../archivos/generados/excelS_MZS/';
        $rutaArchivo = $carpetaZS . $archivo;
    }
    if (!file_exists($rutaArchivo)) {
        die("Error: El archivo no existe o la ruta es incorrecta: $rutaArchivo");
    }

    try {
        // Cargar el archivo Excel
        $spreadsheet = IOFactory::load($rutaArchivo);
        $hoja = $spreadsheet->getActiveSheet();

        // Extraer los datos necesarios
        $datosFormulario = [ 
        "codigoorden" => $hoja->getCell('J6')->getvalue(),
        "fechainicial" => $hoja->getCell('C6')->getValue(),
        "horainicial" => $hoja->getCell('F6')->getValue(),
        "nombreSolicitante" => $hoja->getCell('D7')->getValue(),
        "cargoSolicitante" => $hoja->getCell('I7')->getValue(),
        "objetoDañado" => $hoja->getCell('D9')->getValue(),
        "codigo" => $hoja->getCell('I9')->getValue(),
        "marca" => $hoja->getCell('C10')->getValue(),
        "descripcionDaños" => $hoja->getCell('A12')->getValue(),
        "ubicacion" => $hoja->getCell('I10')->getValue(),
        "tipoMantenimiento" => $hoja->getCell('A17')->getValue(),
        "descripcionTrabajo" => $hoja->getCell('A19')->getValue(),
        "fechaCierre" => $hoja->getCell('G20')->getValue(),
        "hora_cierre" => $hoja->getCell('I20')->getValue(),
        "responsable_Miembro_De_La_Compañia" => $hoja->getCell('A22')->getValue(),
        "nprov" => $hoja->getCell('H38')->getValue(),
        "voboMantenimiento" => $hoja->getCell('G22')->getValue(),
        "descripcion_inocuidad" => $hoja->getCell('A28')->getValue(),
        "retiro_inocuidad" => $hoja->getCell('J29')->getValue(),
        "descripcion_novedad" => $hoja->getCell('A31')->getValue(),
        "riesgo_inocuidad" => $hoja->getCell('J32')->getValue(),
        "implementos" => $hoja->getCell('A34')->getValue(),
        "fecha_revisionl" => $hoja->getCell('C36')->getValue(),
        "hora_revisionl" => $hoja->getCell('H36')->getValue(),
        "control_responsable" => $hoja->getCell('D47')->getValue(),
        "trabajo_realizar" => $hoja->getCell('D48')->getValue(),
        "fechacontrol" => $hoja->getCell('C46')->getValue(),
        "cargo_control" => $hoja->getCell('I47')->getValue(),
        "Vobo_ingreso" => $hoja->getCell('B72')->getValue(),
        "Vobo_salida" => $hoja->getCell('H72')->getValue(),

        //COMIENZO TABLAS//
        //HERRAMIENTAS//

        "herramientas_cantidad1" => $hoja->getCell('A52')->getValue(),
        "descripcion_herramientas1" => $hoja->getCell('B52')->getValue(),
        "herramientas_salida1" => $hoja->getCell('D52')->getValue(),

        "herramientas_cantidad2" => $hoja->getCell('A53')->getValue(),
        "descripcion_herramientas2" => $hoja->getCell('B53')->getValue(),
        "herramientas_salida2" => $hoja->getCell('D53')->getValue(),

        "herramientas_cantidad3" => $hoja->getCell('A54')->getValue(),
        "descripcion_herramientas3" => $hoja->getCell('B54')->getValue(),
        "herramientas_salida3" => $hoja->getCell('D54')->getValue(),

        "herramientas_cantidad4" => $hoja->getCell('A55')->getValue(),
        "descripcion_herramientas4" => $hoja->getCell('B55')->getValue(),
        "herramientas_salida4" => $hoja->getCell('D55')->getValue(),

        "herramientas_cantidad5" => $hoja->getCell('A56')->getValue(),
        "descripcion_herramientas5" => $hoja->getCell('B56')->getValue(),
        "herramientas_salida5" => $hoja->getCell('D56')->getValue(),

        "herramientas_cantidad6" => $hoja->getCell('A57')->getValue(),
        "descripcion_herramientas6" => $hoja->getCell('B57')->getValue(),
        "herramientas_salida6" => $hoja->getCell('D57')->getValue(),

        "herramientas_cantidad7" => $hoja->getCell('A58')->getValue(),
        "descripcion_herramientas7" => $hoja->getCell('B58')->getValue(),
        "herramientas_salida7" => $hoja->getCell('D58')->getValue(),

        "herramientas_cantidad8" => $hoja->getCell('A59')->getValue(),
        "descripcion_herramientas8" => $hoja->getCell('B59')->getValue(),
        "herramientas_salida8" => $hoja->getCell('D59')->getValue(),

        //PIEZAS//

        "piezas_cantidad1" => $hoja->getCell('E52')->getValue(),
        "descripcion_piezas1" => $hoja->getCell('F52')->getValue(),
        "piezas_utilizadas1" => $hoja->getCell('H52')->getValue(),
        "sin_utilizar1" => $hoja->getCell('I52')->getValue(),
        "piezas_quitadas1" => $hoja->getCell('J52')->getValue(),
        "verificacion_piezas1" => $hoja->getCell('K52')->getValue(),
        
        "piezas_cantidad2" => $hoja->getCell('E53')->getValue(),
        "descripcion_piezas2" => $hoja->getCell('F53')->getValue(),
        "piezas_utilizadas2" => $hoja->getCell('H53')->getValue(),
        "sin_utilizar2" => $hoja->getCell('I53')->getValue(),
        "piezas_quitadas2" => $hoja->getCell('J53')->getValue(),
        "verificacion_piezas2" => $hoja->getCell('K53')->getValue(),
        
        "piezas_cantidad3" => $hoja->getCell('E54')->getValue(),
        "descripcion_piezas3" => $hoja->getCell('F54')->getValue(),
        "piezas_utilizadas3" => $hoja->getCell('H54')->getValue(),
        "sin_utilizar3" => $hoja->getCell('I54')->getValue(),
        "piezas_quitadas3" => $hoja->getCell('J54')->getValue(),
        "verificacion_piezas3" => $hoja->getCell('K54')->getValue(),
        
        "piezas_cantidad4" => $hoja->getCell('E55')->getValue(),
        "descripcion_piezas4" => $hoja->getCell('F55')->getValue(),
        "piezas_utilizadas4" => $hoja->getCell('H55')->getValue(),
        "sin_utilizar4" => $hoja->getCell('I55')->getValue(),
        "piezas_quitadas4" => $hoja->getCell('J55')->getValue(),
        "verificacion_piezas4" => $hoja->getCell('K55')->getValue(),
        
        "piezas_cantidad5" => $hoja->getCell('E56')->getValue(),
        "descripcion_piezas5" => $hoja->getCell('F56')->getValue(),
        "piezas_utilizadas5" => $hoja->getCell('H56')->getValue(),
        "sin_utilizar5" => $hoja->getCell('I56')->getValue(),
        "piezas_quitadas5" => $hoja->getCell('J56')->getValue(),
        "verificacion_piezas5" => $hoja->getCell('K56')->getValue(),
        
        "piezas_cantidad6" => $hoja->getCell('E57')->getValue(),
        "descripcion_piezas6" => $hoja->getCell('F57')->getValue(),
        "piezas_utilizadas6" => $hoja->getCell('H57')->getValue(),
        "sin_utilizar6" => $hoja->getCell('I57')->getValue(),
        "piezas_quitadas6" => $hoja->getCell('J57')->getValue(),
        "verificacion_piezas6" => $hoja->getCell('K57')->getValue(),
        
        "piezas_cantidad7" => $hoja->getCell('E58')->getValue(),
        "descripcion_piezas7" => $hoja->getCell('F58')->getValue(),
        "piezas_utilizadas7" => $hoja->getCell('H58')->getValue(),
        "sin_utilizar7" => $hoja->getCell('I58')->getValue(),
        "piezas_quitadas7" => $hoja->getCell('J58')->getValue(),
        "verificacion_piezas7" => $hoja->getCell('K58')->getValue(),
        
        "piezas_cantidad8" => $hoja->getCell('E59')->getValue(),
        "descripcion_piezas8" => $hoja->getCell('F59')->getValue(),
        "piezas_utilizadas8" => $hoja->getCell('H59')->getValue(),
        "sin_utilizar8" => $hoja->getCell('I59')->getValue(),
        "piezas_quitadas8" => $hoja->getCell('J59')->getValue(),
        "verificacion_piezas8" => $hoja->getCell('K59')->getValue(),
    
        //MATERIALES//

        "materiales_cantidad1" => $hoja->getCell('A63')->getValue(),
        "descripcion_materiales1" => $hoja->getCell('F63')->getValue(),
        "materiales_utilizados1" => $hoja->getCell('I63')->getValue(),
        "verificacion_material1" => $hoja->getCell('J63')->getValue(),
        "medida_materiales1" => $hoja->getCell('C63')->getValue(),

        "materiales_cantidad2" => $hoja->getCell('A64')->getValue(),
        "descripcion_materiales2" => $hoja->getCell('F64')->getValue(),
        "materiales_utilizados2" => $hoja->getCell('I64')->getValue(),
        "verificacion_material2" => $hoja->getCell('J64')->getValue(),
        "medida_materiales2" => $hoja->getCell('C64')->getValue(),

        "materiales_cantidad3" => $hoja->getCell('A65')->getValue(),
        "descripcion_materiales3" => $hoja->getCell('F65')->getValue(),
        "materiales_utilizados3" => $hoja->getCell('I65')->getValue(),
        "verificacion_material3" => $hoja->getCell('J65')->getValue(),
        "medida_materiales3" => $hoja->getCell('C65')->getValue(),

        "materiales_cantidad4" => $hoja->getCell('A66')->getValue(),
        "descripcion_materiales4" => $hoja->getCell('F66')->getValue(),
        "materiales_utilizados4" => $hoja->getCell('I66')->getValue(),
        "verificacion_material4" => $hoja->getCell('J66')->getValue(),
        "medida_materiales4" => $hoja->getCell('C66')->getValue(),

        "materiales_cantidad5" => $hoja->getCell('A67')->getValue(),
        "descripcion_materiales5" => $hoja->getCell('F67')->getValue(),
        "materiales_utilizados5" => $hoja->getCell('I67')->getValue(),
        "verificacion_material5" => $hoja->getCell('J67')->getValue(),
        "medida_materiales5" => $hoja->getCell('C67')->getValue(),

        "materiales_cantidad6" => $hoja->getCell('A68')->getValue(),
        "descripcion_materiales6" => $hoja->getCell('F68')->getValue(),
        "materiales_utilizados6" => $hoja->getCell('I68')->getValue(),
        "verificacion_material6" => $hoja->getCell('J68')->getValue(),
        "medida_materiales6" => $hoja->getCell('C68')->getValue(),

        "materiales_cantidad7" => $hoja->getCell('A69')->getValue(),
        "descripcion_materiales7" => $hoja->getCell('F69')->getValue(),
        "materiales_utilizados7" => $hoja->getCell('I69')->getValue(),
        "verificacion_material7" => $hoja->getCell('J69')->getValue(),
        "medida_materiales7" => $hoja->getCell('C69')->getValue(),

        "materiales_cantidad8" => $hoja->getCell('A70')->getValue(),
        "descripcion_materiales8" => $hoja->getCell('F70')->getValue(),
        "materiales_utilizados8" => $hoja->getCell('I70')->getValue(),
        "verificacion_material8" => $hoja->getCell('J70')->getValue(),
        "medida_materiales8" => $hoja->getCell('C70')->getValue(),

        //TERMOGRAFIAS//
        
        "parte_equipo1" => $hoja->getCell('C75')->getValue(),
        "parte_equipo2" => $hoja->getCell('C76')->getValue(),
        "parte_equipo3" => $hoja->getCell('C77')->getValue(),
        "parte_equipo4" => $hoja->getCell('C78')->getValue(),
        "parte_equipo5" => $hoja->getCell('C79')->getValue(),
        "parte_equipo6" => $hoja->getCell('C80')->getValue(),

        "termografia_equipo1" => $hoja->getCell('D75')->getValue(),
        "termografia_equipo2" => $hoja->getCell('D76')->getValue(),
        "termografia_equipo3" => $hoja->getCell('D77')->getValue(),
        "termografia_equipo4" => $hoja->getCell('D78')->getValue(),
        "termografia_equipo5" => $hoja->getCell('D79')->getValue(),
        "termografia_equipo6" => $hoja->getCell('D80')->getValue(),

        "vibraciones_equipo1" => $hoja->getCell('E75')->getValue(),
        "vibraciones_equipo2" => $hoja->getCell('E76')->getValue(),
        "vibraciones_equipo3" => $hoja->getCell('E77')->getValue(),
        "vibraciones_equipo4" => $hoja->getCell('E78')->getValue(),
        "vibraciones_equipo5" => $hoja->getCell('E79')->getValue(),
        "vibraciones_equipo6" => $hoja->getCell('E80')->getValue(),

        "nuevasvibraciones_equipo1" => $hoja->getCell('G75')->getValue(),
        "nuevasvibraciones_equipo2" => $hoja->getCell('G76')->getValue(),
        "nuevasvibraciones_equipo3" => $hoja->getCell('G77')->getValue(),
        "nuevasvibraciones_equipo4" => $hoja->getCell('G78')->getValue(),
        "nuevasvibraciones_equipo5" => $hoja->getCell('G79')->getValue(),
        "nuevasvibraciones_equipo6" => $hoja->getCell('G80')->getValue(),

        "multimetrorango_equipo1" => $hoja->getCell('H75')->getValue(),
        "multimetrorango_equipo2" => $hoja->getCell('H76')->getValue(),
        "multimetrorango_equipo3" => $hoja->getCell('H77')->getValue(),
        "multimetrorango_equipo4" => $hoja->getCell('H78')->getValue(),
        "multimetrorango_equipo5" => $hoja->getCell('H79')->getValue(),
        "multimetrorango_equipo6" => $hoja->getCell('H80')->getValue(),

        "multimetroamperaje_equipo1" => $hoja->getCell('I75')->getValue(),
        "multimetroamperaje_equipo2" => $hoja->getCell('I76')->getValue(),
        "multimetroamperaje_equipo3" => $hoja->getCell('I77')->getValue(),
        "multimetroamperaje_equipo4" => $hoja->getCell('I78')->getValue(),
        "multimetroamperaje_equipo5" => $hoja->getCell('I79')->getValue(),
        "multimetroamperaje_equipo6" => $hoja->getCell('I80')->getValue(),

        "observaciones_equipo1" => $hoja->getCell('J75')->getValue(),
        "observaciones_equipo2" => $hoja->getCell('J76')->getValue(),
        "observaciones_equipo3" => $hoja->getCell('J77')->getValue(),
        "observaciones_equipo4" => $hoja->getCell('J78')->getValue(),
        "observaciones_equipo5" => $hoja->getCell('J79')->getValue(),
        "observaciones_equipo6" => $hoja->getCell('J80')->getValue(),

        ];
        $imagenes = $hoja->getDrawingCollection();


    } catch (Exception $e) {
        die("Error al procesar el archivo: " . $e->getMessage());
    }
} else {
    die("Archivo no especificado.");
}
    // EXTRAER LAS IMAGENES DE PRUEBA

    
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/procesar.css">
    <title>Revisión de Datos</title>
</head>
<body class="body">
    <div class="cuerpo">
    <h1 class="titulo_principal">Revisión de Datos del Formulario</h1>
    <form action="guardar.php" method="post" class="formulario1" enctype="multipart/form-data">

        <div class="formulariog">
    <input type="hidden" name="archivo" value="<?php echo htmlspecialchars($archivo, ENT_QUOTES, 'UTF-8'); ?>">
    <label>CODIGO DE LA ORDEN DE TRABAJO: <input type="text" name="codigoorden" value="<?php echo htmlspecialchars($datosFormulario['codigoorden'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></label><br>

    <label>Fecha Inicial: <input type="date" name="fechainicial" value="<?php echo htmlspecialchars($datosFormulario['fechainicial'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></label><br>
    <label>Hora Inicial: <input type="time" name="horainicial" value="<?php echo htmlspecialchars($datosFormulario['horainicial'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></label><br>
    <label>Nombre del Solicitante: <input type="text" name="nombreSolicitante" value="<?php echo htmlspecialchars($datosFormulario['nombreSolicitante'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></label><br>
    <label>Cargo del Solicitante: <input type="text" name="cargoSolicitante" value="<?php echo htmlspecialchars($datosFormulario['cargoSolicitante'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></label><br>
    <label>Objeto Dañado: <input type="text" name="objetoDañado" value="<?php echo htmlspecialchars($datosFormulario['objetoDañado'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></label><br>
    <label>Código: <input type="text" name="codigo" value="<?php echo htmlspecialchars($datosFormulario['codigo'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></label><br>
    <label>Marca: <input type="text" name="marca" value="<?php echo htmlspecialchars($datosFormulario['marca'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></label><br>
    <label>Descripción de Daños: <textarea name="descripcionDaños"><?php echo htmlspecialchars($datosFormulario['descripcionDaños'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea></label><br>
    <label>Ubicación: <input type="text" name="ubicacion" value="<?php echo htmlspecialchars($datosFormulario['ubicacion'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></label><br>
    <label>Tipo de Mantenimiento: <input type="text" name="tipoMantenimiento" value="<?php echo htmlspecialchars($datosFormulario['tipoMantenimiento'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></label><br>
    <label>Descripción del Trabajo: <textarea name="descripcionTrabajo"><?php echo htmlspecialchars($datosFormulario['descripcionTrabajo'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea></label><br>
    <label>Fecha de Cierre: <input type="date" name="fechaCierre" value="<?php echo htmlspecialchars($datosFormulario['fechaCierre'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></label><br>
    <label>Hora de Cierre: <input type="time" name="hora_cierre" value="<?php echo htmlspecialchars($datosFormulario['hora_cierre'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></label><br>
    <label>Responsable Miembro de la Compañía: <input type="text" name="responsable_Miembro_De_La_Compañia" value="<?php echo htmlspecialchars($datosFormulario['responsable_Miembro_De_La_Compañia'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></label><br>
    <label>Proveedor (NPROV): <input type="text" name="nprov" value="<?php echo htmlspecialchars($datosFormulario['nprov'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></label><br>
    <label>VoBo Mantenimiento: <input type="text" name="voboMantenimiento" value="<?php echo htmlspecialchars($datosFormulario['voboMantenimiento'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></label><br>
    <label>Descripción de Inocuidad: <textarea name="descripcion_inocuidad"><?php echo htmlspecialchars($datosFormulario['descripcion_inocuidad'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea></label><br>
    <label>Retiro de Inocuidad: <input type="text" name="retiro_inocuidad" value="<?php echo htmlspecialchars($datosFormulario['retiro_inocuidad'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></label><br>
    <label>Descripción de Novedad: <textarea name="descripcion_novedad"><?php echo htmlspecialchars($datosFormulario['descripcion_novedad'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea></label><br>
    <label>Riesgo de Inocuidad: <input type="text" name="riesgo_inocuidad" value="<?php echo htmlspecialchars($datosFormulario['riesgo_inocuidad'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></label><br>
    <label>Implementos: <textarea name="implementos"><?php echo htmlspecialchars($datosFormulario['implementos'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea></label><br>
    <label>Fecha de Revisión de Limpieza: <input type="date" name="fecha_revisionl" value="<?php echo htmlspecialchars($datosFormulario['fecha_revisionl'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></label><br>
    <label>Hora de Revisión de Limpieza: <input type="time" name="hora_revisionl" value="<?php echo htmlspecialchars($datosFormulario['hora_revisionl'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></label><br>
    <label>Ubicación: <input type="text" name="ubicacion" value="<?php echo htmlspecialchars($datosFormulario['ubicacion'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></label><br>
    <label>Responsable del Control: <input type="text" name="control_responsable" value="<?php echo htmlspecialchars($datosFormulario['control_responsable'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></label><br>
    <label>Trabajo a Realizar: <textarea name="trabajo_realizar"><?php echo htmlspecialchars($datosFormulario['trabajo_realizar'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea></label><br>
    <label>Fecha del Control: <input type="date" name="fechacontrol" value="<?php echo htmlspecialchars($datosFormulario['fechacontrol'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></label><br>
    <label>Cargo del Responsable: <input type="text" name="cargo_control" value="<?php echo htmlspecialchars($datosFormulario['cargo_control'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></label><br>
    <label>VoBo de Ingreso: <input type="text" name="Vobo_ingreso" value="<?php echo htmlspecialchars($datosFormulario['Vobo_ingreso'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></label><br>
    <label>VoBo de Salida: <input type="text" name="Vobo_salida" value="<?php echo htmlspecialchars($datosFormulario['Vobo_salida'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></label><br>
        <!-- TABLA DE HERRAMIENTAS, MATERIALES Y PIEZAS -->
        </div>

    </div>
    <div class="formulariog">
                <label for="firma_solicitante">Solicitante</label>
                <canvas id="canvasfirma1" width="400" height="200" style="border: 1px solid #000;" required></canvas>
                <button type="button" id="limpiarFirma">Limpiar</button>
                <button type="button" id="guardarFirma">Guardar Firma</button>
                <input type="hidden" name="firma_solicitante" id="firma_solicitante">
            </div>
            <div class="formulariog">
                <label for="firma_autorizado">Autorizado Por:</label>
                <canvas id="canvasfirma2" width="400" height="200" style="border: 1px solid #000;" required></canvas>
                <button type="button" id="limpiarFirma2">Limpiar</button>
                <button type="button" id="guardarFirma2">Guardar Firma</button>
                <input type="hidden" name="firma_autorizado" id="firma_autorizado">
            </div>
            <label for="firma_respLim">Responsable De La limpieza</label>
                <canvas name="firmas" id="canvasfirma3" width="400" height="200" style="border: 1px solid #000;" required></canvas>
                <button type="button" id="limpiarFirma3">Limpiar</button>
                <button type="button" id="guardarFirma3">Guardar Firma</button>
                <input type="hidden" name="firma_respLim" id="firma_respLim">
            </div>
            <div class="formulariog">
                <label for="firma_respLim2">Responsable De Revisar La limpieza</label>
                <canvas id="canvasfirma4" width="400" height="200" style="border: 1px solid #000;"></canvas>
                <button type="button" id="limpiarFirma4">Limpiar</button>
                <button type="button" id="guardarFirma4">Guardar Firma</button>
                <input type="hidden" name="firma_respLim2" id="firma_respLim2">
            </div>
        <table class="tabla">
<div class="tabla-contenedor">
    <thead>
        <tr>  
            <th>Herramientas</th>
        </tr>
        <tr>
            <th>Registro Ingreso</th><th></th><th>Salida</th>
        </tr>
        <tr>
            <th>Cantidad</th><th>Descripcion</th><th>Cantidad</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><input type="text" id="herramientas_cantidad1" name="herramientas_cantidad1" value="<?php echo htmlspecialchars($datosFormulario['herramientas_cantidad1'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Cantidad"></td>
            <td><input type="text" id="descripcion_herramientas1" name="descripcion_herramientas1" value="<?php echo htmlspecialchars($datosFormulario['descripcion_herramientas1'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Descripción"></td>
            <td><input type="text" id="herramientas_salida1" name="herramientas_salida1" value="<?php echo htmlspecialchars($datosFormulario['herramientas_salida1'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Cantidad de Salida"></td>
        </tr>
        <tr>
            <td><input type="text" id="herramientas_cantidad2" name="herramientas_cantidad2" value="<?php echo htmlspecialchars($datosFormulario['herramientas_cantidad2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Cantidad"></td>
            <td><input type="text" id="descripcion_herramientas2" name="descripcion_herramientas2" value="<?php echo htmlspecialchars($datosFormulario['descripcion_herramientas2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Descripción"></td>
            <td><input type="text" id="herramientas_salida2" name="herramientas_salida2" value="<?php echo htmlspecialchars($datosFormulario['herramientas_salida2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Cantidad de Salida"></td>
        </tr>
        <tr>
            <td><input type="text" id="herramientas_cantidad3" name="herramientas_cantidad3" value="<?php echo htmlspecialchars($datosFormulario['herramientas_cantidad3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Cantidad"></td>
            <td><input type="text" id="descripcion_herramientas3" name="descripcion_herramientas3" value="<?php echo htmlspecialchars($datosFormulario['descripcion_herramientas3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Descripción"></td>
            <td><input type="text" id="herramientas_salida3" name="herramientas_salida3" value="<?php echo htmlspecialchars($datosFormulario['herramientas_salida3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Cantidad de Salida"></td>
        </tr>
        <tr>
            <td><input type="text" id="herramientas_cantidad4" name="herramientas_cantidad4" value="<?php echo htmlspecialchars($datosFormulario['herramientas_cantidad4'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Cantidad"></td>
            <td><input type="text" id="descripcion_herramientas4" name="descripcion_herramientas4" value="<?php echo htmlspecialchars($datosFormulario['descripcion_herramientas4'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Descripción"></td>
            <td><input type="text" id="herramientas_salida4" name="herramientas_salida4" value="<?php echo htmlspecialchars($datosFormulario['herramientas_salida4'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Cantidad de Salida"></td>
        </tr>
        <tr>
            <td><input type="text" id="herramientas_cantidad5" name="herramientas_cantidad5" value="<?php echo htmlspecialchars($datosFormulario['herramientas_cantidad5'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Cantidad"></td>
            <td><input type="text" id="descripcion_herramientas5" name="descripcion_herramientas5" value="<?php echo htmlspecialchars($datosFormulario['descripcion_herramientas5'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Descripción"></td>
            <td><input type="text" id="herramientas_salida5" name="herramientas_salida5" value="<?php echo htmlspecialchars($datosFormulario['herramientas_salida5'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Cantidad de Salida"></td>
        </tr>
        <tr>
            <td><input type="text" id="herramientas_cantidad6" name="herramientas_cantidad6" value="<?php echo htmlspecialchars($datosFormulario['herramientas_cantidad6'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Cantidad"></td>
            <td><input type="text" id="descripcion_herramientas6" name="descripcion_herramientas6" value="<?php echo htmlspecialchars($datosFormulario['descripcion_herramientas6'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Descripción"></td>
            <td><input type="text" id="herramientas_salida6" name="herramientas_salida6" value="<?php echo htmlspecialchars($datosFormulario['herramientas_salida6'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Cantidad de Salida"></td>
        </tr>
        <tr>
            <td><input type="text" id="herramientas_cantidad7" name="herramientas_cantidad7" value="<?php echo htmlspecialchars($datosFormulario['herramientas_cantidad7'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Cantidad"></td>
            <td><input type="text" id="descripcion_herramientas7" name="descripcion_herramientas7" value="<?php echo htmlspecialchars($datosFormulario['descripcion_herramientas7'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Descripción"></td>
            <td><input type="text" id="herramientas_salida7" name="herramientas_salida7" value="<?php echo htmlspecialchars($datosFormulario['herramientas_salida7'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Cantidad de Salida"></td>
        </tr>
        <tr>
            <td><input type="text" id="herramientas_cantidad8" name="herramientas_cantidad8" value="<?php echo htmlspecialchars($datosFormulario['herramientas_cantidad8'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Cantidad"></td>
            <td><input type="text" id="descripcion_herramientas8" name="descripcion_herramientas8" value="<?php echo htmlspecialchars($datosFormulario['descripcion_herramientas8'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Descripción"></td>
            <td><input type="text" id="herramientas_salida8" name="herramientas_salida8" value="<?php echo htmlspecialchars($datosFormulario['herramientas_salida8'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Cantidad de Salida"></td>
        </tr>
    </tbody>
</table>

</table>
<table>
    <thead>
        <tr>
            <th>Piezas (Incluir repuestos)</th>
        </tr>
        <tr>
            <th>Registro Ingreso</th>
        </tr>
        <tr>
            <th>Cantidad</th><th>Descripcion</th><th>Utilizado</th><th>Sin Utilizar</th><th>Desinstalado</th><th>Verificacion De Salida</th>
        </tr>
    </thead>
    <tbody>
    <tr>
        <th><input type="text" placeholder="Cantidad" id="piezas_cantidad1" name="piezas_cantidad1" value="<?php echo htmlspecialchars($datosFormulario['piezas_cantidad1'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></th>
        <th><input type="text" placeholder="Descripcion" id="descripcion_piezas1" name="descripcion_piezas1" value="<?php echo htmlspecialchars($datosFormulario['descripcion_piezas1'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></th>
        <th><input type="text" placeholder="Utilizado" id="piezas_utilizadas1" name="piezas_utilizadas1" value="<?php echo htmlspecialchars($datosFormulario['piezas_utilizadas1'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></th>
        <th><input type="text" placeholder="No Utilizado" id="sin_utilizar1" name="sin_utilizar1" value="<?php echo htmlspecialchars($datosFormulario['sin_utilizar1'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></th>
        <th><input type="text" placeholder="Desinstalado" id="piezas_quitadas1" name="piezas_quitadas1" value="<?php echo htmlspecialchars($datosFormulario['piezas_quitadas1'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></th>
        <th><input type="text" placeholder="Verificacion" id="verificacion_piezas1" name="verificacion_piezas1" value="<?php echo htmlspecialchars($datosFormulario['verificacion_piezas1'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></th>
    </tr>
    <tr>
        <th><input type="text" placeholder="Cantidad" id="piezas_cantidad2" name="piezas_cantidad2" value="<?php echo htmlspecialchars($datosFormulario['piezas_cantidad2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></th>
        <th><input type="text" placeholder="Descripcion" id="descripcion_piezas2" name="descripcion_piezas2" value="<?php echo htmlspecialchars($datosFormulario['descripcion_piezas2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></th>
        <th><input type="text" placeholder="Utilizado" id="piezas_utilizadas2" name="piezas_utilizadas2" value="<?php echo htmlspecialchars($datosFormulario['piezas_utilizadas2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></th>
        <th><input type="text" placeholder="No Utilizado" id="sin_utilizar2" name="sin_utilizar2" value="<?php echo htmlspecialchars($datosFormulario['sin_utilizar2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></th>
        <th><input type="text" placeholder="Desinstalado" id="piezas_quitadas2" name="piezas_quitadas2" value="<?php echo htmlspecialchars($datosFormulario['piezas_quitadas2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></th>
        <th><input type="text" placeholder="Verificacion" id="verificacion_piezas2" name="verificacion_piezas2" value="<?php echo htmlspecialchars($datosFormulario['verificacion_piezas2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></th>
    </tr>
    <tr>
        <th><input type="text" placeholder="Cantidad" id="piezas_cantidad3" name="piezas_cantidad3" value="<?php echo htmlspecialchars($datosFormulario['piezas_cantidad3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></th>
        <th><input type="text" placeholder="Descripcion" id="descripcion_piezas3" name="descripcion_piezas3" value="<?php echo htmlspecialchars($datosFormulario['descripcion_piezas3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></th>
        <th><input type="text" placeholder="Utilizado" id="piezas_utilizadas3" name="piezas_utilizadas3" value="<?php echo htmlspecialchars($datosFormulario['piezas_utilizadas3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></th>
        <th><input type="text" placeholder="No Utilizado" id="sin_utilizar3" name="sin_utilizar3" value="<?php echo htmlspecialchars($datosFormulario['sin_utilizar3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></th>
        <th><input type="text" placeholder="Desinstalado" id="piezas_quitadas3" name="piezas_quitadas3" value="<?php echo htmlspecialchars($datosFormulario['piezas_quitadas3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></th>
        <th><input type="text" placeholder="Verificacion" id="verificacion_piezas3" name="verificacion_piezas3" value="<?php echo htmlspecialchars($datosFormulario['verificacion_piezas3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></th>
    </tr>
    <tr>
        <th><input type="text" placeholder="Cantidad" id="piezas_cantidad4" name="piezas_cantidad4" value="<?php echo htmlspecialchars($datosFormulario['piezas_cantidad4'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></th>
        <th><input type="text" placeholder="Descripcion" id="descripcion_piezas4" name="descripcion_piezas4" value="<?php echo htmlspecialchars($datosFormulario['descripcion_piezas4'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></th>
        <th><input type="text" placeholder="Utilizado" id="piezas_utilizadas4" name="piezas_utilizadas4" value="<?php echo htmlspecialchars($datosFormulario['piezas_utilizadas4'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></th>
        <th><input type="text" placeholder="No Utilizado" id="sin_utilizar4" name="sin_utilizar4" value="<?php echo htmlspecialchars($datosFormulario['sin_utilizar4'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></th>
        <th><input type="text" placeholder="Desinstalado" id="piezas_quitadas4" name="piezas_quitadas4" value="<?php echo htmlspecialchars($datosFormulario['piezas_quitadas4'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></th>
        <th><input type="text" placeholder="Verificacion" id="verificacion_piezas4" name="verificacion_piezas4" value="<?php echo htmlspecialchars($datosFormulario['verificacion_piezas4'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></th>
    </tr>
    <tr>
        <th><input type="text" placeholder="Cantidad" id="piezas_cantidad5" name="piezas_cantidad5" value="<?php echo htmlspecialchars($datosFormulario['piezas_cantidad5'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></th>
        <th><input type="text" placeholder="Descripcion" id="descripcion_piezas5" name="descripcion_piezas5" value="<?php echo htmlspecialchars($datosFormulario['descripcion_piezas5'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></th>
        <th><input type="text" placeholder="Utilizado" id="piezas_utilizadas5" name="piezas_utilizadas5" value="<?php echo htmlspecialchars($datosFormulario['piezas_utilizadas5'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></th>
        <th><input type="text" placeholder="No Utilizado" id="sin_utilizar5" name="sin_utilizar5" value="<?php echo htmlspecialchars($datosFormulario['sin_utilizar5'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></th>
        <th><input type="text" placeholder="Desinstalado" id="piezas_quitadas5" name="piezas_quitadas5" value="<?php echo htmlspecialchars($datosFormulario['piezas_quitadas5'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></th>
        <th><input type="text" placeholder="Verificacion" id="verificacion_piezas5" name="verificacion_piezas5" value="<?php echo htmlspecialchars($datosFormulario['verificacion_piezas5'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></th>
    </tr>
    <tr>
        <th><input type="text" placeholder="Cantidad" id="piezas_cantidad6" name="piezas_cantidad6" value="<?php echo htmlspecialchars($datosFormulario['piezas_cantidad6'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></th>
        <th><input type="text" placeholder="Descripcion" id="descripcion_piezas6" name="descripcion_piezas6" value="<?php echo htmlspecialchars($datosFormulario['descripcion_piezas6'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></th>
        <th><input type="text" placeholder="Utilizado" id="piezas_utilizadas6" name="piezas_utilizadas6" value="<?php echo htmlspecialchars($datosFormulario['piezas_utilizadas6'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></th>
        <th><input type="text" placeholder="No Utilizado" id="sin_utilizar6" name="sin_utilizar6" value="<?php echo htmlspecialchars($datosFormulario['sin_utilizar6'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></th>
        <th><input type="text" placeholder="Desinstalado" id="piezas_quitadas6" name="piezas_quitadas6" value="<?php echo htmlspecialchars($datosFormulario['piezas_quitadas6'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></th>
        <th><input type="text" placeholder="Verificacion" id="verificacion_piezas6" name="verificacion_piezas6" value="<?php echo htmlspecialchars($datosFormulario['verificacion_piezas6'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></th>
    </tr>
    <tr>
        <th><input type="text" placeholder="Cantidad" id="piezas_cantidad7" name="piezas_cantidad7" value="<?php echo htmlspecialchars($datosFormulario['piezas_cantidad7'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></th>
        <th><input type="text" placeholder="Descripcion" id="descripcion_piezas7" name="descripcion_piezas7" value="<?php echo htmlspecialchars($datosFormulario['descripcion_piezas7'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></th>
        <th><input type="text" placeholder="Utilizado" id="piezas_utilizadas7" name="piezas_utilizadas7" value="<?php echo htmlspecialchars($datosFormulario['piezas_utilizadas7'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></th>
        <th><input type="text" placeholder="No Utilizado" id="sin_utilizar7" name="sin_utilizar7" value="<?php echo htmlspecialchars($datosFormulario['sin_utilizar7'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></th>
        <th><input type="text" placeholder="Desinstalado" id="piezas_quitadas7" name="piezas_quitadas7" value="<?php echo htmlspecialchars($datosFormulario['piezas_quitadas7'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></th>
        <th><input type="text" placeholder="Verificacion" id="verificacion_piezas7" name="verificacion_piezas7" value="<?php echo htmlspecialchars($datosFormulario['verificacion_piezas7'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></th>
    </tr>
    <tr>
        <th><input type="text" placeholder="Cantidad" id="piezas_cantidad8" name="piezas_cantidad8" value="<?php echo htmlspecialchars($datosFormulario['piezas_cantidad8'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></th>
        <th><input type="text" placeholder="Descripcion" id="descripcion_piezas8" name="descripcion_piezas8" value="<?php echo htmlspecialchars($datosFormulario['descripcion_piezas8'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></th>
        <th><input type="text" placeholder="Utilizado" id="piezas_utilizadas8" name="piezas_utilizadas8" value="<?php echo htmlspecialchars($datosFormulario['piezas_utilizadas8'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></th>
        <th><input type="text" placeholder="No Utilizado" id="sin_utilizar8" name="sin_utilizar8" value="<?php echo htmlspecialchars($datosFormulario['sin_utilizar8'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></th>
        <th><input type="text" placeholder="Desinstalado" id="piezas_quitadas8" name="piezas_quitadas8" value="<?php echo htmlspecialchars($datosFormulario['piezas_quitadas8'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></th>
        <th><input type="text" placeholder="Verificacion" id="verificacion_piezas8" name="verificacion_piezas8" value="<?php echo htmlspecialchars($datosFormulario['verificacion_piezas8'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></th>
    </tr>

</tbody>

</table>
<table>
    <thead>
        <tr>
            <th>Registro Ingreso Materiales</th>
        </tr>
        <tr>
            <th>Cantidad Ingreso</th><th>Unidad De Medida</th><th>Descripcion</th><th>Utilizado</th><th>Verificacion De Salida</th>
        </tr>
    </thead>

    <tbody>
    <tr>
        <td><input type="text" id="materiales_cantidad1" name="materiales_cantidad1" value="<?php echo htmlspecialchars($datosFormulario['materiales_cantidad1'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></td>
        <td><input type="text" id="medida_materiales1" name="medida_materiales1" value="<?php echo htmlspecialchars($datosFormulario['medida_materiales1'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></td>
        <td><input type="text" id="descripcion_materiales1" name="descripcion_materiales1" value="<?php echo htmlspecialchars($datosFormulario['descripcion_materiales1'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></td>
        <td><input type="text" id="materiales_utilizados1" name="materiales_utilizados1" value="<?php echo htmlspecialchars($datosFormulario['materiales_utilizados1'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></td>
        <td><input type="text" id="verificacion_material1" name="verificacion_material1" value="<?php echo htmlspecialchars($datosFormulario['verificacion_material1'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></td>
    </tr>
    <tr>
        <td><input type="text" id="materiales_cantidad2" name="materiales_cantidad2" value="<?php echo htmlspecialchars($datosFormulario['materiales_cantidad2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></td>
        <td><input type="text" id="medida_materiales2" name="medida_materiales2" value="<?php echo htmlspecialchars($datosFormulario['medida_materiales2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></td>
        <td><input type="text" id="descripcion_materiales2" name="descripcion_materiales2" value="<?php echo htmlspecialchars($datosFormulario['descripcion_materiales2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></td>
        <td><input type="text" id="materiales_utilizados2" name="materiales_utilizados2" value="<?php echo htmlspecialchars($datosFormulario['materiales_utilizados2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></td>
        <td><input type="text" id="verificacion_material2" name="verificacion_material2" value="<?php echo htmlspecialchars($datosFormulario['verificacion_material2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></td>
    </tr>
    <tr>
        <td><input type="text" id="materiales_cantidad3" name="materiales_cantidad3" value="<?php echo htmlspecialchars($datosFormulario['materiales_cantidad3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></td>
        <td><input type="text" id="medida_materiales3" name="medida_materiales3" value="<?php echo htmlspecialchars($datosFormulario['medida_materiales3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></td>
        <td><input type="text" id="descripcion_materiales3" name="descripcion_materiales3" value="<?php echo htmlspecialchars($datosFormulario['descripcion_materiales3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></td>
        <td><input type="text" id="materiales_utilizados3" name="materiales_utilizados3" value="<?php echo htmlspecialchars($datosFormulario['materiales_utilizados3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></td>
        <td><input type="text" id="verificacion_material3" name="verificacion_material3" value="<?php echo htmlspecialchars($datosFormulario['verificacion_material3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></td>
    </tr>
    <tr>
        <td><input type="text" id="materiales_cantidad4" name="materiales_cantidad4" value="<?php echo htmlspecialchars($datosFormulario['materiales_cantidad4'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></td>
        <td><input type="text" id="medida_materiales4" name="medida_materiales4" value="<?php echo htmlspecialchars($datosFormulario['medida_materiales4'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></td>
        <td><input type="text" id="descripcion_materiales4" name="descripcion_materiales4" value="<?php echo htmlspecialchars($datosFormulario['descripcion_materiales4'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></td>
        <td><input type="text" id="materiales_utilizados4" name="materiales_utilizados4" value="<?php echo htmlspecialchars($datosFormulario['materiales_utilizados4'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></td>
        <td><input type="text" id="verificacion_material4" name="verificacion_material4" value="<?php echo htmlspecialchars($datosFormulario['verificacion_material4'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></td>
    </tr>
    <tr>
        <td><input type="text" id="materiales_cantidad5" name="materiales_cantidad5" value="<?php echo htmlspecialchars($datosFormulario['materiales_cantidad5'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></td>
        <td><input type="text" id="medida_materiales5" name="medida_materiales5" value="<?php echo htmlspecialchars($datosFormulario['medida_materiales5'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></td>
        <td><input type="text" id="descripcion_materiales5" name="descripcion_materiales5" value="<?php echo htmlspecialchars($datosFormulario['descripcion_materiales5'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></td>
        <td><input type="text" id="materiales_utilizados5" name="materiales_utilizados5" value="<?php echo htmlspecialchars($datosFormulario['materiales_utilizados5'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></td>
        <td><input type="text" id="verificacion_material5" name="verificacion_material5" value="<?php echo htmlspecialchars($datosFormulario['verificacion_material5'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></td>
    </tr>
    <tr>
        <td><input type="text" id="materiales_cantidad6" name="materiales_cantidad6" value="<?php echo htmlspecialchars($datosFormulario['materiales_cantidad6'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></td>
        <td><input type="text" id="medida_materiales6" name="medida_materiales6" value="<?php echo htmlspecialchars($datosFormulario['medida_materiales6'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></td>
        <td><input type="text" id="descripcion_materiales6" name="descripcion_materiales6" value="<?php echo htmlspecialchars($datosFormulario['descripcion_materiales6'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></td>
        <td><input type="text" id="materiales_utilizados6" name="materiales_utilizados6" value="<?php echo htmlspecialchars($datosFormulario['materiales_utilizados6'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></td>
        <td><input type="text" id="verificacion_material6" name="verificacion_material6" value="<?php echo htmlspecialchars($datosFormulario['verificacion_material6'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></td>
    </tr>
    <tr>
        <td><input type="text" id="materiales_cantidad7" name="materiales_cantidad7" value="<?php echo htmlspecialchars($datosFormulario['materiales_cantidad7'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></td>
        <td><input type="text" id="medida_materiales7" name="medida_materiales7" value="<?php echo htmlspecialchars($datosFormulario['medida_materiales7'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></td>
        <td><input type="text" id="descripcion_materiales7" name="descripcion_materiales7" value="<?php echo htmlspecialchars($datosFormulario['descripcion_materiales7'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></td>
        <td><input type="text" id="materiales_utilizados7" name="materiales_utilizados7" value="<?php echo htmlspecialchars($datosFormulario['materiales_utilizados7'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></td>
        <td><input type="text" id="verificacion_material7" name="verificacion_material7" value="<?php echo htmlspecialchars($datosFormulario['verificacion_material7'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></td>
    </tr>
    <tr>
        <td><input type="text" id="materiales_cantidad8" name="materiales_cantidad8" value="<?php echo htmlspecialchars($datosFormulario['materiales_cantidad8'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></td>
        <td><input type="text" id="medida_materiales8" name="medida_materiales8" value="<?php echo htmlspecialchars($datosFormulario['medida_materiales8'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></td>
        <td><input type="text" id="descripcion_materiales8" name="descripcion_materiales8" value="<?php echo htmlspecialchars($datosFormulario['descripcion_materiales8'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></td>
        <td><input type="text" id="materiales_utilizados8" name="materiales_utilizados8" value="<?php echo htmlspecialchars($datosFormulario['materiales_utilizados8'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></td>
        <td><input type="text" id="verificacion_material8" name="verificacion_material8" value="<?php echo htmlspecialchars($datosFormulario['verificacion_material8'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></td>
    </tr>
</tbody>

</table>

</table>
</div>


    <!-- TABLA DE TERMOGRAFÍA Y OTROS (ORDENADA SEGÚN LA IMAGEN) -->
    <div class="tabla-contenedor">
    <table class="excel-table">
        <thead>
            <tr>
                <th>Parte del Equipo</th>
                <th>Termografía</th>
                <th>Analizador de vibraciones</th>
                <th>Vibraciones /Nuevas mediciones</th>
                <th>Multímetro o Rango</th>
                <th>Multímetro Amperaje</th>
                <th>Observaciones</th>
            </tr>
        </thead>
        <tbody>
            <?php for ($i = 1; $i <= 6; $i++): ?>
            <tr>
                <td><input type="text" name="parte_equipo<?= $i ?>" value="<?php echo htmlspecialchars($datosFormulario['parte_equipo'.$i] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></td>
                <td><input type="text" name="termografia_equipo<?= $i ?>" value="<?php echo htmlspecialchars($datosFormulario['termografia_equipo'.$i] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></td>
                <td><input type="text" name="analizador_vibraciones<?= $i ?>" value="<?php echo htmlspecialchars($datosFormulario['vibraciones_equipo'.$i] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></td>
                <td><input type="text" name="nuevasvibraciones_equipo<?= $i ?>" value="<?php echo htmlspecialchars($datosFormulario['nuevasvibraciones_equipo'.$i] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></td>
                <td><input type="text" name="multimetrorango_equipo<?= $i ?>" value="<?php echo htmlspecialchars($datosFormulario['multimetrorango_equipo'.$i] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></td>
                <td><input type="text" name="multimetroamperaje_equipo<?= $i ?>" value="<?php echo htmlspecialchars($datosFormulario['multimetroamperaje_equipo'.$i] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></td>
                <td><input type="text" name="observaciones_equipo<?= $i ?>" value="<?php echo htmlspecialchars($datosFormulario['observaciones_equipo'.$i] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></td>
            </tr>
            <?php endfor; ?>
        </tbody>
    </table>
            </div>
            <div>
                        <?php
                        use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;
                        $imagenes_celdas = [
    ['celda' => 'A82', 'name' => 'imagen_a82'],
    ['celda' => 'G82', 'name' => 'imagen_g82'],
    ['celda' => 'A97', 'name' => 'imagen_a97'],
    ['celda' => 'G97', 'name' => 'imagen_g97'],
];
                        foreach ($imagenes_celdas as $imagen_celda) {
                            $imgSrc = '';
                            $ext = '';
                            foreach ($imagenes as $img) {
                                if ($img->getCoordinates() == $imagen_celda['celda']) {
                                    if ($img instanceof \PhpOffice\PhpSpreadsheet\Worksheet\Drawing) {
                                    $extension = pathinfo($img->getPath(), PATHINFO_EXTENSION);
                                    $imgTemp = '/tmp/' . uniqid('excelimg_', true) . '.' . $extension;
                                    copy($img->getPath(), $imgTemp);
                                    $imgSrc = $imgTemp;
                                } elseif ($img instanceof MemoryDrawing) {
                                    // Imagen en memoria (pegada)
                                    ob_start();
                                    switch ($img->getMimeType()) {
                                        case MemoryDrawing::MIMETYPE_PNG:
                                            imagepng($img->getImageResource());
                                            $ext = 'png';
                                            break;
                                        case MemoryDrawing::MIMETYPE_JPEG:
                                            imagejpeg($img->getImageResource());
                                            $ext = 'jpg';
                                            break;
                                        default:
                                            $ext = 'png';
                                            imagepng($img->getImageResource());
                                    }
                                    $imageData = ob_get_contents();
                                    ob_end_clean();
                                    $imgTemp = '/tmp/' . uniqid('excelimg_', true) . '.' . $ext;
                                    file_put_contents($imgTemp, $imageData);
                                    $imgSrc = $imgTemp;
                                } break;
        }
    }
    echo '<div style="margin-bottom:15px">';
    echo '<label><b>' . htmlspecialchars($imagen_celda['label']) . ':</b></label><br>';
    if (!empty($imgSrc) && file_exists($imgSrc)) {
        echo '<img src="data:image/'.$ext.';base64,'.base64_encode(file_get_contents($imgSrc)).'" style="max-width:200px; display:block; margin-bottom:5px;"><br>';
    } else {
        echo '<span style="color:red;">No se pudo extraer la imagen.</span><br>';
    }
    echo '<label>Reemplazar imagen: <input type="file" name="'.$imagen_celda['name'].'" accept="image/*"></label>';
    echo '</div>';
}
                        ?>
    <button type="submit">Guardar Cambios</button>
    </div>
    </form>
<script>
function inicializarCanvas(idCanvas, idLimpiarBtn, idGuardarBtn, idInputHidden) {
    const canvas = document.getElementById(idCanvas);
    const ctx = canvas.getContext('2d');
    const limpiarBtn = document.getElementById(idLimpiarBtn);
    const guardarBtn = document.getElementById(idGuardarBtn);
    const inputHidden = document.getElementById(idInputHidden);

    let dibujando = false;

    // Ajustar dimensiones dinámicamente para que sea responsivo
    function ajustarTamañoCanvas() {
        const rect = canvas.getBoundingClientRect();
        canvas.width = rect.width;
        canvas.height = rect.height;
        ctx.lineWidth = 2; // Configurar grosor del pincel
        ctx.lineCap = 'round'; // Configurar extremos del trazo
        ctx.strokeStyle = 'black'; // Configurar color del trazo
    }
    ajustarTamañoCanvas();
    window.addEventListener('resize', ajustarTamañoCanvas);

    function obtenerPosicion(e) {
        const rect = canvas.getBoundingClientRect();
        if (e.touches) {
            return {
                x: e.touches[0].clientX - rect.left,
                y: e.touches[0].clientY - rect.top
            };
        } else {
            return {
                x: e.clientX - rect.left,
                y: e.clientY - rect.top
            };
        }
    }

    function iniciarDibujo(e) {
        dibujando = true;
        const pos = obtenerPosicion(e);
        ctx.beginPath();
        ctx.moveTo(pos.x, pos.y);
    }

    function detenerDibujo() {
        dibujando = false;
        ctx.beginPath();
    }

    function dibujar(e) {
        if (!dibujando) return;
        const pos = obtenerPosicion(e);
        ctx.lineTo(pos.x, pos.y);
        ctx.stroke();
    }
    function isCanvasBlank(canvas) {
    const blank = document.createElement('canvas');
    blank.width = canvas.width;
    blank.height = canvas.height;
    return canvas.toDataURL() === blank.toDataURL();
    }
    // Eventos para escritorio
    canvas.addEventListener('mousedown', iniciarDibujo);
    canvas.addEventListener('mouseup', detenerDibujo);
    canvas.addEventListener('mousemove', dibujar);

    // Eventos para dispositivos táctiles
    canvas.addEventListener('touchstart', iniciarDibujo);
    canvas.addEventListener('touchend', detenerDibujo);
    canvas.addEventListener('touchmove', (e) => {
        e.preventDefault(); // Evitar el scroll al dibujar
        dibujar(e);
    });

    // Botón para limpiar el canvas
    limpiarBtn.addEventListener('click', () => {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        inputHidden.value = ''; // Borra el contenido del campo oculto
    });

    // Botón para guardar la firma
    guardarBtn.addEventListener('click', () => {
        if (isCanvasBlank(canvas)) {
            alert('Por favor, realice una firma antes de guardar.');
        } else {
            const dataURL = canvas.toDataURL();
            inputHidden.value = dataURL; // Almacena la firma en el campo oculto
            alert('Firma guardada correctamente.');
            // Mantén la firma visible en el canvas
            const img = new Image();
            img.src = dataURL;
            img.onload = () => {
                ctx.clearRect(0, 0, canvas.width, canvas.height); // Limpia el canvas
                ctx.drawImage(img, 0, 0); // Vuelve a dibujar la firma
            };
        }
    });
}
    inicializarCanvas('canvasfirma1', 'limpiarFirma', 'guardarFirma', 'firma_solicitante');
    inicializarCanvas('canvasfirma2', 'limpiarFirma2', 'guardarFirma2', 'firma_autorizado');
    inicializarCanvas('canvasfirma3', 'limpiarFirma3', 'guardarFirma3', 'firma_respLim');
    inicializarCanvas('canvasfirma4', 'limpiarFirma4', 'guardarFirma4', 'firma_respLim2');
</script>
<?php