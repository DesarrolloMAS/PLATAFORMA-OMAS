<?php
// Captura el "escalón" permanente de una orden ya aprobada por el líder de
// mantenimiento (numero_orden asignado) hacia historico_ordenes_mantenimiento
// (base `maquinas`) — sobrevive aunque después se borre el JSON operativo
// desde la galería, a propósito: eliminar_orden.php nunca toca esta tabla.
//
// La lógica de tipo_ejecucion/técnicos/duración replica la de
// analitica/api_analitica_mantenimiento.php para no tener dos criterios
// distintos calculando lo mismo.

const HIST_SLA_DURACION_MIN_HORAS = 0;
const HIST_SLA_DURACION_MAX_HORAS = 24 * 14; // dos semanas — fuera de esto se descarta como error de captura

function historicoNormalizarClave($texto) {
    $texto = trim(mb_strtolower($texto ?? '', 'UTF-8'));
    $sinAcentos = strtr($texto, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n']);
    return preg_replace('/\s+/', ' ', $sinAcentos);
}

function historicoTipoEjecucion($tipoCrudo) {
    $clave = historicoNormalizarClave($tipoCrudo);
    if ($clave === '') return null;
    if (strpos($clave, 'preventivo') !== false) return 'preventivo';
    if (strpos($clave, 'predictivo') !== false) return 'predictivo';
    if (strpos($clave, 'correctivo') !== false) return 'correctivo';
    if (strpos($clave, 'garantia') !== false) return 'correctivo';
    return null;
}

function historicoExtraerTecnicos($nombreCrudo) {
    $nombreCrudo = trim($nombreCrudo ?? '');
    if ($nombreCrudo === '') return [];
    $partes = preg_split('/\s*\/\s*|\s+-\s+/', $nombreCrudo);
    $resultado = [];
    foreach ($partes as $parte) {
        $parte = trim(preg_replace('/\s+/', ' ', $parte));
        if ($parte !== '') $resultado[] = $parte;
    }
    return $resultado;
}

function historicoFechaHora($fecha, $hora) {
    if (empty($fecha) || empty($hora)) return null;
    $dt = DateTime::createFromFormat('Y-m-d H:i', "$fecha $hora");
    return $dt ? $dt->format('Y-m-d H:i:00') : null;
}

function historicoDuracionHoras($fechaSolicitud, $horaSolicitud, $fechaCierre, $horaCierre) {
    if (empty($fechaSolicitud) || empty($horaSolicitud) || empty($fechaCierre) || empty($horaCierre)) return null;
    $inicio = DateTime::createFromFormat('Y-m-d H:i', "$fechaSolicitud $horaSolicitud");
    $fin = DateTime::createFromFormat('Y-m-d H:i', "$fechaCierre $horaCierre");
    if (!$inicio || !$fin) return null;
    $horas = ($fin->getTimestamp() - $inicio->getTimestamp()) / 3600;
    if ($horas < HIST_SLA_DURACION_MIN_HORAS || $horas > HIST_SLA_DURACION_MAX_HORAS) return null;
    return round($horas, 2);
}

/**
 * @param array $registro   El registro completo tal como vive en el JSON
 *                          (con 'datos' ya incluyendo numero_orden actualizado).
 * @param string $archivoOrigen  Periodo YYYY-MM del archivo donde vive.
 * @param string $sede
 * @param PDO $pdomaquinas
 */
function registrarHistoricoOrden(array $registro, string $archivoOrigen, string $sede, PDO $pdomaquinas): void {
    $datos = $registro['datos'] ?? [];
    $numeroOrden = trim($datos['numero_orden'] ?? '');
    if ($numeroOrden === '' || empty($registro['id'])) return;

    $tipoEjecucion = historicoTipoEjecucion($datos['tipo_ejecucion'] ?? '');
    $tecnicos = historicoExtraerTecnicos($datos['nombre_responsable'] ?? '');
    $fechaSolicitud = historicoFechaHora($datos['fecha_solicitud'] ?? null, $datos['hora_solicitud'] ?? null);
    $fechaCierre = historicoFechaHora($datos['fecha_cierre'] ?? null, $datos['hora_cierre'] ?? null);
    $duracionHoras = historicoDuracionHoras(
        $datos['fecha_solicitud'] ?? null, $datos['hora_solicitud'] ?? null,
        $datos['fecha_cierre'] ?? null, $datos['hora_cierre'] ?? null
    );

    $stmt = $pdomaquinas->prepare("
        INSERT INTO historico_ordenes_mantenimiento
            (id_orden_origen, archivo_origen, sede, codigo_equipo, objeto_danado, numero_orden,
             tipo_ejecucion, clasificacion, tecnicos_json, descripcion_falla,
             fecha_solicitud, fecha_cierre, duracion_horas, usuario_creador, usuario_aprobo, area_aprobo)
        VALUES
            (:id_orden_origen, :archivo_origen, :sede, :codigo_equipo, :objeto_danado, :numero_orden,
             :tipo_ejecucion, :clasificacion, :tecnicos_json, :descripcion_falla,
             :fecha_solicitud, :fecha_cierre, :duracion_horas, :usuario_creador, :usuario_aprobo, :area_aprobo)
        ON DUPLICATE KEY UPDATE
            numero_orden = VALUES(numero_orden),
            tipo_ejecucion = VALUES(tipo_ejecucion),
            clasificacion = VALUES(clasificacion),
            tecnicos_json = VALUES(tecnicos_json),
            descripcion_falla = VALUES(descripcion_falla),
            fecha_solicitud = VALUES(fecha_solicitud),
            fecha_cierre = VALUES(fecha_cierre),
            duracion_horas = VALUES(duracion_horas),
            usuario_aprobo = VALUES(usuario_aprobo),
            area_aprobo = VALUES(area_aprobo)
    ");

    $stmt->execute([
        ':id_orden_origen'   => $registro['id'],
        ':archivo_origen'    => $archivoOrigen,
        ':sede'              => $sede,
        ':codigo_equipo'     => $datos['codigo_equipo'] ?? null,
        ':objeto_danado'     => $datos['objeto_dañado'] ?? null,
        ':numero_orden'      => $numeroOrden,
        ':tipo_ejecucion'    => $tipoEjecucion,
        ':clasificacion'     => $datos['clasificacion'] ?? null,
        ':tecnicos_json'     => json_encode($tecnicos, JSON_UNESCAPED_UNICODE),
        ':descripcion_falla' => $datos['descripcion_falla'] ?? null,
        ':fecha_solicitud'   => $fechaSolicitud,
        ':fecha_cierre'      => $fechaCierre,
        ':duracion_horas'    => $duracionHoras,
        ':usuario_creador'   => $registro['usuario_creador'] ?? null,
        ':usuario_aprobo'    => $_SESSION['nombre'] ?? null,
        ':area_aprobo'       => $_SESSION['area'] ?? null,
    ]);
}
