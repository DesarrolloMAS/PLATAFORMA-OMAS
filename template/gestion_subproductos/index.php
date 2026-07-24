<?php
require_once '../sesion.php';
require_once '../conection.php'; // Optional if we need to fetch users, similar to orden_mantenimiento

verificarAutenticacion();

// Fetch users for the select dropdown
function obtenerUsuarios($pdoUsuarios) {
    if(!$pdoUsuarios) return [];
    try {
        $stmt = $pdoUsuarios->query("SELECT DISTINCT nombre_u FROM usuarios ORDER BY nombre_u ASC");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch(Exception $e) {
        return [];
    }
}

$usuarios = obtenerUsuarios($pdoUsuarios ?? null);
$nombre_usuario = $_SESSION['nombre'] ?? '';
$sede_usuario = $_SESSION['sede'] ?? '';

// Definir los items a evaluar
$items_evaluar = [
    "item_1" => "Directriz de Subproductos (debidamente firmada por la Alta Dirección)",
    "item_2" => "Subproductos y líneas de origen (debidamente identificados y documentados)",
    "item_3" => "Uso previsto y Consumo (de los subproductos, claramente definidos y documentados)",
    "item_4" => "Rutas de circulación de Subproductos (debidamente documentadas y actualizadas)",
    "item_5" => "Productos con marca del cliente (se cuenta con medidas para llevar a cabo operaciones de \"maquila\")",
    "item_6" => "Prevención de la contaminación (se aplican las medidas pertinentes para prevenir la contaminación de subproductos)",
    "item_7" => "Control de PNC (se aplican medidas para controlar subproductos NO CONFORMES)",
    "item_8" => "Trazabilidad (el sistema de trazabilidad permite rastrear todo origen y destino de lotes de subproductos)"
];

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Subproductos - Evaluación</title>
    <link rel="stylesheet" href="../../css/orden_mantenimiento.css">
    <style>
        .evaluation-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            margin-bottom: 15px;
        }
        .evaluation-table th, .evaluation-table td {
            border: 1px solid var(--border, #333);
            padding: 10px;
            text-align: left;
            color: var(--text-color, #e0e0e0);
        }
        .evaluation-table th {
            background-color: rgba(0, 240, 255, 0.1);
            color: var(--primary, #00f0ff);
            text-align: center;
        }
        .evaluation-table select {
            width: 100%;
            padding: 8px;
            background: #111;
            border: 1px solid var(--border);
            color: #fff;
        }
        .summary-box {
            background: rgba(0, 240, 255, 0.05);
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <header style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1>Gestión de Subproductos</h1>
                <p>Formato de Evaluación y Control</p>
            </div>
            <a href="../menu_seccion_sur.html" class="btn-view" style="text-decoration: none;">Volver</a>
        </header>

        <form id="mainForm" action="procesar.php" method="POST">
            
            <div class="section-card">
                <div class="section-title">Información General</div>
                <div class="grid-2">
                    <div class="form-group">
                        <label>Sede Actual</label>
                        <input type="text" value="<?php echo $sede_usuario; ?>" readonly style="color: var(--primary); font-weight: bold;">
                    </div>
                    <div class="form-group">
                        <label>Fecha de Inspección</label>
                        <input type="date" name="fecha_inspeccion" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>
            </div>

            <div class="section-card">
                <div class="section-title">Ítems a Evaluar</div>
                <div class="table-responsive">
                    <table class="evaluation-table">
                        <thead>
                            <tr>
                                <th>Criterio a Evaluar</th>
                                <th style="width: 150px;">Evaluación (1=Cumple, 0=No Cumple)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($items_evaluar as $key => $label): ?>
                            <tr>
                                <td><?php echo $label; ?></td>
                                <td>
                                    <select name="evaluaciones[<?php echo $key; ?>]" class="eval-select" required onchange="calcularTotales()">
                                        <option value="">-- Seleccione --</option>
                                        <option value="1">Cumple (1)</option>
                                        <option value="0">No Cumple (0)</option>
                                        <option value="NA">N/A</option>
                                    </select>
                                    <input type="hidden" name="label_<?php echo $key; ?>" value="<?php echo htmlspecialchars($label); ?>">
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="summary-box">
                    <div class="grid-2">
                        <div class="form-group">
                            <label>Total (Suma de Ítems)</label>
                            <input type="number" name="total_suma" id="total_suma" readonly required style="font-weight: bold; color: var(--primary);">
                        </div>
                        <div class="form-group">
                            <label>% de Cumplimiento</label>
                            <input type="text" name="porcentaje_cumplimiento" id="porcentaje_cumplimiento" readonly required style="font-weight: bold; color: var(--primary);">
                        </div>
                    </div>
                </div>
                
                <div class="grid-2" style="margin-top:20px;">
                    <div class="form-group">
                        <label>Responsable de Inspección</label>
                        <?php if(!empty($usuarios)): ?>
                            <select name="responsable_inspeccion" required>
                                <option value="">-- Seleccionar --</option>
                                <?php foreach($usuarios as $user): ?>
                                    <option value="<?php echo $user; ?>" <?php echo ($user == $nombre_usuario) ? 'selected' : ''; ?>><?php echo $user; ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php else: ?>
                            <input type="text" name="responsable_inspeccion" value="<?php echo $nombre_usuario; ?>" required>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN: HALLAZGO Y OBSERVACIÓN -->
            <div class="section-card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                    <div class="section-title" style="margin: 0; border: none; padding: 0;">Hallazgo y Observación (Opcional)</div>
                    <button type="button" style="padding: 5px 15px; background: transparent; border: 1px solid var(--primary); color: var(--primary); border-radius: 4px; cursor: pointer;" onclick="document.getElementById('hallazgosPanel').style.display='block'; this.style.display='none';">Registrar Hallazgo</button>
                </div>
                
                <div id="hallazgosPanel" style="display: none; background: rgba(0, 240, 255, 0.05); padding: 15px; border-radius: 8px; border: 1px dashed var(--border); margin-top: 10px;">
                    <div class="grid-2">
                        <div class="form-group">
                            <label>Fecha del Hallazgo</label>
                            <input type="date" name="hallazgo_fecha" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="form-group">
                            <label>Responsable Inspección</label>
                            <?php if(!empty($usuarios)): ?>
                                <select name="hallazgo_resp_ins">
                                    <option value="">-- Seleccionar --</option>
                                    <?php foreach($usuarios as $user): ?>
                                        <option value="<?php echo $user; ?>" <?php echo ($user == $nombre_usuario) ? 'selected' : ''; ?>><?php echo $user; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php else: ?>
                                <input type="text" name="hallazgo_resp_ins" value="<?php echo htmlspecialchars($nombre_usuario); ?>">
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="grid-2">
                        <div class="form-group">
                            <label>Hallazgos / Observaciones</label>
                            <textarea name="hallazgo_obs" rows="3" placeholder="Describa el hallazgo..."></textarea>
                        </div>
                        <div class="form-group">
                            <label>Responsable Verificación</label>
                            <?php if(!empty($usuarios)): ?>
                                <select name="hallazgo_resp_verif">
                                    <option value="">-- Seleccionar --</option>
                                    <?php foreach($usuarios as $user): ?>
                                        <option value="<?php echo $user; ?>"><?php echo $user; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php else: ?>
                                <input type="text" name="hallazgo_resp_verif" placeholder="Nombre completo">
                            <?php endif; ?>
                        </div>
                    </div>
                    <div style="text-align: right; margin-top: 10px;">
                        <button type="button" onclick="document.getElementById('hallazgosPanel').style.display='none'; this.closest('.section-card').querySelector('button[onclick^=document.getElementById]').style.display='block'; document.querySelector('textarea[name=hallazgo_obs]').value='';" style="background: transparent; border: none; color: #ff4444; cursor: pointer; font-size: 0.9em;">✕ Cancelar/Eliminar Hallazgo</button>
                    </div>
                </div>
            </div>

            <button type="submit" class="submit-btn" id="btnFinalizar">Guardar Evaluación</button>
        </form>
    </div>

    <script>

        function calcularTotales() {
            let selects = document.querySelectorAll('.eval-select');
            let suma = 0;
            let itemsEvaluados = 0;

            selects.forEach(sel => {
                const val = sel.value;
                if (val === '1' || val === '0') {
                    suma += parseInt(val);
                    itemsEvaluados += 1;
                }
            });

            document.getElementById('total_suma').value = suma;

            let porcentaje = 0;
            if (itemsEvaluados > 0) {
                porcentaje = (suma / itemsEvaluados) * 100;
            }
            document.getElementById('porcentaje_cumplimiento').value = porcentaje.toFixed(2) + '%';
        }

        document.getElementById('mainForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            fetch('procesar.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert(data.message);
                    this.reset();
                    calcularTotales();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Ocurrió un error al procesar la solicitud.');
            });
        });
    </script>
</body>
</html>
