<?php

namespace frontend\modules\contabilidad\controllers;

use frontend\models\MovimientoGasto;
use frontend\models\search\MovimentogastoSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * MovimientogastoController implements the CRUD actions for MovimientoGasto model.
 */
class MovimientogastoController extends Controller
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
     * Lists all MovimientoGasto models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new MovimentogastoSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single MovimientoGasto model.
     * @param int $F_CIA F Cia
     * @param string $F350_CONSEC_DOCTO F350 Consec Docto
     * @param int $F350_ID_CO F350 Id Co
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($F_CIA, $F350_CONSEC_DOCTO, $F350_ID_CO)
    {
        return $this->render('view', [
            'model' => $this->findModel($F_CIA, $F350_CONSEC_DOCTO, $F350_ID_CO),
        ]);
    }

    /**
     * Creates a new MovimientoGasto model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
   public function actionCreate($F350_ID_CO = null, $F350_ID_TIPO_DOCTO = null, $F350_CONSEC_DOCTO = null)
{
    $model = new MovimientoGasto();

    // Prellenar los datos si vienen por GET
    $model->F350_ID_CO = $F350_ID_CO;
    $model->F350_ID_TIPO_DOCTO = $F350_ID_TIPO_DOCTO;
    $model->F350_CONSEC_DOCTO = $F350_CONSEC_DOCTO;

    if ($this->request->isPost) {
        if ($model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'F_CIA' => $model->F_CIA, 'F350_CONSEC_DOCTO' => $model->F350_CONSEC_DOCTO, 'F350_ID_CO' => $model->F350_ID_CO]);
        }
    } else {
        $model->loadDefaultValues();
        
    }

    return $this->render('create', [
        'model' => $model,
    ]);
}


    /**
     * Updates an existing MovimientoGasto model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $F_CIA F Cia
     * @param string $F350_CONSEC_DOCTO F350 Consec Docto
     * @param int $F350_ID_CO F350 Id Co
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($F_CIA, $F350_CONSEC_DOCTO, $F350_ID_CO)
    {
        $model = $this->findModel($F_CIA, $F350_CONSEC_DOCTO, $F350_ID_CO);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'F_CIA' => $model->F_CIA, 'F350_CONSEC_DOCTO' => $model->F350_CONSEC_DOCTO, 'F350_ID_CO' => $model->F350_ID_CO]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing MovimientoGasto model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $F_CIA F Cia
     * @param string $F350_CONSEC_DOCTO F350 Consec Docto
     * @param int $F350_ID_CO F350 Id Co
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($F_CIA, $F350_CONSEC_DOCTO, $F350_ID_CO)
    {
        $this->findModel($F_CIA, $F350_CONSEC_DOCTO, $F350_ID_CO)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the MovimientoGasto model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $F_CIA F Cia
     * @param string $F350_CONSEC_DOCTO F350 Consec Docto
     * @param int $F350_ID_CO F350 Id Co
     * @return MovimientoGasto the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($F_CIA, $F350_CONSEC_DOCTO, $F350_ID_CO)
    {
        if (($model = MovimientoGasto::findOne(['F_CIA' => $F_CIA, 'F350_CONSEC_DOCTO' => $F350_CONSEC_DOCTO, 'F350_ID_CO' => $F350_ID_CO])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
