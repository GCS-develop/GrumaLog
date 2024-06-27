<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\search\OrdendecompradetalleSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="ordendecompradetalle-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'idOrdenCompra') ?>

    <?= $form->field($model, 'idItem') ?>

    <?= $form->field($model, 'idCategoria') ?>

    <?= $form->field($model, 'idSubcategoria') ?>

    <?php // echo $form->field($model, 'cantidadPedida') ?>

    <?php // echo $form->field($model, 'cantidadEntrada') ?>

    <?php // echo $form->field($model, 'cantidadPendiente') ?>

    <?php // echo $form->field($model, 'fechaEntrega') ?>

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
