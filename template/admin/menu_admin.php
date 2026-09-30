<?php
require '../sesion.php';

// Acceso exclusivo para el rol literal 'adm' (superadministrador).
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'adm') {
    die("Acceso Denegado. Este panel es exclusivo para administradores del sistema.");
}

// Este panel es compartido entre áreas (se llega a él desde el engranaje de
// menu_adm.html, menu_administracion_calidad.html y menu_administracion_hseq.html),
// así que "volver" debe respetar de qué área vino el admin en vez de mandar
// siempre a Operaciones.
switch ($_SESSION['area'] ?? '') {
    case 'Calidad':
        $volverMenuUrl = '../menu_administracion_calidad.html';
        break;
    case 'HSEQ':
        $volverMenuUrl = '../menu_administracion_hseq.html';
        break;
    default:
        $volverMenuUrl = '../menu_adm.html';
        break;
}

require '../conection.php'; // $pdoUsuarios, para sincronizar la BD con el mapeo cargo->rol
require_once '../usuario/bandeja_lib.php'; // notificaciones de cambio de rol
require_once '../usuario/roles_lib.php';
require_once __DIR__ . '/menu_operaciones_lib.php'; // visibilidad de botones de menu_adm.html
require_once __DIR__ . '/avisos_operaciones_lib.php'; // destinatarios del aviso semanal de formatos

// Escribe un JSON de configuración y registra si falló. Antes cada
// file_put_contents se ignoraba: si el archivo no tenía permiso de escritura
// para el servidor web (www-data), el panel igual mostraba "actualizado con
// éxito" y el cambio se perdía en silencio. Los archivos que fallan se
// acumulan en $fallosGuardado y se reportan como error al final del POST.
$fallosGuardado = [];
function guardarJsonAdmin(string $ruta, array $datos): bool {
    global $fallosGuardado;
    $ok = @file_put_contents($ruta, json_encode($datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false;
    if (!$ok) {
        $fallosGuardado[] = basename($ruta);
        error_log('menu_admin: no se pudo escribir ' . $ruta . ' (revisar permisos para el usuario del servidor web)');
    }
    return $ok;
}

// Avisa en la bandeja de entrada a todos los usuarios de un cargo que el rol
// de ese cargo cambió. Nunca debe tumbar el guardado del panel: si falla la
// escritura de la bandeja, solo se registra en el log.
function avisarCambioRolCargo(PDO $pdoUsuarios, string $cargo, string $rolAnterior, string $rolNuevo): void {
    if ($rolAnterior === $rolNuevo) return;
    try {
        notificarCargo(
            $pdoUsuarios,
            $cargo,
            'Cambio de rol en tu cargo',
            'El rol del cargo "' . $cargo . '" cambió de ' . etiquetaRol($rolAnterior) . ' a ' . etiquetaRol($rolNuevo) . '. '
                . 'Recibirás otro aviso cuando el cambio quede aplicado a tu cuenta.',
            'aviso',
            'Administración'
        );
    } catch (Throwable $e) {
        error_log('avisarCambioRolCargo: ' . $e->getMessage());
    }
}

$cargo_roles_file = "../../archivos/generados/admin/cargo_roles.json";

if (!file_exists(dirname($cargo_roles_file))) {
    mkdir(dirname($cargo_roles_file), 0777, true);
}

$cargo_roles = file_exists($cargo_roles_file)
    ? (json_decode(file_get_contents($cargo_roles_file), true) ?: [])
    : [];

// Mapeo cargo -> área operativa. Es una herramienta de configuración nada
// más por ahora: no se usa todavía para restringir acceso a ningún menú, ni
// se sincroniza con la BD (a diferencia de cargo_roles.json). Se prepara
// para cuando se implemente el bloqueo por área más adelante.
$cargo_areas_file = "../../archivos/generados/admin/cargo_areas.json";

$cargo_areas_raw = file_exists($cargo_areas_file)
    ? (json_decode(file_get_contents($cargo_areas_file), true) ?: [])
    : [];

// Se recorre la lista canónica de cargos (la misma de cargo_roles.json) para
// que un cargo nuevo que todavía no tenga área asignada aparezca igual en la
// tabla como "Sin Asignar", en vez de quedar invisible.
$cargo_areas = [];
foreach (array_keys($cargo_roles) as $cargo) {
    $cargo_areas[$cargo] = $cargo_areas_raw[$cargo] ?? 'sin_asignar';
}

$areas_validas = ['sin_asignar', 'administracion', 'almacen', 'mantenimiento', 'produccion'];

// Mapeo cargo -> área grande (Operaciones/Calidad/HSEQ/Desarrollo, las que
// deciden el menú de entrada al iniciar sesión — $_SESSION['area']). No debe
// confundirse con $cargo_areas de arriba, que son las miniáreas internas de
// Operaciones. Esta sección es exclusiva de administradores del área
// "Desarrollo": ver el bloqueo al renderizar el formulario más abajo.
$cargo_area_grande_file = "../../archivos/generados/admin/cargo_area_grande.json";

$cargo_area_grande_raw = file_exists($cargo_area_grande_file)
    ? (json_decode(file_get_contents($cargo_area_grande_file), true) ?: [])
    : [];

$cargo_area_grande = [];
foreach (array_keys($cargo_roles) as $cargo) {
    $cargo_area_grande[$cargo] = $cargo_area_grande_raw[$cargo] ?? 'sin_asignar';
}

$areas_grandes_validas = ['sin_asignar', 'Operaciones', 'Calidad', 'HSEQ', 'Desarrollo'];

// Un admin normal (Operaciones/Calidad/HSEQ) solo debe ver y editar los
// cargos de su propia área grande en "Cargos y Roles" y "Cargos y Áreas
// Operativas" — Desarrollo sigue viendo y editando todo, como corresponde
// a quien administra el mapeo cargo -> área grande.
$miAreaGrande = $_SESSION['area'] ?? '';
$esAdminDesarrollo = $miAreaGrande === 'Desarrollo';

$cargosVisibles = $esAdminDesarrollo
    ? array_keys($cargo_roles)
    : array_keys(array_filter($cargo_area_grande, fn($area) => $area === $miAreaGrande));

$mensaje = '';
$mensajeEsError = false;

// La visibilidad del menú de Operaciones solo la administran admins de
// Operaciones (o Desarrollo, que se comporta igual en todo el sistema) — no
// los de Calidad/HSEQ, que también entran a este panel.
$puedeEditarMenuOps = in_array($miAreaGrande, ['Operaciones', 'Desarrollo'], true);
// El menú de Operaciones y sus avisos solo involucran cargos cuya área del
// sistema sea Operaciones ("Cargos y Áreas del Sistema"). Sin este filtro, un
// admin de Desarrollo veía también los cargos de Calidad, HSEQ y Desarrollo.
$cargosOperaciones = array_values(array_filter(
    $cargosVisibles,
    fn($c) => ($cargo_area_grande[$c] ?? '') === 'Operaciones'
));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['menu_operaciones'])) {
        if (!$puedeEditarMenuOps) {
            $mensaje = 'Acceso denegado: la visibilidad del menú de Operaciones solo la administran admins de Operaciones o Desarrollo.';
            $mensajeEsError = true;
        } else {
            $nueva = [];
            foreach (MENU_OPERACIONES_NODOS as $nodo => $info) {
                $nueva[$nodo] = (array)($_POST['menu_ops'][$nodo] ?? []);
            }
            if (!menuOperacionesGuardar($nueva)) $fallosGuardado[] = basename(menuOperacionesRuta());

            // Destinatarios del aviso semanal de formatos. Los cargos que este
            // admin no ve (de otra área grande) se conservan tal como estaban.
            $avisoActual = avisosOperacionesConfig();
            $cargosPost = array_values(array_intersect((array)($_POST['aviso_cargos'] ?? []), $cargosOperaciones));
            $cargosOcultos = array_values(array_diff($avisoActual['cargos'], $cargosOperaciones));
            $avisoOk = avisosOperacionesGuardar([
                'areas'  => (array)($_POST['aviso_areas'] ?? []),
                'cargos' => array_merge($cargosOcultos, $cargosPost),
            ]);
            if (!$avisoOk) $fallosGuardado[] = basename(avisosOperacionesRuta());

            $mensaje = 'Visibilidad del menú de Operaciones y destinatarios del aviso semanal actualizados. Los usuarios verán el menú actualizado al volver a abrirlo (los administradores siempre ven todo).';
        }
    } elseif (isset($_POST['areas'])) {
        foreach ($_POST['areas'] as $cargo => $area) {
            if (array_key_exists($cargo, $cargo_areas) && in_array($cargo, $cargosVisibles, true) && in_array($area, $areas_validas, true)) {
                $cargo_areas[$cargo] = $area;
            }
        }
        ksort($cargo_areas);
        guardarJsonAdmin($cargo_areas_file, $cargo_areas);
        $mensaje = 'Áreas operativas actualizadas con éxito. Esto define qué botones ve cada cargo en el menú de Operaciones (ver "Visibilidad del Menú de Operaciones").';
    } elseif (isset($_POST['crear_cargo'])) {
        $nuevoCargo = trim($_POST['nuevo_cargo'] ?? '');
        $nuevoRol   = $_POST['nuevo_rol'] ?? '';

        $yaExiste = false;
        foreach (array_keys($cargo_roles) as $cargoExistente) {
            if (mb_strtolower($cargoExistente) === mb_strtolower($nuevoCargo)) {
                $yaExiste = true;
                break;
            }
        }

        if ($nuevoCargo === '') {
            $mensaje = 'El nombre del cargo no puede estar vacío.';
            $mensajeEsError = true;
        } elseif ($yaExiste) {
            $mensaje = 'Ya existe un cargo llamado "' . htmlspecialchars($nuevoCargo) . '". Usa la tabla de abajo para cambiarle el rol.';
            $mensajeEsError = true;
        } elseif (!in_array($nuevoRol, ['1', '2'], true)) {
            $mensaje = 'Rol inválido para el nuevo cargo.';
            $mensajeEsError = true;
        } else {
            $cargo_roles[$nuevoCargo] = $nuevoRol;
            $cargo_areas[$nuevoCargo] = 'sin_asignar';
            ksort($cargo_areas);
            guardarJsonAdmin($cargo_areas_file, $cargo_areas);

            // El cargo nuevo queda asignado de una vez a la propia área
            // grande de quien lo crea (si es Desarrollo, a Desarrollo), para
            // que no desaparezca "sin_asignar" de la vista de su creador.
            $cargo_area_grande[$nuevoCargo] = $miAreaGrande !== '' ? $miAreaGrande : 'sin_asignar';
            ksort($cargo_area_grande);
            guardarJsonAdmin($cargo_area_grande_file, $cargo_area_grande);

            $rolLabelNuevo = $nuevoRol === '2' ? 'ROL 2 · INTERMEDIO' : 'ROL 1 · ALTO';
            $mensaje = 'Cargo "' . htmlspecialchars($nuevoCargo) . '" creado con ' . $rolLabelNuevo . '. Ya está disponible en el formulario de registro.';
        }
    } elseif (isset($_POST['otorgar_adm']) && array_key_exists($_POST['otorgar_adm'], $cargo_roles) && in_array($_POST['otorgar_adm'], $cargosVisibles, true)) {
        $cargoObjetivo = $_POST['otorgar_adm'];
        avisarCambioRolCargo($pdoUsuarios, $cargoObjetivo, $cargo_roles[$cargoObjetivo], 'adm');
        $cargo_roles[$cargoObjetivo] = 'adm';
        $mensaje = 'Rol ADM otorgado al cargo "' . htmlspecialchars($cargoObjetivo) . '". Cualquier usuario que se registre con este cargo será superadministrador.';
    } elseif (isset($_POST['revocar_adm']) && array_key_exists($_POST['revocar_adm'], $cargo_roles) && in_array($_POST['revocar_adm'], $cargosVisibles, true)) {
        $cargoObjetivo = $_POST['revocar_adm'];
        avisarCambioRolCargo($pdoUsuarios, $cargoObjetivo, $cargo_roles[$cargoObjetivo], '1');
        $cargo_roles[$cargoObjetivo] = '1';
        $mensaje = 'Rol ADM revocado del cargo "' . htmlspecialchars($cargoObjetivo) . '". Se asignó ROL 1 por defecto.';
    } elseif (isset($_POST['roles']) || isset($_POST['sincronizar_bd'])) {
        // El botón "Actualizar Base de Datos" vive en el MISMO <form> que la
        // tabla de roles, así que su POST también trae roles[...]. Antes esta
        // rama se evaluaba primero y la de sincronizar_bd (un elseif más
        // abajo) nunca se alcanzaba: se guardaba el mapeo pero la BD no se
        // tocaba. Ahora se guardan los selectores y, si se pidió, se
        // sincroniza con el mapeo ya actualizado.
        foreach ($_POST['roles'] ?? [] as $cargo => $rol) {
            if (array_key_exists($cargo, $cargo_roles) && in_array($cargo, $cargosVisibles, true) && in_array($rol, ['1', '2'], true)) {
                avisarCambioRolCargo($pdoUsuarios, $cargo, $cargo_roles[$cargo], $rol);
                $cargo_roles[$cargo] = $rol;
            }
        }
        $mensaje = 'Roles actualizados con éxito. Los nuevos registros usarán esta asignación automáticamente.';

        if (isset($_POST['sincronizar_bd'])) {
            // Aplica el mapeo cargo->rol actual a los usuarios YA existentes en la BD.
            $totalActualizados = 0;
            $detalle = [];
            foreach ($cargo_roles as $cargo => $rolCargo) {
                if (!in_array($cargo, $cargosVisibles, true)) continue;
                // Se leen antes quiénes van a cambiar (y con qué rol venían)
                // para poder avisarle a cada uno en su bandeja de entrada.
                $stmtAfectados = $pdoUsuarios->prepare("SELECT id_usuario, rol FROM usuarios WHERE Cargo = :cargo AND rol != :rolActual");
                $stmtAfectados->execute([':cargo' => $cargo, ':rolActual' => $rolCargo]);
                $afectadosLista = $stmtAfectados->fetchAll(PDO::FETCH_ASSOC);
                if (!$afectadosLista) continue;

                $stmtSync = $pdoUsuarios->prepare("UPDATE usuarios SET rol = :rolNuevo WHERE Cargo = :cargo AND rol != :rolActual");
                $stmtSync->bindValue(':rolNuevo', $rolCargo);
                $stmtSync->bindValue(':cargo', $cargo);
                $stmtSync->bindValue(':rolActual', $rolCargo);
                $stmtSync->execute();
                $afectados = $stmtSync->rowCount();
                if ($afectados > 0) {
                    $totalActualizados += $afectados;
                    $detalle[] = htmlspecialchars($cargo) . " ($afectados)";
                }

                foreach ($afectadosLista as $u) {
                    try {
                        crearNotificacion(
                            $u['id_usuario'],
                            'Tu rol fue actualizado',
                            'Tu cuenta pasó de ' . etiquetaRol((string)$u['rol']) . ' a ' . etiquetaRol($rolCargo) . ' (cargo "' . $cargo . '"). '
                                . 'Cierra sesión y vuelve a ingresar para que los nuevos permisos se apliquen.',
                            'exito',
                            'Administración',
                            '/template/menu_usuario.html'
                        );
                    } catch (Throwable $e) {
                        error_log('notificación de sincronización de rol: ' . $e->getMessage());
                    }
                }
            }
            $mensaje = $totalActualizados > 0
                ? "Base de datos sincronizada: $totalActualizados usuario(s) actualizado(s). Detalle: " . implode(', ', $detalle) . '.'
                : 'Base de datos sincronizada: ningún usuario existente tenía un rol distinto al configurado.';
        }
    } elseif (isset($_POST['areas_grandes'])) {
        // Doble candado: aunque el formulario solo se muestra a admins del
        // área Desarrollo, se revalida aquí por si alguien arma el POST a
        // mano — el bloqueo de verdad es este, no el que oculta el HTML.
        if (($_SESSION['area'] ?? '') !== 'Desarrollo') {
            $mensaje = 'Acceso denegado: esta configuración es exclusiva de administradores del área Desarrollo.';
            $mensajeEsError = true;
        } else {
            foreach ($_POST['areas_grandes'] as $cargo => $areaGrande) {
                if (array_key_exists($cargo, $cargo_area_grande) && in_array($areaGrande, $areas_grandes_validas, true)) {
                    $cargo_area_grande[$cargo] = $areaGrande;
                }
            }
            ksort($cargo_area_grande);
            guardarJsonAdmin($cargo_area_grande_file, $cargo_area_grande);
            $mensaje = 'Áreas (Operaciones/Calidad/HSEQ/Desarrollo) actualizadas con éxito.';
        }
    }
    ksort($cargo_roles);
    guardarJsonAdmin($cargo_roles_file, $cargo_roles);

    if ($fallosGuardado) {
        $mensaje = 'No se pudieron guardar los cambios: el servidor no tiene permiso de escritura sobre '
            . htmlspecialchars(implode(', ', array_unique($fallosGuardado)))
            . ' (en archivos/generados/admin/). Pide a sistemas que revise los permisos de ese archivo.';
        $mensajeEsError = true;
    }
}

ksort($cargo_roles);
ksort($cargo_areas);
ksort($cargo_area_grande);

$rol_labels = [
    '1' => 'ROL 1 · ALTO',
    '2' => 'ROL 2 · INTERMEDIO',
];

$area_labels = [
    'sin_asignar'    => 'SIN ASIGNAR',
    'administracion' => 'ADMINISTRACIÓN',
    'almacen'        => 'ALMACÉN',
    'mantenimiento'  => 'MANTENIMIENTO',
    'produccion'     => 'PRODUCCIÓN',
];

$area_grande_labels = [
    'sin_asignar' => 'SIN ASIGNAR',
    'Operaciones' => 'OPERACIONES',
    'Calidad'     => 'CALIDAD',
    'HSEQ'        => 'HSEQ',
    'Desarrollo'  => 'DESARROLLO',
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Cargos y Roles</title>
    <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700&family=Space+Mono:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #0B0E14;
            --panel-bg: #151A22;
            --accent: #FF3366;
            --accent-glow: rgba(255, 51, 102, 0.4);
            --text-main: #E2E8F0;
            --text-muted: #94A3B8;
            --border-color: #1E293B;
            --input-bg: #0F172A;
            --danger: #FF3366;
            --warning: #FFB000;
            --r-lg: 12px;
            --r-md: 8px;
            --r-sm: 4px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Barlow', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            min-height: 100vh;
            padding: 40px 20px;
            background-image:
                linear-gradient(rgba(255, 51, 102, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 51, 102, 0.03) 1px, transparent 1px);
            background-size: 30px 30px;
        }

        .container { max-width: 1000px; margin: 0 auto; }

        .header-box {
            background: var(--panel-bg);
            border: 1px solid var(--border-color);
            border-left: 4px solid var(--accent);
            padding: 30px;
            border-radius: var(--r-md);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            position: relative;
            overflow: hidden;
        }

        .header-box::before {
            content: "ROOT PRIVILEGES";
            position: absolute; top: -10px; right: 20px;
            background: var(--accent); color: #fff;
            font-family: 'Space Mono', monospace; font-size: 10px; font-weight: 700;
            padding: 4px 12px; border-radius: var(--r-sm);
            box-shadow: 0 0 10px var(--accent-glow);
        }

        .main-title { font-size: 24px; font-weight: 700; color: #fff; text-transform: uppercase; margin-bottom: 4px; letter-spacing: 1px; }
        .sub-title { color: var(--text-muted); font-size: 14px; }

        .btn-back {
            background: transparent; border: 1px solid var(--text-muted); color: var(--text-main);
            padding: 10px 18px; border-radius: var(--r-sm); font-family: 'Space Mono', monospace;
            text-decoration: none; font-size: 12px; transition: all 0.3s; white-space: nowrap;
        }
        .btn-back:hover { border-color: var(--accent); color: var(--accent); background: rgba(255, 51, 102, 0.05); }

        .sys-msg {
            background: rgba(16, 185, 129, 0.1); border: 1px solid #10B981; color: #10B981;
            padding: 15px; border-radius: var(--r-md); margin-bottom: 25px; font-weight: bold;
            font-family: 'Space Mono', monospace; font-size: 13px; text-align: center;
        }
        .sys-msg.sys-msg--error {
            background: rgba(255, 51, 102, 0.1); border-color: var(--danger); color: var(--danger);
        }

        .form-row { display: flex; gap: 14px; flex-wrap: wrap; align-items: flex-end; }
        .form-row .field { flex: 1; min-width: 220px; display: flex; flex-direction: column; gap: 6px; }
        .form-row label {
            font-size: 11px; color: var(--text-muted); text-transform: uppercase;
            letter-spacing: 0.5px; font-family: 'Space Mono', monospace;
        }
        .form-row input[type="text"] {
            background: var(--input-bg); border: 1px solid var(--border-color); color: var(--text-main);
            padding: 10px 12px; border-radius: var(--r-sm); font-family: 'Barlow', sans-serif; font-size: 14px;
        }
        .form-row input[type="text"]:focus {
            outline: none; border-color: var(--accent); box-shadow: 0 0 0 3px rgba(255, 51, 102, 0.1);
        }
        .btn-crear-cargo {
            background: var(--accent); color: #fff; border: none; padding: 11px 22px;
            font-family: 'Space Mono', monospace; font-size: 12px; font-weight: 700; text-transform: uppercase;
            border-radius: var(--r-sm); cursor: pointer; white-space: nowrap; transition: all 0.3s;
            box-shadow: 0 0 15px var(--accent-glow);
        }
        .btn-crear-cargo:hover { background: #fff; color: var(--bg-color); box-shadow: 0 0 25px rgba(255,255,255,0.6); }

        .section-card {
            background: var(--panel-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--r-md);
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }

        .section-title {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 16px;
            font-weight: 600;
            color: var(--accent);
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .section-desc {
            color: var(--text-muted);
            font-size: 13px;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px dashed var(--border-color);
        }

        table { width: 100%; border-collapse: collapse; }

        thead th {
            text-align: left;
            font-family: 'Space Mono', monospace;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-muted);
            padding: 10px 12px;
            border-bottom: 1px solid var(--border-color);
        }

        tbody td {
            padding: 10px 12px;
            border-bottom: 1px solid rgba(255,255,255,0.03);
            font-size: 14px;
        }

        tbody tr:hover { background: rgba(255, 51, 102, 0.03); }

        .badge-adm {
            display: inline-block;
            font-family: 'Space Mono', monospace;
            font-size: 12px;
            font-weight: 700;
            color: var(--warning);
            background: rgba(255, 176, 0, 0.1);
            border: 1px solid var(--warning);
            padding: 6px 10px;
            border-radius: var(--r-sm);
            letter-spacing: 0.5px;
        }

        .badge-scope {
            display: inline-block;
            font-family: 'Space Mono', monospace;
            font-size: 10px;
            font-weight: 700;
            color: var(--text-muted);
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--border-color);
            padding: 3px 9px;
            border-radius: var(--r-sm);
            letter-spacing: 0.5px;
            text-transform: none;
        }

        .empty-row {
            text-align: center;
            color: var(--text-muted);
            font-size: 13px;
            padding: 25px 12px !important;
            font-style: italic;
        }

        /* Sección exclusiva de admins del área Desarrollo */
        .tabla-visibilidad .col-check { text-align: center; }
        .aviso-bloque { margin-top: 26px; padding-top: 20px; border-top: 1px dashed rgba(255,255,255,0.12); }
        .aviso-bloque-title { font-size: 14px; font-weight: 700; margin-bottom: 8px; }
        .aviso-cargos-title { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; opacity: 0.7; margin: 18px 0 10px; }
        .aviso-cargos { display: grid; grid-template-columns: repeat(auto-fill, minmax(230px, 1fr)); gap: 6px 14px; }
        .aviso-cargo { display: flex; align-items: center; gap: 8px; font-size: 13px; cursor: pointer; }
        .aviso-cargo small { display: block; font-size: 10px; opacity: 0.55; letter-spacing: 0.03em; }
        .aviso-resumen { margin-top: 16px; padding: 10px 14px; border-radius: 6px; font-size: 12.5px; line-height: 1.5; border: 1px solid rgba(16,185,129,0.35); }
        .aviso-resumen--vacio { border-color: rgba(242,177,52,0.5); color: #f2b134; }
        .tabla-visibilidad th.col-check { white-space: nowrap; }
        .tabla-visibilidad .col-count {
            display: block; margin-top: 3px;
            font-size: 10px; font-weight: 500; letter-spacing: 0.02em; opacity: 0.6; text-transform: none;
        }
        .check-visibilidad { width: 18px; height: 18px; cursor: pointer; accent-color: #2563eb; }
        .check-visibilidad:disabled { cursor: not-allowed; opacity: 0.55; }
        .badge-fijo {
            display: inline-block; margin-left: 6px; padding: 1px 7px; border-radius: 999px;
            font-size: 9.5px; font-weight: 700; letter-spacing: 0.04em;
            border: 1px solid currentColor; opacity: 0.6;
        }

        .section-card--dev {
            border-color: #7C3AED;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3), 0 0 0 1px rgba(124, 58, 237, 0.15) inset;
        }
        .section-card--dev .section-title { color: #A78BFA; }
        .badge-dev {
            display: inline-block;
            font-family: 'Space Mono', monospace;
            font-size: 11px;
            font-weight: 700;
            color: #A78BFA;
            background: rgba(124, 58, 237, 0.12);
            border: 1px solid #7C3AED;
            padding: 4px 10px;
            border-radius: var(--r-sm);
            letter-spacing: 0.5px;
            text-transform: none;
            margin-left: auto;
        }

        .btn-action {
            font-family: 'Space Mono', monospace;
            font-size: 11px;
            font-weight: 700;
            padding: 8px 12px;
            border-radius: var(--r-sm);
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.2s;
            text-transform: uppercase;
        }

        .btn-grant-adm {
            background: transparent;
            border: 1px solid var(--warning);
            color: var(--warning);
        }
        .btn-grant-adm:hover { background: var(--warning); color: var(--bg-color); }

        .btn-revoke-adm {
            background: transparent;
            border: 1px solid var(--danger);
            color: var(--danger);
        }
        .btn-revoke-adm:hover { background: var(--danger); color: #fff; }

        select.form-control {
            background: var(--input-bg);
            border: 1px solid var(--border-color);
            color: var(--text-main);
            padding: 8px 10px;
            border-radius: var(--r-sm);
            font-family: 'Space Mono', monospace;
            font-size: 13px;
            width: 100%;
            max-width: 220px;
            cursor: pointer;
        }

        select.form-control:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(255, 51, 102, 0.1);
        }

        .btn-submit {
            background: var(--accent);
            color: #fff;
            border: none;
            padding: 16px 30px;
            font-size: 15px;
            font-weight: 700;
            font-family: 'Space Mono', monospace;
            border-radius: var(--r-sm);
            cursor: pointer;
            width: 100%;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            margin-top: 20px;
            box-shadow: 0 0 15px var(--accent-glow);
        }

        .btn-submit:hover { background: #fff; color: var(--bg-color); box-shadow: 0 0 25px rgba(255, 255, 255, 0.6); }

        .btn-sync {
            background: var(--warning);
            box-shadow: 0 0 15px rgba(255, 176, 0, 0.4);
        }
        .btn-sync:hover { background: #fff; box-shadow: 0 0 25px rgba(255, 255, 255, 0.6); }

        .system-status {
            font-family: 'Space Mono', monospace;
            font-size: 12px;
            color: var(--text-muted);
            text-align: center;
            margin-top: 30px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
        }

        .status-dot {
            width: 8px; height: 8px; background: #10B981; border-radius: 50%;
            box-shadow: 0 0 8px #10B981; animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(16, 185, 129, 0); }
            100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }

        @media (max-width: 600px) {
            .header-box { flex-direction: column; align-items: flex-start; gap: 15px; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header-box">
        <div>
            <h1 class="main-title">Panel de Administración</h1>
            <div class="sub-title">Gestión de Cargos y Roles del Sistema</div>
        </div>
        <a href="<?= htmlspecialchars($volverMenuUrl) ?>" class="btn-back">← VOLVER AL MENÚ</a>
    </div>

    <?php if ($mensaje): ?>
        <div class="sys-msg<?= $mensajeEsError ? ' sys-msg--error' : '' ?>"><?= $mensaje ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="section-card">
            <div class="section-title">Crear Nuevo Cargo</div>
            <div class="section-desc">
                Crea un cargo que todavía no existe y asígnale su rol de una vez. Queda disponible de
                inmediato en el <strong>select de Cargo</strong> del formulario de registro.
            </div>

            <div class="form-row">
                <div class="field">
                    <label for="nuevo_cargo">Nombre del cargo</label>
                    <input type="text" id="nuevo_cargo" name="nuevo_cargo" placeholder="Ej: Supervisor de Calidad" required maxlength="100">
                </div>
                <div class="field">
                    <label for="nuevo_rol">Rol asignado</label>
                    <select id="nuevo_rol" name="nuevo_rol" class="form-control">
                        <?php foreach ($rol_labels as $valor => $etiqueta): ?>
                        <option value="<?= $valor ?>"><?= $etiqueta ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" name="crear_cargo" value="1" class="btn-crear-cargo">➕ Crear Cargo</button>
            </div>
        </div>
    </form>

    <form method="post">
        <div class="section-card">
            <div class="section-title">
                Cargos y Roles
                <?php if (!$esAdminDesarrollo): ?><span class="badge-scope"><?= htmlspecialchars($miAreaGrande ?: 'SIN ÁREA') ?></span><?php endif; ?>
            </div>
            <div class="section-desc">
                Cada cargo tiene asignado un rol. Cuando un usuario se registra y selecciona su cargo,
                el sistema le asigna automáticamente el rol configurado aquí — ya no se elige manualmente en el registro.
                <?php if (!$esAdminDesarrollo): ?>
                    Solo ves los cargos asignados al área <strong><?= htmlspecialchars($miAreaGrande ?: 'sin definir') ?></strong>
                    (esa asignación la controla Desarrollo).
                <?php endif; ?>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Cargo</th>
                        <th>Rol asignado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cargo_roles as $cargo => $rol):
                        if (!in_array($cargo, $cargosVisibles, true)) continue;
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($cargo) ?></td>
                        <td>
                            <?php if ($rol === 'adm'): ?>
                                <span class="badge-adm">★ ADM · SUPERADMINISTRADOR</span>
                            <?php else: ?>
                                <select name="roles[<?= htmlspecialchars($cargo) ?>]" class="form-control">
                                    <?php foreach ($rol_labels as $valor => $etiqueta): ?>
                                    <option value="<?= $valor ?>" <?= $rol === $valor ? 'selected' : '' ?>><?= $etiqueta ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($rol === 'adm'): ?>
                                <button type="submit" name="revocar_adm" value="<?= htmlspecialchars($cargo) ?>" class="btn-action btn-revoke-adm"
                                    onclick="return confirm('¿Revocar el rol ADM del cargo &quot;<?= htmlspecialchars($cargo) ?>&quot;? Quedará en ROL 1 por defecto.');">
                                    Revocar ADM
                                </button>
                            <?php else: ?>
                                <button type="submit" name="otorgar_adm" value="<?= htmlspecialchars($cargo) ?>" class="btn-action btn-grant-adm"
                                    onclick="return confirm('¿Otorgar rol ADM (superadministrador) al cargo &quot;<?= htmlspecialchars($cargo) ?>&quot;?\n\nCualquier usuario que se registre con este cargo será superadministrador automáticamente.');">
                                    Otorgar ADM
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($cargosVisibles)): ?>
                    <tr><td colspan="3" class="empty-row">Todavía no hay cargos asignados al área <?= htmlspecialchars($miAreaGrande ?: 'sin definir') ?>. Pídele a Desarrollo que los clasifique.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <button type="submit" class="btn-submit">Guardar Cambios</button>
        </div>

        <div class="section-card">
            <div class="section-title">
                Sincronización con Base de Datos
                <?php if (!$esAdminDesarrollo): ?><span class="badge-scope"><?= htmlspecialchars($miAreaGrande ?: 'SIN ÁREA') ?></span><?php endif; ?>
            </div>
            <div class="section-desc">
                Esta acción recorre la tabla <strong>usuarios</strong> y actualiza el <strong>rol</strong> de todos los
                usuarios ya registrados para que coincida con el mapeo cargo → rol configurado arriba.
                Los usuarios cuyo rol ya coincide no se tocan.
                <?php if (!$esAdminDesarrollo): ?>
                    Solo afecta a usuarios cuyo cargo pertenece al área <strong><?= htmlspecialchars($miAreaGrande ?: 'sin definir') ?></strong>.
                <?php endif; ?>
            </div>
            <button type="submit" name="sincronizar_bd" value="1" class="btn-submit btn-sync"
                onclick="return confirm('¿Actualizar la base de datos ahora?\n\nSe sobreescribirá el rol de TODOS los usuarios existentes cuyo cargo tenga un rol distinto al configurado en esta pantalla. Esta acción no se puede deshacer automáticamente.');">
                Actualizar Base de Datos
            </button>
        </div>
    </form>

    <form method="post">
        <div class="section-card">
            <div class="section-title">
                Cargos y Áreas Operativas
                <?php if (!$esAdminDesarrollo): ?><span class="badge-scope"><?= htmlspecialchars($miAreaGrande ?: 'SIN ÁREA') ?></span><?php endif; ?>
            </div>
            <div class="section-desc">
                Asigna cada cargo a su área operativa (Mantenimiento, Producción, Almacén o Administración).
                Esta asignación <strong>decide qué botones ve cada cargo</strong> en el menú principal de Operaciones,
                según la tabla "Visibilidad del Menú de Operaciones" de abajo. Los administradores siempre ven todo.
                <?php if (!$esAdminDesarrollo): ?>
                    Solo ves los cargos asignados al área <strong><?= htmlspecialchars($miAreaGrande ?: 'sin definir') ?></strong>.
                <?php endif; ?>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Cargo</th>
                        <th>Área asignada</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cargo_areas as $cargo => $area):
                        if (!in_array($cargo, $cargosVisibles, true)) continue;
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($cargo) ?></td>
                        <td>
                            <select name="areas[<?= htmlspecialchars($cargo) ?>]" class="form-control">
                                <?php foreach ($area_labels as $valor => $etiqueta): ?>
                                <option value="<?= $valor ?>" <?= $area === $valor ? 'selected' : '' ?>><?= $etiqueta ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($cargosVisibles)): ?>
                    <tr><td colspan="2" class="empty-row">Todavía no hay cargos asignados al área <?= htmlspecialchars($miAreaGrande ?: 'sin definir') ?>. Pídele a Desarrollo que los clasifique.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <button type="submit" class="btn-submit">Guardar Cambios</button>
        </div>
    </form>

    <?php if ($puedeEditarMenuOps):
        $menuOpsConfig = menuOperacionesConfig();
        // Cargos (visibles para este admin) agrupados por área operativa, para
        // mostrar debajo de cada columna a quién afecta.
        $cargosPorArea = array_fill_keys(MENU_OPERACIONES_AREAS, []);
        foreach ($cargo_areas as $cargoTmp => $areaTmp) {
            if (in_array($cargoTmp, $cargosOperaciones, true) && isset($cargosPorArea[$areaTmp])) {
                $cargosPorArea[$areaTmp][] = $cargoTmp;
            }
        }
    ?>
    <form method="post">
        <input type="hidden" name="menu_operaciones" value="1">
        <div class="section-card">
            <div class="section-title">Visibilidad del Menú de Operaciones</div>
            <div class="section-desc">
                Marca qué áreas operativas ven cada botón del menú principal de Operaciones
                (<strong>menu_adm.html</strong>). El área de cada usuario sale de su cargo, según la tabla
                "Cargos y Áreas Operativas" de arriba. Los usuarios con rol <strong>ADM</strong> ven siempre todos los
                botones. <strong>Usuario</strong> es fijo para todos porque es el acceso a la bandeja de entrada.
            </div>

            <table class="tabla-visibilidad">
                <thead>
                    <tr>
                        <th>Botón</th>
                        <?php foreach (MENU_OPERACIONES_AREAS as $areaCol): ?>
                        <th class="col-check" title="<?= htmlspecialchars(implode(', ', $cargosPorArea[$areaCol]) ?: 'Ningún cargo') ?>">
                            <?= $area_labels[$areaCol] ?>
                            <span class="col-count"><?= count($cargosPorArea[$areaCol]) ?> cargo(s)</span>
                        </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (MENU_OPERACIONES_NODOS as $nodo => $info): ?>
                    <tr>
                        <td><?= htmlspecialchars($info['label']) ?><?= $info['fijo'] ? ' <span class="badge-fijo">FIJO</span>' : '' ?></td>
                        <?php foreach (MENU_OPERACIONES_AREAS as $areaCol): ?>
                        <td class="col-check">
                            <input type="checkbox" class="check-visibilidad"
                                name="menu_ops[<?= $nodo ?>][]" value="<?= $areaCol ?>"
                                <?= in_array($areaCol, $menuOpsConfig[$nodo], true) ? 'checked' : '' ?>
                                <?= $info['fijo'] ? 'disabled' : '' ?>>
                        </td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php
                $avisoCfg = avisosOperacionesConfig();
                $avisoCargosDestino = avisosCargosDestino();
            ?>
            <div class="aviso-bloque">
                <div class="aviso-bloque-title">🔔 Destinatarios del aviso semanal de formatos</div>
                <div class="section-desc">
                    Cada domingo el sistema revisa la semana anterior del <strong>Cronograma de Producción</strong> y avisa en la
                    bandeja de entrada qué formatos de <strong>línea de envasado</strong> y <strong>control de empaque</strong> no se
                    registraron. Elige quién lo recibe: por área operativa, por cargo, o ambos. Cada sede avisa solo a los usuarios
                    de esa misma sede.
                </div>

                <table class="tabla-visibilidad">
                    <thead>
                        <tr>
                            <th>Por área operativa</th>
                            <?php foreach (MENU_OPERACIONES_AREAS as $areaCol): ?>
                            <th class="col-check"><?= $area_labels[$areaCol] ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Recibe el aviso</td>
                            <?php foreach (MENU_OPERACIONES_AREAS as $areaCol): ?>
                            <td class="col-check">
                                <input type="checkbox" class="check-visibilidad" name="aviso_areas[]" value="<?= $areaCol ?>"
                                    <?= in_array($areaCol, $avisoCfg['areas'], true) ? 'checked' : '' ?>>
                            </td>
                            <?php endforeach; ?>
                        </tr>
                    </tbody>
                </table>

                <div class="aviso-cargos-title">Por cargo (además de las áreas marcadas) — solo cargos del área Operaciones</div>
                <?php if (!$cargosOperaciones): ?>
                <div class="section-desc">No hay cargos asignados al área Operaciones en "Cargos y Áreas del Sistema".</div>
                <?php endif; ?>
                <div class="aviso-cargos">
                    <?php foreach ($cargo_areas as $cargoTmp => $areaTmp):
                        if (!in_array($cargoTmp, $cargosOperaciones, true)) continue; ?>
                    <label class="aviso-cargo">
                        <input type="checkbox" class="check-visibilidad" name="aviso_cargos[]" value="<?= htmlspecialchars($cargoTmp) ?>"
                            <?= in_array($cargoTmp, $avisoCfg['cargos'], true) ? 'checked' : '' ?>>
                        <span><?= htmlspecialchars($cargoTmp) ?> <small><?= $area_labels[$areaTmp] ?? '' ?><?= ($cargo_roles[$cargoTmp] ?? '') === 'adm' ? ' · ADM' : '' ?></small></span>
                    </label>
                    <?php endforeach; ?>
                </div>

                <div class="aviso-resumen<?= $avisoCargosDestino ? '' : ' aviso-resumen--vacio' ?>">
                    <?php if ($avisoCargosDestino): ?>
                        Hoy lo reciben los usuarios con cargo: <strong><?= htmlspecialchars(implode(', ', $avisoCargosDestino)) ?></strong>.
                    <?php else: ?>
                        ⚠ Nadie recibe el aviso todavía: marca al menos un área o un cargo.
                    <?php endif; ?>
                </div>
            </div>

            <button type="submit" class="btn-submit">Guardar Visibilidad y Destinatarios</button>
        </div>
    </form>
    <?php endif; ?>

    <?php if ($esAdminDesarrollo): ?>
    <form method="post">
        <div class="section-card section-card--dev">
            <div class="section-title">
                Cargos y Áreas del Sistema
                <span class="badge-dev">🔒 SOLO DESARROLLO</span>
            </div>
            <div class="section-desc">
                Asigna cada cargo a su área grande del sistema (<strong>Operaciones, Calidad, HSEQ o Desarrollo</strong>
                — las mismas que deciden el menú de entrada al iniciar sesión). No confundir con "Cargos y Áreas
                Operativas" de arriba, que son las miniáreas internas de Operaciones (Mantenimiento, Almacén, etc.).
                Esta sección solo la ven administradores del área Desarrollo.
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Cargo</th>
                        <th>Área del sistema</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cargo_area_grande as $cargo => $areaGrande): ?>
                    <tr>
                        <td><?= htmlspecialchars($cargo) ?></td>
                        <td>
                            <select name="areas_grandes[<?= htmlspecialchars($cargo) ?>]" class="form-control">
                                <?php foreach ($area_grande_labels as $valor => $etiqueta): ?>
                                <option value="<?= $valor ?>" <?= $areaGrande === $valor ? 'selected' : '' ?>><?= $etiqueta ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <button type="submit" class="btn-submit">Guardar Cambios</button>
        </div>
    </form>
    <?php endif; ?>

    <div class="system-status">
        <div class="status-dot"></div>
        SISTEMA JSON INTERCONECTADO - CARGO_ROLES.JSON + CARGO_AREAS.JSON + MENU_OPERACIONES.JSON
    </div>
</div>

</body>
</html>
