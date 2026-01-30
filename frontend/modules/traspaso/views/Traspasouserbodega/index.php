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

$this->registerJsFile(
    Yii::$app->request->baseUrl . '/js/mainDataModal.js',
    ['depends' => [\yii\web\JqueryAsset::className()]]
);

use frontend\models\Traspasouserbodega;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;

use common\widgets\Alert;
use frontend\models\Bodegas;
use yii\bootstrap4\Modal;

/** @var yii\web\View $this */
/** @var frontend\models\search\TraspasouserbodegaSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Bodegas por usuario';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php
Modal::begin([
    'title' => '<h4>Datos básicos bodegas por usuario</h4>',
    'id' => 'modaldata',
    'size' => 'modal-lg',
    'options' => [
        'tabindex' => false  // Importante para que funcione el Select
    ]
]);

echo "<div id='modalContentData'></div>";

Modal::end();
?>

<div class="traspasouserbodega-index">

    <?= Alert::widget() ?>

    <div class="row">
        <div class="col-lg-12 centrar">

            <?php $url = Url::to(['create']); ?>

            <p>
                <?= Html::button(
                    'Registrar',
                    ['value' => $url, 'class' => 'btn btn-success btn-lg btn-create', 'id' => 'modalButtonCreate']
                )
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


        'columns' => [

            ['class' => 'kartik\grid\SerialColumn'],
            [
                'attribute' => 'buscarnombreusuario',
                'group' => true,
            ],
            //
            // [
            //     'attribute' => 'idBodega',
            //     'value' => function ($model) {
            //         if ($model->bodega->tipodocumento) {
            //             return $model->idBodega . ' - ' . $model->bodega->nombre . ' - ' . $model->bodega->tipodocumento->tipodocumento->codigo;
            //         } else {
            //             return $model->idBodega . ' - ' . $model->bodega->nombre . ' -  La bodega no tiene asignado un tipo documento';
            //         }
            //     }

            // ]
            [
                'attribute' => 'buscarnombrebodega',
                //   'filter' => Bodegas::getListaData(),
                'label' => 'Bodega',
            ],
            [
                'attribute' => 'idEstado',
            ],
            [
                'class' => ActionColumn::className(),
                'header' => 'Acción',
                'headerOptions' => ['width' => '10%'],
                'template' => '{update} {delete}',
                'buttons' => [
                    'update' => function ($url, $model) {
                        $t = Url::to([
                            'update',
                            'id' => $model->id
                        ]);

                        return Html::button('<i class="fa fa-edit"></i>', [
                            'value' => $t,
                            'title' => 'Actualizar',
                            'class' => 'btn btn-default btn_update',
                        ]);
                    },

                    'delete' => function ($url, $model) {
                        return Html::a(
                            '<i class="fa fa-trash"></i>',
                            ['delete', 'id' => $model->id],
                            [
                                'class' => 'btn btn-default',
                                'title' => 'Eliminar Categoría',
                                'data' => [
                                    'confirm' => 'Esta accion eliminara del usuario ' . $model->userTraspaso->empleadoLogistica->empleado->nombreEmpleado
                                        . ' la bodega ' . strtolower($model->bodega->nombre) .
                                        ' para eligir desde traspasos ',
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