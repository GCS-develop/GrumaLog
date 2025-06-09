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

    .separacion {
        display: block;
        margin-top: 1em;
        margin-bottom: 1em;
    }
');

$this->registerJsFile(Yii::$app->request->baseUrl.'/js/mainDataModal.js',
['depends' => [\yii\web\JqueryAsset::className()]]
);

use frontend\models\Auditoriamanualimportacion;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;

use common\widgets\Alert;
use yii\bootstrap4\Modal;

use kartik\icons\Icon;
Icon::map($this, Icon::FAS);

/** @var yii\web\View $this */
/** @var frontend\models\search\AuditoriamanualimportacionSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Auditoria manual - Importar';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php
    Modal::begin([                
        'title'=>'<h4>Datos de auditoria manual</h4>',
        'id'=>'modaldata',
        'size'=>'modal-lg',
        'options' => [
            'tabindex' => false  // Importante para que funcione el Select
        ]
    ]);
        
    echo "<div id='modalContentData'></div>";
        
    Modal::end(); 
?>

<div class="auditoriamanualimportacion-index">

    <?= Alert::widget() ?>

    <?php $url = Url::to(['upload']); ?>

    <div class="row">    
        <div class="col-lg-12 centrar">    
            <?= Html::button('Importar Datos', 
                        ['value'=>  $url, 'class' => 'btn btn-success btn-lg btn-create', 'id'=>'modalButtonCreate']) 
            ?>
        </div>        
    </div>  

    <div class="separacion"></div>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        //'filterModel' => $searchModel,
        //'showPageSummary' => true,
        'summary' => 'Mostrando {begin} - {end} de {totalCount} resultados',
        'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
        'options' => [
            'class' => 'mi-gridview', // Agrega una clase CSS a la tabla generada por el GridView
        ],

        'columns' => [
            [
                'class' => 'kartik\grid\SerialColumn'
            ],

            [
                'attribute' => 'id', // Nombre del atributo en el modelo
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
            ],

            [
                'attribute' => 'numeroRegistros', // Nombre del atributo en el modelo
                'hAlign' => 'right', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
            ],

            [
                'attribute' => 'totalCantidad', // Nombre del atributo en el modelo
                'hAlign' => 'right', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
            ],

            [
                'attribute' => 'created_at', // Nombre del atributo en el modelo
                //'format' => ['date', 'php:Y-m-d H:i'],
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'value' => function($model){
                    return substr($model->created_at,0, 16);
                }
            ],
            [
                'attribute' => 'created_by', // Nombre del atributo en el modelo
                'value' => function ($model){
                    return $model->usuariocrea->username;
                },
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],

            //'updated_at',
            //'updated_by',
            [
                'class' => ActionColumn::className(),
                'header'=>'Acción',
                'headerOptions' => ['width' => '15%'],
                'template' => '{view} {delete}',

                'buttons' => [

                    'view' => function ($url, $model) {                                
                        return Html::a('<i class="fa fa-search"></i>',
                                [   '/auditoriamanual/auditoriamanualimportaciondetalle/index', 'idinterfase' => $model->id, ], 
                                [
                                    'title' => 'Ver Detalle auditoria manual',
                                    'class' => 'btn btn-default btn_view_detalle',
                                ]
                        );
                    },


                    'delete' => function ($url, $model) {                                  
                        return Html::a('<i class="fa fa-trash"></i>', 
                                [   'delete', 'id' => $model->id], 
                                [   'class' => 'btn btn-default',
                                    'title' => 'Eliminar Intrefase',
                                    'data' => [
                                        'confirm' => 'Esta Seguro de Eliminar esta Interfase? ( ' . $model->id . ' - ' . 
                                                                                        $model->numeroRegistros . ' )',
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
