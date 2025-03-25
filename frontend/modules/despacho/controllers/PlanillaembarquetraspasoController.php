<?php

namespace frontend\modules\despacho\controllers;

use frontend\models\Planillaembarque;
use frontend\models\Planillaembarquetraspaso;
use frontend\models\search\PlanillaembarquetraspasoSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use Yii;
use yii\db\Expression;


/**
 * PlanillaembarquetraspasoController implements the CRUD actions for Planillaembarquetraspaso model.
 */
class PlanillaembarquetraspasoController extends Controller
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
     * Lists all Planillaembarquetraspaso models.
     *
     * @return string
     */
    public function actionIndex($id = null)
    {
        $searchModel = new PlanillaembarquetraspasoSearch();
        $dataProvider = $searchModel->search($this->request->queryParams, $id);

        $programa = 'index';
        $model = '';



        if ($id != null) {
            $programa = 'index_planilla';
            $model = Planillaembarque::findOne($id);

        }


        return $this->render($programa, [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'model' => $model,

        ]);
    }

    /**
     * Displays a single Planillaembarquetraspaso model.
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
     * Creates a new Planillaembarquetraspaso model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Planillaembarquetraspaso();

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
     * Updates an existing Planillaembarquetraspaso model.
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
     * Deletes an existing Planillaembarquetraspaso model.
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
     * Finds the Planillaembarquetraspaso model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Planillaembarquetraspaso the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Planillaembarquetraspaso::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('La página solicitada no existe.');
    }

    // public function actionAnular($id)
    // {
    //     $model = $this->findModel($id);

    //     if ($model->idEstado === 3) {

    //         Yii::$app->session->setFlash('danger', 'No puedes anular por que ya esta recibido!');
    //         return $this->redirect(Yii::$app->request->referrer ?: ['index']);
    //     }

    //     if ($this->request->isPost) {

    //         $model->idEstado = 5;

    //         // var_dump($model);die();

    //         if ($model->save()) {

    //             Yii::$app->session->setFlash(
    //                 'success',
    //                 'PlanillaEmbarqueTraspaso anulado con éxito! ( Planilla: '
    //                 . $model->planillaEmbarque->id . ', Traspaso: '
    //                 . $model->tipoDocumento . $model->consecutivoDocumento . ', con unidades: '
    //                 . $model->unidades . ' para la tienda ' . $model->codAlmacenDestino . $model->almacenDestino . ' )'
    //             );

    //             return $this->redirect(Yii::$app->request->referrer ?: ['index']);

    //         } else {

    //             // Yii::$app->session->setFlash('error', 'Ups!, ocurrio un problema con : ( Planilla: '
    //             //     . $model->planillaEmbarque->id . ', Traspaso: '
    //             //     . $model->tipoDocumento . $model->consecutivoDocumento . ', con unidades: '
    //             //     . $model->unidades . ' para la tienda ' . $model->codAlmacenDestino . $model->almacenDestino . ' )');

    //             // return $this->redirect(Yii::$app->request->referrer ?: ['index']);

    //             // Obtener los errores del modelo
    //             $errors = $model->getErrors();
    //             $errorMessages = '';
    //             foreach ($errors as $attribute => $errorList) {
    //                 $errorMessages .= ucfirst($attribute) . ': ' . implode(', ', $errorList) . '. ';
    //             }

    //             Yii::$app->session->setFlash(
    //                 'error',
    //                 'Ups! Ocurrió un problema: ' . $errorMessages
    //             );
    //         }
    //     }
    // }
    public function actionAnular($id)
    {
        $model = $this->findModel($id);

        if ($model->idEstado === 3) {
            Yii::$app->session->setFlash('danger', 'No puedes anular porque ya está recibido!');
            return $this->redirect(Yii::$app->request->referrer ?: ['index']);
        }

        if ($this->request->isPost) {
            // Obtener el usuario actual
            $usuarioActual = Yii::$app->user->id;
            $fechaActual = date('Y-m-d H:i:s'); // Formato correcto para SQL Server


            // Actualiza solo los campos necesarios sin validaciones
            if (
                $model->updateAttributes([
                    'idEstado' => 5,
                    'updated_at' => new \yii\db\Expression('GETDATE()'), // Usa la fecha del servidor SQL
                    'updated_by' => $usuarioActual
                ])
            ) {
                Yii::$app->session->setFlash(
                    'success',
                    'PlanillaEmbarqueTraspaso anulado con éxito! ( Planilla: '
                    . $model->planillaEmbarque->id . ', Traspaso: '
                    . $model->tipoDocumento . $model->consecutivoDocumento . ', con unidades: '
                    . $model->unidades . ' para la tienda ' . $model->codAlmacenDestino . $model->almacenDestino . ' )'
                );
            } else {
                Yii::$app->session->setFlash('error', 'Error al actualizar el estado.');
            }

            return $this->redirect(Yii::$app->request->referrer ?: ['index']);
        }
    }




}
