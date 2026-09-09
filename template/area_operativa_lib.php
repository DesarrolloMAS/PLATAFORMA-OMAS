<?php
// Área operativa (administracion/almacen/mantenimiento/produccion) — el
// mapeo Cargo → área que ya gestiona admin/menu_admin.php en
// archivos/generados/admin/cargo_areas.json. Hasta ahora ese archivo era
// puramente informativo ("no restringe el acceso de nadie"); esta es la
// primera vez que se usa para controlar algo de verdad.
//
// Deliberadamente NO es lo mismo que $_SESSION['area'] (Operaciones/Calidad/
// HSEQ, la que decide el menú de entrada al iniciar sesión) — son dos
// dimensiones distintas que ya convivían en el sistema.

function obtenerAreaOperativa(): string {
    $path = __DIR__ . '/../archivos/generados/admin/cargo_areas.json';
    if (!file_exists($path)) return 'sin_asignar';

    $mapa = json_decode(file_get_contents($path), true) ?: [];
    $cargo = $_SESSION['cargo'] ?? '';

    return $mapa[$cargo] ?? 'sin_asignar';
}

function esAreaMantenimiento(): bool {
    if ((($_SESSION['rol'] ?? '') === 'adm')) return true; // admin siempre puede
    return obtenerAreaOperativa() === 'mantenimiento';
}

// Quién puede crear/editar/dar de baja en el calendario de máquinas u
// objetos de evento. Dos criterios distintos según el área organizacional
// ($_SESSION['area']: Operaciones/Calidad/HSEQ, la que decide el menú de
// entrada al iniciar sesión — no confundir con la "área operativa" de
// arriba, que es una subdivisión interna de Operaciones):
//   - Operaciones: además de admin, solo el área operativa "mantenimiento"
//     (Almacenista/Empacador/etc. en Operaciones también son rol 1, pero no
//     deben poder tocar el catálogo de máquinas).
//   - Cualquier otra área (Calidad, HSEQ, ...): no existe ahí el concepto de
//     "área operativa" (esa subdivisión es interna de Operaciones), así que
//     el criterio es simplemente rol '1' — la generalidad de esa área.
function puedeGestionarCalendario(): bool {
    if ((($_SESSION['rol'] ?? '') === 'adm')) return true;
    if ((($_SESSION['area'] ?? '') === 'Operaciones')) return esAreaMantenimiento();
    return ($_SESSION['rol'] ?? '') === '1';
}

// Fuera de Operaciones, el catálogo no representa máquinas físicas sino
// cualquier cosa que esa área quiera programar en el calendario — el
// usuario pidió explícitamente que ahí se llamen "objetos de evento".
function terminoObjeto(bool $plural = false): string {
    $esOperaciones = (($_SESSION['area'] ?? '') === 'Operaciones');
    if ($esOperaciones) return $plural ? 'Máquinas' : 'Máquina';
    return $plural ? 'Objetos de Evento' : 'Objeto de Evento';
}

// "Máquina" es femenino, "Objeto de Evento" es masculino — para que los
// mensajes de confirmación concuerden en género según el área.
function terminoObjetoVerbo(string $sufijoFemenino, string $sufijoMasculino): string {
    $esOperaciones = (($_SESSION['area'] ?? '') === 'Operaciones');
    return $esOperaciones ? $sufijoFemenino : $sufijoMasculino;
}
