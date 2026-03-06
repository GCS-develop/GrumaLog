<?php

use yii\helpers\Html;
use yii\bootstrap4\Modal;

/** @var frontend\models\search\GrumascanEnvioFisicoPreviewSearch $searchModel */
/** @var array $totales */

$lineas = (int)($totales['lineas'] ?? 0);

Modal::begin([
    'title' => '<h5 class="mb-0">Consecutivo Siesa</h5>',
    'id' => 'modalConsecutivo',
    'size' => Modal::SIZE_DEFAULT,
    'options' => [
        'tabindex' => false,
    ],
]);

?>

<?= Html::beginForm(['terminar'], 'post', ['id' => 'formConsecutivo']); ?>
<?= Html::hiddenInput('codigoBodega', (string)$searchModel->codigoBodega); ?>
<?= Html::hiddenInput('fechaDesde', (string)$searchModel->fechaDesde); ?>
<?= Html::hiddenInput('fechaHasta', (string)$searchModel->fechaHasta); ?>
<?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>

<div class="text-muted small mb-2">
    Se continuará con el mapeo a Siesa usando el consolidado mostrado en el preview.
</div>

<?php if ($lineas === 0): ?>
    <div class="alert alert-warning mb-0">
        No hay datos para enviar. Cierra el modal y ejecuta <strong>Previsualizar</strong> primero.
    </div>
<?php else: ?>
    <div class="form-group mb-3">
        <label class="form-label">Consecutivo</label>
        <?= Html::textInput('consecutivo', '', [
            'class' => 'form-control',
            'maxlength' => 50,
            'required' => true,
            'autofocus' => true,
            'placeholder' => 'Ej: FIS-000123',
        ]) ?>
        <div class="text-muted small mt-1">
            Este consecutivo se usará para construir los movimientos y el JSON.
        </div>
    </div>

    <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-secondary mr-2" data-dismiss="modal">Cancelar</button>

        <button type="submit" class="btn btn-success mr-2" id="btnContinuar">
            <span class="btn-text">Continuar</span>
            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
        </button>
    </div>
<?php endif; ?>

<?= Html::endForm(); ?>

<?php Modal::end(); ?>

<?php
$this->registerJs(<<<JS
$('#formConsecutivo').on('submit', function() {
    var btn = $('#btnContinuar');

    // evita doble submit
    if (btn.data('submitted')) {
        return false;
    }
    btn.data('submitted', true);

    // UI: deshabilita y muestra spinner
    btn.prop('disabled', true);
    btn.find('.btn-text').text('Procesando...');
    btn.find('.spinner-border').removeClass('d-none');

    // opcional: deshabilitar también cancelar
    $(this).find('button, input, select, textarea').prop('readonly', true);
});
JS);
?>