<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// API JSON: última fecha de verificación por máquina (lee los registros
// consolidados por máquina, en vez de escanear nombres de archivos PDF).
$directorio_base = __DIR__ . "/../../archivos/generados/maquinas_v2/";
$zonas = [];

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

                // El registro vigente es el más reciente por timestamp entre
                // verificaciones/correcciones formales (los borradores no cuentan
                // como "verificada", igual que en v1 donde accion=guardar no generaba PDF).
                $vigente = null;
                foreach ($registros as $registro) {
                    if (($registro['estado'] ?? '') === 'borrador') continue;
                    if ($vigente === null || ($registro['timestamp'] ?? '') > ($vigente['timestamp'] ?? '')) {
                        $vigente = $registro;
                    }
                }
                if (!$vigente) continue;

                $codigo_maquina = $vigente['datos']['codigo_maquina'] ?? pathinfo($archivo, PATHINFO_FILENAME);
                $fecha = substr($vigente['timestamp'] ?? '', 0, 10) ?: date('Y-m-d');

                if (!isset($zonas[$tipo][$codigo_maquina])) {
                    $zonas[$tipo][$codigo_maquina] = ["codigos" => []];
                }
                $zonas[$tipo][$codigo_maquina]["codigos"][] = [
                    "codigo" => $codigo_maquina,
                    "ultima_verificacion" => $fecha
                ];
            }
        }
    }
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($zonas, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
