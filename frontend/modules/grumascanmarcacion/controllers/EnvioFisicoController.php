<?php

namespace frontend\modules\grumascanmarcacion\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\data\ArrayDataProvider;
use yii\web\BadRequestHttpException;
use frontend\models\search\GrumascanEnvioFisicoPreviewSearch;
use frontend\models\SiesaConectorDocumento;

class EnvioFisicoController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'roles' => ['@']],
                ],
            ],
        ];
    }

    public function actionPreview()
    {
        $searchModel = new GrumascanEnvioFisicoPreviewSearch();

        $result = $searchModel->preview(Yii::$app->request->queryParams);

        $rows = $result['rows'];
        $totales = $result['totales'];

        if (empty($rows) && !empty(Yii::$app->request->queryParams['GrumascanEnvioFisicoPreviewSearch'])) {
            Yii::$app->session->setFlash(
                'warning',
                'No se encontraron conteos terminados (estado=1) para la bodega y fecha seleccionadas.'
            );
        }

        $dataProvider = new ArrayDataProvider([
            'allModels' => $rows,
            'pagination' => ['pageSize' => 200],
            'sort' => [
                'attributes' => ['item', 'color', 'talla', 'cantidad_unidad', 'cantidad_paquetes'],
                'defaultOrder' => ['item' => SORT_ASC, 'color' => SORT_ASC, 'talla' => SORT_ASC],
            ],
        ]);

        return $this->render('preview', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'totales' => $totales,
        ]);
    }

    public function actionTerminar()
    {
        $request = Yii::$app->request;

        if (!$request->isPost) {
            throw new BadRequestHttpException('Método no permitido.');
        }

        $codigoBodega = trim((string)$request->post('codigoBodega', ''));
        $fechaDesde   = trim((string)$request->post('fechaDesde', ''));
        $consecutivo  = trim((string)$request->post('consecutivo', ''));

        if ($codigoBodega === '' || $fechaDesde === '' || $consecutivo === '') {
            Yii::$app->session->setFlash('error', 'Código bodega, fecha y consecutivo son obligatorios.');
            return $this->redirect([
                'preview',
                'GrumascanEnvioFisicoPreviewSearch[codigoBodega]' => $codigoBodega,
                'GrumascanEnvioFisicoPreviewSearch[fechaDesde]' => $fechaDesde,
            ]);
        }

        try {
            $documentoId = SiesaConectorDocumento::crearDocumentoYMapearDesdePreview(
                $codigoBodega,
                $fechaDesde,
                $consecutivo,
                4
            );

            $envio = \common\components\SiesaTransferenciaFisico::enviarDocumento($documentoId, 4, 'Físico');

            if ($envio['ok']) {
                Yii::$app->session->setFlash(
                    'success',
                    "Mapeo creado y enviado correctamente. Documento ID: {$documentoId}. Consecutivo: {$consecutivo}."
                );
            } else {
                Yii::$app->session->setFlash(
                    'error',
                    "Mapeo creado, pero falló el envío a Siesa. Documento ID: {$documentoId}. HTTP: {$envio['httpCode']}. Revisa 'respuesta' del documento."
                );
            }
        } catch (\Throwable $e) {
            Yii::error("Error Envío Físico (Terminar): " . $e->getMessage(), __METHOD__);
            Yii::$app->session->setFlash('error', $e->getMessage());
        }


        return $this->redirect([
            'preview',
            'GrumascanEnvioFisicoPreviewSearch[codigoBodega]' => $codigoBodega,
            'GrumascanEnvioFisicoPreviewSearch[fechaDesde]' => $fechaDesde,
        ]);
    }
}
