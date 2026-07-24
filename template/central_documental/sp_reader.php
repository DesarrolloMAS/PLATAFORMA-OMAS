<?php
/**
 * sp_reader.php — Búsqueda unificada: Repositorio Local + SharePoint
 *
 * Cada acción ejecuta DOS búsquedas en paralelo lógico:
 *   1. Local  → Lee directamente los JSON de /archivos/generados/ (PHP puro, sin red)
 *   2. SP     → Llama a sp_reader.js que consulta Microsoft Graph API
 *
 * Los resultados se fusionan y deduплican:
 *   - Molienda : por campo 'id' de cada turno
 *   - Empaque / Bulto : por nombre de archivo (lote / producto)
 *
 * Cada ítem del response lleva un campo 'source': 'local' | 'sharepoint' | 'both'
 */
header('Content-Type: application/json; charset=utf-8');
ini_set('max_execution_time', 60);

require '../sesion.php';

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['success' => false, 'error' => 'Sesión expirada.']);
    exit;
}

$action = $_GET['action'] ?? '';
$sede   = $_GET['sede']   ?? ($_SESSION['sede'] ?? 'ZC');
$fecha  = $_GET['fecha']  ?? '';

$SP_BASE            = 'Documentos compartidos/Codificación Documentos OMAS';
$SP_FOLDER_MOLIENDA = 'Molienda V2';
$SP_FOLDER_EMPAQUE  = 'Control de Empaque V2';
$SP_FOLDER_BULTO    = 'Control de Cantidad Producto en Bulto';
$SP_FOLDER_MAQUINAS = 'Verificación de Máquinas V2';
$SP_FOLDER_BODEGAS  = 'Inspección de Bodegas V2';
$LOCAL_BASE         = '/var/www/fmt/archivos/generados/';
$MAQUINAS_GALERIA   = __DIR__ . '/../maquinas_v2/maquinas_galeria.json';

$readerScript = __DIR__ . '/sp_reader.js';

// ═══════════════════════════════════════════════════════════════════
//  HELPERS SharePoint
// ═══════════════════════════════════════════════════════════════════

function runSpReader(string $action, string $path): array {
    global $readerScript;
    $cmd = 'node ' . escapeshellarg($readerScript)
         . ' ' . escapeshellarg($action)
         . ' ' . escapeshellarg($path)
         . ' 2>&1';
    $output = shell_exec($cmd);
    $result = json_decode($output, true);
    if (!$result) {
        return ['success' => false, 'error' => 'sp_reader.js: ' . substr($output ?? '', 0, 300)];
    }
    return $result;
}

// ═══════════════════════════════════════════════════════════════════
//  HELPERS BÚSQUEDA LOCAL
// ═══════════════════════════════════════════════════════════════════

/** Lee el JSON mensual de molienda y filtra por fecha. */
function localMoliendaByFecha(string $fecha, string $sede, string $base): array {
    $ym   = substr($fecha, 0, 7);
    $file = $base . "molienda/{$sede}/{$ym}.json";
    if (!file_exists($file)) return [];
    $data = json_decode(file_get_contents($file), true) ?? [];
    return array_values(array_filter($data, fn($r) =>
        ($r['fecha'] ?? '') === $fecha && ($r['sede'] ?? '') === $sede
    ));
}

/** Escanea todos los JSON mensuales de molienda buscando el lote. */
function localMoliendaByLote(string $lote, string $sede, string $base): array {
    $dir = $base . "molienda/{$sede}/";
    if (!is_dir($dir)) return [];
    $found = [];
    foreach (glob($dir . '*.json') as $f) {
        foreach (json_decode(file_get_contents($f), true) ?? [] as $r) {
            if (loteEnRegistro($r, $lote)) $found[] = $r;
        }
    }
    return $found;
}

/** Escanea los JSON de lote de empaque filtrando por fecha_alistamiento. */
function localEmpaqueByFecha(string $fecha, string $sede, string $base): array {
    $dir = $base . "empaque_v2/{$sede}/";
    if (!is_dir($dir)) return [];
    $byFile = [];
    foreach (glob($dir . '*.json') as $f) {
        $name = basename($f);
        $data = json_decode(file_get_contents($f), true) ?? [];
        $hit  = array_filter($data, fn($r) =>
            (($r['datos'] ?? $r)['fecha_alistamiento'] ?? '') === $fecha
        );
        if ($hit) {
            $byFile[$name] = [
                'file'      => $name,
                'lote'      => preg_replace('/^EMPAQUE_LOTE_(.+)\.json$/i', '$1', $name),
                'registros' => array_values($data),
                'source'    => 'local',
            ];
        }
    }
    return $byFile;
}

/** Escanea los JSON de lote de empaque filtrando por lote_producto. */
function localEmpaqueByLote(string $lote, string $sede, string $base): array {
    $dir = $base . "empaque_v2/{$sede}/";
    if (!is_dir($dir)) return [];
    $byFile = [];
    foreach (glob($dir . '*.json') as $f) {
        $name = basename($f);
        $data = json_decode(file_get_contents($f), true) ?? [];
        $hit  = array_filter($data, fn($r) =>
            strtoupper(trim(($r['datos'] ?? $r)['lote_producto'] ?? '')) === $lote
        );
        if ($hit) {
            $byFile[$name] = [
                'file'      => $name,
                'lote'      => preg_replace('/^EMPAQUE_LOTE_(.+)\.json$/i', '$1', $name),
                'registros' => array_values($data),
                'source'    => 'local',
            ];
        }
    }
    return $byFile;
}

/** Escanea los JSON de producto en bulto filtrando registros por fecha. */
function localBultoByFecha(string $fecha, string $sede, string $base): array {
    $dir = $base . "cantidad_bulto/{$sede}/";
    if (!is_dir($dir)) return [];
    $byFile = [];
    foreach (glob($dir . '*.json') as $f) {
        $name = basename($f);
        $data = json_decode(file_get_contents($f), true) ?? [];
        $hit  = array_values(array_filter($data, fn($r) =>
            ($r['datos']['fecha'] ?? '') === $fecha
        ));
        if ($hit) {
            $byFile[$name] = [
                'file'      => $name,
                'producto'  => $hit[0]['datos']['harina'] ?? preg_replace('/\.json$/i', '', $name),
                'registros' => $hit,
                'source'    => 'local',
            ];
        }
    }
    return $byFile;
}

/** Escanea los JSON de producto en bulto filtrando registros por lote. */
function localBultoByLote(string $lote, string $sede, string $base): array {
    $dir = $base . "cantidad_bulto/{$sede}/";
    if (!is_dir($dir)) return [];
    $byFile = [];
    foreach (glob($dir . '*.json') as $f) {
        $name = basename($f);
        $data = json_decode(file_get_contents($f), true) ?? [];
        $hit  = array_values(array_filter($data, fn($r) =>
            strtoupper(trim($r['datos']['lote'] ?? '')) === $lote
        ));
        if ($hit) {
            $byFile[$name] = [
                'file'      => $name,
                'producto'  => $hit[0]['datos']['harina'] ?? preg_replace('/\.json$/i', '', $name),
                'registros' => $hit,
                'source'    => 'local',
            ];
        }
    }
    return $byFile;
}

/** Normaliza un código de máquina igual que sanear_ruta() en maquinas_v2/procesar.php. */
function sanearCodigoMaquina(string $valor): string {
    return preg_replace('/_+/', '_', preg_replace('/[^A-Za-z0-9_\-]/', '_', trim($valor)));
}

/**
 * Construye un índice código_saneado -> {tipo, grupo} a partir del catálogo
 * estático de maquinas_v2 (maquinas_galeria.json), para poder resolver en
 * qué carpeta buscar en SharePoint sin tener que recorrer las ~14 carpetas
 * de grupo existentes.
 */
function indiceMaquinas(string $rutaGaleria): array {
    if (!file_exists($rutaGaleria)) return [];
    $galeria = json_decode(file_get_contents($rutaGaleria), true) ?? [];
    $indice = [];
    foreach ($galeria as $tipo => $grupos) {
        foreach ($grupos as $grupo => $codigos) {
            foreach ($codigos as $codigoRaw) {
                $indice[sanearCodigoMaquina($codigoRaw)] = ['tipo' => $tipo, 'grupo' => $grupo];
            }
        }
    }
    return $indice;
}

/** Lista los pares únicos {tipo, grupo} del catálogo (para listar sus carpetas en SharePoint). */
function gruposMaquinas(string $rutaGaleria): array {
    if (!file_exists($rutaGaleria)) return [];
    $galeria = json_decode(file_get_contents($rutaGaleria), true) ?? [];
    $pares = [];
    foreach ($galeria as $tipo => $grupos) {
        foreach ($grupos as $grupo => $codigos) {
            $pares[] = ['tipo' => $tipo, 'grupo' => $grupo];
        }
    }
    return $pares;
}

/** Escanea todo el árbol local de maquinas_v2 buscando registros de una fecha específica. */
function localMaquinasByFecha(string $fecha, string $base): array {
    $dirBase = $base . 'maquinas_v2/';
    if (!is_dir($dirBase)) return [];

    $encontrados = [];
    foreach (glob($dirBase . '*', GLOB_ONLYDIR) as $tipoDir) {
        $tipo = basename($tipoDir);
        foreach (glob($tipoDir . '/*', GLOB_ONLYDIR) as $grupoDir) {
            $grupo = basename($grupoDir);
            foreach (glob($grupoDir . '/*.json') as $f) {
                $cod = pathinfo($f, PATHINFO_FILENAME);
                $registros = json_decode(file_get_contents($f), true) ?? [];
                foreach ($registros as $r) {
                    if (substr($r['timestamp'] ?? '', 0, 10) !== $fecha) continue;
                    $encontrados[] = array_merge($r, [
                        'tipo_maquina' => $tipo,
                        'grupo_maquina' => $grupo,
                        'codigo_maquina' => $cod,
                    ]);
                }
            }
        }
    }
    return $encontrados;
}

/** Escanea todo el árbol local de maquinas_v2 buscando los registros de un código. */
function localMaquinasByCodigo(string $codigo, string $base): array {
    $codigo = strtoupper(sanearCodigoMaquina($codigo));
    $dirBase = $base . 'maquinas_v2/';
    if (!is_dir($dirBase)) return [];

    $encontrados = [];
    foreach (glob($dirBase . '*', GLOB_ONLYDIR) as $tipoDir) {
        $tipo = basename($tipoDir);
        foreach (glob($tipoDir . '/*', GLOB_ONLYDIR) as $grupoDir) {
            $grupo = basename($grupoDir);
            foreach (glob($grupoDir . '/*.json') as $f) {
                $cod = pathinfo($f, PATHINFO_FILENAME);
                if (strtoupper($cod) !== $codigo) continue;
                $registros = json_decode(file_get_contents($f), true) ?? [];
                foreach ($registros as $r) {
                    $encontrados[] = array_merge($r, [
                        'tipo_maquina' => $tipo,
                        'grupo_maquina' => $grupo,
                        'codigo_maquina' => $cod,
                    ]);
                }
            }
        }
    }
    return $encontrados;
}

/**
 * Escanea localmente los JSON mensuales ({bodegaKey}_*.json) de una bodega en
 * una sede. Cada archivo es UN documento (el mes completo de inspecciones de
 * esa bodega), no un registro por ítem.
 */
function localBodegasByBodega(string $bodegaKey, string $sede, string $base): array {
    $dir = $base . "bodegas_v2/{$sede}/";
    if (!is_dir($dir)) return [];

    $byFile = [];
    foreach (glob($dir . $bodegaKey . '_*.json') as $f) {
        $name = basename($f);
        $registros = json_decode(file_get_contents($f), true) ?: [];
        if ($registros) {
            $byFile[$name] = [
                'file'      => $name,
                'periodo'   => preg_replace('/^' . preg_quote($bodegaKey, '/') . '_/', '', str_replace('.json', '', $name)),
                'registros' => $registros,
            ];
        }
    }
    return $byFile;
}

/** Fusiona registros de máquina (local + SP) por id_registro. SP tiene precedencia. */
function mergeMaquinaRegistros(array $local, array $sp): array {
    $map = [];
    foreach ($local as $r) {
        $k = $r['id_registro'] ?? md5(json_encode($r));
        $map[$k] = array_merge($r, ['source' => 'local']);
    }
    foreach ($sp as $r) {
        $k = $r['id_registro'] ?? md5(json_encode($r));
        $map[$k] = array_merge($r, ['source' => isset($map[$k]) ? 'both' : 'sharepoint']);
    }
    return array_values($map);
}

// ═══════════════════════════════════════════════════════════════════
//  HELPERS FUSIÓN
// ═══════════════════════════════════════════════════════════════════

/**
 * Fusiona listas de turnos de molienda por campo 'id'.
 * SP tiene precedencia; si un turno está en ambos queda 'both'.
 */
function mergeTurnos(array $local, array $sp): array {
    $map = [];
    foreach ($local as $t) {
        $k = $t['id'] ?? md5(json_encode($t));
        $map[$k] = array_merge($t, ['source' => 'local']);
    }
    foreach ($sp as $t) {
        $k = $t['id'] ?? md5(json_encode($t));
        $map[$k] = array_merge($t, ['source' => isset($map[$k]) ? 'both' : 'sharepoint']);
    }
    return array_values($map);
}

/**
 * Fusiona resultados agrupados por nombre de archivo (empaque, bulto).
 * SP tiene precedencia; si un archivo está en ambas fuentes queda 'both'.
 */
function mergeByFile(array $local, array $sp): array {
    $result = [];
    foreach ($local as $k => $v) {
        $result[$k] = array_merge($v, ['source' => 'local']);
    }
    foreach ($sp as $k => $v) {
        $result[$k] = array_merge($v, ['source' => isset($result[$k]) ? 'both' : 'sharepoint']);
    }
    return array_values($result);
}

/** Función auxiliar: ¿contiene este turno de molienda el lote buscado? */
function loteEnRegistro(array $registro, string $lote): bool {
    foreach (['harinas', 'subproductos', 'materiales'] as $grupo) {
        foreach ($registro[$grupo] ?? [] as $producto) {
            foreach ($producto['lotes'] ?? [] as $l) {
                if (strtoupper(trim($l['id'] ?? '')) === $lote) return true;
            }
        }
    }
    foreach ($registro['trigo'] ?? [] as $t) {
        if (strtoupper(trim($t['lote'] ?? '')) === $lote) return true;
    }
    return false;
}

// ═══════════════════════════════════════════════════════════════════
//  ACCIÓN: Listar meses disponibles
// ═══════════════════════════════════════════════════════════════════
if ($action === 'list_months') {
    $result = runSpReader('list', $SP_BASE);
    if (!$result['success']) { echo json_encode($result); exit; }
    $months = array_filter($result['items'] ?? [], fn($i) =>
        $i['isFolder'] && preg_match('/^\d{4}-\d{2}$/', $i['name'])
    );
    $months = array_values($months);
    usort($months, fn($a, $b) => strcmp($b['name'], $a['name']));
    echo json_encode(['success' => true, 'months' => array_column($months, 'name')]);
    exit;
}

// ═══════════════════════════════════════════════════════════════════
//  ACCIÓN: Molienda por fecha  (Local + SP)
// ═══════════════════════════════════════════════════════════════════
if ($action === 'search') {
    if (empty($fecha) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
        echo json_encode(['success' => false, 'error' => 'Parámetro "fecha" requerido (YYYY-MM-DD).']);
        exit;
    }

    $ym           = substr($fecha, 0, 7);
    $monthsToTry  = array_unique([$ym, date('Y-m')]);
    $atomicFile   = "Molienda_{$sede}_{$fecha}.json";

    // ── 1. Búsqueda local ────────────────────────────────────────
    $localTurnos = localMoliendaByFecha($fecha, $sede, $LOCAL_BASE);

    // ── 2. Búsqueda SharePoint ───────────────────────────────────
    $spTurnos = [];
    $spSource = null;

    foreach ($monthsToTry as $m) {
        $r = runSpReader('read', "{$SP_BASE}/{$m}/{$SP_FOLDER_MOLIENDA}/{$sede}/{$atomicFile}");
        if ($r['success'] && !empty($r['data'])) {
            $spTurnos = array_values(array_filter(
                is_array($r['data']) ? $r['data'] : [$r['data']],
                fn($t) => isset($t['fecha'])
            ));
            $spSource = 'sharepoint_atomic';
            break;
        }
    }

    if (empty($spTurnos)) {
        $r = runSpReader('read', "{$SP_BASE}/{$ym}/{$SP_FOLDER_MOLIENDA}/{$sede}/{$ym}.json");
        if ($r['success'] && !empty($r['data'])) {
            $spTurnos = array_values(array_filter(
                is_array($r['data']) ? $r['data'] : [],
                fn($t) => ($t['fecha'] ?? '') === $fecha && ($t['sede'] ?? '') === $sede
            ));
            if ($spTurnos) $spSource = 'sharepoint_monthly';
        }
    }

    // ── 3. Fusionar ──────────────────────────────────────────────
    $merged = mergeTurnos($localTurnos, $spTurnos);
    if (empty($merged)) {
        echo json_encode(['success' => false, 'error' => "Sin registros de molienda para {$fecha} (Sede: {$sede}) en local ni en SharePoint."]);
        exit;
    }

    $srcCounts = array_count_values(array_column($merged, 'source'));
    echo json_encode([
        'success'     => true,
        'source'      => $spSource ?? 'local',
        'src_counts'  => $srcCounts,
        'fecha'       => $fecha,
        'sede'        => $sede,
        'turnos'      => $merged,
        'total'       => count($merged),
    ]);
    exit;
}

// ═══════════════════════════════════════════════════════════════════
//  ACCIÓN: Molienda por lote  (Local + SP)
// ═══════════════════════════════════════════════════════════════════
if ($action === 'search_molienda_by_lote') {
    $lote = strtoupper(trim($_GET['lote'] ?? ''));
    if (empty($lote)) {
        echo json_encode(['success' => false, 'error' => 'Parámetro "lote" requerido.']);
        exit;
    }

    // ── 1. Búsqueda local ────────────────────────────────────────
    $localTurnos = localMoliendaByLote($lote, $sede, $LOCAL_BASE);

    // ── 2. Búsqueda SharePoint (últimos 6 meses) ─────────────────
    $spTurnos = [];
    $seen     = [];
    for ($i = 0; $i < 6; $i++) {
        $m = date('Y-m', strtotime("-{$i} months"));
        $lr = runSpReader('list', "{$SP_BASE}/{$m}/{$SP_FOLDER_MOLIENDA}/{$sede}");
        if (!($lr['success'] ?? false)) continue;
        foreach (array_filter($lr['items'] ?? [], fn($f) => !($f['isFolder'] ?? false) && str_ends_with($f['name'], '.json')) as $fi) {
            if (isset($seen[$fi['name']])) continue;
            $seen[$fi['name']] = true;
            $fr = runSpReader('read', "{$SP_BASE}/{$m}/{$SP_FOLDER_MOLIENDA}/{$sede}/{$fi['name']}");
            if (!($fr['success'] ?? false) || empty($fr['data'])) continue;
            foreach (is_array($fr['data']) ? $fr['data'] : [$fr['data']] as $r) {
                if (loteEnRegistro($r, $lote)) $spTurnos[] = $r;
            }
        }
    }

    // ── 3. Fusionar ──────────────────────────────────────────────
    $merged = mergeTurnos($localTurnos, $spTurnos);
    if (empty($merged)) {
        echo json_encode(['success' => false, 'error' => "Lote {$lote} no encontrado en molienda (Sede: {$sede})."]);
        exit;
    }
    usort($merged, fn($a, $b) =>
        strcmp($a['fecha'] ?? '', $b['fecha'] ?? '') ?: (($a['turno'] ?? 0) - ($b['turno'] ?? 0))
    );

    $srcCounts = array_count_values(array_column($merged, 'source'));
    echo json_encode([
        'success'    => true,
        'source'     => 'merged',
        'src_counts' => $srcCounts,
        'lote'       => $lote,
        'sede'       => $sede,
        'turnos'     => $merged,
        'total'      => count($merged),
    ]);
    exit;
}

// ═══════════════════════════════════════════════════════════════════
//  ACCIÓN: Empaque por fecha  (Local + SP)
// ═══════════════════════════════════════════════════════════════════
if ($action === 'search_empaque_by_fecha') {
    if (empty($fecha) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
        echo json_encode(['success' => false, 'error' => 'Parámetro "fecha" requerido (YYYY-MM-DD).']);
        exit;
    }

    $ym = substr($fecha, 0, 7);

    // ── 1. Búsqueda local ────────────────────────────────────────
    $localByFile = localEmpaqueByFecha($fecha, $sede, $LOCAL_BASE);

    // ── 2. Búsqueda SharePoint ───────────────────────────────────
    $monthsToTry = [$ym];
    for ($i = 1; $i <= 3; $i++) {
        $monthsToTry[] = date('Y-m', strtotime("{$ym}-01 -{$i} months"));
    }
    $monthsToTry[] = date('Y-m');
    $monthsToTry   = array_unique($monthsToTry);

    $spByFile = [];
    foreach ($monthsToTry as $m) {
        $lr = runSpReader('list', "{$SP_BASE}/{$m}/{$SP_FOLDER_EMPAQUE}/{$sede}");
        if (!($lr['success'] ?? false)) continue;
        foreach (array_filter($lr['items'] ?? [], fn($f) => !($f['isFolder'] ?? false) && str_ends_with($f['name'], '.json')) as $fi) {
            $name = $fi['name'];
            if (isset($spByFile[$name])) continue;
            $fr = runSpReader('read', "{$SP_BASE}/{$m}/{$SP_FOLDER_EMPAQUE}/{$sede}/{$name}");
            if (!($fr['success'] ?? false) || empty($fr['data'])) continue;
            $regs = is_array($fr['data']) ? $fr['data'] : [$fr['data']];
            $hit  = array_filter($regs, fn($r) => (($r['datos'] ?? $r)['fecha_alistamiento'] ?? '') === $fecha);
            if ($hit) {
                $spByFile[$name] = [
                    'file'      => $name,
                    'lote'      => preg_replace('/^EMPAQUE_LOTE_(.+)\.json$/i', '$1', $name),
                    'registros' => $regs,
                ];
            }
        }
    }

    // ── 3. Fusionar ──────────────────────────────────────────────
    $merged = mergeByFile($localByFile, $spByFile);
    if (empty($merged)) {
        echo json_encode(['success' => false, 'error' => "Sin registros de empaque para {$fecha} (Sede: {$sede}) en local ni en SharePoint."]);
        exit;
    }
    echo json_encode([
        'success' => true,
        'source'  => 'merged',
        'fecha'   => $fecha,
        'sede'    => $sede,
        'lotes'   => $merged,
        'total'   => count($merged),
    ]);
    exit;
}

// ═══════════════════════════════════════════════════════════════════
//  ACCIÓN: Empaque por lote de producto  (Local + SP)
// ═══════════════════════════════════════════════════════════════════
if ($action === 'search_empaque_by_lote_producto') {
    $lote = strtoupper(trim($_GET['lote'] ?? ''));
    if (empty($lote)) {
        echo json_encode(['success' => false, 'error' => 'Parámetro "lote" requerido.']);
        exit;
    }

    // ── 1. Búsqueda local ────────────────────────────────────────
    $localByFile = localEmpaqueByLote($lote, $sede, $LOCAL_BASE);

    // ── 2. Búsqueda SharePoint (últimos 6 meses) ─────────────────
    $spByFile = [];
    for ($i = 0; $i < 6; $i++) {
        $m = date('Y-m', strtotime("-{$i} months"));
        $lr = runSpReader('list', "{$SP_BASE}/{$m}/{$SP_FOLDER_EMPAQUE}/{$sede}");
        if (!($lr['success'] ?? false)) continue;
        foreach (array_filter($lr['items'] ?? [], fn($f) => !($f['isFolder'] ?? false) && str_ends_with($f['name'], '.json')) as $fi) {
            $name = $fi['name'];
            if (isset($spByFile[$name])) continue;
            $fr = runSpReader('read', "{$SP_BASE}/{$m}/{$SP_FOLDER_EMPAQUE}/{$sede}/{$name}");
            if (!($fr['success'] ?? false) || empty($fr['data'])) continue;
            $regs = is_array($fr['data']) ? $fr['data'] : [$fr['data']];
            $hit  = array_filter($regs, fn($r) =>
                strtoupper(trim(($r['datos'] ?? $r)['lote_producto'] ?? '')) === $lote
            );
            if ($hit) {
                $spByFile[$name] = [
                    'file'      => $name,
                    'lote'      => preg_replace('/^EMPAQUE_LOTE_(.+)\.json$/i', '$1', $name),
                    'registros' => $regs,
                ];
            }
        }
    }

    // ── 3. Fusionar ──────────────────────────────────────────────
    $merged = mergeByFile($localByFile, $spByFile);
    if (empty($merged)) {
        echo json_encode(['success' => false, 'error' => "Lote de producto {$lote} no encontrado en empaque (Sede: {$sede})."]);
        exit;
    }
    echo json_encode([
        'success' => true,
        'source'  => 'merged',
        'lote'    => $lote,
        'sede'    => $sede,
        'lotes'   => $merged,
        'total'   => count($merged),
    ]);
    exit;
}

// ═══════════════════════════════════════════════════════════════════
//  ACCIÓN: Cantidad en Bulto por fecha  (Local + SP)
// ═══════════════════════════════════════════════════════════════════
if ($action === 'search_bulto_by_fecha') {
    if (empty($fecha) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
        echo json_encode(['success' => false, 'error' => 'Parámetro "fecha" requerido (YYYY-MM-DD).']);
        exit;
    }

    $ym = substr($fecha, 0, 7);

    // ── 1. Búsqueda local ────────────────────────────────────────
    $localByFile = localBultoByFecha($fecha, $sede, $LOCAL_BASE);

    // ── 2. Búsqueda SharePoint ───────────────────────────────────
    $monthsToTry = [$ym];
    for ($i = 1; $i <= 3; $i++) {
        $monthsToTry[] = date('Y-m', strtotime("{$ym}-01 -{$i} months"));
    }
    $monthsToTry[] = date('Y-m');
    $monthsToTry   = array_unique($monthsToTry);

    $spByFile = [];
    foreach ($monthsToTry as $m) {
        $lr = runSpReader('list', "{$SP_BASE}/{$m}/{$SP_FOLDER_BULTO}/{$sede}");
        if (!($lr['success'] ?? false)) continue;
        foreach (array_filter($lr['items'] ?? [], fn($f) => !($f['isFolder'] ?? false) && str_ends_with($f['name'], '.json')) as $fi) {
            $name = $fi['name'];
            if (isset($spByFile[$name])) continue;
            $fr = runSpReader('read', "{$SP_BASE}/{$m}/{$SP_FOLDER_BULTO}/{$sede}/{$name}");
            if (!($fr['success'] ?? false) || empty($fr['data'])) continue;
            $regs = is_array($fr['data']) ? $fr['data'] : [$fr['data']];
            $hit  = array_values(array_filter($regs, fn($r) => ($r['datos']['fecha'] ?? '') === $fecha));
            if ($hit) {
                $spByFile[$name] = [
                    'file'      => $name,
                    'producto'  => $hit[0]['datos']['harina'] ?? preg_replace('/\.json$/i', '', $name),
                    'registros' => $hit,
                ];
            }
        }
    }

    // ── 3. Fusionar ──────────────────────────────────────────────
    $merged = mergeByFile($localByFile, $spByFile);
    if (empty($merged)) {
        echo json_encode(['success' => false, 'error' => "Sin registros de cantidad en bulto para {$fecha} (Sede: {$sede}) en local ni en SharePoint."]);
        exit;
    }
    echo json_encode([
        'success'   => true,
        'source'    => 'merged',
        'fecha'     => $fecha,
        'sede'      => $sede,
        'productos' => $merged,
        'total'     => count($merged),
    ]);
    exit;
}

// ═══════════════════════════════════════════════════════════════════
//  ACCIÓN: Cantidad en Bulto por lote  (Local + SP)
// ═══════════════════════════════════════════════════════════════════
if ($action === 'search_bulto_by_lote') {
    $lote = strtoupper(trim($_GET['lote'] ?? ''));
    if (empty($lote)) {
        echo json_encode(['success' => false, 'error' => 'Parámetro "lote" requerido.']);
        exit;
    }

    // ── 1. Búsqueda local ────────────────────────────────────────
    $localByFile = localBultoByLote($lote, $sede, $LOCAL_BASE);

    // ── 2. Búsqueda SharePoint (últimos 6 meses) ─────────────────
    $spByFile = [];
    for ($i = 0; $i < 6; $i++) {
        $m = date('Y-m', strtotime("-{$i} months"));
        $lr = runSpReader('list', "{$SP_BASE}/{$m}/{$SP_FOLDER_BULTO}/{$sede}");
        if (!($lr['success'] ?? false)) continue;
        foreach (array_filter($lr['items'] ?? [], fn($f) => !($f['isFolder'] ?? false) && str_ends_with($f['name'], '.json')) as $fi) {
            $name = $fi['name'];
            if (isset($spByFile[$name])) continue;
            $fr = runSpReader('read', "{$SP_BASE}/{$m}/{$SP_FOLDER_BULTO}/{$sede}/{$name}");
            if (!($fr['success'] ?? false) || empty($fr['data'])) continue;
            $regs = is_array($fr['data']) ? $fr['data'] : [$fr['data']];
            $hit  = array_values(array_filter($regs, fn($r) =>
                strtoupper(trim($r['datos']['lote'] ?? '')) === $lote
            ));
            if ($hit) {
                $spByFile[$name] = [
                    'file'      => $name,
                    'producto'  => $hit[0]['datos']['harina'] ?? preg_replace('/\.json$/i', '', $name),
                    'registros' => $hit,
                ];
            }
        }
    }

    // ── 3. Fusionar ──────────────────────────────────────────────
    $merged = mergeByFile($localByFile, $spByFile);
    if (empty($merged)) {
        echo json_encode(['success' => false, 'error' => "Lote {$lote} no encontrado en cantidad en bulto (Sede: {$sede})."]);
        exit;
    }
    echo json_encode([
        'success'   => true,
        'source'    => 'merged',
        'lote'      => $lote,
        'sede'      => $sede,
        'productos' => $merged,
        'total'     => count($merged),
    ]);
    exit;
}

// ═══════════════════════════════════════════════════════════════════
//  ACCIÓN: Máquinas por fecha  (Local + SP)
//  El mes queda determinado por la fecha misma (a diferencia de la
//  búsqueda por código/lote, aquí no hace falta probar varios meses),
//  así que el barrido en SharePoint es listar 1 sola vez cada una de las
//  ~13 carpetas de grupo del catálogo, filtrando por nombre de archivo
//  (el JSON atómico ya lleva la fecha en el nombre) — sin leer contenido
//  salvo en los archivos que ya coincidieron por nombre.
// ═══════════════════════════════════════════════════════════════════
if ($action === 'search_maquinas_by_fecha') {
    if (empty($fecha) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
        echo json_encode(['success' => false, 'error' => 'Parámetro "fecha" requerido (YYYY-MM-DD).']);
        exit;
    }
    $ym = substr($fecha, 0, 7);

    // ── 1. Búsqueda local ────────────────────────────────────────
    $localRegistros = localMaquinasByFecha($fecha, $LOCAL_BASE);

    // ── 2. Búsqueda SharePoint (un único mes, una pasada por cada grupo) ──
    $spRegistros = [];
    foreach (gruposMaquinas($MAQUINAS_GALERIA) as $par) {
        $tipo = $par['tipo'];
        $grupo = $par['grupo'];
        $lr = runSpReader('list', "{$SP_BASE}/{$ym}/{$SP_FOLDER_MAQUINAS}/{$tipo}/{$grupo}");
        if (!($lr['success'] ?? false)) continue;

        foreach (array_filter($lr['items'] ?? [], fn($f) =>
            !($f['isFolder'] ?? false) && str_ends_with($f['name'], '.json') && strpos($f['name'], "_{$fecha}_") !== false
        ) as $fi) {
            $fr = runSpReader('read', "{$SP_BASE}/{$ym}/{$SP_FOLDER_MAQUINAS}/{$tipo}/{$grupo}/{$fi['name']}");
            if (!($fr['success'] ?? false) || empty($fr['data'])) continue;
            $regs = (is_array($fr['data']) && isset($fr['data']['id_registro'])) ? [$fr['data']] : (is_array($fr['data']) ? $fr['data'] : []);
            foreach ($regs as $r) {
                $spRegistros[] = array_merge($r, ['tipo_maquina' => $tipo, 'grupo_maquina' => $grupo]);
            }
        }
    }

    // ── 3. Fusionar ──────────────────────────────────────────────
    $merged = mergeMaquinaRegistros($localRegistros, $spRegistros);
    if (empty($merged)) {
        echo json_encode(['success' => false, 'error' => "Sin verificaciones de máquinas para {$fecha}."]);
        exit;
    }
    usort($merged, fn($a, $b) => strcmp($a['codigo_maquina'] ?? '', $b['codigo_maquina'] ?? ''));

    $srcCounts = array_count_values(array_column($merged, 'source'));
    echo json_encode([
        'success'    => true,
        'source'     => 'merged',
        'src_counts' => $srcCounts,
        'fecha'      => $fecha,
        'registros'  => $merged,
        'total'      => count($merged),
    ]);
    exit;
}

// ═══════════════════════════════════════════════════════════════════
//  ACCIÓN: Máquinas por código  (Local + SP)
// ═══════════════════════════════════════════════════════════════════
if ($action === 'search_maquinas_by_codigo') {
    $codigo = trim($_GET['codigo'] ?? '');
    if (empty($codigo)) {
        echo json_encode(['success' => false, 'error' => 'Parámetro "codigo" requerido.']);
        exit;
    }
    $codigoSaneado = sanearCodigoMaquina($codigo);

    // ── 1. Búsqueda local ────────────────────────────────────────
    $localRegistros = localMaquinasByCodigo($codigoSaneado, $LOCAL_BASE);

    // ── 2. Búsqueda SharePoint (solo si el catálogo resuelve tipo/grupo,
    //       para no tener que barrer las ~14 carpetas de grupo existentes) ──
    $spRegistros = [];
    $indice = indiceMaquinas($MAQUINAS_GALERIA);
    $ubicacion = $indice[strtoupper($codigoSaneado)] ?? null;
    if (!$ubicacion) {
        // Fallback: buscar coincidencia por prefijo (el índice guarda claves tal cual el catálogo)
        foreach ($indice as $cod => $info) {
            if (strtoupper($cod) === strtoupper($codigoSaneado)) { $ubicacion = $info; break; }
        }
    }

    if ($ubicacion) {
        $tipo = $ubicacion['tipo'];
        $grupo = $ubicacion['grupo'];
        for ($i = 0; $i < 6; $i++) {
            $m = date('Y-m', strtotime("-{$i} months"));
            $lr = runSpReader('list', "{$SP_BASE}/{$m}/{$SP_FOLDER_MAQUINAS}/{$tipo}/{$grupo}");
            if (!($lr['success'] ?? false)) continue;
            foreach (array_filter($lr['items'] ?? [], fn($f) => !($f['isFolder'] ?? false) && str_ends_with($f['name'], '.json')) as $fi) {
                if (stripos($fi['name'], "MaqV2_{$codigoSaneado}_") !== 0) continue;
                $fr = runSpReader('read', "{$SP_BASE}/{$m}/{$SP_FOLDER_MAQUINAS}/{$tipo}/{$grupo}/{$fi['name']}");
                if (!($fr['success'] ?? false) || empty($fr['data'])) continue;
                $regs = is_array($fr['data']) && isset($fr['data']['id_registro']) ? [$fr['data']] : (is_array($fr['data']) ? $fr['data'] : []);
                foreach ($regs as $r) {
                    $spRegistros[] = array_merge($r, ['tipo_maquina' => $tipo, 'grupo_maquina' => $grupo, 'codigo_maquina' => $codigoSaneado]);
                }
            }
        }
    }

    // ── 3. Fusionar ──────────────────────────────────────────────
    $merged = mergeMaquinaRegistros($localRegistros, $spRegistros);
    if (empty($merged)) {
        echo json_encode(['success' => false, 'error' => "Sin registros para la máquina \"{$codigo}\" en local ni en SharePoint."]);
        exit;
    }
    usort($merged, fn($a, $b) => strcmp($b['timestamp'] ?? '', $a['timestamp'] ?? ''));

    $srcCounts = array_count_values(array_column($merged, 'source'));
    echo json_encode([
        'success'    => true,
        'source'     => 'merged',
        'src_counts' => $srcCounts,
        'codigo'     => $codigoSaneado,
        'registros'  => $merged,
        'total'      => count($merged),
    ]);
    exit;
}

// ═══════════════════════════════════════════════════════════════════
//  ACCIÓN: Inspecciones de Bodega por bodega  (Local + SP)
//  Cada archivo ({bodegaKey}_{YYYY-MM}.json) es UN documento (el mes
//  completo de inspecciones de esa bodega) — igual que empaque/bulto por
//  lote, se fusiona por nombre de archivo, no por registro individual.
// ═══════════════════════════════════════════════════════════════════
if ($action === 'search_bodegas_by_bodega') {
    $bodegaKey = preg_replace('/[^A-Za-z0-9_-]/', '', trim($_GET['bodega'] ?? ''));
    if (empty($bodegaKey)) {
        echo json_encode(['success' => false, 'error' => 'Parámetro "bodega" requerido.']);
        exit;
    }

    // ── 1. Búsqueda local ────────────────────────────────────────
    $localByFile = localBodegasByBodega($bodegaKey, $sede, $LOCAL_BASE);

    // ── 2. Búsqueda SharePoint (últimos 6 meses) ─────────────────
    $spByFile = [];
    for ($i = 0; $i < 6; $i++) {
        $m = date('Y-m', strtotime("-{$i} months"));
        $lr = runSpReader('list', "{$SP_BASE}/{$m}/{$SP_FOLDER_BODEGAS}/{$sede}");
        if (!($lr['success'] ?? false)) continue;
        foreach (array_filter($lr['items'] ?? [], fn($f) => !($f['isFolder'] ?? false) && str_ends_with($f['name'], '.json')) as $fi) {
            $name = $fi['name'];
            if (stripos($name, "{$bodegaKey}_") !== 0 || isset($spByFile[$name])) continue;
            $fr = runSpReader('read', "{$SP_BASE}/{$m}/{$SP_FOLDER_BODEGAS}/{$sede}/{$name}");
            if (!($fr['success'] ?? false) || empty($fr['data'])) continue;
            $spByFile[$name] = [
                'file'      => $name,
                'periodo'   => preg_replace('/^' . preg_quote($bodegaKey, '/') . '_/', '', str_replace('.json', '', $name)),
                'registros' => is_array($fr['data']) ? $fr['data'] : [$fr['data']],
            ];
        }
    }

    // ── 3. Fusionar (por nombre de archivo = mes) ────────────────
    $merged = mergeByFile($localByFile, $spByFile);
    if (empty($merged)) {
        echo json_encode(['success' => false, 'error' => "Sin inspecciones para la bodega \"{$bodegaKey}\" (Sede: {$sede}) en local ni en SharePoint."]);
        exit;
    }
    usort($merged, fn($a, $b) => strcmp($b['periodo'] ?? '', $a['periodo'] ?? ''));

    echo json_encode([
        'success' => true,
        'source'  => 'merged',
        'bodega'  => $bodegaKey,
        'sede'    => $sede,
        'meses'   => $merged,
        'total'   => count($merged),
    ]);
    exit;
}

// ─── Acción desconocida ───────────────────────────────────────────────────────
echo json_encode(['success' => false, 'error' => "Acción desconocida: \"{$action}\"."]);
