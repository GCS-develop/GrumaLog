<?php

namespace frontend\modules\siesa\controllers;

use Yii;
use common\models\ProveedoresWs;
use common\models\search\ProveedoresWsSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * ProveedoresWsController implements the CRUD actions for ProveedoresWs model.
 */
class ProveedoresWsController extends Controller
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
     * Lists all ProveedoresWs models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new ProveedoresWsSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
		
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'params' => $this->request->queryParams,
        ]);
    }

    /**
     * Creates a new ProveedoresWs model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionSincronizarerp($nit = null)
    {
        $respuesta = ProveedoresWs::sincronizarERP($nit);

        if ($respuesta){
            Yii::$app->session->setFlash('success', 'Sincronización ha finalizado correctamente.');
        }else{
            Yii::$app->session->setFlash('error', 'Sincronización ha fallado.');
        }
        return $this->redirect(['index']);
    }


}
