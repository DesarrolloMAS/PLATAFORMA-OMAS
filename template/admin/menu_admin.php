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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['areas'])) {
        foreach ($_POST['areas'] as $cargo => $area) {
            if (array_key_exists($cargo, $cargo_areas) && in_array($cargo, $cargosVisibles, true) && in_array($area, $areas_validas, true)) {
                $cargo_areas[$cargo] = $area;
            }
        }
        ksort($cargo_areas);
        file_put_contents($cargo_areas_file, json_encode($cargo_areas, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $mensaje = 'Áreas operativas actualizadas con éxito. Esto todavía no restringe el acceso de nadie — es solo la asignación base para cuando se active el control de acceso por área.';
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
            file_put_contents($cargo_areas_file, json_encode($cargo_areas, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            // El cargo nuevo queda asignado de una vez a la propia área
            // grande de quien lo crea (si es Desarrollo, a Desarrollo), para
            // que no desaparezca "sin_asignar" de la vista de su creador.
            $cargo_area_grande[$nuevoCargo] = $miAreaGrande !== '' ? $miAreaGrande : 'sin_asignar';
            ksort($cargo_area_grande);
            file_put_contents($cargo_area_grande_file, json_encode($cargo_area_grande, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            $rolLabelNuevo = $nuevoRol === '2' ? 'ROL 2 · INTERMEDIO' : 'ROL 1 · ALTO';
            $mensaje = 'Cargo "' . htmlspecialchars($nuevoCargo) . '" creado con ' . $rolLabelNuevo . '. Ya está disponible en el formulario de registro.';
        }
    } elseif (isset($_POST['otorgar_adm']) && array_key_exists($_POST['otorgar_adm'], $cargo_roles) && in_array($_POST['otorgar_adm'], $cargosVisibles, true)) {
        $cargoObjetivo = $_POST['otorgar_adm'];
        $cargo_roles[$cargoObjetivo] = 'adm';
        $mensaje = 'Rol ADM otorgado al cargo "' . htmlspecialchars($cargoObjetivo) . '". Cualquier usuario que se registre con este cargo será superadministrador.';
    } elseif (isset($_POST['revocar_adm']) && array_key_exists($_POST['revocar_adm'], $cargo_roles) && in_array($_POST['revocar_adm'], $cargosVisibles, true)) {
        $cargoObjetivo = $_POST['revocar_adm'];
        $cargo_roles[$cargoObjetivo] = '1';
        $mensaje = 'Rol ADM revocado del cargo "' . htmlspecialchars($cargoObjetivo) . '". Se asignó ROL 1 por defecto.';
    } elseif (isset($_POST['roles'])) {
        foreach ($_POST['roles'] as $cargo => $rol) {
            if (array_key_exists($cargo, $cargo_roles) && in_array($cargo, $cargosVisibles, true) && in_array($rol, ['1', '2'], true)) {
                $cargo_roles[$cargo] = $rol;
            }
        }
        $mensaje = 'Roles actualizados con éxito. Los nuevos registros usarán esta asignación automáticamente.';
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
            file_put_contents($cargo_area_grande_file, json_encode($cargo_area_grande, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $mensaje = 'Áreas (Operaciones/Calidad/HSEQ/Desarrollo) actualizadas con éxito.';
        }
    } elseif (isset($_POST['sincronizar_bd'])) {
        // Aplica el mapeo cargo->rol actual a los usuarios YA existentes en la BD.
        $totalActualizados = 0;
        $detalle = [];
        foreach ($cargo_roles as $cargo => $rolCargo) {
            if (!in_array($cargo, $cargosVisibles, true)) continue;
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
        }
        $mensaje = $totalActualizados > 0
            ? "Base de datos sincronizada: $totalActualizados usuario(s) actualizado(s). Detalle: " . implode(', ', $detalle) . '.'
            : 'Base de datos sincronizada: ningún usuario existente tenía un rol distinto al configurado.';
    }
    ksort($cargo_roles);
    file_put_contents($cargo_roles_file, json_encode($cargo_roles, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
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
                Por ahora esto <strong>no restringe el acceso de nadie</strong> — es solo la asignación base
                que se va a usar más adelante para que cada área solo vea su propio menú.
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
        SISTEMA JSON INTERCONECTADO - CARGO_ROLES.JSON + CARGO_AREAS.JSON
    </div>
</div>

</body>
</html>
