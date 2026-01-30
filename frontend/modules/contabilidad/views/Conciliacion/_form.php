<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\ventasimportadas $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="ventasimportadas-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'codigoCentroOperacion')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'nombreCentroOperacion')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'fecha')->textInput() ?>

    <?= $form->field($model, 'rowid_item_ext')->textInput() ?>

    <?= $form->field($model, 'item')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'codigobarra')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'descripcion')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'descripcionCorta')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'color')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'talla')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'referencia')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'nombreproveedor')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'proveedor')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'unidades')->textInput() ?>

    <?= $form->field($model, 'precio_aplicado')->textInput() ?>

    <?= $form->field($model, 'total')->textInput() ?>

    <?= $form->field($model, 'costo_prom_tot')->textInput() ?>

    <?= $form->field($model, 'costo_prom_mp')->textInput() ?>

    <?= $form->field($model, 'factor')->textInput() ?>

    <?= $form->field($model, 'unidadmedida')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
