<?php

namespace frontend\modules\programacion\controllers;

use frontend\models\Agendaentregamercancia;
use frontend\models\Ordendecompra;
use Yii;
use frontend\models\Facturaentregamercancia;
use frontend\models\search\FacturaentregamercanciaSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\widgets\ActiveForm;

use frontend\models\Centrooperacion;
use frontend\models\search\ProgramacionentregamercanciaSearch;

/**
 * FacturaentregamercanciaController implements the CRUD actions for Facturaentregamercancia model.
 */
class FacturaentregamercanciaController extends Controller
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
     * Lists all Facturaentregamercancia models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new FacturaentregamercanciaSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionIndexprogramacion()
    {
        $searchModel = new FacturaentregamercanciaSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index_programacion', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionIndexconteoprogramacion($idfactura)
    {
        $id = null;
        $idestadoconteo = 2;
        $idestadoprogramacion = 1;

        $searchModel = new ProgramacionentregamercanciaSearch();
        $dataProvider = $searchModel->searchxFactura($this->request->queryParams, $idfactura);

        return $this->render('index_conteo_programacion', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'idfactura' => $idfactura
        ]);
    }

    public function actionIndexlegalizaconteo()
    {
        $searchModel = new FacturaentregamercanciaSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index_legalizacion_conteo', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Facturaentregamercancia model.
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
     * Creates a new Facturaentregamercancia model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Facturaentregamercancia();

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
                if ($modeloc){
                    $modelagenda = Agendaentregamercancia::find()
                                            ->where([
                                                'idOrdenCompra' => $modeloc->id
                                            ])->one();
                    if ($modelagenda){
                        $model->idAgendaEntregaMercancia = $modelagenda->id;
                        if (!$model->save()){
                            Yii::$app->session->setFlash( 'error', 'Error Registrando Datos Factura' );    
                        }else{
                            Yii::$app->session->setFlash( 'success', 'Factura Registrada Con ÉXito' );
                        }
                    }else{
                        Yii::$app->session->setFlash( 'error', 'Orden de Compra No Ha Sido Agendada' );
                    }
                }else{
                    Yii::$app->session->setFlash( 'error', 'Orden de Compra NO Existe: ' . $model->idCentroOperacion. '-' . $model->idTipoDocumento . '-' . $model->consecutivo );
                }

                return $this->redirect(['index']);
            }
        } 

        if (Yii::$app->request->isAjax){  
            return $this->renderAjax('create', [
                'model' => $model,
            ]);
        }  

    }

    /**
     * Updates an existing Facturaentregamercancia model.
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
     * Deletes an existing Facturaentregamercancia model.
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
     * Finds the Facturaentregamercancia model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Facturaentregamercancia the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Facturaentregamercancia::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
