<?php
ob_start();
require_once '../sesion.php';

verificarAutenticacion();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sede = $_SESSION['sede'] ?? 'Sin_Sede';
    
    // Directorio base para guardar los archivos
    $base_dir = "../../archivos/generados/gestion_subproductos/" . $sede . "/";
    
    if (!file_exists($base_dir)) {
        @mkdir($base_dir, 0777, true);
    }

    $mes_actual = date('Y-m');
    $archivo_json = $base_dir . $mes_actual . ".json";

    // Recopilar datos del formulario
    $nuevo_registro = [
        'id' => uniqid('subprod_'),
        'fecha_inspeccion' => $_POST['fecha_inspeccion'] ?? date('Y-m-d'),
        'timestamp' => date('Y-m-d H:i:s'),
        'usuario_registro' => $_SESSION['nombre'] ?? 'Desconocido',
        'evaluaciones' => [],
        'total_suma' => $_POST['total_suma'] ?? 0,
        'porcentaje_cumplimiento' => $_POST['porcentaje_cumplimiento'] ?? '0%',
        'responsable_inspeccion' => $_POST['responsable_inspeccion'] ?? ''
    ];

    // Procesar las evaluaciones dinámicamente
    if (isset($_POST['evaluaciones']) && is_array($_POST['evaluaciones'])) {
        foreach ($_POST['evaluaciones'] as $key => $valor) {
            $label = $_POST['label_' . $key] ?? $key;
            $nuevo_registro['evaluaciones'][$key] = [
                'criterio' => $label,
                'resultado' => $valor
            ];
        }
    }

    // Procesar hallazgo si existe
    if (!empty(trim($_POST['hallazgo_obs'] ?? ''))) {
        $nuevo_registro['hallazgo'] = [
            'fecha' => $_POST['hallazgo_fecha'] ?? '',
            'observacion' => $_POST['hallazgo_obs'] ?? '',
            'responsable_inspeccion' => $_POST['hallazgo_resp_ins'] ?? '',
            'responsable_verificacion' => $_POST['hallazgo_resp_verif'] ?? ''
        ];
    }

    // Leer archivo existente o inicializar array
    $datos_existentes = [];
    if (file_exists($archivo_json)) {
        $contenido = @file_get_contents($archivo_json);
        if ($contenido) {
            $datos_existentes = json_decode($contenido, true) ?? [];
        }
    }

    // Agregar el nuevo registro
    $datos_existentes[] = $nuevo_registro;

    // Guardar en el archivo JSON
    $json_output = json_encode($datos_existentes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
    ob_clean(); // Limpiar cualquier advertencia previa
    header('Content-Type: application/json');
    
    if (@file_put_contents($archivo_json, $json_output) !== false) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Evaluación guardada exitosamente.',
            'redirect' => 'index.php'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Error al guardar el archivo JSON. Verifique los permisos de escritura en el servidor.'
        ]);
    }
} else {
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Método no permitido.'
    ]);
}
?>
