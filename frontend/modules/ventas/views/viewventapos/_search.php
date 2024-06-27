<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\modules\ventas\models\search\ViewventaposSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="viewventapos-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'codigoCentroOperacion') ?>

    <?= $form->field($model, 'nombreCentroOperacion') ?>

    <?= $form->field($model, 'fecha') ?>

    <?= $form->field($model, 'item') ?>

    <?php // echo $form->field($model, 'referencia') ?>

    <?php // echo $form->field($model, 'descripcion') ?>

    <?php // echo $form->field($model, 'color') ?>

    <?php // echo $form->field($model, 'talla') ?>

    <?php // echo $form->field($model, 'subtotal') ?>

    <?php // echo $form->field($model, 'proveedor') ?>

    <?php // echo $form->field($model, 'nombreProveedor') ?>

    <?php // echo $form->field($model, 'unidades') ?>

    <?php // echo $form->field($model, 'preciounitario') ?>

    <?php // echo $form->field($model, 'total') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
