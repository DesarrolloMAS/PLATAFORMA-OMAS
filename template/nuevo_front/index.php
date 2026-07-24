<?php
session_start(); // Iniciar sesión
require '../conection.php'; // Conexión a la base de datos (misma lógica que /index.php)

// Función para validar usuario (idéntica a la del index.php raíz)
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

    $usuario = validarUsuario($pdoUsuarios, $nombre, $cedula, $cargo, $sede);

    if ($usuario) {
        // Guardar datos en la sesión
        $_SESSION['id_usuario'] = $usuario['id_usuario'];
        $_SESSION['nombre'] = $usuario['nombre_u'];
        $_SESSION['area'] = $usuario['Area'];
        $_SESSION['rol'] = $usuario['rol'];
        $_SESSION['cargo'] = $usuario['Cargo'];
        $_SESSION['sede'] = $usuario['sede'];
        $_SESSION['cedula'] = $usuario['cedula'];

        // Lógica preferencial para la cédula específica
        if ($cedula === '1085253029') {
            switch ($usuario['rol']) {
                case '1': // Rol alto
                    header('Location: /template/menu_ino_calidad.html');
                    exit();
                default:
                    header('Location: /template/problemas.html');
                    exit();
            }
        }

        // Lógica general para otros usuarios
        switch ($usuario['Area']) {
            case 'Operaciones':
                switch ($usuario['rol']) {
                    case 'adm':
                    case '1': // Rol alto
                        header('Location: /template/menu_adm.html');
                        exit();
                    case '2': // Rol Intermedio
                        header('Location: /template/menu_adm.html');
                        exit();
                    case '3': // Rol bajo
                        header('Location: /template/menu.html');
                        exit();
                    default:
                        header('Location: /template/problemas.html');
                        exit();
                }
                break;

            case 'Calidad':
                switch ($usuario['rol']) {
                    case 'adm':
                    case '1': // Rol alto
                        header('Location: /template/menu_adm_calidad.html');
                        exit();
                    case '3': // Rol bajo
                        header('Location: /template/menu_calidad.html');
                        exit();
                    default:
                        header('Location: /template/problemas.html');
                        exit();
                }
                break;

            case 'HSEQ':
                switch ($usuario['rol']) {
                    case 'adm':
                    case '1': // Rol alto
                        header('Location: /template/menu_hseq_adm.html');
                        exit();
                    case '3': // Rol bajo
                        header('Location: /template/menu_hseq_adm.html');
                        exit();
                    default:
                        header('Location: /template/problemas.html');
                        exit();
                }
                break;

            default:
                // Área no reconocida
                header('Location: /template/default_dashboard.php');
                exit();
        }
    } else {
        // Usuario no válido — mismo flujo que el index.php raíz, pero el aviso
        // ahora lo muestra app.ts vía SweetAlert2 (?error=1) en vez del alert() nativo.
        header('Location: /template/nuevo_front/index.php?error=1');
        exit();
    }
}

// Obtener los cargos desde SQL antes de mostrar la página
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
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/styles.css?v=<?php echo filemtime(__DIR__ . '/css/styles.css'); ?>">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Ingreso · Organización MAS</title>
</head>
<body>
    <canvas id="bg-canvas"></canvas>
    <div class="bg-veil"></div>

    <div class="page">
        <div class="brand">
            <span class="brand-logo-frame">
                <img src="./img/logo_omas.png" alt="Organización MAS" class="brand-logo">
                <span class="brand-shine" aria-hidden="true"></span>
            </span>
        </div>

        <div class="auth-card">
            <div class="auth-head">
                <h1 class="auth-title">Bienvenido de nuevo</h1>
                <p class="auth-sub">Ingresa tus credenciales para continuar</p>
            </div>

            <form class="auth-form" method="post">
                <div class="field">
                    <label for="campo_nombre">Nombre</label>
                    <input type="text" id="campo_nombre" name="nombre" placeholder="Ingresa tu nombre" required>
                </div>

                <div class="field">
                    <label for="cargo">Cargo</label>
                    <select name="cargo" id="cargo" required>
                        <option value="" disabled selected>Selecciona tu cargo</option>
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

                <div class="field">
                    <label for="campo_cedula">Cédula</label>
                    <input type="text" id="campo_cedula" name="cedula" placeholder="Ingresa tu cédula" required>
                </div>

                <div class="field">
                    <label for="campo_sede">Sede</label>
                    <select id="campo_sede" name="sede1" required>
                        <option value="" disabled selected>Selecciona tu sede</option>
                        <option value="ZS">Zona Sur</option>
                        <option value="ZC">Zona Centro</option>
                        <option value="ZB">Buga</option>
                    </select>
                </div>

                <div class="submit-row">
                    <button type="submit" class="btn-primary">Iniciar sesión</button>
                </div>

                <div class="auth-foot">
                    ¿No tienes cuenta? <a href="/template/registroUsuarios.php">Regístrate</a>
                </div>
            </form>
        </div>

        <div class="status-line">
            <span class="status-dot" aria-hidden="true"></span>
            SISTEMA JSON INTERCONECTADO
        </div>
    </div>

    <script src="./dist/app.js?v=<?php echo filemtime(__DIR__ . '/dist/app.js'); ?>"></script>
    <?php if (isset($_GET['error'])): ?>
    <script>
        window.addEventListener('DOMContentLoaded', function () {
            if (window.Swal) {
                Swal.fire({
                    icon: 'error',
                    title: 'Credenciales incorrectas',
                    text: 'Por favor, verifica los datos.',
                    background: '#141519',
                    color: '#f2f2f3',
                    confirmButtonColor: '#ff7a1a'
                });
            }
        });
    </script>
    <?php endif; ?>
</body>
</html>
