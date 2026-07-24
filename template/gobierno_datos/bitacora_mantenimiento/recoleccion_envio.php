<?php
// Configuración de la base de datos PostgreSQL
$host = '127.0.0.1';
$db = 'bitacora_mantenimiento';
$user = 'bitacora_user';
$pass = 'bitacora2026';

try {
    $dsn = "pgsql:host=$host;port=5432;dbname=$db;";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    // Si hay un fallo de conexión, se guardará en el log silenciosamente
    error_log("Fallo en la conexión PostgreSQL para bitácora: " . $e->getMessage());
    die('Error de BD');
}

// === LOG DE DEPURACIÓN DE EMERGENCIA ===
$log_dir = __DIR__ . '/log_debug.txt';
$data_log = "--- NUEVA PETICIÓN RECIBIDA: " . date('Y-m-d H:i:s') . " ---\n";
$data_log .= "PARÁMETROS RECIBIDOS (POST):\n" . print_r($_POST, true) . "\n";
file_put_contents($log_dir, $data_log, FILE_APPEND);
// =======================================

// 1. Recepción de Variables por POST enviadas por el generador de PDF
// Asignamos fallback a vacío en caso de faltar
$fecha = $_POST['fechainicial'] ?? date('Y-m-d');
$hora = $_POST['horainicial'] ?? date('H:i');
$maquina = trim($_POST['objeto_dañado'] ?? 'No especificado');
$codigo = trim($_POST['cod'] ?? '');
$ubicacion = trim($_POST['ubi'] ?? '');
$descripcion_falla = trim($_POST['descripcion_daños'] ?? '');
// El ruteo exacto mapea la celda 'A22' con el nombre usando guion (-) medio
$tecnico = trim($_POST['responsable-Miembro_De_La_Compañia_0'] ?? 'No especificado');

// Ajuste de nombres según formulario001_rastreo.php y discriminación solicitada
// Priorizamos 'tipo_mantenimiento_especial' si existe, si no usamos 'tipomantenimiento'
$tipo_mantenimiento = trim($_POST['tipo_mantenimiento_especial'] ?? $_POST['tipomantenimiento'] ?? 'General');

$zona = trim($_POST['zona'] ?? 'Centro');
$especialidad = trim($_POST['especialidad'] ?? 'General');

// 2. Preparar un JSON de detalles extra por si se detectan variables adicionales 
// omitidas temporalmente en la base de datos principal
$detalles_extra = json_encode([
    'recepcion_automatica' => true,
    'fecha_ingreso_server' => date('Y-m-d H:i:s')
]);

$sql = "INSERT INTO bitacora 
        (fecha, hora, maquina, codigo, ubicacion, descripcion_falla, tecnico, tipo_mantenimiento, detalles_extra, zona, especialidad) 
        VALUES 
        (:fecha, :hora, :maquina, :codigo, :ubicacion, :descripcion, :tecnico, :tipo, :detalles, :zona, :especialidad)";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':fecha' => $fecha,
        ':hora' => $hora,
        ':maquina' => $maquina,
        ':codigo' => $codigo,
        ':ubicacion' => $ubicacion,
        ':descripcion' => $descripcion_falla,
        ':tecnico' => $tecnico,
        ':tipo' => $tipo_mantenimiento,
        ':detalles' => $detalles_extra,
        ':zona' => $zona,
        ':especialidad' => $especialidad
    ]);
    
    // Todo insertado correctamente
    http_response_code(200);
    echo "Bitácora recibida y guardada en PostgreSQL";
} catch (PDOException $e) {
    error_log("Error al insertar en la bitacora de PGSQL: " . $e->getMessage());
    http_response_code(500);
    echo "Error interno de base de datos";
}
?>