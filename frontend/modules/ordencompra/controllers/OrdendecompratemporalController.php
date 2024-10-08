<?php

namespace frontend\modules\ordencompra\controllers;

use Yii;
use frontend\models\Ordendecompratemporal;
use frontend\models\search\OrdendecompratemporalSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\widgets\ActiveForm;

use frontend\models\Centrooperacion;
use frontend\models\Tipodocumento;
use frontend\models\Condicionpago;

/**
 * OrdendecompratemporalController implements the CRUD actions for Ordendecompratemporal model.
 */
class OrdendecompratemporalController extends Controller
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
     * Lists all Ordendecompratemporal models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new OrdendecompratemporalSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Ordendecompratemporal model.
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
     * Creates a new Ordendecompratemporal model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Ordendecompratemporal();

        $centrooperacion = Centrooperacion::find()->where(['codigo' => '002'])->one();
        $tipodocumento = Tipodocumento::find()->where(['codigo' => '2CA'])->one();
        $condicionpago = Condicionpago::find()->where(['codigo' => '060'])->one();

        $model->idCO = $centrooperacion->id;
        $model->idTipoDocumento = $tipodocumento->id;
        $model->fechaDocumento = date('Y-m-d');
        $model->sucursalProveedor = '000';
        $model->idCondicionPago = $condicionpago->id;

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
     * Updates an existing Ordendecompratemporal model.
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
     * Deletes an existing Ordendecompratemporal model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        if ($model !== null) {
            try {
                $model->delete();

                Yii::$app->session->setFlash( 'success', 'Registro Eliminado');
            } catch (\yii\db\IntegrityException $e) {
                Yii::$app->session->setFlash('error', 'No se puede eliminar este registro debido a que tiene Items asociados.');
            }
        } else {
            Yii::$app->session->setFlash('error', 'Registro no encontrado.');
        }

        return $this->redirect(['index']);
    }

    public function actionGenerarexcelsiesa ($idordencompra){

        $rutaGuardado = Ordendecompratemporal::generarexcelsiesa($idordencompra);

        Yii::$app->response->sendFile($rutaGuardado)->send();
        
        return $this->redirect(['index']);
    }

    public function actionGenerarexcelicg ($idordencompra){

        $rutaGuardado = Ordendecompratemporal::generarexcelicg($idordencompra);

        Yii::$app->response->sendFile($rutaGuardado)->send();
        
        return $this->redirect(['index']);
    }

    /**
     * Finds the Ordendecompratemporal model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Ordendecompratemporal the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Ordendecompratemporal::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
