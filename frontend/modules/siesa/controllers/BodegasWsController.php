<?php

namespace frontend\modules\siesa\controllers;

use Yii;
use common\models\BodegasWs;
use common\models\search\BodegasWsSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * BodegasWsController implements the CRUD actions for BodegasWs model.
 */
class BodegasWsController extends Controller
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
     * Lists all BodegasWs models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new BodegasWsSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
		
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new BodegasWs model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionSincronizarerp()
    {
        $respuesta = BodegasWs::sincronizarERP();

        if ($respuesta){
            Yii::$app->session->setFlash('success', 'Sincronización ha finalizado correctamente.');
        }else{
            Yii::$app->session->setFlash('error', 'Sincronización ha fallado.');
        }
        return $this->redirect(['index']);
    }
}
