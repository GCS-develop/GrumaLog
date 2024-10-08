<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\Transferenciaordencompraexcel $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="transferenciaordencompraexcel-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'idTransferenciaerp')->textInput() ?>

    <?= $form->field($model, 'centroOperacionDocumento')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'tipoDocumento')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'consecutivoDocumento')->textInput() ?>

    <?= $form->field($model, 'fechaDocumento')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'tercero')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'numeroFactura')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'sucursal')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'idTerceroComprador')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'consignacion')->textInput() ?>

    <?= $form->field($model, 'centroOperacionOrdenCompra')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'tipoDocumentoOrdenCompra')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'consecutivoOrdenCompra')->textInput() ?>

    <?= $form->field($model, 'centroOperacionMovimiento')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'tipoDocumentoMovimiento')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'consecutivoMovimiento')->textInput() ?>

    <?= $form->field($model, 'numeroRegistroMovimiento')->textInput() ?>

    <?= $form->field($model, 'bodegaMovimiento')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'unidadMovimiento')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'fechaEntregaMovimiento')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'cantidadBase')->textInput() ?>

    <?= $form->field($model, 'item')->textInput() ?>

    <?= $form->field($model, 'color')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'talla')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'rowid')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
