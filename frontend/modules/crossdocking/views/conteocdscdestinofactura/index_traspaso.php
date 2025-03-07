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
');

$this->registerJsFile(Yii::$app->request->baseUrl.'/js/mainDataModal.js',
['depends' => [\yii\web\JqueryAsset::className()]]
);

use frontend\models\Conteocdscdestinofactura;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use yii\bootstrap4\Modal;
use common\widgets\Alert;

/** @var yii\web\View $this */
/** @var frontend\models\search\ConteocdscdestinofacturaSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Traspaso Factura CDSC';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php
    Modal::begin([                
        'title'=>'<h4>Registro datos Documento Traspaso</h4>',
        'id'=>'modaldata',
        'size'=>'modal-lg',
        'options' => [
            'tabindex' => false  // Importante para que funcione el Select
        ]
    ]);
        
    echo "<div id='modalContentData'></div>";
        
    Modal::end(); 
?>

<div class="conteocdscdestinofactura-index">

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
    <?= Alert::widget() ?>

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
                'attribute' => 'almacen',
                'vAlign'=>'middle',
                'hAlign'=>'left',  
            ],

            [
                'attribute' => 'created_at', // Nombre del atributo en el modelo
                'label' => 'Fecha', // Etiqueta de la columna
                'format' => ['date', 'php:Y-m-d'],
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro,
                'filter' => '',
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
                //'filter' => ['0' => 'Sin Entrada', '1' => 'Autorizada', '2' => 'Generada'],
                'filter' => ''
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
                //'filter' => ['0' => 'Sin Traspaso', '1' => 'Pendiente', '2' => 'Autorizada', '3' => 'Generada'],
                'filter' => '',
            ],

            //'created_at',
            //'created_by',
            //'updated_at',
            //'updated_by',
            [
                'class' => ActionColumn::className(),
                'header'=>'Acción',
                //'headerOptions' => ['width' => '15%'],
                'template' => '{traspasofactura} {indexalmacen} {generartraspasodestino}',

                'buttons' => [

                    'traspasofactura' => function ($url, $model) {                                
                        $t = Url::to([  'traspasofactura', 
                                        'idconteofactura' => $model->id
                                    ]);

                        return Html::button('<i class="fa fa-edit"></i>',[
                                    'value'=> $t,
                                    'title' => 'Actualizar Datos Documento Traspaso',
                                    'class' => 'btn btn-default btn_update',
                        ]);
                    },

                    /*'indexalmacen' => function ($url, $model) {                                  
                        return Html::a('<i class="fa fa-store"></i>', 
                                [   'indexalmacen', 'idconteofactura' => $model->id, 'origen' => 'traspaso'], 
                                [   'class' => 'btn btn-default',
                                    'title' => 'Visualizar Conteo por Almacén',
                                ]
                        );
                    },*/

                    'indexalmacen' => function ($url, $model) {                                  
                        return Html::a('<i class="fa fa-print"></i>', 
                                [   '/crossdocking/conteocdscdestino/indexalmacen', 
                                            'idconteofactura' => $model->id,
                                            'origen' => 'traspaso'
                                    ], 
                                [   'class' => 'btn btn-default',
                                    'title' => 'Imprimir Etiqueta de Cajas y Tirilla de Items',
                                    /*'data' => [
                                        'confirm' => 'Esta Seguro de Imprimir Etiqueta de la Caja? ( ' . $model->razonSocial . ' - ' . $model->numeroFactura  .' )',
                                        'method' => 'post',
                                    ]*/
                                ]
                        );
                    },

                    'generartraspasodestino' => function ($url, $model) {                                  
                        return Html::a('<i class="fa fa-list"></i>', 
                                [   '/crossdocking/conteocdscdestino/indextraspaso', 'idconteofactura' => $model->id], 
                                [   'class' => 'btn btn-default',
                                    'title' => 'Generar Traspaso',
                                    'data' => [
                                        'confirm' => 'Esta Seguro de Generar Traspaso ? ( ' . $model->razonSocial . ' - ' . $model->numeroFactura  .' )',
                                        'method' => 'post',
                                    ]
                                ]
                        );
                    },

                    /*'transferenciatraspaso' => function ($url, $model) {                                  
                        return Html::a('<i class="fa fa-globe"></i>', 
                                [   'transferenciatraspaso', 'idconteofactura' => $model->id], 
                                [   'class' => 'btn btn-default',
                                    'title' => 'Transferencia ERP',
                                    'data' => [
                                        'confirm' => 'Esta Seguro de Realizar Transferencia? ( ' . $model->razonSocial . ' - ' . $model->numeroFactura  .' )',
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
