<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\search\PedidoordendecompraSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="pedidoordendecompra-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'idPedido') ?>

    <?= $form->field($model, 'idOrdenCompra') ?>

    <?= $form->field($model, 'nombreArchivo') ?>

    <?= $form->field($model, 'nroItems') ?>

    <?php // echo $form->field($model, 'totalUnidades') ?>

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
