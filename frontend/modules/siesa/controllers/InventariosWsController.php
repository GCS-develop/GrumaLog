<?php

namespace frontend\modules\siesa\controllers;

use common\models\search\InventariosWsSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * InventariosWsController implements the CRUD actions for InventariosWs model.
 */
class InventariosWsController extends Controller
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
     * Lists all InventariosWs models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new InventariosWsSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
		
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
}