<?php
require '../sesion.php';
verificarAutenticacion();
require '../conection.php';

$stmt = $pdomaquinas->query("SELECT codigo, nombre, ubicacion FROM catalogo_maquinas WHERE activo = 1 ORDER BY ubicacion, nombre");
$maquinas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$porUbicacion = [];
foreach ($maquinas as $m) {
    $ubicacion = $m['ubicacion'] ?: 'Sin ubicación';
    $porUbicacion[$ubicacion][] = $m;
}
ksort($porUbicacion);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Máquinas · Cronograma por Equipo · Organización MAS</title>
    <link rel="stylesheet" href="../../css/index.css">
    <link rel="stylesheet" href="../../css/menu_principal.css">
    <style>
        .cal-page {
            position: relative;
            z-index: 2;
            max-width: 1180px;
            margin: 0 auto;
            padding: 108px 20px 70px;
        }

        .cal-head {
            text-align: center;
            margin-bottom: clamp(24px, 4vw, 36px);
            opacity: 0;
            animation: menuFadeUp 0.6s var(--ease) forwards;
        }
        .cal-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--accent);
            margin-bottom: 10px;
        }
        .cal-eyebrow .dot {
            width: 6px; height: 6px; border-radius: 50%;
            background: var(--accent);
            box-shadow: 0 0 8px var(--accent-glow);
            animation: pulse 2.2s ease-in-out infinite;
        }
        .cal-title {
            font-size: clamp(22px, 3.2vw, 30px);
            font-weight: 700;
            letter-spacing: -0.01em;
            color: var(--text-main);
            margin: 0 0 8px;
        }
        .cal-sub {
            font-size: 14px;
            color: var(--text-muted);
            margin: 0;
        }

        .search-card {
            background: var(--panel);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border: 1px solid var(--border);
            border-radius: var(--r-lg);
            padding: 18px 22px;
            margin-bottom: 28px;
            opacity: 0;
            animation: menuFadeUp 0.6s var(--ease) forwards;
            animation-delay: 0.06s;
        }
        .search-input {
            width: 100%;
            padding: 13px 16px;
            background: var(--panel-solid);
            border: 1px solid var(--border);
            border-radius: var(--r-sm);
            color: var(--text-main);
            font-family: inherit;
            font-size: 14.5px;
            outline: none;
            transition: border-color 0.2s var(--ease), box-shadow 0.2s var(--ease);
        }
        .search-input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px var(--accent-glow);
        }
        .search-count {
            margin-top: 10px;
            font-size: 12px;
            color: var(--text-faint);
        }

        .ubicacion-section {
            margin-bottom: 30px;
            opacity: 0;
            animation: menuFadeUp 0.5s var(--ease) forwards;
        }
        .ubicacion-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-muted);
            margin-bottom: 14px;
            padding-bottom: 8px;
            border-bottom: 1px dashed var(--border);
        }
        .ubicacion-title .count {
            font-size: 11px;
            font-weight: 600;
            color: var(--text-faint);
            background: var(--panel-solid);
            border: 1px solid var(--border);
            border-radius: 999px;
            padding: 2px 9px;
        }

        .maquinas-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
            gap: 10px;
        }

        .maquina-card {
            display: flex;
            flex-direction: column;
            gap: 3px;
            background: var(--panel-solid);
            border: 1px solid var(--border);
            border-radius: var(--r-md);
            padding: 12px 14px;
            text-decoration: none;
            transition: border-color 0.25s var(--ease), box-shadow 0.25s var(--ease), transform 0.2s var(--ease);
        }
        .maquina-card:hover {
            border-color: var(--border-hover);
            box-shadow: 0 12px 26px rgba(37, 99, 235, 0.14);
            transform: translateY(-2px);
        }
        .maquina-codigo {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.03em;
            color: var(--accent);
        }
        .maquina-nombre {
            font-size: 13.5px;
            font-weight: 500;
            color: var(--text-main);
            line-height: 1.3;
        }

        .sin-resultados {
            text-align: center;
            color: var(--text-faint);
            font-size: 13.5px;
            padding: 40px 0;
            display: none;
        }

        .cal-footer {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            margin-top: clamp(30px, 5vw, 48px);
            opacity: 0;
            animation: menuFadeUp 0.6s var(--ease) forwards;
            animation-delay: 0.2s;
        }
        .cal-footer img { width: min(180px, 46vw); height: auto; }
    </style>
</head>
<body>
    <canvas id="bg-canvas"></canvas>
    <div class="bg-veil"></div>

    <a href="../menu_mantenimiento.html" class="menu-exit menu-exit--back">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19 8 12l7-7" />
        </svg>
        <span class="exit-label">Volver al menú</span>
    </a>

    <div class="cal-page">
        <div class="cal-head">
            <div class="cal-eyebrow"><span class="dot"></span>MANTENIMIENTO · <?= count($maquinas) ?> MÁQUINAS</div>
            <h1 class="cal-title">Cronograma por Máquina</h1>
            <p class="cal-sub">Elige una máquina para ver su calendario anual y programar sus tareas</p>
        </div>

        <div class="search-card">
            <input type="text" id="buscador" class="search-input" placeholder="Buscar por código o nombre… (ej. MOLBOGDES01, Esclusa, Flowbalancer)">
            <div class="search-count" id="contadorResultados"></div>
        </div>

        <div id="contenedorGrupos">
            <?php foreach ($porUbicacion as $ubicacion => $lista): ?>
            <div class="ubicacion-section" data-ubicacion-section>
                <div class="ubicacion-title">
                    <span><?= htmlspecialchars($ubicacion) ?></span>
                    <span class="count"><?= count($lista) ?></span>
                </div>
                <div class="maquinas-grid">
                    <?php foreach ($lista as $m): ?>
                    <a href="calendario.php?codigo=<?= urlencode($m['codigo']) ?>" class="maquina-card"
                       data-buscar="<?= htmlspecialchars(mb_strtolower($m['codigo'] . ' ' . $m['nombre'], 'UTF-8')) ?>">
                        <span class="maquina-codigo"><?= htmlspecialchars($m['codigo']) ?></span>
                        <span class="maquina-nombre"><?= htmlspecialchars($m['nombre']) ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="sin-resultados" id="sinResultados">No se encontraron máquinas con ese criterio.</div>

        <div class="cal-footer">
            <img src="../../img/logo_omas_azul.png" alt="Organización MAS">
        </div>
    </div>

    <script src="../../dist/app.js"></script>
    <script>
        const buscador = document.getElementById('buscador');
        const contador = document.getElementById('contadorResultados');
        const sinResultados = document.getElementById('sinResultados');
        const tarjetas = Array.from(document.querySelectorAll('.maquina-card'));
        const secciones = Array.from(document.querySelectorAll('[data-ubicacion-section]'));
        const totalMaquinas = tarjetas.length;

        function filtrar() {
            const termino = buscador.value.trim().toLowerCase();
            let visibles = 0;

            secciones.forEach(seccion => {
                let visiblesEnSeccion = 0;
                seccion.querySelectorAll('.maquina-card').forEach(card => {
                    const coincide = !termino || card.dataset.buscar.includes(termino);
                    card.style.display = coincide ? '' : 'none';
                    if (coincide) { visiblesEnSeccion++; visibles++; }
                });
                seccion.style.display = visiblesEnSeccion > 0 ? '' : 'none';
            });

            contador.textContent = termino ? `${visibles} de ${totalMaquinas} máquinas` : `${totalMaquinas} máquinas en total`;
            sinResultados.style.display = visibles === 0 ? 'block' : 'none';
        }

        buscador.addEventListener('input', filtrar);
        filtrar();
    </script>
</body>
</html>
