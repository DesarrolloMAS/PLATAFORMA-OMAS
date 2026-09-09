<?php
session_start();
header('Content-Type: application/json');

// Único rol admin de Calidad: 'adm' (igual que admin/menu_admin.php y
// redireccion.php — '1' es un rol operativo normal en esta área, a
// diferencia de Operaciones).
$esAdmin = isset($_SESSION['area'], $_SESSION['rol'])
    && $_SESSION['area'] === 'Calidad'
    && $_SESSION['rol'] === 'adm';

echo json_encode(['esAdmin' => $esAdmin]);
