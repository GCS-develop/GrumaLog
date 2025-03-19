<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\Conteobylecturacodigo $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="conteobylecturacodigo-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'modulo')->textInput() ?>

    <?= $form->field($model, 'idConteoFactura')->textInput() ?>

    <?= $form->field($model, 'idConteoDestino')->textInput() ?>

    <?= $form->field($model, 'idConteoDetalle')->textInput() ?>

    <?= $form->field($model, 'codigoBarras')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'unidades')->textInput() ?>

    <?= $form->field($model, 'isMobile')->textInput() ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'created_by')->textInput() ?>

    <?= $form->field($model, 'updated_at')->textInput() ?>

    <?= $form->field($model, 'updated_by')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
