<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\search\ConteocdscdestinofacturaSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="conteocdscdestinofactura-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'idProveedor') ?>

    <?= $form->field($model, 'numeroFactura') ?>

    <?= $form->field($model, 'fecha') ?>

    <?= $form->field($model, 'idCentroOperacionLegaliza') ?>

    <?php // echo $form->field($model, 'totalUnidades') ?>

    <?php // echo $form->field($model, 'idEstado') ?>

    <?php // echo $form->field($model, 'idLegalizado') ?>

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
