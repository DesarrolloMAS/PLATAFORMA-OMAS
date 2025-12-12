<?php
require '../conection.php'; // Conexión a la base de datos
require '../sesion.php';
verificarAutenticacion();

function obtenerCargosDesdeSQL($pdo) {
    try {
        $stmt = $pdo->query("SELECT Cargo FROM usuarios");
        $cargos = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return $cargos;
    } catch (PDOException $e) {

        error_log("Error al obtener los cargos: " . $e->getMessage());
        die("Error al obtener los cargos. Intenta nuevamente más tarde.");
    }
}
function obtenerUsuariosPorCargos($pdo, $cargosFiltrados) {
    try {
        $placeholders = implode(',', array_fill(0, '?'));

        $sql = "SELECT nombre_u FROM usuarios WHERE Cargo IN ($placeholders)";
        $stmt = $pdo->prepare($sql);

        $stmt->execute($cargosFiltrados);

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        error_log("Error al obtener los usuarios por cargos: " . $e->getMessage());
        die("Error al obtener los usuarios. Intenta nuevamente más tarde.");
    }
}
function obtenerUsuariosPorCargos2($pdo, $cargosFiltrados) {
    try {
        $placeholders = implode(',', array_fill(0, count($cargosFiltrados), '?'));

        $sql = "SELECT nombre_u FROM usuarios WHERE Cargo IN ($placeholders)";
        $stmt = $pdo->prepare($sql);

        $stmt->execute($cargosFiltrados);

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        error_log("Error al obtener los usuarios por cargos: " . $e->getMessage());
        die("Error al obtener los usuarios. Intenta nuevamente más tarde.");
    }
}
function obtenerUsuariosPorCargos3($pdoUsuarios, $cargosFiltrados) {
    try {
        $placeholders = implode(',', array_fill(0, count($cargosFiltrados), '?'));

        $sql = "SELECT nombre_u FROM usuarios WHERE Cargo IN ($placeholders)";
        $stmt = $pdoUsuarios->prepare($sql);

        $stmt->execute($cargosFiltrados);

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        error_log("Error al obtener los usuarios por cargos: " . $e->getMessage());
        die("Error al obtener los usuarios. Intenta nuevamente más tarde.");
    }
}
$cargosFiltrados = ["Lider de almacen", "Auxiliar de almacen","Almacenista"];
$usuarios2 = obtenerUsuariosPorCargos2($pdoUsuarios, $cargosFiltrados);
$usuarios3 = obtenerUsuariosPorCargos3($pdoUsuarios, $cargosFiltrados);
$cargos = obtenerCargosDesdeSQL($pdoUsuarios);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/formulario2.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@200..700&family=Smooch+Sans:wght@100..900&display=swap" rel="stylesheet">
    <title>Molienda</title>
    <h1 class="titulo_principal">Turno 1     <a class="botonvolver" href="molienda.php">Volver</a></h1>
    <script src="./formulario02.js" defer></script>
</head>
<body class="cuerpo" >
    <form action="./formulario02.php" method="post" id="formulario2" class="formulario2">
    <div class="contenedor1">
        <div class="responsable">    
            <label for="responsable">Nombre</label>
            <input type="text" id="responsable" name="responsable" 
            value="<?php echo htmlspecialchars($_SESSION['nombre'], ENT_QUOTES, 'UTF-8'); ?>" 
            readonly>
        </div>
        <div class="fechayhora">
            <label for="fecha">Fecha de Registro</label>
            <input type="date" name="fecha" id="fecha" >
            <label for="hora">Hora del registro</label>
            <input type="time" name="hora" id="hora">
        </div>   
        <div class="contenedorfirma">
            <label class="labelfir" for="canvasfirma1">Firma Turno</label>
            <canvas class="campofirm" id="canvasfirma1" width="400" height="200" style="border: 1px solid #000;"></canvas>
            <button class="botonext" type="button" id="limpiarFirma1">Limpiar</button>
            <button class="botonext" type="button" id="guardarFirma1">Guardar Firma</button>
            <input type="hidden" name="firma_turn1" id="firma_turn1">
        </div> 
        <div class="responsablesint">  
        <label onclick="addResponsableField()" class="labelmenres" for="">Responsables De La Intervencion Max 5 (+)</label>
        <div id="responsablesContainer"></div>
        </div>
        <div class="almacenista">
            <label for="">Almacenista</label>
            <select name="responsableint" id="responsableint" required>
            <?php if (!empty($usuarios3)): ?>
            <?php foreach ($usuarios3 as $usuarios3): ?>
                <option value="<?php echo htmlspecialchars($usuarios3, ENT_QUOTES, 'UTF-8'); ?>">
                    <?php echo htmlspecialchars($usuarios3, ENT_QUOTES, 'UTF-8'); ?>
                </option>
                <?php endforeach; ?>
                <?php else: ?>
                <option value="">No hay Usuarios disponibles</option>
                <?php endif; ?>
                <option value="NULL">Ninguno.</option>
            </select> 
        </div>
    </div>        
<div class="control">
        <h2 class="titulo_principal">Seccion De Control</h2>
        <h3 class="subtitulo">Harinas</h3>
        <div>
            <label class="labelmen">
                <input type="checkbox" id="toggleResiduos1" onclick="toggleResiduosMenu1()">
                 Harina de trigo Extrapan x50 Kg
            </label>
        </div>

        <!-- Contenedor EXTRAPANX50 -->
        <div class="contenedormen" id="residuosMenu1" style="display: none; margin-top: 10px;">
            <label class="" for="valor1">Ingrese el valor exacto</label>
            <input type="number" id="valor1" name="valor1" placeholder="# Bultos">
            <label for="lote1">Ingrese el Lote</label>
            <input type="text" id="lote1" name="lote1" placeholder="Ejemplo: 211124B">     
            <div class="input-group">
                <label>Resultado:</label>
                <div class="result" id="resultado1">0</div>
                    <div>
                        <label class="labelmen">
                        <input type="checkbox" id="togglextrapanmenx50" onclick="togglextrapanx50()">
                        Click Aqui si necesita ingresar lotes 
                        </label>
                    </div>
                        <div class="contenedormini" id="menuharina" style="display: none; margin-top: 10px;">
                        <div>
                            <h3>lote Addicional 1</h3>
                                <label for="">Ingrese valor exacto</label>
                                <input name="extrapanx50valor1" type="text">
                                <label for="">Ingrese el lote</label>
                                <input name="extrapanx50lote1" type="text">
                        </div>
                    </div>
            </div>
        </div>
        <div>
            <label class="labelmen">
            <input type="checkbox" id="toggleResiduos2" onclick="toggleResiduosMenu2()">
            Extrapan x25
            </label>
        </div> 

    <!-- Contenedor EXTRAPANX25 -->
        <div class="contenedormen" id="residuosMenu2" style="display: none; margin-top: 10px;">
                    <label for="valor2">Ingrese el valor exacto</label>
                    <input type="number" id="valor2" name="valor2" placeholder="# Bultos">
                    <label for="lote2">Ingrese el Lote</label>
                    <input type="text" id="lote2" name="lote2" placeholder="Ejemplo: 211124B">
                    <label>Resultado:</label>
                    <div class="result" id="resultado2">
                    </div>
                    <div>
                        <label class="labelmen">
                        <input type="checkbox" id="togglextrapanmenx25" onclick="togglextrapanx25()">
                        Click Aqui si necesita ingresar lotes extras
                        </label>
                    </div>
                    <div class="contenedormini" id="menuharina2" style="display: none; margin-top: 10px;">
                        <div>
                            <h3>lote Addicional 1</h3>
                                <label for="">Ingrese valor exacto</label>
                                <input name="extrapanx25valor1" type="text">
                                <label for="">Ingrese el lote</label>
                                <input name="extrapanx25lote1" type="text">
                        </div>
                    </div>
        </div>

    <div>
        <label class="labelmen">
            <input type="checkbox" id="toggleResiduos3" onclick="toggleResiduosMenu3()">
            Extrapan x10
        </label>
    </div>

    <!-- Contenedor EXTRAPANX10 -->
    <div  class="contenedormen" id="residuosMenu3" style="display: none; margin-top: 10px;">
        <label for="valor3">Ingrese el valor exacto</label>
        <input type="number" id="valor3" name="valor3" placeholder="# Bultos">
        <label for="lote3">Ingrese el Lote</label>
        <input type="text" id="lote3" name="lote3" placeholder="Ejemplo: 211124B">
        <label>Resultado:</label>
        <div class="result" id="resultado3">0</div>
                <div>
                        <label class="labelmen">
                        <input type="checkbox" id="togglextrapanx10men" onclick="togglextrapanx10()">
                        Click Aqui si necesita ingresar lotes 
                        </label>
                    </div>
                        <div class="contenedormini" id="menuharina3" style="display: none; margin-top: 10px;">
                        <div>
                            <h3>lote Addicional 1</h3>
                                <label for="">Ingrese valor exacto</label>
                                <input name="extrapanx10valor1" type="text">
                                <label for="">Ingrese el lote</label>
                                <input name="extrapanx10lote1" type="text">
                        </div>
                </div>
    </div>
    <div>
        <label class="labelmen">
            <input type="checkbox" id="toggleResiduos4" onclick="toggleResiduosMenu4()">
            Harina de Trigo Artesanal x50 Kg
        </label>
    </div>

    <!-- Contenedor ARTESANAL X50 -->
    <div class="contenedormen" id="residuosMenu4" style="display: none; margin-top: 10px;">
        <label for="valor4">Ingrese el valor exacto</label>
        <input type="number" id="valor4" name="valor4" placeholder="# Bultos">
        <label for="lote3">Ingrese el Lote</label>
        <input type="text" id="lote4" name="lote4" placeholder="Ejemplo: 211124B">
        <label>Resultado:</label>
        <div class="result" id="resultado4">0</div>
        <div>
                        <label class="labelmen">
                        <input type="checkbox" id="toggleartesanalx50men" onclick="toggleartesanalx50()">
                        Click Aqui si necesita ingresar lotes 
                        </label>
                    </div>
                        <div class="contenedormini" id="menuharina4" style="display: none; margin-top: 10px;">
                        <div>
                            <h3>lote Addicional 1</h3>
                                <label for="">Ingrese valor exacto</label>
                                <input name="artex50valor1" type="text">
                                <label for="">Ingrese el lote</label>
                                <input name="artex50lote1" type="text">
                        </div>
        </div>
                </div>

    <div>
        <label class="labelmen">
            <input type="checkbox" id="toggleResiduos5" onclick="toggleResiduosMenu5()">
            Artesanal x25
        </label>
    </div>

    <!-- Contenedor ARTESANAL X25 -->
    <div class="contenedormen" id="residuosMenu5" style="display: none; margin-top: 10px;">
        <label for="valor5">Ingrese el valor exacto</label>
        <input type="number" id="valor5" name="valor5" placeholder="# Bultos">
        <label for="lote5">Ingrese el Lote</label>
        <input type="text" id="lote5" name="lote5" placeholder="Ejemplo: 211124B">
        <label>Resultado:</label>
        <div class="result" id="resultado5">0</div>
        <div>
                        <label class="labelmen">
                        <input type="checkbox" id="toggleartesanalx25men" onclick="toggleartesanalx25()">
                        Click Aqui si necesita ingresar lotes 
                        </label>
                    </div>
                        <div class="contenedormini" id="menuharina5" style="display: none; margin-top: 10px;">
                        <div>
                            <h3>lote Addicional 1</h3>
                                <label for="">Ingrese valor exacto</label>
                                <input name="artex25valor1" type="text">
                                <label for="">Ingrese el lote</label>
                                <input name="artex25lote1" type="text">
                        </div>
        </div>
        </div>

    <div>
        <label class="labelmen">
            <input type="checkbox" id="toggleResiduos6" onclick="toggleResiduosMenu6()">
            Fuerte x25
        </label>
    </div>

    <!-- Contenedor FUERTEX25 -->
    <div class="contenedormen" id="residuosMenu6" style="display: none; margin-top: 10px;">
        <label for="valor6">Ingrese el valor exacto</label>
        <input type="number" id="valor6" name="valor6" placeholder="# Bultos">
        <label for="lote6">Ingrese el Lote</label>
        <input type="text" id="lote6" name="lote6" placeholder="Ejemplo: 211124B">
        <label>Resultado:</label>
        <div class="result" id="resultado6">0</div>
        <div>
                        <label class="labelmen">
                        <input type="checkbox" id="togglefuertex25men" onclick="togglefuertex25()">
                        Click Aqui si necesita ingresar lotes 
                        </label>
                    </div>
                        <div class="contenedormini" id="menuharina6" style="display: none; margin-top: 10px;">
                        <div>
                            <h3>lote Addicional 1</h3>
                                <label for="">Ingrese valor exacto</label>
                                <input name="fuertex25valor1" type="text">
                                <label for="">Ingrese el lote</label>
                                <input name="fuertex25lote1" type="text">
                        </div>
        </div>
        </div>

    
    <div>
        <label class="labelmen">
            <input type="checkbox" id="toggleResiduos7" onclick="toggleResiduosMenu7()">
            Natural x50
        </label>
    </div>

    <!-- Contenedor NATURAL X50 -->
    <div class="contenedormen" id="residuosMenu7" style="display: none; margin-top: 10px;">
        <label for="valor7">Ingrese el valor exacto</label>
        <input type="number" id="valor7" name="valor7" placeholder="# Bultos">
        <label for="lote7">Ingrese el Lote</label>
        <input type="text" id="lote7" name="lote7" placeholder="Ejemplo: 211124B">
        <label>Resultado:</label>
        <div class="result" id="resultado7">0</div>
        <div>
                        <label class="labelmen">
                        <input type="checkbox" id="togglenaturalx50men" onclick="togglenaturalx50()">
                        Click Aqui si necesita ingresar lotes 
                        </label>
                    </div>
                        <div class="contenedormini" id="menuharina7" style="display: none; margin-top: 10px;">
                        <div>
                            <h3>lote Addicional 1</h3>
                                <label for="">Ingrese valor exacto</label>
                                <input name="natux50valor1" type="text">
                                <label for="">Ingrese el lote</label>
                                <input name="natux50lote1" type="text">
                        </div>
        </div>
        </div>




    <div>
        <label class="labelmen">
            <input type="checkbox" id="toggleResiduos8" onclick="toggleResiduosMenu8()">
            Especial x50
        </label>
    </div>

    <!-- Contenedor ESPECIAL X50 -->
    <div class="contenedormen" id="residuosMenu8" style="display: none; margin-top: 10px;">
        <label for="valor8">Ingrese el valor exacto</label>
        <input type="number" id="valor8" name="valor8" placeholder="# Bultos">
        <label for="lote8">Ingrese el Lote</label>
        <input type="text" id="lote8" name="lote8" placeholder="Ejemplo: 211124B">
        <label>Resultado:</label>
        <div class="result" id="resultado8">0</div>
        <div>
                        <label class="labelmen">
                        <input type="checkbox" id="togglespecialx50men" onclick="togglespecialx50()">
                        Click Aqui si necesita ingresar lotes 
                        </label>
                    </div>
                        <div class="contenedormini" id="menuharina8" style="display: none; margin-top: 10px;">
                        <div>
                            <h3>lote Addicional 1</h3>
                                <label for="expecialx50valor1">Ingrese valor exacto</label>
                                <input name="expecialx50valor1" type="text">
                                <label for="">Ingrese el lote</label>
                                <input name="expecialx50lote1" type="text">
                        </div>
                    </div>
        </div>

    <div>
        <label class="labelmen">
            <input type="checkbox" id="toggleResiduos9" onclick="toggleResiduosMenu9()">
            Especial x25
        </label>
    </div>

    <!-- Contenedor ESPECIAL X25 -->
    <div class="contenedormen" id="residuosMenu9" style="display: none; margin-top: 10px;">
        <label for="valor9">Ingrese el valor exacto</label>
        <input type="number" id="valor9" name="valor9" placeholder="# Bultos">
        <label for="lote9">Ingrese el Lote</label>
        <input type="text" id="lote9" name="lote9" placeholder="Ejemplo: 211124B">
        <label>Resultado:</label>
        <div class="result" id="resultado9">0</div>
        <div>
                        <label class="labelmen">
                        <input type="checkbox" id="togglespecialx25men" onclick="togglespecialx25()">
                        Click Aqui si necesita ingresar lotes 
                        </label>
                    </div>
                        <div class="contenedormini" id="menuharina9" style="display: none; margin-top: 10px;">
                        <div>
                            <h3>lote Addicional 1</h3>
                                <label for="">Ingrese valor exacto</label>
                                <input name="espx25valor1" type="text">
                                <label for="">Ingrese el lote</label>
                                <input name="espx25lote1" type="text">
                        </div>
        </div>
        </div>
    <div>
        <label class="labelmen">
            <input type="checkbox" id="toggleResiduos10" onclick="toggleResiduosMenu10()">
            Exclusiva x50
        </label>
    </div>

    <!-- Contenedor EXCLUSIVA X50 -->
    <div class="contenedormen" id="residuosMenu10" style="display: none; margin-top: 10px;">
        <label for="valor10">Ingrese el valor exacto</label>
        <input type="number" id="valor10" name="valor10" placeholder="# Bultos">
        <label for="lote10">Ingrese el Lote</label>
        <input type="text" id="lote10" name="lote10" placeholder="Ejemplo: 211124B">
        <label>Resultado:</label>
        <div class="result" id="resultado10">0</div>
        <div>
                        <label class="labelmen">
                        <input type="checkbox" id="togglexclusivax50men" onclick="togglexclusivax50()">
                        Click Aqui si necesita ingresar lotes 
                        </label>
                    </div>
                        <div class="contenedormini" id="menuharina10" style="display: none; margin-top: 10px;">
                        <div>
                            <h3>lote Addicional 1</h3>
                                <label for="">Ingrese valor exacto</label>
                                <input name="exc50valor1" type="text">
                                <label for="">Ingrese el lote</label>
                                <input name="exc50lote1" type="text">
                        </div>
        </div>
        </div>


    <div>
        <label class="labelmen">
            <input type="checkbox" id="toggleResiduos11" onclick="toggleResiduosMenu11()">
            Desarrollo x1
        </label>
    </div>

    <!-- Contenedor Desarrollox1 -->
    <div class="contenedormen" id="residuosMenu11" style="display: none; margin-top: 10px;">
        <label for="valor11">Ingrese el valor exacto</label>
        <input type="number" id="valor11" name="valor11" placeholder="Ingrese en kg">
        <label for="lote11">Ingrese el Lote</label>
        <input type="text" id="lote11" name="lote11" placeholder="Ejemplo: 211124B">
        <label>Resultado:</label>
        <div class="result" id="resultado11">0</div>
        <div>
                        <label class="labelmen">
                        <input type="checkbox" id="toggledesarrollox1men" onclick="toggledesarrollox1()">
                        Click Aqui si necesita ingresar lotes 
                        </label>
                    </div>
                        <div class="contenedormini" id="menuharina11" style="display: none; margin-top: 10px;">
                        <div>
                            <h3>lote Addicional 1</h3>
                                <label for="">Ingrese valor exacto</label>
                                <input name="desarrollovalor1" type="text">
                                <label for="">Ingrese el lote</label>
                                <input name="desarrollolote1" type="text">
                        </div>
        </div>
        </div>
    <div>
        <label class="labelmen">
            <input type="checkbox" id="toggleResiduos12" onclick="toggleResiduosMenu12()">
            Harina Alta Proteina
        </label>
    </div>

    <!-- HARIAALTAPROTEINA -->
    <div class="contenedormen" id="residuosMenu12" style="display: none; margin-top: 10px;">
        <label for="valor12">Ingrese el valor exacto</label>
        <input type="number" id="valor12" name="valor12" placeholder="Ingrese en kg">
        <label for="lote12">Ingrese el Lote</label>
        <input type="text" id="lote12" name="lote12" placeholder="Ejemplo: 211124B">
        <label>Resultado:</label>
        <div class="result" id="resultado12">0</div>
        <div>
                        <label class="labelmen">
                        <input type="checkbox" id="togglaltapromen" onclick="togglaltapro()">
                        Click Aqui si necesita ingresar lotes 
                        </label>
                    </div>
                        <div class="contenedormini" id="menuharina12" style="display: none; margin-top: 10px;">
                        <div>
                            <h3>lote Addicional 1</h3>
                                <label for="">Ingrese valor exacto</label>
                                <input name="altavalor1" type="text">
                                <label for="">Ingrese el lote</label>
                                <input name="altalote1" type="text">
                        </div>
        </div>
        </div>
    <div>
        <label class="labelmen">
            <input type="checkbox" id="toggleResiduos13" onclick="toggleResiduosMenu13()">
            Harina Media Proteina
        </label>
    </div>

    <!-- HARIAMEDIAPROTEINA -->
    <div class="contenedormen" id="residuosMenu13" style="display: none; margin-top: 10px;">
        <label for="valor13">Ingrese el valor exacto</label>
        <input type="number" id="valor13" name="valor13" placeholder="Ingrese en kg">
        <label for="lote13">Ingrese el Lote</label>
        <input type="text" id="lote13" name="lote13" placeholder="Ejemplo: 211124B">
        <label>Resultado:</label>
        <div class="result" id="resultado13">0</div>
        <div>
                        <label class="labelmen">
                        <input type="checkbox" id="togglemediapromen" onclick="togglemediapro()">
                        Click Aqui si necesita ingresar lotes 
                        </label>
                    </div>
                        <div class="contenedormini" id="menuharina13" style="display: none; margin-top: 10px;">
                        <div>
                            <h3>lote Addicional 1</h3>
                                <label for="">Ingrese valor exacto</label>
                                <input name="mediavalor1" type="text">
                                <label for="">Ingrese el lote</label>
                                <input name="medialote1" type="text">
                        </div>
        </div>
        </div>
    <div>
        <label class="labelmen">
            <input type="checkbox" id="toggleResiduos14" onclick="toggleResiduosMenu14()">
            Harina Baja Proteina
        </label>
    </div>

    <!-- HARIABAJAPROTEINA -->
    <div class="contenedormen" id="residuosMenu14" style="display: none; margin-top: 10px;">
        <label for="valor14">Ingrese el valor exacto</label>
        <input type="number" id="valor14" name="valor14" placeholder="Ingrese en kg">
        <label for="lote14">Ingrese el Lote</label>
        <input type="text" id="lote14" name="lote14" placeholder="Ejemplo: 211124B">
        <label>Resultado:</label>
        <div class="result" id="resultado14">0</div>
        <div>
                        <label class="labelmen">
                        <input type="checkbox" id="togglebajapromen" onclick="togglebajapro()">
                        Click Aqui si necesita ingresar lotes 
                        </label>
                    </div>
                        <div class="contenedormini" id="menuharina14" style="display: none; margin-top: 10px;">
                        <div>
                            <h3>lote Addicional 1</h3>
                                <label for="">Ingrese valor exacto</label>
                                <input name="bajavalor1" type="text">
                                <label for="">Ingrese el lote</label>
                                <input name="bajalote1" type="text">
                        </div>
        </div>
        </div>

    <div>
        <label class="labelmen">
            <input type="checkbox" id="toggleResiduos15" onclick="toggleResiduosMenu15()">
            Artesanal
        </label>
    </div>

    <!-- ARTESANAL -->
    <div class="contenedormen" id="residuosMenu15" style="display: none; margin-top: 10px;">
        <label for="valor15">Ingrese el valor exacto</label>
        <input type="number" id="valor15" name="valor15" placeholder="Ingrese en kg">
        <label for="lote15">Ingrese el Lote</label>
        <input type="text" id="lote15" name="lote15" placeholder="Ejemplo: 211124B">
        <label>Resultado:</label>
        <div class="result" id="resultado15">0</div>
        <div>
                        <label class="labelmen">
                        <input type="checkbox" id="toggleartesanalmen" onclick="toggleartesanal()">
                        Click Aqui si necesita ingresar lotes 
                        </label>
                    </div>
                        <div class="contenedormini" id="menuharina15" style="display: none; margin-top: 10px;">
                        <div>
                            <h3>lote Addicional 1</h3>
                                <label for="">Ingrese valor exacto</label>
                                <input name="valorartesanal1" type="text">
                                <label for="">Ingrese el lote</label>
                                <input name="loteartesanal1" type="text">
                        </div>
                        </div>
                        </div>
                        <div>
        <label class="labelmen">
            <input type="checkbox" id="toggleResiduos16" onclick="toggleResiduosMenu16()">
                    Fuerte Exportacion
        </label>
    </div>
                    <!-- FUERTE EXPORTACION -->
                    <div class="contenedormen" id="residuosMenu16" style="display: none; margin-top: 10px;">
        <label for="valor16">Ingrese el valor exacto</label>
        <input type="number" id="valor16" name="valor16" placeholder="Ingrese en kg">
        <label for="lote16">Ingrese el Lote</label>
        <input type="text" id="lote16" name="lote16" placeholder="Ejemplo: 211124B">
        <label>Resultado:</label>
        <div class="result" id="resultado15">0</div>
        <div>
                        <label class="labelmen">
                        <input type="checkbox" id="togglefuertexpmen" onclick="togglefuerteexp()">
                        Click Aqui si necesita ingresar lotes 
                        </label>
                    </div>
                        <div class="contenedormini" id="menuharina16" style="display: none; margin-top: 10px;">
                        <div>
                            <h3>lote Addicional 1</h3>
                                <label for="">Ingrese valor exacto</label>
                                <input name="valorfuertexp1" type="text">
                                <label for="">Ingrese el lote</label>
                                <input name="lotefuertexp1" type="text">
                        </div>
                    </div>
                </div>                                    
        <!-- ZONA SUB PRODUCTO -->
         <h3 class="subtitulo">Sub productos</h3>
    <div class="residuo">
        <label class="resiudosec" for="residuoSelect1">Selecciona un tipo de Subproducto:</label>
            <select class="residuoselec" id="residuoSelect1" name="residuoSelect1" onchange="addResiduoFields1()">
                <option value="0000">-- Seleccionar --</option>
                <option value="Salvado x25">Sub Salvado x25</option>
                <option value="Salvado x30">Sub Salvado x30</option>
                <option value="Mogolla x40">Sub Mogolla x40</option>
                <option value="Segunda x50">Sub Segunda x50</option>
                <option value="Semola Fina x25">Semola Fina x25</option>
                <option value="Semola Gruesa x25">Semola Gruesa x25</option>
                <option value="Germen x25">Germen de Trigo x25</option>
                <option value="Granza">Sub Granza</option>
                <option value="Vitamina">Vitamina</option>
                <option value="Hilo">Hilo</option>
                <option value="Granza x50">Granza 50Kg</option>
            </select>
        <div>
            <div id="residuosContainer1" ></div>
        </div>
    </div>
    <div class="harinaext">
        <label onclick="addHarinaField()" class="labelmen">Agregue Resultado extra (Click aqui)</label>
            <div id="harinaContainer"></div>
    </div>
        <!-- ZONA MATERIAL -->
    <h3 class="subtitulo">Materiales</h3>
    <div class="residuo">
        <label class="residuosec" for="materialSelect1">Selecciona Material</label>
        <select class="residuoselec"  id="materialSelect1" onchange="addmaterialFields1()">
            <option value="">--Seleccionar</option>
            <option value="Empaque ExtraPan x50">Empaque ExtraPan (60x96) x50</option>
            <option value="Empaque ExtraPan x25">Empaque ExtraPan x25</option>
            <option value="Empaque ExtraPan x10">Empaque ExtraPan x10</option>
            <option value="Empaque Galeras Rojo x50">Empaque Galeras letra Roja (60x100) x50</option>
            <option value="Empaque Galeras Verde x50">Empaque Galeras Verde x50</option>
            <option value="Empaque Galeras Cafe x50">Empaque Galeras Cafe x50</option>
            <option value="Empaque Galeras Azul x50">Empaque Galeras Azul x50</option>
            <option value="Empaque Galeras Naranja x25">Empaque Galeras Naranja x25</option>
            <option value="Empaque Galeras KRAFT x25">Empaque Galeras KRAFT x25</option>
            <option value="Empaque Multi Beige x25">Empaque Multi Beige (50x80) x25</option>
            <option value="Empaque Galeras MOG x40">Empaque Galeras Mogolla (60x115) x40</option>
            <option value="Empaque Galeras Sal x25">Empaque Galeras Salvado (60x98) x25</option>
            <option value="Empaque Galeras Sal x30">Empaque Galeras Sal x30</option>
            <option value="Empaque Galeras Seg x50">Empaque Galeras Seg x50</option>
            <option value="Empaque Fuerte Exp">Empaque Fuerte de Exportacion</option>
            <option value="Vitaminamat">Vitamina</option>
            <option value="Mejorante Extrapan">Mejorante Extrapan</option>
            <option value="Mejorante Artesanal">Mejorante Artesanal</option>
            <option value="Hilo Blanco">Hilo Poliester Blanco</option>
            <option value="Hilo Verde">Hilo Verde</option>
            <option value="Hilo Naranja">Hilo Naranja</option>             
            <option value="Empaque Blanco x50">Empaque Blanco (60x106) x50</option>
        </select>
        <div>
            <div id="materialContainer1"></div>
        </div>
    </div>
        <div>
            <label for="materialSelect2">Agregar Un Material (Max 3)</label>
            <select  id="materialSelect2" onchange="addmaterialFields2()">
            <option value="">--Generar--</option>
            <option value="nuevo">nuevo</option>
            </select>
        </div>
        <div id="materialContainer2"></div>
        <div>
            <label for="enviar"></label>
            <input class="labelmen" type="submit">
        </div>
    </form>
</div>
</div>
</div>


</body>
<script>
        //FUNCION PARA MOSTRAR RESULTADOS 
            // Obtener los elementos
    // Función genérica para actualizar resultados
function actualizarResultado(inputId, resultId, multiplier = 50) {
    const input = document.getElementById(inputId);
    const result = document.getElementById(resultId);

    if (input && result) {
        const valor = parseFloat(input.value) || 0; // Si está vacío, toma 0
        const resultado = valor * multiplier; // Multiplica el valor
        result.textContent = resultado.toFixed(2); // Formato con 2 decimales
    }
}

// Agregar eventos dinámicamente
function agregarEventos(inputs) {
    inputs.forEach((input) => {
        const inputId = input.inputId;
        const resultId = input.resultId;
        const multiplier = input.multiplier || 50; // Valor por defecto

        const inputElement = document.getElementById(inputId);
        if (inputElement) {
            inputElement.addEventListener('input', () => {
                actualizarResultado(inputId, resultId, multiplier);
            });
        }
    });
}

// Lista de todos los inputs y resultados
const inputs = [
    { inputId: 'valor1', resultId: 'resultado1', multiplier: 50 },
    { inputId: 'valor2', resultId: 'resultado2', multiplier: 25 },
    { inputId: 'valor3', resultId: 'resultado3', multiplier: 10 },
    { inputId: 'valor4', resultId: 'resultado4', multiplier: 50 },
    { inputId: 'valor5', resultId: 'resultado5', multiplier: 25 },
    { inputId: 'valor6', resultId: 'resultado6', multiplier: 25 },
    { inputId: 'valor7', resultId: 'resultado7', multiplier: 50 },
    { inputId: 'valor8', resultId: 'resultado8', multiplier: 50 },
    { inputId: 'valor9', resultId: 'resultado9', multiplier: 25 },
    { inputId: 'valor10', resultId: 'resultado10', multiplier: 50 },
    { inputId: 'valor11', resultId: 'resultado11', multiplier: 1 },
    { inputId: 'valor12', resultId: 'resultado12', multiplier: 1 },
    { inputId: 'valor13', resultId: 'resultado13', multiplier: 1 },
    { inputId: 'valor14', resultId: 'resultado14', multiplier: 1 },
    { inputId: 'valor15', resultId: 'resultado15', multiplier: 1 },
    { inputId: 'valor16', resultId: 'resultado16', multiplier: 1 },
    { inputId: 'valor17', resultId: 'resultado17', multiplier: 1 },
];

// Inicializar eventos para los inputs
agregarEventos(inputs);




        let materialCount = 0;
        let residuoCount = 0;

        // Función para mostrar u ocultar el menú
        function toggleResiduosMenu1() {
            const checkbox = document.getElementById('toggleResiduos1');
            const menu = document.getElementById('residuosMenu1');
            menu.style.display = checkbox.checked ? 'block' : 'none';
        }
        //HARINAS EXTRAS MENUS
        function togglextrapanx50(){
            const checkbox = document.getElementById('togglextrapanmenx50');
            const menu = document.getElementById('menuharina');
            menu.style.display = checkbox.checked ? 'block' : 'none'; 
        }
        function togglextrapanx25(){
            const checkbox = document.getElementById('togglextrapanmenx25');
            const menu = document.getElementById('menuharina2');
            menu.style.display = checkbox.checked ? 'block' : 'none'; 
        }
        function togglextrapanx10(){
            const checkbox = document.getElementById('togglextrapanx10men');
            const menu = document.getElementById('menuharina3');
            menu.style.display = checkbox.checked ? 'block' : 'none'; 
        }
        function toggleartesanalx50(){
            const checkbox = document.getElementById('toggleartesanalx50men');
            const menu = document.getElementById('menuharina4');
            menu.style.display = checkbox.checked ? 'block' : 'none'; 
        }
        function toggleartesanalx25(){
            const checkbox = document.getElementById('toggleartesanalx25men');
            const menu = document.getElementById('menuharina5');
            menu.style.display = checkbox.checked ? 'block' : 'none'; 
        }
        function togglefuertex25(){
            const checkbox = document.getElementById('togglefuertex25men');
            const menu = document.getElementById('menuharina6');
            menu.style.display = checkbox.checked ? 'block' : 'none'; 
        }
        function togglenaturalx50(){
            const checkbox = document.getElementById('togglenaturalx50men');
            const menu = document.getElementById('menuharina7');
            menu.style.display = checkbox.checked ? 'block' : 'none'; 
        }
        function togglespecialx50(){
            const checkbox = document.getElementById('togglespecialx50men');
            const menu = document.getElementById('menuharina8');
            menu.style.display = checkbox.checked ? 'block' : 'none'; 
        }
        function togglespecialx25(){
            const checkbox = document.getElementById('togglespecialx25men');
            const menu = document.getElementById('menuharina9');
            menu.style.display = checkbox.checked ? 'block' : 'none'; 
        }
        function togglexclusivax50(){
            const checkbox = document.getElementById('togglexclusivax50men');
            const menu = document.getElementById('menuharina10');
            menu.style.display = checkbox.checked ? 'block' : 'none'; 
        }
        function toggledesarrollox1(){
            const checkbox = document.getElementById('toggledesarrollox1men');
            const menu = document.getElementById('menuharina11');
            menu.style.display = checkbox.checked ? 'block' : 'none'; 
        }
        function togglaltapro(){
            const checkbox = document.getElementById('togglaltapromen');
            const menu = document.getElementById('menuharina12');
            menu.style.display = checkbox.checked ? 'block' : 'none'; 
        }
        function togglemediapro(){
            const checkbox = document.getElementById('togglemediapromen');
            const menu = document.getElementById('menuharina13');
            menu.style.display = checkbox.checked ? 'block' : 'none'; 
        }
        function togglebajapro(){
            const checkbox = document.getElementById('togglebajapromen');
            const menu = document.getElementById('menuharina14');
            menu.style.display = checkbox.checked ? 'block' : 'none'; 
        }
        function toggleartesanal(){
            const checkbox = document.getElementById('toggleartesanalmen');
            const menu = document.getElementById('menuharina15');
            menu.style.display = checkbox.checked ? 'block' : 'none'; 
        }
        function togglefuerteexp(){
            const checkbox = document.getElementById('togglefuertexpmen');
            const menu = document.getElementById('menuharina16');
            menu.style.display = checkbox.checked ? 'block' : 'none'; 
        }
        function toggleartesanalx10(){
            const checkbox = document.getElementById('toggleartesanalx10men');
            const menu = document.getElementById('menuharina17');
            menu.style.display = checkbox.checked ? 'block' : 'none'; 
        }
        function toggleartesanal5und(){
            const checkbox = document.getElementById('toggleartesanal5undmen');
            const menu = document.getElementById('menuharina18');
            menu.style.display = checkbox.checked ? 'block' : 'none'; 
        }
        function toggleharinanax2_5(){
            const checkbox = document.getElementById('toggleharinanax2_5men');
            const menu = document.getElementById('menuharina19');
            menu.style.display = checkbox.checked ? 'block' : 'none'; 
        }
        function toggleharinanax1(){
            const checkbox = document.getElementById('toggleharinanax1men');
            const menu = document.getElementById('menuharina20');
            menu.style.display = checkbox.checked ? 'block' : 'none'; 
        }
        function toggleharinanaxLB(){
            const checkbox = document.getElementById('toggleharinanaxLBmen');
            const menu = document.getElementById('menuharina21');
            menu.style.display = checkbox.checked ? 'block' : 'none'; 
        }
        function toggleharinaint(){
            const checkbox = document.getElementById('toggleharinaintmen');
            const menu = document.getElementById('menuharina22');
            menu.style.display = checkbox.checked ? 'block' : 'none'; 
        }
        function toggleharinaNRÑ(){
            const checkbox = document.getElementById('toggleharinaNRÑM');
            const menu = document.getElementById('menuharina23');
            menu.style.display = checkbox.checked ? 'block' : 'none'; 
        }
        function toggleharinaNRÑ2(){
            const checkbox = document.getElementById('toggleharinaNRÑM2');
            const menu = document.getElementById('menuharina24');
            menu.style.display = checkbox.checked ? 'block' : 'none'; 
        }
        function toggleResiduosMenu2() {
            const checkbox = document.getElementById('toggleResiduos2');
            const menu = document.getElementById('residuosMenu2');
            menu.style.display = checkbox.checked ? 'block' : 'none';
        }


        // 3
        function toggleResiduosMenu3() {
            const checkbox = document.getElementById('toggleResiduos3');
            const menu = document.getElementById('residuosMenu3');
            menu.style.display = checkbox.checked ? 'block' : 'none';
        }


//4
function toggleResiduosMenu4() {
            const checkbox = document.getElementById('toggleResiduos4');
            const menu = document.getElementById('residuosMenu4');
            menu.style.display = checkbox.checked ? 'block' : 'none';
        }



//5
function toggleResiduosMenu5() {
            const checkbox = document.getElementById('toggleResiduos5');
            const menu = document.getElementById('residuosMenu5');
            menu.style.display = checkbox.checked ? 'block' : 'none';
        }


//6
function toggleResiduosMenu6() {
            const checkbox = document.getElementById('toggleResiduos6');
            const menu = document.getElementById('residuosMenu6');
            menu.style.display = checkbox.checked ? 'block' : 'none';
        }


//7
function toggleResiduosMenu7() {
            const checkbox = document.getElementById('toggleResiduos7');
            const menu = document.getElementById('residuosMenu7');
            menu.style.display = checkbox.checked ? 'block' : 'none';
        }

//8
        function toggleResiduosMenu8() {
            const checkbox = document.getElementById('toggleResiduos8');
            const menu = document.getElementById('residuosMenu8');
            menu.style.display = checkbox.checked ? 'block' : 'none';
        }


//9
function toggleResiduosMenu9() {
            const checkbox = document.getElementById('toggleResiduos9');
            const menu = document.getElementById('residuosMenu9');
            menu.style.display = checkbox.checked ? 'block' : 'none';
        }


//10
        function toggleResiduosMenu10() {
            const checkbox = document.getElementById('toggleResiduos10');
            const menu = document.getElementById('residuosMenu10');
            menu.style.display = checkbox.checked ? 'block' : 'none';
        }

//11
function toggleResiduosMenu11() {
            const checkbox = document.getElementById('toggleResiduos11');
            const menu = document.getElementById('residuosMenu11');
            menu.style.display = checkbox.checked ? 'block' : 'none';
        }

//12
function toggleResiduosMenu12() {
            const checkbox = document.getElementById('toggleResiduos12');
            const menu = document.getElementById('residuosMenu12');
            menu.style.display = checkbox.checked ? 'block' : 'none';
        }


//13
function toggleResiduosMenu13() {
            const checkbox = document.getElementById('toggleResiduos13');
            const menu = document.getElementById('residuosMenu13');
            menu.style.display = checkbox.checked ? 'block' : 'none';
        }


//14
function toggleResiduosMenu14() {
            const checkbox = document.getElementById('toggleResiduos14');
            const menu = document.getElementById('residuosMenu14');
            menu.style.display = checkbox.checked ? 'block' : 'none';
        }



//15
function toggleResiduosMenu15() {
            const checkbox = document.getElementById('toggleResiduos15');
            const menu = document.getElementById('residuosMenu15');
            menu.style.display = checkbox.checked ? 'block' : 'none';
        }
//16
function toggleResiduosMenu16() {
            const checkbox = document.getElementById('toggleResiduos16');
            const menu = document.getElementById('residuosMenu16');
            menu.style.display = checkbox.checked ? 'block' : 'none';
        }
        function toggleResiduosMenu17() {
            const checkbox = document.getElementById('toggleResiduos17');
            const menu = document.getElementById('residuosMenu17');
            menu.style.display = checkbox.checked ? 'block' : 'none';
        }
        function toggleResiduosMenu18() {
            const checkbox = document.getElementById('toggleResiduos18');
            const menu = document.getElementById('residuosMenu18');
            menu.style.display = checkbox.checked ? 'block' : 'none';
        }
        function toggleResiduosMenu19() {
            const checkbox = document.getElementById('toggleResiduos19');
            const menu = document.getElementById('residuosMenu19');
            menu.style.display = checkbox.checked ? 'block' : 'none';
        }
        function toggleResiduosMenu20() {
            const checkbox = document.getElementById('toggleResiduos20');
            const menu = document.getElementById('residuosMenu20');
            menu.style.display = checkbox.checked ? 'block' : 'none';
        }
        function toggleResiduosMenu21() {
            const checkbox = document.getElementById('toggleResiduos21');
            const menu = document.getElementById('residuosMenu21');
            menu.style.display = checkbox.checked ? 'block' : 'none';
        }
        function toggleResiduosMenu22() {
            const checkbox = document.getElementById('toggleResiduos22');
            const menu = document.getElementById('residuosMenu22');
            menu.style.display = checkbox.checked ? 'block' : 'none';
        }
        function toggleResiduosMenu23() {
            const checkbox = document.getElementById('toggleResiduos23');
            const menu = document.getElementById('residuosMenu23');
            menu.style.display = checkbox.checked ? 'block' : 'none';
        }
        function toggleResiduosMenu24() {
            const checkbox = document.getElementById('toggleResiduos24');
            const menu = document.getElementById('residuosMenu24');
            menu.style.display = checkbox.checked ? 'block' : 'none';
        }
//SECCION DE CONTAINERS EXTRAS
function addResiduoFields1() {
    const select = document.getElementById('residuoSelect1');
    const residuoType = select.value;

    if (residuoType) {
        const uniqueId = residuoType.replace(/\s+/g, '_'); // Generar un nombre único basado en el tipo

        // Verificar si ya existe un campo con este ID
        if (document.getElementById(`residuo-${uniqueId}`)) {
            alert('Este residuo ya está seleccionado.');
            return;
        }

        const container = document.getElementById('residuosContainer1');
        const fieldsDiv = document.createElement('div');
        fieldsDiv.className = 'residuo-fields';
        fieldsDiv.id = `residuo-${uniqueId}`;

        fieldsDiv.innerHTML = `
            <h3>Detalles del SubProducto (${residuoType})</h3>
            <label for="peso-${uniqueId}">Unidades (Bulto):</label>
            <input type="number" id="peso-${uniqueId}" name="peso-${uniqueId}" step="0.01" placeholder="# De Bultos">

            <label for="codigo-${uniqueId}">Ingrese el lote:</label>
            <input type="text" id="codigo-${uniqueId}" name="codigo-${uniqueId}" placeholder="Ejemplo: 211124B">

            <h3>Detalles del SubProducto Extra (${residuoType})</h3>
            <label for="peso-${uniqueId}">Unidades (Bulto):</label>
            <input type="number" id="peso-${uniqueId}" name="pesoext-${uniqueId}" step="0.01" placeholder="# De Bultos">

            <label for="codigo-${uniqueId}">Ingrese el lote:</label>
            <input type="text" id="codigo-${uniqueId}" name="codigoext-${uniqueId}" placeholder="Ejemplo: 211124B">

            <button type="button" onclick="removeResiduoFields('${uniqueId}')">Eliminar</button>
            
        `;
        //IDS
        //peso-Salvado_x25
        //peso-Mogolla_x40
        //peso-Segunda_x50
        //peso-Semola_Fina_x25
        //peso-Semola_Gruesa_x25
        //peso-Germen_x25
        //peso-Granza
        //peso-Vitamina
        //peso-Hilo
        //codigo-Salvado_x25
        //codigo-Mogolla_x40
        //codigo-Segunda_x50
        //codigo-Semola_Fina_x25
        //codigo-Semola_Gruesa_x25
        //codigo-Germen_x25
        //codigo-Granza
        //codigo-Vitamina
        //codigo-Hilo
        container.appendChild(fieldsDiv);
        select.value = ''; // Reiniciar el menú desplegable
    }
}

function addmaterialFields1() {
    const select = document.getElementById('materialSelect1');
    const materialType = select.value;

    if (materialType) {
        const uniqueId = materialType.replace(/\s+/g, '_'); // Generar un nombre único basado en el tipo

        // Verificar si ya existe un campo con este ID
        if (document.getElementById(`material-${uniqueId}`)) {
            alert('Este material ya está seleccionado.');
            return;
        }

        const container = document.getElementById('materialContainer1');
        const fieldsDiv = document.createElement('div');
        fieldsDiv.className = 'material-fields';
        fieldsDiv.id = `material-${uniqueId}`;

        fieldsDiv.innerHTML = `
            <h3>Detalles del Material (${materialType})</h3>
            <label for="codigo-${uniqueId}">Ingrese el lote:</label>
            <input type="text" id="codigo-${uniqueId}" name="codigo-${uniqueId}" placeholder="Ejemplo: 211124B">

            <button type="button" onclick="removematerialFields('${uniqueId}')">Eliminar</button>
        `;
        //IDS
        //codigo-Empaque_ExtraPan_x50
        //codigo-Empaque_ExtraPan_x25
        //codigo-Empaque_ExtraPan_x10
        //codigo-Empaque_Galeras_Rojo_x50
        //codigo-Empaque_Galeras_Verde_x50
        //codigo-Empaque_Galeras_Cafe_x50
        //codigo-Empaque_Galeras_Azul_x50
        //codigo-Empaque_Galeras_Naranja_x50
        //codigo-Empaque_Galeras_KRAFT_x25
        //codigo-Empaque_Multi_Beige_x25
        //codigo-Empaque_Galeras_MOG_x40
        //codigo-Empaque_Galeras_Sal_x25
        //codigo-Empaque_Galeras_Seg_x50
        //codigo-Vitamina
        //codigo-Mejorante_Extrapan
        //codigo-Mejorante_Artesanal
        //codigo-Hilo_Blanco
        //codigo-Hilo_Verde
        //codigo-Hilo_Naranja
        container.appendChild(fieldsDiv);
        select.value = ''; // Reiniciar el menú desplegable
    }
}
      
//NUEVO RESPONSABLE DE INTERVENCION
// Inicializa la variable para contar los responsables
let responsablecount = 0;
function addResponsableField() {
    const container = document.getElementById('responsablesContainer');
    responsablecount++; // Incrementar el contador

    // Crear un nuevo contenedor para el campo dinámico
    const fieldsDiv = document.createElement('div');
    fieldsDiv.className = 'responsables-fields';
    fieldsDiv.id = `responsables-${responsablecount}`;

    // Contenido dinámico
    fieldsDiv.innerHTML = `
        <label class="labelmin" for="nombre-${responsablecount}">Nombre del responsable:</label>
        <input type="text" id="nombre-${responsablecount}" name="nomresp-${responsablecount}">
        <button type="button" onclick="removeResponsableField('${fieldsDiv.id}')">Eliminar</button>
    `;

    // Añadir el nuevo campo al contenedor
    container.appendChild(fieldsDiv);
}
let harinacount = 0
function addHarinaField() {
    const container = document.getElementById('harinaContainer');
    harinacount++; // Incrementar el contador

    // Crear un nuevo contenedor para el campo dinámico
    const fieldsDiv = document.createElement('div');
    fieldsDiv.className = 'harina-fields';
    fieldsDiv.id = `harina-${harinacount}`;

    // Contenido dinámico
    fieldsDiv.innerHTML = `
        <label for="harina-${harinacount}">Nombre de la harina:</label>
        <input type="text" id="harina-${harinacount}" name="nomhari-${harinacount}">
        <label for="harina-${harinacount}"># Bultos:</label>
        <input type="text" id="harina-${harinacount}" name="bultos-${harinacount}">
        <label for="harina-${harinacount}">Lote:</label>
        <input type="text" id="harina-${harinacount}" name="lote-${harinacount}">
        <label for="valor-${harinacount}">valor x bulto:</label>
        <input type="text" id="valor-${harinacount}" name="valor-${harinacount}">
        <button type="button" onclick="removeResponsableField('${fieldsDiv.id}')">Eliminar</button>
    `;

    // Añadir el nuevo campo al contenedor
    container.appendChild(fieldsDiv);
}

function removeResponsableField(id) {
    const field = document.getElementById(id);
    if (field) {
        field.remove(); // Eliminar el campo dinámico
    }
}



        function addmaterialFields2() {
            const select = document.getElementById('materialSelect2');
            const materialType = select.value;

            if (materialType) {
                materialCount++;
                const container = document.getElementById('materialContainer2');

                // Crear contenedor para los campos de un residuo
                const fieldsDiv = document.createElement('div');
                fieldsDiv.className = 'material-fields';
                fieldsDiv.id = `material-${materialCount}`;

                fieldsDiv.innerHTML = `
                    <h3>Detalles del Material (${materialType})</h3>
                    <label for="codigo-${materialCount}">Ingrese el nombre:</label>
                    <input type="text" id="namegomat2-${materialCount}" name="nomgomat2-${materialCount}" placeholder="Ejemplo: Paquete x50">
                    <label for="codigo-${materialCount}">Ingrese el lote:</label>
                    <input type="text" id="codigomat2-${materialCount}" name="codigomat2-${materialCount}" placeholder="Ejemplo: 211124B">

                    <button type="button" onclick="removematerialFields(${materialCount})">Eliminar</button>
                `;

                container.appendChild(fieldsDiv);

                // Reiniciar el menú desplegable
                select.value = '';
            }
        }

lote12

        // Función para eliminar campos dinámicamente
        function removeResiduoFields(id) {
            const fieldsDiv = document.getElementById(`residuo-${id}`);
            fieldsDiv.remove();
        }
        function removematerialFields(id) {
            const fieldsDiv = document.getElementById(`material-${id}`);
            fieldsDiv.remove();
        }
        function removeResponsableField(uniqueId) {
    // Encontrar y eliminar el contenedor del campo específico
    const fieldToRemove = document.getElementById(uniqueId);
    if (fieldToRemove) {
        fieldToRemove.remove();
    }
}

        //FIRMAS

    // Inicializar el canvas de forma dinámica
    function inicializarCanvas(idCanvas, idLimpiarBtn, idGuardarBtn, idInputHidden) {
    const canvas = document.getElementById(idCanvas);
    const ctx = canvas.getContext('2d');
    const limpiarBtn = document.getElementById(idLimpiarBtn);
    const guardarBtn = document.getElementById(idGuardarBtn);
    const inputHidden = document.getElementById(idInputHidden);

    let dibujando = false;

    // Ajustar dimensiones dinámicamente para que sea responsivo
    function ajustarTamañoCanvas() {
        const rect = canvas.getBoundingClientRect();
        canvas.width = rect.width;
        canvas.height = rect.height;
        ctx.lineWidth = 2; // Configurar grosor del pincel
        ctx.lineCap = 'round'; // Configurar extremos del trazo
        ctx.strokeStyle = 'black'; // Configurar color del trazo
    }
    ajustarTamañoCanvas();
    window.addEventListener('resize', ajustarTamañoCanvas);

    function obtenerPosicion(e) {
        const rect = canvas.getBoundingClientRect();
        if (e.touches) {
            return {
                x: e.touches[0].clientX - rect.left,
                y: e.touches[0].clientY - rect.top
            };
        } else {
            return {
                x: e.clientX - rect.left,
                y: e.clientY - rect.top
            };
        }
    }

    function iniciarDibujo(e) {
        dibujando = true;
        const pos = obtenerPosicion(e);
        ctx.beginPath();
        ctx.moveTo(pos.x, pos.y);
    }

    function detenerDibujo() {
        dibujando = false;
        ctx.beginPath();
    }

    function dibujar(e) {
        if (!dibujando) return;
        const pos = obtenerPosicion(e);
        ctx.lineTo(pos.x, pos.y);
        ctx.stroke();
    }
    function isCanvasBlank(canvas) {
    const blank = document.createElement('canvas');
    blank.width = canvas.width;
    blank.height = canvas.height;
    return canvas.toDataURL() === blank.toDataURL();
    }
    document.addEventListener("DOMContentLoaded", function () {
    let canvas = document.getElementById("canvasfirma1");
    let ctx = canvas.getContext("2d");
    let isDrawing = false;
    let firmaGuardada = false; // Para verificar si se firmó

    // Evento para detectar dibujo
    canvas.addEventListener("mousedown", function () {
        isDrawing = true;
    });
    canvas.addEventListener("mouseup", function () {
        isDrawing = false;
        firmaGuardada = true; // Marca como firmado
    });
    document.querySelector("form").addEventListener("submit", function (event) {
        if (document.getElementById("firma_turn1").value.trim() === "") {
            alert("La firma es obligatoria.");
            event.preventDefault(); // Evita el envío del formulario
        }
    });
});
    // Eventos para escritorio
    canvas.addEventListener('mousedown', iniciarDibujo);
    canvas.addEventListener('mouseup', detenerDibujo);
    canvas.addEventListener('mousemove', dibujar);

    // Eventos para dispositivos táctiles
    canvas.addEventListener('touchstart', iniciarDibujo);
    canvas.addEventListener('touchend', detenerDibujo);
    canvas.addEventListener('touchmove', (e) => {
        e.preventDefault(); // Evitar el scroll al dibujar
        dibujar(e);
    });

    // Botón para limpiar el canvas
    limpiarBtn.addEventListener('click', () => {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        inputHidden.value = ''; // Borra el contenido del campo oculto
    });

    // Botón para guardar la firma
    guardarBtn.addEventListener('click', () => {
        if (isCanvasBlank(canvas)) {
            alert('Por favor, realice una firma antes de guardar.');
        } else {
            const dataURL = canvas.toDataURL();
            inputHidden.value = dataURL; // Almacena la firma en el campo oculto
            alert('Firma guardada correctamente.');
            // Mantén la firma visible en el canvas
            const img = new Image();
            img.src = dataURL;
            img.onload = () => {
                ctx.clearRect(0, 0, canvas.width, canvas.height); // Limpia el canvas
                ctx.drawImage(img, 0, 0); // Vuelve a dibujar la firma
            };
        }
    });
}


    // Inicializar ambos canvas con sus botones y campos ocultos
    inicializarCanvas('canvasfirma1', 'limpiarFirma1', 'guardarFirma1', 'firma_turn1');

    document.getElementById('downloadPdf').addEventListener('click', async () => {
        const condition = confirm("¿Quieres convertir el Excel a PDF?");
        if (!condition) return;

        const excelUrl = 'ruta/al/excel/generado.xlsx';

        try {
            const response = await fetch(excelUrl);
            if (!response.ok) throw new Error("Error al descargar el archivo Excel.");
            const blob = await response.blob();

            const fileReader = new FileReader();
            fileReader.onload = async (e) => {
                const data = new Uint8Array(e.target.result);
                const workbook = XLSX.read(data, { type: "array" });
                const firstSheetName = workbook.SheetNames[0];
                const worksheet = workbook.Sheets[firstSheetName];
                const jsonSheetData = XLSX.utils.sheet_to_json(worksheet, { header: 1 });

                const { jsPDF } = window.jspdf;
                const pdf = new jsPDF();

                pdf.autoTable({
                    head: [jsonSheetData[0]],
                    body: jsonSheetData.slice(1),
                });

                pdf.save('Reporte_Mantenimiento.pdf');
            };

            fileReader.readAsArrayBuffer(blob);
        } catch (error) {
            console.error("Error:", error.message);
            alert("Hubo un problema al procesar el archivo.");
        }
    });
</script>
</html>