<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require 'conection.php';

$contraseña = "fmt2025"; // Contraseña para el registro

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['password'])) {
        $password = $_POST['password'];

        if ($password === $contraseña) {
            $nombre = htmlspecialchars(trim($_POST['nombre']));
            $cargo = htmlspecialchars(trim($_POST['cargo']));
            $cedula = htmlspecialchars(trim($_POST['cedula']));
            $cedula2 = htmlspecialchars(trim($_POST['cedula2']));
            $sede = htmlspecialchars(trim($_POST['sede']));
            $rol = htmlspecialchars(trim($_POST['campo_rol']));
            $area = htmlspecialchars(trim($_POST['Area']));

            // Validar que las cédulas coincidan
            if ($cedula !== $cedula2) {
                echo "<script>
                    alert('Error: Las cédulas no coinciden.');
                    window.history.back();
                </script>";
                exit();
            }

            try {
                // Verificar si la cédula ya existe
                $stmt = $pdoUsuarios->prepare("SELECT * FROM usuarios WHERE cedula_u = :cedula");
                $stmt->bindParam(':cedula', $cedula);
                $stmt->execute();

                if ($stmt->rowCount() > 0) {
                    echo "<script>
                        alert('Error: La cédula ya está registrada.');
                        window.history.back();
                    </script>";
                    exit();
                }

                // Insertar el registro en la base de datos
// Insertar el registro en la base de datos
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
$stmt->execute(); // Ejecutar la consulta


                echo "<script>
                    alert('Registro exitoso.');
                    window.location.href = '/fmt/index.php';
                </script>";
            } catch (PDOException $e) {
                echo "<script>
                    alert('Error al registrar: " . $e->getMessage() . "');
                    window.history.back();
                </script>";
            }
        } else {
            echo "<script>
                alert('Error: Contraseña incorrecta.');
                window.history.back();
            </script>";
        }
    } else {
        echo "<script>
            alert('Error: Debe ingresar la contraseña.');
            window.history.back();
        </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/registrousuarios.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@200..700&display=swap" rel="stylesheet">
    <title>Registro Organizacion MAS</title> 
</head>
<div class="encabezado"></div>
<body class="body" >
    <div class="barra-superior"></div>

<div class="formularioc">
    <div class="containertitulo">
  <h1 class="titulo">CREA UNA CUENTA</h1>

    <form class="formularioregistr"  method="post" > 
        <div class="formulario"> 
            <div class="seccion">
                <h3 class="titulo2">Datos personales</h3>
                        
                        <div class="formulariog">
                            <input type="text" id="campo_nombre" name="nombre" placeholder="Ingrese su nombre" required>
                        </div>

                        <div class="formulariog">
                            <input type="text" id="campo_cedula" name="cedula" placeholder="Ingrese su cédula" required>
                        </div>

                        <div class="formulariog">
                            <input type="text" id="campo_cedula2" name="cedula2" placeholder="Confirme su cedula" required>
                        </div>

                        <div class="formulariog">
                            <input type="password" id="campo_password" name="password" placeholder="Ingrese su Contraseña" required>
                        </div>
            </Div>
            <div class="seccion">
                <h3 class="titulo2">Datos empresariales</h3>
                <div class="formulariog">
                    <select name="cargo" id="">
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
                        <option value="Administrador">Administrador</option>
                    </select>   
                </div>
                <div class="formulariog">
                    <select name="campo_rol" id="campo_rol">
                    <option value="adm">Administrador</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    </select>
                </div>
                <div class="formulariog">
                    <select id="campo_Area" name="Area" required>
                        <option value="" disabled selected>Seleccione su Area</option>
                        <option value="Operaciones">Operaciones</option>
                        <option value="Calidad">Calidad</option>
                        <option value="Tecnología">Tecnología</option>
                    </select>
                </div>
                <div class="formulariog">
                    <select id="campo_sede" name="sede" required>
                        <option value="" disabled selected>Seleccione su sede</option>
                        <option value="ZS">Zona Sur</option>
                        <option value="ZC">Zona Centro</option>
                        <option value="ZB">Buga</option>
                    </select>
                </div>

            </div>
        </div>    
          <div class="formularioa">
              <button type="submit" class="boton">Registrarse</button>
            <a href="/index.php" class="botonprime">Volver</a>
    </div>
    </form>
  
</div>

</body> 
           
</html>
