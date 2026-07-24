<?php
require '../sesion.php';
require '../conection.php'; 
$sede = $_SESSION['sede'];
if ($sede === 'ZC') {
    $productos = [
    'Mogolla' => 'Empaque Galeras Mogolla',
    'Salvado' => 'Empaque Galeras Salvado',
    'Fuerte x25' => 'Empaque Galeras Letra Naranja X25',
    'Natural x50' => 'Empaque Galeras Letra Verde X50',
    'Harina de Centeno' => 'Empaque Galeras Multi Beige X25',
    'Exclusiva x50' => 'Empaque Galeras Letra Cafe X50',
    'Artesanal x50' => 'Empaque Galeras Letra Roja X50',
    'Artesanal x25'=> 'Empaque Galeras Papel Kraft X25',
    'Extrapan x50'=> 'Empaque Extrapan X50',
    'Extrapan x25' => 'Empaque Extrapan x25',
    'Extrapan x10' => 'Empaque Extrapan Laminado x10',
    'Extrapan x11.4'=> 'Empaque Extrapan x11.4',
    'Segunda' => 'Empaque Galeras Segunda',
    'Fuerte de Exportación' => 'Empaque Harina Fuerte de Exportación',
    'Especial x50' => 'Empaque Galeras Letra Azul X50',
    'Especial x25' => 'Empaque Galeras Letra Naranja X25',
    'Harina T1 x50'=> 'Empaque Galeras Letra Verde X50',
    'Harina Integral' => 'Empaque Galeras Multi Beige X25',
    'Grano entero fino' => 'Empaque Galeras Multi Beige X25',
    'Trigo entero' => 'Empaque Galeras Multi Beige X25',
    'Manitoba'=> 'Empaque Galeras Letra Naranja X25',
    'Centeno Pepa'=> 'Empaque Galeras Multi Beige X25'
];
} else {
    $productos = [
        'Mogolla' => 'Empaque Galeras Mogolla',
        'Salvado' => 'Empaque Galeras Salvado',
        'Extrapan x50' => 'Empaque Extrapan x50',
        'Extrapan x25' => 'Empaque Extrapan x25',
        'Extrapan x10' => 'Empaque Extrapan x10',
        'Artesanal x50' => 'Empaque Galeras Rojo x50',
        'Natural x50' => 'Empaque Galeras Verde x50',
        'Exclusiva x50' => 'Empaque Galeras Cafe x50',
        'Especial x50' => 'Empaque Galeras Azul x50',
        'Harina Fuerte x50' => 'Empaque Galeras Naranja x50',
        'Artesanal Kraft x50' => 'Empaque Galeras Kraft x50',
        'Harina Integral' => 'Empaque Galeras Biege x50',
        'Segunda' => 'Empaque Galeras Segunda'
    ];
}


?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Galería de Empaques</title>
    <link rel="stylesheet" href="../../css/galeria_envasado.css">
    <a href="../redireccion.php">VOLVER</a>
</head>
<body class="body">
    <h1>Galería de Empaques</h1>
    <div class="galeria">
        <?php foreach ($productos as $producto => $empaque): ?>
            <div class="producto">
                <h3><?php echo htmlspecialchars($producto); ?></h3>
                <form action="envasado.php" method="get">
                    <input type="hidden" name="harina" value="<?php echo htmlspecialchars($producto); ?>">
                    <input type="hidden" name="empaque" value="<?php echo htmlspecialchars($empaque); ?>">
                    <button type="submit">Seleccionar</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>