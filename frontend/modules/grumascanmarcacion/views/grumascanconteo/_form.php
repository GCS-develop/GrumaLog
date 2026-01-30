<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\Grumascanconteo $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="grumascanconteo-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'idmarcacion')->textInput() ?>

    <?= $form->field($model, 'idestado')->textInput() ?>

    <?= $form->field($model, 'ultimoean')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'totalregistros')->textInput() ?>

    <?= $form->field($model, 'totalunidades')->textInput() ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'created_by')->textInput() ?>

    <?= $form->field($model, 'updated_at')->textInput() ?>

    <?= $form->field($model, 'updated_by')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
