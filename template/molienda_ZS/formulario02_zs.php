<?php
session_start();
require __DIR__ . '/../../vendor/autoload.php';
require '../sesion.php'; // Incluye el archivo de autenticación
require '../conection.php'; // Conexión a la base de datos
$sede = $_SESSION['sede'];
verificarAutenticacion(); // Verifica que el usuario esté autenticado
use PhpOffice\PhpSpreadsheet\IOFactory;
use Mpdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
if (!isset($_SESSION['id_usuario']) || empty($_SESSION['id_usuario'])) {
    die("Error: El usuario no está definido en la sesión.");
}

$queryCheck = "SELECT id_proceso FROM control_molienda_zs ORDER BY id_proceso DESC LIMIT 1";
$stmtCheck = $pdoControl_zs->prepare($queryCheck);
$stmtCheck->execute();
$result = $stmtCheck->fetch(PDO::FETCH_ASSOC);

if (!$result) {
    // Si no hay ningún registro previo, crear uno nuevo
    $queryInsert = "INSERT INTO control_molienda_zs (fecha, archivogen, zona, turn1, turn2, creador) VALUES (NOW(), 0, :zona, 0, 0, :creador)";
    $stmtInsert = $pdoControl_zs->prepare($queryInsert);
    $stmtInsert->bindParam(':zona', $sede);
    $stmtInsert->bindParam(':creador', $_SESSION['id_usuario']);
    $stmtInsert->execute();
    
} else {
    // Si hay un proceso previo, usar su ID
    $idProceso = $result['id_proceso'];
}

// Definir el turno actual (por ejemplo, turno 1)
$turnoActual = "turn1";

// Actualizar el turno correspondiente en la base de datos
$queryUpdate = "UPDATE control_molienda_zs SET $turnoActual = 1 WHERE id_proceso = ?";
$stmtUpdate = $pdoControl_zs->prepare($queryUpdate);
$stmtUpdate->execute([$idProceso]);


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
//PROCESAMIENTO DE DATOS
//echo "<pre>";
//print_r($_POST);
//echo "</pre>";
//exit;
$prefijo = "Control_Molienda_zs";
$extension = "Xlsx";
$ubicacion =  __DIR__ . '/../../archivos/generados/excelC_MZS';
$numero = 0001;
function guardarConNombreConsecutivo($ubicacion, $prefijo, $numero, $extension) { 
    do {
        $nombreArchivo = $prefijo . "_" . $numero . "." . $extension;
        $rutaCompleta = $ubicacion . "/" . $nombreArchivo;
        $numero++;
    } while (file_exists($rutaCompleta));

    // Decrementar el número ya que el último incremento es el no usado
    $numero--;

    return [
        'rutaCompleta' => $rutaCompleta,
        'numero' => $numero
    ];
}
$resultado = guardarConNombreConsecutivo($ubicacion, $prefijo,$numero, $extension);
$rutaArchivo = $resultado['rutaCompleta'];
$numeroAsignado = $resultado['numero'];

$_SESSION['ultimoArchivo'] = $rutaArchivo;
$queryUpdateArchivo = "UPDATE control_molienda_zs SET archivo_ruta = ? WHERE id_proceso = ?";
$stmtUpdateArchivo = $pdoControl_zs->prepare($queryUpdateArchivo);
$stmtUpdateArchivo->execute([$rutaArchivo, $idProceso]);

$responsable = $_SESSION['nombre'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];
    $residuoSelect1 = $_POST['residuoSelect1'] ??'No seleccionado' ;
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
    $valor_5 = $_POST['valor-5']?? '0';
}
//CARGAR PLANTILLA DE EXCEL//
//RUTA DE PLANTILLA
$baseExcelPath = __DIR__ . '/../../archivos/formularios/formulario2_ZS.xlsx';
echo realpath($baseExcelPath);
if (!file_exists($baseExcelPath)) {
    die("Error: No se encontró la plantilla base.");
}
$spreadsheet = IOFactory::load($baseExcelPath);
    $sheet = $spreadsheet->getActiveSheet();
//RELLENAR PLANTILLA//
$sheet->setCellValue('H8',$responsable);
$sheet->setCellValue('H64',$responsableint);
$sheet->setCellValue('G7', $fecha);
$sheet->setCellValue('I5', $hora);
$sheet->setCellValue('D24', $codigores1_2);
$sheet->setCellValue('D26', $codigores1_3);
$sheet->setCellValue('D38', $codigores1_4);
$sheet->setCellValue('D32', $codigores1_6);
$sheet->setCellValue('D36', $codigores1_7);
$sheet->setCellValue('D28', $codigores1_10);
//$sheet->setCellValue('I50', $codigomat2_1);
//$sheet->setCellValue('I51', $codigomat2_2);
//$sheet->setCellValue('I52', $codigomat2_3);

$sheet->setCellValue('C24', $pesores1_2);
$sheet->setCellValue('C26', $pesores1_3);
$sheet->setCellValue('C38', $pesores1_4);
$sheet->setCellValue('C32', $pesores1_6);
$sheet->setCellValue('C36', $pesores1_7);
$sheet->setCellValue('C28', $pesores1_10);
//SUBPRODUCTO EXTRA
//LOTES
$sheet->setCellValue('D29', $codigoresext1);
$sheet->setCellValue('D25', $codigoresext1_2);
$sheet->setCellValue('D27', $codigoresext1_3);
$sheet->setCellValue('D37', $codigoresext1_4);

$sheet->setCellValue('C29', $pesoresext1);
$sheet->setCellValue('C25', $pesoresext1_2);
$sheet->setCellValue('C27', $pesoresext1_3);
$sheet->setCellValue('C37', $pesoresext1_4);

$sheet->setCellValue('C10',$valor1);
$sheet->setCellValue('C11',$valor1_2);
//$sheet->setCellValue('C15',$valor3_2);
//$sheet->setCellValue('C14',$valor3);
$sheet->setCellValue('C13',$valor4_2);
$sheet->setCellValue('C12',$valor4);
$sheet->setCellValue('C14',$valor5);
$sheet->setCellValue('C15',$valor5_2);
//$sheet->setCellValue('C28',$valor6);
//$sheet->setCellValue('C29',$valor6_2);
$sheet->setCellValue('C18',$valor7);
$sheet->setCellValue('C19',$valor7_2);
$sheet->setCellValue('C20',$valor8);
$sheet->setCellValue('C21',$valor8_2);
$sheet->setCellValue('C22',$valor9);
$sheet->setCellValue('C23',$valor9_2);
$sheet->setCellValue('C40',$valor10);
$sheet->setCellValue('C41',$valor10_2);
$sheet->setCellValue('C16',$valor11);
$sheet->setCellValue('C17',$valor11_2);
$sheet->setCellValue('C48',$valor12);
$sheet->setCellValue('C49',$valor12_2);
$sheet->setCellValue('C34',$valor14);
$sheet->setCellValue('C35',$valor14_2);
$sheet->setCellValue('C32',$valor15);
$sheet->setCellValue('C33',$valor15_2);
$sheet->setCellValue('C38',$valor16);
$sheet->setCellValue('C39',$valor16_2);

//LOTES HARINA
$sheet->setCellValue('D10',$lote1);
$sheet->setCellValue('D11',$lote1_2);
$sheet->setCellValue('D14',$lote3);
$sheet->setCellValue('D15',$lote3_2);
$sheet->setCellValue('D12',$lote4);
$sheet->setCellValue('D13',$lote4_2);
$sheet->setCellValue('D14',$lote5);
$sheet->setCellValue('D15',$lote5_2);
//$sheet->setCellValue('D28',$lote6);
//$sheet->setCellValue('D29',$lote6_2);
$sheet->setCellValue('D18',$lote7);
$sheet->setCellValue('D19',$lote7_2);
$sheet->setCellValue('D20',$lote8);
$sheet->setCellValue('D21',$lote8_2);
$sheet->setCellValue('D22',$lote9);
$sheet->setCellValue('D23',$lote9_2);
$sheet->setCellValue('D40',$lote10);
$sheet->setCellValue('D41',$lote10_2);
$sheet->setCellValue('D16',$lote11);
$sheet->setCellValue('D17',$lote11_2);
$sheet->setCellValue('D48',$lote12);
$sheet->setCellValue('D49',$lote12_2);
$sheet->setCellValue('D34',$lote14);
$sheet->setCellValue('D35',$lote14_2);
$sheet->setCellValue('D32',$lote15);
$sheet->setCellValue('D33',$lote15_2);
$sheet->setCellValue('D38',$lote16);
$sheet->setCellValue('D39',$lote16_2);

$sheet->setCellValue('I10',$codigomat1_1);
//$sheet->setCellValue('I12',$codigomat1_2);
//$sheet->setCellValue('I14',$codigomat1_3);
$sheet->setCellValue('I12',$codigomat1_4);
//$sheet->setCellValue('I18',$codigomat1_9);
$sheet->setCellValue('I32',$codigomat1_10);
$sheet->setCellValue('I24',$codigomat1_11);
$sheet->setCellValue('I28',$codigomat1_12);
$sheet->setCellValue('I26',$codigomat1_13);
//$sheet->setCellValue('I50',$codigomat1_14);
$sheet->setCellValue('I52',$codigomat1_15);
$sheet->setCellValue('I50',$codigomat1_16);
$sheet->setCellValue('I34',$codigomat1_17);
$sheet->setCellValue('I14',$codigomat1_20);
$sheet->setCellValue('I16',$codigomat1_21);
$sheet->setCellValue('I22',$codigomat1_22);
$sheet->setCellValue('I20',$codigomat1_23);
$sheet->setCellValue('I18',$codigomat1_24);
$sheet->setCellValue('I30',$codigomat1_25);
$sheet->setCellValue('I36',$codigomat1_26);
$sheet->setCellValue('I38',$codigomat1_27);
$sheet->setCellValue('I46',$codigomat1_28);
$sheet->setCellValue('I48',$codigomat1_29);
$sheet->setCellValue('H54',$nomgomat2_1);
$sheet->setCellValue('H56',$nomgomat2_2);
$sheet->setCellValue('H58',$nomgomat2_3);
$sheet->setCellValue('C63',$nomresp1_1);
$sheet->setCellValue('C64',$nomresp1_2);
$sheet->setCellValue('C65',$nomresp1_3);
$sheet->setCellValue('C66',$nomresp1_4);
$sheet->setCellValue('C67',$nomresp1_5);
//HARINAS EXTRAS
$sheet->setCellValue('B58',$nomhari_1);
//$sheet->setCellValue('',$nomhari_3);
//$sheet->setCellValue('',$nomhari_4);
//$sheet->setCellValue('',$nomhari_5);
$sheet->setCellValue('C58',$bultos_1);
//$sheet->setCellValue('C73',$bultos_3);
//$sheet->setCellValue('C74',$bultos_4);
//$sheet->setCellValue('C75',$bultos_5);
$sheet->setCellValue('D58',$lote_1);
//$sheet->setCellValue('D73',$lote_3);
//$sheet->setCellValue('D74',$lote_4);
//$sheet->setCellValue('D75',$lote_5);
$sheet->setCellValue('G58',($valor_1 * $bultos_1));
//$sheet->setCellValue('G48',($valor_2 * $bultos_2));
//$sheet->setCellValue('I73',($valor_3 * $bultos_3));
//$sheet->setCellValue('I74',($valor_4 * $bultos_4));
//$sheet->setCellValue('I75',($valor_5 * $bultos_5));


$sheet->setCellValue('H4',$numeroAsignado);
//FIRMAS
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
    $firmaturn1Img->setCoordinates('H62'); // Asignar celda
    $firmaturn1Img->setWorksheet($sheet);
}
//LOGO
$imagen_logoPath =  __DIR__ .'/../../archivos/formularios/logomas.png';
$imagen_logoImg = new Drawing();
$imagen_logoImg->setPath($imagen_logoPath);
$imagen_logoImg->setHeight(200); // Ajusta el tamaño según sea necesario
$imagen_logoImg->setCoordinates('A1');
$imagen_logoImg->setWorksheet($sheet);
//GUARDAR EXCEL

// Crear la carpeta si no existe
if (!is_dir($ubicacion)) {
    mkdir($ubicacion, 0777, true); 
}
$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
$writer->save($rutaArchivo);

unlink($tempExcelPath);
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
