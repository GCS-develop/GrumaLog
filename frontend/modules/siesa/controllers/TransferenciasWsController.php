<?php

namespace frontend\modules\siesa\controllers;

use common\models\search\TransferenciasWsSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * TransferenciasWsController implements the CRUD actions for TransferenciasWs model.
 */
class TransferenciasWsController extends Controller
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
     * Lists all TransferenciasWs models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new TransferenciasWsSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
		
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
}