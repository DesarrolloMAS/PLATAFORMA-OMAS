<?php
require 'sesion.php';
verificarAutenticacion();

if ($_SESSION['rol'] === 'adm') {
    header('Location: session_revisiones_jefatura.php');
} elseif ($_SESSION['rol'] === '1') {
    header('Location: session_revisiones_jefatura.php');
} else {
    header('Location: seccion_revisiones.html');
}
exit();