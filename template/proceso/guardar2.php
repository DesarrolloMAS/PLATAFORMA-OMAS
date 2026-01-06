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
if($sede === 'ZS'){
    $carpeta = __DIR__ . '/../../archivos/generados/proceso_molienda/';
}else{
    $carpeta = __DIR__ . '/../../archivos/generados/proceso_molienda/';
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['archivo']) && isset($_POST['data'])) {
    $archivo = $_POST['archivo'];
    $rutaArchivo = $carpeta . DIRECTORY_SEPARATOR . $archivo;

    if (!file_exists($rutaArchivo)) {
        die("Error: El archivo no existe en la ruta: " . $rutaArchivo);
    }

        try {
            $spreadsheet = IOFactory::load($rutaArchivo);
            $hoja = $spreadsheet->getActiveSheet();
            $celdasConFormula = [];
            for ($fila = 11; $fila <= 75; $fila++) {
                $celdasConFormula[] = "I$fila";
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
        $firmaturn1Img->setCoordinates('J85'); // Asignar celda
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
        $firmaturn2Img->setCoordinates('J86'); // Asignar celda
        $firmaturn2Img->setWorksheet($hoja);
    }
    $firmaturn3 = $_POST['firma_turn3'];
    if ($firmaturn3) {
        $firmaturn3Path = __DIR__ . '/../../archivos/firmas_imagenes/firma_turn3.png';
        $firmaturn3Data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $firmaturn3));

        if (file_put_contents($firmaturn3Path, $firmaturn3Data) === false) {
        die("Error al guardar la firma del Turno.");
        }

        // Insertar la firma en el Excel
        $firmaturn3Img = new Drawing();
        $firmaturn3Img->setPath($firmaturn3Path);
        $firmaturn3Img->setHeight(100);
        $firmaturn3Img->setCoordinates('J87'); // Asignar celda
        $firmaturn3Img->setWorksheet($hoja);
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
        $firmaturn4Img->setCoordinates('J90'); // Asignar celda
        $firmaturn4Img->setWorksheet($hoja);
    }


        // Guardar el archivo con los cambios
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($rutaArchivo);

        header("Location: revisar_proceso.php?archivo=" . urlencode($archivo));
    } catch (Exception $e) {
        die("Error al guardar los cambios: " . $e->getMessage());
    }
}
