<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\search\OrdendecompraSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="ordendecompra-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'idCO') ?>

    <?= $form->field($model, 'idTipoDocumento') ?>

    <?= $form->field($model, 'consecutivo') ?>

    <?= $form->field($model, 'fecha') ?>

    <?php // echo $form->field($model, 'idProveedor') ?>

    <?php // echo $form->field($model, 'idEstado') ?>

    <?php // echo $form->field($model, 'fechaEntrega') ?>

    <?php // echo $form->field($model, 'totalCantidadPedida') ?>

    <?php // echo $form->field($model, 'totalCantidadEntrada') ?>

    <?php // echo $form->field($model, 'totalCantidadPendiente') ?>

    <?php // echo $form->field($model, 'nroPaquetes') ?>

    <?php // echo $form->field($model, 'comprador') ?>

    <?php // echo $form->field($model, 'nitcomprador') ?>

    <?php // echo $form->field($model, 'sucursalProveedor') ?>

    <?php // echo $form->field($model, 'idTipoDocumentoEntrada') ?>

    <?php // echo $form->field($model, 'idCODocumentoEntrada') ?>

    <?php // echo $form->field($model, 'fechaDocumentoEntrada') ?>

    <?php // echo $form->field($model, 'consecutivoDocumentoEntrada') ?>

    <?php // echo $form->field($model, 'consignacion') ?>

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
