<?php

namespace frontend\modules\crossdocking\controllers;

use Yii;
use frontend\models\Conteocdscusuario;
use frontend\models\search\ConteocdscusuarioSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\widgets\ActiveForm;

use frontend\models\Conteocdscdestinofactura;

/**
 * ConteocdscusuarioController implements the CRUD actions for Conteocdscusuario model.
 */
class ConteocdscusuarioController extends Controller
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
     * Lists all Conteocdscusuario models.
     *
     * @return string
     */
    public function actionIndex($idconteofactura)
    {
        $modelfactura = Conteocdscdestinofactura::findOne(['id' => $idconteofactura]);

        $searchModel = new ConteocdscusuarioSearch();
        $dataProvider = $searchModel->search($this->request->queryParams, $idconteofactura);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'modelfactura' => $modelfactura
        ]);
    }

    /**
     * Displays a single Conteocdscusuario model.
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
     * Creates a new Conteocdscusuario model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate($idconteofactura)
    {
        $model = new Conteocdscusuario();
        $model->idConteocdscdestinofactura = $idconteofactura;
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

                return $this->redirect(['index', 'idconteofactura' => $idconteofactura]);
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
     * Updates an existing Conteocdscusuario model.
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

                return $this->redirect(['index', 'idconteofactura' => $idconteofactura]);
            }
        }

        if (Yii::$app->request->isAjax){  
            return $this->renderAjax('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Conteocdscusuario model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $idconteofactura = $model->idConteocdscdestinofactura;

        if ($model->delete()){
            Yii::$app->session->setFlash( 'success', 'Registro Eliminado');
        }else{
            Yii::$app->session->setFlash( 'error', 'Error Eliminando Registro');
        }

        return $this->redirect(['index', 'idconteofactura' => $idconteofactura]);
    }

    /**
     * Finds the Conteocdscusuario model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Conteocdscusuario the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Conteocdscusuario::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
