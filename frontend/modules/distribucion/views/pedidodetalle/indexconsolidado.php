<?php

use kartik\grid\GridView;
use yii\helpers\Html;
use yii\bootstrap4\Modal;
use common\widgets\Alert;
use yii\grid\ActionColumn;
use yii\helpers\Url;

/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var \frontend\models\search\PedidodetalleSearch $searchModel */

$this->title = 'Consolidado por tienda - Pedido ' . $idpedido . ($idordencompra ? " (OC $idordencompra)" : '');
$this->params['breadcrumbs'][] = $this->title;

$this->registerJsFile(
    Yii::$app->request->baseUrl . '/js/mainDataModal.js',
    ['depends' => [\yii\web\JqueryAsset::className()]]
);
$this->registerCss("
    .mi-gridview { font-size: 12px; }
    .badge-ok { background:#28a745; color:#fff; padding:3px 8px; border-radius:10px; font-weight:600; }
    .badge-nok { background:#dc3545; color:#fff; padding:3px 8px; border-radius:10px; font-weight:600; }
");
?>

<?php
Modal::begin([
    'title' => '<h4>Detalle</h4>',
    'id' => 'modaldata',
    'size' => 'modal-xl',
    'options' => [
        'tabindex' => false  // Importante para que funcione el Select
    ]
]);

echo "<div id='modalContentData'></div>";

Modal::end();
?>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel'  => $searchModel,              // si muestras filtros por tienda / consecutivo
    'summary'      => 'Mostrando {count} tiendas',
    'showPageSummary' => true,
    'options' => ['class' => 'mi-gridview'],
    'rowOptions' => function ($model) {
        return ((int)$model->completo === 0) ? ['style' => 'background:#ffecec;'] : [];
    },
    'columns' => array_filter([

        // ID Pedido
        [
            'attribute' => 'idPedido',
            'label'     => 'Pedido',
            'hAlign'    => 'center',
            'vAlign'    => 'middle',
            'width'     => '90px',
            'filter'    => true,
        ],

        // OC (consecutivo)
        [
            'attribute' => 'oc_consecutivo',
            'label'     => 'OC',
            'hAlign'    => 'center',
            'vAlign'    => 'middle',
            'width'     => '90px',
            'filter'    => true,
        ],
        // item
        [
            'attribute' => 'item_item',
            'label'     => 'Item',
            'hAlign'    => 'center',
            'vAlign'    => 'middle',
            'width'     => '110px',
            'filter'    => true,
        ],
        // Si activas $agruparPorOC en el controlador, añade esta columna:
        isset($agruparPorOC) && $agruparPorOC ? [
            'attribute' => 'idOrdenCompra',
            'label' => 'OC',
            'hAlign' => 'center',
            'vAlign' => 'middle',
        ] : null,

        [
            'attribute' => 'bodega_codigo',
            'label' => 'Cod. Tienda',
            'value' => function ($m) {
                return $m->bodega ? $m->bodega->codigo : null;
            },
            'hAlign' => 'center',
            'vAlign' => 'middle',
            'filter' => true,
        ],
        [
            'attribute' => 'bodega_nombre',
            'label' => 'Tienda',
            'value' => function ($m) {
                return $m->bodega ? $m->bodega->nombre : null;
            },
            'filter' => true,
        ],
        [
            'attribute' => 'total_unidades',
            'label' => 'Unidades Pedidas',
            'hAlign' => 'right',
            'vAlign' => 'middle',
            'format' => ['decimal', 0],
            'pageSummary' => true,
            'filter' => false,
        ],
        [
            'attribute' => 'total_recibidas',
            'label' => 'Unidades Recibidas',
            'hAlign' => 'right',
            'vAlign' => 'middle',
            'format' => ['decimal', 0],
            'pageSummary' => true,
            'filter' => false,
        ],
        [
            'label' => '% Avance',
            'hAlign' => 'right',
            'format' => 'raw',
            'value' => function ($m) {
                $ped = (float)$m->total_unidades;
                $rec = (float)$m->total_recibidas;
                return $ped > 0 ? round(($rec / $ped) * 100) . '%' : '0%';
            },
            'pageSummary' => function ($summary, $data, $widget) {
                $tp = 0.0;
                $tr = 0.0;
                // ⬇️ la línea clave
                foreach ($widget->grid->dataProvider->getModels() as $model) {
                    $tp += (float)$model->total_unidades;
                    $tr += (float)$model->total_recibidas;
                }
                return $tp > 0 ? round(($tr / $tp) * 100) . '%' : '0%';
            },
        ],


        [
            'attribute' => 'completo',
            'label' => 'Estado',
            'format' => 'raw',
            'hAlign' => 'center',
            'value' => fn($m) => ((int)$m->completo === 1)
                ? '<span class="badge-ok">Completo</span>'
                : '<span class="badge-nok">Incompleto</span>',
            'filter' => [
                1 => 'Completo',
                0 => 'Incompleto',
            ],
        ],
        [
            'class' => \kartik\grid\ActionColumn::class,
            'header' => 'Acción',
            'template' => '{detalle}',
            'hAlign' => 'center',
            'vAlign' => 'middle',
            'buttons' => [
                'detalle' => function ($url, $model) {
                    $idPedido  = $model->idPedido;
                    $bodegaCod = $model->bodega_codigo;
                    $item      = $model->item_item ?? '';
                    $oc        = $model->oc_consecutivo ?? '';

                    $detalleUrl = \yii\helpers\Url::to([
                        '/distribucion/pedidodetalle/indexdetalledistribucion',
                        'PedidodetalleSearch[idPedido]'          => $idPedido,
                        'PedidodetalleSearch[oc_consecutivo]'    => $oc,
                        'PedidodetalleSearch[bodega_codigo]'     => $bodegaCod,
                        'PedidodetalleSearch[bodega_nombre]'     => '',
                        'PedidodetalleSearch[item_numero]'       => $item,
                        'PedidodetalleSearch[item_codigobarras]' => '',
                        'PedidodetalleSearch[color_codigo]'      => '',
                        'PedidodetalleSearch[talla_codigo]'      => '',
                    ]);

                    if ((int)$model->completo === 0) {
                        return \yii\helpers\Html::button('<i class="fa fa-eye"></i>', [
                            'class' => 'btn btn-default btn_view', // <- tu JS la escucha
                            'value' => $detalleUrl,                // <- ¡clave! mainDataModal lee 'value'
                            'title' => 'Ver detalle desglosado',
                            'data-pjax' => 0,                      // evita que PJAX intercepte, por si acaso
                        ]);
                    }
                    return '-';
                },
            ],
        ],




    ]),
]); ?>