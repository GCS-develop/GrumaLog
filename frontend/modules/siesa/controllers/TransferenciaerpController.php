<?php

namespace frontend\modules\siesa\controllers;

use Yii;
use frontend\models\Transferenciaerp;
use frontend\models\search\TransferenciaerpSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\widgets\ActiveForm;

use yii\web\UploadedFile;

use frontend\models\FileTransferenciaInput;
use frontend\models\Transferenciaordencompraexcel;
use frontend\models\Transferenciaerperror;
use frontend\models\Transferencialogws;
use frontend\models\Transferenciatransitoexcel;
use frontend\models\Traspaso;
use frontend\models\Conteocdscdestinofactura;
use frontend\models\Logtransferenciaerp;
use frontend\models\search\LogtransferenciaerpSearch;

use common\models\ProcedimientosGenerales;

/**
 * TransferenciaerpController implements the CRUD actions for Transferenciaerp model.
 */
class TransferenciaerpController extends Controller
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
     * Lists all Transferenciaerp models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new TransferenciaerpSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Transferenciaerp model.
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
     * Creates a new Transferenciaerp model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Transferenciaerp();
        $model->numeroRegistros = 0;
        $model->enviadoWS = 0;

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {

                if ($model->save() != null) {

                    $documentoNotas = 'Crossdocking certificado => ' . $model->id . '-' . $model->documento . ' ' . $model->notas;
                    $documentoDescripcion = 'Crossdocking certificado => ' . $model->id . '-' . $model->documento . ' ' . $model->descripcion;

                    $modeltransferencia = $this->findModel($model->id);
                    $modeltransferencia->notas = $documentoNotas;
                    $modeltransferencia->descripcion = $documentoDescripcion;
                    $modeltransferencia->save();

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
     * Updates an existing Transferenciaerp model.
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

                if ($model->save() != null) {
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
            return $this->renderAjax('update', [
                'model' => $model,
            ]);
        }
    }

    public function actionTransferencia($id, $origen = null, $idconteofactura = null)
    {

        $model = $this->findModel($id);

        switch ($model->idConectorDinamico) {
            case 1:
                $respuesta = Transferenciaerp::transferenciaSalidaWS($id);
                break;
            case 3:
                $respuesta = Transferenciaerp::entradaAlmacenInteWS($id);
                break;
        }

        if ($respuesta == 0) {
            $mensaje = "Proceso de Actualización Finalizo Con Éxito";
            Yii::$app->session->setFlash('success', $mensaje);

            $model->enviadoWS = 1;
        } else {
            $mensaje = "Proceso de Actualización Presenta Inconsistencia";
            Yii::$app->session->setFlash('error', $mensaje);
            $model->enviadoWS = 0;
        }
        $model->save();

        if ($origen == 'Conteo') {
            return $this->redirect(['/programacion/facturaentregamercancia/indexlegalizaconteo']);
        }

        if ($origen == 'CDSC') {
            $iderpentrada = Conteocdscdestinofactura::actualizarentradaerp($idconteofactura);
            return $this->redirect(['/crossdocking/conteocdscdestinofactura/indexentrada']);
        }

        if ($origen == 'Traspaso CDSC') {
            Conteocdscdestinofactura::actualizartraspasoerp($idconteofactura);
            return $this->redirect(['/crossdocking/conteocdscdestinofactura/indextraspaso']);
        }

        return $this->redirect(['resultado', 'id' => $id]);
    }

    public function actionResultado($id)
    {
        $model = $this->findModel($id);

        $logs = Transferencialogws::find()
            ->where(['idTransferenciaerp' => $id])
            ->orderBy(['id' => SORT_ASC])
            ->all();

        $errors = Transferenciaerperror::find()
            ->where(['idTransferenciaerp' => $id])
            ->orderBy(['id' => SORT_ASC])
            ->all();

        $totalGrupos = count($logs);
        $exitosos = count(array_filter($logs, function ($l) { return $l->mensaje == '0'; }));
        $fallidos = $totalGrupos - $exitosos;

        return $this->render('resultado', compact('model', 'logs', 'errors', 'totalGrupos', 'exitosos', 'fallidos'));
    }

    /**
     * Deletes an existing Transferenciaerp model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        Logtransferenciaerp::registrar($model, 'MANUAL_USUARIO');

        Transferencialogws::deleteAll(['idTransferenciaerp' => $id]);
        Transferenciaerperror::deleteAll(['idTransferenciaerp' => $id]);
        Transferenciaordencompraexcel::deleteAll(['idTransferenciaerp' => $id]);
        Transferenciatransitoexcel::deleteAll(['idTransferenciaerp' => $id]);

        $model->delete();

        return $this->redirect(['index']);
    }

    public function actionImportardataxls($id)
    {
        $model = new FileTransferenciaInput();

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post())) {

            $userId = Yii::$app->user->id;
            $model->archivo = UploadedFile::getInstance($model, 'archivo');

            if ($model->importTransferencia($id)) {

                $tipomovimiento = 2;
                $idtraspaso = Traspaso::generarTraspasoDesdeTransferencia($id, $tipomovimiento);

                Yii::$app->session->setFlash('success', 'El Archivo se ha cargado correctamente. ');
            } else {
                $errorString = ProcedimientosGenerales::erroresModelo($model->getErrors());
                Yii::$app->session->setFlash('error', 'Ocurrió un error al cargar los archivos: ' . $errorString);
            }

            return $this->redirect(['index']);

        }

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('uploadtransferencia', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Finds the Transferenciaerp model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Transferenciaerp the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionHistorial()
    {
        $searchModel  = new LogtransferenciaerpSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('historial', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionLogBorrada($id)
    {
        $logs = Transferencialogws::find()
            ->where(['idTransferenciaerp' => $id])
            ->orderBy(['id' => SORT_ASC])
            ->all();

        $logHeader = Logtransferenciaerp::find()
            ->where(['idTransferenciaerpBorrada' => $id])
            ->orderBy(['id' => SORT_DESC])
            ->one();

        return $this->render('log_borrada', [
            'idBorrada' => $id,
            'logs'      => $logs,
            'logHeader' => $logHeader,
        ]);
    }

    protected function findModel($id)
    {
        if (($model = Transferenciaerp::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
