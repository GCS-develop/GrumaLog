<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\search\DevoluciondocumentodetalleSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="devoluciondocumentodetalle-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'idDocumento') ?>

    <?= $form->field($model, 'codigoBarras') ?>

    <?= $form->field($model, 'cantidadDevolucion') ?>

    <?= $form->field($model, 'cantidadRegistrada') ?>

    <?= $form->field($model, 'fechaRegistra') ?>


    <?php // echo $form->field($model, 'item') ?>

    <?php // echo $form->field($model, 'talla') ?>

    <?php // echo $form->field($model, 'color') ?>

    <?php // echo $form->field($model, 'referencia') ?>

    <?php // echo $form->field($model, 'itemResumen') ?>

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