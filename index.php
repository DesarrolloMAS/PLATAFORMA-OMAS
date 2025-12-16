<?php
session_start(); // Iniciar sesión
require './template/conection.php'; // Conexión a la base de datos

// Función para validar usuario
function validarUsuario($pdoUsuarios, $nombre, $cedula, $cargo, $sede) {
    try {
        $stmt = $pdoUsuarios->prepare("
            SELECT * FROM usuarios
            WHERE nombre_u = :nombre AND cedula_u = :cedula AND Cargo = :cargo AND sede = :sede
        ");
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':cedula', $cedula);
        $stmt->bindParam(':cargo', $cargo);
        $stmt->bindParam(':sede', $sede);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error al validar usuario: " . $e->getMessage());
        return false;
    }
}

// Si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = htmlspecialchars(trim($_POST['nombre']));
    $cedula = htmlspecialchars(trim($_POST['cedula']));
    $cargo = htmlspecialchars(trim($_POST['cargo']));
    $sede = htmlspecialchars(trim($_POST['sede1']));

    // Validar el usuario
    $usuario = validarUsuario($pdoUsuarios, $nombre, $cedula, $cargo, $sede);

    
    if ($usuario) {
        $_SESSION['id_usuario'] = $usuario['id_usuario']; // ID del usuario
        $_SESSION['nombre'] = $usuario['nombre_u']; // Nombre del usuario
        $_SESSION['area'] = $usuario['Area']; // **Área del usuario** (modificación realizada aquí)
        $_SESSION['rol'] = $usuario['rol']; // Rol del usuario
        $_SESSION['cargo'] = $usuario['Cargo']; // Cargo del usuario
        $_SESSION['sede'] = $usuario['sede']; // Sede del usuario
    

        // Redirigir según el área y el rol
        switch ($usuario['Area']) {
            case 'Operaciones':
                // Redirigir según el rol en el área de Operaciones
                switch ($usuario['rol']) {
                    case 'adm': // Rol alto en Operaciones
                        header('Location: ./template/menu_adm.html');
                        exit();
                    case '1': // Rol alto en Operaciones
                        header('Location: ./template/menu_adm.html');
                        exit();
                    case '3': // Rol bajo en Operaciones
                        header('Location: ./template/menu.html');
                        exit();
                    default:
                        header('Location: ./template/problemas.html');
                        exit();
                }
                break;

            case 'Calidad':
                // Redirigir según el rol en el área de Calidad
                switch ($usuario['rol']) {
                    case 'adm': // Rol alto en Operaciones
                        header('Location: ./template/menu_adm_calidad.html');
                        exit();
                    case '1': // Rol alto en Calidad
                        header('Location: ./template/menu_adm_calidad.html');
                        exit();
                    case '3': // Rol bajo en Calidad
                        header('Location: ./template/menu_calidad.html');
                        exit();
                    default:
                        header('Location: ./template/problemas.html');
                        exit();
                }
                break;

            default:
                // Si el área no coincide con Operaciones o Calidad
                header('Location: ./template/default_dashboard.php');
                exit();
        }
    } else {
        echo "<script>
            alert('Credenciales incorrectas. Por favor, verifica los datos.');
            window.location.href = 'index.php';
        </script>";
        exit();
    }
}

// Obtener los cargos y usuarios antes de mostrar la página
function obtenerCargosDesdeSQL($pdoUsuarios) {
    try {
        $stmt = $pdoUsuarios->query("SELECT DISTINCT Cargo FROM usuarios");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        error_log("Error al obtener los cargos: " . $e->getMessage());
        return [];
    }
}

$cargos = obtenerCargosDesdeSQL($pdoUsuarios);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;300&family=Roboto:wght@100;300&display=swap"  rel="stylesheet">
    <link rel="stylesheet" href="./css/index.css">
    <title>OMAS DESARROLLO</title>
</head>
<body class="body">
    <!-- Barra superior -->
    <div class="barra-superior"></div>
    
    <div class="encabezado">
    
    </div>
    <div class="formularic">
        <form class="formulario" method="post">
             <div class="header-section">
                <span class="icon" aria-hidden="true">
                    <!-- Engranaje/producción SVG --><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                </span>
                <div class="titulo">
                    <h1>M</h1>  
                    <h1>A</h1>  
                    <h1>S</h1>               
                </div>
             </div>
        
            <div class="formulariog">
                <input type="text" id="campo_nombre" name="nombre" placeholder="Ingrese su nombre" required>
            </div>

            <div class="formulariog">
                <select name="cargo" id="cargo" required>
                    <option value="" disabled selected>cargo</option>
                    <option value="">No hay cargos disponibles</option>   
                       <?php if (!empty($cargos)): ?>
                        <?php foreach ($cargos as $cargo): ?>
                            <option value="<?php echo htmlspecialchars($cargo, ENT_QUOTES, 'UTF-8'); ?>">
                                <?php echo htmlspecialchars($cargo, ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="">No hay cargos disponibles</option>
                    <?php endif; ?>                   
                    <option value="NULL">Ninguno</option>
                </select>
            </div>

            <div class="formulariog">
                
                <input type="text" id="campo_cedula" name="cedula" placeholder="Ingrese su cédula" required>
            </div>

            <div class="formulariog">        
                <select id="campo_sede" name="sede1" required>
                    <option value="" disabled selected>Seleccione su sede</option>
                    <option value="ZS">Zona Sur</option>
                    <option value="ZC">Zona Centro</option>
                    <option value="ZB">Buga</option>
                </select>
            </div>

            <div class="formularioa">
                <button type="submit" class="boton">Iniciar Sesion</button>   
            </div>
            <br>
            <a href="./template/registroUsuarios.php" class="botonprime">Registrarse</a>
        </form>
            </div>
    
    <!-- Barra inferior -->
    <div class="barra-inferior"></div>
</body>
</html>