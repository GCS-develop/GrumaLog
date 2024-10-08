<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\Planillaembarque $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="planillaembarque-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'fechaDespacho')->textInput() ?>

    <?= $form->field($model, 'horaDespacho')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'idTransportadora')->textInput() ?>

    <?= $form->field($model, 'idVehiculo')->textInput() ?>

    <?= $form->field($model, 'placa')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'idConductor')->textInput() ?>

    <?= $form->field($model, 'nombreConductor')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'sello')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'created_by')->textInput() ?>

    <?= $form->field($model, 'updated_at')->textInput() ?>

    <?= $form->field($model, 'updated_by')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
