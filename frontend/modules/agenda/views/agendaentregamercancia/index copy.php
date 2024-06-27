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

use frontend\models\Agendaentregamercancia;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use kartik\export\ExportMenu;

use yii\bootstrap4\Modal;
use common\widgets\Alert;
use common\models\ProcedimientosGenerales;
use frontend\models\Transportadora;

/** @var yii\web\View $this */
/** @var frontend\models\search\AgendaentregamercanciaSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$nombremes = ProcedimientosGenerales::nombreMes($modelagenda->periodoMes);

$this->title = 'Agenda Recepción Mercancia';
$this->params['breadcrumbs'][] = ['label' => 'Agendamiento', 'url' => ['indexagendaperiodo']];
$this->params['breadcrumbs'][] = $this->title;

$titulo = $nombremes . ' del ' . $modelagenda->periodoAnio;

$fecha_actual = date("Y-m-d");
$filename = "Relacion_AgendaEntregaMercancia_" . $fecha_actual;

?>

<?php
    Modal::begin([                
        'title'=>'<h4>Registro datos básicos Agendamiento</h4>',
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
$gridColumns = [
    'id',
    'codigoCentroOperacion',
    'codigoTipoDocumento',
    'numeroOrdenCompra',
    'nit',
    'razonSocial',
    [
        'attribute' => 'fechaCita', // Nombre del atributo en el modelo
        'label' => 'Fecha Cita',
        'format' => ['date', 'php:Y-m-d'],
    ],
    [
        'attribute' => 'fechaCita', // Nombre del atributo en el modelo
        'label' => 'Hora Cita',
        'format' => ['date', 'php:H:i'],
    ],    
    'unidades',
    'numeroCajas',
    [
        'attribute' => 'idTransportadora', // Nombre del atributo en el modelo
        'label' => 'Transportadora', // Etiqueta de la columna
        'value' => function ($model){
            $nombre = '';
            if ($model->idTransportadora){
                $nombre = $model->transportadora->nombre;
            }
            return $nombre;
        }
    ],
    'contacto',
    [
        'attribute' => 'fechaContacto', // Nombre del atributo en el modelo
        'format' => ['date', 'php:Y-m-d'],
    ],
    'numeroGuia',
    'observacion',
    [
        'attribute' => 'idEstado', // Nombre del atributo en el modelo
        'value' => function ($model){
            return $model->estado->nombre;
        }
    ],
    [
        'attribute' => 'idEstadoConteo', // Nombre del atributo en el modelo
        'value' => function ($model){
            return $model->estadoconteo->nombre;
        }
    ],
];
?>

<div class="agendaentregamercancia-index">

    <h1 class="centrar"><?= Html::encode($titulo) ?></h1>

    <?= Alert::widget() ?>

    <?php echo $this->render('_search', ['model' => $searchModel, 'idagenda' => $modelagenda->id]); ?>

    <?= Html::tag('hr', '', ['class' => 'horizontal-line']) ?>

    <div class="row">

        <div class="col-lg-4 derecha">
            <?php $url = Url::to(['create', 'idagenda' => $modelagenda->id]); ?>
            
            <p>
            <?= Html::button('Registrar', 
                        ['value'=>  $url, 'class' => 'btn btn-success btn-lg btn-create', 'id'=>'modalButtonCreate']) 
            ?>
            </p>
        </div>

        <div class="col-lg-4 derecha">
                <?= Html::a('Registrar Test', ['createtest', 'idagenda' => $modelagenda->id], ['class' => 'btn btn-primary btn-lg btn-create', 'id'=>'modalButtonCreateTest']) ?>
        </div>

        <div class="col-lg-4 izquierda">   
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
        //'filterModel' => $searchModel,

        'summary' => 'Mostrando {begin} - {end} de {totalCount} resultados',
		'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
		'options' => [
			'class' => 'mi-gridview', // Agrega una clase CSS a la tabla generada por el GridView
		],

        'columns' => [
            //['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'id', // Nombre del atributo en el modelo
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],

            [
                'attribute' => 'codigoCentroOperacion',
                'label' => 'Almacen',
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],
            [
                'attribute' => 'codigoTipoDocumento',
                'label' => 'Serie',
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],
            [
                'attribute' => 'numeroOrdenCompra', // Nombre del atributo en el modelo
                'label' => 'Número Orden', // Etiqueta de la columna
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro

            ],
            [
                'attribute' => 'nit', // Nombre del atributo en el modelo
                'label' => 'Nit', // Etiqueta de la columna
                'hAlign' => 'right', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
            ],
            [
                'attribute' => 'razonSocial', // Nombre del atributo en el modelo
                'label' => 'Razón Social', // Etiqueta de la columna
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],
            [
                'attribute' => 'nombreCategoria', // Nombre del atributo en el modelo
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],

            [
                'attribute' => 'fechaCita', // Nombre del atributo en el modelo
                //'label' => 'Desde', // Etiqueta de la columna
                'format' => ['date', 'php:Y-m-d H:i'],
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],  
            [
                'attribute' => 'unidades', // Nombre del atributo en el modelo
                'label' => 'Unidades Agendadas', // Etiqueta de la columna
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
            ],

            [
                'attribute' => 'numeroCajas', // Nombre del atributo en el modelo
                'label' => 'Cajas', // Etiqueta de la columna
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'format' => ['decimal', 0], // Formato decimal con 0 decimales

            ],

            [
                'attribute' => 'idTransportadora', // Nombre del atributo en el modelo
                'label' => 'Transportadora', // Etiqueta de la columna
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'value' => function ($model){
                    $nombre = '';
                    if ($model->idTransportadora){
                        $nombre = $model->transportadora->nombre;
                    }
                    return $nombre;
                }
            ],

            [
                'attribute' => 'contacto', // Nombre del atributo en el modelo
                //'label' => 'Contacto', // Etiqueta de la columna
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],  
            [
                'attribute' => 'fechaContacto', // Nombre del atributo en el modelo
                //'label' => 'Fecha Contacto', // Etiqueta de la columna
                'format' => ['date', 'php:Y-m-d'],
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],  
 
            //'numeroGuia',
            //'observacion',
            [
                'attribute' => 'idEstado', // Nombre del atributo en el modelo
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'value' => function ($model){
                    return $model->estado->nombre;
                }
            ],

            [
                'attribute' => 'idEstadoConteo', // Nombre del atributo en el modelo
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'value' => function ($model){
                    return $model->estadoconteo->nombre;
                }
            ],

            [
                'attribute' => 'idAgendaEntregaMercancia', // Nombre del atributo en el modelo
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],

            [
                'class' => ActionColumn::className(),
                'header'=>'Acción',
                'headerOptions' => ['width' => '15%'],
                'template' => '{update} {change} {cancel} {delete}',

                'buttons' => [

                    'update' => function ($url, $model) {                                
                        return Html::a('<i class="fa fa-calendar"></i>',
                                [   'update', 'id' => $model->id], 
                                [
                                    'title' => 'Registrar Datos Cita Entrega Mercancía',
                                    'class' => 'btn btn-default btn_agendar_cita',
                                ]
                        );
                    },

                    'change' => function ($url, $model) {                                
                        return Html::a('<i class="fa fa-calendar-times"></i>',
                                [   'change', 'id' => $model->id], 
                                [
                                    'title' => 'Registrar Datos Nueva Cita Entrega Mercancía',
                                    'class' => 'btn btn-default btn_agendar_cita',
                                ]
                        );
                    },

                    'cancel' => function ($url, $model) {                                  
                        return Html::a('<i class="fa fa-times"></i>', 
                                [   'cancel', 'id' => $model->id], 
                                [   'class' => 'btn btn-default',
                                    'title' => 'Cancelar Cita Entrega Mercancía',
                                    'data' => [
                                        'confirm' => 'Esta Seguro de Cancelar Esta Cita? ( OC:' . $model->codigoCentroOperacion . '-' . 
                                                                                        $model->codigoTipoDocumento . '-' .
                                                                                        $model->numeroOrdenCompra .  ' - Fecha Cita:' .
                                                                                        $model->fechaCita . ' )',
                                        'method' => 'post',
                                    ]
                                ]
                        );
                    },

                    'delete' => function ($url, $model) {                                  
                        return Html::a('<i class="fa fa-trash"></i>', 
                                [   'delete', 'id' => $model->id], 
                                [   'class' => 'btn btn-default',
                                    'title' => 'Eliminar Registro',
                                    'data' => [
                                        'confirm' => 'Esta Seguro de Eliminar Este Registro? ( OC:' . $model->codigoCentroOperacion . '-' . 
                                                                                        $model->codigoTipoDocumento . '-' .
                                                                                        $model->numeroOrdenCompra .  ' - Fecha Cita:' .
                                                                                        $model->fechaCita . ' )',
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
