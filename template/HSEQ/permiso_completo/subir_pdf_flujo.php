<?php
include '../../sesion.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['nombre']) || empty($_SESSION['sede'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Sin sesión.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id_flujo'])
    || !isset($_FILES['pdf']) || $_FILES['pdf']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Datos incompletos.']);
    exit;
}

$MAX_SIZE = 50 * 1024 * 1024;

if ($_FILES['pdf']['size'] > $MAX_SIZE) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'El PDF excede el tamaño máximo (50MB).']);
    exit;
}

$ext   = strtolower(pathinfo($_FILES['pdf']['name'], PATHINFO_EXTENSION));
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime  = $finfo->file($_FILES['pdf']['tmp_name']);

if ($ext !== 'pdf' || $mime !== 'application/pdf') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'El archivo debe ser un PDF válido.']);
    exit;
}

$sede_san = preg_replace('/[^A-Za-z0-9_-]/', '', $_SESSION['sede']);
$id_flujo = trim($_POST['id_flujo']);

$sede_dir   = "../../../archivos/generados/flujo_permisos/" . $sede_san . "/";
$upload_dir = $sede_dir . "documentos_pdf/";
if (!file_exists($upload_dir)) @mkdir($upload_dir, 0777, true);

$nombre_guardado = 'pdf_' . uniqid() . '.pdf';
$documento_pdf   = null;
$actualizado     = false;

foreach (glob($sede_dir . "*.json") as $archivo) {
    $datos = json_decode(file_get_contents($archivo), true) ?: [];
    foreach ($datos as &$flujo) {
        if ($flujo['id_flujo'] !== $id_flujo) continue;

        if (!move_uploaded_file($_FILES['pdf']['tmp_name'], $upload_dir . $nombre_guardado)) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'No se pudo guardar el archivo en disco.']);
            exit;
        }

        if (!empty($flujo['documento_pdf']['nombre_archivo'])) {
            @unlink($upload_dir . $flujo['documento_pdf']['nombre_archivo']);
        }

        $documento_pdf = [
            'nombre_original' => basename($_FILES['pdf']['name']),
            'nombre_archivo'  => $nombre_guardado,
            'ruta'            => "/archivos/generados/flujo_permisos/{$sede_san}/documentos_pdf/{$nombre_guardado}",
            'timestamp'       => date('Y-m-d H:i:s'),
            'usuario'         => $_SESSION['nombre'],
        ];
        $flujo['documento_pdf'] = $documento_pdf;
        $actualizado = true;
        break;
    }
    unset($flujo);
    if ($actualizado) {
        file_put_contents($archivo, json_encode($datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        break;
    }
}

echo json_encode($actualizado
    ? ['status' => 'success', 'documento_pdf' => $documento_pdf]
    : ['status' => 'error', 'message' => 'Flujo no encontrado.']
);
?>
