<?php

namespace frontend\modules\traspaso\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use frontend\models\search\TraspasoDashboardSearch;

class TraspasodashboardController extends Controller
{
    public function behaviors()
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

    public function actionIndex()
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        $searchModel = new TraspasoDashboardSearch();
        $searchModel->load(Yii::$app->request->queryParams);

        // Provider agregado por usuario
        $perUserProvider = $searchModel->searchPerUser(Yii::$app->request->queryParams);

        // KPIs con unidades EQUIVALENTES
        $rows = $perUserProvider->getModels();
        $totalUnidadesEq = 0.0;
        $totalUnidades   = 0.0;
        $totalRegistros  = 0;
        $totalSegundosPersona = 0;
        $velocidades = [];

        foreach ($rows as $r) {
            $totalUnidadesEq += (float)($r['total_unidades_eq'] ?? 0);
            $totalUnidades   += (float)($r['total_unidades'] ?? 0);
            $totalRegistros  += (int)($r['total_registros'] ?? 0);

            $dur = (int)($r['dur_seconds'] ?? 0);
            if ($dur > 0) $totalSegundosPersona += $dur;

            $v = isset($r['velocidad_uph']) ? (float)$r['velocidad_uph'] : null;
            if ($v !== null) $velocidades[] = $v;
        }

        $velocidadGrupo = ($totalSegundosPersona > 0)
            ? round($totalUnidadesEq * 3600 / $totalSegundosPersona, 2)
            : 0.0;

        // Datos para la gráfica
        $labels       = array_map(fn($r) => $r['username'] ?? ('user#' . ($r['created_by'] ?? '')), $rows);
        $dataUnidades = array_map(fn($r) => (float)($r['total_unidades_eq'] ?? 0), $rows); // equivalentes
        $dataRegs     = array_map(fn($r) => (int)  ($r['total_registros']   ?? 0), $rows);
        $dataVel      = array_map(fn($r) => (float)($r['velocidad_uph']     ?? 0), $rows);

        return $this->render('index', [
            'searchModel'           => $searchModel,
            'perUserProvider'       => $perUserProvider,
            'totalUnidadesEq'       => $totalUnidadesEq,
            'totalUnidades'         => $totalUnidades,
            'totalRegistros'        => $totalRegistros,
            'totalSegundosPersona'  => $totalSegundosPersona,
            'velocidadGrupo'        => $velocidadGrupo,
            'velMax'                => !empty($velocidades) ? max($velocidades) : null,
            'velMin'                => !empty($velocidades) ? min($velocidades) : null,
            'labels'                => $labels,
            'dataUnidades'          => $dataUnidades,
            'dataRegs'              => $dataRegs,
            'dataVel'               => $dataVel,
        ]);
    }
}
