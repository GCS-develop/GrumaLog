<?php

use frontend\models\Talla;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use common\widgets\Alert;
use yii\bootstrap4\Modal;


/** @var yii\web\View $this */
/** @var frontend\models\search\Tallasearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Tallas';
$this->params['breadcrumbs'][] = $this->title;

$this->registerJsFile(
    Yii::$app->request->baseUrl . '/js/mainDataModal.js',
    ['depends' => [\yii\web\JqueryAsset::className()]]
);


?>

<link rel="stylesheet" href="css/shared.css">

<?php
Modal::begin([
    'title' => '<h4>Datos básicos de talla</h4>',
    'id' => 'modaldata',
    'size' => 'modal-lg',
    'options' => [
        'tabindex' => false  // Importante para que funcione el Select
    ]
]);

echo "<div id='modalContentData'></div>";

Modal::end();
?>


<div class="talla-index">


    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
    <?= Alert::widget() ?>

    <div class="row">
        <div class="col-lg-12 centrar">

            <?php $url = Url::to(['create']); ?>
            <?= Html::button(
                'Registrar',
                ['value' => $url, 'class' => 'btn btn-success btn-lg btn-create', 'id' => 'modalButtonCreate']
            )
                ?>
        </div>
        <div class="col-lg-12">

            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                // 'filterModel' => $searchModel,
                'showPageSummary' => false,
                'summary' => 'Mostrando {begin} - {end} de {totalCount} resultados',
                'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
                'options' => [
                    'class' => 'mi-gridview', // Agrega una clase CSS a la tabla generada por el GridView
                ],
                'columns' => [
                    ['class' => 'kartik\grid\SerialColumn'],

                    'id',
                    'codigo',
                    'nombre',
                    'orden',
                    'created_at',
                    [
                        'attribute' => 'created_by',
                        'value' => function ($model) {
                        return $model->usuarioCreador->username;

                    },
                    ],
                    [
                        'attribute' => 'updated_by',
                        'value' => function ($model) {
                        return $model->usuarioActualiza->username;
                    },
                    ],
                    //'created_by',
                    //'updated_at',
                    //'updated_by',
                    [
                        'class' => ActionColumn::className(),
                        'header' => 'Acción',
                        'headerOptions' => ['width' => '8%'],
                        'template' => '  {update} {delete}',
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
                                    'title' => 'Eliminar Registro',
                                    'data' => [
                                        'confirm' => 'Esta seguro de eliminar esta talla: ' . $model->codigo,
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
    </div>
</div>