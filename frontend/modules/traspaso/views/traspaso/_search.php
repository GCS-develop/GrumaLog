<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\searchTraspasoSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="traspaso-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'idCentroOperacion') ?>

    <?= $form->field($model, 'idBodegaOrigen') ?>

    <?= $form->field($model, 'idBodegaDestino') ?>

    <?= $form->field($model, 'numeroCajas') ?>

    <?php // echo $form->field($model, 'idTipoDocumento') ?>

    <?php // echo $form->field($model, 'consecutivo') ?>

    <?php // echo $form->field($model, 'idEstado') ?>

    <?php // echo $form->field($model, 'idUltimoItem') ?>

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
