<?php

namespace frontend\modules\ordencompra\controllers;

use Yii;
use frontend\models\Ordendecompratemporalitem;
use frontend\models\search\OrdendecompratemporalitemSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\widgets\ActiveForm;

use frontend\models\Centrooperacion;
use frontend\models\Ordendecompratemporal;

/**
 * OrdendecompratemporalitemController implements the CRUD actions for Ordendecompratemporalitem model.
 */
class OrdendecompratemporalitemController extends Controller
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
     * Lists all Ordendecompratemporalitem models.
     *
     * @return string
     */
    public function actionIndex($idordencompra)
    {
        $searchModel = new OrdendecompratemporalitemSearch();
        $dataProvider = $searchModel->search($this->request->queryParams, $idordencompra);

        $ordendecompra = Ordendecompratemporal::findOne(['id' => $idordencompra]);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'ordendecompra' => $ordendecompra
        ]);
    }

    /**
     * Displays a single Ordendecompratemporalitem model.
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
     * Creates a new Ordendecompratemporalitem model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate($idordencompra)
    {
        $model = new Ordendecompratemporalitem();
        $model->idOrdenCompra = $idordencompra;
        $model->codigoMotivo = '01';
        $model->numeroRegistro = 0;

        $minNumeroRegistro = Ordendecompratemporalitem::find()->where(['idOrdenCompra' => $idordencompra])->max('numeroRegistro');
        $registroMinimo = Ordendecompratemporalitem::findOne(['idOrdenCompra' => $idordencompra, 'numeroRegistro' => $minNumeroRegistro]);

        if ($registroMinimo){
            $model->idBodega = $registroMinimo->idBodega;
            $model->item = $registroMinimo->item;
            $model->fechaEntrega = $registroMinimo->fechaEntrega;
            $model->cantidadPedida = $registroMinimo->cantidadPedida;
            $model->precioUnitario = $registroMinimo->precioUnitario;
        }

        $centrooperacion = Centrooperacion::find()->where(['codigo' => '002'])->one();
        $model->idCOMovimiento = $centrooperacion->id;

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        } 

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                $id = null;
                if ($model->validate()){
                    $id = $model->save();

                    Ordendecompratemporalitem::actualizarRegistro($model->idOrdenCompra);
                }

                if ($id != null){
                    Yii::$app->session->setFlash( 'success', 'Registro Actualizado');
                }else{
                    Yii::$app->session->setFlash( 'error', 'Error Actualizando Registro');
                }

                return $this->redirect(['index', 'idordencompra' => $idordencompra]);
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
     * Updates an existing Ordendecompratemporalitem model.
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

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                $id = null;
                if ($model->validate()){
                    $id = $model->save();
                }

                if ($id != null){
                    Yii::$app->session->setFlash( 'success', 'Registro Actualizado');
                }else{
                    Yii::$app->session->setFlash( 'error', 'Error Actualizando Registro');
                }

                return $this->redirect(['index']);
            }
        }

        if (Yii::$app->request->isAjax){  
            return $this->renderAjax('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Ordendecompratemporalitem model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $idordencompra = $model->idOrdenCompra;
        
        $model->delete();

        $count = Ordendecompratemporalitem::find()->where(['idOrdenCompra' => $idordencompra])->count();

        if ($count > 0){
            return $this->redirect(['index', 'idordencompra' => $idordencompra]);
        }else{
            return $this->redirect(['/ordencompra/ordendecompratempora/index']);            
        }
    }

    /**
     * Finds the Ordendecompratemporalitem model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Ordendecompratemporalitem the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Ordendecompratemporalitem::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
