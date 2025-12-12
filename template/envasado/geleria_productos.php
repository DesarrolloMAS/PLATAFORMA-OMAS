<?php
require '../sesion.php';
require '../conection.php'; 
$sede = $_SESSION['sede'];
if ($sede === 'ZC') {
    $productos = [
    'Mogolla',
    'Salvado',
    'Fuerte x25',
    'Natural x50',
    'Centeno',
    'Exclusiva x50',
    'Artesanal x50',
    'Artesanal x25',
    'Extrapan x50',
    'Extrapan x25',
    'Extrapan x10',
    'Segunda',
    'Fuerte de Exportación',
    'Especial x50',
    'Especial x25',
    'Harina T1 x50',
    'Fuerte Exportacion',
    'Harina de centeno',
    'Harina Integral',
    'Grano entero fino',
    'Trigo entero',
    'Manitoba',
    'Centeno Pepa'
];
} else {
    $productos = [
        'Mogolla',
        'Salvado',
        'Empaque Extrapan x50',
        'Empaque Extrapan x25',
        'Empaque Extrapan x10',
        'Empaque Galeras Rojo x50',
        'Empaque Galeras Verde x50',
        'Empaque Galeras Cafe x50',
        'Empaque Galeras Azul x50',
        'Empaque Galeras Naranja x50',
        'Empaque Galeras Kraft x50',
        'Empaque Galeras Biege x50',
        'Segunda'
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
        <?php foreach ($productos as $producto): ?>
            <div class="producto">
                <h3><?php echo htmlspecialchars($producto); ?></h3>
                <form action="envasado.php" method="get">
                    <input type="hidden" name="harina" value="<?php echo htmlspecialchars($producto); ?>">
                    <button type="submit">Seleccionar</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>