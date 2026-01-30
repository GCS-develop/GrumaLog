<?php

use frontend\models\Impresoraspaxarbodega;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use kartik\export\ExportMenu;
use common\widgets\Alert;
use yii\bootstrap4\Modal;
use frontend\models\Bodegas;


/** @var yii\web\View $this */
/** @var frontend\models\search\ImpresoraspaxarbodegaSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Configurar impresora paxar segun bodega';
$this->params['breadcrumbs'][] = $this->title;

$this->registerJsFile(
    Yii::$app->request->baseUrl . '/js/mainDataModal.js',
    ['depends' => [\yii\web\JqueryAsset::className()]]
);

?>

<link rel="stylesheet" href="css/shared.css">
<?php
Modal::begin([
    'title' => '<h4>Datos básicos paxar</h4>',
    'id' => 'modaldata',
    'size' => 'modal-lg',
    'options' => [
        'tabindex' => false  // Importante para que funcione el Select
    ]
]);

echo "<div id='modalContentData'></div>";

Modal::end();
?>
<div class="impresoraspaxarbodega-index">


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

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
    <?= Alert::widget() ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'showPageSummary' => true,
        'summary' => 'Mostrando {begin} - {end} de {totalCount} resultados',
        'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
        'options' => [
            'class' => 'mi-gridview', // Agrega una clase CSS a la tabla generada por el GridView
        ],

        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            // 'id',
            // 'bodega_id',
            [
                'attribute' => 'bodega_id', // Nombre del atributo en el modelo
                'filter' => Bodegas::getListaData(),
                'value' => function ($model) {
        return $model->bodega->codigo . ' - ' . $model->bodega->nombre;
    }
            ],
            'tipo',
            'ip',
            'puerto',
            'recurso',
            'created_at',
            [
                'attribute' => 'created_by',
                'label' => 'Usuario creador',
                'contentOptions' => ['data-cellvalue' => 'Usuario'],
                'value' => function ($model) {
        return $model->created_by . ' - ' . $model->createdby->username;
    },
            ],
            //'updated_at',
            //'updated_by',
            [
                'class' => ActionColumn::className(),
                'header' => 'Acción',
                'headerOptions' => ['width' => '10%'],
                'template' => '{update} {delete} ',
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
                '<i class="fa fa-ban"></i>',
                ['delete', 'id' => $model->id],
                [
                    'class' => 'btn btn-default',
                    'title' => 'Borrar registro',
                    'data' => [
                        'confirm' => 'Esta seguro de delete este registro? ' . $model->id .
                            ' tienda: ' . $model->bodega->nombre,
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