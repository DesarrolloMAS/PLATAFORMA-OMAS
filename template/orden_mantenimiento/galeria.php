 <?php
require_once '../sesion.php';
verificarAutenticacion();

$sede = $_SESSION['sede'] ?? 'NA';
$base_dir = "../../archivos/generados/orden_mantenimiento/" . $sede . "/";

$archivos_json = [];
if (is_dir($base_dir)) {
    $archivos_json = glob($base_dir . "*.json");
    rsort($archivos_json); // Mostrar más recientes primero
}

$es_admin = in_array($_SESSION['rol'] ?? '', ['adm', '1']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial - S.O.M. V2</title>
    <link rel="stylesheet" href="../../css/orden_mantenimiento.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Historial de Órdenes</h1>
            <p>Sede: <?php echo $sede; ?></p>
            <div style="margin-top: 20px; display: flex; gap: 10px; flex-wrap: wrap; justify-content: center;">
                <a href="index.php" style="color: var(--primary); text-decoration: none; border: 1px solid var(--primary); padding: 5px 15px; border-radius: 4px;">+ Nueva Orden</a>
                <a href="../menu_mantenimiento_adm.php" style="color: var(--text-muted, #7a8599); text-decoration: none; border: 1px solid var(--border, #2d324a); padding: 5px 15px; border-radius: 4px; font-size: 0.85rem; opacity: 0.7; transition: opacity 0.2s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.7'">← Menú</a>
            </div>
        </header>

        <?php if (empty($archivos_json)): ?>
            <div class="section-card" style="text-align: center;">
                <p>No se han encontrado registros para esta sede.</p>
            </div>
        <?php else: ?>
            <?php foreach ($archivos_json as $archivo): ?>
                <?php 
                $nombre_mes = basename($archivo, ".json"); 
                $data = json_decode(file_get_contents($archivo), true);
                $total = count($data);
                ?>
                <div class="section-card" id="card-<?php echo $nombre_mes; ?>">
                    <div class="section-title" style="display:flex;justify-content:space-between;align-items:center;">
                        <span>Periodo: <?php echo $nombre_mes; ?> (<?php echo $total; ?> registros)</span>
                        <div style="display:flex; gap:10px;">
                            <a href="visor_masivo.php?file=<?php echo $nombre_mes; ?>" target="_blank"
                                style="background:rgba(34,197,94,0.15);color:#22c55e;border:1px solid #22c55e;padding:4px 14px;border-radius:4px;cursor:pointer;font-size:0.8rem;font-weight:600;text-decoration:none;transition:background .2s;"
                                onmouseover="this.style.background='rgba(34,197,94,0.35)'"
                                onmouseout="this.style.background='rgba(34,197,94,0.15)'">
                                ⬇️ Descargar Periodo (PDF)
                            </a>
                            <?php if ($es_admin): ?>
                            <button
                                onclick="eliminarPeriodo('<?php echo $nombre_mes; ?>', <?php echo $total; ?>)"
                                style="background:rgba(239,68,68,0.15);color:#ef4444;border:1px solid #ef4444;padding:4px 14px;border-radius:4px;cursor:pointer;font-size:0.8rem;font-weight:600;transition:background .2s;"
                                onmouseover="this.style.background='rgba(239,68,68,0.35)'"
                                onmouseout="this.style.background='rgba(239,68,68,0.15)'">
                                🗑 Eliminar todo el período
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>N° Orden</th>
                                    <th>Fecha</th>
                                    <th>Equipo</th>
                                    <th>Solicitante</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data as $registro): ?>
                                    <?php $num_orden = $registro['datos']['numero_orden'] ?? $registro['id']; ?>
                                    <tr>
                                        <td><span style="background: linear-gradient(135deg, #f59e0b 0%, #ef4444 100%); color: white; padding: 3px 10px; border-radius: 12px; font-size: 0.8rem; font-weight: 600;"><?php echo htmlspecialchars($num_orden); ?></span></td>
                                        <td><?php echo date('d/m/Y', strtotime($registro['timestamp'])); ?></td>
                                        <td><?php echo $registro['datos']['objeto_dañado'] ?? 'N/A'; ?></td>
                                        <td><?php echo $registro['usuario_creador']; ?></td>
                                        <td style="white-space:nowrap;">
                                            <a href="visor.php?file=<?php echo $nombre_mes; ?>&id=<?php echo $registro['id']; ?>" class="btn-clear" style="text-decoration: none; display: inline-block; margin-right: 5px;">Ver Detalle</a>
                                            <a href="descargar_pdf.php?file=<?php echo $nombre_mes; ?>&id=<?php echo $registro['id']; ?>" class="btn-clear" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b; border: 1px solid #f59e0b; border-radius: 4px; padding: 5px 10px; text-decoration: none; display: inline-block; margin-right: 5px; font-size: 0.8rem; transition: 0.3s;" onmouseover="this.style.background='rgba(245, 158, 11, 0.3)'" onmouseout="this.style.background='rgba(245, 158, 11, 0.1)'">
                                                <span style="margin-right: 5px;">⬇️</span> Descargar PDF
                                            </a>
                                            <button onclick="cargarABitacora('<?php echo $nombre_mes; ?>', '<?php echo $registro['id']; ?>', this)" class="btn-clear" style="background: rgba(0, 240, 255, 0.1); color: #00F0FF; border: 1px solid #00F0FF; border-radius: 4px; padding: 5px 10px; cursor: pointer; transition: 0.3s; font-size: 0.8rem;" onmouseover="this.style.background='rgba(0, 240, 255, 0.3)'" onmouseout="this.style.background='rgba(0, 240, 255, 0.1)'">
                                                <span style="margin-right: 5px;">☁️</span> Cargar a Bitácora
                                            </button>
                                            <?php if ($es_admin): ?>
                                            <button
                                                onclick="eliminarOrden('<?php echo $nombre_mes; ?>', '<?php echo $registro['id']; ?>', '<?php echo htmlspecialchars($num_orden, ENT_QUOTES); ?>', this)"
                                                style="background:rgba(239,68,68,0.12);color:#ef4444;border:1px solid #ef4444;border-radius:4px;padding:5px 10px;cursor:pointer;font-size:0.8rem;transition:background .2s;"
                                                onmouseover="this.style.background='rgba(239,68,68,0.32)'"
                                                onmouseout="this.style.background='rgba(239,68,68,0.12)'">
                                                🗑 Eliminar
                                            </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script>
        // ── Eliminar una sola orden ───────────────────────────────────────────
        function eliminarOrden(mes, id, numOrden, btn) {
            if (!confirm('⚠️ ¿Eliminar la orden "' + numOrden + '"?\n\nEsta acción no se puede deshacer. Se eliminarán también sus firmas y evidencias fotográficas.')) return;

            btn.disabled = true;
            btn.textContent = '⏳';

            fetch('eliminar_orden.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ accion: 'una', mes: mes, id: id })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const fila = btn.closest('tr');
                    fila.style.transition = 'opacity .3s';
                    fila.style.opacity = '0';
                    setTimeout(() => {
                        fila.remove();
                        // Actualizar contador del período en el título
                        const card = document.getElementById('card-' + mes);
                        if (card) {
                            const tbody = card.querySelector('tbody');
                            const restantes = tbody ? tbody.querySelectorAll('tr').length : 0;
                            const titulo = card.querySelector('.section-title span');
                            if (titulo) titulo.textContent = 'Periodo: ' + mes + ' (' + restantes + ' registros)';
                            // Si no quedan filas, eliminar la tarjeta completa
                            if (restantes === 0) {
                                card.style.transition = 'opacity .3s';
                                card.style.opacity = '0';
                                setTimeout(() => card.remove(), 300);
                            }
                        }
                    }, 300);
                } else {
                    alert('❌ Error: ' + (data.error || 'Error desconocido'));
                    btn.disabled = false;
                    btn.textContent = '🗑 Eliminar';
                }
            })
            .catch(() => {
                alert('❌ Error de red.');
                btn.disabled = false;
                btn.textContent = '🗑 Eliminar';
            });
        }

        // ── Eliminar todo el período ──────────────────────────────────────────
        function eliminarPeriodo(mes, total) {
            if (!confirm('⚠️ ¿Eliminar TODOS los ' + total + ' registro(s) del período ' + mes + '?\n\nSe eliminarán permanentemente todas las órdenes, firmas y evidencias del período.\n\nEsta acción NO se puede deshacer.')) return;
            // Segunda confirmación por seguridad
            if (!confirm('🔴 CONFIRMAR: Eliminar el período completo "' + mes + '"\n\n¿Estás seguro?')) return;

            fetch('eliminar_orden.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ accion: 'periodo', mes: mes })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const card = document.getElementById('card-' + mes);
                    if (card) {
                        card.style.transition = 'opacity .4s';
                        card.style.opacity = '0';
                        setTimeout(() => card.remove(), 400);
                    }
                    alert('✅ ' + data.message);
                } else {
                    alert('❌ Error: ' + (data.error || 'Error desconocido'));
                }
            })
            .catch(() => alert('❌ Error de red.'));
        }

        // ── Cargar a bitácora ─────────────────────────────────────────────────
        function cargarABitacora(mes, id, btnElement) {
            if (!confirm('¿Deseas enviar este registro a la Bitácora de Mantenimiento?')) {
                return;
            }

            const originalText = btnElement.innerHTML;
            btnElement.innerHTML = '⏳ Cargando...';
            btnElement.disabled = true;

            fetch('enviar_a_bitacora.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ mes: mes, id: id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.exito) {
                    alert('✅ ' + data.mensaje);
                    btnElement.innerHTML = '✔️ Cargado';
                    btnElement.style.background = 'rgba(0, 255, 100, 0.2)';
                    btnElement.style.borderColor = '#00FF64';
                    btnElement.style.color = '#00FF64';
                } else {
                    alert('❌ Error: ' + data.mensaje);
                    btnElement.innerHTML = originalText;
                    btnElement.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ Error de red al intentar conectar con el servidor.');
                btnElement.innerHTML = originalText;
                btnElement.disabled = false;
            });
        }
    </script>
</body>
</html>
