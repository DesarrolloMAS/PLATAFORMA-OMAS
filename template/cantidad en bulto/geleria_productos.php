<?php
require '../sesion.php';
require '../conection.php'; 
$sede = $_SESSION['sede'];
if ($sede === 'ZC') {
    $productos = [
        ['nombre' => 'Mogolla', 'peso' => 40],
        ['nombre' => 'Salvado', 'peso' => 25],
        ['nombre' => 'Fuerte x25', 'peso' => 25],
        ['nombre' => 'Natural x50', 'peso' => 50],
        ['nombre' => 'Centeno', 'peso' => 25],
        ['nombre' => 'Exclusiva x50', 'peso' => 50],
        ['nombre' => 'Artesanal x50', 'peso' => 50],
        ['nombre' => 'Artesanal x25', 'peso' => 25],
        ['nombre' => 'Extrapan x50', 'peso' => 50],
        ['nombre' => 'Extrapan x25', 'peso' => 25],
        ['nombre' => 'Extrapan x10', 'peso' => 10],
        ['nombre' => 'Segunda', 'peso' => 50],
        ['nombre' => 'Fuerte de Exportación', 'peso' => 25],
        ['nombre' => 'Especial x50', 'peso' => 50],
        ['nombre' => 'Especial x25', 'peso' => 25],
        ['nombre' => 'Harina T1 x50', 'peso' => 50],
        ['nombre' => 'Harina Integral', 'peso' => 25],
        ['nombre' => 'Grano entero fino', 'peso' => 25],
        ['nombre' => 'Trigo entero', 'peso' => 25],
        ['nombre' => 'Manitoba', 'peso' => 25],
        ['nombre' => 'Centeno Pepa', 'peso' => 25]

    ];
} else {
    $productos = [
        ['nombre' => 'Mogolla x50', 'peso' => 40],
        ['nombre' => 'Salvado x30', 'peso' => 30],
        ['nombre' => 'Granza x50', 'peso' => 50],
        ['nombre' => 'Segunda x50', 'peso' => 50],
        ['nombre' => 'Artesanal x50', 'peso' => 50],
        ['nombre' => 'Artesanal x25', 'peso' => 25],
        ['nombre' => 'Artesanal x10', 'peso' => 10],
        ['nombre' => 'Artesanal x10 (5 und)', 'peso' => 10],
        ['nombre' => 'Germen de Trigo x25', 'peso' => 25],
        ['nombre' => 'Harina Integral x25', 'peso' => 25],
        ['nombre' => 'Harina de Trigo Nariño x10', 'peso' => 10],
        ['nombre' => 'Harina de Trigo Nariño x10 5 und', 'peso' => 10],
        ['nombre' => 'Extrapan x50', 'peso' => 50],
        ['nombre' => 'Extrapan x25', 'peso' => 25],
        ['nombre' => 'Extrapan x10', 'peso' => 10],
        ['nombre' => 'Semola Fina x25', 'peso' => 25],
        ['nombre' => 'Harina de Trigo Nariño x2500GR', 'peso' => 2.5],
        ['nombre' => 'Harina de Trigo Nariño x1000GR', 'peso' => 1],
        ['nombre' => 'Harina de Trigo Nariño x500GR', 'peso' => 0.5],
        ['nombre' => 'Harina de Trigo Extrapan x10 (5 und)', 'peso' => 10]
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
        .peso {
            color: #666;
            font-size: 0.9em;
            margin-top: 5px;
        }
    </style>
</head>
<body class="body">
    <h1>Galería de Productos</h1>
    <div class="galeria">
        <?php foreach ($productos as $producto): ?>
            <div class="producto">
                <h3><?php echo htmlspecialchars($producto['nombre']); ?></h3>
                <p class="peso">Peso: <?php echo $producto['peso']; ?> kg</p>
                <form action="control.php" method="get">
                    <input type="hidden" name="harina" value="<?php echo htmlspecialchars($producto['nombre']); ?>">
                    <input type="hidden" name="peso" value="<?php echo $producto['peso']; ?>">
                    <button type="submit">Seleccionar</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>