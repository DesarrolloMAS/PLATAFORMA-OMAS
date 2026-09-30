<?php
// Bandeja de entrada del usuario — persistencia y helpers.
//
// Un archivo JSON por usuario (la bandeja es personal, no por sede ni por
// mes): archivos/generados/usuario/bandeja/[id_usuario].json, con un arreglo
// de notificaciones:
//   { id, titulo, mensaje, tipo, modulo, enlace, fecha, leida }
//   tipo: info | aviso | alerta | exito
//
// Cualquier módulo puede enviar una notificación con:
//   require_once __DIR__ . '/../usuario/bandeja_lib.php';
//   crearNotificacion($idUsuario, 'Título', 'Mensaje', 'aviso', 'Mantenimiento', '/template/...');
// Es una escritura local de archivo (no una integración externa), así que es
// seguro llamarla desde un procesar.php.

const BANDEJA_TIPOS = ['info', 'aviso', 'alerta', 'exito'];

function bandejaRuta($idUsuario): string {
    $id = preg_replace('/[^0-9A-Za-z_-]/', '', (string)$idUsuario);
    return __DIR__ . '/../../archivos/generados/usuario/bandeja/' . $id . '.json';
}

// Abre la bandeja con bloqueo exclusivo, deja que $fn la modifique y la
// guarda. $fn recibe el arreglo por referencia y puede devolver un valor.
function bandejaModificar($idUsuario, callable $fn) {
    $ruta = bandejaRuta($idUsuario);
    if (!is_dir(dirname($ruta))) {
        mkdir(dirname($ruta), 0777, true);
    }
    $fp = fopen($ruta, 'c+');
    if (!$fp) {
        throw new RuntimeException('No se pudo abrir la bandeja.');
    }
    try {
        flock($fp, LOCK_EX);
        $contenido = stream_get_contents($fp);
        $notificaciones = json_decode($contenido ?: '[]', true) ?: [];
        $resultado = $fn($notificaciones);
        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, json_encode(array_values($notificaciones), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        fflush($fp);
        flock($fp, LOCK_UN);
        return $resultado;
    } finally {
        fclose($fp);
    }
}

function bandejaLeer($idUsuario): array {
    $ruta = bandejaRuta($idUsuario);
    if (!file_exists($ruta)) {
        // Primera vez: se crea con un mensaje de bienvenida para que la
        // bandeja no aparezca vacía sin explicación.
        crearNotificacion(
            $idUsuario,
            'Bienvenido a tu bandeja de entrada',
            'Aquí vas a recibir los avisos de la plataforma: tareas asignadas, aprobaciones, recordatorios y novedades de tus módulos.',
            'info',
            'NOVA'
        );
    }
    $notificaciones = json_decode(@file_get_contents($ruta) ?: '[]', true) ?: [];
    usort($notificaciones, fn($a, $b) => strcmp($b['fecha'] ?? '', $a['fecha'] ?? ''));
    return $notificaciones;
}

function crearNotificacion($idUsuario, string $titulo, string $mensaje, string $tipo = 'info', string $modulo = '', string $enlace = ''): string {
    $notificacion = [
        'id'      => uniqid('ntf_'),
        'titulo'  => $titulo,
        'mensaje' => $mensaje,
        'tipo'    => in_array($tipo, BANDEJA_TIPOS, true) ? $tipo : 'info',
        'modulo'  => $modulo,
        'enlace'  => $enlace,
        'fecha'   => date('Y-m-d H:i:s'),
        'leida'   => false,
    ];
    bandejaModificar($idUsuario, function (array &$lista) use ($notificacion) {
        $lista[] = $notificacion;
    });
    return $notificacion['id'];
}

// Envía la misma notificación a todos los usuarios de la tabla `usuarios`
// que tengan un cargo dado. Devuelve cuántos la recibieron.
function notificarCargo(PDO $pdoUsuarios, string $cargo, string $titulo, string $mensaje, string $tipo = 'info', string $modulo = '', string $enlace = ''): int {
    $stmt = $pdoUsuarios->prepare('SELECT id_usuario FROM usuarios WHERE Cargo = :cargo');
    $stmt->execute([':cargo' => $cargo]);
    $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($ids as $id) {
        crearNotificacion($id, $titulo, $mensaje, $tipo, $modulo, $enlace);
    }
    return count($ids);
}
