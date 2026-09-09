<?php
/**
 * Conexión propia y exclusiva para la integración mIA -> NOVA.
 * NO reutiliza template/conection.php ni la Postgres de bitacora_mantenimiento
 * — base de datos nueva y aislada (mia_datos), para que un problema en este
 * receptor no pueda afectar a ningún otro módulo de la plataforma.
 */
$host = 'localhost';
$dbname = 'mia_datos';
$username = 'root';
$password = '0000';

try {
    $pdoMia = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdoMia->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    die(json_encode(["error" => "No se pudo conectar a la base de datos de integración mIA"]));
}
