<?php

namespace frontend\modules\siesa\controllers;

use Yii;
use common\models\search\OrdenesCompraWsSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

use common\models\OrdenesCompraWs;

/**
 * OrdenesCompraWsController implements the CRUD actions for OrdenesCompraWs model.
 */
class OrdenesCompraWsController extends Controller
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
     * Lists all OrdenesCompraWs models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new OrdenesCompraWsSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
		
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'params' => $this->request->queryParams,
        ]);
    }

    public function actionSincronizarerp($co = null, $tipodocumento = null, $consecutivo = null)
    {
        $respuesta = OrdenesCompraWs::sincronizarERP($co, $tipodocumento, $consecutivo);

        if ($respuesta){
            Yii::$app->session->setFlash('success', 'Sincronización ha finalizado correctamente.');
        }else{
            Yii::$app->session->setFlash('error', 'Sincronización ha fallado.');
        }
        return $this->redirect(['index']);
    }
}