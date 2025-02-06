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

use frontend\models\Facturaentregamercancia;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use yii\bootstrap4\Modal;

use common\widgets\Alert;

/** @var yii\web\View $this */
/** @var frontend\models\search\FacturaentregamercanciaSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Programación Recibo Mercancía';
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

<div class="facturaentregamercancia-index">

    <?= Alert::widget() ?>

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
            //['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'radicado', // Nombre del atributo en el modelo
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],

            [
                'attribute' => 'almacen',
                'label' => 'Almacen',
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],
            [
                'attribute' => 'serie',
                'label' => 'Serie',
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],
            [
                'attribute' => 'consecutivo', // Nombre del atributo en el modelo
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro

            ],

            [
                'attribute' => 'categoria', // Nombre del atributo en el modelo
                'label' => 'Categoría', // Etiqueta de la columna
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
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],

            [
                'attribute' => 'numeroFactura', // Nombre del atributo en el modelo
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro

            ],

            [
                'attribute' => 'observaciones', // Nombre del atributo en el modelo
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro

            ],
            //'created_at',
            //'created_by',
            //'updated_at',
            //'updated_by',
            [
                'class' => ActionColumn::className(),
                'header'=>'Acción',
                'headerOptions' => ['width' => '15%'],
                'template' => '{view} {end}',

                'buttons' => [

                    'view' => function ($url, $model) {                                
                        return Html::a('<i class="fa fa-search"></i>',
                                [   'indexconteoprogramacion', 'idfactura' => $model->id, ], 
                                [
                                    'title' => 'Ver Programación Conteos',
                                    'class' => 'btn btn-default btn_asignar_cita',
                                ]
                        );
                    },

                    'end' => function ($url, $model) {                                  
                        return Html::a('<i class="fa fa-window-close"></i>', 
                                [   '/programacion/conteoentregamercancia/indexagenda', 
                                                            'idagenda' =>  null,
                                                            'idfactura' => $model->id], 
                                [   'class' => 'btn btn-default',
                                    'title' => 'Finalizar Conteo',
                                    'data' => [
                                        'confirm' => 'Esta Seguro de Finalizar Conteo? ( Radicado:' . $model->id . 
                                                                                        ' - Serie: ' . $model->serie . 
                                                                                        ' - Orden de Compra: ' . $model->consecutivo . 
                                                                                        ' - Número Factura: ' . $model->numeroFactura . ' )',
                                        'method' => 'post',
                                    ]
                                ]
                        );
                    },

                    /*'index' => function ($url, $model) {                                
                        return Html::a('<i class="fa fa-users"></i>',
                                [   'index', 'id' => $model->id, ], 
                                [
                                    'title' => 'Asignar Personas Entrega Mercancía',
                                    'class' => 'btn btn-default btn_asignar_cita',
                                ]
                        );
                    },*/

                ],

            ],
        ],
    ]); ?>


</div>
