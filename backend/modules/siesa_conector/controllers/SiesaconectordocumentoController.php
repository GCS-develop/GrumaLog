<?php

namespace backend\modules\siesa_conector\controllers;

use Yii;
use frontend\models\SiesaConectorDocumento;
use frontend\models\search\SiesaconectordocumentoSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * SiesaconectordocumentoController implements the CRUD actions for SiesaConectorDocumento model.
 */
class SiesaconectordocumentoController extends Controller
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
     * Lists all SiesaConectorDocumento models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new SiesaconectordocumentoSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single SiesaConectorDocumento model.
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
     * Creates a new SiesaConectorDocumento model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new SiesaConectorDocumento();

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
     * Updates an existing SiesaConectorDocumento model.
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
     * Deletes an existing SiesaConectorDocumento model.
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
     * Finds the SiesaConectorDocumento model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return SiesaConectorDocumento the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = SiesaConectorDocumento::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    // public function actionViewdetalle($id)
    // {
    //     $documento = SiesaConectorDocumento::find()
    //         ->with([
    //             'camposDocumento',
    //             'camposMovimiento',
    //             'valoresDocumento',
    //             'movimientos.valores'
    //         ])
    //         ->where(['id' => $id])
    //         ->one();

    //     if (!$documento) {
    //         throw new NotFoundHttpException("No se encontró el documento con ID $id.");
    //     }

    //     return $this->render('viewdetalle', [
    //         'documento' => $documento,
    //     ]);
    // }

    public function actionViewdetalle($id)
    {
        $documento = SiesaConectorDocumento::find()
            ->with([
                'camposDocumento',
                'camposMovimiento',
                'valoresDocumento',
                'movimientos.valores'
            ])
            ->where(['id' => $id])
            ->one();

        if (!$documento) {
            throw new NotFoundHttpException("No se encontró el documento con ID $id.");
        }

        // Agrupar movimientos por el valor del campo f470_id_item
        $movimientosAgrupados = [];

        foreach ($documento->movimientos as $movimiento) {
            $itemId = null;
            foreach ($movimiento->valores as $valor) {
                if ($valor->campo->nombre_campo === 'f470_id_item') {
                    $itemId = $valor->valor;
                    break;
                }
            }
            if (!$itemId) {
                $itemId = 'sin_item';
            }

            $movimientosAgrupados[$itemId][] = $movimiento;
        }

        return $this->render('viewdetalle', [
            'documento' => $documento,
            'movimientosAgrupados' => $movimientosAgrupados,
        ]);
    }

    public function actionBuscarAen($id)
    {
        $model = SiesaConectorDocumento::findOne($id);

        if (!$model) {
            Yii::$app->session->setFlash('error', 'No se encontró el conector.');
            return $this->redirect(['index']);
        }

        $aen = $model->vincularAenConDocumentosiesa();

        if (!$aen) {
            $msg = $model->hasErrors()
                ? reset($model->firstErrors)
                : 'No se pudo vincular el AEN.';
            Yii::$app->session->setFlash('error', $msg);
            return $this->redirect(['index']);
        }

        Yii::$app->session->setFlash(
            'success',
            "AEN encontrado y grabado: {$aen['tipo_aen']} {$aen['consec_aen']} (CO {$aen['co_aen']})"
        );

        return $this->redirect(['index']);
    }
}
