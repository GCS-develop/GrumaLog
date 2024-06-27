<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\modules\ventas\models\Transferencia $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="transferencia-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'idFactura')->textInput() ?>

    <?= $form->field($model, 'codigoBarra')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'item')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'color')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'talla')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'unidadMedida')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'bodega')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'motivo')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'cantidadBase')->textInput() ?>

    <?= $form->field($model, 'precioUnitario')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'referencia')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'descripcion')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
