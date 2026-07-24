<?php
require '../../conection.php';
require '../../sesion.php';
verificarAutenticacion();

$sede = $_SESSION['sede'];
$nombre_usuario = $_SESSION['nombre'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control de Producto No Conforme (PNC)</title>
    <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;600&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0a0b10;
            --surface: #141620;
            --surface2: #1c1f2e;
            --border: #2d324a;
            --accent: #00f2ff; /* Cyan Cyberpunk */
            --accent2: #7000ff; /* Purple */
            --text: #e0e6ed;
            --text-muted: #7a8599;
            --danger: #ff0055;
            --success: #00ff88;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'Barlow', sans-serif;
            line-height: 1.6;
            overflow-x: hidden;
        }

        body::before {
            content: " ";
            position: fixed;
            top: 0; left: 0; bottom: 0; right: 0;
            background: linear-gradient(rgba(18, 16, 16, 0) 50%, rgba(0, 0, 0, 0.1) 50%), linear-gradient(90deg, rgba(255, 0, 0, 0.03), rgba(0, 255, 0, 0.01), rgba(0, 0, 255, 0.03));
            z-index: 1000;
            background-size: 100% 4px, 3px 100%;
            pointer-events: none;
        }

        .header {
            background: var(--surface);
            border-bottom: 2px solid var(--accent);
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 0 20px rgba(0, 242, 255, 0.1);
        }

        .header-title h1 {
            font-family: 'Space Mono', monospace;
            font-size: 18px;
            letter-spacing: 2px;
            color: var(--accent);
            text-transform: uppercase;
        }

        .header-meta {
            text-align: right;
            font-family: 'Space Mono', monospace;
            font-size: 11px;
            color: var(--text-muted);
        }

        .container {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .section-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 4px;
            margin-bottom: 30px;
            padding: 25px;
            position: relative;
        }

        .section-card::before {
            content: "";
            position: absolute;
            top: -1px; left: -1px;
            width: 10px; height: 10px;
            border-top: 2px solid var(--accent);
            border-left: 2px solid var(--accent);
        }

        .section-title {
            font-family: 'Space Mono', monospace;
            font-size: 12px;
            font-weight: 700;
            color: var(--accent);
            text-transform: uppercase;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title::after {
            content: "";
            height: 1px;
            flex: 1;
            background: var(--border);
        }

        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .grid-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            font-family: 'Space Mono', monospace;
            font-size: 11px;
            color: var(--text-muted);
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .form-control {
            width: 100%;
            background: var(--surface2);
            border: 1px solid var(--border);
            color: var(--text);
            padding: 10px 15px;
            font-size: 14px;
            border-radius: 2px;
            outline: none;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        .form-control:focus {
            border-color: var(--accent);
            box-shadow: 0 0 10px rgba(0, 242, 255, 0.1);
        }

        .form-control[readonly] {
            opacity: 0.7;
            cursor: not-allowed;
        }

        textarea.form-control {
            resize: vertical;
            min-height: 80px;
        }

        .actions {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 40px;
        }

        .btn {
            font-family: 'Space Mono', monospace;
            padding: 12px 30px;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 1px;
            cursor: pointer;
            border: none;
            border-radius: 2px;
            transition: all 0.3s;
        }

        .btn-submit {
            background: var(--accent);
            color: var(--bg);
            box-shadow: 0 0 15px rgba(0, 242, 255, 0.3);
        }

        .btn-submit:hover {
            box-shadow: 0 0 25px rgba(0, 242, 255, 0.5);
            transform: translateY(-2px);
        }

    </style>
</head>
    </style>
</head>
<body>

<div class="header">
    <div class="header-title">
        <h1>Control de Producto No Conforme (PNC)</h1>
    </div>
    <div class="header-meta">
        <div>ZONA: <?= htmlspecialchars($sede) ?></div>
        <div>FORMATO PNC</div>
    </div>
</div>

<div class="container">
    <form id="formPNC" action="procesar.php" method="POST">
        
        <div class="section-card">
            <h2 class="section-title">Información del Reporte</h2>
            
            <div class="grid-2">
                <div class="form-group">
                    <label>Quien Reporta</label>
                    <input type="text" class="form-control" name="quien_reporta" value="<?= htmlspecialchars($nombre_usuario) ?>" required>
                </div>
                <div class="form-group">
                    <label>Fecha de Reporte</label>
                    <input type="date" class="form-control" name="fecha_reporte" value="<?= date('Y-m-d') ?>" required>
                </div>
            </div>

            <div class="grid-3">
                <div class="form-group">
                    <label>Producto</label>
                    <input type="text" class="form-control" name="producto" placeholder="Nombre completo del producto" required>
                </div>
                <div class="form-group">
                    <label>Número de Lote</label>
                    <input type="text" class="form-control" name="numero_lote" placeholder="Identificador de lote" required>
                </div>
                <div class="form-group">
                    <label>Cantidad No Conforme</label>
                    <input type="number" step="0.01" class="form-control" name="cantidad_nc" placeholder="Unidades o Kg" required>
                </div>
            </div>

            <div class="form-group">
                <label>Descripción del Evento/Motivo</label>
                <textarea class="form-control" name="descripcion_evento" placeholder="Detalle claramente el hallazgo o motivo de no conformidad..." required></textarea>
            </div>
            
            <div class="form-group">
                <label>Verifica Identificación</label>
                <input type="text" class="form-control" name="verifica_identificacion" placeholder="Nombre de quien verifica la identificación del hallazgo" required>
            </div>
        </div>

        <div class="section-card" style="border-color: var(--accent2);">
            <h2 class="section-title" style="color: var(--accent2);">Tratamiento y Disposición</h2>
            
            <div class="form-group">
                <label>Corrección / Destino / Almacenamiento</label>
                <textarea class="form-control" name="correccion_destino" placeholder="Indique qué se hizo con el producto, dónde se almacenó o destino final..."></textarea>
            </div>

            <div class="grid-3">
                <div class="form-group">
                    <label>N° NC / Documento</label>
                    <input type="text" class="form-control" name="num_documento" placeholder="ID de Documento">
                </div>
                <div class="form-group">
                    <label>Responsable de la Corrección</label>
                    <input type="text" class="form-control" name="responsable_correccion" placeholder="Nombre">
                </div>
                <div class="form-group">
                    <label>Fecha de la Corrección</label>
                    <input type="date" class="form-control" name="fecha_correccion">
                </div>
            </div>
        </div>

        <div class="actions">
            <button type="button" class="btn" style="background: var(--surface2); color: var(--text-muted);" onclick="history.back()">Cancelar</button>
            <button type="submit" class="btn btn-submit">Guardar Registro PNC</button>
        </div>

    </form>
</div>

</body>
</html>
