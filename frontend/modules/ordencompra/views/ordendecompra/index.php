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
');

$this->registerJsFile(Yii::$app->request->baseUrl.'/js/mainDataModal.js',
['depends' => [\yii\web\JqueryAsset::className()]]
);

use frontend\models\Ordendecompra;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;

use yii\bootstrap4\Modal;
use common\widgets\Alert;

/** @var yii\web\View $this */
/** @var frontend\models\search\OrdendecompraSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Ordenes de compra';
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

<div class="ordendecompra-index">

    <?= Alert::widget() ?>

    <div class="row">

        <div class="col-lg-12 centrar">
            <?php $url = Url::to(['create']); ?>
            
            <p>
            <?= Html::button('Sincronizar Orden de Compra', 
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

        'columns' => [
            [
                'class' => 'kartik\grid\SerialColumn',
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],

            [
                'attribute' => 'id', // Nombre del atributo en el modelo
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],

            [
                'attribute' => 'idCO', // Nombre del atributo en el modelo
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'value' => function ($model){
                    return $model->cO->codigo;
                }
            ],
            [
                'attribute' => 'idTipoDocumento', // Nombre del atributo en el modelo
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'value' => function ($model){
                    return $model->tipoDocumento->codigo;
                }
            ],
            [
                'attribute' => 'consecutivo', // Nombre del atributo en el modelo
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],
            [
                'attribute' => 'fecha', // Nombre del atributo en el modelo
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],
            [
                'attribute' => 'idProveedor', // Nombre del atributo en el modelo
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'label' => 'Proveedor',
                'value' => function ($model){
                    return $model->proveedor->razonSocial;
                }
            ],
            [
                'attribute' => 'sucursalProveedor', // Nombre del atributo en el modelo
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'label' => 'Sucursal',
                'value' => function ($model){
                    return $model->proveedor->sucursal;
                }
            ],
            [
                'attribute' => 'idEstado', // Nombre del atributo en el modelo
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'label' => 'Estado',
                'value' => function ($model){
                    return $model->estadoordencompra->nombre;
                }
            ],
            [
                'attribute' => 'totalCantidadPedida', // Nombre del atributo en el modelo
                'hAlign' => 'right', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
            ],
            [
                'attribute' => 'totalCantidadEntrada', // Nombre del atributo en el modelo
                'hAlign' => 'right', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
            ],
            [
                'attribute' => 'totalCantidadPendiente', // Nombre del atributo en el modelo
                'hAlign' => 'right', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
            ],
            [
                'attribute' => 'nroPaquetes', // Nombre del atributo en el modelo
                'hAlign' => 'right', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
            ],

            /*'id',
            'idCO',
            'idTipoDocumento',
            'consecutivo',
            'fecha',*/
            //'idProveedor',
            //'idEstado',
            //'fechaEntrega',
            //'totalCantidadPedida',
            //'totalCantidadEntrada',
            //'totalCantidadPendiente',
            //'nroPaquetes',
            //'comprador',
            //'nitcomprador',
            //'sucursalProveedor',
            //'idTipoDocumentoEntrada',
            //'idCODocumentoEntrada',
            //'fechaDocumentoEntrada',
            //'consecutivoDocumentoEntrada',
            //'consignacion',
            //'created_at',
            //'created_by',
            //'updated_at',
            //'updated_by',
            [
                'class' => ActionColumn::className(),
                'header'=>'Acción',
                //'headerOptions' => ['width' => '15%'],
                'template' => '{item}',

                'buttons' => [

                    'item' => function ($url, $model) {                                
                        return Html::a('<i class="fa fa-list"></i>',
                                [   '/ordencompra/ordendecompradetalle/index',
                                    'idordencompra' => $model->id
                                ], 
                                [
                                    'title' => 'Ver Items de la Orden de Compra',
                                    'class' => 'btn btn-default btn_detalle',
                                ]
                        );
                    },


                ],

            ],

        ],
    ]); ?>


</div>
