<?php

namespace frontend\modules\ordencompra\controllers;

use frontend\models\Impresoraspaxarbodega;
use Yii;
use frontend\models\Ordendecompra;
use frontend\models\search\OrdendecompraSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\widgets\ActiveForm;

use frontend\models\Centrooperacion;
use frontend\models\DataOrdenCompra;

/**
 * OrdendecompraController implements the CRUD actions for Ordendecompra model.
 */
class OrdendecompraController extends Controller
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
     * Lists all Ordendecompra models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new OrdendecompraSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Ordendecompra model.
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
     * Creates a new Ordendecompra model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new DataOrdenCompra();

        $modelco = Centrooperacion::findOne(['codigo' => '002']);
        $model->idCentroOperacion = $modelco->id;

        $idCia = 7;

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        } 

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                $id = null;
                $mensajeError = 'Error Actualizando Registro';

                if ($model->validate()){
                    $OK = Ordendecompra::insertarDatosOC ($idCia, 
                                                            $model->idCentroOperacion, 
                                                            $model->idTipoDocumento, 
                                                            $model->numeroOrdenCompra);

                    if ($OK < 1){
                        switch($OK){
                            case - 1:
                                Yii::$app->session->setFlash( 'error', 'OC. Tiene Inconsistencias. No Unidades de Item No Son Equivalentes a la Unidad de Medida');
                                break;
                            case 0: 
                                Yii::$app->session->setFlash( 'error', 'Número Orden de Compra NO Existe');
                                break;
                        }
                    }
                }

                return $this->redirect(['index', 'id' => $model->idAgenda]);
            }
        } 

        if (Yii::$app->request->isAjax){  
            return $this->renderAjax('create', [
                'model' => $model,
            ]);
        }  

    }

    /**
     * Updates an existing Ordendecompra model.
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
     * Deletes an existing Ordendecompra model.
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
     * Finds the Ordendecompra model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Ordendecompra the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Ordendecompra::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
