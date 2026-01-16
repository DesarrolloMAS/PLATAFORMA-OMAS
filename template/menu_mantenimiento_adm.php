<?php
//=============================================
//VALIDACION DE SESION JS 
require_once 'sesion.php';
verificarAutenticacion();
//=============================================
?>
<script>
setInterval(function() {
    verificarSesionAjax(function(activa) {
        // Si no está activa, ya se redirigió
    });
}, 10000); // Cada 10 segundos
function verificarSesionAjax(callback) {
    fetch('/template/verificar_sesion.php')
        .then(response => response.json())
        .then(data => {
            if (data.activa) {
                callback(true);
            } else {
                // Muestra mensaje o redirige
                alert('Tu sesión ha expirado. Por favor, inicia sesión nuevamente.');
                window.location.href = '/index.php?motivo=sesion';
                callback(false);
            }
        })
        .catch(() => {
            alert('Error al verificar la sesión.');
            callback(false);
        });
}

// Ejemplo: antes de enviar un formulario
const form = document.querySelector('form');
if (form) {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        verificarSesionAjax(function(activa) {
            if (activa) {
                form.submit();
            }
            // Si no está activa, ya se redirigió
        });
    });
}
</script>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/menu_revisiones_mant.css">
    <title>MANTENIMIENTO</title>
</head>
<body>
    <h1>MANTENIMIENTO</h1>
    <div class="menu-revisiones-links">
        <a href="seccion_revisiones.html">
        <span class="icon" aria-hidden="true">
            <svg width="48" height="48" viewBox="0 0 48 48" fill="none">
                    <rect x="8" y="8" width="32" height="32" rx="4" stroke="#fee5b8" stroke-width="3"/>
                    <rect x="16" y="16" width="16" height="12" rx="2" stroke="#fee5b8" stroke-width="3"/>
                    <rect x="16" y="30" width="8" height="6" rx="1" stroke="#fee5b8" stroke-width="3"/>
                </svg>
        </span>
        VOLVER AL MENU</a>
        <a href="./formulario001.php">
        <span class="icon" aria-hidden="true">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#fee5b8" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z"/>
        </svg>
    </span>
    ORDEN DE MANTENIMIENTO
</a>
    <a href="./maquinas/revision_maquinas.php">
    <span class="icon" aria-hidden="true">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#fee5b8" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/>
        </svg>
    </span>
    VERIFICACIONES MAQUINAS
    </a>
    <a href="../template/ordenes_pendientes.php">
    <span class="icon" aria-hidden="true">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#fee5b8" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m6.75 12H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
        </svg>

    </span>
    ORDENES PENDIENTES
    </a>
    
    <a href="../template/problemas.html">
    <span class="icon" aria-hidden="true">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#fee5b8" stroke-width="1.5">
  <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
</svg>


    </span>
    LIBERACIONES
    </a>
    
    <?php
    // Mostrar botón de administrador solo para roles específicos
    $cargosAdmin = ['adm', '1'];
    $cargoActual = $_SESSION['rol'] ?? '';
    
    if (in_array($cargoActual, $cargosAdmin)):
    ?>
    <a href="../template/ordenes_administrador.php">
    <span class="icon" aria-hidden="true">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#fee5b8" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
        </svg>
    </span>
    VERIFICAR ORDENES
    </a>
    <?php endif; ?>
    
    </div>
</body>
</html>