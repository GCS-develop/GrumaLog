<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\Facturaentregamercancia $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="facturaentregamercancia-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'idAgendaEntregaMercancia')->textInput() ?>

    <?= $form->field($model, 'idProgramacionEntregaMercancia')->textInput() ?>

    <?= $form->field($model, 'numeroFactura')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'observaciones')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'created_by')->textInput() ?>

    <?= $form->field($model, 'updated_at')->textInput() ?>

    <?= $form->field($model, 'updated_by')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
