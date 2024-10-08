<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\search\OrdendecompratemporalSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="ordendecompratemporal-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'idCO') ?>

    <?= $form->field($model, 'idTipoDocumento') ?>

    <?= $form->field($model, 'fechaDocumento') ?>

    <?= $form->field($model, 'idProveedor') ?>

    <?php // echo $form->field($model, 'sucursalProveedor') ?>

    <?php // echo $form->field($model, 'idComprador') ?>

    <?php // echo $form->field($model, 'idCondicionPago') ?>

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
