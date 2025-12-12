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
    'Mogolla x50',
    'Salvado x30',
    'Granza x50',
    'Segunda x50',
    'Artesanal x50',
    'Artesanal x25',
    'Artesanal x10',
    'Artesanal x10 (5 und)',
    'Germen de Trigo x25',
    'Harina Integral x25',
    'Harina de Trigo Nariño x10',
    'Harina de Trigo Nariño x10 5 und',
    'Extrapan x50',
    'Extrapan x25',
    'Extrapan x10',
    'Germen de Trigo x25',
    'Semola Fina x25',
    'Harina de Trigo Nariño x2500GR',
    'Harina de Trigo Nariño x1000GR',
    'Harina de Trigo Nariño x500GR',
    'Harina de Trigo Extrapan x10 (5 und)'
];
}


?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Galería de Productos</title>
    <link rel="stylesheet" href="/css/cantidad-producto_galeria.css">
    <a href="../redireccion.php">VOLVER</a>
    <style>
        .galeria {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .producto {
            border: 1px solid #ccc;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            width: 150px;
            background: #f9f9f9;
        }
        .producto button {
            margin-top: 10px;
            padding: 8px 16px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .producto button:hover {
            background: #0056b3;
        }
    </style>
</head>
<body class="body">
    <h1>Galería de Productos</h1>
    <div class="galeria">
        <?php foreach ($productos as $producto): ?>
            <div class="producto">
                <h3><?php echo htmlspecialchars($producto); ?></h3>
                <form action="control.php" method="get">
                    <input type="hidden" name="harina" value="<?php echo htmlspecialchars($producto); ?>">
                    <button type="submit">Seleccionar</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>