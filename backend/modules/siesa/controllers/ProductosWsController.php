<?php

namespace backend\modules\siesa\controllers;

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
        // Normaliza
        $item = is_string($item) ? trim($item) : $item;

        // Si no llega item, no sincronices (evita falso "OK")
        if ($item === null || $item === '') {
            Yii::$app->session->setFlash('error', 'Debe indicar un Item para sincronizar. El parámetro "item" llegó vacío.');
            return $this->redirect(['index']);
        }

        $resp = ProductosWs::sincronizarERP($item);

        if (!empty($resp['ok'])) {
            $msg = $resp['message'] ?: 'Sincronización ha finalizado correctamente.';
            Yii::$app->session->setFlash('success', $msg);
        } else {
            Yii::$app->session->setFlash('error', $resp['message'] ?? 'Sincronización ha fallado.');
        }

        return $this->redirect(['index']);
    }
}
