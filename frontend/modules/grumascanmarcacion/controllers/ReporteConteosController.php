<?php

namespace frontend\modules\grumascanmarcacion\controllers;

use Yii;
use yii\web\Controller;
use frontend\models\search\GrumascanReporteSearch;

class ReporteConteosController extends Controller
{
    public function actionConsolidado()
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['/site/login']);
        }

        $searchModel = new GrumascanReporteSearch();
        $result = $searchModel->searchConsolidadoConteoVsInventario(Yii::$app->request->queryParams);

        return $this->render('consolidado', [
            'searchModel' => $searchModel,
            'dataProvider' => $result['dataProvider'],
            'resumenTiendas' => $result['resumenTiendas'],
        ]);
    }
}
