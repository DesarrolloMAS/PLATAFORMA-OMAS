<?php
session_start();
require __DIR__ . '/../../vendor/autoload.php';
require '../sesion.php'; // Incluye el archivo de autenticación
require '../conection.php'; // Conexión a la base de datos

verificarAutenticacion();
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
// Verificar que el usuario está definido
$queryGetProceso = "SELECT id_proceso, archivo_ruta FROM control_molienda WHERE zona = ? ORDER BY id_proceso DESC LIMIT 1";
$stmtGetProceso = $pdoControl->prepare($queryGetProceso);
$stmtGetProceso->execute([$_SESSION['sede']]);
$resultProceso = $stmtGetProceso->fetch(PDO::FETCH_ASSOC);
if (!$resultProceso) {
    die("Error: No se encontró un proceso previo en la base de datos.");
}
$idProceso = $resultProceso['id_proceso'];
$rutaArchivoPrimerTurno = $resultProceso['archivo_ruta'];
if (empty($rutaArchivoPrimerTurno) || !file_exists($rutaArchivoPrimerTurno)) {
    die("Error: El turno 1 no fue completado o el archivo no existe.");
}
try {
    $spreadsheet = IOFactory::load($rutaArchivoPrimerTurno);
    $sheet = $spreadsheet->getActiveSheet();
} catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
    die("Error al cargar el archivo Excel: " . $e->getMessage());
}

// VERIFICAR SI EL TURNO 2 YA FUE COMPLETADO
$queryCheck = "SELECT * FROM control_molienda WHERE id_proceso = ? LIMIT 1";
$stmt = $pdoControl->prepare($queryCheck);
$stmt->execute([$idProceso]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$result) {
    die("Error: Se necesita completar primero el Turno 1.");
}

if ($result['turn3'] == 1) {
    die("Error: El turno 3 ya ha sido completado.");
}
// ACTUALIZAR EL TURNO 3
$queryUpdateTurno = "UPDATE control_molienda SET turn3 = 1 WHERE id_proceso = ?";
$stmtUpdate = $pdoControl->prepare($queryUpdateTurno);
$stmtUpdate->execute([$idProceso]);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
//PROCESAMIENTO DE DATOS
//echo "<pre>";
//print_r($_POST);
//echo "</pre>";
//exit;


try {
    // Cargar el archivo Excel
    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($rutaArchivoPrimerTurno);

    // Obtener la hoja activa
    $sheet = $spreadsheet->getActiveSheet();
} catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
    die("Error al cargar el archivo Excel: " . $e->getMessage());
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hora = $_POST['hora'];
    $residuoSelect1 = $_POST['residuoSelect1'] ??'No seleccionado' ;
    $pesores1 = $_POST['peso-Salvado_x25']?? '';
    $pesores1_2 = $_POST['peso-Mogolla_x40']?? '';
    $pesores1_3 = $_POST['peso-Segunda_x50']?? '';
    $pesores1_4 = $_POST['peso-Semola_Fina_x25']?? '';
    $pesores1_5 = $_POST['peso-Semola_Gruesa_x25']?? '';
    $pesores1_6 = $_POST['peso-Germen_x25']?? '';
    $pesores1_7 = $_POST['peso-Granza']?? '';
    $pesores1_8 = $_POST['peso-Vitamina']?? '';
    $pesores1_9 = $_POST['peso-Hilo']?? '';
    $pesores1_10 = $_POST['peso-Salvado_x30']?? '';
    $codigores1 = $_POST['codigo-Salvado_x25']?? '';
    $codigores1_2 = $_POST['codigo-Mogolla_x40']?? '';
    $codigores1_3 = $_POST['codigo-Segunda_x50']?? '';
    $codigores1_4 = $_POST['codigo-Semola_Fina_x25']?? '';
    $codigores1_5 = $_POST['codigo-Semola_Gruesa_x25']?? '';
    $codigores1_6 = $_POST['codigo-Germen_x25']?? '';
    $codigores1_7 = $_POST['codigo-Granza']?? '';
    $codigores1_8 = $_POST['codigo-Vitamina']?? '';
    $codigores1_9 = $_POST['codigo-Hilo']??'';
    $codigores1_10 = $_POST['codigo-Salvado_x30']?? '';
        //SUBPRODUCTO EXTRA 
        $pesoresext1 = $_POST['pesoext-Salvado_x25']?? '';
        $pesoresext1_2 = $_POST['pesoext-Mogolla_x40']?? '';
        $pesoresext1_3 = $_POST['pesoext-Segunda_x50']?? '';
        $pesoresext1_4 = $_POST['pesoext-Semola_Fina_x25']?? '';
        $pesoresext1_5 = $_POST['pesoext-Semola_Gruesa_x25']?? '';
        $pesoresext1_6 = $_POST['pesoext-Germen_x25']?? '';
        $pesoresext1_7 = $_POST['pesoext-Granza']?? '';
        $pesoresext1_8 = $_POST['pesoext-Vitamina']?? '';
        $pesoresext1_9 = $_POST['pesoext-Hilo']?? '';
        $pesoresext1_10 = $_POST['pesoext-Salvado_x30']?? '';
        $codigoresext1 = $_POST['codigoext-Salvado_x25']?? '';
        $codigoresext1_2 = $_POST['codigoext-Mogolla_x40']?? '';
        $codigoresext1_3 = $_POST['codigoext-Segunda_x50']?? '';
        $codigoresext1_4 = $_POST['codigoext-Semola_Fina_x25']?? '';
        $codigoresext1_5 = $_POST['codigoext-Semola_Gruesa_x25']?? '';
        $codigoresext1_6 = $_POST['codigoext-Germen_x25']?? '';
        $codigoresext1_7 = $_POST['codigoext-Granza']?? '';
        $codigoresext1_8 = $_POST['codigoext-Vitamina']?? '';
        $codigoresext1_9 = $_POST['codigoext-Hilo']?? '';
        $codigoresext1_10 = $_POST['codigoext-Salvado_x30']?? '';


    $codigomat1_1 = $_POST['codigo-Empaque_ExtraPan_x50']?? '';
    $codigomat1_2 = $_POST['codigo-Empaque_ExtraPan_x25']?? '';
    $codigomat1_3 = $_POST['codigo-Empaque_ExtraPan_x10']?? '';
    $codigomat1_4 = $_POST['codigo-Empaque_Galeras_Rojo_x50']?? '';
    $codigomat1_5 = $_POST['codigo-Empaque_Galeras_Verde_x50']?? '';
    $codigomat1_6 = $_POST['codigo-Empaque_Galeras_Cafe_x50']?? '';
    $codigomat1_7 = $_POST['codigo-Empaque_Galeras_Azul_x50']?? '';
    $codigomat1_8 = $_POST['codigo-Empaque_Galeras_Naranja_x50']?? '';
    $codigomat1_9 = $_POST['codigo-Empaque_Galeras_KRAFT_x25']?? '';
    $codigomat1_10 = $_POST['codigo-Empaque_Multi_Beige_x25']?? '';
    $codigomat1_11 = $_POST['codigo-Empaque_Galeras_MOG_x40']?? '';
    $codigomat1_12 = $_POST['codigo-Empaque_Galeras_Sal_x25']?? '';
    $codigomat1_13 = $_POST['codigo-Empaque_Galeras_Seg_x50']?? '';
    $codigomat1_20 = $_POST['codigo-Empaque_Fuerte_Exp']?? '';
    $codigomat1_14 = $_POST['codigo-Vitaminamat']?? '';
    $codigomat1_15 = $_POST['codigo-Mejorante_Extrapan']?? '';
    $codigomat1_16 = $_POST['codigo-Mejorante_Artesanal']?? '';
    $codigomat1_17 = $_POST['codigo-Hilo_Blanco']?? '';
    $codigomat1_18 = $_POST['codigo-Hilo_Verde']?? '';
    $codigomat1_19 = $_POST['codigo-Hilo_Naranja']?? '';
    $codigomat1_25 = $_POST['codigo-Empaque_Blanco_x50']?? '0';
    $codigomat2_1 = $_POST['codigomat2-1']?? '';
    $codigomat2_2 = $_POST['codigomat2-2']?? '';
    $codigomat2_3 = $_POST['codigomat2-3']?? '';
    $valor1 = $_POST['valor1']?? '';
    $valor1_2 = $_POST['extrapanx50valor1']??'';
    $valor2 = $_POST['valor2']?? '';
    $valor2_2 = $_POST['extrapanx25valor1']?? '';
    $valor3 = $_POST['valor3']?? '';
    $valor3_2 = $_POST['extrapanx10valor1']?? '';
    $valor4 = $_POST['valor4']?? '';
    $valor4_2 = $_POST['artex50valor1']?? '';
    $valor5 = $_POST['valor5']?? '';
    $valor5_2 = $_POST['artex25valor1']?? '';
    $valor6 = $_POST['valor6']?? '';
    $valor6_2 = $_POST['fuertex25valor1']?? '';
    $valor7 = $_POST['valor7']?? '';
    $valor7_2 = $_POST['natux50valor1']?? '';
    $valor8 = $_POST['valor8']?? '';
    $valor8_2 = $_POST['expecialx50valor1']?? '';
    $valor9 = $_POST['valor9']?? '';
    $valor9_2 = $_POST['espx25valor1']?? '';
    $valor10 = $_POST['valor10']?? '';
    $valor10_2 = $_POST['exc50valor1']?? '';
    $valor11 = $_POST['valor11']?? '';
    $valor11_2 = $_POST['desarrollovalor1']?? '';
    $valor12 = $_POST['valor12']?? '';
    $valor12_2 = $_POST['altavalor1']?? '';
    $valor13 = $_POST['valor13']?? '';
    $valor13_2 = $_POST['mediavalor1']?? '';
    $valor14 = $_POST['valor14']?? '';
    $valor14_2 = $_POST['bajavalor1']?? '';
    $valor15 = $_POST['valor15']?? '';
    $valor15_2 = $_POST['valorartesanal1']?? '';
    $valor16 = $_POST['valor16']?? '';
    $valor16_2 = $_POST['valorfuertexp1']?? '';
    $valor17 = $_POST['valor17']?? '';
    $valor17_2 = $_POST['valorartex101']?? '';
    $valor18 = $_POST['valor18']?? '';
    $valor18_2 = $_POST['valorartesanal5und1']?? '';
    $valor19 = $_POST['valor19']?? '';
    $valor19_2 = $_POST['valorharinanax2_51']?? '';
    $valor20 = $_POST['valor20']?? '';
    $valor20_2 = $_POST['valorharinanax1_1']?? '';
    $valor21 = $_POST['valor21']?? '';
    $valor21_2 = $_POST['valorharinanaxLB1']?? '';
    $valor22 = $_POST['valor22']?? '';
    $valor22_2 = $_POST['valorharinaint1']?? '';
    $valor23 = $_POST['valor23']?? '';
    $valor23_2 = $_POST['valorharinaNRÑ']?? '';
    $valor24 = $_POST['valor24']?? '';
    $valor24_2 = $_POST['valorharinaNRÑ2']?? '';
    $lote1 = $_POST['lote1']?? '';
    $lote1_2 = $_POST['extrapanx50lote1']?? '';
    $lote2 = $_POST['lote2']?? '';
    $lote2_2 = $_POST['extrapanx25lote1']?? '';
    $lote3 = $_POST['lote3']?? '';
    $lote3_2 = $_POST['extrapanx10lote1']?? '';
    $lote4 = $_POST['lote4']?? '';
    $lote4_2 = $_POST['artex50lote1']?? '';
    $lote5 = $_POST['lote5']?? '';
    $lote5_2 = $_POST['artex25lote1']?? '';
    $lote6 = $_POST['lote6']?? '';
    $lote6_2 = $_POST['fuertex25lote1']?? '';
    $lote7 = $_POST['lote7']?? '';
    $lote7_2 = $_POST['natux50lote1']?? '';
    $lote8 = $_POST['lote8']?? '';
    $lote8_2 = $_POST['expecialx50lote1']?? '';
    $lote9 = $_POST['lote9']?? '';
    $lote9_2 = $_POST['espx25lote1']?? '';
    $lote10 = $_POST['lote10']?? '';
    $lote10_2 = $_POST['exc50lote1']?? '';
    $lote11 = $_POST['lote11']?? '';
    $lote11_2 = $_POST['desarrollolote1']?? '';
    $lote12 = $_POST['lote12']?? '';
    $lote12_2 = $_POST['altalote1']?? '';
    $lote13 = $_POST['lote13']?? '';
    $lote13_2 = $_POST['medialote1']?? '';
    $lote14 = $_POST['lote14']?? '';
    $lote14_2 = $_POST['bajalote1']?? '';
    $lote15 = $_POST['lote15']?? '';
    $lote15_2 = $_POST['loteartesanal1']?? '';
    $lote16 = $_POST['lote16']?? '';
    $lote17 = $_POST['lote17']?? '';
    $lote17_2 = $_POST['loteartex101']?? '';
    $lote18 = $_POST['lote18']?? '';
    $lote18_2 = $_POST['loteartesanal5und1']?? '';
    $lote19 = $_POST['lote19']?? '';
    $lote19_2 = $_POST['loteharinanax2_51']?? '';
    $lote20 = $_POST['lote20']?? '';
    $lote20_2 = $_POST['loteharinanax1_1']?? '';
    $lote21 = $_POST['lote21']?? '';
    $lote21_2 = $_POST['loteharinanaxLB1']?? '';
    $lote22 = $_POST['lote22']?? '';
    $lote22_2 = $_POST['loteharinaint1']?? '';
    $lote23 = $_POST['lote23']?? '';
    $lote23_2 = $_POST['loteharinaNRÑ']?? '';
    $lote24 = $_POST['lote24']?? '';
    $lote24_2 = $_POST['loteharinaNRÑ2']?? '';
    $nomgomat2_1 = $_POST['nomgomat2-1']?? '';
    $nomgomat2_2 = $_POST['nomgomat2-2']?? '';
    $nomgomat2_3 = $_POST['nomgomat2-3']?? '';
    $nomresp1_1 = $_POST['nomresp-1']?? '';
    $nomresp1_2 = $_POST['nomresp-2']?? '';
    $nomresp1_3 = $_POST['nomresp-3']?? '';
    $nomresp1_4 = $_POST['nomresp-4']?? '';
    $nomresp1_5 = $_POST['nomresp-5']?? '';

    $nomhari_1 = $_POST['nomhari-1']?? '';
    $nomhari_2 = $_POST['nomhari-2']?? '';
    $nomhari_3 = $_POST['nomhari-3']?? '';
    $nomhari_4 = $_POST['nomhari-4']?? '';
    $nomhari_5 = $_POST['nomhari-5']?? '';
    $bultos_1 = $_POST['bultos-1']?? '0';
    $bultos_2 = $_POST['bultos-2']?? '0';
    $bultos_3 = $_POST['bultos-3']?? '0';
    $bultos_4 = $_POST['bultos-4']?? '0';
    $bultos_5 = $_POST['bultos-5']?? '0';
    $lote_1 = $_POST['lote-1']?? '';
    $lote_2 = $_POST['lote-2']?? '';
    $lote_3 = $_POST['lote-3']?? '';
    $lote_4 = $_POST['lote-4']?? '';
    $lote_5 = $_POST['lote-5']?? '';
    $valor_1 = $_POST['valor-1']?? '0';
    $valor_2 = $_POST['valor-2']?? '0';
    $valor_3 = $_POST['valor-3']?? '0';
    $valor_4 = $_POST['valor-4']?? '0';
    $valor_5 = $_POST['valor-5']?? '0';
    $observaciones = $_POST['observaciones']??'';
}
$spreadsheet = IOFactory::load($rutaArchivoPrimerTurno);
    $sheet = $spreadsheet->getActiveSheet();

$sheet->setCellValue('K7', $hora);
$sheet->setCellValue('H45', $codigores1);
$sheet->setCellValue('H47', $codigores1_2);
$sheet->setCellValue('H49', $codigores1_3);
$sheet->setCellValue('H51', $codigores1_4);
$sheet->setCellValue('H53', $codigores1_5);
$sheet->setCellValue('H55', $codigores1_6);
$sheet->setCellValue('H57', $codigores1_7);
$sheet->setCellValue('H59', $codigores1_8);
$sheet->setCellValue('H61', $codigores1_9);
$sheet->setCellValue('H77', $codigores1_10);
$sheet->setCellValue('K59', $codigomat2_1);
$sheet->setCellValue('K61', $codigomat2_2);
$sheet->setCellValue('G45', $pesores1);
$sheet->setCellValue('G47', $pesores1_2);
$sheet->setCellValue('G49', $pesores1_3);
$sheet->setCellValue('G51', $pesores1_4);
$sheet->setCellValue('G53', $pesores1_5);
$sheet->setCellValue('G55', $pesores1_6);
$sheet->setCellValue('G57', $pesores1_7);
$sheet->setCellValue('G59', $pesores1_8);
$sheet->setCellValue('G61', $pesores1_9);
$sheet->setCellValue('G77', $pesores1_10);
//SUBPRODUCTO EXTRA

$sheet->setCellValue('H46', $codigoresext1);
$sheet->setCellValue('H48', $codigoresext1_2);
$sheet->setCellValue('H50', $codigoresext1_3);
$sheet->setCellValue('H52', $codigoresext1_4);
$sheet->setCellValue('H54', $codigoresext1_5);
$sheet->setCellValue('H56', $codigoresext1_6);
$sheet->setCellValue('H58', $codigoresext1_7);
$sheet->setCellValue('H60', $codigoresext1_8);
$sheet->setCellValue('H62', $codigoresext1_9);
$sheet->setCellValue('H78', $codigoresext1_10);
$sheet->setCellValue('G46', $pesoresext1);
$sheet->setCellValue('G48', $pesoresext1_2);
$sheet->setCellValue('G50', $pesoresext1_3);
$sheet->setCellValue('G52', $pesoresext1_4);
$sheet->setCellValue('G54', $pesoresext1_5);
$sheet->setCellValue('G56', $pesoresext1_6);
$sheet->setCellValue('G58', $pesoresext1_7);
$sheet->setCellValue('G60', $pesoresext1_8);
$sheet->setCellValue('G62', $pesoresext1_9);
$sheet->setCellValue('G78', $pesoresext1_10);

$sheet->setCellValue('G11',$valor1);
$sheet->setCellValue('G12',$valor1_2);
$sheet->setCellValue('G13',$valor2);
$sheet->setCellValue('G14',$valor2_2);
$sheet->setCellValue('G16',$valor3_2);
$sheet->setCellValue('G15',$valor3);
$sheet->setCellValue('G18',$valor4_2);
$sheet->setCellValue('G17',$valor4);
$sheet->setCellValue('G19',$valor5);
$sheet->setCellValue('G20',$valor5_2);
$sheet->setCellValue('G21',$valor6);
$sheet->setCellValue('G22',$valor6_2);
$sheet->setCellValue('G23',$valor7);
$sheet->setCellValue('G24',$valor7_2);
$sheet->setCellValue('G25',$valor8);
$sheet->setCellValue('G26',$valor8_2);
$sheet->setCellValue('G27',$valor9);
$sheet->setCellValue('G28',$valor9_2);
$sheet->setCellValue('G29',$valor10);
$sheet->setCellValue('G30',$valor10_2);
$sheet->setCellValue('G31',$valor11);
$sheet->setCellValue('G32',$valor11_2);
$sheet->setCellValue('G33',$valor12);
$sheet->setCellValue('G34',$valor12_2);
$sheet->setCellValue('G35',$valor13);
$sheet->setCellValue('G36',$valor13_2);
$sheet->setCellValue('G37',$valor14);
$sheet->setCellValue('G38',$valor14_2);
$sheet->setCellValue('G39',$valor15);
$sheet->setCellValue('G40',$valor15_2);
$sheet->setCellValue('G41',$valor16);
$sheet->setCellValue('G42',$valor16_2);
$sheet->setCellValue('G59',$valor17);
$sheet->setCellValue('G60',$valor17_2);
$sheet->setCellValue('G61',$valor18);
$sheet->setCellValue('G62',$valor18_2);
$sheet->setCellValue('G63',$valor19);
$sheet->setCellValue('G64',$valor19_2);
$sheet->setCellValue('G65',$valor20);
$sheet->setCellValue('G66',$valor20_2);
$sheet->setCellValue('G67',$valor21);
$sheet->setCellValue('G68',$valor21_2);
$sheet->setCellValue('G71',$valor22);
$sheet->setCellValue('G72',$valor22_2);
$sheet->setCellValue('G73',$valor23);
$sheet->setCellValue('G74',$valor23_2);
$sheet->setCellValue('G75',$valor24);
$sheet->setCellValue('G76',$valor24_2);
//LOTES HARINA
$sheet->setCellValue('H11',$lote1);
$sheet->setCellValue('H12',$lote1_2);
$sheet->setCellValue('H13',$lote2);
$sheet->setCellValue('H14',$lote2_2);
$sheet->setCellValue('H15',$lote3);
$sheet->setCellValue('H16',$lote3_2);
$sheet->setCellValue('H17',$lote4);
$sheet->setCellValue('H18',$lote4_2);
$sheet->setCellValue('H19',$lote5);
$sheet->setCellValue('H20',$lote5_2);
$sheet->setCellValue('H21',$lote6);
$sheet->setCellValue('H22',$lote6_2);
$sheet->setCellValue('H23',$lote7);
$sheet->setCellValue('H24',$lote7_2);
$sheet->setCellValue('H25',$lote8);
$sheet->setCellValue('H26',$lote8_2);
$sheet->setCellValue('H27',$lote9);
$sheet->setCellValue('H28',$lote9_2);
$sheet->setCellValue('H29',$lote10);
$sheet->setCellValue('H30',$lote10_2);
$sheet->setCellValue('H31',$lote11);
$sheet->setCellValue('H32',$lote11_2);
$sheet->setCellValue('H33',$lote12);
$sheet->setCellValue('H34',$lote12_2);
$sheet->setCellValue('H35',$lote13);
$sheet->setCellValue('H36',$lote13_2);
$sheet->setCellValue('H37',$lote14);
$sheet->setCellValue('H38',$lote14_2);
$sheet->setCellValue('H39',$lote15);
$sheet->setCellValue('H40',$lote15_2);
$sheet->setCellValue('H41',$lote16);
$sheet->setCellValue('H42',$lote16_2);
$sheet->setCellValue('H59',$lote17);
$sheet->setCellValue('H60',$lote17_2);
$sheet->setCellValue('H61',$lote18);
$sheet->setCellValue('H62',$lote18_2);
$sheet->setCellValue('H63',$lote19);
$sheet->setCellValue('H64',$lote19_2);
$sheet->setCellValue('H65',$lote20);
$sheet->setCellValue('H66',$lote20_2);
$sheet->setCellValue('H67',$lote21);
$sheet->setCellValue('H68',$lote21_2);
$sheet->setCellValue('H71',$lote22);
$sheet->setCellValue('H72',$lote22_2);
$sheet->setCellValue('H73',$lote23);
$sheet->setCellValue('H74',$lote23_2);
$sheet->setCellValue('H75',$lote24);
$sheet->setCellValue('H76',$lote24_2);
//Materiales
$sheet->setCellValue('M11',$codigomat1_1);
$sheet->setCellValue('M13',$codigomat1_2);
$sheet->setCellValue('M15',$codigomat1_3);
$sheet->setCellValue('M17',$codigomat1_4);
$sheet->setCellValue('M19',$codigomat1_5);
$sheet->setCellValue('M21',$codigomat1_6);
$sheet->setCellValue('M23',$codigomat1_7);
$sheet->setCellValue('M25',$codigomat1_8);
$sheet->setCellValue('M27',$codigomat1_9);
$sheet->setCellValue('M29',$codigomat1_10);
$sheet->setCellValue('M31',$codigomat1_11);
$sheet->setCellValue('M33',$codigomat1_12);
$sheet->setCellValue('M35',$codigomat1_13);
$sheet->setCellValue('M37',$codigomat1_14);
$sheet->setCellValue('M39',$codigomat1_15);
$sheet->setCellValue('M41',$codigomat1_16);
$sheet->setCellValue('M43',$codigomat1_17);
$sheet->setCellValue('M45',$codigomat1_18);
$sheet->setCellValue('M47',$codigomat1_19);
$sheet->setCellValue('M49',$codigomat1_20);
$sheet->setCellValue('M55',$codigomat1_21);
$sheet->setCellValue('M57',$codigomat1_22);
$sheet->setCellValue('M59',$codigomat1_23);
$sheet->setCellValue('M61',$codigomat1_24);
$sheet->setCellValue('M63',$codigomat1_25);
$sheet->setCellValue('M65',$codigomat1_26);
$sheet->setCellValue('M67',$codigomat1_27);
$sheet->setCellValue('M69',$codigomat1_28);
$sheet->setCellValue('M71',$codigomat1_29);
$sheet->setCellValue('M75',$codigomat1_30);
$sheet->setCellValue('M73',$codigomat1_31);
$sheet->setCellValue('J77',$nomgomat2_1);
$sheet->setCellValue('J79',$nomgomat2_2);
$sheet->setCellValue('J81',$nomgomat2_2);
$sheet->setCellValue('G62',$nomresp1_1);
$sheet->setCellValue('G63',$nomresp1_2);
$sheet->setCellValue('G64',$nomresp1_3);
$sheet->setCellValue('G65',$nomresp1_4);
$sheet->setCellValue('G66',$nomresp1_5);

$sheet->setCellValue('B79',$nomhari_1);
$sheet->setCellValue('B81',$nomhari_2);
//$sheet->setCellValue('B73',$nomhari_3);
//$sheet->setCellValue('B74',$nomhari_4);
//$sheet->setCellValue('B75',$nomhari_5);
$sheet->setCellValue('G79',$bultos_1);
$sheet->setCellValue('G81',$bultos_2);
//$sheet->setCellValue('G73',$bultos_3);
//$sheet->setCellValue('G74',$bultos_4);
//$sheet->setCellValue('G75',$bultos_5);
$sheet->setCellValue('H79',$lote_1);
$sheet->setCellValue('H81',$lote_2);
//$sheet->setCellValue('H73',$lote_3);
//$sheet->setCellValue('H74',$lote_4);
//$sheet->setCellValue('H75',$lote_5);
$sheet->setCellValue('I79',($valor_1 * $bultos_1));
$sheet->setCellValue('I81',($valor_2 * $bultos_2));
//$sheet->setCellValue('I73',($valor_3 * $bultos_3));
//$sheet->setCellValue('I74',($valor_4 * $bultos_4));
//$sheet->setCellValue('I75',($valor_5 * $bultos_5));
//FIRMAS
$sheet->setCellValue('A93',$observaciones);
$firmaturn1 = $_POST['firma_turn1'];
if ($firmaturn1) {
    $firmaturn1Path = __DIR__ . '/../../archivos/formularios/firma_turn1.png';
    $firmaturn1Data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $firmaturn1));

    if (file_put_contents($firmaturn1Path, $firmaturn1Data) === false) {
        die("Error al guardar la firma del Turno.");
    }

    // Insertar la firma en el Excel
    $firmaturn1Img = new Drawing();
    $firmaturn1Img->setPath($firmaturn1Path);
    $firmaturn1Img->setHeight(100);
    $firmaturn1Img->setCoordinates('J62'); // Asignar celda
    $firmaturn1Img->setWorksheet($sheet);
}


//GUARDAR EXCEL

// Crear la carpeta si no existe

// Obtener la ruta y el número generado

// Guardar el archivo
$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
$writer->save($rutaArchivoPrimerTurno);

// Ahora puedes usar $numeroAsignado en tu formulario o para otros propósitos
//echo "Número asignado al archivo: " . $numeroAsignado;

// Generar archivo único para descarga
if ($_SESSION['rol'] === 'adm') {
    header("Location: ../../template/menu_adm.html"); // Redirige al menú del administrador
} elseif ($_SESSION['rol'] === '3') {
    header("Location: ../../template/menu.html");
} elseif ($_SESSION['rol'] === '1') {
    header("Location: ../../template/menu_adm.html"); // Redirige al menú de usuario
} else {
    header("Location: ../../template/menu.html"); // O redirige a un menú genérico si el rol no está especificado
}
exit();
?>
