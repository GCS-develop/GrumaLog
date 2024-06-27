<?php

namespace frontend\modules\agenda\controllers;

use Yii;
use frontend\models\Agendapresupuestocategoria;
use frontend\models\search\AgendapresupuestocategoriaSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

use frontend\models\Agendapresupuesto;
use frontend\models\search\AgendapresupuestoSearch;

/**
 * AgendapresupuestocategoriaController implements the CRUD actions for Agendapresupuestocategoria model.
 */
class AgendapresupuestocategoriaController extends Controller
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

    public function actionIndexperiodo()
    {
        $searchModel = new AgendapresupuestoSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index_periodo', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Lists all Agendapresupuestocategoria models.
     *
     * @return string
     */
    public function actionIndex($idagendapresupuesto, $menu = null)
    {
        $model = Agendapresupuesto::findOne(['id' => $idagendapresupuesto]);

        $searchModel = new AgendapresupuestocategoriaSearch();
        $dataProvider = $searchModel->search($this->request->queryParams, $idagendapresupuesto);

        $dataProviderDia = $searchModel->searchPivotCategoriaDia($this->request->queryParams, $idagendapresupuesto);

        // Agrupar los datos por bodega
        $dataByCrossDocking = [];
        foreach ($dataProviderDia->getModels() as $modelagenda) {
            $crossdocking = $modelagenda['crossDocking'];
            if (!isset($dataByCrossDocking[$crossdocking])) {
                $dataByCrossDocking[$crossdocking] = [];
            }
            $dataByCrossDocking[$crossdocking][] = $modelagenda;
        }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'dataProviderDia' => $dataProviderDia,
            'dataByCrossDocking' => $dataByCrossDocking,
            'model' => $model,
            'menu' => $menu
        ]);
    }

    /**
     * Displays a single Agendapresupuestocategoria model.
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
     * Creates a new Agendapresupuestocategoria model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Agendapresupuestocategoria();

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
     * Updates an existing Agendapresupuestocategoria model.
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
     * Deletes an existing Agendapresupuestocategoria model.
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
     * Finds the Agendapresupuestocategoria model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Agendapresupuestocategoria the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Agendapresupuestocategoria::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
