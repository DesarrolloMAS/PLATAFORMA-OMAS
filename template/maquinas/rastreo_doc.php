<?php
header('Content-Type: application/json');

$directorio = "/var/www/fmt/archivos/generados/verificaciones/";
$resultado = [];

if (is_dir($directorio)) {
    foreach (scandir($directorio) as $zona) {
        if ($zona === '.' || $zona === '..') continue;
        $rutaZona = $directorio . $zona . '/';
        if (!is_dir($rutaZona)) continue;

        foreach (scandir($rutaZona) as $maquina) {
            if ($maquina === '.' || $maquina === '..') continue;
            $rutaMaquina = $rutaZona . $maquina . '/';
            if (!is_dir($rutaMaquina)) continue;

            $pdfs = [];
            foreach (scandir($rutaMaquina) as $archivo) {
                if (strtolower(pathinfo($archivo, PATHINFO_EXTENSION)) === 'pdf') {
                    $pdfs[] = $archivo;
                }
            }
            if (!empty($pdfs)) {
                $resultado[$zona][$maquina] = [
                    'pdfs' => $pdfs
                ];
            }
        }
    }
}

echo json_encode($resultado, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);