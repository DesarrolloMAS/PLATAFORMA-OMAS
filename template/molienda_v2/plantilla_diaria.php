<?php
require '../conection.php'; // Conexión a la base de datos
require '../sesion.php';
verificarAutenticacion();

$sede = $_GET['sede'] ?? $_SESSION['sede'];
$fecha = $_GET['fecha'] ?? date('Y-m-d');
$mesFile = substr($fecha, 0, 7) . '.json';

$file_path = "../../archivos/generados/molienda/" . $sede . "/" . $mesFile;
$recordsDia = [];

if (file_exists($file_path)) {
    $records = json_decode(file_get_contents($file_path), true) ?: [];
    foreach ($records as $r) {
        if ($r['fecha'] === $fecha) {
            $recordsDia[] = $r;
        }
    }
} else {
    // Fallback: Consultar en tiempo real a SharePoint en memoria (sin guardar archivo en disco)
    // Ruta privada de JSONs del sistema (separada de la galería pública de PDFs)
    $SP_BASE = 'Documentos compartidos/Documentos Generados OMAS';
    $SP_FOLDER_MOLIENDA = 'Molienda V2';
    $yearMonth = substr($fecha, 0, 7);
    $readerScript = __DIR__ . '/../central_documental/sp_reader.js';

    if (file_exists($readerScript)) {
        // 1. Intentar con el JSON atómico del día
        $atomicFileName = "Molienda_{$sede}_{$fecha}.json";
        $atomicPath     = "{$SP_BASE}/{$yearMonth}/{$SP_FOLDER_MOLIENDA}/{$sede}/{$atomicFileName}";
        
        $cmd = 'node ' . escapeshellarg($readerScript)
             . ' read ' . escapeshellarg($atomicPath)
             . ' 2>&1';
        $output = shell_exec($cmd);
        $result = json_decode($output, true);

        if ($result && $result['success'] && !empty($result['data'])) {
            $registros = is_array($result['data']) ? $result['data'] : [$result['data']];
            $recordsDia = array_values(array_filter($registros, fn($r) => isset($r['fecha'])));
        } else {
            // 2. Si falla el atómico, intentar buscar dentro del mensual consolidado en SP
            $monthlyFileName = "{$yearMonth}.json";
            $monthlyPath     = "{$SP_BASE}/{$yearMonth}/{$SP_FOLDER_MOLIENDA}/{$sede}/{$monthlyFileName}";
            
            $cmd = 'node ' . escapeshellarg($readerScript)
                 . ' read ' . escapeshellarg($monthlyPath)
                 . ' 2>&1';
            $output = shell_exec($cmd);
            $result = json_decode($output, true);

            if ($result && $result['success'] && !empty($result['data'])) {
                $todos = is_array($result['data']) ? $result['data'] : [];
                $recordsDia = array_values(array_filter($todos, function($r) use ($fecha, $sede) {
                    return ($r['fecha'] ?? '') === $fecha && ($r['sede'] ?? '') === $sede;
                }));
            }
        }
    }
}

usort($recordsDia, function($a, $b) {
    // Si tienen campo 'turno', priorizarlo
    if (isset($a['turno']) && isset($b['turno'])) {
        return $a['turno'] - $b['turno'];
    }
    // Fallback: usar el orden en que llegaron (o hora si no hay más remedio)
    return strtotime($a['created_at'] ?? $a['hora']) - strtotime($b['created_at'] ?? $b['hora']);
});

// Asignación explícita por número de turno si existe, sino por posición
$turno1 = null; $turno2 = null; $turno3 = null;
foreach ($recordsDia as $r) {
    $tNum = $r['turno'] ?? null;
    if ($tNum == 1) $turno1 = $r;
    elseif ($tNum == 2) $turno2 = $r;
    elseif ($tNum == 3) $turno3 = $r;
}

// Si no se asignaron por campo 'turno' (registros viejos), usar posición
if (!$turno1 && !$turno2 && !$turno3) {
    $turno1 = $recordsDia[0] ?? null;
    $turno2 = $recordsDia[1] ?? null;
    $turno3 = $recordsDia[2] ?? null;
}

$responsable = $turno1['responsable'] ?? ($turno2['responsable'] ?? ($turno3['responsable'] ?? ''));

// Obtener el número consecutivo (N° de Molienda)
$numeroMolienda = '--';
try {
    $stmtId = $pdoControl->prepare("SELECT MAX(id_proceso) as num FROM control_molienda WHERE fecha = ? AND zona = ?");
    $stmtId->execute([$fecha, $sede]);
    $rowId = $stmtId->fetch(PDO::FETCH_ASSOC);
    if (!empty($rowId['num'])) {
        $numeroMolienda = $rowId['num'];
    }
} catch (Exception $e) {
    // Si la BD de turnos temporales está fallando no detenemos el renderizado
}

// Configuración Dinámica de Productos como Diccionario
$config_url = "../../archivos/generados/molienda/config_{$sede}.json";
$diccionarioNombres = [];
$diccionarioHarinasIds = []; // Para identificar cuáles son harinas en el cálculo final

if (file_exists($config_url)) {
    $dynamicCfg = json_decode(file_get_contents($config_url), true);
    if ($dynamicCfg) {
        foreach (['harinas', 'subproductos', 'materiales'] as $cat) {
            foreach (($dynamicCfg[$cat] ?? []) as $item) {
                $diccionarioNombres[$item['id']] = $item['name'];
                if ($cat === 'harinas') $diccionarioHarinasIds[] = $item['id'];
            }
        }
    }
}

// Analizar qué productos y materiales fueron REALMENTE USADOS en cualquiera de los 3 turnos
$usadosProductos = [];
$usadosMateriales = [];

foreach ([$turno1, $turno2, $turno3] as $t) {
    if (!$t) continue;
    // Extraer harinas y subs
    foreach (['harinas', 'subproductos'] as $c) {
        if (!empty($t[$c])) {
            foreach ($t[$c] as $id => $data) {
                if ($data['active'] === 'on') {
                    if (!in_array($id, $usadosProductos)) {
                        $usadosProductos[] = $id;
                    }
                    // Forzar su reconocimiento como harina para el cálculo de extracción
                    if ($c === 'harinas' && !in_array($id, $diccionarioHarinasIds)) {
                        $diccionarioHarinasIds[] = $id;
                    }
                }
            }
        }
    }
    // Extraer materiales
    if (!empty($t['materiales'])) {
        foreach ($t['materiales'] as $id => $data) {
            if ($data['active'] === 'on' && !in_array($id, $usadosMateriales)) {
                $usadosMateriales[] = $id;
            }
        }
    }
}

$productos = [];
foreach ($usadosProductos as $id) {
    if (isset($diccionarioNombres[$id])) {
        $productos[] = ['id' => $id, 'name' => $diccionarioNombres[$id]];
    } else {
        $productos[] = ['id' => $id, 'name' => str_replace('_', ' ', strtoupper($id)) . ' (Inactivo)'];
    }
}
$materiales = [];
foreach ($usadosMateriales as $id) {
    if (isset($diccionarioNombres[$id])) {
        $materiales[] = ['id' => $id, 'name' => $diccionarioNombres[$id]];
    } else {
        $n = strtoupper($id);
        $materiales[] = ['id' => $id, 'name' => str_replace(['_', 'EMP_'], [' ', 'EMPAQUE '], $n) . ' (Inactivo)'];
    }
}

function getValLotes($dataTurno, $key, $id) {
    if (!$dataTurno) return ['val' => 0, 'kilos' => 0, 'lotes' => '', 'val_lines' => '', 'raw' => []];
    $cat = null;
    $catN = '';
    if (isset($dataTurno['harinas'][$id])) { $cat = $dataTurno['harinas'][$id]; $catN = 'harinas'; }
    elseif (isset($dataTurno['subproductos'][$id])) { $cat = $dataTurno['subproductos'][$id]; $catN = 'subproductos'; }
    elseif (isset($dataTurno['materiales'][$id])) { $cat = $dataTurno['materiales'][$id]; $catN = 'materiales'; }
    
    if (!$cat || !$cat['active']) return ['val' => 0, 'kilos' => 0, 'lotes' => '', 'val_lines' => '', 'raw' => []];
    
    $totalVal = 0;
    $pesoUnit = floatval($cat['peso_unit'] ?? 1);
    $loteArr = [];
    $valLinesArr = [];
    $rawArr = [];

    foreach ($cat['lotes'] as $idx => $l) {
        $v = floatval($l['valor']);
        $totalVal += $v;
        if (!empty($l['id']) || $v > 0) {
            $loteArr[] = !empty($l['id']) ? $l['id'] : '&nbsp;';
            $valLinesArr[] = $v > 0 ? $v : '&nbsp;';
            $rawArr[] = [
                'idx' => $idx,
                'cat' => $catN,
                'v' => $v > 0 ? $v : '',
                'id' => !empty($l['id']) ? $l['id'] : ''
            ];
        }
    }
    
    $kilos = $totalVal * $pesoUnit;
    
    return [
        'val' => $totalVal, 
        'kilos' => $kilos, 
        'lotes' => implode('<br>', $loteArr), 
        'val_lines' => implode('<br>', $valLinesArr),
        'raw' => $rawArr
    ];
}

// Exportar valores actuales por producto/turno para el panel de edición de estructura
$currentTurnValues = [];
$todosParaValores = array_unique(array_merge($usadosProductos, $usadosMateriales));
foreach ($todosParaValores as $pid) {
    $currentTurnValues[$pid] = [];
    foreach ([1 => $turno1, 2 => $turno2, 3 => $turno3] as $tNum => $tData) {
        $res         = getValLotes($tData, null, $pid);
        $totalBultos = $res['val'];
        $firstLote   = '';
        foreach ($res['raw'] as $r) {
            if ($r['v'] !== '' && floatval($r['v']) > 0 && !$firstLote) {
                $firstLote = $r['id'];
            }
        }
        // Exportar todos los lotes existentes por turno usando $res['raw'] (ya calculado por getValLotes)
        $allLotes = [];
        foreach ($res['raw'] as $r) {
            $v = floatval($r['v'] ?? 0);
            $n = (string)($r['id'] ?? '');
            if ($v > 0 || $n !== '') {
                $allLotes[] = ['bultos' => $v, 'lote' => $n];
            }
        }
        $currentTurnValues[$pid][$tNum] = [
            'lotes'     => $allLotes,   // todos los lotes reales para pre-rellenar
            'total'     => $totalBultos,
            'totalLote' => $firstLote,
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Plantilla Molienda Diario - <?= $fecha ?></title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Space+Mono:wght@700&display=swap');
        
        body { margin: 0; padding: 40px 20px; font-family: 'Inter', sans-serif; background: #eef2f6; color: #1e293b; }
        .sheet {
            width: 100%;
            max-width: 1450px;
            margin: 0 auto;
            border-collapse: collapse;
            font-size: 10px;
            background: #fff;
            box-shadow: 0 10px 30px rgba(0,0,0,0.06);
        }
        .sheet th, .sheet td {
            border: 1px solid #cbd5e1;
            padding: 6px 4px;
            text-align: center;
            vertical-align: middle;
        }
        
        .title-block {
            background: #0f172a;
            color: #fff;
            font-weight: 800;
            font-size: 16px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }
        .header-red { 
            color: #e11d48; 
            font-size: 16px; 
            font-weight: 800; 
            font-family: 'Space Mono', monospace; 
            letter-spacing: 2px;
        }
        .logo-img { max-width: 140px; padding: 10px; }
        
        /* Celdas especiales */
        .bg-gray { background: #f8fafc; color: #334155; font-weight: 700; }
        .row-head { 
            background: #e2e8f0; 
            color: #0f172a; 
            font-weight: 800; 
            font-size: 9px; 
            letter-spacing: 0.5px;
        }
        .ref-col { text-align: left !important; font-weight: 700; color: #0f172a; background: #fbfbfb; }
        
        /* Botón de Imprimir Moderno */
        .print-btn {
            position: fixed; bottom: 30px; right: 30px;
            background: #2563eb; color: #fff; padding: 14px 28px;
            text-decoration: none; border-radius: 50px; font-size: 13px; font-weight: 700;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.4);
            transition: all 0.3s;
            z-index: 1000;
        }
        .print-btn:hover { background: #1d4ed8; transform: translateY(-3px); box-shadow: 0 8px 25px rgba(37, 99, 235, 0.5); }
        
        /* Botón de Guardar SAP */
        .save-sap-btn {
            position: fixed; bottom: 30px; right: 260px; /* Al lado del print btn */
            background: #475569; color: #fff; padding: 14px 28px;
            text-decoration: none; border-radius: 50px; font-size: 13px; font-weight: 700;
            box-shadow: 0 4px 15px rgba(71, 85, 105, 0.4);
            transition: all 0.3s;
            cursor: pointer; border: none;
            z-index: 1000;
        }
        .save-sap-btn:hover { background: #334155; transform: translateY(-3px); box-shadow: 0 8px 25px rgba(71, 85, 105, 0.5); }
        .sap-cell { cursor: text; transition: all 0.2s; }
        .sap-cell:focus { outline: 2px solid #2563eb; background: #eff6ff !important; }

        /* Botón de Editar Estructura */
        .structure-btn {
            position: fixed; bottom: 30px; right: 460px;
            background: linear-gradient(135deg, #7c3aed, #4f46e5); color: #fff; padding: 14px 28px;
            text-decoration: none; border-radius: 50px; font-size: 13px; font-weight: 700;
            box-shadow: 0 4px 15px rgba(124, 58, 237, 0.4);
            transition: all 0.3s;
            cursor: pointer; border: none;
            z-index: 1000;
        }
        .structure-btn:hover { background: linear-gradient(135deg, #6d28d9, #4338ca); transform: translateY(-3px); box-shadow: 0 8px 25px rgba(124, 58, 237, 0.5); }
        .structure-btn.active { background: linear-gradient(135deg, #e11d48, #be123c); box-shadow: 0 4px 15px rgba(225, 29, 72, 0.5); }

        /* Sidebar Overlay */
        .structure-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
            z-index: 2000;
            opacity: 0; pointer-events: none;
            transition: opacity 0.35s ease;
        }
        .structure-overlay.open { opacity: 1; pointer-events: all; }

        .structure-panel {
            position: fixed; top: 0; right: -480px; width: 460px; height: 100%;
            background: linear-gradient(180deg, #0f172a 0%, #1e1b4b 100%);
            border-left: 2px solid rgba(124, 58, 237, 0.5);
            box-shadow: -10px 0 40px rgba(0, 0, 0, 0.5);
            z-index: 2001;
            transition: right 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            display: flex; flex-direction: column;
            overflow: hidden;
        }
        .structure-overlay.open .structure-panel { right: 0; }

        .sp-header {
            padding: 28px 25px 20px;
            background: rgba(124, 58, 237, 0.08);
            border-bottom: 1px solid rgba(124, 58, 237, 0.2);
        }
        .sp-header h2 {
            font-family: 'Inter', sans-serif; font-size: 18px; font-weight: 800;
            color: #c4b5fd; letter-spacing: 1px; text-transform: uppercase; margin: 0 0 4px;
        }
        .sp-header p {
            font-size: 12px; color: #94a3b8; margin: 0;
        }
        .sp-close {
            position: absolute; top: 20px; right: 20px;
            background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.12);
            color: #94a3b8; width: 36px; height: 36px; border-radius: 50%;
            cursor: pointer; font-size: 18px; display: flex; align-items: center; justify-content: center;
            transition: all 0.2s;
        }
        .sp-close:hover { background: rgba(225, 29, 72, 0.2); color: #fca5a5; border-color: rgba(225, 29, 72, 0.4); }

        .sp-body {
            flex: 1; overflow-y: auto; padding: 20px 25px;
            scrollbar-width: thin; scrollbar-color: rgba(124,58,237,0.3) transparent;
        }
        .sp-body::-webkit-scrollbar { width: 6px; }
        .sp-body::-webkit-scrollbar-thumb { background: rgba(124,58,237,0.3); border-radius: 3px; }

        .sp-section-title {
            font-size: 11px; font-weight: 700; color: #7c3aed;
            text-transform: uppercase; letter-spacing: 1.5px;
            margin: 20px 0 10px; padding-bottom: 6px;
            border-bottom: 1px solid rgba(124, 58, 237, 0.15);
        }
        .sp-section-title:first-child { margin-top: 0; }

        .sp-item {
            display: flex; align-items: center; justify-content: space-between;
            padding: 10px 14px; margin-bottom: 6px;
            border-radius: 8px; transition: all 0.2s;
            font-size: 12px; font-weight: 600;
        }
        .sp-item-active {
            background: rgba(34, 197, 94, 0.08);
            border: 1px solid rgba(34, 197, 94, 0.2);
            color: #bbf7d0;
        }
        .sp-item-active:hover { background: rgba(34, 197, 94, 0.14); }
        .sp-item-available {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.06);
            color: #94a3b8;
        }
        .sp-item-available:hover { background: rgba(124, 58, 237, 0.1); border-color: rgba(124, 58, 237, 0.3); color: #c4b5fd; }

        .sp-item-name { flex: 1; }
        .sp-item-cat {
            font-size: 9px; background: rgba(255,255,255,0.06); color: #64748b;
            padding: 2px 8px; border-radius: 10px; margin-right: 10px; text-transform: uppercase; letter-spacing: 0.5px;
        }
        .sp-item-btn {
            width: 30px; height: 30px; border-radius: 50%; border: none;
            cursor: pointer; font-size: 14px; font-weight: 700;
            display: flex; align-items: center; justify-content: center;
            transition: all 0.2s;
        }
        .sp-btn-remove {
            background: rgba(225, 29, 72, 0.12); color: #fb7185;
        }
        .sp-btn-remove:hover { background: rgba(225, 29, 72, 0.3); color: #fff; transform: scale(1.1); }
        .sp-btn-add {
            background: rgba(34, 197, 94, 0.12); color: #4ade80;
        }
        .sp-btn-add:hover { background: rgba(34, 197, 94, 0.3); color: #fff; transform: scale(1.1); }

        .sp-footer {
            padding: 20px 25px;
            border-top: 1px solid rgba(124, 58, 237, 0.2);
            background: rgba(0, 0, 0, 0.2);
        }
        .sp-footer-info {
            font-size: 11px; color: #64748b; margin-bottom: 12px; text-align: center;
        }
        .sp-footer-info strong { color: #22c55e; }
        .sp-footer-info .del-count { color: #f43f5e; }

        .sp-apply-btn {
            width: 100%; padding: 14px; border: none; border-radius: 10px;
            background: linear-gradient(135deg, #7c3aed, #4f46e5);
            color: #fff; font-size: 14px; font-weight: 700;
            cursor: pointer; text-transform: uppercase; letter-spacing: 1px;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(124, 58, 237, 0.3);
        }
        .sp-apply-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(124, 58, 237, 0.5); }
        .sp-apply-btn:disabled { opacity: 0.4; cursor: not-allowed; transform: none; box-shadow: none; }

        .sp-empty {
            text-align: center; padding: 20px; color: #475569; font-size: 12px; font-style: italic;
        }

        /* ── Sub-formulario de valores al agregar harinas ── */
        .sp-val-form {
            margin-top: 8px;
            background: rgba(124, 58, 237, 0.06);
            border: 1px solid rgba(124, 58, 237, 0.2);
            border-radius: 10px;
            padding: 14px 14px 10px;
            animation: slideDown 0.25s ease;
        }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-8px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .sp-val-form-title {
            font-size: 10px; font-weight: 700; color: #a78bfa;
            text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px;
        }
        .sp-turn-tabs {
            display: flex; gap: 6px; margin-bottom: 10px;
        }
        .sp-turn-tab {
            flex: 1; padding: 6px 4px; border: 1px solid rgba(124,58,237,0.3);
            background: rgba(255,255,255,0.03); color: #94a3b8;
            border-radius: 6px; font-size: 11px; font-weight: 600;
            cursor: pointer; text-align: center; transition: all 0.2s;
        }
        .sp-turn-tab.selected {
            background: rgba(124,58,237,0.25); border-color: rgba(124,58,237,0.6);
            color: #c4b5fd;
        }
        .sp-turn-tab:hover:not(.selected) {
            background: rgba(124,58,237,0.1); color: #c4b5fd;
        }
        .sp-val-fields {
            display: flex; gap: 8px;
        }
        .sp-val-field {
            flex: 1;
        }
        .sp-val-field label {
            display: block; font-size: 10px; color: #7c3aed;
            font-weight: 700; letter-spacing: 0.5px; margin-bottom: 4px;
        }
        .sp-val-field input {
            width: 100%; box-sizing: border-box;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(124,58,237,0.3);
            border-radius: 6px; padding: 7px 8px;
            color: #e2e8f0; font-size: 12px; font-weight: 600;
            outline: none; transition: all 0.2s;
        }
        .sp-val-field input:focus {
            border-color: rgba(124,58,237,0.7);
            background: rgba(124,58,237,0.08);
            box-shadow: 0 0 0 2px rgba(124,58,237,0.12);
        }
        .sp-val-field input::placeholder { color: #475569; }
        .sp-val-confirm-btn {
            width: 100%; margin-top: 10px; padding: 8px;
            background: linear-gradient(135deg, #22c55e, #16a34a);
            border: none; border-radius: 7px; color: #fff;
            font-size: 12px; font-weight: 700; cursor: pointer;
            transition: all 0.2s; letter-spacing: 0.5px;
        }
        .sp-val-confirm-btn:hover { opacity: 0.9; transform: translateY(-1px); }

        /* ── Sección de materiales dentro del formulario de harina ── */
        .sp-mat-divider {
            height: 1px; background: rgba(124,58,237,0.15); margin: 14px 0;
        }
        .sp-mat-section-title {
            font-size: 10px; font-weight: 700; color: #818cf8;
            text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px;
        }
        .sp-mat-list { margin-bottom: 6px; }
        .sp-mat-empty {
            font-size: 11px; color: #475569; font-style: italic;
            text-align: center; padding: 6px 0;
        }
        .sp-mat-item {
            display: flex; align-items: center; gap: 6px;
            padding: 5px 0; border-bottom: 1px solid rgba(255,255,255,0.04);
        }
        .sp-mat-item:last-child { border-bottom: none; }
        .sp-mat-item-name {
            flex: 1; font-size: 11px; font-weight: 600; color: #cbd5e1;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .sp-mat-lote-input {
            width: 95px; box-sizing: border-box;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(99,102,241,0.3);
            border-radius: 5px; padding: 5px 7px;
            color: #e2e8f0; font-size: 11px; outline: none; transition: all 0.2s;
        }
        .sp-mat-lote-input:focus {
            border-color: rgba(99,102,241,0.7); background: rgba(99,102,241,0.08);
        }
        .sp-mat-lote-input::placeholder { color: #475569; font-size: 10px; }
        .sp-mat-remove-btn {
            width: 22px; height: 22px; border-radius: 50%; border: none; flex-shrink: 0;
            background: rgba(225,29,72,0.1); color: #fb7185;
            font-size: 11px; font-weight: 700; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: all 0.2s;
        }
        .sp-mat-remove-btn:hover { background: rgba(225,29,72,0.3); color: #fff; }
        .sp-mat-add-row {
            display: flex; gap: 6px; align-items: center; margin-top: 6px;
        }
        .sp-mat-select {
            flex: 1; background: #0d0d1a; border: 1px solid rgba(99,102,241,0.2);
            border-radius: 6px; padding: 6px 8px;
            color: #94a3b8; font-size: 11px; outline: none; cursor: pointer;
            transition: border-color 0.2s;
        }
        .sp-mat-select:focus { border-color: rgba(99,102,241,0.5); }
        .sp-mat-add-btn {
            padding: 6px 12px; border: none; border-radius: 6px;
            background: rgba(99,102,241,0.2); color: #818cf8;
            font-size: 11px; font-weight: 700; cursor: pointer;
            transition: all 0.2s; white-space: nowrap;
        }
        .sp-mat-add-btn:hover { background: rgba(99,102,241,0.4); color: #fff; }

        /* ── Formulario edición de valores en harinas ya activas (modo ámbar) ── */
        .sp-edit-form {
            margin-top: 8px;
            background: rgba(245, 158, 11, 0.05);
            border: 1px solid rgba(245, 158, 11, 0.2);
            border-radius: 10px;
            padding: 14px 14px 10px;
            animation: slideDown 0.25s ease;
        }
        .sp-edit-indicator {
            font-size: 10px; color: #6ee7b7;
            background: rgba(34,197,94,0.08);
            border-radius: 5px; padding: 4px 10px; margin-bottom: 8px;
            border: 1px solid rgba(34,197,94,0.15);
        }
        .sp-edit-save-btn {
            width: 100%; margin-top: 10px; padding: 9px;
            background: linear-gradient(135deg, #d97706, #b45309);
            border: none; border-radius: 7px; color: #fff;
            font-size: 12px; font-weight: 700; cursor: pointer;
            transition: all 0.2s; letter-spacing: 0.5px;
            box-shadow: 0 3px 10px rgba(217,119,6,0.3);
        }
        .sp-edit-save-btn:hover { opacity: 0.9; transform: translateY(-1px); }

        /* ── Filas de multi-lote en el formulario de edición ── */
        .sp-lote-row {
            display: flex; gap: 5px; align-items: center; margin-bottom: 5px;
        }
        .sp-lote-row-num {
            background: rgba(255,255,255,0.04); border: 1px solid rgba(99,102,241,0.2);
            border-radius: 5px; padding: 5px 6px; color: #e2e8f0; font-size: 11px;
            outline: none; transition: all 0.2s; width: 68px; box-sizing: border-box;
        }
        .sp-lote-row-num:focus { border-color: rgba(99,102,241,0.6); background: rgba(99,102,241,0.08); }
        .sp-lote-row-id {
            background: rgba(255,255,255,0.04); border: 1px solid rgba(99,102,241,0.2);
            border-radius: 5px; padding: 5px 6px; color: #e2e8f0; font-size: 11px;
            outline: none; transition: all 0.2s; flex: 1; min-width: 0;
        }
        .sp-lote-row-id:focus { border-color: rgba(99,102,241,0.6); background: rgba(99,102,241,0.08); }
        .sp-lote-row-del {
            width: 22px; height: 22px; flex-shrink: 0; border-radius: 50%; border: none;
            background: rgba(225,29,72,0.1); color: #fb7185;
            font-size: 12px; font-weight: 700; cursor: pointer;
            display: flex; align-items: center; justify-content: center; transition: all 0.2s;
        }
        .sp-lote-row-del:hover { background: rgba(225,29,72,0.3); color: #fff; }
        .sp-lote-hdr {
            display: flex; gap: 5px; margin-bottom: 4px;
            font-size: 9px; font-weight: 700; color: #64748b;
            text-transform: uppercase; letter-spacing: 0.6px;
        }
        .sp-add-lote-btn {
            width: 100%; margin-top: 4px; padding: 6px;
            background: rgba(245,158,11,0.08); border: 1px dashed rgba(245,158,11,0.3);
            border-radius: 6px; color: #fbbf24; font-size: 11px;
            font-weight: 600; cursor: pointer; transition: all 0.2s;
        }
        .sp-add-lote-btn:hover { background: rgba(245,158,11,0.18); }

        /* Configuración de Impresión Segura */
        @media print {
            @page { size: landscape; margin: 6mm; }
            body { padding: 0; background: #fff; }
            .sheet { box-shadow: none; border: 2px solid #000; }
            .sheet th, .sheet td { border-color: #000; padding: 4px; }
            .title-block { background: #1a1a1a; color: #fff; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .header-red { color: #d00000 !important; background: transparent; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .bg-gray, .row-head { background: #eee; color: #000; -webkit-print-color-adjust: exact; print-color-adjust: exact; border-color: #000; }
            .ref-col { background: transparent; }
            .print-btn, .save-sap-btn, .structure-btn, .structure-overlay { display: none; }
        }
    </style>
</head>
<body>

<button id="btn-structure" onclick="toggleStructurePanel()" class="structure-btn">✏️ EDITAR ESTRUCTURA</button>
<button id="btn-sap" onclick="saveSAP()" class="save-sap-btn">GUARDAR N° SAP</button>
<a href="#" onclick="window.print()" class="print-btn">IMPRIMIR PLANILLA</a>

<!-- Sidebar Panel de Edición de Estructura -->
<div id="structureOverlay" class="structure-overlay" onclick="closeStructurePanel(event)">
    <div class="structure-panel" onclick="event.stopPropagation()">
        <div class="sp-header">
            <h2>Editar Estructura</h2>
            <p>Agregar o eliminar productos de la planilla del día <?= $fecha ?></p>
            <button class="sp-close" onclick="closeStructurePanel()">&times;</button>
        </div>
        <div class="sp-body" id="spBody">
            <!-- Se llena dinámicamente -->
        </div>
        <div class="sp-footer">
            <div class="sp-footer-info" id="spFooterInfo">Sin cambios pendientes</div>
            <button class="sp-apply-btn" id="spApplyBtn" disabled onclick="applyStructureChanges()">APLICAR CAMBIOS</button>
        </div>
    </div>
</div>

<table class="sheet">
    <!-- Row 1 -->
    <tr>
        <td colspan="2" rowspan="5">
            <img src="/archivos/formularios/logomas.png" class="logo-img" alt="mas somos más que harina">
        </td>
        <td colspan="6" rowspan="3" class="title-block">
            CONTROL DE MOLIENDA ZONA <?= $sede === 'ZC' ? 'CENTRO' : 'SUR' ?>
        </td>
        <td colspan="5" class="bg-gray" style="font-weight: bold; font-size:12px;">MOLIENDA DE TRIGO N°</td>
    </tr>
    <!-- Row 2 -->
    <tr>
        <td colspan="5" class="header-red"><?= $numeroMolienda ?></td>
    </tr>
    <!-- Row 3 -->
    <tr>
        <td colspan="3" class="bg-gray" style="font-weight:bold;">Hora Turno 1</td>
        <td colspan="2"><?= $turno1['hora'] ?? '' ?></td>
    </tr>
    <!-- Row 4 -->
    <tr>
        <td colspan="6" rowspan="2"></td>
        <td colspan="3" class="bg-gray" style="font-weight:bold;">Hora Turno 2</td>
        <td colspan="2"><?= $turno2['hora'] ?? '' ?></td>
    </tr>
    <!-- Row 5 -->
    <tr>
        <td colspan="3" class="bg-gray" style="font-weight:bold;">Hora Turno 3</td>
        <td colspan="2"><?= $turno3['hora'] ?? '' ?></td>
    </tr>
    <!-- Row 6 -->
    <tr>
        <td colspan="6"></td>
        <td style="font-weight:bold;">FECHA</td>
        <td colspan="6"><?= $fecha ?></td>
    </tr>
    <!-- Row 7 -->
    <tr>
        <td colspan="6"></td>
        <td style="font-weight:bold;">Responsable</td>
        <td colspan="6"><?= $responsable ?></td>
    </tr>
    <!-- Row Header -->
    <tr class="row-head">
        <td>N SAP Orden de<br>Trabajo</td>
        <td>REFERENCIA</td>
        <td>1 TURNO</td>
        <td>LOTE</td>
        <td>2 TURNO</td>
        <td>LOTE</td>
        <td>3 TURNO</td>
        <td>LOTE</td>
        <td>TOTAL KILOS</td>
        <td>MATERIALES</td>
        <td>LOTES TURNO 1</td>
        <td>LOTES TURNO 2</td>
        <td>LOTES TURNO 3</td>
        <td style="background:#1a0a2e; color:#c084fc;">TRIGO TURNO 1</td>
        <td style="background:#1a0a2e; color:#c084fc;">TRIGO TURNO 2</td>
        <td style="background:#1a0a2e; color:#c084fc;">TRIGO TURNO 3</td>
    </tr>

    <!-- Generación paralela de filas -->
    <?php 
        // Consolidar trigo de los 3 turnos en arrays
        $trigoT1 = $turno1['trigo'] ?? [];
        $trigoT2 = $turno2['trigo'] ?? [];
        $trigoT3 = $turno3['trigo'] ?? [];

        // Trigo ya no expande filas — se muestra por destino en la fila de su harina
        $maxRows = max(count($productos), count($materiales));

        $totHarinasT1 = 0; $totHarinasT2 = 0; $totHarinasT3 = 0;
        $totProdT1 = 0; $totProdT2 = 0; $totProdT3 = 0;

        $extHarina = 0; $extMogolla = 0; $extSalvado25 = 0; $extSegunda = 0; $extGermen = 0; $extSalvado30 = 0;

        // Formatea una entrada de trigo individual para edición
        function fmtTrigo($t) {
            if (!$t) return '';
            $tipo = htmlspecialchars($t['tipo'] ?? '');
            $cant = $t['cantidad'] ?? '';
            $lote = htmlspecialchars($t['lote'] ?? '');

            $out = $tipo;
            $out .= '<br><span style="color:#a855f7;font-weight:700;">' . $cant . ' Ton</span>';
            $out .= '<br><span style="font-size:8px;color:#888;">L: ' . $lote . '</span>';

            return $out;
        }

        // Devuelve las entradas de trigo y sus índices originales en el array del turno
        function getTrigoParaHarina($trigoArr, $prodId) {
            if (!$prodId) return [];
            $results = [];
            foreach ($trigoArr as $idx => $t) {
                if (($t['destino_harina'] ?? '') === $prodId) {
                    $results[] = ['data' => $t, 'originalIdx' => $idx];
                }
            }
            return $results;
        }

        // Formatea la lista de trigos de una harina
        function fmtTrigoList($list) {
            if (empty($list)) return '';
            $html = '';
            foreach ($list as $i => $item) {
                if ($i > 0) $html .= '<div style="border-top:1px solid rgba(255,255,255,0.1);margin:3px 0;"></div>';
                $html .= fmtTrigo($item['data']);
            }
            return $html;
        }

        // Sumar totales de trigo
        $totalTrigoT1 = array_reduce($trigoT1, function($carry, $item){ return $carry + ($item['cantidad'] ?? 0); }, 0);
        $totalTrigoT2 = array_reduce($trigoT2, function($carry, $item){ return $carry + ($item['cantidad'] ?? 0); }, 0);
        $totalTrigoT3 = array_reduce($trigoT3, function($carry, $item){ return $carry + ($item['cantidad'] ?? 0); }, 0);

        // Track de trigos mostrados para identificar huérfanos
        $mostradosT1 = []; $mostradosT2 = []; $mostradosT3 = [];

        for ($i = 0; $i < $maxRows; $i++): 
            $prod = $productos[$i] ?? null;
            $mat  = $materiales[$i] ?? null;

            // Reiniciar variables de fila para evitar duplicados
            $p1 = ''; $p2 = ''; $p3 = ''; $l1 = ''; $l2 = ''; $l3 = '';
            $k1 = 0; $k2 = 0; $k3 = 0; $sapNum = '';
            $pData1 = null; $pData2 = null; $pData3 = null;
            $mData1 = null; $mData2 = null; $mData3 = null;
            $m1 = ''; $m2 = ''; $m3 = '';
            $trH1 = []; $trH2 = []; $trH3 = [];

            if ($prod) {
                $pData1 = getValLotes($turno1, null, $prod['id']); $p1 = $pData1['val_lines']; $k1 = $pData1['kilos']; $l1 = $pData1['lotes'];
                $pData2 = getValLotes($turno2, null, $prod['id']); $p2 = $pData2['val_lines']; $k2 = $pData2['kilos']; $l2 = $pData2['lotes'];
                $pData3 = getValLotes($turno3, null, $prod['id']); $p3 = $pData3['val_lines']; $k3 = $pData3['kilos']; $l3 = $pData3['lotes'];
                $sapNum = $turno1['sap_diario'][$prod['id']] ?? '';
            }
            $kTotal = $k1 + $k2 + $k3;
            $totProdT1 += $k1; $totProdT2 += $k2; $totProdT3 += $k3;

            // Revisar si es harina
            $isHarina = in_array($prod['id'] ?? '', $diccionarioHarinasIds);
            if ($isHarina) {
                $totHarinasT1 += $k1; $totHarinasT2 += $k2; $totHarinasT3 += $k3;
                $extHarina += $kTotal;
            }

            // Para porcentajes
            $pId = $prod['id'] ?? '';
            if (strpos($pId, 'mogolla') !== false) $extMogolla += $kTotal;
            if (strpos($pId, 'salvado_x25') !== false) $extSalvado25 += $kTotal;
            if (strpos($pId, 'salvado_x30') !== false) $extSalvado30 += $kTotal;
            if (strpos($pId, 'segunda') !== false) $extSegunda += $kTotal;
            if (strpos($pId, 'germen') !== false) $extGermen += $kTotal;

            if ($mat) {
                $mData1 = getValLotes($turno1, null, $mat['id']); $m1 = $mData1['lotes'];
                $mData2 = getValLotes($turno2, null, $mat['id']); $m2 = $mData2['lotes'];
                $mData3 = getValLotes($turno3, null, $mat['id']); $m3 = $mData3['lotes'];
            }

            // Trigo alineado con la harina: buscar por destino_harina === $prod['id']
            if ($prod) {
                $trH1 = getTrigoParaHarina($trigoT1, $prod['id']);
                $trH2 = getTrigoParaHarina($trigoT2, $prod['id']);
                $trH3 = getTrigoParaHarina($trigoT3, $prod['id']);
            }

            // Marcar como mostrados (guardar el dato RAW, no el wrapper)
            foreach($trH1 as $item) { $mostradosT1[] = $item['data']; }
            foreach($trH2 as $item) { $mostradosT2[] = $item['data']; }
            foreach($trH3 as $item) { $mostradosT3[] = $item['data']; }
    ?>
    <tr>
        <td class="sap-cell" contenteditable="true" data-id="<?= $prod ? $prod['id'] : '' ?>" style="font-weight:800; color:#e11d48; background:#fffcfc;"><?= $sapNum ?></td>
        <td class="ref-col"><?= $prod ? $prod['name'] : '' ?></td>
        <td><?= $p1 ?: '' ?></td>
        <td><?= $l1 ?></td>
        <td><?= $p2 ?: '' ?></td>
        <td><?= $l2 ?></td>
        <td><?= $p3 ?: '' ?></td>
        <td><?= $l3 ?></td>
        <td style="font-weight:bold;"><?= $kTotal > 0 ? $kTotal : '' ?></td>

        <td class="ref-col" style="background: #fdfdfd;"><?= $mat ? $mat['name'] : '' ?></td>
        <td><?= $mat ? $m1 : '' ?></td>
        <td><?= $mat ? $m2 : '' ?></td>
        <td><?= $mat ? $m3 : '' ?></td>

        <td style="background:#0d0620; font-size:9px; vertical-align:middle;"><?= fmtTrigoList($trH1) ?></td>
        <td style="background:#0d0620; font-size:9px; vertical-align:middle;"><?= fmtTrigoList($trH2) ?></td>
        <td style="background:#0d0620; font-size:9px; vertical-align:middle;"><?= fmtTrigoList($trH3) ?></td>
    </tr>
    <?php endfor; 
        
        // Identificar huérfanos (trigos que no tienen destino o cuyo destino no existe en la lista de productos)
        $orphT1 = []; foreach($trigoT1 as $idx => $t) { if(!in_array($t, $mostradosT1)) $orphT1[] = ['data'=>$t, 'originalIdx'=>$idx]; }
        $orphT2 = []; foreach($trigoT2 as $idx => $t) { if(!in_array($t, $mostradosT2)) $orphT2[] = ['data'=>$t, 'originalIdx'=>$idx]; }
        $orphT3 = []; foreach($trigoT3 as $idx => $t) { if(!in_array($t, $mostradosT3)) $orphT3[] = ['data'=>$t, 'originalIdx'=>$idx]; }

        if (!empty($orphT1) || !empty($orphT2) || !empty($orphT3)):
    ?>
    <tr style="border-top: 2px solid #7000ff;">
        <td colspan="9" style="text-align:right; font-weight:bold; color:#7000ff; font-size:10px; background:rgba(112,0,255,0.05);">TRIGO ADICIONAL / SIN DESTINO:</td>
        <td colspan="4" style="background:#fdfdfd;"></td>
        <td style="background:#0d0620; font-size:9px; vertical-align:middle;"><?= fmtTrigoList($orphT1) ?></td>
        <td style="background:#0d0620; font-size:9px; vertical-align:middle;"><?= fmtTrigoList($orphT2) ?></td>
        <td style="background:#0d0620; font-size:9px; vertical-align:middle;"><?= fmtTrigoList($orphT3) ?></td>
    </tr>
    <?php endif; ?>

    <!-- FILA DE TOTALES DE TRIGO -->
    <tr class="row-head" style="background:#1a0a2e !important;">
        <td colspan="9" style="text-align:right; color:#c084fc;">TOTAL TRIGO MOLIDO (TON):</td>
        <td colspan="4" style="background:#fdfdfd;"></td>
        <td style="color:#fff; font-weight:bold;"><?= $totalTrigoT1 > 0 ? number_format($totalTrigoT1, 2) : '' ?></td>
        <td style="color:#fff; font-weight:bold;"><?= $totalTrigoT2 > 0 ? number_format($totalTrigoT2, 2) : '' ?></td>
        <td style="color:#fff; font-weight:bold;"><?= $totalTrigoT3 > 0 ? number_format($totalTrigoT3, 2) : '' ?></td>
    </tr>

    <?php
        $totGlobal = $totProdT1 + $totProdT2 + $totProdT3;
        $totalTrigoGral = $totalTrigoT1 + $totalTrigoT2 + $totalTrigoT3;
        
        // Extracción general (Harinas Totales / Trigo Total)
        $porcExtGral = $totalTrigoGral > 0 ? number_format(($extHarina / ($totalTrigoGral * 1000)) * 100, 2) : '0.00';
        $porcHarina = $totGlobal > 0 ? number_format(($extHarina / $totGlobal) * 100, 2) : '0.00';
        
        $porcMogolla = $totGlobal > 0 ? number_format(($extMogolla / $totGlobal) * 100, 2) : '0.00';
        $porcSalvado25 = $totGlobal > 0 ? number_format(($extSalvado25 / $totGlobal) * 100, 2) : '0.00';
        $porcSalvado30 = $totGlobal > 0 ? number_format(($extSalvado30 / $totGlobal) * 100, 2) : '0.00';
        $porcSegunda = $totGlobal > 0 ? number_format(($extSegunda / $totGlobal) * 100, 2) : '0.00';
        $porcGermen = $totGlobal > 0 ? number_format(($extGermen / $totGlobal) * 100, 2) : '0.00';

        $totHarinasGral = $totHarinasT1 + $totHarinasT2 + $totHarinasT3;

        // Responsables de intervención para llenar la parte central bajo cada turno
        $respArrT1 = $turno1['responsables_intervencion'] ?? [];
        $respArrT2 = $turno2['responsables_intervencion'] ?? [];
        $respArrT3 = $turno3['responsables_intervencion'] ?? [];
    ?>

    <!-- FILA DE TOTALES -->
    <tr class="row-head">
        <td colspan="2">TOTAL HARINA</td>
        <td colspan="2"><?= $totHarinasT1 ?></td>
        <td colspan="1">TOTAL TURNO 2</td>
        <td colspan="1"><?= $totHarinasT2 ?></td>
        <td colspan="1">TOTAL TURNO 3</td>
        <td colspan="1"><?= $totHarinasT3 ?></td>
        <td colspan="1">TOTAL</td>
        <td colspan="4"><?= $totHarinasGral ?></td>
    </tr>

    <!-- BLOQUE INFERIOR (EXTRACCION, ENCARGADOS Y FIRMAS) -->
    <tr>
        <td class="bg-gray"></td>
        <td class="bg-gray"></td>
        <td colspan="2" class="bg-gray" style="font-weight:bold;">Resp. T1</td>
        <td colspan="2" class="bg-gray" style="font-weight:bold;">Resp. T2</td>
        <td colspan="2" class="bg-gray" style="font-weight:bold;">Resp. T3</td>
        <td colspan="2" class="row-head">ENCARGADO TURNO 1</td>
        <td colspan="3" style="text-align:center; vertical-align:middle; width:200px; height: 50px;">
            <div style="font-size: 10px; font-weight:bold; color: #1a1a1a; margin-bottom: 2px;">
                <?= $turno1['responsable'] ?? '' ?>
            </div>
            <?php if(!empty($turno1['firma'])): ?>
                <?php if (strpos($turno1['firma'], 'data:image') === 0): ?>
                    <img src="<?= $turno1['firma'] ?>" style="max-height: 35px; margin-top:2px;">
                <?php else: ?>
                    <div style="font-weight:bold; font-size:12px; margin-top:10px;"><?= htmlspecialchars($turno1['firma']) ?></div>
                <?php endif; ?>
            <?php endif; ?>
        </td>
    </tr>
    <tr>
        <td class="row-head">EXT. HARINA</td>
        <td class="bg-gray"><?= $porcHarina ?>%</td>
        <td colspan="2" style="font-size:9px;"><?= $respArrT1[0] ?? '' ?></td>
        <td colspan="2" style="font-size:9px;"><?= $respArrT2[0] ?? '' ?></td>
        <td colspan="2" style="font-size:9px;"><?= $respArrT3[0] ?? '' ?></td>
        <td colspan="2" class="row-head">ENCARGADO TURNO 2</td>
        <td colspan="3" style="text-align:center; vertical-align:middle; height: 50px;">
            <div style="font-size: 10px; font-weight:bold; color: #1a1a1a; margin-bottom: 2px;">
                <?= $turno2['responsable'] ?? '' ?>
            </div>
            <?php if(!empty($turno2['firma'])): ?>
                <?php if (strpos($turno2['firma'], 'data:image') === 0): ?>
                    <img src="<?= $turno2['firma'] ?>" style="max-height: 35px; margin-top:2px;">
                <?php else: ?>
                    <div style="font-weight:bold; font-size:12px; margin-top:10px;"><?= htmlspecialchars($turno2['firma']) ?></div>
                <?php endif; ?>
            <?php endif; ?>
        </td>
    </tr>
    <tr>
        <td class="row-head">EXT. MOGOLLA</td>
        <td class="bg-gray"><?= $porcMogolla ?>%</td>
        <td colspan="2" style="font-size:9px;"><?= $respArrT1[1] ?? '' ?></td>
        <td colspan="2" style="font-size:9px;"><?= $respArrT2[1] ?? '' ?></td>
        <td colspan="2" style="font-size:9px;"><?= $respArrT3[1] ?? '' ?></td>
        <td colspan="2" class="row-head">ENCARGADO TURNO 3</td>
        <td colspan="3" style="text-align:center; vertical-align:middle; height: 50px;">
            <div style="font-size: 10px; font-weight:bold; color: #1a1a1a; margin-bottom: 2px;">
                <?= $turno3['responsable'] ?? '' ?>
            </div>
            <?php if(!empty($turno3['firma'])): ?>
                <?php if (strpos($turno3['firma'], 'data:image') === 0): ?>
                    <img src="<?= $turno3['firma'] ?>" style="max-height: 35px; margin-top:2px;">
                <?php else: ?>
                    <div style="font-weight:bold; font-size:12px; margin-top:10px;"><?= htmlspecialchars($turno3['firma']) ?></div>
                <?php endif; ?>
            <?php endif; ?>
        </td>
    </tr>
    <tr>
        <td class="row-head">EXT. SALVADO x25</td>
        <td class="bg-gray"><?= $porcSalvado25 ?>%</td>
        <td colspan="2" style="font-size:9px;"><?= $respArrT1[2] ?? '' ?></td>
        <td colspan="2" style="font-size:9px;"><?= $respArrT2[2] ?? '' ?></td>
        <td colspan="2" style="font-size:9px;"><?= $respArrT3[2] ?? '' ?></td>
        <td colspan="2" class="row-head">ALMACENISTA 1</td>
        <td colspan="3" rowspan="2" style="text-align:center; vertical-align:middle; font-weight:bold;">
            <?= $turno1['almacenista'] ?? ($turno2['almacenista'] ?? ($turno3['almacenista'] ?? '')) ?>
        </td>
    </tr>
    <tr>
        <td class="row-head">EXT. SEGUNDA</td>
        <td class="bg-gray"><?= $porcSegunda ?>%</td>
        <td colspan="2" style="font-size:9px;"><?= $respArrT1[3] ?? '' ?></td>
        <td colspan="2" style="font-size:9px;"><?= $respArrT2[3] ?? '' ?></td>
        <td colspan="2" style="font-size:9px;"><?= $respArrT3[3] ?? '' ?></td>
        <td colspan="2" class="row-head">ALMACENISTA 2</td>
    </tr>
    <tr>
        <td class="row-head">EXT. GERMEN</td>
        <td class="bg-gray"><?= $porcGermen ?>%</td>
        <td colspan="2" style="font-size:9px;"><?= $respArrT1[4] ?? '' ?></td>
        <td colspan="2" style="font-size:9px;"><?= $respArrT2[4] ?? '' ?></td>
        <td colspan="2" style="font-size:9px;"><?= $respArrT3[4] ?? '' ?></td>
        <td colspan="2" class="row-head" rowspan="2">COORDINADOR DE<br>OPERACIONES</td>
        <td colspan="3" rowspan="2"></td>
    </tr>
    <tr>
        <td class="row-head">EXT. SALVADO X30</td>
        <td class="bg-gray"><?= $porcSalvado30 ?>%</td>
        <td colspan="2" style="font-size:9px;"><?= $respArrT1[5] ?? '' ?></td>
        <td colspan="2" style="font-size:9px;"><?= $respArrT2[5] ?? '' ?></td>
        <td colspan="2" style="font-size:9px;"><?= $respArrT3[5] ?? '' ?></td>
    </tr>
    <tr>
        <td class="row-head" style="background:#eee !important;">TOTAL HARINAS Gral.</td>
        <td colspan="7" style="background:#fdfdfd;"></td>
        <td colspan="2" class="row-head">PESO TOTAL</td>
        <td colspan="3" style="font-weight:bold; color:#e11d48; text-align:center;"><?= number_format($totHarinasGral) ?> Kg</td>
    </tr>
</table>

<script>
function saveSAP() {
    let sapData = {};
    document.querySelectorAll('.sap-cell').forEach(cell => {
        let id = cell.getAttribute('data-id');
        let val = cell.innerText.trim();
        if(id && val !== '') {
            sapData[id] = val;
        }
    });

    let btn = document.getElementById('btn-sap');
    let oldText = btn.innerText;
    btn.innerText = 'GUARDANDO...';

    fetch('guardar_sap.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ sede: '<?= $sede ?>', fecha: '<?= $fecha ?>', sap: sapData })
    })
    .then(r => r.json())
    .then(res => {
        if(res.success) {
            btn.innerText = '¡GUARDADO!';
            btn.style.background = '#10b981';
            setTimeout(() => { btn.innerText = oldText; btn.style.background = ''; }, 2000);
        } else {
            alert('Error al guardar N° SAP: ' + res.error);
            btn.innerText = oldText;
        }
    })
    .catch(e => {
        alert('Error conectando al servidor.');
        btn.innerText = oldText;
    });
}

// ══════════════════════════════════════════════════════════════════════════
// MODO EDICIÓN DE ESTRUCTURA
// ══════════════════════════════════════════════════════════════════════════

// Config completa de la sede (todos los productos posibles)
const allConfig = <?= json_encode($dynamicCfg ?? ['harinas'=>[],'subproductos'=>[],'materiales'=>[]]) ?>;

// Productos actualmente activos en la planilla (renderizados en la tabla)
const activeProductIds = <?= json_encode($usadosProductos) ?>;
const activeMaterialIds = <?= json_encode($usadosMateriales) ?>;

// Estado de cambios pendientes
// pendingAdd: [{id, cat, valores: {1:{bultos:'',lote:''}, 2:{...}, 3:{...}}}]
let pendingAdd = [];
let pendingRemove = []; // [{id, cat}]
let editingProducts = {}; // {prodId: {cat, selectedTurn, valores}}

// Valores actuales por producto/turno (exportados desde PHP)
const currentTurnValues = <?= json_encode($currentTurnValues) ?>;

function toggleStructurePanel() {
    const overlay = document.getElementById('structureOverlay');
    const btn = document.getElementById('btn-structure');
    if (overlay.classList.contains('open')) {
        overlay.classList.remove('open');
        btn.classList.remove('active');
    } else {
        pendingAdd = [];
        pendingRemove = [];
        renderStructurePanel();
        overlay.classList.add('open');
        btn.classList.add('active');
    }
}

function closeStructurePanel(e) {
    if (e && e.target !== e.currentTarget) return;
    document.getElementById('structureOverlay').classList.remove('open');
    document.getElementById('btn-structure').classList.remove('active');
}

// ── Construir diccionario de nombres desde config (global para reuso) ──
const nameDict = {};
const catDict  = {};
['harinas','subproductos','materiales'].forEach(cat => {
    (allConfig[cat] || []).forEach(item => {
        nameDict[item.id] = item.name;
        catDict[item.id]  = cat;
    });
});

function renderStructurePanel() {
    const body = document.getElementById('spBody');
    let html = '';

    // ── PRODUCTOS ACTIVOS ──
    html += '<div class="sp-section-title">📋 Productos en la planilla actual</div>';

    const activeItems = [...activeProductIds].filter(id => !pendingRemove.find(p => p.id === id));
    const addedItems  = pendingAdd.filter(p => p.cat !== 'materiales');

    if (activeItems.length === 0 && addedItems.length === 0) {
        html += '<div class="sp-empty">No hay productos activos</div>';
    } else {
        activeItems.forEach(id => {
            const name      = nameDict[id] || id.toUpperCase();
            const cat       = catDict[id]  || 'harinas';
            const catLabel  = cat === 'harinas' ? 'Harina' : 'Subproducto';
            const isEditing = !!editingProducts[id];
            html += `
                <div class="sp-item sp-item-active" style="flex-direction:column; align-items:stretch;">
                    <div style="display:flex; align-items:center; justify-content:space-between;">
                        <span class="sp-item-name">${name}</span>
                        <span class="sp-item-cat">${catLabel}</span>
                        <button class="sp-item-btn" style="background:rgba(245,158,11,0.12);color:#fbbf24;margin-right:4px;" onclick="toggleEditProduct('${id}','${cat}')" title="${isEditing ? 'Cancelar edición' : 'Editar valores por turno'}">${isEditing ? '↩' : '✏️'}</button>
                        <button class="sp-item-btn sp-btn-remove" onclick="markRemove('${id}','${cat}')" title="Eliminar">✕</button>
                    </div>
                    ${isEditing ? renderEditProductForm(id) : ''}
                </div>`;        });

        // Productos recién añadidos – mostrar con su sub-formulario
        addedItems.forEach(p => {
            const name     = nameDict[p.id] || p.id.toUpperCase();
            const catLabel = p.cat === 'harinas' ? 'Harina' : 'Subproducto';
            const safeId   = p.id.replace(/[^a-zA-Z0-9_]/g,'_');

            // Tabs de turno
            const tabsHtml = [1,2,3].map(t => {
                const sel = p.selectedTurn === t ? 'selected' : '';
                return `<div class="sp-turn-tab ${sel}" onclick="selectTurn('${p.id}',${t})" id="tab_${safeId}_${t}">Turno ${t}</div>`;
            }).join('');

            // Campos de valores para el turno activo
            const activeTurn = p.selectedTurn || 1;
            const vals = p.valores[activeTurn] || {bultos:'', lote:''};

            // Opciones de materiales disponibles (sin los ya seleccionados para esta harina)
            const selectedMatIds = (p.materiales || []).map(m => m.id);
            const matOptions = (allConfig.materiales || [])
                .filter(m => !selectedMatIds.includes(m.id))
                .map(m => `<option value="${m.id}" style="background:#0d0d1a;">${m.name}</option>`)
                .join('');

            html += `
                <div class="sp-item sp-item-active" style="flex-direction:column; align-items:stretch; padding-bottom:4px;">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
                        <span class="sp-item-name">➕ ${name}</span>
                        <span class="sp-item-cat">${catLabel}</span>
                        <button class="sp-item-btn sp-btn-remove" onclick="undoAdd('${p.id}')" title="Cancelar">↩</button>
                    </div>
                    <div class="sp-val-form" id="form_${safeId}">
                        <div class="sp-val-form-title">📦 Valores de producción por turno</div>
                        <div class="sp-turn-tabs">${tabsHtml}</div>
                        <div id="fields_${safeId}">
                            ${renderTurnFields(p.id, activeTurn, vals)}
                        </div>
                        <div style="font-size:10px; color:#64748b; margin-top:6px; text-align:center;">
                            Deja en blanco los turnos sin producción
                        </div>
                        <div class="sp-mat-divider"></div>
                        <div class="sp-mat-section-title">🔧 Insumos / Materiales asociados</div>
                        <div id="mat_list_${safeId}" class="sp-mat-list">
                            ${renderMatListHtml(p, activeTurn)}
                        </div>
                        <div class="sp-mat-add-row">
                            <select id="mat_sel_${safeId}" class="sp-mat-select">
                                <option value="">— Seleccionar insumo —</option>
                                ${matOptions}
                            </select>
                            <button class="sp-mat-add-btn" onclick="addMaterialToHarina('${p.id}')">+ Agregar</button>
                        </div>
                    </div>
                </div>`;
        });
    }

    // ── MARCADOS PARA ELIMINAR ──
    if (pendingRemove.filter(p => p.cat !== 'materiales').length > 0) {
        html += '<div class="sp-section-title" style="color: #f43f5e;">🗑️ Marcados para eliminar</div>';
        pendingRemove.filter(p => p.cat !== 'materiales').forEach(p => {
            const name     = nameDict[p.id] || p.id.toUpperCase();
            const catLabel = p.cat === 'harinas' ? 'Harina' : 'Subproducto';
            html += `
                <div class="sp-item" style="background:rgba(225,29,72,0.08);border:1px solid rgba(225,29,72,0.25);color:#fca5a5;text-decoration:line-through;">
                    <span class="sp-item-name">${name}</span>
                    <span class="sp-item-cat">${catLabel}</span>
                    <button class="sp-item-btn" style="background:rgba(255,255,255,0.1);color:#94a3b8;" onclick="undoRemove('${p.id}')" title="Restaurar">↩</button>
                </div>`;
        });
    }

    // ── DISPONIBLES PARA AGREGAR ──
    html += '<div class="sp-section-title" style="margin-top:30px;">➕ Productos disponibles para agregar</div>';
    let availableCount = 0;
    ['harinas','subproductos'].forEach(cat => {
        (allConfig[cat] || []).forEach(item => {
            const isActive       = activeProductIds.includes(item.id);
            const isPendingAdd   = pendingAdd.find(p => p.id === item.id);
            const isPendingRemove = pendingRemove.find(p => p.id === item.id);
            if ((!isActive || isPendingRemove) && !isPendingAdd) {
                const catLabel = cat === 'harinas' ? 'Harina' : 'Subproducto';
                html += `
                    <div class="sp-item sp-item-available">
                        <span class="sp-item-name">${item.name}</span>
                        <span class="sp-item-cat">${catLabel}</span>
                        <button class="sp-item-btn sp-btn-add" onclick="markAdd('${item.id}','${cat}')" title="Agregar">+</button>
                    </div>`;
                availableCount++;
            }
        });
    });
    if (availableCount === 0) {
        html += '<div class="sp-empty">Todos los productos ya están en la planilla</div>';
    }

    // ── MATERIALES / INSUMOS (sección independiente) ──
    html += `
        <div style="margin-top:28px; padding-top:18px; border-top:2px solid rgba(99,102,241,0.2);">
            <div class="sp-section-title" style="color:#818cf8; margin-top:0;">
                📦 Materiales / Insumos de la planilla
            </div>
        </div>`;

    const activeMats  = [...activeMaterialIds].filter(id => !pendingRemove.find(p => p.id === id));
    const addedMats   = pendingAdd.filter(p => p.cat === 'materiales');
    const removingMats = pendingRemove.filter(p => p.cat === 'materiales');

    // Materiales activos
    if (activeMats.length === 0 && addedMats.length === 0) {
        html += '<div class="sp-empty">No hay materiales activos en la planilla</div>';
    } else {
        activeMats.forEach(id => {
            const name = nameDict[id] || id.replace(/_/g,' ').toUpperCase();
            const isEditing = !!editingProducts[id];
            html += `
                <div class="sp-item sp-item-active" style="flex-direction:column; align-items:stretch; background:rgba(99,102,241,0.08);border:1px solid rgba(99,102,241,0.2);color:#c7d2fe;">
                    <div style="display:flex; align-items:center; justify-content:space-between;">
                        <span class="sp-item-name">${name}</span>
                        <span class="sp-item-cat" style="color:#818cf8;">Insumo</span>
                        <button class="sp-item-btn" style="background:rgba(245,158,11,0.12);color:#fbbf24;margin-right:4px;" onclick="toggleEditProduct('${id}','materiales')" title="${isEditing ? 'Cancelar edición' : 'Editar valores por turno'}">${isEditing ? '↩' : '✏️'}</button>
                        <button class="sp-item-btn sp-btn-remove" onclick="markRemove('${id}','materiales')" title="Eliminar de la planilla">✕</button>
                    </div>
                    ${isEditing ? renderEditProductForm(id) : ''}
                </div>`;
        });
        // Materiales recién agregados (pendientes de guardar)
        addedMats.forEach(p => {
            const name = nameDict[p.id] || p.id.replace(/_/g,' ').toUpperCase();
            html += `
                <div class="sp-item" style="background:rgba(34,197,94,0.08);border:1px solid rgba(34,197,94,0.2);color:#bbf7d0;">
                    <span class="sp-item-name">➕ ${name}</span>
                    <span class="sp-item-cat" style="color:#22c55e;">Insumo</span>
                    <button class="sp-item-btn sp-btn-remove" onclick="undoAdd('${p.id}')" title="Cancelar">↩</button>
                </div>`;
        });
    }

    // Materiales marcados para eliminar (con undo)
    if (removingMats.length > 0) {
        html += '<div class="sp-section-title" style="color:#f43f5e; margin-top:12px;">🗑️ Materiales a eliminar</div>';
        removingMats.forEach(p => {
            const name = nameDict[p.id] || p.id.replace(/_/g,' ').toUpperCase();
            html += `
                <div class="sp-item" style="background:rgba(225,29,72,0.08);border:1px solid rgba(225,29,72,0.25);color:#fca5a5;text-decoration:line-through;">
                    <span class="sp-item-name">${name}</span>
                    <span class="sp-item-cat">Insumo</span>
                    <button class="sp-item-btn" style="background:rgba(255,255,255,0.1);color:#94a3b8;" onclick="undoRemove('${p.id}')" title="Restaurar">↩</button>
                </div>`;
        });
    }

    // Materiales disponibles para activar
    html += '<div class="sp-section-title" style="color:#6366f1; margin-top:14px;">➕ Materiales disponibles para activar</div>';
    let matAvailCount = 0;
    (allConfig.materiales || []).forEach(mat => {
        const isMActive   = activeMaterialIds.includes(mat.id);
        const isMPendAdd  = pendingAdd.find(p => p.id === mat.id);
        const isMPendRm   = pendingRemove.find(p => p.id === mat.id);
        if ((!isMActive || isMPendRm) && !isMPendAdd) {
            html += `
                <div class="sp-item sp-item-available">
                    <span class="sp-item-name">${mat.name}</span>
                    <span class="sp-item-cat" style="color:#6366f1;">Insumo</span>
                    <button class="sp-item-btn sp-btn-add" onclick="markAdd('${mat.id}','materiales')" title="Activar">+</button>
                </div>`;
            matAvailCount++;
        }
    });
    if (matAvailCount === 0) {
        html += '<div class="sp-empty">Todos los materiales del inventario ya están activos</div>';
    }

    body.innerHTML = html;
    updateFooterInfo();
}

// Genera el HTML de los 2 inputs (bultos + lote) para un turno dado
function renderTurnFields(prodId, turn, vals) {
    const safeId = prodId.replace(/[^a-zA-Z0-9_]/g,'_');
    return `<div class="sp-val-fields">
        <div class="sp-val-field">
            <label>BULTOS (T${turn})</label>
            <input type="number" min="0" step="1"
                   id="bultos_${safeId}_${turn}"
                   placeholder="0"
                   value="${vals.bultos || ''}"
                   onchange="saveTurnValue('${prodId}',${turn},'bultos',this.value)">
        </div>
        <div class="sp-val-field">
            <label>N° LOTE (T${turn})</label>
            <input type="text"
                   id="lote_${safeId}_${turn}"
                   placeholder="ej. L-2024"
                   value="${vals.lote || ''}"
                   onchange="saveTurnValue('${prodId}',${turn},'lote',this.value)">
        </div>
    </div>`;
}

// Cuando el usuario cambia de tab de turno, guardar los vals actuales y mostrar los del nuevo turno
function selectTurn(prodId, turn) {
    const item   = pendingAdd.find(p => p.id === prodId);
    if (!item) return;

    // Guardar valores del input actual antes de cambiar la vista
    flushTurnInputs(item);

    item.selectedTurn = turn;

    const safeId = prodId.replace(/[^a-zA-Z0-9_]/g,'_');

    // Actualizar tabs
    [1,2,3].forEach(t => {
        const tab = document.getElementById(`tab_${safeId}_${t}`);
        if (tab) tab.classList.toggle('selected', t === turn);
    });

    // Actualizar campos
    const vals   = item.valores[turn] || {bultos:'', lote:''};
    const fields = document.getElementById(`fields_${safeId}`);
    if (fields) fields.innerHTML = renderTurnFields(prodId, turn, vals);

    // Actualizar lista de materiales para el nuevo turno
    const matList = document.getElementById(`mat_list_${safeId}`);
    if (matList) matList.innerHTML = renderMatListHtml(item, turn);
}

// Lee los inputs del DOM y los persiste en pendingAdd[].valores
function flushTurnInputs(item) {
    const activeTurn = item.selectedTurn || 1;
    const safeId     = item.id.replace(/[^a-zA-Z0-9_]/g,'_');
    const bEl = document.getElementById(`bultos_${safeId}_${activeTurn}`);
    const lEl = document.getElementById(`lote_${safeId}_${activeTurn}`);
    if (!item.valores[activeTurn]) item.valores[activeTurn] = {bultos:'', lote:''};
    if (bEl) item.valores[activeTurn].bultos = bEl.value;
    if (lEl) item.valores[activeTurn].lote   = lEl.value;
    flushMatLoteInputs(item); // también guardar lotes de materiales
}

// Flush de lotes de materiales desde el DOM al estado
function flushMatLoteInputs(item) {
    const activeTurn = item.selectedTurn || 1;
    (item.materiales || []).forEach(m => {
        const safeProdId = item.id.replace(/[^a-zA-Z0-9_]/g,'_');
        const safeMatId  = m.id.replace(/[^a-zA-Z0-9_]/g,'_');
        const el = document.getElementById(`mat_lote_${safeProdId}_${safeMatId}_${activeTurn}`);
        if (el) m.lotes[activeTurn] = el.value;
    });
}

// Guardar un campo en tiempo real (onchange)
function saveTurnValue(prodId, turn, field, value) {
    const item = pendingAdd.find(p => p.id === prodId);
    if (!item) return;
    if (!item.valores[turn]) item.valores[turn] = {bultos:'', lote:''};
    item.valores[turn][field] = value;
}

// ── Funciones de materiales asociados a harinas ──

function renderMatListHtml(item, activeTurn) {
    if (!item.materiales || item.materiales.length === 0) {
        return '<div class="sp-mat-empty">Sin insumos seleccionados</div>';
    }
    let h = '';
    item.materiales.forEach(m => {
        const matName    = nameDict[m.id] || m.id.toUpperCase();
        const loteVal    = m.lotes[activeTurn] || '';
        const safeProdId = item.id.replace(/[^a-zA-Z0-9_]/g,'_');
        const safeMatId  = m.id.replace(/[^a-zA-Z0-9_]/g,'_');
        h += `<div class="sp-mat-item">
            <span class="sp-mat-item-name">${matName}</span>
            <input type="text" class="sp-mat-lote-input"
                   id="mat_lote_${safeProdId}_${safeMatId}_${activeTurn}"
                   placeholder="Lote T${activeTurn}"
                   value="${loteVal}"
                   onchange="saveMaterialLote('${item.id}','${m.id}',${activeTurn},this.value)">
            <button class="sp-mat-remove-btn" onclick="removeMaterialFromHarina('${item.id}','${m.id}')">✕</button>
        </div>`;
    });
    return h;
}

function addMaterialToHarina(prodId) {
    const item   = pendingAdd.find(p => p.id === prodId);
    if (!item) return;
    const safeId = prodId.replace(/[^a-zA-Z0-9_]/g,'_');
    const sel    = document.getElementById(`mat_sel_${safeId}`);
    const matId  = sel ? sel.value : '';
    if (!matId) return;
    if (item.materiales.find(m => m.id === matId)) return;
    // Flush antes de modificar
    flushTurnInputs(item);
    item.materiales.push({ id: matId, lotes: {1:'', 2:'', 3:''} });
    // Re-renderizar lista y select
    const matList = document.getElementById(`mat_list_${safeId}`);
    if (matList) matList.innerHTML = renderMatListHtml(item, item.selectedTurn || 1);
    rebuildMatSelect(prodId, item);
}

function removeMaterialFromHarina(prodId, matId) {
    const item = pendingAdd.find(p => p.id === prodId);
    if (!item) return;
    item.materiales = item.materiales.filter(m => m.id !== matId);
    const safeId  = prodId.replace(/[^a-zA-Z0-9_]/g,'_');
    const matList = document.getElementById(`mat_list_${safeId}`);
    if (matList) matList.innerHTML = renderMatListHtml(item, item.selectedTurn || 1);
    rebuildMatSelect(prodId, item);
}

function saveMaterialLote(prodId, matId, turn, value) {
    const item = pendingAdd.find(p => p.id === prodId);
    if (!item) return;
    const mat = item.materiales.find(m => m.id === matId);
    if (mat) mat.lotes[turn] = value;
}

function rebuildMatSelect(prodId, item) {
    const safeId     = prodId.replace(/[^a-zA-Z0-9_]/g,'_');
    const sel        = document.getElementById(`mat_sel_${safeId}`);
    if (!sel) return;
    const selectedIds = item.materiales.map(m => m.id);
    sel.innerHTML = '<option value="">— Seleccionar insumo —</option>' +
        (allConfig.materiales || [])
            .filter(m => !selectedIds.includes(m.id))
            .map(m => `<option value="${m.id}" style="background:#0d0d1a;">${m.name}</option>`)
            .join('');
}

function markAdd(id, cat) {
    const rmIdx = pendingRemove.findIndex(p => p.id === id);
    if (rmIdx >= 0) {
        pendingRemove.splice(rmIdx, 1);
    } else {
        // Inicializar con valores vacíos por turno y array de materiales
        pendingAdd.push({ id, cat, selectedTurn: 1, valores: {1:{bultos:'',lote:''}, 2:{bultos:'',lote:''}, 3:{bultos:'',lote:''}}, materiales: [] });
    }
    renderStructurePanel();
}

function markRemove(id, cat) {
    pendingRemove.push({id, cat});
    renderStructurePanel();
}

function undoAdd(id) {
    pendingAdd = pendingAdd.filter(p => p.id !== id);
    renderStructurePanel();
}

function undoRemove(id) {
    pendingRemove = pendingRemove.filter(p => p.id !== id);
    renderStructurePanel();
}

// ══════════════════════════════════════════════════════════════════════════
// EDITAR VALORES DE HARINAS YA ACTIVAS POR TURNO
// ══════════════════════════════════════════════════════════════════════════

function toggleEditProduct(id, cat) {
    if (editingProducts[id]) {
        delete editingProducts[id];
    } else {
        const cv = currentTurnValues[id] || {};
        // Pre-rellenar con los lotes existentes (copia profunda)
        const copyLotes = t => (cv[t]?.lotes || []).map(l => ({...l}));
        editingProducts[id] = {
            cat,
            selectedTurn: 1,
            valores: { 1: copyLotes(1), 2: copyLotes(2), 3: copyLotes(3) }
        };
    }
    renderStructurePanel();
}

function renderEditProductForm(id) {
    const state = editingProducts[id];
    if (!state) return '';
    const safeId     = id.replace(/[^a-zA-Z0-9_]/g,'_');
    const activeTurn = state.selectedTurn || 1;
    const cv         = currentTurnValues[id] || {};

    // Tabs con indicador de datos
    const tabsHtml = [1,2,3].map(t => {
        const hasSaved = (cv[t]?.lotes || []).some(l => parseFloat(l.bultos) > 0 || l.lote);
        const sel      = activeTurn === t ? 'selected' : '';
        const dot      = hasSaved ? ' •' : '';
        const style    = hasSaved ? 'border-color:rgba(34,197,94,0.5);' : '';
        return `<div class="sp-turn-tab ${sel}" onclick="selectEditTurn('${id}',${t})" id="etab_${safeId}_${t}" style="${style}">T${t}${dot}</div>`;
    }).join('');

    // Indicador del turno activo
    const savedTotal = cv[activeTurn]?.total || 0;
    const savedLote  = cv[activeTurn]?.totalLote || '';
    const hasCurrentData = parseFloat(savedTotal) > 0 || savedLote;
    
    let indicatorHtml = '';
    if (state.cat === 'materiales') {
        indicatorHtml = hasCurrentData
            ? `<div class="sp-edit-indicator">📋 Turno ${activeTurn} guardado: Lote <strong>${savedLote || '--'}</strong> — edita abajo</div>`
            : `<div style="font-size:10px;color:#64748b;margin-bottom:8px;">○ Turno ${activeTurn} vacío — agrega los lotes</div>`;
    } else {
        indicatorHtml = hasCurrentData
            ? `<div class="sp-edit-indicator">📋 Turno ${activeTurn} guardado: <strong>${savedTotal} bultos</strong> (ref. lote: <strong>${savedLote || '--'}</strong>) — edita abajo</div>`
            : `<div style="font-size:10px;color:#64748b;margin-bottom:8px;">○ Turno ${activeTurn} vacío — agrega los lotes</div>`;
    }

    const lotesArr = state.valores[activeTurn] || [];

    return `<div class="sp-edit-form">
        <div class="sp-val-form-title" style="color:#fbbf24;">✏️ Editar lotes por turno</div>
        <div class="sp-turn-tabs">${tabsHtml}</div>
        ${indicatorHtml}
        <div id="editfields_${safeId}">${renderEditTurnFields(id, activeTurn, lotesArr)}</div>
        <button class="sp-edit-save-btn" onclick="saveProductValues('${id}')">&#x1F4BE; GUARDAR VALORES</button>
    </div>`;
}

function renderEditTurnFields(id, turn, lotesArr) {
    const state    = editingProducts[id];
    const cat      = state ? state.cat : 'harinas';
    const safeId   = id.replace(/[^a-zA-Z0-9_]/g,'_');
    const rows     = lotesArr.length > 0 ? lotesArr : [{bultos:'', lote:''}];
    let h = `<div class="sp-lote-hdr">
        ${cat !== 'materiales' ? '<span style="width:68px;">Bultos</span>' : ''}
        <span style="flex:1;">N° Lote</span>
        <span style="width:22px;"></span>
    </div>`;
    rows.forEach((l, idx) => {
        h += `<div class="sp-lote-row">
            ${cat !== 'materiales' ? `<input type="number" min="0" step="1" class="sp-lote-row-num"
                   placeholder="0" value="${l.bultos || ''}"
                   oninput="saveLoteRow('${id}',${turn},${idx},'bultos',this.value)">` : ''}
            <input type="text" class="sp-lote-row-id"
                   placeholder="${cat === 'materiales' ? 'ej. L-1020' : 'ej. 060426B'}" value="${l.lote || ''}"
                   oninput="saveLoteRow('${id}',${turn},${idx},'lote',this.value)">
            <button class="sp-lote-row-del" onclick="removeLoteRow('${id}',${turn},${idx})"
                    title="Eliminar fila">×</button>
        </div>`;
    });
    h += `<button class="sp-add-lote-btn" onclick="addLoteRow('${id}',${turn})">+ Agregar lote</button>`;
    return h;
}

function selectEditTurn(id, turn) {
    const state = editingProducts[id];
    if (!state) return;
    state.selectedTurn = turn;
    renderStructurePanel(); // re-render completo para actualizar indicador + tabs + filas
}

function saveLoteRow(id, turn, rowIdx, field, value) {
    const state = editingProducts[id];
    if (!state) return;
    if (!Array.isArray(state.valores[turn])) state.valores[turn] = [];
    if (!state.valores[turn][rowIdx]) state.valores[turn][rowIdx] = {bultos:'', lote:''};
    state.valores[turn][rowIdx][field] = value;
}

function addLoteRow(id, turn) {
    const state = editingProducts[id];
    if (!state) return;
    if (!Array.isArray(state.valores[turn])) state.valores[turn] = [{bultos:'', lote:''}];
    state.valores[turn].push({bultos:'', lote:''});
    const safeId = id.replace(/[^a-zA-Z0-9_]/g,'_');
    const el = document.getElementById(`editfields_${safeId}`);
    if (el) el.innerHTML = renderEditTurnFields(id, turn, state.valores[turn]);
}

function removeLoteRow(id, turn, rowIdx) {
    const state = editingProducts[id];
    if (!state || !Array.isArray(state.valores[turn])) return;
    state.valores[turn].splice(rowIdx, 1);
    const safeId = id.replace(/[^a-zA-Z0-9_]/g,'_');
    const el = document.getElementById(`editfields_${safeId}`);
    if (el) el.innerHTML = renderEditTurnFields(id, turn, state.valores[turn]);
}

// Flush: con oninput el estado ya está en sync; esta función queda como guardia
function flushEditInputs(id) { /* oninput mantiene el estado actualizado */ }

function saveProductValues(id) {
    const state = editingProducts[id];
    if (!state) return;

    const btn = document.querySelector(`button[onclick="saveProductValues('${id}')"]`);
    if (btn) { btn.textContent = 'GUARDANDO...'; btn.disabled = true; }

    // valores: {1: [{bultos, lote}, ...], 2: [...], 3: [...]}
    fetch('editar_valores_turno.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            sede:    '<?= $sede ?>',
            fecha:   '<?= $fecha ?>',
            id,
            cat:     state.cat,
            valores: state.valores
        })
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            delete editingProducts[id];
            setTimeout(() => window.location.reload(), 600);
        } else {
            alert('Error: ' + res.error);
            if (btn) { btn.textContent = '&#x1F4BE; GUARDAR VALORES'; btn.disabled = false; }
        }
    })
    .catch(() => {
        alert('Error de conexión.');
        if (btn) { btn.textContent = '&#x1F4BE; GUARDAR VALORES'; btn.disabled = false; }
    });
}

function updateFooterInfo() {
    const info = document.getElementById('spFooterInfo');
    const btn  = document.getElementById('spApplyBtn');
    const totalChanges = pendingAdd.length + pendingRemove.length;

    if (totalChanges === 0) {
        info.innerHTML = 'Sin cambios pendientes';
        btn.disabled = true;
    } else {
        let parts = [];
        if (pendingAdd.length    > 0) parts.push(`<strong>+${pendingAdd.length}</strong> por agregar`);
        if (pendingRemove.length > 0) parts.push(`<span class="del-count">-${pendingRemove.length}</span> por eliminar`);
        info.innerHTML = parts.join(' &nbsp;·&nbsp; ');
        btn.disabled = false;
    }
}

function applyStructureChanges() {
    // Antes de enviar, volcar los inputs del DOM al estado (para el ítem activo)
    pendingAdd.forEach(item => flushTurnInputs(item));

    const btn = document.getElementById('spApplyBtn');
    btn.disabled = true;
    btn.textContent = 'APLICANDO...';

    // Preparar payload: incluir valores por turno y materiales asociados
    const agregar = pendingAdd.map(item => ({
        id:         item.id,
        cat:        item.cat,
        valores:    item.valores,
        materiales: item.materiales || []
    }));

    fetch('editar_estructura.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            sede:     '<?= $sede ?>',
            fecha:    '<?= $fecha ?>',
            agregar:  agregar,
            eliminar: pendingRemove
        })
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            btn.textContent = '¡APLICADO!';
            btn.style.background = 'linear-gradient(135deg, #059669, #10b981)';
            setTimeout(() => window.location.reload(), 800);
        } else {
            alert('Error: ' + res.error);
            btn.disabled = false;
            btn.textContent = 'APLICAR CAMBIOS';
        }
    })
    .catch(err => {
        console.error('Error:', err);
        alert('Error de conexión al aplicar cambios.');
        btn.disabled = false;
        btn.textContent = 'APLICAR CAMBIOS';
    });
}
</script>

</body>
</html>
