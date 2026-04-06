<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $model frontend\models\ReporteCreditosHistorialForm */
/* @var $dataProvider yii\data\SqlDataProvider */
/* @var $total float */
/* @var $cupoInfo array|null */

$this->title = 'Historial Facturas Crédito Empleados';
$this->params['breadcrumbs'][] = ['label' => 'Reporte Créditos Empleados (Saldos Abiertos)', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Parámetros actuales para pasar al detalle y al back
$currentParams = Yii::$app->request->get();
?>
<div class="reporte-creditos-historial-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="well">
        <?php $form = ActiveForm::begin([
            'method' => 'get',
            'action' => ['historial-facturas'],
            'options' => ['data-pjax' => 0],
        ]); ?>
        <div class="row">
            <div class="col-sm-2">
                <?= $form->field($model, 'fecha_inicio')->input('date') ?>
            </div>
            <div class="col-sm-2">
                <?= $form->field($model, 'fecha_fin')->input('date') ?>
            </div>
            <div class="col-sm-2">
                <?= $form->field($model, 'ID_TERCERO')->textInput(['placeholder' => 'Cédula / NIT']) ?>
            </div>
            <div class="col-sm-6" style="margin-top:25px;">
                <?= Html::submitButton('Filtrar', ['class' => 'btn btn-primary']) ?>
                <?= Html::a('Mes actual', ['historial-facturas'], ['class' => 'btn btn-default']) ?>
                <?= Html::a(
                    '&larr; Volver a Saldos Abiertos',
                    array_merge(
                        ['index'],
                        array_filter([
                            'ReporteCreditosEmpleadosForm[fecha_inicio]' => $model->fecha_inicio,
                            'ReporteCreditosEmpleadosForm[fecha_fin]'    => $model->fecha_fin,
                            'ReporteCreditosEmpleadosForm[ID_TERCERO]'   => $model->ID_TERCERO,
                        ])
                    ),
                    ['class' => 'btn btn-warning', 'style' => 'margin-left:5px;', 'data-pjax' => 0]
                ) ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>

    <?php if (!empty($cupoInfo)): ?>
    <?php
        $cupoCredito    = $cupoInfo['cupo_credito'];
        $saldoPendiente = $cupoInfo['saldo_pendiente'];
        $cupoDisponible = $cupoInfo['cupo_disponible'];
        $pctUsado       = $cupoCredito > 0 ? min(100, round($saldoPendiente / $cupoCredito * 100)) : 0;
        $barColor       = $pctUsado >= 90 ? 'danger' : ($pctUsado >= 60 ? 'warning' : 'success');
        $fmt = Yii::$app->formatter;
    ?>
    <div class="row" style="margin-bottom:15px;">
        <div class="col-sm-4">
            <div class="panel panel-default" style="border-radius:6px;">
                <div class="panel-body text-center">
                    <div style="font-size:12px; color:#888; text-transform:uppercase; letter-spacing:.5px;">Cupo Asignado</div>
                    <div style="font-size:22px; font-weight:bold; color:#337ab7;">
                        $ <?= $fmt->asDecimal($cupoCredito, 0) ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="panel panel-default" style="border-radius:6px;">
                <div class="panel-body text-center">
                    <div style="font-size:12px; color:#888; text-transform:uppercase; letter-spacing:.5px;">Saldo Pendiente Total</div>
                    <div style="font-size:22px; font-weight:bold; color:#d9534f;">
                        $ <?= $fmt->asDecimal($saldoPendiente, 0) ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="panel panel-<?= $barColor ?>" style="border-radius:6px;">
                <div class="panel-body text-center">
                    <div style="font-size:12px; color:#888; text-transform:uppercase; letter-spacing:.5px;">Cupo Disponible</div>
                    <div style="font-size:22px; font-weight:bold; color:<?= $cupoDisponible >= 0 ? '#5cb85c' : '#d9534f' ?>;">
                        $ <?= $fmt->asDecimal($cupoDisponible, 0) ?>
                    </div>
                    <?php if ($cupoCredito > 0): ?>
                    <div class="progress" style="margin:6px 0 0; height:8px;">
                        <div class="progress-bar progress-bar-<?= $barColor ?>"
                             role="progressbar"
                             style="width:<?= $pctUsado ?>%;">
                        </div>
                    </div>
                    <small style="color:#888;"><?= $pctUsado ?>% utilizado</small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php Pjax::begin(); ?>

    <?php
        $totalValor = isset($total)
            ? Yii::$app->formatter->asDecimal($total, 2)
            : Yii::$app->formatter->asDecimal(0, 2);
    ?>
    <div class="well text-right" style="font-size:16px; font-weight:bold; margin-bottom:10px;">
        Total Valor: <?= $totalValor ?>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel'  => $model,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'ID_TERCERO',
                'label'     => 'Cédula / NIT',
                'contentOptions' => ['style' => 'white-space:nowrap;'],
            ],
            [
                'attribute' => 'RAZON_SOCIAL',
                'label'     => 'Razón Social',
            ],
            [
                'attribute' => 'SUCURSAL_CLIENTE',
                'label'     => 'Sucursal',
            ],
            [
                'attribute' => 'TIPO_DOCUMENTO_CRUCE',
                'label'     => 'Tipo Doc.',
                'contentOptions' => ['style' => 'text-align:center; white-space:nowrap;'],
            ],
            [
                'attribute' => 'NUMERO_DOCUMENTO_CRUCE',
                'label'     => 'N° Factura',
                'contentOptions' => ['style' => 'white-space:nowrap; font-weight:bold;'],
            ],
            [
                'attribute' => 'CONDICION_PAGO',
                'label'     => 'Condición Pago',
            ],
            [
                'attribute' => 'FECHA',
                'label'     => 'Fecha',
                'format'    => ['date', 'php:Y-m-d'],
                'contentOptions' => ['style' => 'white-space:nowrap; text-align:center;'],
                'filter' => Html::activeInput('date', $model, 'FECHA', ['class' => 'form-control']),
            ],
            [
                'label'  => 'Cuotas',
                'format' => 'raw',
                'filter' => false,
                'value'  => function ($row) {
                    $total   = (int)$row['TOTAL_CUOTAS'];
                    $pagadas = (int)$row['CUOTAS_PAGADAS'];
                    $pend    = (int)$row['CUOTAS_PENDIENTES'];
                    return "<span style='color:#5cb85c; font-weight:bold;'>{$pagadas}</span>"
                         . "<span style='color:#888;'>/</span>"
                         . "<span style='color:#d9534f; font-weight:bold;'>{$pend}</span>"
                         . "<small style='color:#aaa;'> de {$total}</small>";
                },
                'contentOptions' => ['style' => 'text-align:center; white-space:nowrap;'],
            ],
            [
                'attribute' => 'VALOR_TOTAL',
                'label'     => 'Valor Total',
                'format'    => ['decimal', 2],
                'contentOptions' => ['style' => 'text-align:right; white-space:nowrap;'],
                'headerOptions'  => ['style' => 'text-align:right;'],
                'filter'         => false,
            ],
            [
                'attribute' => 'SALDO_PENDIENTE',
                'label'     => 'Saldo Pendiente',
                'format'    => ['decimal', 2],
                'filter'    => false,
                'contentOptions' => function ($row) {
                    $color = ((float)$row['SALDO_PENDIENTE'] > 0) ? 'color:#d9534f;' : 'color:#5cb85c;';
                    return ['style' => "text-align:right; white-space:nowrap; font-weight:bold; {$color}"];
                },
                'headerOptions' => ['style' => 'text-align:right;'],
            ],
            [
                'attribute' => 'ESTADO',
                'label'     => 'Estado',
                'filter'    => Html::activeDropDownList($model, 'ESTADO', [
                    ''          => 'Todos',
                    'Pendiente' => 'Pendiente',
                    'Pagado'    => 'Pagado',
                ], ['class' => 'form-control']),
                'value'  => function ($row) { return $row['ESTADO']; },
                'contentOptions' => function ($row) {
                    $color = ($row['ESTADO'] === 'Pendiente') ? '#d9534f' : '#5cb85c';
                    return ['style' => "text-align:center; font-weight:bold; color:{$color}; white-space:nowrap;"];
                },
            ],
            [
                'label'          => 'Detalle',
                'format'         => 'raw',
                'filter'         => false,
                'contentOptions' => ['style' => 'text-align:center; white-space:nowrap;'],
                'value'          => function ($row) use ($currentParams) {
                    $url = array_merge(
                        ['detalle-factura'],
                        $currentParams,
                        [
                            'nit'  => $row['ID_TERCERO'],
                            'tipo' => $row['TIPO_DOCUMENTO_CRUCE'],
                            'num'  => $row['NUMERO_DOCUMENTO_CRUCE'],
                        ]
                    );
                    return Html::a(
                        '<span class="glyphicon glyphicon-list-alt"></span> Ver Factura',
                        $url,
                        ['class' => 'btn btn-xs btn-info', 'data-pjax' => 0]
                    );
                },
            ],
        ],
        'tableOptions' => [
            'class' => 'table table-striped table-bordered table-condensed',
            'style' => 'font-size:13px;',
        ],
        'summary' => 'Mostrando {begin}-{end} de {totalCount} facturas',
    ]); ?>

    <?php Pjax::end(); ?>

</div>
