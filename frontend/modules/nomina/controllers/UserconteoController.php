<?php

namespace frontend\modules\nomina\controllers;

use Yii;
use frontend\models\Userconteo;
use frontend\models\search\UserconteoSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\widgets\ActiveForm;

use frontend\models\SignupEmpleadoLogistica;

/**
 * UserconteoController implements the CRUD actions for Userconteo model.
 */
class UserconteoController extends Controller
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
     * Lists all Userconteo models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new UserconteoSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Userconteo model.
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
     * Creates a new Userconteo model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Userconteo();

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        } 

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                $id = null;
                if ($model->validate()){
                    $modeluser = new SignupEmpleadoLogistica();
                    $modeluser->username = $model->username;
                    $modeluser->email = $model->email;
                    $modeluser->password = $model->password;
                    $modeluser->retypePassword = $model->retypePassword;

                    $idUser = null;
                    $user = $modeluser->signup();
                    if ($user){
                        $idUser = $user->id;
                    }                    

                    $model->idUser = $idUser;
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
     * Updates an existing Userconteo model.
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

                    $modeluser = new SignupEmpleadoLogistica();
                    $modeluser->username = $model->username;
                    $modeluser->email = $model->email;
                    $modeluser->password = $model->password;
                    $modeluser->retypePassword = $model->retypePassword;

                    $idUser = null;
                    $user = $model->signup();
                    if ($user){
                        $idUser = $user->id;
                    }                    

                    $model->idUser = $idUser;

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
     * Deletes an existing Userconteo model.
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

                /*$modeluser = User::findOne(['id' => $model->idUser]);
                $modeluser->status = 9;
                $modeluser->save();

                $modelemp = Empleadologistica::findOne(['id' => $model->idEmpleadoLogistica]);
                $modelemp->idEstado = 0;
                $modelemp->save();*/

                Yii::$app->session->setFlash( 'success', 'Registro Eliminado');
            } catch (\yii\db\IntegrityException $e) {
                Yii::$app->session->setFlash('error', 'No se puede eliminar este registro debido a que tiene agendamientos-conteos asociados.');
            }
        } else {
            Yii::$app->session->setFlash('error', 'Registro no encontrado.');
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the Userconteo model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Userconteo the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Userconteo::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
