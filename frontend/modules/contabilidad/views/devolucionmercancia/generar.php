<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Generar Documento JSON';

$totalUnidades = !empty($detalle) ? array_sum(array_column($detalle, 'cantidad')) : 0;
$totalCosto    = !empty($detalle) ? array_sum(array_column($detalle, 'costo_total')) : 0;
$totalItems    = !empty($detalle) ? count($detalle) : 0;
?>

<h1><?= Html::encode($this->title) ?></h1>

<?php if (Yii::$app->session->hasFlash('error')): ?>
    <div class="alert alert-danger">
        <?= Yii::$app->session->getFlash('error') ?>
    </div>
<?php endif; ?>

<?php if (!empty($detalle)): ?>
    <div class="row mb-4 text-center">
        <div class="col-md-4">
            <div class="card shadow-sm p-3">
                <small class="text-muted">UNIDADES</small>
                <h4 class="mb-0"><?= number_format($totalUnidades, 0, ',', '.') ?></h4>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm p-3">
                <small class="text-muted">TOTAL COSTO</small>
                <h4 class="mb-0">$ <?= number_format($totalCosto, 0, ',', '.') ?></h4>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm p-3">
                <small class="text-muted">ÍTEMS</small>
                <h4 class="mb-0"><?= number_format($totalItems, 0, ',', '.') ?></h4>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="card card-body mb-4">
    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-4">
            <?= Html::label('Tipo de Documento') ?>
            <?= Html::textInput('tipo_documento', '', ['class' => 'form-control', 'value' => '2CV']) ?>
        </div>
        <div class="col-md-4">
            <?= Html::label('Fecha Documento') ?>
            <?= Html::input('date', 'fecha_documento', '', ['class' => 'form-control']) ?>
        </div>
        <div class="col-md-4">
            <?= Html::label('Tercero Proveedor') ?>
            <?= Html::textInput('tercero_proveedor', '', ['class' => 'form-control']) ?>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-4">
            <?= Html::label('Sucursal Proveedor') ?>
            <?= Html::textInput('sucursal_proveedor', '', ['class' => 'form-control']) ?>
        </div>
        <div class="col-md-4">
            <?= Html::label('Prefijo Documento Proveedor') ?>
            <?= Html::textInput('prefijo_documento_proveedor', '', ['class' => 'form-control']) ?>
        </div>
        <div class="col-md-4">
            <?= Html::label('Consecutivo Documento Proveedor') ?>
            <?= Html::textInput('consecutivo_documento_proveedor', '', ['class' => 'form-control']) ?>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-4">
            <?= Html::label('Fecha Documento Proveedor') ?>
            <?= Html::input('date', 'fecha_documento_proveedor', '', ['class' => 'form-control']) ?>
        </div>
    </div>

    <div class="form-group mt-4">
        <?= Html::submitButton('Enviar a siesa', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<?php if ($json): ?>
    <h3>JSON Generado</h3>
    <pre class="bg-dark text-white p-3 rounded" style="max-height:400px; overflow:auto;">
<?= json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?>
    </pre>
<?php endif; ?>
