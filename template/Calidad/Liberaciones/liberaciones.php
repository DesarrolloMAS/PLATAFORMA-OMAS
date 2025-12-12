<!DOCTYPE html>
<html lang="en">
<head>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/liberaciones_formato.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_blue.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script> <!-- Localización en español -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- jQuery -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <title>Registro de Liberaciones</title>
</head>
</head>
<body class="body">
    <div>
    <form class="formulario" action="liberaciones_procesamiento.php" method="POST">
        <div class="contenedor_basico">
            <script>
                    document.addEventListener('DOMContentLoaded', function () {
                    flatpickr("#fecha", {
                    dateFormat: "Y-m-d", // Formato de la fecha (por ejemplo, 2025-05-09)
                    altInput: true, // Muestra un input alternativo más elegante
                    altFormat: "F j, Y", // Formato alternativo (por ejemplo, May 9, 2025)
                    locale: "es" // Cambia el idioma a español
                    });
                    });
                    document.addEventListener('DOMContentLoaded', function () {
                    $('#planta').select2({
                    placeholder: "Selecciona una opción", // Texto de marcador de posición
                    allowClear: true // Permite limpiar la selección
    });
});
            </script>
            <a href="../../redireccion.php">VOLVER</a>
            <label for="fecha">Fecha De Produccion</label>
            <input type="date" id="fecha" name="fecha">
            <label for="planta">Planta</label>
            <select name="planta" id="planta">
                <option value="Zona Centro">Zona Centro</option>
                <option value="Zona Sur">Zona Sur</option>
            </select>
        </div>
        <input type="checkbox" id="toggle_harinas">
        <label for="toggle_harinas" class="titulo_principal_contenedor">LIBERACIONES</label>
        <div class="contenedor_harinas">
        <h2>HARINAS PARA LIBERAR</h2>
            <div>
            <input type="checkbox" id="harina1" onclick="botonharina1()">
                <label class="labelmen" for="harina1">
                    HARINA DE TRIGO EXTRAPAN
                </label>
            </div>
            <div id="contenedor_harina1_lotes">
            </div>
            <div class="contenedor_harina" id="contenedor_harina1" style="display: none;">
                <div> 
                    <label for="referencia">Referencia</label>
                    <select name="referencia_extrapan" id="referencia_extrapan">
                        <option value="50 KG">50 KG</option>
                        <option value="25 KG">25 KG</option>
                        <option value="10 KG">10 KG</option>
                    </select>
                    <label for="lote_extrapan">Lote</label>
                    <input type="text" id="lote_extrapan" name="lote_extrapan">
                    <label for="cantidad">Cantidad Liberada</label>
                    <input type="number" id="cantidad_extrapan" name="cantidad_extrapan">
                </div>        
                <div>
                    <h2>AREA DE CUMPLIMIENTO</h2>
                    <label for="bitacora">Cumple con especificaciones de FT o Bitacora de analisis</label>
                    <select name="bitacora1" id="bitacora">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                    </select>
                    <label for="panificacion">Cumple los analisis de panificacion?</label>
                    <select name="panificaciona1" id="panificacion">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                    </select>
                    <label for="laboratorios_ex1">Cumple los analisis de laboratorios externos?</label>
                    <select name="laboratorios_ex1" id="laboratorios_ex1">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                        <option value="N/A">N/A</option>
                    </select>
                    <label for="fortificacion1">Cumple los adicion de fortificacion?</label>
                    <select name="fortificacion1" id="fortificacion1">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                    </select>
                    <label for="mejorantes1">Cumple con adicion de mejorantes?</label>
                    <select name="mejorantes1" id="mejorantes1">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                    </select>
                    <label for="empaque1">Cumple las condiciones y empaque rotulado?</label>
                    <select name="empaque1" id="empaque1">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                    </select>
                    <label for="certificado1">Cumple el certificado de calidad?</label>
                    <select name="certificado1" id="certificado1">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                    </select>
                    <button type="button" onclick="agregarLote('harina1')">Agregar Lote</button>
                </div>
            </div>

            <!-- DIVISION ENTRE HARINAS -->

            <div>
                <input type="checkbox" id="harina2" onclick="botonharina2()">
                <label class="labelmen" for="harina2">
                    HARINA DE TRIGO ALTA PROTEINA
                </label>
            </div>
            <div id="contenedor_harina2_lotes">
            </div>
            <div class="contenedor_harina" id="contenedor_harina2" style="display: none;">
                <div> 
                    <label for="referencia2">Referencia</label>
                    <select name="referencia_proteina2" id="harina2_referencia_1" onchange="handleCustomOption('harina2', 1)">
                        <option value="Granel">Granel</option>
                        <option value="custom">Otro (Especificar)</option>
                    </select>
                    <input type="text" id="harina2_customInput_1" 
                    placeholder="Escribe aquí..." 
                    style="display: none;" 
                    oninput="updateCustomOption('harina2', 1)">
                    <label for="lote2">Lote</label>
                    <input type="text" id="lote2" name="lote2">
                    <label for="cantidad">Cantidad Liberada</label>
                    <input type="number" id="cantidad2" name="cantidad2">
                </div>        
                <div>
                    <h2>AREA DE CUMPLIMIENTO</h2>
                    <label for="bitacora2">Cumple con especificaciones de FT o Bitacora de analisis</label>
                    <select name="bitacora2" id="bitacora2">
                        <option value="">---</option>   
                        <option value="N/A">No Aplica</option>                     
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                    </select>
                    <label for="panificacion2">Cumple los analisis de panificacion?</label>
                    <select name="panificaciona2" id="panificacion2">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                    </select>
                    <label for="laboratorios2">Cumple los analisis de laboratorios externos?</label>
                    <select name="laboratorios2" id="laboratorios2">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                        <option value="N/A">N/A</option>
                    </select>
                    <label for="fortificacion2">Cumple los adicion de fortificacion?</label>
                    <select name="fortificacion2" id="fortificacion2">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                    </select>
                    <label for="mejorantes2">Cumple con adicion de mejorantes?</label>
                    <select name="mejorantes2" id="mejorantes2">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                    </select>
                    <label for="empaque2">Cumple las condiciones y empaque rotulado?</label>
                    <select name="empaque2" id="empaque2">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                    </select>
                    <label for="certificado2">Cumple el certificado de calidad?</label>
                    <select name="certificado2" id="certificado2">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                    </select>
                    <button type="button" onclick="agregarLote('harina2')">Agregar Lote</button>
                </div>
            </div>

            <!-- DIVISION ENTRE HARINAS -->

            <div>
                <input type="checkbox" id="harina3" onclick="botonharina3()">
                <label class="labelmen" for="harina3">
                    HARINA DE TRIGO ARTESANAL
                </label>
            </div>
            <div id="contenedor_harina3_lotes">
            </div>
            <div class="contenedor_harina" id="contenedor_harina3" style="display: none;">
                <div> 
                    <label for="referencia">Referencia</label>
                    <select name="referencia_artesanal" id="harina3_referencia_2" onchange="handleCustomOption('harina3', 2)">
                        <option value="50 KG">50 KG</option>
                        <option value="25 KG">25 KG</option>
                        <option value="10 KG">10 KG</option>
                        <option value="custom">Otro (Especifico)</option>
                    </select>
                    <input type="text" id="harina3_customInput_2" 
                    placeholder="Escribe aquí..." 
                    style="display: none;" 
                    oninput="updateCustomOption('harina3', 2)">
                    <label for="lote">Lote</label>
                    <input type="text" id="lote3" name="lote3">
                    <label for="cantidad">Cantidad Liberada</label>
                    <input type="number" id="cantidad3" name="cantidad3">
                </div>        
                <div>
                    <h2>AREA DE CUMPLIMIENTO</h2>
                    <label for="bitacora3">Cumple con especificaciones de FT o Bitacora de analisis</label>
                    <select name="bitacora3" id="bitacora3">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                    </select>
                    <label for="panificacion3">Cumple los analisis de panificacion?</label>
                    <select name="panificaciona3" id="panificacion3">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                    </select>
                    <label for="laboratorios3">Cumple los analisis de laboratorios externos?</label>
                    <select name="laboratorios3" id="laboratorios3">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                        <option value="N/A">N/A</option>
                    </select>
                    <label for="fortificacion3">Cumple los adicion de fortificacion?</label>
                    <select name="fortificacion3" id="fortificacion3">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                    </select>
                    <label for="mejorantes3">Cumple con adicion de mejorantes?</label>
                    <select name="mejorantes3" id="mejorantes3">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                    </select>
                    <label for="empaque3">Cumple las condiciones y empaque rotulado?</label>
                    <select name="empaque3" id="empaque3">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                    </select>
                    <label for="certificado3">Cumple el certificado de calidad?</label>
                    <select name="certificado3" id="certificado3">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                    </select>
                    <button type="button" onclick="agregarLote('harina3')">Agregar Lote</button>
                </div>
            </div>

            <!-- DIVISION ENTRE HARINAS -->

            <div>
                <input type="checkbox" id="harina4" onclick="botonharina4()">
                <label class="labelmen" for="harina4">
                    
                    HARINA DE TRIGO EXCLUSIVA 
                </label>
            </div>
            <div id="contenedor_harina4_lotes">
            </div>
            <div class="contenedor_harina" id="contenedor_harina4" style="display: none;">
                <div> 
                    <label for="referencia">Referencia</label>
                    <select name="referencia4" id="referencia4">
                        <option value="50 KG">50 KG</option>
                    </select>
                    <label for="lote">Lote</label>
                    <input type="text" id="lote4" name="lote4">
                    <label for="cantidad">Cantidad Liberada</label>
                    <input type="number" id="cantidad4" name="cantidad4">
                </div>        
                <div>
                    <h2>AREA DE CUMPLIMIENTO</h2>
                    <label for="bitacora4">Cumple con especificaciones de FT o Bitacora de analisis</label>
                    <select name="bitacora4" id="bitacora4">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                    </select>
                    <label for="panificacion4">Cumple los analisis de panificacion?</label>
                    <select name="panificacion4" id="panificacion4">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                    </select>
                    <label for="laboratorios4">Cumple los analisis de laboratorios externos?</label>
                    <select name="laboratorios4" id="laboratorios4">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                        <option value="N/A">N/A</option>
                    </select>
                    <label for="fortificacion4">Cumple los adicion de fortificacion?</label>
                    <select name="fortificacion4" id="fortificacion4">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                    </select>
                    <label for="mejorantes4">Cumple con adicion de mejorantes?</label>
                    <select name="mejorantes4" id="mejorantes4">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                    </select>
                    <label for="empaque4">Cumple las condiciones y empaque rotulado?</label>
                    <select name="empaque4" id="empaque4">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                    </select>
                    <label for="certificado4">Cumple el certificado de calidad?</label>
                    <select name="certificado4" id="certificado4">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                    </select>
                    <button type="button" onclick="agregarLote('harina4')">Agregar Lote</button>
                </div>
            </div>

            <!-- DIVISION ENTRE HARINAS -->

            <div>
            <input type="checkbox" id="harina5" onclick="botonharina5()">
                <label class="labelmen" for="harina5">
                    
                    HARINA DE TRIGO NATURAL
                </label>
            </div>
            <div id="contenedor_harina5_lotes">
            </div>
            <div class="contenedor_harina" id="contenedor_harina5" style="display: none;">
                <div> 
                    <label for="referencia">Referencia</label>
                    <select name="referencia5" id="referencia5">
                        <option value="50 KG">50 KG</option>
                    </select>
                    <label for="lote">Lote</label>
                    <input type="text" id="lote5" name="lote5">
                    <label for="cantidad5">Cantidad Liberada</label>
                    <input type="number" id="cantidad5" name="cantidad5">
                </div>        
                <div>
                    <h2>AREA DE CUMPLIMIENTO</h2>
                    <label for="bitacora5">Cumple con especificaciones de FT o Bitacora de analisis</label>
                    <select name="bitacora5" id="bitacora5">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                    </select>
                    <label for="panificacion5">Cumple los analisis de panificacion?</label>
                    <select name="panificacion5" id="panificacion5">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                    </select>
                    <label for="laboratorios5">Cumple los analisis de laboratorios externos?</label>
                    <select name="laboratorios5" id="laboratorios5">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                        <option value="N/A">N/A</option>
                    </select>
                    <label for="fortificacion5">Cumple los adicion de fortificacion?</label>
                    <select name="fortificacion5" id="fortificacion5">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                    </select>
                    <label for="mejorantes5">Cumple con adicion de mejorantes?</label>
                    <select name="mejorantes5" id="mejorantes5">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                    </select>
                    <label for="empaque5">Cumple las condiciones y empaque rotulado?</label>
                    <select name="empaque5" id="empaque5">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                    </select>
                    <label for="certificado5">Cumple el certificado de calidad?</label>
                    <select name="certificado5" id="certificado5">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                    </select>
                    <button type="button" onclick="agregarLote('harina5')">Agregar Lote</button>
                </div>
            </div>

            <!-- DIVISION ENTRE HARINAS -->

            <div>
            <input type="checkbox" id="harina6" onclick="botonharina6()">
                <label class="labelmen" for="harina6">
                    
                    HARINA DE TRIGO FUERTE
                </label>
            </div>
            <div id="contenedor_harina6_lotes">
            </div>
            <div class="contenedor_harina" id="contenedor_harina6" style="display: none;">
                <div> 
                    <label for="referencia">Referencia</label>
                    <select name="referencia6" id="referencia6">
                        <option value="25 KG">25 KG</option>
                    </select>
                    <label for="lote">Lote</label>
                    <input type="text" id="lote6" name="lote6">
                    <label for="cantidad">Cantidad Liberada</label>
                    <input type="number" id="cantidad6" name="cantidad6">
                </div>        
                <div>
                    <h2>AREA DE CUMPLIMIENTO</h2>
                    <label for="bitacora6">Cumple con especificaciones de FT o Bitacora de analisis</label>
                    <select name="bitacora6" id="bitacora6">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                    </select>
                    <label for="panificacion6">Cumple los analisis de panificacion?</label>
                    <select name="panificacion6" id="panificacion6">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                    </select>
                    <label for="laboratorios6">Cumple los analisis de laboratorios externos?</label>
                    <select name="laboratorios6" id="laboratorios6">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                        <option value="N/A">N/A</option>
                    </select>
                    <label for="fortificacion6">Cumple los adicion de fortificacion?</label>
                    <select name="fortificacion6" id="fortificacion6">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                    </select>
                    <label for="mejorantes6">Cumple con adicion de mejorantes?</label>
                    <select name="mejorantes6" id="mejorantes6">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                    </select>
                    <label for="empaque6">Cumple las condiciones y empaque rotulado?</label>
                    <select name="empaque6" id="empaque6">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                    </select>
                    <label for="certificado6">Cumple el certificado de calidad?</label>
                    <select name="certificado6" id="certificado6">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                    </select>
                    <button type="button" onclick="agregarLote('harina6')">Agregar Lote</button>
                </div>
            </div>

            <!-- DIVISION ENTRE HARINAS -->

            <div>
            <input type="checkbox" id="harina7" onclick="botonharina7()">
                <label class="labelmen" for="harina7">
                    
                    HARINA DE TRIGO ESPECIAL
                </label>
            </div>
            <div id="contenedor_harina7_lotes">
            </div>
            <div class="contenedor_harina" id="contenedor_harina7" style="display: none;">
                <div> 
                    <label for="referencia">Referencia</label>
                    <select name="referencia7" id="harina7_referencia_3" onchange="handleCustomOption('harina7', 3)">
                        <option value="50 KG">50 KG</option>
                        <option value="25 KG">25 KG</option>
                        <option value="10 KG">10 KG</option>
                        <option value="custom
                        7 ">Otro (Especifique)</option>
                    </select>
                    <input type="text" id="harina7_customInput_3" 
                    placeholder="Escribe aquí..." 
                    style="display: none;"
                    oninput="updateCustomOption('harina7', 3)">
                    <label for="lote">Lote</label>
                    <input type="text" id="lote7" name="lote7">
                    <label for="cantidad7">Cantidad Liberada</label>
                    <input type="number7" id="cantidad7" name="cantidad7">
                </div>        
                <div>
                    <h2>AREA DE CUMPLIMIENTO</h2>
                    <label for="bitacora7">Cumple con especificaciones de FT o Bitacora de analisis</label>
                    <select name="bitacora7" id="bitacora7">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                    </select>
                    <label for="panificacion7">Cumple los analisis de panificacion?</label>
                    <select name="panificaciona7" id="panificacion7">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                    </select>
                    <label for="laboratorios7">Cumple los analisis de laboratorios externos?</label>
                    <select name="laboratorios7" id="laboratorios7">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                        <option value="N/A">N/A</option>
                    </select>
                    <label for="fortificacion7">Cumple los adicion de fortificacion?</label>
                    <select name="fortificacion7" id="fortificacion7">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                    </select>
                    <label for="mejorantes7">Cumple con adicion de mejorantes?</label>
                    <select name="mejorantes7" id="mejorantes7">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                    </select>
                    <label for="empaque7">Cumple las condiciones y empaque rotulado?</label>
                    <select name="empaque7" id="empaque7">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                    </select>
                    <label for="certificado7">Cumple el certificado de calidad?</label>
                    <select name="certificado7" id="certificado7">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                    </select>
                    <button type="button" onclick="agregarLote('harina7')">Agregar Lote</button>
                </div>
            </div>

            <!-- DIVISION ENTRE HARINAS -->

            <div>
            <input type="checkbox" id="harina8" onclick="botonharina8()">
                <label class="labelmen" for="harina8">
                    
                    Mogolla
                </label>
            </div>
            <div id="contenedor_harina8_lotes">
            </div>
            <div class="contenedor_harina" id="contenedor_harina8" style="display: none;">
                <div> 
                    <label for="referencia8">Referencia</label>
                    <select name="referencia8" id="referencia_extrapan">
                        <option value="40 KG">40 KG</option>
                    </select>
                    <label for="lote8">Lote</label>
                    <input type="text" id="lote8" name="lote8" >
                    <label for="cantidad8">Cantidad Liberada</label>
                    <input type="number" id="cantidad8" name="cantidad8" >
                </div>        
                <div>
                    <h2>AREA DE CUMPLIMIENTO</h2>
                    <label for="bitacora8">Cumple con especificaciones de FT o Bitacora de analisis</label>
                    <select name="bitacora8" id="bitacora8">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                    </select>
                    <label for="panificacion8">Cumple los analisis de panificacion?</label>
                    <select name="panificacion8" id="panificacion8">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                    </select>
                    <label for="laboratorios8">Cumple los analisis de laboratorios externos?</label>
                    <select name="laboratorios8" id="laboratorios8">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                        <option value="N/A">N/A</option>
                    </select>
                    <label for="fortificacion8">Cumple los adicion de fortificacion?</label>
                    <select name="fortificacion8" id="fortificacion8">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                    </select>
                    <label for="mejorantes8">Cumple con adicion de mejorantes?</label>
                    <select name="mejorantes8" id="mejorantes8">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                    </select>
                    <label for="empaque8">Cumple las condiciones y empaque rotulado?</label>
                    <select name="empaque8" id="empaque8">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                    </select>
                    <label for="certificado8">Cumple el certificado de calidad?</label>
                    <select name="certificado8" id="certificado8">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                    </select>
                    <button type="button" onclick="agregarLote('harina8')">Agregar Lote</button>
                </div>
            </div>

            <!-- DIVISION ENTRE HARINAS -->

            <div>
            <input type="checkbox" id="harina9" onclick="botonharina9()">
                <label class="labelmen" for="harina9">
                    
                    SALVADO
                </label>
            </div>
            <div id="contenedor_harina9_lotes">
            </div>
            <div class="contenedor_harina" id="contenedor_harina9"style="display: none;">
                <div> 
                    <label for="referencia9">Referencia</label>
                    <select name="referencia9" id="referencia9">
                        <option value="25 KG">25 KG</option>
                    </select>
                    <label for="lote9">Lote</label>
                    <input type="text" id="lote9" name="lote9">
                    <label for="cantidad9">Cantidad Liberada</label>
                    <input type="number" id="cantidad9" name="cantidad9">
                </div>        
                <div>
                    <h2>AREA DE CUMPLIMIENTO</h2>
                    <label for="bitacora9">Cumple con especificaciones de FT o Bitacora de analisis</label>
                    <select name="bitacora9" id="bitacora9">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                    </select>
                    <label for="panificacion9">Cumple los analisis de panificacion?</label>
                    <select name="panificacion9" id="panificacion9">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                    </select>
                    <label for="laboratorios9">Cumple los analisis de laboratorios externos?</label>
                    <select name="laboratorios9" id="laboratorios9">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                        <option value="N/A">N/A</option>
                    </select>
                    <label for="fortificacion9">Cumple los adicion de fortificacion?</label>
                    <select name="fortificacion9" id="fortificacion9">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                    </select>
                    <label for="mejorantes9">Cumple con adicion de mejorantes?</label>
                    <select name="mejorantes9" id="mejorantes9">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                    </select>
                    <label for="empaque9">Cumple las condiciones y empaque rotulado?</label>
                    <select name="empaque9" id="empaque9">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                    </select>
                    <label for="certificado9">Cumple el certificado de calidad?</label>
                    <select name="certificado9" id="certificado9">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                    </select>
                    <button type="button" onclick="agregarLote('harina9')">Agregar Lote</button>
                </div>
            </div>

            <!-- DIVISION ENTRE HARINAS -->

            <div>
            <input type="checkbox" id="harina10" onclick="botonharina10()">
                <label class="labelmen" for="harina10">
                    
                    Segunda
                </label>
            </div>
            <div id="contenedor_harina10_lotes">
            </div>
            <div class="contenedor_harina" id="contenedor_harina10" style="display: none;">
                <div> 
                    <label for="referencia">Referencia</label>
                    <select name="referencia10" id="referencia10">
                        <option value="50 KG">50 KG</option>
                    </select>
                    <label for="lote10">Lote</label>
                    <input type="text" id="lote10" name="lote10">
                    <label for="cantidad10">Cantidad Liberada</label>
                    <input type="number" id="cantidad10" name="cantidad10" >
                </div>        
                <div>
                    <h2>AREA DE CUMPLIMIENTO</h2>
                    <label for="bitacora10">Cumple con especificaciones de FT o Bitacora de analisis</label>
                    <select name="bitacora10" id="bitacora10">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                    </select>
                    <label for="panificacion10">Cumple los analisis de panificacion?</label>
                    <select name="panificacion10" id="panificacion10">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                    </select>
                    <label for="laboratorios10">Cumple los analisis de laboratorios externos?</label>
                    <select name="laboratorios10" id="laboratorios10">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                        <option value="N/A">N/A</option>
                    </select>
                    <label for="fortificacion10">Cumple los adicion de fortificacion?</label>
                    <select name="fortificacion10" id="fortificacion10">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                    </select>
                    <label for="mejorantes10">Cumple con adicion de mejorantes?</label>
                    <select name="mejorantes10" id="mejorantes10">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                    </select>
                    <label for="empaque10">Cumple las condiciones y empaque rotulado?</label>
                    <select name="empaque10" id="empaque10">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                    </select>
                    <label for="certificado10">Cumple el certificado de calidad?</label>
                    <select name="certificado10" id="certificado10">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                    </select>
                    <button type="button" onclick="agregarLote('harina10')">Agregar Lote</button>
                </div>
            </div>

            <!-- DIVISION ENTRE HARINAS -->

            <div>
            <input type="checkbox" id="harina11" onclick="botonharina11()">
                <label class="labelmen" for="harina11">
                    
                    Germen
                </label>
            </div>
            <div id="contenedor_harina11_lotes">
            </div>
            <div class="contenedor_harina" id="contenedor_harina11" style="display: none;">
                <div> 
                    <label for="referencia11">Referencia</label>
                    <select name="referencia11" id="referencia11">
                        <option value="25 KG">25 KG</option>
                    </select>
                    <label for="lote11">Lote</label>
                    <input type="text" id="lote11" name="lote11" >
                    <label for="cantidad11">Cantidad Liberada</label>
                    <input type="number" id="cantidad11" name="cantidad11">
                </div>        
                <div>
                    <h2>AREA DE CUMPLIMIENTO</h2>
                    <label for="bitacora11">Cumple con especificaciones de FT o Bitacora de analisis</label>
                    <select name="bitacora11" id="bitacora11">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                    </select>
                    <label for="panificacion11">Cumple los analisis de panificacion?</label>
                    <select name="panificacion11" id="panificacion11">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                    </select>
                    <label for="laboratorios11">Cumple los analisis de laboratorios externos?</label>
                    <select name="laboratorios11" id="laboratorios11">
                        <option value="">---</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                        <option value="N/A">N/A</option>
                    </select>
                    <label for="fortificacion11">Cumple los adicion de fortificacion?</label>
                    <select name="fortificacion11" id="fortificacion11">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                    </select>
                    <label for="mejorantes11">Cumple con adicion de mejorantes?</label>
                    <select name="mejorantes11" id="mejorantes11">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                    </select>
                    <label for="empaque11">Cumple las condiciones y empaque rotulado?</label>
                    <select name="empaque11" id="empaque11">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                    </select>
                    <label for="certificado11">Cumple el certificado de calidad?</label>
                    <select name="certificado11" id="certificado11">
                        <option value="">---</option>
                        <option value="N/A">No Aplica</option>
                        <option value="Cumple">Cumple</option>
                        <option value="No Cumple">No Cumple</option>
                    </select>
                    <button type="button" onclick="agregarLote('harina11')">Agregar Lote</button>
                </div>
            </div>
            <div>
                    <h3>Harina Extra</h3>
                    <h4>En caso de que se necesite liberar una harina no registrada</h4>
                    <div>
                    <input type="checkbox" id="harina_extra" onclick="botonharinaextra()">
                        <label class="labelmen" for="harina_extra">
                        Harina Extra
                        </label>
                    </div>
                    <div class="contenedor_harina" id="contenedor_harina_extra" style="display: none;">
                        <button type="button" onclick="agregarLote_extra('harina_extra')">Agregar Lote</button>
                            <div id="contenedor_harina_extra_lotes"></div>
                    </div>
                    <div>
                        <input type="submit" value="Enviar">
                    </div>

            </div>
        </div>
        </div>
        </form>
    </div>
    <div class="overlay-fondo"></div>

</body>
<script>
        function botonharina1() {
            const checkbox = document.getElementById('harina1');
            const menu = document.getElementById('contenedor_harina1');
            menu.style.display = checkbox.checked ? 'block' : 'none';
        }
        function botonharina2() {
            const checkbox = document.getElementById('harina2');
            const menu = document.getElementById('contenedor_harina2');
            menu.style.display = checkbox.checked ? 'block' : 'none';
        }
        function botonharina3() {
            const checkbox = document.getElementById('harina3');
            const menu = document.getElementById('contenedor_harina3');
            menu.style.display = checkbox.checked ? 'block' : 'none';
        }
        function botonharina4() {
            const checkbox = document.getElementById('harina4');
            const menu = document.getElementById('contenedor_harina4');
            menu.style.display = checkbox.checked ? 'block' : 'none';
        }
        function botonharina5() {
            const checkbox = document.getElementById('harina5');
            const menu = document.getElementById('contenedor_harina5');
            menu.style.display = checkbox.checked ? 'block' : 'none';
        }
        function botonharina6() {
            const checkbox = document.getElementById('harina6');
            const menu = document.getElementById('contenedor_harina6');
            menu.style.display = checkbox.checked ? 'block' : 'none';
        }
        function botonharina7() {
            const checkbox = document.getElementById('harina7');
            const menu = document.getElementById('contenedor_harina7');
            menu.style.display = checkbox.checked ? 'block' : 'none';
        }
        function botonharina8() {
            const checkbox = document.getElementById('harina8');
            const menu = document.getElementById('contenedor_harina8');
            menu.style.display = checkbox.checked ? 'block' : 'none';
        }
        function botonharina9() {
            const checkbox = document.getElementById('harina9');
            const menu = document.getElementById('contenedor_harina9');
            menu.style.display = checkbox.checked ? 'block' : 'none';
        }
        function botonharina10() {
            const checkbox = document.getElementById('harina10');
            const menu = document.getElementById('contenedor_harina10');
            menu.style.display = checkbox.checked ? 'block' : 'none';
        }
        function botonharina11() {
            const checkbox = document.getElementById('harina11');
            const menu = document.getElementById('contenedor_harina11');
            menu.style.display = checkbox.checked ? 'block' : 'none';
        }
        function botonharina11() {
            const checkbox = document.getElementById('harina11');
            const menu = document.getElementById('contenedor_harina11');
            menu.style.display = checkbox.checked ? 'block' : 'none';
        }
        function botonharinaextra() {
            const checkbox = document.getElementById('harina_extra');
            const menu = document.getElementById('contenedor_harina_extra');
            menu.style.display = checkbox.checked ? 'block' : 'none';
        }

        function handleCustomOption(harinaId, loteId) {
    const select = document.getElementById(`${harinaId}_referencia_${loteId}`);
    const customInput = document.getElementById(`${harinaId}_customInput_${loteId}`);
    if (select.value === 'custom') {
        customInput.style.display = 'inline-block';
    } else {
        customInput.style.display = 'none';
    }
}

function updateCustomOption(harinaId, loteId) {
    const select = document.getElementById(`${harinaId}_referencia_${loteId}`);
    const customInput = document.getElementById(`${harinaId}_customInput_${loteId}`);
    const customOption = Array.from(select.options).find(option => option.value === 'custom');
    
    if (customOption) {
        customOption.value = customInput.value;
        customOption.textContent = customInput.value || 'Otro (Especificar)';
    }
}
    // Funcion de agregar lotes por harina 

    let contadorLotes = {};

function agregarLote(harinaId) {
    // Inicializar el contador de lotes para la harina si no existe
    if (!contadorLotes[harinaId]) {
        contadorLotes[harinaId] = 0;
    }
    contadorLotes[harinaId]++;

    const contenedor = document.getElementById(`contenedor_${harinaId}`);

    // Crear un nuevo div para el lote
    const nuevoLote = document.createElement('div');
nuevoLote.classList.add('lote');
nuevoLote.innerHTML = `
    <h4>Lote ${contadorLotes[harinaId]}</h4>
    <label for="${harinaId}_referencia_${contadorLotes[harinaId]}">Referencia</label>
    <select name="${harinaId}[lote_${contadorLotes[harinaId]}][referencia]" id="${harinaId}_referencia_${contadorLotes[harinaId]}" onchange="handleCustomOption('${harinaId}', ${contadorLotes[harinaId]})">
        <option value="Granel">Granel</option>
        <option value="50 KG">50 KG</option>
        <option value="40 KG">40 KG</option>
        <option value="25 KG">25 KG</option>
        <option value="10 KG">10 KG</option>
        <option value="custom">Otro (Especificar)</option>
    </select>
    <input type="text" id="${harinaId}_customInput_${contadorLotes[harinaId]}" placeholder="Escribe aquí..." style="display: none;" oninput="updateCustomOption('${harinaId}', ${contadorLotes[harinaId]})">
    <label for="${harinaId}_lote_${contadorLotes[harinaId]}">Lote</label>
    <input type="text" name="${harinaId}[lote_${contadorLotes[harinaId]}][lote]" id="${harinaId}_lote_${contadorLotes[harinaId]}">
    <label for="${harinaId}_cantidad_${contadorLotes[harinaId]}">Cantidad Liberada</label>
    <input type="number" name="${harinaId}[lote_${contadorLotes[harinaId]}][cantidad]" id="${harinaId}_cantidad_${contadorLotes[harinaId]}">
    <label for="${harinaId}_bitacora_${contadorLotes[harinaId]}">Cumple con especificaciones de FT o Bitacora de análisis</label>
    <select name="${harinaId}[lote_${contadorLotes[harinaId]}][bitacora]" id="${harinaId}_bitacora_${contadorLotes[harinaId]}">
        <option value="">--</option>
        <option value="Cumple">Cumple</option>
        <option value="N/A">No Aplica</option>
        <option value="No Cumple">No Cumple</option>
    </select>
    <label for="${harinaId}_panificacion_${contadorLotes[harinaId]}">Cumple los análisis de panificación?</label>
    <select name="${harinaId}[lote_${contadorLotes[harinaId]}][panificacion]" id="${harinaId}_panificacion_${contadorLotes[harinaId]}">
    <option value="">--</option>
        <option value="Cumple">Cumple</option>
        <option value="N/A">No Aplica</option>
        <option value="No Cumple">No Cumple</option>
    </select>
    <label for="${harinaId}_laboratorios_${contadorLotes[harinaId]}">Cumple los análisis de laboratorios externos?</label>
    <select name="${harinaId}[lote_${contadorLotes[harinaId]}][laboratorios]" id="${harinaId}_laboratorios_${contadorLotes[harinaId]}">
    <option value="">--</option>
        <option value="Cumple">Cumple</option>
        <option value="No Cumple">No Cumple</option>
        <option value="N/A">N/A</option>
    </select>
    <label for="${harinaId}_fortificacion_${contadorLotes[harinaId]}">Cumple los adición de fortificación?</label>
    <select name="${harinaId}[lote_${contadorLotes[harinaId]}][fortificacion]" id="${harinaId}_fortificacion_${contadorLotes[harinaId]}">
    <option value="">--</option>
        <option value="Cumple">Cumple</option>
        <option value="N/A">No Aplica</option>
        <option value="No Cumple">No Cumple</option>
    </select>
    <label for="${harinaId}_mejorantes_${contadorLotes[harinaId]}">Cumple con adición de mejorantes?</label>
    <select name="${harinaId}[lote_${contadorLotes[harinaId]}][mejorantes]" id="${harinaId}_mejorantes_${contadorLotes[harinaId]}">
    <option value="">--</option>
        <option value="N/A">No Aplica</option>0
        <option value="Cumple">Cumple</option>
        <option value="No Cumple">No Cumple</option>
    </select>
    <label for="${harinaId}_empaque_${contadorLotes[harinaId]}">Cumple las condiciones y empaque rotulado?</label>
    <select name="${harinaId}[lote_${contadorLotes[harinaId]}][empaque]" id="${harinaId}_empaque_${contadorLotes[harinaId]}">
    <option value="">--</option>
        <option value="Cumple">Cumple</option>
        <option value="N/A">No Aplica</option>
        <option value="No Cumple">No Cumple</option>
    </select>
    <label for="${harinaId}_certificado_${contadorLotes[harinaId]}">Cumple el certificado de calidad?</label>
    <select name="${harinaId}[lote_${contadorLotes[harinaId]}][certificado]" id="${harinaId}_certificado_${contadorLotes[harinaId]}">
    <option value="">--</option>
        <option value="Cumple">Cumple</option>
        <option value="N/A">No Aplica</option>
        <option value="No Cumple">No Cumple</option>
    </select>
    <button type="button" onclick="eliminarLote(this)">Eliminar Lote</button>
`;

    contenedor.appendChild(nuevoLote);
}

function eliminarLote(boton) {
    const lote = boton.parentElement;
    lote.remove();
}


function updateCustomOption(harinaId, loteId) {
    const select = document.getElementById(`${harinaId}_referencia_${loteId}`);
    const customInput = document.getElementById(`${harinaId}_customInput_${loteId}`);
    const customOption = Array.from(select.options).find(option => option.value === 'custom');
    customOption.value = customInput.value;
    customOption.textContent = customInput.value || 'Otro (Especificar)';
}

// FUNCION PARA HARINAS EXTRAS 

function agregarLote_extra(harinaId) {
    // Inicializar el contador de lotes para la harina si no existe
    if (!contadorLotes[harinaId]) {
        contadorLotes[harinaId] = 0;
    }
    contadorLotes[harinaId]++;

    const contenedor = document.getElementById(`contenedor_${harinaId}_lotes`);

    // Crear un nuevo div para el lote
    const nuevoLote = document.createElement('div');
    nuevoLote.classList.add('lote');
    nuevoLote.innerHTML = `
        <h4>Lote ${contadorLotes[harinaId]}</h4>
        <label for="${harinaId}_nombre_harina_${contadorLotes[harinaId]}">Nombre de la Harina</label>
        <input type="text" name="${harinaId}[lote_${contadorLotes[harinaId]}][nombre_harina]" id="${harinaId}_nombre_harina_${contadorLotes[harinaId]}" placeholder="Escribe el nombre de la harina" required>
        <label for="${harinaId}_referencia_${contadorLotes[harinaId]}">Referencia</label>
        <select name="${harinaId}[lote_${contadorLotes[harinaId]}][referencia]" id="${harinaId}_referencia_${contadorLotes[harinaId]}" onchange="handleCustomOption('${harinaId}', ${contadorLotes[harinaId]})">
            <option value="50 KG">50 KG</option>
            <option value="25 KG">25 KG</option>
            <option value="10 KG">10 KG</option>
            <option value="custom">Otro (Especificar)</option>
        </select>
        <input type="text" id="${harinaId}_customInput_${contadorLotes[harinaId]}" placeholder="Escribe aquí..." style="display: none;" oninput="updateCustomOption('${harinaId}', ${contadorLotes[harinaId]})">
        <label for="${harinaId}_lote_${contadorLotes[harinaId]}">Lote</label>
        <input type="text" name="${harinaId}[lote_${contadorLotes[harinaId]}][lote]" id="${harinaId}_lote_${contadorLotes[harinaId]}" required>
        <label for="${harinaId}_cantidad_${contadorLotes[harinaId]}">Cantidad Liberada</label>
        <input type="number" name="${harinaId}[lote_${contadorLotes[harinaId]}][cantidad]" id="${harinaId}_cantidad_${contadorLotes[harinaId]}" required>
        <label for="${harinaId}_bitacora_${contadorLotes[harinaId]}">Cumple con especificaciones de FT o Bitacora de análisis</label>
        <select name="${harinaId}[lote_${contadorLotes[harinaId]}][bitacora]" id="${harinaId}_bitacora_${contadorLotes[harinaId]}">
        <option value="">--</option>
            <option value="Cumple">Cumple</option>
            <option value="N/A">No Aplica</option>
            <option value="No Cumple">No Cumple</option>
        </select>
        <label for="${harinaId}_panificacion_${contadorLotes[harinaId]}">Cumple los análisis de panificación?</label>
        <select name="${harinaId}[lote_${contadorLotes[harinaId]}][panificacion]" id="${harinaId}_panificacion_${contadorLotes[harinaId]}">
        <option value="">--</option>
            <option value="Cumple">Cumple</option>
            <option value="N/A">No Aplica</option>
            <option value="No Cumple">No Cumple</option>
        </select>
        <label for="${harinaId}_laboratorios_${contadorLotes[harinaId]}">Cumple los análisis de laboratorios externos?</label>
        <select name="${harinaId}[lote_${contadorLotes[harinaId]}][laboratorios]" id="${harinaId}_laboratorios_${contadorLotes[harinaId]}">
        <option value="">--</option>
            <option value="Cumple">Cumple</option>
            <option value="N/A">No Aplica</option>
            <option value="No Cumple">No Cumple</option>
            <option value="N/A">N/A</option>
        </select>
        <label for="${harinaId}_fortificacion_${contadorLotes[harinaId]}">Cumple los adición de fortificación?</label>
        <select name="${harinaId}[lote_${contadorLotes[harinaId]}][fortificacion]" id="${harinaId}_fortificacion_${contadorLotes[harinaId]}">
        <option value="">--</option>
            <option value="Cumple">Cumple</option>
            <option value="N/A">No Aplica</option>
            <option value="No Cumple">No Cumple</option>
        </select>
        <label for="${harinaId}_mejorantes_${contadorLotes[harinaId]}">Cumple con adición de mejorantes?</label>
        <select name="${harinaId}[lote_${contadorLotes[harinaId]}][mejorantes]" id="${harinaId}_mejorantes_${contadorLotes[harinaId]}">
        <option value="">--</option>
            <option value="Cumple">Cumple</option>
            <option value="N/A">No Aplica</option>
            <option value="No Cumple">No Cumple</option>
        </select>
        <label for="${harinaId}_empaque_${contadorLotes[harinaId]}">Cumple las condiciones y empaque rotulado?</label>
        <select name="${harinaId}[lote_${contadorLotes[harinaId]}][empaque]" id="${harinaId}_empaque_${contadorLotes[harinaId]}">
        <option value="">--</option>
            <option value="Cumple">Cumple</option>
            <option value="N/A">No Aplica</option>
            <option value="No Cumple">No Cumple</option>
        </select>
        <label for="${harinaId}_certificado_${contadorLotes[harinaId]}">Cumple el certificado de calidad?</label>
        <select name="${harinaId}[lote_${contadorLotes[harinaId]}][certificado]" id="${harinaId}_certificado_${contadorLotes[harinaId]}">
        <option value="">--</option>
            <option value="Cumple">Cumple</option>
            <option value="N/A">No Aplica</option>
            <option value="No Cumple">No Cumple</option>
        </select>
        <button type="button" onclick="eliminarLote(this)">Eliminar Lote</button>
    `;

    contenedor.appendChild(nuevoLote);
}


</script>
</html>