// Funcionalidad para guardar el estado del formulario de Mantenimiento V2
function guardarBorradorServidor() {
    const form = document.getElementById('mainForm');
    const formData = new FormData(form);
    const jsonData = {};
    
    console.log('=== GUARDANDO BORRADOR S.O.M. V2 ===');

    // Procesar todos los inputs normales
    for (const [key, value] of formData.entries()) {
        if (value instanceof File) continue;

        if (key.endsWith('[]')) {
            const cleanKey = key.slice(0, -2);
            if (!jsonData[cleanKey]) {
                jsonData[cleanKey] = [];
            }
            jsonData[cleanKey].push(value);
        } else {
            jsonData[key] = value;
        }
    }

    // Capturar SELECTs extra que puedan no haber sido añadidos por FormData (disabled info)
    const selects = form.querySelectorAll('select');
    selects.forEach(select => {
        const name = select.name || select.id;
        if (name && select.disabled) {
            if (name.endsWith('[]')) {
                const cleanKey = name.slice(0, -2);
                if (!jsonData[cleanKey]) jsonData[cleanKey] = [];
                jsonData[cleanKey].push(select.value);
            } else {
                if (!jsonData[name]) jsonData[name] = select.value;
            }
        }
    });

    const inputs = form.querySelectorAll('input:disabled');
    inputs.forEach(input => {
        if(input.type === 'file') return;
        const name = input.name || input.id;
        if(name) {
            if (name.endsWith('[]')) {
                const cleanKey = name.slice(0, -2);
                if (!jsonData[cleanKey]) jsonData[cleanKey] = [];
                jsonData[cleanKey].push(input.value);
            } else {
                if (!jsonData[name]) jsonData[name] = input.value;
            }
        }
    });

    // Capturar firmas
    let canvasGuardados = 0;
    ['canvas_solicitante', 'canvas_autorizado', 'canvas_limpieza', 'canvas_revisa_limpieza'].forEach(id => {
        const input = document.getElementById(id + '_input');
        const canvas = document.getElementById(id);
        
        if (canvas && input) {
            const hasDrawing = !isCanvasBlank(canvas);
            if (hasDrawing) {
                jsonData[input.name] = canvas.toDataURL('image/png');
                canvasGuardados++;
            }
        }
    });

    console.log('Datos procesados:', jsonData, 'Firmas guardadas:', canvasGuardados);

    fetch('guardar_borrador.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(jsonData)
    })
    .then(async response => {
        const text = await response.text();
        try {
            return JSON.parse(text);
        } catch (e) {
            console.error('La respuesta no es un JSON válido:', text);
            throw new Error('Servidor retornó formato inválido');
        }
    })
    .then(data => {
        if (data.success) {
            alert('✅ Borrador guardado exitosamente.\n\nArchivo: ' + data.archivo);
        } else {
            alert('❌ Error del servidor: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error al guardar:', error);
        alert('❌ Error al conectar con el servidor: ' + error.message);
    });
}

// Función para cargar borrador automático
function cargarBorradorAutomaticoV2() {
    const nombreArchivo = localStorage.getItem('cargarBorradorV2');
    if (!nombreArchivo) return;

    console.log('=== CARGANDO BORRADOR V2 AUTOMÁTICO ===', nombreArchivo);

    fetch('cargar_borrador.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ archivo: nombreArchivo })
    })
    .then(response => response.json())
    .then(data => {
        if (!data || data.success === false) {
            console.error('Error al cargar datos:', data ? data.message : 'Respuesta vacía');
            localStorage.removeItem('cargarBorradorV2');
            return;
        }

        const form = document.getElementById('mainForm');
        
        // 1. Manejar Seccion Mediciones (Predictivo)
        if (data.usa_mediciones === "1") {
            if (typeof window.toggleMediciones === 'function') {
                window.toggleMediciones();
            }
        }

        // 2. Poblar inputs simples
        for (let key in data) {
            if (Array.isArray(data[key])) continue; // Saltamos arrays para el siguiente paso

            const element = form.querySelector(`[name="${key}"]`);
            if (element) {
                if (element.type === 'checkbox') {
                    element.checked = (data[key] === "on" || data[key] === true);
                } else if (element.type === 'radio') {
                    // Manejar radios si existen
                } else {
                    element.value = data[key];
                }
            }
        }

        // 3. Manejar Arrays y Tablas Dinámicas
        const tablasConfig = {
            'med_equipo_name': 'add_medicion_btn', // Para mediciones el boton tiene ID
            'tool_cant': 'table_tools',
            'part_cant': 'table_parts',
            'mat_cant': 'table_materials'
        };

        for (let key in tablasConfig) {
            if (data[key] && Array.isArray(data[key])) {
                const valuesCount = data[key].length;
                
                // Si es la tabla de mediciones
                if (key === 'med_equipo_name') {
                    const btn = document.getElementById('add_medicion_btn');
                    // La primera fila ya existe, agregar N-1 filas adicionales
                    for (let i = 1; i < valuesCount; i++) {
                        btn.click();
                    }
                } else {
                    const tableId = tablasConfig[key];
                    // La primera fila ya existe, agregar N-1 filas
                    for (let i = 1; i < valuesCount; i++) {
                        if (typeof window.addRow === 'function') {
                            window.addRow(tableId);
                        }
                    }
                }

                // Ahora llenar los datos de esa tabla
                // Buscamos todos los campos que terminan en [] y empiezan con el prefijo
                const inputs = form.querySelectorAll(`input[name^="${key.split('_')[0]}"], select[name^="${key.split('_')[0]}"]`);
                // Esto es un poco genérico, mejor iterar por las claves del JSON que sabemos que son arrays
            }
        }

        // Llenado específico de arrays para asegurar orden
        Object.keys(data).forEach(key => {
            if (Array.isArray(data[key])) {
                const inputs = form.querySelectorAll(`[name="${key}[]"]`);
                data[key].forEach((val, index) => {
                    if (inputs[index]) {
                        inputs[index].value = val;
                    }
                });
            }
        });

        // 4. Restaurar Firmas
        const firmas = {
            'firma_solicitante': 'canvas_solicitante',
            'firma_autorizado': 'canvas_autorizado',
            'firma_respLim': 'canvas_limpieza',
            'firma_respLim2': 'canvas_revisa_limpieza'
        };

        for (let inputName in firmas) {
            const base64 = data[inputName];
            const canvasId = firmas[inputName];
            if (base64 && base64.startsWith('data:image')) {
                const canvas = document.getElementById(canvasId);
                const input = document.getElementById(canvasId + '_input');
                if (canvas) {
                    const ctx = canvas.getContext('2d');
                    const img = new Image();
                    img.onload = function() {
                        ctx.clearRect(0, 0, canvas.width, canvas.height);
                        ctx.drawImage(img, 0, 0);
                    };
                    img.src = base64;
                    if (input) input.value = base64;
                }
            }
        }

        // Limpiar el disparador
        localStorage.removeItem('cargarBorradorV2');
        console.log('✅ Borrador V2 cargado con éxito');
    })
    .catch(err => {
        console.error('Error en fetch de carga:', err);
        localStorage.removeItem('cargarBorradorV2');
    });
}

// Aux de verificación de blanqueo en el Canvas
function isCanvasBlank(canvas) {
    const context = canvas.getContext('2d');
    const pixelBuffer = new Uint32Array(
        context.getImageData(0, 0, canvas.width, canvas.height).data.buffer
    );
    return !pixelBuffer.some(color => color !== 0);
}

// Inicializar carga al cargar el DOM
document.addEventListener('DOMContentLoaded', () => {
    setTimeout(cargarBorradorAutomaticoV2, 500); // Pequeño delay para asegurar que script.js cargó los manejadores
});
