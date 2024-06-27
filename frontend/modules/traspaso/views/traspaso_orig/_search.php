<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\search\TraspasoSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="traspaso-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'idBodegaOrigen') ?>

    <?= $form->field($model, 'idBodegaDestino') ?>

    <?= $form->field($model, 'numeroCajas') ?>

    <?= $form->field($model, 'idTipoDocumento') ?>

    <?php // echo $form->field($model, 'consecutivo') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
