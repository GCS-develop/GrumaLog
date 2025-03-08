<?php

namespace frontend\modules\catalogos\controllers;

use Yii;

use frontend\models\Color;
use frontend\models\search\ColorSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\widgets\ActiveForm;
/**
 * ColorController implements the CRUD actions for Color model.
 */
class ColorController extends Controller
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
     * Lists all Color models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new ColorSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Color model.
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
     * Creates a new Color model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Color();

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                $id = null;
                if ($model->validate()) {
                    $id = $model->save();
                }

                if ($id != null) {
                    Yii::$app->session->setFlash('success', 'Registro Actualizado');
                } else {
                    Yii::$app->session->setFlash('error', 'Error Actualizando Registro');
                }

                return $this->redirect(['index']);
            }
        } else {
            $model->loadDefaultValues();
        }

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Color model.
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
                if ($model->validate()) {
                    $id = $model->save();
                }

                if ($id != null) {
                    Yii::$app->session->setFlash('success', 'Registro Actualizado');
                } else {
                    Yii::$app->session->setFlash('error', 'Error Actualizando Registro');
                }

                return $this->redirect(['index']);
            }
        }

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Color model.
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

                Yii::$app->session->setFlash('success', 'Registro Eliminado');
            } catch (\yii\db\IntegrityException $e) {
                Yii::$app->session->setFlash('error', 'No se puede eliminar este registro.');
            }
        } else {
            Yii::$app->session->setFlash('error', 'Registro no encontrado.');
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the Color model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Color the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Color::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
