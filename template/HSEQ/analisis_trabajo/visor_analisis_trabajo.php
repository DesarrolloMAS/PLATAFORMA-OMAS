<?php include '../../sesion.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visor ATS — Análisis de Trabajo Seguro</title>
    <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700&family=Space+Mono:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
        :root {
            --bg-color: #0B0E14; --panel-bg: #151A22; --accent: #00F0FF;
            --text-main: #E2E8F0; --text-muted: #94A3B8;
            --border-color: #1E293B; --input-bg: #0F172A;
            --success: #10B981; --r-md: 8px; --r-sm: 4px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Barlow', sans-serif; background: var(--bg-color);
            color: var(--text-main); min-height: 100vh; padding: 40px 20px;
            background-image:
                linear-gradient(rgba(0,240,255,0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0,240,255,0.03) 1px, transparent 1px);
            background-size: 30px 30px;
        }
        .container { max-width: 1000px; margin: 0 auto; }
        .toolbar {
            display: flex; gap: 12px; margin-bottom: 24px; flex-wrap: wrap; align-items: center;
        }
        .btn {
            padding: 10px 20px; border-radius: var(--r-sm); font-family: 'Space Mono', monospace;
            font-size: 13px; cursor: pointer; transition: all 0.2s; border: 1px solid; text-decoration: none;
            display: inline-block;
        }
        .btn-back { background: var(--input-bg); border-color: var(--border-color); color: var(--text-main); }
        .btn-back:hover { border-color: var(--accent); color: var(--accent); }
        .btn-pdf { background: var(--accent); border-color: var(--accent); color: var(--bg-color); font-weight: 700; }
        .btn-pdf:hover { background: #fff; }

        /* ── DOCUMENTO IMPRIMIBLE ── */
        #documento {
            background: #fff;
            color: #111;
            padding: 24px;
            border-radius: var(--r-md);
            box-shadow: 0 10px 40px rgba(0,0,0,0.5);
        }

        .doc-header {
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: 0;
            border: 2px solid #1a3a5c;
            margin-bottom: 0;
        }

        .doc-header-logo {
            padding: 8px 12px;
            border-right: 1px solid #1a3a5c;
            display: flex; align-items: center; justify-content: center;
            font-size: 10px; color: #666; min-width: 80px;
        }

        .doc-header-center {
            padding: 8px 12px; text-align: center;
            border-right: 1px solid #1a3a5c;
        }

        .doc-header-center .titulo-doc {
            font-size: 10px; font-weight: 600; color: #333;
            text-transform: uppercase; margin-bottom: 2px;
        }

        .doc-header-center .subtitulo-doc {
            font-size: 11px; font-weight: 700; color: #1a3a5c;
        }

        .doc-header-meta table { border-collapse: collapse; }
        .doc-header-meta td {
            padding: 3px 8px; font-size: 10px; border-bottom: 1px solid #ddd;
        }
        .doc-header-meta td:first-child { font-weight: 600; color: #333; min-width: 60px; }

        .doc-section-title {
            background: #1a3a5c; color: #fff; text-align: center;
            font-size: 11px; font-weight: 700; padding: 5px;
            text-transform: uppercase; letter-spacing: 1px; margin-top: 0;
        }

        .doc-table { width: 100%; border-collapse: collapse; font-size: 11px; }
        .doc-table th, .doc-table td {
            border: 1px solid #aaa; padding: 5px 8px;
        }
        .doc-table th {
            background: #d0d8e4; font-weight: 700; text-align: center;
            font-size: 10px; text-transform: uppercase;
        }
        .doc-table td { background: #fff; vertical-align: top; }

        .field-label {
            background: #c0cfe0; font-weight: 700; font-size: 10px;
            text-transform: uppercase; padding: 4px 8px; white-space: nowrap;
        }
        .field-value { padding: 4px 8px; min-height: 22px; }

        .doc-grid { display: grid; }

        .nota-doc {
            background: #fff3cd; border: 1px solid #aaa;
            padding: 5px 10px; font-size: 10px; font-weight: 700; text-align: center; color: #7a5200;
        }

        .firma-doc {
            border: 1px solid #aaa; padding: 8px; min-height: 70px;
        }
        .firma-doc .f-titulo {
            background: #d0d8e4; font-weight: 700; font-size: 10px;
            text-transform: uppercase; padding: 3px; text-align: center; margin-bottom: 6px;
        }
        .firma-doc .f-row { display: flex; gap: 8px; margin-bottom: 4px; }
        .firma-doc .f-label { font-weight: 600; font-size: 10px; min-width: 60px; }
        .firma-doc .f-val { font-size: 10px; border-bottom: 1px solid #ccc; flex: 1; padding-bottom: 2px; }

        .system-status {
            display: flex; align-items: center; justify-content: center; gap: 10px;
            font-family: 'Space Mono', monospace; font-size: 11px; color: var(--text-muted); padding: 30px 0 10px;
        }
        .status-dot {
            width: 7px; height: 7px; border-radius: 50%;
            background: var(--success); box-shadow: 0 0 6px var(--success); animation: pulse 2s infinite;
        }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.4; } }

        @media print {
            body { background: #fff; padding: 0; }
            .toolbar { display: none; }
            #documento { box-shadow: none; padding: 10px; }
        }
    </style>
</head>
<body>
<div class="container">

    <div class="toolbar">
        <a href="rev_analisis_trabajo.php" class="btn btn-back">← Galería</a>
        <button class="btn btn-pdf" onclick="exportarPDF()">⬇ Descargar PDF</button>
        <button class="btn btn-back" onclick="window.print()">🖨 Imprimir</button>
    </div>

    <div id="documento">
        <div id="contenidoDoc" style="color: #888; text-align:center; padding: 40px;">Cargando registro...</div>
    </div>

    <div class="system-status">
        <div class="status-dot"></div>
        SISTEMA JSON INTERCONECTADO — PLATAFORMA OMAS / HSEQ
    </div>
</div>

<script>
    const params = new URLSearchParams(location.search);
    const fileRuta = params.get('file');
    const idRegistro = params.get('id');

    function v(val) { return val || '—'; }

    function yn(val) {
        if (!val || val === '') return '—';
        return val;
    }

    function renderDoc(reg) {
        const d = reg.datos || {};
        const trabajadores = d.trabajadores || [];

        let filasTrabs = '';
        trabajadores.forEach((t, i) => {
            filasTrabs += `<tr>
                <td style="text-align:center;">${i+1}</td>
                <td>${v(t.nombre)}</td>
                <td>${v(t.tipo_doc)}</td>
                <td>${v(t.documento)}</td>
                <td>${v(t.cargo)}</td>
                <td>${v(t.dia)}</td>
                <td>${v(t.firma)}</td>
            </tr>`;
        });
        if (!filasTrabs) filasTrabs = `<tr><td colspan="7" style="text-align:center; color:#888;">Sin trabajadores registrados</td></tr>`;

        const html = `
        <!-- ENCABEZADO -->
        <div class="doc-header">
            <div class="doc-header-logo">
                <span style="font-size:9px; text-align:center;">Manual de<br>Seguridad y<br>Salud en el<br>Trabajo</span>
            </div>
            <div class="doc-header-center">
                <div class="titulo-doc">Sub-proceso Seguridad y Salud en el Trabajo</div>
                <div class="subtitulo-doc">"Análisis de Trabajo Seguro"</div>
            </div>
            <div class="doc-header-meta">
                <table>
                    <tr><td>Código:</td><td>HSEQ-HS-RE-FO-001</td></tr>
                    <tr><td>Versión:</td><td>1</td></tr>
                    <tr><td>Fecha:</td><td>28/3/2023</td></tr>
                    <tr><td>Página:</td><td>1 de 1</td></tr>
                </table>
            </div>
        </div>

        <!-- 01 GENERALIDADES -->
        <div class="doc-section-title">Generalidades</div>
        <table class="doc-table">
            <tr>
                <td class="field-label">Fecha de Elaboración</td>
                <td class="field-value">${v(d.fecha_elaboracion)}</td>
                <td class="field-label">Tipo de Trabajo</td>
                <td class="field-value">${v(d.tipo_trabajo)}</td>
            </tr>
            <tr>
                <td class="field-label">Fecha Inicio</td>
                <td class="field-value">${v(d.fecha_inicio)}</td>
                <td class="field-label">Fecha Fin</td>
                <td class="field-value">${v(d.fecha_fin)}</td>
            </tr>
            <tr>
                <td class="field-label">Hora Inicio</td>
                <td class="field-value">${v(d.hora_inicio)}</td>
                <td class="field-label">Hora Fin</td>
                <td class="field-value">${v(d.hora_fin)}</td>
            </tr>
            <tr>
                <td class="field-label">Valoración del Riesgo</td>
                <td class="field-value">${v(d.valoracion_riesgo)}</td>
                <td class="field-label">Frecuencia</td>
                <td class="field-value">${v(d.frecuencia)}</td>
            </tr>
            <tr>
                <td class="field-label">Zona de Trabajo</td>
                <td class="field-value">${v(d.zona_trabajo)}</td>
                <td class="field-label">Dependencia</td>
                <td class="field-value">${v(d.dependencia)}</td>
            </tr>
            <tr>
                <td class="field-label">Equipo o Sistema Objeto</td>
                <td class="field-value">${v(d.equipo_sistema)}</td>
                <td class="field-label">Altura Máxima (m)</td>
                <td class="field-value">${v(d.altura_maxima)}</td>
            </tr>
        </table>

        <!-- DESCRIPCIÓN -->
        <div class="doc-section-title">Descripción de la Actividad a Realizar</div>
        <table class="doc-table">
            <tr><td style="min-height:50px; padding: 8px;">${v(d.descripcion_actividad)}</td></tr>
        </table>

        <!-- ACTIVIDADES CRÍTICAS -->
        <table class="doc-table">
            <tr>
                <td class="field-label" style="max-width:400px; white-space:normal; font-size:10px;">¿Las actividades incluyen actividades críticas adicionales?</td>
                <td class="field-value" style="width:100px;">${v(d.actividades_criticas)}</td>
                <td class="field-label">Actividad</td>
                <td class="field-value">${v(d.actividad_critica_detalle)}</td>
            </tr>
        </table>

        <!-- TRABAJADORES AUTORIZADOS -->
        <div class="doc-section-title">Trabajadores Autorizados</div>
        <table class="doc-table">
            <thead>
                <tr>
                    <th>N°</th><th>Nombres y Apellidos</th><th>Tipo Doc</th>
                    <th>N° Documento</th><th>Cargo</th><th>Día de la Semana</th><th>Firma</th>
                </tr>
            </thead>
            <tbody>${filasTrabs}</tbody>
        </table>

        <!-- HERRAMIENTAS -->
        <div class="doc-section-title">Elementos, Herramientas y Equipos a Utilizar</div>
        <table class="doc-table">
            <tr>
                <td class="field-label">Manuales</td>
                <td class="field-value">${v(d.herr_manuales)}</td>
                <td class="field-label">Eléctricas</td>
                <td class="field-value">${v(d.herr_electricas)}</td>
            </tr>
            <tr>
                <td class="field-label">Neumáticas</td>
                <td class="field-value">${v(d.herr_neumaticas)}</td>
                <td class="field-label">Hidráulicas</td>
                <td class="field-value">${v(d.herr_hidraulicas)}</td>
            </tr>
            <tr>
                <td class="field-label">Mecánicas</td>
                <td class="field-value">${v(d.herr_mecanicas)}</td>
                <td class="field-label">Otras</td>
                <td class="field-value">${v(d.herr_otras)}</td>
            </tr>
        </table>

        <!-- RIESGOS CRÍTICOS -->
        <div class="doc-section-title">Identificación de Riesgos Críticos Asociados al Trabajo</div>
        <table class="doc-table">
            <tr>
                <td class="field-label" style="white-space:normal; font-size:10px; max-width:340px;">¿El trabajo presenta riesgos críticos asociados?</td>
                <td class="field-value" style="width:80px;">${v(d.riesgos_criticos)}</td>
                <td class="field-label">Especifique</td>
                <td class="field-value">${v(d.riesgos_criticos_detalle)}</td>
            </tr>
        </table>

        <!-- CONTROLES CRÍTICOS -->
        <div class="doc-section-title">Controles Críticos Obligatorios</div>
        <table class="doc-table">
            <tr>
                <td class="field-label">Aislamiento energías (LOTO)</td>
                <td class="field-value" style="width:70px;">${v(d.ctrl_loto)}</td>
                <td class="field-label">Evaluación riesgos (ATS socializado)</td>
                <td class="field-value" style="width:70px;">${v(d.ctrl_ats)}</td>
            </tr>
            <tr>
                <td class="field-label">Medición atmósferas</td>
                <td class="field-value">${v(d.ctrl_atmosferas)}</td>
                <td class="field-label">Protección contra caídas</td>
                <td class="field-value">${v(d.ctrl_caidas)}</td>
            </tr>
            <tr>
                <td class="field-label">Control de ignición</td>
                <td class="field-value">${v(d.ctrl_ignicion)}</td>
                <td class="field-label">Delimitación del área</td>
                <td class="field-value">${v(d.ctrl_delimitacion)}</td>
            </tr>
            <tr>
                <td class="field-label">Vigía asignado</td>
                <td class="field-value">${v(d.ctrl_vigia)}</td>
                <td class="field-label">Uso de EPP completo</td>
                <td class="field-value">${v(d.ctrl_epp)}</td>
            </tr>
            <tr>
                <td class="field-label">Personal competente</td>
                <td class="field-value">${v(d.ctrl_personal)}</td>
                <td class="field-label">Plan de emergencia y rescate</td>
                <td class="field-value">${v(d.ctrl_emergencia)}</td>
            </tr>
        </table>
        <div class="nota-doc">NOTA: Si alguno NO cumple — NO iniciar actividad</div>

        <!-- EPP -->
        <div class="doc-section-title">Elementos de Protección Personal (EPP)</div>
        <table class="doc-table">
            <tr>
                <td class="field-label">Casco</td><td class="field-value" style="width:60px;">${v(d.epp_casco)}</td>
                <td class="field-label">Protector Auditivo</td><td class="field-value" style="width:60px;">${v(d.epp_auditivo)}</td>
                <td class="field-label">Chaleco</td><td class="field-value" style="width:60px;">${v(d.epp_chaleco)}</td>
            </tr>
            <tr>
                <td class="field-label">Casco Dieléctrico</td><td class="field-value">${v(d.epp_casco_dielectrico)}</td>
                <td class="field-label">Prot. Respiratorio</td><td class="field-value">${v(d.epp_respiratorio)}</td>
                <td class="field-label">Overol</td><td class="field-value">${v(d.epp_overol)}</td>
            </tr>
            <tr>
                <td class="field-label">Barbuquejo</td><td class="field-value">${v(d.epp_barbuquejo)}</td>
                <td class="field-label">Botas de Seguridad</td><td class="field-value">${v(d.epp_botas)}</td>
                <td class="field-label">Overol Ignífugo</td><td class="field-value">${v(d.epp_overol_ignifugo)}</td>
            </tr>
            <tr>
                <td class="field-label">Protector Visual</td><td class="field-value">${v(d.epp_visual)}</td>
                <td class="field-label">Guantes Carnaza</td><td class="field-value">${v(d.epp_guantes_carnaza)}</td>
                <td class="field-label">Careta Soldar</td><td class="field-value">${v(d.epp_careta_soldar)}</td>
            </tr>
            <tr>
                <td class="field-label">Delantal</td><td class="field-value">${v(d.epp_delantal)}</td>
                <td class="field-label">Guantes Poliuretano</td><td class="field-value">${v(d.epp_guantes_poli)}</td>
                <td class="field-label">Mascarilla</td><td class="field-value">${v(d.epp_mascarilla)}</td>
            </tr>
            <tr>
                <td class="field-label">Polainas</td><td class="field-value">${v(d.epp_polainas)}</td>
                <td class="field-label">Guantes Dieléctrico</td><td class="field-value">${v(d.epp_guantes_dielectrico)}</td>
                <td class="field-label">Mangas Dieléctricas</td><td class="field-value">${v(d.epp_mangas_dielectricas)}</td>
            </tr>
        </table>

        <!-- PLAN DE EMERGENCIA -->
        <div class="doc-section-title">Plan de Emergencia</div>
        <table class="doc-table">
            <tr>
                <td class="field-label">Tipo de Emergencia</td>
                <td class="field-value">${v(d.tipo_emergencia)}</td>
                <td class="field-label">Ruta de Evacuación</td>
                <td class="field-value">${v(d.ruta_evacuacion)}</td>
                <td class="field-label">Punto de Encuentro</td>
                <td class="field-value">${v(d.punto_encuentro)}</td>
            </tr>
            <tr>
                <td class="field-label">Brigadista Responsable</td>
                <td class="field-value">${v(d.brigadista_responsable)}</td>
                <td class="field-label">Equipos Disponibles</td>
                <td class="field-value" colspan="3">${v(d.equipos_disponibles)}</td>
            </tr>
        </table>

        <!-- FIRMAS -->
        <div class="doc-section-title">Firmas de Autorización del Permiso</div>
        <div style="font-size:10px; color:#444; padding:5px 8px; border: 1px solid #aaa; border-top:none; margin-bottom:0;">
            Nota: Verificando el cumplimiento por parte de los trabajadores a los procedimientos de los trabajos seguros instaurados, se avala la efectividad en campo y se firma el actual ATS para actividades de terceros.
        </div>
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:0; border:1px solid #aaa; border-top:none;">
            <div class="firma-doc" style="border-right: 1px solid #aaa;">
                <div class="f-titulo">Jefe Encargado de la Actividad</div>
                <div class="f-row"><span class="f-label">Nombre:</span><span class="f-val">${v(d.jefe_nombre)}</span></div>
                <div class="f-row"><span class="f-label">ID:</span><span class="f-val">${v(d.jefe_id)}</span></div>
                <div class="f-row"><span class="f-label">Firma:</span><span class="f-val">${v(d.jefe_firma)}</span></div>
            </div>
            <div class="firma-doc">
                <div class="f-titulo">Jefe Encargado Suplente</div>
                <div class="f-row"><span class="f-label">Nombre:</span><span class="f-val">${v(d.jefe_suplente_nombre)}</span></div>
                <div class="f-row"><span class="f-label">ID:</span><span class="f-val">${v(d.jefe_suplente_id)}</span></div>
                <div class="f-row"><span class="f-label">Firma:</span><span class="f-val">${v(d.jefe_suplente_firma)}</span></div>
            </div>
            <div class="firma-doc" style="border-top: 1px solid #aaa; border-right: 1px solid #aaa;">
                <div class="f-titulo">Coordinador de Trabajo Seguro</div>
                <div class="f-row"><span class="f-label">Nombre:</span><span class="f-val">${v(d.coord_nombre)}</span></div>
                <div class="f-row"><span class="f-label">ID:</span><span class="f-val">${v(d.coord_id)}</span></div>
                <div class="f-row"><span class="f-label">Firma:</span><span class="f-val">${v(d.coord_firma)}</span></div>
            </div>
            <div class="firma-doc" style="border-top: 1px solid #aaa;">
                <div class="f-titulo">Coordinador de Trabajo Seguro Suplente</div>
                <div class="f-row"><span class="f-label">Nombre:</span><span class="f-val">${v(d.coord_suplente_nombre)}</span></div>
                <div class="f-row"><span class="f-label">ID:</span><span class="f-val">${v(d.coord_suplente_id)}</span></div>
                <div class="f-row"><span class="f-label">Firma:</span><span class="f-val">${v(d.coord_suplente_firma)}</span></div>
            </div>
        </div>

        <div style="font-size:10px; color:#888; text-align:center; margin-top:12px; padding-top:8px; border-top:1px solid #ddd;">
            ID: ${reg.id_registro} &nbsp;|&nbsp; Registrado por: ${reg.usuario_sys} &nbsp;|&nbsp; Sede: ${reg.sede_sys} &nbsp;|&nbsp; ${reg.timestamp}
        </div>`;

        document.getElementById('contenidoDoc').innerHTML = html;
    }

    async function cargar() {
        if (!fileRuta || !idRegistro) {
            document.getElementById('contenidoDoc').textContent = 'Parámetros de consulta faltantes.';
            return;
        }
        try {
            const res = await fetch(fileRuta);
            const arr = await res.json();
            const reg = arr.find(r => r.id_registro === idRegistro);
            if (!reg) { document.getElementById('contenidoDoc').textContent = 'Registro no encontrado.'; return; }
            renderDoc(reg);
        } catch (e) {
            document.getElementById('contenidoDoc').textContent = 'Error al cargar el registro.';
        }
    }

    async function exportarPDF() {
        const { jsPDF } = window.jspdf;
        const el = document.getElementById('documento');
        const canvas = await html2canvas(el, { scale: 2, useCORS: true });
        const imgData = canvas.toDataURL('image/png');
        const pdf = new jsPDF('p', 'mm', 'a4');
        const pW = pdf.internal.pageSize.getWidth();
        const pH = pdf.internal.pageSize.getHeight();
        const ratio = canvas.width / canvas.height;
        const imgH = pW / ratio;
        let posY = 0;
        let remaining = imgH;
        while (remaining > 0) {
            pdf.addImage(imgData, 'PNG', 0, posY, pW, imgH);
            remaining -= pH;
            posY -= pH;
            if (remaining > 0) pdf.addPage();
        }
        pdf.save('ATS_' + (idRegistro || 'registro') + '.pdf');
    }

    cargar();
</script>
</body>
</html>
