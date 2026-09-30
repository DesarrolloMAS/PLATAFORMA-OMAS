<?php
// Etiquetas legibles de los roles — mismas que admin/menu_admin.php.
// Compartido por validar_usuario.php y las notificaciones de cambio de rol.
function etiquetaRol(string $rol): string {
    switch ($rol) {
        case 'adm': return 'ADM · Superadministrador';
        case '1':   return 'Rol 1 · Alto';
        case '2':   return 'Rol 2 · Intermedio';
        case '3':   return 'Rol 3 · Operador';
        default:    return $rol !== '' ? 'Rol ' . $rol : 'Sin rol';
    }
}
