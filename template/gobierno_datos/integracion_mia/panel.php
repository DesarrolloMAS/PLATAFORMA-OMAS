<?php
/**
 * Botón manual de sincronización con mIA — disparado desde el dashboard de
 * Bitácora de Mantenimiento. Corre el mismo motor que usa el cron
 * (sync_logic.php) pero desde un clic humano en vez de una tarea programada.
 * Ver integracion-mia.md.
 */
require '../../sesion.php';
verificarAutenticacion();

if (file_exists(__DIR__ . '/config_mia.local.php')) {
    require_once __DIR__ . '/config_mia.local.php';
} else {
    require_once __DIR__ . '/config_mia.example.php';
}
require_once __DIR__ . '/conexion_mia.php';
require_once __DIR__ . '/sync_logic.php';

$resultado = ejecutar_sync_tickets($pdoMia);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sincronización mIA</title>
    <style>
        :root {
            --bg-color: #0B0E14;
            --panel-bg: #151A22;
            --accent: #00F0FF;
            --ok: #22c55e;
            --error: #ef4444;
        }
        body {
            background: var(--bg-color);
            color: #e2e8f0;
            font-family: 'Space Mono', monospace, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .card {
            background: var(--panel-bg);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px;
            padding: 32px;
            max-width: 480px;
            width: 90%;
            text-align: center;
        }
        h1 { font-size: 18px; color: var(--accent); margin-bottom: 20px; text-transform: uppercase; }
        .resultado { font-size: 15px; margin-bottom: 24px; padding: 16px; border-radius: 8px; }
        .ok { background: rgba(34,197,94,0.1); border: 1px solid var(--ok); color: var(--ok); }
        .error { background: rgba(239,68,68,0.1); border: 1px solid var(--error); color: var(--error); }
        .btn-back {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            color: #fff;
            text-decoration: none;
            background: rgba(255,255,255,0.05);
            margin: 0 6px;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>🔄 Sincronización con mIA</h1>
        <div class="resultado <?= $resultado['ok'] ? 'ok' : 'error' ?>">
            <?= $resultado['ok'] ? '✅' : '❌' ?> <?= htmlspecialchars($resultado['mensaje']) ?>
        </div>
        <a href="../../analitica/dashboard_bitacora.php" class="btn-back">← Volver a Bitácora</a>
        <a href="panel.php" class="btn-back" style="border-color: var(--accent); color: var(--accent);">🔄 Consultar de nuevo</a>
    </div>
</body>
</html>
