<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\search\TransferdevdocumentosSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="transferdevdocumentos-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'centro_operacion') ?>

    <?= $form->field($model, 'tipo_documento') ?>

    <?= $form->field($model, 'consecutivo_documento') ?>

    <?= $form->field($model, 'fecha_documento') ?>

    <?php // echo $form->field($model, 'tercero_proveedor') ?>

    <?php // echo $form->field($model, 'notas') ?>

    <?php // echo $form->field($model, 'sucursal_proveedor') ?>

    <?php // echo $form->field($model, 'comprador') ?>

    <?php // echo $form->field($model, 'consignacion') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
