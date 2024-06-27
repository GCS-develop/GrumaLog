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
use yii\bootstrap4\Modal;

use kartik\icons\Icon;
Icon::map($this, Icon::FAS);

/** @var yii\web\View $this */
/** @var frontend\models\search\AgendapresupuestoSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Períodos recibo mercancia';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php
    Modal::begin([                
        'title'=>'<h4>Datos Básicos Fechas recibo mercancía</h4>',
        'id'=>'modaldata',
        'size'=>'modal-lg',
        'options' => [
            'tabindex' => false  // Importante para que funcione el Select
        ]
    ]);
        
    echo "<div id='modalContentData'></div>";
        
    Modal::end(); 
?>

<div class="agendapresupuesto-index">

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
                            $estado = 'Abierto'; break;
                        case 0:
                            $estado = 'Cerrado'; break;
                    }
                    
                    return $estado;
                }
            ],

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
                                    'title' => 'Actualizar Período Presupuesto',
                                    'class' => 'btn btn-default btn_update',
                        ]);
                    },


                    'delete' => function ($url, $model) {                                  
                        return Html::a('<i class="fa fa-trash"></i>', 
                                [   'delete', 'id' => $model->id], 
                                [   'class' => 'btn btn-default',
                                    'title' => 'Eliminar Período',
                                    'data' => [
                                        'confirm' => 'Esta Seguro de Eliminar este Período? ( ' . $model->periodoAnio . ' - ' . 
                                                                                        $model->nombremes . ' )',
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
