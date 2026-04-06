<?php

namespace frontend\modules\nomina\controllers;

use Yii;
use frontend\models\Userauditoriaconteo;
use frontend\models\search\UserauditoriaconteoSearch;
use frontend\models\SignupEmpleadoLogistica;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\widgets\ActiveForm;

class UserauditoriaconteoController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class'   => VerbFilter::className(),
                'actions' => ['delete' => ['POST']],
            ],
        ]);
    }

    public function actionIndex()
    {
        $searchModel  = new UserauditoriaconteoSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionCreate()
    {
        $model = new Userauditoriaconteo();

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($this->request->isPost && $model->load($this->request->post())) {
            if ($model->validate()) {
                $signup = new SignupEmpleadoLogistica();
                $signup->username       = $model->username;
                $signup->email          = $model->email;
                $signup->password       = $model->password;
                $signup->retypePassword = $model->retypePassword;

                $user = $signup->signup();
                if ($user) {
                    $model->idUser = $user->id;
                    if ($model->save()) {
                        Yii::$app->session->setFlash('success', 'Usuario registrado correctamente.');
                        return $this->redirect(['index']);
                    }
                }
            }
            Yii::$app->session->setFlash('error', 'Error al registrar el usuario.');
            return $this->redirect(['index']);
        }

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('create', ['model' => $model]);
        }
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        try {
            $model->delete();
            Yii::$app->session->setFlash('success', 'Registro eliminado.');
        } catch (\yii\db\IntegrityException $e) {
            Yii::$app->session->setFlash('error', 'No se puede eliminar: tiene registros asociados.');
        }

        return $this->redirect(['index']);
    }

    protected function findModel($id)
    {
        if (($model = Userauditoriaconteo::findOne(['id' => $id])) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('Registro no encontrado.');
    }
}
