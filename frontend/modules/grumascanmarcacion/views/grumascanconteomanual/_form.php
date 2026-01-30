<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Grumascanconteomanual $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="grumascanconteomanual-form">

    <?php $form = ActiveForm::begin(); ?>

    <!-- <?= $form->field($model, 'idgrumascanconteo')->textInput() ?> -->

    <?= $form->field($model, 'idmarcacion')->textInput() ?>

    <!-- <?= $form->field($model, 'unidades_manual')->textInput() ?>

    <?= $form->field($model, 'unidades_sistema')->textInput() ?>

    <?= $form->field($model, 'diferencia')->textInput() ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'created_by')->textInput() ?>

    <?= $form->field($model, 'updated_at')->textInput() ?>

    <?= $form->field($model, 'updated_by')->textInput() ?> -->

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>