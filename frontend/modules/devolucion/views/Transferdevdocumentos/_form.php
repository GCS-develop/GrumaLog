<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use frontend\models\Proveedor;

/** @var yii\web\View $this */
/** @var frontend\models\Transferdevdocumentos $model */
/** @var yii\widgets\ActiveForm $form */
/** @var array $ids */

$centroOperacionConstante = '002'; // Valor fijo
$consecutivoDocumentoConstante = '1'; // Valor fijo
?>

<div class="transferdevdocumentos-form">

    <?php $form = ActiveForm::begin([
        'id' => 'transferencia-form',
        'enableClientValidation' => true,
    ]); ?>

    <?php
    // Asignar valores fijos al modelo
    $model->centro_operacion = $centroOperacionConstante;
    $model->consecutivo_documento = $consecutivoDocumentoConstante;
    ?>

    <!-- Campos ocultos fijos -->
    <?= $form->field($model, 'centro_operacion')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'consecutivo_documento')->hiddenInput()->label(false) ?>

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'tipo_documento')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'fecha_documento')->textInput(['type' => 'date']) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'tercero_proveedor')->widget(Select2::class, [
                'initValueText' => $model->tercero_proveedor
                    ? ($model->tercero_proveedor . ' - ' . (\frontend\models\Proveedor::findOne(['nit' => $model->tercero_proveedor])?->razonSocial ?? ''))
                    : '',
                'options' => [
                    'placeholder' => 'Buscar proveedor por NIT o nombre...',
                ],
                'pluginOptions' => [
                    'allowClear'         => true,
                    'minimumInputLength' => 2,
                    'ajax'               => [
                        'url'      => \yii\helpers\Url::to(['/devolucion/transferdevdocumentos/buscarproveedor']),
                        'dataType' => 'json',
                        'delay'    => 300,
                        'data'     => new \yii\web\JsExpression('function(params){ return {q: params.term}; }'),
                        'processResults' => new \yii\web\JsExpression('function(data){ return {results: data.results}; }'),
                        'cache'    => true,
                    ],
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

    <!-- 🔹 Campo oculto único con todos los IDs en JSON -->
    <?= Html::hiddenInput('ids_json', '', ['id' => 'ids-json']) ?>

    <div class="form-group text-center">
        <?= Html::submitButton('Guardar', [
            'class' => 'btn btn-success',
            'style' => 'width: 200px'
        ]) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
// 🔹 Script para capturar checkboxes seleccionados y meterlos en ids_json
$this->registerJs(<<<JS
    $('#transferencia-form').on('beforeSubmit', function () {
        let ids = [];
        $('input[name="selection[]"]:checked').each(function () {
            ids.push($(this).val());
        });
        $('#ids-json').val(JSON.stringify(ids));
        return true; // continuar con el submit
    });
JS);
?>
