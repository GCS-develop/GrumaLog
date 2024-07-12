<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\modules\ventas\models\Cotizacionprecio $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="cotizacionprecio-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'codigoProveedor')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'nitProveedor')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'razonSocial')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'item')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'descripcion')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'color')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'talla')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'fechaactivacion')->textInput() ?>

    <?= $form->field($model, 'unidad')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'moneda')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'preciounitario')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'tiempoentrega')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'fechahasta')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
