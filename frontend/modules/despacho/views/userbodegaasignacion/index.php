<?php

// Definir el estilo CSS directamente en la vista
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

$this->registerJsFile(Yii::$app->request->baseUrl.'/js/mainDataModal.js',
['depends' => [\yii\web\JqueryAsset::className()]]
);

use frontend\models\Userbodegaasignacion;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use kartik\detail\DetailView;

use common\models\ProcedimientosGenerales;
use common\widgets\Alert;
use yii\bootstrap4\Modal;

/** @var yii\web\View $this */
/** @var frontend\models\search\UserbodegaasignacionSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Asignar Almacén';
$this->params['breadcrumbs'][] = ['label' => 'Usuarios Planillas', 'url' => ['/despacho/userbodega/index']];
$this->params['breadcrumbs'][] = $this->title;

?>

<?php
    Modal::begin([                
        'title'=>'<h4>Registro datos básicos Almacén</h4>',
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
        'label'=>'SECCIÓN 1: Información Usuario',
        'rowOptions'=>['class'=>'table-info']
    ],
    [
        'columns' => [
            [
                'attribute'=>'identificacion', 
                'label'=>'Identificación',
                'displayOnly'=>true,
                'labelColOptions'=>['style'=>'width:10%'],
                'valueColOptions'=>['style'=>'width:15%'],
                'value' => $modeluserbodega->empleado->identificacion,
            ],

            [
                'attribute'=>'nombreEmpleado', 
                'label'=>'Empleado',
                'displayOnly'=>true,
                'labelColOptions'=>['style'=>'width:10%'],
                'valueColOptions'=>['style'=>'width:30%'],
                'value' => $modeluserbodega->empleado->nombreEmpleado,
            ],

            [
                'attribute'=>'username', 
                'label'=>'Usuario',
                'displayOnly'=>true,
                'labelColOptions'=>['style'=>'width:10%'],
                'valueColOptions'=>['style'=>'width:15%'],
                'value' => $modeluserbodega->user->username,
            ],

        ],
    ],

];
?>

<div class="userbodegaasignacion-index">

    <?= Alert::widget() ?>

    <div class="row">
        <div class="col-lg-12">

        <p>
        <?=
            DetailView::widget([
                'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '-'],
                'options' => ['style' => 'font-size:14px;'],
                'model' => $modeluserbodega,
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
            <?php $url = Url::to(['create', 'iduserbodega' => $modeluserbodega->id]); ?>

            <p>
            <?= Html::button('Asignar Almacén', 
                        ['value'=>  $url, 'class' => 'btn btn-success btn-lg btn-create', 'id'=>'modalButtonCreate']) 
            ?>
            </p>
        </div>
    </div>

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
            ['class' => 'yii\grid\SerialColumn'],

            //'id',
            [
                'attribute' => 'identificacion', 
                'format' => ['decimal', 0], 
                'hAlign' => 'right', 
                'vAlign' => 'middle', 
            ],

            [
                'attribute' => 'nombreEmpleado', 
                'hAlign' => 'left', 
                'vAlign' => 'middle', 
            ],
            
            [
                'attribute' => 'username', 
                'hAlign' => 'left', 
                'vAlign' => 'middle', 
            ],
            [
                'attribute' => 'idEstado',
                'hAlign' => 'left', 
                'vAlign' => 'middle', 
                'filter' => ['0' => 'Inactivo', '1' => 'Activo'],
                'filterInputOptions' => ['class' => 'form-control', 'prompt' => 'Seleccione una opción'],
                'value' => function($model){
                    return $model->idEstado == 0 ? 'Inactivo' : 'Activo';
                },
            ],

            [
                'attribute' => 'codigoAlmacen', 
                'hAlign' => 'center', 
                'vAlign' => 'middle', 
            ],

            [
                'attribute' => 'almacen', 
                'hAlign' => 'left', 
                'vAlign' => 'middle', 
            ],

            //'created_at',
            //'created_by',
            //'updated_at',
            //'updated_by',
            [
                'class' => ActionColumn::className(),
                'header'=>'Acción',
                'headerOptions' => ['width' => '15%'],
                'template' => '{delete}',

                'buttons' => [

                    'delete' => function ($url, $model) {                                  
                        return Html::a('<i class="fa fa-trash"></i>', 
                                [   'delete', 'id' => $model->id], 
                                [   'class' => 'btn btn-default',
                                    'title' => 'Eliminar Almacén',
                                    'data' => [
                                        'confirm' => 'Esta Seguro de Eliminar este Registro? ( ' . $model->identificacion . ' - ' . 
                                                                                        $model->nombreEmpleado . ' - ' .
                                                                                        $model->codigoAlmacen . ' - ' . 
                                                                                        $model->almacen . ' )',
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
