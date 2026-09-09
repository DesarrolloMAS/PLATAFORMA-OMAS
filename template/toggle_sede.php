<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['nombre'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Sin sesión activa.']);
    exit;
}

$actual = $_SESSION['sede'] ?? 'ZC';
$nueva  = ($actual === 'ZS') ? 'ZC' : 'ZS';
$_SESSION['sede'] = $nueva;

echo json_encode(['status' => 'success', 'sede' => $nueva]);
