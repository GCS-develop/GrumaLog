<?php
namespace frontend\modules\contabilidad\controllers;

use frontend\models\Centrooperacion;
use Yii;
use yii\web\Controller;
use frontend\models\ReporteCreditosEmpleadosForm;
use frontend\models\ReporteCreditosHistorialForm;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class ReporteCreditosEmpleadosController extends Controller
{
    /**
     * Calcula fechas de cuotas (periodos 15 y 30).
     */
    private function calcularFechasCuotas($fechaInicial, $cuotas)
    {
        $fechas = [];
        $fecha = new \DateTime($fechaInicial);

        // Normalizar la primera fecha
        $dia = (int)$fecha->format('d');
        if ($dia <= 15) {
            $fecha->setDate($fecha->format('Y'), $fecha->format('m'), 15);
        } else {
            $ultimoDia = (int)$fecha->format('t');
            $dia30 = ($ultimoDia >= 30) ? 30 : $ultimoDia;
            $fecha->setDate($fecha->format('Y'), $fecha->format('m'), $dia30);
        }

        for ($i = 1; $i <= $cuotas; $i++) {

            // Guardar YYYYMMDD
            $fechas[] = $fecha->format('Ymd');

            // Calcular siguiente fecha
            $diaActual = (int)$fecha->format('d');

            if ($diaActual == 15) {
                $ultimoMes = (int)$fecha->format('t');
                $dia30 = ($ultimoMes >= 30) ? 30 : $ultimoMes;
                $fecha->setDate($fecha->format('Y'), $fecha->format('m'), $dia30);

            } else {
                $fecha->modify('first day of next month');
                $fecha->setDate($fecha->format('Y'), $fecha->format('m'), 15);
            }
        }

        return $fechas;
    }

    /**
     * Vista principal del reporte
     */
    public function actionIndex()
    {
        $model = new ReporteCreditosEmpleadosForm();

        // Si no se ha enviado formulario, asigna mes actual por defecto
        if (!($model->load(Yii::$app->request->get()) && $model->validate())) {
            $model->fecha_inicio = $model->fecha_inicio ?: date('Y-m-01');
            $model->fecha_fin    = $model->fecha_fin ?: date('Y-m-t');
        }

        $dataProvider = $model->dataProvider();
        $total = $model->getTotalValor();

        return $this->render('index', [
            'model' => $model,
            'dataProvider' => $dataProvider,
            'total' => $total,
        ]);
    }

    /**
     * Diagnóstico: muestra qué hay en t353 para un NIT sin filtros restrictivos
     */
    public function actionDiagnostico()
    {
        $nit = Yii::$app->request->get('nit', '');
        $resultado = [];

        if ($nit) {
            $db = Yii::$app->dbSiesa;

            // 1. ¿Existe el NIT en t200?
            $resultado['tercero'] = $db->createCommand("
                SELECT f200_rowid, f200_nit, f200_razon_social, f200_id_cia
                FROM t200_mm_terceros
                WHERE LTRIM(RTRIM(f200_nit)) = LTRIM(RTRIM(:nit))
            ", [':nit' => $nit])->queryAll();

            // 2. ¿Existe en t201 (datos comerciales)?
            $resultado['cliente'] = $db->createCommand("
                SELECT C.f201_id_cia, C.f201_id_sucursal, C.f201_cupo_credito,
                       C.f201_id_cond_pago, C.f201_ind_estado_activo, C.f201_ind_estado_bloqueado
                FROM t201_mm_clientes C
                INNER JOIN t200_mm_terceros T ON C.f201_rowid_tercero = T.f200_rowid
                WHERE LTRIM(RTRIM(T.f200_nit)) = LTRIM(RTRIM(:nit))
            ", [':nit' => $nit])->queryAll();

            // 3. ¿Qué auxiliares tiene en t353 (SIN filtro de auxiliar)?
            $resultado['auxiliares'] = $db->createCommand("
                SELECT SA.f353_rowid_auxiliar, COUNT(*) AS total,
                       MIN(SA.f353_fecha) AS primera_fecha,
                       MAX(SA.f353_fecha) AS ultima_fecha,
                       SUM(SA.f353_total_db) AS suma_db
                FROM t353_co_saldo_abierto SA
                INNER JOIN t200_mm_terceros T ON SA.f353_rowid_tercero = T.f200_rowid
                WHERE LTRIM(RTRIM(T.f200_nit)) = LTRIM(RTRIM(:nit))
                GROUP BY SA.f353_rowid_auxiliar
                ORDER BY total DESC
            ", [':nit' => $nit])->queryAll();

            // 5. Auditoría: timestamps en t200 y t201
            $resultado['auditoria_t200'] = $db->createCommand("
                SELECT f200_rowid, f200_nit, f200_razon_social, f200_id_cia, f200_ts
                FROM t200_mm_terceros
                WHERE LTRIM(RTRIM(f200_nit)) = LTRIM(RTRIM(:nit))
            ", [':nit' => $nit])->queryAll();

            $resultado['auditoria_t201'] = $db->createCommand("
                SELECT C.f201_id_cia, C.f201_id_sucursal, C.f201_id_tipo_cli,
                       C.f201_fecha_ingreso, C.f201_fecha_cupo, C.f201_ts
                FROM t201_mm_clientes C
                INNER JOIN t200_mm_terceros T ON C.f201_rowid_tercero = T.f200_rowid
                WHERE LTRIM(RTRIM(T.f200_nit)) = LTRIM(RTRIM(:nit))
            ", [':nit' => $nit])->queryAll();

            // Log de cambios: primero detectar columnas reales de t2005
            $resultado['log_cambios'] = [];
            try {
                // Obtener columnas reales de la tabla
                $cols = $db->createCommand("
                    SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE TABLE_NAME = 't2005_mm_terceros_log_unif'
                    ORDER BY ORDINAL_POSITION
                ")->queryAll();
                $resultado['log_cols'] = array_column($cols, 'COLUMN_NAME');

                // Buscar columna que relacione con tercero
                $colTercero = null;
                foreach ($resultado['log_cols'] as $col) {
                    if (stripos($col, 'tercero') !== false || stripos($col, 'rowid') !== false) {
                        $colTercero = $col;
                        break;
                    }
                }

                $rowids = array_column($resultado['auditoria_t200'], 'f200_rowid');
                if ($colTercero && !empty($rowids)) {
                    $ids = implode(',', array_map('intval', $rowids));
                    // Buscar columna de timestamp
                    $colTs = in_array('f2005_ts', $resultado['log_cols']) ? 'f2005_ts' : $resultado['log_cols'][0];
                    $resultado['log_cambios'] = $db->createCommand("
                        SELECT TOP 20 *
                        FROM t2005_mm_terceros_log_unif
                        WHERE {$colTercero} IN ({$ids})
                        ORDER BY {$colTs} DESC
                    ")->queryAll();
                }
            } catch (\Exception $e) {
                $resultado['log_cambios'] = ['error' => $e->getMessage()];
            }

            // 3b. Nombre de los auxiliares en el catálogo SIESA
            $resultado['nombres_auxiliares'] = $db->createCommand("
                SELECT TABLE_NAME
                FROM INFORMATION_SCHEMA.TABLES
                WHERE TABLE_NAME LIKE '%auxiliar%'
                ORDER BY TABLE_NAME
            ")->queryAll();

            // Resolver nombres desde t253_co_auxiliares
            try {
                $rows = $db->createCommand("
                    SELECT f253_rowid, f253_id, f253_nombre, f253_descripcion
                    FROM t253_co_auxiliares
                    WHERE f253_rowid IN (20805, 1323)
                ")->queryAll();
                $resultado['nombre_auxiliar_detalle'] = ['tabla' => 't253_co_auxiliares', 'rows' => $rows];
            } catch (\Exception $e) {
                // Intentar con todos los campos si los anteriores fallan
                try {
                    $rows = $db->createCommand("
                        SELECT TOP 5 * FROM t253_co_auxiliares WHERE f253_rowid IN (20805, 1323)
                    ")->queryAll();
                    $resultado['nombre_auxiliar_detalle'] = ['tabla' => 't253_co_auxiliares', 'rows' => $rows];
                } catch (\Exception $e2) {
                    $resultado['nombre_auxiliar_detalle'] = null;
                }
            }

            // 4. Registros con auxiliar 20805 (sin filtro de fecha ni cia)
            $resultado['con_auxiliar_20805'] = $db->createCommand("
                SELECT SA.f353_id_tipo_docto_cruce, SA.F353_CONSEC_DOCTO_CRUCE,
                       SA.F353_NRO_CUOTA_CRUCE, SA.f353_fecha, SA.f353_total_db,
                       SA.f353_total_cr, SA.f353_ind_anticipo, SA.f353_id_cia
                FROM t353_co_saldo_abierto SA
                INNER JOIN t200_mm_terceros T ON SA.f353_rowid_tercero = T.f200_rowid
                WHERE LTRIM(RTRIM(T.f200_nit)) = LTRIM(RTRIM(:nit))
                  AND SA.f353_rowid_auxiliar = 20805
                ORDER BY SA.f353_fecha DESC
            ", [':nit' => $nit])->queryAll();
        }

        return $this->render('diagnostico', [
            'nit'       => $nit,
            'resultado' => $resultado,
        ]);
    }

    /**
     * Vista de TODAS las facturas crédito (pagadas + pendientes)
     */
    public function actionHistorialFacturas()
    {
        $model = new ReporteCreditosHistorialForm();

        if (!($model->load(Yii::$app->request->get()) && $model->validate())) {
            $model->fecha_inicio = $model->fecha_inicio ?: date('Y-m-01');
            $model->fecha_fin    = $model->fecha_fin ?: date('Y-m-t');
        }

        $dataProvider = $model->dataProviderAgrupado();
        $total        = $model->getTotalValor();
        $cupoInfo     = $model->getCupoInfo();

        return $this->render('historial-facturas', [
            'model'        => $model,
            'dataProvider' => $dataProvider,
            'total'        => $total,
            'cupoInfo'     => $cupoInfo,
        ]);
    }

    /**
     * Detalle de cuotas de una factura específica
     */
    public function actionDetalleFactura()
    {
        $nit  = Yii::$app->request->get('nit', '');
        $tipo = Yii::$app->request->get('tipo', '');
        $num  = (int) Yii::$app->request->get('num', 0);

        if (!$nit || !$tipo || !$num) {
            throw new \yii\web\BadRequestHttpException('Parámetros incompletos.');
        }

        $cuotas = \frontend\models\ReporteCreditosHistorialForm::getCuotasFactura($nit, $tipo, $num);

        // Parámetros para volver al listado
        $backParams = Yii::$app->request->get();
        unset($backParams['nit'], $backParams['tipo'], $backParams['num']);

        return $this->render('historial-facturas-detalle', [
            'cuotas'     => $cuotas,
            'nit'        => $nit,
            'tipo'       => $tipo,
            'num'        => $num,
            'backParams' => $backParams,
        ]);
    }

    /**
     * Exporta el reporte a Excel usando plantilla
     */
    public function actionExportarExcel()
    {
        $model = new ReporteCreditosEmpleadosForm();

        // Carga filtros
        if (!($model->load(Yii::$app->request->get()) && $model->validate())) {
            Yii::$app->session->setFlash('error', 'Debe seleccionar un rango de fechas válido.');
            return $this->redirect(['index']);
        }

        $dataProvider = $model->dataProvider();
        $rows = $dataProvider->getModels();

        if (empty($rows)) {
            Yii::$app->session->setFlash('warning', 'No se encontraron datos para exportar.');
            return $this->redirect(['index']);
        }

        // Ruta plantilla
        $plantilla = Yii::getAlias('@frontend/web/archivos/NomProImpGeneralReg(16).xlsx');
        if (!file_exists($plantilla)) {
            throw new \yii\web\NotFoundHttpException("No se encontró la plantilla: $plantilla");
        }

        // Cargar hoja "datos"
        $spreadsheet = IOFactory::load($plantilla);
        $sheet = $spreadsheet->getSheetByName('datos');
        if (!$sheet) {
            throw new \yii\web\NotFoundHttpException("La hoja 'datos' no existe en la plantilla.");
        }

        // Fila inicial
        $fila = 2;

        foreach ($rows as $row) {

            // ============================
            //  CALCULO DE LA FECHA COLUMNA AE 
            // ============================

            $totalCuotas = (int)$row['NUM_CUOTAS'];

// Fecha base = hoy
$fechaBase = date('Y-m-d');

// Generamos las fechas según el total de cuotas
$fechasCuotas = $this->calcularFechasCuotas($fechaBase, $totalCuotas);

// 🔥 Ajustar número de cuota cuando venga en 0
$numeroCuota = (int)$row['NUMERO_CUOTA_CRUCE'];
if ($numeroCuota === 0) {
    $numeroCuota = 1; // Siesa manda 0 cuando es una sola cuota
}

$indice = $numeroCuota - 1;

// Valor por defecto (por si hay algún dato raro)
$fechaInicialCuota = date('Ymd');

// Validación de seguridad
if ($indice >= 0 && $indice < count($fechasCuotas)) {
    $fechaInicialCuota = $fechasCuotas[$indice];
}

            // =====================================
            // ✔ ESCRITURA DE COLUMNAS DEL EXCEL
            // =====================================

            $sheet->setCellValue("A{$fila}", 7);
            $sheet->setCellValue("B{$fila}", $row['ID_TERCERO']);
            $sheet->setCellValue("D{$fila}", 570);
            $sheet->setCellValue("E{$fila}", 1);
            $sheet->setCellValue("F{$fila}", 1);
           // $sheet->setCellValue("M{$fila}", $row['UNIDAD_DE_NEGOCIO']);
            $sheet->setCellValue("N{$fila}", $row['TIPO_DOCUMENTO_CRUCE']);
            $sheet->setCellValue("O{$fila}", $row['NUMERO_DOCUMENTO_CRUCE']);
            $sheet->setCellValue("P{$fila}", $row['NUMERO_CUOTA_CRUCE']);
            $sheet->setCellValue("Q{$fila}", 0);
            $sheet->setCellValue("R{$fila}", (float)$row['VALOR']);
            $sheet->setCellValue("S{$fila}", 0);
            $sheet->setCellValue("T{$fila}", (float)$row['VALOR']);
            $sheet->setCellValue("U{$fila}", 0);
            $sheet->setCellValue("V{$fila}", 0);
            //$sheet->setCellValue("J{$fila}", $row['CENTRO_OPERACION']  );
           // $sheet->setCellValue("K{$fila}", $row['CENTRO_COSTOS']  );
            $sheet->setCellValue("L{$fila}", ''  );
            $sheet->setCellValue("X{$fila}", 1);
            $sheet->setCellValue("Y{$fila}", $row['ID_TERCERO']);
            $sheet->setCellValue("Z{$fila}", $row['SUCURSAL_CLIENTE']);
            $sheet->setCellValue("AB{$fila}", 0);
            $sheet->setCellValue("AC{$fila}", 
                $row['TIPO_DOCUMENTO_CRUCE'] . '-' . $row['NUMERO_DOCUMENTO_CRUCE']
            );

            // ⭐ NUEVA: Columna AE = FECHA INICIAL QUINCENAL
            $sheet->setCellValue("AE{$fila}", $fechaInicialCuota);

            $fila++;
        }

        // Guardar archivo temp
        $tempDir = Yii::getAlias('@frontend/web/temp');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        $filename = 'reporte_creditos_empleados_' . date('Ymd_His') . '.xlsx';
        $filePath = $tempDir . '/' . $filename;

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($filePath);

        return Yii::$app->response->sendFile($filePath, $filename)->on(
            \yii\web\Response::EVENT_AFTER_SEND,
            function ($event) {
                @unlink($event->data);
            },
            $filePath
        );
    }

    /**
     * Genera el DOCUMENTO CONTABLE (NC) para TODOS los empleados con anticipos pendientes.
     * Un NC por empleado (consecutivo correlativo), todos en un solo archivo Excel.
     */
    public function actionGenerarCruceGeneral()
    {
        $db = Yii::$app->dbSiesa;

        // 1. Obtener todos los NITs con anticipos pendientes
        $sqlNits = <<<SQL
SELECT DISTINCT LTRIM(RTRIM(T.f200_nit)) AS nit
FROM t353_co_saldo_abierto AS SA
INNER JOIN t200_mm_terceros AS T ON SA.f353_rowid_tercero = T.f200_rowid
WHERE SA.f353_id_cia = 7
  AND SA.f353_rowid_auxiliar = 20805
  AND SA.f353_ind_anticipo = 1
  AND SA.f353_total_db = 0
  AND SA.f353_total_cr > 0
ORDER BY nit
SQL;
        $nits = $db->createCommand($sqlNits)->queryColumn();

        if (empty($nits)) {
            Yii::$app->session->setFlash('warning', 'No hay empleados con pagos de nómina pendientes de cruzar.');
            return $this->redirect(['historial-facturas']);
        }

        // 2. Para cada NIT, calcular entradas FIFO
        $todosPorNit = []; // [ ['nit'=>..., 'entradas'=>[...]], ... ]

        foreach ($nits as $nit) {
            // Anticipos disponibles
            $anticipos = $db->createCommand(<<<SQL
SELECT SA.f353_id_tipo_docto_cruce AS tipo_docto,
       SA.F353_CONSEC_DOCTO_CRUCE  AS consec_docto,
       SUM(SA.f353_total_cr)       AS total_anticipo
FROM t353_co_saldo_abierto AS SA
INNER JOIN t200_mm_terceros AS T ON SA.f353_rowid_tercero = T.f200_rowid
WHERE LTRIM(RTRIM(T.f200_nit)) = :nit
  AND SA.f353_id_cia = 7
  AND SA.f353_rowid_auxiliar = 20805
  AND SA.f353_ind_anticipo = 1
  AND SA.f353_total_db = 0
GROUP BY SA.f353_id_tipo_docto_cruce, SA.F353_CONSEC_DOCTO_CRUCE
HAVING SUM(SA.f353_total_cr) > 0
SQL, [':nit' => $nit])->queryAll();

            if (empty($anticipos)) continue;

            $disponible = [];
            foreach ($anticipos as $a) {
                $key = $a['tipo_docto'] . '-' . $a['consec_docto'];
                $disponible[$key] = (float) $a['total_anticipo'];
            }

            // Cuotas abiertas FIFO
            $cuotas = $db->createCommand(<<<SQL
SELECT SA.f353_id_tipo_docto_cruce         AS tipo_docto,
       SA.F353_CONSEC_DOCTO_CRUCE          AS consec_docto,
       SA.F353_NRO_CUOTA_CRUCE             AS nro_cuota,
       SA.f353_total_db - SA.f353_total_cr AS saldo,
       SA.F353_ID_CO_CRUCE                 AS co_cruce,
       SA.f353_id_sucursal                 AS sucursal,
       SA.f353_fecha_vcto                  AS fecha_vcto
FROM t353_co_saldo_abierto AS SA
INNER JOIN t200_mm_terceros AS T ON SA.f353_rowid_tercero = T.f200_rowid
WHERE LTRIM(RTRIM(T.f200_nit)) = :nit
  AND SA.f353_id_cia = 7
  AND SA.f353_rowid_auxiliar = 20805
  AND SA.f353_ind_anticipo = 0
  AND SA.f353_total_db > SA.f353_total_cr
ORDER BY SA.f353_id_tipo_docto_cruce, SA.F353_CONSEC_DOCTO_CRUCE, SA.F353_NRO_CUOTA_CRUCE ASC
SQL, [':nit' => $nit])->queryAll();

            $entradas = [];
            foreach ($cuotas as $cuota) {
                $key = $cuota['tipo_docto'] . '-' . $cuota['consec_docto'];
                if (empty($disponible[$key]) || $disponible[$key] <= 0.01) continue;

                $monto = min((float) $cuota['saldo'], $disponible[$key]);
                if ($monto <= 0) continue;

                $fechaVcto = !empty($cuota['fecha_vcto'])
                    ? date('Ymd', strtotime($cuota['fecha_vcto']))
                    : date('Ymd');

                $entradas[] = [
                    'tipo'      => $cuota['tipo_docto'],
                    'consec'    => $cuota['consec_docto'],
                    'cuota'     => (int) $cuota['nro_cuota'],
                    'monto'     => $monto,
                    'co_cruce'  => $cuota['co_cruce'],
                    'sucursal'  => $cuota['sucursal'] ?: '001',
                    'fecha_vcto'=> $fechaVcto,
                ];
                $disponible[$key] -= $monto;
            }

            if (!empty($entradas)) {
                $todosPorNit[] = ['nit' => $nit, 'entradas' => $entradas];
            }
        }

        if (empty($todosPorNit)) {
            Yii::$app->session->setFlash('warning', 'No se encontraron cuotas para cruzar en ningún empleado.');
            return $this->redirect(['historial-facturas']);
        }

        // 3. Construir Excel
        $spreadsheet = new Spreadsheet();

        // ── Hoja 1: Documentocontable ──
        $h1 = $spreadsheet->getActiveSheet();
        $h1->setTitle('Documentocontable');
        $h1->fromArray(
            ['F350_ID_CO','F350_ID_TIPO_DOCTO','F350_CONSEC_DOCTO','AAAAMMDD','F350_ID_TERCERO','Notas'],
            null, 'A1'
        );

        // ── Hoja 2: MovimientoCxC ──
        $h2 = $spreadsheet->createSheet();
        $h2->setTitle('MovimientoCxC');
        $h2->fromArray([
            'F350_ID_CO','F350_ID_TIPO_DOCTO','F350_CONSEC_DOCTO',
            'F351_ID_AUXILIAR','F351_ID_TERCERO','F351_ID_CO_MOV','F351_ID_UN',
            'F351_VALOR_DB','F351_VALOR_CR','F351_NOTAS',
            'F353_ID_SUCURSAL','F353_ID_TIPO_DOCTO_CRUCE','F353_CONSEC_DOCTO_CRUCE',
            'F353_NRO_CUOTA_CRUCE','F353_FECHA_VCTO','F353_FECHA_DSCTO_PP',
            'F354_TERCERO_VEND','F354_NOTAS',
        ], null, 'A1');

        $setText = function($sheet, $coord, $value) {
            $sheet->getCell($coord)->setValueExplicit((string)$value, DataType::TYPE_STRING);
        };

        $filaH1   = 2;
        $filaH2   = 2;
        $consec   = 1; // consecutivo del documento NC por empleado
        $fechaHoy = (int) date('Ymd');

        foreach ($todosPorNit as $item) {
            $nit      = $item['nit'];
            $entradas = $item['entradas'];
            $consecPad = str_pad($consec, 8, '0', STR_PAD_LEFT);

            // Fila en Documentocontable
            $h1->fromArray(
                ['002', 'NC', null, $fechaHoy, '9900', 'CRUCE DOCUMENTOS'],
                null, 'A' . $filaH1
            );
            $setText($h1, 'C' . $filaH1, $consecPad);
            $filaH1++;

            // Filas en MovimientoCxC
            foreach ($entradas as $e) {
                $consecCruce = str_pad($e['consec'],  8, '0', STR_PAD_LEFT);
                $cuotaPad    = str_pad($e['cuota'],   3, '0', STR_PAD_LEFT);
                $sucursalPad = str_pad($e['sucursal'] ?: '001', 3, '0', STR_PAD_LEFT);

                // Línea DÉBITO
                $h2->fromArray([
                    '002', 'NC', null,
                    '13659525', $nit, '002', null,
                    $e['monto'], 0, 'CRUCE DOCUMENTOS',
                    null, $e['tipo'], null,
                    null, $e['fecha_vcto'], $e['fecha_vcto'],
                    '9999', 'CRUCE DOCUMENTOS',
                ], null, 'A' . $filaH2);
                $setText($h2, 'C' . $filaH2, $consecPad);
                $setText($h2, 'G' . $filaH2, '03');
                $setText($h2, 'K' . $filaH2, $sucursalPad);
                $setText($h2, 'M' . $filaH2, $consecCruce);
                $setText($h2, 'N' . $filaH2, $cuotaPad);
                $filaH2++;

                // Línea CRÉDITO
                $h2->fromArray([
                    '002', 'NC', null,
                    '13659525', $nit, $e['co_cruce'], null,
                    0, $e['monto'], 'CRUCE DOCUMENTOS',
                    null, $e['tipo'], null,
                    null, $e['fecha_vcto'], $e['fecha_vcto'],
                    '9900', 'CRUCE DOCUMENTOS',
                ], null, 'A' . $filaH2);
                $setText($h2, 'C' . $filaH2, $consecPad);
                $setText($h2, 'G' . $filaH2, '99');
                $setText($h2, 'K' . $filaH2, $sucursalPad);
                $setText($h2, 'M' . $filaH2, $consecCruce);
                $setText($h2, 'N' . $filaH2, $cuotaPad);
                $filaH2++;
            }

            $consec++;
        }

        // 4. Guardar y descargar
        $tempDir = Yii::getAlias('@frontend/web/temp');
        if (!is_dir($tempDir)) mkdir($tempDir, 0777, true);

        $filename = 'CRUCE_GENERAL_' . date('Ymd_His') . '.xlsx';
        $filePath = $tempDir . '/' . $filename;

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($filePath);

        return Yii::$app->response->sendFile($filePath, $filename)->on(
            \yii\web\Response::EVENT_AFTER_SEND,
            function ($event) { @unlink($event->data); },
            $filePath
        );
    }

    /**
     * Genera el DOCUMENTO CONTABLE (NC) para cruzar pagos de nómina
     * con las cuotas de facturas crédito del empleado.
     * Orden FIFO: cuota 1 → cuota 2 → ... por factura.
     */
    public function actionGenerarCruce()
    {
        $nit = Yii::$app->request->get('nit', '');
        if (!$nit) {
            Yii::$app->session->setFlash('error', 'NIT requerido.');
            return $this->redirect(['historial-facturas']);
        }

        $db = Yii::$app->dbSiesa;

        // 1. Pagos de nómina pendientes (anticipo=1, CO=002) agrupados por factura
        $sqlAnticipos = <<<SQL
SELECT
    SA.f353_id_tipo_docto_cruce AS tipo_docto,
    SA.F353_CONSEC_DOCTO_CRUCE  AS consec_docto,
    SUM(SA.f353_total_cr)       AS total_anticipo
FROM t353_co_saldo_abierto AS SA
INNER JOIN t200_mm_terceros AS T ON SA.f353_rowid_tercero = T.f200_rowid
WHERE LTRIM(RTRIM(T.f200_nit)) = :nit
  AND SA.f353_id_cia = 7
  AND SA.f353_rowid_auxiliar = 20805
  AND SA.f353_ind_anticipo = 1
  AND SA.f353_total_db = 0
GROUP BY SA.f353_id_tipo_docto_cruce, SA.F353_CONSEC_DOCTO_CRUCE
HAVING SUM(SA.f353_total_cr) > 0
SQL;

        $anticipos = $db->createCommand($sqlAnticipos, [':nit' => $nit])->queryAll();

        if (empty($anticipos)) {
            Yii::$app->session->setFlash('warning', 'No hay pagos de nómina pendientes de cruzar para este empleado.');
            return $this->redirect([
                'historial-facturas',
                'ReporteCreditosHistorialForm[fecha_inicio]' => date('Y-m-01'),
                'ReporteCreditosHistorialForm[fecha_fin]'    => date('Y-m-t'),
                'ReporteCreditosHistorialForm[ID_TERCERO]'   => $nit,
            ]);
        }

        // Índice disponible: 'TIPO-CONSEC' => monto total de nómina por aplicar
        $disponible = [];
        foreach ($anticipos as $a) {
            $key = $a['tipo_docto'] . '-' . $a['consec_docto'];
            $disponible[$key] = (float) $a['total_anticipo'];
        }

        // 2. Cuotas abiertas de esas facturas, orden cuota ASC
        $sqlCuotas = <<<SQL
SELECT
    SA.f353_id_tipo_docto_cruce         AS tipo_docto,
    SA.F353_CONSEC_DOCTO_CRUCE          AS consec_docto,
    SA.F353_NRO_CUOTA_CRUCE             AS nro_cuota,
    SA.f353_total_db - SA.f353_total_cr AS saldo,
    SA.F353_ID_CO_CRUCE                 AS co_cruce,
    SA.f353_id_sucursal                 AS sucursal,
    SA.f353_fecha_vcto                  AS fecha_vcto
FROM t353_co_saldo_abierto AS SA
INNER JOIN t200_mm_terceros AS T ON SA.f353_rowid_tercero = T.f200_rowid
WHERE LTRIM(RTRIM(T.f200_nit)) = :nit
  AND SA.f353_id_cia = 7
  AND SA.f353_rowid_auxiliar = 20805
  AND SA.f353_ind_anticipo = 0
  AND SA.f353_total_db > SA.f353_total_cr
ORDER BY SA.f353_id_tipo_docto_cruce,
         SA.F353_CONSEC_DOCTO_CRUCE,
         SA.F353_NRO_CUOTA_CRUCE ASC
SQL;

        $cuotas = $db->createCommand($sqlCuotas, [':nit' => $nit])->queryAll();

        // 3. Cruce FIFO por cuota dentro de cada factura
        $entradas = [];
        foreach ($cuotas as $cuota) {
            $key = $cuota['tipo_docto'] . '-' . $cuota['consec_docto'];
            if (empty($disponible[$key]) || $disponible[$key] <= 0.01) continue;

            $monto = min((float) $cuota['saldo'], $disponible[$key]);
            if ($monto <= 0) continue;

            $fechaVcto = !empty($cuota['fecha_vcto'])
                ? date('Ymd', strtotime($cuota['fecha_vcto']))
                : date('Ymd');

            $entradas[] = [
                'tipo'      => $cuota['tipo_docto'],
                'consec'    => $cuota['consec_docto'],
                'cuota'     => (int) $cuota['nro_cuota'],
                'monto'     => $monto,
                'co_cruce'  => $cuota['co_cruce'],
                'sucursal'  => $cuota['sucursal'] ?: '001',
                'fecha_vcto'=> $fechaVcto,
            ];

            $disponible[$key] -= $monto;
        }

        if (empty($entradas)) {
            Yii::$app->session->setFlash('warning', 'No se encontraron cuotas para cruzar.');
            return $this->redirect(['historial-facturas']);
        }

        // 4. Construir el Excel con el mismo formato del plano SIESA
        $spreadsheet = new Spreadsheet();

        // ── Hoja 1: Documentocontable ──
        $h1 = $spreadsheet->getActiveSheet();
        $h1->setTitle('Documentocontable');
        $h1->fromArray(
            ['F350_ID_CO','F350_ID_TIPO_DOCTO','F350_CONSEC_DOCTO','AAAAMMDD','F350_ID_TERCERO','Notas'],
            null, 'A1'
        );
        $h1->fromArray(
            ['002', 'NC', null, (int) date('Ymd'), '9900', 'CRUCE DOCUMENTOS'],
            null, 'A2'
        );
        $h1->getCell('C2')->setValueExplicit('00000001', DataType::TYPE_STRING);

        // ── Hoja 2: MovimientoCxC ──
        $h2 = $spreadsheet->createSheet();
        $h2->setTitle('MovimientoCxC');
        $h2->fromArray([
            'F350_ID_CO','F350_ID_TIPO_DOCTO','F350_CONSEC_DOCTO',
            'F351_ID_AUXILIAR','F351_ID_TERCERO','F351_ID_CO_MOV','F351_ID_UN',
            'F351_VALOR_DB','F351_VALOR_CR','F351_NOTAS',
            'F353_ID_SUCURSAL','F353_ID_TIPO_DOCTO_CRUCE','F353_CONSEC_DOCTO_CRUCE',
            'F353_NRO_CUOTA_CRUCE','F353_FECHA_VCTO','F353_FECHA_DSCTO_PP',
            'F354_TERCERO_VEND','F354_NOTAS',
        ], null, 'A1');

        // Helper para escribir una celda como texto explícito (preserva ceros a la izquierda)
        $setText = function($sheet, $coord, $value) {
            $sheet->getCell($coord)->setValueExplicit((string)$value, DataType::TYPE_STRING);
        };

        $consecDoc   = str_pad(1, 8, '0', STR_PAD_LEFT); // '00000001'

        $fila = 2;
        foreach ($entradas as $e) {
            $consecCruce = str_pad($e['consec'],  8, '0', STR_PAD_LEFT);
            $cuotaPad    = str_pad($e['cuota'],   3, '0', STR_PAD_LEFT);
            $sucursalPad = str_pad($e['sucursal'] ?: '001', 3, '0', STR_PAD_LEFT);

            // Línea DÉBITO: cierra el anticipo de nómina (CO=002, UN=003)
            $h2->fromArray([
                '002', 'NC', null,
                '13659525', $nit, '002', null,
                $e['monto'], 0, 'CRUCE DOCUMENTOS',
                null, $e['tipo'], null,
                null, $e['fecha_vcto'], $e['fecha_vcto'],
                '9999', 'CRUCE DOCUMENTOS',
            ], null, 'A' . $fila);
            $setText($h2, 'C' . $fila, $consecDoc);
            $setText($h2, 'G' . $fila, '03');
            $setText($h2, 'K' . $fila, $sucursalPad);
            $setText($h2, 'M' . $fila, $consecCruce);
            $setText($h2, 'N' . $fila, $cuotaPad);
            $fila++;

            // Línea CRÉDITO: aplica el pago a la cuota de la factura original
            $h2->fromArray([
                '002', 'NC', null,
                '13659525', $nit, $e['co_cruce'], null,
                0, $e['monto'], 'CRUCE DOCUMENTOS',
                null, $e['tipo'], null,
                null, $e['fecha_vcto'], $e['fecha_vcto'],
                '9900', 'CRUCE DOCUMENTOS',
            ], null, 'A' . $fila);
            $setText($h2, 'C' . $fila, $consecDoc);
            $setText($h2, 'G' . $fila, '99');
            $setText($h2, 'K' . $fila, $sucursalPad);
            $setText($h2, 'M' . $fila, $consecCruce);
            $setText($h2, 'N' . $fila, $cuotaPad);
            $fila++;
        }

        // 5. Guardar y descargar
        $tempDir = Yii::getAlias('@frontend/web/temp');
        if (!is_dir($tempDir)) mkdir($tempDir, 0777, true);

        $filename = 'CRUCE_' . $nit . '_' . date('Ymd_His') . '.xlsx';
        $filePath = $tempDir . '/' . $filename;

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($filePath);

        return Yii::$app->response->sendFile($filePath, $filename)->on(
            \yii\web\Response::EVENT_AFTER_SEND,
            function ($event) { @unlink($event->data); },
            $filePath
        );
    }
}
