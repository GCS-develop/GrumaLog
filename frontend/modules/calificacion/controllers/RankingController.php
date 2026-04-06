<?php

namespace frontend\modules\calificacion\controllers;

use frontend\models\Calificacionproveedor;
use Yii;
use yii\web\Controller;

class RankingController extends Controller
{
    public function actionIndex()
    {
        $ranking = Calificacionproveedor::ranking();

        return $this->render('index', [
            'ranking' => $ranking,
        ]);
    }

    /**
     * Detalle de todas las OCs calificadas de un proveedor (llamada AJAX o directa).
     */
    public function actionDetalle($id_proveedor)
    {
        $historial = Calificacionproveedor::historialProveedor($id_proveedor);

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('_detalle', ['historial' => $historial]);
        }

        return $this->render('detalle', ['historial' => $historial]);
    }
}
