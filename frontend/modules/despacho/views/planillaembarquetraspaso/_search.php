<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\search\PlanillaembarquetraspasoSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="planillaembarquetraspaso-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'idPlanillaEmbarque') ?>

    <?= $form->field($model, 'idTraspaso') ?>

    <?= $form->field($model, 'idBodegaOrigen') ?>

    <?= $form->field($model, 'idBodegaDestino') ?>

    <?php // echo $form->field($model, 'unidades') ?>

    <?php // echo $form->field($model, 'unidadesEmp') ?>

    <?php // echo $form->field($model, 'sello') ?>

    <?php // echo $form->field($model, 'fechaRecibido') ?>

    <?php // echo $form->field($model, 'idUsuarioRecibido') ?>

    <?php // echo $form->field($model, 'idEstado') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'created_by') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <?php // echo $form->field($model, 'updated_by') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
