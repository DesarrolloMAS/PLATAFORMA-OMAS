<?php
session_start();
header('Content-Type: application/json');
echo json_encode(['esAdmin' => isset($_SESSION['rol']) && $_SESSION['rol'] === 'adm']);
