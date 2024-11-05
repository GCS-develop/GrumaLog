<?php

namespace frontend\modules\despacho\controllers;

use frontend\models\Planillaembarque;
use frontend\models\Planillaembarquetraspaso;
use frontend\models\search\PlanillaembarquetraspasoSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * PlanillaembarquetraspasoController implements the CRUD actions for Planillaembarquetraspaso model.
 */
class PlanillaembarquetraspasoController extends Controller
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
     * Lists all Planillaembarquetraspaso models.
     *
     * @return string
     */
    public function actionIndex($id = null)
    {
        $searchModel = new PlanillaembarquetraspasoSearch();
        $dataProvider = $searchModel->search($this->request->queryParams, $id);

        $programa = 'index';
        $model = '';

        

        if ($id != null){
            $programa = 'index_planilla';
            // var_dump(Planillaembarque::findOne($id));die('hola');
            $model = Planillaembarque::findOne($id);

        }

        return $this->render($programa, [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'model' => $model,

        ]);
    }

    /**
     * Displays a single Planillaembarquetraspaso model.
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
     * Creates a new Planillaembarquetraspaso model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Planillaembarquetraspaso();

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
     * Updates an existing Planillaembarquetraspaso model.
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
     * Deletes an existing Planillaembarquetraspaso model.
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
     * Finds the Planillaembarquetraspaso model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Planillaembarquetraspaso the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Planillaembarquetraspaso::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('La página solicitada no existe.');
    }
}
