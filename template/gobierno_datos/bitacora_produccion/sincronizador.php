<?php
require __DIR__ . '/../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class ProduccionSincronizador {
    private $pdoPg;
    private $excelPath;
    private $configMolienda;

    public function __construct() {
        // Configuración PostgreSQL
        $host = '127.0.0.1';
        $db   = 'bitacora_mantenimiento';
        $user = 'bitacora_user';
        $pass = 'bitacora2026';
        $dsn  = "pgsql:host=$host;port=5432;dbname=$db;";

        try {
            $this->pdoPg = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        } catch (PDOException $e) {
            error_log("Error conexión Postgres: " . $e->getMessage());
        }

        $this->excelPath = __DIR__ . '/Producción Harinas 2026 (1).xlsx';
        
        // Cargar configuración de productos para saber los IDs
        $configPath = __DIR__ . '/../../../archivos/generados/molienda/config_ZC.json';
        if (file_exists($configPath)) {
            $this->configMolienda = json_decode(file_get_contents($configPath), true);
        }
    }

    /**
     * Sincroniza los datos de un día específico desde el JSON a Postgres y Excel
     */
    public function sincronizar($fecha, $sede = 'ZC') {
        $mes = date('Y-m', strtotime($fecha));
        $dia = (int)date('d', strtotime($fecha));
        $jsonPath = __DIR__ . "/../../../archivos/generados/molienda/$sede/$mes.json";

        if (!file_exists($jsonPath)) return ["status" => "error", "message" => "No existe JSON para el mes"];

        $datosMes = json_decode(file_get_contents($jsonPath), true);
        
        // 1. Calcular Totales Diarios (Suma de todos los turnos del día)
        $totalesDia = [];
        $ultimoTurno = null;
        
        foreach ($datosMes as $registro) {
            if ($registro['fecha'] === $fecha) {
                // Sumar Harinas
                foreach ($registro['harinas'] as $id => $info) {
                    if (!isset($totalesDia[$id])) $totalesDia[$id] = 0;
                    foreach ($info['lotes'] as $lote) {
                        $totalesDia[$id] += (float)$lote['valor'];
                    }
                }
                // Sumar Subproductos
                foreach ($registro['subproductos'] as $id => $info) {
                    if (!isset($totalesDia[$id])) $totalesDia[$id] = 0;
                    foreach ($info['lotes'] as $lote) {
                        $totalesDia[$id] += (float)$lote['valor'];
                    }
                }
                $ultimoTurno = $registro; // Guardamos el último para sacar el responsable
            }
        }

        if (empty($totalesDia)) return ["status" => "info", "message" => "No hay datos para esta fecha"];

        // 2. Guardar en PostgreSQL (Guardamos el registro individual del turno actual)
        // Nota: Para simplificar, guardamos los totales que llevaba el turno actual
        $this->guardarPostgres($fecha, $ultimoTurno, $totalesDia, $sede);

        // 3. Actualizar Excel (Aquí sí ponemos el total acumulado del día)
        return $this->actualizarExcel($dia, $totalesDia);
    }

    private function guardarPostgres($fecha, $registro, $totales, $sede) {
        if (!$this->pdoPg) return;

        $sql = "INSERT INTO bitacora_produccion_molienda (fecha, turno, sede, responsable, datos_produccion) 
                VALUES (:fecha, :turno, :sede, :responsable, :datos)";
        
        // Determinar turno (esto depende de cómo lo maneje tu procesar.php, por ahora enviamos 1)
        $turno = 1; 

        try {
            $stmt = $this->pdoPg->prepare($sql);
            $stmt->execute([
                ':fecha' => $fecha,
                ':turno' => $turno,
                ':sede'  => $sede,
                ':responsable' => $registro['responsable'] ?? 'Sincronizador',
                ':datos' => json_encode($totales)
            ]);
        } catch (Exception $e) {
            error_log("Error guardando en Postgres: " . $e->getMessage());
        }
    }

    private function actualizarExcel($dia, $totales) {
        try {
            $reader = IOFactory::createReader('Xlsx');
            $spreadsheet = $reader->load($this->excelPath);
            $sheet = $spreadsheet->getActiveSheet();

            // Mapeo de Columnas (Según nuestro análisis previo)
            $map = [
                'extrapan_x50' => 'C',
                'extrapan_x25' => 'D',
                'extrapan_x10' => 'E',
                'artesanal_x50' => 'F',
                'artesanal_x25' => 'G',
                'natural_x50'  => 'H',
                'fuerte_x25'   => 'I',
                'fuerte_exp'   => 'J',
                'exclusiva_x50' => 'K',
                'Exclusiva_x25' => 'L',
                'alta_proteina' => 'R',
                'baja_proteina' => 'S',
                'artesanal_kg'  => 'T',
                'salvado_x25'   => 'AA',
                'mogolla_x40'   => 'AB',
                'segunda_x50'   => 'AC'
            ];

            // Especiales (Sumas o búsquedas manuales)
            // Semolas (Sumar fina y gruesa si existen)
            $semola = ($totales['semola_fina_x25'] ?? 0) + ($totales['semola_gruesa_x25'] ?? 0);
            if ($semola > 0) $totales['semola_total'] = $semola;
            $map['semola_total'] = 'Y';

            // Buscar la fila correspondiente al día
            // Basado en el análisis: Fila 4 = Día 1, Fila 5 = Día 2...
            // Por lo tanto: Fila = Día + 3
            $targetRow = $dia + 3;

            // Verificación de seguridad: El valor en la columna A (calculado) debería ser el día o estar vacío
            // Si prefieres ser ultra-seguro, podemos validar Column B (Fecha), pero el offset +3 parece constante
            
            // Escribir datos
            foreach ($map as $id => $col) {
                if (isset($totales[$id])) {
                    $sheet->setCellValue($col . $targetRow, $totales[$id]);
                }
            }

            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save($this->excelPath);

            return ["status" => "ok", "message" => "Excel actualizado para el día $dia"];

        } catch (Exception $e) {
            return ["status" => "error", "message" => "Error Excel: " . $e->getMessage()];
        }
    }
}
