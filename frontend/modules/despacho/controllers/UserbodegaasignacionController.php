<?php

namespace frontend\modules\despacho\controllers;

use Yii;
use frontend\models\Userbodegaasignacion;
use frontend\models\search\UserbodegaasignacionSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\widgets\ActiveForm;

use frontend\models\Userbodega;
use common\models\ProcedimientosGenerales;

/**
 * UserbodegaasignacionController implements the CRUD actions for Userbodegaasignacion model.
 */
class UserbodegaasignacionController extends Controller
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
     * Lists all Userbodegaasignacion models.
     *
     * @return string
     */
    public function actionIndex($iduserbodega = null)
    {
        $modeluserbodega = Userbodega::findOne(['id' => $iduserbodega]);

        $searchModel = new UserbodegaasignacionSearch();
        $dataProvider = $searchModel->search($this->request->queryParams, $iduserbodega);

        $programa = 'index';
        if ($iduserbodega == null){
            $programa = 'indexall';
        }

        return $this->render($programa, [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'modeluserbodega' => $modeluserbodega
        ]);
    }

    /**
     * Displays a single Userbodegaasignacion model.
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
     * Creates a new Userbodegaasignacion model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate($iduserbodega)
    {
        $model = new Userbodegaasignacion();
        $model->idEstado = 1;
        $model->idUserBodega = $iduserbodega;

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
                    $message = ProcedimientosGenerales::erroresModelo($model->getErrors());
                    Yii::$app->session->setFlash( 'error', 'Error Actualizando Registro: ' . $message);
                }

                return $this->redirect(['index', 'iduserbodega' => $iduserbodega]);
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
     * Updates an existing Userbodegaasignacion model.
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
     * Deletes an existing Userbodegaasignacion model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $iduserbodega = $model->idUserBodega;

        $model->delete();

        return $this->redirect(['index', 'iduserbodega' => $iduserbodega]);
    }

    /**
     * Finds the Userbodegaasignacion model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Userbodegaasignacion the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Userbodegaasignacion::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
