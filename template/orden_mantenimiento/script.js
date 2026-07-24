// S.O.M. V2 - Terminal Logic
document.addEventListener('DOMContentLoaded', () => {
    // Signature Handling
    const setupCanvas = (id) => {
        const canvas = document.getElementById(id);
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        let painting = false;

        const startPosition = (e) => {
            painting = true;
            draw(e);
        };

        const finishedPosition = () => {
            painting = false;
            ctx.beginPath(); // Fix: beginPath() en lugar de beginTransaction()
        };

        const draw = (e) => {
            if (!painting) return;
            ctx.lineWidth = 2;
            ctx.lineCap = 'round';
            ctx.strokeStyle = 'black';

            const rect = canvas.getBoundingClientRect();
            let clientX = e.clientX;
            let clientY = e.clientY;

            // Soporte seguro para eventos táctiles
            if (e.touches && e.touches.length > 0) {
                clientX = e.touches[0].clientX;
                clientY = e.touches[0].clientY;
            } else if (e.changedTouches && e.changedTouches.length > 0) {
                clientX = e.changedTouches[0].clientX;
                clientY = e.changedTouches[0].clientY;
            }

            // Escalar coordenadas por si el canvas fue redimensionado por CSS
            const scaleX = canvas.width / rect.width;
            const scaleY = canvas.height / rect.height;

            const x = (clientX - rect.left) * scaleX;
            const y = (clientY - rect.top) * scaleY;

            ctx.lineTo(x, y);
            ctx.stroke();
            ctx.beginPath();
            ctx.moveTo(x, y);
        };

        // Eventos de Mouse
        canvas.addEventListener('mousedown', startPosition);
        canvas.addEventListener('mouseup', finishedPosition);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseleave', finishedPosition); // Por si el mouse sale del canvas
        
        // Eventos Touch
        canvas.addEventListener('touchstart', (e) => { e.preventDefault(); startPosition(e); }, {passive: false});
        canvas.addEventListener('touchend', (e) => { e.preventDefault(); finishedPosition(); }, {passive: false});
        canvas.addEventListener('touchcancel', (e) => { e.preventDefault(); finishedPosition(); }, {passive: false});
        canvas.addEventListener('touchmove', (e) => { e.preventDefault(); draw(e); }, {passive: false});

        // Clear button
        const clearBtn = canvas.parentElement.querySelector('.btn-clear');
        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                document.getElementById(id + '_input').value = '';
            });
        }
    };

    setupCanvas('canvas_solicitante');
    setupCanvas('canvas_autorizado');
    setupCanvas('canvas_limpieza');
    setupCanvas('canvas_revisa_limpieza');

    // Form Submission - Prepare signatures
    document.getElementById('mainForm').addEventListener('submit', (e) => {
        ['canvas_solicitante', 'canvas_autorizado', 'canvas_limpieza', 'canvas_revisa_limpieza'].forEach(id => {
            const canvas = document.getElementById(id);
            if (canvas) {
                const input = document.getElementById(id + '_input');
                // Only set value if anything was drawn
                input.value = canvas.toDataURL();
            }
        });
    });

    // Dynamic Table Rows
    window.addRow = (tableId) => {
        const table = document.getElementById(tableId).getElementsByTagName('tbody')[0];
        const newRow = table.insertRow();
        const firstRow = table.rows[0];
        const colCount = firstRow.cells.length;
        
        for (let i = 0; i < colCount; i++) {
            const cell = newRow.insertCell(i);
            const originalCell = firstRow.cells[i];
            
            // Si la celda contiene inputs, clonarlos
            const originalInput = originalCell.querySelector('input, select');
            if (originalInput) {
                const newInput = originalInput.cloneNode(true);
                newInput.value = '';
                // Mantener formato de array en nombres
                const baseName = originalInput.name.replace(/\[\]$/, '');
                newInput.name = baseName + '[]';
                cell.appendChild(newInput);
            } else if (originalCell.querySelector('.btn-delete-row') || originalCell.classList.contains('action-cell')) {
                // Si la primera fila ya tiene celda de accion o la creamos, inyectar el boton de eliminar
                cell.classList.add('action-cell');
                cell.style.textAlign = 'center';
                cell.innerHTML = '<button type="button" class="btn-delete-row" onclick="deleteRow(this)">✕</button>';
            }
        }
    };

    window.deleteRow = (btn) => {
        const row = btn.closest('tr');
        if(row.parentNode.rows.length > 1) { // Evita borrar la unica fila
            row.remove();
        }
    };

    // Toggle para Sección de Mediciones
    window.toggleMediciones = () => {
        const wrapper = document.getElementById('mediciones_wrapper');
        const status = document.getElementById('mediciones_status');
        const input = document.getElementById('usa_mediciones');
        const isHidden = wrapper.style.display === 'none';
        
        wrapper.style.display = isHidden ? 'block' : 'none';
        status.textContent = isHidden ? '[ SECCIÓN ACTIVA ]' : '[ CLIC PARA ACTIVAR ]';
        status.style.color = isHidden ? '#a6e3a1' : 'var(--accent2)'; // #a6e3a1 is success green
        input.value = isHidden ? '1' : '0';
        
        // Habilitar/Deshabilitar inputs para que no se envíen si están ocultos
        const inputs = wrapper.querySelectorAll('input, select, textarea');
        inputs.forEach(i => i.disabled = !isHidden);

        const addBtn = document.getElementById('add_medicion_btn');
        if (addBtn) addBtn.disabled = !isHidden;
    };

    // Agregar fila de medición
    const addMedicionBtn = document.getElementById('add_medicion_btn');
    if (addMedicionBtn) {
        addMedicionBtn.addEventListener('click', () => {
            const container = document.getElementById('mediciones_container');
            const firstRow = container.querySelector('.medicion-row');
            const newRow = firstRow.cloneNode(true);
            
            // Limpiar valores
            newRow.querySelectorAll('input, select').forEach(i => {
                i.value = i.tagName === 'SELECT' ? i.options[0].value : '';
                i.disabled = false;
            });

            // Añadir botón de eliminar a la nueva fila
            if (!newRow.querySelector('.btn-delete-item')) {
                const deleteBtn = document.createElement('button');
                deleteBtn.type = 'button';
                deleteBtn.className = 'btn-clear'; // Usando estilo existente
                deleteBtn.style.marginTop = '10px';
                deleteBtn.style.width = '100%';
                deleteBtn.style.background = '#e11d48';
                deleteBtn.style.color = 'white';
                deleteBtn.style.border = 'none';
                deleteBtn.style.padding = '8px';
                deleteBtn.style.borderRadius = '4px';
                deleteBtn.style.cursor = 'pointer';
                deleteBtn.innerHTML = '✕ Eliminar esta fila';
                deleteBtn.onclick = function() { this.closest('.medicion-row').remove(); };
                newRow.appendChild(deleteBtn);
            }

            container.appendChild(newRow);
        });
    }

    // Custom File Input Logic
    const fileInputs = document.querySelectorAll('.file-input-hidden');
    fileInputs.forEach(input => {
        input.addEventListener('change', (e) => {
            const nameDisplay = document.getElementById(input.id + '_name');
            if (nameDisplay) {
                if (e.target.files.length > 0) {
                    nameDisplay.textContent = e.target.files[0].name;
                    nameDisplay.style.color = 'var(--success)';
                } else {
                    nameDisplay.textContent = 'Ningún archivo seleccionado';
                    nameDisplay.style.color = 'var(--text-dim)';
                }
            }
        });
    });
});
