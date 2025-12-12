<?php
session_start();
require __DIR__ . '/../../vendor/autoload.php';
require '../sesion.php'; // Incluye el archivo de autenticación
require '../conection.php'; // Conexión a la base de datos

verificarAutenticacion();
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
// Verificar que el usuario está definido
$queryGetProceso = "SELECT id_proceso, archivo_ruta FROM control_molienda_zs WHERE zona = ? ORDER BY id_proceso DESC LIMIT 1";
$stmtGetProceso = $pdoControl_zs->prepare($queryGetProceso);
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
$queryCheck = "SELECT * FROM control_molienda_zs WHERE id_proceso = ? LIMIT 1";
$stmt = $pdoControl_zs->prepare($queryCheck);
$stmt->execute([$idProceso]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$result) {
    die("Error: Se necesita completar primero el Turno 1.");
}

if ($result['turn2'] == 1) {
    die("Error: El turno 2 ya ha sido completado.");
}
// ACTUALIZAR EL TURNO 2
$queryUpdateTurno = "UPDATE control_molienda_zs SET turn2 = 1 WHERE id_proceso = ?";
$stmtUpdate = $pdoControl_zs->prepare($queryUpdateTurno);
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
    $responsableint = $_POST['responsableint'] ??'';
    $pesores1_2 = $_POST['peso-Mogolla_x40']?? '';
    $pesores1_3 = $_POST['peso-Segunda_x50']?? '';
    $pesores1_4 = $_POST['peso-Semola_Fina_x25']?? '';
    $pesores1_6 = $_POST['peso-Germen_x25']?? '';
    $pesores1_7 = $_POST['peso-Granza']?? '';
    $pesores1_10 = $_POST['peso-Salvado_x30']?? '';
    $codigores1_2 = $_POST['codigo-Mogolla_x40']?? '';
    $codigores1_3 = $_POST['codigo-Segunda_x50']?? '';
    $codigores1_4 = $_POST['codigo-Semola_Fina_x25']?? '';
    $codigores1_6 = $_POST['codigo-Germen_x25']?? '';
    $codigores1_7 = $_POST['codigo-Granza']?? '';
    $codigores1_10 = $_POST['codigo-Salvado_x30']?? '';
    //SUBPRODUCTO EXTRA
    $pesoresext1_2 = $_POST['pesoext-Mogolla_x40']?? '';
    $pesoresext1_3 = $_POST['pesoext-Segunda_x50']?? '';
    $pesoresext1_4 = $_POST['pesoext-Granza']?? '';
    $pesoresext1 = $_POST['pesoext-Salvado_x30']?? '';

    $codigoresext1_2 = $_POST['codigoext-Mogolla_x40']?? '';
    $codigoresext1_3 = $_POST['codigoext-Segunda_x50']?? '';
    $codigoresext1_4 = $_POST['codigoext-Granza']?? '';
    $codigoresext1 = $_POST['codigoext-Salvado_x30']?? '';

    $codigomat1_1 = $_POST['codigo-Empaque_ExtraPan_x50']?? '0';
    $codigomat1_2 = $_POST['codigo-Empaque_ExtraPan_x25']?? '0';
    $codigomat1_3 = $_POST['codigo-Empaque_ExtraPan_x10']?? '0';
    $codigomat1_4 = $_POST['codigo-Empaque_Galeras_Rojo_x50']?? '0';
    $codigomat1_9 = $_POST['codigo-Empaque_Galeras_KRAFT_x25']?? '0';
    $codigomat1_10 = $_POST['codigo-Empaque_Multi_Beige_x25']?? '0';
    $codigomat1_11 = $_POST['codigo-Empaque_Galeras_MOG_x40']?? '0';
    $codigomat1_12 = $_POST['codigo-Empaque_Galeras_Sal_x25']?? '0';
    $codigomat1_13 = $_POST['codigo-Empaque_Galeras_Seg_x50']?? '0';
    $codigomat1_14 = $_POST['codigo-Empaque_Artesanal_x_10_kg_Letra_Roja']?? '0';
    $codigomat1_15 = $_POST['codigo-Mejorante_Extrapan']?? '0';
    $codigomat1_16 = $_POST['codigo-Mejorante_Artesanal']?? '0';
    $codigomat1_17 = $_POST['codigo-Hilo_Blanco']?? '0';
    $codigomat1_20 = $_POST['codigo-Empaque_Nariño_x10']?? '0';
    $codigomat1_21 = $_POST['codigo-Bolsa_Fardo_xLB']?? '0';
    $codigomat1_22 = $_POST['codigo-Lamina_nariño_xLB']?? '0';
    $codigomat1_23 = $_POST['codigo-Lamina_nariño_xKg']?? '0';
    $codigomat1_24 = $_POST['codigo-Lamina_nariño_xcuarto']?? '0';
    $codigomat1_25 = $_POST['codigo-Empaque_Blanco_x50']?? '0';
    $codigomat1_26 = $_POST['codigo-Cinta_Trans_Termina_40x122mm']?? '0';
    $codigomat1_27 = $_POST['codigo-Cinta_Trans_Termina_85x122mm']?? '0';
    $codigomat1_28 = $_POST['codigo-Etiqueta_Adhesiva_de_trans']?? '0';
    $codigomat1_29 = $_POST['codigo-Premezcla_Vitaminica']?? '0';
    $codigomat2_1 = $_POST['codigomat2-1']?? '';
    $codigomat2_2 = $_POST['codigomat2-2']?? '';
    $codigomat2_3 = $_POST['codigomat2-3']?? '';
    $codigomat2_3 = $_POST['codigomat2-4']?? '';
    $codigomat2_3 = $_POST['codigomat2-5']?? '';
    //HARINAS
    $valor1 = $_POST['valor1']?? '';
    $valor1_2 = $_POST['extrapanx50valor1']??'';
    $valor2 = $_POST['valor2']?? '';
    $valor2_2 = $_POST['extrapanx25valor1']?? '';
    $valor3 = $_POST['valor3']?? '';
    $valor3_2 = $_POST['extrapanx10valor1']?? '';
    $valor4 = $_POST['valor4']?? '';
    $valor4_2 = $_POST['artex50valor1']?? '';
    $valor5 = $_POST['valor5']?? '';
    $valor5_2 = $_POST['valorartex101']?? '';
    $valor6 = $_POST['valor6']?? '';
    $valor6_2 = $_POST['valorartesanal5und1']?? '';
    $valor7 = $_POST['valor7']?? '';
    $valor7_2 = $_POST['valorharinanax2_51']?? '';
    $valor8 = $_POST['valor8']?? '';
    $valor8_2 = $_POST['valorharinanax1_1']?? '';
    $valor9 = $_POST['valor9']?? '';
    $valor9_2 = $_POST['valorharinanaxLB1']?? '';
    $valor10 = $_POST['valor10']?? '';
    $valor10_2 = $_POST['valorharinaint1']?? '';
    $valor11 = $_POST['valor11']?? '';
    $valor11_2 = $_POST['valorharina_art']?? '';
    $valor12 = $_POST['valor12']?? '';
    $valor12_2 = $_POST['extrapanx10_5undvalor1 ']?? '';
    $valor13 = $_POST['valor13']?? '';
    $valor13_2 = $_POST['valorharinaNA1']?? '';
    $valor14 = $_POST['valor14']?? '';
    $valor14_2 = $_POST['valorharinaNA']?? '';
    $valor15 = $_POST['valor15']?? '';
    $valor15_2 = $_POST['valorgermen']?? '';
    $valor16 = $_POST['valor16']?? '';
    $valor16_2 = $_POST['valorsemola']?? '';
    $lote1 = $_POST['lote1']?? '';
    $lote1_2 = $_POST['extrapanx50lote1']?? '';
    $lote2 = $_POST['lote2']?? '';
    $lote2_2 = $_POST['extrapanx25lote1']?? '';
    $lote3 = $_POST['lote3']?? '';
    $lote3_2 = $_POST['extrapanx10lote1']?? '';
    $lote4 = $_POST['lote4']?? '';
    $lote4_2 = $_POST['artex50lote1']?? '';
    $lote5 = $_POST['lote5']?? '';
    $lote5_2 = $_POST['loteartex101']?? '';
    $lote6 = $_POST['lote6']?? '';
    $lote6_2 = $_POST['loteartesanal5und1']?? '';
    $lote7 = $_POST['lote7']?? '';
    $lote7_2 = $_POST['loteharinanax2_51']?? '';
    $lote8 = $_POST['lote8']?? '';
    $lote8_2 = $_POST['loteharinanax1_1']?? '';
    $lote9 = $_POST['lote9']?? '';
    $lote9_2 = $_POST['loteharinanaxLB1']?? '';
    $lote10 = $_POST['lote10']?? '';
    $lote10_2 = $_POST['extrapanx105undlote1']?? '';
    $lote11 = $_POST['lote11']?? '';
    $lote11_2 = $_POST['loteharina_art']?? '';
    $lote12 = $_POST['lote12']?? '';
    $lote12_2 = $_POST['loteharinaint1']?? '';
    $lote13 = $_POST['lote13']?? '';
    $lote13_2 = $_POST['loteharinaNA1']?? '';
    $lote14 = $_POST['lote14']?? '';
    $lote14_2 = $_POST['loteharinaNA']?? '';
    $lote15 = $_POST['lote15']?? '';
    $lote15_2 = $_POST['lotegermen']?? '';
    $lote16 = $_POST['lote16']?? '';
    $lote16_2 = $_POST['lotesemola']?? '';
    $nomgomat2_1 = $_POST['nomgomat2-1']?? '';
    $nomgomat2_2 = $_POST['nomgomat2-2']?? '';
    $nomgomat2_3 = $_POST['nomgomat2-3']?? '';
    $nomgomat2_3 = $_POST['nomgomat2-4']?? '';
    $nomgomat2_3 = $_POST['nomgomat2-5']?? '';
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
    $valor_5 = $_POST['valor-5']?? '0';}
$spreadsheet = IOFactory::load($rutaArchivoPrimerTurno);
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('H65',$responsableint);
    $sheet->setCellValue('I6', $hora);
    $sheet->setCellValue('F24', $codigores1_2);
    $sheet->setCellValue('F26', $codigores1_3);
    $sheet->setCellValue('F38', $codigores1_4);
    $sheet->setCellValue('F32', $codigores1_6);
    $sheet->setCellValue('F36', $codigores1_7);
    $sheet->setCellValue('F28', $codigores1_10);
    
    $sheet->setCellValue('E24', $pesores1_2);
    $sheet->setCellValue('E26', $pesores1_3);
    $sheet->setCellValue('E38', $pesores1_4);
    $sheet->setCellValue('E32', $pesores1_6);
    $sheet->setCellValue('E36', $pesores1_7);
    $sheet->setCellValue('E28', $pesores1_10);
    //SUBPRODUCTO EXTRA
    //LOTES
    $sheet->setCellValue('F29', $codigoresext1);
    $sheet->setCellValue('F25', $codigoresext1_2);
    $sheet->setCellValue('F27', $codigoresext1_3);
    $sheet->setCellValue('F37', $codigoresext1_4);
    
    $sheet->setCellValue('E41', $pesoresext1);
    $sheet->setCellValue('E19', $pesoresext1_2);
    $sheet->setCellValue('E21', $pesoresext1_3);
    $sheet->setCellValue('E37', $pesoresext1_4);
    
    $sheet->setCellValue('E10',$valor1);
    $sheet->setCellValue('E11',$valor1_2);
    //$sheet->setCellValue('E12',$valor2);
    //$sheet->setCellValue('E13',$valor2_2);
    //$sheet->setCellValue('E15',$valor3_2);
    //$sheet->setCellValue('E14',$valor3);
    $sheet->setCellValue('E13',$valor4_2);
    $sheet->setCellValue('E12',$valor4);
    $sheet->setCellValue('E14',$valor5);
    $sheet->setCellValue('E27',$valor5_2);
    //$sheet->setCellValue('E28',$valor6);
    //$sheet->setCellValue('E29',$valor6_2);
    $sheet->setCellValue('E18',$valor7);
    $sheet->setCellValue('E19',$valor7_2);
    $sheet->setCellValue('E20',$valor8);
    $sheet->setCellValue('E21',$valor8_2);
    $sheet->setCellValue('E22',$valor9);
    $sheet->setCellValue('E23',$valor9_2);
    $sheet->setCellValue('E40',$valor10);
    $sheet->setCellValue('E41',$valor10_2);
    $sheet->setCellValue('E16',$valor11);
    $sheet->setCellValue('E17',$valor11_2);
    $sheet->setCellValue('E48',$valor12);
    $sheet->setCellValue('E49',$valor12_2);
    //$sheet->setCellValue('E52',$valor13);
    //$sheet->setCellValue('E53',$valor13_2);
    $sheet->setCellValue('E34',$valor14);
    $sheet->setCellValue('E35',$valor14_2);
    $sheet->setCellValue('E32',$valor15);
    $sheet->setCellValue('E33',$valor15_2);
    $sheet->setCellValue('E38',$valor16);
    $sheet->setCellValue('E39',$valor16_2);
    
    //LOTES HARINA
    $sheet->setCellValue('F10',$lote1);
    $sheet->setCellValue('F11',$lote1_2);
    //$sheet->setCellValue('F12',$lote2);
    //$sheet->setCellValue('F13',$lote2_2);
    //$sheet->setCellValue('F14',$lote3);
    //$sheet->setCellValue('F15',$lote3_2);
    $sheet->setCellValue('F12',$lote4);
    $sheet->setCellValue('F13',$lote4_2);
    $sheet->setCellValue('F14',$lote5);
    $sheet->setCellValue('F15',$lote5_2);
    //$sheet->setCellValue('F28',$lote6);
    //$sheet->setCellValue('F29',$lote6_2);
    $sheet->setCellValue('F18',$lote7);
    $sheet->setCellValue('F19',$lote7_2);
    $sheet->setCellValue('F20',$lote8);
    $sheet->setCellValue('F21',$lote8_2);
    $sheet->setCellValue('F22',$lote9);
    $sheet->setCellValue('F23',$lote9_2);
    $sheet->setCellValue('F40',$lote10);
    $sheet->setCellValue('F41',$lote10_2);
    $sheet->setCellValue('F16',$lote11);
    $sheet->setCellValue('F17',$lote11_2);
    $sheet->setCellValue('F48',$lote12);
    $sheet->setCellValue('F49',$lote12_2);
    //$sheet->setCellValue('F52',$lote13);
    //$sheet->setCellValue('F53',$lote13_2);
    $sheet->setCellValue('F34',$lote14);
    $sheet->setCellValue('F35',$lote14_2);
    $sheet->setCellValue('F32',$lote15);
    $sheet->setCellValue('F33',$lote15_2);
    $sheet->setCellValue('F38',$lote16);
    $sheet->setCellValue('F39',$lote16_2);
    
    $sheet->setCellValue('J10',$codigomat1_1);
    //$sheet->setCellValue('J12',$codigomat1_2);
    //$sheet->setCellValue('J14',$codigomat1_3);
    $sheet->setCellValue('J12',$codigomat1_4);
    //$sheet->setCellValue('J18',$codigomat1_9);
    $sheet->setCellValue('J32',$codigomat1_10);
    $sheet->setCellValue('J24',$codigomat1_11);
    $sheet->setCellValue('J28',$codigomat1_12);
    $sheet->setCellValue('J26',$codigomat1_13);
    //$sheet->setCellValue('J50',$codigomat1_14);
    $sheet->setCellValue('J52',$codigomat1_15);
    $sheet->setCellValue('J50',$codigomat1_16);
    $sheet->setCellValue('J34',$codigomat1_17);
    $sheet->setCellValue('J14',$codigomat1_20);
    $sheet->setCellValue('J16',$codigomat1_21);
    $sheet->setCellValue('J22',$codigomat1_22);
    $sheet->setCellValue('J20',$codigomat1_23);
    $sheet->setCellValue('J18',$codigomat1_24);
    $sheet->setCellValue('J30',$codigomat1_25);
    $sheet->setCellValue('J36',$codigomat1_26);
    $sheet->setCellValue('J38',$codigomat1_27);
    $sheet->setCellValue('J46',$codigomat1_28);
    $sheet->setCellValue('J48',$codigomat1_29);
    $sheet->setCellValue('E63',$nomresp1_1);
    $sheet->setCellValue('E64',$nomresp1_2);
    $sheet->setCellValue('E65',$nomresp1_3);
    $sheet->setCellValue('E66',$nomresp1_4);
    $sheet->setCellValue('E67',$nomresp1_5);
$sheet->setCellValue('A70',$observaciones);
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
    $firmaturn1Img->setCoordinates('H63'); // Asignar celda
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
