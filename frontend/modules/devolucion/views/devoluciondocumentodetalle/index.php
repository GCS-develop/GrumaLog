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

    .titulo {
        color: black;
        font-weight: bold;
    }
');

use frontend\models\Devoluciondocumentodetalle;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use common\widgets\Alert;

/** @var yii\web\View $this */
/** @var frontend\models\search\DevoluciondocumentodetalleSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Detalle';
$this->params['breadcrumbs'][] = ['label' => 'Devolución Documentos', 'url' => ['/devolucion/devoluciondocumento/register']];
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
                <?= Html::encode($model->codigoBodegaSalida) . ' - ' . $model->bodegasalida->nombre?>
            </div>
        </div>

        <div class="row">
            <div class="col-12 titulo">
                <?= Html::encode($model->numeroDocumento) ?>
            </div>
        </div>

    </div>
</div>

<div class="devoluciondocumentodetalle-index">

    <?= Alert::widget() ?>

    <p>
    <div class="col-lg-12 centrar">
                <?= Html::a('Registrar', ['create', 'iddocumento' => $model->id], ['class' => 'btn btn-success btn-lg btn-create', 'id'=>'modalButtonCreateTest']) ?>
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

        'showPageSummary' => true,

        'rowOptions' => function ($model) {
                    $options = [];
                
                    if ($model->cantidadDevolucion != $model->cantidadRegistrada){
                        $options['style'] = 'background-color: #ff4d4d; color:white; font-weight: bold;'; // Puedes cambiar el color aquí
                    }
                
                    return $options;
                },

        'columns' => [
            [
                'class' => 'kartik\grid\SerialColumn',
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],

            /*'id',
            'idDocumento',*/
            [
                'attribute' => 'codigoBarras', // Nombre del atributo en el modelo
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],

            [
                'attribute' => 'item', // Nombre del atributo en el modelo
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],

            [
                'attribute' => 'referencia', // Nombre del atributo en el modelo
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],

            [
                'attribute' => 'itemResumen', // Nombre del atributo en el modelo
                'hAlign' => 'left', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],
            //'talla',
            //'color',

            [
                'attribute' => 'cantidadDevolucion', // Nombre del atributo en el modelo
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'label' => 'Cant. Saldo',
                'filter' => '',
                'pageSummary' => true,
            ],

            [
                'attribute' => 'cantidadRegistrada', // Nombre del atributo en el modelo
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'label' => 'Cant. Registrada',
                'filter' => '',
                'pageSummary' => true,
            ],

            [
                'attribute' => 'fechaRegistra', // Nombre del atributo en el modelo
                //'format' => ['date', 'php:Y-m-d H:i'],
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
                'value' => function($model){
                    if ($model->fechaRegistra){
                        return substr($model->fechaRegistra,0, 16);
                    }

                    return '-';
                }
            ],

            [
                'attribute' => 'usuarioRegistra', // Nombre del atributo en el modelo
                'value' => function ($model){
                    return $model->usuarioregistra ? $model->usuarioregistra->username : ' - ';
                },
                'hAlign' => 'center', // Alineación horizontal al centro
                'vAlign' => 'middle', // Alineación vertical al centro
            ],

            [
                'class' => ActionColumn::className(),
                'header'=>'Acción',
                //'headerOptions' => ['width' => '15%'],
                'template' => '{viewconteo}',

                'buttons' => [

                    'viewconteo' => function ($url, $model) {                                
                        return Html::a('<i class="fa fa-list"></i>',
                                [   'viewconteo',
                                    'id' => $model->id
                                ], 
                                [
                                    'title' => 'Ver Registro de Conteo',
                                    'class' => 'btn btn-default btn_detalle',
                                ]
                        );
                    },


                ],

            ],
        ],
    ]); ?>


</div>
