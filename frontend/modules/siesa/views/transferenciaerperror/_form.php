<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\Transferenciaerperror $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="transferenciaerperror-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'idTransferenciaerp')->textInput() ?>

    <?= $form->field($model, 'centroOperacionDocumento')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'tipoDocumento')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'numeroLinea')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'tipoRegistro')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'subTipoRegistro')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'version')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'nivel')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'valor')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'detalle')->textarea(['rows' => 6]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
