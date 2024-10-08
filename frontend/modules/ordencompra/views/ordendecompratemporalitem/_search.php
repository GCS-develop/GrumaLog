<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\search\OrdendecompratemporalitemSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="ordendecompratemporalitem-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'idOrdenCompra') ?>

    <?= $form->field($model, 'numeroRegistro') ?>

    <?= $form->field($model, 'idBodega') ?>

    <?= $form->field($model, 'codigoMotivo') ?>

    <?php // echo $form->field($model, 'idCOMovimiento') ?>

    <?php // echo $form->field($model, 'cantidadPedida') ?>

    <?php // echo $form->field($model, 'fechaEntrega') ?>

    <?php // echo $form->field($model, 'precioUnitario') ?>

    <?php // echo $form->field($model, 'idItem') ?>

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
