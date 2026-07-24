<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MUESTRAS</title>
    <link rel="stylesheet" href="/css/muestras_form.css">
</head>
<body>

    <header>
        <h1>CONTROL INGRESO Y SALIDA DE <span>MUESTRAS</span></h1>
    </header>

    <div class="header-line"></div>

    <div class="form-wrapper">
        <form action="procesar.php" method="post" id="muestrasForm">

            <div class="form-topbar">
                <div class="status-dot">SISTEMA ACTIVO</div>
                <div class="form-id">FORM-MUE</div>
            </div>
            <div class="form-section">
                <div class="section-label">
                    <h2>Información General</h2>
                </div>
                <div class="fields-grid">
                    <div class="field">
                        <label for="hora">Hora de Registro</label>
                        <input type="time" name="hora" id="hora">
                    </div>
                    <div class="field">
                        <label for="producto">Producto</label>
                        <input type="text" name="producto" id="producto" placeholder="Nombre del producto">
                    </div>
                    <div class="field field-full">
                        <label for="lote">Número de Lote</label>
                        <input type="text" name="lote" id="lote" placeholder="Ej. MP-8746B">
                    </div>
                </div>
            </div>
            <div class="form-section">
                <div class="section-label">
                    <h2>Datos de Muestreo</h2>
                </div>
                <div class="fields-grid">
                    <div class="field">
                        <label for="fecha_muestreo">Fecha de Muestreo</label>
                        <input type="date" name="fecha_muestreo" id="fecha_muestreo">
                    </div>
                    <div class="field">
                        <label for="hora_muestreo">Hora de Muestreo</label>
                        <input type="time" name="hora_muestreo" id="hora_muestreo">
                    </div>
                    <div class="field field-full">
                        <label for="responsable_muestra">Responsable de la Toma</label>
                        <input type="text" name="responsable_muestra" id="responsable_muestra" placeholder="Nombre del responsable">
                    </div>
                    <div class="field">
                        <label for="cantidad">Cantidad (g)</label>
                        <input type="text" name="cantidad" id="cantidad" placeholder="0.00">
                    </div>
                </div>
            </div>

            <div class="form-footer">
                <span class="footer-note">Todos los campos son requeridos</span>
                <input type="submit" value="Guardar Registro">
                <input type="button" value="Volver" onclick="window.history.back();">
            </div>

        </form>
    </div>

    <script>
        document.getElementById('muestrasForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const form = e.target;
            const submitBtn = form.querySelector('input[type="submit"]');
            const oldText = submitBtn.value;
            submitBtn.value = 'Guardando...';
            submitBtn.disabled = true;

            fetch(form.action, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: new FormData(form)
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    // Misma redirección que el botón "Volver"
                    window.history.back();
                } else {
                    alert('Error al guardar el registro: ' + (res.error || 'Error desconocido'));
                    submitBtn.value = oldText;
                    submitBtn.disabled = false;
                }
            })
            .catch(() => {
                alert('Error conectando al servidor.');
                submitBtn.value = oldText;
                submitBtn.disabled = false;
            });
        });
    </script>

</body>
</html>