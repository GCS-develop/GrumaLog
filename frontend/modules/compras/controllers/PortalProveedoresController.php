<?php

namespace frontend\modules\compras\controllers;

use Yii;
use yii\web\Controller;
use frontend\modules\compras\models\PortalProveedorListSearch;
use frontend\modules\compras\models\PortalVentasSearch;
use frontend\modules\compras\models\PortalExistenciasSearch;

class PortalProveedoresController extends Controller
{
    public function actionVentasDiario()
    {
        if (Yii::$app->user->isGuest) return $this->goHome();

        $searchModel  = new PortalProveedorListSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index-ventas', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionVentasFecha($codigo, $razonSocial, $fechaDesde = null, $fechaHasta = null)
    {
        if (Yii::$app->user->isGuest) return $this->goHome();

        if (Yii::$app->request->isPost) {
            $post       = Yii::$app->request->post();
            $fechaDesde = $post['fechaDesde'] ?? null;
            $fechaHasta = $post['fechaHasta'] ?? null;
            return $this->redirect([
                'ventas-fecha',
                'codigo'      => $codigo,
                'razonSocial' => $razonSocial,
                'fechaDesde'  => $fechaDesde,
                'fechaHasta'  => $fechaHasta,
            ]);
        }

        if ($fechaDesde && $fechaHasta) {
            $searchModel  = new PortalVentasSearch();
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $codigo, $fechaDesde, $fechaHasta);

            return $this->render('ventas-resultado', [
                'searchModel'  => $searchModel,
                'dataProvider' => $dataProvider,
                'codigo'       => $codigo,
                'razonSocial'  => $razonSocial,
                'fechaDesde'   => $fechaDesde,
                'fechaHasta'   => $fechaHasta,
            ]);
        }

        return $this->render('_form-fecha', [
            'codigo'      => $codigo,
            'razonSocial' => $razonSocial,
        ]);
    }

    public function actionExistenciaGeneral()
    {
        if (Yii::$app->user->isGuest) return $this->goHome();

        $searchModel  = new PortalProveedorListSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index-existencia', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionExistenciaResultado($codigo, $razonSocial)
    {
        if (Yii::$app->user->isGuest) return $this->goHome();

        $searchModel  = new PortalExistenciasSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $codigo);

        return $this->render('existencia-resultado', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
            'codigo'       => $codigo,
            'razonSocial'  => $razonSocial,
            'tipo'         => 'general',
        ]);
    }
}
