<?php

namespace frontend\modules\distribucion\controllers;

use frontend\models\Pedidoordendecompraitem;
use frontend\models\search\PedidoordendecompraitemSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

use frontend\models\Pedidoordendecompra;
use frontend\models\Pedidodetalle;
use frontend\models\Logborradopedido;
use Yii;

/**
 * PedidoordendecompraitemController implements the CRUD actions for Pedidoordendecompraitem model.
 */
class PedidoordendecompraitemController extends Controller
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
     * Lists all Pedidoordendecompraitem models.
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

        $searchModel = new PedidoordendecompraitemSearch();
        $dataProvider = $searchModel->search($this->request->queryParams, $idpedido, $idordencompra);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'modelpoc' => $modelpoc
        ]);
    }

    /**
     * Displays a single Pedidoordendecompraitem model.
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
     * Creates a new Pedidoordendecompraitem model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Pedidoordendecompraitem();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Pedidoordendecompraitem model.
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
     * Deletes an existing Pedidoordendecompraitem model.
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

        // Validar que ningún SKU de este item haya sido contado
        $totalRecibidas = (int) Pedidodetalle::find()
            ->where([
                'idPedido'      => $idPedido,
                'idOrdenCompra' => $idOC,
                'idItem'        => $model->idItem,
                'idBodega'      => $model->idBodega,
            ])
            ->sum('unidadesRecibidas');

        if ($totalRecibidas > 0) {
            Yii::$app->session->setFlash(
                'warning',
                'No se puede eliminar el item porque ya tiene unidades contadas/recibidas.'
            );
            return $this->redirect(['/distribucion/pedidoordendecompraitem/index',
                'idpedido'      => $idPedido,
                'idordencompra' => $idOC,
            ]);
        }

        $tx = Yii::$app->db->beginTransaction();
        try {
            // Log antes de borrar
            $unidadesAntes = (int) $model->totalUnidades;
            Logborradopedido::registrarEliminarItem($model, $unidadesAntes);

            // Borrar SKUs del item en esta OC
            Pedidodetalle::deleteAll([
                'idPedido'      => $idPedido,
                'idOrdenCompra' => $idOC,
                'idItem'        => $model->idItem,
                'idBodega'      => $model->idBodega,
            ]);

            // Borrar el item
            $model->delete();

            // Recalcular pedidoordendecompra (nroItems y totalUnidades)
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
            Yii::$app->session->setFlash('success', 'Item eliminado del pedido correctamente.');
        } catch (\Throwable $e) {
            $tx->rollBack();
            Yii::$app->session->setFlash('error', 'No se pudo eliminar el item: ' . $e->getMessage());
        }

        return $this->redirect(['/distribucion/pedidoordendecompraitem/index',
            'idpedido'      => $idPedido,
            'idordencompra' => $idOC,
        ]);
    }

    /**
     * Finds the Pedidoordendecompraitem model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Pedidoordendecompraitem the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Pedidoordendecompraitem::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
