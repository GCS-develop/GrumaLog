<?php

namespace frontend\modules\ventas\controllers;

use Yii;
use frontend\modules\ventas\models\Facturadetalle;
use frontend\modules\ventas\models\search\FacturadetalleSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

use frontend\modules\ventas\models\Factura;
use frontend\modules\ventas\models\Facturaitem;

/**
 * FacturadetalleController implements the CRUD actions for Facturadetalle model.
 */
class FacturadetalleController extends Controller
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
     * Lists all Facturadetalle models.
     *
     * @return string
     */
    public function actionIndex($idfacturaitem = null)
    {
        $modelfactura = new Factura();
        $modelitem = new Facturaitem();

        if ($idfacturaitem){
            $modelitem = Facturaitem::findOne(['id' => $idfacturaitem]);

            $modelfactura = Factura::findOne(['id' => $modelitem->idFactura]);
        }

        $searchModel = new FacturadetalleSearch();
        $dataProvider = $searchModel->search($this->request->queryParams, $modelitem);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'modelfactura' => $modelfactura,
            //'modelitem' => $modelitem
        ]);
    }

    /**
     * Displays a single Facturadetalle model.
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
     * Creates a new Facturadetalle model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Facturadetalle();

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
     * Updates an existing Facturadetalle model.
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

    public function actionUpdateprecio($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post())) {

            $preciounitario = $model->precioUnitario;
            if ($preciounitario > 0){
                Facturadetalle::updateAll(
                    [
                        'precioUnitario' => $preciounitario,
                        'error' => 1
                    ], 
                    [
                        'item' => $model->item, 
                        'codigoBarra' => $model->codigoBarra, 
                        'color' => $model->color, 
                        'talla' => $model->talla
                    ]
                );
            }

            return $this->redirect(['index', 'idfactura' => $model->idFactura]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Facturadetalle model.
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
     * Finds the Facturadetalle model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Facturadetalle the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Facturadetalle::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
