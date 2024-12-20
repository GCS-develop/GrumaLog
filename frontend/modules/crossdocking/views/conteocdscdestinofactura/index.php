<?php

$this->registerCss('
    .mi-gridview {
        font-size: 12px; /* Ajusta el tamaño de la fuente según sea necesario */
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
');

use frontend\models\Conteocdscdestinofactura;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use kartik\export\ExportMenu;
use common\widgets\Alert;

/** @var yii\web\View $this */
/** @var frontend\models\search\ConteocdscdestinofacturaSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Factura CDSC';
$this->params['breadcrumbs'][] = $this->title;

$fecha_actual = date("Y-m-d");

$filename = "Relacion_CDSCFactura_General_"  . $fecha_actual;
?>

<?php 
$gridColumns = [

    [
        'attribute' => 'id', // Nombre del atributo en el modelo
    ],

    [
        'attribute' => 'almacen',
    ],

    [
        'attribute' => 'codigoProveedor', // Nombre del atributo en el modelo
    ],
    [
        'attribute' => 'nit', // Nombre del atributo en el modelo
    ], 

    [
        'attribute' => 'razonSocial',
    ],

    [
        'attribute' => 'numeroFactura',
    ],

    [
        'attribute' => 'fecha', // Nombre del atributo en el modelo
        'format' => ['date', 'php:Y-m-d'],
    ],
    [
        'attribute' => 'idEstado',
        'value' => function($model) {
            $estado = '';
            switch($model->idEstado){
                case 1:
                    $estado = 'En Conteo'; break;
                case 2:
                    $estado = 'Finalizada'; break;
                default:
                    $estado = 'Sin Conteo'; break;
            }
            
            return $estado;
        },
    ],

    [
        'attribute' => 'idLegalizado',
        'value' => function($model) {
            $estado = '';
            switch($model->idLegalizado){
                case 1:
                    $estado = 'Legalizado'; break;
                default:
                    $estado = 'No Legalizado'; break;
            }
            
            return $estado;
        },
    ],

    [
        'attribute' => 'idEstadoEntrada',
        'value' => function($model) {
            $estado = '';
            switch($model->idEstadoEntrada){
                case 1:
                    $estado = 'Autorizada'; break;
                case 2:
                    $estado = 'Generada'; break;
                default:
                    $estado = 'Sin Entrada'; break;
            }
            
            return $estado;
        },
    ],

    [
        'attribute' => 'idEstadoTraspaso',
        'value' => function($model) {
            $estado = '';
            switch($model->idEstadoTraspaso){
                case 1:
                    $estado = 'Pendiente'; break;
                case 2:
                    $estado = 'Autorizada'; break;
                case 3:
                    $estado = 'Generada'; break;
                default:
                    $estado = 'Sin Traspaso'; break;
            }
            
            return $estado;
        },
    ],

    'fechaLegaliza',
    'observacionLegalizacion',
    [
        'attribute' => 'idUserLegaliza',
        'value' => function ($model) {
            return $model->userlegaliza ? $model->userlegaliza->username : '-';
        }
    ],

    'fechaEntrada',
    [
        'attribute' => 'idUserEntrada',
        'value' => function ($model) {
            return $model->userentrada ? $model->userentrada->username : '-';
        }
    ],

    [
        'attribute' => 'idSerieEntrada',
        'value' => function ($model) {
            return $model->tipodocumento ? $model->tipodocumento->codigo : '-';
        }
    ],
    'numeroEntrada',

    'fechaTraspaso',
    [
        'attribute' => 'idUserTraspaso',
        'value' => function ($model) {
            return $model->usertraspaso ? $model->usertraspaso->username : '-';
        }
    ],
];
?>

<div class="conteocdscdestinofactura-index">

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
    <?= Alert::widget() ?>

    <div class="row">
        <div class="col-lg-6 derecha">
            <?= Html::a('Registrar Factura', ['create'], ['class' => 'btn btn-success btn-lg btn-create']) ?>
        </div>

        <div class="col-lg-6 izquierda">   
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
        'filterModel' => $searchModel,

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
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
            ],

            [
                'attribute' => 'codigoProveedor', // Nombre del atributo en el modelo
                //'label' => 'Año', // Etiqueta de la columna
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],
            [
                'attribute' => 'nit', // Nombre del atributo en el modelo
                //'label' => 'Año', // Etiqueta de la columna
                'hAlign' => 'right', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
            ], 

            [
                'attribute' => 'razonSocial',
                'vAlign'=>'middle',
                'hAlign'=>'left',  
            ],

            [
                'attribute' => 'numeroFactura',
                'vAlign'=>'middle',
                'hAlign'=>'center',  
            ],

            /*[
                'attribute' => 'totalUnidades', // Nombre del atributo en el modelo
                //'label' => 'Año', // Etiqueta de la columna
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'filter' => ''
            ],*/  

            //'idCentroOperacionLegaliza',

            [
                'attribute' => 'idEstado',
                'format' => 'html',
                'vAlign'=>'middle',
                'hAlign'=>'left',                
                'value' => function($model) {
                    $estado = '';
                    switch($model->idEstado){
                        case 1:
                            $estado = 'En Conteo'; break;
                        case 2:
                            $estado = 'Finalizada'; break;
                        default:
                            $estado = 'Sin Conteo'; break;
                    }
                    
                    return $estado;
                },
                'filter' => ['0' => 'Sin Conteo', '1' => 'En Conteo', '2' => 'Finalizada'],
            ],

            [
                'attribute' => 'idLegalizado',
                'format' => 'html',
                'vAlign'=>'middle',
                'hAlign'=>'left',                
                'value' => function($model) {
                    $estado = '';
                    switch($model->idLegalizado){
                        case 1:
                            $estado = 'Legalizado'; break;
                        default:
                            $estado = 'No Legalizado'; break;
                    }
                    
                    return $estado;
                },
                'filter' => ['0' => 'No Legalizado', '1' => 'Legalizado'],
            ],

            [
                'attribute' => 'idEstadoEntrada',
                'format' => 'html',
                'vAlign'=>'middle',
                'hAlign'=>'left',                
                'value' => function($model) {
                    $estado = '';
                    switch($model->idEstadoEntrada){
                        case 1:
                            $estado = 'Autorizada'; break;
                        case 2:
                            $estado = 'Generada'; break;
                        default:
                            $estado = 'Sin Entrada'; break;
                    }
                    
                    return $estado;
                },
                'filter' => ['0' => 'Sin Entrada', '1' => 'Autorizada', '2' => 'Generada'],
                //'filter' => ''
            ],

            [
                'attribute' => 'idEstadoTraspaso',
                'format' => 'html',
                'vAlign'=>'middle',
                'hAlign'=>'left',                
                'value' => function($model) {
                    $estado = '';
                    switch($model->idEstadoTraspaso){
                        case 1:
                            $estado = 'Pendiente'; break;
                        case 2:
                            $estado = 'Autorizada'; break;
                        case 3:
                            $estado = 'Generada'; break;
                        default:
                            $estado = 'Sin Traspaso'; break;
                    }
                    
                    return $estado;
                },
                'filter' => ['0' => 'Sin Traspaso', '1' => 'Pendiente', '2' => 'Autorizada', '3' => 'Generada'],
                //'filter' => '',
            ],

            //'created_at',
            //'created_by',
            //'updated_at',
            //'updated_by',
            [
                'class' => ActionColumn::className(),
                'header'=>'Acción',
                //'headerOptions' => ['width' => '15%'],
                'template' => '{assignuser} {indexalmacen} {end} {print} {delete}',

                'buttons' => [

                    'indexalmacen' => function ($url, $model) {                                  
                        return Html::a('<i class="fa fa-store"></i>', 
                                [   'indexalmacen', 'idconteofactura' => $model->id, 'origen' => 'inicio'], 
                                [   'class' => 'btn btn-default',
                                    'title' => 'Visualizar Conteo por Almacén',
                                ]
                        );
                    },

                    'assignuser' => function ($url, $model) {                                
                        return Html::a('<i class="fa fa-users"></i>',
                                [   '/crossdocking/conteocdscusuario', 'idconteofactura' => $model->id, ], 
                                [
                                    'title' => 'Asignar Usuarios Conteo',
                                    'class' => 'btn btn-default btn_conteocdsc',
                                ]
                        );
                    },

                    'end' => function ($url, $model) {                                  
                        return Html::a('<i class="fa fa-window-close"></i>', 
                                [   'end', 'id' => $model->id], 
                                [   'class' => 'btn btn-default',
                                    'title' => 'Finalizar Conteo por Orden de Compra',
                                    'data' => [
                                        'confirm' => 'Esta Seguro de Finalizar Conteo de Orden de Compra? ( Rdaicado:' . $model->id . 
                                                                                        ' - Factura: ' . $model->numeroFactura . 
                                                                                        ' - Proveedor: ' . $model->proveedor->razonSocial . ' )',
                                        'method' => 'post',
                                    ]
                                ]
                        );
                    },

                    'print' => function ($url, $model) {                                  
                        return Html::a('<i class="fa fa-print"></i>', 
                                [   'imprimirtraspasos', 'idconteofactura' => $model->id], 
                                [   'class' => 'btn btn-default',
                                    'title' => 'Imprimir Traspaso Mercancia',
                                ]
                        );
                    },

                    'delete' => function ($url, $model) {                                  
                        return Html::a('<i class="fa fa-trash"></i>', 
                                [   'delete', 'id' => $model->id], 
                                [   'class' => 'btn btn-default',
                                    'title' => 'Eliminar Conteo OC de Proveedor',
                                    'data' => [
                                        'confirm' => 'Esta Seguro de Eliminar OC de Proveedor? ( ID:' . $model->id . 
                                                                                        ' - OC: ' . $model->numeroFactura . 
                                                                                        ' - Proveedor: ' . $model->proveedor->razonSocial . ' )',
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
