<?php
namespace frontend\modules\contabilidad\controllers;

use frontend\models\Centrooperacion;
use Yii;
use yii\web\Controller;
use frontend\models\ReporteCreditosEmpleadosForm;
use PhpOffice\PhpSpreadsheet\IOFactory;

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
}
