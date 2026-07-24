<?php
include '../../../sesion.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['nombre']) || empty($_SESSION['sede'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Sin sesión activa.']);
    exit;
}

$sede         = $_SESSION['sede'];
$sede_saneada = preg_replace('/[^A-Za-z0-9_-]/', '', $sede);
$target_dir   = "../../../../archivos/generados/cert_apoyo_alturas/" . $sede_saneada . "/";

$archivos = [];

if (file_exists($target_dir)) {
    foreach (scandir($target_dir) as $file) {
        if ($file === '.' || $file === '..') continue;
        if (strtolower(pathinfo($file, PATHINFO_EXTENSION)) !== 'json') continue;

        $filepath = $target_dir . $file;
        $content  = json_decode(file_get_contents($filepath), true) ?: [];

        $total    = count($content);
        $ultimo   = !empty($content) ? end($content)   : null;
        $primero  = !empty($content) ? reset($content) : null;

        $archivos[] = [
            'filename'       => $file,
            'periodo'        => str_replace(['ALTA_', '.json'], '', $file),
            'registros'      => $total,
            'primera_fecha'  => $primero['datos']['fecha']   ?? '—',
            'ultima_fecha'   => $ultimo['datos']['fecha']    ?? '—',
            'ultimo_usuario' => $ultimo['usuario_sys']       ?? '—',
            'mod_time'       => filemtime($filepath),
            'fecha_mod'      => date('d M Y - H:i', filemtime($filepath)),
        ];
    }
    usort($archivos, fn($a, $b) => strcmp($b['filename'], $a['filename']));
}

echo json_encode(['status' => 'success', 'sede' => $sede, 'archivos' => $archivos]);
?>
