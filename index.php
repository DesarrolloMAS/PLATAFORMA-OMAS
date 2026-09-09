<?php
session_start(); // Iniciar sesión
require './template/conection.php'; // Conexión a la base de datos

// El login se envía vía fetch() (ver dist/app.js) para poder reproducir una
// animación de salida antes de navegar al menú; se detecta por este header
// para responder JSON en vez de con un header('Location: ...') clásico.
function esPeticionFetch(): bool {
    return ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch';
}

function responderRedireccion(string $url): void {
    if (esPeticionFetch()) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'success', 'redirect' => $url]);
    } else {
        header('Location: ' . $url);
    }
    exit();
}

function responderErrorLogin(string $mensaje): void {
    if (esPeticionFetch()) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'error', 'message' => $mensaje]);
        exit();
    }
    header('Location: index.php?error=1');
    exit();
}

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
                    responderRedireccion('./template/menu_ino_calidad.html');
                default:
                    responderRedireccion('./template/problemas.html');
            }
        }

        // Lógica general para otros usuarios
        switch ($usuario['Area']) {
            case 'Operaciones':
                switch ($usuario['rol']) {
                    case 'adm':
                    case '1': // Rol alto
                        responderRedireccion('./template/menu_adm.html');
                    case '2': // Rol Intermedio
                        responderRedireccion('./template/menu_adm.html');
                    default:
                        responderRedireccion('./template/problemas.html');
                }
                break;

            case 'Calidad':
                switch ($usuario['rol']) {
                    case 'adm': // Único rol admin en Calidad (ver redireccion.php)
                        responderRedireccion('./template/menu_administracion_calidad.html');
                    case '1': // Rol operativo normal en Calidad, no admin
                        responderRedireccion('./template/menu_adm_calidad.html');
                    default:
                        responderRedireccion('./template/problemas.html');
                }
                break;

            case 'HSEQ':
                switch ($usuario['rol']) {
                    case 'adm': // Único rol admin en HSEQ (ver redireccion.php)
                        responderRedireccion('./template/menu_administracion_hseq.html');
                    case '1': // Rol bajo
                        responderRedireccion('./template/menu_hseq_adm.html');
                    case '3': // Rol bajo
                        responderRedireccion('./template/menu_hseq_adm.html');
                    default:
                        responderRedireccion('./template/problemas.html');
                }
                break;

            default:
                // Área no reconocida
                responderRedireccion('./template/default_dashboard.php');
        }
    } else {
        // Usuario no válido
        responderErrorLogin('Credenciales incorrectas. Por favor, verifica los datos.');
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
    <link rel="stylesheet" href="./css/index.css?v=<?php echo filemtime(__DIR__ . '/css/index.css'); ?>">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Ingreso · Organización MAS</title>
</head>
<body>
    <canvas id="bg-canvas"></canvas>
    <div class="bg-veil"></div>

    <div class="page">
        <div class="brand">
            <span class="brand-logo-frame">
                <img src="./img/logo_omas_azul.png" alt="Organización MAS" class="brand-logo">
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
                    ¿No tienes cuenta? <a href="./registro.php">Regístrate</a>
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
                    background: '#ffffff',
                    color: '#0b1b33',
                    confirmButtonColor: '#2563eb'
                });
            }
        });
    </script>
    <?php endif; ?>
    <?php if (isset($_GET['registro']) && $_GET['registro'] === 'exito'): ?>
    <script>
        window.addEventListener('DOMContentLoaded', function () {
            if (window.Swal) {
                Swal.fire({
                    icon: 'success',
                    title: 'Registro exitoso',
                    text: 'Ya puedes iniciar sesión con tus datos.',
                    background: '#ffffff',
                    color: '#0b1b33',
                    confirmButtonColor: '#2563eb'
                });
            }
        });
    </script>
    <?php endif; ?>
</body>
</html>
