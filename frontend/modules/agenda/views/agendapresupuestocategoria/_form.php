<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\Agendapresupuestocategoria $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="agendapresupuestocategoria-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'periodoAnio')->textInput() ?>

    <?= $form->field($model, 'periodoMes')->textInput() ?>

    <?= $form->field($model, 'crossDocking')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'categoria')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'fechaLlegada')->textInput() ?>

    <?= $form->field($model, 'cantidad')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
