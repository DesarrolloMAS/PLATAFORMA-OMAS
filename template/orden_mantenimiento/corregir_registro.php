<?php
require_once '../sesion.php';
verificarAutenticacion();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

$sede = $_SESSION['sede'] ?? 'NA';
$file = $_POST['file'] ?? '';
$id   = $_POST['id']   ?? '';

if (!$file || !$id) {
    echo json_encode(['success' => false, 'error' => 'Faltan parámetros']);
    exit;
}

// Sanitizar nombre de archivo
$file = basename($file);
if (!preg_match('/^\d{4}-\d{2}$/', $file)) {
    echo json_encode(['success' => false, 'error' => 'Nombre de archivo inválido']);
    exit;
}

$json_path  = "../../archivos/generados/orden_mantenimiento/{$sede}/{$file}.json";
$upload_dir = "../../archivos/generados/orden_mantenimiento/evidencias/";

if (!file_exists($json_path)) {
    echo json_encode(['success' => false, 'error' => 'Archivo no encontrado']);
    exit;
}

$registros = json_decode(file_get_contents($json_path), true) ?: [];
$found = false;

foreach ($registros as &$reg) {
    if ($reg['id'] !== $id) continue;
    $found = true;

    // ── Campos de datos ──────────────────────────────────────────────────────
    // Construir array de datos a actualizar (todo el POST excepto control interno y firmas)
    $ignorar = ['file', 'id', 'firma_solicitante', 'firma_autorizado', 'firma_respLim', 'firma_respLim2'];
    $nuevos_datos = $reg['datos']; // comenzar con los existentes

    foreach ($_POST as $key => $val) {
        if (in_array($key, $ignorar)) continue;
        $nuevos_datos[$key] = $val;
    }

    // Preservar siempre el numero_orden original (tiene su propio sistema)
    $nuevos_datos['numero_orden'] = $reg['datos']['numero_orden'] ?? ($reg['datos']['numero_orden'] ?? '');

    $reg['datos'] = $nuevos_datos;

    // ── Procesar firmas (Base64 → PNG) ────────────────────────────────────────
    $firmasMap = [
        'firma_solicitante' => ['key' => 'solicitante', 'prefix' => 'sig_sol'],
        'firma_autorizado'  => ['key' => 'autorizado',  'prefix' => 'sig_aut'],
        'firma_respLim'     => ['key' => 'limpieza',    'prefix' => 'sig_limp'],
        'firma_respLim2'    => ['key' => 'revisa_limpieza', 'prefix' => 'sig_rev_limp'],
    ];

    foreach ($firmasMap as $postKey => $info) {
        $base64 = $_POST[$postKey] ?? '';
        if (!empty($base64) && strpos($base64, 'data:image') === 0) {
            // Eliminar archivo anterior si existe
            $anterior = $reg['firmas'][$info['key']] ?? null;
            if ($anterior && file_exists($upload_dir . $anterior)) {
                @unlink($upload_dir . $anterior);
            }
            // Guardar nuevo PNG
            $parts    = explode(',', $base64);
            $imgData  = base64_decode($parts[1]);
            $fileName = $info['prefix'] . '_' . uniqid() . '.png';
            file_put_contents($upload_dir . $fileName, $imgData);
            $reg['firmas'][$info['key']] = $fileName;
        }
    }

    // ── Procesar evidencias fotográficas (archivos subidos) ───────────────────
    $evidenciasMap = [
        'foto_antes'    => 'antes',
        'foto_despues'  => 'despues',
        'foto_antes2'   => 'antes2',
        'foto_despues2' => 'despues2',
    ];

    $allowed_exts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    foreach ($evidenciasMap as $fileKey => $evKey) {
        if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] === UPLOAD_ERR_OK) {
            // Eliminar archivo anterior si existe
            $anterior = $reg['evidencias'][$evKey] ?? null;
            if ($anterior && file_exists($upload_dir . $anterior)) {
                @unlink($upload_dir . $anterior);
            }
            $ext = strtolower(pathinfo($_FILES[$fileKey]['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowed_exts)) {
                $newName = 'img_' . uniqid() . '.' . $ext;
                if (move_uploaded_file($_FILES[$fileKey]['tmp_name'], $upload_dir . $newName)) {
                    $reg['evidencias'][$evKey] = $newName;
                }
            }
        }
    }

    break;
}
unset($reg);

if (!$found) {
    echo json_encode(['success' => false, 'error' => 'Registro no encontrado']);
    exit;
}

file_put_contents($json_path, json_encode($registros, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo json_encode(['success' => true, 'message' => 'Registro corregido correctamente']);
