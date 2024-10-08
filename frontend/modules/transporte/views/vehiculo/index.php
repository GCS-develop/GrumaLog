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
');

$this->registerJsFile(
    Yii::$app->request->baseUrl . '/js/mainDataModal.js',
    ['depends' => [\yii\web\JqueryAsset::className()]]
);

use frontend\models\Vehiculo;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use yii\bootstrap4\Modal;
use common\widgets\Alert;

/** @var yii\web\View $this */
/** @var frontend\models\search\VehiculoSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Vehiculos';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php
Modal::begin([
    'title' => '<h4>Registro datos vehiculo</h4>',
    'id' => 'modaldata',
    'size' => 'modal-lg',
    'options' => [
        'tabindex' => false  // Importante para que funcione el Select
    ]
]);

echo "<div id='modalContentData'></div>";

Modal::end();
?>

<?= Alert::widget() ?>

<div class="vehiculo-index">

    <!-- <p>
        <?= Html::a('Registrar Vehiculo', ['create'], ['class' => 'btn btn-success']) ?>
    </p> -->
    <div class="col-lg-12 centrar">
        <?php $url = Url::to(['create']); ?>

        <?= Html::button(
            'Registrar',
            ['value' => $url, 'class' => 'btn btn-success btn-lg btn-create', 'id' => 'modalButtonCreate']
        )
            ?>
        </p>
    </div>
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
            'id',
            'descripcion',
            'placa',

            [
                'class' => ActionColumn::className(),
                'header' => 'Acción',
                'headerOptions' => ['width' => '15%'],
                'template' => '{update} {delete}',

                'buttons' => [

                    'update' => function ($url, $model) {
                $t = Url::to([
                    'update',
                    'id' => $model->id
                ]);

                return Html::button('<i class="fa fa-edit"></i>', [
                    'value' => $t,
                    'title' => 'Actualizar vehiculo',
                    'class' => 'btn btn-default btn_update',
                ]);
            },


                    'delete' => function ($url, $model) {
                return Html::a(
                    '<i class="fa fa-trash"></i>',
                    ['delete', 'id' => $model->id],
                    [
                        'class' => 'btn btn-default',
                        'title' => 'Eliminar vehiculo',
                        'data' => [
                            'confirm' => 'Esta seguro de eliminar este vehiculo ? ( ' . $model->descripcion . ' )',
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