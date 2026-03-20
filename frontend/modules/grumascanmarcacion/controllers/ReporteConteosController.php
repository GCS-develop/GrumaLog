<?php

namespace frontend\modules\grumascanmarcacion\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use frontend\models\search\GrumascanReporteSearch;
use frontend\models\GrumascanSnapshot;

class ReporteConteosController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    // tu modal normalmente hace GET por URL en "value"
                    'marcaciones-detalle' => ['GET'],
                ],
            ],
        ];
    }

    public function actionConsolidado()
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['/site/login']);
        }

        $searchModel = new GrumascanReporteSearch();
        $result = $searchModel->searchConsolidadoConteoVsInventario(Yii::$app->request->queryParams);

        // Snapshots disponibles para la bodega seleccionada (para el selector en la vista)
        $snapshotsDisponibles = [];
        $tienda = trim((string)($searchModel->tienda ?? ''));
        if ($tienda !== '') {
            if (ctype_digit($tienda)) {
                $tienda = str_pad($tienda, 3, '0', STR_PAD_LEFT);
            }
            $snapshotsDisponibles = GrumascanSnapshot::find()
                ->where(['codigoBodega' => $tienda])
                ->orderBy(['fecha_snapshot' => SORT_DESC])
                ->all();
        }

        return $this->render('consolidado', [
            'searchModel'          => $searchModel,
            'dataProvider'         => $result['dataProvider'],
            'resumenTiendas'       => $result['resumenTiendas'],
            'snapshotsDisponibles' => $snapshotsDisponibles,
        ]);
    }

    /**
     * ✅ Contenido del modal (HTML)
     * Muestra: estado del conteo, marcación (sección/ubicación), fecha del conteo,
     * y si existe "manual" en grumascanconteomanual (resumen).
     */
    public function actionMarcacionesDetalle(
        $codigoBodega = null,
        $item = null,
        $idColor = null,
        $idTalla = null,
        $created_from = null,
        $created_to = null
    ) {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['/site/login']);
        }

        $search = new \frontend\models\search\GrumascanReporteSearch();

        try {
            $rows = $search->fetchMarcacionesDetalleHtml([
                'codigoBodega' => (string)$codigoBodega,
                'item' => (string)$item,
                'idColor' => (string)$idColor,
                'idTalla' => (string)$idTalla,
                'created_from' => (string)$created_from,
                'created_to' => (string)$created_to,
            ]);

            return $this->renderAjax('_marcaciones_detalle', [
                'rows' => $rows,
                'codigoBodega' => (string)$codigoBodega,
                'item' => (string)$item,
                'idColor' => (string)$idColor,
                'idTalla' => (string)$idTalla,
                'created_from' => (string)$created_from,
                'created_to' => (string)$created_to,
            ]);
        } catch (\Throwable $e) {

            // 🔥 Log real para que lo veas en runtime/logs/app.log
            Yii::error([
                'msg' => 'Error modal marcaciones-detalle',
                'err' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'params' => compact('codigoBodega', 'item', 'idColor', 'idTalla', 'created_from', 'created_to'),
            ], 'grumascan');

            // ✅ No 500: devuelve HTML con el error dentro del modal
            return $this->renderAjax('_marcaciones_error', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
