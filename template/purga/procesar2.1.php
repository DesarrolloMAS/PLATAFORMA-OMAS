

<?php
require __DIR__ . '/../../vendor/autoload.php'; 
require '../sesion.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
$sede = $_SESSION['sede'];
if ($sede === 'ZS'){
    $carpeta = rtrim(__DIR__, '/') . '/../../archivos/generados/Purga De proceso/';
}else
    $carpeta = rtrim(__DIR__, '/') . '/../../archivos/generados/Purga De proceso/';
if (isset($_GET['archivo'])) {
    $archivo = $_GET['archivo'];
    $rutaArchivo = $carpeta . DIRECTORY_SEPARATOR . $archivo;

    if (!file_exists($rutaArchivo)) {
        die("Error: El archivo no existe o la ruta es incorrecta: $rutaArchivo");
    }

    try {
        $spreadsheet = IOFactory::load($rutaArchivo);
        $hoja = $spreadsheet->getActiveSheet();
        $filas = $hoja->toArray();

        // Obtener estilos de celdas (ancho de columnas y alto de filas)
        $colWidths = [];
        foreach ($hoja->getColumnDimensions() as $columna => $dimension) {
            $colWidths[$columna] = $dimension->getWidth();
        }

        $filasHeight = [];
        foreach ($hoja->getRowDimensions() as $fila => $dimension) {
            $filasHeight[$fila] = $dimension->getRowHeight();
        }

        // Carpeta para imágenes temporales
        $rutaImagenes = __DIR__ . "/imagenes_temporales/";
        if (!file_exists($rutaImagenes)) {
            mkdir($rutaImagenes, 0777, true);
        }

        // Obtener imágenes del archivo Excel
        $imagenes = [];
        foreach ($hoja->getDrawingCollection() as $drawing) {
            $imagenNombre = $drawing->getName() . ".png"; 
            $imagenRuta = $rutaImagenes . $imagenNombre;

            if ($drawing instanceof \PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing) {
                switch ($drawing->getMimeType()) {
                    case \PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing::MIMETYPE_PNG:
                        imagepng($drawing->getImageResource(), $imagenRuta);
                        break;
                    case \PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing::MIMETYPE_JPEG:
                        imagejpeg($drawing->getImageResource(), $imagenRuta);
                        break;
                    case \PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing::MIMETYPE_GIF:
                        imagegif($drawing->getImageResource(), $imagenRuta);
                        break;
                }
            } else {
                copy($drawing->getPath(), $imagenRuta);
            }

            $imagenes[$drawing->getCoordinates()] = "imagenes_temporales/" . $imagenNombre;
        }

    } catch (Exception $e) {
        die("Error al procesar el archivo: " . $e->getMessage());
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Excel</title>
    <!-- Agrega esto en tu <head> o reemplaza tu bloque <style> -->
<style>
    .excel-table-wrapper {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        background: #fff;
        border-radius: 6px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        margin-bottom: 1em;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        min-width: 900px; /* Ajusta según tus columnas */
        background: #fff;
    }
    th, td {
        border: 1px solid #bbb;
        padding: 4px 6px;
        text-align: center;
        font-size: 13px;
        background: #fff;
        word-break: break-word;
    }
    th {
        background: #f4f4f4;
        position: sticky;
        top: 0;
        z-index: 2;
    }
    input[type="text"] {
        width: 100%;
        min-width: 60px;
        box-sizing: border-box;
        font-size: 13px;
        padding: 2px 4px;
    }
    img {
        max-width: 40px;
        height: auto;
        display: block;
        margin: 0 auto;
    }
    @media (max-width: 800px) {
        table { min-width: 600px; font-size: 12px; }
        th, td { padding: 2px 3px; font-size: 12px; }
        input[type="text"] { font-size: 12px; min-width: 40px; }
    }
    @media (max-width: 500px) {
        table { min-width: 400px; font-size: 11px; }
        th, td { padding: 1px 2px; font-size: 11px; }
        input[type="text"] { font-size: 11px; min-width: 30px; }
    }
</style>
</head>
<body>

<h2>📄 Editar Datos del Excel</h2>

<div class="excel-table-wrapper">
<form action="guardar2.php" method="post">
    <input type="hidden" name="archivo" value="<?php echo htmlspecialchars($archivo, ENT_QUOTES, 'UTF-8'); ?>">
    <input type="hidden" name="carpeta" value="<?php echo htmlspecialchars($carpeta, ENT_QUOTES, 'UTF-8'); ?>">
    <table>
        <?php foreach ($filas as $filaIndex => $fila) : ?>
            <tr style="height: <?php echo isset($filasHeight[$filaIndex+1]) ? $filasHeight[$filaIndex+1].'px' : 'auto'; ?>">
                <?php foreach ($fila as $colIndex => $celda) :
                    $letraCol = Coordinate::stringFromColumnIndex($colIndex + 1);
                    $celdaRef = $letraCol . ($filaIndex + 1);
                    
                    // Obtener estilos de celda
                    $style = $hoja->getStyle($celdaRef);
                    $colorFondo = $style->getFill()->getStartColor()->getARGB();
                    $alineacion = $style->getAlignment()->getHorizontal();
                    $colorFondoCSS = ($colorFondo !== '00000000') ? '#' . substr($colorFondo, 2) : 'transparent';

                    // Verificar si hay imagen en la celda
                    $imagenHTML = isset($imagenes[$celdaRef]) ? "<img src='{$imagenes[$celdaRef]}' width='50' height='50'>" : "";
                ?>
                    <td style="width: <?php echo isset($colWidths[$letraCol]) ? ($colWidths[$letraCol] * 7) . 'px' : 'auto'; ?>;
                               background-color: <?php echo $colorFondoCSS; ?>;
                               text-align: <?php echo $alineacion; ?>;">
                        <?php echo $imagenHTML; ?>
                        <input type="text" name="data[<?php echo $filaIndex; ?>][<?php echo $colIndex; ?>]" 
                               value="<?php echo htmlspecialchars($celda ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
    </table>
    <button type="submit">✅ Guardar Cambios</button>
</form>
</div>

</body>
</html>
<script>
    //FIRMAS

    // Inicializar el canvas de forma dinámica
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


    // Inicializar ambos canvas con sus botones y campos ocultos
    inicializarCanvas('canvasfirma1', 'limpiarFirma1', 'guardarFirma1', 'firma_turn1');
    inicializarCanvas('canvasfirma2', 'limpiarFirma2', 'guardarFirma2', 'firma_turn2');
    inicializarCanvas('canvasfirma3', 'limpiarFirma3', 'guardarFirma3', 'firma_turn3');
    inicializarCanvas('canvasfirma4', 'limpiarFirma4', 'guardarFirma4', 'firma_turn4');
</script>
