<?php

namespace frontend\modules\siesa\controllers;

use Yii;
use common\models\search\ProductosWsSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

use common\models\ProductosWs;

/**
 * ProductosWsController implements the CRUD actions for ProductosWs model.
 */
class ProductosWsController extends Controller
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
     * Lists all ProductosWs models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new ProductosWsSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'params' => $this->request->queryParams,
        ]);
    }

    public function actionSincronizarerp($item = null)
    {
        $respuesta = ProductosWs::sincronizarERP($item);

        if ($respuesta){
            Yii::$app->session->setFlash('success', 'Sincronización ha finalizado correctamente.');
        }else{
            Yii::$app->session->setFlash('error', 'Sincronización ha fallado.');
        }
        return $this->redirect(['index']);
    }
}