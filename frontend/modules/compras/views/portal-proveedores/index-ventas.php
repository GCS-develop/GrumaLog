<?php

use yii\helpers\Html;
use kartik\grid\GridView;

$this->title = 'Consulta de Ventas por Proveedor';

$this->registerCss('
    .mi-gridview { font-size: 14px; }
    .btn-create  { width: 300px; }
    .centrar     { text-align: center; }
    .izquierda   { text-align: left; }
    .derecha     { text-align: right; }
    .horizontal-line { border: none; border-top: 1px solid #ccc; margin: 10px 0; }
    .titulonombre { color: black; font-weight: bold; font-size: 20px; }
');
?>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel'  => $searchModel,
    'summary'      => 'Mostrando {begin} - {end} de {totalCount} resultados',
    'formatter'    => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
    'options'      => ['class' => 'mi-gridview'],
    'columns'      => [
        'razonSocial',
        'codigo',
        [
            'class'    => 'yii\grid\ActionColumn',
            'template' => '{consultar}',
            'buttons'  => [
                'consultar' => function ($url, $model) {
                    return Html::a('Consultar', [
                        'ventas-fecha',
                        'codigo'      => $model['codigo'],
                        'razonSocial' => $model['razonSocial'],
                    ], ['class' => 'btn btn-primary btn-sm']);
                },
            ],
        ],
    ],
]); ?>
