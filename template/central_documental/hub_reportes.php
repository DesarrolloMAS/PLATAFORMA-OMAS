<?php
require '../sesion.php';
verificarAutenticacion();

$categorias = [
    'ALMACÉN' => [
        ['id' => 'molienda_v2', 'nombre' => 'Control de Molienda', 'icon' => '🏭', 'color' => '#00f2ff', 'ruta' => '../../archivos/generados/molienda/', 'tipo' => 'json_daily'],
        ['id' => 'empaque_v2', 'nombre' => 'Control de Empaque', 'icon' => '📦', 'color' => '#f59e0b', 'ruta' => '../../archivos/generados/empaque_v2/', 'tipo' => 'json'],
        ['id' => 'bodegas_v2', 'nombre' => 'Inspección de Bodegas V2', 'icon' => '🏬', 'color' => '#10B981', 'ruta' => '../../archivos/generados/bodegas_v2/', 'tipo' => 'json'],
    ],
    'PRODUCCIÓN' => [
        ['id' => 'cantidad_bulto', 'nombre' => 'Control Cantidad en Bulto', 'icon' => '⚖️', 'color' => '#a855f7', 'ruta' => '../../archivos/generados/cantidad_bulto/', 'tipo' => 'json'],
    ],
    'MANTENIMIENTO' => [
        ['id' => 'maquinas_v2', 'nombre' => 'Verificación de Máquinas V2', 'icon' => '⚙️', 'color' => '#FF8A00', 'ruta' => '../../archivos/generados/maquinas_v2/', 'tipo' => 'maquinas_nested'],
    ],
];

// Módulos cuyas carpetas están organizadas por sede (ZC / ZS) dentro de archivos/generados/
$SEDE_SCOPED_MODULES = ['molienda_v2', 'empaque_v2', 'cantidad_bulto', 'bodegas_v2'];

// Catálogo de bodegas para el filtro de búsqueda "Bodega" (mismo catálogo
// que template/bodegas_v2/menu_bodegas_v2.php: ZS usa un listado distinto).
$BODEGAS_PRINCIPALES = [
    'BodegaPNC'        => 'BODEGA PNC',
    'BodegaMogolla'    => 'BODEGA MOGOLLA',
    'Bodega1'          => 'BODEGA 1',
    'Bodega2'          => 'BODEGA 2',
    'Bodega3'          => 'BODEGA 3',
    'Bodega4'          => 'BODEGA 4',
    'BodegaPreMezclas' => 'BODEGA PRE MEZCLAS',
    'BodegaMejorantes' => 'BODEGA MEJORANTES',
];
$BODEGAS_ZS = [
    'PTfamiliarZS'        => 'PT FAMILIAR',
    'PTespecialZS'        => 'PT ESPECIAL',
    'materialesZS'        => 'MATERIALES',
    'PTindustrialZS'      => 'PT INDUSTRIAL',
    'microingredientesZS' => 'MICROINGREDIENTES',
    'LaboratorioZS'       => 'LABORATORIO',
];
$BODEGAS_POR_SEDE = ['ZC' => $BODEGAS_PRINCIPALES, 'ZB' => $BODEGAS_PRINCIPALES, 'ZS' => $BODEGAS_ZS];

$ZONAS   = ['ZC' => 'Zona Centro', 'ZS' => 'Zona Sur'];
$miSede  = array_key_exists($_SESSION['sede'] ?? '', $ZONAS) ? $_SESSION['sede'] : 'ZC';
$otraSede = $miSede === 'ZC' ? 'ZS' : 'ZC';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HUB Maestría Documental - FMT</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Barlow:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #050608;
            --surface: #0f111a;
            --surface-hover: #161a29;
            --border: #1e243a;
            --accent: #00f2ff;
            --text-main: #e0e6ed;
            --text-dim: #7a8599;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background-color: var(--bg);
            color: var(--text-main);
            font-family: 'Barlow', sans-serif;
            min-height: 100vh;
            background-attachment: fixed;
            background-image:
                radial-gradient(circle at 50% 0%, rgba(0, 242, 255, 0.05) 0%, transparent 50%);
        }

        .container { max-width: 1400px; margin: 0 auto; padding: 40px 20px; }

        header { text-align: center; margin-bottom: 50px; position: relative; }

        .btn-back-admin {
            position: absolute;
            top: 4px;
            left: 0;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--surface);
            border: 1px solid var(--border);
            color: var(--text-dim);
            padding: 10px 18px;
            border-radius: 8px;
            font-family: 'Orbitron', sans-serif;
            font-size: 0.68rem;
            letter-spacing: 1px;
            text-decoration: none;
            text-transform: uppercase;
            transition: all 0.25s;
            white-space: nowrap;
        }
        .btn-back-admin:hover {
            color: var(--accent);
            border-color: rgba(0,242,255,0.3);
            box-shadow: 0 0 15px rgba(0,242,255,0.1);
        }
        @media (max-width: 720px) {
            .btn-back-admin { position: static; display: inline-flex; margin-bottom: 20px; }
        }

        header h1 {
            font-family: 'Orbitron', sans-serif;
            font-size: 2.2rem;
            letter-spacing: 5px;
            color: var(--accent);
            text-shadow: 0 0 20px rgba(0, 242, 255, 0.3);
            text-transform: uppercase;
        }

        .search-box {
            max-width: 500px;
            margin: 20px auto 40px;
            position: relative;
        }

        .search-box input {
            width: 100%;
            background: var(--surface);
            border: 1px solid var(--border);
            padding: 15px 25px;
            border-radius: 50px;
            color: #fff;
            font-family: 'Orbitron', sans-serif;
            font-size: 0.8rem;
            outline: none;
            transition: 0.3s;
        }

        .search-box input:focus { border-color: var(--accent); box-shadow: 0 0 15px rgba(0, 242, 255, 0.1); }

        /* ── SELECTOR DE SEDE ── */
        .scope-bar-wrap { display: flex; justify-content: center; margin-bottom: 45px; }
        .scope-bar {
            display: flex;
            gap: 4px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 4px;
        }
        .scope-tab {
            font-family: 'Orbitron', sans-serif;
            font-size: 0.68rem;
            letter-spacing: 1px;
            padding: 11px 22px;
            border-radius: 7px;
            border: none;
            cursor: pointer;
            transition: all 0.25s;
            color: var(--text-dim);
            background: transparent;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .scope-tab.active {
            background: var(--surface-hover);
            color: var(--accent);
            border: 1px solid rgba(0,242,255,0.3);
            box-shadow: 0 0 15px rgba(0,242,255,0.1);
        }
        .scope-tab.active.is-ambas {
            color: #ffb45c;
            border-color: rgba(255,180,92,0.35);
            box-shadow: 0 0 15px rgba(255,180,92,0.12);
        }
        .scope-tab:not(.active):hover { color: var(--text-main); }

        .category-section { margin-bottom: 60px; }
        .category-title {
            font-family: 'Orbitron', sans-serif;
            font-size: 1rem;
            color: var(--text-dim);
            letter-spacing: 2px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .category-title::after { content: ''; flex-grow: 1; height: 1px; background: var(--border); }

        .grid-modules {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .module-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 20px;
            text-decoration: none;
            color: inherit;
            display: flex;
            align-items: center;
            gap: 20px;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }

        .module-card::before {
            content: '';
            position: absolute;
            left: 0; top: 0; height: 100%; width: 3px;
            background: var(--mod-color);
        }

        .module-card:hover {
            background: var(--surface-hover);
            border-color: var(--mod-color);
            transform: scale(1.03);
            box-shadow: 0 5px 20px rgba(0,0,0,0.4);
        }

        .module-icon { font-size: 1.8rem; background: rgba(255,255,255,0.03); width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; border-radius: 8px; border: 1px solid var(--border); }

        .module-info h3 { font-family: 'Orbitron', sans-serif; font-size: 0.85rem; letter-spacing: 1px; color: #fff; margin-bottom: 4px; }
        .module-info span { font-size: 0.7rem; color: var(--text-dim); text-transform: uppercase; }

        @media (max-width: 768px) {
            .grid-modules { grid-template-columns: 1fr; }
        }

        /* ── SP READER ── */
        .sp-reader-section {
            margin-top: 70px;
            padding-top: 40px;
            border-top: 1px solid var(--border);
        }
        .sp-reader-title {
            font-family: 'Orbitron', sans-serif;
            font-size: 1rem;
            color: #0078d4;
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .sp-reader-title svg { width: 22px; height: 22px; fill: #0078d4; flex-shrink: 0; }
        .sp-reader-title::after { content: ''; flex-grow: 1; height: 1px; background: #0078d4; opacity: 0.3; }

        .sp-search-bar {
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 20px 24px;
            margin-bottom: 30px;
        }
        .sp-search-bar label {
            font-size: 0.72rem;
            color: var(--text-dim);
            letter-spacing: 1px;
            font-family: 'Orbitron', sans-serif;
            white-space: nowrap;
        }
        .sp-search-bar input[type="date"],
        .sp-search-bar select {
            background: #0a0c14;
            border: 1px solid var(--border);
            color: var(--text-main);
            padding: 10px 16px;
            border-radius: 6px;
            font-family: 'Barlow', sans-serif;
            font-size: 0.9rem;
            outline: none;
            transition: border-color 0.2s;
            cursor: pointer;
        }
        .sp-search-bar input[type="date"]:focus,
        .sp-search-bar select:focus { border-color: #0078d4; }

        .btn-sp-search {
            background: linear-gradient(135deg, #0078d4 0%, #106ebe 100%);
            color: #fff;
            border: none;
            padding: 11px 24px;
            border-radius: 6px;
            font-family: 'Orbitron', sans-serif;
            font-size: 0.72rem;
            letter-spacing: 1px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.25s;
            white-space: nowrap;
        }
        .btn-sp-search:hover { opacity: 0.9; transform: translateY(-1px); box-shadow: 0 4px 15px rgba(0,120,212,0.4); }
        .btn-sp-search:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }

        /* Estado de búsqueda */
        .sp-status-line {
            font-size: 0.78rem;
            color: var(--text-dim);
            margin-bottom: 20px;
            min-height: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .sp-status-line .spinner {
            width: 14px; height: 14px;
            border: 2px solid var(--border);
            border-top-color: #0078d4;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            display: none;
        }
        .sp-status-line.loading .spinner { display: block; }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Grid de resultados */
        .sp-results-grid {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .sp-turn-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 18px 20px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            position: relative;
            overflow: hidden;
            transition: all 0.25s;
        }
        .sp-turn-card::before {
            content: '';
            position: absolute;
            left: 0; top: 0; height: 100%; width: 3px;
            background: #0078d4;
        }
        .sp-turn-card:hover {
            border-color: #0078d4;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,120,212,0.2);
        }

        /* Tarjeta consolidada del día */
        .sp-day-card {
            max-width: 600px;
            padding: 24px 28px;
            gap: 14px;
        }
        .sp-day-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .sp-turn-badge {
            font-family: 'Orbitron', sans-serif;
            font-size: 0.65rem;
            letter-spacing: 2px;
            background: rgba(0,120,212,0.15);
            color: #0078d4;
            border: 1px solid rgba(0,120,212,0.3);
            padding: 3px 10px;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            width: fit-content;
        }
        .sp-turn-meta {
            font-size: 0.9rem;
            color: var(--text-main);
            font-weight: 600;
        }

        /* Chips de turnos encontrados */
        .sp-turno-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .sp-turno-chip {
            font-family: 'Barlow', sans-serif;
            font-size: 0.72rem;
            font-weight: 600;
            background: rgba(0,242,255,0.06);
            color: var(--accent);
            border: 1px solid rgba(0,242,255,0.2);
            padding: 4px 12px;
            border-radius: 6px;
            letter-spacing: 0.5px;
        }

        .sp-turn-sub {
            font-size: 0.78rem;
            color: var(--text-dim);
            line-height: 1.7;
        }
        .sp-turn-products {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }
        .sp-product-tag {
            font-size: 0.65rem;
            background: rgba(0,242,255,0.08);
            color: #00c8d4;
            border: 1px solid rgba(0,242,255,0.15);
            padding: 2px 8px;
            border-radius: 4px;
        }
        .sp-turn-actions {
            display: flex;
            gap: 8px;
            margin-top: 6px;
        }
        .btn-sp-view {
            flex: 1;
            text-align: center;
            padding: 8px;
            border-radius: 5px;
            font-family: 'Orbitron', sans-serif;
            font-size: 0.6rem;
            letter-spacing: 1px;
            text-decoration: none;
            transition: 0.2s;
            border: 1px solid #0078d4;
            color: #0078d4;
            background: transparent;
        }
        .btn-sp-view:hover {
            background: #0078d4;
            color: #fff;
        }
        .btn-sp-view-primary {
            background: linear-gradient(135deg, #0078d4 0%, #106ebe 100%);
            color: #fff;
            border-color: transparent;
            padding: 11px 16px;
            font-size: 0.65rem;
        }
        .btn-sp-view-primary:hover {
            opacity: 0.9;
            box-shadow: 0 4px 15px rgba(0,120,212,0.4);
        }

        .sp-empty {
            text-align: center;
            padding: 50px 20px;
            color: var(--text-dim);
            font-size: 0.85rem;
        }
        .sp-empty svg { width: 48px; height: 48px; fill: var(--border); margin-bottom: 15px; display: block; margin-left: auto; margin-right: auto; }
        .sp-module-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 14px;
        }
        .sp-module-header::after { content: ''; flex-grow: 1; height: 1px; background: var(--border); }

        /* ── TABS ── */
        .search-tabs {
            display: flex;
            gap: 4px;
            margin-bottom: 20px;
            background: #0a0c14;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 4px;
            width: fit-content;
        }
        .search-tab {
            font-family: 'Orbitron', sans-serif;
            font-size: 0.65rem;
            letter-spacing: 1.5px;
            padding: 9px 20px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            color: var(--text-dim);
            background: transparent;
        }
        .search-tab.active {
            background: var(--surface);
            color: var(--accent);
            border: 1px solid rgba(0,242,255,0.2);
        }
        .search-tab:not(.active):hover { color: var(--text-main); }
        .search-form-panel { display: none; }
        .search-form-panel.active { display: flex; flex-wrap: wrap; gap: 12px; align-items: center; }
        .sp-error {
            background: rgba(248,81,73,0.08);
            border: 1px solid rgba(248,81,73,0.3);
            color: #f85149;
            padding: 16px 20px;
            border-radius: 8px;
            font-size: 0.82rem;
        }
    </style>
</head>
<body>

<div class="container">
    <header>
        <a href="../menu_adm.html" class="btn-back-admin">← Volver al Menú Admin</a>
        <h1>Central Documental</h1>
        <div class="search-box">
            <input type="text" id="moduleSearch" placeholder="BUSCAR FORMATO..." onkeyup="filterModules()">
        </div>
    </header>

    <div class="scope-bar-wrap">
        <div class="scope-bar" id="scopeBar">
            <button type="button" class="scope-tab" data-scope="propia" onclick="setSedeScope('propia')">
                📍 <?= htmlspecialchars($ZONAS[$miSede]) ?> (MI SEDE)
            </button>
            <button type="button" class="scope-tab" data-scope="otra" onclick="setSedeScope('otra')">
                📍 <?= htmlspecialchars($ZONAS[$otraSede]) ?>
            </button>
            <button type="button" class="scope-tab" data-scope="ambas" onclick="setSedeScope('ambas')">
                🌐 AMBAS SEDES
            </button>
        </div>
    </div>

    <?php foreach ($categorias as $catName => $mods): ?>
        <section class="category-section" id="cat-<?= $catName ?>">
            <div class="category-title"><?= $catName ?></div>
            <div class="grid-modules">
                <?php foreach ($mods as $m): ?>
                    <a href="galeria_unificada.php?modulo=<?= $m['id'] ?>"
                       class="module-card"
                       data-name="<?= strtolower($m['nombre']) ?>"
                       data-modulo="<?= $m['id'] ?>"
                       data-sede-scoped="<?= in_array($m['id'], $SEDE_SCOPED_MODULES) ? '1' : '0' ?>"
                       style="--mod-color: <?= $m['color'] ?>;">
                        <div class="module-icon"><?= $m['icon'] ?></div>
                        <div class="module-info">
                            <h3><?= $m['nombre'] ?></h3>
                            <span>TIPO: <?= $m['tipo'] ?></span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endforeach; ?>

    <!-- ══════════════════════════════════════════════════ -->
    <!-- BÚSQUEDA UNIFICADA: Molienda + Empaque           -->
    <!-- ══════════════════════════════════════════════════ -->
    <div class="sp-reader-section">
        <div class="sp-reader-title">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M9.5,3A6.5,6.5 0 0,1 16,9.5C16,11.11 15.41,12.59 14.44,13.73L14.71,14H15.5L20.5,19L19,20.5L14,15.5V14.71L13.73,14.44C12.59,15.41 11.11,16 9.5,16A6.5,6.5 0 0,1 3,9.5A6.5,6.5 0 0,1 9.5,3M9.5,5C7,5 5,7 5,9.5C5,12 7,14 9.5,14C12,14 14,12 14,9.5C14,7 12,5 9.5,5Z"/></svg>
            Búsqueda Unificada · Molienda &amp; Empaque
        </div>

        <!-- Tabs -->
        <div class="search-tabs">
            <button class="search-tab active" id="tabFecha" onclick="switchTab('fecha')">📅 POR FECHA</button>
            <button class="search-tab" id="tabLote" onclick="switchTab('lote')">🏷️ POR LOTE DE PRODUCTO</button>
            <button class="search-tab" id="tabMaquina" onclick="switchTab('maquina')">🔧 POR CÓDIGO DE MÁQUINA</button>
            <button class="search-tab" id="tabBodega" onclick="switchTab('bodega')">🏬 POR BODEGA</button>
        </div>

        <!-- Formulario: Por Fecha -->
        <div class="sp-search-bar" style="padding-top:0; border:none; background:none; padding-left:0; padding-right:0;">
            <div class="search-form-panel active" id="formFecha">
                <label for="sp_fecha">FECHA</label>
                <input type="date" id="sp_fecha" value="<?= date('Y-m-d') ?>">
                <label for="sp_sede">SEDE</label>
                <select id="sp_sede">
                    <option value="ZC" <?= ($_SESSION['sede'] ?? '') === 'ZC' ? 'selected' : '' ?>>ZC — Zona Centro</option>
                    <option value="ZS" <?= ($_SESSION['sede'] ?? '') === 'ZS' ? 'selected' : '' ?>>ZS — Zona Sur</option>
                    <option value="ZB">ZB — Buga</option>
                </select>
                <button class="btn-sp-search" id="btnFechaSearch" onclick="unifiedSearch()">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M9.5,3A6.5,6.5 0 0,1 16,9.5C16,11.11 15.41,12.59 14.44,13.73L14.71,14H15.5L20.5,19L19,20.5L14,15.5V14.71L13.73,14.44C12.59,15.41 11.11,16 9.5,16A6.5,6.5 0 0,1 3,9.5A6.5,6.5 0 0,1 9.5,3M9.5,5C7,5 5,7 5,9.5C5,12 7,14 9.5,14C12,14 14,12 14,9.5C14,7 12,5 9.5,5Z"/></svg>
                    BUSCAR
                </button>
            </div>

            <!-- Formulario: Por Lote de Producto -->
            <div class="search-form-panel" id="formLote">
                <label for="sp_lote">LOTE DE PRODUCTO</label>
                <input type="text" id="sp_lote" placeholder="Ej: 050526B" style="background:#0a0c14; border:1px solid var(--border); color:var(--text-main); padding:10px 16px; border-radius:6px; font-family:'Barlow',sans-serif; font-size:0.9rem; outline:none; transition:border-color 0.2s; width:180px; text-transform:uppercase;">
                <label for="sp_sede_lote">SEDE</label>
                <select id="sp_sede_lote">
                    <option value="ZC" <?= ($_SESSION['sede'] ?? '') === 'ZC' ? 'selected' : '' ?>>ZC — Zona Centro</option>
                    <option value="ZS" <?= ($_SESSION['sede'] ?? '') === 'ZS' ? 'selected' : '' ?>>ZS — Zona Sur</option>
                    <option value="ZB">ZB — Buga</option>
                </select>
                <button class="btn-sp-search" id="btnLoteSearch" onclick="loteSearch()">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M9.5,3A6.5,6.5 0 0,1 16,9.5C16,11.11 15.41,12.59 14.44,13.73L14.71,14H15.5L20.5,19L19,20.5L14,15.5V14.71L13.73,14.44C12.59,15.41 11.11,16 9.5,16A6.5,6.5 0 0,1 3,9.5A6.5,6.5 0 0,1 9.5,3M9.5,5C7,5 5,7 5,9.5C5,12 7,14 9.5,14C12,14 14,12 14,9.5C14,7 12,5 9.5,5Z"/></svg>
                    BUSCAR
                </button>
            </div>

            <!-- Formulario: Por Código de Máquina -->
            <div class="search-form-panel" id="formMaquina">
                <label for="sp_codigo_maquina">CÓDIGO DE MÁQUINA</label>
                <input type="text" id="sp_codigo_maquina" placeholder="Ej: ANALITICA220G_LABBOGBAL01" style="background:#0a0c14; border:1px solid var(--border); color:var(--text-main); padding:10px 16px; border-radius:6px; font-family:'Barlow',sans-serif; font-size:0.9rem; outline:none; transition:border-color 0.2s; width:260px; text-transform:uppercase;">
                <button class="btn-sp-search" id="btnMaquinaSearch" onclick="maquinaSearch()">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M9.5,3A6.5,6.5 0 0,1 16,9.5C16,11.11 15.41,12.59 14.44,13.73L14.71,14H15.5L20.5,19L19,20.5L14,15.5V14.71L13.73,14.44C12.59,15.41 11.11,16 9.5,16A6.5,6.5 0 0,1 3,9.5A6.5,6.5 0 0,1 9.5,3M9.5,5C7,5 5,7 5,9.5C5,12 7,14 9.5,14C12,14 14,12 14,9.5C14,7 12,5 9.5,5Z"/></svg>
                    BUSCAR
                </button>
            </div>

            <!-- Formulario: Por Bodega -->
            <div class="search-form-panel" id="formBodega">
                <label for="sp_sede_bodega">SEDE</label>
                <select id="sp_sede_bodega" onchange="actualizarBodegasSelect()">
                    <option value="ZC" <?= ($_SESSION['sede'] ?? '') === 'ZC' ? 'selected' : '' ?>>ZC — Zona Centro</option>
                    <option value="ZS" <?= ($_SESSION['sede'] ?? '') === 'ZS' ? 'selected' : '' ?>>ZS — Zona Sur</option>
                    <option value="ZB">ZB — Buga</option>
                </select>
                <label for="sp_bodega">BODEGA</label>
                <select id="sp_bodega"></select>
                <button class="btn-sp-search" id="btnBodegaSearch" onclick="bodegaSearch()">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M9.5,3A6.5,6.5 0 0,1 16,9.5C16,11.11 15.41,12.59 14.44,13.73L14.71,14H15.5L20.5,19L19,20.5L14,15.5V14.71L13.73,14.44C12.59,15.41 11.11,16 9.5,16A6.5,6.5 0 0,1 3,9.5A6.5,6.5 0 0,1 9.5,3M9.5,5C7,5 5,7 5,9.5C5,12 7,14 9.5,14C12,14 14,12 14,9.5C14,7 12,5 9.5,5Z"/></svg>
                    BUSCAR
                </button>
            </div>
        </div>

        <!-- Resultados Molienda -->
        <div class="sp-module-header" id="molHeader" style="display:none;">
            <span style="color: #0078d4; font-family: 'Orbitron', sans-serif; font-size: 0.75rem; letter-spacing: 2px;">🏭 CONTROL DE MOLIENDA</span>
        </div>
        <div class="sp-status-line" id="spStatusLine">
            <div class="spinner"></div>
            <span id="spStatusText">Selecciona un modo de búsqueda e ingresa los datos.</span>
        </div>
        <div class="sp-results-grid" id="spResultsGrid"></div>

        <!-- Resultados Empaque -->
        <div class="sp-module-header" id="empHeader" style="display:none; margin-top: 30px;">
            <span style="color: #f59e0b; font-family: 'Orbitron', sans-serif; font-size: 0.75rem; letter-spacing: 2px;">📦 CONTROL DE EMPAQUE</span>
        </div>
        <div class="sp-status-line" id="spEmpStatusLine" style="display:none;">
            <div class="spinner"></div>
            <span id="spEmpStatusText"></span>
        </div>
        <div class="sp-results-grid" id="spEmpResultsGrid"></div>

        <!-- Resultados Cantidad en Bulto -->
        <div class="sp-module-header" id="bultoHeader" style="display:none; margin-top: 30px;">
            <span style="color: #a855f7; font-family: 'Orbitron', sans-serif; font-size: 0.75rem; letter-spacing: 2px;">⚖️ CONTROL CANTIDAD EN BULTO</span>
        </div>
        <div class="sp-status-line" id="spBultoStatusLine" style="display:none;">
            <div class="spinner"></div>
            <span id="spBultoStatusText"></span>
        </div>
        <div class="sp-results-grid" id="spBultoResultsGrid"></div>

        <!-- Resultados Máquinas V2 -->
        <div class="sp-module-header" id="maqHeader" style="display:none; margin-top: 30px;">
            <span style="color: #FF8A00; font-family: 'Orbitron', sans-serif; font-size: 0.75rem; letter-spacing: 2px;">⚙️ VERIFICACIÓN DE MÁQUINAS V2</span>
        </div>
        <div class="sp-status-line" id="spMaqStatusLine" style="display:none;">
            <div class="spinner"></div>
            <span id="spMaqStatusText"></span>
        </div>
        <div class="sp-results-grid" id="spMaqResultsGrid"></div>

        <!-- Resultados Inspección de Bodegas V2 -->
        <div class="sp-module-header" id="bodHeader" style="display:none; margin-top: 30px;">
            <span style="color: #10B981; font-family: 'Orbitron', sans-serif; font-size: 0.75rem; letter-spacing: 2px;">🏬 INSPECCIÓN DE BODEGAS V2</span>
        </div>
        <div class="sp-status-line" id="spBodStatusLine" style="display:none;">
            <div class="spinner"></div>
            <span id="spBodStatusText"></span>
        </div>
        <div class="sp-results-grid" id="spBodResultsGrid"></div>
    </div>

</div>

<script>
    // ─── SELECTOR DE SEDE (Hub → Galería Unificada) ────────────────────────────
    const MI_SEDE    = '<?= $miSede ?>';
    const OTRA_SEDE  = '<?= $otraSede ?>';
    const SCOPE_KEY  = 'hub_vista_sede';

    function sedesParaScope(scope) {
        if (scope === 'ambas') return `${MI_SEDE},${OTRA_SEDE}`;
        if (scope === 'otra')  return OTRA_SEDE;
        return MI_SEDE;
    }

    function applySedeScope(scope) {
        const sedes = sedesParaScope(scope);

        document.querySelectorAll('.scope-tab').forEach(btn => {
            const active = btn.dataset.scope === scope;
            btn.classList.toggle('active', active);
            btn.classList.toggle('is-ambas', active && scope === 'ambas');
        });

        document.querySelectorAll('.module-card[data-sede-scoped="1"]').forEach(card => {
            const url = new URL(card.getAttribute('href'), window.location.href);
            url.searchParams.set('sedes', sedes);
            card.setAttribute('href', url.pathname + url.search);
        });
    }

    function setSedeScope(scope) {
        localStorage.setItem(SCOPE_KEY, scope);
        applySedeScope(scope);
    }

    applySedeScope(localStorage.getItem(SCOPE_KEY) || 'propia');

    function filterModules() {
        const input = document.getElementById('moduleSearch').value.toLowerCase();
        const cards = document.querySelectorAll('.module-card');

        cards.forEach(card => {
            const name = card.getAttribute('data-name');
            card.style.display = name.includes(input) ? 'flex' : 'none';
        });

        const sections = document.querySelectorAll('.category-section');
        sections.forEach(sec => {
            const allCardsNone = Array.from(sec.querySelectorAll('.module-card')).every(c => c.style.display === 'none');
            sec.style.display = (allCardsNone && input !== '') ? 'none' : 'block';
        });
    }

    // ─── BÚSQUEDA UNIFICADA ───────────────────────────────────────────────────
    // ─── BADGE DE ORIGEN ──────────────────────────────────────────────────────
    function srcBadge(src) {
        if (!src || src === 'sharepoint' || src === 'sharepoint_atomic' || src === 'sharepoint_monthly')
            return '☁️ SharePoint';
        if (src === 'local')  return '📁 Local';
        if (src === 'both')   return '📁☁️ Local + SP';
        return '☁️';
    }

    // Para listas de turnos: deriva el origen del conjunto
    function srcBadgeFromList(items) {
        const s = new Set((items || []).map(i => i.source || 'sharepoint'));
        if (s.has('both') || (s.has('local') && s.has('sharepoint'))) return '📁☁️ Local + SP';
        if (s.has('local')) return '📁 Local';
        return '☁️ SharePoint';
    }

    const PRODUCT_NAMES = {
        extrapan_x50: 'Extrapan x50', extrapan_x25: 'Extrapan x25',
        alta_proteina: 'Alta Proteína', galeras_x50: 'Galeras x50',
        galeras_x25: 'Galeras x25', salvado_x25: 'Salvado x25',
        mogolla_x40: 'Mogolla x40', segunda_x50: 'Segunda x50',
    };
    const TURNO_HORA = { 1: '🕑 Tarde (14:00)', 2: '🌙 Noche (22:00)', 3: '🌅 Mañana (06:00)' };

    function unifiedSearch() {
        const fecha = document.getElementById('sp_fecha').value;
        const sede  = document.getElementById('sp_sede').value;
        const btn   = document.getElementById('btnFechaSearch');

        if (!fecha) { alert('Selecciona una fecha antes de buscar.'); return; }

        btn.disabled = true;

        // Mostrar secciones de resultados
        document.getElementById('molHeader').style.display = 'flex';
        document.getElementById('empHeader').style.display = 'flex';
        document.getElementById('bultoHeader').style.display = 'flex';
        document.getElementById('maqHeader').style.display = 'flex';
        document.getElementById('spEmpStatusLine').style.display = 'flex';
        document.getElementById('spBultoStatusLine').style.display = 'flex';
        document.getElementById('spMaqStatusLine').style.display = 'flex';

        // Lanzar las cuatro búsquedas en paralelo
        Promise.all([
            fetchMolienda(fecha, sede),
            fetchEmpaque(fecha, sede),
            fetchBultoByFecha(fecha, sede),
            fetchMaquinaByFecha(fecha),
        ]).finally(() => {
            btn.disabled = false;
        });
    }

    function fetchMolienda(fecha, sede) {
        const status = document.getElementById('spStatusLine');
        const text   = document.getElementById('spStatusText');
        const grid   = document.getElementById('spResultsGrid');

        status.classList.add('loading');
        text.textContent = `Buscando molienda para ${fecha} (${sede})...`;
        grid.innerHTML = '';

        return fetch(`sp_reader.php?action=search&fecha=${fecha}&sede=${sede}`)
            .then(r => r.json())
            .then(data => {
                status.classList.remove('loading');

                if (!data.success) {
                    text.textContent = `Sin resultados de molienda para ${fecha}.`;
                    grid.innerHTML = `<div class="sp-error">☁️ ${data.error || 'No se encontraron datos en SharePoint.'}</div>`;
                    return;
                }

                const turnos = data.turnos || [];
                if (turnos.length === 0) {
                    text.textContent = `Sin registros de molienda para ${fecha}.`;
                    grid.innerHTML = `<div class="sp-empty"><svg viewBox="0 0 24 24"><path d="M13 9h-2V7h2m0 10h-2v-6h2m-1-9A10 10 0 0 0 2 12a10 10 0 0 0 10 10 10 10 0 0 0 10-10A10 10 0 0 0 12 2z"/></svg>Sin registros de molienda para <strong>${fecha}</strong>.</div>`;
                    return;
                }

                turnos.sort((a, b) => (a.turno || 0) - (b.turno || 0));
                const srcCounts = data.src_counts || {};
                const srcDesc = srcCounts.both ? '📁☁️ Local + SP'
                              : (srcCounts.local && !srcCounts.sharepoint) ? '📁 Local'
                              : srcCounts.local ? '📁☁️ Local + SP' : '☁️ SharePoint';
                text.textContent = `Molienda: ${turnos.length} turno(s) encontrado(s) · ${srcDesc}`;
                grid.innerHTML = renderDayCard(turnos, fecha, sede);
            })
            .catch(err => {
                status.classList.remove('loading');
                text.textContent = 'Error de conexión (molienda).';
                grid.innerHTML = `<div class="sp-error">❌ ${err.message}</div>`;
            });
    }

    function fetchEmpaque(fecha, sede) {
        const status = document.getElementById('spEmpStatusLine');
        const text   = document.getElementById('spEmpStatusText');
        const grid   = document.getElementById('spEmpResultsGrid');

        status.classList.add('loading');
        text.textContent = `Buscando empaque para ${fecha} (${sede})...`;
        grid.innerHTML = '';

        return fetch(`sp_reader.php?action=search_empaque_by_fecha&fecha=${fecha}&sede=${sede}`)
            .then(r => r.json())
            .then(data => {
                status.classList.remove('loading');

                if (!data.success) {
                    text.textContent = `Sin resultados de empaque para ${fecha}.`;
                    grid.innerHTML = `<div class="sp-error">📦 ${data.error || 'No se encontraron datos de empaque.'}</div>`;
                    return;
                }

                const lotes = data.lotes || [];
                text.textContent = `Empaque: ${lotes.length} lote(s) encontrado(s) el ${fecha} · ☁️ SharePoint`;
                grid.innerHTML = lotes.map(l => renderEmpCard(l.registros, l.lote, sede, l.file, l.source)).join('');
            })
            .catch(err => {
                status.classList.remove('loading');
                text.textContent = 'Error de conexión (empaque).';
                grid.innerHTML = `<div class="sp-error">❌ ${err.message}</div>`;
            });
    }

    function renderDayCard(turnos, fecha, sede) {
        const turnoLabels = turnos.map(t => {
            const n = t.turno || '?';
            const h = TURNO_HORA[n] || '';
            return `<span class="sp-turno-chip">T${n} ${h}</span>`;
        }).join('');

        const responsables = [...new Set(turnos.map(t => t.responsable).filter(Boolean))];
        const almacenistas = [...new Set(turnos.map(t => t.almacenista).filter(Boolean))];

        const allActiveProducts = new Set();
        turnos.forEach(t => {
            const products = { ...(t.harinas || {}), ...(t.subproductos || {}) };
            Object.keys(products)
                .filter(k => products[k]?.active === 'on')
                .forEach(k => allActiveProducts.add(PRODUCT_NAMES[k] || k.replace(/_/g, ' ').toUpperCase()));
        });

        const productArr = [...allActiveProducts];
        const productTags = productArr.slice(0, 6).map(p => `<span class="sp-product-tag">${p}</span>`).join('');
        const extraCount = productArr.length > 6 ? `<span class="sp-product-tag">+${productArr.length - 6}</span>` : '';
        const viewerUrl = `/template/molienda_v2/plantilla_diaria.php?fecha=${fecha}&sede=${sede}`;
        const badge = srcBadgeFromList(turnos);

        return `
        <div class="sp-turn-card sp-day-card">
            <div class="sp-day-card-header">
                <div class="sp-turn-meta">📅 ${fecha} &nbsp;|&nbsp; 📍 ${sede}</div>
                <div class="sp-turn-badge">${badge} · ${turnos.length} TURNO${turnos.length > 1 ? 'S' : ''}</div>
            </div>
            <div class="sp-turno-chips">${turnoLabels}</div>
            <div class="sp-turn-sub">
                👤 Responsable${responsables.length > 1 ? 's' : ''}: <strong>${responsables.join(', ') || 'N/D'}</strong><br>
                🏪 Almacenista${almacenistas.length > 1 ? 's' : ''}: ${almacenistas.join(', ') || 'N/D'}
            </div>
            ${productArr.length > 0 ? `<div class="sp-turn-products">${productTags}${extraCount}</div>` : ''}
            <div class="sp-turn-actions">
                <a href="${viewerUrl}" target="_blank" class="btn-sp-view btn-sp-view-primary">📄 VER REPORTE COMPLETO DEL DÍA</a>
            </div>
        </div>`;
    }

    function renderEmpCard(registros, lote, sede, filename, source) {
        const primer = registros[0]?.datos || registros[0] || {};
        const referencia = primer.nombre_empaque || 'N/D';
        const producto = primer.producto_envasar || 'N/D';

        const responsables = [...new Set(registros.map(r => (r.datos || r).responsable_alistamiento).filter(Boolean))];

        let totalSolicitados = 0, totalEntregados = 0, totalDevueltos = 0;
        registros.forEach(r => {
            const d = r.datos || r;
            if ((d.tipo_registro || '') === 'rapido') return;
            totalSolicitados += parseInt(d.cantidad_solicitada || 0);
            totalEntregados  += parseInt(d.cantidad_total_entregadas || 0);
            totalDevueltos   += parseInt(d.cantidad_devueltas || 0);
        });

        const viewerUrl = `/template/empaque_v2/visor_empaque_v2.php?file=${encodeURIComponent(filename)}`;

        return `
        <div class="sp-turn-card sp-day-card" style="border-left: 3px solid #f59e0b;">
            <div class="sp-day-card-header">
                <div class="sp-turn-meta">📦 Lote: ${lote} &nbsp;|&nbsp; 📍 ${sede}</div>
                <div class="sp-turn-badge" style="background: rgba(245,158,11,0.15); color: #f59e0b; border-color: rgba(245,158,11,0.3);">
                    ${srcBadge(source)} · ${registros.length} REGISTRO${registros.length > 1 ? 'S' : ''}
                </div>
            </div>
            <div class="sp-turn-sub">
                🏷️ Referencia: <strong>${referencia}</strong><br>
                📋 Producto: ${producto}<br>
                👤 Responsable${responsables.length > 1 ? 's' : ''}: <strong>${responsables.join(', ') || 'N/D'}</strong>
            </div>
            <div class="sp-turno-chips">
                <span class="sp-turno-chip" style="background: rgba(245,158,11,0.06); color: #f59e0b; border-color: rgba(245,158,11,0.2);">Solicitados: ${totalSolicitados}</span>
                <span class="sp-turno-chip" style="background: rgba(245,158,11,0.06); color: #f59e0b; border-color: rgba(245,158,11,0.2);">Entregados: ${totalEntregados}</span>
                <span class="sp-turno-chip" style="background: rgba(245,158,11,0.06); color: #f59e0b; border-color: rgba(245,158,11,0.2);">Devueltos: ${totalDevueltos}</span>
            </div>
            <div class="sp-turn-actions">
                <a href="${viewerUrl}" target="_blank" class="btn-sp-view btn-sp-view-primary" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-color: transparent;">📄 VER CONTROL DE EMPAQUE</a>
            </div>
        </div>`;
    }

    // ─── TABS ─────────────────────────────────────────────────────────────────
    function switchTab(tab) {
        document.getElementById('tabFecha').classList.toggle('active', tab === 'fecha');
        document.getElementById('tabLote').classList.toggle('active', tab === 'lote');
        document.getElementById('tabMaquina').classList.toggle('active', tab === 'maquina');
        document.getElementById('tabBodega').classList.toggle('active', tab === 'bodega');
        document.getElementById('formFecha').classList.toggle('active', tab === 'fecha');
        document.getElementById('formLote').classList.toggle('active', tab === 'lote');
        document.getElementById('formMaquina').classList.toggle('active', tab === 'maquina');
        document.getElementById('formBodega').classList.toggle('active', tab === 'bodega');
        // Limpiar resultados al cambiar de tab
        clearResults();
    }

    function clearResults() {
        document.getElementById('spResultsGrid').innerHTML = '';
        document.getElementById('spEmpResultsGrid').innerHTML = '';
        document.getElementById('spBultoResultsGrid').innerHTML = '';
        document.getElementById('spMaqResultsGrid').innerHTML = '';
        document.getElementById('spBodResultsGrid').innerHTML = '';
        document.getElementById('molHeader').style.display = 'none';
        document.getElementById('empHeader').style.display = 'none';
        document.getElementById('bultoHeader').style.display = 'none';
        document.getElementById('maqHeader').style.display = 'none';
        document.getElementById('bodHeader').style.display = 'none';
        document.getElementById('spEmpStatusLine').style.display = 'none';
        document.getElementById('spBultoStatusLine').style.display = 'none';
        document.getElementById('spMaqStatusLine').style.display = 'none';
        document.getElementById('spBodStatusLine').style.display = 'none';
        document.getElementById('spStatusText').textContent = 'Selecciona un modo de búsqueda e ingresa los datos.';
        document.getElementById('spStatusLine').classList.remove('loading');
    }

    // ─── BÚSQUEDA POR LOTE ────────────────────────────────────────────────────
    function loteSearch() {
        const lote = document.getElementById('sp_lote').value.trim().toUpperCase();
        const sede = document.getElementById('sp_sede_lote').value;
        const btn  = document.getElementById('btnLoteSearch');

        if (!lote) { alert('Ingresa un lote de producto antes de buscar.'); return; }

        btn.disabled = true;
        document.getElementById('molHeader').style.display = 'flex';
        document.getElementById('empHeader').style.display = 'flex';
        document.getElementById('bultoHeader').style.display = 'flex';
        document.getElementById('spEmpStatusLine').style.display = 'flex';
        document.getElementById('spBultoStatusLine').style.display = 'flex';

        Promise.all([
            fetchMoliendaByLote(lote, sede),
            fetchEmpaqueByLote(lote, sede),
            fetchBultoByLote(lote, sede),
        ]).finally(() => { btn.disabled = false; });
    }

    function fetchMoliendaByLote(lote, sede) {
        const status = document.getElementById('spStatusLine');
        const text   = document.getElementById('spStatusText');
        const grid   = document.getElementById('spResultsGrid');

        status.classList.add('loading');
        text.textContent = `Buscando lote ${lote} en molienda (${sede})...`;
        grid.innerHTML = '';

        return fetch(`sp_reader.php?action=search_molienda_by_lote&lote=${encodeURIComponent(lote)}&sede=${sede}`)
            .then(r => r.json())
            .then(data => {
                status.classList.remove('loading');
                if (!data.success) {
                    text.textContent = `Sin resultados de molienda para lote ${lote}.`;
                    grid.innerHTML = `<div class="sp-error">🏭 ${data.error}</div>`;
                    return;
                }
                const turnos = data.turnos || [];
                text.textContent = `Molienda: lote ${lote} encontrado en ${turnos.length} turno(s) · ☁️ SharePoint`;
                // Agrupar por fecha para renderizar una card por día
                const porFecha = {};
                turnos.forEach(t => {
                    const f = t.fecha || '?';
                    if (!porFecha[f]) porFecha[f] = [];
                    porFecha[f].push(t);
                });
                grid.innerHTML = Object.entries(porFecha)
                    .map(([fecha, ts]) => renderDayCard(ts, fecha, sede))
                    .join('');
            })
            .catch(err => {
                status.classList.remove('loading');
                text.textContent = 'Error de conexión (molienda).';
                grid.innerHTML = `<div class="sp-error">❌ ${err.message}</div>`;
            });
    }

    function fetchEmpaqueByLote(lote, sede) {
        const status = document.getElementById('spEmpStatusLine');
        const text   = document.getElementById('spEmpStatusText');
        const grid   = document.getElementById('spEmpResultsGrid');

        status.classList.add('loading');
        text.textContent = `Buscando lote de producto ${lote} en empaque (${sede})...`;
        grid.innerHTML = '';

        return fetch(`sp_reader.php?action=search_empaque_by_lote_producto&lote=${encodeURIComponent(lote)}&sede=${sede}`)
            .then(r => r.json())
            .then(data => {
                status.classList.remove('loading');
                if (!data.success) {
                    text.textContent = `Sin resultados de empaque para lote ${lote}.`;
                    grid.innerHTML = `<div class="sp-error">📦 ${data.error}</div>`;
                    return;
                }
                const lotes = data.lotes || [];
                text.textContent = `Empaque: lote ${lote} en ${lotes.length} material(es) de empaque · ☁️ SharePoint`;
                grid.innerHTML = lotes.map(l => renderEmpCard(l.registros, l.lote, sede, l.file, l.source)).join('');
            })
            .catch(err => {
                status.classList.remove('loading');
                text.textContent = 'Error de conexión (empaque).';
                grid.innerHTML = `<div class="sp-error">❌ ${err.message}</div>`;
            });
    }

    // ─── CANTIDAD EN BULTO ───────────────────────────────────────────────────
    function fetchBultoByFecha(fecha, sede) {
        const status = document.getElementById('spBultoStatusLine');
        const text   = document.getElementById('spBultoStatusText');
        const grid   = document.getElementById('spBultoResultsGrid');

        status.classList.add('loading');
        text.textContent = `Buscando cantidad en bulto para ${fecha} (${sede})...`;
        grid.innerHTML = '';

        return fetch(`sp_reader.php?action=search_bulto_by_fecha&fecha=${fecha}&sede=${sede}`)
            .then(r => r.json())
            .then(data => {
                status.classList.remove('loading');
                if (!data.success) {
                    text.textContent = `Sin resultados de bulto para ${fecha}.`;
                    grid.innerHTML = `<div class="sp-error">⚖️ ${data.error || 'No se encontraron datos de bulto.'}</div>`;
                    return;
                }
                const productos = data.productos || [];
                text.textContent = `Bulto: ${productos.length} producto(s) registrado(s) el ${fecha} · ☁️ SharePoint`;
                grid.innerHTML = productos.map(p => renderBultoCard(p, sede)).join('');
            })
            .catch(err => {
                status.classList.remove('loading');
                text.textContent = 'Error de conexión (bulto).';
                grid.innerHTML = `<div class="sp-error">❌ ${err.message}</div>`;
            });
    }

    function fetchBultoByLote(lote, sede) {
        const status = document.getElementById('spBultoStatusLine');
        const text   = document.getElementById('spBultoStatusText');
        const grid   = document.getElementById('spBultoResultsGrid');

        status.classList.add('loading');
        text.textContent = `Buscando lote ${lote} en cantidad en bulto (${sede})...`;
        grid.innerHTML = '';

        return fetch(`sp_reader.php?action=search_bulto_by_lote&lote=${encodeURIComponent(lote)}&sede=${sede}`)
            .then(r => r.json())
            .then(data => {
                status.classList.remove('loading');
                if (!data.success) {
                    text.textContent = `Sin resultados de bulto para lote ${lote}.`;
                    grid.innerHTML = `<div class="sp-error">⚖️ ${data.error}</div>`;
                    return;
                }
                const productos = data.productos || [];
                text.textContent = `Bulto: lote ${lote} en ${productos.length} producto(s) · ☁️ SharePoint`;
                grid.innerHTML = productos.map(p => renderBultoCard(p, sede)).join('');
            })
            .catch(err => {
                status.classList.remove('loading');
                text.textContent = 'Error de conexión (bulto).';
                grid.innerHTML = `<div class="sp-error">❌ ${err.message}</div>`;
            });
    }

    function renderBultoCard(prod, sede) {
        const nombre = prod.producto || prod.file.replace('.json','');
        const registros = prod.registros || [];
        const total = registros.length;

        const fechas = [...new Set(registros.map(r => r.datos?.fecha).filter(Boolean))].sort();
        const lotes  = [...new Set(registros.map(r => r.datos?.lote).filter(Boolean))];
        const responsables = [...new Set(registros.map(r => r.datos?.responsable).filter(Boolean))];

        const fechaChips = fechas.map(f =>
            `<span class="sp-turno-chip" style="background:rgba(168,85,247,0.06);color:#a855f7;border-color:rgba(168,85,247,0.2);">📅 ${f}</span>`
        ).join('');
        const loteChips = lotes.map(l =>
            `<span class="sp-product-tag" style="background:rgba(168,85,247,0.08);color:#c084fc;border-color:rgba(168,85,247,0.15);">🏷️ ${l}</span>`
        ).join('');

        const viewerUrl = `/template/cantidad%20en%20bulto_v2/visor_cantidad_bulto.php?file=${encodeURIComponent(prod.file)}&sede=${encodeURIComponent(sede)}`;

        return `
        <div class="sp-turn-card sp-day-card" style="border-left: 3px solid #a855f7;">
            <div class="sp-day-card-header">
                <div class="sp-turn-meta">⚖️ ${nombre} &nbsp;|&nbsp; 📍 ${sede}</div>
                <div class="sp-turn-badge" style="background:rgba(168,85,247,0.15);color:#a855f7;border-color:rgba(168,85,247,0.3);">
                    ${srcBadge(prod.source)} · ${total} REGISTRO${total !== 1 ? 'S' : ''}
                </div>
            </div>
            ${fechaChips ? `<div class="sp-turno-chips">${fechaChips}</div>` : ''}
            <div class="sp-turn-sub">
                👤 Responsable${responsables.length > 1 ? 's' : ''}: <strong>${responsables.join(', ') || 'N/D'}</strong>
            </div>
            ${loteChips ? `<div class="sp-turn-products">${loteChips}</div>` : ''}
            <div class="sp-turn-actions">
                <a href="${viewerUrl}" target="_blank" class="btn-sp-view btn-sp-view-primary" style="background:linear-gradient(135deg,#a855f7 0%,#7c3aed 100%);border-color:transparent;">📄 VER CONTROL EN BULTO</a>
            </div>
        </div>`;
    }

    // ─── VERIFICACIÓN DE MÁQUINAS V2 ────────────────────────────────────────
    function maquinaSearch() {
        const codigo = document.getElementById('sp_codigo_maquina').value.trim().toUpperCase();
        const btn    = document.getElementById('btnMaquinaSearch');

        if (!codigo) { alert('Ingresa un código de máquina antes de buscar.'); return; }

        btn.disabled = true;
        document.getElementById('maqHeader').style.display = 'flex';
        fetchMaquinaByCodigo(codigo).finally(() => { btn.disabled = false; });
    }

    function fetchMaquinaByFecha(fecha) {
        const status = document.getElementById('spMaqStatusLine');
        const text   = document.getElementById('spMaqStatusText');
        const grid   = document.getElementById('spMaqResultsGrid');

        status.classList.add('loading');
        text.textContent = `Buscando verificaciones de máquinas para ${fecha}...`;
        grid.innerHTML = '';

        return fetch(`sp_reader.php?action=search_maquinas_by_fecha&fecha=${fecha}`)
            .then(r => r.json())
            .then(data => {
                status.classList.remove('loading');
                if (!data.success) {
                    text.textContent = `Sin verificaciones de máquinas para ${fecha}.`;
                    grid.innerHTML = `<div class="sp-empty"><svg viewBox="0 0 24 24"><path d="M13 9h-2V7h2m0 10h-2v-6h2m-1-9A10 10 0 0 0 2 12a10 10 0 0 0 10 10 10 10 0 0 0 10-10A10 10 0 0 0 12 2z"/></svg>Sin verificaciones de máquinas para <strong>${fecha}</strong>.</div>`;
                    return;
                }
                const registros = data.registros || [];
                text.textContent = `Máquinas: ${registros.length} verificación(es) el ${fecha}.`;
                grid.innerHTML = registros.map(r => renderMaqCard(r)).join('');
            })
            .catch(err => {
                status.classList.remove('loading');
                text.textContent = 'Error de conexión (máquinas).';
                grid.innerHTML = `<div class="sp-error">❌ ${err.message}</div>`;
            });
    }

    function fetchMaquinaByCodigo(codigo) {
        const status = document.getElementById('spMaqStatusLine');
        const text   = document.getElementById('spMaqStatusText');
        const grid   = document.getElementById('spMaqResultsGrid');

        status.style.display = 'flex';
        status.classList.add('loading');
        text.textContent = `Buscando verificaciones de ${codigo}...`;
        grid.innerHTML = '';

        return fetch(`sp_reader.php?action=search_maquinas_by_codigo&codigo=${encodeURIComponent(codigo)}`)
            .then(r => r.json())
            .then(data => {
                status.classList.remove('loading');
                if (!data.success) {
                    text.textContent = `Sin resultados para ${codigo}.`;
                    grid.innerHTML = `<div class="sp-error">⚙️ ${data.error || 'No se encontraron verificaciones.'}</div>`;
                    return;
                }
                const registros = data.registros || [];
                text.textContent = `Máquinas: ${registros.length} verificación(es) encontrada(s) para ${codigo}.`;
                grid.innerHTML = registros.map(r => renderMaqCard(r)).join('');
            })
            .catch(err => {
                status.classList.remove('loading');
                text.textContent = 'Error de conexión (máquinas).';
                grid.innerHTML = `<div class="sp-error">❌ ${err.message}</div>`;
            });
    }

    function renderMaqCard(reg) {
        const estado = reg.tipo_registro === 'correccion' ? '✏️ Corrección'
            : (reg.estado === 'borrador' ? '📝 Borrador' : '✅ Verificado');
        const fecha = reg.timestamp || 'N/D';
        const tecnico = reg.usuario_sys || 'N/D';
        const puedeVerLocal = reg.source !== 'sharepoint' && reg.id_registro;
        const viewerUrl = puedeVerLocal
            ? `/template/maquinas_v2/visor_verificacion.php?tipo=${encodeURIComponent(reg.tipo_maquina)}&maquina=${encodeURIComponent(reg.grupo_maquina)}&codigo=${encodeURIComponent(reg.codigo_maquina)}&id=${encodeURIComponent(reg.id_registro)}`
            : null;

        return `
        <div class="sp-turn-card sp-day-card" style="border-left: 3px solid #FF8A00;">
            <div class="sp-day-card-header">
                <div class="sp-turn-meta">⚙️ ${reg.codigo_maquina || 'N/D'} &nbsp;|&nbsp; ${reg.grupo_maquina || ''}</div>
                <div class="sp-turn-badge" style="background:rgba(255,138,0,0.15);color:#FF8A00;border-color:rgba(255,138,0,0.3);">
                    ${srcBadge(reg.source)}
                </div>
            </div>
            <div class="sp-turn-sub">
                📅 Fecha: <strong>${fecha}</strong><br>
                ${estado}<br>
                👤 Técnico: ${tecnico}
            </div>
            <div class="sp-turn-actions">
                ${viewerUrl ? `<a href="${viewerUrl}" target="_blank" class="btn-sp-view btn-sp-view-primary" style="background: linear-gradient(135deg, #FF8A00 0%, #d97706 100%); border-color: transparent;">📄 VER VERIFICACIÓN</a>` : ''}
            </div>
        </div>`;
    }

    // ─── INSPECCIÓN DE BODEGAS V2 ────────────────────────────────────────────
    const BODEGAS_POR_SEDE = <?= json_encode($BODEGAS_POR_SEDE, JSON_UNESCAPED_UNICODE) ?>;

    function actualizarBodegasSelect() {
        const sede = document.getElementById('sp_sede_bodega').value;
        const select = document.getElementById('sp_bodega');
        const bodegas = BODEGAS_POR_SEDE[sede] || {};
        select.innerHTML = Object.entries(bodegas)
            .map(([key, nombre]) => `<option value="${key}">${nombre}</option>`)
            .join('');
    }

    function bodegaSearch() {
        const bodegaKey = document.getElementById('sp_bodega').value;
        const sede = document.getElementById('sp_sede_bodega').value;
        const btn  = document.getElementById('btnBodegaSearch');

        if (!bodegaKey) { alert('Selecciona una bodega antes de buscar.'); return; }

        btn.disabled = true;
        document.getElementById('bodHeader').style.display = 'flex';
        fetchBodegaByBodega(bodegaKey, sede).finally(() => { btn.disabled = false; });
    }

    function fetchBodegaByBodega(bodegaKey, sede) {
        const status = document.getElementById('spBodStatusLine');
        const text   = document.getElementById('spBodStatusText');
        const grid   = document.getElementById('spBodResultsGrid');

        status.style.display = 'flex';
        status.classList.add('loading');
        text.textContent = `Buscando inspecciones de ${bodegaKey} (${sede})...`;
        grid.innerHTML = '';

        return fetch(`sp_reader.php?action=search_bodegas_by_bodega&bodega=${encodeURIComponent(bodegaKey)}&sede=${sede}`)
            .then(r => r.json())
            .then(data => {
                status.classList.remove('loading');
                if (!data.success) {
                    text.textContent = `Sin resultados para ${bodegaKey}.`;
                    grid.innerHTML = `<div class="sp-error">🏬 ${data.error || 'No se encontraron inspecciones.'}</div>`;
                    return;
                }
                const meses = data.meses || [];
                text.textContent = `Bodegas: ${meses.length} documento(s) mensual(es) encontrado(s) para ${bodegaKey}.`;
                grid.innerHTML = meses.map(m => renderBodCard(m, sede)).join('');
            })
            .catch(err => {
                status.classList.remove('loading');
                text.textContent = 'Error de conexión (bodegas).';
                grid.innerHTML = `<div class="sp-error">❌ ${err.message}</div>`;
            });
    }

    // Cada tarjeta representa UN documento (el mes completo de inspecciones
    // de la bodega), no un registro individual.
    function renderBodCard(mes, sede) {
        const registros = mes.registros || [];
        const total = registros.length;

        let sumaPct = 0, conPct = 0;
        registros.forEach(r => {
            const d = r.datos || r;
            let si = 0, no = 0;
            for (let i = 1; i <= 14; i++) {
                if (d['opcion' + i] === 'SI') si++;
                if (d['opcion' + i] === 'NO') no++;
            }
            if ((si + no) > 0) { sumaPct += (si / (si + no)) * 100; conPct++; }
        });
        const promedio = conPct > 0 ? Math.round((sumaPct / conPct) * 10) / 10 : null;
        const pctColor = promedio === null ? '#7a8599' : (promedio < 60 ? '#f85149' : (promedio < 80 ? '#d29922' : '#3fb950'));

        const bodegaNombre = registros[0]?.bodega_nombre || mes.file.replace('.json', '');
        const viewerUrl = `/template/bodegas_v2/visor_bodegas_v2.php?file=${encodeURIComponent(mes.file)}&sede=${encodeURIComponent(sede)}`;

        return `
        <div class="sp-turn-card sp-day-card" style="border-left: 3px solid #10B981;">
            <div class="sp-day-card-header">
                <div class="sp-turn-meta">🏬 ${bodegaNombre} &nbsp;|&nbsp; 📅 ${mes.periodo}</div>
                <div class="sp-turn-badge" style="background:rgba(16,185,129,0.15);color:#10B981;border-color:rgba(16,185,129,0.3);">
                    ${srcBadge(mes.source)} · ${total} INSPECCIÓN${total !== 1 ? 'ES' : ''}
                </div>
            </div>
            <div class="sp-turn-sub">
                ${promedio !== null ? `% Cumplimiento promedio: <strong style="color:${pctColor};">${promedio}%</strong><br>` : ''}
                📍 Sede: ${sede}
            </div>
            <div class="sp-turn-actions">
                <a href="${viewerUrl}" target="_blank" class="btn-sp-view btn-sp-view-primary" style="background: linear-gradient(135deg, #10B981 0%, #059669 100%); border-color: transparent;">📄 VER DOCUMENTO DEL MES</a>
            </div>
        </div>`;
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('sp_fecha').addEventListener('keydown', e => {
            if (e.key === 'Enter') unifiedSearch();
        });
        document.getElementById('sp_lote').addEventListener('keydown', e => {
            if (e.key === 'Enter') loteSearch();
        });
        document.getElementById('sp_codigo_maquina').addEventListener('keydown', e => {
            if (e.key === 'Enter') maquinaSearch();
        });
        // Forzar uppercase en el input de lote mientras escribe
        document.getElementById('sp_lote').addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
        document.getElementById('sp_codigo_maquina').addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
        actualizarBodegasSelect();
    });
</script>


</body>
</html>
