function guardarBorradorServidor() {
    const formData = {};
    const form = document.getElementById('formulario1');
    const formElements = form.elements;

    console.log('=== GUARDANDO BORRADOR ===');
    console.log('Total de elementos en formulario:', formElements.length);

    // Recopilar todos los campos del formulario
    for (let element of formElements) {
        if (element.name && element.type !== 'file' && element.type !== 'submit' && element.type !== 'button') {
            console.log(`Procesando elemento: ${element.name}, tipo: ${element.type}, tagName: ${element.tagName}, valor: ${element.value}`);
            
            if (element.type === 'checkbox') {
                formData[element.name] = element.checked;
                console.log(`Checkbox ${element.name}: ${element.checked}`);
            } else if (element.type === 'radio') {
                if (element.checked) {
                    formData[element.name] = element.value;
                    console.log(`Radio ${element.name}: ${element.value}`);
                }
            } else if (element.tagName === 'SELECT') {
                // Para SELECT guardamos tanto el value como el selectedIndex para mayor confiabilidad
                formData[element.name] = {
                    value: element.value,
                    selectedIndex: element.selectedIndex,
                    selectedText: element.selectedIndex >= 0 ? element.options[element.selectedIndex].text : ''
                };
                console.log(`SELECT ${element.name}: value="${element.value}", selectedIndex=${element.selectedIndex}, text="${formData[element.name].selectedText}"`);
            } else {
                formData[element.name] = element.value;
                console.log(`Input ${element.name}: ${element.value}`);
            }
        }
    }

    // También capturar SELECT que puedan no tener name pero sí id
    const selects = form.querySelectorAll('select');
    for (let select of selects) {
        const name = select.name || select.id;
        if (name && !formData.hasOwnProperty(name)) {
            formData[name] = {
                value: select.value,
                selectedIndex: select.selectedIndex,
                selectedText: select.selectedIndex >= 0 ? select.options[select.selectedIndex].text : ''
            };
            console.log(`SELECT adicional ${name}: value="${select.value}", selectedIndex=${select.selectedIndex}, text="${formData[name].selectedText}"`);
        }
    }

    // Guardar firmas
    formData['firma_solicitante'] = document.getElementById('firma_solicitante').value;
    formData['firma_autorizado'] = document.getElementById('firma_autorizado').value;
    formData['firma_respLim'] = document.getElementById('firma_respLim').value;
    formData['firma_respLim2'] = document.getElementById('firma_respLim2').value;

    console.log('Datos finales a guardar:', formData);
    console.log('Cantidad total de campos:', Object.keys(formData).length);

    // Enviar al servidor
    fetch('guardado.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Borrador guardado en el servidor correctamente');
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error al guardar: ' + error.message);
    });
}
// Función para cargar borrador desde JSON
function cargarBorrador() {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = '.json';
    
    input.onchange = function(e) {
        const file = e.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function(event) {
            try {
                const formData = JSON.parse(event.target.result);
                const form = document.getElementById('formulario1');

                // Rellenar campos del formulario
                for (let key in formData) {
                    const elements = form.elements[key];
                    if (elements) {
                        // Si es una NodeList (radio buttons con el mismo name)
                        if (elements.length > 1) {
                            for (let i = 0; i < elements.length; i++) {
                                const element = elements[i];
                                if (element.type === 'radio') {
                                    element.checked = (element.value === formData[key]);
                                } else if (element.type === 'checkbox') {
                                    element.checked = formData[key];
                                } else if (element.tagName === 'SELECT') {
                                    // Lógica especial para selects en NodeList - NO usar element.value
                                    let found = false;
                                    for (let j = 0; j < element.options.length; j++) {
                                        if (element.options[j].value === formData[key]) {
                                            element.selectedIndex = j;
                                            found = true;
                                            break;
                                        }
                                    }
                                    // Si no se encontró por valor, buscar por texto
                                    if (!found) {
                                        for (let j = 0; j < element.options.length; j++) {
                                            if (element.options[j].text.trim() === formData[key]) {
                                                element.selectedIndex = j;
                                                break;
                                            }
                                        }
                                    }
                                } else {
                                    // NO usar element.value para SELECT
                                    if (element.tagName !== 'SELECT') {
                                        element.value = formData[key];
                                    }
                                }
                            }
                        } 
                        // Si es un elemento único
                        else {
                            const element = elements.length ? elements[0] : elements;
                            if (element.type === 'checkbox') {
                                element.checked = formData[key];
                            } else if (element.type === 'radio') {
                                element.checked = (element.value === formData[key]);
                            } else if (element.tagName === 'SELECT') {
                                // Lógica especial para selects - NO usar element.value
                                let found = false;
                                for (let i = 0; i < element.options.length; i++) {
                                    if (element.options[i].value === formData[key]) {
                                        element.selectedIndex = i;
                                        found = true;
                                        break;
                                    }
                                }
                                // Si no se encontró por valor, buscar por texto
                                if (!found) {
                                    for (let i = 0; i < element.options.length; i++) {
                                        if (element.options[i].text.trim() === formData[key]) {
                                            element.selectedIndex = i;
                                            break;
                                        }
                                    }
                                }
                            } else {
                                // NO usar element.value para SELECT
                                if (element.tagName !== 'SELECT') {
                                    element.value = formData[key];
                                }
                            }
                        }
                    }
                }

                // Restaurar firmas
                ['firma_solicitante', 'firma_autorizado', 'firma_respLim', 'firma_respLim2'].forEach((firma, index) => {
                    const canvasId = ['canvasfirma1', 'canvasfirma2', 'canvasfirma3', 'canvasfirma4'][index];
                    if (formData[firma]) {
                        const canvas = document.getElementById(canvasId);
                        const ctx = canvas.getContext('2d');
                        const img = new Image();
                        img.onload = function() {
                            ctx.clearRect(0, 0, canvas.width, canvas.height);
                            ctx.drawImage(img, 0, 0);
                        };
                        img.src = formData[firma];
                        document.getElementById(firma).value = formData[firma];
                    }
                });

                alert('Borrador cargado correctamente');
            } catch (error) {
                alert('Error al cargar el borrador: ' + error.message);
            }
        };
        reader.readAsText(file);
    };
    
    input.click();
}
// Función para cargar SELECT usando el nuevo formato de objeto
function cargarSelectConObjeto(element, value, key) {
    console.log(`🔧 cargarSelectConObjeto: ${key}`, value);
    
    // Si value es un objeto con nuestro nuevo formato
    if (typeof value === 'object' && value !== null && value.hasOwnProperty('selectedIndex')) {
        console.log(`📋 Formato objeto detectado para ${key}:`, value);
        
        // Método 1: Intentar usar selectedIndex (más confiable)
        if (value.selectedIndex >= 0 && value.selectedIndex < element.options.length) {
            element.selectedIndex = value.selectedIndex;
            console.log(`✅ ${key}: selectedIndex establecido a ${value.selectedIndex}`);
            console.log(`   Texto seleccionado: "${element.options[value.selectedIndex].text}"`);
            console.log(`   Valor seleccionado: "${element.options[value.selectedIndex].value}"`);
            return;
        }
        
        // Método 2: Si selectedIndex falla, intentar por value
        if (value.value) {
            element.value = value.value;
            console.log(`🔄 ${key}: intentando por valor "${value.value}"`);
            if (element.value === value.value) {
                console.log(`✅ ${key}: valor establecido correctamente`);
                return;
            }
        }
        
        // Método 3: Si todo falla, intentar por texto
        if (value.selectedText) {
            for (let i = 0; i < element.options.length; i++) {
                if (element.options[i].text === value.selectedText) {
                    element.selectedIndex = i;
                    console.log(`✅ ${key}: encontrado por texto "${value.selectedText}" en índice ${i}`);
                    return;
                }
            }
        }
        
        console.log(`❌ ${key}: No se pudo establecer la selección`, value);
    } 
    // Si value es un string simple (compatibilidad hacia atrás)
    else {
        console.log(`📋 Formato simple detectado para ${key}: "${value}"`);
        element.value = value;
        console.log(`${element.value === value ? '✅' : '❌'} ${key}: valor ${element.value === value ? 'establecido' : 'falló'}`);
    }
}

// Función separada para cargar borrador automáticamente
function cargarBorradorAutomatico() {
    const nombreArchivo = localStorage.getItem('cargarBorrador');
    
    if (nombreArchivo) {
        console.log('Iniciando carga de borrador:', nombreArchivo);
        
        // Verificar que el formulario exista
        const form = document.getElementById('formulario1');
        if (!form) {
            console.error('Formulario no encontrado');
            setTimeout(cargarBorradorAutomatico, 500); // Reintentar en 500ms
            return;
        }
        
        // Solicitar el contenido del archivo al servidor
        fetch('cargar_borrador.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ archivo: nombreArchivo })
        })
        .then(response => response.json())
        .then(formData => {
            console.log('Datos recibidos del servidor:', formData);
            
            // Verificar si hay error
            if (formData.success === false) {
                alert('Error: ' + formData.message);
                localStorage.removeItem('cargarBorrador');
                return;
            }
            
            const form = document.getElementById('formulario1');
            console.log('Formulario encontrado:', form);

            // Primero, intentar cargar por form.elements
            console.log('=== CARGANDO POR FORM.ELEMENTS ===');
            for (let key in formData) {
                const value = formData[key];
                const elements = form.elements[key];
                console.log(`Procesando campo: ${key}`, value);
                
                if (elements) {
                    // Si es una NodeList (radio buttons con el mismo name)
                    if (elements.length > 1) {
                        console.log(`Campo ${key} es NodeList con ${elements.length} elementos`);
                        for (let i = 0; i < elements.length; i++) {
                            const element = elements[i];
                            if (element.type === 'radio') {
                                const radioValue = typeof value === 'object' ? value.value : value;
                                element.checked = (element.value === radioValue);
                                console.log(`Radio ${key}[${i}]: value=${element.value}, checked=${element.checked}`);
                            } else if (element.type === 'checkbox') {
                                element.checked = typeof value === 'object' ? value.value : value;
                            } else if (element.tagName === 'SELECT') {
                                console.log(`📋 SELECT ${key}[${i}]: cargando datos`);
                                cargarSelectConObjeto(element, value, key);
                            } else {
                                element.value = typeof value === 'object' ? value.value : value;
                            }
                        }
                    } 
                    // Si es un elemento único
                    else {
                        const element = elements.length ? elements[0] : elements;
                        console.log(`Campo ${key} es elemento único:`, element);
                        
                        if (element.type === 'checkbox') {
                            element.checked = typeof value === 'object' ? value.value : value;
                        } else if (element.type === 'radio') {
                            const radioValue = typeof value === 'object' ? value.value : value;
                            element.checked = (element.value === radioValue);
                        } else if (element.tagName === 'SELECT') {
                            console.log(`📋 SELECT ${key}: cargando datos`);
                            cargarSelectConObjeto(element, value, key);
                        } else {
                            element.value = typeof value === 'object' ? value.value : value;
                        }
                    }
                } else {
                    console.log(`Elemento no encontrado para campo: ${key}`);
                }
            }
            
            // Segundo intento: buscar por ID o name directamente
            console.log('=== CARGANDO POR ID/NAME DIRECTO ===');
            for (let key in formData) {
                const value = formData[key];
                
                // Buscar por name
                let element = form.querySelector(`[name="${key}"]`);
                
                // Si no se encontró por name, buscar por id
                if (!element) {
                    element = document.getElementById(key);
                }
                
                if (element && element.tagName === 'SELECT') {
                    console.log(`📋 SELECT ${key}: búsqueda directa - cargando datos`);
                    cargarSelectConObjeto(element, value, key);
                }
            }

            // Restaurar firmas
            ['firma_solicitante', 'firma_autorizado', 'firma_respLim', 'firma_respLim2'].forEach((firma, index) => {
                const canvasId = ['canvasfirma1', 'canvasfirma2', 'canvasfirma3', 'canvasfirma4'][index];
                if (formData[firma]) {
                    const canvas = document.getElementById(canvasId);
                    if (canvas) {
                        const ctx = canvas.getContext('2d');
                        const img = new Image();
                        img.onload = function() {
                            ctx.clearRect(0, 0, canvas.width, canvas.height);
                            ctx.drawImage(img, 0, 0);
                        };
                        img.src = formData[firma];
                        document.getElementById(firma).value = formData[firma];
                    }
                }
            });

            console.log('Carga completada');
            alert('Borrador cargado correctamente');
            
            // Limpiar el localStorage
            localStorage.removeItem('cargarBorrador');
        })
        .catch(error => {
            alert('Error al cargar el borrador: ' + error.message);
            localStorage.removeItem('cargarBorrador');
        });
    }
}

// Función mejorada para inicialización automática
function inicializarCargaAutomatica() {
    console.log('=== INICIANDO CARGA AUTOMATICA ===');
    
    // Verificar localStorage
    const nombreArchivo = localStorage.getItem('cargarBorrador');
    console.log('Archivo en localStorage:', nombreArchivo);
    
    if (!nombreArchivo) {
        console.log('No hay archivo para cargar');
        return;
    }
    
    // Verificar que el formulario exista
    const form = document.getElementById('formulario1');
    if (!form) {
        console.log('Formulario no encontrado, reintentando en 1 segundo...');
        setTimeout(inicializarCargaAutomatica, 1000);
        return;
    }
    
    console.log('Formulario encontrado, procediendo con la carga...');
    cargarBorradorAutomatico();
}



// Inicialización múltiple con diferentes eventos
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOMContentLoaded event fired');
    setTimeout(inicializarCargaAutomatica, 100);
});

window.addEventListener('load', function() {
    console.log('Window load event fired');
    setTimeout(inicializarCargaAutomatica, 500);
});



// Backup con setTimeout
setTimeout(function() {
    console.log('Backup timeout ejecutado');
    inicializarCargaAutomatica();
}, 2000);