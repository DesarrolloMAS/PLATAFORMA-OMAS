<?php
require '../../sesion.php';
verificarAutenticacion();
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$file = $data['file'] ?? '';
$id = $data['id'] ?? '';
$sede = $_SESSION['sede'];

if (!$file || !$id) {
    echo json_encode(['status' => 'error', 'message' => 'Faltan parámetros.']);
    exit;
}

$file_path = "../../../archivos/generados/PNC/" . $sede . "/" . $file;
if (!file_exists($file_path)) {
    echo json_encode(['status' => 'error', 'message' => 'Archivo no encontrado.']);
    exit;
}

$json_data = json_decode(file_get_contents($file_path), true) ?: [];
$updated = false;

foreach ($json_data as &$r) {
    if ($r['id'] === $id) {
        $camposPermitidos = [
            'quien_reporta', 'fecha_reporte', 'producto', 'cantidad_nc', 'numero_lote', 
            'descripcion_evento', 'verifica_identificacion', 'correccion_destino', 'num_documento', 
            'responsable_correccion', 'fecha_correccion'
        ];
        
        foreach($camposPermitidos as $campo) {
            if (isset($data[$campo])) {
                $r[$campo] = trim($data[$campo]);
            }
        }
        
        $updated = true;
        break;
    }
}

if ($updated) {
    if (file_put_contents($file_path, json_encode($json_data, JSON_PRETTY_PRINT)) !== false) {
        echo json_encode(['status' => 'ok']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al escribir el JSON en el servidor.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'El registro no se encontró.']);
}
?>
