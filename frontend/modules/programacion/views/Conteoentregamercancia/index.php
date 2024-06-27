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

use frontend\models\Conteoentregamercancia;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use kartik\detail\DetailView;

use yii\bootstrap4\Modal;
use common\widgets\Alert;

/** @var yii\web\View $this */
/** @var frontend\models\search\ConteoentregamercanciaSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Conteo';
$this->params['breadcrumbs'][] = ['label' => 'Programación Recepción Mercancia', 'url' => ['/programacion/programacionentregamercancia/view']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php
    Modal::begin([                
        'title'=>'<h4>Referencias Orden de Compra</h4>',
        'id'=>'modaldata',
        'size'=>'modal-lg',
        'options' => [
            'tabindex' => false  // Importante para que funcione el Select
        ]
    ]);
        
    echo "<div id='modalContentData'></div>";
        
    Modal::end(); 
?>

<?php
$attributes = [
    [
        'group'=>true,
        'label'=>'SECCIÓN 1: Información Agendamiento',
        'rowOptions'=>['class'=>'table-info']
    ],
    [
        'columns' => [
            [
                'attribute'=>'id', 
                'label'=>'Radicado',
                'displayOnly'=>true,
                'labelColOptions'=>['style'=>'width:10%'],
                'valueColOptions'=>['style'=>'width:10%'],
            ],
            [
                'attribute'=>'fechaCita', 
                'format'=>'date',
                'type'=>DetailView::INPUT_DATE,
                'widgetOptions' => [
                    'pluginOptions'=>['format'=>'yyyy-mm-dd']
                ],
                'labelColOptions'=>['style'=>'width:10%'],
                'valueColOptions'=>['style'=>'width:10%']
            ],
            [
                'attribute'=>'idOrdenCompra', 
                'format'=>'raw',
                'label' => 'Orden de Compra',
                'widgetOptions' => [
                    'pluginOptions'=>['format'=>'yyyy-mm-dd']
                ],
                'value' => $modelagendaentrega->ordenCompra ? $modelagendaentrega->ordenCompra->cO->codigo . '-' 
                                                            . $modelagendaentrega->ordenCompra->tipoDocumento->codigo . '-' 
                                                            . $modelagendaentrega->ordenCompra->consecutivo : '-',
                'labelColOptions'=>['style'=>'width:10%'],
                'valueColOptions'=>['style'=>'width:10%']
            ],
            [
                'attribute'=>'categoria', 
                'label'=>'Categoría',
                'displayOnly'=>true,
                'labelColOptions'=>['style'=>'width:10%'],
                'valueColOptions'=>['style'=>'width:10%'],
                'value' => $modelagendaentrega->categoria->nombre,
            ],

        ],
    ],

    [
        'columns' => [
            [
                'attribute'=>'unidades', 
                'displayOnly'=>true,
                'label' => 'Unidades',
                'labelColOptions'=>['style'=>'width:5%'],
                'valueColOptions'=>['style'=>'width:10%'],
                'format'=>['decimal', 0],
                'format'=>'raw', 
            ],
            [
                'attribute'=>'numeroCajas', 
                'displayOnly'=>true,
                'label' => 'No. Cajas',
                'labelColOptions'=>['style'=>'width:5%'],
                'valueColOptions'=>['style'=>'width:10%'],
                'format'=>['decimal', 0],
                'format'=>'raw', 
            ],
            [
                'attribute'=>'idTransportadora',
                'labelColOptions'=>['style'=>'width:5%'],
                'valueColOptions'=>['style'=>'width:20%'],
                'value' => $modelagendaentrega->transportadora ? $modelagendaentrega->transportadora->nombre : '-',
            ],
            [
                'attribute'=>'numeroGuia',
                'label' => 'Guía',
                'labelColOptions'=>['style'=>'width:5%'],
                'valueColOptions'=>['style'=>'width:10%'],
            ],
        ]

    ],

    [
        'columns' => [
            [
                'attribute'=>'contacto',
                'label' => 'Contacto',
                'labelColOptions'=>['style'=>'width:5%'],
                'valueColOptions'=>['style'=>'width:25%'],
            ],
            [
                'attribute'=>'fechaContacto', 
                'format'=>'date',
                'type'=>DetailView::INPUT_DATE,
                'widgetOptions' => [
                    'pluginOptions'=>['format'=>'yyyy-mm-dd']
                ],
                'labelColOptions'=>['style'=>'width:15%'],
                'valueColOptions'=>['style'=>'width:10%']
            ],
            [
                'attribute'=>'observacion',
                'labelColOptions'=>['style'=>'width:5%'],
                'valueColOptions'=>['style'=>'width:30%'],
            ],
        ],
        
        'columns' => [
            [
                'attribute'=>'idOrdenCompra', 
                'label' => 'Fecha Orden de Compra',
                'format'=>'date',
                'type'=>DetailView::INPUT_DATE,
                'widgetOptions' => [
                    'pluginOptions'=>['format'=>'yyyy-mm-dd']
                ],
                'labelColOptions'=>['style'=>'width:15%'],
                'valueColOptions'=>['style'=>'width:10%'],
                'value' => $modelagendaentrega->ordenCompra ? $modelagendaentrega->ordenCompra->fecha : '-',
            ],
            [
                'attribute'=>'idOrdenCompra',
                'label' => 'Nit Proveedor',
                'format'=>['decimal', 0],
                'labelColOptions'=>['style'=>'width:5%'],
                'valueColOptions'=>['style'=>'width:10%'],
                'value' => $modelagendaentrega->ordenCompra->proveedor ? $modelagendaentrega->ordenCompra->proveedor->nit : '-',
            ],
            [
                'attribute'=>'idOrdenCompra',
                'label' => 'Razón Social',
                'labelColOptions'=>['style'=>'width:5%'],
                'valueColOptions'=>['style'=>'width:20%'],
                'value' => $modelagendaentrega->ordenCompra->proveedor ? $modelagendaentrega->ordenCompra->proveedor->razonSocial : '-',
            ],
        ],
    ],
    [
        'group'=>true,
        'label'=>'SECCIÓN 2: Información Programación',
        'rowOptions'=>['class'=>'table-info']
    ],

    [
        'columns' => [
            [
                'attribute'=>'idOrdenCompra', 
                'label' => 'Usuario',
                'labelColOptions'=>['style'=>'width:15%'],
                'valueColOptions'=>['style'=>'width:10%'],
                'value' => $modelprogramacion->userConteo->user->username,
            ],
            [
                'attribute'=>'idOrdenCompra',
                'label' => 'Identificación',
                'format'=>['decimal', 0],
                'labelColOptions'=>['style'=>'width:5%'],
                'valueColOptions'=>['style'=>'width:10%'],
                'value' => $modelprogramacion->empleadoLogistica->empleado->identificacion,
            ],
            [
                'attribute'=>'idOrdenCompra',
                'label' => 'Razón Social',
                'labelColOptions'=>['style'=>'width:5%'],
                'valueColOptions'=>['style'=>'width:20%'],
                'value' =>  $modelprogramacion->empleadoLogistica->empleado->nombreEmpleado,
            ],
        ],
    ],

];
?>

<div class="conteoentregamercancia-index">

    <?= Alert::widget() ?>

    <div class="row">
        <div class="col-lg-12">

        <p>
        <?=
            DetailView::widget([
                'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '-'],
                'options' => ['style' => 'font-size:14px;'],
                'model' => $modelagendaentrega,
                'attributes' => $attributes,
                'mode' => DetailView::MODE_VIEW,
                'bordered' => true,
                'striped' => true,
                'condensed' => true,
                'responsive' => true,
                'hover' => true,
                'hAlign'=> 'left',
                'vAlign'=> 'top',
            ]);
        ?>
        </p>
        </div>
    </div>

    <div class="row">

        <div class="col-lg-12 centrar">
            <?php $url = Url::to(['create', 'idprogramacion' => $modelprogramacion->id]); ?>
            
            <p>
            <?= Html::button('Asignar Referencia', 
                        ['value'=>  $url, 'class' => 'btn btn-success btn-lg btn-create', 'id'=>'modalButtonCreate']) 
            ?>
            </p>
        </div>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        //'filterModel' => $searchModel,

        'summary' => 'Mostrando {begin} - {end} de {totalCount} resultados',
		'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
		'options' => [
			'class' => 'mi-gridview', // Agrega una clase CSS a la tabla generada por el GridView
		],

        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'item', // Nombre del atributo en el modelo
                'label' => 'Item', // Etiqueta de la columna
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'value' => function ($model){
                    return $model->item;
                }
            ],

            [
                'attribute' => 'referencia', // Nombre del atributo en el modelo
                'label' => 'Referencia', // Etiqueta de la columna
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'value' => function ($model){
                    return $model->referencia;
                }
            ],

            [
                'attribute' => 'descripcion', // Nombre del atributo en el modelo
                'label' => 'Descripción', // Etiqueta de la columna
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'value' => function ($model){
                    return $model->descripcion;
                }
            ],

            [
                'attribute' => 'subcategoria', // Nombre del atributo en el modelo
                'label' => 'Subcategoría', // Etiqueta de la columna
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'value' => function ($model){
                    return $model->subcategoria;
                }
            ],

            [
                'attribute' => 'marca', // Nombre del atributo en el modelo
                'label' => 'Marca', // Etiqueta de la columna
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'value' => function ($model){
                    return $model->marca;
                }
            ],

            [
                'attribute' => 'talla', // Nombre del atributo en el modelo
                'label' => 'Talla', // Etiqueta de la columna
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],

            [
                'attribute' => 'color', // Nombre del atributo en el modelo
                'label' => 'Color', // Etiqueta de la columna
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],

            [
                'attribute' => 'unidadEmpaque', // Nombre del atributo en el modelo
                'label' => 'UND Empaque', // Etiqueta de la columna
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],

            [
                'attribute' => 'unidadesAsignadas', // Nombre del atributo en el modelo
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'value' => function ($model){
                    if ($model->unidadesAsignadas == 0){
                        return $model->programacionEntregaMercancia->agendaEntregaMercancia->unidades;
                    }
                    return $model->unidadesAsignadas;
                }
            ],

            [
                'attribute' => 'unidadesConteo', // Nombre del atributo en el modelo
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
            ],

            [
                'class' => ActionColumn::className(),
                'header'=>'Acción',
                //'headerOptions' => ['width' => '15%'],
                'template' => '{delete}',

                'buttons' => [

                    'delete' => function ($url, $model) {                                  
                        return Html::a('<i class="fa fa-trash"></i>', 
                                [   'delete', 'id' => $model->id], 
                                [   'class' => 'btn btn-default',
                                    'title' => 'Eliminar Empleado',
                                    'data' => [
                                        'confirm' => 'Esta Seguro de Eliminar este Registro? ( ' . $model->item . ' - ' . 
                                                                                                    $model->referencia . ' )',
                                        'method' => 'post',
                                    ]
                                ]
                        );
                    },

                ],

            ],

        ],
    ]); ?>


</div>
