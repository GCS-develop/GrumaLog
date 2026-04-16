<?php

namespace frontend\modules\distribucion\controllers;


use Yii;
use frontend\models\Pedidodetalle;
use frontend\models\Search\PedidodetalleSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

use frontend\models\Pedidoordendecompra;
use frontend\models\Logborradopedido;

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
        $model    = $this->findModel($id);
        $idPedido = $model->idPedido;
        $idOC     = $model->idOrdenCompra;

        // Validar que el SKU no haya sido contado
        if ((int) $model->unidadesRecibidas > 0) {
            Yii::$app->session->setFlash(
                'warning',
                'No se puede eliminar el SKU porque ya tiene unidades contadas/recibidas.'
            );
            return $this->redirect(['/distribucion/pedidodetalle/index',
                'idpedido'      => $idPedido,
                'idordencompra' => $idOC,
            ]);
        }

        $idItem   = $model->idItem;
        $idBodega = $model->idBodega;

        $tx = Yii::$app->db->beginTransaction();
        try {
            $unidadesAntes = (int) $model->unidades;
            Logborradopedido::registrarEliminarSku($model, $unidadesAntes);

            $model->delete();

            // Recalcular pedidoordendecompraitem (totalUnidades del item)
            Yii::$app->db->createCommand("
                UPDATE pedidoordendecompraitem SET
                    totalUnidades = ISNULL((
                        SELECT SUM(unidades) FROM pedidodetalle
                        WHERE idPedido = :p1 AND idOrdenCompra = :p2
                          AND idItem = :p3 AND idBodega = :p4
                    ), 0)
                WHERE idPedido = :p5 AND idOrdenCompra = :p6
                  AND idItem = :p7 AND idBodega = :p8
            ", [
                ':p1' => $idPedido, ':p2' => $idOC, ':p3' => $idItem, ':p4' => $idBodega,
                ':p5' => $idPedido, ':p6' => $idOC, ':p7' => $idItem, ':p8' => $idBodega,
            ])->execute();

            // Recalcular pedidoordendecompra (totalUnidades y nroItems de la OC)
            Yii::$app->db->createCommand("
                UPDATE pedidoordendecompra SET
                    nroItems = (
                        SELECT COUNT(*) FROM pedidoordendecompraitem
                        WHERE idPedido = :p1 AND idOrdenCompra = :p2
                    ),
                    totalUnidades = ISNULL((
                        SELECT SUM(totalUnidades) FROM pedidoordendecompraitem
                        WHERE idPedido = :p3 AND idOrdenCompra = :p4
                    ), 0)
                WHERE idPedido = :p5 AND idOrdenCompra = :p6
            ", [':p1' => $idPedido, ':p2' => $idOC, ':p3' => $idPedido, ':p4' => $idOC, ':p5' => $idPedido, ':p6' => $idOC])->execute();

            // Recalcular pedido (totalUnidades)
            Yii::$app->db->createCommand("
                UPDATE pedido SET
                    totalUnidades = ISNULL((
                        SELECT SUM(totalUnidades) FROM pedidoordendecompra WHERE idPedido = :p1
                    ), 0)
                WHERE id = :p2
            ", [':p1' => $idPedido, ':p2' => $idPedido])->execute();

            $tx->commit();
            Yii::$app->session->setFlash('success', 'SKU eliminado del pedido correctamente.');
        } catch (\Throwable $e) {
            $tx->rollBack();
            Yii::$app->session->setFlash('error', 'No se pudo eliminar el SKU: ' . $e->getMessage());
        }

        return $this->redirect(['/distribucion/pedidodetalle/index',
            'idpedido'      => $idPedido,
            'idordencompra' => $idOC,
        ]);
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
