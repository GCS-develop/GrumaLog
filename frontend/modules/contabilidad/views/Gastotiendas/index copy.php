<?php
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;

$this->title = 'Registro de Gastos';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="documento-gasto-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <h3>Historial de Documentos</h3>

    <?php Pjax::begin(); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'tableOptions' => ['class' => 'table table-striped table-bordered'],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn', 'header' => '#'],

            [
                'attribute' => 'F350_ID_CO',
                'label' => 'Centro',
                'value' => function ($model) {
                    return $model['F350_ID_CO'];
                }
            ],
            [
                'attribute' => 'F350_ID_TIPO_DOCTO',
                'label' => 'Tipo',
                'value' => function ($model) {
                    return $model['F350_ID_TIPO_DOCTO'];
                }
            ],
            [
                'attribute' => 'F350_FECHA',
                'label' => 'Fecha',
                'value' => function ($model) {
                    return Yii::$app->formatter->asDate($model['F350_FECHA'], 'php:Y-m-d');
                }
            ],
            [
                'attribute' => 'F350_ID_TERCERO',
                'label' => 'Tercero',
                'value' => function ($model) {
                    return $model['F350_ID_TERCERO'];
                }
            ],
            [
                'attribute' => 'F350_NOTAS',
                'label' => 'Notas',
                'value' => function ($model) {
                    return $model['F350_NOTAS'];
                }
            ],

            // ESTADO: futuro (enviado/no enviado/anulado)
            [
                'attribute' => 'estado',
                'label' => 'Estado',
                'format' => 'raw',
                'value' => function ($model) {
                    return Html::tag('span', 'NO ENVIADO', ['class' => 'badge bg-secondary']);
                }
            ],

            // Botones
            [
                'class' => 'yii\grid\ActionColumn',
                'header' => 'Acciones',
                'template' => '{view} {anular}',
                'buttons' => [
                    'view' => function ($url, $model) {
                        return Html::a(
                            '<i class="fas fa-eye"></i> Ver Detalles',
                            ['view',
                                'F350_ID_CO' => $model['F350_ID_CO'],
                                'F350_ID_TIPO_DOCTO' => $model['F350_ID_TIPO_DOCTO'],
                                'F350_CONSEC_DOCTO' => $model['F350_CONSEC_DOCTO']
                            ],
                            ['class' => 'btn btn-primary btn-sm']
                        );
                    },
                    'anular' => function ($url, $model) {
                        return Html::a(
                            '<i class="fas fa-ban"></i> Anular',
                            ['anular',
                                'F350_ID_CO' => $model['F350_ID_CO'],
                                'F350_ID_TIPO_DOCTO' => $model['F350_ID_TIPO_DOCTO'],
                                'F350_CONSEC_DOCTO' => $model['F350_CONSEC_DOCTO']
                            ],
                            [
                                'class' => 'btn btn-danger btn-sm',
                                'data' => [
                                    'confirm' => '¿Seguro que deseas anular este documento?',
                                    'method' => 'post',
                                ],
                            ]
                        );
                    },
                ]
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>
</div>
