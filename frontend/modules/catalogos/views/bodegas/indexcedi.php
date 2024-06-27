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
');


use frontend\models\Bodegas;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;

use common\models\ProcedimientosGenerales;
use common\widgets\Alert;

/** @var yii\web\View $this */
/** @var frontend\models\search\BodegasSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Bodegas - CEDI';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="bodegas-index">

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
            [
                'class' => 'kartik\grid\SerialColumn',
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'width' => '5%',
            ],

            [
                'attribute' => 'id',
                'format' => 'html',
                'width' => '10%',
                'vAlign'=>'middle',
                'hAlign'=>'left',                

            ],

            [
                'attribute' => 'codigo',
                'format' => 'html',
                'width' => '10%',
                'vAlign'=>'middle',
                'hAlign'=>'left',                

            ],

            [
                'attribute' => 'nombre',
                'format' => 'html',
                'width' => '45%',
                'vAlign'=>'middle',
                'hAlign'=>'left',                

            ],

            /*[
                'attribute' => 'cedi', // Nombre del atributo en el modelo
                'width' => '10%',
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'value' => function ($model){
                    return $model->cedi == 1 ? 'SI' : 'NO';
                },
                'filter' => ['0' => 'NO', '1' => 'SI'],
            ],*/

            /*[
                'class' => ActionColumn::className(),
                'header'=>'Acción',
                'headerOptions' => ['width' => '10%'],
                'template' => '{update}',

                'buttons' => [

                    'update' => function ($url, $model) {                                
                        $t = Url::to([  'update', 
                                        'id' => $model->id
                                    ]);

                        return Html::button('<i class="fa fa-edit"></i>',[
                                    'value'=> $t,
                                    'title' => 'Actualizar Datos Bodega',
                                    'class' => 'btn btn-default btn_update',
                        ]);
                    },

                    'delete' => function ($url, $model) {                                  
                        return Html::a('<i class="fa fa-trash"></i>', 
                                [   'delete', 'id' => $model->id], 
                                [   'class' => 'btn btn-default',
                                    'title' => 'Eliminar Bodega',
                                    'data' => [
                                        'confirm' => 'Esta Seguro de Eliminar esta Bodega? ( ' . $model->id . ' - ' . 
                                                                                        $model->nombre . ' )',
                                        'method' => 'post',
                                    ]
                                ]
                        );
                    }, 

                ],

            ],*/
        ],
    ]); ?>


</div>
