<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\Auditoriamanualimportaciondetalle $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="auditoriamanualimportaciondetalle-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'idInterfase')->textInput() ?>

    <?= $form->field($model, 'co')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'fecha')->textInput() ?>

    <?= $form->field($model, 'bodegaSalida')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'item')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'talla')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'color')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'numeroDocumento')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'notasDocumento')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'bodegaEntrada')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'codigoBodegaEntrada')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'codigoBodegaSalida')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'referencia')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'itemResumen')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'unidadMedida')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'cantidad')->textInput() ?>

    <?= $form->field($model, 'categoria')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'proveedor')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'codigoBarras')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
