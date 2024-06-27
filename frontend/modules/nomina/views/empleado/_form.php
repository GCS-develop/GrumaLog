<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\Empleado $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="empleado-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'identificacion')->textInput() ?>

    <?= $form->field($model, 'nombreEmpleado')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'ndc')->textInput() ?>

    <?= $form->field($model, 'idEstado')->textInput() ?>

    <?= $form->field($model, 'idCargo')->textInput() ?>

    <?= $form->field($model, 'idCO')->textInput() ?>

    <?= $form->field($model, 'idCC')->textInput() ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'created_by')->textInput() ?>

    <?= $form->field($model, 'updated_at')->textInput() ?>

    <?= $form->field($model, 'updated_by')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
