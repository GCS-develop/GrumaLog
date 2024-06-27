<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\search\AgendapresupuestosubcategoriaSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="agendapresupuestosubcategoria-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'idAgendaPresupuesto') ?>

    <?= $form->field($model, 'periodoAnio') ?>

    <?= $form->field($model, 'periodoMes') ?>

    <?= $form->field($model, 'crossDocking') ?>

    <?php // echo $form->field($model, 'categoria') ?>

    <?php // echo $form->field($model, 'subcategoria') ?>

    <?php // echo $form->field($model, 'fechaLlegada') ?>

    <?php // echo $form->field($model, 'cantidad') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
