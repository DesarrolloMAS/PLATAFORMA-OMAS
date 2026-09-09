<?php
session_start();
header('Content-Type: application/json');

// Único rol admin de HSEQ: 'adm' (igual que admin/menu_admin.php,
// redireccion.php e index.php — mismo patrón que Calidad).
$esAdmin = isset($_SESSION['area'], $_SESSION['rol'])
    && $_SESSION['area'] === 'HSEQ'
    && $_SESSION['rol'] === 'adm';

echo json_encode(['esAdmin' => $esAdmin]);
