<?php
include '../sesion.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['nombre']) || empty($_SESSION['sede'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Sin sesión activa.']);
    exit;
}

$sede = $_SESSION['sede'];
$sede_saneada = preg_replace('/[^A-Za-z0-9_-]/', '', $sede);
$target_dir = "../../archivos/generados/inspeccion_dosificadores/" . $sede_saneada . "/";

$archivos = [];

if (file_exists($target_dir)) {
    $files = scandir($target_dir);
    foreach ($files as $file) {
        // Cada inspección vive en su propio archivo "INSPECCION_...json" —
        // se excluye el catálogo de dosificadores y cualquier otro json que
        // no sea una inspección individual.
        if (!preg_match('/^INSPECCION_.*\.json$/i', $file)) continue;

        $filepath = $target_dir . $file;
        $reg = json_decode(file_get_contents($filepath), true);
        if (!is_array($reg) || empty($reg['datos'])) continue;

        $d = $reg['datos'];

        $archivos[] = [
            'filename'         => $file,
            'id_registro'      => $reg['id_registro'] ?? '',
            'ultima_fecha'     => $d['fecha']            ?? '—',
            'dosificador'      => $d['dosificador']       ?? '—',
            'microingrediente' => $d['microingrediente'] ?? '—',
            'cumple'           => $d['cumple']           ?? null,
            'ultimo_usuario'   => $reg['usuario_sys']    ?? '—',
            'mod_time'         => filemtime($filepath),
            'fecha_mod'        => date('d M Y - H:i', filemtime($filepath)),
        ];
    }

    usort($archivos, fn($a, $b) => $b['mod_time'] <=> $a['mod_time']);
}

echo json_encode(['status' => 'success', 'sede' => $sede, 'archivos' => $archivos]);
?>
