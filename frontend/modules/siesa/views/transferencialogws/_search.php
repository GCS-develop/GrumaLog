<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\search\TransferencialogwsSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="transferencialogws-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'centroOperacionDocumento') ?>

    <?= $form->field($model, 'tipoDocumento') ?>

    <?= $form->field($model, 'fechaDocumento') ?>

    <?= $form->field($model, 'bodegaSalidaDocumento') ?>

    <?php // echo $form->field($model, 'bodegaEntradaDocumento') ?>

    <?php // echo $form->field($model, 'startDate') ?>

    <?php // echo $form->field($model, 'endDate') ?>

    <?php // echo $form->field($model, 'numeroRegistros') ?>

    <?php // echo $form->field($model, 'mensaje') ?>

    <?php // echo $form->field($model, 'idConectorDinamico') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
