<?php
require_once '../sesion.php';
verificarAutenticacion();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sede = $_SESSION['sede'] ?? 'NA';
    $mes_actual = date('Y-m');
    $nombre_archivo = $mes_actual . ".json";
    
    $base_dir = "../../archivos/generados/orden_mantenimiento/" . $sede . "/";
    
    if (!is_dir($base_dir)) {
        mkdir($base_dir, 0777, true);
    }
    
    $target_file = $base_dir . $nombre_archivo;
    
    // Cargar datos existentes
    $registros = [];
    if (file_exists($target_file)) {
        $registros = json_decode(file_get_contents($target_file), true) ?: [];
    }
    
    // Procesar Imágenes (Base64 or File)
    $upload_img_dir = "../../archivos/generados/orden_mantenimiento/evidencias/";
    if (!is_dir($upload_img_dir)) mkdir($upload_img_dir, 0777, true);

    $processImage = function($fileKey) use ($upload_img_dir) {
        if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES[$fileKey]['name'], PATHINFO_EXTENSION);
            $newName = uniqid('img_') . '.' . $ext;
            move_uploaded_file($_FILES[$fileKey]['tmp_name'], $upload_img_dir . $newName);
            return $newName;
        }
        return null;
    };

    $foto_antes    = $processImage('foto_antes');
    $foto_despues  = $processImage('foto_despues');
    $foto_antes2   = $processImage('foto_antes2');
    $foto_despues2 = $processImage('foto_despues2');

    // Procesar Firmas (Base64)
    $processSignature = function($base64Data, $prefix) use ($upload_img_dir) {
        if (!empty($base64Data) && strpos($base64Data, 'data:image') !== false) {
            $data = explode(',', $base64Data);
            $img = base64_decode($data[1]);
            $fileName = $prefix . '_' . uniqid() . '.png';
            file_put_contents($upload_img_dir . $fileName, $img);
            return $fileName;
        }
        return null;
    };

    $firma_solicitante = $processSignature($_POST['firma_solicitante'] ?? '', 'sig_sol');
    $firma_autorizado = $processSignature($_POST['firma_autorizado'] ?? '', 'sig_aut');
    $firma_respLim = $processSignature($_POST['firma_respLim'] ?? '', 'sig_limp');
    $firma_respLim2 = $processSignature($_POST['firma_respLim2'] ?? '', 'sig_rev_limp');

    // Nuevo Registro
    $nuevo_registro = [
        'id' => uniqid(),
        'timestamp' => date('Y-m-d H:i:s'),
        'usuario_creador' => $_SESSION['nombre'],
        'sede' => $sede,
        'datos' => $_POST,
        'evidencias' => [
            'antes'    => $foto_antes,
            'despues'  => $foto_despues,
            'antes2'   => $foto_antes2,
            'despues2' => $foto_despues2,
        ],
        'firmas' => [
            'solicitante' => $firma_solicitante,
            'autorizado' => $firma_autorizado,
            'limpieza' => $firma_respLim,
            'revisa_limpieza' => $firma_respLim2
        ]
    ];
    
    // Quitar data pesada de los datos del POST (las firmas base64 ya se procesaron)
    unset($nuevo_registro['datos']['firma_solicitante']);
    unset($nuevo_registro['datos']['firma_autorizado']);
    unset($nuevo_registro['datos']['firma_respLim']);
    unset($nuevo_registro['datos']['firma_respLim2']);
    
    $registros[] = $nuevo_registro;
    
    if (file_put_contents($target_file, json_encode($registros, JSON_PRETTY_PRINT))) {
        echo "<script>
            alert('Orden de Mantenimiento Guardada Exitosamente.');
            window.location.href = 'index.php';
        </script>";
    } else {
        echo "Error al guardar el registro.";
    }
}
?>
