<?php

namespace frontend\modules\distribucion\controllers;


use Yii;
use frontend\models\Pedidodetalle;
use frontend\models\Search\PedidodetalleSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

use frontend\models\Pedidoordendecompra;

/**
 * PedidodetalleController implements the CRUD actions for Pedidodetalle model.
 */
class PedidodetalleController extends Controller
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
     * Lists all Pedidodetalle models.
     *
     * @return string
     */
    public function actionIndex($idpedido, $idordencompra)
    {
        $modelpoc = Pedidoordendecompra::find()
            ->where([
                'idPedido' => $idpedido,
                'idOrdenCompra' => $idordencompra
            ])->one();

        $searchModel = new PedidodetalleSearch();
        $dataProvider = $searchModel->search($this->request->queryParams, false, $idpedido, $idordencompra);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'modelpoc' => $modelpoc
        ]);
    }

    public function actionIndexdetalledistribucion()
    {
        $searchModel = new PedidodetalleSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        $exportProvider = $searchModel->search(Yii::$app->request->queryParams, true, null, null);

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('detalledistribucion', [
                'searchModel'    => $searchModel,
                'dataProvider'   => $dataProvider,
                'exportProvider' => $exportProvider,
            ]);
        }

        return $this->render('detalledistribucion', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'exportProvider' => $exportProvider
        ]);
    }

    public function actionIndexConsolidado($idpedido = null, $idordencompra = null)
    {
        $searchModel   = new PedidodetalleSearch();
        $dataProvider  = $searchModel->searchConsolidado($this->request->queryParams, false, $idpedido, $idordencompra);
        $exportProvider = $searchModel->searchConsolidado(Yii::$app->request->queryParams, true,  $idpedido, $idordencompra);

        return $this->render('indexconsolidado', [
            'searchModel'    => $searchModel,
            'dataProvider'   => $dataProvider,
            'exportProvider' => $exportProvider,
            'idpedido'       => $idpedido ?? null,
            'idordencompra'  => $idordencompra ?? null,
        ]);
    }

    public function actionIndexMacro($idpedido = null)
    {
        $searchModel    = new PedidodetalleSearch();
        $dataProvider   = $searchModel->searchPorTienda($this->request->queryParams, false, $idpedido);
        $exportProvider = $searchModel->searchPorTienda(Yii::$app->request->queryParams, true,  $idpedido);

        return $this->render('indexmacro', [
            'searchModel'    => $searchModel,
            'dataProvider'   => $dataProvider,
            'exportProvider' => $exportProvider,
            'idpedido'       => $idpedido ?? null,
        ]);
    }

    /**
     * Displays a single Pedidodetalle model.
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
     * Creates a new Pedidodetalle model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate() {}

    /**
     * Updates an existing Pedidodetalle model.
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
     * Deletes an existing Pedidodetalle model.
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
     * Finds the Pedidodetalle model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Pedidodetalle the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Pedidodetalle::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
