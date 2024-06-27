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

use frontend\models\Empleadologistica;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;

use yii\bootstrap4\Modal;
use common\widgets\Alert;

/** @var yii\web\View $this */
/** @var frontend\models\search\EmpleadologisticaSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Empleados Logística';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php
    Modal::begin([                
        'title'=>'<h4>Registro datos básicos Empleado Logística</h4>',
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
        'title'=>'<h4>Registro datos básicos Usuario del Sistema</h4>',
        'id'=>'modaldatalogistica',
        'size'=>'modal-lg',
        'options' => [
            'tabindex' => false  // Importante para que funcione el Select
        ]
    ]);
        
    echo "<div id='modalContentDataLogistica'></div>";
        
    Modal::end(); 
?>

<div class="empleadologistica-index">

    <?= Alert::widget() ?>

    <div class="row">
        <div class="col-lg-12 centrar">
            <?php $url = Url::to(['create']); ?>
            
            <p>
            <?= Html::button('Registrar', 
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
            [
                'class' => 'kartik\grid\SerialColumn',
                'hAlign' => 'left', 
                'vAlign' => 'middle', 
                'width' => '5%',
            ],

            [
                'attribute' => 'identificacion', 
                'format' => ['decimal', 0], 
                'hAlign' => 'right', 
                'vAlign' => 'middle', 
                'width' => '15%',
            ],

            [
                'attribute' => 'nombreEmpleado', 
                'hAlign' => 'left', 
                'vAlign' => 'middle', 
                'width' => '35%',
            ],
            
            [
                'attribute' => 'username', 
                'hAlign' => 'left', 
                'vAlign' => 'middle', 
                'width' => '15%',
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
                'width' => '15%',
            ],

            [
                'class' => ActionColumn::className(),
                'header'=>'Acción',
                'headerOptions' => ['width' => '15%'],
                'template' => '{update} {user} {assign} {delete}',

                'buttons' => [

                    'update' => function ($url, $model) {                                
                        $t = Url::to([  'update', 
                                        'id' => $model->id
                                    ]);

                        return Html::button('<i class="fa fa-edit"></i>',[
                                    'value'=> $t,
                                    'title' => 'Actualizar Registro Empleado',
                                    'class' => 'btn btn-default btn_update',
                        ]);
                    },

                    'assign' => function ($url, $model) {                                
                        $t = Url::to([  'assign', 
                                        'id' => $model->id
                                    ]);

                        return Html::button('<i class="fa fa-user"></i>',[
                                    'value'=> $t,
                                    'title' => 'Crear Usuario Nuevo Conteo',
                                    'class' => 'btn btn-default btn_user',
                        ]);
                    },

                    'user' => function ($url, $model) {                                
                        $t = Url::to([  'user', 
                                        'id' => $model->id
                                    ]);

                        return Html::button('<i class="fa fa-user-plus"></i>',[
                                    'value'=> $t,
                                    'title' => 'Asignar usuario Conteo',
                                    'class' => 'btn btn-default btn_user',
                        ]);
                    },

                    'delete' => function ($url, $model) {                                  
                        return Html::a('<i class="fa fa-trash"></i>', 
                                [   'delete', 'id' => $model->id], 
                                [   'class' => 'btn btn-default',
                                    'title' => 'Eliminar Empleado Logística',
                                    'data' => [
                                        'confirm' => 'Esta Seguro de Retirar este Registro? ( ' . $model->identificacion . ' - ' . 
                                                                                        $model->nombreEmpleado . ' )',
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
