<?php
require '../sesion.php';

// Validar credenciales de administrador (Roles '1' y 'adm' autorizados),
// mismo criterio que empaque_v2/admin_catalogo_empaques.php.
if (!isset($_SESSION['rol']) || ($_SESSION['rol'] != '1' && $_SESSION['rol'] != 'adm')) {
    die("Acceso Denegado. Solo administradores pueden configurar el catálogo.");
}

$catalogo_file = "../../archivos/generados/envasado_v2/catalogo_productos.json";

if (!file_exists($catalogo_file)) {
    die("Catálogo no encontrado. Ingrese primero a la galería para inicializarlo.");
}

$catalogo_data = json_decode(file_get_contents($catalogo_file), true) ?: [];

// Enlace con la lista maestra de productos (molienda_v2/gestion_productos.php
// → archivos/generados/molienda/config_[zona].json). Cada fila de este
// catálogo guarda el `producto_id` maestro, que la galería pasa al formulario
// de envasado; así el cronograma de producción puede contar los registros.
// Solo harinas y subproductos (los materiales no se envasan).
function productosMaestros(string $zona): array {
    $ruta = __DIR__ . "/../../archivos/generados/molienda/config_" . preg_replace('/[^A-Z]/', '', $zona) . ".json";
    $config = json_decode(@file_get_contents($ruta) ?: '[]', true) ?: [];
    $lista = [];
    foreach (['harinas' => 'Harinas', 'subproductos' => 'Subproductos'] as $cat => $label) {
        foreach ($config[$cat] ?? [] as $item) {
            if (!empty($item['id'])) $lista[$item['id']] = ['name' => $item['name'] ?? $item['id'], 'grupo' => $label];
        }
    }
    return $lista;
}
$maestros = [];
foreach (array_keys($catalogo_data) as $zonaTmp) $maestros[$zonaTmp] = productosMaestros($zonaTmp);

// producto_id enviado: vacío (sin enlazar) o un id que exista en la lista
// maestra de esa zona; cualquier otro valor se descarta.
function productoIdValido(array $maestrosZona, string $id): string {
    return isset($maestrosZona[$id]) ? $id : '';
}

// Sugerencia: solo coincidencia exacta de nombre, sin distinguir mayúsculas
// (ej. "Extrapan x50" ↔ "EXTRAPAN X50"). No se guarda sola.
function sugerirProductoId(array $maestrosZona, string $producto): string {
    $objetivo = mb_strtolower(trim($producto), 'UTF-8');
    foreach ($maestrosZona as $id => $m) {
        if (mb_strtolower(trim($m['name']), 'UTF-8') === $objetivo) return $id;
    }
    return '';
}

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $zona = $_POST['zona'] ?? '';

    if (isset($catalogo_data[$zona])) {
        if ($action === 'add') {
            $producto = trim($_POST['producto'] ?? '');
            $empaque = trim($_POST['empaque'] ?? '');
            $productoId = productoIdValido($maestros[$zona], trim($_POST['producto_id'] ?? ''));
            if ($producto !== '' && $empaque !== '') {
                $catalogo_data[$zona][] = ['producto' => $producto, 'empaque' => $empaque, 'producto_id' => $productoId];
                $mensaje = "Producto añadido con éxito a $zona.";
            }
        } elseif ($action === 'edit') {
            $index = $_POST['index'] ?? -1;
            $producto = trim($_POST['producto'] ?? '');
            $empaque = trim($_POST['empaque'] ?? '');
            $productoId = productoIdValido($maestros[$zona], trim($_POST['producto_id'] ?? ''));
            if (isset($catalogo_data[$zona][$index]) && $producto !== '' && $empaque !== '') {
                $catalogo_data[$zona][$index] = ['producto' => $producto, 'empaque' => $empaque, 'producto_id' => $productoId];
                $mensaje = "Producto actualizado con éxito en $zona.";
            }
        } elseif ($action === 'delete') {
            $index = $_POST['index'] ?? -1;
            if (isset($catalogo_data[$zona][$index])) {
                array_splice($catalogo_data[$zona], $index, 1);
                $mensaje = "Producto eliminado con éxito de $zona.";
            }
        }

        if (@file_put_contents($catalogo_file, json_encode($catalogo_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) === false) {
            $mensaje = 'No se pudo guardar: el servidor no tiene permiso de escritura sobre catalogo_productos.json.';
        }
    }
}
?>
<?php
function selectMaestro(array $maestrosZona, string $seleccionado, string $clase): string {
    if (!$maestrosZona) {
        return '<span class="maestro-na" title="Esta zona no tiene lista maestra en Gestión de Productos">Sin lista maestra</span>';
    }
    $html = '<select name="producto_id" class="' . $clase . ' maestro-select" title="Producto maestro (Gestión de Productos)">';
    $html .= '<option value="">— Sin enlazar —</option>';
    $grupo = null;
    foreach ($maestrosZona as $id => $m) {
        if ($m['grupo'] !== $grupo) {
            if ($grupo !== null) $html .= '</optgroup>';
            $grupo = $m['grupo'];
            $html .= '<optgroup label="' . htmlspecialchars($grupo) . '">';
        }
        $html .= '<option value="' . htmlspecialchars($id) . '"' . ($id === $seleccionado ? ' selected' : '') . '>' . htmlspecialchars($m['name']) . '</option>';
    }
    return $html . ($grupo !== null ? '</optgroup>' : '') . '</select>';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración de Productos - Envasado V2</title>
    <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700&family=Space+Mono:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #0B0E14;
            --panel-bg: #151A22;
            --accent: #FF3366; /* Rojo Admin, mismo criterio que admin_catalogo_empaques.php */
            --accent-glow: rgba(255, 51, 102, 0.4);
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
            background-image: linear-gradient(rgba(255, 51, 102, 0.03) 1px, transparent 1px),
                              linear-gradient(90deg, rgba(255, 51, 102, 0.03) 1px, transparent 1px);
            background-size: 30px 30px;
        }

        .container { max-width: 1100px; margin: 0 auto; }

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

        .main-title { font-size: 24px; font-weight: 700; color: #fff; text-transform: uppercase; margin-bottom: 4px; }
        .sub-title { color: var(--text-muted); font-size: 14px; font-family: 'Space Mono', monospace; }

        .btn-back {
            background: transparent; border: 1px solid var(--text-muted); color: var(--text-main);
            padding: 8px 16px; border-radius: var(--r-sm); font-family: 'Space Mono', monospace;
            text-decoration: none; font-size: 12px; transition: all 0.3s;
        }
        .btn-back:hover { background: rgba(255,255,255,0.1); }

        .sys-msg {
            background: rgba(16, 185, 129, 0.1); border: 1px solid #10B981; color: #10B981;
            padding: 15px; border-radius: var(--r-md); margin-bottom: 30px; font-weight: bold;
            font-family: 'Space Mono', monospace; font-size: 13px; text-align: center;
        }

        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }

        .zone-card {
            background: var(--panel-bg); border: 1px solid var(--border-color);
            padding: 25px; border-radius: var(--r-md);
        }

        .zone-title {
            font-size: 18px; font-weight: 700; color: var(--accent); border-bottom: 1px dashed var(--border-color);
            padding-bottom: 15px; margin-bottom: 20px;
        }

        .ref-list {
            list-style: none; display: flex; flex-direction: column; gap: 10px; margin-bottom: 25px;
        }

        .ref-item {
            display: flex; flex-direction: column; gap: 8px;
            background: var(--input-bg); padding: 12px 15px; border-radius: var(--r-sm);
            border: 1px solid rgba(255,255,255,0.05); font-size: 14px;
        }

        .ref-edit-form { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
        .maestro-select { cursor: pointer; }
        .maestro-select option, .maestro-select optgroup { background: #141620; color: #fff; }
        .maestro-na { font-size: 11px; color: #8a94a6; font-style: italic; padding: 0 6px; }
        .ref-item--sin-enlace { border-color: rgba(242, 177, 52, 0.35); }
        .aviso-enlace { font-size: 11.5px; color: #f2b134; line-height: 1.4; }

        .ref-input {
            flex: 1; min-width: 110px; background: var(--panel-bg); border: 1px solid var(--border-color);
            color: #fff; padding: 8px 10px; border-radius: var(--r-sm); font-family: 'Barlow'; font-size: 13px;
        }
        .ref-input:focus { outline: none; border-color: var(--accent); }

        .btn-save {
            background: transparent; border: 1px solid #10B981; color: #10B981;
            cursor: pointer; border-radius: var(--r-sm); padding: 8px 12px; font-size: 11px;
            font-weight: bold; transition: all 0.2s; white-space: nowrap;
        }
        .btn-save:hover { background: #10B981; color: #fff; }

        .btn-del {
            background: transparent; border: 1px solid #FF3366; color: #FF3366;
            cursor: pointer; border-radius: var(--r-sm); padding: 8px 12px; font-size: 11px;
            font-weight: bold; transition: all 0.2s; white-space: nowrap;
        }
        .btn-del:hover { background: #FF3366; color: #fff; }

        .add-form {
            display: flex; flex-wrap: wrap; gap: 10px;
        }

        .add-input {
            flex: 1; min-width: 120px; background: var(--input-bg); border: 1px solid var(--border-color);
            color: #fff; padding: 10px; border-radius: var(--r-sm); font-family: 'Barlow'; font-size: 14px;
        }
        .add-input:focus { outline: none; border-color: var(--accent); }

        .btn-add {
            background: var(--accent); color: #fff; border: none; padding: 10px 15px;
            border-radius: var(--r-sm); font-weight: bold; cursor: pointer; transition: 0.3s;
        }
        .btn-add:hover { box-shadow: 0 0 15px var(--accent-glow); }

        @media (max-width: 768px) {
            .grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header-box">
        <div>
            <h1 class="main-title">Administración de Productos</h1>
            <div class="sub-title">Catálogo producto → empaque de Envasado V2</div>
        </div>
        <a href="geleria_productos.php" class="btn-back">← VOLVER A GALERÍA</a>
    </div>

    <?php if ($mensaje): ?>
        <div class="sys-msg"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>

    <div class="grid">
        <?php foreach ($catalogo_data as $zona => $items): ?>
        <div class="zone-card">
            <?php $nEnlazados = count(array_filter($items, fn($i) => !empty($i['producto_id']) && isset($maestros[$zona][$i['producto_id']]))); ?>
            <h2 class="zone-title">ZONA <?= htmlspecialchars($zona) ?> (<?= count($items) ?> productos · <?= $nEnlazados ?> enlazados)</h2>

            <ul class="ref-list">
                <?php foreach ($items as $index => $item):
                    $idGuardado = $item['producto_id'] ?? '';
                    $enlazado = $idGuardado !== '' && isset($maestros[$zona][$idGuardado]);
                    $sugerido = $enlazado ? '' : sugerirProductoId($maestros[$zona], $item['producto']);
                ?>
                <li class="ref-item<?= $enlazado ? '' : ' ref-item--sin-enlace' ?>">
                    <form method="post" class="ref-edit-form">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="zona" value="<?= htmlspecialchars($zona) ?>">
                        <input type="hidden" name="index" value="<?= $index ?>">
                        <input type="text" name="producto" class="ref-input" value="<?= htmlspecialchars($item['producto']) ?>" required>
                        <input type="text" name="empaque" class="ref-input" value="<?= htmlspecialchars($item['empaque']) ?>" required>
                        <?= selectMaestro($maestros[$zona], $enlazado ? $idGuardado : $sugerido, 'ref-input') ?>
                        <button type="submit" class="btn-save">GUARDAR</button>
                    </form>
                    <?php if (!$enlazado && $maestros[$zona]): ?>
                    <div class="aviso-enlace"><?= $sugerido
                        ? '⚠ Sin enlazar — se sugiere <strong>' . htmlspecialchars($maestros[$zona][$sugerido]['name']) . '</strong>; presiona GUARDAR para confirmarlo.'
                        : '⚠ Sin enlazar — elige su producto maestro y presiona GUARDAR. Mientras tanto, sus registros de envasado no cuentan en el cronograma de producción.' ?></div>
                    <?php endif; ?>
                    <form method="post" onsubmit="return confirm('¿Eliminar este producto de forma permanente?');" style="margin:0;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="zona" value="<?= htmlspecialchars($zona) ?>">
                        <input type="hidden" name="index" value="<?= $index ?>">
                        <button type="submit" class="btn-del">BORRAR</button>
                    </form>
                </li>
                <?php endforeach; ?>
            </ul>

            <form method="post" class="add-form">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="zona" value="<?= htmlspecialchars($zona) ?>">
                <input type="text" name="producto" class="add-input" placeholder="Nombre del producto..." required>
                <input type="text" name="empaque" class="add-input" placeholder="Empaque asociado..." required>
                <?= selectMaestro($maestros[$zona], '', 'add-input') ?>
                <button type="submit" class="btn-add">+</button>
            </form>
        </div>
        <?php endforeach; ?>
    </div>
</div>

</body>
</html>
