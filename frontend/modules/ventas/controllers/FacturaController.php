<?php

namespace frontend\modules\ventas\controllers;

use Yii;
use frontend\modules\ventas\models\Factura;
use frontend\modules\ventas\models\search\FacturaSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\widgets\ActiveForm;
use yii\web\Response;

use frontend\modules\ventas\models\Viewventapos;
use frontend\modules\ventas\models\Estadofactura;
use frontend\modules\ventas\models\Facturadetalle;
use frontend\modules\ventas\models\Facturaitem;
use frontend\modules\ventas\models\Transferencia;

/**
 * FacturaController implements the CRUD actions for Factura model.
 */
class FacturaController extends Controller
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
     * Lists all Factura models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new FacturaSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Factura model.
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
     * Creates a new Factura model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Factura();

        $model->centroOperacion = '002';
        $model->consecutivoDocumento = 1;
        $model->fechaDesde = date('Y-m-d');
        $model->fechaHasta = date('Y-m-d');
        $model->fechaDocumento = date('Y-m-d');
        $model->tipoDocumento = 'ECG';
        $model->valorDocumento = 0;
        $model->tieneNotaCredito = 0;

        $estado = Estadofactura::find()->where(['codigo' => 1])->one();
        $model->idEstado = $estado->id;

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        } 

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                if ($model->validate()){

                    $totalEntrada = Viewventapos::totalEntradas($model->proveedor->codigo,
                                                        $model->fechaDesde,
                                                        $model->fechaHasta);

                    $totalDevolucion = Viewventapos::totalDevoluciones($model->proveedor->codigo,
                                                        $model->fechaDesde,
                                                        $model->fechaHasta);

                    $total = $totalEntrada + $totalDevolucion;

                    $model->valorDocumento = $total;
                    $model->save();   

                    $result = Facturadetalle::grabarItems ($model, $model->tieneNotaCredito);

                    /*if ($total <> 0){
                        $model->valorDocumento = $total;
                        $model->save();   

                        $result = Facturadetalle::grabarItems ($model, $model->tieneNotaCredito);
                    }

                    if ($model->tieneNotaCredito){
                        $total = Viewventapos::totalDevoluciones($model->proveedor->codigo,
                                                            $model->fechaDesde,
                                                            $model->fechaHasta);

                        if ($total <> 0){
                            $modeldevol = new Factura();
                            $modeldevol->attributes = $model->attributes;
                            $modeldevol->valorDocumento = $total;
                            $modeldevol->tipoDocumento = 'DCG';
                            $modeldevol->save();

                            $result = Facturadetalle::grabarItems ($modeldevol, $model->tieneNotaCredito);
                        }
                    }*/

                    return $this->redirect(['index']);
                }else{
                    var_dump($model->getErrors()); die("hola");
                }
            }
        } else {
            $model->loadDefaultValues();
        }

        if (Yii::$app->request->isAjax){  
            return $this->renderAjax('create', [
                'model' => $model,
            ]);
        }  
    }

    /**
     * Updates an existing Factura model.
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

        if (Yii::$app->request->isAjax){  
            return $this->renderAjax('update', [
                'model' => $model,
            ]);
        }  
    }

    /**
     * Deletes an existing Factura model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $numRegistrosBorrados = Facturadetalle::deleteAll(['idFactura' => $id]);
        $numRegistrosBorrados = Facturaitem::deleteAll(['idFactura' => $id]);
        $numRegistrosBorrados = Transferencia::deleteAll(['idFactura' => $id]);

        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Factura model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Factura the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Factura::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
