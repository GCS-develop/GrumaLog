<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\Agendapresupuestodetalle $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="agendapresupuestodetalle-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'idAgendaPresupuesto')->textInput() ?>

    <?= $form->field($model, 'codigo')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'subcategoria')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'consumidor')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'universo')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'producto')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'tendencia')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'talla')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'tipo')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'modelo')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'cantidad')->textInput() ?>

    <?= $form->field($model, 'fechaLlegada')->textInput() ?>

    <?= $form->field($model, 'crossDocking')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
