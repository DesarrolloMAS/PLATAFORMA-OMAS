<?php
require '../../sesion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id'])) {
    echo json_encode(['success' => false, 'error' => 'Solicitud inválida.']);
    exit;
}

$id = intval($_POST['id']);

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

    // Opcional: Podríamos validar sesión aquí si hay niveles de usuario
    // if ($_SESSION['rol'] !== 'admin') { ... }

    $stmt = $pdo->prepare("DELETE FROM bitacora WHERE id = :id");
    $stmt->execute([':id' => $id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'No se encontró el registro o ya fue eliminado.']);
    }

} catch (PDOException $e) {
    error_log("Error al eliminar registro: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Error en el servidor al intentar eliminar el registro.']);
}
