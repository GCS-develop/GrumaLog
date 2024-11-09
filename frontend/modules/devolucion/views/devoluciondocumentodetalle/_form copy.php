<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\Devoluciondocumentodetalle $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="devoluciondocumentodetalle-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'idDocumento')->textInput() ?>

    <?= $form->field($model, 'codigoBarras')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'cantidadDevolucion')->textInput() ?>

    <?= $form->field($model, 'cantidadRegistrada')->textInput() ?>

    <?= $form->field($model, 'item')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'talla')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'color')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'referencia')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'itemResumen')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'created_by')->textInput() ?>

    <?= $form->field($model, 'updated_at')->textInput() ?>

    <?= $form->field($model, 'updated_by')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
