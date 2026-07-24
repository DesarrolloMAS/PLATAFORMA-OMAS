<?php
require_once 'sesion.php'; // Asegúrate de incluir la sesión
header('Content-Type: application/json');
verificarAutenticacion();
echo json_encode([
    'sede' => $_SESSION['sede'],
    'nombre' => $_SESSION['nombre'],
    // agrega otros datos si los necesitas
]);