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
');

$this->registerJsFile(Yii::$app->request->baseUrl.'/js/mainDataModal.js',
['depends' => [\yii\web\JqueryAsset::className()]]
);

use frontend\models\Agendapresupuestosubcategoria;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;

use common\models\ProcedimientosGenerales;
use common\widgets\Alert;
use yii\bootstrap4\Modal;

/** @var yii\web\View $this */
/** @var frontend\models\search\AgendapresupuestosubcategoriaSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Período: ' . $model->periodoAnio . ' - ' . ProcedimientosGenerales::nombreMes($model->periodoMes);
$this->params['breadcrumbs'][] = ['label' => 'Fecha recibo mercancia', 'url' => ['/agenda/agendapresupuestosubcategoria/indexperiodo']];

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

<div class="agendapresupuestosubcategoria-index">

    <?= Alert::widget() ?>

    <div class="row">
        <div class="col-lg-12 centrar">
            <?php $url = Url::to(['create', 'idagendapresupuesto' => $model->id]); ?>
            
            <p>
            <?= Html::button('Registrar', 
                        ['value'=>  $url, 'class' => 'btn btn-success btn-lg btn-create', 'id'=>'modalButtonCreate']) 
            ?>
            </p>
        </div>
    </div>    

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,

        'summary' => 'Mostrando {begin} - {end} de {totalCount} resultados',
		'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
		'options' => [
			'class' => 'mi-gridview', // Agrega una clase CSS a la tabla generada por el GridView
		],

        'showPageSummary' => true,

        'columns' => [
            [
                'class' => 'kartik\grid\SerialColumn',
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'width' => '2%',
            ],

            //'id',
            //'idAgendaPresupuesto',
            //'periodoAnio',
            //'periodoMes',
            [
                'attribute' => 'crossDocking', // Nombre del atributo en el modelo
                //'label' => 'Amount', // Etiqueta de la columna
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'width' => '15%',
            ],

            [
                'attribute' => 'categoria', // Nombre del atributo en el modelo
                //'label' => 'Amount', // Etiqueta de la columna
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'width' => '20%',
            ],
            [
                'attribute' => 'subcategoria', // Nombre del atributo en el modelo
                //'label' => 'Amount', // Etiqueta de la columna
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'width' => '20%',
            ],

            [
                'attribute' => 'fechaLlegada',
                //'label' => 'Date',
                'format' => ['date', 'php:Y-m-d'],
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'width' => '10%',
                // También puedes ajustar otras propiedades de estilo según tus necesidades
                //'contentOptions' => ['style' => 'font-weight:bold;'],
            ],

            [
                'attribute' => 'cantidad', // Nombre del atributo en el modelo
                //'label' => 'Amount', // Etiqueta de la columna
                'format' => ['decimal', 0], // Formato decimal con 2 decimales
                'hAlign' => 'right', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'width' => '5%',
                'pageSummary' => true,
                'filter' => ''
            ],

            [
                'attribute' => 'cantidadAgendada', // Nombre del atributo en el modelo
                //'label' => 'Amount', // Etiqueta de la columna
                'format' => ['decimal', 0], // Formato decimal con 2 decimales
                'hAlign' => 'right', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'width' => '5%',
                'pageSummary' => true,
                'filter' => ''
            ],

            [
                'attribute' => 'Disponible', // Nombre del atributo en el modelo
                //'label' => 'Amount', // Etiqueta de la columna
                'value' => function ($model){
                    return $model->cantidad - $model->cantidadAgendada;
                },
                'format' => ['decimal', 0], // Formato decimal con 2 decimales
                'hAlign' => 'right', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'width' => '5%',
                'pageSummary' => true
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
                                    'title' => 'Actualizar Fecha mercancia categoría',
                                    'class' => 'btn btn-default btn_update',
                        ]);
                    },

                    'delete' => function ($url, $model) {                                  
                        return Html::a('<i class="fa fa-trash"></i>', 
                                [   'delete', 'id' => $model->id], 
                                [   'class' => 'btn btn-default',
                                    'title' => 'Eliminar Fecha mercancia categoría',
                                    'data' => [
                                        'confirm' => 'Esta Seguro de Eliminar este Registro? ( ' . $model->fechaLlegada . ' - ' .
                                                                                        $model->crossDocking . ' - ' .
                                                                                        $model->categoria . ' - ' .
                                                                                        $model->subcategoria . ' - ' .
                                                                                        $model->cantidad . ' )',
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
