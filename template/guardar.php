<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require '../vendor/autoload.php';
require 'sesion.php'; 
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Html as HtmlWriter;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Mpdf\Mpdf;
$sede = $_SESSION['sede'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar que se haya enviado el archivo
    if (empty($_POST['archivo'])) {
        die("Error: No se recibió el nombre del archivo en la solicitud POST.");
    }
    if ($sede === 'ZC'){
        $archivo = trim($_POST['archivo']);
        $carpeta = realpath(__DIR__ . '/../archivos/generados/excelS_M/');
        $rutaArchivo = $carpeta . DIRECTORY_SEPARATOR . $archivo;
    }else{
        $archivo = trim($_POST['archivo']);
        $carpetaZS = realpath(__DIR__ . '/../archivos/generados/excelS_MZS/');
        $rutaArchivo = $carpetaZS . DIRECTORY_SEPARATOR . $archivo;
    }
    
    


    if (!file_exists($rutaArchivo)) {
        die("Error: El archivo no existe o la ruta es incorrecta: $rutaArchivo");
    }

    try {
        // Cargar el archivo Excel
        $spreadsheet = IOFactory::load($rutaArchivo);
        $hoja = $spreadsheet->getActiveSheet();

        // Actualizar celdas según los datos recibidos
        $hoja->setCellValue('J6',$_POST['codigoorden']);
        $codigoorden = $_POST['codigoorden'];
        $hoja->setCellValue('C6', $_POST['fechainicial']);
        $hoja->setCellValue('F6', $_POST['horainicial']);
        $hoja->setCellValue('D7', $_POST['nombreSolicitante']);
        $hoja->setCellValue('I7', $_POST['cargoSolicitante']);
        $hoja->setCellValue('D9', $_POST['objetoDañado']);
        $hoja->setCellValue('I9', $_POST['codigo']);
        $hoja->setCellValue('C10', $_POST['marca']);
        $hoja->setCellValue('A12', $_POST['descripcionDaños']);
        $hoja->setCellValue('I10', $_POST['ubicacion']);
        $hoja->setCellValue('A17', $_POST['tipoMantenimiento']);
        $hoja->setCellValue('A19', $_POST['descripcionTrabajo']);
        $hoja->setCellValue('G20', $_POST['fechaCierre']);
        $hoja->setCellValue('I20', $_POST['hora_cierre']);
        $hoja->setCellValue('A22', $_POST['responsable_Miembro_De_La_Compañia']);
        $hoja->setCellValue('H38', $_POST['nprov']);
        $hoja->setCellValue('G22', $_POST['voboMantenimiento']);
        $hoja->setCellValue('A28', $_POST['descripcion_inocuidad']);
        $hoja->setCellValue('J29', $_POST['retiro_inocuidad']);
        $hoja->setCellValue('A31', $_POST['descripcion_novedad']);
        $hoja->setCellValue('J32', $_POST['riesgo_inocuidad']);
        $hoja->setCellValue('A34', $_POST['implementos']);
        $hoja->setCellValue('C36', $_POST['fecha_revisionl']);
        $hoja->setCellValue('H36', $_POST['hora_revisionl']);
        $hoja->setCellValue('D47', $_POST['control_responsable']);
        $hoja->setCellValue('D48', $_POST['trabajo_realizar']);
        $hoja->setCellValue('C46', $_POST['fechacontrol']);
        $hoja->setCellValue('I47', $_POST['cargo_control']);
        $hoja->setCellValue('B72', $_POST['Vobo_ingreso']);
        $hoja->setCellValue('H72', $_POST['Vobo_salida']);

        //COMIENZO DE TABLAS//
        //TABLA DE HERRAMIENTAS//

        // Guardar los datos de las herramientas en el archivo Excel
        $hoja->setCellValue('A52', $_POST['herramientas_cantidad1']);
        $hoja->setCellValue('B52', $_POST['descripcion_herramientas1']);
        $hoja->setCellValue('D52', $_POST['herramientas_salida1']);

        $hoja->setCellValue('A53', $_POST['herramientas_cantidad2']);
        $hoja->setCellValue('B53', $_POST['descripcion_herramientas2']);
        $hoja->setCellValue('D53', $_POST['herramientas_salida2']);

        $hoja->setCellValue('A54', $_POST['herramientas_cantidad3']);
        $hoja->setCellValue('B54', $_POST['descripcion_herramientas3']);
        $hoja->setCellValue('D54', $_POST['herramientas_salida3']);

        $hoja->setCellValue('A55', $_POST['herramientas_cantidad4']);
        $hoja->setCellValue('B55', $_POST['descripcion_herramientas4']);
        $hoja->setCellValue('D55', $_POST['herramientas_salida4']);

        $hoja->setCellValue('A56', $_POST['herramientas_cantidad5']);
        $hoja->setCellValue('B56', $_POST['descripcion_herramientas5']);
        $hoja->setCellValue('D56', $_POST['herramientas_salida5']);

        $hoja->setCellValue('A57', $_POST['herramientas_cantidad6']);
        $hoja->setCellValue('B57', $_POST['descripcion_herramientas6']);
        $hoja->setCellValue('D57', $_POST['herramientas_salida6']);

        $hoja->setCellValue('A58', $_POST['herramientas_cantidad7']);
        $hoja->setCellValue('B58', $_POST['descripcion_herramientas7']);
        $hoja->setCellValue('D58', $_POST['herramientas_salida7']);

        $hoja->setCellValue('A59', $_POST['herramientas_cantidad8']);
        $hoja->setCellValue('B59', $_POST['descripcion_herramientas8']);
        $hoja->setCellValue('D59', $_POST['herramientas_salida8']);

        //PIEZAS//

        $hoja->setCellValue('E52', $_POST['piezas_cantidad1']);
        $hoja->setCellValue('F52', $_POST['descripcion_piezas1']);
        $hoja->setCellValue('H52', $_POST['piezas_utilizadas1']);
        $hoja->setCellValue('I52', $_POST['sin_utilizar1']);
        $hoja->setCellValue('J52', $_POST['piezas_quitadas1']);
        $hoja->setCellValue('K52', $_POST['verificacion_piezas1']);

        $hoja->setCellValue('E53', $_POST['piezas_cantidad2']);
        $hoja->setCellValue('F53', $_POST['descripcion_piezas2']);
        $hoja->setCellValue('H53', $_POST['piezas_utilizadas2']);
        $hoja->setCellValue('I53', $_POST['sin_utilizar2']);
        $hoja->setCellValue('J53', $_POST['piezas_quitadas2']);
        $hoja->setCellValue('K53', $_POST['verificacion_piezas2']);

        $hoja->setCellValue('E54', $_POST['piezas_cantidad3']);
        $hoja->setCellValue('F54', $_POST['descripcion_piezas3']);
        $hoja->setCellValue('H54', $_POST['piezas_utilizadas3']);
        $hoja->setCellValue('I54', $_POST['sin_utilizar3']);
        $hoja->setCellValue('J54', $_POST['piezas_quitadas3']);
        $hoja->setCellValue('K54', $_POST['verificacion_piezas3']);

        $hoja->setCellValue('E55', $_POST['piezas_cantidad4']);
        $hoja->setCellValue('F55', $_POST['descripcion_piezas4']);
        $hoja->setCellValue('H55', $_POST['piezas_utilizadas4']);
        $hoja->setCellValue('I55', $_POST['sin_utilizar4']);
        $hoja->setCellValue('J55', $_POST['piezas_quitadas4']);
        $hoja->setCellValue('K55', $_POST['verificacion_piezas4']);

        $hoja->setCellValue('E56', $_POST['piezas_cantidad5']);
        $hoja->setCellValue('F56', $_POST['descripcion_piezas5']);
        $hoja->setCellValue('H56', $_POST['piezas_utilizadas5']);
        $hoja->setCellValue('I56', $_POST['sin_utilizar5']);
        $hoja->setCellValue('J56', $_POST['piezas_quitadas5']);
        $hoja->setCellValue('K56', $_POST['verificacion_piezas5']);

        $hoja->setCellValue('E57', $_POST['piezas_cantidad6']);
        $hoja->setCellValue('F57', $_POST['descripcion_piezas6']);
        $hoja->setCellValue('H57', $_POST['piezas_utilizadas6']);
        $hoja->setCellValue('I57', $_POST['sin_utilizar6']);
        $hoja->setCellValue('J57', $_POST['piezas_quitadas6']);
        $hoja->setCellValue('K57', $_POST['verificacion_piezas6']);

        $hoja->setCellValue('E58', $_POST['piezas_cantidad7']);
        $hoja->setCellValue('F58', $_POST['descripcion_piezas7']);
        $hoja->setCellValue('H58', $_POST['piezas_utilizadas7']);
        $hoja->setCellValue('I58', $_POST['sin_utilizar7']);
        $hoja->setCellValue('J58', $_POST['piezas_quitadas7']);
        $hoja->setCellValue('K58', $_POST['verificacion_piezas7']);

        $hoja->setCellValue('E59', $_POST['piezas_cantidad8']);
        $hoja->setCellValue('F59', $_POST['descripcion_piezas8']);
        $hoja->setCellValue('H59', $_POST['piezas_utilizadas8']);
        $hoja->setCellValue('I59', $_POST['sin_utilizar8']);
        $hoja->setCellValue('J59', $_POST['piezas_quitadas8']);
        $hoja->setCellValue('K59', $_POST['verificacion_piezas8']);
 


        $hoja->setCellValue('A63', $_POST['materiales_cantidad1']);
        $hoja->setCellValue('F63', $_POST['descripcion_materiales1']);
        $hoja->setCellValue('I63', $_POST['materiales_utilizados1']);
        $hoja->setCellValue('J63', $_POST['verificacion_material1']);
        $hoja->setCellValue('C63', $_POST['medida_materiales1']);

        $hoja->setCellValue('A64', $_POST['materiales_cantidad2']);
        $hoja->setCellValue('F64', $_POST['descripcion_materiales2']);
        $hoja->setCellValue('I64', $_POST['materiales_utilizados2']);
        $hoja->setCellValue('J64', $_POST['verificacion_material2']);
        $hoja->setCellValue('C64', $_POST['medida_materiales2']);

        $hoja->setCellValue('A65', $_POST['materiales_cantidad3']);
        $hoja->setCellValue('F65', $_POST['descripcion_materiales3']);
        $hoja->setCellValue('I65', $_POST['materiales_utilizados3']);
        $hoja->setCellValue('J65', $_POST['verificacion_material3']);
        $hoja->setCellValue('C65', $_POST['medida_materiales3']);

        $hoja->setCellValue('A66', $_POST['materiales_cantidad4']);
        $hoja->setCellValue('F66', $_POST['descripcion_materiales4']);
        $hoja->setCellValue('I66', $_POST['materiales_utilizados4']);
        $hoja->setCellValue('J66', $_POST['verificacion_material4']);
        $hoja->setCellValue('C66', $_POST['medida_materiales4']);

        $hoja->setCellValue('A67', $_POST['materiales_cantidad5']); 
        $hoja->setCellValue('F67', $_POST['descripcion_materiales5']);
        $hoja->setCellValue('I67', $_POST['materiales_utilizados5']);
        $hoja->setCellValue('J67', $_POST['verificacion_material5']);
        $hoja->setCellValue('C67', $_POST['medida_materiales5']);

        $hoja->setCellValue('A68', $_POST['materiales_cantidad6']);
        $hoja->setCellValue('F68', $_POST['descripcion_materiales6']);
        $hoja->setCellValue('I68', $_POST['materiales_utilizados6']);
        $hoja->setCellValue('J68', $_POST['verificacion_material6']);
        $hoja->setCellValue('C68', $_POST['medida_materiales6']);

        $hoja->setCellValue('A69', $_POST['materiales_cantidad7']);
        $hoja->setCellValue('F69', $_POST['descripcion_materiales7']);
        $hoja->setCellValue('I69', $_POST['materiales_utilizados7']);
        $hoja->setCellValue('J69', $_POST['verificacion_material7']);
        $hoja->setCellValue('C69', $_POST['medida_materiales7']);

        $hoja->setCellValue('A70', $_POST['materiales_cantidad8']); 
        $hoja->setCellValue('F70', $_POST['descripcion_materiales8']);
        $hoja->setCellValue('I70', $_POST['materiales_utilizados8']);
        $hoja->setCellValue('J70', $_POST['verificacion_material8']);
        $hoja->setCellValue('C70', $_POST['medida_materiales8']);

            // TABLA DE TERMOGRAFÍA Y OTROS (ORDENADA SEGÚN LA NUEVA TABLA)
            for ($i = 1; $i <= 6; $i++) {
                $hoja->setCellValue('C'.(74+$i), $_POST['parte_equipo'.$i] ?? '');
                $hoja->setCellValue('D'.(74+$i), $_POST['termografia_equipo'.$i] ?? '');
                // En el HTML, el campo "analizador_vibraciones" corresponde a vibraciones_equipo
                $hoja->setCellValue('E'.(74+$i), $_POST['analizador_vibraciones'.$i] ?? '');
                $hoja->setCellValue('G'.(74+$i), $_POST['nuevasvibraciones_equipo'.$i] ?? '');
                $hoja->setCellValue('H'.(74+$i), $_POST['multimetrorango_equipo'.$i] ?? '');
                $hoja->setCellValue('I'.(74+$i), $_POST['multimetroamperaje_equipo'.$i] ?? '');
                $hoja->setCellValue('J'.(74+$i), $_POST['observaciones_equipo'.$i] ?? '');
                // OT Mantenimiento Preventivo (columna K, si existe en tu Excel)
                if (isset($_POST['ot_mantenimiento_preventivo'.$i])) {
                    $hoja->setCellValue('K'.(74+$i), $_POST['ot_mantenimiento_preventivo'.$i]);
                }
            }

        $firmaSolicitante = $_POST['firma_solicitante'];
        if ($firmaSolicitante) {
            $firmaSolicitantePath = '../archivos/formularios/firma_solicitante.png';
            $firmaSolicitanteData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $firmaSolicitante));
    
            if (file_put_contents($firmaSolicitantePath, $firmaSolicitanteData) === false) {
                die("Error al guardar la firma del solicitante.");
            }
            
            // Insertar la firma en el Excel
            $firmaSolicitanteImg = new Drawing();
            $firmaSolicitanteImg->setPath($firmaSolicitantePath);
            $firmaSolicitanteImg->setHeight(100);
            $firmaSolicitanteImg->setCoordinates('A13'); // Asignar celda
            $firmaSolicitanteImg->setWorksheet($hoja);
        }
    
        // Procesar firma del autorizante
        $firmaAutorizado = $_POST['firma_autorizado'];
        if ($firmaAutorizado) {
            $firmaAutorizadoPath = '../archivos/formularios/firma_autorizado.png';
            $firmaAutorizadoData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $firmaAutorizado));
    
            if (file_put_contents($firmaAutorizadoPath, $firmaAutorizadoData) === false) {
                die("Error al guardar la firma del autorizante.");
            }
    
            // Insertar la firma en el Excel
            $firmaAutorizadoImg = new Drawing();
            $firmaAutorizadoImg->setPath($firmaAutorizadoPath);
            $firmaAutorizadoImg->setHeight(100);
            $firmaAutorizadoImg->setCoordinates('G13'); // Asignar celda
            $firmaAutorizadoImg->setWorksheet($hoja);
        }
    
        // Procesar firma del responsable de limpieza
        $firmaRespLimpieza = $_POST['firma_respLim'];
        if ($firmaRespLimpieza) {
            $firmaRespLimpiezaPath = '../archivos/formularios/firma_resp_limpieza.png';
            $firmaRespLimpiezaData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $firmaRespLimpieza));
    
            if (file_put_contents($firmaRespLimpiezaPath, $firmaRespLimpiezaData) === false) {
                die("Error al guardar la firma del responsable de limpieza.");
            }
    
            // Insertar la firma en el Excel
            $firmaRespLimpiezaImg = new Drawing();
            $firmaRespLimpiezaImg->setPath($firmaRespLimpiezaPath);
            $firmaRespLimpiezaImg->setHeight(100);
            $firmaRespLimpiezaImg->setCoordinates('A38'); // Asignar celda
            $firmaRespLimpiezaImg->setWorksheet($hoja);
        }
    
        // Procesar firma del responsable de revisar la limpieza
        $firmaRespRevisar = $_POST['firma_respLim2'];
        if ($firmaRespRevisar) {
            $firmaRespRevisarPath = '../archivos/formularios/firma_resp_revisar.png';
            $firmaRespRevisarData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $firmaRespRevisar));
    
            if (file_put_contents($firmaRespRevisarPath, $firmaRespRevisarData) === false) {
                die("Error al guardar la firma del responsable de revisar la limpieza.");
            }
    
            // Insertar la firma en el Excel
            $firmaRespRevisarImg = new Drawing();
            $firmaRespRevisarImg->setPath($firmaRespRevisarPath);
            $firmaRespRevisarImg->setHeight(100);
            $firmaRespRevisarImg->setCoordinates('D38'); // Asignar celda
            $firmaRespRevisarImg->setWorksheet($hoja);
        }

        // Manejar imágenes en la hoja de cálculo
        $imagenes_celdas = [
    ['celda' => 'A82', 'name' => 'imagen_a82'],
    ['celda' => 'G82', 'name' => 'imagen_g82'],
    ['celda' => 'A97', 'name' => 'imagen_a97'],
    ['celda' => 'G97', 'name' => 'imagen_g97'],
];

foreach ($imagenes_celdas as $img_info) {
    if (
        isset($_FILES[$img_info['name']]) &&
        $_FILES[$img_info['name']]['error'] === UPLOAD_ERR_OK
    ) {
        //OPCION PARA ELIMINAR LA IMAGEN ANTERIOR (SIN TERMINAR)
        //foreach ($hoja->getDrawingCollection() as $drawing) {
          //  if ($drawing->getCoordinates() === $img_info['celda']) {
            //    $hoja->getDrawingCollection()->detach($drawing);
              //  break;
           // }
        //}
        // Guardar el archivo subido temporalmente
        $tmpPath = $_FILES[$img_info['name']]['tmp_name'];
        $ext = pathinfo($_FILES[$img_info['name']]['name'], PATHINFO_EXTENSION);
        $destPath = sys_get_temp_dir() . '/' . uniqid('img_', true) . '.' . $ext;
        move_uploaded_file($tmpPath, $destPath);

        // Insertar la nueva imagen en la celda correspondiente
        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
        $drawing->setPath($destPath);
        $drawing->setCoordinates($img_info['celda']);
        $drawing->setHeight(120); // Ajusta el tamaño si lo deseas
        $drawing->setWorksheet($hoja);
    }
}
        // Generar el HTML con las imágenes incluida
        
        $writer = new Xlsx($spreadsheet); // Usar Xlsx para archivos .xlsx
        $writer->save($rutaArchivo);

        session_start(); // Asegurar que la sesión está iniciada

if (isset($_SESSION['pagina_anterior'])) {
    $url_redireccion = $_SESSION['pagina_anterior'];
    unset($_SESSION['pagina_anterior']); // Eliminar para evitar redirecciones innecesarias
    header("Location: $url_redireccion");
    exit();
} else {
    // Si no hay página previa guardada, redirigir a una página por defecto
    header("Location: ../template/menu_adm.html");
    exit();
};
    } catch (Exception $e) {
        die("Error al procesar el archivo: " . $e->getMessage());
    }
} else {
    die("Método no permitido.");
}
?>
