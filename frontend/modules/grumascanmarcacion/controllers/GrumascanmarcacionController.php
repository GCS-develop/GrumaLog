<?php

namespace frontend\modules\grumascanmarcacion\controllers;

use frontend\models\Grumascanmarcacion;
use frontend\models\search\GrumascanmarcacionSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii;
// use frontend\models\Impresora;
use frontend\models\forms\GrumascanMarcacionBulkUseForm;
use yii\db\Expression;
use frontend\models\Impresoraspaxarbodega as impresora;
use frontend\models\forms\GrumascanMarcacionPrintForm;
use common\components\MarcacionStickerPrinter;
use frontend\models\forms\GrumascanMarcacionUseForm;
use frontend\models\search\VwGrumascanMapaSearch;

/**
 * GrumascanmarcacionController implements the CRUD actions for Grumascanmarcacion model.
 */
class GrumascanmarcacionController extends Controller
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
     * Lists all Grumascanmarcacion models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new GrumascanmarcacionSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Grumascanmarcacion model.
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
     * Creates a new Grumascanmarcacion model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Grumascanmarcacion();

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
     * Updates an existing Grumascanmarcacion model.
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
     * Deletes an existing Grumascanmarcacion model.
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
     * Finds the Grumascanmarcacion model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Grumascanmarcacion the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Grumascanmarcacion::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }


    public function actionIndeximprecion()
    {
        $form = new GrumascanMarcacionPrintForm();

        // Traer impresoras (ajusta campos: id/nombre/tipo/ip/puerto/recurso)
        $printers = Impresora::getListaData();

        return $this->render('indeximprecion', [
            'model' => $form,
            'printers' => $printers,
        ]);
    }

    public function actionPrint()
    {
        $form = new GrumascanMarcacionPrintForm();

        // Traer impresoras para re-render en caso de error
        $printers = Impresora::getListaData();

        if (!$form->load(Yii::$app->request->post()) || !$form->validate()) {
            Yii::$app->session->setFlash('error', 'Datos inválidos para impresión.');
            return $this->render('indeximprecion', [
                'model' => $form,
                'printers' => $printers,
            ]);
        }

        $printer = Impresora::findOne($form->printer_id);
        if (!$printer) {
            Yii::$app->session->setFlash('error', 'Impresora no encontrada.');
            return $this->redirect(['indeximprecion']);
        }

        $usuario = Yii::$app->user->identity->username ?? 'N/A';

        $db = Yii::$app->db;
        $ids = [];

        $tx = $db->beginTransaction();
        try {
            for ($i = 0; $i < (int)$form->cantidad; $i++) {
                $m = new Grumascanmarcacion();
                $m->idbodega  = $form->idbodega;
                $m->ubicacion = $form->ubicacion;
                $m->seccion   = $form->seccion;

                // ✅ Guardado seguro (sin save(false))
                if (!$m->save()) {
                    $errors = $m->getFirstErrors();
                    $msg = 'No fue posible insertar marcación: ' . json_encode($errors, JSON_UNESCAPED_UNICODE);
                    throw new \RuntimeException($msg);
                }

                $ids[] = (int)$m->id;
            }

            $tx->commit();
        } catch (\Throwable $e) {
            $tx->rollBack();
            Yii::error($e->getMessage(), __METHOD__);
            Yii::$app->session->setFlash('error', 'Error creando stickers: ' . $e->getMessage());
            return $this->redirect(['indeximprecion']);
        }

        // -------------------------
        // Construir payload ZPL/EPL
        // -------------------------
        $esEpl = ((string)$printer->tipo === 'epl');

        if ($esEpl) {
            // EPL: tu método ya imprime etiqueta por cada ID
            $payload = MarcacionStickerPrinter::buildPayload($ids, $usuario, 'epl');
        } else {
            // ✅ ZPL: imprimir en páginas de 3 (3-up). Concatenamos varios ^XA...^XZ
            $payload = '';
            $chunks = array_chunk($ids, 3);
            foreach ($chunks as $chunk) {
                $payload .= MarcacionStickerPrinter::buildPayloadZpl3Up($chunk, $usuario);
            }
        }

        // Enviar a impresora
        $config = [
            'tipo'    => $printer->tipo,   // 'ip' o 'recurso' según tu tabla
            'ip'      => $printer->ip ?? null,
            'puerto'  => $printer->puerto ?? 9100,
            'recurso' => $printer->recurso ?? null,
        ];

        $resp = $this->enviarImpresora($payload, $config);

        if (($resp['status'] ?? 'error') === 'success') {
            Yii::$app->session->setFlash(
                'success',
                'Impresión enviada. Stickers: ' . count($ids)
                    . ' | Impresora: ' . ($printer->nombre ?? ($printer->bodega->nombre ?? 'N/A'))
                    . ' | ip: ' . ($printer->ip ?? 'N/A')
            );
        } else {
            Yii::$app->session->setFlash(
                'error',
                'Error imprimiendo: ' . ($resp['message'] ?? 'Error desconocido')
            );
        }

        return $this->redirect(['indeximprecion']);
    }


    /**
     * Reutiliza tu implementación. Pégala tal cual y ajústala si hace falta.
     */
    private function enviarImpresora($zpl, $config)
    {
        $tipo = $config['tipo'];
        $ip = $config['ip'] ?? null;
        $puerto = $config['puerto'] ?? 9100;
        $recurso = $config['recurso'] ?? null;

        Yii::$app->session->removeAllFlashes();

        if ($tipo == 'ip') {
            $socket = @fsockopen($ip, $puerto, $errno, $errstr, 10);
            if (!$socket) {
                $msg = "No se pudo conectar a la impresora. Ip: $ip:$puerto Error: $errstr ($errno)";
                Yii::error($msg, __METHOD__);
                return ['status' => 'error', 'message' => $msg];
            }
            fwrite($socket, $zpl);
            fclose($socket);
            return ['status' => 'success', 'message' => "Impresión enviada correctamente a $ip:$puerto."];
        }

        if ($tipo === 'recurso') {
            $tempDir = Yii::getAlias('@frontend') . '/temp';
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0777, true);
            }
            $tempFile = $tempDir . '/zpl_' . uniqid() . '.tmp';
            file_put_contents($tempFile, $zpl);

            $command = sprintf(
                'print %s /D:%s "%s"',
                $ip,
                '\\\\' . str_replace('\\', '\\\\', ltrim($recurso, '\\')),
                $tempFile
            );

            exec($command, $output, $returnVar);
            @unlink($tempFile);

            if ($returnVar !== 0) {
                $msg = "Error al imprimir en $recurso: " . implode("\n", $output);
                Yii::error($msg, __METHOD__);
                return ['status' => 'error', 'message' => $msg];
            }
            return ['status' => 'success', 'message' => "Impresión enviada a $recurso, correctamente!"];
        }

        return ['status' => 'error', 'message' => 'Tipo de impresora no reconocido.'];
    }
    /**
     * Re-imprime stickers existentes dado un rango de IDs.
     * No crea registros nuevos — solo reenvía los IDs a la impresora.
     */
    public function actionReprint()
    {
        $request  = Yii::$app->request;
        $printers = Impresora::getListaData();

        if (!$request->isPost) {
            return $this->redirect(['indeximprecion']);
        }

        $printerId = (int)$request->post('printer_id_reprint');
        $desde     = (int)$request->post('desde_reprint');
        $hasta     = (int)$request->post('hasta_reprint');

        if (!$printerId || $desde < 1 || $hasta < $desde) {
            Yii::$app->session->setFlash('error', 'Datos inválidos: verifique impresora y rango de IDs.');
            return $this->redirect(['indeximprecion']);
        }

        $printer = Impresora::findOne($printerId);
        if (!$printer) {
            Yii::$app->session->setFlash('error', 'Impresora no encontrada.');
            return $this->redirect(['indeximprecion']);
        }

        $ids = Grumascanmarcacion::find()
            ->where(['between', 'id', $desde, $hasta])
            ->orderBy(['id' => SORT_ASC])
            ->select('id')
            ->column();

        if (empty($ids)) {
            Yii::$app->session->setFlash('warning', "No se encontraron marcaciones entre ID {$desde} y {$hasta}.");
            return $this->redirect(['indeximprecion']);
        }

        $usuario = Yii::$app->user->identity->username ?? 'N/A';
        $esEpl   = ((string)$printer->tipo === 'epl');

        if ($esEpl) {
            $payload = MarcacionStickerPrinter::buildPayload($ids, $usuario, 'epl');
        } else {
            $payload = '';
            foreach (array_chunk($ids, 3) as $chunk) {
                $payload .= MarcacionStickerPrinter::buildPayloadZpl3Up($chunk, $usuario);
            }
        }

        $config = [
            'tipo'    => $printer->tipo,
            'ip'      => $printer->ip      ?? null,
            'puerto'  => $printer->puerto  ?? 9100,
            'recurso' => $printer->recurso ?? null,
        ];

        $resp = $this->enviarImpresora($payload, $config);

        if (($resp['status'] ?? 'error') === 'success') {
            Yii::$app->session->setFlash(
                'success',
                'Re-impresión enviada. Stickers: ' . count($ids)
                    . ' (IDs ' . $desde . '–' . $hasta . ')'
                    . ' | Impresora: ' . ($printer->nombre ?? $printer->ip ?? 'N/A')
            );
        } else {
            Yii::$app->session->setFlash(
                'error',
                'Error re-imprimiendo: ' . ($resp['message'] ?? 'Error desconocido')
            );
        }

        return $this->redirect(['indeximprecion']);
    }

    public function actionUsarSticker()
    {
        $form = new GrumascanMarcacionUseForm();

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {

            $model = Grumascanmarcacion::findOne($form->codigo);

            if (!$model) {
                Yii::$app->session->setFlash('error', 'Sticker no existe.');
                return $this->refresh();
            }

            // Opcional: evitar reuso
            if ($model->idbodega !== null) {
                Yii::$app->session->setFlash('error', 'Sticker ya fue utilizado.');
                return $this->refresh();
            }

            $model->idbodega  = $form->idbodega;
            $model->ubicacion = $form->ubicacion;
            $model->seccion   = $form->seccion;

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Sticker asignado correctamente.');
            } else {
                Yii::$app->session->setFlash('error', 'Error al asignar el sticker.');
            }

            return $this->refresh();
        }

        return $this->render('usar-sticker', [
            'model' => $form,
        ]);
    }

    public function actionMapa()
    {
        $searchModel = new VwGrumascanMapaSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('mapa', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


    public function actionUsarStickerMasivo()
    {
        $form = new GrumascanMarcacionBulkUseForm();

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {

            $db = Yii::$app->db;
            $tx = $db->beginTransaction();

            try {
                $userId = Yii::$app->user->id ?? null;

                $set = [
                    'idbodega'    => (int)$form->idbodega,
                    'ubicacion'   => $form->ubicacion,
                    'seccion'     => $form->seccion,
                    'updated_at'  => new Expression('GETDATE()'),
                    'updated_by'  => $userId,
                ];

                $where = ['between', 'id', (int)$form->desde, (int)$form->hasta];

                // Si NO quiere sobrescribir → solo los que estén sin bodega
                if (!(bool)$form->sobrescribir) {
                    $where = ['and', $where, ['is', 'idbodega', null]];
                }

                $rows = $db->createCommand()
                    ->update('grumascanmarcacion', $set, $where)
                    ->execute();

                $tx->commit();

                if ($rows > 0) {
                    Yii::$app->session->setFlash(
                        'success',
                        "Asignación masiva OK. Registros afectados: {$rows}. Usuario: {$userId}"
                    );
                } else {
                    Yii::$app->session->setFlash(
                        'warning',
                        'No se actualizó ningún registro. Verifique el rango o el estado actual.'
                    );
                }

                return $this->refresh();
            } catch (\Throwable $e) {

                $tx->rollBack();
                Yii::error($e->getMessage(), __METHOD__);

                Yii::$app->session->setFlash(
                    'error',
                    'Error en la asignación masiva: ' . $e->getMessage()
                );

                return $this->refresh();
            }
        }

        return $this->render('usar-sticker-masivo', [
            'model' => $form,
        ]);
    }
}
