<?php
// Evitar acceso directo sin POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Método no permitido.');
}

// ── 1. PROCESAR TODOS LOS CAMPOS POST ──
function limpiar_post($arr) {
    $res = [];
    foreach ($arr as $k => $v) {
        if (is_array($v)) {
            $res[$k] = limpiar_post($v);
        } else {
            $res[$k] = trim($v);
        }
    }
    return $res;
}
$datos_post = limpiar_post($_POST);

// ── 2. PROCESAR TODOS LOS ARCHIVOS ──
$archivos_subidos = [];
$upload_dir = '/var/www/fmt/archivos/generados/HSEQ/investigacionesfotos/';
if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

$ext_permitidas = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
$max_size = 10 * 1024 * 1024; // 10MB

foreach ($_FILES as $campo => $info) {
    $archivos_subidos[$campo] = [];
    if (is_array($info['name'])) {
        for ($i = 0; $i < count($info['name']); $i++) {
            if ($info['error'][$i] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($info['name'][$i], PATHINFO_EXTENSION));
                if (in_array($ext, $ext_permitidas) && $info['size'][$i] <= $max_size) {
                    $filename = $campo . '_' . date('Ymd_His') . '_' . $i . '_' . uniqid() . '.' . $ext;
                    $destino = $upload_dir . $filename;
                    if (move_uploaded_file($info['tmp_name'][$i], $destino)) {
                        $archivos_subidos[$campo][] = [
                            'nombre_original' => $info['name'][$i],
                            'nombre_guardado' => $filename,
                            'ruta'            => $destino,
                            'tamano'          => $info['size'][$i],
                            'tipo'            => $info['type'][$i],
                        ];
                    }
                }
            }
        }
    }
}
$total_fotos = 0;
foreach ($archivos_subidos as $arr) $total_fotos += count($arr);

// ── 3. GUARDAR TODO EN UN SOLO JSON ──
$data = [
    'timestamp' => date('Y-m-d H:i:s'),
    'post'      => $datos_post,
    'archivos'  => $archivos_subidos,
];

// ── 4. GUARDAR COMO JSON ──
$json_dir = '/var/www/fmt/archivos/generados/HSEQ/investigacionesjson/';
if (!is_dir($json_dir)) mkdir($json_dir, 0755, true);
$json_file = $json_dir . 'investigacion_' . date('Ymd_His') . '.json';
file_put_contents($json_file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// ── 5. RESPUESTA AL USUARIO ──
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Investigación Registrada</title>
    <style>
        body { font-family: 'Inter', sans-serif; background: #0a1628; color: #d4dae5; display: flex; align-items: center; justify-content: center; min-height: 100vh;}
        .card { background: #16243a; border-radius: 16px; padding: 2.5rem; text-align: center; box-shadow: 0 12px 40px rgba(0,0,0,0.4);}
        .icon { width: 64px; height: 64px; background: #10b981; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;}
        .icon svg { width: 32px; height: 32px; stroke: #fff; fill: none;}
        h2 { margin-bottom: 1rem;}
        .stats { color: #6b8ab0; margin-bottom: 1.5rem;}
        .stats span { color: #10b981; font-weight: 600;}
        a { color: #fff; background: #3b82f6; padding: 0.75rem 2rem; border-radius: 8px; text-decoration: none; font-weight: 600;}
        a:hover { background: #2563eb;}
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">
            <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        </div>
        <h2>Investigación Registrada Exitosamente</h2>
        <div class="stats">
            Total archivos subidos: <span><?= $total_fotos ?></span>
        </div>
        <a href="/template/menu_hseq_adm.html">← Volver al Menu</a>
    </div>
</body>
</html>