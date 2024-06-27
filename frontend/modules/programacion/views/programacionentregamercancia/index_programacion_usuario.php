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

use frontend\models\Programacionentregamercancia;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use kartik\export\ExportMenu;

use common\widgets\Alert;

/** @var yii\web\View $this */
/** @var frontend\models\search\ProgramacionentregamercanciaSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Programación Recepción Mercancia';
$this->params['breadcrumbs'][] = $this->title;

$fecha_actual = date("Y-m-d");
$filename = "Relacion_ProgramacionEntregaMercancia_" . $fecha_actual;

//die("hola");

?>

<?php
$gridColumns = [
    [
        'attribute' => 'idAgendaEntregaMercancia', // Nombre del atributo en el modelo
        'label' => 'Radicado', // Etiqueta de la columna
        'hAlign' => 'left', // Alineación horizontal al centro
        'vAlign' => 'middle', // Alineación vertical al centro
    ],
    [
        'attribute' => 'fechaCita', // Nombre del atributo en el modelo
        'format' => ['date', 'php:Y-m-d H:i'],
        'value' => function ($model){
            return $model->agendaEntregaMercancia->fechaCita;
        },
    ],  

    [
        'attribute' => 'ordenCompra', // Nombre del atributo en el modelo
        'label' => 'Orden Compra', // Etiqueta de la columna
        'value' => function ($model){
            return $model->agendaEntregaMercancia->ordenCompra->cO->codigo . '-' . 
                    $model->agendaEntregaMercancia->ordenCompra->tipoDocumento->nombre . '-' .
                    $model->agendaEntregaMercancia->ordenCompra->consecutivo;
        },
    ],  
 
    [
        'attribute' => 'idAgendaEntregaMercancia', // Nombre del atributo en el modelo
        'label' => 'Estado OC', // Etiqueta de la columna
        'value' => function ($model){
            return $model->agendaEntregaMercancia->estado->nombre;
        },
    ],

    [
        'attribute' => 'ordenCompra', // Nombre del atributo en el modelo
        'label' => 'Transportadora', // Etiqueta de la columna
        'value' => function ($model){
            return $model->agendaEntregaMercancia->transportadora->nombre;
        },
    ],

    [
        'attribute' => 'idEmpleadoLogistica', // Nombre del atributo en el modelo
        'label' => 'Identificación', // Etiqueta de la columna
        'value' => function ($model){
            return $model->empleadoLogistica->empleado->identificacion;
        }
    ],

    [
        'attribute' => 'idEmpleadoLogistica', // Nombre del atributo en el modelo
        'label' => 'Nombre Completo', // Etiqueta de la columna
        'value' => function ($model){
            return $model->empleadoLogistica->empleado->nombreEmpleado;
        }
    ],

    [
        'attribute' => 'idEmpleadoLogistica', // Nombre del atributo en el modelo
        'label' => 'Cargo', // Etiqueta de la columna
        'value' => function ($model){
            return $model->empleadoLogistica->empleado->cargo->nombre;
        }
    ],

    [
        'attribute' => 'idEmpleadoLogistica', // Nombre del atributo en el modelo
        'label' => 'CO', // Etiqueta de la columna
        'value' => function ($model){
            return $model->empleadoLogistica->empleado->cO->nombre;
        }
    ],

    [
        'attribute' => 'idEstado', // Nombre del atributo en el modelo
        'label' => 'Estado', // Etiqueta de la columna
        'value' => function ($model){
            return $model->estado->nombre;
        }
    ],
];

?>


<div class="programacionentregamercancia-index">

    <?= Alert::widget() ?>

    <?php echo $this->render('_search_agenda_programacion', ['model' => $searchModel]); ?>

    <div class="row">

        <div class="col-lg-12 centrar">   
            <?php echo ExportMenu::widget(
                [
                    'dataProvider' => $dataProvider,
                    'columns' => $gridColumns,
                    'fontAwesome' => true,
                    'filename' => $filename,
                    'dropdownOptions' => [
                        'label' => 'Exportar',
                        'class' => 'btn btn-success btn-lg btn-create',
                    ],
                    'exportConfig' => [
                        ExportMenu::FORMAT_TEXT => false,
                        ExportMenu::FORMAT_HTML => false,
                        ExportMenu::FORMAT_EXCEL => false,
                        ExportMenu::FORMAT_PDF => false,
                        ExportMenu::FORMAT_CSV => false,
                        ExportMenu::FORMAT_EXCEL_X => [
                            'label' => 'Excel 2007+',
                            'icon' => 'file-excel-o' ,
                            'iconOptions' => ['class' => 'text-success'],
                            'linkOptions' => [],
                            'options' => ['title' => 'Microsoft Excel 2007+ (xlsx)'],
                            'alertMsg' => 'Se va a generar un archivo en formato EXCEL 2007+ (xlsx).',
                            'mime' => 'application/application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'extension' => 'xlsx',
                            'writer' => ExportMenu::FORMAT_EXCEL_X
                        ],
                        
                    ]                            
                ]);
            ?>        
        </div>

    </div>    

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

            [
                'attribute' => 'idAgendaEntregaMercancia', // Nombre del atributo en el modelo
                'label' => 'Radicado', // Etiqueta de la columna
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],
            [
                'attribute' => 'fechaCita', // Nombre del atributo en el modelo
                //'label' => 'Desde', // Etiqueta de la columna
                'format' => ['date', 'php:Y-m-d H:i'],
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'value' => function ($model){
                    return $model->agendaEntregaMercancia->fechaCita;
                },
            ],  

            [
                'attribute' => 'ordenCompra', // Nombre del atributo en el modelo
                'label' => 'Orden Compra', // Etiqueta de la columna
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'value' => function ($model){
                    return $model->agendaEntregaMercancia->ordenCompra->cO->codigo . '-' . 
                            $model->agendaEntregaMercancia->ordenCompra->tipoDocumento->nombre . '-' .
                            $model->agendaEntregaMercancia->ordenCompra->consecutivo;
                },
            ],  

            [
                'attribute' => 'ordenCompra', // Nombre del atributo en el modelo
                'label' => 'Transportadora', // Etiqueta de la columna
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'value' => function ($model){
                    return $model->agendaEntregaMercancia->transportadora->nombre;
                },
            ],

            [
                'attribute' => 'idEmpleadoLogistica', // Nombre del atributo en el modelo
                'label' => 'Identificación', // Etiqueta de la columna
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'format'=>['decimal', 0],
                'value' => function ($model){
                    return $model->empleadoLogistica->empleado->identificacion;
                }
            ],

            [
                'attribute' => 'idEmpleadoLogistica', // Nombre del atributo en el modelo
                'label' => 'Nombre Completo', // Etiqueta de la columna
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'value' => function ($model){
                    return $model->empleadoLogistica->empleado->nombreEmpleado;
                }
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
                'attribute' => 'idAgendaEntregaMercancia', // Nombre del atributo en el modelo
                'label' => 'Estado OC', // Etiqueta de la columna
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'value' => function ($model){
                    return $model->agendaEntregaMercancia->estadoconteo->nombre;
                }
            ],

            [
                'attribute' => 'idEstado', // Nombre del atributo en el modelo
                'label' => 'Estado Usuario', // Etiqueta de la columna
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'value' => function ($model){
                    switch($model->idEstado){
                        case 1: $estado = 'Habilitado'; break;
                        case 2: $estado = 'Termino Conteo'; break;
                        default: $estado = 'Pendiente'; break;
                    }
                    return $estado;
                }
            ],

            [
                'class' => ActionColumn::className(),
                'header'=>'Acción',
                //'headerOptions' => ['width' => '5%'],
                'template' => '{habilitarconteo}',

                'buttons' => [

                    'habilitarconteo' => function ($url, $model) {                                
                        return Html::a('<i class="fa fa-qrcode"></i>',
                                [   'habilitarconteo', 'id' => $model->id], 
                                [
                                    'title' => 'Asignar Referencias de Orden de Compra',
                                    'class' => 'btn btn-default btn_habilitar_conteo',
                                    'data' => [
                                        'confirm' => 'Esta Seguro de Habilitar? ( ' . $model->empleadoLogistica->empleado->nombreEmpleado . ' - OC:' . 
                                                                                    $model->agendaEntregaMercancia->ordenCompra->consecutivo . ' - ' .
                                                                                    $model->agendaEntregaMercancia->ordenCompra->tipoDocumento->nombre . ' - UND:' .
                                                                                    $model->unidadesAsignadas . ' )',
                                        'method' => 'post',
                                    ]
                                ]
                        );
                    },

                    'delete' => function ($url, $model) {                                  
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
                    },

                ],

            ],
        ],
    ]); ?>


</div>
