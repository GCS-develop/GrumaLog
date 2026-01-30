<?php

namespace frontend\modules\traspaso\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use frontend\models\search\TraspasoAuditDashboardSearch;

class TraspasoauditdashboardController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only'  => ['index'],
                'rules' => [
                    ['allow' => true, 'roles' => ['@']],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $searchModel = new TraspasoAuditDashboardSearch();
        $searchModel->load(Yii::$app->request->queryParams);

        // Panel “fuente” (productividad por operador de filas: detalle/auditado/ambos)
        $perUserProvider = $searchModel->searchPerUser();

        // Comparativo por CREADOR (errores atribuidos al padre)
        $creadoresProvider = $searchModel->searchComparativoCreadores();

        // Productividad de AUDITORES
        $auditoresProvider = $searchModel->searchAuditoresProductividad();

        // Novedades
        $novedadesUsuarioProvider     = $searchModel->searchNovedadesPerUser();
        $novedadesDetalleProvider     = $searchModel->searchNovedadesDetalle();
        $novedadesPorTraspasoProvider = $searchModel->searchNovedadesPorTraspaso();

        // KPIs de “fuente”
        $rowsFuente = $perUserProvider->getModels();
        $totalUnidades = 0;
        $totalSegundosPersona = 0;
        $labels = $dataUnidades = $dataVel = [];
        foreach ($rowsFuente as $r) {
            $totalUnidades += (int)($r['total_unidades'] ?? 0);
            $totalSegundosPersona += (int)($r['dur_seconds'] ?? 0);
            $labels[]       = (string)($r['username'] ?? '—');
            $dataUnidades[] = (int)($r['total_unidades'] ?? 0);
            $dataVel[]      = (float)($r['velocidad_uph'] ?? 0);
        }
        $promVel = count($rowsFuente) ? round(array_sum($dataVel) / max(1, count($rowsFuente)), 2) : 0;

        // KPIs de novedades (globales por usuario creador)
        $rowsNov = $novedadesUsuarioProvider->getModels();
        $labelsNov = $dataSobrantes = $dataFaltantes = $dataNeto = $dataTotalAbs = [];
        foreach ($rowsNov as $r) {
            $labelsNov[]     = (string)($r['username'] ?? '—');
            $dataSobrantes[] = (int)($r['sobrantes'] ?? 0);
            $dataFaltantes[] = (int)($r['faltantes'] ?? 0);
            $dataNeto[]      = (int)($r['neto'] ?? 0);
            $dataTotalAbs[]  = (int)($r['total_abs'] ?? 0);
        }
        $totSobrantes = array_sum($dataSobrantes);
        $totFaltantes = array_sum($dataFaltantes);
        $totNeto      = array_sum($dataNeto);
        $totAbs       = array_sum($dataTotalAbs);

        return $this->render('index', [
            'searchModel'                  => $searchModel,

            // Panel fuente
            'perUserProvider'              => $perUserProvider,
            'totalUnidades'                => $totalUnidades,
            'totalSegundosPersona'         => $totalSegundosPersona,
            'promVel'                      => $promVel,
            'labels'                       => $labels,
            'dataUnidades'                 => $dataUnidades,
            'dataVel'                      => $dataVel,

            // Comparativo creadores
            'creadoresProvider'            => $creadoresProvider,

            // Productividad auditores
            'auditoresProvider'            => $auditoresProvider,

            // Novedades
            'novedadesUsuarioProvider'     => $novedadesUsuarioProvider,
            'novedadesDetalleProvider'     => $novedadesDetalleProvider,
            'novedadesPorTraspasoProvider' => $novedadesPorTraspasoProvider,

            // Series novedades
            'labelsNov'                    => $labelsNov,
            'dataSobrantes'                => $dataSobrantes,
            'dataFaltantes'                => $dataFaltantes,
            'dataNeto'                     => $dataNeto,
            'dataTotalAbs'                 => $dataTotalAbs,
            'totSobrantes'                 => $totSobrantes,
            'totFaltantes'                 => $totFaltantes,
            'totNeto'                      => $totNeto,
            'totAbs'                       => $totAbs,
        ]);
    }
}
