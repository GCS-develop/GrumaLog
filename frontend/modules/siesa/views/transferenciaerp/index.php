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

use frontend\models\Transferenciaerp;
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
/** @var frontend\models\search\TransferenciaerpSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Transferencia ERP';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php
    Modal::begin([                
        'title'=>'<h4>Subir Archivo Transferencia</h4>',
        'id'=>'modaldata',
        'size'=>'modal-lg',
        'options' => [
            'tabindex' => false  // Importante para que funcione el Select
        ]
    ]);
        
    echo "<div id='modalContentData'></div>";
        
    Modal::end(); 
?>

<div class="transferenciaerp-index">

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
        //'filterModel' => $searchModel,
        'summary' => 'Mostrando {begin} - {end} de {totalCount} resultados',
		'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
		'options' => [
			'class' => 'mi-gridview', // Agrega una clase CSS a la tabla generada por el GridView
		],

        'columns' => [
            [
                'class' => 'kartik\grid\SerialColumn',
                'format' => 'html',
                'vAlign'=>'middle',
                'hAlign'=>'center',
            ],

            //'id',
            [
                'attribute' => 'descripcion',
                'format' => 'html',
                'vAlign'=>'middle',
                'hAlign'=>'left',
            ],

            [
                'attribute' => 'notas',
                'format' => 'html',
                'vAlign'=>'middle',
                'hAlign'=>'left',
            ],

            [
                'attribute' => 'idConectorDinamico',
                'format' => 'html',
                'vAlign'=>'middle',
                'hAlign'=>'left',                
                'value' => function($model) {
                    return $model->conectordinamico->nombreDocumento;
                }
            ],

            [
                'attribute' => 'documento',
                'format' => 'html',
                'vAlign'=>'middle',
                'hAlign'=>'left',
            ],

            [
                'attribute' => 'numeroRegistros',
                'format' => 'html',
                'vAlign'=>'middle',
                'hAlign'=>'left',
            ],

            [
                'attribute' => 'enviadoWS',
                'label' => 'Transferencia ERP',
                'format' => 'html',
                'vAlign'=>'middle',
                'hAlign'=>'left',                
                'value' => function($model) {
                    $estado = '';
                    switch($model->enviadoWS){
                        case 1:
                            $estado = 'Generado'; break;
                        case 0:
                            $estado = 'Sin Generar'; break;
                    }
                    
                    return $estado;
                }
            ],

            [
                'attribute' => 'created_at', // Nombre del atributo en el modelo
                'label' => 'Fecha Creactón', // Etiqueta de la columna
                'format' => ['date', 'php:Y-m-d H:i'],
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'width' => '10%',
            ],

            [
                'attribute' => 'created_by',
                'label' => 'Usuario',
                'format' => 'html',
                'vAlign'=>'middle',
                'hAlign'=>'left',                
                'value' => function($model) {
                    return $model->usercreated->username;
                }
            ],

            //'updated_at',
            //'updated_by',
            [
                'class' => ActionColumn::className(),
                'header'=>'Acción',
                //'headerOptions' => ['width' => '15%'],
                'template' => '{update} {importardataxls} {transferencia} {errorws} {logws} {delete}',

                'buttons' => [

                    'update' => function ($url, $model) {                                
                        $t = Url::to([  'update', 
                                        'id' => $model->id
                                    ]);

                        return Html::button('<i class="fa fa-edit"></i>',[
                                    'value'=> $t,
                                    'title' => 'Actualizar datos básicos transferencia',
                                    'class' => 'btn btn-default btn_update',
                        ]);
                    },

                    'importardataxls' => function ($url, $model){                                
                        $t = Url::to([  'importardataxls', 
                                        'id' => $model->id,
                                    ]);

                        return Html::button('<i class="fa fa-file-excel"></i>',[
                                    'value'=> $t,
                                    'title' => 'Subir Archivo Transferencia',
                                    'class' => 'btn btn-default btn_upload',
                        ]);
                    },

                    'transferencia' => function ($url, $model) {                                  
                        return Html::a('<i class="fa fa-globe"></i>', 
                                [   'transferencia', 'id' => $model->id], 
                                [   'class' => 'btn btn-default',
                                    'title' => 'Transferencia ERP',
                                    'data' => [
                                        'confirm' => 'Esta Seguro de Realizar Transferencia? ( ' . $model->descripcion . ' )',
                                        'method' => 'post',
                                    ]
                                ]
                        );
                    },

                    'errorws' => function ($url, $model) {                                  
                        return Html::a('<i class="fa fa-times"></i>', 
                                [   '/siesa/transferenciaerperror/index', 'idtransferenciaerp' => $model->id], 
                                [   'class' => 'btn btn-default',
                                    'title' => 'Ver Errores Ejecutar WS',
                                ]
                        );
                    },

                    'logws' => function ($url, $model) {                                  
                        return Html::a('<i class="fa fa-clock"></i>', 
                                [   '/siesa/transferencialogws/index', 'idtransferenciaerp' => $model->id], 
                                [   'class' => 'btn btn-default',
                                    'title' => 'Ver Log Ejecutar WS',
                                ]
                        );
                    },


                    'delete' => function ($url, $model) {                                  
                        return Html::a('<i class="fa fa-trash"></i>', 
                                [   'delete', 'id' => $model->id], 
                                [   'class' => 'btn btn-default',
                                    'title' => 'Eliminar Transferencia',
                                    'data' => [
                                        'confirm' => 'Esta Seguro de Eliminar Transferencia? ( ' . $model->descripcion . ' )',
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
