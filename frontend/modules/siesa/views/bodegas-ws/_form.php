<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var common\models\Bodegas $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="bodegas-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'Codigo_Bodega')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'Descripcion_Bodega')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
