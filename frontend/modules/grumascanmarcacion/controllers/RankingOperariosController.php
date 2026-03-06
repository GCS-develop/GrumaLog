<?php

namespace frontend\modules\grumascanmarcacion\controllers;

use Yii;
use yii\web\Controller;
use frontend\models\search\GrumascanRankingOperariosSearch;

class RankingOperariosController extends Controller
{
    public function actionIndex()
    {
        $searchModel = new GrumascanRankingOperariosSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        // IMPORTANTÍSIMO: el chart SIEMPRE con números crudos (no formateados)
        $rows = $dataProvider->getModels();

        $chartLabels = [];
        $chartUnidades = [];
        $chartPaquetes = [];

        foreach ($rows as $r) {
            $chartLabels[] = (string)($r['username'] ?? '');

            // Blindaje: si llega "3.439,00" o "3439.00" lo convertimos a float bien
            $chartUnidades[] = $this->toFloat($r['unidades_reales'] ?? 0);
            $chartPaquetes[] = $this->toFloat($r['paquetes'] ?? 0);
        }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'chartLabels' => $chartLabels,
            'chartUnidades' => $chartUnidades,
            'chartPaquetes' => $chartPaquetes,
        ]);
    }

    private function toFloat($value): float
    {
        if ($value === null) return 0.0;

        // Si ya es numérico, listo
        if (is_int($value) || is_float($value)) {
            return (float)$value;
        }

        $s = trim((string)$value);
        if ($s === '') return 0.0;

        // Caso formato es: "3.439,00" -> "3439.00"
        // Quitamos separadores de miles "." y cambiamos coma por punto
        // OJO: si viene "3439.00" esto no lo daña (no tiene coma)
        if (preg_match('/^\d{1,3}(\.\d{3})*(,\d+)?$/', $s)) {
            $s = str_replace('.', '', $s);
            $s = str_replace(',', '.', $s);
        } else {
            // Limpieza defensiva
            $s = str_replace(' ', '', $s);
        }

        return (float)$s;
    }
}
