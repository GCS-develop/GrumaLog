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

    .titulo {
        color: black;
        font-weight: bold;
    }
    
    .titulonombre {
        color: black;
        font-weight: bold;
        font-size: 11px;
    }

    .horizontal-line {
        border: none;
        border-top: 1px solid #ccc; /* Color y grosor de la línea */
        margin: 10px 0; /* Espacio alrededor de la línea */
    }

');

use app\models\Conteobylecturacodigo;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;

use frontend\models\Devoluciondocumentodetalle;

/** @var yii\web\View $this */
/** @var app\models\search\ConteobylecturacodigoSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Lectura EAN';
$this->params['breadcrumbs'][] = ['label' => 'Devolución Documento Detalle', 'url' => ['index', 'iddocumento' => $modeldocumento->id]];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="card">
    <div class="card-body">

        <div class="row">
            <div class="col-12 titulo">
                <?= Html::encode($modeluser->username) . ' - ' . $modeluser->empleado->nombreEmpleado?>
            </div>
        </div>

        <div class="row">
            <div class="col-12 titulo">
                <?= Html::encode($modeldocumento->codigoBodegaSalida) . ' - ' . $modeldocumento->bodegasalida->nombre?>
            </div>
        </div>

        <div class="row">
            <div class="col-12 titulo">
                <?= Html::encode($modeldocumento->numeroDocumento) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-12 titulo">
                <?= Html::encode($model->codigoBarras . ' - ' . $model->itemResumen) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12 titulo">
                <?= Html::encode('UND: ' . $model->cantidadRegistrada) ?>
            </div>
        </div>

    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <?= Html::tag('hr', '', ['class' => 'horizontal-line']) ?>
    </div>
</div>

<div class="conteobylecturacodigo-index">

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p class="centrar">
        <?= Html::a('Salir', ['index', 'iddocumento' => $modeldocumento->id], ['class' => 'btn btn-success btn-lg btn-create']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        //'filterModel' => $searchModel,
        'summary' => '',

		'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
		'options' => [
			'class' => 'mi-gridview', // Agrega una clase CSS a la tabla generada por el GridView
		],

        'showPageSummary' => true,

        'columns' => [
            [
                'class' => 'kartik\grid\SerialColumn',
                'hAlign' => 'center',
                'vAlign' => 'middle',
            ],

            //'id',
            [
                'attribute' => 'codigoBarras', // Nombre del atributo en el modelo
                'hAlign' => 'left',
                'vAlign' => 'middle',
                'filter' => '',
                'value' => function ($data) use ($model) {
                    return $model->codigoBarras . ' - ' . $model->itemResumen;
                }
            ],

            /*[
                'attribute' => 'item', // Nombre del atributo en el modelo
                'hAlign' => 'left',
                'vAlign' => 'middle',
                'value' => function ($data) use ($model) {
                    return $model->itemResumen;
                } 
            ],*/

            [
                'attribute' => 'unidades', // Nombre del atributo en el modelo
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
                'filter' => '',
                'width' => '10%',
            ],

            [
                'attribute' => 'isMobile', // Nombre del atributo en el modelo
                'width' => '10%',
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'value' => function ($model){
                    return $model->isMobile == 1 ? 'SI' : 'NO';
                },
                'label' => 'Móvil',
                'filter' => ['0' => 'NO', '1' => 'SI'],
            ],
            
            [
                'attribute' => 'created_at', // Nombre del atributo en el modelo
                'width' => '15%',
                'label' => 'F. Registro',
                'hAlign' => 'center',
                'vAlign' => 'middle',
                //'format' => ['date', 'php:Y-m-d h:i:s'],
                'filter' => '',
            ],

            [
                'attribute' => 'created_by', // Nombre del atributo en el modelo
                'width' => '10%',
                'label' => 'User Registra',
                'hAlign' => 'center',
                'vAlign' => 'middle',
                'value' => function ($data){
                    return $data->usercreated->username; 
                },
                'label' => 'Usuario Registra'
            ],

            //'created_by',
            //'updated_at',
            //'updated_by',
            [
                'class' => ActionColumn::className(),
                'header'=>'Acción',
                'headerOptions' => ['width' => '15%'],
                'template' => '{deleteconteo}',
                'contentOptions' => ['data-cellvalue' => 'Acciones',],
                'buttons' => [

                    'deleteconteo' => function ($url, $data) use ($model) {                                  
                        return Html::a('<i class="fa fa-trash"></i>', 
                                [   'deleteconteo', 'id' => $data->id], 
                                [   'class' => 'btn btn-default',
                                    'title' => 'Eliminar Conteo',
                                    'data' => [
                                        'confirm' => 'Esta Seguro de Eliminar Este Conteo? ( ID:' . $model->codigoBarras . ' - ' . $model->itemResumen . ' Cant: ' . $model->cantidadRegistrada . ' )',
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
