<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $model frontend\models\ReporteCreditosEmpleadosForm */
/* @var $dataProvider yii\data\SqlDataProvider */
/* @var $total float */

$this->title = 'Reporte Créditos Empleados (Saldos Abiertos)';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="reporte-creditos-empleados-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="well">
        <?php $form = ActiveForm::begin([
            'method' => 'get',
            'action' => ['index'],
            'options' => ['data-pjax' => 1],
        ]); ?>
        <div class="row">
            <div class="col-sm-2">
                <?= $form->field($model, 'fecha_inicio')->input('date') ?>
            </div>
            <div class="col-sm-2">
                <?= $form->field($model, 'fecha_fin')->input('date') ?>
            </div>
            <!-- 🔹 Filtro por Cédula / NIT usando ID_TERCERO -->
    <div class="col-sm-2">
        <?= $form->field($model, 'ID_TERCERO')->textInput([
            'placeholder' => 'Cédula / NIT',
        ]) ?>
    </div>


            <div class="col-sm-6" style="margin-top:25px;">
                <?= Html::submitButton('Filtrar', ['class' => 'btn btn-primary']) ?>
                <?= Html::a('Mes actual', ['index'], ['class' => 'btn btn-default']) ?>
                <?= Html::a(
                    'Descargar Excel',
                    array_merge(['exportar-excel'], Yii::$app->request->get()),
                    [
                        'class' => 'btn btn-success',
                        'style' => 'margin-left:5px;',
                        'data-pjax' => 0,
                    ]
                ) ?>
                <?= Html::a(
                    'Ver Todas las Facturas Crédito',
                    array_merge(
                        ['historial-facturas'],
                        array_filter([
                            'ReporteCreditosHistorialForm[fecha_inicio]' => $model->fecha_inicio,
                            'ReporteCreditosHistorialForm[fecha_fin]'    => $model->fecha_fin,
                            'ReporteCreditosHistorialForm[ID_TERCERO]'   => $model->ID_TERCERO,
                        ])
                    ),
                    [
                        'class'      => 'btn btn-info',
                        'style'      => 'margin-left:5px;',
                        'data-pjax'  => 0,
                        'title'      => 'Ver todas las facturas crédito, pagadas y con saldo pendiente',
                    ]
                ) ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>

    <?php Pjax::begin(); ?>

    <?php
        // 🔹 Total general desde el controlador
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
                'label' => 'Cédula / NIT',
                'contentOptions' => ['style' => 'white-space:nowrap;'],
            ],
            [
                'attribute' => 'RAZON_SOCIAL',
                'label' => 'Razón Social',
            ],
            [
                'attribute' => 'SUCURSAL_CLIENTE',
                'label' => 'Sucursal Cliente',
            ],
            [
                'attribute' => 'CENTRO_OPERACION',
                'label' => 'Centro Operación',
            ],
            [
                'attribute' => 'UNIDAD_DE_NEGOCIO',
                'label' => 'Unidad de Negocio',
            ],
            [
                'attribute' => 'TIPO_DOCUMENTO_CRUCE',
                'label' => 'Tipo Doc. Cruce',
                'contentOptions' => ['style' => 'white-space:nowrap;'],
            ],
            [
                'attribute' => 'NUMERO_DOCUMENTO_CRUCE',
                'label' => 'Número Doc. Cruce',
                'contentOptions' => ['style' => 'white-space:nowrap; font-weight:bold;'],
            ],
            [
                'attribute' => 'NUMERO_CUOTA_CRUCE',
                'label' => 'N° Cuota',
                'contentOptions' => ['style' => 'text-align:center;'],
            ],
            [
                'attribute' => 'CONDICION_PAGO',
                'label' => 'Condición de Pago',
            ],
            [
                'attribute' => 'VALOR',
                'label' => 'Valor',
                'format' => ['decimal', 2],
                'contentOptions' => ['style' => 'text-align:right; white-space:nowrap;'],
                'headerOptions' => ['style' => 'text-align:right;'],
            ],
            [
                'attribute' => 'FECHA',
                'label' => 'Fecha',
                'format' => ['date', 'php:Y-m-d'],
                'contentOptions' => ['style' => 'white-space:nowrap; text-align:center;'],
                'filter' => Html::activeInput('date', $model, 'FECHA', [
                    'class' => 'form-control',
                ]),
            ],
        ],
        'tableOptions' => [
            'class' => 'table table-striped table-bordered table-condensed',
            'style' => 'font-size:13px;',
        ],
        'summary' => 'Mostrando {begin}-{end} de {totalCount} registros',
    ]); ?>

    <?php Pjax::end(); ?>

</div>
