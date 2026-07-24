<?php
session_start();
if (isset($_GET['sede'])) {
    $_SESSION['sede'] = $_GET['sede'];
}
header('Location: session_revisiones_jefatura.php'); // Redirige de vuelta al menú
exit();
?>