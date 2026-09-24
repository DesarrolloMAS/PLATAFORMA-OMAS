<?php
require './template/conection.php'; // Conexión a la base de datos (misma lógica que registroUsuarios.php)

$claveRegistro = "fmt2025"; // Código de registro requerido, igual que en el formulario legacy

// Lista de cargos disponibles en el select: se lee del mismo archivo que
// gobierna el mapeo cargo -> rol (template/admin/menu_admin.php), para que
// un cargo creado ahí aparezca aquí sin tocar este archivo.
$cargoRolesPath = __DIR__ . '/archivos/generados/admin/cargo_roles.json';
$cargoRolesTodos = file_exists($cargoRolesPath)
    ? (json_decode(file_get_contents($cargoRolesPath), true) ?: [])
    : [];
ksort($cargoRolesTodos);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $nombre = htmlspecialchars(trim($_POST['nombre'] ?? ''));
    $cedula = htmlspecialchars(trim($_POST['cedula'] ?? ''));
    $cedula2 = htmlspecialchars(trim($_POST['cedula2'] ?? ''));
    $cargo = htmlspecialchars(trim($_POST['cargo'] ?? ''));
    $area = htmlspecialchars(trim($_POST['Area'] ?? ''));
    $sede = htmlspecialchars(trim($_POST['sede'] ?? ''));
    $cedulaAdmin = htmlspecialchars(trim($_POST['cedula_admin'] ?? ''));

    if ($password !== $claveRegistro) {
        header('Location: registro.php?error=password');
        exit();
    }

    if ($cedula === '' || $cedula !== $cedula2) {
        header('Location: registro.php?error=cedula');
        exit();
    }

    try {
        // Confirmación por un administrador — mismo patrón de firma por
        // cédula que usa el resto de la plataforma (ver README: "Firmas").
        // Se busca la cédula en usuarios y se exige rol de administrador.
        $stmtAdmin = $pdoUsuarios->prepare("SELECT rol FROM usuarios WHERE cedula_u = :cedula");
        $stmtAdmin->bindParam(':cedula', $cedulaAdmin);
        $stmtAdmin->execute();
        $admin = $stmtAdmin->fetch(PDO::FETCH_ASSOC);

        if (!$admin || !in_array($admin['rol'], ['adm', '1'], true)) {
            header('Location: registro.php?error=admin');
            exit();
        }

        $stmt = $pdoUsuarios->prepare("SELECT * FROM usuarios WHERE cedula_u = :cedula");
        $stmt->bindParam(':cedula', $cedula);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            header('Location: registro.php?error=duplicado');
            exit();
        }

        // El rol ya no se selecciona manualmente: se deriva del cargo elegido,
        // según la asignación configurada en el panel de administración
        // (template/admin/menu_admin.php -> archivos/generados/admin/cargo_roles.json).
        $rol = $cargoRolesTodos[$cargo] ?? '3';

        $stmt = $pdoUsuarios->prepare("
            INSERT INTO usuarios (nombre_u, Cargo, cedula_u, sede, rol, Area)
            VALUES (:nombre, :cargo, :cedula, :sede, :rol, :Area)
        ");
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':cargo', $cargo);
        $stmt->bindParam(':cedula', $cedula);
        $stmt->bindParam(':sede', $sede);
        $stmt->bindParam(':rol', $rol);
        $stmt->bindParam(':Area', $area);
        $stmt->execute();

        header('Location: index.php?registro=exito');
        exit();
    } catch (PDOException $e) {
        error_log('Error al registrar usuario: ' . $e->getMessage());
        header('Location: registro.php?error=servidor');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/index.css?v=<?php echo filemtime(__DIR__ . '/css/index.css'); ?>">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Regístrate · Organización MAS</title>
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

        <div class="auth-card auth-card--wide">
            <div class="auth-head">
                <h1 class="auth-title">Crea tu cuenta</h1>
                <p class="auth-sub">Completa tus datos para solicitar acceso</p>
            </div>

            <form class="auth-form auth-form--steps" method="post">
                <div class="step-indicator" data-reveal>
                    <div class="step-row">
                        <span class="step-circle step-circle--active" data-step-circle="1">1</span>
                        <span class="step-track"><span class="step-track-fill"></span></span>
                        <span class="step-circle" data-step-circle="2">2</span>
                        <span class="step-track"><span class="step-track-fill"></span></span>
                        <span class="step-circle" data-step-circle="3">3</span>
                    </div>
                    <div class="step-labels">
                        <span class="step-label step-label--active" data-step-label="1">Datos personales</span>
                        <span class="step-label" data-step-label="2">Datos empresariales</span>
                        <span class="step-label" data-step-label="3">Confirmación</span>
                    </div>
                </div>

                <div class="form-step" data-step="1">
                    <h3 class="form-section-title">Datos personales</h3>

                    <div class="field">
                        <label for="campo_nombre">Nombre</label>
                        <input type="text" id="campo_nombre" name="nombre" placeholder="Ingresa tu nombre" required>
                    </div>

                    <div class="field">
                        <label for="campo_cedula">Cédula</label>
                        <input type="text" id="campo_cedula" name="cedula" placeholder="Ingresa tu cédula" required>
                    </div>

                    <div class="field">
                        <label for="campo_cedula2">Confirmar cédula</label>
                        <input type="text" id="campo_cedula2" name="cedula2" placeholder="Confirma tu cédula" required>
                    </div>

                    <div class="submit-row">
                        <button type="button" class="btn-primary" data-goto="2">Siguiente</button>
                    </div>
                </div>

                <div class="form-step form-step--hidden" data-step="2">
                    <h3 class="form-section-title">Datos empresariales</h3>

                    <div class="field">
                        <label for="cargo">Cargo</label>
                        <select id="cargo" name="cargo" required>
                            <option value="" disabled selected>Selecciona tu cargo</option>
                            <?php foreach (array_keys($cargoRolesTodos) as $cargoOpcion): ?>
                            <option value="<?= htmlspecialchars($cargoOpcion) ?>"><?= htmlspecialchars($cargoOpcion) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="field">
                        <label for="campo_Area">Área</label>
                        <select id="campo_Area" name="Area" required>
                            <option value="" disabled selected>Selecciona tu área</option>
                            <option value="Operaciones">Operaciones</option>
                            <option value="Calidad">Calidad</option>
                            <option value="Tecnología">Tecnología</option>
                            <option value="HSEQ">HSEQ</option>
                            <option value="Desarrollo">Desarrollo</option>
                        </select>
                    </div>

                    <div class="field">
                        <label for="campo_sede">Sede</label>
                        <select id="campo_sede" name="sede" required>
                            <option value="" disabled selected>Selecciona tu sede</option>
                            <option value="ZS">Zona Sur</option>
                            <option value="ZC">Zona Centro</option>
                            <option value="ZB">Buga</option>
                        </select>
                    </div>

                    <div class="submit-row submit-row--split">
                        <button type="button" class="btn-secondary" data-goto="1">Atrás</button>
                        <button type="button" class="btn-primary" data-goto="3">Siguiente</button>
                    </div>
                </div>

                <div class="form-step form-step--hidden" data-step="3">
                    <h3 class="form-section-title">Confirmación</h3>

                    <div class="field">
                        <label for="campo_password">Contraseña de registro</label>
                        <input type="password" id="campo_password" name="password" placeholder="Código entregado por tu sede" required>
                    </div>

                    <div class="field">
                        <label for="campo_cedula_admin">Cédula del administrador que autoriza</label>
                        <input type="text" id="campo_cedula_admin" name="cedula_admin" placeholder="Cédula de un administrador" required>
                    </div>

                    <div class="submit-row submit-row--split">
                        <button type="button" class="btn-secondary" data-goto="2">Atrás</button>
                        <button type="submit" class="btn-primary" data-loading-text="Registrando…">Crear cuenta</button>
                    </div>
                </div>

                <div class="auth-foot">
                    ¿Ya tienes cuenta? <a href="./index.php">Inicia sesión</a>
                </div>
            </form>
        </div>
    </div>

    <script src="./dist/app.js?v=<?php echo filemtime(__DIR__ . '/dist/app.js'); ?>"></script>
    <?php if (isset($_GET['error'])): ?>
    <script>
        window.addEventListener('DOMContentLoaded', function () {
            if (!window.Swal) return;
            const mensajes = {
                password: 'El código de registro no es correcto.',
                cedula: 'Las cédulas ingresadas no coinciden.',
                admin: 'La cédula del administrador no es válida o no tiene permisos.',
                duplicado: 'Esa cédula ya está registrada.',
                servidor: 'Ocurrió un error al registrar. Intenta de nuevo.'
            };
            const clave = <?php echo json_encode($_GET['error']); ?>;
            Swal.fire({
                icon: 'error',
                title: 'No se pudo completar el registro',
                text: mensajes[clave] || 'Verifica los datos e intenta de nuevo.',
                background: '#ffffff',
                color: '#0b1b33',
                confirmButtonColor: '#2563eb'
            });
        });
    </script>
    <?php endif; ?>
</body>
</html>
