<?php
session_start();
header('Content-Type: application/json');
if (
    isset($_SESSION['id_usuario']) &&
    isset($_SESSION['area']) &&
    isset($_SESSION['nombre'])
) {
    echo json_encode(['activa' => true]);
} else {
    echo json_encode(['activa' => false]);
}
exit();