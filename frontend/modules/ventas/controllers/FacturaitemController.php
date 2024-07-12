<?php

namespace frontend\modules\ventas\controllers;

use Yii;
use frontend\modules\ventas\models\Facturaitem;
use frontend\modules\ventas\models\search\FacturaitemSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

use frontend\modules\ventas\models\Factura;

/**
 * FacturaitemController implements the CRUD actions for Facturaitem model.
 */
class FacturaitemController extends Controller
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
     * Lists all Facturaitem models.
     *
     * @return string
     */
    public function actionIndex($idfactura)
    {
        $modelfactura = Factura::findOne(['id' => $idfactura]);

        $searchModel = new FacturaitemSearch();
        $dataProvider = $searchModel->search($this->request->queryParams, $idfactura);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'modelfactura' => $modelfactura
        ]);
    }

    /**
     * Displays a single Facturaitem model.
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
     * Creates a new Facturaitem model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Facturaitem();

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
     * Updates an existing Facturaitem model.
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
     * Deletes an existing Facturaitem model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $idfactura = $model->idFactura;
        
        $model->delete();

        return $this->redirect(['index', 'idfactura' => $idfactura]);
    }

    public function actionSincronizarbodega ($idfactura){
        Facturaitem::buscarBodega ($idfactura);

        Yii::$app->session->setFlash( 'success', 'Proceso Finalizó Satisfactoriamente');

        return $this->redirect(['index', 'idfactura' => $idfactura]);
    }

    /**
     * Finds the Facturaitem model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Facturaitem the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Facturaitem::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
