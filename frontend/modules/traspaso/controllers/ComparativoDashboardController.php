<?php

namespace frontend\modules\traspaso\controllers;

use frontend\models\search\ComparativoDashboardSearch;
use yii\web\Controller;

/**
 * Dashboard comparativo OC Entrada → Conteo Logística → Traspasos.
 */
class ComparativoDashboardController extends Controller
{
    public function actionIndex()
    {
        $searchModel = new ComparativoDashboardSearch();
        $params      = \Yii::$app->request->queryParams;

        $comparativo = $searchModel->searchComparativoProveedores($params);
        $proveedores = $searchModel->getProveedores();

        return $this->render('index', [
            'searchModel' => $searchModel,
            'comparativo' => $comparativo,
            'proveedores' => $proveedores,
        ]);
    }

    /**
     * OCs del proveedor en el período vía AJAX (primer nivel de drill-down).
     */
    public function actionOcsProveedor($tercero)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $searchModel = new ComparativoDashboardSearch();
        $params      = \Yii::$app->request->queryParams;

        return $searchModel->searchOcsPorProveedor($params, $tercero);
    }

    /**
     * Items de una OC con comparativo Pedido vs Conteo vía AJAX (segundo nivel).
     */
    public function actionItemsOc($idOc)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $searchModel = new ComparativoDashboardSearch();

        return $searchModel->searchItemsPorOc($idOc);
    }

    /**
     * Devuelve el detalle de traspasos de un item vía AJAX (modal).
     */
    public function actionDetalle($idItem)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $searchModel = new ComparativoDashboardSearch();
        $params      = \Yii::$app->request->queryParams;

        $detalle = $searchModel->searchTraspasoDetalle($params, $idItem);

        return $detalle;
    }

    /**
     * Devuelve la lista de OCs de un item en el período vía AJAX.
     */
    public function actionOcs($idItem)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $searchModel = new ComparativoDashboardSearch();
        $params      = \Yii::$app->request->queryParams;

        return $searchModel->searchOcs($params, $idItem);
    }

    /**
     * Devuelve el detalle de conteos por factura e item (desglose por usuario y SKU).
     */
    public function actionConteoOc($idFactura, $idItem)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $searchModel = new ComparativoDashboardSearch();

        return $searchModel->searchConteoOc($idFactura, $idItem);
    }
}
