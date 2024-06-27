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

use frontend\models\Conteocdscusuario;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use kartik\detail\DetailView;

use yii\bootstrap4\Modal;
use common\widgets\Alert;
use common\models\ProcedimientosGenerales;

/** @var yii\web\View $this */
/** @var frontend\models\search\ConteocdscusuarioSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Usuarios Conteo';
$this->params['breadcrumbs'][] = ['label' => 'Factura CDSC', 'url' => ['/crossdocking/conteocdscdestinofactura/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php
    Modal::begin([                
        'title'=>'<h4>Registro datos básicos usuario conteo</h4>',
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
        'label'=>'SECCIÓN 1: Información Orden de Compra',
        'rowOptions'=>['class'=>'table-info']
    ],
    [
        'columns' => [
            [
                'attribute'=>'nit', 
                'label'=>'Nit Proveedor',
                'displayOnly'=>true,
                'labelColOptions'=>['style'=>'width:10%'],
                'valueColOptions'=>['style'=>'width:15%'],
                'value' => $modelfactura->proveedor->nit,
            ],

            [
                'attribute'=>'razonSocial', 
                'label'=>'Nombre Proveedor',
                'displayOnly'=>true,
                'labelColOptions'=>['style'=>'width:10%'],
                'valueColOptions'=>['style'=>'width:30%'],
                'value' => $modelfactura->proveedor->razonSocial,
            ],

            [
                'attribute'=>'numeroFactura', 
                'label'=>'Orden de Compra',
                'displayOnly'=>true,
                'labelColOptions'=>['style'=>'width:10%'],
                'valueColOptions'=>['style'=>'width:15%'],
                'value' => $modelfactura->numeroFactura,
            ],

        ],
    ],

];
?>


<div class="conteocdscusuario-index">

    <div class="row">
        <div class="col-lg-12">

        <p>
        <?=
            DetailView::widget([
                'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '-'],
                'options' => ['style' => 'font-size:14px;'],
                'model' => $modelfactura,
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

    <div class="col-lg-12 centrar">
        <?php $url = Url::to(['create', 'idconteofactura' => $modelfactura->id]); ?>
        
        <p>
        <?= Html::button('Registrar', 
                    ['value'=>  $url, 'class' => 'btn btn-success btn-lg btn-create', 'id'=>'modalButtonCreate']) 
        ?>
        </p>
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

            //'id',
            //'idConteocdscdestinofactura',
            [
                'attribute' => 'idUserConteo',
                'label' => 'Usuario',
                'vAlign'=>'middle',
                'hAlign'=>'left',                
                'value' => function($model) {      
                    return $model->userconteo->user->username;
                },
            ],
            
            [
                'attribute' => 'idUserConteo',
                'label' => 'Identificación',
                'format' => ['decimal', 0],
                'vAlign'=>'middle',
                'hAlign'=>'right',                
                'value' => function($model) {
                                       
                    return $model->userconteo->empleadoLogistica->empleado->identificacion;
                },
            ],

            [
                'attribute' => 'idUserConteo',
                'label' => 'Empleado',
                'format' => 'html',
                'vAlign'=>'middle',
                'hAlign'=>'left',                
                'value' => function($model) {
                                       
                    return $model->userconteo->empleadoLogistica->empleado->nombreEmpleado;
                },
            ],

            [
                'attribute' => 'idEstado',
                'format' => 'html',
                'vAlign'=>'middle',
                'hAlign'=>'left',                
                'value' => function($model) {
                    $estado = '';
                    switch($model->idEstado){
                        case 1:
                            $estado = 'Programado'; break;
                        default:
                            $estado = 'Finalizado'; break;
                    }
                    
                    return $estado;
                },
                //'filter' => ['0' => 'Finalizado', '1' => 'Programado'],
            ],

            //'idUserConteo',
            //'created_at',
            //'created_by',
            //'updated_at',
            //'updated_by',
            [
                'class' => ActionColumn::className(),
                'header'=>'Acción',
                'headerOptions' => ['width' => '15%'],
                'template' => '{update} {delete}',

                'buttons' => [

                    'update' => function ($url, $model) {                                
                        $t = Url::to([  'update', 
                                        'id' => $model->id
                                    ]);

                        return Html::button('<i class="fa fa-edit"></i>',[
                                    'value'=> $t,
                                    'title' => 'Actualizar Usuario',
                                    'class' => 'btn btn-default btn_update',
                        ]);
                    },


                    'delete' => function ($url, $model) {                                  
                        return Html::a('<i class="fa fa-trash"></i>', 
                                [   'delete', 'id' => $model->id], 
                                [   'class' => 'btn btn-default',
                                    'title' => 'Eliminar Usuario',
                                    'data' => [
                                        'confirm' => 'Esta Seguro de Eliminar Usuario? ( ' . $model->userconteo->empleadoLogistica->empleado->nombreEmpleado . ' )',
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
