<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\DocumentoGasto $model */
/** @var yii\widgets\ActiveForm $form */

$this->title = 'Formulario de Documento de Gasto';
?>

<div class="documento-gasto-form card shadow p-4 bg-white rounded">

    <h3 class="mb-4"><?= Html::encode($this->title) ?></h3>

    <?php $form = ActiveForm::begin([
        'options' => ['class' => 'row g-3'],
    ]); ?>

    <div class="col-md-4">
        <?= $form->field($model, 'F350_ID_CO')->textInput(['class' => 'form-control']) ?>
    </div>

    <div class="col-md-4">
        <?= $form->field($model, 'F350_ID_TIPO_DOCTO')->textInput(['maxlength' => true, 'class' => 'form-control']) ?>
    </div>

    <div class="col-md-4">
        <?= $form->field($model, 'F350_CONSEC_DOCTO')->textInput(['maxlength' => true, 'class' => 'form-control']) ?>
    </div>

    <div class="col-md-4">
        <?= $form->field($model, 'F350_FECHA')->input('date', ['class' => 'form-control']) ?>
    </div>

    <div class="col-md-4">
        <?= $form->field($model, 'F350_ID_TERCERO')->textInput(['maxlength' => true, 'class' => 'form-control']) ?>
    </div>

    <div class="col-md-4">
        <?= $form->field($model, 'F350_IND_ESTADO')->dropDownList([
            'A' => 'Activo',
            'I' => 'Inactivo',
        ], ['prompt' => 'Seleccione Estado', 'class' => 'form-control']) ?>
    </div>

    <div class="col-md-12">
        <?= $form->field($model, 'F350_NOTAS')->textarea(['rows' => 4, 'class' => 'form-control']) ?>
    </div>

    <div class="col-12 d-flex justify-content-between mt-3">
        <?= Html::submitButton('<i class="fas fa-save"></i> Guardar', ['class' => 'btn btn-success']) ?>

        <?= Html::button('<i class="fas fa-plus-circle"></i> Agregar Movimiento', [
    'class' => 'btn btn-primary',
    'id' => 'btnAgregarMovimiento',
    'data-toggle' => 'modal',
    'data-target' => '#modalMovimientoGasto',
]) ?>

    </div>

    <?php ActiveForm::end(); ?>

</div>
<?php
// Modal que cargará el formulario
\yii\bootstrap4\Modal::begin([
    'title' => '<h5>Agregar Movimiento</h5>',
    'id' => 'modalMovimientoGasto',
    'size' => \yii\bootstrap4\Modal::SIZE_LARGE,
    'options' => ['tabindex' => false], // importante para inputs en el modal
]);

echo '<div id="modalContentMovimiento"></div>';

\yii\bootstrap4\Modal::end();
?>
<?php
$createUrl = \yii\helpers\Url::to([
    'frontend/modules/contabilidad/views/movimiento-gasto/create.php',
    'F350_ID_CO' => $model->F350_ID_CO,
    'F350_ID_TIPO_DOCTO' => $model->F350_ID_TIPO_DOCTO,
    'F350_CONSEC_DOCTO' => $model->F350_CONSEC_DOCTO,
]);
$script = <<<JS
$('#btnAgregarMovimiento').on('click', function() {
    $('#modalContentMovimiento').html('<div class="text-center my-3"><i class="fas fa-spinner fa-spin fa-2x"></i> Cargando...</div>');
    $('#modalContentMovimiento').load('$createUrl');
});
JS;
$this->registerJs($script);
?>
