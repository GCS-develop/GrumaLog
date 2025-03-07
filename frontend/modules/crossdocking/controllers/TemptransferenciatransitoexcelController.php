<?php

namespace frontend\modules\crossdocking\controllers;

use Yii;
use frontend\models\Conteocdscdestino;
use frontend\models\Temptransferenciaerp;
use frontend\models\Temptransferenciatransitoexcel;
use frontend\models\search\TemptransferenciatransitoexcelSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * TemptransferenciatransitoexcelController implements the CRUD actions for Temptransferenciatransitoexcel model.
 */
class TemptransferenciatransitoexcelController extends Controller
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
     * Lists all Temptransferenciatransitoexcel models.
     *
     * @return string
     */
    public function actionIndex($idconteofactura, $idconteodestino)
    {
        $model = Conteocdscdestino::findOne(['id' => $idconteodestino]);

        if ($model->idErpTraspaso != null){
            Yii::$app->session->setFlash('error', 'Transferencia/Traspaso Ya Fueron Generados. ');
            return $this->redirect(['/crossdocking/conteocdscdestino/indextraspaso', 'idconteofactura' => $idconteofactura]);
        }

        $modeltransferencia = Temptransferenciaerp::find()
                                ->where([
                                    'idConteoFactura' => $idconteofactura,
                                    'idConteoDestino' => $idconteodestino
                                ])->one();

        $idtransferenciaerp = $modeltransferencia->id;

        $searchModel = new TemptransferenciatransitoexcelSearch();
        $dataProvider = $searchModel->search($this->request->queryParams, $idtransferenciaerp);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'modeldestino' => $model,
            'idtransferenciaerp' => $idtransferenciaerp
        ]);
    }

    public function actionEjecutartransferencia ($id, $origen, $idconteofactura, $idconteodestino = null){

        $idtransferenciaerp = $id;
        $ok = Temptransferenciaerp::generarTransferenciaTemporal ($idtransferenciaerp, $idconteofactura, $idconteodestino);

        if ($ok == 0) {
            Yii::$app->session->setFlash('success', 'Traspaso se Ha Generado Con ÉXito' );
        } else {
            Yii::$app->session->setFlash('error', 'Error Generando Traspaso. ');
        }

        return $this->redirect(['/crossdocking/conteocdscdestino/indextraspaso', 'idconteofactura' => $idconteofactura]);
    }

    public function actionEjecutartransferenciatodos ($idconteofactura){

        $modeltransferencia = Temptransferenciaerp::find()
                    ->alias('t')
                    ->join('INNER JOIN', 'conteocdscdestino d', 't.idConteoDestino = d.id')
                    ->where(['t.idConteoFactura' => $idconteofactura, 'd.idErpTraspaso' => null])
                    ->all();

        $error = 0;
        foreach ($modeltransferencia as $transferencia){

            $idtransferenciaerp = $transferencia->id;
            $idconteodestino = $transferencia->idConteoDestino;

            $ok = Temptransferenciaerp::generarTransferenciaTemporal ($idtransferenciaerp, $idconteofactura, $idconteodestino);

            if ($ok != 0) {
                Yii::$app->session->setFlash('success', 'Traspaso se Ha Generado Con ÉXito' );
            } else {
                Yii::$app->session->setFlash('error', 'Error Generando Traspaso. ');
            }
        }

        if ($error == 0) {
            Yii::$app->session->setFlash('success', 'Transferencia / Traspasos se Han Generado Con ÉXito' );
        } else {
            Yii::$app->session->setFlash('error', 'Error Generando Transferencia / Traspaso. ');
        }        

        return $this->redirect(['/crossdocking/conteocdscdestino/indextraspaso', 'idconteofactura' => $idconteofactura]);
    }

    /**
     * Displays a single Temptransferenciatransitoexcel model.
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
     * Creates a new Temptransferenciatransitoexcel model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Temptransferenciatransitoexcel();

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
     * Updates an existing Temptransferenciatransitoexcel model.
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
     * Deletes an existing Temptransferenciatransitoexcel model.
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
     * Finds the Temptransferenciatransitoexcel model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Temptransferenciatransitoexcel the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Temptransferenciatransitoexcel::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
