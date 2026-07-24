<?php
require '../conection.php'; // Conexión a la base de datos
require '../sesion.php';
verificarAutenticacion();

// Obtener la zona desde la sesión del usuario
$zonaSeleccionada = $_SESSION['sede'];

// Obtener el último registro de turnos de la zona asignada al usuario
$query = "SELECT id_proceso, turn1, turn2, turn3 
          FROM control_molienda 
          WHERE zona = ? ORDER BY id_proceso DESC LIMIT 1";
$stmt = $pdoControl->prepare($query);
$stmt->execute([$zonaSeleccionada]);
$turnos = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$turnos) {
    // Si no hay registros en la zona, se debe crear el primer registro
    $queryInsert = "INSERT INTO control_molienda (fecha, archivogen, turn1, turn2, turn3, creador, zona) 
                    VALUES (?, 0, 0, 0, 0, ?, ?)";
    $stmtInsert = $pdoControl->prepare($queryInsert);
    $stmtInsert->execute([date('Y-m-d'), $_SESSION['id_usuario'], $zonaSeleccionada]);

    // Obtener el nuevo registro recién creado
    $stmt->execute([$zonaSeleccionada]);
    $turnos = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Verificar si los turnos están completos dependiendo de la sede (ZS = 2 turnos, ZC = 3 turnos)
$todosLlenos = false;
if ($zonaSeleccionada === 'ZS') {
    if ($turnos['turn1'] == 1 && $turnos['turn2'] == 1) $todosLlenos = true;
} else {
    if ($turnos['turn1'] == 1 && $turnos['turn2'] == 1 && $turnos['turn3'] == 1) $todosLlenos = true;
}

if ($todosLlenos) {
    // Crear un nuevo registro para la misma zona con turnos en 0
    $queryInsert = "INSERT INTO control_molienda (fecha, archivogen, turn1, turn2, turn3, creador, zona) 
                    VALUES (?, 0, 0, 0, 0, ?, ?)";
    $stmtInsert = $pdoControl->prepare($queryInsert);
    $stmtInsert->execute([date('Y-m-d'), $_SESSION['id_usuario'], $zonaSeleccionada]);

    // Obtener el nuevo registro creado
    $stmt->execute([$zonaSeleccionada]);
    $turnos = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Verificar qué turnos están disponibles
$turn1Activo = ($turnos['turn1'] == 0);
$turn2Activo = ($turnos['turn2'] == 0);
$turn3Activo = ($turnos['turn3'] == 0);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Turnos - Molienda V2</title>
    <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;600&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0a0b10;
            --surface: #141620;
            --surface2: #1c1f2e;
            --border: #2d324a;
            --accent: #00f2ff; /* Cyan Cyberpunk */
            --text: #e0e6ed;
            --text-muted: #7a8599;
            --danger: #ff0055;
            --success: #00ff88;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'Barlow', sans-serif;
            text-align: center;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Scanline Effect */
        body::before {
            content: " ";
            position: fixed;
            top: 0; left: 0; bottom: 0; right: 0;
            background: linear-gradient(rgba(18, 16, 16, 0) 50%, rgba(0, 0, 0, 0.1) 50%), linear-gradient(90deg, rgba(255, 0, 0, 0.03), rgba(0, 255, 0, 0.01), rgba(0, 0, 255, 0.03));
            z-index: 1000;
            background-size: 100% 4px, 3px 100%;
            pointer-events: none;
        }

        .header {
            background: var(--surface);
            border-bottom: 2px solid var(--accent);
            padding: 25px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 0 20px rgba(0, 242, 255, 0.1);
        }

        .header h1 {
            font-family: 'Space Mono', monospace;
            font-size: 22px;
            letter-spacing: 2px;
            color: var(--accent);
            text-transform: uppercase;
        }

        .btn-volver {
            color: var(--text-muted);
            text-decoration: none;
            font-family: 'Space Mono', monospace;
            font-size: 13px;
            border: 1px dashed var(--border);
            padding: 8px 15px;
            border-radius: 2px;
            transition: all 0.3s;
        }

        .btn-volver:hover {
            border-color: var(--danger);
            color: var(--danger);
            background: rgba(255, 0, 85, 0.05);
        }

        .container {
            max-width: 800px;
            margin: 60px auto;
            padding: 0 20px;
            flex-grow: 1;
        }

        .status-hero {
            background: var(--surface);
            border: 1px solid var(--border);
            border-left: 4px solid var(--accent);
            padding: 20px;
            margin-bottom: 40px;
            text-align: left;
            display: inline-block;
            width: 100%;
            border-radius: 2px;
        }

        .status-hero h2 {
            font-family: 'Space Mono', monospace;
            font-size: 16px;
            color: var(--text);
            font-weight: normal;
        }

        .status-hero strong {
            color: var(--accent);
        }

        .grid-turnos {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            margin-top: 20px;
        }

        .turno-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 4px;
            padding: 40px 20px;
            text-decoration: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            position: relative;
        }

        .turno-card.activo {
            border-color: var(--accent);
            cursor: pointer;
            box-shadow: 0 0 15px rgba(0, 242, 255, 0.1);
        }

        .turno-card.activo:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 25px rgba(0, 242, 255, 0.3);
            background: linear-gradient(180deg, var(--surface) 0%, rgba(0, 242, 255, 0.05) 100%);
        }

        .turno-card.bloqueado {
            opacity: 0.5;
            cursor: not-allowed;
            border-style: dashed;
        }

        .turno-card.bloqueado::after {
            content: "";
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: repeating-linear-gradient(45deg, rgba(0,0,0,0) 0px, rgba(0,0,0,0) 10px, rgba(255,0,85,0.05) 10px, rgba(255,0,85,0.05) 20px);
            z-index: 10;
        }

        .turno-title {
            font-family: 'Space Mono', monospace;
            font-size: 24px;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 15px;
        }

        .turno-card.activo .turno-title {
            color: var(--accent);
        }

        .turno-status {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            padding: 5px 15px;
            border-radius: 20px;
        }

        .status-activo {
            background: rgba(0, 242, 255, 0.1);
            color: var(--accent);
            border: 1px solid var(--accent);
        }

        .status-bloqueado {
            background: rgba(255, 0, 85, 0.1);
            color: var(--danger);
            border: 1px solid var(--danger);
        }

        .links-extra {
            margin-top: 60px;
            padding-top: 30px;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: center;
            gap: 20px;
        }

        .btn-secundario {
            display: inline-block;
            padding: 12px 25px;
            background: var(--surface2);
            color: var(--text);
            text-decoration: none;
            font-family: 'Space Mono', monospace;
            font-size: 12px;
            text-transform: uppercase;
            border: 1px solid var(--border);
            border-radius: 2px;
            transition: all 0.3s;
        }

        .btn-secundario:hover {
            border-color: var(--success);
            color: var(--success);
            box-shadow: 0 0 15px rgba(0, 255, 136, 0.1);
        }
    </style>
</head>
<body>

<div class="header">
    <h1>Panel de Molienda</h1>
    <a class="btn-volver" href="../redireccion.php">&lt; MENÚ CIBERNÉTICO</a>
</div>

<div class="container">
    <div class="status-hero">
        <h2>SISTEMA ACTIVO | ZONA: <strong><?= htmlspecialchars($zonaSeleccionada) ?></strong></h2>
        <p style="font-size: 13px; color: var(--text-muted); margin-top: 5px; font-family: 'Space Mono', monospace;">
            Seleccione un turno disponible para iniciar el registro. Al completar, el turno se bloqueará automáticamente.
        </p>
    </div>

    <div class="grid-turnos">
        <!-- TURNO 1 -->
        <?php if ($turn1Activo): ?>
            <a href="molienda.php" class="turno-card activo">
                <div class="turno-title">TURNO 1</div>
                <div class="turno-status status-activo">Disponible</div>
            </a>
        <?php else: ?>
            <div class="turno-card bloqueado">
                <div class="turno-title">TURNO 1</div>
                <div class="turno-status status-bloqueado">Completado</div>
            </div>
        <?php endif; ?>

        <!-- TURNO 2 -->
        <?php if ($turn2Activo && !$turn1Activo): ?>
            <a href="molienda.php" class="turno-card activo">
                <div class="turno-title">TURNO 2</div>
                <div class="turno-status status-activo">Disponible</div>
            </a>
        <?php else: ?>
            <div class="turno-card bloqueado">
                <div class="turno-title">TURNO 2</div>
                <?php if (!$turn2Activo): ?>
                    <div class="turno-status status-bloqueado">Completado</div>
                <?php else: ?>
                    <div class="turno-status status-bloqueado" style="color:var(--text-muted); border-color:var(--border); background: var(--surface2);">En Espera</div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- TURNO 3 (SOLO ZONA CENTRO) -->
        <?php if ($zonaSeleccionada !== 'ZS'): ?>
            <?php if ($turn3Activo && !$turn2Activo): ?>
                <a href="molienda.php" class="turno-card activo">
                    <div class="turno-title">TURNO 3</div>
                    <div class="turno-status status-activo">Disponible</div>
                </a>
            <?php else: ?>
                <div class="turno-card bloqueado">
                    <div class="turno-title">TURNO 3</div>
                    <?php if (!$turn3Activo): ?>
                        <div class="turno-status status-bloqueado">Completado</div>
                    <?php else: ?>
                        <div class="turno-status status-bloqueado" style="color:var(--text-muted); border-color:var(--border); background: var(--surface2);">En Espera</div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <div class="links-extra">
        <a href="rev_molienda.php" class="btn-secundario">Ver Historial Turnos (Detalle)</a>
        <a href="galeria_diaria.php" class="btn-secundario" style="border-color: var(--accent); color: var(--accent);">Ver Planillas Diarias (Consolidado)</a>
        <a href="gestion_productos.php" class="btn-secundario" style="border-color: var(--success); color: var(--success);">Configuración Insumos</a>
    </div>
</div>

</body>
</html>
