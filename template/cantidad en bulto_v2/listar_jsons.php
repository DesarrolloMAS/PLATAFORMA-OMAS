<?php
require '../sesion.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['nombre']) || empty($_SESSION['sede'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Sin sesión activa.']);
    exit;
}

$sede         = $_SESSION['sede'];
$sede_saneada = preg_replace('/[^A-Za-z0-9_-]/', '', $sede);
$target_dir   = "../../archivos/generados/cantidad_bulto/" . $sede_saneada . "/";

$archivos = [];

if (file_exists($target_dir)) {
    $files = scandir($target_dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        if (strtolower(pathinfo($file, PATHINFO_EXTENSION)) !== 'json') continue;

        $filepath = $target_dir . $file;
        $content  = json_decode(file_get_contents($filepath), true) ?: [];

        $total_registros = count($content);
        $ultimo          = !empty($content) ? end($content) : null;
        $primero         = !empty($content) ? reset($content) : null;

        $last_fecha    = $ultimo['datos']['fecha']    ?? '—';
        $first_fecha   = $primero['datos']['fecha']   ?? '—';
        $last_usuario  = $ultimo['usuario_sys']       ?? '—';
        // Nombre del producto = nombre del archivo sin extensión (revertir saneado visual)
        $producto      = str_replace('_', ' ', pathinfo($file, PATHINFO_FILENAME));

        $archivos[] = [
            'filename'       => $file,
            'producto'       => $producto,
            'registros'      => $total_registros,
            'primera_fecha'  => $first_fecha,
            'ultima_fecha'   => $last_fecha,
            'ultimo_usuario' => $last_usuario,
            'mod_time'       => filemtime($filepath),
            'fecha_mod'      => date('d M Y - H:i', filemtime($filepath)),
        ];
    }

    usort($archivos, fn($a, $b) => strcmp($a['producto'], $b['producto']));
}

echo json_encode(['status' => 'success', 'sede' => $sede, 'archivos' => $archivos]);
?>
