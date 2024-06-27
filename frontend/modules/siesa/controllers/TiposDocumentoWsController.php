<?php

namespace frontend\modules\siesa\controllers;

use Yii;
use common\models\TiposDocumentoWs;
use common\models\search\TiposDocumentoWsSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

use frontend\models\Tipodocumento;
/**
 * TiposDocumentoWsController implements the CRUD actions for TiposDocumentoWs model.
 */
class TiposDocumentoWsController extends Controller
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
     * Lists all TiposDocumentoWs models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new TiposDocumentoWsSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
		
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new TiposDocumentoWs model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionSincronizarerp()
    {
        $respuesta = TiposDocumentoWs::sincronizarERP();

        if ($respuesta){
            Yii::$app->session->setFlash('success', 'Sincronización ha finalizado correctamente.');
        }else{
            Yii::$app->session->setFlash('error', 'Sincronización ha fallado.');
        }
        return $this->redirect(['index']);
    }

}
