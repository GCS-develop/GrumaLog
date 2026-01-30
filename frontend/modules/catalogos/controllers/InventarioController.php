<?php

namespace frontend\modules\catalogos\controllers;

use frontend\models\Inventario;
use frontend\models\search\InventarioSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use Yii;
use yii\web\BadRequestHttpException;
use yii\filters\VerbFilter;
use yii\helpers\Html;
use common\components\SiesaSyncService;

/**
 * InventarioController implements the CRUD actions for Inventario model.
 */
class InventarioController extends Controller
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
                        'sincronizar-inventario' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all Inventario models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new InventarioSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Inventario model.
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
     * Creates a new Inventario model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Inventario();

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
     * Updates an existing Inventario model.
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
     * Deletes an existing Inventario model.
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
     * Finds the Inventario model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Inventario the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Inventario::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionSincronizarInventario()
    {
        $codigoBodega = Yii::$app->request->post('InventarioSearch')['codigoBodega'] ?? null;
        if (empty($codigoBodega)) {
            throw new \yii\web\BadRequestHttpException('Debe seleccionar una bodega para sincronizar.');
        }

        $syncItemsActivo = (int)\frontend\models\Parametroscontrol::getValorparametro('SYNC_ITEMS_SIESA') === 1;

        $svc = new SiesaSyncService('E:\laragon\bin\php\php-8.1.10-win32-vs16-x64\php.exe', 'C:\Apache24\htdocs\conektasiesav2');


        $r = $svc->syncInventarioBodega((int)$codigoBodega, $syncItemsActivo);

        return $this->renderContent(
            '<pre style="white-space:pre-wrap;">' . \yii\helpers\Html::encode($r['log'] ?? ($r['error'] ?? '')) . '</pre>'
        );
    }
}
