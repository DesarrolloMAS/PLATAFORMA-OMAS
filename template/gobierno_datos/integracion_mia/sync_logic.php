<?php
/**
 * Motor de sincronización con mIA — compartido entre consultar_mia.php (cron)
 * y panel.php (botón manual desde el dashboard de Bitácora de Mantenimiento).
 * Una sola función, un solo lugar con la lógica real — ver integracion-mia.md.
 */

function ejecutar_sync_tickets(PDO $pdoMia): array {
    // --- 1. Cursor propio: el máximo que ya tenemos guardado ---
    $since = null;
    $row = $pdoMia->query("SELECT MAX(fecha_actualizacion) AS ultima FROM tickets_mia")->fetch(PDO::FETCH_ASSOC);
    if ($row && $row['ultima']) {
        $since = str_replace(' ', 'T', $row['ultima']);
    }

    // --- 2. Llamar a mIA ---
    $url = MIA_BASE_URL . '/api/nova/pull/tickets?limit=100';
    if ($since) {
        $url .= '&since=' . urlencode($since);
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['X-Nova-Api-Key: ' . MIA_API_KEY],
        CURLOPT_TIMEOUT => 20,
    ]);
    $respuesta = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $errorCurl = curl_error($ch);
    curl_close($ch);

    if ($errorCurl) {
        _log_sync($pdoMia, $since, 0, 'error', "Error de red: $errorCurl");
        return ['ok' => false, 'recibidos' => 0, 'mensaje' => "Error de red consultando mIA: $errorCurl"];
    }

    if ($httpCode !== 200) {
        _log_sync($pdoMia, $since, 0, 'error', "mIA respondió HTTP $httpCode: $respuesta");
        return ['ok' => false, 'recibidos' => 0, 'mensaje' => "mIA respondió HTTP $httpCode: $respuesta"];
    }

    $data = json_decode($respuesta, true);
    if (!$data || !isset($data['registros'])) {
        _log_sync($pdoMia, $since, 0, 'error', "Respuesta de mIA no reconocida");
        return ['ok' => false, 'recibidos' => 0, 'mensaje' => "Respuesta de mIA no reconocida: $respuesta"];
    }

    // --- 3. Guardar los tickets recibidos ---
    $sql = "INSERT INTO tickets_mia (
                id_ticket_mia, asunto, estado, prioridad, categoria, subcategoria,
                mesa_servicio, departamento, sede, solicitante_email,
                especialista_email, tipo_ticket, fecha_creacion, fecha_actualizacion, fecha_cierre
            ) VALUES (
                :id, :asunto, :estado, :prioridad, :categoria, :subcategoria,
                :mesa_servicio, :departamento, :sede, :solicitante_email,
                :especialista_email, :tipo_ticket, :fecha_creacion, :fecha_actualizacion, :fecha_cierre
            )
            ON DUPLICATE KEY UPDATE
                asunto = VALUES(asunto), estado = VALUES(estado), prioridad = VALUES(prioridad),
                categoria = VALUES(categoria), subcategoria = VALUES(subcategoria),
                mesa_servicio = VALUES(mesa_servicio), departamento = VALUES(departamento),
                sede = VALUES(sede), solicitante_email = VALUES(solicitante_email),
                especialista_email = VALUES(especialista_email), tipo_ticket = VALUES(tipo_ticket),
                fecha_creacion = VALUES(fecha_creacion), fecha_actualizacion = VALUES(fecha_actualizacion),
                fecha_cierre = VALUES(fecha_cierre)";

    try {
        $stmt = $pdoMia->prepare($sql);
        $procesados = 0;
        foreach ($data['registros'] as $r) {
            if (!isset($r['id'])) continue;
            $stmt->execute([
                ':id' => $r['id'],
                ':asunto' => $r['asunto'] ?? null,
                ':estado' => $r['estado'] ?? null,
                ':prioridad' => $r['prioridad'] ?? null,
                ':categoria' => $r['categoria'] ?? null,
                ':subcategoria' => $r['subcategoria'] ?? null,
                ':mesa_servicio' => $r['mesa_servicio'] ?? null,
                ':departamento' => $r['departamento'] ?? null,
                ':sede' => $r['sede'] ?? null,
                ':solicitante_email' => $r['solicitante_email'] ?? null,
                ':especialista_email' => $r['especialista_email'] ?? null,
                ':tipo_ticket' => $r['tipo_ticket'] ?? null,
                ':fecha_creacion' => $r['fecha_creacion'] ?? null,
                ':fecha_actualizacion' => $r['fecha_actualizacion'] ?? null,
                ':fecha_cierre' => $r['fecha_cierre'] ?? null,
            ]);
            $procesados++;
        }
        _log_sync($pdoMia, $since, $procesados, 'exito');
        return ['ok' => true, 'recibidos' => $procesados, 'mensaje' => "$procesados registro(s) recibidos y guardados"];
    } catch (PDOException $e) {
        _log_sync($pdoMia, $since, 0, 'error', "Error guardando: " . $e->getMessage());
        return ['ok' => false, 'recibidos' => 0, 'mensaje' => "Error guardando registros: " . $e->getMessage()];
    }
}

function _log_sync(PDO $pdoMia, ?string $since, int $recibidos, string $estado, ?string $error = null): void {
    $stmt = $pdoMia->prepare(
        "INSERT INTO mia_sync_log (since_enviado, registros_recibidos, estado, error) VALUES (?, ?, ?, ?)"
    );
    $stmt->execute([$since, $recibidos, $estado, $error]);
}
