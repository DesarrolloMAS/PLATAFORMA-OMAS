<?php
require '../sesion.php';
require '../conection.php'; 
$sede = $_SESSION['sede'];
    $productos = [
    'Harina de Trigo Nariño x10kg',
    'Harina de Trigo Nariño x10kg (5 Und)',
    'Harina de Trigo Nariño 2500g',
    'Harina de Trigo Nariño 1000g',
    'Harina de Trigo Nariño 500g'
    ];
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
                <form action="control_familiar.php" method="get">
                    <input type="hidden" name="harina" value="<?php echo htmlspecialchars($producto); ?>">
                    <button type="submit">Seleccionar</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>