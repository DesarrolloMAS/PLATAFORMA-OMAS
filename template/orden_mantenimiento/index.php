<?php
require_once '../sesion.php';
require_once '../conection.php';

verificarAutenticacion();

// Obtener cargos y usuarios para selectores
function obtenerCargos($pdoUsuarios) {
    $stmt = $pdoUsuarios->query("SELECT DISTINCT Cargo FROM usuarios ORDER BY Cargo ASC");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function obtenerUsuarios($pdoUsuarios) {
    $stmt = $pdoUsuarios->query("SELECT DISTINCT nombre_u FROM usuarios ORDER BY nombre_u ASC");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

$cargos = obtenerCargos($pdoUsuarios);
$usuarios = obtenerUsuarios($pdoUsuarios);
$nombre_usuario = $_SESSION['nombre'] ?? '';
$sede_usuario = $_SESSION['sede'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>S.O.M. - Sistema de Orden de Mantenimiento</title>
    <link rel="stylesheet" href="../../css/orden_mantenimiento_v2.css">
</head>
<body>
    <svg class="corner-deco" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle cx="200" cy="0" r="160" stroke="#e8c840" stroke-width="1"/>
        <circle cx="200" cy="0" r="120" stroke="#e8c840" stroke-width="0.5"/>
        <circle cx="200" cy="0" r="80" stroke="#e8c840" stroke-width="0.5"/>
        <line x1="200" y1="0" x2="0" y2="200" stroke="#e8c840" stroke-width="0.5"/>
        <line x1="200" y1="0" x2="60" y2="200" stroke="#e8c840" stroke-width="0.3"/>
    </svg>
    <div class="container">
        <header>
            <h1>S.O.M. V2</h1>
            <p>Sistema de Orden de Mantenimiento - Formato 001 Modernizado</p>
        </header>

        <form id="mainForm" action="procesar.php" method="POST" enctype="multipart/form-data">
            
            <!-- SECCION 1: CLASIFICACION Y SOLICITANTE -->
            <div class="section-card">
                <div class="section-title">Clasificación y Solicitante</div>
                <div class="grid-2">
                    <div class="form-group">
                        <label>Sede Actual</label>
                        <input type="text" value="<?php echo $sede_usuario; ?>" readonly style="color: var(--primary); font-weight: bold;">
                    </div>
                    <div class="form-group">
                        <label>Fecha de Solicitud</label>
                        <input type="date" name="fecha_solicitud" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Hora de Solicitud</label>
                        <input type="time" name="hora_solicitud" value="<?php echo date('H:i'); ?>" required>
                    </div>
                </div>
                <div class="grid-2">
                    <div class="form-group">
                        <label>Tipo de Mantenimiento</label>
                        <select name="clasificacion" required>
                            <option value="">-- Seleccionar --</option>
                            <option value="Locativa">Locativa</option>
                            <option value="Mecanico">Mecánico</option>
                            <option value="Electrico">Eléctrico</option>
                            <option value="NA">N/A</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Nombre del Solicitante</label>
                        <input type="text" name="nombre_solicitante" value="<?php echo $nombre_usuario; ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Cargo del Solicitante</label>
                    <select name="cargo_solicitante" required>
                        <option value="">-- Seleccionar Cargo --</option>
                        <?php foreach($cargos as $cargo): ?>
                            <option value="<?php echo $cargo; ?>"><?php echo $cargo; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- SECCION 2: INFORMACION DEL EQUIPO -->
            <div class="section-card">
                <div class="section-title">Información de la Zona / Equipo</div>
                <div class="grid-2">
                    <div class="form-group">
                        <label>Equipo / Objeto Dañado</label>
                        <input type="text" name="objeto_dañado" placeholder="Nombre de la máquina o zona" required>
                    </div>
                    <div class="form-group">
                        <label>Ubicación Específica</label>
                        <input type="text" name="ubicacion" placeholder="Ej: Planta 2, Sección A">
                    </div>
                </div>
                <div class="grid-2">
                    <div class="form-group">
                        <label>Marca (Opcional)</label>
                        <input type="text" name="marca" placeholder="Marca del equipo">
                    </div>
                    <div class="form-group">
                        <label>Código Interno</label>
                        <input type="text" name="codigo_equipo" placeholder="Cod. Inventario">
                    </div>
                </div>
                <div class="form-group">
                    <label>Descripción Detallada del Fallo</label>
                    <textarea name="descripcion_falla" rows="3" placeholder="Describa qué sucede y posibles causas observadas..." required></textarea>
                </div>
            </div>

            <!-- SECCION 3: MEDICIONES TECNICAS (COLAPSABLE) -->
            <div class="section-card">
                <div class="section-title" onclick="toggleMediciones()" style="cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
                    <span>Registro de Mediciones (Predictivo)</span>
                    <span id="mediciones_status" style="font-size: 0.7rem; color: var(--accent2);">[ CLIC PARA ACTIVAR ]</span>
                </div>
                <div id="mediciones_wrapper" style="display: none;">
                    <div style="padding-top: 10px;"></div>
                    <input type="hidden" name="usa_mediciones" id="usa_mediciones" value="0">
                    
                    <div id="mediciones_container">
                        <div class="medicion-row" style="border: 1px solid #334155; padding: 15px; border-radius: 8px; margin-bottom: 15px; background: rgba(30, 41, 59, 0.5);">
                            <div class="grid-2">
                                <div class="form-group">
                                    <label>Nombre del Componente Evaluado</label>
                                    <input type="text" name="med_equipo_name[]" placeholder="Chumacera 2, Motor B..." disabled>
                                </div>
                                <div class="form-group">
                                    <label>Parte del Equipo</label>
                                    <select name="med_parte[]" disabled>
                                        <option value="Equipo">Equipo</option>
                                        <option value="Motor">Motor</option>
                                    </select>
                                </div>
                            </div>
                            <div class="grid-3" style="margin-top: 15px;">
                                <div class="form-group">
                                    <label>Termografía</label>
                                    <select name="med_termografia[]" disabled>
                                        <option value="N/A">N/A</option>
                                        <option value="Si">Sí</option>
                                        <option value="No">No</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Vibraciones</label>
                                    <select name="med_vibraciones[]" disabled>
                                        <option value="N/A">N/A</option>
                                        <option value="Bueno">Bueno</option>
                                        <option value="Satisfactorio">Satisfactorio</option>
                                        <option value="No satisfactorio">No satisfactorio</option>
                                        <option value="Inaceptable">Inaceptable</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Multímetro: Rango</label>
                                    <input type="text" name="med_rango[]" placeholder="Ej: 200V / 20A" disabled>
                                </div>
                            </div>
                            <div class="grid-2" style="margin-top: 15px;">
                                <div class="form-group">
                                    <label>Multímetro: Amperaje</label>
                                    <input type="text" name="med_amperaje[]" placeholder="Ej: 12A / 11.5A / 12A" disabled>
                                </div>
                                <div class="form-group">
                                    <label>Observaciones</label>
                                    <input type="text" name="med_obs[]" placeholder="Novedades encontradas..." disabled>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="button" id="add_medicion_btn" class="btn-add" style="margin-top: 10px; width: 100%;" disabled>
                        + Agregar Otra Medición
                    </button>
                </div>
            </div>

            <!-- SECCION 4: AUTORIZACION PRE-EJECUCION -->
            <div class="section-card">
                <div class="section-title">Firmas de Autorización de la Solicitud</div>
                <div class="grid-2">
                    <div class="signature-container">
                        <label>Firma Solicitante</label>
                        <canvas id="canvas_solicitante" width="400" height="150"></canvas>
                        <div class="signature-btns">
                            <button type="button" class="btn-clear">Limpiar</button>
                        </div>
                        <input type="hidden" name="firma_solicitante" id="canvas_solicitante_input">
                    </div>
                    <div class="signature-container">
                        <label>Firma Autorizado Por:</label>
                        <canvas id="canvas_autorizado" width="400" height="150"></canvas>
                        <div class="signature-btns">
                            <button type="button" class="btn-clear">Limpiar</button>
                        </div>
                        <input type="hidden" name="firma_autorizado" id="canvas_autorizado_input">
                    </div>
                </div>
            </div>

            <!-- SECCION 5: EJECUCION DEL MANTENIMIENTO -->
            <div class="section-card">
                <div class="section-title">Ejecución del Trabajo</div>
                <div class="form-group">
                    <label>Tipo de Mantenimiento a realizar</label>
                    <select name="tipo_ejecucion" required>
                        <option value="Preventivo, Planeado">Preventivo, Planeado</option>
                        <option value="Predictivo, Planeado">Predictivo, Planeado</option>
                        <option value="Preventivo, No Planeado">Preventivo, No Planeado</option>
                        <option value="Predictivo, No Planeado">Predictivo, No Planeado</option>
                        <option value="Correctivo">Correctivo</option>
                        <option value="Garantia">Garantía</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Descripción del Trabajo Realizado</label>
                    <textarea name="trabajo_realizado" rows="4" placeholder="Detalle las acciones tomadas..." required></textarea>
                </div>
                
                <div class="grid-2">
                    <div class="form-group">
                        <label>Fecha de Cierre del Mantenimiento</label>
                        <input type="date" name="fecha_cierre" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-group">
                        <label>Hora del Cierre del Mantenimiento</label>
                        <input type="time" name="hora_cierre" value="<?php echo date('H:i'); ?>">
                    </div>
                </div>
                
                <div class="grid-2" style="background: rgba(0, 240, 255, 0.05); padding: 15px; border-radius: 8px; margin-top: 20px;">
                    <div class="form-group">
                        <label>Responsable de Ejecución</label>
                        <select name="tipo_responsable" id="tipo_responsable">
                            <option value="">--Seleccionar Tipo--</option>
                            <option value="Miembro De La Compañia">Miembro De La Compañia</option>
                            <option value="Proveedor">Proveedor</option>            
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Nombre del Responsable / Proveedor</label>
                        <input type="text" name="nombre_responsable" placeholder="Especifique el nombre o proveedor...">
                    </div>
                </div>

                <div class="form-group" style="margin-top: 15px;">
                    <label>VoBo de Mantenimiento</label>
                    <select name="vobo_mantenimiento" required>
                        <option value="">-- Seleccionar Usuario --</option>
                        <?php foreach($usuarios as $user): ?>
                            <option value="<?php echo $user; ?>"><?php echo $user; ?></option>
                        <?php endforeach; ?>
                        <option value="NULL">Ninguno.</option>
                    </select>
                </div>
            </div>

            <!-- SECCION 5: CONTROL DE INOCUIDAD -->
            <div class="section-card">
                <div class="section-title">Revisión de Inocuidad (Sanitario)</div>
                <div class="form-group">
                    <label>1. ¿Se utilizaron insumos con riesgo químico/físico/biológico? Indíquelos:</label>
                    <textarea name="insumos_riesgo" rows="2"></textarea>
                </div>
                <div class="grid-2">
                    <div class="form-group">
                        <label>¿Fueron retirados de la zona?</label>
                        <select name="retirados_zona">
                            <option value="NoSelect">-- Seleccionar --</option>
                            <option value="Si">Sí</option>
                            <option value="No">No</option>
                            <option value="N/A">N/A (No hubo insumos)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>2. Novedad de riesgo ocurrida durante mantenimiento:</label>
                        <input type="text" name="novedad_inocuidad" placeholder="Describa la novedad si la hubo...">
                    </div>
                </div>
                <div class="grid-2">
                    <div class="form-group">
                        <label>3. ¿Se entrega la zona en condiciones higiénicas sin riesgo?</label>
                        <select name="condiciones_higienicas" required>
                            <option value="NoSelect">-- Seleccionar --</option>
                            <option value="Si">Sí</option>
                            <option value="No">No</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>4. Implementos de limpieza usados:</label>
                        <input type="text" name="implementos_limpieza" placeholder="Trapos, desinfectantes...">
                    </div>
                </div>

                <div class="grid-2" style="margin-top: 20px; padding-top: 15px; border-top: 1px dashed var(--border);">
                    <div class="form-group">
                        <label>Fecha Revisión Limpieza</label>
                        <input type="date" name="fecha_revisionl" required>
                    </div>
                    <div class="form-group">
                        <label>Hora Revisión Limpieza</label>
                        <input type="time" name="hora_revisionl" required>
                    </div>
                </div>

                <div class="grid-2" style="margin-top: 20px;">
                    <div class="signature-container">
                        <label>Firma Responsable de Limpieza</label>
                        <canvas id="canvas_limpieza" width="300" height="100"></canvas>
                        <div class="signature-btns">
                            <button type="button" class="btn-clear">Limpiar</button>
                        </div>
                        <input type="hidden" name="firma_respLim" id="canvas_limpieza_input">
                    </div>
                    <div class="signature-container">
                        <label>Firma Quien Revisa Limpieza</label>
                        <canvas id="canvas_revisa_limpieza" width="300" height="100"></canvas>
                        <div class="signature-btns">
                            <button type="button" class="btn-clear">Limpiar</button>
                        </div>
                        <input type="hidden" name="firma_respLim2" id="canvas_revisa_limpieza_input">
                    </div>
                </div>
            </div>

            <!-- SECCION 6: CONTROL DE PARTES SUELTAS (TABLAS DINAMICAS) -->
            <div class="section-card">
                <div class="section-title">Control de Partes Sueltas</div>

                <div class="grid-2">
                    <div class="form-group">
                        <label>Responsable de Control</label>
                        <input type="text" name="control_responsable" placeholder="Responsable del manejo" required>
                    </div>
                    <div class="form-group">
                        <label>Cargo Responsable</label>
                        <input type="text" name="cargo_control" required>
                    </div>
                </div>
                <div class="grid-2" style="margin-bottom: 20px;">
                    <div class="form-group">
                        <label>Trabajo Específico A Realizar</label>
                        <input type="text" name="trabajo_realizar_control" required>
                    </div>
                    <div class="form-group">
                        <label>Fecha de Control</label>
                        <input type="date" name="fechacontrol" required>
                    </div>
                </div>
                
                <p style="font-size: 0.8rem; color: var(--primary); margin-bottom: 10px; font-weight: bold; border-bottom: 1px solid var(--border);">1. HERRAMIENTAS</p>
                <div class="table-responsive">
                    <table id="table_tools">
                        <thead>
                            <tr>
                                <th>Cant. Ingreso</th>
                                <th>Descripción Herramienta</th>
                                <th>Cant. Salida</th>
                                <th style="width: 40px; text-align: center;">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><input type="number" name="tool_cant[]" step="any"></td>
                                <td><input type="text" name="tool_desc[]"></td>
                                <td><input type="text" name="tool_salida[]"></td>
                                <td class="action-cell" style="text-align: center;"><button type="button" class="btn-delete-row" onclick="deleteRow(this)">✕</button></td>
                            </tr>
                        </tbody>
                    </table>
                    <button type="button" class="btn-clear" onclick="addRow('table_tools')" style="margin-top: 5px;">+ Agregar Herramienta</button>
                </div>

                <p style="font-size: 0.8rem; color: var(--primary); margin: 20px 0 10px 0; font-weight: bold; border-bottom: 1px solid var(--border);">2. PIEZAS Y REPUESTOS</p>
                <div class="table-responsive">
                    <table id="table_parts">
                        <thead>
                            <tr>
                                <th>Cant.</th>
                                <th>Descripción</th>
                                <th>Utilizado</th>
                                <th>Sin Utilizar</th>
                                <th>Desinstalado</th>
                                <th>Verif. Salida</th>
                                <th style="width: 40px; text-align: center;">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><input type="number" name="part_cant[]" step="any" style="width: 60px;"></td>
                                <td><input type="text" name="part_desc[]"></td>
                                <td><input type="text" name="part_used[]" style="width: 70px;"></td>
                                <td><input type="text" name="part_unused[]" style="width: 70px;"></td>
                                <td><input type="text" name="part_removed[]" style="width: 80px;"></td>
                                <td><input type="text" name="part_verif[]" style="width: 80px;"></td>
                                <td class="action-cell" style="text-align: center;"><button type="button" class="btn-delete-row" onclick="deleteRow(this)">✕</button></td>
                            </tr>
                        </tbody>
                    </table>
                    <button type="button" class="btn-clear" onclick="addRow('table_parts')" style="margin-top: 5px;">+ Agregar Pieza</button>
                </div>

                <p style="font-size: 0.8rem; color: var(--primary); margin: 20px 0 10px 0; font-weight: bold; border-bottom: 1px solid var(--border);">3. MATERIALES E INSUMOS</p>
                <div class="table-responsive">
                    <table id="table_materials">
                        <thead>
                            <tr>
                                <th>Cant. Ingreso</th>
                                <th>Unidad de Medida</th>
                                <th>Descripción (Lijas, Pinturas...)</th>
                                <th>Utilizado</th>
                                <th>Verif. Salida</th>
                                <th style="width: 40px; text-align: center;">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><input type="number" name="mat_cant[]" step="any" style="width: 70px;"></td>
                                <td><input type="text" name="mat_unidad[]" style="width: 80px;"></td>
                                <td><input type="text" name="mat_desc[]"></td>
                                <td><input type="text" name="mat_used[]" style="width: 80px;"></td>
                                <td><input type="text" name="mat_verif[]" style="width: 80px;"></td>
                                <td class="action-cell" style="text-align: center;"><button type="button" class="btn-delete-row" onclick="deleteRow(this)">✕</button></td>
                            </tr>
                        </tbody>
                    </table>
                    <button type="button" class="btn-clear" onclick="addRow('table_materials')" style="margin-top: 5px;">+ Agregar Material</button>
                </div>
                
                <div class="grid-2" style="margin-top: 20px;">
                    <div class="form-group">
                        <label>VoBo Verificación Ingreso</label>
                        <input type="text" name="vobo_ingreso_control" required>
                    </div>
                    <div class="form-group">
                        <label>VoBo Verificación Salida</label>
                        <input type="text" name="vobo_salida_control" required>
                    </div>
                </div>
            </div>

            <!-- SECCION 8: EVIDENCIAS EXTRA -->
            <div class="section-card">
                <div class="section-title">Registro Fotográfico</div>
                <div class="grid-2">
                    <div class="form-group">
                        <label>Evidencia de Antes</label>
                        <div class="file-upload-wrapper">
                            <input type="file" name="foto_antes" id="foto_antes" accept="image/*" class="file-input-hidden" required>
                            <label for="foto_antes" class="file-upload-button">
                                <span class="file-upload-icon">📷</span>
                                <span class="file-upload-text">Seleccionar Imagen</span>
                            </label>
                            <div class="file-upload-name" id="foto_antes_name">Ningún archivo seleccionado</div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Evidencia Proceso Terminado</label>
                        <div class="file-upload-wrapper">
                            <input type="file" name="foto_despues" id="foto_despues" accept="image/*" class="file-input-hidden" required>
                            <label for="foto_despues" class="file-upload-button">
                                <span class="file-upload-icon">📷</span>
                                <span class="file-upload-text">Seleccionar Imagen</span>
                            </label>
                            <div class="file-upload-name" id="foto_despues_name">Ningún archivo seleccionado</div>
                        </div>
                    </div>
                </div>
                
                <div style="margin-top: 15px; padding-top: 15px; border-top: 1px dashed var(--border);">
                    <p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 10px;">IMÁGENES ADICIONALES (Opcional)</p>
                    <div class="grid-2">
                        <div class="form-group">
                            <label>Evidencia Extra (ANTES)</label>
                            <div class="file-upload-wrapper">
                                <input type="file" name="foto_antes2" id="foto_antes2" accept="image/*" class="file-input-hidden">
                                <label for="foto_antes2" class="file-upload-button">
                                    <span class="file-upload-icon">📷</span>
                                    <span class="file-upload-text">Seleccionar Imagen</span>
                                </label>
                                <div class="file-upload-name" id="foto_antes2_name">Ningún archivo seleccionado</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Evidencia Extra (DESPUÉS)</label>
                            <div class="file-upload-wrapper">
                                <input type="file" name="foto_despues2" id="foto_despues2" accept="image/*" class="file-input-hidden">
                                <label for="foto_despues2" class="file-upload-button">
                                    <span class="file-upload-icon">📷</span>
                                    <span class="file-upload-text">Seleccionar Imagen</span>
                                </label>
                                <div class="file-upload-name" id="foto_despues2_name">Ningún archivo seleccionado</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div style="display: flex; gap: 15px; margin-top: 20px; flex-wrap: wrap;">
                <button type="button" onclick="guardarBorradorServidor()" style="flex: 1; padding: 15px; background: rgba(30, 41, 59, 0.8); border: 1px solid var(--border); color: var(--text); border-radius: 8px; font-weight: bold; cursor: pointer; transition: all 0.3s ease;" onmouseover="this.style.background='var(--primary)'; this.style.color='var(--bg)'" onmouseout="this.style.background='rgba(30, 41, 59, 0.8)'; this.style.color='var(--text)'">💾 Guardar Estado / Borrador</button>
                <button type="submit" class="submit-btn" id="btnFinalizar" style="flex: 2; margin-top: 0;">Finalizar y Enviar</button>
            </div>
        </form>
    </div>

    <script src="script.js"></script>
    <script src="guardado.js"></script>
</body>
</html>
