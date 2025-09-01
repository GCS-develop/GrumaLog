<?php

namespace frontend\modules\devolucion\controllers;


use Yii;
use frontend\models\DataCodigoBarrasDevolucion;
use frontend\models\Devoluciondocumento;
use frontend\models\Devoluciondocumentodetalle;

use frontend\models\search\DevoluciondocumentodetalleSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\db\Expression;

use common\models\User;
use frontend\models\Conteobylecturacodigo;
use frontend\models\search\ConteobylecturacodigoSearch;


use frontend\models\search\TransferdevdocumentosSearch;
use kartik\form\ActiveForm;
use frontend\models\Transferdevdocumentos;
use yii\data\ActiveDataProvider;
use frontend\models\DataDocumentoDevolucion;
use frontend\models\DataDocumentoEntrada;

/**
 * DevoluciondocumentodetalleController implements the CRUD actions for Devoluciondocumentodetalle model.
 */
class DevoluciondocumentodetalleController extends Controller
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
     * Lists all Devoluciondocumentodetalle models.
     *
     * @return string
     */
    public function actionIndex($iddocumento)
    {
        $model = Devoluciondocumento::findOne(['id' => $iddocumento]);

        $idusuario = Yii::$app->user->id;
        $modeluser = User::findOne(['id' => $idusuario]);

        $searchModel = new DevoluciondocumentodetalleSearch();
        $dataProvider = $searchModel->search($this->request->queryParams, $iddocumento);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'model' => $model,
            'modeluser' => $modeluser
        ]);
    }

   public function actionIndexall()
{
    $iddocumento = null;
    $idusuario = Yii::$app->user->id;
    $modeluser = User::findOne(['id' => $idusuario]);

    $searchModel = new DevoluciondocumentodetalleSearch();
    $params = Yii::$app->request->queryParams;
    $dataProvider = $searchModel->search($params, $iddocumento);

    // Revisamos si hay un filtro aplicado por proveedor
    if (!empty($params['DevoluciondocumentodetalleSearch']['nombreProveedor'])) {
        $dataProvider->pagination = false; // sin paginación si hay filtro por proveedor
    } else {
        $dataProvider->pagination = ['pageSize' => 50]; // paginación normal
    }

    return $this->render('index_all', [
        'searchModel' => $searchModel,
        'dataProvider' => $dataProvider,
        'modeluser' => $modeluser
    ]);
}



    public function actionIndexenviosiesa()
    {
        $iddocumento = null;
        $idusuario = Yii::$app->user->id;
        $modeluser = User::findOne(['id' => $idusuario]);

        $searchModel = new DevoluciondocumentodetalleSearch();
        $dataProvider = $searchModel->searchenviosiesa($this->request->queryParams, $iddocumento, true);

        return $this->render('index_envio_siesa', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'modeluser' => $modeluser
        ]);
    }


 public function actionRegistrarDatos($id)
{
    if ($id === null) {
    throw new \yii\web\BadRequestHttpException('El parámetro "id" es obligatorio.');
}

    if ($id === null) {
        Yii::$app->session->setFlash('error', 'ID inválido.');
        return $this->redirect(['index']);
    }

    $ids = explode(',', $id);
    $modelBase = new Transferdevdocumentos();

    // Validación AJAX
    if (Yii::$app->request->isAjax && $modelBase->load(Yii::$app->request->post())) {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return \yii\widgets\ActiveForm::validate($modelBase);
    }

    // Si se envió el formulario
    if (Yii::$app->request->isPost && $modelBase->load(Yii::$app->request->post())) {
        $guardados = 0;
        $errores = 0;
        $ultimoDocumentoId = null;

        // Obtener el nuevo ID de transferencia secuencial
        $ultimoIdTransferencia = Transferdevdocumentos::find()->max('id_transferencia');
        $nuevoIdTransferencia = $ultimoIdTransferencia ? $ultimoIdTransferencia + 1 : 1;

        foreach ($ids as $devolucionId) {
            $detalle = Devoluciondocumentodetalle::findOne($devolucionId);
            if (!$detalle) {
                Yii::warning("No se encontró el detalle con ID $devolucionId", __METHOD__);
                $errores++;
                continue;
            }

            $nuevo = new Transferdevdocumentos();
            $nuevo->attributes = $modelBase->attributes;
            $nuevo->id_devoluciondocumento = $devolucionId;
            $nuevo->id_transferencia = $nuevoIdTransferencia; // Asignar mismo ID de grupo
            $nuevo->fecha_documento = date('Ymd'); // Fecha con formato YYYYMMDD para campo CHAR(8)


            // Validación antes de guardar
            if ($nuevo->validate()) {
                if ($nuevo->save()) {
                    Yii::info("Transferencia guardada para ID $devolucionId con ID Transferencia {$nuevo->id}", __METHOD__);
                    $guardados++;
                    $ultimoDocumentoId = $nuevo->id;
                } else {
                    Yii::error("Error al guardar transferencia para ID $devolucionId: " . print_r($nuevo->errors, true), __METHOD__);
                    $errores++;
                }
            } else {
                Yii::error("Errores de validación para el detalle $devolucionId: " . print_r($nuevo->errors, true), __METHOD__);
                $errores++;
            }
        }

        // Mensajes de resultado
        if ($guardados > 0 && $errores === 0) {
            Yii::$app->session->setFlash('success', "$guardados transferencias registradas correctamente (Transferencia #$nuevoIdTransferencia).");
        } elseif ($guardados > 0 && $errores > 0) {
            Yii::$app->session->setFlash('warning', "$guardados transferencias guardadas, $errores con errores.");
        } else {
            Yii::$app->session->setFlash('error', "No se pudo guardar ninguna transferencia.");
        }

        // Redirigir si se guardó al menos un registro
        if ($ultimoDocumentoId !== null) {
            return $this->redirect(['/devolucion/transferdevdocumentos/viewtransferenciaocerp', 'id' => $nuevoIdTransferencia]);
        } else {
            return $this->redirect(['index']);
        }
    }

    // Renderizar formulario inicial
    $devolucion = Devoluciondocumentodetalle::findOne($ids[0]);
    return $this->renderAjax('@frontend/modules/devolucion/views/Transferdevdocumentos/_form', [
        'model' => $modelBase,
        'devolucion' => $devolucion,
        'ids' => $ids,
    ]);
}



    public function actionGenerarDocumento()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $ids = Yii::$app->request->post('ids');

        if (Yii::$app->request->isAjax && Yii::$app->request->post()) {

            // Yii::debug($ids, 'ajax');

            if ($ids) {

                return ['success' => true, 'message' => 'Todos los registros se actualizaron correctamente.ids: ' . implode(', ', $ids)];
            }
        }
    }
    /**
     * Displays a single Devoluciondocumentodetalle model.
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
     * Creates a new Devoluciondocumentodetalle model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate($iddocumento)
    {
        $model = new DataCodigoBarrasDevolucion();
        $model->cantidad = 1;

        $modeldocumento = Devoluciondocumento::findOne(['id' => $iddocumento]);

        $idusuario = Yii::$app->user->id;
        $modeluser = User::findOne(['id' => $idusuario]);

        if ($this->request->isPost && $model->load($this->request->post())) {

            $modeldetalle = Devoluciondocumentodetalle::find()->where(['idDocumento' => $iddocumento, 'codigoBarras' => $model->codigobarras])->one();

            if ($modeldetalle != null) {
                $modeldetalle->cantidadRegistrada = $modeldetalle->cantidadRegistrada + $model->cantidad;
                $modeldetalle->registrada = 1;
                $modeldetalle->usuarioRegistra = Yii::$app->user->id;
                $modeldetalle->fechaRegistra = new Expression('GETDATE()');;

                if ($modeldetalle->save()) {
                    $tipo = 3; // Devoluciones
                    $ok = Conteobylecturacodigo::grabarRegistro(
                        $tipo,
                        $model->codigobarras,
                        $model->cantidad,
                        $modeldocumento->idInterfase,
                        $modeldocumento->id,
                        $modeldetalle->id,
                    );
                }

                Yii::$app->session->setFlash('success', 'Registro Actualizado');
            } else {
                Yii::$app->session->setFlash('error', 'Número Documento NO EXISTE');
            }
            return $this->redirect(['create', 'iddocumento' => $iddocumento]);
        }

        return $this->render('create', [
            'model' => $model,
            'modeldocumento' => $modeldocumento,
            'modeluser' => $modeluser
        ]);
    }

    /**
     * Updates an existing Devoluciondocumentodetalle model.
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
     * Deletes an existing Devoluciondocumentodetalle model.
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

    public function actionDeleteconteo($id)
    {
        $modelconteo = Conteobylecturacodigo::findOne(['id' => $id]);
        $iddetalle = $modelconteo->idConteoDetalle;
        $unidades = $modelconteo->unidades;

        $modelconteo->delete();

        $model = $this->findModel($iddetalle);
        $model->cantidadRegistrada = $model->cantidadRegistrada - $unidades;
        $model->save();

        return $this->redirect(['viewconteo', 'id' => $model->id]);
    }

    public function actionViewconteo($id)
    {
        $model = $this->findModel($id);

        $modeldocumento = Devoluciondocumento::findOne(['id' => $model->idDocumento]);

        $modulo = 3;
        $idusuario = Yii::$app->user->id;
        $modeluser = User::findOne(['id' => $idusuario]);

        $searchModel = new ConteobylecturacodigoSearch();
        $dataProvider = $searchModel->search($this->request->queryParams, $modulo, $modeldocumento->id, $id);

        return $this->render('index_conteo', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'model' => $model,
            'modeldocumento' => $modeldocumento,
            'modeluser' => $modeluser
        ]);
    }

    /**
     * Finds the Devoluciondocumentodetalle model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Devoluciondocumentodetalle the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Devoluciondocumentodetalle::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }





}