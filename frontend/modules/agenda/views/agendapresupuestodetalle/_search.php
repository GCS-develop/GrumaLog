<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\search\AgendapresupuestodetalleSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="agendapresupuestodetalle-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'idAgendaPresupuesto') ?>

    <?= $form->field($model, 'codigo') ?>

    <?= $form->field($model, 'subcategoria') ?>

    <?= $form->field($model, 'consumidor') ?>

    <?php // echo $form->field($model, 'universo') ?>

    <?php // echo $form->field($model, 'producto') ?>

    <?php // echo $form->field($model, 'tendencia') ?>

    <?php // echo $form->field($model, 'talla') ?>

    <?php // echo $form->field($model, 'tipo') ?>

    <?php // echo $form->field($model, 'modelo') ?>

    <?php // echo $form->field($model, 'cantidad') ?>

    <?php // echo $form->field($model, 'fechaLlegada') ?>

    <?php // echo $form->field($model, 'crossDocking') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
