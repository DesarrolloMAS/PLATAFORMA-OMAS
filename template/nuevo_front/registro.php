<?php
require '../conection.php'; // Conexión a la base de datos (misma lógica que registroUsuarios.php)

$claveRegistro = "fmt2025"; // Código de registro requerido, igual que en el formulario legacy

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $nombre = htmlspecialchars(trim($_POST['nombre'] ?? ''));
    $cedula = htmlspecialchars(trim($_POST['cedula'] ?? ''));
    $cedula2 = htmlspecialchars(trim($_POST['cedula2'] ?? ''));
    $cargo = htmlspecialchars(trim($_POST['cargo'] ?? ''));
    $rol = htmlspecialchars(trim($_POST['campo_rol'] ?? ''));
    $area = htmlspecialchars(trim($_POST['Area'] ?? ''));
    $sede = htmlspecialchars(trim($_POST['sede'] ?? ''));

    if ($password !== $claveRegistro) {
        header('Location: /template/nuevo_front/registro.php?error=password');
        exit();
    }

    if ($cedula === '' || $cedula !== $cedula2) {
        header('Location: /template/nuevo_front/registro.php?error=cedula');
        exit();
    }

    try {
        $stmt = $pdoUsuarios->prepare("SELECT * FROM usuarios WHERE cedula_u = :cedula");
        $stmt->bindParam(':cedula', $cedula);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            header('Location: /template/nuevo_front/registro.php?error=duplicado');
            exit();
        }

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

        header('Location: /template/nuevo_front/index.php?registro=exito');
        exit();
    } catch (PDOException $e) {
        error_log('Error al registrar usuario: ' . $e->getMessage());
        header('Location: /template/nuevo_front/registro.php?error=servidor');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/styles.css?v=<?php echo filemtime(__DIR__ . '/css/styles.css'); ?>">
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

            <form class="auth-form" method="post">
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

                <div class="field">
                    <label for="campo_password">Contraseña de registro</label>
                    <input type="password" id="campo_password" name="password" placeholder="Código entregado por tu sede" required>
                </div>

                <h3 class="form-section-title">Datos empresariales</h3>

                <div class="field">
                    <label for="cargo">Cargo</label>
                    <select id="cargo" name="cargo" required>
                        <option value="" disabled selected>Selecciona tu cargo</option>
                        <option value="Gestion Calidad">Gestion Calidad</option>
                        <option value="Aprendiz">Aprendiz</option>
                        <option value="Coordinador de Operaciones">Coordinador Operaciones</option>
                        <option value="Jefe Nacional Operaciones">Jefe Nacional De Operaciones</option>
                        <option value="Jefe Operaciones Sur">Jefe Operaciones Sur</option>
                        <option value="Analista de Operaciones">Analista de Operaciones</option>
                        <option value="Maquinista">Maquinista</option>
                        <option value="Asistente Recepcion de Trigo">Asistente Recepcion de Trigo</option>
                        <option value="Auxiliar de almacen">Auxiliar de almacen</option>
                        <option value="Almacenista">Almacenista</option>
                        <option value="Auxiliar de mantenimiento">Auxiliar de mantenimiento</option>
                        <option value="Auxiliar de Operaciones">Auxiliar de Operaciones</option>
                        <option value="Empacador">Empacador</option>
                        <option value="Lider de almacen">Lider de almacen</option>
                        <option value="Lider de Turno">Lider de Turno</option>
                        <option value="Lider de Mantenimiento">Lider de Mantenimiento</option>
                        <option value="Lider de Mantenimiento Locativo">Lider de Mantenimiento Locativo</option>
                        <option value="Lider de Mantenimiento Mecanico">Lider de Mantenimiento Mecanico</option>
                        <option value="Operario de Carga">Operario de Carga</option>
                        <option value="Tecnico Mecanico">Tecnico Mecanico</option>
                        <option value="Revision Inocuidad">Revision Inocuidad</option>
                        <option value="Jefe HSEQ">Jefe HSEQ</option>
                        <option value="Analista HSEQ">Analista HSEQ</option>
                        <option value="Auxiliar HSEQ">Auxiliar HSEQ</option>
                    </select>
                </div>

                <div class="field">
                    <label for="campo_rol">Rol</label>
                    <select id="campo_rol" name="campo_rol" required>
                        <option value="" disabled selected>Selecciona tu rol</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
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

                <div class="submit-row">
                    <button type="submit" class="btn-primary" data-loading-text="Registrando…">Crear cuenta</button>
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
