<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../sesion.php';
verificarAutenticacion();

header('Content-Type: application/json; charset=utf-8');

// Equivalente a rastreo_doc.php de v1, pero lee los registros JSON
// consolidados por máquina en vez de escanear nombres de archivos PDF.
$directorio_base = __DIR__ . "/../../archivos/generados/maquinas_v2/";
$resultado = [];

if (is_dir($directorio_base)) {
    foreach (scandir($directorio_base) as $tipo) {
        if ($tipo === "." || $tipo === "..") continue;
        $ruta_tipo = $directorio_base . $tipo . "/";
        if (!is_dir($ruta_tipo)) continue;

        foreach (scandir($ruta_tipo) as $grupo) {
            if ($grupo === "." || $grupo === "..") continue;
            $ruta_grupo = $ruta_tipo . $grupo . "/";
            if (!is_dir($ruta_grupo)) continue;

            foreach (scandir($ruta_grupo) as $archivo) {
                if (pathinfo($archivo, PATHINFO_EXTENSION) !== "json") continue;

                $registros = json_decode(file_get_contents($ruta_grupo . $archivo), true);
                if (!is_array($registros) || empty($registros)) continue;

                $codigo_maquina = $registros[0]['datos']['codigo_maquina'] ?? pathinfo($archivo, PATHINFO_FILENAME);

                $resumen = array_map(function ($r) {
                    return [
                        'id_registro'   => $r['id_registro'] ?? '',
                        'timestamp'     => $r['timestamp'] ?? '',
                        'usuario_sys'   => $r['usuario_sys'] ?? '',
                        'tipo_registro' => $r['tipo_registro'] ?? 'verificacion',
                        'estado'        => $r['estado'] ?? 'verificado',
                        'corrige_id'    => $r['corrige_id'] ?? null,
                    ];
                }, $registros);

                // Más reciente primero
                usort($resumen, fn($a, $b) => $b['timestamp'] <=> $a['timestamp']);

                $resultado[$tipo][$grupo][$codigo_maquina] = ['registros' => $resumen];
            }
        }
    }
}

echo json_encode(['status' => 'success', 'zonas' => $resultado], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
