<?php

namespace frontend\modules\grumascanmarcacion\controllers;

use frontend\models\Grumascanconteo;
use frontend\models\search\GrumascanconteoSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\db\Expression;
use Yii;

/**
 * GrumascanconteoController implements the CRUD actions for Grumascanconteo model.
 */
class GrumascanconteoController extends Controller
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
                        'anular' => ['POST'],
                        'desanular' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all Grumascanconteo models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new GrumascanconteoSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Grumascanconteo model.
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
     * Creates a new Grumascanconteo model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Grumascanconteo();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Grumascanconteo model.
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
     * Deletes an existing Grumascanconteo model.
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
     * Finds the Grumascanconteo model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Grumascanconteo the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Grumascanconteo::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionAnular($id)
    {
        $model = $this->findModel($id);

        // Solo se puede anular si está terminado (1)
        if ((int)$model->idestado !== 1) {
            Yii::$app->session->setFlash('warning', 'Solo se puede anular un conteo en estado TERMINADO (1).');
            return $this->redirect(['index']);
        }

        $model->idestado = 2; // Anulado

        // Auditoría (si existen estas columnas)
        if ($model->hasAttribute('updated_at')) {
            $model->updated_at = new Expression('GETDATE()'); // SQL Server
        }
        if ($model->hasAttribute('updated_by')) {
            $model->updated_by = Yii::$app->user->id ?? null;
        }

        if ($model->save()) {
            Yii::$app->session->setFlash('success', "Conteo #{$model->id} anulado correctamente (estado 2).");
        } else {
            $errors = $model->getFirstErrors();
            Yii::$app->session->setFlash('error', 'No se pudo anular: ' . json_encode($errors, JSON_UNESCAPED_UNICODE));
        }

        return $this->redirect(Yii::$app->request->referrer ?: ['index']);
    }

    public function actionDesanular($id)
    {
        $model = $this->findModel($id);

        // Solo se puede desanular si está anulado (2)
        if ((int)$model->idestado !== 2) {
            Yii::$app->session->setFlash('warning', 'Solo se puede desanular un conteo en estado ANULADO (2).');
            return $this->redirect(['index']);
        }

        $model->idestado = 1; // Volver a Terminado

        // Auditoría (si existen estas columnas)
        if ($model->hasAttribute('updated_at')) {
            $model->updated_at = new Expression('GETDATE()'); // SQL Server
        }
        if ($model->hasAttribute('updated_by')) {
            $model->updated_by = Yii::$app->user->id ?? null;
        }

        if ($model->save()) {
            Yii::$app->session->setFlash('success', "Conteo #{$model->id} desanulado correctamente (vuelve a estado 1).");
        } else {
            $errors = $model->getFirstErrors();
            Yii::$app->session->setFlash('error', 'No se pudo desanular: ' . json_encode($errors, JSON_UNESCAPED_UNICODE));
        }

        return $this->redirect(Yii::$app->request->referrer ?: ['index']);
    }
}
