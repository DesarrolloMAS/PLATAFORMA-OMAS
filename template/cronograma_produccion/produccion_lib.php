<?php
// Cronograma de Producción — persistencia.
// JSON por sede y mes (convención del README para módulos con sede):
//   archivos/generados/cronograma_produccion/[sede]/[YYYY-MM].json
// Cada archivo es un arreglo de programaciones de UN día:
//   { id, fecha, producto_id, producto, categoria, turno, observaciones,
//     creado_por, creado_en, editado_por, editado_en }
// Una programación de varios días se guarda como una entrada por día (así
// cada archivo mensual es autocontenido y cada día se edita por separado).
// Quién ve/edita: regla fija en area_operativa_lib.php.

const PRODUCCION_SEDES = ['ZC', 'ZS', 'ZB'];
const PRODUCCION_MAX_DIAS = 62; // tope de un rango en una sola programación
// La cantidad se retiró a propósito (no se puede saber de antemano cuánto se
// va a producir). Entradas viejas pueden traer estos campos: se ignoran al
// mostrar y se eliminan al editar.
const PRODUCCION_CAMPOS_RETIRADOS = ['cantidad', 'peso_kg'];

function turnosSede(string $sede): int {
    return $sede === 'ZS' ? 2 : 3; // README: ZC/ZB 3 turnos, ZS 2
}

function produccionRuta(string $sede, string $mes): string {
    if (!in_array($sede, PRODUCCION_SEDES, true) || !preg_match('/^\d{4}-\d{2}$/', $mes)) {
        throw new InvalidArgumentException('Sede o mes inválido.');
    }
    return __DIR__ . "/../../archivos/generados/cronograma_produccion/$sede/$mes.json";
}

function produccionLeerMes(string $sede, string $mes): array {
    $lista = json_decode(@file_get_contents(produccionRuta($sede, $mes)) ?: '[]', true) ?: [];
    usort($lista, fn($a, $b) => [$a['fecha'], $a['turno'], $a['producto']] <=> [$b['fecha'], $b['turno'], $b['producto']]);
    return $lista;
}

// Abre el mes con bloqueo exclusivo, deja que $fn lo modifique y lo guarda.
function produccionModificarMes(string $sede, string $mes, callable $fn) {
    $ruta = produccionRuta($sede, $mes);
    if (!is_dir(dirname($ruta))) mkdir(dirname($ruta), 0777, true);
    $fp = @fopen($ruta, 'c+');
    if (!$fp) throw new RuntimeException('No se pudo abrir el archivo del cronograma (revisar permisos).');
    try {
        flock($fp, LOCK_EX);
        $lista = json_decode(stream_get_contents($fp) ?: '[]', true) ?: [];
        $resultado = $fn($lista);
        ftruncate($fp, 0);
        rewind($fp);
        if (fwrite($fp, json_encode(array_values($lista), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) === false) {
            throw new RuntimeException('No se pudo guardar el cronograma.');
        }
        fflush($fp);
        flock($fp, LOCK_UN);
        return $resultado;
    } finally {
        fclose($fp);
    }
}

// Valida y normaliza los campos editables de una programación.
function produccionNormalizar(array $in, string $sede): array {
    $producto = trim((string)($in['producto'] ?? ''));
    if ($producto === '' || mb_strlen($producto) > 120) {
        throw new InvalidArgumentException('El producto es obligatorio.');
    }
    $turno = (string)($in['turno'] ?? 'todos');
    $turnosValidos = array_merge(['todos'], array_map('strval', range(1, turnosSede($sede))));
    if (!in_array($turno, $turnosValidos, true)) {
        throw new InvalidArgumentException('Turno inválido para la sede.');
    }
    $categoria = in_array($in['categoria'] ?? '', ['harinas', 'subproductos', 'otro'], true) ? $in['categoria'] : 'otro';

    return [
        'producto_id'   => preg_replace('/[^A-Za-z0-9_-]/', '', (string)($in['producto_id'] ?? '')),
        'producto'      => $producto,
        'categoria'     => $categoria,
        'turno'         => $turno,
        'observaciones' => mb_substr(trim((string)($in['observaciones'] ?? '')), 0, 500),
    ];
}

function produccionValidarFecha(string $fecha): string {
    $d = DateTime::createFromFormat('!Y-m-d', $fecha);
    if (!$d || $d->format('Y-m-d') !== $fecha) throw new InvalidArgumentException('Fecha inválida.');
    return $fecha;
}
