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

use common\widgets\Alert;
use yii\bootstrap4\Modal;

use kartik\icons\Icon;
Icon::map($this, Icon::FAS);

use common\models\ProcedimientosGenerales;
use frontend\models\Transportadora;
use frontend\models\Conteoentregamercancia;
use frontend\models\Programacionentregamercancia;
use frontend\models\Ordendecompradetalle;

/** @var yii\web\View $this */
/** @var frontend\models\search\AgendaentregamercanciaSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

// $nombremes = ProcedimientosGenerales::nombreMes($modelagenda->periodoMes);

$this->title = 'Legalización Conteo';
$this->params['breadcrumbs'][] = $this->title;

// $titulo = $nombremes . ' del ' . $modelagenda->periodoAnio;

$fecha_actual = date("Y-m-d");
$filename = "Relacion_LegalizacionConteo_" . $fecha_actual;

?>

<?php
    Modal::begin([                
        'title'=>'<h4>Datos Básicos Ordenes de Compra SIESA</h4>',
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
        'format' => ['date', 'php:Y-m-d H:i'],
    ], 

    [
        'attribute' => 'unidadesCumplidas', // Nombre del atributo en el modelo
        'label' => 'UND Cumplidas', // Etiqueta de la columna
    ],
    
    [
        'attribute' => 'unidadesConteo', // Nombre del atributo en el modelo
        'label' => 'UND Conteo', // Etiqueta de la columna
        'value' => function ($model){
            return Conteoentregamercancia::totalCantidadConteo (null,null,null, $model->id);
        }
    ],

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
        'attribute' => 'fechaCita', // Nombre del atributo en el modelo
        'format' => ['date', 'php:Y-m-d'],
    ],
    [
        'attribute' => 'numeroFactura', // Nombre del atributo en el modelo
        'hAlign' => 'left', // Alineación horizontal al centro
        'vAlign' => 'middle', // Alineación vertical al centro
    ], 

    [
        'attribute' => 'observacionLegalizacion', // Nombre del atributo en el modelo
        'hAlign' => 'left', // Alineación horizontal al centro
        'vAlign' => 'middle', // Alineación vertical al centro
    ], 

    [
        'attribute' => 'idEstadoLegalizacion', // Nombre del atributo en el modelo
        'label' => 'Legalización',
        'value' => function ($model){
            return $model->estadolegalizacion->nombre;
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

    <?= Alert::widget() ?>

    <?php echo $this->render('_search_legalizacion', ['model' => $searchModel]); ?>

    <?= Html::tag('hr', '', ['class' => 'horizontal-line']) ?>
    
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

        <!--
        <div class="col-lg-6 izquierda">
            <?php 
                $url = Url::to(
                    [  'extraerdataocsiesa'
                        ]);
            ?>
            
            <p>
            <?= Html::button('Importar Datos OC SIESA', 
                        ['value'=>  $url, 'class' => 'btn btn-success btn-lg btn-create', 'id'=>'modalButtonCreate']) 
            ?>
            </p>
        </div>
        -->

    </div>    

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        //'filterModel' => $searchModel,

        'summary' => 'Mostrando {begin} - {end} de {totalCount} resultados',
		'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
		'options' => [
			'class' => 'mi-gridview', // Agrega una clase CSS a la tabla generada por el GridView
		],

        'showPageSummary' => true,

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
            /*[
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
                'attribute' => 'unidadesCumplidas', // Nombre del atributo en el modelo
                'label' => 'UND Cumplidas', // Etiqueta de la columna
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
            ],*/
            [
                'attribute' => 'totalCantidadPendiente', // Nombre del atributo en el modelo
                'label' => 'UND OC', // Etiqueta de la columna
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'value' => function ($model) {
                    return Ordendecompradetalle::totalCantidadPendiente($model->idOrdenCompra, $model->idCategoria);
                },
                'pageSummary' => true,
            ],

            [
                'attribute' => 'unidades', // Nombre del atributo en el modelo
                'label' => 'UND Asignadas', // Etiqueta de la columna
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'value' => function ($model) {
                    return Programacionentregamercancia::totalUnidadesAsignadas ($model->id);
                    //return array_sum(array_column($model->programacionentregamercancia, 'unidadesAsignadas'));
                },
                'pageSummary' => true,
            ],

            [
                'attribute' => 'unidadesConteo', // Nombre del atributo en el modelo
                'label' => 'UND Conteo', // Etiqueta de la columna
                'hAlign' => 'right', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'value' => function ($model){
                    return Conteoentregamercancia::totalCantidadConteo (null,null,null, $model->id);
                },
                'pageSummary' => true,
            ],

            [
                'attribute' => 'numeroCajas', // Nombre del atributo en el modelo
                'label' => 'Cajas', // Etiqueta de la columna
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'format' => ['decimal', 0], // Formato decimal con 0 decimales

            ],

            /*[
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
            ],*/ 
            
            [
                'attribute' => 'numeroFactura', // Nombre del atributo en el modelo
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ], 

            [
                'attribute' => 'observacionLegalizacion', // Nombre del atributo en el modelo
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ], 
 
            //'numeroGuia',
            //'observacion',
            [
                'attribute' => 'idEstadoLegalizacion', // Nombre del atributo en el modelo
                'label' => 'Legalización',
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'value' => function ($model){
                    return $model->estadolegalizacion->nombre;
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
                'attribute' => 'comprador', // Nombre del atributo en el modelo
                'label' => 'Comprador', // Etiqueta de la columna
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],

            [
                'class' => ActionColumn::className(),
                'header'=>'Acción',
                'headerOptions' => ['width' => '15%'],
                'template' => '{legalizaconteo} {habilitarconteo} {exportarmatriz} {documentoentrada} {transferencia}',

                'buttons' => [

                    'exportarmatriz' => function ($url, $model) {                                  
                        return Html::a('<i class="fa fa-file-excel"></i>', 
                                [   'generarexcelconteocurvas', 'idagenda' => $model->id], 
                                [   'class' => 'btn btn-default',
                                    'title' => 'Exportar Matriz',
                                ]
                        );
                    },

                    'legalizaconteo' => function ($url, $model) {                                  
                        return Html::a('<i class="fa fa-check"></i>', 
                                [   'viewlegalizaconteo', 'idagenda' => $model->id], 
                                [   'class' => 'btn btn-default',
                                    'title' => 'Legalizar Conteo',
                                    /*'data' => [
                                        'confirm' => 'Esta Seguro de Legalizar Este Conteo? ( OC:' . $model->codigoCentroOperacion . '-' . 
                                                                                        $model->codigoTipoDocumento . '-' .
                                                                                        $model->numeroOrdenCompra .  ' - Fecha Cita:' .
                                                                                        $model->fechaCita . ' )',
                                        'method' => 'post',
                                    ]*/
                                ]
                        );
                    },

                    'habilitarconteo' => function ($url, $model) {                                  
                        return Html::a('<i class="fa fa-reply"></i>', 
                                [   'habilitarconteo', 'idagenda' => $model->id], 
                                [   'class' => 'btn btn-default',
                                    'title' => 'Habilitar Conteo',
                                    'data' => [
                                        'confirm' => 'Esta Seguro de Habilitar Este Conteo? ( OC:' . $model->codigoCentroOperacion . '-' . 
                                                                                        $model->codigoTipoDocumento . '-' .
                                                                                        $model->numeroOrdenCompra .  ' - Fecha Cita:' .
                                                                                        $model->fechaCita . ' )',
                                        'method' => 'post',
                                    ]
                                ]
                        );
                    },

                    'documentoentrada' => function ($url, $model) {                                
                        $t = Url::to([  'actualizardocumentoentrada', 
                                        'idagenda' => $model->id
                                    ]);

                        return Html::button('<i class="fa fa-edit"></i>',[
                                    'value'=> $t,
                                    'title' => 'Registrar Datos Documento Entrada SIESA',
                                    'class' => 'btn btn-default btn_upload',
                        ]);
                    },

                    'transferencia' => function ($url, $model) {                                  
                        return Html::a('<i class="fa fa-globe"></i>', 
                                [   'transferencia', 'idagenda' => $model->id], 
                                [   'class' => 'btn btn-default',
                                    'title' => 'Transferencia ERP',
                                    'data' => [
                                        'confirm' => 'Esta Seguro de Realizar Transaferencia? ( OC:' . $model->codigoCentroOperacion . '-' . 
                                                                                        $model->codigoTipoDocumento . '-' .
                                                                                        $model->numeroOrdenCompra .  ' - Factura:' .
                                                                                        $model->numeroFactura . ' )',
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
