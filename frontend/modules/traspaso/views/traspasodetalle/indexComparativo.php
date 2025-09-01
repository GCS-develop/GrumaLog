<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\ActiveForm;

/* @var $searchModel frontend\models\search\TraspasodetalletiendaSearch */
/* @var $dataProvider yii\data\ArrayDataProvider */

$this->title = 'Comparativo SIESA - Traspaso';
?>

<h1><?= Html::encode($this->title) ?></h1>

<div class="search-form">
    <?php $form = ActiveForm::begin([
        'method' => 'get',
        'action' => ['comparativo-siesa'],
    ]); ?>

    <?= $form->field($searchModel, 'tipoDocumento') ?>
    <?= $form->field($searchModel, 'numero') ?>

    <div class="form-group">
        <?= Html::submitButton('Buscar', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        'item',
        'color',
        'talla',
        [
            'attribute' => 'cantidadLocal',
            'format' => ['decimal', 0],
        ],
        [
            'attribute' => 'cantidadSiesa',
            'format' => ['decimal', 0],
        ],
        [
            'attribute' => 'diferencia',
            'format' => ['decimal', 0],
            'contentOptions' => function ($model) {
            return $model['diferencia'] != 0 ? ['style' => 'color:red'] : [];
        },
        ],
    ],
]); ?>



?>