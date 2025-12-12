<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require '../vendor/autoload.php';
require 'sesion.php'; // Incluye el archivo de autenticación
require 'conection.php'; // Conexión a la base de datos
verificarAutenticacion(); // Verifica que el usuario esté autenticado

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Mpdf\Mpdf;
//echo '<pre>';
//print_r($_FILES);
//echo '</pre>';
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
//PROCESAMIENTO DE DATOS
//echo "<pre>";
//print_r($_POST);
//echo "</pre>";
//exit;
// PROCESAMIENTO DE DATOS//
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener datos del formulario
    $sede = $_SESSION['sede'];
    $fechainicial = $_POST['fechainicial'];
    $horainicial = $_POST['horainicial'];
    $nombreSolicitante = $_POST['nombre_solicitante'];
    $cargoSolicitante = $_POST['cargo_solicitante'];
    $objetoDañado = $_POST['objeto_dañado'];
    $ubi = $_POST['ubi'];
    $marca = $_POST['marca'];
    $codigo = $_POST['cod'];
    $descripcionDaños = $_POST['descripcion_daños'];
    $tipoMantenimiento = $_POST['tipomantenimiento'];
    $descripcionTrabajo = $_POST['descripcion_trabajo'];
    $fechaCierre = $_POST['fecha_cierre'];
    $hora_cierre = $_POST['hora_cierre'];
    $responsable_Miembro_De_La_Compañia_0 = $_POST["responsable-Miembro_De_La_Compañia_0"]?? "";
    $responsable_Miembro_De_La_Compañia_1 = $_POST["responsable-Miembro_De_La_Compañia_1"]?? "";
    $responsable_Miembro_De_La_Compañia_2 = $_POST["responsable-Miembro_De_La_Compañia_2"]?? "";
    $nprov = $_POST['nprov']?? 'No proporcionado';
    $voboMantenimiento = $_POST['VoBo'];
    $descripcion_inocuidad = $_POST['descripcion_inocuidad'];
    $retiro_inocuidad = $_POST['retiro_inocuidad'];
    $descripcion_novedad = $_POST['descripcion_novedad'];
    $riesgo_inocuidad = $_POST['riesgo_inocuidad'];
    $implementos = $_POST['implementos'];
    $fecha_revisionl = $_POST['fecha_revisionl'];
    $hora_revisionl = $_POST['hora_revisionl'];
    //PROCESAMIENTO DE DOCUMENTOS



    //Control de Partes Sueltas

    //Herramientras 
    $herramientaext1 = $_POST['herramientaext1']?? "";
    $control_responsable = $_POST['control_responsable']?? "";
    $trabajo_realizar = $_POST['trabajo_realizar']?? "";
    $fechacontrol = $_POST['fechacontrol']?? "";
    $cargo_control = $_POST['cargo_control']?? "";
    $Vobo_ingreso = $_POST['Vobo_ingreso']?? "";
    $Vobo_salida = $_POST['Vobo_salida']?? "";
    $herramientas_cantidad1 = $_POST['herramientas_cantidad1']?? "";
    $herramientas_cantidad2 = $_POST['herramientas_cantidad2']?? "";
    $herramientas_cantidad3 = $_POST['herramientas_cantidad3']?? "";
    $herramientas_cantidad4 = $_POST['herramientas_cantidad4']?? "";
    $herramientas_cantidad5 = $_POST['herramientas_cantidad5']?? "";
    $herramientas_cantidad6 = $_POST['herramientas_cantidad6']?? "";
    $herramientas_cantidad7 = $_POST['herramientas_cantidad7']?? "";
    $herramientas_cantidad8 = $_POST['herramientas_cantidad8']?? "";
    $descripcion_herramientas1 = $_POST['descripcion_herramientas1']?? "";
    $descripcion_herramientas2 = $_POST['descripcion_herramientas2']?? "";
    $descripcion_herramientas3 = $_POST['descripcion_herramientas3']?? "";
    $descripcion_herramientas4 = $_POST['descripcion_herramientas4']?? "";
    $descripcion_herramientas5 = $_POST['descripcion_herramientas5']?? "";
    $descripcion_herramientas6 = $_POST['descripcion_herramientas6']?? "";
    $descripcion_herramientas7 = $_POST['descripcion_herramientas7']?? "";
    $descripcion_herramientas8 = $_POST['descripcion_herramientas8']?? "";
    $herramientas_salida1 = $_POST['herramientas_salida1']?? "";
    $herramientas_salida2 = $_POST['herramientas_salida2']?? "";
    $herramientas_salida3 = $_POST['herramientas_salida3']?? "";
    $herramientas_salida4 = $_POST['herramientas_salida4']?? "";
    $herramientas_salida5 = $_POST['herramientas_salida5']?? "";
    $herramientas_salida6 = $_POST['herramientas_salida6']?? "";
    $herramientas_salida7 = $_POST['herramientas_salida7']?? "";
    $herramientas_salida8 = $_POST['herramientas_salida8']?? "";
    //Piezas
    $piezas_cantidad1 = $_POST['piezas_cantidad1']?? "";
    $piezas_cantidad2 = $_POST['piezas_cantidad2']?? "";
    $piezas_cantidad3 = $_POST['piezas_cantidad3']?? "";
    $piezas_cantidad4 = $_POST['piezas_cantidad4']?? "";
    $piezas_cantidad5 = $_POST['piezas_cantidad5']?? "";
    $piezas_cantidad6 = $_POST['piezas_cantidad6']?? "";
    $piezas_cantidad7 = $_POST['piezas_cantidad7']?? "";
    $piezas_cantidad8 = $_POST['piezas_cantidad8']?? "";
    $descripcion_piezas1 = $_POST['descripcion_piezas1']?? "";
    $descripcion_piezas2 = $_POST['descripcion_piezas2']?? "";
    $descripcion_piezas3 = $_POST['descripcion_piezas3']?? "";
    $descripcion_piezas4 = $_POST['descripcion_piezas4']?? "";
    $descripcion_piezas5 = $_POST['descripcion_piezas5']?? "";
    $descripcion_piezas6 = $_POST['descripcion_piezas6']?? "";
    $descripcion_piezas7 = $_POST['descripcion_piezas7']?? "";
    $descripcion_piezas8 = $_POST['descripcion_piezas8']?? "";
    $piezas_utilizadas1 = $_POST['piezas_utilizadas1']?? "";
    $piezas_utilizadas2 = $_POST['piezas_utilizadas2']?? "";
    $piezas_utilizadas3 = $_POST['piezas_utilizadas3']?? "";
    $piezas_utilizadas4 = $_POST['piezas_utilizadas4']?? "";
    $piezas_utilizadas5 = $_POST['piezas_utilizadas5']?? "";
    $piezas_utilizadas6 = $_POST['piezas_utilizadas6']?? "";
    $piezas_utilizadas7 = $_POST['piezas_utilizadas7']?? "";
    $piezas_utilizadas8 = $_POST['piezas_utilizadas8']?? "";
    $sin_utilizar1 = $_POST['sin_utilizar1']?? "";
    $sin_utilizar2 = $_POST['sin_utilizar2']?? "";
    $sin_utilizar3 = $_POST['sin_utilizar3']?? "";
    $sin_utilizar4 = $_POST['sin_utilizar4']?? "";
    $sin_utilizar5 = $_POST['sin_utilizar5']?? "";
    $sin_utilizar6 = $_POST['sin_utilizar6']?? "";
    $sin_utilizar7 = $_POST['sin_utilizar7']?? "";
    $sin_utilizar8 = $_POST['sin_utilizar8']?? "";
    $piezas_quitadas1 = $_POST['piezas_quitadas1']?? "";
    $piezas_quitadas2 = $_POST['piezas_quitadas2']?? "";
    $piezas_quitadas3 = $_POST['piezas_quitadas3']?? "";
    $piezas_quitadas4 = $_POST['piezas_quitadas4']?? "";
    $piezas_quitadas5 = $_POST['piezas_quitadas5']?? "";
    $piezas_quitadas6 = $_POST['piezas_quitadas6']?? "";
    $piezas_quitadas7 = $_POST['piezas_quitadas7']?? "";
    $piezas_quitadas8 = $_POST['piezas_quitadas8']?? "";
    $verificacion_piezas1 = $_POST['verificacion_piezas1']?? "";
    $verificacion_piezas2 = $_POST['verificacion_piezas2']?? "";
    $verificacion_piezas3 = $_POST['verificacion_piezas3'] ?? '';
    $verificacion_piezas4 = $_POST['verificacion_piezas4'] ?? '';
    $verificacion_piezas5 = $_POST['verificacion_piezas5'] ?? '';
    $verificacion_piezas6 = $_POST['verificacion_piezas6'] ?? '';
    $verificacion_piezas7 = $_POST['verificacion_piezas7'] ?? '';
    $verificacion_piezas8 = $_POST['verificacion_piezas8'] ?? '';
    //Materiales
    $materiales_cantidad1 = $_POST['materiales_cantidad1']?? "";
    $materiales_cantidad2 = $_POST['materiales_cantidad2']?? "";
    $materiales_cantidad3 = $_POST['materiales_cantidad3']?? "";
    $materiales_cantidad4 = $_POST['materiales_cantidad4']?? "";
    $materiales_cantidad5 = $_POST['materiales_cantidad5']?? "";
    $materiales_cantidad6 = $_POST['materiales_cantidad6']?? "";
    $materiales_cantidad7 = $_POST['materiales_cantidad7']?? "";
    $materiales_cantidad8 = $_POST['materiales_cantidad8']?? "";
    $descripcion_materiales1 = $_POST['descripcion_materiales1']?? "";
    $descripcion_materiales2 = $_POST['descripcion_materiales2']?? "";
    $descripcion_materiales3 = $_POST['descripcion_materiales3']?? "";
    $descripcion_materiales4 = $_POST['descripcion_materiales4']?? "";
    $descripcion_materiales5 = $_POST['descripcion_materiales5']?? "";
    $descripcion_materiales6 = $_POST['descripcion_materiales6']?? "";
    $descripcion_materiales7 = $_POST['descripcion_materiales7']?? "";
    $descripcion_materiales8 = $_POST['descripcion_materiales8']?? "";
    $materiales_utilizados1 = $_POST['materiales_utilizados1']?? "";
    $materiales_utilizados2 = $_POST['materiales_utilizados2']?? "";
    $materiales_utilizados3 = $_POST['materiales_utilizados3']?? "";
    $materiales_utilizados4 = $_POST['materiales_utilizados4']?? "";
    $materiales_utilizados5 = $_POST['materiales_utilizados5']?? "";
    $materiales_utilizados6 = $_POST['materiales_utilizados6']?? "";
    $materiales_utilizados7 = $_POST['materiales_utilizados7']?? "";
    $materiales_utilizados8 = $_POST['materiales_utilizados8']?? "";
    $verificacion_material1 = $_POST['verificacion_material1']?? "";
    $verificacion_material2 = $_POST['verificacion_material2']?? "";
    $verificacion_material3 = $_POST['verificacion_material3']?? "";
    $verificacion_material4 = $_POST['verificacion_material4']?? "";
    $verificacion_material5 = $_POST['verificacion_material5']?? "";
    $verificacion_material6 = $_POST['verificacion_material6']?? "";
    $verificacion_material7= $_POST['verificacion_material7']?? "";
    $verificacion_material8 = $_POST['verificacion_material8']?? "";
    $medida_materiales1 = $_POST['medida_materiales1']?? "";
    $medida_materiales2 = $_POST['medida_materiales2']?? "";
    $medida_materiales3 = $_POST['medida_materiales3']?? "";
    $medida_materiales4 = $_POST['medida_materiales4']?? "";
    $medida_materiales5 = $_POST['medida_materiales5']?? "";
    $medida_materiales6 = $_POST['medida_materiales6']?? "";
    $medida_materiales7 = $_POST['medida_materiales7']?? "";
    $medida_materiales8 = $_POST['medida_materiales8']?? "";
    //SECCION DE REVISION DE MANTENIMIENTO PREDICTIVO
    //EQUIPO 1
    $equipo_name = $_POST['equipo_name'] ?? 'N/A';
    $termografia1 = $_POST['termografia1'] ?? 'N/A';
    $vibraciones1 = $_POST['vibraciones1']?? 'N/A';
    $rango1 = $_POST['rango1'] ?? 'N/A';
    $amperaje1 = $_POST['amperaje1'] ?? 'N/A';
    $observaciones1 = $_POST['observaciones1']?? 'N/A';
    //EQUIPO 2
    $equipo_name_2 = $_POST['equipo_name_2'] ?? 'N/A';
    $termografia2 = $_POST['termografia2']?? 'N/A';
    $vibraciones2 = $_POST['vibraciones2']?? 'N/A';
    $rango2 = $_POST['rango2']?? 'N/A';
    $amperaje2 = $_POST['amperaje2']?? 'N/A';
    $observaciones2 = $_POST['observaciones2']?? 'N/A';
// EQUIPO 3 (Chumacera y Motor)
// EQUIPO 3 (Chumacera y Motor)
$equipo_name3 = $_POST['equipo_name2'] ?? 'N/A';
$termografia3 = $_POST['termografia3'] ?? 'N/A';
$vibraciones3 = $_POST['vibraciones3'] ?? 'N/A';
$rango3 = $_POST['rango3'] ?? 'N/A';
$amperaje3 = $_POST['amperaje3'] ?? 'N/A';
$observaciones3 = $_POST['observaciones3'] ?? 'N/A';

$equipo_name3_motor = $_POST['equipo_name22'] ?? 'N/A';
$termografia_motor3 = $_POST['termografia_motor3'] ?? 'N/A';
$vibraciones_motor3 = $_POST['vibraciones_motor3'] ?? 'N/A';
$rango_motor3 = $_POST['rango_motor3'] ?? 'N/A';
$amperaje_motor3 = $_POST['amperaje_motor3'] ?? 'N/A';
$observaciones_motor3 = $_POST['observaciones_motor3'] ?? 'N/A';

// EQUIPO 4 (Chumacera y Motor)
$equipo_name4 = $_POST['equipo_name3'] ?? 'N/A';
$termografia4 = $_POST['termografia4'] ?? 'N/A';
$vibraciones4 = $_POST['vibraciones4'] ?? 'N/A';
$rango4 = $_POST['rango4'] ?? 'N/A';
$amperaje4 = $_POST['amperaje4'] ?? 'N/A';
$observaciones4 = $_POST['observaciones4'] ?? 'N/A';

$equipo_name4_motor = $_POST['equipo_name23'] ?? 'N/A';
$termografia_motor4 = $_POST['termografia_motor4'] ?? 'N/A';
$vibraciones_motor4 = $_POST['vibraciones_motor4'] ?? 'N/A';
$rango_motor4 = $_POST['rango_motor4'] ?? 'N/A';
$amperaje_motor4 = $_POST['amperaje_motor4'] ?? 'N/A';
$observaciones_motor4 = $_POST['observaciones_motor4'] ?? 'N/A';

// EQUIPO 5 (Chumacera y Motor)
$equipo_name5 = $_POST['equipo_name4'] ?? 'N/A';
$termografia5 = $_POST['termografia5'] ?? 'N/A';
$vibraciones5 = $_POST['vibraciones5'] ?? 'N/A';
$rango5 = $_POST['rango5'] ?? 'N/A';
$amperaje5 = $_POST['amperaje5'] ?? 'N/A';
$observaciones5 = $_POST['observaciones5'] ?? 'N/A';

$equipo_name5_motor = $_POST['equipo_name24'] ?? 'N/A';
$termografia_motor5 = $_POST['termografia_motor5'] ?? 'N/A';
$vibraciones_motor5 = $_POST['vibraciones_motor5'] ?? 'N/A';
$rango_motor5 = $_POST['rango_motor5'] ?? 'N/A';
$amperaje_motor5 = $_POST['amperaje_motor5'] ?? 'N/A';
$observaciones_motor5 = $_POST['observaciones_motor5'] ?? 'N/A';

// EQUIPO 6 (Chumacera y Motor)
$equipo_name6 = $_POST['equipo_name6'] ?? 'N/A';
$termografia6 = $_POST['termografia6'] ?? 'N/A';
$vibraciones6 = $_POST['vibraciones6'] ?? 'N/A';
$rango6 = $_POST['rango6'] ?? 'N/A';
$amperaje6 = $_POST['amperaje6'] ?? 'N/A';
$observaciones6 = $_POST['observaciones6'] ?? 'N/A';

$equipo_name6_motor = $_POST['equipo_name6_motor'] ?? 'N/A';
$termografia_motor6 = $_POST['termografia_motor6'] ?? 'N/A';
$vibraciones_motor6 = $_POST['vibraciones_motor6'] ?? 'N/A';
$rango_motor6 = $_POST['rango_motor6'] ?? 'N/A';
$amperaje_motor6 = $_POST['amperaje_motor6'] ?? 'N/A';
$observaciones_motor6 = $_POST['observaciones_motor6'] ?? 'N/A';


    // Ruta de la plantilla base
    $baseExcelPath = "../archivos/formularios/formulario1.xlsx";
}

    if (!file_exists($baseExcelPath)) {
        //die("Error: No se encontró la plantilla base.");
    }

    // Cargar la plantilla de Excel
    $spreadsheet = IOFactory::load($baseExcelPath);
    $sheet = $spreadsheet->getActiveSheet();

    // Rellenar datos en el Excel
    $sheet->setCellValue('B156', $herramientaext1);
    $sheet->setCellValue('C6', $fechainicial);
    $sheet->setCellValue('F6', $horainicial);
    $sheet->setCellValue('D7', $nombreSolicitante);
    $sheet->setCellValue('I7', $cargoSolicitante);
    $sheet->setCellValue('D9', $objetoDañado);
    $sheet->setCellValue('I9', $codigo);
    $sheet->setCellValue('C10', $marca);
    $sheet->setCellValue('A12', $descripcionDaños);
    $sheet->setCellValue('I10', $ubi);
    $sheet->setCellValue('A17', $tipoMantenimiento);
    $sheet->setCellValue('A19', $descripcionTrabajo);
    $sheet->setCellValue('G20', $fechaCierre);
    $sheet->setCellValue('I20', $hora_cierre);
    $sheet->setCellValue('A22', $responsable_Miembro_De_La_Compañia_0);
    $sheet->setCellValue('C22', $responsable_Miembro_De_La_Compañia_1);
    $sheet->setCellValue('E22', $responsable_Miembro_De_La_Compañia_2);
    $sheet->setCellValue('H38', $nprov);
    $sheet->setCellValue('G22', $voboMantenimiento);
    $sheet->setCellValue('A28', $descripcion_inocuidad);
    $sheet->setCellValue('J29', $retiro_inocuidad);
    $sheet->setCellValue('A31', $descripcion_novedad);
    $sheet->setCellValue('J32', $riesgo_inocuidad);
    $sheet->setCellValue('A34', $implementos);
    $sheet->setCellValue('C36', $fecha_revisionl);
    $sheet->setCellValue('H36', $hora_revisionl);


    $sheet->setCellValue('D47', $control_responsable);
    $sheet->setCellValue('D48', $trabajo_realizar);
    $sheet->setCellValue('C46', $fechacontrol);
    $sheet->setCellValue('E72', $fechacontrol);
    $sheet->setCellValue('K72', $fechacontrol);

    $sheet->setCellValue('I47', $cargo_control);
    $sheet->setCellValue('B72', $Vobo_ingreso);
    $sheet->setCellValue('H72', $Vobo_salida);


    $sheet->setCellValue('A52', $herramientas_cantidad1);
    $sheet->setCellValue('B52', $descripcion_herramientas1);
    $sheet->setCellValue('D52', $herramientas_salida1);

    $sheet->setCellValue('A53', $herramientas_cantidad2);
    $sheet->setCellValue('B53', $descripcion_herramientas2);
    $sheet->setCellValue('D53', $herramientas_salida2);

    $sheet->setCellValue('A54', $herramientas_cantidad3);
    $sheet->setCellValue('B54', $descripcion_herramientas3);
    $sheet->setCellValue('D54', $herramientas_salida3);

    $sheet->setCellValue('A55', $herramientas_cantidad4);
    $sheet->setCellValue('B55', $descripcion_herramientas4);
    $sheet->setCellValue('D55', $herramientas_salida4);

    $sheet->setCellValue('A56', $herramientas_cantidad5);
    $sheet->setCellValue('B56', $descripcion_herramientas5);
    $sheet->setCellValue('D56', $herramientas_salida5);

    $sheet->setCellValue('A57', $herramientas_cantidad6);
    $sheet->setCellValue('B57', $descripcion_herramientas6);
    $sheet->setCellValue('D57', $herramientas_salida6);

    $sheet->setCellValue('A58', $herramientas_cantidad7);
    $sheet->setCellValue('B58', $descripcion_herramientas7);
    $sheet->setCellValue('D58', $herramientas_salida7);

    $sheet->setCellValue('A59', $herramientas_cantidad8);
    $sheet->setCellValue('B59', $descripcion_herramientas8);
    $sheet->setCellValue('D59', $herramientas_salida8);

    // Rellenar datos de piezas uno por uno
    $sheet->setCellValue('E52', $piezas_cantidad1);
    $sheet->setCellValue('F52', $descripcion_piezas1);
    $sheet->setCellValue('H52', $piezas_utilizadas1);
    $sheet->setCellValue('I52', $sin_utilizar1);
    $sheet->setCellValue('J52', $piezas_quitadas1);
    $sheet->setCellValue('K52', $verificacion_piezas1);

    $sheet->setCellValue('E53', $piezas_cantidad2);
    $sheet->setCellValue('F53', $descripcion_piezas2);
    $sheet->setCellValue('H53', $piezas_utilizadas2);
    $sheet->setCellValue('I53', $sin_utilizar2);
    $sheet->setCellValue('J53', $piezas_quitadas2);
    $sheet->setCellValue('K53', $verificacion_piezas2);

    $sheet->setCellValue('E54', $piezas_cantidad3);
    $sheet->setCellValue('F54', $descripcion_piezas3);
    $sheet->setCellValue('H54', $piezas_utilizadas3);
    $sheet->setCellValue('I54', $sin_utilizar3);
    $sheet->setCellValue('J54', $piezas_quitadas3);
    $sheet->setCellValue('K54', $verificacion_piezas3);

    $sheet->setCellValue('E55', $piezas_cantidad4);
    $sheet->setCellValue('F55', $descripcion_piezas4);
    $sheet->setCellValue('H55', $piezas_utilizadas4);
    $sheet->setCellValue('I55', $sin_utilizar4);
    $sheet->setCellValue('J55', $piezas_quitadas4);
    $sheet->setCellValue('K55', $verificacion_piezas4);

    $sheet->setCellValue('E56', $piezas_cantidad5);
    $sheet->setCellValue('F56', $descripcion_piezas5);
    $sheet->setCellValue('H56', $piezas_utilizadas5);
    $sheet->setCellValue('I56', $sin_utilizar5);
    $sheet->setCellValue('J56', $piezas_quitadas5);
    $sheet->setCellValue('K56', $verificacion_piezas5);

    $sheet->setCellValue('E57', $piezas_cantidad6);
    $sheet->setCellValue('F57', $descripcion_piezas6);
    $sheet->setCellValue('H57', $piezas_utilizadas6);
    $sheet->setCellValue('I57', $sin_utilizar6);
    $sheet->setCellValue('J57', $piezas_quitadas6);
    $sheet->setCellValue('K57', $verificacion_piezas6);

    $sheet->setCellValue('E58', $piezas_cantidad7);
    $sheet->setCellValue('F58', $descripcion_piezas7);
    $sheet->setCellValue('H58', $piezas_utilizadas7);
    $sheet->setCellValue('I58', $sin_utilizar7);
    $sheet->setCellValue('J58', $piezas_quitadas7);
    $sheet->setCellValue('K58', $verificacion_piezas7);

    $sheet->setCellValue('E59', $piezas_cantidad8);
    $sheet->setCellValue('F59', $descripcion_piezas8);
    $sheet->setCellValue('H59', $piezas_utilizadas8);
    $sheet->setCellValue('I59', $sin_utilizar8);
    $sheet->setCellValue('J59', $piezas_quitadas8);
    $sheet->setCellValue('K59', $verificacion_piezas8);

    // Rellenar datos de materiales uno por uno
    $sheet->setCellValue('A63', $materiales_cantidad1);
    $sheet->setCellValue('F63', $descripcion_materiales1);
    $sheet->setCellValue('I63', $materiales_utilizados1);
    $sheet->setCellValue('J63', $verificacion_material1);
    $sheet->setCellValue('C63', $medida_materiales1);

    $sheet->setCellValue('A64', $materiales_cantidad2);
    $sheet->setCellValue('F64', $descripcion_materiales2);
    $sheet->setCellValue('I64', $materiales_utilizados2);
    $sheet->setCellValue('J64', $verificacion_material2);
    $sheet->setCellValue('C64', $medida_materiales2);

    $sheet->setCellValue('A65', $materiales_cantidad3);
    $sheet->setCellValue('F65', $descripcion_materiales3);
    $sheet->setCellValue('I65', $materiales_utilizados3);
    $sheet->setCellValue('J65', $verificacion_material3);
    $sheet->setCellValue('C65', $medida_materiales3);

    $sheet->setCellValue('A66', $materiales_cantidad4);
    $sheet->setCellValue('F66', $descripcion_materiales4);
    $sheet->setCellValue('I66', $materiales_utilizados4);
    $sheet->setCellValue('J66', $verificacion_material4);
    $sheet->setCellValue('C66', $medida_materiales4);

    $sheet->setCellValue('A67', $materiales_cantidad5);
    $sheet->setCellValue('F67', $descripcion_materiales5);
    $sheet->setCellValue('I67', $materiales_utilizados5);
    $sheet->setCellValue('J67', $verificacion_material5);
    $sheet->setCellValue('C67', $medida_materiales5);

    $sheet->setCellValue('A68', $materiales_cantidad6);
    $sheet->setCellValue('F68', $descripcion_materiales6);
    $sheet->setCellValue('I68', $materiales_utilizados6);
    $sheet->setCellValue('J68', $verificacion_material6);
    $sheet->setCellValue('C68', $medida_materiales6);

    $sheet->setCellValue('A69', $materiales_cantidad7);
    $sheet->setCellValue('F69', $descripcion_materiales7);
    $sheet->setCellValue('I69', $materiales_utilizados7);
    $sheet->setCellValue('J69', $verificacion_material7);
    $sheet->setCellValue('C69', $medida_materiales7);

    $sheet->setCellValue('A70', $materiales_cantidad8);
    $sheet->setCellValue('F70', $descripcion_materiales8);
    $sheet->setCellValue('I70', $materiales_utilizados8);
    $sheet->setCellValue('J70', $verificacion_material8);
    $sheet->setCellValue('C70', $medida_materiales8);

    //SECCION DE REGISTRO DE MEDICIONES MANTENIMIENTO PREDICTIVO 
    $sheet->setCellValue('D75',$termografia1);
    $sheet->setCellValue('E75',$vibraciones1);
    $sheet->setCellValue('H75',$rango1);
    $sheet->setCellValue('I75',$amperaje1);
    $sheet->setCellValue('J75',$observaciones1);

    $sheet->setCellValue('D76',$termografia2);
    $sheet->setCellValue('E76',$vibraciones2);
    $sheet->setCellValue('H76',$rango2);
    $sheet->setCellValue('I76',$amperaje2);
    $sheet->setCellValue('J76',$observaciones2);

   // EQUIPO 1
$sheet->setCellValue('C75', $equipo_name);
$sheet->setCellValue('D75', $termografia1);
$sheet->setCellValue('E75', $vibraciones1);
$sheet->setCellValue('H75', $rango1);
$sheet->setCellValue('I75', $amperaje1);
$sheet->setCellValue('J75', $observaciones1);

// EQUIPO 2
$sheet->setCellValue('C76', $equipo_name_2);
$sheet->setCellValue('D76', $termografia2);
$sheet->setCellValue('E76', $vibraciones2);
$sheet->setCellValue('H76', $rango2);
$sheet->setCellValue('I76', $amperaje2);
$sheet->setCellValue('J76', $observaciones2);

// EQUIPO 3 (Chumacera)
$sheet->setCellValue('C77', $equipo_name3);
$sheet->setCellValue('D77', $termografia3);
$sheet->setCellValue('E77', $vibraciones3);
$sheet->setCellValue('H77', $rango3);
$sheet->setCellValue('I77', $amperaje3);
$sheet->setCellValue('J77', $observaciones3);

// EQUIPO 3 (Motor)
$sheet->setCellValue('C78', $equipo_name3_motor);
$sheet->setCellValue('D78', $termografia_motor3);
$sheet->setCellValue('E78', $vibraciones_motor3);
$sheet->setCellValue('H78', $rango_motor3);
$sheet->setCellValue('I78', $amperaje_motor3);
$sheet->setCellValue('J78', $observaciones_motor3);

// EQUIPO 4 (Chumacera)
$sheet->setCellValue('C79', $equipo_name4);
$sheet->setCellValue('D79', $termografia4);
$sheet->setCellValue('E79', $vibraciones4);
$sheet->setCellValue('H79', $rango4);
$sheet->setCellValue('I79', $amperaje4);
$sheet->setCellValue('J79', $observaciones4);

// EQUIPO 4 (Motor)
$sheet->setCellValue('C80', $equipo_name4_motor);
$sheet->setCellValue('D80', $termografia_motor4);
$sheet->setCellValue('E80', $vibraciones_motor4);
$sheet->setCellValue('H80', $rango_motor4);
$sheet->setCellValue('I80', $amperaje_motor4);
$sheet->setCellValue('J80', $observaciones_motor4);

// EQUIPO 5 (Chumacera)
$sheet->setCellValue('C81', $equipo_name5);
$sheet->setCellValue('D81', $termografia5);
$sheet->setCellValue('E81', $vibraciones5);
$sheet->setCellValue('H81', $rango5);
$sheet->setCellValue('I81', $amperaje5);
$sheet->setCellValue('J81', $observaciones5);

// EQUIPO 5 (Motor)
$sheet->setCellValue('C82', $equipo_name5_motor);
$sheet->setCellValue('D82', $termografia_motor5);
$sheet->setCellValue('E82', $vibraciones_motor5);
$sheet->setCellValue('H82', $rango_motor5);
$sheet->setCellValue('I82', $amperaje_motor5);
$sheet->setCellValue('J82', $observaciones_motor5);

// EQUIPO 6 (Chumacera)
$sheet->setCellValue('C83', $equipo_name6);
$sheet->setCellValue('D83', $termografia6);
$sheet->setCellValue('E83', $vibraciones6);
$sheet->setCellValue('H83', $rango6);
$sheet->setCellValue('I83', $amperaje6);
$sheet->setCellValue('J83', $observaciones6);

// EQUIPO 6 (Motor)
$sheet->setCellValue('C84', $equipo_name6_motor);
$sheet->setCellValue('D84', $termografia_motor6);
$sheet->setCellValue('E84', $vibraciones_motor6);
$sheet->setCellValue('H84', $rango_motor6);
$sheet->setCellValue('I84', $amperaje_motor6);
$sheet->setCellValue('J84', $observaciones_motor6);



    // Procesar firma del solicitante
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
        $firmaSolicitanteImg->setWorksheet($sheet);
    }

    // Procesar firma del autorizante
    $firmaAutorizado = $_POST['firma_autorizado'];
    if ($firmaAutorizado) {
        $firmaAutorizadoPath = '../archivos/formularios/firma_autorizado.png';
        $firmaAutorizadoData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $firmaAutorizado));

        if (file_put_contents($firmaAutorizadoPath, $firmaAutorizadoData) === false) {
            //die("Error al guardar la firma del autorizante.");
        }

        // Insertar la firma en el Excel
        $firmaAutorizadoImg = new Drawing();
        $firmaAutorizadoImg->setPath($firmaAutorizadoPath);
        $firmaAutorizadoImg->setHeight(100);
        $firmaAutorizadoImg->setCoordinates('G13'); // Asignar celda
        $firmaAutorizadoImg->setWorksheet($sheet);
    }

    // Procesar firma del responsable de limpieza
    $firmaRespLimpieza = $_POST['firma_respLim'];
    if ($firmaRespLimpieza) {
        $firmaRespLimpiezaPath = '../archivos/formularios/firma_resp_limpieza.png';
        $firmaRespLimpiezaData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $firmaRespLimpieza));

        if (file_put_contents($firmaRespLimpiezaPath, $firmaRespLimpiezaData) === false) {
            //die("Error al guardar la firma del responsable de limpieza.");
        }

        // Insertar la firma en el Excel
        $firmaRespLimpiezaImg = new Drawing();
        $firmaRespLimpiezaImg->setPath($firmaRespLimpiezaPath);
        $firmaRespLimpiezaImg->setHeight(100);
        $firmaRespLimpiezaImg->setCoordinates('A38'); // Asignar celda
        $firmaRespLimpiezaImg->setWorksheet($sheet);
    }

    // Procesar firma del responsable de revisar la limpieza
    $firmaRespRevisar = $_POST['firma_respLim2'];
    if ($firmaRespRevisar) {
        $firmaRespRevisarPath = '../archivos/formularios/firma_resp_revisar.png';
        $firmaRespRevisarData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $firmaRespRevisar));

        if (file_put_contents($firmaRespRevisarPath, $firmaRespRevisarData) === false) {
            //die("Error al guardar la firma del responsable de revisar la limpieza.");
        }

        // Insertar la firma en el Excel
        $firmaRespRevisarImg = new Drawing();
        $firmaRespRevisarImg->setPath($firmaRespRevisarPath);
        $firmaRespRevisarImg->setHeight(100);
        $firmaRespRevisarImg->setCoordinates('D38'); // Asignar celda
        $firmaRespRevisarImg->setWorksheet($sheet);
    }

    // Procesar solo las imágenes que el usuario haya enviado
    $imagenes = [
        ['campo' => 'imagen',    'celda' => 'A82',  'ruta' => '../archivos/generados/evidencias/imagen_evidencia'],
        ['campo' => 'imagen_1',  'celda' => 'A97',  'ruta' => '../archivos/generados/evidencias/imagen_evidencia_1'],
        ['campo' => 'imagen_2',  'celda' => 'A113', 'ruta' => '../archivos/generados/evidencias/imagen_evidencia_2'],
        ['campo' => 'imagen_3',  'celda' => 'A135', 'ruta' => '../archivos/generados/evidencias/imagen_evidencia_3'],
        ['campo' => 'imagen2',   'celda' => 'G82',  'ruta' => '../archivos/formularios/imagen_evidencia2'],
        ['campo' => 'imagen2_1', 'celda' => 'G97',  'ruta' => '../archivos/generados/evidencias/imagen_evidencia2_1'],
        ['campo' => 'imagen2_2', 'celda' => 'G113', 'ruta' => '../archivos/generados/evidencias/imagen_evidencia2_2'],
        ['campo' => 'imagen2_3', 'celda' => 'G135', 'ruta' => '../archivos/generados/evidencias/imagen_evidencia2_3'],
    ];

    foreach ($imagenes as $img) {
    $campo = $img['campo'];
    $celda = $img['celda'];
    $rutaBase = $img['ruta'];
    if (
        isset($_FILES[$campo]) &&
        $_FILES[$campo]['error'] === UPLOAD_ERR_OK &&
        !empty($_FILES[$campo]['tmp_name']) &&
        is_string($_FILES[$campo]['tmp_name'])
    ) {
        $rutaFinal = $rutaBase . uniqid() . '.png';
        $tmpPath = $_FILES[$campo]['tmp_name'];
        if (move_uploaded_file($tmpPath, $rutaFinal)) {
            $imgDrawing = new Drawing();
            $imgDrawing->setPath($rutaFinal);
            $imgDrawing->setHeight(220);
            $imgDrawing->setCoordinates($celda);
            $imgDrawing->setWorksheet($sheet);
        } else {
            // die("Error al guardar la imagen de evidencia: $campo");
        }
    }
}


//LOGO
    $imagen_logoPath = '../archivos/formularios/imagen_logo.jpeg';
    $imagen_logoImg = new Drawing();
    $imagen_logoImg->setPath($imagen_logoPath);
    $imagen_logoImg->setHeight(100); // Ajusta el tamaño según sea necesario
    $imagen_logoImg->setCoordinates('A1');
    $imagen_logoImg->setWorksheet($sheet);

    $imagen_logo2Path = '../archivos/formularios/imagen_logo.jpeg';
    $imagen_logo2Img = new Drawing();
    $imagen_logo2Img->setPath($imagen_logo2Path);
    $imagen_logo2Img->setHeight(100); // Ajusta el tamaño según sea necesario
    $imagen_logo2Img->setCoordinates('A41');
    $imagen_logo2Img->setWorksheet($sheet);




  //Guardar el excel en el server
    $ubicacionZS="../archivos/generados/excelS_MZS";
    $ubicacion="../archivos/generados/excelS_M";
    $prefijo="Solicitud";
    $extension="Xlsx";


    function guardarConNombreConsecutivo($ubicacion, $prefijo, $extension, $objetoDañado) {
        $numero = 1; // Inicia desde 1
    
        do {
            $nombreArchivo = $prefijo . "_" . $numero ."_" .  $objetoDañado . "_" . date('Y-m-d') . "." . $extension;
            $rutaCompleta = $ubicacion . "/" . $nombreArchivo;
            $numero++;
        } while (file_exists($rutaCompleta));
    
        return $rutaCompleta; // Devuelve la ruta completa del archivo
    }
    
    // Asegúrate de que la carpeta destino exista
    if (!is_dir($ubicacion)) {
        mkdir($ubicacion, 0777, true); 
    }
    
    // Llamada a la función para generar el nombre del archivo
    if ($sede === 'ZC'){
    $rutaArchivo = guardarConNombreConsecutivo($ubicacion, $prefijo, $extension, $objetoDañado);
    
    // Guardar el archivo Excel en la ruta generada
    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save($rutaArchivo);
    
    // Generar un archivo temporal con un nombre único que también incluya fecha y hora
    $tempExcelPath = "../archivos/formularios/SolicitudMantenimiento_". $objetoDañado . date('Y-m-d_H-i-s') . ".xlsx";
    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save($tempExcelPath);
    }
    else {
        $rutaArchivo2 = guardarConNombreConsecutivo($ubicacionZS, $prefijo, $extension, $objetoDañado);
    
    // Guardar el archivo Excel en la ruta generada
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($rutaArchivo2);
    
    // Generar un archivo temporal con un nombre único que también incluya fecha y hora
        $tempExcelPath = "../archivos/formularios/SolicitudMantenimiento_" . date('Y-m-d_H-i-s') . ".xlsx";
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($tempExcelPath);
    }
unlink($tempExcelPath);
if ($_SESSION['rol'] === 'adm') {
    header("Location: ../template/menu_adm.html"); // Redirige al menú del administrador
} elseif ($_SESSION['rol'] === 'usr') {
    header("Location: ../template/menu.html"); // Redirige al menú de usuario
} else {
    header("Location: ../template/menu.html"); // O redirige a un menú genérico si el rol no está especificado
}
exit();
?>