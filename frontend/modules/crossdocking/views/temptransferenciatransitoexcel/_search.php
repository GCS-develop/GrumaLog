<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\search\TemptransferenciatransitoexcelSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="temptransferenciatransitoexcel-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'idTransferenciaerp') ?>

    <?= $form->field($model, 'centroOperacionDocumento') ?>

    <?= $form->field($model, 'tipoDocumento') ?>

    <?= $form->field($model, 'fechaDocumento') ?>

    <?php // echo $form->field($model, 'bodegaSalidaDocumento') ?>

    <?php // echo $form->field($model, 'bodegaEntradaDocumento') ?>

    <?php // echo $form->field($model, 'centroOperacion') ?>

    <?php // echo $form->field($model, 'tipoDocumentoMovimiento') ?>

    <?php // echo $form->field($model, 'bodegaSalidaMovimiento') ?>

    <?php // echo $form->field($model, 'centroOperacionMovimiento') ?>

    <?php // echo $form->field($model, 'unidadSalida') ?>

    <?php // echo $form->field($model, 'cantidadBase') ?>

    <?php // echo $form->field($model, 'costoPromedioUnitario') ?>

    <?php // echo $form->field($model, 'item') ?>

    <?php // echo $form->field($model, 'color') ?>

    <?php // echo $form->field($model, 'talla') ?>

    <?php // echo $form->field($model, 'numero') ?>

    <?php // echo $form->field($model, 'procesado') ?>

    <?php // echo $form->field($model, 'fila') ?>

    <?php // echo $form->field($model, 'notas') ?>

    <?php // echo $form->field($model, 'codigoBarras') ?>

    <?php // echo $form->field($model, 'codigoUnidadEmpaque') ?>

    <?php // echo $form->field($model, 'unidadesConteoEmpaque') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
