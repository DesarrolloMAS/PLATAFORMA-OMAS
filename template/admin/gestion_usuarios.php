<?php
require '../sesion.php';

// Acceso exclusivo para el rol literal 'adm' (superadministrador), mismo
// criterio que admin/menu_admin.php.
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'adm') {
    die("Acceso Denegado. Este panel es exclusivo para administradores del sistema.");
}

// Compartido entre áreas (se llega a él desde el engranaje de
// menu_administracion.html, mismo que admin/menu_admin.php), así que
// "volver" debe respetar de qué área vino el admin.
switch ($_SESSION['area'] ?? '') {
    case 'Calidad':
        $volverMenuUrl = '../menu_administracion_calidad.html';
        break;
    case 'HSEQ':
        $volverMenuUrl = '../panel_administracion_hseq.html';
        break;
    default:
        $volverMenuUrl = '../menu_administracion.html';
        break;
}

require '../conection.php'; // $pdoUsuarios

// Cada admin solo ve y edita usuarios de su propia área (Operaciones,
// Calidad, HSEQ, ...) — no de las otras. Se filtra tanto la consulta como
// la actualización, para que un POST manipulado a mano tampoco pueda tocar
// usuarios de otra área.
$areaAdmin = $_SESSION['area'] ?? '';

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['usuarios'])) {
    // Edición de nombre y cédula por usuario individual. La cédula es hoy el
    // dato que funciona como "contraseña" para iniciar sesión (ver
    // validarUsuario() en /registro.php), así que se valida que no quede
    // duplicada entre usuarios antes de guardar.
    $actualizados = 0;
    $erroresUsuarios = [];
    foreach ($_POST['usuarios'] as $idUsuario => $datos) {
        $nombreNuevo = trim($datos['nombre'] ?? '');
        $cedulaNueva = trim($datos['cedula'] ?? '');
        if ($nombreNuevo === '' || $cedulaNueva === '') {
            continue;
        }

        $stmtCheck = $pdoUsuarios->prepare("SELECT id_usuario FROM usuarios WHERE cedula_u = :cedula AND id_usuario != :id");
        $stmtCheck->bindValue(':cedula', $cedulaNueva);
        $stmtCheck->bindValue(':id', $idUsuario, PDO::PARAM_INT);
        $stmtCheck->execute();
        if ($stmtCheck->rowCount() > 0) {
            $erroresUsuarios[] = 'La cédula "' . htmlspecialchars($cedulaNueva) . '" ya está en uso por otro usuario (no se actualizó "' . htmlspecialchars($nombreNuevo) . '").';
            continue;
        }

        $stmtUpd = $pdoUsuarios->prepare("UPDATE usuarios SET nombre_u = :nombre, cedula_u = :cedula WHERE id_usuario = :id AND Area = :area");
        $stmtUpd->bindValue(':nombre', $nombreNuevo);
        $stmtUpd->bindValue(':cedula', $cedulaNueva);
        $stmtUpd->bindValue(':id', $idUsuario, PDO::PARAM_INT);
        $stmtUpd->bindValue(':area', $areaAdmin);
        $stmtUpd->execute();
        if ($stmtUpd->rowCount() > 0) {
            $actualizados++;
        }
    }
    $mensaje = $actualizados > 0
        ? "$actualizados usuario(s) actualizado(s) con éxito."
        : 'No se detectaron cambios para guardar.';
    if (!empty($erroresUsuarios)) {
        $mensaje .= ' ' . implode(' ', $erroresUsuarios);
    }
}

$stmtUsuarios = $pdoUsuarios->prepare("SELECT id_usuario, nombre_u, cedula_u, Cargo, sede, rol, Area FROM usuarios WHERE Area = :area ORDER BY nombre_u");
$stmtUsuarios->bindValue(':area', $areaAdmin);
$stmtUsuarios->execute();
$usuarios = $stmtUsuarios->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Organización MAS</title>
    <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700&family=Space+Mono:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #0B0E14;
            --panel-bg: #151A22;
            --accent: #2563EB;
            --accent-glow: rgba(37, 99, 235, 0.4);
            --text-main: #E2E8F0;
            --text-muted: #94A3B8;
            --border-color: #1E293B;
            --input-bg: #0F172A;
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
                linear-gradient(rgba(37, 99, 235, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(37, 99, 235, 0.03) 1px, transparent 1px);
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
        .btn-back:hover { border-color: var(--accent); color: var(--accent); background: rgba(37, 99, 235, 0.05); }

        .sys-msg {
            background: rgba(16, 185, 129, 0.1); border: 1px solid #10B981; color: #10B981;
            padding: 15px; border-radius: var(--r-md); margin-bottom: 25px; font-weight: bold;
            font-family: 'Space Mono', monospace; font-size: 13px; text-align: center;
        }

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

        .table-wrap { overflow-x: auto; }

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
            white-space: nowrap;
        }

        tbody td {
            padding: 10px 12px;
            border-bottom: 1px solid rgba(255,255,255,0.03);
            font-size: 14px;
        }

        tbody tr:hover { background: rgba(37, 99, 235, 0.03); }

        .cell-static {
            font-family: 'Space Mono', monospace;
            font-size: 12px;
            color: var(--text-muted);
            white-space: nowrap;
        }

        input.form-control {
            background: var(--input-bg);
            border: 1px solid var(--border-color);
            color: var(--text-main);
            padding: 8px 10px;
            border-radius: var(--r-sm);
            font-family: 'Space Mono', monospace;
            font-size: 13px;
            width: 100%;
            min-width: 140px;
        }

        input.form-control:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
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
            <h1 class="main-title">Gestión de Usuarios</h1>
            <div class="sub-title">Consulta y corrección de nombre y cédula · Área: <?= htmlspecialchars($areaAdmin ?: 'Sin asignar') ?></div>
        </div>
        <a href="<?= htmlspecialchars($volverMenuUrl) ?>" class="btn-back">← VOLVER AL MENÚ</a>
    </div>

    <?php if ($mensaje): ?>
        <div class="sys-msg"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="section-card">
            <div class="section-title">Usuarios Registrados</div>
            <div class="section-desc">
                Consulta los usuarios existentes de tu área (<?= htmlspecialchars($areaAdmin ?: 'sin asignar') ?>) y
                corrige nombre o cédula cuando haya un error de registro. La cédula es hoy el dato que funciona como
                credencial de acceso (junto con nombre, cargo y sede) — editarla aquí cambia con qué cédula ese
                usuario inicia sesión. Cargo, sede, rol y área no se editan desde esta tabla todavía. Un
                administrador de otra área no ve ni puede modificar estos usuarios.
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Cédula</th>
                            <th>Cargo</th>
                            <th>Sede</th>
                            <th>Área</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($usuarios)): ?>
                        <tr><td colspan="5" class="cell-static">No hay usuarios registrados.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($usuarios as $u): ?>
                        <tr>
                            <td>
                                <input type="text" class="form-control"
                                    name="usuarios[<?= (int) $u['id_usuario'] ?>][nombre]"
                                    value="<?= htmlspecialchars($u['nombre_u']) ?>">
                            </td>
                            <td>
                                <input type="text" class="form-control"
                                    name="usuarios[<?= (int) $u['id_usuario'] ?>][cedula]"
                                    value="<?= htmlspecialchars($u['cedula_u']) ?>">
                            </td>
                            <td class="cell-static"><?= htmlspecialchars($u['Cargo']) ?></td>
                            <td class="cell-static"><?= htmlspecialchars($u['sede']) ?></td>
                            <td class="cell-static"><?= htmlspecialchars($u['Area']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <button type="submit" class="btn-submit">Guardar Cambios</button>
        </div>
    </form>

    <div class="system-status">
        <div class="status-dot"></div>
        SISTEMA JSON INTERCONECTADO - TABLA USUARIOS (BD)
    </div>
</div>

</body>
</html>
