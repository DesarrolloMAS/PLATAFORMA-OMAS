<?php
session_start();
require __DIR__ . '/../../vendor/autoload.php';
require '../sesion.php';
require '../conection.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Mpdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

$sede = $_SESSION['sede'];
    $carpeta = '/var/www/fmt/archivos/generados/control_familiar/';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['archivo']) && isset($_POST['data'])) {
    $archivo = $_POST['archivo'];
    $rutaArchivo = $carpeta . '/' . $archivo;

    if (!file_exists($rutaArchivo)) {
        die("Error: El archivo no existe en la ruta: " . $rutaArchivo);
    }

        try {
            $spreadsheet = IOFactory::load($rutaArchivo);
            $hoja = $spreadsheet->getActiveSheet();
            $celdasConFormula = ['O01','O02','O03','O04','O05','O06','O07','O08','O09','O10','O11','O12','O13','O14','O15','O16','O17','O18','O19','O20','O21','O22','O23','O24','O25','O26','O27','O28'];
            for ($fila = 10; $fila <= 60; $fila++) {
                $celdasConFormula[] = "G$fila";
            }
            $formulasGuardadas = [];
            foreach ($celdasConFormula as $celdaRef) {
                if ($hoja->getCell($celdaRef)->isFormula()) {
                    $formulasGuardadas[$celdaRef] = $hoja->getCell($celdaRef)->getValue();
                }
            }
            // 🔥 Recorrer todas las celdas enviadas desde el formulario
            foreach ($_POST['data'] as $filaIndex => $fila) {
                foreach ($fila as $colIndex => $valorCelda) {
                    $letraCol = Coordinate::stringFromColumnIndex($colIndex + 1);
                    $celdaRef = $letraCol . ($filaIndex + 1);
    
                    // Solo modificar si la celda **NO** está en la lista de fórmulas a conservar
                    if (!in_array($celdaRef, $celdasConFormula)) {
                        $hoja->setCellValue($celdaRef, $valorCelda);
                    }
                }
            }
        $firmaturn1 = $_POST['firma_turn1'];
    if ($firmaturn1) {
        $firmaturn1Path = __DIR__ . '/../../archivos/firmas_imagenes/firma_turn1.png';
        $firmaturn1Data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $firmaturn1));

        if (file_put_contents($firmaturn1Path, $firmaturn1Data) === false) {
        die("Error al guardar la firma del Turno.");
        }

        // Insertar la firma en el Excel
        $firmaturn1Img = new Drawing();
        $firmaturn1Img->setPath($firmaturn1Path);
        $firmaturn1Img->setHeight(100);
        $firmaturn1Img->setCoordinates('H62'); // Asignar celda
        $firmaturn1Img->setWorksheet($hoja);
    }
    $firmaturn2 = $_POST['firma_turn2'];
    if ($firmaturn2) {
        $firmaturn2Path = __DIR__ . '/../../archivos/firmas_imagenes/firma_turn2.png';
        $firmaturn2Data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $firmaturn2));

        if (file_put_contents($firmaturn2Path, $firmaturn2Data) === false) {
        die("Error al guardar la firma del Turno.");
        }

        // Insertar la firma en el Excel
        $firmaturn2Img = new Drawing();
        $firmaturn2Img->setPath($firmaturn2Path);
        $firmaturn2Img->setHeight(100);
        $firmaturn2Img->setCoordinates('H63'); // Asignar celda
        $firmaturn2Img->setWorksheet($hoja);
    }
    $firmaturn4 = $_POST['firma_turn4'];
    if ($firmaturn4) {
        $firmaturn4Path = __DIR__ . '/../../archivos/firmas_imagenes/firma_turn4.png';
        $firmaturn4Data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $firmaturn4));

        if (file_put_contents($firmaturn4Path, $firmaturn4Data) === false) {
        die("Error al guardar la firma del Turno.");
        }

        // Insertar la firma en el Excel
        $firmaturn4Img = new Drawing();
        $firmaturn4Img->setPath($firmaturn4Path);
        $firmaturn4Img->setHeight(100);
        $firmaturn4Img->setCoordinates('H66'); // Asignar celda
        $firmaturn4Img->setWorksheet($hoja);
    }


        // Guardar el archivo con los cambios
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($rutaArchivo);

        header("Location: observar_cantidad_familiar.php?archivo=" . urlencode($archivo));
    } catch (Exception $e) {
        die("Error al guardar los cambios: " . $e->getMessage());
    }
}
