<?php
require_once '../sesion.php';
$sede = $_SESSION['sede'] ?? '';
$ubicacion = $_GET['ubicacion'] ?? '';

// Cargar la configuración para obtener el nombre real de la ubicación
$nombreUbi = $ubicacion;
$configFile = "../../archivos/generados/termohigrometros/config_ubicaciones_{$sede}.json";
if (file_exists($configFile)) {
    $ubis = json_decode(file_get_contents($configFile), true) ?: [];
    foreach ($ubis as $u) {
        if ($u['id'] === $ubicacion) {
            $nombreUbi = $u['nombre'];
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TERMOHIGROMETROS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=Barlow+Condensed:wght@300;400;600;700;900&family=Barlow:wght@300;400;500&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="/css/termohigrometros.css">
</head>

<body>
    <div class="form-wrapper">
        <div class="form-header">
            <h1>Control de <span>Termohigrómetro</span></h1>
            <div style="color:#6ee7b7; font-family:'Space Mono', monospace; font-size:16px; margin-top:5px; text-transform:uppercase;">
                UBICACIÓN: <?= htmlspecialchars($nombreUbi) ?>
            </div>
            <div class="header-meta">
                <span>Programa de Produccion</span>
                <button type="button" class="submit-btn" onclick="window.history.back()" style="padding: 8px 20px; font-size: 12px; margin-left: auto;">Volver</button>
            </div>
        </div>

        <form id="formTermo">
            <div class="section-card">
                <div class="section-header">
                    <h2>Información de Registro</h2>
                </div>
                <div class="section-body">
                    <div class="fields-row">
                        <div class="field-group">
                            <label for="fecha">Fecha</label>
                            <input type="date" id="fecha" required>
                        </div>
                        <div class="field-group">
                            <label for="hora">Hora</label>
                            <input type="time" id="hora" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="section-card">
                <div class="section-header">
                    <h2>Parámetros Actuales</h2>
                </div>
                <div class="section-body">
                    <div class="fields-row">
                        <div class="field-group">
                            <label for="temparatura">Temperatura (°C)</label>
                            <input type="number" id="temparatura" step="any" required>
                        </div>
                        <div class="field-group">
                            <label for="humedad">Humedad (%)</label>
                            <input type="text" id="humedad" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="section-card">
                <div class="section-header">
                    <h2>Rangos Críticos</h2>
                </div>
                <div class="section-body">
                    <div class="fields-row">
                        <div class="field-group">
                            <label for="temperaturamax">Temp. Máxima</label>
                            <input type="text" id="temperaturamax" required>
                        </div>
                        <div class="field-group">
                            <label for="temperaturamin">Temp. Mínima</label>
                            <input type="text" id="temperaturamin" required>
                        </div>
                    </div>
                    <div class="fields-row">
                        <div class="field-group">
                            <label for="humedadmax">Humedad Máxima</label>
                            <input type="text" id="humedadmax" required>
                        </div>
                        <div class="field-group">
                            <label for="humedadmin">Humedad Mínima</label>
                            <input type="text" id="humedadmin" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="section-card">
                <div class="section-header">
                    <h2>Verificación</h2>
                </div>
                <div class="section-body">
                    <div class="field-group">
                        <label for="verificacion">Observaciones / Verificación</label>
                        <input type="text" id="verificacion" required>
                    </div>
                </div>
            </div>

            <div class="form-footer">
                <button type="submit" class="submit-btn">Enviar Registro</button>
            </div>
        </form>
    </div>

    <script>
        document.getElementById('formTermo').addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = {
                ubicacion_id: '<?= htmlspecialchars($ubicacion) ?>',
                ubicacion_nombre: '<?= htmlspecialchars($nombreUbi) ?>',
                fecha: document.getElementById('fecha').value,
                hora: document.getElementById('hora').value,
                temperatura: document.getElementById('temparatura').value,
                humedad: document.getElementById('humedad').value,
                humedad_max: document.getElementById('humedadmax').value,
                temp_max: document.getElementById('temperaturamax').value,
                humedad_min: document.getElementById('humedadmin').value,
                temp_min: document.getElementById('temperaturamin').value,
                verificacion: document.getElementById('verificacion').value
            };

            console.log('Datos a enviar:', formData);

            fetch('procesar.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            })
                .then(response => response.json())
                .then(data => {
                    console.log('Respuesta:', data);
                    if (data.success) {
                        alert('Datos guardados correctamente para ' + formData.ubicacion_nombre);
                        window.location.href = 'index.php';
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Ocurrió un error al enviar los datos.');
                });
        });
    </script>
</body>

</html>