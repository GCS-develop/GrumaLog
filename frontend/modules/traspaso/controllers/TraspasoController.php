<?php

namespace frontend\modules\traspaso\controllers;

use Yii;
use frontend\models\Traspaso;
use frontend\models\Inventario;
use frontend\models\searchTraspasoSearch;
use frontend\models\search\TraspasoSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\widgets\ActiveForm;
use yii\db\Expression;
use frontend\models\Traspasodetalle;



use Exception;
use yii\web\BadRequestHttpException;

/**
 * TraspasoController implements the CRUD actions for Traspaso model.
 */
class TraspasoController extends Controller
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
     * Lists all Traspaso models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new TraspasoSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Traspaso model.
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
     * Creates a new Traspaso model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Traspaso();

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                $id = null;
                if ($model->validate()) {
                    $id = $model->save();
                }

                if ($id != null) {
                    Yii::$app->session->setFlash('success', 'Registro Actualizado');
                } else {
                    Yii::$app->session->setFlash('error', 'Error Actualizando Registro');
                }

                return $this->redirect(['index']);
            }
        } else {
            $model->loadDefaultValues();
        }

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Traspaso model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);


        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                $id = null;
                if ($model->validate()) {
                    $id = $model->save();
                }

                if ($id != null) {
                    Yii::$app->session->setFlash('success', 'Registro Actualizado');
                } else {
                    Yii::$app->session->setFlash('error', 'Error Actualizando Registro');
                }

                return $this->redirect(['index']);
            }
        }

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Traspaso model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        if ($model !== null) {
            try {
                $model->delete();

                Yii::$app->session->setFlash('success', 'Registro Eliminado');
            } catch (\yii\db\IntegrityException $e) {
                Yii::$app->session->setFlash('error', 'No se puede eliminar este registro debido a que tiene subcategorías asociadas.');
            }
        } else {
            Yii::$app->session->setFlash('error', 'Registro no encontrado.');
        }

        return $this->redirect(['index']);
    }

    public function actionSincronizar($id)
    {

        Traspaso::sincronizarTraspaso($id);

        return $this->redirect(['index']);
    }

    /**
     * Finds the Traspaso model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Traspaso the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Traspaso::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionAnular($id)
    {
        $model = $this->findModel($id);
        $filasAfectadas = 0;

        if ($model->idEstado !== 1 && $model->idEstado !== 3) {

            Yii::$app->session->setFlash('warning', 'No puedes anular en este estado!');
            return $this->redirect(['index']);

        }

        if ($this->request->isPost) {

            $model->anula_at = new Expression('GETDATE()');
            $model->anula_by = Yii::$app->user->id;

            $model->idEstado = 2;

            if ($model->save()) {

                foreach ($model->traspasodetalles as $detalle) {
                    $filasAfectadas += $detalle->retornarInventario();
                    Yii::$app->session->setFlash('success', 'Items: ' . $filasAfectadas . ' regresaron fueron regresados al inventario');
                }

                Yii::$app->session->setFlash(
                    'info',
                    'Traspaso anulado con éxito! ' .
                    $model->tipodocumento->codigo .
                    (isset($model->codigoerp) ? $model->codigoerp->f350_consec_docto : ' interno: ' . $model->consecutivo)
                );

                return $this->redirect(['index']);

            } else {

                Yii::$app->session->setFlash('error', 'Ups!, ocurrio un problema con : ' . $model);

            }
        }
    }

    public function actionFactura($id)
    {
        $model = $this->findModel($id);
        if ($model->idEstado !== 1 && $model->idEstado !== 3) {

            Yii::$app->session->setFlash('warning', 'No puedes imprimir en este estado!');
            return $this->redirect(['index']);

        }

        return $this->redirect(['/traspaso/traspasodetalle/print', 'idtraspaso' => $model->id]);
    }

    public function actionCambiarEstado()
    {
        // Yii::info('Acción Cambiar Estado ejecutada para mandar a muelle de forma masiva los 207 (VMI)', __METHOD__);

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $ids = Yii::$app->request->post('ids');

        if (Yii::$app->request->isAjax && Yii::$app->request->post()) {

            // Yii::debug($ids, 'ajax');

            if ($ids) {
                // Primero obtenemos los registros que están en estado "terminado"
                $traspasos = Traspaso::find()->where(['id' => $ids, 'idEstado' => 1])->all();

                // Verificar si hay registros en estado "terminado"
                if (empty($traspasos)) {
                    return ['success' => false, 'message' => 'No se encontraron registros en estado "terminado" para actualizar.'];
                }

                // Array para almacenar los errores
                $errores = [];

                // Intentamos actualizar los registros
                foreach ($traspasos as $traspaso) {
                    // Actualizar el estado de cada registro
                    $traspaso->idEstado = 3;  // Aquí pones el nuevo estado que deseas
                    $traspaso->muelle_at = new Expression('GETDATE()');
                    $traspaso->muelle_by = Yii::$app->user->id;

                    if (!$traspaso->save()) {
                        // Si algo falla, guardamos el error
                        $errores[] = 'Error al actualizar el registro con ID ' . $traspaso->id;
                    }
                }

                // Si no hubo errores, confirmamos la actualización
                if (empty($errores)) {


                    return ['success' => true, 'message' => 'Todos los registros se actualizaron correctamente.'];
                } else {
                    // Si hubo errores, reportamos qué registros fallaron
                    return ['success' => false, 'message' => implode(', ', $errores)];
                }
            } else {
                return ['success' => false, 'message' => 'No se seleccionaron registros.'];
            }
        }

        return ['success' => false, 'message' => 'La solicitud no es válida.'];
    }


}
