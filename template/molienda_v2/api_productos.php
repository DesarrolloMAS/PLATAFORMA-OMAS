<?php
require '../sesion.php';
verificarAutenticacion();

header('Content-Type: application/json');

$sede = $_GET['sede'] ?? $_SESSION['sede'];
if (!in_array($sede, ['ZC', 'ZS'])) {
    echo json_encode(['status' => 'error', 'message' => 'Sede inválida']);
    exit;
}

$config_file = "../../archivos/generados/molienda/config_{$sede}.json";

// Valores por defecto para inicialización suave
$default_config = [
    'harinas' => [],
    'subproductos' => [],
    'materiales' => []
];

if ($sede === 'ZC') {
    $default_config['harinas'] = [
        ['id' => 'extrapan_x50', 'name' => 'EXTRAPAN X50', 'weight' => 50],
        ['id' => 'extrapan_x25', 'name' => 'EXTRAPAN X25', 'weight' => 25],
        ['id' => 'extrapan_x10', 'name' => 'EXTRAPAN X10', 'weight' => 10],
        ['id' => 'artesanal_x50', 'name' => 'ARTESANAL X50', 'weight' => 50],
        ['id' => 'artesanal_x25', 'name' => 'ARTESANAL X25', 'weight' => 25],
        ['id' => 'fuerte_x25', 'name' => 'FUERTE X25', 'weight' => 25],
        ['id' => 'natural_x50', 'name' => 'NATURAL X50', 'weight' => 50],
        ['id' => 'especial_x50', 'name' => 'ESPECIAL X50', 'weight' => 50],
        ['id' => 'especial_x25', 'name' => 'ESPECIAL X25', 'weight' => 25],
        ['id' => 'exclusiva_x50', 'name' => 'EXCLUSIVA X50', 'weight' => 50],
        ['id' => 'desarrollo_x1', 'name' => 'DESARROLLO X1', 'weight' => 1],
        ['id' => 'alta_proteina', 'name' => 'HARINA ALTA PROTEINA X1', 'weight' => 1],
        ['id' => 'media_proteina', 'name' => 'HARINA MEDIA PROTEINA X1', 'weight' => 1],
        ['id' => 'baja_proteina', 'name' => 'HARINA BAJA PROTEINA X1', 'weight' => 1],
        ['id' => 'artesanal_kg', 'name' => 'ARTESANAL X1', 'weight' => 1],
        ['id' => 'fuerte_exp', 'name' => 'FUERTE EXPORTACION', 'weight' => 1],
        ['id' => 'harina_t1_x50', 'name' => 'HARINA T1 X 50', 'weight' => 50]
    ];
} else {
    $default_config['harinas'] = [
        ['id' => 'extrapan_x50', 'name' => 'EXTRAPAN X50', 'weight' => 50],
        ['id' => 'extrapan_x25', 'name' => 'EXTRAPAN X25', 'weight' => 25],
        ['id' => 'extrapan_x10', 'name' => 'EXTRAPAN X10', 'weight' => 10],
        ['id' => 'extrapan_x10_5u', 'name' => 'EXTRAPAN X10 (5 UND)', 'weight' => 10],
        ['id' => 'artesanal_x50', 'name' => 'ARTESANAL X50', 'weight' => 50],
        ['id' => 'artesanal_x25', 'name' => 'ARTESANAL X25', 'weight' => 25],
        ['id' => 'artesanal_x10', 'name' => 'ARTESANAL X10', 'weight' => 10],
        ['id' => 'narino_2500', 'name' => 'NARIÑO 2500 GR', 'weight' => 50],
        ['id' => 'narino_1000', 'name' => 'NARIÑO 1000 GR', 'weight' => 50],
        ['id' => 'narino_500', 'name' => 'NARIÑO 500 GR', 'weight' => 12.5],
        ['id' => 'integral_x25', 'name' => 'INTEGRAL X25', 'weight' => 25],
        ['id' => 'germen_x25', 'name' => 'GERMEN DE TRIGO X25', 'weight' => 25],
        ['id' => 'semola_fina_x25', 'name' => 'SEMOLA FINA X25', 'weight' => 25]
    ];
}

// Subproductos base
$default_sub = [
    ['id' => 'salvado_x25', 'name' => 'SALVADO X25', 'weight' => 25],
    ['id' => 'salvado_x30', 'name' => 'SALVADO X30', 'weight' => 30],
    ['id' => 'mogolla_x40', 'name' => 'MOGOLLA X40', 'weight' => 40],
    ['id' => 'segunda_x50', 'name' => 'SEGUNDA PREMIUM X50', 'weight' => 50],
    ['id' => 'semola_fina_x25', 'name' => 'SEMOLA FINA X25', 'weight' => 25],
    ['id' => 'semola_gruesa_x25', 'name' => 'SEMOLA GRUESA X25', 'weight' => 25],
    ['id' => 'germen_x25_sub', 'name' => 'GERMEN DE TRIGO X25', 'weight' => 25],
    ['id' => 'granza', 'name' => 'GRANZA', 'weight' => 1],
    ['id' => 'granza_x50', 'name' => 'GRANZA 50KG', 'weight' => 50],
    ['id' => 'vitamina_sub', 'name' => 'VITAMINA', 'weight' => 1],
    ['id' => 'hilo_sub', 'name' => 'HILO', 'weight' => 1]
];
$default_config['subproductos'] = $default_sub;

// Materiales base
$default_mat = [
    ['id' => 'emp_extrapan_x50', 'name' => 'EMPAQUE EXTRA PAN 50', 'weight' => 1],
    ['id' => 'emp_extrapan_x25', 'name' => 'EMPAQUE EXTRA PAN 25', 'weight' => 1],
    ['id' => 'emp_extrapan_x10', 'name' => 'EMPAQUE EXTRAPAN X10', 'weight' => 1],
    ['id' => 'emp_galeras_rojo_x50', 'name' => 'EMPAQUE GALERAS ROJO', 'weight' => 1],
    ['id' => 'emp_galeras_verde_x50', 'name' => 'EMPAQUE GALERAS VERDE', 'weight' => 1],
    ['id' => 'emp_galeras_cafe_x50', 'name' => 'EMPAQUE GALERAS CAFE', 'weight' => 1],
    ['id' => 'emp_galeras_azul_x50', 'name' => 'EMPAQUE GALERAS AZUL', 'weight' => 1],
    ['id' => 'emp_galeras_naranja_x25', 'name' => 'EMPAQUE GALERAS NARANJA', 'weight' => 1],
    ['id' => 'emp_galeras_kraft_x25', 'name' => 'EMPAQUE GALERAS KRAFT', 'weight' => 1],
    ['id' => 'emp_multi_beige_x25', 'name' => 'EMPAQUE MULTI BEIGE', 'weight' => 1],
    ['id' => 'emp_galeras_mog_x40', 'name' => 'EMPAQUE GALERAS MOG', 'weight' => 1],
    ['id' => 'emp_galeras_sal_x25', 'name' => 'EMPAQUE GALERAS SAL', 'weight' => 1],
    ['id' => 'emp_galeras_seg_x50', 'name' => 'EMPAQUE GALERAS SEG', 'weight' => 1],
    ['id' => 'vitaminamat', 'name' => 'VITAMINA (MATERIAL)', 'weight' => 1],
    ['id' => 'mejorante_extrapan', 'name' => 'MEJORANTE EXTRA PAN', 'weight' => 1],
    ['id' => 'mejorante_artesanal', 'name' => 'MEJORANTE ARTESANAL', 'weight' => 1],
    ['id' => 'hilo_blanco', 'name' => 'HILO BLANCO', 'weight' => 1],
    ['id' => 'hilo_verde', 'name' => 'HILO VERDE', 'weight' => 1],
    ['id' => 'hilo_naranja', 'name' => 'HILO NARANJA', 'weight' => 1]
];
$default_config['materiales'] = $default_mat;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if ($input) {
        // Asegurarse de que el directorio existe
        if (!is_dir("../../archivos/generados/molienda")) {
            mkdir("../../archivos/generados/molienda", 0777, true);
        }
        file_put_contents($config_file, json_encode($input, JSON_PRETTY_PRINT));
        echo json_encode(['status' => 'ok', 'message' => 'Configuración guardada correctamente.', 'data' => $input]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Payload inválido']);
    }
} else {
    // Es un GET
    if (!file_exists($config_file)) {
        // Inicializar archivo
        if (!is_dir("../../archivos/generados/molienda")) {
            mkdir("../../archivos/generados/molienda", 0777, true);
        }
        file_put_contents($config_file, json_encode($default_config, JSON_PRETTY_PRINT));
        echo json_encode(['status' => 'ok', 'data' => $default_config]);
    } else {
        $data = json_decode(file_get_contents($config_file), true);
        echo json_encode(['status' => 'ok', 'data' => $data]);
    }
}
