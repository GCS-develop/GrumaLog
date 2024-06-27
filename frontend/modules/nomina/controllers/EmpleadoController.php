<?php

namespace frontend\modules\nomina\controllers;

use frontend\models\Empleado;
use frontend\models\search\EmpleadoSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * EmpleadoController implements the CRUD actions for Empleado model.
 */
class EmpleadoController extends Controller
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
     * Lists all Empleado models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new EmpleadoSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Empleado model.
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
     * Creates a new Empleado model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Empleado();

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
     * Updates an existing Empleado model.
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
     * Deletes an existing Empleado model.
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
     * Finds the Empleado model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Empleado the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Empleado::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionObtenerDatosUsuario($idEmpleado)
    {
        
        // Buscar la orden de compra en la base de datos
        $ordenCompra = Ordendecompra::findOne(['idCO' => $idCentroOperacion,
                                                'idTipoDocumento' => $idTipoDocumento,
                                                'consecutivo' => $numeroOrdenCompra
                                            ]);

        if ($ordenCompra == null) {
            $model = new Ordendecompra();
            $model->idCO = $idCentroOperacion;
            $model->idTipoDocumento = $idTipoDocumento;

            $idordencompra = OrdenesCompraWs::sincronizarERPAgenda ($model->cO->codigo, $model->tipoDocumento->codigo, $numeroOrdenCompra);
        }else{
            $idordencompra = $ordenCompra->id;
        }

        $ordenCompra = Ordendecompra::findOne(['id' => $idordencompra]);
        
        //$ordenCompra = OrdenesCompraWs::sincronizarERPAgenda ('002', '2CA', 64035);
        //var_dump($idordencompra); die("HOLA 6");

        if ($ordenCompra !== null) {
            // La orden de compra fue encontrada, devolver los datos en formato JSON
            Yii::$app->response->format = Response::FORMAT_JSON;
            
            return [
                'id' => $ordenCompra->id,
                'fechaOrden' => $ordenCompra->fecha,
                'totalCantidadPedida' => $ordenCompra->totalCantidadPedida,
                'totalCantidadEntrada' => $ordenCompra->totalCantidadEntrada,
                'totalCantidadPendiente' => $ordenCompra->totalCantidadPendiente,
                'nitProveedor' => $ordenCompra->proveedor->nit,
                'razonSocial' => $ordenCompra->proveedor->razonSocial,
                'dataProveedor' => $ordenCompra->proveedor->nit . '- ' . $ordenCompra->proveedor->razonSocial,
                'encontrada' => true,
            ];
        } else {

            // La orden de compra no fue encontrada, devolver un mensaje de error en formato JSON
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'error' => 'La orden de compra no fue encontrada.',
                'encontrada' => false,
                'fechaOrden' => '',
                'id' => ''
            ];
        }
    }

}
