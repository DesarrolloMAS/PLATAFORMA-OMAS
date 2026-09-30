<?php
// Aviso semanal de formatos faltantes → bandeja de entrada.
// Se ejecuta por cron los DOMINGOS 06:00 y revisa la semana que acaba de
// cerrar (domingo a sábado, igual que el calendario), para que el lunes los
// destinatarios encuentren en su bandeja qué formatos quedaron sin registrar.
//
// Usa el mismo cálculo que el calendario (preconteo_lib.php): por día +
// producto programado espera >= 1 registro de línea de envasado y >= 1 de
// control de empaque. Envía UNA notificación por sede a cada destinatario.
//
// SOLO LÍNEA DE COMANDOS. Debe correr como el usuario del servidor web
// (www-data): la carpeta de bandejas es suya y otro usuario no puede escribirla.
//   0 6 * * 0  php /var/www/fmt/template/cronograma_produccion/aviso_semanal.php >> /var/log/nova_aviso_semanal.log 2>&1
// (crontab -u www-data -e)
//
// Opciones:
//   --simular            muestra qué enviaría, sin enviar ni registrar nada
//   --semana=YYYY-MM-DD  revisa la semana (domingo a sábado) que contiene esa fecha
//   --forzar             reenvía aunque esa semana ya se haya avisado
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Solo se ejecuta desde línea de comandos (cron).');
}

require __DIR__ . '/../conection.php'; // $pdoUsuarios
require_once __DIR__ . '/produccion_lib.php';
require_once __DIR__ . '/preconteo_lib.php';
require_once __DIR__ . '/../usuario/bandeja_lib.php';
require_once __DIR__ . '/../admin/avisos_operaciones_lib.php';

// Destinatarios: NO se definen aquí. Se configuran en admin/menu_admin.php →
// "Visibilidad del Menú de Operaciones" → "Destinatarios del aviso semanal de
// formatos" (por área operativa y/o por cargo). A cada sede le llegan solo los
// usuarios de esa sede con alguno de esos cargos.
const AVISO_MAX_LINEAS = 30; // tope de faltantes listados en el mensaje
const AVISO_ENLACE = '/template/cronograma_produccion/calendario_produccion.php';

$opciones = getopt('', ['simular', 'forzar', 'semana:']);
$simular = isset($opciones['simular']);
$forzar = isset($opciones['forzar']);

// ---------- Semana a revisar (domingo a sábado) ----------
if (isset($opciones['semana'])) {
    $ref = DateTime::createFromFormat('!Y-m-d', $opciones['semana']);
    if (!$ref) exit("Fecha inválida en --semana (usa YYYY-MM-DD)\n");
    $inicio = (clone $ref)->modify('-' . (int)$ref->format('w') . ' days'); // domingo de esa semana
} else {
    // La última semana completa: termina el sábado anterior a hoy.
    $hoy = new DateTime('today');
    $inicio = (clone $hoy)->modify('-' . ((int)$hoy->format('w') + 7) . ' days');
}
$fin = (clone $inicio)->modify('+6 days');
$desde = $inicio->format('Y-m-d');
$hasta = $fin->format('Y-m-d');
$diaSiguiente = (clone $fin)->modify('+1 day')->format('Y-m-d'); // "hoy" para el preconteo: la semana ya cerró

$MESES = ['', 'ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
$DIAS = ['dom', 'lun', 'mar', 'mié', 'jue', 'vie', 'sáb'];
$SEDES = ['ZC' => 'Zona Centro', 'ZS' => 'Zona Sur', 'ZB' => 'Buga'];
$fmt = fn(string $f) => (int)substr($f, 8, 2) . ' ' . $MESES[(int)substr($f, 5, 2)];
$fmtDia = fn(string $f) => $DIAS[(int)date('w', strtotime($f))] . ' ' . $fmt($f);
$rango = $fmt($desde) . ' al ' . $fmt($hasta);

echo '[' . date('Y-m-d H:i:s') . "] Aviso semanal — semana $desde a $hasta" . ($simular ? ' (SIMULACIÓN)' : '') . "\n";

// ---------- Destinatarios por sede ----------
$cargosDestino = avisosCargosDestino(AVISO_SEMANAL_FORMATOS);
if (!$cargosDestino) {
    echo "  ⚠ No hay destinatarios configurados (admin/menu_admin.php → Visibilidad del Menú de Operaciones → Destinatarios del aviso semanal). No se envía nada.\n";
    exit(0);
}
echo '  Destinatarios (cargos): ' . implode(', ', $cargosDestino) . "\n";

function destinatariosSede(PDO $pdo, array $cargos, string $sede): array {
    if (!$cargos) return [];
    $marcas = implode(',', array_fill(0, count($cargos), '?'));
    $stmt = $pdo->prepare("SELECT id_usuario, nombre_u FROM usuarios WHERE sede = ? AND Cargo IN ($marcas)");
    $stmt->execute(array_merge([$sede], $cargos));
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ---------- Registro de avisos enviados (no repetir la misma semana) ----------
function avisosRuta(string $sede): string {
    return __DIR__ . "/../../archivos/generados/cronograma_produccion/$sede/avisos_semanales.json";
}

$totalEnviadas = 0;
foreach (PRODUCCION_SEDES as $sede) {
    // Programaciones de la semana (puede cruzar dos meses).
    $programaciones = [];
    foreach (array_unique([substr($desde, 0, 7), substr($hasta, 0, 7)]) as $mes) {
        $delMes = array_filter(produccionLeerMes($sede, $mes), fn($p) => $p['fecha'] >= $desde && $p['fecha'] <= $hasta);
        $programaciones = array_merge($programaciones, preconteoAplicar(array_values($delMes), $sede, $mes, $diaSiguiente));
    }
    if (!$programaciones) {
        echo "  $sede: sin producción programada, no se avisa.\n";
        continue;
    }

    // Por día + producto (los turnos del mismo producto comparten estado).
    $items = [];
    foreach ($programaciones as $p) {
        $clave = $p['fecha'] . '|' . (($p['producto_id'] ?? '') ?: $p['producto']);
        $items[$clave] ??= $p;
    }
    ksort($items);
    $cuenta = ['completo' => 0, 'faltante' => 0, 'sin_enlace' => 0, 'no_aplica' => 0];
    $lineas = [];
    foreach ($items as $p) {
        $estado = $p['preconteo']['estado'];
        $cuenta[$estado] = ($cuenta[$estado] ?? 0) + 1;
        if ($estado === 'faltante') {
            $falta = [];
            if ($p['preconteo']['envasado'] === 0) $falta[] = 'línea de envasado';
            if ($p['preconteo']['empaque'] === 0) $falta[] = 'control de empaque';
            $lineas[] = '• ' . $fmtDia($p['fecha']) . ' — ' . $p['producto'] . ': falta ' . implode(' y ', $falta);
        } elseif ($estado === 'sin_enlace') {
            $lineas[] = '• ' . $fmtDia($p['fecha']) . ' — ' . $p['producto'] . ': programado como "Otro", no se pudo contar';
        }
    }
    $revisados = $cuenta['completo'] + $cuenta['faltante'] + $cuenta['sin_enlace'];
    if ($revisados === 0) {
        echo "  $sede: todo lo programado es anterior al inicio del preconteo (" . PRECONTEO_DESDE . "), no se avisa.\n";
        continue;
    }

    if ($cuenta['faltante'] === 0 && $cuenta['sin_enlace'] === 0) {
        $tipo = 'exito';
        $titulo = "Formatos completos · semana $rango · {$SEDES[$sede]}";
        $mensaje = "Los formatos de línea de envasado y control de empaque quedaron registrados para toda la producción programada de la semana ({$cuenta['completo']} de $revisados producto-día).";
    } else {
        $tipo = 'alerta';
        $titulo = "Formatos faltantes · semana $rango · {$SEDES[$sede]}";
        $extra = count($lineas) > AVISO_MAX_LINEAS ? "\n… y " . (count($lineas) - AVISO_MAX_LINEAS) . ' más (ver el cronograma).' : '';
        $mensaje = "De $revisados producto-día programados, {$cuenta['completo']} quedaron completos y "
            . ($cuenta['faltante'] + $cuenta['sin_enlace']) . " no:\n\n"
            . implode("\n", array_slice($lineas, 0, AVISO_MAX_LINEAS)) . $extra
            . "\n\nSi el formato sí se diligenció, revisa que tenga la fecha correcta y el producto elegido de la lista (en envasado, que la tarjeta de la galería esté enlazada a su producto).";
    }

    // ¿Ya se avisó esta semana?
    $registro = json_decode(@file_get_contents(avisosRuta($sede)) ?: '[]', true) ?: [];
    if (isset($registro[$desde]) && !$forzar) {
        echo "  $sede: la semana $desde ya se avisó el {$registro[$desde]['enviado_en']} (usa --forzar para reenviar).\n";
        continue;
    }

    $destinatarios = destinatariosSede($pdoUsuarios, $cargosDestino, $sede);
    echo "  $sede: {$cuenta['completo']} completos, {$cuenta['faltante']} faltantes, {$cuenta['sin_enlace']} sin código → " . count($destinatarios) . " destinatario(s)\n";
    if (!$destinatarios) {
        echo "  $sede: ⚠ ningún usuario de esa sede tiene alguno de los cargos destinatarios.\n";
        continue;
    }
    if ($simular) {
        echo "    [$tipo] $titulo\n    " . str_replace("\n", "\n    ", $mensaje) . "\n";
        echo "    Para: " . implode(', ', array_column($destinatarios, 'nombre_u')) . "\n";
        continue;
    }

    $enviados = [];
    foreach ($destinatarios as $u) {
        try {
            crearNotificacion($u['id_usuario'], $titulo, $mensaje, $tipo, 'Cronograma de Producción', AVISO_ENLACE);
            $enviados[] = $u['id_usuario'];
        } catch (Throwable $e) {
            echo "  $sede: ✕ no se pudo notificar a {$u['id_usuario']}: {$e->getMessage()}\n";
        }
    }
    $totalEnviadas += count($enviados);

    if ($enviados) {
        $registro[$desde] = [
            'hasta' => $hasta, 'enviado_en' => date('Y-m-d H:i:s'), 'tipo' => $tipo,
            'destinatarios' => $enviados, 'completos' => $cuenta['completo'],
            'faltantes' => $cuenta['faltante'], 'sin_codigo' => $cuenta['sin_enlace'],
        ];
        ksort($registro);
        $ruta = avisosRuta($sede);
        if (!is_dir(dirname($ruta))) mkdir(dirname($ruta), 0777, true);
        if (@file_put_contents($ruta, json_encode($registro, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) === false) {
            echo "  $sede: ⚠ se envió pero no se pudo registrar (podría repetirse): revisar permisos de " . $ruta . "\n";
        }
    }
}
echo "Listo: $totalEnviadas notificación(es) enviada(s).\n";
