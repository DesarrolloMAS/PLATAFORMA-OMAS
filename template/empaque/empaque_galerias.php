<?php
require '../sesion.php';
require '../conection.php'; 
$sede = $_SESSION['sede'];
if ($sede === 'ZC') {
    $productos = [
        'Mogolla',
        'Salvado',
        'Empaque Extrapan x50',
        'Empaque x25',
        'Empaque x10',
        'Segunda',
        'Empaque Galeras Rojo X50',
        'Empaque Galeras Verde X50',
        'Empaque Galeras Cafe X50',
        'Empaque Galeras Azul X50',
        'Empaque Galeras Naranja X25',
        'Empaque Galeras Kraft X25',
        'Empaque Galeras Multi Beige X25',
        'Empaque Fuerte de Exportacion'
    ];
} else {
    $productos = [
        'Mogolla',
        'Salvado',
        'Centeno',
        'Empaque Extrapan x50',
        'Empaque x25',
        'Empaque x10',
        'Segunda',
        'Empaque Galeras Rojo X50',
        'Empaque Galeras Verde X50',
        'Empaque Galeras Cafe X50',
        'Empaque Galeras Azul X50',
        'Empaque Galeras Naranja X25',
        'Empaque Galeras Kraft X25',
        'Empaque Galeras Multi Beige X50'
    ];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Galería de Empaques</title>
    <link rel="stylesheet" href="/css/empaque_galerias.css">
    
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
        .input-lote {
            margin-top: 10px;
            display: none;
        }
        .continuar-btn {
            display: none;
            margin-top: 10px;
            padding: 8px 16px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .continuar-btn:hover {
            background: #1e7e34;
        }
    </style>
</head>
<body class="body">
    <h1>Galería de Empaques</h1>
    <a href="../redireccion.php" class="volver">VOLVER</a>
    <div class="galeria">
        <?php foreach ($productos as $producto): ?>
            <div class="producto">
                <h3><?php echo htmlspecialchars($producto); ?></h3>
                <form action="selector_lote.php" method="get" onsubmit="return validarLote(this);">
                    <input type="hidden" name="empaque" value="<?php echo htmlspecialchars($producto); ?>">
                    <input type="text" name="lote" placeholder="Ingrese lote" class="input-lote" required>
                    <button type="button" onclick="mostrarInputLote(this)">Seleccionar</button>
                    <button type="submit" class="continuar-btn">Continuar</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
    <script>
    function mostrarInputLote(btn) {
        const form = btn.closest('form');
        form.querySelector('.input-lote').style.display = 'inline';
        btn.style.display = 'none';
        form.querySelector('.continuar-btn').style.display = 'inline';
        form.querySelector('.input-lote').focus();
    }
    function validarLote(form) {
        const lote = form.querySelector('.input-lote').value.trim();
        if (!lote) {
            alert('Ingrese el lote');
            return false;
        }
        return true;
    }
    </script>
</body>
</html>