<?php

namespace frontend\modules\nomina\controllers;

use Yii;
use frontend\models\Empleadologistica;
use frontend\models\search\EmpleadologisticaSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\widgets\ActiveForm;

use frontend\models\SignupEmpleadoLogistica;
use frontend\models\Userconteo;

/**
 * EmpleadologisticaController implements the CRUD actions for Empleadologistica model.
 */
class EmpleadologisticaController extends Controller
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
     * Lists all Empleadologistica models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new EmpleadologisticaSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Empleadologistica model.
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
     * Creates a new Empleadologistica model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Empleadologistica();
        $model->idEstado = 1;

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
     * Updates an existing Empleadologistica model.
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

    public function actionUser($id)
    {
        $modelempleadologisitica = $this->findModel($id);

        $modeluserconteo = Userconteo::findOne(['idEmpleadoLogistica' => $id]);

        $model = new SignupEmpleadoLogistica();

        $idUser = null;

        if ($modeluserconteo){
            $model->username = $modeluserconteo->user->username;
            $model->email = $modeluserconteo->user->email;
        }

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        } 

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                $user = $model->signup();
                if ($user){
                    $idUser = $user->id;
                }

                if ((!$modeluserconteo) && ($user)){
                    $modeluserconteo = new Userconteo();
                    $modeluserconteo->idUser = $idUser;
                    $modeluserconteo->idEmpleadoLogistica = $id;
                    $modeluserconteo->save();
                }

                if (($user != null) && ($modeluserconteo != null)){
                    Yii::$app->session->setFlash( 'success', 'Registro Actualizado');
                }else{
                    Yii::$app->session->setFlash( 'error', 'Error Actualizando Registro');
                }

                return $this->redirect(['index']);
            }
        } 

        if (Yii::$app->request->isAjax){  
            return $this->renderAjax('signup', [
                'model' => $model,
            ]);
        }  
    }

    public function actionAssign($id)
    {
        $model = Userconteo::findOne(['idEmpleadoLogistica' => $id]);

        if (!$model){
            $model = new Userconteo();
            $model->idEmpleadoLogistica = $id;
        }

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
            return $this->renderAjax('assignuser', [
                'model' => $model,
            ]);
        }  
    }

    /**
     * Deletes an existing Empleadologistica model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        Yii::$app->session->setFlash( 'success', 'Registro Eliminado');

        return $this->redirect(['index']);
    }

    /**
     * Finds the Empleadologistica model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Empleadologistica the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Empleadologistica::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
