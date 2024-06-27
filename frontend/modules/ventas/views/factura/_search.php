<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\modules\ventas\models\search\FacturaSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="factura-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'centroOperacion') ?>

    <?= $form->field($model, 'tipoDocumento') ?>

    <?= $form->field($model, 'consecutivoDocumento') ?>

    <?= $form->field($model, 'fechaDocumento') ?>

    <?php // echo $form->field($model, 'codigoProveedor') ?>

    <?php // echo $form->field($model, 'documentoProveedor') ?>

    <?php // echo $form->field($model, 'codigoSucursal') ?>

    <?php // echo $form->field($model, 'prefijoDocumentoProveedor') ?>

    <?php // echo $form->field($model, 'consecutivoDocumentoProveedor') ?>

    <?php // echo $form->field($model, 'fechaDocumentoProveedor') ?>

    <?php // echo $form->field($model, 'condicionPago') ?>

    <?php // echo $form->field($model, 'tipoProveedor') ?>

    <?php // echo $form->field($model, 'valorDocumento') ?>

    <?php // echo $form->field($model, 'porcentajeCuota') ?>

    <?php // echo $form->field($model, 'fechaVencimientoCuota') ?>

    <?php // echo $form->field($model, 'fechaProntoPago') ?>

    <?php // echo $form->field($model, 'fechaDesde') ?>

    <?php // echo $form->field($model, 'fechaHasta') ?>

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
