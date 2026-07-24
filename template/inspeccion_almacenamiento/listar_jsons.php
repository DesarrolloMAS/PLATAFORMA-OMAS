<?php
require '../sesion.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['nombre']) || empty($_SESSION['sede'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Sin sesión activa.']);
    exit;
}

$sede        = $_SESSION['sede'];
$sede_san    = preg_replace('/[^A-Za-z0-9_-]/', '', $sede);
$target_dir  = "../../archivos/generados/inspeccion_almacenamiento/" . $sede_san . "/";

$archivos = [];

if (file_exists($target_dir)) {
    foreach (scandir($target_dir) as $file) {
        if ($file === '.' || $file === '..') continue;
        if (strtolower(pathinfo($file, PATHINFO_EXTENSION)) !== 'json') continue;

        $filepath = $target_dir . $file;
        $content  = json_decode(file_get_contents($filepath), true) ?: [];

        $total_registros = count($content);
        $ultimo          = !empty($content) ? end($content) : null;

        $last_fecha      = $ultimo['datos']['fecha']                     ?? '—';
        $last_zona       = $ultimo['datos']['zona']                      ?? '—';
        $last_pct        = $ultimo['datos']['porcentaje_cumplimiento']   ?? null;
        $last_usuario    = $ultimo['usuario_sys']                        ?? '—';

        $periodo = str_replace(['INSALM_', '.json'], '', $file);

        $archivos[] = [
            'filename'       => $file,
            'periodo'        => $periodo,
            'registros'      => $total_registros,
            'ultima_fecha'   => $last_fecha,
            'zona'           => $last_zona,
            'porcentaje'     => $last_pct !== null ? floatval($last_pct) : null,
            'ultimo_usuario' => $last_usuario,
            'mod_time'       => filemtime($filepath),
            'fecha_mod'      => date('d M Y - H:i', filemtime($filepath)),
        ];
    }

    usort($archivos, fn($a, $b) => $b['mod_time'] <=> $a['mod_time']);
}

echo json_encode(['status' => 'success', 'sede' => $sede, 'archivos' => $archivos]);
?>
