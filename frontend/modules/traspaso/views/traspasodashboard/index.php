<?php

/** @var $this yii\web\View */
/** @var $searchModel app\models\search\TraspasoDashboardSearch */
/** @var $perUserProvider yii\data\ActiveDataProvider */
/** @var $totalUnidadesEq float */
/** @var $totalUnidades float */
/** @var $totalRegistros int */
/** @var $totalSegundosPersona int */
/** @var $velocidadGrupo float */
/** @var $velMax float|null */
/** @var $velMin float|null */
/** @var $labels array */
/** @var $dataUnidades array */  // equivalentes
/** @var $dataRegs array */
/** @var $dataVel array */
/** @var $dataHoras array */

use frontend\models\Bodegas;
use frontend\models\Usertraspaso;
use kartik\date\DatePicker;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\grid\GridView;
use kartik\select2\Select2;

$this->title = 'Dashboard de Traspasos';
?>
<div class="container-fluid py-3">

    <div class="card mb-3 border-0 shadow-sm card-body py-3">
        <?php $form = ActiveForm::begin(['method' => 'get']); ?>

        <div class="row align-items-end">

            <div class="col-12 col-md-2">
                <?=
                $form->field($searchModel, 'desde')->widget(DatePicker::className(), [
                    'name' => 'desde',
                    'language' => 'es',
                    'options' => [
                        'placeholder' => 'Fecha Cita Desde ...',
                        'value' => $searchModel->desde ?? date('Y-m-01'), // <-- aquí se establece por defecto
                        'disabled' => false
                    ],
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format' => 'yyyy-mm-dd',
                        'todayHighlight' => false
                    ]
                ])
                ?>
            </div>

            <div class="col-12 col-md-2">
                <?=
                $form->field($searchModel, 'hasta')->widget(DatePicker::className(), [
                    'name' => 'hasta',
                    'language' => 'es',
                    'options' => [
                        'placeholder' => 'Fecha Cita hasta ...',
                        'value' => $searchModel->hasta ?? date('Y-m-01'), // <-- aquí se establece por defecto
                        'disabled' => false
                    ],
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format' => 'yyyy-mm-dd',
                        'todayHighlight' => false
                    ]
                ])

                ?>
            </div>

            <div class="col-6 col-md-1">
                <?= $form->field($searchModel, 'hora_desde')
                    ->input('time', [
                        'value' => $searchModel->hora_desde ?? '00:00'
                    ])->label('Hora desde') ?>
            </div>

            <div class="col-6 col-md-1">
                <?= $form->field($searchModel, 'hora_hasta')
                    ->input('time', [
                        'value' => $searchModel->hora_hasta ?? '23:59'
                    ])->label('Hora hasta') ?>
            </div>

            <div class="col-12 col-md-3">
                <?= $form->field($searchModel, 'bodega_codigo', [
                    'template' => "{label}\n{input}",
                    'labelOptions' => ['class' => 'form-label fw-semibold ']
                ])->widget(\kartik\select2\Select2::classname(), [
                    'data' => \frontend\models\Bodegas::getListaData(),
                    'options' => ['placeholder' => 'Bodega origen...', 'multiple' => true],
                    'pluginOptions' => ['allowClear' => true],
                    'size' => \kartik\select2\Select2::SMALL,
                ]); ?>
            </div>

            <!-- <div class="col-12 col-md-3 mt-5">
                <?= $form->field($searchModel, 'bodega_destino_id', [
                    'template' => "{label}\n{input}",
                    'labelOptions' => ['class' => 'form-label fw-semibold ']
                ])->widget(\kartik\select2\Select2::classname(), [
                    'data' => \frontend\models\Bodegas::getListaData(),
                    'options' => ['placeholder' => 'Bodega destino...', 'multiple' => true],
                    'pluginOptions' => ['allowClear' => true],
                    'size' => \kartik\select2\Select2::SMALL,
                ]); ?>
            </div> -->

            <!-- <div class="col-12 col-md-2">
                <?php
                echo $form->field($searchModel, 'user_id')->label('Usuario')
                    ->dropDownList(
                        Usertraspaso::getListaDataUsertraspaso(),
                        [
                            'prompt' => ' Seleccionar usuario ... ',
                            'id' => 'created_by',
                        ]
                    );
                ?>
                <?= Html::label('Usuario', 'user_id', ['class' => 'form-label fw-semibold mb-1']) ?>
                <?= Html::input('number', 'TraspasoDashboardSearch[user_id]', $searchModel->user_id, [
                    'class' => 'form-control form-control-sm',
                    'min' => 1,
                    'placeholder' => 'ID'
                ]) ?>
            </div> -->

        </div>

        <div class="col-12 text-center mt-2">
            <button class="btn btn-primary btn-lg px-4">
                <i class="bi bi-funnel me-1"></i> Filtrar
            </button>
        </div>

        <?php ActiveForm::end(); ?>
    </div>



    <div class="row g-3 mb-3">
        <div class="col-12 col-md-3">
            <div class="card kpi-card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">Unidades (equiv.)</div>
                    <div class="display-6 mb-1" id="kpi-unidades"><?= number_format($totalUnidadesEq, 2, ',', '.') ?></div>
                    <div class="text-muted small">Suma cantidad × equivalencia</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Registros</div>
                    <div class="display-6" id="kpi-registros"><?= number_format($totalRegistros, 0, ',', '.') ?></div>
                    <div class="text-muted small">Líneas del rango</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Velocidad del grupo</div>
                    <div class="display-6" id="kpi-velocidad"><?= number_format($velocidadGrupo, 2, ',', '.') ?> UPH</div>
                    <div class="text-muted small">Basada en unidades (equiv.)</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Horas-persona</div>
                    <div class="display-6" id="kpi-horas"><?= number_format($totalSegundosPersona / 3600, 2, ',', '.') ?></div>
                    <div class="text-muted small">Σ duración por usuario</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                <div>
                    <h5 class="card-title m-0">Comparativo por usuario</h5>
                    <div class="text-muted small">Unidades (equiv.), Registros y UPH</div>
                </div>
            </div>
            <canvas id="chartUsuarios" height="120"></canvas>
            <div class="text-center">
                <button id="btnResetChart" type="button" class="btn btn-outline-secondary btn-sm">
                    Mostrar todos
                </button>
            </div>
        </div>
    </div>

    <?php
    // resumen de duración total en hh:mm usando $totalSegundosPersona
    $__totS = (int)$totalSegundosPersona;
    $__hh   = floor($__totS / 3600);
    $__mm   = floor(($__totS % 3600) / 60);
    $__durSummaryHHMM = sprintf('%02d:%02d', $__hh, $__mm);
    ?>
    <?php
    $numUsers = (int)$perUserProvider->totalCount;

    $avgReg = $numUsers > 0 ? ($totalRegistros / $numUsers) : 0;
    $avgUnidEq = $numUsers > 0 ? ($totalUnidadesEq / $numUsers) : 0;

    // Promedio de UPH por usuario usando el arreglo ya pasado a la vista
    $sumVel = 0;
    $cntVel = 0;
    foreach ($dataVel as $v) {
        if ($v !== null) {
            $sumVel += (float)$v;
            $cntVel++;
        }
    }
    $avgUPH = $cntVel > 0 ? $sumVel / $cntVel : 0;
    ?>


    <?= GridView::widget([
        'dataProvider'     => $perUserProvider,
        'filterModel'      => $searchModel,
        'showPageSummary'  => true,   // fila de TOTALES
        'showFooter'       => true,   // fila de PROMEDIOS
        'toolbar'          => [],     // o false
        'export'           => false,
        'toggleData'       => false,
        'columns' => [
            [
                'class' => 'kartik\grid\SerialColumn',
                'header' => '#',
                'width' => '36px',
                'contentOptions' => ['class' => 'text-center'],
                'headerOptions'  => ['class' => 'text-center'],
            ],

            [
                'attribute'   => 'username',
                'label'       => 'Usuario',
                // Fila de totales:
                'pageSummary' => 'Totales',
                'pageSummaryOptions' => ['class' => 'fw-bold'],
                // Fila de promedios:
                'footer'      => 'Promedios',
                'footerOptions' => ['class' => 'fw-bold'],
            ],
            [
                'attribute'        => 'total_registros',
                'label'            => 'Registros',
                'format'           => ['decimal', 0],
                'contentOptions'   => ['class' => 'text-end'],
                'headerOptions'    => ['class' => 'text-end'],
                // Totales
                'pageSummary'      => true,
                'pageSummaryFunc'  => \kartik\grid\GridView::F_SUM,
                // Promedios
                'footer'           => Yii::$app->formatter->asDecimal($avgReg, 0),
                'footerOptions'    => ['class' => 'text-end'],
            ],
            [
                'attribute'        => 'total_unidades_eq',
                'label'            => 'Unidades (equiv.)',
                'format'           => ['decimal', 2],
                'contentOptions'   => ['class' => 'text-end'],
                'headerOptions'    => ['class' => 'text-end'],
                // Totales
                'pageSummary'      => true,
                'pageSummaryFunc'  => \kartik\grid\GridView::F_SUM,
                // Promedios
                'footer'           => Yii::$app->formatter->asDecimal($avgUnidEq, 2),
                'footerOptions'    => ['class' => 'text-end'],
            ],
            [
                'attribute'      => 'dur_seconds',
                'label'          => 'Duración (hh:mm)',
                'value' => function ($r) {
                    $s = (int)($r['dur_seconds'] ?? 0);
                    if ($s <= 0) return '—';
                    $hh = floor($s / 3600);
                    $mm = floor(($s % 3600) / 60);
                    return sprintf('%02d:%02d', $hh, $mm);
                },
                'contentOptions'    => ['class' => 'text-end'],
                'headerOptions'     => ['class' => 'text-end'],
                // Totales (string ya formateado):
                'pageSummary'       => $__durSummaryHHMM,
                'pageSummaryOptions' => ['class' => 'text-end fw-bold'],
                'format'            => 'text',
                // Promedios de duración (opcional): hh:mm por usuario
                'footer' => (function () use ($perUserProvider) {
                    $rows = $perUserProvider->getModels();
                    $num = count($rows);
                    if ($num === 0) return '—';
                    $sum = 0;
                    foreach ($rows as $r) {
                        $sum += (int)($r['dur_seconds'] ?? 0);
                    }
                    $avg = (int)round($sum / $num);
                    $hh = floor($avg / 3600);
                    $mm = floor(($avg % 3600) / 60);
                    return sprintf('%02d:%02d', $hh, $mm);
                })(),
                'footerOptions' => ['class' => 'text-end'],
            ],
            [
                'label'            => 'Velocidad (UPH)',
                'attribute'        => 'velocidad_uph',
                'value' => function ($r) {
                    return isset($r['velocidad_uph']) ? (float)$r['velocidad_uph'] : null; // número crudo
                },
                'contentOptions'   => ['class' => 'text-end'],
                'headerOptions'    => ['class' => 'text-end'],

                // TOTAlES (fila de pageSummary): muestra la velocidad del grupo (no promedio simple)
                // 👇 usa el valor calculado en el controlador, NO uses F_AVG aquí
                'pageSummary'      => $velocidadGrupo,
                'pageSummaryFunc'  => false,           // desactiva agregación automática
                'format'           => ['decimal', 2],  // se formatea como número

                // PROMEDIOS (footer): promedio simple de UPH por usuario
                'footer'           => Yii::$app->formatter->asDecimal($avgUPH, 2),
                'footerOptions'    => ['class' => 'text-end'],
            ],

        ],
        'hover'      => true,
        'condensed'  => false,
        'responsive' => true,
        'tableOptions'     => ['class' => 'table table-bordered table-striped align-middle'], // filas más altas
        'pjax'       => true,
        'panel' => [
            'type'    => 'primary',
            'heading' => 'Detalle por usuario (' . $perUserProvider->getTotalCount() . ' usuarios)',
            'before'  => false,   // sin barra superior
            'after'   => false,   // sin barra inferior
        ],
    ]) ?>



</div>
<?php
// Pasa los datos al JS (quedan en window.dashboardData)
$this->registerJsVar('dashboardData', [
    'labels'   => array_values($labels),
    'unidades' => array_values($dataUnidades),
    'regs'     => array_values($dataRegs),
    'vel'      => array_values($dataVel),
    'horas'    => array_values($dataHoras),
]);

// Carga Chart.js local (sin CDN)
$this->registerJsFile('@web/js/chart.umd.min.js', [
    'position' => \yii\web\View::POS_END,
]);

// Carga el JS del dashboard
$this->registerJsFile('@web/js/traspaso_dashboard.js', [
    'position' => \yii\web\View::POS_END,
    'depends'  => [\yii\web\YiiAsset::class], // asegura que Yii ya esté cargado
]);

$this->registerCssFile('@web/css/traspasodashboard.css', [
    'position' => \yii\web\View::POS_END,
    'depends'  => [\yii\web\YiiAsset::class], // asegura que Yii ya esté cargado
]);
?>