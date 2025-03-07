<?php

namespace frontend\modules\crossdocking\controllers;

use frontend\models\Conteocdscdestinodetalle;
use frontend\models\search\ConteocdscdestinodetalleSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

use frontend\models\Conteocdscdestino;

/**
 * ConteocdscdestinodetalleController implements the CRUD actions for Conteocdscdestinodetalle model.
 */
class ConteocdscdestinodetalleController extends Controller
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
     * Lists all Conteocdscdestinodetalle models.
     *
     * @return string
     */
    public function actionIndex($idconteodestino, $origen)
    {

        $modeldestino = Conteocdscdestino::findOne(['id' => $idconteodestino]);

        $searchModel = new ConteocdscdestinodetalleSearch();
        $dataProvider = $searchModel->search($this->request->queryParams, $idconteodestino);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'modeldestino' => $modeldestino,
            'origen' => $origen
        ]);
    }

    public function actionViewtraspaso($idconteodestino)
    {
        $modeldestino = Conteocdscdestino::findOne(['id' => $idconteodestino]);

        $searchModel = new ConteocdscdestinodetalleSearch();
        $dataProvider = $searchModel->search($this->request->queryParams, $idconteodestino);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'modeldestino' => $modeldestino,
        ]);
    }

    /**
     * Displays a single Conteocdscdestinodetalle model.
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
     * Creates a new Conteocdscdestinodetalle model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Conteocdscdestinodetalle();

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
     * Updates an existing Conteocdscdestinodetalle model.
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
     * Deletes an existing Conteocdscdestinodetalle model.
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
     * Finds the Conteocdscdestinodetalle model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Conteocdscdestinodetalle the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Conteocdscdestinodetalle::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
