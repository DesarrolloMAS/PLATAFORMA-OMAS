<?php
require '../sesion.php';

if (!isset($_SESSION['nombre']) || empty($_SESSION['sede'])) {
    header('Location: ../../index.php');
    exit;
}

$sede = $_SESSION['sede'];
$target_file = $_GET['file'] ?? '';

if (empty($target_file)) die("Archivo no especificado.");

$ruta_json = "../../archivos/generados/permiso_trabajo/" . preg_replace('/[^A-Za-z0-9_-]/', '', $sede) . "/" . basename($target_file);
if (!file_exists($ruta_json)) die("El archivo no existe.");

$registros = json_decode(file_get_contents($ruta_json), true) ?: [];
if (empty($registros)) die("El archivo está vacío.");

usort($registros, function($a, $b) {
    return strtotime($b['timestamp'] ?? '') <=> strtotime($a['timestamp'] ?? '');
});

function e($v) { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Visor Permiso de Trabajo</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Roboto', sans-serif; font-size: 8pt; margin: 0; padding: 20px; background: #e2e8f0; }
        .action-bar { margin-bottom: 20px; padding: 15px; background: #0f172a; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; }
        .btn-print { background: #10B981; color: #fff; border: none; padding: 10px 20px; border-radius: 4px; font-weight: bold; cursor: pointer; }
        .btn-back { color: #00f0ff; text-decoration: none; border: 1px solid #00f0ff; padding: 8px 15px; border-radius: 4px; }
        
        .page-wrap { max-width: 100%; margin: 0 auto; background: #fff; padding: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); margin-bottom: 20px; page-break-after: always; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        td, th { border: 1px solid #000; padding: 4px; vertical-align: top; }
        
        .header-table { margin-bottom: 15px; }
        .header-table td { text-align: center; vertical-align: middle; }
        .header-title-main { font-size: 10pt; font-weight: bold; }
        .header-title-doc { font-size: 12pt; font-weight: bold; }
        
        .section-title { background: #1e3a8a; color: #fff; font-weight: bold; text-align: center; text-transform: uppercase; font-size: 9pt; padding: 4px; }
        
        .label { font-weight: bold; background: #f1f5f9; width: 20%; font-size: 8pt; }
        .val { width: 30%; font-size: 8pt; }
        
        .sub-table th { background: #e2e8f0; font-size: 7.5pt; text-align: left; }
        
        @media print {
            body { background: #fff; padding: 0; }
            .action-bar { display: none !important; }
            .page-wrap { box-shadow: none; padding: 0; margin: 0; }
            @page { size: portrait; margin: 10mm; }
        }
    </style>
</head>
<body>

<div class="action-bar">
    <a href="rev_permiso_trabajo.php" class="btn-back">← Volver</a>
    <button class="btn-print" onclick="window.print()">🖨️ IMPRIMIR PDF</button>
</div>

<?php foreach ($registros as $idx => $reg): $d = $reg['datos']; ?>
<div class="page-wrap">
    
    <table class="header-table">
        <tr>
            <td style="width:20%;"><img src="/img/logo_empresa.jpeg" alt="Logo" style="max-height:60px;"></td>
            <td style="width:60%;">
                <div class="header-title-main">Sistema de Gestión de Seguridad y Salud en el Trabajo</div>
                <div class="header-title-doc">Permiso de Trabajo - HSEQ</div>
            </td>
            <td style="width:20%; text-align:left; font-size:7pt;">
                Código: FOR-HSEQ-005<br>
                Versión: 2<br>
                Fecha Emisión: 8/5/2021<br>
                Registro: <?= e($reg['timestamp']) ?>
            </td>
        </tr>
    </table>

    <div class="section-title">1. GENERALIDADES</div>
    <table>
        <tr>
            <td class="label">Frecuencia</td><td class="val"><?= e($d['frecuencia']) ?></td>
            <td class="label">Área</td><td class="val"><?= e($d['area_realiza']) ?></td>
        </tr>
        <tr>
            <td class="label">Tipo Trabajo</td><td class="val"><?= e($d['tipo_trabajo']) ?></td>
            <td class="label">Altura aprox.</td><td class="val"><?= e($d['altura_aprox']) ?></td>
        </tr>
        <tr>
            <td class="label">Fecha Inicio</td><td class="val"><?= e($d['fecha_inicio']) ?></td>
            <td class="label">Fecha Fin</td><td class="val"><?= e($d['fecha_fin']) ?></td>
        </tr>
        <tr>
            <td class="label">Hora Inicio</td><td class="val"><?= e($d['hora_inicio']) ?></td>
            <td class="label">Hora Fin</td><td class="val"><?= e($d['hora_fin']) ?></td>
        </tr>
        <tr>
            <td class="label">Ciudad</td><td class="val"><?= e($d['ciudad']) ?></td>
            <td class="label">Sede</td><td class="val"><?= e($reg['sede_sys']) ?></td>
        </tr>
        <tr>
            <td class="label" colspan="4" style="text-align:center;">Descripción de la Actividad</td>
        </tr>
        <tr>
            <td colspan="4"><?= nl2br(e($d['descripcion_actividad'])) ?></td>
        </tr>
    </table>

    <div class="section-title">2. TRABAJADORES AUTORIZADOS Y RESPONSABLES</div>
    <table class="sub-table">
        <tr>
            <th>Nombres y Apellidos</th>
            <th>Tipo Doc</th>
            <th>N° Documento</th>
            <th>Fecha Curso</th>
            <th>Día</th>
            <th>Firma</th>
        </tr>
        <?php 
        $nombres = $d['t_nombre'] ?? []; 
        for($i=0; $i<count($nombres); $i++): 
        ?>
        <tr>
            <td><?= e($d['t_nombre'][$i] ?? '') ?></td>
            <td><?= e($d['t_tipo'][$i] ?? '') ?></td>
            <td><?= e($d['t_doc'][$i] ?? '') ?></td>
            <td><?= e($d['t_fecha_curso'][$i] ?? '') ?></td>
            <td><?= e($d['t_dia'][$i] ?? '') ?></td>
            <td><?= e($d['t_firma'][$i] ?? '') ?></td>
        </tr>
        <?php endfor; ?>
    </table>
    
    <table>
        <tr><td class="label" style="width:30%">Vigía Designado</td><td><?= e($d['vigia_nombre']) ?> | <?= e($d['vigia_doc']) ?> | <?= e($d['vigia_firma']) ?></td></tr>
        <tr><td class="label" style="width:30%">Resp. Plan de Emergencia</td><td><?= e($d['emergencia_nombre']) ?> | <?= e($d['emergencia_doc']) ?> | <?= e($d['emergencia_firma']) ?></td></tr>
    </table>

    <div class="section-title">3. EQUIPOS REQUERIDOS Y SISTEMAS</div>
    <table style="font-size:7pt;">
        <tr>
            <td>Arnés: <b><?= e($d['eq_arnes']) ?></b></td>
            <td>Línea Vida Vert: <b><?= e($d['eq_lv_vertical']) ?></b></td>
            <td>Eslinga Posicionamiento: <b><?= e($d['eq_eslinga_pos']) ?></b></td>
            <td>Línea Vida Horiz: <b><?= e($d['eq_lv_horizontal']) ?></b></td>
        </tr>
        <tr>
            <td>Eslinga Absorbedor: <b><?= e($d['eq_eslinga_abs']) ?></b></td>
            <td>Anclaje Fijo: <b><?= e($d['eq_anclaje_fijo']) ?></b></td>
            <td>Freno: <b><?= e($d['eq_freno']) ?></b></td>
            <td>Anclaje Móvil: <b><?= e($d['eq_anclaje_movil']) ?></b></td>
        </tr>
        <tr>
            <td>Red Seguridad: <b><?= e($d['eq_red_seg']) ?></b></td>
            <td>Malla Restricción: <b><?= e($d['eq_malla']) ?></b></td>
            <td colspan="2">Sistema Implementar: <b><?= e($d['sistema_tipo']) ?></b> | Acceso: <b><?= e($d['sistema_acceso']) ?></b></td>
        </tr>
    </table>

    <div class="section-title">4. MEDIDAS Y EPP</div>
    <table style="font-size:7pt;">
        <tr>
            <td colspan="4" class="label">Medidas de Prevención</td>
        </tr>
        <tr>
            <td>Análisis peligros: <?= e($d['prev_peligros']) ?></td>
            <td>Capacitación: <?= e($d['prev_cap']) ?></td>
            <td>Sistemas Ing: <?= e($d['prev_ing']) ?></td>
            <td>Procedimientos: <?= e($d['prev_proc']) ?> | Suspensión: <?= e($d['prev_susp']) ?></td>
        </tr>
        <tr>
            <td colspan="4" class="label">Medidas Colectivas</td>
        </tr>
        <tr>
            <td>Delimitación: <?= e($d['col_delim']) ?></td>
            <td>Acceso: <?= e($d['col_acceso']) ?></td>
            <td>Advertencia: <?= e($d['col_adv']) ?></td>
            <td>Desniveles: <?= e($d['col_desniv']) ?></td>
        </tr>
        <tr>
            <td>Señalización: <?= e($d['col_senal']) ?></td>
            <td>Superficies: <?= e($d['col_sup']) ?></td>
            <td>Bandas: <?= e($d['col_bandas']) ?></td>
            <td>Ayudante: <?= e($d['col_vigia']) ?></td>
        </tr>
        <tr>
            <td colspan="4" class="label">EPP Principales (Resumen)</td>
        </tr>
        <tr>
            <td>Casco: <?= e($d['epp_0'] ?? '') ?></td>
            <td>Auditivo: <?= e($d['epp_1'] ?? '') ?></td>
            <td>Respiratorio: <?= e($d['epp_4'] ?? '') ?></td>
            <td>Botas: <?= e($d['epp_7'] ?? '') ?></td>
        </tr>
    </table>

    <div class="section-title">5. VERIFICACIÓN DIARIA</div>
    <table style="font-size:7pt;" class="sub-table">
        <?php for($i=0; $i<16; $i++): if(isset($d["preg_t_$i"])): ?>
        <tr>
            <td style="width:70%"><?= e($d["preg_t_$i"]) ?></td>
            <td style="width:15%">Día: <?= e($d["preg_d_$i"] ?? '') ?></td>
            <td style="width:15%"><b><?= e($d["preg_v_$i"] ?? '') ?></b></td>
        </tr>
        <?php endif; endfor; ?>
    </table>

    <div class="section-title">6. OBSERVACIONES Y AUTORIZACIÓN</div>
    <table>
        <tr>
            <td style="width:50%;"><b>Observaciones:</b><br><?= nl2br(e($d['observaciones'])) ?></td>
            <td style="width:50%;">
                ¿Aprueba?: <b><?= e($d['aprueba']) ?></b><br>
                Motivo Negación: <?= e($d['motivo_negacion']) ?>
            </td>
        </tr>
    </table>

    <table style="font-size:7pt;" class="sub-table">
        <tr>
            <th>Rol</th><th>Nombre</th><th>Identificación</th><th>Firma</th>
        </tr>
        <tr>
            <td>Jefe Encargado</td><td><?= e($d['firma_n_0']??'') ?></td><td><?= e($d['firma_id_0']??'') ?></td><td><?= e($d['firma_f_0']??'') ?></td>
        </tr>
        <tr>
            <td>Jefe Suplente</td><td><?= e($d['firma_n_1']??'') ?></td><td><?= e($d['firma_id_1']??'') ?></td><td><?= e($d['firma_f_1']??'') ?></td>
        </tr>
        <tr>
            <td>Coord. Trabajo Seguro</td><td><?= e($d['firma_n_2']??'') ?></td><td><?= e($d['firma_id_2']??'') ?></td><td><?= e($d['firma_f_2']??'') ?></td>
        </tr>
    </table>
    
    <div style="font-size:6pt; margin-top:5px; text-align:justify; color:#555;">
        <b>NOTA NORMATIVA:</b> El presente Permiso de Trabajo se emite y ejecuta en cumplimiento de la normatividad vigente en Seguridad y Salud en el Trabajo en Colombia, especialmente lo establecido en la Resolución 4272 de 2021, la Resolución 0491 de 2020 y el RETIE, aplicable a las actividades que involucren riesgo eléctrico o intervención de instalaciones energizadas.
    </div>

</div>
<?php endforeach; ?>

</body>
</html>
