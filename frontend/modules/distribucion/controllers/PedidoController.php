<?php

namespace frontend\modules\distribucion\controllers;

use Yii;
use frontend\models\Pedido;
use frontend\models\Search\PedidoSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\widgets\ActiveForm;
use yii\web\UploadedFile;

use frontend\models\Centrooperacion;
use frontend\models\Ordendecompra;
use frontend\models\FileFormInput;
use common\models\ProcedimientosGenerales;
use common\components\PedidoImportService;

/**
 * PedidoController implements the CRUD actions for Pedido model.
 */
class PedidoController extends Controller
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
     * Lists all Pedido models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new PedidoSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Pedido model.
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
     * Creates a new Pedido model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreatearchivo()
    {
        $model = new Pedido();
        $model->origen = 'Archivo';

        $modelco = Centrooperacion::findOne(['codigo' => '002']);
        $model->idCentroOperacion = $modelco->id;

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {

                $modeloc = Ordendecompra::find()
                    ->where([
                        'idCO' => $model->idCentroOperacion,
                        'idTipoDocumento' => $model->idTipoDocumento,
                        'consecutivo' => $model->consecutivo
                    ])->one();

                if ($modeloc) {
                    $model->idOrdenCompra = $modeloc->id;

                    if (!$model->save()) {
                        Yii::$app->session->setFlash('error', 'Error Registrando Datos Factura');
                    } else {
                        Yii::$app->session->setFlash('success', 'Factura Registrada Con ÉXito');
                    }
                } else {
                    Yii::$app->session->setFlash('error', 'Orden de Compra NO Existe: ' . $model->idCentroOperacion . '-' . $model->idTipoDocumento . '-' . $model->consecutivo . 'o su estado es No Cumplio');
                }

                return $this->redirect(['index']);
            }
        }

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('create_archivo', [
                'model' => $model,
            ]);
        }
    }

    public function actionCreate()
    {
        $model = new Pedido();
        $model->fecha = date('Y-m-d');

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {

                if (!$model->save()) {
                    Yii::$app->session->setFlash('error', 'Error Registrando Datos Pedido');
                } else {
                    Yii::$app->session->setFlash('success', 'Pedido Registrado Con ÉXito');
                }

                return $this->redirect(['index']);
            }
        }

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Pedido model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        }

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Pedido model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        if ($model->nroOrdenesCompra > 0 || $model->totalUnidades > 0) {
            Yii::$app->session->setFlash('error', 'Pedido Tienes Ordenes de Compra Relacionadas');
            return $this->redirect(['index']);
        }

        $model->delete();
        Yii::$app->session->setFlash('success', 'Pedido Eliminado Con ÉXito');

        return $this->redirect(['index']);
    }

    public function actionImportardataxls($id)
    {
        $model = new FileFormInput();

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post())) {

            $userId = Yii::$app->user->id;
            $model->archivo = UploadedFile::getInstance($model, 'archivo');

            if (!$model->archivo) {
                Yii::$app->session->setFlash('error', 'Debes adjuntar un archivo .xlsx.');
                return $this->redirect(['index']);
            }

            $ext = strtolower($model->archivo->getExtension());

            // Guardar en carpeta temporal
            $uploadDir = Yii::getAlias('@runtime/uploads');
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0775, true);
            }
            $tmpPath = $uploadDir . DIRECTORY_SEPARATOR .
                pathinfo($model->archivo->name, PATHINFO_FILENAME) .
                '-' . time() . '.' . $ext;

            if (!$model->archivo->saveAs($tmpPath)) {
                Yii::$app->session->setFlash('error', 'No se pudo guardar el archivo temporalmente.');
                return $this->redirect(['index']);
            }

            // Importar con el servicio (actualiza Pedido y crea Detalles con save())
            try {
                $svc = new PedidoImportService();
                $res = $svc->importarData((int)$id, $tmpPath);

                // Éxito: mensaje con resumen
                Yii::$app->session->setFlash(
                    'success',
                    "Archivo importado correctamente.<br>" .
                        "Pedido #{$res['pedido_id']} · Fecha: {$res['fecha']}<br>" .
                        "Bodega: {$res['bodega_codigo']} - {$res['bodega_nombre']}<br>" .
                        "Items (pestañas): {$res['total_items']} · Unidades totales: {$res['total_unidades']}<br>" .
                        "Detalles creados: {$res['detalles_creados']}"
                );
            } catch (\Throwable $e) {
                Yii::error($e->getMessage() . ' ' . $e->getTraceAsString(), __METHOD__);
                Yii::$app->session->setFlash('error', 'Ocurrió un error en la importación: ' . $e->getMessage());
            } finally {
                @unlink($tmpPath); // limpiar temporal
            }

            return $this->redirect(['index']);
        }

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('uploadtransferencia', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Finds the Pedido model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Pedido the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Pedido::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
