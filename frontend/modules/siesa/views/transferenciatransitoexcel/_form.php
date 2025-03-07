<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\Transferenciatransitoexcel $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="transferenciatransitoexcel-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'idTransferenciaerp')->textInput() ?>

    <?= $form->field($model, 'centroOperacionDocumento')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'tipoDocumento')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'fechaDocumento')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'bodegaSalidaDocumento')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'bodegaEntradaDocumento')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'centroOperacion')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'tipoDocumentoMovimiento')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'bodegaSalidaMovimiento')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'centroOperacionMovimiento')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'unidadSalida')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'cantidadBase')->textInput() ?>

    <?= $form->field($model, 'costoPromedioUnitario')->textInput() ?>

    <?= $form->field($model, 'item')->textInput() ?>

    <?= $form->field($model, 'color')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'talla')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'numero')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'notas')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
