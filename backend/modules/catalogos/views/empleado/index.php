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

use frontend\models\Empleado;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;

use frontend\models\Cargo;
use frontend\models\Centrooperacion;
use frontend\models\Centrocostos;

/** @var yii\web\View $this */
/** @var frontend\models\search\EmpleadoSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Empleados';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="empleado-index">

    <div class="row">
        <div class="col-lg-12 centrar">
                <?= Html::a('Registrar', ['create'], ['class' => 'btn btn-primary btn-lg btn-create', 'id'=>'modalButtonCreateTest']) ?>
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
                'width' => '10%',
            ],

            [
                'attribute' => 'nombreEmpleado', 
                'hAlign' => 'left', 
                'vAlign' => 'middle', 
                'width' => '20%',
            ],

            [
                'attribute' => 'ndc', 
                'hAlign' => 'center', 
                'vAlign' => 'middle', 
                'width' => '5%',
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
                'width' => '10%',
            ],

            [
                'attribute' => 'idCargo',
                'format' => 'html',
                'width' => '10%',
                'vAlign'=>'middle',
                'hAlign'=>'left',
                'value' => function ($model){
                    return ($model->idCargo) ? $model->cargo->nombre : '-';
                },
                'filter' => Cargo::getListaData(),                

            ],

            [
                'attribute' => 'idCO',
                'format' => 'html',
                'width' => '10%',
                'vAlign'=>'middle',
                'hAlign'=>'left',
                'value' => function ($model){
                    return ($model->idCO) ? $model->co->nombre : '-';
                },
                'filter' => Centrooperacion::getListaData(),                
            ],

            [
                'attribute' => 'idCC',
                'format' => 'html',
                'width' => '10%',
                'vAlign'=>'middle',
                'hAlign'=>'left',
                'value' => function ($model){
                    return ($model->idCC) ? $model->cc->nombre : '-';
                },
                'filter' => Centrocostos::getListaData(),                
            ],

            //'created_at',
            //'created_by',
            //'updated_at',
            //'updated_by',

            
            [
                'class' => 'yii\grid\ActionColumn',
                //'template' => Helper::filterActionColumn(['view', 'activate', 'delete']),

                'template' => '{update} {delete}',
                'headerOptions' => ['width' => '10%'],

                'buttons' => [
                    'update' => function ($url, $model) {                                
                        return Html::a('<i class="fa fa-edit"></i>',
                                [   'update', 'id' => $model->id], 
                                [
                                    'title' => 'Actualizar Datos Empleado',
                                    'class' => 'btn btn-default btn_update',
                                ]
                        );
                    },

                    'delete' => function ($url, $model) {                                  
                        return Html::a('<i class="fa fa-trash"></i>', 
                                [   'delete', 'id' => $model->id], 
                                [   'class' => 'btn btn-default',
                                    'title' => 'Eliminar Registro',
                                    'data' => [
                                        'confirm' => 'Esta Seguro de Eliminar este Registro ? ( ' . $model->identificacion . ' - ' . $model->nombreEmpleado .  ' )',
                                        'method' => 'post',
                                    ]
                                ]
                        );
                    },

                ]
            ],
        ],
    ]); ?>


</div>
