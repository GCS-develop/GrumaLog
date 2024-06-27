<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\Transferencialogws $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="transferencialogws-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'centroOperacionDocumento')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'tipoDocumento')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'fechaDocumento')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'startDate')->textInput() ?>

    <?= $form->field($model, 'endDate')->textInput() ?>

    <?= $form->field($model, 'numeroRegistros')->textInput() ?>

    <?= $form->field($model, 'mensaje')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'idConectorDinamico')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
