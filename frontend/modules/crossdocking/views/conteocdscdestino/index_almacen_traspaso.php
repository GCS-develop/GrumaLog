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

$this->registerJsFile(Yii::$app->request->baseUrl.'/js/mainDataModal.js',
['depends' => [\yii\web\JqueryAsset::className()]]
);

use frontend\models\Conteocdscdestino;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use kartik\detail\DetailView;
use common\widgets\Alert;
use yii\bootstrap4\Modal;

/** @var yii\web\View $this */
/** @var frontend\models\search\ConteocdscdestinoSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Conteo por Destino';
$this->params['breadcrumbs'][] = ['label' => 'Traspaso Factura CDSC', 'url' => ['/crossdocking/conteocdscdestinofactura/indextraspaso']]; 
$this->params['breadcrumbs'][] = $this->title;
?>

<?php
    Modal::begin([                
        'title'=>'<h4>Registro datos para impresión</h4>',
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
        'label'=>'SECCIÓN 1: Información Orden de Compra',
        'rowOptions'=>['class'=>'table-info']
    ],
    [
        'columns' => [
            [
                'attribute'=>'nit', 
                'label'=>'Nit Proveedor',
                'displayOnly'=>true,
                'labelColOptions'=>['style'=>'width:10%'],
                'valueColOptions'=>['style'=>'width:15%'],
                'value' => $modelfactura->proveedor->nit,
            ],

            [
                'attribute'=>'razonSocial', 
                'label'=>'Nombre Proveedor',
                'displayOnly'=>true,
                'labelColOptions'=>['style'=>'width:10%'],
                'valueColOptions'=>['style'=>'width:30%'],
                'value' => $modelfactura->proveedor->razonSocial,
            ],

            [
                'attribute'=>'numeroFactura', 
                'label'=>'Orden de Compra',
                'displayOnly'=>true,
                'labelColOptions'=>['style'=>'width:10%'],
                'valueColOptions'=>['style'=>'width:15%'],
                'value' => $modelfactura->numeroFactura,
            ],

        ],
    ],

];
?>

<div class="conteocdscdestino-index">

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
    <?= Alert::widget() ?>

    <div class="row">
        <div class="col-lg-12">

        <p>
        <?=
            DetailView::widget([
                'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => '-'],
                'options' => ['style' => 'font-size:14px;'],
                'model' => $modelfactura,
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

            <?= Html::a('Generar Todos Traspasos', 
            ['/crossdocking/temptransferenciatransitoexcel/ejecutartransferenciatodos', 'idconteofactura' => $modelfactura->id], 
            ['class' => 'btn btn-success btn-lg btn-create', 'id'=>'modalButtonCreate']) 
            ?>

        </div>
    </div>  

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        //'filterModel' => $searchModel,

        //'summary' => '<h3 style="background-color: #f0f0f0; padding: 10px; text-align: center; margin-bottom: 20px;">DESTINOS</h3>',
        'summary' => 'Mostrando {begin} - {end} de {totalCount} Destinos',
		'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
		'options' => [
			'class' => 'mi-gridview', // Agrega una clase CSS a la tabla generada por el GridView
		],
        
        'showPageSummary' => true,

        'columns' => [
            //['class' => 'yii\grid\SerialColumn'],
            'id',

            [
                'attribute' => 'created_at', // Nombre del atributo en el modelo
                //'label' => 'Desde', // Etiqueta de la columna
                'format' => ['date', 'php:Y-m-d H:i'],
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],  

            [
                'attribute' => 'updated_at', // Nombre del atributo en el modelo
                //'label' => 'Desde', // Etiqueta de la columna
                'format' => ['date', 'php:Y-m-d H:i'],
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],

            'codigoAlmacen',
            'almacen',

            [
                'attribute' => 'numeroCajas', // Nombre del atributo en el modelo
                'label' => 'Total Cajas', // Etiqueta de la columna
                'hAlign' => 'right', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'format' => ['decimal', 0], // Formato decimal con 0 decimales,
                'pageSummary' => true,
        
            ],

            [
                'attribute' => 'total', // Nombre del atributo en el modelo
                'label' => 'Total UND EMP', // Etiqueta de la columna
                'hAlign' => 'right', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'format' => ['decimal', 0], // Formato decimal con 0 decimales,
                'value' => function ($model){
                    return Conteocdscdestino::getTotalUnidadesEmp($model->id);
                },
                'pageSummary' => true,
        
            ],

            [
                'attribute' => 'total', // Nombre del atributo en el modelo
                'label' => 'Total UND Conteo', // Etiqueta de la columna
                'hAlign' => 'right', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'format' => ['decimal', 0], // Formato decimal con 0 decimales,
                'pageSummary' => true,
                'value' => function ($model){
                    return Conteocdscdestino::getTotalUnidades($model->id);
                },
            ],

            [
                'attribute' => 'idErpTraspaso', // Nombre del atributo en el modelo
                'label' => 'Traspaso ERP', // Etiqueta de la columna
                'hAlign' => 'right', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'value' => function ($model) {
                    if ($model->idErpTraspaso){
                        return $model->codigoerp->f350_id_tipo_docto. 
                        $model->codigoerp->f350_consec_docto;
                    }
                    return '';
                }
            ],

            [
                'class' => ActionColumn::className(),
                'header'=>'Acción',
                //'headerOptions' => ['width' => '15%'],
                'template' => '{viewtraspasodetalle}',

                'buttons' => [

                    'viewtraspasodetalle' => function ($url, $model) {                                  
                        return Html::a('<i class="fa fa-eye"></i>', 
                                [   '/crossdocking/temptransferenciatransitoexcel/index',
                                            'idconteofactura' => $model->idConteocdscdestinofactura, 
                                            'idconteodestino' => $model->id,
                                    ], 
                                [   'class' => 'btn btn-default',
                                    'title' => 'Visualizar Items Traspaso',
                                    'data' => [
                                        'confirm' => 'Esta Seguro de Visualizar Traspaso ? ( ' . $model->razonSocial . ' - ' . $model->numeroFactura  .' )',
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
