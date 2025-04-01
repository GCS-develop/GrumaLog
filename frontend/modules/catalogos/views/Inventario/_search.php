<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\search\InventarioSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="inventario-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'codigoBarras') ?>

    <?= $form->field($model, 'item') ?>

    <?= $form->field($model, 'idItem') ?>

    <?= $form->field($model, 'codigoBodega') ?>

    <?php // echo $form->field($model, 'existencia') ?>

    <?php // echo $form->field($model, 'fechaUltimaActualizacion') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'created_by') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <?php // echo $form->field($model, 'updated_by') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
