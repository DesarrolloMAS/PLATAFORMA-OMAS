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
$target_dir   = "../../archivos/generados/inspeccion_control_operaciones/" . $sede_saneada . "/";

$archivos = [];

if (file_exists($target_dir)) {
    $files = scandir($target_dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        if (strtolower(pathinfo($file, PATHINFO_EXTENSION)) !== 'json') continue;

        $filepath = $target_dir . $file;
        $content  = json_decode(file_get_contents($filepath), true) ?: [];

        $total_registros = count($content);
        $ultimo_registro = !empty($content) ? end($content) : null;

        $last_fecha       = $ultimo_registro['datos']['fecha']               ?? '—';
        $last_linea       = $ultimo_registro['datos']['linea_produccion']     ?? '—';
        $last_porcentaje  = $ultimo_registro['datos']['porcentaje_cumplimiento'] ?? null;
        $last_usuario     = $ultimo_registro['usuario_sys']                  ?? '—';

        $periodo = str_replace(['INSCOP_', '.json'], '', $file);

        $archivos[] = [
            'filename'       => $file,
            'periodo'        => $periodo,
            'registros'      => $total_registros,
            'ultima_fecha'   => $last_fecha,
            'linea'          => $last_linea,
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
