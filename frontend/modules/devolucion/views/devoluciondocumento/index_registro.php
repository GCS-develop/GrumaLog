<?php
use frontend\models\Devoluciondocumentodetalle;

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

use frontend\models\Devoluciondocumento;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use common\widgets\Alert;

/** @var yii\web\View $this */
/** @var frontend\models\search\DevoluciondocumentoSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Devolución - Documentos';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="devoluciondocumento-index">

    <?= Alert::widget() ?>

    <p>
    <div class="col-lg-12 centrar">
                <?= Html::a('Registrar', ['create'], ['class' => 'btn btn-success btn-lg btn-create', 'id'=>'modalButtonCreateTest']) ?>
     </div>
    </p>

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
            ['class' => 'yii\grid\SerialColumn'],

            //'id',
            //'idInterfase

            [
                'attribute' => 'codigoBodegaSalida', // Nombre del atributo en el modelo
                'value' => function ($model){
                    return $model->codigoBodegaSalida . ' - ' . $model->bodegasalida->nombre;
                },
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],

            [
                'attribute' => 'numeroDocumento', // Nombre del atributo en el modelo
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],

            [
                'attribute' => 'notasDocumento', // Nombre del atributo en el modelo
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],

            [
                'attribute' => 'totalCantidadDevolucion', // Nombre del atributo en el modelo
                'value' => function ($model){
                    return Devoluciondocumentodetalle::find()->where(['idDocumento' => $model->id])->sum('cantidadDevolucion');
                },
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'label' => 'Cant. Saldo Devolución'
            ],

            [
                'attribute' => 'totalCantidadRegistra', // Nombre del atributo en el modelo
                'value' => function ($model){
                    return Devoluciondocumentodetalle::find()->where(['idDocumento' => $model->id, 'registrada' => 1])->sum('cantidadRegistrada');
                },
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'label' => 'Cant. Registrada'
            ],

            [
                'attribute' => 'fechaRegistra', // Nombre del atributo en el modelo
                //'format' => ['date', 'php:Y-m-d H:i'],
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'value' => function($model){
                    return substr($model->fechaRegistra,0, 16);
                }
            ],

            [
                'attribute' => 'usuarioRegistra', // Nombre del atributo en el modelo
                'value' => function ($model){
                    return $model->usuarioregistra->username;
                },
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],
            //'created_at',
            //'created_by',
            //'updated_at',
            //'updated_by',
            [
                'class' => ActionColumn::className(),
                'header'=>'Acción',
                'headerOptions' => ['width' => '15%'],
                'template' => '{view}',

                'buttons' => [

                    'view' => function ($url, $model) {                                
                        return Html::a('<i class="fa fa-search"></i>',
                                [   '/devolucion/devoluciondocumentodetalle/index', 'iddocumento' => $model->id, ], 
                                [
                                    'title' => 'Ver Detalle Devolución',
                                    'class' => 'btn btn-default btn_view_detalle',
                                ]
                        );
                    },

                ],

            ],
        ],
    ]); ?>


</div>
