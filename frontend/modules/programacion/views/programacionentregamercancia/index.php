<?php

$this->registerCss('
    .mi-gridview {
        font-size: 14px; /* Ajusta el tamaño de la fuente según sea necesario */
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

use frontend\models\Programacionentregamercancia;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use kartik\detail\DetailView;

use yii\bootstrap4\Modal;
use common\widgets\Alert;


/** @var yii\web\View $this */
/** @var frontend\models\search\ProgramacionentregamercanciaSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Programación';
//$this->params['breadcrumbs'][] = ['label' => 'Programación Recibo Mercancia', 'url' => ['indexprogramacion', 'menu' => 'programacion']];
$this->params['breadcrumbs'][] = ['label' => 'Factura Recibo Mercancia', 'url' => ['/programacion/facturaentregamercancia/index']];
$this->params['breadcrumbs'][] = $this->title;

?>

<?php
    Modal::begin([                
        'title'=>'<h4>Registro datos básicos Programación</h4>',
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
    ],
    [
        'group'=>true,
        'label'=>'SECCIÓN 2: Información Orden de Compra',
        'rowOptions'=>['class'=>'table-info']
    ],

    [
        'columns' => [
            [
                'attribute'=>'idOrdenCompra', 
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
        'columns' => [     
            [
                'attribute'=>'idOrdenCompra', 
                'displayOnly'=>true,
                'label' => 'Cant. Pedida',
                'labelColOptions'=>['style'=>'width:10%'],
                'valueColOptions'=>['style'=>'width:10%'],
                'value' => $modelagendaentrega->ordenCompra ? $modelagendaentrega->ordenCompra->totalCantidadPedida : '-',
                'format'=>['decimal', 0],
                'format'=>'raw', 
            ],
            [
                'attribute'=>'idOrdenCompra', 
                'displayOnly'=>true,
                'label' => 'Cant. Entrada',
                'labelColOptions'=>['style'=>'width:10%'],
                'valueColOptions'=>['style'=>'width:10%'],
                'value' => $modelagendaentrega->ordenCompra ? $modelagendaentrega->ordenCompra->totalCantidadEntrada : '-',
                'format'=>['decimal', 0],
                'format'=>'raw', 
            ],
        ]

    ],

    [
        'group'=>true,
        'label'=>'SECCIÓN 3: Información Factura',
        'rowOptions'=>['class'=>'table-info']
    ],
    [
        'columns' => [
            [
                'attribute'=>'numeroFactura',
                'label' => 'Número Factura',
                'value' => $modelfactura->numeroFactura,
                'labelColOptions'=>['style'=>'width:5%'],
                'valueColOptions'=>['style'=>'width:25%'],
            ],

            [
                'attribute'=>'observacion',
                'value' => $modelfactura->observaciones,
                'labelColOptions'=>['style'=>'width:5%'],
                'valueColOptions'=>['style'=>'width:30%'],
            ],
        ],        
    ],


];
?>

<div class="programacionentregamercancia-index">

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
            <?php
                $urlOneUser   = Url::to(['assignoneuser',       'idfactura' => $modelfactura->id]);
                $urlMultiUser = Url::to(['assignmultipleusers', 'idfactura' => $modelfactura->id]);
            ?>
            <p>
                <?= Html::button(
                    '<i class="fa fa-user"></i> Asignar 1 Usuario (todos los ítems)',
                    ['value' => $urlOneUser,   'class' => 'btn btn-success btn-lg btn-create', 'id' => 'modalButtonCreate',
                     'title' => 'Asigna un único operario a todos los ítems sin asignar']
                ) ?>
                &nbsp;
                <?= Html::button(
                    '<i class="fa fa-users"></i> Asignar Múltiples Usuarios',
                    ['value' => $urlMultiUser, 'class' => 'btn btn-primary btn-lg btn_create',
                     'title' => 'Selecciona varios operarios y distribuye los ítems en forma rotativa']
                ) ?>
            </p>
        </div>
    </div>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        // 'filterModel' => $searchModel,

        'summary' => 'Mostrando {begin} - {end} de {totalCount} resultados',
		'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
		'options' => [
			'class' => 'mi-gridview', // Agrega una clase CSS a la tabla generada por el GridView
		],

        'showPageSummary' => true,

        'columns' => [
            [
                'class' => 'kartik\grid\SerialColumn',
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],

            'id',
            [
                'attribute' => 'item', // Nombre del atributo en el modelo
                'label' => 'Item', // Etiqueta de la columna
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],

            [
                'attribute' => 'referencia', // Nombre del atributo en el modelo
                'label' => 'Referencia', // Etiqueta de la columna
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],

            [
                'attribute' => 'descripcion', // Nombre del atributo en el modelo
                'label' => 'Descripción', // Etiqueta de la columna
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],

            [
                'attribute' => 'unidadesAsignadas', // Nombre del atributo en el modelo
                'label' => 'UND Asignadas', // Etiqueta de la columna
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'format' => ['decimal', 0],
                'pageSummary' => true,
            ],

            [
                'attribute' => 'idUserConteo', // Nombre del atributo en el modelo
                'label' => 'Usuario', // Etiqueta de la columna
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'value' => function ($model){
                    return $model->userConteo ? $model->userConteo->user->username : ' - ';
                }
            ],

            [
                'attribute' => 'idEmpleadoLogistica', // Nombre del atributo en el modelo
                'label' => 'Nombre', // Etiqueta de la columna
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'value' => function ($model){
                    return $model->empleadoLogistica ? $model->empleadoLogistica->empleado->nombreEmpleado : '-';
                }
            ],

            [
                'attribute' => 'idEstado', // Nombre del atributo en el modelo
                'label' => 'Estado', // Etiqueta de la columna
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'value' => function ($model){
                    return $model->estado->nombre;
                }
            ],

            [
                'attribute' => 'puedeModificarEntrada', // Nombre del atributo en el modelo
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'value' => function ($model){
                    return $model->puedeModificarEntrada == 1 ? 'SI' : 'NO';
                }
            ],

            [
                'attribute' => 'unidadxPaquete', // Nombre del atributo en el modelo
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'format' => ['decimal', 0],
            ],

            [
                'class' => ActionColumn::className(),
                'header'=>'Acción',
                'headerOptions' => ['width' => '10%'],
                'template' => '{update} {anular-conteo}',

                'buttons' => [

                    'update' => function ($url, $model) {
                        $t = Url::to([  'update',
                                        'id' => $model->id
                                    ]);

                        return Html::button('<i class="fa fa-edit"></i>',[
                                    'value'=> $t,
                                    'title' => 'Actualizar Datos Asignación',
                                    'class' => 'btn btn-default btn_update',
                        ]);
                    },

                    'anular-conteo' => function ($url, $model) {
                        $estadoActual = $model->estado ? $model->estado->codigo : null;
                        // Solo mostrar si el conteo ya fue finalizado (codigo=2)
                        if ($estadoActual != 2) {
                            return '';
                        }
                        $t = Url::to(['anular-conteo', 'id' => $model->id]);
                        return Html::a('<i class="fa fa-undo"></i>', $t, [
                            'class' => 'btn btn-warning btn-xs',
                            'title' => 'Anular Conteo (permitir recontar)',
                            'data'  => [
                                'confirm' => '¿Anular el conteo de ' . Html::encode($model->userConteo ? $model->userConteo->user->username : '') . '? Se borrarán todos los registros de conteo.',
                                'method'  => 'post',
                            ],
                        ]);
                    },

                    'create' => function ($url, $model) {                                
                        $t = Url::to([  'create', 
                                        'id' => $model->id
                                    ]);

                        return Html::button('<i class="fa fa-plus"></i>',[
                                    'value'=> $t,
                                    'title' => 'Asignar Otro Nuevo Usuario al Conteo',
                                    'class' => 'btn btn-default btn_create',
                        ]);
                    },

                    /*'delete' => function ($url, $model) {                                  
                        return Html::a('<i class="fa fa-trash"></i>', 
                                [   'delete', 'id' => $model->id], 
                                [   'class' => 'btn btn-default',
                                    'title' => 'Eliminar Empleado',
                                    'data' => [
                                        'confirm' => 'Esta Seguro de Eliminar este Registro? ( ' . $model->empleadoLogistica->empleado->identificacion . ' - ' . 
                                                                                        $model->empleadoLogistica->empleado->nombreEmpleado . ' )',
                                        'method' => 'post',
                                    ]
                                ]
                        );
                    },*/

                ],

            ],
        ],
    ]); ?>


</div>
