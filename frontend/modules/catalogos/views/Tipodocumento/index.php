<?php

use frontend\models\Tipodocumento;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;

use common\widgets\Alert;
use yii\bootstrap4\Modal;


/** @var yii\web\View $this */
/** @var frontend\models\search\TipodocumentoSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Tipodocumentos';
$this->params['breadcrumbs'][] = $this->title;

$this->registerJsFile(
    Yii::$app->request->baseUrl . '/js/mainDataModal.js',
    ['depends' => [\yii\web\JqueryAsset::className()]]
);
?>
<link rel="stylesheet" href="css/shared.css">

<?php
Modal::begin([
    'title' => '<h4>Tipos de documentos</h4>',
    'id' => 'modaldata',
    'size' => 'modal-xl',
    'options' => [
        'tabindex' => false  // Importante para que funcione el Select
    ]
]);

echo "<div id='modalContentData'></div>";

Modal::end();
?>

<div class="tipodocumento-index">


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

    <?php // echo $this->render('_search', ['model' => $searchModel]); 
    ?>

    <?= Alert::widget() ?>

    <?= GridView::widget([
        'responsiveWrap' => false,
        'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],

        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'kartik\grid\SerialColumn'],

            'id',
            'codigo',
            'nombre',
            'consecutivoProximo',
            [
                'attribute' => 'requierePedido',
                'format'    => 'raw',
                'value'     => fn($m) => $m->requierePedido ? 'Sí' : 'No',
                'filterType' => GridView::FILTER_SELECT2,
                'filter'     => [1 => 'Sí', 0 => 'No'],
                'filterWidgetOptions' => [
                    'pluginOptions' => ['allowClear' => true],
                ],
                'filterInputOptions' => ['placeholder' => 'Todos'],
                'contentOptions' => ['class' => 'centrar'],
                'width' => '120px',
            ],

            [
                'attribute' => 'permite_cantidad_manual',
                'format'    => 'raw',
                'value'     => fn($m) => $m->permite_cantidad_manual ? 'Sí' : 'No',
                'filterType' => GridView::FILTER_SELECT2,
                'filter'     => [1 => 'Sí', 0 => 'No'],
                'filterWidgetOptions' => [
                    'pluginOptions' => ['allowClear' => true],
                ],
                'filterInputOptions' => ['placeholder' => 'Todos'],
                'contentOptions' => ['class' => 'centrar'],
                'width' => '120px',
            ],
            'created_at',
            //'created_by',
            //'updated_at',
            //'updated_by',

            [
                'class' => ActionColumn::className(),
                'header' => 'Acción',
                'headerOptions' => ['width' => '15%'],
                'template' => '{view} {update}',

                'buttons' => [
                    'view' => function ($url, $model, $key) {
                        $t = Url::to(['view', 'id' => $model->id]);
                        return Html::button('<i class="fa fa-eye"></i>', [
                            'value' => $t,
                            'title' => 'Ver',
                            'class' => 'btn btn-default btn_view', // ← aquí el cambio
                            'type'  => 'button',                   // evita submit accidental
                        ]);
                    },

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


                ]
            ]
        ],
    ]); ?>


</div>