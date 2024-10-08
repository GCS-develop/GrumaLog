<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\search\EmpleadoSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="empleado-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'identificacion') ?>

    <?= $form->field($model, 'nombreEmpleado') ?>

    <?= $form->field($model, 'ndc') ?>

    <?= $form->field($model, 'idEstado') ?>

    <?php // echo $form->field($model, 'idCargo') ?>

    <?php // echo $form->field($model, 'idCO') ?>

    <?php // echo $form->field($model, 'idCC') ?>

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
