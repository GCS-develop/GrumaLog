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

$this->registerJsFile(Yii::$app->request->baseUrl.'/js/mainDataModal.js',
['depends' => [\yii\web\JqueryAsset::className()]]
);

use frontend\models\Agendapresupuesto;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;

use common\models\ProcedimientosGenerales;
use common\widgets\Alert;

use kartik\icons\Icon;
Icon::map($this, Icon::FAS);

/** @var yii\web\View $this */
/** @var frontend\models\search\AgendapresupuestoSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Fechas recibo mercancia';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="agendapresupuesto-index">

    <?= Alert::widget() ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        //'filterModel' => $searchModel,
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
                'attribute' => 'periodoAnio', // Nombre del atributo en el modelo
                //'label' => 'Año', // Etiqueta de la columna
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'width' => '10%',

            ],  
            [
                'attribute' => 'periodoMes', // Nombre del atributo en el modelo
                //'label' => 'Año', // Etiqueta de la columna
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'width' => '10%',
                'value' => function ($model) {
                    return ProcedimientosGenerales::nombreMes ($model->periodoMes);
                },
            ],  

            [
                'attribute' => 'desde', // Nombre del atributo en el modelo
                //'label' => 'Desde', // Etiqueta de la columna
                'format' => ['date', 'php:Y-m-d'],
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'width' => '10%',
            ],  

            [
                'attribute' => 'hasta', // Nombre del atributo en el modelo
                //'label' => 'Desde', // Etiqueta de la columna
                'format' => ['date', 'php:Y-m-d'],
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'width' => '10%',
            ],  

            //'created_at',
            //'created_by',
            //'updated_at',
            //'updated_by',
            [
                'attribute' => 'observacion',
                'format' => 'html',
                'width' => '30%',
                'vAlign'=>'middle',
                'hAlign'=>'left',                
                'value' => function($model) {
                    if ($model->observacion){
                        return "<span style='font-family: Dejavu Sans, monospace'>" 
                            . Yii::$app->formatter->asNtext($model->observacion) . '</span>';
                    }
                    
                    return '-';
                }
            ],

            [
                'attribute' => 'idEstado',
                'format' => 'html',
                'width' => '10%',
                'vAlign'=>'middle',
                'hAlign'=>'left',                
                'value' => function($model) {
                    $estado = '';
                    switch($model->idEstado){
                        case 1:
                            $estado = 'Activo'; break;
                        case 0:
                            $estado = 'Inactivo'; break;
                    }
                    
                    return $estado;
                }
            ],

            [
                'class' => ActionColumn::className(),
                'header'=>'Acción',
                'headerOptions' => ['width' => '15%'],
                'template' => '{indexupdate}',

                'buttons' => [

                    'indexupdate' => function ($url, $model) {                                
                        return Html::a('<i class="fa fa-check"></i>',
                                [   'indexupdate', 'idagendapresupuesto' => $model->id], 
                                [
                                    'title' => 'Visualizar Datos por Categoría',
                                    'class' => 'btn btn-default btn_view_categoria',
                                ]
                        );
                    },

                ],

            ],
        ],
    ]); ?>


</div>
