<?php
require 'conection.php'; // Conexión a la base de datos
require 'sesion.php';
verificarAutenticacion();

function obtenerCargosDesdeSQL($pdoUsuarios) {
    try {
        $stmt = $pdoUsuarios->query("SELECT DISTINCT Cargo FROM usuarios");
        $cargos = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return $cargos;
    } catch (PDOException $e) {

        error_log("Error al obtener los cargos: " . $e->getMessage());
        die("Error al obtener los cargos. Intenta nuevamente más tarde.");
    }
}
function obtenerUsuariosDesdeSQL($pdoUsuarios) {
    try {
        $stmt = $pdoUsuarios->query("SELECT DISTINCT nombre_u FROM usuarios");
        $usuarios = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return $usuarios;
    } catch (PDOException $e) {
        error_log("Error al obtener los usuarios: " . $e->getMessage());
        die("Error al obtener los usuarios. Intenta nuevamente más tarde.");
    }
}
$cargos = obtenerCargosDesdeSQL($pdoUsuarios);
$usuarios = obtenerUsuariosDesdeSQL($pdoUsuarios);
$usuario = obtenerUsuariosDesdeSQL($pdoUsuarios);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SOLICITUD DE MANTENIMIENTO</title>
    <link rel="stylesheet" href="../css/formulario1.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <h1 class="titulo_principal">PPR DE MANTENIMIENTO DE EQUIPAMIENTOS</h1>
</head>
<body class="body">
    <br><br><br><br><br><br>


<form action="./formulario01.php" method="post" id="formulario1" class="formulario" enctype="multipart/form-data">
    <div class="formulario2">
        <h1 class="titulo">Solicitud De Reparacion Y Mantenimiento</h1>
            <div class="seccion">
                <h2 class="titulo">Información del Solicitante</h2>
                <div class="datos">
                    <input type="date" id="fechainicial" name="fechainicial" required>
                    <input type="time" id="horainicial" name="horainicial" prequired >
                    <input type="text" name="nombre_solicitante" id="nombre_solicitante" placeholder="Ingrese su Nombre" required>
                    <select name="cargo_solicitante" id="cargo_solicitante" required>
                         <?php if (!empty($cargos)): ?>
        <?php foreach ($cargos as $cargo): ?>
            <option value="<?php echo htmlspecialchars($cargo, ENT_QUOTES, 'UTF-8'); ?>">
                <?php echo htmlspecialchars($cargo, ENT_QUOTES, 'UTF-8'); ?>
            </option>
        <?php endforeach; ?>
    <?php else: ?>
        <option value="">No hay cargos disponibles</option>
    <?php endif; ?>
                    <option value="">No hay cargos disponibles</option>
                    <option value="NULL">Ninguno.</option>
                    </select>
                </div>
            </div>


            <div class="seccion">
                <h2 class="titulo">Información de la Zona, Máquina o Equipo</h2>
                <div class="datos">
                    <input type="text" name="objeto_dañado" id="objeto_dañado" placeholder="Zona, Máquina o Equipo afectados" required>
                </div>
                <div class="datos">
                    <input type="text" name="ubi" id="ubi" placeholder="Ingrese la Ubicación" required>
                </div>
                <div class="datos">
                    <input type="text" name="marca" id="marca" placeholder="Ingrese la Marca">
                </div>
                <div class="datos">
                    <input type="text" name="cod" id="cod" placeholder="Código">
                </div>

                <h2 class="titulo">Descripción Detallada</h2>
                <div class="datos">
                    <textarea name="descripcion_daños" id="descripcion_daños" required placeholder="Describa detalladamente el estado y las fallas que presenta."></textarea>
                </div>
            </div>
                <label for="toggle3" class="labelmen">
                <input type="checkbox" id="toggle3" onclick="toggleMenu3()">Registro Mediciones Mantenimiento Predictivo                   
                </label>
                <div id="Menu3" >
                    <div class="desplegable">
                        <h3>Componente 1</h3>
                        <label for="equipo_name">Nombre del componente que se Revisara</label>
                        <input type="text" name="equipo_name" placeholder="Chumacera 2...">
                        <label for="termografia">termografia</label>
                        <select name="termografia1" id="termografia">
                            <option value="N/A">N/A</option>
                            <option value="Si">Si</option>
                            <option value="No">No</option>
                        </select>
                        <label for="vibraciones">Analizador de Vibraciones</label>
                        <select name="vibraciones1" id="Vibraciones">
                            <option value="N/A">N/A</option>
                            <option value="Bueno">Bueno</option>
                            <option value="Satisfactorio">Satisfactorio</option>
                            <option value="No satisfactorio">No satisfactorio</option>
                            <option value="Inaceptable">Inaceptable</option>
                        </select>
                        <label for="multimetro">Multimetro</label>
                        <input type="text" name="rango1" placeholder="Rango">
                        <input type="text" name="amperaje1" placeholder="Amperaje">
                        <label for="observaciones">Observaciones</label>
                        <textarea name="observaciones1" id="observaciones"></textarea>
                        <label for="OT">Orden de trabajo Mantenimiento Preventivo</label>
                        <input type="number">
                      <br>
                    </div>
                    <div class="desplegable">
                        <h3>Componente 2</h3>
                        <label for="equipo_name_2">Nombre del componente que se Revisara</label>
                        <input type="text" name="equipo_name_2" placeholder="Chumacera 2...">
                        <label for="termografia2">termografia</label>
                        <select name="termografia2" id="termografia2">
                            <option value="N/A">N/A</option>
                            <option value="Si">Si</option>
                            <option value="No">No</option>
                        </select>
                        <label for="vibraciones2">Analizador de Vibraciones</label>
                        <select name="vibraciones2" id="vibraciones2">
                        <option value="N/A">N/A</option>
                            <option value="Bueno">Bueno</option>
                            <option value="Satisfactorio">Satisfactorio</option>
                            <option value="No satisfactorio">No satisfactorio</option>
                            <option value="Inaceptable">Inaceptable</option>
                        </select>
                        <label for="multimetro">Multimetro</label>
                        <input type="text" name="rango2" placeholder="Rango">
                        <input type="text" name="amperaje2" placeholder="Amperaje">
                        <label for="observaciones2">Observaciones</label>
                        <textarea name="observaciones2" id="observaciones2"></textarea>
                        <label for="OT">Orden de trabajo Mantenimiento Preventivo</label>
                        <input type="number">
                        <button class="boton" type="button" id="botonAgregar" onclick="agregarEquipo()">Agregar Otro Componente</button>                       
                    </div>
                </div> 
            <div class="contenedor-firmas">
                <h2>Firmas de Autorización</h2>
                <div class="firma">
                    <div class="formul">
                        <label for="firma_solicitante">Solicitante</label>
                        <canvas id="canvasfirma1" class="firmas-canvas" required></canvas>
                        <div class="botones-firma">
                            <button type="button" id="limpiarFirma">Limpiar</button>
                            <button type="button" id="guardarFirma">Guardar Firma</button>
                        </div>
                        <input type="hidden" name="firma_solicitante" id="firma_solicitante">
                    </div>
                    <div class="formul">
                        <label for="firma_autorizado">Autorizado Por:</label>
                        <canvas id="canvasfirma2" class="firmas-canvas" required></canvas>
                        <div class="botones-firma">
                            <button type="button" id="limpiarFirma2">Limpiar</button>
                            <button type="button" id="guardarFirma2">Guardar Firma</button>
                        </div>
                        <input type="hidden" name="firma_autorizado" id="firma_autorizado">
                    </div>
                </div>
            </div>

            <div class="seccion">
                <h2 class="titulo">Tipo de Mantenimiento</h2>
                <div class="datos">
                    <label for="tipomantenimiento">Tipo de Mantenimiento a realizar</label>
                    <select name="tipomantenimiento" id="tipomantenimiento" required>
                        <option value="Preventivo, Planeado">Preventivo, Planeado</option>
                        <option value="Predictivo, Planeado">Predictivo, Planeado</option>
                        <option value="Preventivo, No Planeado">Preventivo, No Planeado</option>
                        <option value="Predictivo, No Planeado">Predictivo, No Planeado</option>
                        <option value="correctivo">Correctivo</option>
                        <option value="garantia">Garantía</option>
                    </select>
                </div>
                <div class="datos">
                    <label for="descripcion_trabajo">Descripción del Trabajo Realizado</label>
                    <textarea name="descripcion_trabajo" id="descripcion_trabajo" required></textarea>
                </div>
                <div class="datos">
                    <label for="fecha_cierre">Fecha de Cierre del Mantenimiento</label>
                    <input type="date" id="fecha_cierre" name="fecha_cierre" required>
                </div>
                <div class="datos">
                    <label for="hora_cierre">Hora del Cierre del Mantenimiento</label>
                    <input type="time" id="hora_cierre" name="hora_cierre" required>

                </div>
            </div>

            <div class="seccion">
                <div class="datos">
                    <h2 class="titulo">Responsables</h2>
                    <div>
                    <label for="respondSelect">Seleccione el Responsable </label>
                <select  id="respondSelect" onchange="addrespondFields1()">
                    <option value="">--Seleccionar</option>
                    <option value="Miembro De La Compañia">Miembro De La Compañia (Max 3)</option>
                    <option value="Proveedor">Proveedor</option>            
                </select>
                <div>
                <div id="respondContainer"> </div>
                </div>
                </div>
                </div>
                    <div class="datos">
                        <label for="VoBo">VoBo de Mantenimiento</label>
                        <select name="VoBo" id="VoBo" required>
                        <?php if (!empty($usuario)): ?>
        <?php foreach ($usuario as $usuario): ?>
            <option value="<?php echo htmlspecialchars($usuario, ENT_QUOTES, 'UTF-8'); ?>">
                <?php echo htmlspecialchars($usuario, ENT_QUOTES, 'UTF-8'); ?>
            </option>
        <?php endforeach; ?>
    <?php else: ?>
        <option value="">No hay Usuarios disponibles</option>
    <?php endif; ?>
                <option value="NULL">Ninguno.</option>
                </select>
                </div>
            </div>
                <!-- Control de inocuidad -->
            <div class="seccion">
                    <h2>Revision De inocuidad</h2>
                    <div class="datos">
                        <label for="descripcion_inocuidad">1. En la labor ejecutada se utilizó algún tipo de insumo que represente riesgo químico, físico o biológico? indique cuáles.</label>
                        <textarea name="descripcion_inocuidad" id="descripcion_inocuidad" required></textarea>
                    </div>
                    <div class="datos">
                        <label for="retiro_inocuidad">Fueron retirados de la zona?</label>
                        <select name="retiro_inocuidad" id="retiro_inocuidad" required>
                            <option value="NoSelect">No seleccionado...</option>
                            <option value="Si">Sí.</option>
                            <option value="No">No.</option>
                            <option value="N/A">No hubieron insumos.</option>
                        </select>
                    </div>
                    <div class="datos">
                        <label for="descripcion_novedad">2. La persona encargada del mantenimiento reporta alguna novedad ocurrida que pueda implicar riesgos de la inocuidad? Especifique.</label>
                        <textarea name="descripcion_novedad" id="descripcion_novedad" required></textarea>
                    </div>
                    <div class="datos">
                        <label for="riesgo_inocuidad">3. El lugar del mantenimiento y zonas adyacentes es entregado en condiciones que no generan riesgo a la inocuidad del producto?</label>
                        <select name="riesgo_inocuidad" id="riesgo_inocuidad" required>
                            <option value="NoSelect">No seleccionado...</option>
                            <option value="Si">Sí.</option>
                            <option value="No">No.</option>
                        </select>
                    </div>
                    <div class="datos">
                        <label for="implementos">Qué implementos fueron utilizados para la limpieza/desinfección de la zona de mantenimiento.</label>
                        <textarea name="implementos" id="implementos"></textarea>
                    </div>
            </div>
            <div class="contenedor-firmas">
                <div class="firma">
                    <div class="formul">
                        <label for="firma_respLim">Responsable De La limpieza</label>
                        <canvas name="firmas" id="canvasfirma3" class="firmas-canvas" required></canvas>
                        <div class="botones-firma">
                            <button type="button" id="limpiarFirma3">Limpiar</button>
                            <button type="button" id="guardarFirma3">Guardar Firma</button>
                        </div>
                        <input type="hidden" name="firma_respLim" id="firma_respLim">
                    </div>
                    <div class="formul">
                        <label for="firma_respLim2">Responsable De Revisar La limpieza</label>
                        <canvas id="canvasfirma4" class="firmas-canvas"></canvas>
                        <div class="botones-firma">
                            <button type="button" id="limpiarFirma4">Limpiar</button>
                            <button type="button" id="guardarFirma4">Guardar Firma</button>
                        </div>
                        <input type="hidden" name="firma_respLim2" id="firma_respLim2">
                    </div>
                </div> 
                <div class="datos">
                    <label for="fecha_revisionl">Fecha y Hora de la revisión de la limpieza</label>
                    <input type="time" id="hora_revisionl" name="hora_revisionl">
                    <input type="date" id="fecha_revisionl" name="fecha_revisionl" required>
                </div>
                    <br>
            </div>
    </div>
    </div>
    <!-- // CONTROL DE PARTES SUELTAS // -->

    <div class="formulario2">
        <h1>Control de Partes Sueltas</h1>
            <br>
        <div class="seccion">
            <div class="datos">
                    <input type="text" placeholder="Responsable" name="control_responsable" id="control_responsable" required>
            </div>
            <div class="datos">
                    <input type="text" placeholder="Cargo" name="cargo_control" id="cargo_control" required>
            </div>
            <br>
            <div class="datos">
                <textarea name="trabajo_realizar" placeholder="trabajo a realizar" id="trabajo_realizar"></textarea required>
            </div>
            <div class="datos">
                    <input type="date" id="fechacontrol" name="fechacontrol" required>
            </div>
        </div>
        <div class="tabla" >
                <table class="tabla1">
                    <thead>
                        <tr>  
                            <th colspan="3">Herramientas</th>
                        </tr>
                        <tr>
                            <th>Registro Ingreso</th>
                            <th rowspan="2">Descripcion</th>
                            <th rowspan="2">Salida</th>
                        </tr>
                        <tr>
                            <th>Cantidad</th>
                            
                        </tr>
                    </thead>
                            <tbody>
                            <tr>
                                <th><input type="text" id="herramientas_cantidad1" name="herramientas_cantidad1" placeholder="Cantidad" required></th><th><input type="text" id="descripcion_herramientas1" name="descripcion_herramientas1"  placeholder="Descripcion" required></th><th><input type="text" placeholder="Cantidad De Salida" id="herramientas_salida1" name="herramientas_salida1" required></th>
                            </tr>
                            <tr>
                                <th><input type="text" id="herramientas_cantidad2" name="herramientas_cantidad2" placeholder="Cantidad"></th><th><input type="text" id="descripcion_herramientas2" name="descripcion_herramientas2"  placeholder="Descripcion"></th><th><input type="text" placeholder="Cantidad De Salida" id="herramientas_salida2" name="herramientas_salida2"></th>
                            </tr>
                            <tr>
                                <th><input type="text" id="herramientas_cantidad3" name="herramientas_cantidad3" placeholder="Cantidad"></th><th><input type="text" id="descripcion_herramientas3" name="descripcion_herramientas3"  placeholder="Descripcion"></th><th><input type="text" placeholder="Cantidad De Salida" id="herramientas_salida3" name="herramientas_salida3"></th>
                            </tr>
                            <tr>
                                <th><input type="text" id="herramientas_cantidad4" name="herramientas_cantidad4" placeholder="Cantidad" ></th><th><input type="text" id="descripcion_herramientas4" name="descripcion_herramientas4"  placeholder="Descripcion" ></th><th><input type="text" placeholder="Cantidad De Salida" id="herramientas_salida4" name="herramientas_salida4"></th>
                            </tr>
                            <tr>
                                <th><input type="text" id="herramientas_cantidad5" name="herramientas_cantidad5" placeholder="Cantidad"></th><th><input type="text" id="descripcion_herramientas5" name="descripcion_herramientas5"  placeholder="Descripcion"></th><th><input type="text" placeholder="Cantidad De Salida" id="herramientas_salida5" name="herramientas_salida5"></th>
                            </tr>
                            <tr>
                                <th><input type="text" id="herramientas_cantidad6" name="herramientas_cantidad6" placeholder="Cantidad"></th><th><input type="text" id="descripcion_herramientas6" name="descripcion_herramientas6"  placeholder="Descripcion"></th><th><input type="text" placeholder="Cantidad De Salida" id="herramientas_salida6" name="herramientas_salida6"></th>
                            </tr>
                            <tr>
                                <th><input type="text" id="herramientas_cantidad7" name="herramientas_cantidad7" placeholder="Cantidad" ></th><th><input type="text" id="descripcion_herramientas7" name="descripcion_herramientas7"  placeholder="Descripcion"></th><th><input type="text" placeholder="Cantidad De Salida" id="herramientas_salida7" name="herramientas_salida7"></th>
                            </tr>
                            <tr>
                                <th><input type="text" id="herramientas_cantidad8" name="herramientas_cantidad8" placeholder="Cantidad"></th><th><input type="text" id="descripcion_herramientas8" name="descripcion_herramientas8"  placeholder="Descripcion"></th><th><input type="text" placeholder="Cantidad De Salida" id="herramientas_salida8" name="herramientas_salida8"></th>
                            </tr>
                    
                        </tbody>
                </table>
                <br>
                <div >
                    <button type="button" onclick="addResponsableField()" class="boton"> Agregar Herramienta +</button>
                    <br><br>
                    <div id="responsablesContainer"></div>
                </div>  
                  <br>

                <table class="tabla1">
                    <thead>
                        <tr>
                            <th colspan="6">Piezas (Incluir repuestos)</th>
                        </tr>
                        <tr >
                            <th>Registro Ingreso</th>
                            <th rowspan="2">Descripcion</th>
                            <th rowspan="2">Utilizado</th>
                            <th rowspan="2">Sin Utilizar</th>
                            <th rowspan="2">Desinstalado</th>
                            <th rowspan="2">Verificacion De Salida</th>
                        </tr>
                        <tr>
                            <th>Cantidad</th>

                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                        </tr>
                        <tr>
                            <th><input type="text" placeholder="Cantidad" id="piezas_cantidad1" name="piezas_cantidad1" required></th><th><input type="text" placeholder="Descripcion" id="descripcion_piezas1" name="descripcion_piezas1" required></th><th><input type="text" placeholder="Utilizado" id="piezas_utilizadas1" name="piezas_utilizadas1" required></th><th><input type="text" placeholder="No Utilizado" id="sin_utilizar1" name="sin_utilizar1" required></th><th><input type="text" placeholder="Desinstalado" id="piezas_quitadas1" name="piezas_quitadas1" required></th><th><input type="text" placeholder="Verificacion" id="verificacion_piezas1" name="verificacion_piezas1" required></th>
                        </tr>
                        <tr>
                            <th><input type="text" placeholder="Cantidad" id="piezas_cantidad2" name="piezas_cantidad2" ></th><th><input type="text" placeholder="Descripcion" id="descripcion_piezas2" name="descripcion_piezas2" ></th><th><input type="text" placeholder="Utilizado" id="piezas_utilizadas2" name="piezas_utilizadas2" ></th><th><input type="text" placeholder="No Utilizado" id="sin_utilizar2" name="sin_utilizar2" ></th><th><input type="text" placeholder="Desinstalado" id="piezas_quitadas2" name="piezas_quitadas2"></th><th><input type="text" placeholder="Verificacion" id="verificacion_piezas2" name="verificacion_piezas2" ></th>
                        </tr>
                        <tr>
                            <th><input type="text" placeholder="Cantidad" id="piezas_cantidad3" name="piezas_cantidad3" ></th><th><input type="text" placeholder="Descripcion" id="descripcion_piezas3" name="descripcion_piezas3" ></th><th><input type="text" placeholder="Utilizado" id="piezas_utilizadas3" name="piezas_utilizadas3" ></th><th><input type="text" placeholder="No Utilizado" id="sin_utilizar3" name="sin_utilizar3" ></th><th><input type="text" placeholder="Desinstalado" id="piezas_quitadas2" name="piezas_quitadas3"></th><th><input type="text" placeholder="Verificacion" id="verificacion_piezas3" name="verificacion_piezas3" ></th>
                        </tr>
                            <tr>
                            <th><input type="text" placeholder="Cantidad" id="piezas_cantidad4" name="piezas_cantidad4" ></th><th><input type="text" placeholder="Descripcion" id="descripcion_piezas4" name="descripcion_piezas4" ></th><th><input type="text" placeholder="Utilizado" id="piezas_utilizadas4" name="piezas_utilizadas4" ></th><th><input type="text" placeholder="No Utilizado" id="sin_utilizar4" name="sin_utilizar4" ></th><th><input type="text" placeholder="Desinstalado" id="piezas_quitadas4" name="piezas_quitadas4"></th><th><input type="text" placeholder="Verificacion" id="verificacion_piezas4" name="verificacion_piezas4" ></th>
                        </tr>
                        <tr>
                            <th><input type="text" placeholder="Cantidad" id="piezas_cantidad5" name="piezas_cantidad5" ></th><th><input type="text" placeholder="Descripcion" id="descripcion_piezas5" name="descripcion_piezas5" ></th><th><input type="text" placeholder="Utilizado" id="piezas_utilizadas5" name="piezas_utilizadas5" ></th><th><input type="text" placeholder="No Utilizado" id="sin_utilizar5" name="sin_utilizar5" ></th><th><input type="text" placeholder="Desinstalado" id="piezas_quitadas5" name="piezas_quitadas5"></th><th><input type="text" placeholder="Verificacion" id="verificacion_piezas5" name="verificacion_piezas5" ></th>
                        </tr>
                        <tr>
                            <th><input type="text" placeholder="Cantidad" id="piezas_cantidad6" name="piezas_cantidad6" ></th><th><input type="text" placeholder="Descripcion" id="descripcion_piezas6" name="descripcion_piezas6" ></th><th><input type="text" placeholder="Utilizado" id="piezas_utilizadas6" name="piezas_utilizadas6" ></th><th><input type="text" placeholder="No Utilizado" id="sin_utilizar6" name="sin_utilizar6" ></th><th><input type="text" placeholder="Desinstalado" id="piezas_quitadas6" name="piezas_quitadas6"></th><th><input type="text" placeholder="Verificacion" id="verificacion_piezas6" name="verificacion_piezas6" ></th>
                        </tr>
                            <tr>
                            <th><input type="text" placeholder="Cantidad" id="piezas_cantidad7" name="piezas_cantidad7" ></th><th><input type="text" placeholder="Descripcion" id="descripcion_piezas7" name="descripcion_piezas7" ></th><th><input type="text" placeholder="Utilizado" id="piezas_utilizadas7" name="piezas_utilizadas7" ></th><th><input type="text" placeholder="No Utilizado" id="sin_utilizar7" name="sin_utilizar7" ></th><th><input type="text" placeholder="Desinstalado" id="piezas_quitadas7" name="piezas_quitadas7"></th><th><input type="text" placeholder="Verificacion" id="verificacion_piezas7" name="verificacion_piezas7" ></th>
                        </tr>
                        <tr>
                            <th><input type="text" placeholder="Cantidad" id="piezas_cantidad8" name="piezas_cantidad8" ></th><th><input type="text" placeholder="Descripcion" id="descripcion_piezas8" name="descripcion_piezas8" ></th><th><input type="text" placeholder="Utilizado" id="piezas_utilizadas8" name="piezas_utilizadas8" ></th><th><input type="text" placeholder="No Utilizado" id="sin_utilizar8" name="sin_utilizar8" ></th><th><input type="text" placeholder="Desinstalado" id="piezas_quitadas8" name="piezas_quitadas8"></th><th><input type="text" placeholder="Verificacion" id="verificacion_piezas8" name="verificacion_piezas8" ></th>
                        </tr>
                    </tbody>
                </table>
                <br><br>

                <table class="tabla1">
                    <thead>
                        <tr>
                            <th colspan="5">MATERIALES (Registrar los materiales e insumos utilizados, lubricantes, pinturas, lijas, etc)</th>
                        </tr>
                        <tr>
                            <th>Registro Ingreso</th>
                            <th rowspan="2">Unidad De Medida</th>
                            <th rowspan="2">Descripcion</th>
                            <th rowspan="2">Utilizado</th> 
                            <th rowspan="2">Verificacion De Salida</th>
                        </tr>
                        <tr>
                            <th>Cantidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th><input type="text" placeholder="Cantidad" id="materiales_cantidad1" name="materiales_cantidad1" required></th><th><input type="text" placeholder="Unidad" id="medida_materiales1" name="medida_materiales1" required></th><th><input type="text" placeholder="Descripcion" id="descripcion_materiales1" name="descripcion_materiales1" required></th><th><input type="text" placeholder=" Utilizado" id="materiales_utilizados1" name="materiales_utilizados1" required></th><th><input type="text" placeholder="Verificacion Salida" id="verificacion_material1" name="verificacion_material1" required></th>
                        </tr>
                        <tr>
                            <th><input type="text" placeholder="Cantidad" id="materiales_cantidad2" name="materiales_cantidad2" ></th><th><input type="text" placeholder="Unidad" id="medida_materiales2" name="medida_materiales2" ></th><th><input type="text" placeholder="Descripcion" id="descripcion_materiales2" name="descripcion_materiales2" ></th><th><input type="text" placeholder=" Utilizado" id="materiales_utilizados2" name="materiales_utilizados2" ></th><th><input type="text" placeholder="Verificacion Salida" id="verificacion_material2" name="verificacion_material2" ></th>
                        </tr>
                        <tr>
                            <th><input type="text" placeholder="Cantidad" id="materiales_cantidad3" name="materiales_cantidad3" ></th><th><input type="text" placeholder="Unidad" id="medida_materiales3" name="medida_materiales3" ></th><th><input type="text" placeholder="Descripcion" id="descripcion_materiales3" name="descripcion_materiales3" ></th><th><input type="text" placeholder=" Utilizado" id="materiales_utilizados3" name="materiales_utilizados3" ></th><th><input type="text" placeholder="Verificacion Salida" id="verificacion_material3" name="verificacion_material3" ></th>
                        </tr>
                        <tr>
                            <th><input type="text" placeholder="Cantidad" id="materiales_cantidad4" name="materiales_cantidad4" ></th><th><input type="text" placeholder="Unidad" id="medida_materiales4" name="medida_materiales4" ></th><th><input type="text" placeholder="Descripcion" id="descripcion_materiales4" name="descripcion_materiales4"></th><th><input type="text" placeholder=" Utilizado" id="materiales_utilizados4" name="materiales_utilizados4" ></th><th><input type="text" placeholder="Verificacion Salida" id="verificacion_material4" name="verificacion_material4" ></th>
                        </tr>
                        <tr>
                            <th><input type="text" placeholder="Cantidad" id="materiales_cantidad5" name="materiales_cantidad5" ></th><th><input type="text" placeholder="Unidad" id="medida_materiales5" name="medida_materiales5" ></th><th><input type="text" placeholder="Descripcion" id="descripcion_materiales5" name="descripcion_materiales5" ></th><th><input type="text" placeholder=" Utilizado" id="materiales_utilizados5" name="materiales_utilizados5" ></th><th><input type="text" placeholder="Verificacion Salida" id="verificacion_material5" name="verificacion_material5" ></th>
                        </tr>
                        <tr>
                            <th><input type="text" placeholder="Cantidad" id="materiales_cantidad6" name="materiales_cantidad6" ></th><th><input type="text" placeholder="Unidad" id="medida_materiales6" name="medida_materiales6" ></th><th><input type="text" placeholder="Descripcion" id="descripcion_materiales6" name="descripcion_materiales6" ></th><th><input type="text" placeholder=" Utilizado" id="materiales_utilizados6" name="materiales_utilizados6" ></th><th><input type="text" placeholder="Verificacion Salida" id="verificacion_material6" name="verificacion_material6" ></th>
                        </tr>
                            <tr>
                            <th><input type="text" placeholder="Cantidad" id="materiales_cantidad7" name="materiales_cantidad7" ></th><th><input type="text" placeholder="Unidad" id="medida_materiales7" name="medida_materiales7" ></th><th><input type="text" placeholder="Descripcion" id="descripcion_materiales7" name="descripcion_materiales7" ></th><th><input type="text" placeholder=" Utilizado" id="materiales_utilizados7" name="materiales_utilizados7" ></th><th><input type="text" placeholder="Verificacion Salida" id="verificacion_material7" name="verificacion_material7 " ></th>
                        </tr>
                        <tr>
                            <th><input type="text" placeholder="Cantidad" id="materiales_cantidad8" name="materiales_cantidad8" ></th><th><input type="text" placeholder="Unidad" id="medida_materiales8" name="medida_materiales8" ></th><th><input type="text" placeholder="Descripcion" id="descripcion_materiales8" name="descripcion_materiales8" ></th><th><input type="text" placeholder=" Utilizado" id="materiales_utilizados8" name="materiales_utilizados8" ></th><th><input type="text" placeholder="Verificacion Salida" id="verificacion_material8" name="verificacion_material8" ></th>
                        </tr>
                    </tbody>
                </table>
        </div>
    <div class="seccion">
            <div class="datos">
                <label for="">VoBo Verificacion Ingreso</label>
                <input type="text" name="Vobo_ingreso" id="Vobo_ingreso" required>
            </div>
            <div class="datos">
                <label for="">VoBo Verificacion Salida</label>
                <input type="text" name="Vobo_salida" id="Vobo_salida" required>
                <!-- IMAGEN -->
            </div>
    </div>  
    <div class="seccion">
        <div class="datos">
        <label for="imagen">Evidencia De Antes:</label>
        <input type="file" id="imagen" name="imagen" accept="image/*" required><br><br>
        <label> Evidencia Extra<input type="checkbox" id="toggle1" onclick="toggleMenu1()"> </label>    
        <div id="Menu1" style="display: none; margin-top: 10px;" class="datos">
            <label for="imagen_1">Evidencia Extra (ANTES)</label>
            <input type="file" id="imagen_1" name="imagen_1" accept="image/*">
            <label for="imagen_2">Evidencia Extra (ANTES)</label>
            <input type="file" id="imagen_2" name="imagen_2" accept="image/*">
            <label for="imagen_3">Evidencia Extra (ANTES)</label>
            <input type="file" id="imagen_3" name="imagen_3" accept="image/*">
        </div>
        </div>
    </div>
    <div class="seccion">
        <div class="datos">
        <label for="imagen2">Evidencia Proceso terminado:</label>
        <input  type="file" id="imagen2" name="imagen2" accept="image/*" required><br><br>
        <label>Evidencia Extra <input type="checkbox" id="toggle2" onclick="toggleMenu2()"></label>
                <div id="Menu2" style="display: none; margin-top: 10px;">
            <label for="imagen2_1">Evidencia Extra (DESPUES)</label>
            <input type="file" id="imagen2_1" name="imagen2_1" accept="image/*">
            <label for="imagen2_2">Evidencia Extra (DESPUES)</label>
            <input type="file" id="imagen2_2" name="imagen2_2" accept="image/*">
            <label for="imagen2_3">Evidencia Extra (DESPUES)</label>
            <input type="file" id="imagen2_3" name="imagen2_3" accept="image/*">
        </div>
        </div>
    </div>
 
    <div class="espacio-botones">
        <button type="submit" class="boton">Enviar</button>
        <button type="reset" class="boton">Restablecer</button>
    </div>
    </div>
    
</form>


<!-- Contenedor de firmas separado del formulario principal -->



    <!-- ESPACIO PARA SCRIPTS -->

<script>
    //Menus Desplegables
// JavaScript

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
     <div class="herramientas-dynamic-row">
        <label for="nombre-${responsablecount}" >Cantidad Ingreso:</label>
        <input  type="text" id="herramienta-${responsablecount}" name="herramienta-${responsablecount}" placeholder="Cantidad">
       
        <label for="herramientaext1" >Descripcion</label>
        <input type="text" id="herramientades-${responsablecount}" name="herramientades-${responsablecount}" placeholder="Descripción">
        
        <label for="herramientaexts1">Cantidad Salida</label>
        <input type="text" id="herramientacant-${responsablecount}" name="herramientacant-${responsablecount}" placeholder="Cantidad Salida">
    </div> 
    <button type="button" class="boton" onclick="removeResponsableField('${fieldsDiv.id}')">Eliminar</button>`;

    // Añadir el nuevo campo al contenedor
    container.appendChild(fieldsDiv);
}
function removeResponsableField(id) {
    const field = document.getElementById(id);
    if (field) {
        field.remove(); // Eliminar el campo dinámico
    }
}



let contador = 1;
let counter = 0; // Contador global para generar IDs únicos

function addrespondFields1() {
    const select = document.getElementById('respondSelect');
    const respondType = select.value;

    if (respondType) {
        const uniqueId = `${respondType.replace(/\s+/g, '_')}_${counter++}`; // ID único basado en el tipo y contador

        const container = document.getElementById('respondContainer');
        const fieldsDiv = document.createElement('div');
        fieldsDiv.className = 'respond-fields';
        fieldsDiv.id = `respond-${uniqueId}`;

        if (respondType === "Proveedor") {
            // Formulario para Proveedor
            fieldsDiv.innerHTML = `
                <h3>Detalles del Responsable (Proveedor)</h3>
                <label for="nombre-${uniqueId}">Ingrese el Nombre del Proveedor:</label>
                <input type="text" id="nombre-${uniqueId}" name="nombre-${uniqueId}">
                <button type="button" onclick="removematerialFields('${uniqueId}')">Eliminar</button>
                <label for="idfile-${uniqueId}">Ingrese Los Documentos Necesarios</label>
                <input type="file" id="idfile-${uniqueId}" name="fileprov-${uniqueId}" accept=".pdf">
            `;
        } else if (respondType === "Miembro De La Compañia") {
            // Formulario para Miembro De La Compañia
            fieldsDiv.innerHTML = `
                <h3>Detalles del Responsable (Miembro De La Compañia)</h3>
                <label for="responsable-${uniqueId}">Seleccione un Usuario:</label>
                <select name="responsable-${uniqueId}" id="responsable-${uniqueId}" required>
                    <?php if (!empty($usuarios)): ?>
                        <?php foreach ($usuarios as $usuario): ?>
                            <option value="<?php echo htmlspecialchars($usuario, ENT_QUOTES, 'UTF-8'); ?>">
                                <?php echo htmlspecialchars($usuario, ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="">No hay Usuarios disponibles</option>
                    <?php endif; ?>
                    <option value="NULL">Ninguno</option>
                    <option value="Proveedor">Proveedor</option>
                </select>
                <button type="button" onclick="removematerialFields('${uniqueId}')">Eliminar</button>
            `;
        }

        container.appendChild(fieldsDiv); // Agregar los campos al contenedor
        select.value = "";
    }
}

function removematerialFields(id) {
    const fieldToRemove = document.getElementById(`respond-${id}`);
    if (fieldToRemove) {
        fieldToRemove.remove(); // Eliminar el campo del DOM
    }
}

    


function removematerialFields(id) {
    const fieldsDiv = document.getElementById(`respond-${id}`);
    fieldsDiv.remove();
}


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
    inicializarCanvas('canvasfirma1', 'limpiarFirma', 'guardarFirma', 'firma_solicitante');
    inicializarCanvas('canvasfirma2', 'limpiarFirma2', 'guardarFirma2', 'firma_autorizado');
    inicializarCanvas('canvasfirma3', 'limpiarFirma3', 'guardarFirma3', 'firma_respLim');
    inicializarCanvas('canvasfirma4', 'limpiarFirma4', 'guardarFirma4', 'firma_respLim2');

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
    function toggleMenu1() {
            const checkbox = document.getElementById('toggle1');
            const menu = document.getElementById('Menu1');
            menu.style.display = checkbox.checked ? 'block' : 'none';
        }
    function toggleMenu2() {
        const checkbox = document.getElementById('toggle2');
        const menu = document.getElementById('Menu2');
        menu.style.display = checkbox.checked ? 'block' : 'none';
        }        
    function toggleMenu3() {
        const checkbox = document.getElementById('toggle3');
        const menu = document.getElementById('Menu3');
        menu.style.display = checkbox.checked ? 'block' : 'none';
        } // Contador de equipos adicionales

function agregarEquipo() {
    if (contador >= 4) {
        alert("Solo puedes agregar hasta 4 equipos adicionales.");
        return;
    }

    contador++;
    let contenedor = document.getElementById("Menu3");

    let nuevoEquipo = document.createElement("div");
    nuevoEquipo.classList.add("equipo");
    nuevoEquipo.id = "equipo" + contador;

    nuevoEquipo.innerHTML = `<div class="datos">
    <div class="seccion">
        
        <h3>Componente${contador}</h3>
        <label for="equipo_name">Nombre del componente que se Revisara</label>
        <input type="text" name="equipo_name${contador}" placeholder="Chumacera 2...">
        <label for="termografia${contador}">Termografía</label>
        <select name="termografia${contador}">
            <option value="N/A">N/A</option>
            <option value="Si">Si</option>
            <option value="No">No</option>
        </select>

        <label for="vibraciones${contador}">Analizador de Vibraciones</label>
        <select name="vibraciones${contador}">
            <option value="N/A">N/A</option>
            <option value="Bueno">Bueno</option>
            <option value="Satisfactorio">Satisfactorio</option>
            <option value="No satisfactorio">No satisfactorio</option>
            <option value="Inaceptable">Inaceptable</option>
        </select>

        <label for="multimetro${contador}">Multímetro</label>
        <input type="text" name="rango${contador}" placeholder="Rango">
        <input type="text" name="amperaje${contador}" placeholder="Amperaje">

        <label for="observaciones${contador}">Observaciones</label>
        <textarea name="observaciones${contador}"></textarea>
        <label for="OT${contador}">Orden de Trabajo Mantenimiento Preventivo</label>
        <input type="number" name="OT${contador}">
    </div>
    <div class="seccion">
        <h3>Componente${contador}</h3>
        <label for="equipo_name">Nombre del componente que se Revisara</label>
        <input type="text" name="equipo_name2${contador}" placeholder="Chumacera 2...">
        <label for="termografia_motor${contador}">Termografía</label>
        <select name="termografia_motor${contador}">
            <option value="N/A">N/A</option>
            <option value="Si">Si</option>
            <option value="No">No</option>
        </select>

        <label for="vibraciones_motor${contador}">Analizador de Vibraciones</label>
        <select name="vibraciones_motor${contador}">
            <option value="N/A">N/A</option>
            <option value="Bueno">Bueno</option>
            <option value="Satisfactorio">Satisfactorio</option>
            <option value="No satisfactorio">No satisfactorio</option>
            <option value="Inaceptable">Inaceptable</option>
        </select>

        <label for="multimetro_motor${contador}">Multímetro</label>
        <input type="text" name="rango_motor${contador}" placeholder="Rango">
        <input type="text" name="amperaje_motor${contador}" placeholder="Amperaje">

        <label for="observaciones_motor${contador}">Observaciones</label>
        <textarea name="observaciones_motor${contador}"></textarea>

        <label for="OT_motor${contador}">Orden de Trabajo Mantenimiento Preventivo</label>
        <input type="number" name="OT_motor${contador}">
        <button type="button" class="eliminarEquipo" onclick="eliminarEquipo(${contador})">Eliminar</button>
    </div>
    </div>`;

    contenedor.appendChild(nuevoEquipo);
    contenedor.appendChild(botonAgregar);
}
function eliminarEquipo(id) {
    let equipo = document.getElementById("equipo" + id);
    if (equipo) {
        equipo.remove();
    }
}
    </script>
   
</body>
</html>
