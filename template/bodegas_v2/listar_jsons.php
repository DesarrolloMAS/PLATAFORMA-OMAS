<?php
require '../sesion.php';
verificarAutenticacion();

header('Content-Type: application/json; charset=utf-8');

$sede = $_SESSION['sede'];
$sede_saneada = preg_replace('/[^A-Za-z0-9_-]/', '', $sede);
$target_dir = "../../archivos/generados/bodegas_v2/" . $sede_saneada . "/";

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

        $bodega_key    = $ultimo['bodega_key']    ?? str_replace('.json', '', preg_replace('/_\d{4}-\d{2}$/', '', $file));
        $bodega_nombre = $ultimo['bodega_nombre'] ?? $bodega_key;
        $last_fecha    = $ultimo['datos']['fecha'] ?? '—';
        $last_usuario  = $ultimo['usuario_sys']    ?? '—';

        // % de cumplimiento del último registro: SI / (SI + NO)
        $porcentaje = null;
        if ($ultimo) {
            $si = 0; $no = 0;
            for ($i = 1; $i <= 14; $i++) {
                $v = $ultimo['datos']["opcion$i"] ?? '';
                if ($v === 'SI') $si++;
                if ($v === 'NO') $no++;
            }
            if (($si + $no) > 0) $porcentaje = round(($si / ($si + $no)) * 100, 1);
        }

        // Periodo: [bodega_key]_[YYYY-MM].json
        $periodo = preg_replace('/^' . preg_quote($bodega_key, '/') . '_/', '', str_replace('.json', '', $file));

        $archivos[] = [
            'filename'       => $file,
            'bodega_key'     => $bodega_key,
            'bodega_nombre'  => $bodega_nombre,
            'periodo'        => $periodo,
            'registros'      => $total_registros,
            'ultima_fecha'   => $last_fecha,
            'porcentaje'     => $porcentaje,
            'ultimo_usuario' => $last_usuario,
            'mod_time'       => filemtime($filepath),
            'fecha_mod'      => date('d M Y - H:i', filemtime($filepath)),
        ];
    }

    usort($archivos, fn($a, $b) => $b['mod_time'] <=> $a['mod_time']);
}

echo json_encode(['status' => 'success', 'sede' => $sede, 'archivos' => $archivos]);
?>
