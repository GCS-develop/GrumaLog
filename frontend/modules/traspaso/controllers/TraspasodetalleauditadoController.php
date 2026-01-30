<?php

namespace frontend\modules\traspaso\controllers;

use common\components\TraspasoDetalleAuditadoService;
use Yii;

use frontend\models\Traspasodetalleauditado;
use frontend\models\search\TraspasodetalleauditadoSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\widgets\ActiveForm;
use yii\db\Expression;
use yii\web\Response;
use yii\web\BadRequestHttpException;
use yii\data\ActiveDataProvider;
use yii\db\Query;


/**
 * TraspasodetalleauditadoController implements the CRUD actions for Traspasodetalleauditado model.
 */
class TraspasodetalleauditadoController extends Controller
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
                        'purge-traspaso' => ['POST'],
                        'delete-zeros-by-user' => ['POST'],

                    ],
                ],
            ]
        );
    }

    /**
     * Lists all Traspasodetalleauditado models.
     *
     * @return string
     */
    public function actionIndex($idtraspaso = null)
    {
        $searchModel = new TraspasodetalleauditadoSearch();
        $dataProvider = $searchModel->search($this->request->queryParams, $idtraspaso);

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('index_ajax', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'idtraspaso' => $idtraspaso,
            ]);
        } else {
            return $this->render('index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'idtraspaso' => $idtraspaso,
            ]);
        }
    }

    public function actionIndexNovedades($idtraspaso = null)
    {
        $searchModel = new TraspasodetalleauditadoSearch();
        $dataProvider = $searchModel->searchConDiferencias($this->request->queryParams, $idtraspaso);

        return $this->render('index_novedades', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'idtraspaso' => $idtraspaso,
        ]);
    }


    /**
     * Displays a single Traspasodetalleauditado model.
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
     * Creates a new Traspasodetalleauditado model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Traspasodetalleauditado();

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
     * Updates an existing Traspasodetalleauditado model.
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
     * Deletes an existing Traspasodetalleauditado model.
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
     * Finds the Traspasodetalleauditado model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Traspasodetalleauditado the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Traspasodetalleauditado::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * Lista TODOS los traspasos que tienen traspasodetalleauditado.
     * Muestra totales (registros y unidades) y acciones.
     */

    public function actionIndexAgrupado($idtraspaso = null)
    {
        $searchModel  = new TraspasodetalleauditadoSearch();
        $dataProvider = $searchModel->searchAgrupadoPorUsuario($this->request->queryParams, $idtraspaso);

        return $this->render('index_agrupado', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
            'idtraspaso'   => $idtraspaso,
        ]);
    }

    /**
     * Borra SOLO lo auditado por $creadorUserId en el traspaso $idtraspaso,
     * guardando historial en *_delete con el usuario actual (ejecutor).
     */
    public function actionDeleteByUser($idtraspaso, $creadorUserId)
    {
        $ejecutor = (int)Yii::$app->user->id;
        if (!$ejecutor) {
            throw new \yii\web\BadRequestHttpException('Sesión inválida.');
        }

        /** @var TraspasoDetalleAuditadoService $svc */
        $svc = Yii::createObject(TraspasoDetalleAuditadoService::class);
        [$ins, $del] = $svc->purgeByTraspasoAndCreador((int)$idtraspaso, (int)$creadorUserId, $ejecutor);

        if ($del > 0) {
            Yii::$app->session->setFlash('success', "Se borraron $del registros y se archivaron $ins en historial.");
        } else {
            Yii::$app->session->setFlash('info', 'No había registros para ese usuario en este traspaso.');
        }

        // Volver al listado agrupado manteniendo filtros si vienen por GET
        return $this->redirect(array_merge(['index-agrupado'], Yii::$app->request->get()));
    }

    public function actionArchiveZerosByUser($idtraspaso, $creadorUserId)
    {
        $ejecutor = (int)Yii::$app->user->id;
        if (!$ejecutor) {
            throw new \yii\web\BadRequestHttpException('Sesión inválida.');
        }

        /** @var \common\components\TraspasoDetalleAuditadoService $svc */
        $svc = Yii::createObject(\common\components\TraspasoDetalleAuditadoService::class);
        $inserted = $svc->archiveZerosByTraspasoAndCreador((int)$idtraspaso, (int)$creadorUserId, $ejecutor);

        Yii::$app->session->setFlash(
            $inserted > 0 ? 'success' : 'info',
            $inserted > 0
                ? "Archivados $inserted registros (cantidad = 0) de ese usuario."
                : "No había registros con cantidad = 0 para archivar (o ya estaban en historial)."
        );

        // Volver al listado agrupado conservando filtros actuales
        return $this->redirect(array_merge(
            ['/traspaso/traspasodetalleauditado/index-agrupado'],
            Yii::$app->request->get()
        ));
    }

    public function actionDeleteZerosByUser($idtraspaso, $creadorUserId)
    {
        $ejecutor = (int)\Yii::$app->user->id;
        if (!$ejecutor) {
            throw new \yii\web\BadRequestHttpException('Sesión inválida.');
        }

        /** @var \common\components\TraspasoDetalleAuditadoService $svc */
        $svc = \Yii::createObject(\common\components\TraspasoDetalleAuditadoService::class);
        [$ins, $del] = $svc->deleteZerosByTraspasoAndCreador((int)$idtraspaso, (int)$creadorUserId, $ejecutor);

        if ($del > 0) {
            \Yii::$app->session->setFlash('success', "Archivados: $ins. Borrados: $del (cantidad = 0).");
        } else {
            \Yii::$app->session->setFlash('info', 'No había registros con cantidad = 0 para borrar.');
        }

        return $this->redirect(array_merge(
            ['/traspaso/traspasodetalleauditado/index-agrupado'],
            \Yii::$app->request->get()
        ));
    }
}
