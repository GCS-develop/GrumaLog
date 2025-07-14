<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\Siesaconector $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="siesaconector-form">

    <?php $form = ActiveForm::begin(); ?>
    <div class="row">
        <div class="col-2">
            <?= $form->field($model, 'nombre')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-2">
            <?= $form->field($model, 'id_compania')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-2">
            <?= $form->field($model, 'id_documento')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-2">
            <?= $form->field($model, 'nombre_documento')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-2">
            <?= $form->field($model, 'id_sistema')->textInput(['maxlength' => true]) ?>
        </div>

        <div class="col-2">
            <?= $form->field($model, 'header_key')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-2">
            <?= $form->field($model, 'header_token')->textarea(['rows' => 6]) ?>
        </div>
        <div class="col-2">
            <?= $form->field($model, 'url_base')->textarea(['rows' => 6]) ?>
        </div>

    </div>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>