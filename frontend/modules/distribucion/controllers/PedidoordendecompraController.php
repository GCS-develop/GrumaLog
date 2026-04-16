<?php

namespace frontend\modules\distribucion\controllers;

use Yii;
use frontend\models\Pedidoordendecompra;
use frontend\models\search\PedidoordendecompraSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\widgets\ActiveForm;
use yii\web\UploadedFile;
use yii\helpers\FileHelper;

use yii\db\IntegrityException;
use yii\db\Exception as DbException;

use frontend\models\Pedido;
use frontend\models\Centrooperacion;
use frontend\models\Ordendecompra;
use frontend\models\Pedidoordendecompraitem;
use frontend\models\Pedidodetalle;
use frontend\models\FileFormInput;
use frontend\models\Logborradopedido;

use common\components\PedidoImportService;

/**
 * PedidoordendecompraController implements the CRUD actions for Pedidoordendecompra model.
 */
class PedidoordendecompraController extends Controller
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
     * Lists all Pedidoordendecompra models.
     *
     * @return string
     */
    public function actionIndex($idpedido)
    {
        $modelpedido = Pedido::findOne(['id' => $idpedido]);

        $searchModel = new PedidoordendecompraSearch();
        $dataProvider = $searchModel->search($this->request->queryParams, $idpedido);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'modelpedido' => $modelpedido
        ]);
    }

    /**
     * Displays a single Pedidoordendecompra model.
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
     * Creates a new Pedidoordendecompra model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate($idpedido)
    {
        $model = new Pedidoordendecompra();
        $model->nroItems = 0;
        $model->totalUnidades = 0;
        $model->idPedido = $idpedido;

        $modelco = Centrooperacion::findOne(['codigo' => '002']);
        $model->idCentroOperacion = $modelco->id;

        $fileForm  = new FileFormInput();

        if (
            Yii::$app->request->isAjax
            && $model->load(Yii::$app->request->post())
            && $fileForm->load(Yii::$app->request->post())
        ) {
            $fileForm->archivo = UploadedFile::getInstance($fileForm, 'archivo');
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ActiveForm::validateMultiple([$model, $fileForm]);
        }

        if ($this->request->isPost) {
            if (
                $model->load($this->request->post())  &&
                $fileForm->load(Yii::$app->request->post())
            ) {

                $fileForm->archivo = UploadedFile::getInstance($fileForm, 'archivo');
                $this->attachOrdenCompra($model);

                // valida cabecera y archivo por separado
                if ($model->validate() && $fileForm->validate()) {
                    $tx = Yii::$app->db->beginTransaction();
                    $tmpPath = null;
                    try {
                        // guarda SOLO el nombre en la cabecera
                        $model->nombreArchivo = $fileForm->archivo ? $fileForm->archivo->name : null;

                        if (!$model->save()) {
                            throw new \RuntimeException('No se pudo guardar la OC: ' . json_encode($model->getErrors()));
                        }

                        // guardar el Excel de forma temporal
                        $dir = Yii::getAlias('@runtime/uploads');
                        FileHelper::createDirectory($dir, 0775, true);
                        $tmpPath = $dir . DIRECTORY_SEPARATOR . uniqid('imp_', true) . '.' . $fileForm->archivo->getExtension();
                        $fileForm->archivo->saveAs($tmpPath); // NO borrar tmp original

                        // throw new \RuntimeException('OC: ' . json_encode($model->idOrdenCompra));

                        // importar (idPedido, idOrdenCompra, archivo)
                        $svc = new PedidoImportService();
                        $svc->importarData($model->idPedido, $model->idOrdenCompra, $tmpPath);

                        // (opcional) recalcular totales en cascada por OC
                        // \app\services\TotalesPedidoService::recalcFromOcId((int)$model->id);

                        $tx->commit();
                        @unlink($tmpPath);

                        Yii::$app->session->setFlash('success', 'Orden de Compra registrada e importada con éxito.');
                        return $this->redirect(['index', 'idpedido' => $model->idPedido]);
                    } catch (\Throwable $e) {
                        $tx->rollBack();
                        @unlink($tmpPath);
                        Yii::$app->session->setFlash('error', $e->getMessage());
                        return $this->redirect(['index', 'idpedido' => $model->idPedido]);
                    }
                }
            }
        }

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('create', [
                'model' => $model,
                'fileForm' => $fileForm,
            ]);
        }
    }

    /**
     * Updates an existing Pedidoordendecompra model.
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
     * Deletes an existing Pedidoordendecompra model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $idpedido = $model->idPedido;

        $tx = Yii::$app->db->beginTransaction();
        try {
            // 1) Validar que ningún SKU tenga unidades recibidas (ya fue contado)
            $totalRecibidas = (int) Pedidodetalle::find()
                ->where([
                    'idPedido'      => $model->idPedido,
                    'idOrdenCompra' => $model->idOrdenCompra,
                ])
                ->sum('unidadesRecibidas');

            if ($totalRecibidas > 0) {
                Yii::$app->session->setFlash(
                    'warning',
                    'No se puede eliminar la OC porque ya tiene unidades contadas/recibidas.'
                );
                return $this->redirect(['index', 'idpedido' => $idpedido]);
            }

            // 2) Registrar log ANTES de borrar (necesitamos los datos de relaciones)
            $unidadesAntes = (int) $model->totalUnidades;
            Logborradopedido::registrarEliminarOC($model, $unidadesAntes);

            // 3) Borrar pedidodetalle de esta OC
            $deletedDet = Pedidodetalle::deleteAll([
                'idPedido'      => $model->idPedido,
                'idOrdenCompra' => $model->idOrdenCompra,
            ]);

            // 4) Borrar pedidoordendecompraitem de esta OC
            $deletedItem = Pedidoordendecompraitem::deleteAll([
                'idPedido'      => $model->idPedido,
                'idOrdenCompra' => $model->idOrdenCompra,
            ]);

            // 5) Borrar el registro pedidoordendecompra
            $idPedido = $model->idPedido;
            $model->delete();

            // 6) Recalcular totales del pedido padre
            Yii::$app->db->createCommand("
                UPDATE pedido SET
                    nroOrdenesCompra = (
                        SELECT COUNT(*) FROM pedidoordendecompra WHERE idPedido = :p1
                    ),
                    totalUnidades = ISNULL((
                        SELECT SUM(totalUnidades) FROM pedidoordendecompra WHERE idPedido = :p2
                    ), 0)
                WHERE id = :p3
            ", [':p1' => $idPedido, ':p2' => $idPedido, ':p3' => $idPedido])->execute();

            $tx->commit();
            Yii::$app->session->setFlash(
                'success',
                "OC eliminada del pedido. ({$deletedItem} items, {$deletedDet} SKUs borrados)"
            );
        } catch (\Throwable $e) {
            $tx->rollBack();
            Yii::$app->session->setFlash('error', 'No se pudo eliminar la OC: ' . $e->getMessage());
        }

        return $this->redirect(['index', 'idpedido' => $idpedido]);
    }

    /**
     * Finds the Pedidoordendecompra model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Pedidoordendecompra the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Pedidoordendecompra::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /** 1) Vincula la Orden de Compra y valida que exista */
    private function attachOrdenCompra($model): void
    {
        $oc = Ordendecompra::find()->where([
            'idCO'           => $model->idCentroOperacion,
            'idTipoDocumento' => $model->idTipoDocumento,
            'consecutivo'    => $model->consecutivo,
        ])->one();

        if (!$oc) {
            throw new \DomainException(
                'Orden de Compra NO existe: ' .
                    "{$model->idCentroOperacion}/{$model->idTipoDocumento}/{$model->consecutivo}"
            );
        }
        $model->idOrdenCompra = $oc->id;
    }

    /** 2) Sube el Excel y lo guarda en @runtime/uploads; devuelve la ruta temporal */
    private function storeUpload($model): string
    {
        $model->archivo = UploadedFile::getInstance($model, 'archivo');
        if (!$model->archivo) {
            throw new \DomainException('Debes adjuntar un archivo .xlsx.');
        }

        $model->nombreArchivo = $model->archivo->name;

        $ext = strtolower($model->archivo->getExtension());
        if (!in_array($ext, ['xlsx', 'xls'])) {
            throw new \DomainException('Formato no permitido. Solo .xlsx o .xls.');
        }

        // Guardar en carpeta temporal
        $uploadDir = Yii::getAlias('@runtime/uploads');
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0775, true);
        }
        $tmpPath = $uploadDir . DIRECTORY_SEPARATOR .
            pathinfo($model->archivo->name, PATHINFO_FILENAME) .
            '-' . time() . '.' . $ext;

        if (!$model->archivo->saveAs($tmpPath)) {
            Yii::$app->session->setFlash('error', 'No se pudo guardar el archivo temporalmente.');
            return $this->redirect(['index', 'idpedido' => $model->idPedido]);
        }

        return $tmpPath;
    }

    /** 3) Guarda el Pedido y ejecuta la importación dentro de una transacción */
    private function saveAndImport($model, $tmpPath): array
    {
        $tx = Yii::$app->db->beginTransaction();
        try {
            $svc = new PedidoImportService();                 // tu servicio actual
            $res = $svc->importarData((int)$model->idPedido, (int)$model->idOrdenCompra, $tmpPath);

            $tx->commit();
            return $res;
        } catch (IntegrityException | DbException $e) {
            // SQLSTATE y códigos propios de SQL Server para unicidad
            $sqlstate = $e->errorInfo[0] ?? null;   // '23000'
            $driver   = $e->errorInfo[1] ?? null;   // 2627 o 2601
            $msg      = $e->errorInfo[2] ?? $e->getMessage();

            if (
                $sqlstate === '23000'
                || in_array((int)$driver, [2627, 2601], true)
            ) {

                $tx->rollBack();
                // Mensaje amigable hacia arriba
                throw new \DomainException('La Orden de Compra ya está registrada.');
            }

            $tx->rollBack();
            throw $e; // otra cosa: re-lanza
        } catch (\Throwable $e) {
            $tx->rollBack();
            throw $e;
        } finally {
            @unlink($tmpPath);
        }
    }
}
