<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use frontend\models\Proveedor;
use yii\helpers\ArrayHelper;

/** @var yii\web\View $this */
/** @var frontend\models\Transferdevdocumentos $model */
/** @var yii\widgets\ActiveForm $form */
$centroOperacionConstante = '002'; // Valor fijo
$consecutivoDocumentoConstante = '1'; // Valor fijo
?>

<div class="transferdevdocumentos-form">

    <?php $form = \yii\widgets\ActiveForm::begin([
        'id' => 'transferencia-form',
        'enableClientValidation' => true,
    ]); ?>

    <?php
    // Asignar los valores fijos
    $model->centro_operacion = $centroOperacionConstante;
    $model->consecutivo_documento = $consecutivoDocumentoConstante;
    ?>

    <!-- Campos ocultos -->
    <?= $form->field($model, 'centro_operacion')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'consecutivo_documento')->hiddenInput()->label(false) ?>

    <!-- Formulario visible -->
    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'tipo_documento')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'fecha_documento')->textInput(['type' => 'date']) ?>
        </div>

        <div class="col-md-4">
            <?= $form->field($model, 'tercero_proveedor')->widget(Select2::class, [
                'data' => \yii\helpers\ArrayHelper::map(
                    Proveedor::find()
                        ->select(['nit', "CONCAT(nit, ' - ', razonSocial) AS nombre"])
                        ->orderBy('razonSocial')
                        ->asArray()
                        ->all(),
                    'nit', // valor que se guardará
                    'nombre' // valor visible
                ),
                'options' => [
                    'placeholder' => 'Seleccione un proveedor por NIT o nombre...',
                ],
                'pluginOptions' => [
                    'allowClear' => true,
                ],
            ]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'sucursal_proveedor')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'comprador')->dropDownList(
                \frontend\models\Comprador::getListaDocumentoComoClave(),
                ['prompt' => 'Seleccione un comprador']
            ) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'consignacion')->dropDownList(
                [
                    1 => 'Sí',
                    0 => 'No',
                ],
                [
                    'prompt' => 'Seleccione una opción',
                    'class' => 'form-control',
                ]
            ) ?>
        </div>

    </div>

    <div class="row">
        <div class="col-md-12">
            <?= $form->field($model, 'notas')->textarea(['rows' => 2]) ?>
        </div>
    </div>



    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'motivo')->widget(Select2::class, [
                'data' => \frontend\models\Motivo::getListaMotivos(),
                'options' => ['placeholder' => 'Seleccione un motivo'],
                'pluginOptions' => ['allowClear' => true],
            ]) ?>
        </div>
    </div>



    <div class="form-group text-center">
        <?= \yii\helpers\Html::submitButton('Guardar', ['class' => 'btn btn-success', 'style' => 'width: 200px']) ?>
    </div>

    <?php \yii\widgets\ActiveForm::end(); ?>

</div>