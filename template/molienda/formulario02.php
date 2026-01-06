<?php
require __DIR__ . '/../../vendor/autoload.php';
require '../sesion.php'; // Incluye el archivo de autenticación
require '../conection.php'; // Conexión a la base de datos
$sede = $_SESSION['sede'];
verificarAutenticacion(); // Verifica que el usuario esté autenticado
use PhpOffice\PhpSpreadsheet\IOFactory;
use Mpdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
$date = date('Y-m-d');
if (!isset($_SESSION['id_usuario']) || empty($_SESSION['id_usuario'])) {
    die("Error: El usuario no está definido en la sesión.");
}

$queryCheck = "SELECT id_proceso FROM control_molienda ORDER BY id_proceso DESC LIMIT 1";
$stmtCheck = $pdoControl->prepare($queryCheck);
$stmtCheck->execute();
$result = $stmtCheck->fetch(PDO::FETCH_ASSOC);

if (!$result) {
    // Si no hay ningún registro previo, crear uno nuevo
    $queryInsert = "INSERT INTO control_molienda (fecha, archivogen, zona, turn1, turn2, turn3, creador) VALUES (NOW(), 0, :zona, 0, 0, 0, :creador)";
    $stmtInsert = $pdoControl->prepare($queryInsert);
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
$queryUpdate = "UPDATE control_molienda SET $turnoActual = 1 WHERE id_proceso = ?";
$stmtUpdate = $pdoControl->prepare($queryUpdate);
$stmtUpdate->execute([$idProceso]);


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
//PROCESAMIENTO DE DATOS
//echo "<pre>";
//print_r($_POST);
//echo "</pre>";
//exit;
if ($sede === "ZC"){
    $ubicacion = __DIR__ . '/../../archivos/generados/excelC_M'; 
    $numero = 1423;
}else{
    $ubicacion =  __DIR__ . '/../../archivos/generados/excelC_MZS';
    $numero = 0062;
}
$prefijo = "Control_Molienda";
$extension = "Xlsx";

function guardarConNombreConsecutivo($ubicacion, $prefijo, $numero, $extension) { 
    do {
        $nombreArchivo = $prefijo . "_" . $numero . "_" . $date . "." . $extension;
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
$queryUpdateArchivo = "UPDATE control_molienda SET archivo_ruta = ? WHERE id_proceso = ?";
$stmtUpdateArchivo = $pdoControl->prepare($queryUpdateArchivo);
$stmtUpdateArchivo->execute([$rutaArchivo, $idProceso]);

$responsable = $_SESSION['nombre'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];
    $residuoSelect1 = $_POST['residuoSelect1'] ??'No seleccionado' ;
    $responsableint = $_POST['responsableint'] ??'';
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
    $codigores1_9 = $_POST['codigo-Hilo']?? '';
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

    $codigomat1_1 = $_POST['codigo-Empaque_ExtraPan_x50']?? '0';
    $codigomat1_2 = $_POST['codigo-Empaque_ExtraPan_x25']?? '0';
    $codigomat1_3 = $_POST['codigo-Empaque_ExtraPan_x10']?? '0';
    $codigomat1_4 = $_POST['codigo-Empaque_Galeras_Rojo_x50']?? '0';
    $codigomat1_5 = $_POST['codigo-Empaque_Galeras_Verde_x50']?? '0';
    $codigomat1_6 = $_POST['codigo-Empaque_Galeras_Cafe_x50']?? '0';
    $codigomat1_7 = $_POST['codigo-Empaque_Galeras_Azul_x50']?? '0';
    $codigomat1_8 = $_POST['codigo-Empaque_Galeras_Naranja_x50']?? '0';
    $codigomat1_9 = $_POST['codigo-Empaque_Galeras_KRAFT_x25']?? '0';
    $codigomat1_10 = $_POST['codigo-Empaque_Multi_Beige_x25']?? '0';
    $codigomat1_11 = $_POST['codigo-Empaque_Galeras_MOG_x40']?? '0';
    $codigomat1_12 = $_POST['codigo-Empaque_Galeras_Sal_x25']?? '0';
    $codigomat1_13 = $_POST['codigo-Empaque_Galeras_Seg_x50']?? '0';
    $codigomat1_20 = $_POST['codigo-Empaque_Fuerte_Exp']?? '0';
    $codigomat1_14 = $_POST['codigo-Vitaminamat']?? '0';
    $codigomat1_15 = $_POST['codigo-Mejorante_Extrapan']?? '0';
    $codigomat1_16 = $_POST['codigo-Mejorante_Artesanal']?? '0';
    $codigomat1_17 = $_POST['codigo-Hilo_Blanco']?? '0';
    $codigomat1_18 = $_POST['codigo-Hilo_Verde']?? '0';
    $codigomat1_19 = $_POST['codigo-Hilo_Naranja']?? '0';
    $codigomat1_25 = $_POST['codigo-Empaque_Blanco_x50']?? '0';

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
    $lote16_2 = $_POST['lotefuertexp1']?? '';
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
$baseExcelPath = __DIR__ . '/../../archivos/formularios/formulario2.xlsx';
echo realpath($baseExcelPath);
if (!file_exists($baseExcelPath)) {
    die("Error: No se encontró la plantilla base.");
}
$spreadsheet = IOFactory::load($baseExcelPath);
    $sheet = $spreadsheet->getActiveSheet();
//RELLENAR PLANTILLA//
$sheet->setCellValue('J9',$responsable);
$sheet->setCellValue('J64',$responsableint);
$sheet->setCellValue('I8', $fecha);
$sheet->setCellValue('K5', $hora);
$sheet->setCellValue('D45', $codigores1);
$sheet->setCellValue('D47', $codigores1_2);
$sheet->setCellValue('D49', $codigores1_3);
$sheet->setCellValue('D51', $codigores1_4);
$sheet->setCellValue('D53', $codigores1_5);
$sheet->setCellValue('D55', $codigores1_6);
$sheet->setCellValue('D57', $codigores1_7);
$sheet->setCellValue('D59', $codigores1_8);
$sheet->setCellValue('D61', $codigores1_9);
$sheet->setCellValue('D77', $codigores1_10);
$sheet->setCellValue('K77', $codigomat2_1);
$sheet->setCellValue('K79', $codigomat2_2);
$sheet->setCellValue('K81', $codigomat2_3);
$sheet->setCellValue('C45', $pesores1);
$sheet->setCellValue('C47', $pesores1_2);
$sheet->setCellValue('C49', $pesores1_3);
$sheet->setCellValue('C51', $pesores1_4);
$sheet->setCellValue('C53', $pesores1_5);
$sheet->setCellValue('C55', $pesores1_6);
$sheet->setCellValue('C57', $pesores1_7);
$sheet->setCellValue('C59', $pesores1_8);
$sheet->setCellValue('C61', $pesores1_9);
$sheet->setCellValue('C77', $pesores1_10);
//SUBPRODUCTO EXTRA
//LOTES
$sheet->setCellValue('D46', $codigoresext1);
$sheet->setCellValue('D48', $codigoresext1_2);
$sheet->setCellValue('D50', $codigoresext1_3);
$sheet->setCellValue('D52', $codigoresext1_4);
$sheet->setCellValue('D54', $codigoresext1_5);
$sheet->setCellValue('D56', $codigoresext1_6);
$sheet->setCellValue('D58', $codigoresext1_7);
$sheet->setCellValue('D60', $codigoresext1_8);
$sheet->setCellValue('D62', $codigoresext1_9);
$sheet->setCellValue('D78', $codigoresext1_10);

$sheet->setCellValue('C46', $pesoresext1);
$sheet->setCellValue('C48', $pesoresext1_2);
$sheet->setCellValue('C50', $pesoresext1_3);
$sheet->setCellValue('C52', $pesoresext1_4);
$sheet->setCellValue('C54', $pesoresext1_5);
$sheet->setCellValue('C56', $pesoresext1_6);
$sheet->setCellValue('C58', $pesoresext1_7);
$sheet->setCellValue('C60', $pesoresext1_8);
$sheet->setCellValue('C62', $pesoresext1_9);
$sheet->setCellValue('C78', $pesoresext1_10);

$sheet->setCellValue('C11',$valor1);
$sheet->setCellValue('C12',$valor1_2);
$sheet->setCellValue('C13',$valor2);
$sheet->setCellValue('C14',$valor2_2);
$sheet->setCellValue('C16',$valor3_2);
$sheet->setCellValue('C15',$valor3);
$sheet->setCellValue('C18',$valor4_2);
$sheet->setCellValue('C17',$valor4);
$sheet->setCellValue('C19',$valor5);
$sheet->setCellValue('C20',$valor5_2);
$sheet->setCellValue('C21',$valor6);
$sheet->setCellValue('C22',$valor6_2);
$sheet->setCellValue('C23',$valor7);
$sheet->setCellValue('C24',$valor7_2);
$sheet->setCellValue('C25',$valor8);
$sheet->setCellValue('C26',$valor8_2);
$sheet->setCellValue('C27',$valor9);
$sheet->setCellValue('C28',$valor9_2);
$sheet->setCellValue('C29',$valor10);
$sheet->setCellValue('C30',$valor10_2);
$sheet->setCellValue('C31',$valor11);
$sheet->setCellValue('C32',$valor11_2);
$sheet->setCellValue('C33',$valor12);
$sheet->setCellValue('C34',$valor12_2);
$sheet->setCellValue('C35',$valor13);
$sheet->setCellValue('C36',$valor13_2);
$sheet->setCellValue('C37',$valor14);
$sheet->setCellValue('C38',$valor14_2);
$sheet->setCellValue('C39',$valor15);
$sheet->setCellValue('C40',$valor15_2);
$sheet->setCellValue('C41',$valor16);
$sheet->setCellValue('C42',$valor16_2);
$sheet->setCellValue('C59',$valor17);
$sheet->setCellValue('C60',$valor17_2);
$sheet->setCellValue('C61',$valor18);
$sheet->setCellValue('C62',$valor18_2);
$sheet->setCellValue('C63',$valor19);
$sheet->setCellValue('C64',$valor19_2);
$sheet->setCellValue('C65',$valor20);
$sheet->setCellValue('C66',$valor20_2);
$sheet->setCellValue('C67',$valor21);
$sheet->setCellValue('C68',$valor21_2);
$sheet->setCellValue('C71',$valor22);
$sheet->setCellValue('C72',$valor22_2);
$sheet->setCellValue('C73',$valor23);
$sheet->setCellValue('C74',$valor23_2);
$sheet->setCellValue('C75',$valor24);
$sheet->setCellValue('C76',$valor24_2);
//LOTES HARINA
$sheet->setCellValue('D11',$lote1);
$sheet->setCellValue('D12',$lote1_2);
$sheet->setCellValue('D13',$lote2);
$sheet->setCellValue('D14',$lote2_2);
$sheet->setCellValue('D15',$lote3);
$sheet->setCellValue('D16',$lote3_2);
$sheet->setCellValue('D17',$lote4);
$sheet->setCellValue('D18',$lote4_2);
$sheet->setCellValue('D19',$lote5);
$sheet->setCellValue('D20',$lote5_2);
$sheet->setCellValue('D21',$lote6);
$sheet->setCellValue('D22',$lote6_2);
$sheet->setCellValue('D23',$lote7);
$sheet->setCellValue('D24',$lote7_2);
$sheet->setCellValue('D25',$lote8);
$sheet->setCellValue('D26',$lote8_2);
$sheet->setCellValue('D27',$lote9);
$sheet->setCellValue('D28',$lote9_2);
$sheet->setCellValue('D29',$lote10);
$sheet->setCellValue('D30',$lote10_2);
$sheet->setCellValue('D31',$lote11);
$sheet->setCellValue('D32',$lote11_2);
$sheet->setCellValue('D33',$lote12);
$sheet->setCellValue('D34',$lote12_2);
$sheet->setCellValue('D35',$lote13);
$sheet->setCellValue('D36',$lote13_2);
$sheet->setCellValue('D37',$lote14);
$sheet->setCellValue('D38',$lote14_2);
$sheet->setCellValue('D39',$lote15);
$sheet->setCellValue('D40',$lote15_2);
$sheet->setCellValue('D41',$lote16);
$sheet->setCellValue('D42',$lote16_2);
$sheet->setCellValue('D59',$lote17);
$sheet->setCellValue('D60',$lote17_2);
$sheet->setCellValue('D61',$lote18);
$sheet->setCellValue('D62',$lote18_2);
$sheet->setCellValue('D63',$lote19);
$sheet->setCellValue('D64',$lote19_2);
$sheet->setCellValue('D65',$lote20);
$sheet->setCellValue('D66',$lote20_2);
$sheet->setCellValue('D67',$lote21);
$sheet->setCellValue('D68',$lote21_2);
$sheet->setCellValue('D71',$lote22);
$sheet->setCellValue('D72',$lote22_2);
$sheet->setCellValue('D73',$lote23);
$sheet->setCellValue('D74',$lote23_2);
$sheet->setCellValue('D75',$lote24);
$sheet->setCellValue('D76',$lote24_2);

$sheet->setCellValue('K11',$codigomat1_1);
$sheet->setCellValue('K13',$codigomat1_2);
$sheet->setCellValue('K15',$codigomat1_3);
$sheet->setCellValue('K17',$codigomat1_4);
$sheet->setCellValue('K19',$codigomat1_5);
$sheet->setCellValue('K21',$codigomat1_6);
$sheet->setCellValue('K23',$codigomat1_7);
$sheet->setCellValue('K25',$codigomat1_8);
$sheet->setCellValue('K27',$codigomat1_9);
$sheet->setCellValue('K29',$codigomat1_10);
$sheet->setCellValue('K31',$codigomat1_11);
$sheet->setCellValue('K33',$codigomat1_12);
$sheet->setCellValue('K35',$codigomat1_13);
$sheet->setCellValue('K37',$codigomat1_14);
$sheet->setCellValue('K39',$codigomat1_15);
$sheet->setCellValue('K41',$codigomat1_16);
$sheet->setCellValue('K43',$codigomat1_17);
$sheet->setCellValue('K45',$codigomat1_18);
$sheet->setCellValue('K47',$codigomat1_19);
$sheet->setCellValue('K49',$codigomat1_20);
$sheet->setCellValue('K55',$codigomat1_21);
$sheet->setCellValue('K57',$codigomat1_22);
$sheet->setCellValue('K59',$codigomat1_23);
$sheet->setCellValue('K61',$codigomat1_24);
$sheet->setCellValue('K63',$codigomat1_25);
$sheet->setCellValue('K65',$codigomat1_26);
$sheet->setCellValue('K67',$codigomat1_27);
$sheet->setCellValue('K69',$codigomat1_28);
$sheet->setCellValue('K71',$codigomat1_29);
$sheet->setCellValue('K75',$codigomat1_30);
$sheet->setCellValue('K73',$codigomat1_31);
$sheet->setCellValue('J77',$nomgomat2_1);
$sheet->setCellValue('J79',$nomgomat2_2);
$sheet->setCellValue('J81',$nomgomat2_3);
$sheet->setCellValue('C62',$nomresp1_1);
$sheet->setCellValue('C63',$nomresp1_2);
$sheet->setCellValue('C64',$nomresp1_3);
$sheet->setCellValue('C66',$nomresp1_4);
$sheet->setCellValue('C66',$nomresp1_5);
//HARINAS EXTRAS
$sheet->setCellValue('B79',$nomhari_1);
$sheet->setCellValue('B81',$nomhari_2);
//$sheet->setCellValue('',$nomhari_3);
//$sheet->setCellValue('',$nomhari_4);
//$sheet->setCellValue('',$nomhari_5);
$sheet->setCellValue('C79',$bultos_1);
$sheet->setCellValue('C81',$bultos_2);
//$sheet->setCellValue('C73',$bultos_3);
//$sheet->setCellValue('C74',$bultos_4);
//$sheet->setCellValue('C75',$bultos_5);
$sheet->setCellValue('D79',$lote_1);
$sheet->setCellValue('D81',$lote_2);
//$sheet->setCellValue('D73',$lote_3);
//$sheet->setCellValue('D74',$lote_4);
//$sheet->setCellValue('D75',$lote_5);
$sheet->setCellValue('I79',($valor_1 * $bultos_1));
$sheet->setCellValue('I81',($valor_2 * $bultos_2));
//$sheet->setCellValue('I73',($valor_3 * $bultos_3));
//$sheet->setCellValue('I74',($valor_4 * $bultos_4));
//$sheet->setCellValue('I75',($valor_5 * $bultos_5));


$sheet->setCellValue('J4',$numeroAsignado);
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
    $firmaturn1Img->setCoordinates('J62'); // Asignar celda
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