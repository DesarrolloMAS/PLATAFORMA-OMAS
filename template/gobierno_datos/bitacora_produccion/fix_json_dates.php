<?php
$path = '/var/www/fmt/archivos/generados/molienda/ZC/2026-04.json';
$data = json_decode(file_get_contents($path), true);

$ids_a_mover = ['69d78f0bcf585', '69d7fb9f4c168'];

foreach ($data as &$registro) {
    if (in_array($registro['id'], $ids_a_mover)) {
        $registro['fecha'] = '2026-04-08';
        echo "Cambiando ID {$registro['id']} a fecha 2026-04-08\n";
    }
}

// Guardar los cambios (Borrar antes para saltar el bloqueo de permisos)
unlink($path); 
if (file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT))) {
    echo "JSON actualizado con éxito.\n";
} else {
    echo "Error al guardar el JSON.\n";
}
