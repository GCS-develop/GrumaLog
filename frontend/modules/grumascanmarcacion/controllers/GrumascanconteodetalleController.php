<?php

namespace frontend\modules\grumascanmarcacion\controllers;

use Yii;
use yii\web\Response;
use frontend\models\forms\ExportInvFisicoForm;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use frontend\models\Grumascanconteodetalle;
use frontend\models\search\GrumascanconteodetalleSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * GrumascanconteodetalleController implements the CRUD actions for Grumascanconteodetalle model.
 */
class GrumascanconteodetalleController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all Grumascanconteodetalle models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new GrumascanconteodetalleSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Grumascanconteodetalle model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Grumascanconteodetalle model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Grumascanconteodetalle();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Grumascanconteodetalle model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Grumascanconteodetalle model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Grumascanconteodetalle model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Grumascanconteodetalle the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Grumascanconteodetalle::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionExportFisico()
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        $model = new \frontend\models\forms\ExportInvFisicoForm();

        if ($this->request->isPost) {

            if (!$model->load($this->request->post()) || !$model->validate()) {
                Yii::$app->session->setFlash('error', 'Datos inválidos para exportar.');
                return $this->render('export-fisico', ['model' => $model]);
            }

            // 1) Plantilla
            $local = Yii::getAlias('@frontend/web/plantillas/INV_FISICO_TEMPLATE.xlsx');
            $unc   = '\\\\192.168.2.20\\c$\\Apache24\\htdocs\\GRUMALog\\frontend\\web\\plantillas\\INV_FISICO_TEMPLATE.xlsx';
            $templatePath = is_file($local) ? $local : $unc;

            if (!is_file($templatePath)) {
                throw new NotFoundHttpException('No existe la plantilla de exportación.');
            }

            // 2) Parámetros
            $ESTADO_TERMINADO = 1;
            $bodegaId = (int)$model->idbodega; // PK bodegas.id
            $fecha    = $model->fecha;         // 'YYYY-MM-DD'

            // 3) Ejecutar SP
            $rows = Yii::$app->db->createCommand(
                'EXEC dbo.sp_grumascan_export_inv_fisico :bodegaId, :fecha, :estado',
                [
                    ':bodegaId' => $bodegaId,
                    ':fecha'    => $fecha,
                    ':estado'   => $ESTADO_TERMINADO,
                ]
            )->queryAll();

            // 4) Si viene vacío, mostrar mensaje claro
            if (empty($rows)) {

                $bodegaInfo = $model->getBodegaModel(); // Bodegas AR o null
                $cod = $bodegaInfo ? $bodegaInfo->codigo : (string)$bodegaId;
                $nom = $bodegaInfo ? $bodegaInfo->nombre : '';

                $msg = "No se generó el archivo porque no existen registros para exportar con los filtros seleccionados:\n"
                    . "- Bodega: {$cod}" . ($nom ? " ({$nom})" : "") . "\n"
                    . "- Fecha: {$fecha}\n"
                    . "- Estado requerido: Terminado ({$ESTADO_TERMINADO})";

                Yii::$app->session->setFlash('warning', nl2br($msg));
                return $this->render('export-fisico', ['model' => $model]);
            }

            // 5) Abrir plantilla y escribir Excel
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($templatePath);
            $sheet = $spreadsheet->getSheetByName('Físico') ?: $spreadsheet->getActiveSheet();

            $rowNum = 2;

            foreach ($rows as $r) {

                $bodega3 = str_pad((string)$r['bodega_codigo'], 3, '0', STR_PAD_LEFT);

                // A: siempre 1
                $sheet->setCellValueExplicit('A' . $rowNum, '1', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

                // B: código bodega
                $sheet->setCellValueExplicit('B' . $rowNum, $bodega3, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

                // C,D,F,G,H: ceros
                $sheet->setCellValue('C' . $rowNum, 0);
                $sheet->setCellValue('D' . $rowNum, 0);
                $sheet->setCellValue('F' . $rowNum, 0);
                $sheet->setCellValue('G' . $rowNum, 0);
                $sheet->setCellValue('H' . $rowNum, 0);

                // E: unidades
                $sheet->setCellValue('E' . $rowNum, (int)$r['unidades']);

                // I/J/K
                $sheet->setCellValueExplicit('I' . $rowNum, (string)$r['item'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValueExplicit('J' . $rowNum, (string)($r['color'] ?? ''), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValueExplicit('K' . $rowNum, (string)($r['talla'] ?? ''), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

                $rowNum++;
            }

            // 6) Guardar y descargar
            $bodega3File = str_pad((string)$rows[0]['bodega_codigo'], 3, '0', STR_PAD_LEFT);

            $outName = 'INV_FISICO_' . $fecha . '_BODEGA_' . $bodega3File . '.xlsx';
            $outPath = Yii::getAlias('@runtime/' . $outName);

            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save($outPath);

            // NO setFlash success aquí: este request responde un archivo y el flash queda “pegado” para luego.
            return Yii::$app->response->sendFile($outPath, $outName, [
                'mimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'inline' => false,
            ]);
        }

        // GET
        $model->fecha = date('Y-m-d');
        return $this->render('export-fisico', ['model' => $model]);
    }
}
