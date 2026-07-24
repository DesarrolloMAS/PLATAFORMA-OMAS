<?php
/**
 * listar_dias.php
 * ---------------------
 * Agrupa todos los registros JSON de la sede activa por su fecha_registro
 * real (no por archivo). Cada grupo resultante equivale a lo que antes
 * era "un archivo .xlsx" en el sistema legacy.
 */
require_once '../../sesion.php';
verificarAutenticacion();
header('Content-Type: application/json; charset=utf-8');

$sede = $_SESSION['sede'];
$sede_san = preg_replace('/[^A-Za-z0-9_-]/', '', $sede);
$sede_dir = "/var/www/fmt/archivos/generados/Calidad/muestras/" . $sede_san . "/";

$dias = [];

if (is_dir($sede_dir)) {
    foreach (glob($sede_dir . '*.json') as $archivo) {
        $periodo   = basename($archivo, '.json');
        $registros = json_decode(file_get_contents($archivo), true) ?: [];

        foreach ($registros as $r) {
            $d = $r['datos'] ?? [];
            $fecha = $d['fecha_registro'] ?? null;
            if (!$fecha) continue;

            if (!isset($dias[$fecha])) {
                $dias[$fecha] = [
                    'fecha'          => $fecha,
                    'periodo'        => $periodo,
                    'total'          => 0,
                    'con_disp'       => 0,
                    'ultimo_usuario' => $r['usuario_sys'] ?? '—',
                    'mod_time'       => filemtime($archivo),
                ];
            }

            $dias[$fecha]['total']++;
            if (!empty($d['disp_fecha'])) {
                $dias[$fecha]['con_disp']++;
            }
        }
    }
}

$listaDias = array_values($dias);
usort($listaDias, fn($a, $b) => strcmp($b['fecha'], $a['fecha']));

echo json_encode(['status' => 'success', 'sede' => $sede, 'dias' => $listaDias]);
?>
