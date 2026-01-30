<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\ActiveForm;

/** @var $this yii\web\View */
/** @var $searchModel frontend\models\search\TraspasoAuditDashboardSearch */
$this->title = 'Dashboard Auditoría de Traspasos';
$this->params['breadcrumbs'][] = ['label' => 'Traspaso', 'url' => ['/traspaso/traspaso/index']];
$this->params['breadcrumbs'][] = $this->title;

$fmtHHMM = function (int $seconds): string {
    if ($seconds <= 0) return '—';
    $h = floor($seconds / 3600);
    $m = floor(($seconds % 3600) / 60);
    return sprintf('%02d:%02d', $h, $m);
};

$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js', [
    'depends' => [\yii\web\JqueryAsset::class],
    'position' => \yii\web\View::POS_END
]);
?>

<div class="container-fluid">

    <!-- FILTROS -->
    <div class="card card-outline card-primary mb-4">
        <div class="card-header">
            <h3 class="card-title">Filtros</h3>
        </div>
        <div class="card-body">
            <?php $form = ActiveForm::begin([
                'method' => 'get',
                'action' => Url::to(['/traspaso/traspasoauditdashboard/index']),
                'options' => ['class' => 'row g-3']
            ]); ?>

            <!-- <div class="col-sm-2">
                <?= Html::label('Fecha', 'fecha', ['class' => 'form-label']) ?>
                <?= Html::input('date', 'TraspasoAuditDashboardSearch[fecha]', $searchModel->fecha, ['class' => 'form-control']) ?>
                <div class="form-text">Si usas fecha, ignora desde/hasta.</div>
            </div> -->
            <div class="col-sm-2">
                <?= Html::label('Desde', 'desde', ['class' => 'form-label']) ?>
                <?= Html::input('date', 'TraspasoAuditDashboardSearch[desde]', $searchModel->desde, ['class' => 'form-control']) ?>
            </div>
            <div class="col-sm-2">
                <?= Html::label('Hasta', 'hasta', ['class' => 'form-label']) ?>
                <?= Html::input('date', 'TraspasoAuditDashboardSearch[hasta]', $searchModel->hasta, ['class' => 'form-control']) ?>
            </div>
            <div class="col-sm-2">
                <?= Html::label('Creador (ID)', 'user_id', ['class' => 'form-label']) ?>
                <?= Html::input('number', 'TraspasoAuditDashboardSearch[user_id]', $searchModel->user_id, ['class' => 'form-control', 'min' => 1]) ?>
            </div>
            <div class="col-sm-2">
                <?= Html::label('Bodega Origen', 'bodega_origen_id', ['class' => 'form-label']) ?>
                <?= Html::input('number', 'TraspasoAuditDashboardSearch[bodega_origen_id]', $searchModel->bodega_origen_id, ['class' => 'form-control', 'min' => 1]) ?>
            </div>
            <div class="col-sm-2">
                <?= Html::label('Bodega Destino', 'bodega_destino_id', ['class' => 'form-label']) ?>
                <?= Html::input('number', 'TraspasoAuditDashboardSearch[bodega_destino_id]', $searchModel->bodega_destino_id, ['class' => 'form-control', 'min' => 1]) ?>
            </div>
            <!-- <div class="col-sm-2">
                <?= Html::label('Tipo Doc.', 'tipo_documento', ['class' => 'form-label']) ?>
                <?= Html::input('number', 'TraspasoAuditDashboardSearch[tipo_documento]', $searchModel->tipo_documento, ['class' => 'form-control', 'min' => 1]) ?>
            </div> -->



            <div class="form-row mt-2">
                <div class="form-group col-md-6 pr-md-2">
                    <?= Html::label('Buscar', null, ['class' => 'form-label d-block']) ?>
                    <?= Html::submitButton('Aplicar filtros', ['class' => 'btn btn-primary mr-2']) ?>
                    <?= Html::a('Limpiar', Url::to(['/traspaso/traspasoauditdashboard/index']), ['class' => 'btn btn-outline-secondary']) ?>
                </div>

                <div class="form-group col-md-6 pl-md-2">
                    <?= Html::label('Fuente (panel productividad)', null, ['class' => 'form-label d-block']) ?>
                    <div class="btn-group" role="group">
                        <?php
                        $fuentes = ['detalle' => 'Detalle', 'auditado' => 'Auditado', 'ambos' => 'Ambos'];
                        foreach ($fuentes as $val => $lbl) {
                            $active = ($searchModel->fuente === $val) ? 'active' : '';
                            echo Html::a(
                                $lbl,
                                Url::current(['TraspasoAuditDashboardSearch[fuente]' => $val]),
                                ['class' => 'btn btn-outline-primary ' . $active]
                            );
                        }
                        ?>
                    </div>
                </div>
            </div>
            
            <?php ActiveForm::end(); ?>
        </div>
    </div>

    <!-- PANEL: Productividad (fuente) -->
    <div class="row">
        <div class="col-md-3">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3><?= number_format($totalUnidades) ?></h3>
                    <p>Total Unidades (<?= Html::encode($searchModel->fuente) ?>)</p>
                </div>
                <div class="icon"><i class="fas fa-cubes"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3><?= $fmtHHMM((int)$totalSegundosPersona) ?></h3>
                    <p>Duración acumulada (hh:mm)</p>
                </div>
                <div class="icon"><i class="fas fa-hourglass-half"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3><?= number_format((float)$promVel, 2) ?></h3>
                    <p>Velocidad promedio (UPH)</p>
                </div>
                <div class="icon"><i class="fas fa-tachometer-alt"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3><?= Html::encode(ucfirst($searchModel->fuente)) ?></h3>
                    <p>Fuente seleccionada</p>
                </div>
                <div class="icon"><i class="fas fa-database"></i></div>
            </div>
        </div>
    </div>

    <div class="card card-outline card-primary mb-4">
        <div class="card-header">
            <h3 class="card-title">Comparativo por usuario (<?= Html::encode($searchModel->fuente) ?>)</h3>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-8"><canvas id="chartFuente"></canvas></div>
                <div class="col-md-4">
                    <p class="text-muted mb-1"><strong>Qué es:</strong></p>
                    <ul class="mb-2">
                        <li><strong>Barras:</strong> Unidades</li>
                        <li><strong>Línea:</strong> UPH</li>
                    </ul>
                </div>
            </div>
            <?= GridView::widget([
                'dataProvider' => $perUserProvider,
                'summary' => false,
                'tableOptions' => ['class' => 'table table-sm table-striped table-bordered'],
                'columns' => [
                    ['attribute' => 'username', 'label' => 'Usuario'],
                    [
                        'attribute' => 'total_unidades',
                        'label'     => 'Total Unidades',
                        'format'    => ['decimal', 0],
                        'contentOptions' => ['class' => 'text-right'],
                    ],
                    [
                        'attribute' => 'dur_seconds',
                        'label'     => 'Duración (hh:mm)',
                        'value'     => fn($m) => $fmtHHMM((int)($m['dur_seconds'] ?? 0)),
                    ],
                    [
                        'attribute' => 'velocidad_uph',
                        'label'     => 'Velocidad (UPH)',
                        'format'    => ['decimal', 2],
                        'contentOptions' => ['class' => 'text-right'],
                    ],
                ],
            ]) ?>
        </div>
    </div>

    <!-- COMPARATIVO POR CREADOR (ERRORES ATRIBUIDOS AL PADRE) -->
    <div class="card card-outline card-danger mb-4">
        <div class="card-header">
            <h3 class="card-title">Errores atribuidos al creador (por traspaso padre)</h3>
        </div>
        <div class="card-body">
            <?= GridView::widget([
                'dataProvider' => $creadoresProvider,
                'summary' => false,
                'tableOptions' => ['class' => 'table table-sm table-striped table-bordered'],
                'columns' => [
                    ['attribute' => 'creator', 'label' => 'Creador'],
                    [
                        'attribute' => 'unidades_detalle',
                        'label'     => 'Unidades (Detalle)',
                        'format'    => 'integer',
                        'contentOptions' => ['class' => 'text-right'],
                    ],
                    [
                        'attribute' => 'dur_detalle',
                        'label'     => 'Duración (hh:mm)',
                        'value'     => fn($m) => $fmtHHMM((int)($m['dur_detalle'] ?? 0)),
                    ],
                    [
                        'attribute' => 'uph_detalle',
                        'label'     => 'UPH (Detalle)',
                        'format'    => ['decimal', 2],
                        'contentOptions' => ['class' => 'text-right'],
                    ],
                    [
                        'attribute' => 'sobrantes',
                        'label'     => 'Sobrantes (Δ&gt;0)',
                        'format'    => 'integer',
                        'contentOptions' => ['class' => 'text-right text-success'],
                    ],
                    [
                        'attribute' => 'faltantes',
                        'label'     => 'Faltantes (Δ&lt;0)',
                        'format'    => 'integer',
                        'contentOptions' => ['class' => 'text-right text-danger'],
                    ],
                    [
                        'attribute' => 'total_abs',
                        'label'     => 'Total |Δ|',
                        'format'    => 'integer',
                        'contentOptions' => ['class' => 'text-right'],
                    ],
                    [
                        'attribute' => 'neto',
                        'label'     => 'Neto',
                        'format'    => 'integer',
                        'contentOptions' => ['class' => 'text-right'],
                    ],
                ],
            ]) ?>
        </div>
    </div>

    <!-- PRODUCTIVIDAD DE AUDITORES -->
    <div class="card card-outline card-success mb-4">
        <div class="card-header">
            <h3 class="card-title">Productividad de auditores</h3>
        </div>
        <div class="card-body">
            <?= GridView::widget([
                'dataProvider' => $auditoresProvider,
                'summary' => false,
                'tableOptions' => ['class' => 'table table-sm table-striped table-bordered'],
                'columns' => [
                    ['attribute' => 'auditor', 'label' => 'Auditor'],
                    [
                        'attribute' => 'unidades_auditadas',
                        'label'     => 'Unidades',
                        'format'    => 'integer',
                        'contentOptions' => ['class' => 'text-right'],
                    ],
                    [
                        'attribute' => 'dur_auditado',
                        'label'     => 'Duración (hh:mm)',
                        'value'     => fn($m) => $fmtHHMM((int)($m['dur_auditado'] ?? 0)),
                    ],
                    [
                        'attribute' => 'uph_auditado',
                        'label'     => 'UPH',
                        'format'    => ['decimal', 2],
                        'contentOptions' => ['class' => 'text-right'],
                    ],
                ],
            ]) ?>
        </div>
    </div>

    <!-- KPIs NOVEDADES (global por usuario creador) -->
    <div class="row">
        <div class="col-md-3">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3><?= number_format($totSobrantes) ?></h3>
                    <p>Sobrantes (Δ &gt; 0)</p>
                </div>
                <div class="icon"><i class="fas fa-plus-circle"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3><?= number_format($totFaltantes) ?></h3>
                    <p>Faltantes (Δ &lt; 0)</p>
                </div>
                <div class="icon"><i class="fas fa-minus-circle"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3><?= number_format($totAbs) ?></h3>
                    <p>Total Absoluto |Δ|</p>
                </div>
                <div class="icon"><i class="fas fa-exchange-alt"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3><?= number_format($totNeto) ?></h3>
                    <p>Neto (Sobrantes - Faltantes)</p>
                </div>
                <div class="icon"><i class="fas fa-equals"></i></div>
            </div>
        </div>
    </div>

    <!-- NOVEDADES POR USUARIO (CREADOR) -->
    <div class="card card-outline card-danger mb-4">
        <div class="card-header d-flex justify-content-between">
            <h3 class="card-title">Novedades por usuario creador</h3>
            <span class="text-muted small">Δ = (Auditado) − (Detalle)</span>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-8"><canvas id="chartNovUsuarios"></canvas></div>
                <div class="col-md-4">
                    <p class="text-muted mb-1">Barras: Sobrantes / Faltantes</p>
                    <p class="text-muted mb-1">Línea: Total |Δ|</p>
                </div>
            </div>

            <?= GridView::widget([
                'dataProvider' => $novedadesUsuarioProvider,
                'summary' => false,
                'tableOptions' => ['class' => 'table table-sm table-striped table-bordered'],
                'columns' => [
                    ['attribute' => 'username', 'label' => 'Usuario'],
                    [
                        'attribute' => 'sobrantes',
                        'format' => 'integer',
                        'contentOptions' => ['class' => 'text-right text-success'],
                    ],
                    [
                        'attribute' => 'faltantes',
                        'format' => 'integer',
                        'contentOptions' => ['class' => 'text-right text-danger'],
                    ],
                    [
                        'attribute' => 'neto',
                        'format' => 'integer',
                        'contentOptions' => ['class' => 'text-right'],
                    ],
                    [
                        'attribute' => 'total_abs',
                        'label' => 'Total |Δ|',
                        'format' => 'integer',
                        'contentOptions' => ['class' => 'text-right'],
                    ],
                    [
                        'attribute' => 'traspasos_con_novedad',
                        'label' => '#Traspasos con novedad',
                        'format' => 'integer',
                        'contentOptions' => ['class' => 'text-right'],
                    ],
                    [
                        'attribute' => 'items_con_novedad',
                        'label' => '#Ítems con novedad',
                        'format' => 'integer',
                        'contentOptions' => ['class' => 'text-right'],
                    ],
                ],
            ]) ?>
        </div>
    </div>

    <!-- NOVEDADES POR TRASPASO -->
    <div class="card card-outline card-warning mb-4">
        <div class="card-header">
            <h3 class="card-title">Novedades por traspaso</h3>
        </div>
        <div class="card-body">
            <?= GridView::widget([
                'dataProvider' => $novedadesPorTraspasoProvider,
                'summary' => false,
                'tableOptions' => ['class' => 'table table-sm table-striped table-bordered'],
                'columns' => [
                    ['attribute' => 'idTraspaso', 'label' => 'Traspaso'],
                    [
                        'attribute' => 'usuarios_involucrados',
                        'label'     => '#Auditores',
                        'format'    => 'integer',
                        'contentOptions' => ['class' => 'text-right'],
                    ],
                    [
                        'attribute' => 'sobrantes',
                        'format'    => 'integer',
                        'contentOptions' => ['class' => 'text-right text-success'],
                    ],
                    [
                        'attribute' => 'faltantes',
                        'format'    => 'integer',
                        'contentOptions' => ['class' => 'text-right text-danger'],
                    ],
                    [
                        'attribute' => 'total_abs',
                        'label'     => 'Total |Δ|',
                        'format'    => 'integer',
                        'contentOptions' => ['class' => 'text-right'],
                    ],
                    [
                        'attribute' => 'neto',
                        'format'    => 'integer',
                        'contentOptions' => ['class' => 'text-right'],
                    ],
                ],
            ]) ?>
        </div>
    </div>

    <!-- DETALLE DE NOVEDADES -->
    <div class="card card-outline card-secondary mb-5">
        <div class="card-header">
            <h3 class="card-title">Detalle de novedades (Creador – Traspaso – Ítem)</h3>
        </div>
        <div class="card-body">
            <?= GridView::widget([
                'dataProvider' => $novedadesDetalleProvider,
                'summary' => false,
                'tableOptions' => ['class' => 'table table-sm table-striped table-bordered'],
                'columns' => [
                    ['attribute' => 'username',   'label' => 'Creador'],
                    ['attribute' => 'idTraspaso', 'label' => 'Traspaso'],
                    ['attribute' => 'idItem',     'label' => 'Ítem'],
                    [
                        'attribute' => 'cant_detalle',
                        'label'     => 'Detalle',
                        'format'    => 'integer',
                        'contentOptions' => ['class' => 'text-right'],
                    ],
                    [
                        'attribute' => 'cant_auditado',
                        'label'     => 'Auditado',
                        'format'    => 'integer',
                        'contentOptions' => ['class' => 'text-right'],
                    ],
                    [
                        'attribute' => 'delta',
                        'label'     => 'Δ (Aud - Det)',
                        'format'    => 'integer',
                        'contentOptions' => function ($m) {
                            $cls = ((int)($m['delta'] ?? 0)) >= 0 ? 'text-success' : 'text-danger';
                            return ['class' => 'text-right ' . $cls];
                        },
                    ],
                ],
            ]) ?>
        </div>
    </div>

</div>

<?php
// ==== JS para gráficas ====
$labelsJson       = json_encode(array_values($labels));
$dataUnidadesJson = json_encode(array_values($dataUnidades));
$dataVelJson      = json_encode(array_values($dataVel));

$labelsNovJson     = json_encode(array_values($labelsNov));
$dataSobrantesJson = json_encode(array_values($dataSobrantes));
$dataFaltantesJson = json_encode(array_values($dataFaltantes));
$dataTotalAbsJson  = json_encode(array_values($dataTotalAbs));

$js = <<<JS
(function(){
  if (typeof Chart === 'undefined') return;

  // Panel fuente
  var c1 = document.getElementById('chartFuente');
  if (c1) {
    new Chart(c1.getContext('2d'), {
      type: 'bar',
      data: {
        labels: $labelsJson,
        datasets: [
          { type: 'bar',  label: 'Unidades', data: $dataUnidadesJson },
          { type: 'line', label: 'Velocidad (UPH)', data: $dataVelJson, yAxisID: 'y1' }
        ]
      },
      options: {
        responsive: true,
        interaction: { mode: 'index', intersect: false },
        scales: {
          y:  { beginAtZero: true, title: { display: true, text: 'Unidades' } },
          y1: { beginAtZero: true, position: 'right', title: { display: true, text: 'UPH' }, grid: { drawOnChartArea: false } }
        }
      }
    });
  }

  // Novedades por usuario creador
  var c2 = document.getElementById('chartNovUsuarios');
  if (c2) {
    new Chart(c2.getContext('2d'), {
      type: 'bar',
      data: {
        labels: $labelsNovJson,
        datasets: [
          { type: 'bar',  label: 'Sobrantes', data: $dataSobrantesJson },
          { type: 'bar',  label: 'Faltantes', data: $dataFaltantesJson },
          { type: 'line', label: 'Total |Δ|', data: $dataTotalAbsJson, yAxisID: 'y1' }
        ]
      },
      options: {
        responsive: true,
        interaction: { mode: 'index', intersect: false },
        scales: {
          y:  { beginAtZero: true, title: { display: true, text: 'Unidades' } },
          y1: { beginAtZero: true, position: 'right', title: { display: true, text: '|Δ|' }, grid: { drawOnChartArea: false } }
        }
      }
    });
  }
})();
JS;

$this->registerJs($js, \yii\web\View::POS_END);
?>