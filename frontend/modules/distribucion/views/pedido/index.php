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

use frontend\models\Pedido;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;

use yii\bootstrap4\Modal;
use common\widgets\Alert;

/** @var yii\web\View $this */
/** @var frontend\models\Search\PedidoSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Pedidos';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php
    Modal::begin([                
        'title'=>'<h4>Datos Básicos Pedido</h4>',
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
    Modal::begin([                
        'title'=>'<h4>Datos Básicos Pedido</h4>',
        'id'=>'modaldatamanual',
        'size'=>'modal-lg',
        'options' => [
            'tabindex' => false  // Importante para que funcione el Select
        ]
    ]);
        
    echo "<div id='modalContentDataManual'></div>";
        
    Modal::end(); 
?>

<?php
Modal::begin([
    'title' => '<h4>Subir Archivo Distribución</h4>',
    'id' => 'modaldataupload',
    'size' => 'modal-lg',
    'options' => [
        'tabindex' => false  // Importante para que funcione el Select
    ]
]);

echo "<div id='modalContentDataUpload'></div>";

Modal::end();
?>

<div class="pedido-index">

    <?= Alert::widget() ?>

    <!--
    <h1><?= Html::encode($this->title) ?></h1>
    -->

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <div class="row">

        <div class="col-lg-12 centrar">
            <?php $url = Url::to(['create']); ?>
            
            <p>
            <?= Html::button('Registrar Pedido', 
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
            //['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'id', // Nombre del atributo en el modelo
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],

            [
                'attribute' => 'fecha', 
                'hAlign' => 'center', 
                'vAlign' => 'middle', 
            ],

            [
                'attribute' => 'observaciones', 
                'hAlign' => 'left', 
                'vAlign' => 'middle', 
            ],

            [
                'attribute' => 'nroOrdenesCompra', // Nombre del atributo en el modelo
                'label' => 'No. OC', // Etiqueta de la columna
                'hAlign' => 'right', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
            ],

            [
                'attribute' => 'totalUnidades', // Nombre del atributo en el modelo
                'label' => 'Total Unidades', // Etiqueta de la columna
                'hAlign' => 'right', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
            ],

            [
                'attribute' => 'created_by', // Nombre del atributo en el modelo
                'label' => 'Creado por',
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'value' => function ($model){
                    return $model->creadopor->username;
                }
            ],

            [
                'class' => ActionColumn::className(),
                'header' => 'Acción',
                //'headerOptions' => ['width' => '15%'],
                'template' => '{update} {view} {delete}',

                'buttons' => [
                    'update' => function ($url, $model) {
                        $t = Url::to([
                            'update',
                            'id' => $model->id
                        ]);

                        return Html::button('<i class="fa fa-edit"></i>', [
                            'value' => $t,
                            'title' => 'Actualizar datos básicos pedido',
                            'class' => 'btn btn-default btn_update',
                        ]);
                    },

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

                    'view' => function ($url, $model) {                                
                        return Html::a('<i class="fa fa-eye"></i>',
                                [   '/distribucion/pedidoordendecompra/index', 'idpedido' => $model->id, ], 
                                [
                                    'title' => 'Relación Ordenes de Compra',
                                    'class' => 'btn btn-default btn_ver_ordenes',
                                ]
                        );
                    },

                    'delete' => function ($url, $model) {
                        return Html::a(
                            '<i class="fa fa-trash"></i>',
                            ['delete', 'id' => $model->id],
                            [
                                'class' => 'btn btn-default',
                                'title' => 'Eliminar Pedido',
                                'data' => [
                                    'confirm' => 'Esta Seguro de Eliminar Pedido? ( ' . $model->id . ' )',
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
