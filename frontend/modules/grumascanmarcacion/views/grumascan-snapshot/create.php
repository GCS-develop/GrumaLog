<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\GrumascanSnapshot $model */
/** @var frontend\models\Bodegas[] $bodegas */

$this->title = 'Crear Snapshot de Inventario';
$this->params['breadcrumbs'][] = ['label' => 'Snapshots', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$bodegaOptions = ArrayHelper::map($bodegas, 'id', function ($b) {
    $codigo = str_pad(ltrim((string)$b->codigo), 3, '0', STR_PAD_LEFT);
    return "[{$codigo}] {$b->nombre}";
});
?>

<div class="grumascan-snapshot-create" style="max-width:700px;">

    <h4>📸 Crear Snapshot de Inventario Siesa</h4>
    <p class="text-muted">
        Captura el inventario actual de Siesa para la bodega seleccionada y lo congela en este momento.
        Luego podrás asignarlo a los conteos del período para que el consolidado compare contra ese inventario.
    </p>

    <div class="alert alert-info">
        <strong>¿Cuándo usar?</strong> Antes de revisar el consolidado. El snapshot toma el inventario
        de Siesa <em>ahora mismo</em> para la bodega indicada.
    </div>

    <?php foreach (Yii::$app->session->getAllFlashes() as $type => $msg): ?>
        <div class="alert alert-<?= $type === 'error' ? 'danger' : $type ?>">
            <?= Html::encode(is_array($msg) ? implode(' ', $msg) : $msg) ?>
        </div>
    <?php endforeach; ?>

    <?php $form = ActiveForm::begin(['action' => ['create'], 'method' => 'post']); ?>

    <div class="card">
        <div class="card-body">

            <div class="form-group mb-3">
                <label class="form-label fw-bold">Bodega <span class="text-danger">*</span></label>
                <select name="idbodega" id="sel-bodega" class="form-control" required>
                    <option value="">-- Seleccione bodega --</option>
                    <?php foreach ($bodegas as $b):
                        $codigo = str_pad(ltrim((string)$b->codigo), 3, '0', STR_PAD_LEFT);
                    ?>
                        <option value="<?= $b->id ?>" data-codigo="<?= Html::encode($codigo) ?>">
                            [<?= Html::encode($codigo) ?>] <?= Html::encode($b->nombre) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="hidden" name="codigoBodega" id="inp-codigoBodega" value="">
                <small class="text-muted">Solo se captura el inventario de esta bodega en Siesa.</small>
            </div>

            <div class="form-group mb-3">
                <label class="form-label fw-bold">Descripción (opcional)</label>
                <input type="text" name="descripcion" class="form-control"
                       placeholder="Ej: Inventario bodega 075 - cierre marzo 2026" maxlength="200">
                <small class="text-muted">Ayuda a identificar el snapshot más adelante.</small>
            </div>

        </div>
    </div>

    <div class="mt-3 d-flex gap-2">
        <?= Html::submitButton('📸 Crear Snapshot (consulta Siesa ahora)', [
            'class' => 'btn btn-success',
            'id' => 'btn-crear',
        ]) ?>
        <?= Html::a('Cancelar', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
$js = <<<JS
// Al cambiar bodega, llenar el hidden codigoBodega
$('#sel-bodega').on('change', function () {
    var codigo = $(this).find(':selected').data('codigo') || '';
    $('#inp-codigoBodega').val(codigo);
});

// Confirmación antes de enviar (puede tardar si Siesa tiene muchos items)
$('#btn-crear').on('click', function (e) {
    var bodega = $('#sel-bodega').find(':selected').text().trim();
    if (!bodega || bodega === '-- Seleccione bodega --') {
        alert('Seleccione una bodega.');
        e.preventDefault();
        return;
    }
    if (!confirm('¿Crear snapshot de inventario Siesa para:\\n' + bodega + '?\\n\\nEsto puede tardar unos segundos mientras consulta Siesa.')) {
        e.preventDefault();
    }
});
JS;
$this->registerJs($js);
?>
