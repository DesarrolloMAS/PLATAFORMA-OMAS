<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../sesion.php';
verificarAutenticacion();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700&family=Space+Mono:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/maquinas_v2.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<title>Revisión de Máquinas · Mantenimiento</title>
</head>
<body>
<div class="container">
    <div class="header-box">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
            <div>
                <h1 class="main-title">📁 Revisión de Máquinas</h1>
                <p class="sub-title" id="stats-texto">Cargando estadísticas…</p>
                <div class="badge-mantenimiento">🔧 Área de Mantenimiento · Mecánicos</div>
            </div>
            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <button class="btn-danger" id="btn-eliminar" disabled>🗑️ Eliminar seleccionados</button>
                <a class="btn-back" href="maquinas_menu.php">← Volver al menú</a>
            </div>
        </div>
    </div>

    <div id="contenedor-zonas"><p class="sub-title">Cargando registros…</p></div>
</div>

<script>
setInterval(() => verificarSesionAjax(() => {}), 10000);
function verificarSesionAjax(callback) {
    fetch('/template/verificar_sesion.php')
        .then(r => r.json())
        .then(data => {
            if (data.activa) { callback(true); return; }
            alert('Tu sesión ha expirado. Por favor, inicia sesión nuevamente.');
            window.location.href = '/index.php?motivo=sesion';
            callback(false);
        })
        .catch(() => callback(false));
}

function marcarSeleccion() {
    const marcados = document.querySelectorAll('.registro-checkbox:checked');
    const btn = document.getElementById('btn-eliminar');
    btn.disabled = marcados.length === 0;
    btn.textContent = marcados.length ? `🗑️ Eliminar (${marcados.length})` : '🗑️ Eliminar seleccionados';
}

function badgeEstado(reg) {
    if (reg.tipo_registro === 'correccion') return '<span style="color:var(--accent);">✏️ Corrección</span>';
    if (reg.estado === 'borrador') return '<span style="color:var(--warning);">📝 Borrador</span>';
    return '<span style="color:var(--ok);">✅ Verificado</span>';
}

fetch('listar_registros.php')
    .then(r => r.json())
    .then(data => {
        const contenedor = document.getElementById('contenedor-zonas');
        contenedor.innerHTML = '';
        const zonas = data.zonas || {};

        if (Object.keys(zonas).length === 0) {
            contenedor.innerHTML = '<p class="sub-title">No hay registros guardados todavía.</p>';
            document.getElementById('stats-texto').textContent = '0 registros';
            return;
        }

        let totalRegistros = 0;

        Object.entries(zonas).forEach(([tipo, grupos]) => {
            const zonaBox = document.createElement('div');
            zonaBox.classList.add('zona-container');

            const header = document.createElement('div');
            header.classList.add('zona-header');
            header.innerHTML = `<span><span class="zona-icon">▶</span> ⚙️ ${tipo.toUpperCase()}</span>
                <button class="btn-danger btn-eliminar-zona" data-tipo="${tipo}" type="button">🗑️ Eliminar toda la zona</button>`;

            const contenido = document.createElement('div');
            contenido.classList.add('zona-contenido');

            header.onclick = () => {
                header.classList.toggle('activa');
                contenido.classList.toggle('visible');
            };

            header.querySelector('.btn-eliminar-zona').addEventListener('click', (e) => {
                e.stopPropagation();
                eliminarZonaCompleta(tipo);
            });

            Object.entries(grupos).forEach(([grupo, maquinas]) => {
                Object.entries(maquinas).forEach(([codigo, info]) => {
                    totalRegistros += info.registros.length;

                    const maquinaDiv = document.createElement('div');
                    maquinaDiv.classList.add('grupo-maquina');

                    let colorClass = '';
                    if (grupo.toLowerCase().includes('bog')) colorClass = 'style="border-left-color:#00F0FF;"';
                    else if (grupo.toLowerCase().includes('pas')) colorClass = 'style="border-left-color:#10B981;"';

                    let filas = info.registros.map(reg => `
                        <div class="pdf-item" style="display:flex; align-items:center; gap:10px; padding:8px 0; border-bottom:1px solid var(--border-color); flex-wrap:wrap;">
                            <input type="checkbox" class="registro-checkbox" data-tipo="${tipo}" data-grupo="${grupo}" data-codigo="${codigo}" data-id="${reg.id_registro}">
                            <span style="min-width:150px;">${reg.timestamp}</span>
                            ${badgeEstado(reg)}
                            <span class="sub-title" style="margin:0;">${reg.usuario_sys}</span>
                            <div style="margin-left:auto; display:flex; gap:8px;">
                                <a class="btn-back" target="_blank" href="visor_verificacion.php?tipo=${encodeURIComponent(tipo)}&maquina=${encodeURIComponent(grupo)}&codigo=${encodeURIComponent(codigo)}&id=${encodeURIComponent(reg.id_registro)}">📂 Ver</a>
                                <a class="btn-back" href="formulario_correccion.php?tipo=${encodeURIComponent(tipo)}&maquina=${encodeURIComponent(grupo)}&codigo=${encodeURIComponent(codigo)}&id=${encodeURIComponent(reg.id_registro)}">✏️ Corregir</a>
                            </div>
                        </div>
                    `).join('');

                    maquinaDiv.innerHTML = `
                        <div class="grupo-maquina-header" ${colorClass}>
                            <span class="grupo-maquina-nombre">${codigo} <span class="sub-title" style="margin:0;">(${grupo})</span></span>
                        </div>
                        ${filas}
                    `;
                    contenido.appendChild(maquinaDiv);
                });
            });

            zonaBox.appendChild(header);
            zonaBox.appendChild(contenido);
            contenedor.appendChild(zonaBox);
        });

        document.getElementById('stats-texto').textContent = `${totalRegistros} registro${totalRegistros !== 1 ? 's' : ''} total${totalRegistros !== 1 ? 'es' : ''}`;
        document.querySelectorAll('.registro-checkbox').forEach(cb => cb.addEventListener('change', marcarSeleccion));
    })
    .catch(err => {
        document.getElementById('contenedor-zonas').innerHTML = `<p style="color:var(--danger)">❌ Error al cargar: ${err.message}</p>`;
    });

function eliminarZonaCompleta(tipo) {
    Swal.fire({
        title: `¿Eliminar toda la zona "${tipo.toUpperCase()}"?`,
        html: `Se borrarán <b>todos</b> los registros de <b>todas</b> las máquinas de este tipo, sin posibilidad de recuperarlos.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar todo',
        cancelButtonText: 'Cancelar',
        background: '#151A22', color: '#fff', confirmButtonColor: '#FF3366', cancelButtonColor: '#334155'
    }).then(result => {
        if (!result.isConfirmed) return;

        Swal.fire({
            title: `Escribe "${tipo}" para confirmar`,
            input: 'text',
            inputPlaceholder: tipo,
            showCancelButton: true,
            confirmButtonText: 'Eliminar definitivamente',
            cancelButtonText: 'Cancelar',
            background: '#151A22', color: '#fff', confirmButtonColor: '#FF3366', cancelButtonColor: '#334155',
            preConfirm: (valor) => {
                if (valor !== tipo) {
                    Swal.showValidationMessage('El texto no coincide.');
                    return false;
                }
                return true;
            }
        }).then(confirmacion => {
            if (!confirmacion.isConfirmed) return;

            fetch('eliminar_zona.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ tipo })
            })
                .then(r => r.json())
                .then(data => {
                    Swal.fire({
                        title: data.status === 'success' ? '¡Zona eliminada!' : 'Error',
                        text: data.message,
                        icon: data.status === 'success' ? 'success' : 'error',
                        background: '#151A22', color: '#fff', confirmButtonColor: '#FF8A00'
                    }).then(() => location.reload());
                })
                .catch(err => {
                    Swal.fire({
                        title: 'Error de conexión',
                        text: err.message,
                        icon: 'error',
                        background: '#151A22', color: '#fff', confirmButtonColor: '#FF8A00'
                    });
                });
        });
    });
}

document.getElementById('btn-eliminar').addEventListener('click', () => {
    const marcados = Array.from(document.querySelectorAll('.registro-checkbox:checked'));
    if (marcados.length === 0) return;
    if (!confirm(`¿Seguro que deseas eliminar ${marcados.length} registro${marcados.length !== 1 ? 's' : ''}?`)) return;

    const solicitudes = marcados.map(cb => fetch('eliminar_registro.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            tipo: cb.dataset.tipo,
            maquina: cb.dataset.grupo,
            codigo: cb.dataset.codigo,
            id: cb.dataset.id
        })
    }).then(r => r.json()));

    Promise.all(solicitudes).then(resultados => {
        const fallidos = resultados.filter(r => r.status !== 'success').length;
        Swal.fire({
            title: fallidos ? `Se eliminaron ${marcados.length - fallidos}, fallaron ${fallidos}` : '¡Eliminados!',
            icon: fallidos ? 'warning' : 'success',
            background: '#151A22', color: '#fff', confirmButtonColor: '#FF8A00'
        }).then(() => location.reload());
    });
});
</script>
</body>
</html>
