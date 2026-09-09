<?php
require '../sesion.php';
verificarAutenticacion();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Datos · Calidad · Organización MAS</title>
    <link rel="stylesheet" href="../../css/index.css">
    <link rel="stylesheet" href="../../css/menu_principal.css">
    <style>
        .empty-state {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 100px 20px 60px;
        }
        .empty-state-card {
            max-width: 440px;
            width: 100%;
            text-align: center;
            padding: 48px 36px;
            border-radius: 24px;
            background: var(--panel);
            border: 1px solid var(--border);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
        .empty-state-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: var(--accent-glow);
            color: var(--accent);
        }
        .empty-state-icon svg { width: 30px; height: 30px; }
        .empty-state-card h1 {
            font-size: 18px;
            font-weight: 700;
            letter-spacing: 0.01em;
            color: var(--text-main);
            margin: 0 0 10px;
        }
        .empty-state-card p {
            font-size: 13.5px;
            line-height: 1.6;
            color: var(--text-muted);
            margin: 0;
        }
    </style>
</head>

<body class="theme-invert theme-quality">
    <canvas id="bg-canvas"></canvas>
    <div class="bg-veil"></div>

    <a href="../menu_administracion_calidad.html" class="menu-exit menu-exit--back">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19 8 12l7-7" />
        </svg>
        <span class="exit-label">Volver al menú</span>
    </a>

    <a href="../logout.php" class="menu-exit">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
        </svg>
        <span class="exit-label">Cerrar sesión</span>
    </a>

    <div class="empty-state">
        <div class="empty-state-card">
            <div class="empty-state-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.5V21h4.5v-7.5H3ZM9.75 8.25V21h4.5V8.25h-4.5ZM16.5 3v18H21V3h-4.5Z" />
                </svg>
            </div>
            <h1>Sin datos disponibles</h1>
            <p>Todavía no hay datos disponibles para renderizar en la sección de Datos de Calidad. Vuelve a intentarlo más adelante.</p>
        </div>
    </div>

    <script src="../../dist/app.js"></script>
</body>

</html>
