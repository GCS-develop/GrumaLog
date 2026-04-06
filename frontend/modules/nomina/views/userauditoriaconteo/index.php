<?php

$this->registerCss('
    .mi-gridview { font-size: 14px; }
    .centrar     { text-align: center; }
');

$this->registerJsFile(
    Yii::$app->request->baseUrl . '/js/mainDataModal.js',
    ['depends' => [\yii\web\JqueryAsset::className()]]
);

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;
use yii\bootstrap4\Modal;
use common\widgets\Alert;

/** @var yii\web\View $this */
/** @var frontend\models\search\UserauditoriaconteoSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Usuarios Auditoría Entrada';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php
Modal::begin([
    'title'   => '<h4>Registrar Usuario Auditoría Entrada</h4>',
    'id'      => 'modaldata',
    'size'    => 'modal-lg',
    'options' => ['tabindex' => false],
]);
echo "<div id='modalContentData'></div>";
Modal::end();
?>

<div class="userauditoriaconteo-index">

    <?= Alert::widget() ?>

    <div class="row mb-2">
        <div class="col-lg-12 centrar">
            <?php $url = Url::to(['create']); ?>
            <?= Html::button('Registrar Usuario', [
                'value' => $url,
                'class' => 'btn btn-success btn-lg',
                'id'    => 'modalButtonCreate',
            ]) ?>
        </div>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel'  => $searchModel,
        'summary'      => 'Mostrando {begin} - {end} de {totalCount} resultados',
        'formatter'    => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
        'options'      => ['class' => 'mi-gridview'],
        'columns'      => [
            [
                'class'  => 'kartik\grid\SerialColumn',
                'hAlign' => 'left',
                'vAlign' => 'middle',
                'width'  => '5%',
            ],
            [
                'attribute' => 'identificacion',
                'format'    => ['decimal', 0],
                'hAlign'    => 'right',
                'vAlign'    => 'middle',
                'width'     => '15%',
            ],
            [
                'attribute' => 'nombreEmpleado',
                'label'     => 'Nombre Empleado',
                'hAlign'    => 'left',
                'vAlign'    => 'middle',
                'width'     => '35%',
            ],
            [
                'attribute' => 'username',
                'label'     => 'Usuario',
                'hAlign'    => 'left',
                'vAlign'    => 'middle',
                'width'     => '15%',
            ],
            [
                'attribute'          => 'status',
                'label'              => 'Estado',
                'hAlign'             => 'left',
                'vAlign'             => 'middle',
                'filter'             => ['0' => 'Inactivo', '10' => 'Activo'],
                'filterInputOptions' => ['class' => 'form-control', 'prompt' => 'Todos'],
                'value'              => fn($m) => $m->status == 10 ? 'Activo' : 'Inactivo',
                'width'              => '15%',
            ],
            [
                'class'         => 'yii\grid\ActionColumn',
                'header'        => 'Acción',
                'headerOptions' => ['width' => '10%'],
                'template'      => '{delete}',
                'buttons'       => [
                    'delete' => function ($url, $model) {
                        return Html::a('<i class="fa fa-trash"></i>', ['delete', 'id' => $model->id], [
                            'class' => 'btn btn-default btn-sm',
                            'title' => 'Eliminar',
                            'data'  => [
                                'confirm' => '¿Eliminar usuario ' . $model->nombreEmpleado . '?',
                                'method'  => 'post',
                            ],
                        ]);
                    },
                ],
            ],
        ],
    ]); ?>

</div>
