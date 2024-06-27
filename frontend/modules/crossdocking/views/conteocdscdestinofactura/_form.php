<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\Conteocdscdestinofactura $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="conteocdscdestinofactura-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'idProveedor')->textInput() ?>

    <?= $form->field($model, 'numeroFactura')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'fecha')->textInput() ?>

    <?= $form->field($model, 'idCentroOperacionLegaliza')->textInput() ?>

    <?= $form->field($model, 'totalUnidades')->textInput() ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'created_by')->textInput() ?>

    <?= $form->field($model, 'updated_at')->textInput() ?>

    <?= $form->field($model, 'updated_by')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
