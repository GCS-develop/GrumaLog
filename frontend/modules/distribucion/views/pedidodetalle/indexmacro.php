<?php

use frontend\models\Bodegas;
use frontend\models\Pedido;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\select2\Select2;

/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var frontend\models\Search\PedidodetalleSearch $searchModel */

$this->title = 'Pedidos por Tienda (Macro)';
$this->params['breadcrumbs'][] = $this->title;
?>


<div class="pedidos-macro">
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel'  => $searchModel,
        'hover'        => true,
        'resizableColumns' => true,
        'floatHeader'  => true,
        'toolbar' => false,
        'summary'      => 'Mostrando {count} tiendas',
        'showPageSummary' => true,
        'panel' => [
            'type' => 'primary',
            'heading' => 'Resumen Pedido × Tienda',
        ],
        'rowOptions' => function ($model, $key, $index, $grid) {
            static $acumulado = 0;
            static $grupo = 0;

            $acumulado += (float)$model->total_unidades;

            // cada 3800 unidades, cambia de grupo
            if ($acumulado > ($grupo + 1) * 3800) {
                $grupo++;
            }

            // alterna colores por grupo
            $colores = [
                '#f8f9fa', // gris claro
                '#fff3e0', // naranja claro
                '#e3f2fd', // azul muy suave
                '#fce4ec', // rosado
                '#f1f8e9', // verde suave
            ];

            $color = $colores[$grupo % count($colores)];

            return ['style' => "background-color: {$color};"];
        },

        'columns' => [
            [
                'class' => 'kartik\grid\SerialColumn',
                'header' => '#',
                'contentOptions' => ['class' => 'centrar'],
                'headerOptions'  => ['class' => 'centrar', 'style' => 'width:60px'],
            ],

            [
                'attribute' => 'idPedido',
                'width' => '90px',
                'filter' => Pedido::getListaData(),
                'contentOptions' => ['class' => 'centrar'],
                'headerOptions'  => ['class' => 'centrar'],
            ],
            [
                'attribute' => 'bodega_codigo',
                'label' => 'Tienda',
                'format' => 'raw',
                'value' => function ($m) {
                    $url = Url::to([
                        'index-consolidado',
                        'idpedido' => $m->idPedido,
                        'PedidodetalleSearch[bodega_codigo]' => $m->bodega_codigo,
                    ]);
                    $texto = Html::tag('strong', $m->bodega_codigo) . ' - ' . Html::encode($m->bodega_nombre);
                    return Html::a($texto, $url, ['data-pjax' => 0, 'title' => 'Ver consolidado de esta tienda']);
                },
                'filterType' => GridView::FILTER_SELECT2,
                'filter' => Bodegas::getListaData(), // ⚠️ asegúrate de que coincida con cómo filtras (id vs código)
                'filterWidgetOptions' => [
                    'options' => [
                        'placeholder' => 'Filtrar tiendas...',
                        'multiple' => true,
                        'style' => 'width:500px', // ⬅️ ancho fijo del control
                    ],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'closeOnSelect' => false,
                        'dropdownAutoWidth' => true,
                        // 'width' => 'style',       // ⬅️ respeta el width del style
                    ],
                ],
                'headerOptions'  => ['style' => 'width:520px'], // ⬅️ reserva espacio de header
                'contentOptions' => ['class' => 'izquierda'],
            ],



            [
                'attribute' => 'total_unidades',
                'label'     => 'Unidades',
                'format'    => ['decimal', 0],
                'contentOptions' => ['class' => 'derecha'],
                'headerOptions'  => ['style' => 'width:120px'],
                'filter' => false,
                'pageSummary' => true,
            ],
            [
                'attribute' => 'total_recibidas',
                'label'     => 'Recibidas',
                'format'    => ['decimal', 0],
                'contentOptions' => ['class' => 'derecha'],
                'headerOptions'  => ['style' => 'width:120px'],
                'filter' => false,
                'pageSummary' => true,
            ],
            [
                'label'  => '% Recibido',
                'value'  => function ($m) {
                    $t = (float)$m->total_unidades;
                    $r = (float)$m->total_recibidas;
                    if ($t <= 0) return '0%';
                    return round(($r * 100) / $t, 1) . '%';
                },
                'contentOptions' => ['class' => 'derecha'],
                'headerOptions'  => ['style' => 'width:120px'],
            ],
        ],
    ]); ?>
</div>