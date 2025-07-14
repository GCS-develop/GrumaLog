<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\search\SiesaconectordocumentocampoSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="siesa-conector-documento-campo-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'conector_id') ?>

    <?= $form->field($model, 'nombre_campo') ?>

    <?= $form->field($model, 'alias') ?>

    <?= $form->field($model, 'tipo_dato') ?>

    <?php // echo $form->field($model, 'obligatorio') ?>

    <?php // echo $form->field($model, 'created_by') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'updated_by') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
