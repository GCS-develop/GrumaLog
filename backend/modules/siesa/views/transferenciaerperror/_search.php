<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\search\TransferenciaerperrorSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="transferenciaerperror-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'idTransferenciaerp') ?>

    <?= $form->field($model, 'centroOperacionDocumento') ?>

    <?= $form->field($model, 'tipoDocumento') ?>

    <?= $form->field($model, 'numeroLinea') ?>

    <?php // echo $form->field($model, 'tipoRegistro') ?>

    <?php // echo $form->field($model, 'subTipoRegistro') ?>

    <?php // echo $form->field($model, 'version') ?>

    <?php // echo $form->field($model, 'nivel') ?>

    <?php // echo $form->field($model, 'valor') ?>

    <?php // echo $form->field($model, 'detalle') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
