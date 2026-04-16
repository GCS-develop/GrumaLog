<?php

$this->registerCss('
    .mi-gridview {
        font-size: 11px; /* Ajusta el tamaño de la fuente según sea necesario */
        /* Otros estilos CSS según sea necesario */
    }

    .btn-create {
        width: 300px;
    }
    
    .centrar {
        text-align: center;
    }

    .izquierda {
        text-align: left;
    }

    .derecha {
        text-align: right;
    }

    .horizontal-line {
        border: none;
        border-top: 1px solid #ccc; /* Color y grosor de la línea */
        margin: 10px 0; /* Espacio alrededor de la línea */
    }

    .titulo {
        color: black;
        font-weight: bold;
    }
');

$this->registerJsFile(Yii::$app->request->baseUrl.'/js/mainDataModal.js',
['depends' => [\yii\web\JqueryAsset::className()]]
);

use frontend\models\Pedidoordendecompra;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;

use yii\bootstrap4\Modal;
use common\widgets\Alert;

/** @var yii\web\View $this */
/** @var frontend\models\search\PedidoordendecompraSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Pedido - Ordenes de Compra';
$this->params['breadcrumbs'][] = ['label' => 'Pedidos', 'url' => ['pedido/index']];
$this->params['breadcrumbs'][] = $this->title;

?>

<?php
    Modal::begin([                
        'title'=>'<h4>Datos Básicos Orden de Compra</h4>',
        'id'=>'modaldata',
        'size'=>'modal-lg',
        'options' => [
            'tabindex' => false  // Importante para que funcione el Select
        ]
    ]);
        
    echo "<div id='modalContentData'></div>";
        
    Modal::end(); 
?>

<div class="card">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <?= Html::label('No. Pedido', 'idpedido', ['class' => 'form-label']) ?>
                <?= Html::textInput(
                    null,                         // sin nombre, no afecta formularios
                    $modelpedido->id ?? '',
                    ['class' => 'form-control', 'readonly' => true, 'id' => 'idpedido']
                    ) 
                ?>
            </div>

            <div class="col-md-4">
                <?= Html::label('Fecha Pedido', 'fecha', ['class' => 'form-label']) ?>
                <?= Html::textInput(
                    null,                         // sin nombre, no afecta formularios
                    $modelpedido->fecha ?? '',
                    ['class' => 'form-control', 'readonly' => true, 'id' => 'fecha']
                    ) 
                ?>
            </div>

            <div class="col-md-4">
                <?= Html::label('Creado por', 'creado-por', ['class' => 'form-label']) ?>
                <?= Html::textInput(
                    null,                         // sin nombre, no afecta formularios
                    $modelpedido->creadopor->username ?? '',
                    ['class' => 'form-control', 'readonly' => true, 'id' => 'creado-por']
                    ) 
                ?>
            </div>
        </div>

        <div class="row g-3 mt-3">
            <div class="col-md-4">
                <?= Html::label('Observaciones', 'observaciones', ['class' => 'form-label']) ?>
                <?= Html::textInput(
                    null,                         // sin nombre, no afecta formularios
                    $modelpedido->observaciones ?? '',
                    ['class' => 'form-control', 'readonly' => true, 'id' => 'observaciones']
                    ) 
                ?>
            </div>
        </div>
    </div>
</div>

<div class="pedidoordendecompra-index">

    <?= Alert::widget() ?>

    <!--
    <h1><?= Html::encode($this->title) ?></h1>
    -->

    <div class="row">

        <div class="col-lg-12 centrar">
            <?php $url = Url::to(['create', 'idpedido' => $modelpedido->id]); ?>
            
            <p>
            <?= Html::button('Registrar Orden de Compra', 
                        ['value'=>  $url, 'class' => 'btn btn-success btn-lg btn-create', 'id'=>'modalButtonCreate']) 
            ?>
            </p>
        </div>

    </div>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,

        'summary' => 'Mostrando {begin} - {end} de {totalCount} resultados',
		'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
		'options' => [
			'class' => 'mi-gridview', // Agrega una clase CSS a la tabla generada por el GridView
		],
        'showPageSummary' => true,
        'columns' => [
            [
                'class' => 'kartik\grid\SerialColumn',
                'hAlign' => 'center', 
                'vAlign' => 'middle', 
            ],

            // 'id',

            [
                'attribute' => 'tipoDocumento',
                'value' => 'ordencompra.tipoDocumento.codigo',
                'label' => 'Serie',
                'hAlign' => 'center', 
                'vAlign' => 'middle', 
            ],

            [
                'attribute' => 'consecutivo',
                'value' => 'ordencompra.consecutivo',
                'hAlign' => 'center', 
                'vAlign' => 'middle', 
            ],

            [
                'attribute' => 'proveedor_razonSocial',
                'label' => 'Proveedor',
                'value' => 'ordencompra.proveedor.razonSocial',
                'hAlign' => 'left', 
                'vAlign' => 'middle', 
            ],

            [
                'attribute' => 'nombreArchivo',
                'hAlign' => 'left', 
                'vAlign' => 'middle', 
            ],

            [
                'attribute' => 'nroItems', // Nombre del atributo en el modelo
                'hAlign' => 'right', 
                'vAlign' => 'middle', 
                'format' => ['decimal', 0], 
                'filter' => '',
                'label' => 'No Items Pedido',
                'pageSummary' => true,
            ],

            [
                'attribute' => 'totalUnidades', // Nombre del atributo en el modelo
                'hAlign' => 'right', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'filter' => '',
                'label' => 'Total UND Pedido',
                'pageSummary' => true,
            ],

            [
                'attribute' => 'nroPaquetes', // Nombre del atributo en el modelo
                'value'  => 'ordencompra.nroPaquetes',
                'hAlign' => 'right', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'filter' => '',
                'label' => 'No. Paquetes OC',
                'pageSummary' => true,
            ],

            [
                'attribute' => 'totalCantidadPedida', // Nombre del atributo en el modelo
                'value'  => 'ordencompra.totalCantidadPedida',
                'hAlign' => 'right', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'filter' => '',
                'label' => 'Cantidad Pedida OC',
                'pageSummary' => true,
            ],

            //'created_at',
            //'created_by',
            //'updated_at',
            //'updated_by',
            [
                'class' => ActionColumn::className(),
                'header' => 'Acción',
                //'headerOptions' => ['width' => '15%'],
                'template' => '{detalle} {view} {delete}',

                'buttons' => [

                    'importardataxls' => function ($url, $model) {
                        $t = Url::to([
                            'importardataxls',
                            'id' => $model->id,
                        ]);

                        return Html::button('<i class="fa fa-file-excel"></i>', [
                            'value' => $t,
                            'title' => 'Subir Archivo Distribución',
                            'class' => 'btn btn-default btn_upload',
                        ]);
                    },

                    'detalle' => function ($url, $model) {                                
                        return Html::a('<i class="fa fa-home"></i>',
                                [   '/distribucion/pedidodetalle/index', 
                                    'idpedido' => $model->idPedido,
                                    'idordencompra' => $model->idOrdenCompra 
                                ], 
                                [
                                    'title' => 'Relación Distribución Orden de Compra - Bodega',
                                    'class' => 'btn btn-default btn_ver_detalle_oc',
                                ]
                        );
                    },

                    'view' => function ($url, $model) {                                
                        return Html::a('<i class="fa fa-list"></i>',
                                [   '/distribucion/pedidoordendecompraitem/index', 
                                    'idpedido' => $model->idPedido,
                                    'idordencompra' => $model->idOrdenCompra 
                                ], 
                                [
                                    'title' => 'Relación Distribución Orden de Compra - Item',
                                    'class' => 'btn btn-default btn_ver_pedido_oc_item',
                                ]
                        );
                    },

                    'delete' => function ($url, $model) {
                        $oc = $model->ordencompra;
                        $label = $oc
                            ? ($oc->cO->codigo ?? '') . '-' . ($oc->tipoDocumento->codigo ?? '') . '-' . $oc->consecutivo
                            : $model->idOrdenCompra;
                        return Html::a(
                            '<i class="fa fa-trash"></i>',
                            ['delete', 'id' => $model->id],
                            [
                                'class' => 'btn btn-danger btn-sm',
                                'title' => 'Eliminar OC del pedido',
                                'data'  => [
                                    'confirm' => '¿Eliminar la OC ' . $label . ' de este pedido? Esta acción quedará registrada en el log de auditoría.',
                                    'method'  => 'post',
                                ],
                            ]
                        );
                    },

                ],
            ],

        ],
    ]); ?>


</div>
