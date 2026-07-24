<?php
require '../sesion.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['nombre']) || empty($_SESSION['sede'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Sin sesión activa.']);
    exit;
}

$sede       = $_SESSION['sede'];
$sede_dir   = preg_replace('/[^A-Za-z0-9_-]/', '', $sede);
$target_dir = "../../archivos/generados/inspeccion_gestion_produccion_zs/" . $sede_dir . "/";

$archivos = [];

if (file_exists($target_dir)) {
    $files = scandir($target_dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        if (strtolower(pathinfo($file, PATHINFO_EXTENSION)) !== 'json') continue;

        $filepath = $target_dir . $file;
        $content  = json_decode(file_get_contents($filepath), true) ?: [];

        $total  = count($content);
        $ultimo = !empty($content) ? end($content) : null;

        $last_fecha       = $ultimo['datos']['fecha']                   ?? '—';
        $last_responsable = $ultimo['datos']['responsable_nombre']      ?? '—';
        $last_porcentaje  = $ultimo['datos']['porcentaje_cumplimiento'] ?? null;
        $last_usuario     = $ultimo['usuario_sys']                      ?? '—';

        $periodo = str_replace(['INGGESTPROD_', '.json'], '', $file);

        $archivos[] = [
            'filename'       => $file,
            'periodo'        => $periodo,
            'registros'      => $total,
            'ultima_fecha'   => $last_fecha,
            'responsable'    => $last_responsable,
            'porcentaje'     => $last_porcentaje !== null ? floatval($last_porcentaje) : null,
            'ultimo_usuario' => $last_usuario,
            'mod_time'       => filemtime($filepath),
            'fecha_mod'      => date('d M Y - H:i', filemtime($filepath)),
        ];
    }

    usort($archivos, fn($a, $b) => $b['mod_time'] <=> $a['mod_time']);
}

echo json_encode(['status' => 'success', 'sede' => $sede, 'archivos' => $archivos]);
?>
