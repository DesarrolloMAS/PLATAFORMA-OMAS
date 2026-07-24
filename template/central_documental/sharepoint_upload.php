<?php
/**
 * sharepoint_upload.php
 * ---------------------
 * Endpoint que recibe la lista de archivos seleccionados desde la galería
 * y los sube al SharePoint mediante el uploader_selective.js
 */
ini_set('max_execution_time', 300); // Dar 5 minutos de tiempo de ejecución
header('Content-Type: application/json; charset=utf-8');

require '../sesion.php';

function sp_debug($msg) {
    file_put_contents(dirname(__DIR__) . '/sp_debug.log', date('Y-m-d H:i:s') . ' - ' . $msg . "\n", FILE_APPEND);
}

// Si no hay sesión, devolver JSON en lugar de redirigir (para evitar el error de <!DOCTYPE)
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['success' => false, 'error' => 'Sesión expirada. Por favor, refresca la página e inicia sesión nuevamente.']);
    exit;
}


// Solo aceptar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || empty($input['archivos'])) {
    echo json_encode(['success' => false, 'error' => 'No se recibieron archivos para subir.']);
    exit;
}

$archivos = $input['archivos'];
$modulo = $input['modulo'] ?? '';
$host = $_SERVER['HTTP_HOST'];
$sessionId = session_id();
$baseDir = '/var/www/fmt/';

// Directorio temporal para PDFs generados al vuelo
$tempPdfDir = $baseDir . 'archivos/generados/TEMP_PDF/';
if (!is_dir($tempPdfDir)) {
    mkdir($tempPdfDir, 0777, true);
}

// ═══════════════════════════════════════════════════════════════════
// ETAPA A — Normalizar cada archivo y armar el manifiesto de jobs HTML
// (Puppeteer). No se ejecuta nada todavía: solo se recopila qué hay
// que renderizar, para lanzar el navegador UNA sola vez por todo el lote
// en vez de una vez por archivo.
// ═══════════════════════════════════════════════════════════════════
$fileContext = []; // índice => contexto resuelto de cada archivo válido
$pdfJobs = [];      // subconjunto que necesita Puppeteer: {id, url, pdfPath, landscape}

foreach ($archivos as $i => $item) {
    // Soporte para formato antiguo (string) y nuevo (objeto con path y date)
    $archivoRaw = is_array($item) ? $item['path'] : $item;
    $date = is_array($item) ? ($item['date'] ?? '') : '';

    // La galería puede mostrar documentos de otra sede (o de ambas a la vez);
    // cada archivo trae su propia sede real, que prevalece sobre la de sesión
    // para no generar PDFs/URLs con datos de la sede equivocada.
    $itemSedeRaw = is_array($item) ? ($item['sede'] ?? '') : '';
    $sede = $itemSedeRaw ? preg_replace('/[^A-Za-z0-9_-]/', '', $itemSedeRaw) : ($_SESSION['sede'] ?? 'ZC');

    // Normalizar ruta
    $ruta = $archivoRaw;
    if (strpos($ruta, '../') === 0) {
        $ruta = realpath(__DIR__ . '/' . $ruta);
    } elseif (strpos($ruta, '/archivos/generados/') === 0) {
        $ruta = $baseDir . ltrim($ruta, '/');
    } elseif (strpos($ruta, 'archivos/generados/') === 0) {
        $ruta = $baseDir . $ruta;
    }

    if (!($ruta && file_exists($ruta) && strpos($ruta, $baseDir . 'archivos/generados/') === 0)) {
        continue;
    }

    $filename = basename($ruta);
    $filenameNoExt = pathinfo($filename, PATHINFO_FILENAME);

    $urlToRender = null;
    $landscape = false;
    $pdfPath = false;
    $jsonAtomicoPath = null;

    // 1. Mapeo para Visores HTML (Molienda, HSEQ, etc.) -> Usan Puppeteer
    switch ($modulo) {
        case 'molienda_v2':
            if ($date) {
                $urlToRender = "http://{$host}/template/molienda_v2/plantilla_diaria.php?fecha={$date}&sede={$sede}";
                $landscape = true;
                $pdfPath = dirname($ruta) . "/Molienda_{$sede}_{$date}.pdf";
                // JSON atómico: todos los turnos del día extraídos del mensual.
                // Se guarda en dirname($ruta) (molienda/SEDE/) para que uploader_selective.js
                // lo mapee correctamente a "Molienda V2" en SharePoint.
                $dataMensual  = json_decode(file_get_contents($ruta), true) ?? [];
                $registrosDia = array_values(array_filter($dataMensual, fn($r) => ($r['fecha'] ?? '') === $date));
                $jsonAtomicoPath = dirname($ruta) . "/Molienda_{$sede}_{$date}.json";
                file_put_contents($jsonAtomicoPath, json_encode($registrosDia, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                sp_debug("JSON atómico creado: $jsonAtomicoPath (" . count($registrosDia) . " turnos)");
            }
            break;
        case 'hseq':
            $urlToRender = "http://{$host}/template/HSEQ/formulario_investigacion_impr.php?id={$filenameNoExt}";
            $landscape = false;
            $pdfPath = dirname($ruta) . "/HSEQ_{$filenameNoExt}.pdf";
            break;
        case 'pnc':
            $urlToRender = "http://{$host}/template/Calidad/PNC/visor_PNC.php?file={$filename}";
            $landscape = false;
            $pdfPath = dirname($ruta) . "/PNC_{$filenameNoExt}.pdf";
            break;
        case 'empaque_v2':
            $urlToRender = "http://{$host}/template/empaque_v2/visor_empaque_v2.php?file={$filename}";
            $landscape = true;
            $pdfPath = dirname($ruta) . "/Empaque_{$filenameNoExt}.pdf";
            break;
        case 'termohigrometros':
            $urlToRender = "http://{$host}/template/termohigrometros/visor_termo.php?file={$filename}&zona={$sede}";
            $landscape = true;
            $pdfPath = dirname($ruta) . "/Termohigrometros_{$filenameNoExt}.pdf";
            break;
        case 'gestion_subproductos':
            $urlToRender = "http://{$host}/template/gestion_subproductos/visor_gestion_subproductos.php?file={$filename}";
            $landscape = false;
            $pdfPath = dirname($ruta) . "/Subproductos_{$filenameNoExt}.pdf";
            break;
        case 'cantidad_bulto':
            $urlToRender = "http://{$host}/template/cantidad%20en%20bulto_v2/visor_cantidad_bulto.php?file=" . rawurlencode($filename) . "&sede={$sede}";
            $landscape = true;
            $pdfPath = dirname($ruta) . "/Bulto_{$filenameNoExt}.pdf";
            break;
        case 'maquinas_v2':
            // $ruta apunta al JSON acumulado de la máquina (histórico completo);
            // hay que aislar el registro puntual seleccionado para renderizar
            // el visor y para el JSON atómico bilateral.
            $tipoM   = is_array($item) ? ($item['tipo_maquina'] ?? '') : '';
            $grupoM  = is_array($item) ? ($item['grupo_maquina'] ?? '') : '';
            $codigoM = is_array($item) ? ($item['codigo_maquina'] ?? '') : '';
            $idReg   = is_array($item) ? ($item['id_registro'] ?? '') : '';

            if ($tipoM && $grupoM && $codigoM && $idReg) {
                $registrosMaquina = json_decode(file_get_contents($ruta), true) ?: [];
                $registroSel = null;
                foreach ($registrosMaquina as $r) {
                    if (($r['id_registro'] ?? '') === $idReg) { $registroSel = $r; break; }
                }

                if ($registroSel) {
                    $fechaReg = substr($registroSel['timestamp'] ?? '', 0, 10) ?: date('Y-m-d');
                    $idCorto  = substr(preg_replace('/[^A-Za-z0-9]/', '', $idReg), -8);

                    $urlToRender = "http://{$host}/template/maquinas_v2/visor_verificacion.php?tipo=" . rawurlencode($tipoM)
                        . "&maquina=" . rawurlencode($grupoM) . "&codigo=" . rawurlencode($codigoM) . "&id=" . rawurlencode($idReg);
                    $landscape = false;
                    $pdfPath = dirname($ruta) . "/MaqV2_{$codigoM}_{$fechaReg}_{$idCorto}.pdf";

                    $jsonAtomicoPath = dirname($ruta) . "/MaqV2_{$codigoM}_{$fechaReg}_{$idCorto}.json";
                    file_put_contents($jsonAtomicoPath, json_encode($registroSel, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                    sp_debug("JSON atómico de verificación creado: $jsonAtomicoPath");
                }
            }
            break;
        case 'bodegas_v2':
            // Documento = archivo mensual completo de la bodega (todas sus
            // inspecciones del mes en una sola tabla), igual que empaque_v2:
            // no se aísla un registro puntual.
            $urlToRender = "http://{$host}/template/bodegas_v2/visor_bodegas_v2.php?file=" . rawurlencode($filename) . "&sede={$sede}";
            $landscape = true;
            $pdfPath = dirname($ruta) . "/Bodega_{$filenameNoExt}.pdf";
            break;
    }

    sp_debug("Procesando: $ruta");
    sp_debug("Modulo: $modulo, Date: $date");

    $needsHtml = (bool) ($urlToRender && $pdfPath);
    if ($needsHtml) {
        sp_debug("URL a renderizar: $urlToRender");
        sp_debug("PDF Path: $pdfPath");
        $pdfJobs[] = ['id' => $i, 'url' => $urlToRender, 'pdfPath' => $pdfPath, 'landscape' => $landscape];
    } else {
        sp_debug("No se cumplen condiciones HTML. urlToRender: " . ($urlToRender ? 'SI' : 'NO') . ", pdfPath: " . ($pdfPath ? 'SI' : 'NO'));
    }

    $fileContext[$i] = [
        'ruta' => $ruta,
        'filename' => $filename,
        'filenameNoExt' => $filenameNoExt,
        'sede' => $sede,
        'needsHtml' => $needsHtml,
        'pdfPath' => $pdfPath,
        'jsonAtomicoPath' => $jsonAtomicoPath,
    ];
}

// ═══════════════════════════════════════════════════════════════════
// ETAPA B — Ejecutar TODOS los jobs de Puppeteer en una sola invocación:
// un único navegador se lanza una vez y renderiza una pestaña por job,
// en vez de un navegador nuevo por cada archivo.
// ═══════════════════════════════════════════════════════════════════
$pdfResults = []; // id => ['success'=>bool, 'error'=>...]
if (!empty($pdfJobs)) {
    sp_debug("Session ID para Puppeteer: $sessionId");
    // CRÍTICO: Liberar el bloqueo de sesión ANTES de llamar a Puppeteer.
    // Si no lo hacemos, PHP mantiene el archivo de sesión bloqueado,
    // Puppeteer no puede leer la sesión, la página redirige al login,
    // y el timeout se cumple sin generar el PDF.
    session_write_close();

    $manifestPath = $tempPdfDir . 'batch_' . uniqid('', true) . '.json';
    file_put_contents($manifestPath, json_encode([
        'sessionId' => $sessionId,
        'host' => $host,
        'jobs' => $pdfJobs,
    ], JSON_UNESCAPED_UNICODE));

    $cmd = "node " . escapeshellarg(__DIR__ . "/generate_pdf_headless.js") . " --batch " . escapeshellarg($manifestPath) . " 2>&1";
    sp_debug("Comando batch (" . count($pdfJobs) . " jobs): $cmd");
    $out = shell_exec($cmd);
    sp_debug("Salida Puppeteer batch: " . print_r($out, true));
    @unlink($manifestPath);

    $decoded = json_decode($out, true);
    if (is_array($decoded) && !empty($decoded['results'])) {
        foreach ($decoded['results'] as $r) {
            $pdfResults[$r['id']] = $r;
        }
    }
}

// ═══════════════════════════════════════════════════════════════════
// ETAPA C — Reensamblar $rutasValidas en el orden original, aplicando
// éxito/fallo de cada job y los mismos fallbacks (Excel, archivo crudo)
// que existían antes.
// ═══════════════════════════════════════════════════════════════════
$rutasValidas = [];
$tempJsonsToDelete = []; // JSONs atómicos temporales a limpiar tras la subida
$excelPdfModules = ['liberaciones', 'liberaciones_zs', 'mantenimiento_zc', 'mantenimiento_zs'];

foreach ($fileContext as $i => $ctx) {
    $ruta = $ctx['ruta'];
    $filename = $ctx['filename'];
    $filenameNoExt = $ctx['filenameNoExt'];
    $sede = $ctx['sede'];

    if ($ctx['needsHtml']) {
        $pdfOk = !empty($pdfResults[$i]['success']) && file_exists($ctx['pdfPath']);
        if ($pdfOk) {
            sp_debug("PDF Creado con éxito: {$ctx['pdfPath']}");
            $rutasValidas[] = $ctx['pdfPath'];
            // Flujo bilateral: JSON atómico del día (solo los turnos de $date), o del
            // registro puntual de verificación en el caso de maquinas_v2.
            if (in_array($modulo, ['molienda_v2', 'maquinas_v2']) && $ctx['jsonAtomicoPath'] && file_exists($ctx['jsonAtomicoPath'])) {
                $rutasValidas[]      = $ctx['jsonAtomicoPath'];
                $tempJsonsToDelete[] = $ctx['jsonAtomicoPath'];
                sp_debug("JSON atómico añadido a subida: {$ctx['jsonAtomicoPath']}");
            }
            // Flujo bilateral empaque: subir también el JSON del lote junto al PDF
            if ($modulo === 'empaque_v2' && file_exists($ruta)) {
                $rutasValidas[] = $ruta;
                sp_debug("JSON de lote empaque añadido a subida: $ruta");
            }
            // Flujo bilateral bulto: subir JSON del producto junto al PDF
            if ($modulo === 'cantidad_bulto' && file_exists($ruta)) {
                $rutasValidas[] = $ruta;
                sp_debug("JSON de producto bulto añadido a subida: $ruta");
            }
            // Flujo bilateral bodegas: subir también el JSON mensual completo junto al PDF
            if ($modulo === 'bodegas_v2' && file_exists($ruta)) {
                $rutasValidas[] = $ruta;
                sp_debug("JSON mensual de bodega añadido a subida: $ruta");
            }
            continue; // PDF HTML generado exitosamente
        }
        $errMsg = $pdfResults[$i]['error'] ?? 'sin resultado del batch';
        sp_debug("Fallo al crear PDF ($errMsg), se usará fallback.");
    }

    // 2. Mapeo para Visores Excel (Mantenimiento, Liberaciones) -> Usan el descargar_pdf interno
    if (in_array($modulo, $excelPdfModules)) {
        $tipo = strpos($modulo, 'liberaciones') !== false ? 'liberaciones' : 'mantenimiento';
        $pdfPath = dirname($ruta) . "/{$tipo}_{$filenameNoExt}.pdf";
        $excelUrl = "http://{$host}/template/descargar_pdf.php?archivo=" . urlencode($filename) . "&sede={$sede}&tipo={$tipo}";

        $ch = curl_init($excelUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIE, "PHPSESSID=" . $sessionId);
        $pdfData = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode == 200 && $pdfData && substr($pdfData, 0, 4) === '%PDF') {
            file_put_contents($pdfPath, $pdfData);
            sp_debug("PDF Excel descargado con éxito.");
            $rutasValidas[] = $pdfPath;
            continue; // PDF Excel generado exitosamente
        }
        sp_debug("Fallo en PDF Excel. HTTP: $httpCode. Fallback activado.");
    }

    // Si no hay mapeo de PDF o falló la generación, usamos el archivo original como respaldo
    sp_debug("Añadiendo ruta original (Fallback): $ruta");
    $rutasValidas[] = $ruta;
}

if (empty($rutasValidas)) {
    echo json_encode(['success' => false, 'error' => 'Ninguno de los archivos especificados es válido o accesible.']);
    exit;
}

// Construir el comando: node uploader_selective.js archivo1 archivo2 ...
$uploaderScript = dirname(__DIR__) . '/uploader_selective.js';
$cmd = 'cd ' . escapeshellarg(dirname($uploaderScript)) . ' && ';
$cmd .= 'node ' . escapeshellarg($uploaderScript);

foreach ($rutasValidas as $rutaValidaFinal) {
    $cmd .= ' ' . escapeshellarg($rutaValidaFinal);
}

// Redirigir stderr a stdout para capturar errores
$cmd .= ' 2>&1';

$output = shell_exec($cmd);

// Intentar parsear la salida JSON del script Node
$result = json_decode($output, true);

// Limpiar los PDFs temporales que creamos
foreach ($rutasValidas as $rutaValidaFinal) {
    // Si la ruta termina en .pdf y la ruta original no era un pdf (interceptamos el archivo)
    if (pathinfo($rutaValidaFinal, PATHINFO_EXTENSION) === 'pdf') {
        // En este punto, solo borramos si el archivo fue generado por nosotros.
        // Como todos los de $rutasValidas que terminan en pdf fueron creados para esta subida (los originales eran excel/json),
        // es seguro borrarlos. Si algún formato original YA ERA pdf, tendríamos que ser más cuidadosos,
        // pero según el switch anterior, todos partían de archivos distintos.
        if (file_exists($rutaValidaFinal)) {
            @unlink($rutaValidaFinal);
        }
    }
}

// Limpiar JSONs atómicos temporales generados para el flujo bilateral
foreach ($tempJsonsToDelete as $tmpJson) {
    if (file_exists($tmpJson)) {
        @unlink($tmpJson);
        sp_debug("JSON atómico temporal eliminado: $tmpJson");
    }
}

if ($result) {
    echo json_encode($result);
} else {
    // Si no se pudo parsear, devolver la salida cruda como error
    echo json_encode([
        'success' => false,
        'error' => 'Error ejecutando el uploader: ' . substr($output, 0, 2000),
        'archivos_enviados' => count($rutasValidas)
    ]);
}
