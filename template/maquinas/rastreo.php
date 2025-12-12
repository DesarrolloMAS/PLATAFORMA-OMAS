<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$directorio_verificaciones = "/var/www/html/fmt/archivos/generados/verificaciones/";
$zonas = [];

if (is_dir($directorio_verificaciones)) {
    $zonas_directorio = scandir($directorio_verificaciones);

    foreach ($zonas_directorio as $zona) {
        if ($zona !== "." && $zona !== ".." && is_dir($directorio_verificaciones . $zona)) {
            $zonaKey = strtolower($zona);
            $ruta_zona = $directorio_verificaciones . $zona . "/";
            $carpetas_maquinas = scandir($ruta_zona);

            foreach ($carpetas_maquinas as $nombre_maquina) {
                if ($nombre_maquina !== "." && $nombre_maquina !== ".." && is_dir($ruta_zona . $nombre_maquina)) {
                    $ruta_maquina = $ruta_zona . $nombre_maquina . "/";
                    $archivos = scandir($ruta_maquina);

                    foreach ($archivos as $archivo) {
                        if (pathinfo($archivo, PATHINFO_EXTENSION) == "pdf") {
                            $nombre = pathinfo($archivo, PATHINFO_FILENAME);
                            $partes = explode('_', $nombre);

                            // --- Formato especial para Camionera ---
                            if (
                                strtoupper($zona) === "camionera" &&
                                strtoupper(substr($nombre, 0, 10)) === "camionera_"
                                && count($partes) >= 5
                            ) {
                                // Ejemplo: CAMIONERA_BOG_BOGOTA_2025-06-24_2025-06-24
                                $fecha = array_pop($partes); // última parte es la fecha
                                $codigo = implode('_', array_slice($partes, 0, 3)); // CAMIONERA_BOG_BOGOTA
                                $codigo_json = $codigo;
                            }
                            // --- Formato estándar ---
                            elseif (count($partes) >= 4) {
                                $fecha = array_pop($partes);
                                $codigo = array_pop($partes);
                                $nombre_maquina_archivo = array_pop($partes);
                                $codigo_json = $nombre_maquina_archivo . '_' . $codigo;
                            } else {
                                continue; // No válido
                            }

                            // Usa el código completo como clave de máquina
                            if (!isset($zonas[$zonaKey][$codigo_json])) {
                                $zonas[$zonaKey][$codigo_json] = ["codigos" => []];
                            }

                            // Buscar si ya existe este código y si la fecha es más reciente
                            $encontrado = false;
                            foreach ($zonas[$zonaKey][$codigo_json]["codigos"] as &$c) {
                                if ($c["codigo"] === $codigo_json) {
                                    if ($fecha > $c["ultima_verificacion"]) {
                                        $c["ultima_verificacion"] = $fecha;
                                    }
                                    $encontrado = true;
                                    break;
                                }
                            }
                            if (!$encontrado) {
                                $zonas[$zonaKey][$codigo_json]["codigos"][] = [
                                    "codigo" => $codigo_json,
                                    "ultima_verificacion" => $fecha
                                ];
                            }
                        }
                    }
                }
            }
        }
    }
}

header('Content-Type: application/json');
echo json_encode($zonas, JSON_PRETTY_PRINT);
?>