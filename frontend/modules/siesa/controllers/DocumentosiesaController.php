<?php

namespace frontend\modules\siesa\controllers;

use frontend\models\Documentosiesa;
use frontend\models\search\DocumentosiesaSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * DocumentosiesaController implements the CRUD actions for Documentosiesa model.
 */
class DocumentosiesaController extends Controller
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
     * Lists all Documentosiesa models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new DocumentosiesaSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionIndexdctointerno()
    {
        $searchModel = new DocumentosiesaSearch();
        $dataProvider = $searchModel->searchSIESA($this->request->queryParams, 'I');

        return $this->render('index_documento', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'action' => 'indexdctointerno',
            'origenconsulta' => 'I'
        ]);
    }

    public function actionIndexdctosiesa()
    {
        $searchModel = new DocumentosiesaSearch();
        $dataProvider = $searchModel->searchSIESA($this->request->queryParams, 'S');

        return $this->render('index_documento', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'action' => 'indexdctosiesa',
            'origenconsulta' => 'S'
        ]);
    }

    public function actionIndexdctosrepetidos()
    {
        $searchModel = new DocumentosiesaSearch(); // O el nombre del modelo que estás usando
        $dataProvider = $searchModel->searchAgrupados($this->request->queryParams); // Asegúrate de pasar los parámetros correctamente

        return $this->render('index_agrupados', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Documentosiesa model.
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
     * Creates a new Documentosiesa model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Documentosiesa();

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
     * Updates an existing Documentosiesa model.
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
     * Deletes an existing Documentosiesa model.
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
     * Finds the Documentosiesa model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Documentosiesa the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Documentosiesa::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
