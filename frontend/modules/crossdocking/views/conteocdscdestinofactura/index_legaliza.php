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
');

use frontend\models\Conteocdscdestinofactura;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;

/** @var yii\web\View $this */
/** @var frontend\models\search\ConteocdscdestinofacturaSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Legalizar Factura CDSC';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="conteocdscdestinofactura-index">

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
            //['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'id', // Nombre del atributo en el modelo
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
            ],

            [
                'attribute' => 'codigoProveedor', // Nombre del atributo en el modelo
                //'label' => 'Año', // Etiqueta de la columna
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],
            [
                'attribute' => 'nit', // Nombre del atributo en el modelo
                //'label' => 'Año', // Etiqueta de la columna
                'hAlign' => 'right', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
            ], 

            [
                'attribute' => 'razonSocial',
                'vAlign'=>'middle',
                'hAlign'=>'left',  
            ],

            [
                'attribute' => 'numeroFactura',
                'vAlign'=>'middle',
                'hAlign'=>'center',  
            ],

            [
                'attribute' => 'fecha', // Nombre del atributo en el modelo
                //'label' => 'Desde', // Etiqueta de la columna
                'format' => ['date', 'php:Y-m-d'],
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro,
                'filter' => '',
            ],

            [
                'attribute' => 'totalUnidades', // Nombre del atributo en el modelo
                //'label' => 'Año', // Etiqueta de la columna
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'filter' => ''
            ],  

            //'idCentroOperacionLegaliza',

            [
                'attribute' => 'idEstado',
                'format' => 'html',
                'vAlign'=>'middle',
                'hAlign'=>'left',                
                'value' => function($model) {
                    $estado = '';
                    switch($model->idEstado){
                        case 1:
                            $estado = 'En Conteo'; break;
                        case 2:
                            $estado = 'Finalizada'; break;
                        default:
                            $estado = 'Sin Conteo'; break;
                    }
                    
                    return $estado;
                },
                'filter' => ['0' => 'Sin Conteo', '1' => 'En Conteo', '2' => 'Finalizada'],
            ],

            [
                'attribute' => 'idLegalizado',
                'format' => 'html',
                'vAlign'=>'middle',
                'hAlign'=>'left',                
                'value' => function($model) {
                    $estado = '';
                    switch($model->idLegalizado){
                        case 1:
                            $estado = 'Legalizado'; break;
                        default:
                            $estado = 'No Legalizado'; break;
                    }
                    
                    return $estado;
                },
                'filter' => ['0' => 'No Legalizado', '1' => 'Legalizado'],
            ],

            [
                'attribute' => 'almacen',
                'vAlign'=>'middle',
                'hAlign'=>'left',  
            ],

            //'created_at',
            //'created_by',
            //'updated_at',
            //'updated_by',
            [
                'class' => ActionColumn::className(),
                'header'=>'Acción',
                //'headerOptions' => ['width' => '15%'],
                'template' => '{indexalmacen} {legalizaconteo}',

                'buttons' => [

                    'indexalmacen' => function ($url, $model) {                                  
                        return Html::a('<i class="fa fa-store"></i>', 
                                [   'indexalmacen', 'idconteofactura' => $model->id, 'origen' => 'legaliza'], 
                                [   'class' => 'btn btn-default',
                                    'title' => 'Visualizar Conteo por Almacén',
                                ]
                        );
                    },

                    'legalizaconteo' => function ($url, $model) {                                  
                        return Html::a('<i class="fa fa-check"></i>', 
                                [   'viewlegalizaconteo', 'idconteofactura' => $model->id], 
                                [   'class' => 'btn btn-default',
                                    'title' => 'Legalizar Factura',
                                    'data' => [
                                        'confirm' => 'Esta Seguro de Legalizar Esta Factura? ( Proveedor:' . $model->nit . '-' . 
                                                                                        $model->razonSocial . ' - Factura:' .
                                                                                        $model->numeroFactura . ' )',
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
