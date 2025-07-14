<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\search\SiesaconectorSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="siesaconector-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'nombre') ?>

    <?= $form->field($model, 'url_base') ?>

    <?= $form->field($model, 'id_compania') ?>

    <?= $form->field($model, 'id_documento') ?>

    <?php // echo $form->field($model, 'nombre_documento') ?>

    <?php // echo $form->field($model, 'id_sistema') ?>

    <?php // echo $form->field($model, 'header_key') ?>

    <?php // echo $form->field($model, 'header_token') ?>

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
